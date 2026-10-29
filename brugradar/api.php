<?php
// Geeft eenvoudige JSON terug voor de pagina.
// api.php   → alle bruggen uit de NDW-feed met naam, ligging, of ze nu open zijn en de komende geplande openingen
require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

function antwoord(array $gegevens, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($gegevens, JSON_UNESCAPED_UNICODE);
    exit;
}

function ophalen(string $url): ?string
{
    $curl = curl_init($url);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_USERAGENT => USER_AGENT]);
    $tekst  = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return $tekst !== false && $status === 200 ? $tekst : null;
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

// Alle beweegbare bruggen van vaarweginformatie.nl (naam, plaats, ligging), een week bewaard.
// Lukt ophalen niet, dan de oude lijst gebruiken; zonder namen werkt de kaart ook.
function beweegbareBruggen(): array
{
    if ($cache = uitCache('bruggen.json', BRUGGEN_CACHE_S)) {
        return $cache;
    }

    $generatie = json_decode(ophalen(VAARWEG_URL . '/geogeneration') ?? '', true)['GeoGeneration'] ?? null;
    $bruggen   = [];
    for ($offset = 0, $totaal = 1; $generatie && $offset < $totaal; $offset += BRUGGEN_PER_PAGINA) {
        $pagina = json_decode(ophalen(sprintf('%s/%d/bridge?offset=%d&count=%d', VAARWEG_URL, $generatie, $offset, BRUGGEN_PER_PAGINA)) ?? '', true);
        if (!isset($pagina['Result'])) {
            $bruggen = [];
            break;
        }
        $totaal = $pagina['TotalCount'];
        foreach ($pagina['Result'] as $brug) {
            if (!($brug['CanOpen'] ?? false) || !preg_match('/POINT \(([\d.-]+) ([\d.-]+)\)/', $brug['Geometry'] ?? '', $punt)) {
                continue;
            }
            $bruggen[] = [
                'naam'      => $brug['Name'] ?? '',
                'plaats'    => $brug['City'] ?? '',
                'p'         => [(float) $punt[2], (float) $punt[1]],
                'telefoon'  => $brug['PhoneNumber'] ?? '',
                'openingen' => $brug['NumberOfOpenings'] ?? null,
                'opAfstand' => $brug['IsRemoteControlled'] ?? false,
            ];
        }
    }

    if (!$bruggen) {
        $oud = CACHE_MAP . '/bruggen.json';
        return is_file($oud) ? json_decode(file_get_contents($oud), true) : [];
    }
    inCache('bruggen.json', $bruggen);
    return $bruggen;
}

// Afstand in meters; op deze korte afstanden is een platte benadering ruim nauwkeurig genoeg.
function afstand(array $a, array $b): float
{
    return hypot(($a[0] - $b[0]) * 111320, ($a[1] - $b[1]) * 111320 * cos(deg2rad($a[0])));
}

function dichtstbijzijndeBrug(array $punt, array $bruggen): ?array
{
    $beste = null;
    $kortste = MAX_AFSTAND_M;
    foreach ($bruggen as $brug) {
        $meter = afstand($punt, $brug['p']);
        if ($meter < $kortste) {
            $kortste = $meter;
            $beste = $brug;
        }
    }
    return $beste;
}

// De NDW-feed bevat per brug losse meldingen: "certain" is een brug die nu open staat (gemeten),
// "riskOf" een geplande opening met begin- en eindtijd.
function openingen(): array
{
    if ($cache = uitCache('openingen.json', OPENINGEN_CACHE_S)) {
        return $cache;
    }

    $gzip = ophalen(NDW_URL);
    $xml  = $gzip !== null ? @gzdecode($gzip) : false;
    $feed = $xml ? @simplexml_load_string($xml) : false;
    if (!$feed) {
        throw new RuntimeException('De NDW-feed is nu niet bereikbaar. Probeer het zo nog eens.');
    }
    $feed->registerXPathNamespace('sit', 'http://datex2.eu/schema/3/situation');

    $nu    = time();
    $lijst = [];
    foreach ($feed->xpath('//sit:situationRecord') as $melding) {
        $sit = $melding->children('http://datex2.eu/schema/3/situation');
        if ((string) $sit->generalNetworkManagementType !== 'bridgeSwingInOperation') {
            continue;
        }
        $tijden = $sit->validity->children('http://datex2.eu/schema/3/common')->validityTimeSpecification;
        $locatie = $sit->locationReference->children('http://datex2.eu/schema/3/locationReferencing');
        $coordinaten = $locatie->pointByCoordinates->pointCoordinates;

        $code  = (string) $locatie->externalReferencing->externalLocationCode;
        $begin = strtotime((string) $tijden->overallStartTime);
        $eind  = (string) $tijden->overallEndTime !== '' ? strtotime((string) $tijden->overallEndTime) : null;
        if ($code === '' || !$begin || ($eind !== null && $eind < $nu)) {
            continue;
        }

        $lijst[$code] ??= ['p' => [(float) $coordinaten->latitude, (float) $coordinaten->longitude], 'open' => [], 'gepland' => []];
        if ((string) $sit->probabilityOfOccurrence === 'certain' && $begin <= $nu) {
            $lijst[$code]['open'][] = $begin;
        } else {
            $lijst[$code]['gepland'][] = [$begin, $eind];
        }
    }

    $resultaat = ['tijd' => date('H:i:s'), 'meldingen' => $lijst];
    inCache('openingen.json', $resultaat);
    return $resultaat;
}

// Geplande vensters die elkaar overlappen (bijv. bij een brug met twee doorvaarten) samenvoegen tot één opening.
function samengevoegd(array $vensters): array
{
    usort($vensters, fn ($a, $b) => $a[0] <=> $b[0]);
    $lijst = [];
    foreach ($vensters as [$begin, $eind]) {
        $laatste = count($lijst) - 1;
        if ($laatste >= 0 && $lijst[$laatste][1] !== null && $begin <= $lijst[$laatste][1]) {
            $lijst[$laatste][1] = $eind === null ? null : max($lijst[$laatste][1], $eind);
        } else {
            $lijst[] = [$begin, $eind];
        }
    }
    return $lijst;
}

try {
    $openingen = openingen();
    $bruggen   = beweegbareBruggen();

    $lijst = [];
    foreach ($openingen['meldingen'] as $code => $melding) {
        $brug = dichtstbijzijndeBrug($melding['p'], $bruggen);
        $lijst[] = [
            'id'        => $code,
            'naam'      => $brug['naam'] ?? 'Onbekende brug',
            'plaats'    => $brug['plaats'] ?? '',
            'p'         => $melding['p'],
            'openSinds' => $melding['open'] ? min($melding['open']) : null,
            'gepland'   => array_slice(samengevoegd($melding['gepland']), 0, MAX_GEPLAND),
            'telefoon'  => $brug['telefoon'] ?? '',
            'openingen' => $brug['openingen'] ?? null,
            'opAfstand' => $brug['opAfstand'] ?? false,
        ];
    }
    antwoord(['tijd' => $openingen['tijd'], 'nu' => time(), 'bruggen' => $lijst]);
} catch (RuntimeException $e) {
    antwoord(['fout' => $e->getMessage()], 502);
}
