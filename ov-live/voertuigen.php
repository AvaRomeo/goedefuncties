<?php
// Live posities van alle bussen, trams en metro's uit de GTFS-realtime feed van OVapi.
// OVapi geeft "429 Too Many Requests" als je te vaak vraagt: daarom hooguit eens per VOERTUIGEN_CACHE_S
// ophalen, gedeeld door alle tabbladen, en bij een mislukte poging de vorige posities houden.

// Minimale protobuf-lezer: geeft per veldnummer een lijst met waarden.
function pbVelden(string $bytes): array
{
    $velden = [];
    $i      = 0;
    $lengte = strlen($bytes);
    while ($i < $lengte) {
        $sleutel = pbVarint($bytes, $i);
        $type    = $sleutel & 7;
        if ($type === 0) {
            $waarde = pbVarint($bytes, $i);
        } elseif ($type === 2) {
            $grootte = pbVarint($bytes, $i);
            $waarde  = substr($bytes, $i, $grootte);
            $i      += $grootte;
        } elseif ($type === 5) {
            $waarde = unpack('g', substr($bytes, $i, 4))[1]; // float
            $i     += 4;
        } elseif ($type === 1) {
            $waarde = substr($bytes, $i, 8);
            $i     += 8;
        } else {
            throw new UnexpectedValueException('Onbekend protobuf-type.');
        }
        $velden[$sleutel >> 3][] = $waarde;
    }
    if ($i !== $lengte) {
        throw new UnexpectedValueException('Feed is afgebroken.');
    }
    return $velden;
}

function pbVarint(string $bytes, int &$i): int
{
    $waarde = 0;
    $schuif = 0;
    do {
        if (!isset($bytes[$i])) {
            throw new UnexpectedValueException('Feed is afgebroken.');
        }
        $byte    = ord($bytes[$i++]);
        $waarde |= ($byte & 0x7f) << $schuif;
        $schuif += 7;
    } while ($byte & 0x80);
    return $waarde;
}

// Zet de feed om naar [ "lijnplanningsnummer:ritnummer" => [lat, lon, tijd, voertuignummer, route_id, vervoerder] ].
// Die sleutel staat ook bij elke rit in de vertrektijden van OVapi (LinePlanningNumber + JourneyNumber).
function leesVoertuigenFeed(string $feed): array
{
    $voertuigen = [];
    foreach (pbVelden($feed)[2] ?? [] as $entiteit) {
        $entiteit = pbVelden($entiteit);
        if (!isset($entiteit[4][0], $entiteit[1][0])) {
            continue;
        }
        // id: "2026-10-28:ARR:23003:1110" = datum : vervoerder : lijnplanningsnummer : ritnummer
        $id = explode(':', $entiteit[1][0]);
        if (count($id) < 4) {
            continue;
        }
        $voertuig = pbVelden($entiteit[4][0]);
        $positie  = isset($voertuig[2][0]) ? pbVelden($voertuig[2][0]) : [];
        if (!isset($positie[1][0], $positie[2][0])) {
            continue;
        }
        $rit    = isset($voertuig[1][0]) ? pbVelden($voertuig[1][0]) : [];
        $nummer = isset($voertuig[8][0]) ? pbVelden($voertuig[8][0]) : [];

        $voertuigen[$id[2] . ':' . $id[3]] = [
            round($positie[1][0], 6),
            round($positie[2][0], 6),
            $voertuig[5][0] ?? null,
            $nummer[2][0] ?? $nummer[1][0] ?? '',
            $rit[5][0] ?? '',
            $id[1],
        ];
    }
    return $voertuigen;
}

// OVapi vernieuwt de feed eens per minuut. We onthouden wanneer (Last-Modified) en vragen pas weer
// zodra er een nieuwe zou moeten zijn; met If-Modified-Since krijgen we "304" als hij er nog niet is.
function laadVoertuigen(): array
{
    $pad      = CACHE_MAP . '/voertuigen.json';
    $metaPad  = CACHE_MAP . '/voertuigen.meta.json';
    $meta     = (is_file($metaPad) ? json_decode(file_get_contents($metaPad), true) : null) ?? ['feed' => 0, 'poging' => 0];
    $vorige   = fn () => is_file($pad) ? json_decode(file_get_contents($pad), true) ?? [] : [];
    $nu       = time();

    if (is_file($pad) && ($nu < $meta['feed'] + VOERTUIGEN_INTERVAL_S || $nu < $meta['poging'] + VOERTUIGEN_PROBEER_S)) {
        return $vorige();
    }

    // Slot: als een ander tabblad al aan het ophalen is, gebruiken we gewoon de vorige posities.
    $slot = fopen(CACHE_MAP . '/voertuigen.lock', 'c');
    if (!flock($slot, LOCK_EX | LOCK_NB)) {
        fclose($slot);
        return $vorige();
    }

    $meta['poging'] = $nu;
    try {
        $curl = curl_init(VOERTUIGEN_URL);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_FILETIME       => true,
            CURLOPT_TIMECONDITION  => CURL_TIMECOND_IFMODSINCE,
            CURLOPT_TIMEVALUE      => is_file($pad) ? $meta['feed'] : 0,
        ]);
        $feed      = curl_exec($curl);
        $status    = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $soort     = (string) curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        $gewijzigd = curl_getinfo($curl, CURLINFO_FILETIME);
        curl_close($curl);

        if ($feed !== false && $status === 200 && $feed !== '' && !str_contains($soort, 'html')) {
            $voertuigen = leesVoertuigenFeed($feed);
            if ($voertuigen) {
                file_put_contents($pad, json_encode($voertuigen));
                $meta['feed'] = $gewijzigd > 0 ? $gewijzigd : $nu;
                return $voertuigen;
            }
        }
    } catch (UnexpectedValueException) {
        // Kapotte of half geschreven feed: vorige posities houden.
    } finally {
        file_put_contents($metaPad, json_encode($meta));
        flock($slot, LOCK_UN);
        fclose($slot);
    }

    return $vorige();
}

// Wanneer OVapi de posities die we hebben gepubliceerd heeft (unix-tijd), of 0 als onbekend.
function feedTijd(): int
{
    $metaPad = CACHE_MAP . '/voertuigen.meta.json';
    return is_file($metaPad) ? (int) (json_decode(file_get_contents($metaPad), true)['feed'] ?? 0) : 0;
}

function lijnKleuren(): array
{
    return is_file(LIJNEN_PAD) ? json_decode(file_get_contents(LIJNEN_PAD), true) ?? [] : [];
}
