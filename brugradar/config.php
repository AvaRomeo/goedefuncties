<?php
// Instellingen voor Brugradar: welke bruggen nu open staan en wanneer ze open gaan.
// Bronnen: NDW (brugopeningen, open data, geen sleutel nodig) en vaarweginformatie.nl van Rijkswaterstaat (brugnamen).
date_default_timezone_set('Europe/Amsterdam');

const CACHE_MAP        = __DIR__ . '/cache';
const NDW_URL          = 'https://opendata.ndw.nu/planningsfeed_brugopeningen.xml.gz';
const VAARWEG_URL      = 'https://www.vaarweginformatie.nl/wfswms/dataservice/1.4';
const USER_AGENT       = 'goedefuncties-brugradar (persoonlijk gebruik)';
const OPENINGEN_CACHE_S = 55;               // NDW werkt de feed elke minuut bij; de pagina vraagt elke minuut
const BRUGGEN_CACHE_S  = 7 * 24 * 3600;     // namen en ligging van bruggen veranderen zelden
const BRUGGEN_PER_PAGINA = 500;             // meer geeft vaarweginformatie.nl niet per keer
const MAX_AFSTAND_M    = 150;               // zo dicht moet een NDW-punt bij een brug liggen om die naam te krijgen
const MAX_GEPLAND      = 12;                // zoveel komende openingen per brug meesturen
