# ETS2 Dashboard

Een live dashboard voor tijdens het spelen van Euro Truck Simulator 2, bedoeld voor een
tweede scherm. Toont brandstofbereik en resterende rijtijd tot de verplichte pauze,
zodat je de in-game SOS-hulp niet nodig hebt.

## Installatie

1. **Installeer de ETS2 Telemetry Server (Funbit).**
   Download en installeer de plugin vanaf de [Funbit ETS2 Telemetry Server](https://github.com/Funbit/ets2-telemetry-server)
   releases-pagina. Dit is een losse Windows-applicatie/plugin die telemetriegegevens
   van het spel uitleest en op `http://localhost:25555` beschikbaar stelt.
2. **Start Euro Truck Simulator 2** en start daarna (of vooraf) de telemetry-server.
   Controleer of `http://localhost:25555/api/ets2/telemetry` in je browser JSON teruggeeft
   terwijl je in-game bent.
3. **Draai de migraties** van dit Laravel-project (indien nog niet gedaan):
   ```
   php artisan migrate
   ```
   Dit maakt de tabel `telemetry_sessions` aan, die de rijtijd-status server-side
   bijhoudt (zodat een refresh van de browser de timer niet reset).
4. **Open het dashboard** in je browser op `/ets2-dashboard` (bijv. op een tweede
   scherm/monitor tijdens het rijden).

## Hoe het werkt

- De Blade-view pollt elke 1,5 seconde de route `GET /api/telemetry/status`
  (`TelemetryController@status`).
- Die controller-actie fungeert als **proxy** naar de lokale telemetry-server
  (`Http::get('http://localhost:25555/api/ets2/telemetry')`), zodat de browser niet
  rechtstreeks hoeft te pollen (voorkomt CORS-problemen) en de rijtijd/pauze-logica
  server-side (database) bijgehouden wordt.
- Rijdt de truck (snelheid > 1 km/u) dan loopt de rijtijd-teller op. Staat de truck
  langer dan 60 seconden stil, dan wordt dit als pauze beschouwd en reset de teller.
  Beide drempels zijn instelbaar via de constanten bovenin `TelemetryController`.
- Is de telemetry-server niet bereikbaar (spel niet gestart, plugin niet actief), dan
  toont het dashboard een duidelijke "geen verbinding"-status in plaats van te crashen.

## "In de buurt": dichtstbijzijnd tankstation & rustplek (optioneel)

Het dashboard kan ook het dichtstbijzijnde tankstation en de dichtstbijzijnde rustplek
tonen, met een haalbaarheids-check tegen je resterende bereik. Dit is **optioneel** —
zonder de kaartdata hieronder toont het dashboard gewoon een "nog niet geladen"-melding
in plaats van te crashen.

De ETS2-telemetrie zelf geeft geen lijst van tankstations/rustplaatsen en ook geen
routepolylijn (alleen de rechte-lijn-afstand tot je bestemming). Daarom is een losse,
zelf te genereren kaartdataset nodig:

1. **Genereer het POI-bestand** met de open-source tool
   [truckermudgeon/maps](https://github.com/truckermudgeon/maps) op de PC waar Euro
   Truck Simulator 2 geïnstalleerd staat (vereist Node.js + git):
   ```
   git clone --recurse-submodules https://github.com/truckermudgeon/maps.git
   cd maps
   npm install
   npm run build -w packages/clis/parser
   npx parser -i "<pad naar je ETS2-installatiemap (met de .scs-bestanden)>" -o ./output
   ```
   Dit duurt een paar minuten en schrijft o.a. `output/europe-pois.json`.
2. **Kopieer** `europe-pois.json` naar dit project, naar:
   ```
   storage/app/ets2/europe-pois.json
   ```
   (Deze map staat in `.gitignore` — dit is geëxtraheerde spelinhoud en hoort niet in
   versiebeheer/redistributie.)
3. Ververs het dashboard — de "In de buurt"-tegel toont voortaan automatisch de
   dichtstbijzijnde tankstation-/rustplek-afstand.

**Beperking:** de match gebeurt op **hemelsbrede afstand** vanaf je huidige positie
(`truck.placement.x`/`z`), niet op de daadwerkelijk gekozen route — de telemetrie-API
geeft geen routepad, dus 100% routetrouw ("dit is het volgende tankstation op je route")
is hiermee niet te bepalen. Zie `zoekDichtstbijzijndePois()` in `TelemetryController` als
je dit later wilt verfijnen (bijv. met een richtingsfilter op basis van `heading`).

## Instelbare waarden

Alle drempels staan overzichtelijk bovenin
[app/Http/Controllers/Tools/TelemetryController.php](app/Http/Controllers/Tools/TelemetryController.php):

| Constante | Standaard | Betekenis |
|---|---|---|
| `SNELHEID_RIJDEND_DREMPEL` | 1,0 km/u | Boven deze snelheid geldt de truck als "rijdend" |
| `STILSTAND_RESET_SECONDEN` | 60 sec | Zo lang stilstaan telt als pauze genomen |
| `MAX_RIJTIJD_SECONDEN` | 4u30 | EU-regel (game-versie) tot verplichte pauze |
| `BRANDSTOF_ROOD_KM` / `BRANDSTOF_ORANJE_KM` | 100 / 250 km | Kleurdrempels voor resterend bereik |

## Opgeleverde bestanden

- Migratie: `database/migrations/2026_07_06_123656_create_telemetry_sessions_table.php`
- Model: `app/Models/TelemetrySession.php`
- Controller: `app/Http/Controllers/Tools/TelemetryController.php`
- Routes: `routes/tools.php` (`ets2-dashboard.index`, `telemetry.status`)
- View: `resources/views/ets2-dashboard/index.blade.php`
- Styling: `public/css/ets2-dashboard.css`
- `.gitignore`-regel voor `storage/app/ets2` (zelf te genereren kaartdata, niet in git)

## Let op: telemetry-JSON-structuur kan afwijken

De velden `truck.speed`, `truck.fuel`, `truck.fuelCapacity`, `truck.fuelAverageConsumption`
en `game.paused` worden defensief uitgelezen (`?? null`/`?? 0`). Wijkt de JSON-structuur
van jouw telemetry-server-versie af, pas dan alleen de veldnamen aan in
`TelemetryController::status()` — de rest van de logica blijft ongewijzigd werken.
