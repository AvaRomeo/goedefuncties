<?php
// Instellingen voor Vliegradar: vliegtuigen live op de kaart.
// Bronnen: adsb.lol (posities, ODbL, geen sleutel nodig) en adsbdb.com (vertrek en bestemming per vluchtnummer).
date_default_timezone_set('Europe/Amsterdam');

const CACHE_MAP          = __DIR__ . '/cache';
const ADSB_URL           = 'https://api.adsb.lol/v2/point/%s/%s/%d';   // lat, lon, straal in zeemijl
const ROUTE_URL          = 'https://api.adsbdb.com/v0/callsign/%s';
const USER_AGENT         = 'goedefuncties-vliegradar (persoonlijk gebruik)';
const MAX_STRAAL_NM      = 250;          // meer staat adsb.lol niet toe
const VLIEGTUIGEN_CACHE_S = 4;           // de pagina vraagt elke 5 s; tabbladen delen de gegevens
const ROUTE_CACHE_S      = 24 * 3600;    // routes per vluchtnummer veranderen zelden
