<x-layouts.portaal title="GPX Viewer">
    <x-slot:head>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/leaflet-gpx@1.7.0/gpx.min.js"></script>
    </x-slot:head>

    {{-- Banner --}}
    <div class="gpx-banner">
        <div class="gpx-banner-top">
            <div class="gpx-banner-icoon">
                <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="16" cy="16" r="13"/>
                    <path stroke-linecap="round" d="M16 5v2M16 25v2M5 16h2M25 16h2"/>
                    <polygon points="16,8 18.5,16 16,14.5 13.5,16" fill="#60a5fa" stroke="none"/>
                    <polygon points="16,24 18.5,16 16,17.5 13.5,16" fill="#5a7fa8" stroke="none"/>
                    <circle cx="16" cy="16" r="1.5" fill="#e8f0fc" stroke="none"/>
                </svg>
            </div>
            <div class="gpx-banner-tekst">
                <div class="gpx-banner-titel">GPX Viewer</div>
                <div class="gpx-banner-sub">Laad een GPX-bestand om de route op de kaart te tonen.</div>
            </div>
        </div>

        {{-- Berglandschap ------------------------------------------------ --}}
        <div class="gpx-bergen" aria-hidden="true">
            @foreach([
                ['w'=>110,'h'=>52,'p'=>'42%','c'=>'#091526'],
                ['w'=>80, 'h'=>35,'p'=>'55%','c'=>'#0b1c38'],
                ['w'=>140,'h'=>68,'p'=>'38%','c'=>'#07111e'],
                ['w'=>90, 'h'=>44,'p'=>'50%','c'=>'#0d2040'],
                ['w'=>120,'h'=>60,'p'=>'44%','c'=>'#091526'],
                ['w'=>75, 'h'=>32,'p'=>'58%','c'=>'#0b1c38'],
                ['w'=>130,'h'=>72,'p'=>'40%','c'=>'#060f1a'],
                ['w'=>95, 'h'=>48,'p'=>'47%','c'=>'#0d2040'],
                ['w'=>115,'h'=>56,'p'=>'43%','c'=>'#091526'],
                ['w'=>85, 'h'=>38,'p'=>'52%','c'=>'#0b1c38'],
                ['w'=>100,'h'=>62,'p'=>'36%','c'=>'#07111e'],
                ['w'=>70, 'h'=>30,'p'=>'60%','c'=>'#0d2040'],
            ] as $b)
                <div class="gpx-berg" style="width:{{$b['w']}}px;height:{{$b['h']}}px;background:{{$b['c']}};--piek:{{$b['p']}};margin-right:-18px"></div>
            @endforeach
            <div class="gpx-route-lijn"></div>
        </div>
    </div>

    <div class="max-w-275 mx-auto px-5 pb-10 flex flex-col gap-6 relative" style="z-index:1">

        {{-- Dropzone --}}
        <label id="dropzone"
               class="p-10 flex flex-col items-center gap-2 text-center cursor-pointer transition-colors select-none relative">
            <input id="gpx-input" type="file" accept=".gpx" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
            <svg class="w-10 h-10 mb-1" style="color:#3b82f6;opacity:.7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
            </svg>
            <span class="font-semibold text-tekst text-base">Klik of sleep een GPX-bestand hierheen</span>
            <span class="text-gedempt text-sm">.gpx bestanden — routes, wandelingen, fietstochten</span>
            <span id="bestandsnaam" class="text-sm font-mono hidden" style="color:#60a5fa"></span>
        </label>

        {{-- Statistieken --}}
        <div id="info" class="grid-cols-2 sm:grid-cols-4 gap-4" style="display:none">
            <div class="bg-paneel border border-rand rounded-xl p-4 text-center">
                <div class="text-gedempt text-xs uppercase tracking-wide mb-1.5">Afstand</div>
                <div id="stat-afstand" class="font-bold text-xl tabular-nums" style="color:#60a5fa">—</div>
            </div>
            <div class="bg-paneel border border-rand rounded-xl p-4 text-center">
                <div class="text-gedempt text-xs uppercase tracking-wide mb-1.5">Duur</div>
                <div id="stat-duur" class="font-bold text-xl tabular-nums text-tekst">—</div>
            </div>
            <div class="bg-paneel border border-rand rounded-xl p-4 text-center">
                <div class="text-gedempt text-xs uppercase tracking-wide mb-1.5">Stijging</div>
                <div id="stat-stijging" class="font-bold text-xl tabular-nums text-accent">—</div>
            </div>
            <div class="bg-paneel border border-rand rounded-xl p-4 text-center">
                <div class="text-gedempt text-xs uppercase tracking-wide mb-1.5">Daling</div>
                <div id="stat-daling" class="font-bold text-xl tabular-nums text-waarschuwing">—</div>
            </div>
        </div>

        {{-- Kaart --}}
        <div id="kaart" class="rounded-xl overflow-hidden border border-rand hidden"></div>

    </div>

    <script>
    const input     = document.getElementById('gpx-input');
    const dropzone  = document.getElementById('dropzone');
    const kaartDiv  = document.getElementById('kaart');
    const infoDiv   = document.getElementById('info');
    const bestandEl = document.getElementById('bestandsnaam');
    let map = null;
    let huidigeLaag = null;

    dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('over'); });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('over'));
    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('over');
        const file = e.dataTransfer.files[0];
        if (file) laadGpx(file);
    });
    input.addEventListener('change', () => { if (input.files[0]) laadGpx(input.files[0]); });

    function laadGpx(file) {
        if (!file.name.endsWith('.gpx')) { alert('Alleen .gpx bestanden zijn toegestaan.'); return; }
        bestandEl.textContent = file.name;
        bestandEl.classList.remove('hidden');
        const reader = new FileReader();
        reader.onload = e => tekenRoute(e.target.result, file.name);
        reader.readAsText(file);
    }

    function tekenRoute(inhoud, naam) {
        kaartDiv.classList.remove('hidden');
        infoDiv.style.display = 'grid';
        if (!map) {
            map = L.map('kaart');
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19,
            }).addTo(map);
        }
        if (huidigeLaag) map.removeLayer(huidigeLaag);
        const blob = new Blob([inhoud], { type: 'application/gpx+xml' });
        const url  = URL.createObjectURL(blob);
        huidigeLaag = new L.GPX(url, {
            async: true,
            marker_options: {
                startIconUrl: 'https://cdn.jsdelivr.net/npm/leaflet-gpx@1.7.0/pin-icon-start.png',
                endIconUrl:   'https://cdn.jsdelivr.net/npm/leaflet-gpx@1.7.0/pin-icon-end.png',
                shadowUrl:    'https://cdn.jsdelivr.net/npm/leaflet-gpx@1.7.0/pin-shadow.png',
            },
            polyline_options: { color: '#3b82f6', weight: 3.5 },
        }).on('loaded', ev => {
            const gpx = ev.target;
            map.fitBounds(gpx.getBounds());
            URL.revokeObjectURL(url);
            document.getElementById('stat-afstand').textContent  = gpx.get_distance()       ? (gpx.get_distance() / 1000).toFixed(2) + ' km' : '—';
            document.getElementById('stat-duur').textContent     = gpx.get_total_time()     ? formatDuur(gpx.get_total_time()) : '—';
            document.getElementById('stat-stijging').textContent = gpx.get_elevation_gain() != null ? '+' + Math.round(gpx.get_elevation_gain()) + ' m' : '—';
            document.getElementById('stat-daling').textContent   = gpx.get_elevation_loss() != null ? '-' + Math.round(gpx.get_elevation_loss()) + ' m' : '—';
        }).addTo(map);
    }

    function formatDuur(ms) {
        const s = Math.floor(ms / 1000);
        const u = Math.floor(s / 3600);
        const m = Math.floor((s % 3600) / 60);
        return u > 0 ? `${u}u ${m}m` : `${m}m`;
    }
    </script>
</x-layouts.portaal>
