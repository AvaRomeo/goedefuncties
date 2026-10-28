<?php
// Instellingen voor OV Live: vertrektijden en live posities van bus, tram, metro en veerboot.
// Bron: OVapi, geen sleutel nodig.
date_default_timezone_set('Europe/Amsterdam');

const CACHE_MAP          = __DIR__ . '/cache';
const HALTES_PAD         = CACHE_MAP . '/haltes.json';
const LIJNEN_PAD         = CACHE_MAP . '/lijnen.json';
const OVAPI_BASIS        = 'http://v0.ovapi.nl';                           // OVapi werkt alleen via http
const GTFS_URL           = 'http://gtfs.ovapi.nl/nl/gtfs-nl.zip';          // dienstregeling, alleen nodig voor de haltelijst
const VOERTUIGEN_URL     = 'http://gtfs.ovapi.nl/nl/vehiclePositions.pb';  // live posities (GTFS-realtime)
const VERTREK_CACHE_S    = 10;                                             // spaart aanroepen als meerdere tabbladen openstaan
const VOERTUIGEN_INTERVAL_S = 62;  // OVapi vernieuwt de posities eens per minuut (+ 2 s speling)
const VOERTUIGEN_PROBEER_S  = 10;  // nog geen nieuwe feed: zo lang wachten tot de volgende poging (anders "429")
const MAX_RITTEN         = 40;

// Een haltelijst van vóór de kaart heeft nog geen coördinaten ("p") en moet opnieuw worden opgehaald.
function heeftHaltes(): bool
{
    return is_file(HALTES_PAD) && is_file(LIJNEN_PAD);
}
