<?php
// Haltelijst: wordt één keer opgebouwd uit de GTFS-dienstregeling.
// Per haltenaam (bijv. "Breda, Centraal Station") de haltecodes (TPC's) van de perrons en de plek op de kaart.
// Tegelijk komen per lijn de kleuren, het lijnnummer en de soort uit routes.txt in lijnen.json.

// route_type in GTFS → soort zoals de pagina hem toont
const GTFS_SOORTEN = ['0' => 'Tram', '1' => 'Metro', '2' => 'Trein', '3' => 'Bus', '4' => 'Boot'];

function bouwHaltes(): int
{
    set_time_limit(900);
    if (!is_dir(CACHE_MAP)) {
        mkdir(CACHE_MAP, 0775, true);
    }

    // De zip is ruim 200 MB; we halen hem op, lezen wat we nodig hebben en gooien hem weer weg.
    $zipPad  = CACHE_MAP . '/gtfs.zip';
    $bestand = fopen($zipPad, 'w');
    $curl    = curl_init(GTFS_URL);
    curl_setopt_array($curl, [CURLOPT_FILE => $bestand, CURLOPT_TIMEOUT => 600, CURLOPT_FAILONERROR => true]);
    $gelukt = curl_exec($curl);
    $fout   = curl_error($curl);
    curl_close($curl);
    fclose($bestand);

    try {
        if (!$gelukt) {
            throw new RuntimeException('Dienstregeling downloaden mislukt: ' . $fout);
        }
        return haltesUitZip($zipPad);
    } finally {
        @unlink($zipPad);
    }
}

// Leest een CSV-bestand uit de zip regel voor regel als array met kolomnamen.
function gtfsRegels(ZipArchive $zip, string $naam): Generator
{
    if (!($stream = $zip->getStream($naam))) {
        throw new RuntimeException($naam . ' niet gevonden in de dienstregeling.');
    }
    try {
        $kop = fgetcsv($stream);
        while ($rij = fgetcsv($stream)) {
            yield array_combine($kop, $rij);
        }
    } finally {
        fclose($stream); // anders blijft de zip open en kan Windows hem niet verwijderen
    }
}

function haltesUitZip(string $zipPad): int
{
    $zip = new ZipArchive();
    if ($zip->open($zipPad) !== true) {
        throw new RuntimeException('De dienstregeling is geen geldige zip.');
    }

    $haltes = [];
    foreach (gtfsRegels($zip, 'stops.txt') as $halte) {
        if ($halte['location_type'] !== '0') {
            continue;
        }
        // De code staat in stop_code, of anders achteraan stop_ifopt ("NL:Q:50000121").
        $code = $halte['stop_code'];
        if ($code === '' && preg_match('/^NL:Q:(\d+)$/', $halte['stop_ifopt'], $m)) {
            $code = $m[1];
        }
        if ($code === '' || !ctype_digit($code)) {
            continue;
        }
        $naam = $halte['stop_name'];
        $haltes[$naam]['c'][$code] = true;
        if ($halte['platform_code'] !== '') {
            $haltes[$naam]['pf'][$code] = $halte['platform_code']; // perron, bijv. "C2" (vooral op grote stations)
        }
        $haltes[$naam]['lat'][]    = (float) $halte['stop_lat'];
        $haltes[$naam]['lon'][]    = (float) $halte['stop_lon'];
    }

    $lijnen = [];
    foreach (gtfsRegels($zip, 'routes.txt') as $lijn) {
        // [achtergrondkleur, tekstkleur, lijnnummer, soort]; zonder eigen kleur kiest de pagina er een per soort.
        $lijnen[$lijn['route_id']] = [
            $lijn['route_color'] !== '' ? '#' . $lijn['route_color'] : null,
            $lijn['route_color'] !== '' ? '#' . ($lijn['route_text_color'] ?: 'ffffff') : null,
            $lijn['route_short_name'],
            GTFS_SOORTEN[$lijn['route_type']] ?? 'Bus',
        ];
    }
    $zip->close();

    // Per halte het midden van alle perrons als plek op de kaart.
    $lijst = [];
    foreach ($haltes as $naam => $halte) {
        $lijst[$naam] = [
            'c' => array_map('strval', array_keys($halte['c'])),
            'p' => [
                round(array_sum($halte['lat']) / count($halte['lat']), 6),
                round(array_sum($halte['lon']) / count($halte['lon']), 6),
            ],
        ];
        if (!empty($halte['pf'])) {
            $lijst[$naam]['pf'] = array_map('strval', $halte['pf']);
        }
    }
    ksort($lijst, SORT_NATURAL | SORT_FLAG_CASE);
    file_put_contents(HALTES_PAD, json_encode($lijst, JSON_UNESCAPED_UNICODE));
    file_put_contents(LIJNEN_PAD, json_encode($lijnen));
    return count($lijst);
}

function laadHaltes(): array
{
    return heeftHaltes() ? json_decode(file_get_contents(HALTES_PAD), true) : [];
}

// "Café" → "cafe", zodat zoeken niet struikelt over hoofdletters en accenten.
function plat(string $tekst): string
{
    $tekst = mb_strtolower($tekst);
    return strtr($tekst, ['á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
        'í' => 'i', 'ï' => 'i', 'ó' => 'o', 'ö' => 'o', 'ô' => 'o', 'ú' => 'u', 'ü' => 'u', 'û' => 'u']);
}

// Dichtstbijzijnde haltes met afstand in meters. Varianten per perron ("…, CS Jaarbeurszijde (C1)")
// worden overgeslagen als de halte zelf er ook bij staat.
function haltesInDeBuurt(float $lat, float $lon, int $max = 8): array
{
    $haltes   = laadHaltes();
    $cosLat   = cos(deg2rad($lat));
    $afstanden = [];
    foreach ($haltes as $naam => $halte) {
        if (preg_match('/^(.*) \([^)]+\)$/', $naam, $m) && isset($haltes[$m[1]])) {
            continue;
        }
        // Platte benadering is nauwkeurig genoeg voor een paar kilometer.
        $dy = ($halte['p'][0] - $lat) * 111320;
        $dx = ($halte['p'][1] - $lon) * 111320 * $cosLat;
        $afstanden[$naam] = sqrt($dx * $dx + $dy * $dy);
    }
    asort($afstanden);

    $uit = [];
    foreach (array_slice($afstanden, 0, $max, true) as $naam => $meters) {
        $uit[] = ['naam' => $naam, 'meters' => (int) round($meters)];
    }
    return $uit;
}

// Haltes binnen een rechthoek op de kaart (zuid, west, noord, oost), met dezelfde overslag van perronvarianten.
function haltesInGebied(float $zuid, float $west, float $noord, float $oost, int $max = 1500): array
{
    $haltes = laadHaltes();
    $uit    = [];
    foreach ($haltes as $naam => $halte) {
        [$lat, $lon] = $halte['p'];
        if ($lat < $zuid || $lat > $noord || $lon < $west || $lon > $oost) {
            continue;
        }
        if (preg_match('/^(.*) \([^)]+\)$/', $naam, $m) && isset($haltes[$m[1]])) {
            continue;
        }
        $uit[] = ['naam' => $naam, 'p' => $halte['p']];
        if (count($uit) >= $max) {
            break;
        }
    }
    return $uit;
}

// Haltes waarvan de naam alle zoekwoorden bevat; namen die met de zoekterm beginnen eerst.
function zoekHaltes(string $zoekterm, int $max = 15): array
{
    $zoekterm = plat(trim($zoekterm));
    $woorden  = preg_split('/[\s,]+/', $zoekterm, -1, PREG_SPLIT_NO_EMPTY);
    if (!$woorden) {
        return [];
    }

    $treffers = [];
    foreach (array_keys(laadHaltes()) as $naam) {
        $platteNaam = plat($naam);
        foreach ($woorden as $woord) {
            if (!str_contains($platteNaam, $woord)) {
                continue 2;
            }
        }
        $treffers[] = ['naam' => $naam, 'score' => (str_starts_with($platteNaam, $zoekterm) ? 0 : 1000) + strlen($naam)];
    }
    usort($treffers, fn ($a, $b) => $a['score'] <=> $b['score']);
    return array_column(array_slice($treffers, 0, $max), 'naam');
}
