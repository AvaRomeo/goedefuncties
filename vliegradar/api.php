<?php
// Geeft eenvoudige JSON terug voor de pagina.
// api.php?actie=vliegtuigen&lat=52.2&lon=5.3&straal=150   → vliegtuigen binnen die straal (zeemijl)
// api.php?actie=route&vlucht=KLM1234                       → maatschappij, vertrek en bestemming
require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

function antwoord(array $gegevens, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($gegevens, JSON_UNESCAPED_UNICODE);
    exit;
}

function ophalen(string $url): ?array
{
    $curl = curl_init($url);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_USERAGENT => USER_AGENT]);
    $tekst  = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return $tekst !== false && $status === 200 ? json_decode($tekst, true) : null;
}

function uitCache(string $naam, int $seconden): ?array
{
    $pad = CACHE_MAP . '/' . $naam;
    return is_file($pad) && time() - filemtime($pad) < $seconden ? json_decode(file_get_contents($pad), true) : null;
}

function inCache(string $naam, array $gegevens): void
{
    if (!is_dir(CACHE_MAP)) {
        mkdir(CACHE_MAP, 0775, true);
    }
    file_put_contents(CACHE_MAP . '/' . $naam, json_encode($gegevens, JSON_UNESCAPED_UNICODE));
}

function vliegtuigen(float $lat, float $lon, int $straal): array
{
    // Afronden, zodat bijna dezelfde kaartbeelden (ook in andere tabbladen) dezelfde cache delen.
    $lat    = round($lat * 5) / 5;
    $lon    = round($lon * 5) / 5;
    $straal = min(MAX_STRAAL_NM, max(10, (int) ceil($straal / 25) * 25));

    $cacheNaam = sprintf('vliegtuigen_%s_%s_%d.json', $lat, $lon, $straal);
    if ($cache = uitCache($cacheNaam, VLIEGTUIGEN_CACHE_S)) {
        return $cache;
    }

    $gegevens = ophalen(sprintf(ADSB_URL, $lat, $lon, $straal));
    if ($gegevens === null) {
        throw new RuntimeException('adsb.lol is nu niet bereikbaar. Probeer het zo nog eens.');
    }

    $lijst = [];
    foreach ($gegevens['ac'] ?? [] as $vliegtuig) {
        if (!isset($vliegtuig['lat'], $vliegtuig['lon'])) {
            continue;
        }
        $hoogte = $vliegtuig['alt_baro'] ?? $vliegtuig['alt_geom'] ?? null;
        $lijst[] = [
            'id'         => $vliegtuig['hex'],
            'vlucht'     => trim($vliegtuig['flight'] ?? ''),
            'reg'        => $vliegtuig['r'] ?? '',
            'type'       => $vliegtuig['t'] ?? '',
            'p'          => [round($vliegtuig['lat'], 5), round($vliegtuig['lon'], 5)],
            'grond'      => $hoogte === 'ground',
            'hoogte'     => is_numeric($hoogte) ? (int) $hoogte : null,           // voet
            'snelheid'   => isset($vliegtuig['gs']) ? (int) round($vliegtuig['gs']) : null, // knopen
            'koers'      => $vliegtuig['track'] ?? $vliegtuig['true_heading'] ?? $vliegtuig['mag_heading'] ?? null,
            'stijgen'    => $vliegtuig['baro_rate'] ?? $vliegtuig['geom_rate'] ?? null,   // voet per minuut
            'squawk'     => $vliegtuig['squawk'] ?? '',
            'nood'       => ($vliegtuig['emergency'] ?? 'none') !== 'none'
                || in_array($vliegtuig['squawk'] ?? '', ['7500', '7600', '7700'], true),
            'militair'   => (($vliegtuig['dbFlags'] ?? 0) & 1) === 1,
        ];
    }

    $resultaat = ['tijd' => date('H:i:s'), 'vliegtuigen' => $lijst];
    inCache($cacheNaam, $resultaat);
    ruimOp();
    return $resultaat;
}

// Elk kaartbeeld krijgt een eigen cachebestand; af en toe de oude weggooien.
function ruimOp(): void
{
    if (random_int(1, 20) !== 1) {
        return;
    }
    foreach (glob(CACHE_MAP . '/vliegtuigen_*.json') as $pad) {
        if (time() - filemtime($pad) > 60) {
            @unlink($pad);
        }
    }
}

function route(string $vlucht): array
{
    $cacheNaam = 'route_' . $vlucht . '.json';
    if (($cache = uitCache($cacheNaam, ROUTE_CACHE_S)) !== null) {
        return $cache;
    }

    $route = ophalen(sprintf(ROUTE_URL, rawurlencode($vlucht)))['response']['flightroute'] ?? null;
    $luchthaven = fn (?array $haven) => $haven ? [
        'naam'  => $haven['name'],
        'plaats' => $haven['municipality'] ?? '',
        'land'  => $haven['country_name'] ?? '',
        'code'  => ($haven['iata_code'] ?? '') ?: ($haven['icao_code'] ?? ''),
    ] : null;

    // Ook "geen route gevonden" bewaren, dan vragen we het niet steeds opnieuw.
    $resultaat = [
        'gevonden'      => (bool) $route,
        'maatschappij'  => $route['airline']['name'] ?? null,
        'van'           => $luchthaven($route['origin'] ?? null),
        'naar'          => $luchthaven($route['destination'] ?? null),
    ];
    inCache($cacheNaam, $resultaat);
    return $resultaat;
}

try {
    switch ($_GET['actie'] ?? '') {
        case 'vliegtuigen':
            if (!is_numeric($_GET['lat'] ?? null) || !is_numeric($_GET['lon'] ?? null) || !is_numeric($_GET['straal'] ?? null)) {
                antwoord(['fout' => 'Geen geldig gebied.'], 400);
            }
            antwoord(vliegtuigen((float) $_GET['lat'], (float) $_GET['lon'], (int) $_GET['straal']));
        case 'route':
            $vlucht = strtoupper(trim($_GET['vlucht'] ?? ''));
            if (!preg_match('/^[A-Z0-9]{2,8}$/', $vlucht)) {
                antwoord(['fout' => 'Ongeldig vluchtnummer.'], 400);
            }
            antwoord(route($vlucht));
        default:
            antwoord(['fout' => 'Onbekende actie.'], 400);
    }
} catch (RuntimeException $e) {
    antwoord(['fout' => $e->getMessage()], 502);
}
