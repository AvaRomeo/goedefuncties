<?php
require_once __DIR__ . '/eenheden.php'; // roepnummersUitBericht() voor de regio

// Haalt de P2000-feed op (hooguit eens per minuut, gedeeld door alle tabbladen) en bewaart de meldingen
// zelf, want de feed bevat maar de laatste ~5 minuten. Eén oproep gaat vaak naar meerdere capcodes
// (piepers); die voegen we samen tot één melding.

// Diensten zoals de feed ze noemt → korte naam. "Gereserveerd" en leeg zeggen niets over de dienst.
const DIENSTEN = [
    'Brandweerdiensten'  => 'Brandweer',
    'Ambulancediensten'  => 'Ambulance',
    'Politiediensten'    => 'Politie',
    'Kustwacht Centrum'  => 'Kustwacht',
    'KNRM'               => 'KNRM',
];

function laadArchief(): array
{
    return is_file(ARCHIEF_PAD) ? json_decode(file_get_contents(ARCHIEF_PAD), true) ?? [] : [];
}

// Zonder bekende dienst raden we hem uit het bericht: "A1 …"/"B2 …" is ambulance, "P 1 BNH-02 …" brandweer.
function raadDienst(string $bericht): string
{
    if (preg_match('/^(A[012]|B[12])\b/i', $bericht)) {
        return 'Ambulance';
    }
    if (preg_match('/^P ?[123]\b/i', $bericht)) {
        return 'Brandweer';
    }
    return 'Overig';
}

// "A1", "B2", "P 1" → prioriteit voor het labeltje; anders leeg.
function prioriteit(string $bericht): string
{
    if (preg_match('/^(A[012]|B[12])\b/i', $bericht, $m)) {
        return strtoupper($m[1]);
    }
    if (preg_match('/^(?:P|PRIO) ?([123])\b/i', $bericht, $m)) {
        return 'P' . $m[1];
    }
    return '';
}

// "A1 Drunen Rit: 128513" → "Drunen". Alleen ambulance-oproepen hebben zo'n vaste vorm.
function plaatsUitBericht(string $bericht): ?string
{
    return preg_match('/^(?:A[012]|B[12])\s+(.+?)\s+rit\b/i', $bericht, $m) ? $m[1] : null;
}

// Plaatsnaam → [lat, lon] via PDOK (overheid, gratis). Elke plaats wordt één keer opgezocht en bewaard,
// ook als hij niet gevonden wordt; hooguit $max nieuwe plaatsen per keer.
function zoekPlaatsen(array $namen, int $max = 5): array
{
    $pad      = CACHE_MAP . '/plaatsen.json';
    $plaatsen = is_file($pad) ? json_decode(file_get_contents($pad), true) ?? [] : [];
    $nieuw    = 0;

    foreach (array_unique($namen) as $naam) {
        $sleutel = mb_strtolower($naam);
        if (array_key_exists($sleutel, $plaatsen) || $nieuw >= $max) {
            continue;
        }
        $nieuw++;
        $url = PDOK_URL . '?' . http_build_query([
            'q' => 'woonplaatsnaam:"' . $naam . '"', 'fq' => 'type:woonplaats', 'rows' => 1, 'fl' => 'centroide_ll',
        ]);
        $antwoord = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 5]]));
        if ($antwoord === false) {
            continue; // niet bereikbaar: volgende keer opnieuw proberen
        }
        $punt = json_decode($antwoord, true)['response']['docs'][0]['centroide_ll'] ?? '';
        $plaatsen[$sleutel] = preg_match('/POINT\(([\d.]+) ([\d.]+)\)/', $punt, $m)
            ? [round((float) $m[2], 5), round((float) $m[1], 5)]
            : null;
    }

    if ($nieuw) {
        file_put_contents($pad, json_encode($plaatsen, JSON_UNESCAPED_UNICODE));
    }
    return $plaatsen;
}

function leesFeed(string $xml): array
{
    $feed = @simplexml_load_string($xml);
    if (!$feed || !isset($feed->channel->item)) {
        throw new UnexpectedValueException('De P2000-feed is niet leesbaar.');
    }

    $meldingen = [];
    foreach ($feed->channel->item as $item) {
        $bericht = trim((string) $item->message);
        $tijd    = strtotime((string) $item->pubDate);
        if ($bericht === '' || !$tijd) {
            continue;
        }
        $id = md5($tijd . '|' . $bericht);
        $melding = $meldingen[$id] ?? [
            'id'       => $id,
            'tijd'     => $tijd,
            'bericht'  => $bericht,
            'prio'     => prioriteit($bericht),
            'dienst'   => null,
            'regio'    => null,
            'p'        => null,
            'capcodes' => [],
        ];

        $melding['capcodes'][] = (string) $item->code;
        $melding['dienst'] ??= DIENSTEN[(string) $item->Dienst] ?? null;
        $regio = trim((string) $item->RegCode);
        if ($regio !== '' && $regio !== '00') {
            $melding['regio'] ??= $regio;
        }
        $lat = (float) (string) $item->lat;
        $lon = (float) (string) $item->lon;
        if ($lat && $lon) {
            $melding['p'] ??= [round($lat, 5), round($lon, 5)];
        }
        $meldingen[$id] = $melding;
    }

    foreach ($meldingen as &$melding) {
        $melding['dienst'] ??= raadDienst($melding['bericht']);
        // Geen regio in de feed: het eerste roepnummer in het bericht begint met de regiocode ("04-1832").
        $roepnummer = roepnummersUitBericht($melding['bericht'], $melding['dienst'])[0]['nummer'] ?? '';
        if (!$melding['regio'] && isset(REGIOS[substr($roepnummer, 0, 2)])) {
            $melding['regio'] = substr($roepnummer, 0, 2);
        }
        $melding['regio'] ??= '00';
        $melding['capcodes'] = array_values(array_unique($melding['capcodes']));
    }
    unset($melding);

    // Zonder coördinaten: het midden van de plaats uit het bericht, als die te vinden is ("ongeveer").
    $zonderPlek = array_filter($meldingen, fn ($melding) => !$melding['p'] && plaatsUitBericht($melding['bericht']));
    if ($zonderPlek) {
        $plaatsen = zoekPlaatsen(array_map(fn ($melding) => plaatsUitBericht($melding['bericht']), $zonderPlek));
        foreach ($zonderPlek as $id => $melding) {
            $plek = $plaatsen[mb_strtolower(plaatsUitBericht($melding['bericht']))] ?? null;
            if ($plek) {
                $meldingen[$id]['p']         = $plek;
                $meldingen[$id]['ongeveer'] = true;
            }
        }
    }
    return $meldingen;
}

// Vult het archief aan met de feed als die minstens FEED_INTERVAL oud is; geeft het archief terug.
function bijgewerktArchief(): array
{
    $archief = laadArchief();
    if (is_file(ARCHIEF_PAD) && time() - filemtime(ARCHIEF_PAD) < FEED_INTERVAL) {
        return $archief;
    }
    if (!is_dir(CACHE_MAP)) {
        mkdir(CACHE_MAP, 0775, true);
    }

    // Slot: als een ander tabblad al aan het ophalen is, gebruiken we gewoon wat er al is.
    $slot = fopen(CACHE_MAP . '/meldingen.lock', 'c');
    if (!flock($slot, LOCK_EX | LOCK_NB)) {
        fclose($slot);
        return $archief;
    }

    try {
        $curl = curl_init(FEED_URL);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_USERAGENT      => 'goedefuncties-p2000 (persoonlijk gebruik, eens per minuut)',
        ]);
        $xml    = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($xml !== false && $status === 200) {
            // Nieuwe meldingen erbij; een bestaande melding kan er capcodes bij krijgen.
            foreach (leesFeed($xml) as $id => $melding) {
                if (isset($archief[$id])) {
                    $melding['capcodes'] = array_values(array_unique([...$archief[$id]['capcodes'], ...$melding['capcodes']]));
                }
                $archief[$id] = $melding;
            }
        }
    } catch (UnexpectedValueException) {
        // Kapotte feed: volgende minuut opnieuw proberen.
    } finally {
        $grens   = time() - BEWAAR_S;
        $archief = array_filter($archief, fn ($melding) => $melding['tijd'] >= $grens);
        uasort($archief, fn ($a, $b) => $b['tijd'] <=> $a['tijd']);
        file_put_contents(ARCHIEF_PAD, json_encode($archief, JSON_UNESCAPED_UNICODE)); // ook bij een fout: zo wachten we weer een minuut
        flock($slot, LOCK_UN);
        fclose($slot);
    }
    return $archief;
}
