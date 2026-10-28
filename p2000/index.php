<?php
require __DIR__ . '/config.php';
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>P2000 Live</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" defer></script>
    <script src="js/app.js" defer></script>
</head>
<body>
<main>
    <a class="terug" href="../">← Alle projecten</a>
    <h1>P2000 Live</h1>

    <div class="filters">
        <label class="regio">
            <span>Regio</span>
            <select id="regio">
                <option value="alle">Heel Nederland</option>
                <?php foreach (REGIOS as $code => $naam): ?>
                    <?php $code = sprintf('%02d', $code); // PHP maakt van sleutel '20' het getal 20 ?>
                    <option value="<?= $code ?>"<?= $code === STANDAARD_REGIO ? ' selected' : '' ?>><?= htmlspecialchars($naam) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div id="diensten" class="diensten" role="group" aria-label="Diensten">
            <button type="button" class="dienst actief" data-dienst="Brandweer">Brandweer</button>
            <button type="button" class="dienst actief" data-dienst="Ambulance">Ambulance</button>
            <button type="button" class="dienst actief" data-dienst="Politie">Politie</button>
            <button type="button" class="dienst actief" data-dienst="Kustwacht">Kustwacht</button>
            <button type="button" class="dienst actief" data-dienst="Overig">Overig</button>
        </div>
    </div>

    <p id="status" class="status">Meldingen laden…</p>

    <div class="indeling">
        <div id="kaart" class="kaart"></div>
        <ol id="lijst" class="lijst"></ol>
    </div>

    <p class="voetnoot">
        Gegevens: <a href="https://p2000.brandweer-berkel-enschot.nl" target="_blank" rel="noopener">P2000 monitor Brandweer Berkel-Enschot</a>
        en plaatsen via <a href="https://www.pdok.nl" target="_blank" rel="noopener">PDOK</a>.
        Capcodes: <a href="https://github.com/XMaarten/p2000-capcodes" target="_blank" rel="noopener">p2000-capcodes</a>
        (bronnen: <a href="https://p2000.bommel.net" target="_blank" rel="noopener">P2000 Bommel</a>,
        <a href="https://capcodes.eu" target="_blank" rel="noopener">Capcodes.eu</a> en
        <a href="https://www.tomzulu10capcodes-brandweervoertuignummers.nl" target="_blank" rel="noopener">TomZulu10</a>).
        Het voertuigtype bij een roepnummer komt uit de landelijke indeling (5e cijfer bij de brandweer).
        Wordt eens per minuut bijgewerkt; meldingen van het afgelopen etmaal blijven bewaard zolang deze pagina af en toe openstaat.
        Een stippellijn op de kaart betekent: alleen de plaats is bekend, niet het adres.
    </p>
</main>
</body>
</html>
