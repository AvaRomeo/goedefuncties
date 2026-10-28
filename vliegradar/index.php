<?php
require __DIR__ . '/config.php';
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vliegradar</title>
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
            <h1>Vliegradar</h1>
        </div>
        <div class="bediening">
            <input id="zoek" type="search" placeholder="Zoek vlucht, registratie of type" autocomplete="off">
            <label class="schakelaar"><input id="grond" type="checkbox"> Ook op de grond</label>
        </div>
    </div>

    <p id="status" class="status">Vliegtuigen laden…</p>

    <div class="indeling">
        <div id="kaart" class="kaart"></div>

        <aside id="paneel" class="paneel" hidden>
            <button id="sluit" type="button" class="sluit" title="Sluiten">×</button>
            <h2 id="p-vlucht"></h2>
            <p id="p-maatschappij" class="zacht"></p>
            <div id="p-route" class="route"></div>
            <dl class="gegevens">
                <dt>Type</dt><dd id="p-type"></dd>
                <dt>Registratie</dt><dd id="p-reg"></dd>
                <dt>Hoogte</dt><dd id="p-hoogte"></dd>
                <dt>Snelheid</dt><dd id="p-snelheid"></dd>
                <dt>Koers</dt><dd id="p-koers"></dd>
                <dt>Stijgen/dalen</dt><dd id="p-stijgen"></dd>
                <dt>Squawk</dt><dd id="p-squawk"></dd>
                <dt>Transponder</dt><dd id="p-hex"></dd>
            </dl>
        </aside>
    </div>

    <div class="legenda" aria-label="Kleur per hoogte">
        <span><i style="background:#9ca3af"></i>grond</span>
        <span><i style="background:#84cc16"></i>&lt; 1.000 m</span>
        <span><i style="background:#14b8a6"></i>&lt; 3.000 m</span>
        <span><i style="background:#3b82f6"></i>&lt; 6.000 m</span>
        <span><i style="background:#8b5cf6"></i>&lt; 9.000 m</span>
        <span><i style="background:#ec4899"></i>hoger</span>
        <span><i style="background:#dc2626"></i>noodcode</span>
    </div>

    <p class="voetnoot">
        Posities: <a href="https://adsb.lol" target="_blank" rel="noopener">adsb.lol</a> (ODbL), routes:
        <a href="https://www.adsbdb.com" target="_blank" rel="noopener">adsbdb.com</a>.
        Alleen vliegtuigen die door vrijwilligers met een ontvanger worden opgepikt; de route hoort bij het vluchtnummer
        en klopt meestal, maar niet altijd.
    </p>
</main>
</body>
</html>
