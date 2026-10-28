<?php
// Geeft eenvoudige JSON terug voor de pagina.
// api.php?actie=zoek&q=breda centraal             → haltenamen die passen
// api.php?actie=buurt&lat=51.59&lon=4.78         → dichtstbijzijnde haltes met afstand
// api.php?actie=kaart&gebied=z,w,n,o&haltes=1    → alle voertuigen (en eventueel haltes) in dat gebied
// api.php?actie=vertrek&halte=Breda, Centraal Station → vertrektijden van die halte, met live posities
// api.php?actie=haltes_bijwerken (POST)          → haltelijst (opnieuw) opbouwen
require __DIR__ . '/config.php';
require __DIR__ . '/haltes.php';
require __DIR__ . '/voertuigen.php';

header('Content-Type: application/json; charset=utf-8');

function antwoord(array $gegevens, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($gegevens, JSON_UNESCAPED_UNICODE);
    exit;
}

function ovapiOphalen(string $pad): array
{
    $curl = curl_init(OVAPI_BASIS . $pad);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15]);
    $tekst  = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($tekst === false || $status !== 200) {
        throw new RuntimeException('OVapi is nu niet bereikbaar. Probeer het zo nog eens.');
    }
    return json_decode($tekst, true) ?? [];
}

const SOORTEN = ['BUS' => 'Bus', 'TRAM' => 'Tram', 'METRO' => 'Metro', 'BOAT' => 'Boot', 'TRAIN' => 'Trein'];

const VERVOERDERS = [
    'ARR' => 'Arriva', 'BRAVO' => 'Bravo', 'CXX' => 'Connexxion', 'EBS' => 'EBS', 'GVB' => 'GVB', 'HTM' => 'HTM',
    'KEOLIS' => 'Keolis', 'MEERPLUS' => 'Meerplus', 'QBUZZ' => 'Qbuzz', 'RET' => 'RET', 'SYNTUS' => 'Syntus',
    'TESO' => 'TESO', 'UOV' => 'U-OV', 'WPD' => 'Wagenborg', 'DOEKSEN' => 'Doeksen', 'HERMES' => 'Hermes',
];

// "BRAVO:ARR" → "Bravo (Arriva)", "QBUZZ" → "Qbuzz"; onbekende codes blijven zoals ze zijn.
function vervoerder(string $code): string
{
    $namen = array_map(fn ($deel) => VERVOERDERS[$deel] ?? $deel, explode(':', $code));
    return count($namen) > 1 ? $namen[0] . ' (' . implode(', ', array_slice($namen, 1)) . ')' : $namen[0];
}

function kaartGegevens(string $gebied, bool $metHaltes): array
{
    $grenzen = array_map('floatval', explode(',', $gebied));
    if (count($grenzen) !== 4) {
        throw new InvalidArgumentException('Geen geldig gebied.');
    }
    [$zuid, $west, $noord, $oost] = $grenzen;

    $kleuren    = lijnKleuren();
    $voertuigen = [];
    foreach (laadVoertuigen() as $sleutel => $voertuig) {
        [$lat, $lon, $tijdstip, $nummer, $route] = $voertuig;
        if ($lat < $zuid || $lat > $noord || $lon < $west || $lon > $oost) {
            continue;
        }
        [$kleur, $tekstkleur, $lijn, $soort] = $kleuren[$route] ?? [null, null, '', 'Bus'];
        $voertuigen[] = [
            'id'         => $sleutel,
            'p'          => [$lat, $lon],
            'gezien'     => $tijdstip,
            'voertuig'   => $nummer,
            'lijn'       => $lijn,
            'soort'      => $soort,
            'kleur'      => $kleur,
            'tekstkleur' => $tekstkleur,
            'vervoerder' => vervoerder($voertuig[5] ?? ''),
        ];
    }

    return [
        'feed'       => feedTijd() ? date('H:i:s', feedTijd()) : null,
        'voertuigen' => $voertuigen,
        'haltes'     => $metHaltes ? haltesInGebied($zuid, $west, $noord, $oost) : [],
    ];
}

function vertrektijden(string $halte): array
{
    $gegevens = laadHaltes()[$halte] ?? null;
    if (!$gegevens) {
        throw new InvalidArgumentException('Deze halte ken ik niet.');
    }
    $codes = $gegevens['c'];

    $cachePad = CACHE_MAP . '/vertrek_' . md5($halte) . '.json';
    if (is_file($cachePad) && time() - filemtime($cachePad) < VERTREK_CACHE_S) {
        return json_decode(file_get_contents($cachePad), true);
    }

    $nu        = time();
    $ritten    = [];
    $meldingen = [];
    foreach (array_chunk($codes, 25) as $groep) {
        foreach (ovapiOphalen('/tpc/' . implode(',', $groep)) as $perron) {
            foreach ($perron['GeneralMessages'] ?? [] as $melding) {
                if (!empty($melding['MessageContent'])) {
                    $meldingen[$melding['MessageContent']] = true;
                }
            }
            foreach ($perron['Passes'] ?? [] as $rit) {
                // Ritten die hier eindigen of al weg zijn, horen niet op een vertrekbord.
                if (($rit['JourneyStopType'] ?? '') === 'LAST'
                    || in_array($rit['TripStopStatus'] ?? '', ['PASSED', 'ARRIVED'], true)) {
                    continue;
                }
                $gepland  = strtotime($rit['TargetDepartureTime']);
                $verwacht = strtotime($rit['ExpectedDepartureTime'] ?? $rit['TargetDepartureTime']);
                if ($verwacht < $nu - 60) {
                    continue;
                }
                $haltecode = $rit['TimingPointCode'] ?? $perron['Stop']['TimingPointCode'] ?? '';
                $ritten[] = [
                    'sleutel'     => ($rit['LinePlanningNumber'] ?? '') . ':' . ($rit['JourneyNumber'] ?? ''),
                    'perron'      => $gegevens['pf'][$haltecode] ?? '',
                    'verwacht'    => $verwacht,
                    'tijd'        => date('H:i', $gepland),
                    'vertraging'  => max(0, (int) round(($verwacht - $gepland) / 60)),
                    'lijn'        => $rit['LinePublicNumber'] ?? '',
                    'soort'       => SOORTEN[$rit['TransportType'] ?? ''] ?? 'Bus',
                    'richting'    => $rit['DestinationName50'] ?? '',
                    'vervoerder'  => vervoerder($rit['OperatorCode'] ?? ''),
                    'live'        => ($rit['TripStopStatus'] ?? '') === 'DRIVING',
                    'geannuleerd' => ($rit['TripStopStatus'] ?? '') === 'CANCEL',
                ];
            }
        }
    }

    usort($ritten, fn ($a, $b) => $a['verwacht'] <=> $b['verwacht']);
    $ritten = array_slice($ritten, 0, MAX_RITTEN);

    // Live positie erbij voor ritten die nu rijden en in de feed staan.
    $voertuigen = laadVoertuigen();
    $kleuren    = lijnKleuren();
    foreach ($ritten as &$rit) {
        $voertuig = $voertuigen[$rit['sleutel']] ?? null;
        if ($voertuig) {
            [$lat, $lon, $tijdstip, $nummer, $route] = $voertuig;
            $rit['positie']  = [$lat, $lon];
            $rit['gezien']   = $tijdstip;
            $rit['voertuig'] = $nummer;
            [$rit['kleur'], $rit['tekstkleur']] = $kleuren[$route] ?? [null, null];
        }
    }
    unset($rit);

    $resultaat = [
        'bijgewerkt' => date('H:i:s'),
        'halte'      => $gegevens['p'],
        'ritten'     => $ritten,
        'meldingen'  => array_keys($meldingen),
    ];
    file_put_contents($cachePad, json_encode($resultaat, JSON_UNESCAPED_UNICODE));
    return $resultaat;
}

try {
    switch ($_GET['actie'] ?? '') {
        case 'zoek':
            antwoord(zoekHaltes($_GET['q'] ?? ''));
        case 'buurt':
            if (!is_numeric($_GET['lat'] ?? null) || !is_numeric($_GET['lon'] ?? null)) {
                antwoord(['fout' => 'Geen geldige locatie.'], 400);
            }
            antwoord(haltesInDeBuurt((float) $_GET['lat'], (float) $_GET['lon']));
        case 'kaart':
            antwoord(kaartGegevens($_GET['gebied'] ?? '', !empty($_GET['haltes'])));
        case 'vertrek':
            antwoord(vertrektijden($_GET['halte'] ?? ''));
        case 'haltes_bijwerken':
            if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
                antwoord(['fout' => 'Gebruik POST.'], 405);
            }
            antwoord(['aantal' => bouwHaltes()]);
        default:
            antwoord(['fout' => 'Onbekende actie.'], 400);
    }
} catch (InvalidArgumentException $e) {
    antwoord(['fout' => $e->getMessage()], 404);
} catch (RuntimeException $e) {
    antwoord(['fout' => $e->getMessage()], 502);
}
