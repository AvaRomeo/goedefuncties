<?php
// Geeft eenvoudige JSON terug voor de pagina.
// api.php?regio=20            → meldingen van die regio (nieuwste eerst), "alle" voor heel Nederland,
//                               met per melding de gealarmeerde eenheden
require __DIR__ . '/config.php';
require __DIR__ . '/meldingen.php';
require_once __DIR__ . '/eenheden.php';

header('Content-Type: application/json; charset=utf-8');

$regio = $_GET['regio'] ?? STANDAARD_REGIO;
if ($regio !== 'alle' && !isset(REGIOS[$regio])) {
    http_response_code(400);
    echo json_encode(['fout' => 'Onbekende regio.']);
    exit;
}

$meldingen = [];
$archief   = bijgewerktArchief();
$capcodes  = laadCapcodes();
foreach ($archief as $melding) {
    if ($regio === 'alle' || $melding['regio'] === $regio) {
        $meldingen[] = metEenheden($melding, $capcodes);
        if (count($meldingen) >= MAX_MELDINGEN) {
            break;
        }
    }
}

echo json_encode([
    'bijgewerkt' => is_file(ARCHIEF_PAD) ? date('H:i:s', filemtime(ARCHIEF_PAD)) : null,
    'meldingen'  => $meldingen,
], JSON_UNESCAPED_UNICODE);
