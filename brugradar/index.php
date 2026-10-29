<?php
require __DIR__ . '/config.php';
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Brugradar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" defer></script>
    <script src="js/app.js" defer></script>
</head>
<body>
<main>
    <div class="kop">
        <div>
            <a class="terug" href="../">← Alle projecten</a>
            <h1>Brugradar</h1>
        </div>
        <div class="bediening">
            <input id="zoek" type="search" placeholder="Zoek brug of plaats" autocomplete="off">
            <label class="schakelaar"><input id="alleen-open" type="checkbox"> Alleen open bruggen</label>
        </div>
    </div>

    <p id="status" class="status">Bruggen laden…</p>

    <div class="indeling">
        <div class="kaart-vak">
            <div id="kaart" class="kaart"></div>

            <aside id="paneel" class="paneel" hidden>
                <button id="sluit" type="button" class="sluit" title="Sluiten">×</button>
                <h2 id="p-naam"></h2>
                <p id="p-plaats" class="zacht"></p>
                <p id="p-toestand" class="toestand"></p>
                <h3>Geplande openingen</h3>
                <ul id="p-gepland" class="gepland"></ul>
                <dl class="gegevens">
                    <dt>Doorvaarten</dt><dd id="p-openingen"></dd>
                    <dt>Bediening</dt><dd id="p-bediening"></dd>
                    <dt>Telefoon</dt><dd id="p-telefoon"></dd>
                    <dt>Code</dt><dd id="p-code"></dd>
                </dl>
            </aside>
        </div>

        <section class="zijlijst" aria-label="Overzicht">
            <h2>Nu open <span id="aantal-open" class="teller"></span></h2>
            <ul id="lijst-open" class="bruglijst"></ul>
            <h2>Gaat binnenkort open</h2>
            <ul id="lijst-binnenkort" class="bruglijst"></ul>
        </section>
    </div>

    <div class="legenda" aria-label="Kleur per toestand">
        <span><i class="stip open"></i>open (weg dicht)</span>
        <span><i class="stip binnenkort"></i>opent binnen 15 minuten</span>
        <span><i class="stip dicht"></i>dicht</span>
    </div>

    <p class="voetnoot">
        Brugopeningen: <a href="https://opendata.ndw.nu" target="_blank" rel="noopener">NDW</a> (open data),
        brugnamen: <a href="https://www.vaarweginformatie.nl" target="_blank" rel="noopener">vaarweginformatie.nl</a> (Rijkswaterstaat).
        Alleen bruggen die hun openingen aan NDW doorgeven; "open" komt van een sensor op de brug,
        geplande openingen zijn een verwachting en kunnen verschuiven.
    </p>
</main>
</body>
</html>
