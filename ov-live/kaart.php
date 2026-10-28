<?php
require __DIR__ . '/config.php';
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OV Live · Kaart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" defer></script>
    <script src="js/kaart.js" defer></script>
</head>
<body>
<main class="breed">
    <a class="terug" href="../">← Alle projecten</a>
    <h1>OV Live</h1>
    <nav class="tabs">
        <a href="./">Vertrekbord</a>
        <a class="actief" href="kaart.php">Kaart</a>
    </nav>

    <?php if (!heeftHaltes()): ?>
        <section class="uitleg">
            <h2>Eerst de haltelijst ophalen</h2>
            <p>Ga naar het <a href="./">vertrekbord</a> en haal daar eenmalig de haltelijst op.</p>
        </section>
    <?php else: ?>
        <p id="status" class="status">Voertuigen laden…</p>
        <div id="grote-kaart" class="grote-kaart"></div>
        <p class="voetnoot">
            Gegevens: <a href="https://ovapi.nl" target="_blank" rel="noopener">OVapi</a>. OVapi vernieuwt de posities eens per minuut;
            niet elke bus geeft zijn positie door. Haltes verschijnen als je verder inzoomt.
        </p>
    <?php endif; ?>
</main>
</body>
</html>
