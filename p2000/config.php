<?php
// Instellingen voor P2000 Live: oproepen van brandweer, ambulance, politie en kustwacht.
// Bron: de P2000-feed van Brandweer Berkel-Enschot (gemaakt voor Home Assistant, geen sleutel nodig).
date_default_timezone_set('Europe/Amsterdam');

const CACHE_MAP     = __DIR__ . '/cache';
const ARCHIEF_PAD   = CACHE_MAP . '/meldingen.json';
const FEED_URL      = 'https://p2000.brandweer-berkel-enschot.nl/homeassistant/rss.asp';
const PDOK_URL      = 'https://api.pdok.nl/bzk/locatieserver/search/v3_1/free'; // plaatsnaam → coördinaten
const CAPCODES_URL  = 'https://raw.githubusercontent.com/XMaarten/p2000-capcodes/main/data/capcodes.csv';
const CAPCODES_PAD  = CACHE_MAP . '/capcodes.json';
const CAPCODES_BEWAAR_S = 7 * 24 * 3600;
const FEED_INTERVAL = 60;          // de feed wordt eens per minuut bijgewerkt (ttl 60); vaker vragen heeft geen zin
const BEWAAR_S      = 24 * 3600;   // de feed bevat maar ~5 minuten, wij bewaren zelf een dag
const MAX_MELDINGEN = 300;         // zoveel stuurt de API hooguit terug
const STANDAARD_REGIO = '20';      // Midden- en West-Brabant

const REGIOS = [
    '01' => 'Groningen', '02' => 'Fryslân', '03' => 'Drenthe', '04' => 'IJsselland', '05' => 'Twente',
    '06' => 'Noord- en Oost-Gelderland', '07' => 'Gelderland-Midden', '08' => 'Gelderland-Zuid', '09' => 'Utrecht',
    '10' => 'Noord-Holland Noord', '11' => 'Zaanstreek-Waterland', '12' => 'Kennemerland', '13' => 'Amsterdam-Amstelland',
    '14' => 'Gooi en Vechtstreek', '15' => 'Haaglanden', '16' => 'Hollands Midden', '17' => 'Rotterdam-Rijnmond',
    '18' => 'Zuid-Holland Zuid', '19' => 'Zeeland', '20' => 'Midden- en West-Brabant', '21' => 'Brabant-Noord',
    '22' => 'Brabant-Zuidoost', '23' => 'Limburg-Noord', '24' => 'Zuid-Limburg', '25' => 'Flevoland',
    '00' => 'Landelijk / kustwacht',
];
