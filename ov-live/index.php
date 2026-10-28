<?php
require __DIR__ . '/config.php';
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OV Live</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" defer></script>
    <script src="js/app.js" defer></script>
</head>
<body>
<main>
    <a class="terug" href="../">← Alle projecten</a>
    <h1>OV Live</h1>
    <nav class="tabs">
        <a class="actief" href="./">Vertrekbord</a>
        <a href="kaart.php">Kaart</a>
    </nav>

    <?php if (!heeftHaltes()): ?>
        <section class="uitleg">
            <h2>Eerst de haltelijst ophalen</h2>
            <p>Om haltes te kunnen zoeken is eenmalig een lijst met alle haltes van Nederland nodig. Die komt uit de
                dienstregeling van OVapi (een download van ruim 200 MB, duurt ongeveer een minuut).</p>
            <button id="haltes-ophalen" type="button">Haltelijst ophalen</button>
            <p id="ophalen-status" class="status"></p>
        </section>
    <?php else: ?>
        <div id="favorieten" class="favorieten" hidden></div>

        <label class="zoeken" for="halte">Halte</label>
        <div class="zoekrij">
            <input id="halte" list="haltes" placeholder="Typ een halte, bijv. Breda Centraal Station" autocomplete="off">
            <datalist id="haltes"></datalist>
            <button id="favoriet" type="button" class="knop-licht" disabled>☆ Bewaar</button>
            <button id="in-de-buurt" type="button" class="knop-licht">📍 In de buurt</button>
        </div>
        <div id="buurt" class="buurt" hidden></div>

        <p id="status" class="status">Kies een halte.</p>
        <ul id="meldingen" class="meldingen" hidden></ul>

        <div id="kaart" class="kaart" hidden></div>

        <p id="filter" class="filter" hidden></p>

        <table id="bord" class="bord" hidden>
            <thead>
                <tr><th>Vertrek</th><th>Lijn</th><th class="kolom-perron">Perron</th><th>Naar</th><th>Vervoerder</th></tr>
            </thead>
            <tbody></tbody>
        </table>

        <p class="voetnoot">
            Gegevens: <a href="https://ovapi.nl" target="_blank" rel="noopener">OVapi</a>. OVapi vernieuwt de posities eens per minuut;
            niet elke bus geeft zijn positie door.
            <button id="haltes-ophalen" type="button" class="link">Haltelijst bijwerken</button>
            <span id="ophalen-status"></span>
        </p>
    <?php endif; ?>
</main>
</body>
</html>
