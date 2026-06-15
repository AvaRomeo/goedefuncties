<x-layouts.portaal title="GPX Viewer">
    <x-slot:head>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/leaflet-gpx@1.7.0/gpx.min.js"></script>
        <style>
            #kaart { height: calc(100vh - 120px); min-height: 400px; }
            #dropzone {
                border: 2px dashed var(--rand, #3a414d);
                border-radius: 12px;
                padding: 2rem;
                text-align: center;
                cursor: pointer;
                transition: border-color .2s, background .2s;
            }
            #dropzone.over { border-color: #4cc38a; background: rgba(76,195,138,.06); }
        </style>
    </x-slot:head>

    <div class="max-w-[1100px] mx-auto px-5 py-8 flex flex-col gap-6">

        <header>
            <h1 class="text-[1.8rem] text-tekst font-bold">GPX Viewer</h1>
            <p class="text-gedempt mt-1">Laad een GPX-bestand om de route op de kaart te bekijken.</p>
        </header>

        <div id="dropzone" onclick="document.getElementById('gpx-input').click()">
            <p class="text-tekst font-semibold mb-1">Klik of sleep een GPX-bestand hierheen</p>
            <p class="text-gedempt text-sm">Alleen .gpx bestanden</p>
            <input id="gpx-input" type="file" accept=".gpx" class="hidden">
        </div>

        <div id="info" class="hidden grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-paneel border border-rand rounded-xl p-4 text-center">
                <div class="text-gedempt text-xs mb-1">Afstand</div>
                <div id="stat-afstand" class="text-tekst font-bold text-lg">—</div>
            </div>
            <div class="bg-paneel border border-rand rounded-xl p-4 text-center">
                <div class="text-gedempt text-xs mb-1">Duur</div>
                <div id="stat-duur" class="text-tekst font-bold text-lg">—</div>
            </div>
            <div class="bg-paneel border border-rand rounded-xl p-4 text-center">
                <div class="text-gedempt text-xs mb-1">Stijging</div>
                <div id="stat-stijging" class="text-tekst font-bold text-lg">—</div>
            </div>
            <div class="bg-paneel border border-rand rounded-xl p-4 text-center">
                <div class="text-gedempt text-xs mb-1">Daling</div>
                <div id="stat-daling" class="text-tekst font-bold text-lg">—</div>
            </div>
        </div>

        <div id="kaart" class="rounded-xl overflow-hidden border border-rand hidden"></div>

    </div>

    <script>
    const input    = document.getElementById('gpx-input');
    const dropzone = document.getElementById('dropzone');
    const kaartDiv = document.getElementById('kaart');
    const infoDiv  = document.getElementById('info');
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
        const reader = new FileReader();
        reader.onload = e => tekenRoute(e.target.result, file.name);
        reader.readAsText(file);
    }

    function tekenRoute(inhoud, naam) {
        kaartDiv.classList.remove('hidden');
        infoDiv.classList.remove('hidden');
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
            polyline_options: { color: '#4cc38a', weight: 3 },
        }).on('loaded', ev => {
            const gpx = ev.target;
            map.fitBounds(gpx.getBounds());
            URL.revokeObjectURL(url);
            const afstand = gpx.get_distance();
            const duur    = gpx.get_total_time();
            const stijg   = gpx.get_elevation_gain();
            const daling  = gpx.get_elevation_loss();
            document.getElementById('stat-afstand').textContent  = afstand ? (afstand / 1000).toFixed(2) + ' km' : '—';
            document.getElementById('stat-duur').textContent     = duur     ? formatDuur(duur) : '—';
            document.getElementById('stat-stijging').textContent = stijg  != null ? '+' + Math.round(stijg)  + ' m' : '—';
            document.getElementById('stat-daling').textContent   = daling != null ? '-' + Math.round(daling) + ' m' : '—';
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
