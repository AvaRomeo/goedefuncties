<?php
// Wie is er gealarmeerd? Twee bronnen:
// 1. capcodes → omschrijving uit de capcodelijst van XMaarten/p2000-capcodes (MIT; bronnen: P2000 Bommel,
//    Capcodes.eu en TomZulu10, hergebruik met bronvermelding). Eens per week opnieuw opgehaald van GitHub.
// 2. roepnummers in het bericht zelf: brandweer "203132" → 20-3132, ambulance "AMBU 17130" → 17-130.

// Landelijke betekenis van het 5e cijfer van een brandweerroepnummer.
const BRANDWEER_CATEGORIEEN = [
    '0' => 'Personenvervoer',
    '1' => 'Waterongevallen',
    '2' => 'Gevaarlijke stoffen',
    '3' => 'Tankautospuit',
    '4' => 'Natuurbrand',
    '5' => 'Redvoertuig',
    '6' => 'Bijzondere brandbestrijding',
    '7' => 'Ondersteuning',
    '8' => 'Ondersteuning',
    '9' => 'Overig',
];

// Betekenis van het 3e cijfer van een ambulanceroepnummer.
const AMBULANCE_GROEPEN = [
    '1' => 'Ambulance', '2' => 'Ambulance', '3' => 'Speciale functie', '4' => 'Zorgambulance',
    '5' => 'RAV-leiding', '6' => 'Internationaal', '7' => 'Huisarts',
];

function bouwCapcodes(): int
{
    $csv = @file_get_contents(CAPCODES_URL, false, stream_context_create(['http' => ['timeout' => 60]]));
    if ($csv === false || !str_starts_with($csv, 'capcode,')) {
        throw new RuntimeException('De capcodelijst kon niet worden opgehaald.');
    }

    $stream = fopen('php://memory', 'r+');
    fwrite($stream, $csv);
    rewind($stream);
    $kop = fgetcsv($stream);

    $capcodes = [];
    while ($rij = fgetcsv($stream)) {
        $rij  = array_combine($kop, $rij);
        $naam = $rij['description'] ?: $rij['unit_type_name'] ?: $rij['service'];
        if ($naam === '') {
            continue;
        }
        // Plaats/kazerne en roepnaam alleen als ze nog niet in de omschrijving staan.
        $extra = [];
        foreach (array_unique(array_filter([$rij['station'] ?: $rij['location'], $rij['callsign']])) as $stuk) {
            if (stripos($naam, $stuk) === false) {
                $extra[] = $stuk;
            }
        }
        // Codes die niemand specifiek alarmeren: monitors, groepscodes, lichtkranten in de kazerne, tests.
        $groep = (bool) preg_match('/monitor|groepsoproep|groepscode|group-\d|flex protocol|lichtkrant|testoproep|proefalarm/i',
            $naam . ' ' . $rij['unit_type_name']);

        $capcodes[$rij['capcode_short']] = [$naam, implode(' · ', $extra), $rij['discipline'], $groep];
    }

    file_put_contents(CAPCODES_PAD, json_encode($capcodes, JSON_UNESCAPED_UNICODE));
    return count($capcodes);
}

// De capcodelijst; wordt opgehaald als hij er nog niet is of ouder is dan een week.
function laadCapcodes(): array
{
    $verouderd = !is_file(CAPCODES_PAD) || time() - filemtime(CAPCODES_PAD) > CAPCODES_BEWAAR_S;
    if ($verouderd) {
        $slot = fopen(CACHE_MAP . '/capcodes.lock', 'c');
        if (flock($slot, LOCK_EX | LOCK_NB)) {
            try {
                bouwCapcodes();
            } catch (RuntimeException) {
                if (is_file(CAPCODES_PAD)) {
                    touch(CAPCODES_PAD); // oude lijst houden en over een week opnieuw proberen
                }
            } finally {
                flock($slot, LOCK_UN);
                fclose($slot);
            }
        } else {
            fclose($slot);
        }
    }
    return is_file(CAPCODES_PAD) ? json_decode(file_get_contents(CAPCODES_PAD), true) ?? [] : [];
}

// Roepnummers die in het bericht zelf staan, met het soort voertuig.
function roepnummersUitBericht(string $bericht, string $dienst): array
{
    $roepnummers = [];
    if ($dienst === 'Brandweer') {
        // 6 cijfers: regio (01-28, of 35 voor KNRM) + post + categorie + volgnummer, bijv. 203132.
        preg_match_all('/\b(0[1-9]|1\d|2[0-8]|35)(\d{2})(\d)(\d)\b/', $bericht, $treffers, PREG_SET_ORDER);
        foreach ($treffers as [, $regio, $post, $categorie, $volgnummer]) {
            if ("$post$categorie$volgnummer" === '0000') {
                continue; // bijv. "130000": een meldkamercode, geen voertuig
            }
            $roepnummers[] = ['nummer' => "$regio-$post$categorie$volgnummer", 'soort' => BRANDWEER_CATEGORIEEN[$categorie]];
        }
    }
    if ($dienst === 'Ambulance' && preg_match_all('/\bAMBU\s*(\d{2})(\d)(\d{2})\b/i', $bericht, $treffers, PREG_SET_ORDER)) {
        foreach ($treffers as [, $regio, $groep, $nummer]) {
            $roepnummers[] = ['nummer' => "$regio-$groep$nummer", 'soort' => AMBULANCE_GROEPEN[$groep] ?? 'Ambulance'];
        }
    }
    return array_values(array_unique($roepnummers, SORT_REGULAR));
}

// Vult een melding aan met 'eenheden' (per capcode) en 'roepnummers' (uit het bericht).
function metEenheden(array $melding, array $capcodes): array
{
    $melding['eenheden'] = array_map(function (string $capcode) use ($capcodes) {
        [$naam, $extra, , $groep] = $capcodes[$capcode] ?? ['Onbekende capcode', '', '', false];
        return ['capcode' => $capcode, 'naam' => $naam, 'extra' => $extra, 'groep' => $groep];
    }, $melding['capcodes']);
    $melding['roepnummers'] = roepnummersUitBericht($melding['bericht'], $melding['dienst']);
    return $melding;
}
