<x-layouts.portaal title="ETS2 Dashboard">

    <div class="max-w-275 mx-auto px-5 pb-10 flex flex-col gap-6">

        {{-- Verbindingsstatus --}}
        <div id="verbinding-balk" class="ets2-verbinding ets2-status-rood">
            <span class="ets2-verbinding-stip"></span>
            <span id="verbinding-tekst">Verbinden met telemetry-server…</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Brandstof-module --}}
            <div class="ets2-paneel" id="paneel-brandstof">
                <div class="ets2-paneel-titel">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="ets2-icoon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h12M6 21V6a2 2 0 012-2h4a2 2 0 012 2v15M6 11h6M17 8l2.5 2.5a1.5 1.5 0 010 2.121V17a1.5 1.5 0 003 0v-6l-3-3"/>
                    </svg>
                    Brandstof
                </div>

                <div id="brandstof-bereik" class="ets2-groot-getal">— km</div>
                <div class="ets2-subtekst">resterend bereik</div>

                <div class="ets2-balk-track">
                    <div id="brandstof-balk" class="ets2-balk-vulling" style="width:0%"></div>
                </div>

                <div class="ets2-detail-rij">
                    <span id="brandstof-percentage">—%</span>
                    <span id="brandstof-liters">— / — L</span>
                </div>
            </div>

            {{-- Rusttijd-module --}}
            <div class="ets2-paneel" id="paneel-rusttijd">
                <div class="ets2-paneel-titel">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="ets2-icoon">
                        <circle cx="12" cy="12" r="9"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3"/>
                    </svg>
                    Rusttijd
                </div>

                <div id="rusttijd-resterend" class="ets2-groot-getal">—</div>
                <div class="ets2-subtekst" id="rusttijd-label">tot verplichte pauze</div>

                <div class="ets2-balk-track">
                    <div id="rusttijd-balk" class="ets2-balk-vulling" style="width:0%"></div>
                </div>

                <div class="ets2-detail-rij">
                    <span id="rusttijd-status-tekst">—</span>
                    <span id="rusttijd-snelheid">0 km/u</span>
                </div>
            </div>

        </div>

        {{-- Navigatie: dichtstbijzijnde tankstation/rustplek (op basis van kaart-POI-bestand) --}}
        <div class="ets2-paneel ets2-paneel-nav" id="paneel-navigatie">
            <div class="ets2-paneel-titel">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="ets2-icoon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.5-4.2-7-7.8-7-11a7 7 0 1114 0c0 3.2-2.5 6.8-7 11z"/>
                    <circle cx="12" cy="10" r="2.5"/>
                </svg>
                In de buurt
            </div>

            <div id="nav-inhoud" class="ets2-nav-rij">
                <div>
                    <div class="ets2-nav-label">Dichtstbijzijnd tankstation</div>
                    <div id="nav-tankstation" class="ets2-nav-waarde">—</div>
                </div>
                <div>
                    <div class="ets2-nav-label">Dichtstbijzijnde rustplek</div>
                    <div id="nav-rustplek" class="ets2-nav-waarde">—</div>
                </div>
            </div>
            <div id="nav-onbeschikbaar" class="ets2-subtekst" style="display:none">
                Kaart-POI-data nog niet geladen — zie <code>ETS2-DASHBOARD.md</code> voor installatie.
            </div>
        </div>

        <p class="ets2-voetnoot">
            Vereist de <strong>ETS2 Telemetry Server</strong> (Funbit) draaiend op <code>localhost:25555</code>, met Euro Truck Simulator 2 actief.
            Ververst automatisch elke 1,5&nbsp;seconde.
        </p>
    </div>

    <script>
    (function () {
        const STATUS_URL = @json(route('telemetry.status'));
        const POLL_MS = 1500;

        const verbindingBalk   = document.getElementById('verbinding-balk');
        const verbindingTekst  = document.getElementById('verbinding-tekst');

        const brandstofBereik  = document.getElementById('brandstof-bereik');
        const brandstofBalk    = document.getElementById('brandstof-balk');
        const brandstofPercent = document.getElementById('brandstof-percentage');
        const brandstofLiters  = document.getElementById('brandstof-liters');
        const paneelBrandstof  = document.getElementById('paneel-brandstof');

        const rusttijdResterend = document.getElementById('rusttijd-resterend');
        const rusttijdBalk       = document.getElementById('rusttijd-balk');
        const rusttijdStatus     = document.getElementById('rusttijd-status-tekst');
        const rusttijdSnelheid   = document.getElementById('rusttijd-snelheid');
        const paneelRusttijd     = document.getElementById('paneel-rusttijd');

        const navInhoud       = document.getElementById('nav-inhoud');
        const navOnbeschikbaar = document.getElementById('nav-onbeschikbaar');
        const navTankstation   = document.getElementById('nav-tankstation');
        const navRustplek      = document.getElementById('nav-rustplek');

        let vorigeWaarschuwing15min = false;

        function formatDuur(seconden) {
            seconden = Math.max(0, Math.round(seconden));
            const u = Math.floor(seconden / 3600);
            const m = Math.floor((seconden % 3600) / 60);
            return u > 0 ? `${u}u ${String(m).padStart(2, '0')}m` : `${m}m`;
        }

        function zetKleur(paneelEl, status) {
            paneelEl.classList.remove('ets2-status-groen', 'ets2-status-oranje', 'ets2-status-rood', 'ets2-status-onbekend');
            paneelEl.classList.add('ets2-status-' + (status || 'onbekend'));
        }

        // Korte piep via de Web Audio API — geen extern geluidsbestand nodig.
        function speelWaarschuwing() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                [0, 0.3].forEach(vertraging => {
                    const oscillator = ctx.createOscillator();
                    const gain = ctx.createGain();
                    oscillator.type = 'sine';
                    oscillator.frequency.value = 880;
                    gain.gain.setValueAtTime(0.15, ctx.currentTime + vertraging);
                    oscillator.connect(gain).connect(ctx.destination);
                    oscillator.start(ctx.currentTime + vertraging);
                    oscillator.stop(ctx.currentTime + vertraging + 0.2);
                });
            } catch (e) { /* audio niet beschikbaar (bv. autoplay-restrictie) — geen probleem */ }
        }

        function toonGeenVerbinding() {
            verbindingBalk.className = 'ets2-verbinding ets2-status-rood';
            verbindingTekst.textContent = 'Geen verbinding met de ETS2 Telemetry Server — start het spel en controleer of de Funbit-plugin actief is.';
            zetKleur(paneelBrandstof, 'onbekend');
            zetKleur(paneelRusttijd, 'onbekend');
        }

        async function ververs() {
            let data;
            try {
                const respons = await fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } });
                if (!respons.ok) { toonGeenVerbinding(); return; }
                data = await respons.json();
            } catch (e) {
                toonGeenVerbinding();
                return;
            }

            if (!data.verbonden) { toonGeenVerbinding(); return; }

            verbindingBalk.className = 'ets2-verbinding ets2-status-groen';
            verbindingTekst.textContent = 'Verbonden met telemetry-server';

            // Brandstof
            const b = data.brandstof || {};
            brandstofBereik.textContent = b.bereik_km != null ? `${Math.round(b.bereik_km)} km` : '— km';
            brandstofBalk.style.width = (b.percentage != null ? b.percentage : 0) + '%';
            brandstofPercent.textContent = b.percentage != null ? `${b.percentage}%` : '—%';
            brandstofLiters.textContent = (b.liters != null && b.capaciteit != null)
                ? `${Math.round(b.liters)} / ${Math.round(b.capaciteit)} L`
                : '— / — L';
            zetKleur(paneelBrandstof, b.status);

            // Rusttijd
            const r = data.rusttijd || {};
            rusttijdResterend.textContent = r.resterend_seconden != null ? formatDuur(r.resterend_seconden) : '—';
            const percentageOver = r.max_rijtijd_seconden ? (r.resterend_seconden / r.max_rijtijd_seconden * 100) : 0;
            rusttijdBalk.style.width = Math.max(0, Math.min(100, percentageOver)) + '%';
            rusttijdStatus.textContent = r.rijdend ? 'Aan het rijden' : 'Stilstaand';
            rusttijdSnelheid.textContent = (data.snelheid_kmu != null ? Math.round(Math.abs(data.snelheid_kmu)) : 0) + ' km/u';
            zetKleur(paneelRusttijd, r.status);

            if (r.waarschuwing_15min && !vorigeWaarschuwing15min) {
                speelWaarschuwing();
            }
            vorigeWaarschuwing15min = !!r.waarschuwing_15min;

            // Navigatie: dichtstbijzijnd tankstation/rustplek (indien kaart-POI-bestand aanwezig)
            const n = data.navigatie || {};
            if (!n.beschikbaar) {
                navInhoud.style.display = 'none';
                navOnbeschikbaar.style.display = 'block';
            } else {
                navInhoud.style.display = 'flex';
                navOnbeschikbaar.style.display = 'none';

                const t = n.dichtstbijzijnd_tankstation;
                navTankstation.textContent = t ? `${t.afstand_km} km` : '—';
                navTankstation.classList.toggle('ets2-nav-waarde-rood', !!t && t.haalbaar === false);

                const p = n.dichtstbijzijnde_rustplek;
                navRustplek.textContent = p ? `${p.afstand_km} km` : '—';
            }
        }

        ververs();
        setInterval(ververs, POLL_MS);
    })();
    </script>
</x-layouts.portaal>
