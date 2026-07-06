<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\TelemetrySession;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelemetryController extends Controller
{
    // ── Instelbare drempels ──────────────────────────────────────────────
    // (Dit zijn spelregels/voorkeuren, geen vaste wetten — pas hier aan.)

    /** Adres van de lokale ETS2 Telemetry Server (Funbit-plugin). */
    private const TELEMETRY_URL = 'http://localhost:25555/api/ets2/telemetry';

    /** Boven deze snelheid (km/u) telt de truck als "aan het rijden". */
    private const SNELHEID_RIJDEND_DREMPEL = 1.0;

    /** Zoveel seconden stilstand telt als pauze genomen -> rijtijd-timer reset. */
    private const STILSTAND_RESET_SECONDEN = 60;

    /** EU-regel (game-versie): 4u30 rijden -> verplichte pauze. */
    private const MAX_RIJTIJD_SECONDEN = 4 * 3600 + 30 * 60;

    /** Verplichte pauzeduur (informatief, alleen getoond in de UI). */
    private const VERPLICHTE_PAUZE_MINUTEN = 45;

    /** Onder dit resterend bereik (km) wordt brandstof "rood" (actie nodig). */
    private const BRANDSTOF_ROOD_KM = 100;

    /** Onder dit resterend bereik (km) wordt brandstof "oranje" (let op). */
    private const BRANDSTOF_ORANJE_KM = 250;

    /**
     * Kaart-POI's (tankstations/rustplaatsen), zelf te genereren met de
     * `truckermudgeon/maps`-parser — zie ETS2-DASHBOARD.md. Optioneel: als
     * dit bestand ontbreekt, laat het dashboard die gegevens gewoon weg.
     */
    private const POIS_BESTAND = 'ets2/europe-pois.json';

    /** POI-iconen uit de truckermudgeon/maps-parser die we willen tonen. */
    private const ICOON_TANKSTATION = 'gas_ico';
    private const ICOON_RUSTPLEK    = 'parking_ico';

    /**
     * Toont het dashboard (Blade-view met polling-JS).
     */
    public function index()
    {
        return view('ets2-dashboard.index');
    }

    /**
     * Proxyt de lokale telemetry-server en geeft een samengevatte status terug.
     * De frontend pollt deze route (niet rechtstreeks de telemetry-server) zodat:
     *  - er geen CORS-problemen ontstaan;
     *  - de rijtijd/pauze-logica server-side (database) bijgehouden wordt, dus
     *    een refresh van de browser de timer niet reset.
     */
    public function status()
    {
        try {
            $response = Http::timeout(2)->get(self::TELEMETRY_URL);
        } catch (\Throwable $e) {
            Log::debug('ETS2 telemetry: geen verbinding met telemetry-server', ['fout' => $e->getMessage()]);
            return response()->json(['verbonden' => false]);
        }

        if (! $response->successful()) {
            return response()->json(['verbonden' => false]);
        }

        $data = $response->json() ?? [];

        // Structuur kan iets afwijken per telemetry-server-versie, dus defensief
        // parsen met null-coalescing i.p.v. te vertrouwen op de exacte vorm.
        $truck = $data['truck'] ?? [];
        $game  = $data['game'] ?? [];

        $snelheid       = (float) ($truck['speed'] ?? 0);                       // km/u (kan negatief zijn tijdens achteruit rijden)
        $brandstof      = $truck['fuel'] ?? null;                               // liters
        $tankinhoud     = $truck['fuelCapacity'] ?? null;                       // liters
        $verbruikPerKm  = $truck['fuelAverageConsumption'] ?? null;             // liter/km
        $gamePauze      = (bool) ($game['paused'] ?? false);                    // spel gepauzeerd (menu/pauzescherm)
        $placement      = $truck['placement'] ?? [];                           // { x, y, z, heading, pitch, roll }

        $rusttijd       = $this->werkRijtijdBij(abs($snelheid), $gamePauze);
        $brandstofData  = $this->berekenBrandstof($brandstof, $tankinhoud, $verbruikPerKm);

        return response()->json([
            'verbonden'   => true,
            'snelheid_kmu' => round($snelheid, 1),
            'brandstof'   => $brandstofData,
            'rusttijd'    => $rusttijd,
            'navigatie'   => $this->zoekDichtstbijzijndePois($placement, $brandstofData['bereik_km']),
        ]);
    }

    /**
     * Werkt de server-side rijtijd/pauze-status bij op basis van de huidige
     * snelheid en geeft de samengevatte rusttijd-gegevens terug.
     */
    private function werkRijtijdBij(float $snelheidAbs, bool $gamePauze): array
    {
        $sessie = TelemetrySession::huidige();
        $nu     = now();
        $isRijdend = ! $gamePauze && $snelheidAbs > self::SNELHEID_RIJDEND_DREMPEL;

        if ($isRijdend) {
            if ($sessie->status !== 'rijdend') {
                $sessie->status       = 'rijdend';
                $sessie->status_sinds = $nu;
            }
        } else {
            if ($sessie->status === 'rijdend') {
                // Truck stopt net: tel de afgelegde rijtijd op bij de teller.
                $sessie->rijtijd_seconden += $sessie->status_sinds?->diffInSeconds($nu) ?? 0;
                $sessie->status       = 'stilstaand';
                $sessie->status_sinds = $nu;
            } elseif ($sessie->status_sinds && $sessie->status_sinds->diffInSeconds($nu) >= self::STILSTAND_RESET_SECONDEN) {
                // Al lang genoeg stil: dit telt als de verplichte pauze -> reset.
                $sessie->rijtijd_seconden = 0;
            }
        }

        $sessie->save();

        $rijtijdActueel = $sessie->rijtijd_seconden;
        if ($sessie->status === 'rijdend' && $sessie->status_sinds) {
            $rijtijdActueel += $sessie->status_sinds->diffInSeconds($nu);
        }

        $resterend = max(0, self::MAX_RIJTIJD_SECONDEN - $rijtijdActueel);

        return [
            'rijdend'              => $isRijdend,
            'rijtijd_seconden'     => $rijtijdActueel,
            'resterend_seconden'   => $resterend,
            'max_rijtijd_seconden' => self::MAX_RIJTIJD_SECONDEN,
            'pauze_minuten'        => self::VERPLICHTE_PAUZE_MINUTEN,
            'waarschuwing_15min'   => $resterend <= 15 * 60,
            'status'               => $resterend <= 15 * 60 ? 'rood' : ($resterend <= 3600 ? 'oranje' : 'groen'),
        ];
    }

    /**
     * Berekent brandstofpercentage en resterend bereik uit de ruwe telemetry-velden.
     */
    private function berekenBrandstof(?float $liters, ?float $capaciteit, ?float $verbruikPerKm): array
    {
        $percentage = ($liters !== null && $capaciteit) ? round($liters / $capaciteit * 100, 1) : null;
        $bereikKm   = ($liters !== null && $verbruikPerKm) ? round($liters / $verbruikPerKm, 1) : null;

        $status = 'onbekend';
        if ($bereikKm !== null) {
            $status = $bereikKm < self::BRANDSTOF_ROOD_KM
                ? 'rood'
                : ($bereikKm < self::BRANDSTOF_ORANJE_KM ? 'oranje' : 'groen');
        }

        return [
            'liters'         => $liters !== null ? round($liters, 1) : null,
            'capaciteit'     => $capaciteit,
            'percentage'     => $percentage,
            'verbruik_per_km' => $verbruikPerKm,
            'bereik_km'      => $bereikKm,
            'drempel_km'     => self::BRANDSTOF_ROOD_KM,
            'status'         => $status,
        ];
    }

    /**
     * Zoekt het dichtstbijzijnde tankstation en de dichtstbijzijnde rustplek
     * t.o.v. de huidige truckpositie, op basis van het (optionele) kaart-
     * POI-bestand. Geeft `beschikbaar => false` terug als dat bestand er
     * niet is, zodat het dashboard dan gewoon niets toont i.p.v. te crashen.
     */
    private function zoekDichtstbijzijndePois(array $placement, ?float $bereikKm): array
    {
        $x = $placement['x'] ?? null;
        $z = $placement['z'] ?? null; // horizontale vlak = truck.placement.x/z (y is hoogte)

        $pois = $this->laadPois();

        if ($pois === null || $x === null || $z === null) {
            return ['beschikbaar' => false];
        }

        $tankstation = $this->dichtstbijzijnde($pois['tankstations'], (float) $x, (float) $z);
        $rustplek    = $this->dichtstbijzijnde($pois['rustplekken'], (float) $x, (float) $z);

        if ($tankstation) {
            // Kleine veiligheidsmarge: 10% van het resterende bereik achter de hand houden.
            $tankstation['haalbaar'] = $bereikKm !== null ? $tankstation['afstand_km'] <= $bereikKm * 0.9 : null;
        }

        return [
            'beschikbaar'              => true,
            'dichtstbijzijnd_tankstation' => $tankstation,
            'dichtstbijzijnde_rustplek'   => $rustplek,
        ];
    }

    /**
     * Laadt en cachet de (mogelijk grote) POI-lijst, gefilterd op tankstations
     * en rustplekken. Het ruwe bestand komt uit de `truckermudgeon/maps`-
     * parser (zie ETS2-DASHBOARD.md) en bevat objecten met o.a.
     * `type` ("facility" voor tankstations/rustplekken), `icon` en `x`/`y`.
     */
    private function laadPois(): ?array
    {
        $pad = storage_path('app/' . self::POIS_BESTAND);

        if (! file_exists($pad)) {
            return null;
        }

        return Cache::remember('ets2_pois_' . filemtime($pad), now()->addDay(), function () use ($pad) {
            $ruw = json_decode(file_get_contents($pad), true) ?? [];

            $tankstations = [];
            $rustplekken  = [];

            foreach ($ruw as $poi) {
                if (($poi['type'] ?? null) !== 'facility') {
                    continue;
                }
                $punt = ['x' => (float) ($poi['x'] ?? 0), 'y' => (float) ($poi['y'] ?? 0)];
                match ($poi['icon'] ?? null) {
                    self::ICOON_TANKSTATION => $tankstations[] = $punt,
                    self::ICOON_RUSTPLEK    => $rustplekken[]  = $punt,
                    default                 => null,
                };
            }

            return ['tankstations' => $tankstations, 'rustplekken' => $rustplekken];
        });
    }

    /**
     * Zoekt het dichtstbijzijnde punt (hemelsbrede afstand) uit een lijst
     * POI-coördinaten. Geeft null terug als de lijst leeg is.
     */
    private function dichtstbijzijnde(array $punten, float $x, float $z): ?array
    {
        $kortsteAfstand = null;

        foreach ($punten as $punt) {
            // Beide coördinaten zijn in meters t.o.v. hetzelfde kaart-origin.
            $afstandMeter = sqrt(($punt['x'] - $x) ** 2 + ($punt['y'] - $z) ** 2);
            if ($kortsteAfstand === null || $afstandMeter < $kortsteAfstand) {
                $kortsteAfstand = $afstandMeter;
            }
        }

        return $kortsteAfstand === null ? null : ['afstand_km' => round($kortsteAfstand / 1000, 1)];
    }
}
