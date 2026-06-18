<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Kleine Projecten' }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    @php
        $cssSegment = request()->segment(1) ?: 'home';
        if (in_array($cssSegment, ['rekeningen', 'transacties', 'categorieen'])) $cssSegment = 'budget';
        $cssBestand = public_path("css/{$cssSegment}.css");
    @endphp
    @if(file_exists($cssBestand))
        <link rel="stylesheet" href="{{ asset("css/{$cssSegment}.css") }}?v={{ filemtime($cssBestand) }}">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('tailwind.config.js') }}"></script>
    @vite([])
    {{ $head ?? '' }}
</head>
<body {{ request()->routeIs('home') ? 'style="padding-bottom: 96px"' : '' }}>

    <nav class="site-nav">
        <a href="{{ route('home') }}" class="nav-home">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
            </svg>
            Kleine Projecten
        </a>

        <div style="width:1px;height:20px;background:#2d3540;margin:0 6px;flex-shrink:0"></div>

        @php
            $navProjecten = [
                ['naam' => 'SQL Vergelijker', 'route' => 'sql-vergelijker.index', 'patroon' => 'sql-vergelijker*', 'auth' => false],
                ['naam' => 'SQL Data',         'route' => 'sql-data.index',        'patroon' => 'sql-data*',        'auth' => false],
                ['naam' => 'Budget',           'route' => 'budget.home',           'patroon' => 'budget*|rekeningen*|transacties*|categorieen*', 'auth' => true],
                ['naam' => 'GPX Viewer',       'route' => 'gpx-viewer.index',      'patroon' => 'gpx-viewer*',      'auth' => false],
                ['naam' => 'Scouting',         'route' => 'scouting.home',         'patroon' => 'scouting*',        'auth' => true],
            ];
        @endphp
        @foreach($navProjecten as $project)
            @if($project['auth'] && !auth()->check())
                @continue
            @endif
            @if(request()->is(explode('|', $project['patroon'])))
                <span class="nav-actief">{{ $project['naam'] }}</span>
            @else
                <a href="{{ route($project['route']) }}">{{ $project['naam'] }}</a>
            @endif
        @endforeach

        <div style="flex:1"></div>

        @auth
            <span style="font-size:.8rem;color:#5a6472;padding:0 4px">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('uitloggen') }}" style="display:inline">
                @csrf
                <button type="submit" style="background:none;border:none;cursor:pointer;color:#8a94a2;font-size:.8rem;padding:5px 10px;border-radius:7px;transition:color .15s,background .15s" onmouseover="this.style.color='#e6e9ee';this.style.background='rgba(255,255,255,.07)'" onmouseout="this.style.color='#8a94a2';this.style.background='none'">
                    Uitloggen
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" style="font-size:.8rem;padding:5px 12px;border-radius:7px;border:1px solid #2d3540;color:#8a94a2;text-decoration:none;transition:color .15s,border-color .15s" onmouseover="this.style.color='#e6e9ee';this.style.borderColor='#4cc38a'" onmouseout="this.style.color='#8a94a2';this.style.borderColor='#2d3540'">
                Inloggen
            </a>
        @endauth
    </nav>

    {{ $slot }}

    @if(request()->routeIs('home'))
    @php
        $treinDir = public_path('trein/');
        $treinBestanden = is_dir($treinDir)
            ? array_values(array_filter(scandir($treinDir), fn($f) => (bool) preg_match('/\.(png|jpg|gif|svg|webp)$/i', $f)))
            : [];
        $treinJson = json_encode($treinBestanden);
        $treinPad  = asset('trein') . '/';
    @endphp


    <div id="trein-baan">
        <div id="trein-bovenleiding"></div>
        <div id="trein-perron">
            <div id="trein-perron-dak"></div>
            <div class="studs top"></div>
            <div class="studs bottom"></div>
        </div>
        <div id="perron-sign"><div class="panel"></div></div>
        <div class="perron-post left"></div>
        <div class="perron-post right"></div>
        <div id="trein-berm"></div>
        <div class="landschap-object windmolen hide-sm" style="left:2%"><div class="molenhuis"></div><div class="wieken"><span class="wiek"></span><span class="wiek"></span><span class="wiek"></span><span class="wiek"></span></div></div>
        <div class="landschap-object loofboom loofboom-s" style="left:6%"></div>
        <div class="landschap-object rijtjeshuis rijtjeshuis-l hide-xs" style="left:9%"><div class="dak"></div><div class="muur"></div></div>
        <div class="landschap-object dennenboom den-l" style="left:15%"><span class="stam"></span></div>
        <div class="landschap-object boerderij hide-sm" style="left:19%"><div class="dak"></div><div class="muur"></div></div>
        <div class="landschap-object loofboom loofboom-l" style="left:27%"></div>
        <div class="landschap-object rijtjeshuis hide-xs" style="left:31%"><div class="dak"></div><div class="muur"></div></div>
        <div class="landschap-object dennenboom den-s" style="left:38%"><span class="stam"></span></div>
        <div class="landschap-object lantaarnpaal" style="left:43%"></div>
        <div class="landschap-object bushokje"></div>
        <div class="landschap-object lantaarnpaal" style="left:calc(50% - 82px)"></div>
        <div class="landschap-object stationsgebouw"><div class="dak"></div><div class="muur"></div></div>
        <div class="landschap-object lantaarnpaal" style="left:calc(50% + 96px)"></div>
        <div class="landschap-object dennenboom den-s hide-xs" style="left:62%"><span class="stam"></span></div>
        <div class="landschap-object loofboom loofboom-s" style="left:67%"></div>
        <div class="landschap-object rijtjeshuis rijtjeshuis-l hide-sm" style="left:71%"><div class="dak"></div><div class="muur"></div></div>
        <div class="landschap-object loofboom" style="left:78%"></div>
        <div class="landschap-object boerderij hide-xs" style="left:82%"><div class="dak"></div><div class="muur"></div></div>
        <div class="landschap-object dennenboom den-l" style="left:91%"><span class="stam"></span></div>
        <div class="landschap-object loofboom loofboom-l hide-xs" style="left:96%"></div>
        <div id="trein-rails"></div>
    </div>

    <script>
    (function() {
        const treinen = {!! $treinJson !!};
        const baan = document.getElementById('trein-baan');

        function animeer(el, van, naar, duur, gereed, ease) {
            const start = performance.now();
            (function stap(nu) {
                const t = Math.min((nu - start) / duur, 1);
                const e = ease ? ease(t) : t;
                el.style.left = (van + (naar - van) * e) + 'px';
                t < 1 ? requestAnimationFrame(stap) : gereed();
            })(start);
        }

        function rijdTrein() {
            if (!treinen.length) return;
            const naam = treinen[Math.floor(Math.random() * treinen.length)];
            const naarRecht = Math.random() < 0.5;
            const stoptAanPerron = Math.random() < 0.35;
            const img = new Image();

            img.onload = function() {
                const schaal = Math.min(1, 70 / img.naturalHeight);
                const w = Math.round(img.naturalWidth * schaal);
                const h = Math.round(img.naturalHeight * schaal);
                Object.assign(img.style, { position:'absolute', bottom:'4px', height:h+'px', width:w+'px', transform:'scaleX('+(naarRecht?1:-1)+')', zIndex:60 });

                const vanX = naarRecht ? -w : window.innerWidth;
                const naarX = naarRecht ? window.innerWidth : -w;
                const rect = document.getElementById('trein-perron').getBoundingClientRect();
                let stopX = Math.round(rect.left + rect.width / 2 - w / 2);
                stopX = Math.min(Math.max(stopX, Math.min(vanX, naarX)), Math.max(vanX, naarX));
                const opRoute = naarRecht ? (stopX > vanX) : (stopX < vanX);
                const duur = 7000 + Math.random() * 5000;

                img.style.left = vanX + 'px';
                baan.appendChild(img);

                if (stoptAanPerron && opRoute) {
                    const deel = Math.abs(stopX - vanX) / Math.abs(naarX - vanX);
                    animeer(img, vanX, stopX, duur * deel, () => {
                        setTimeout(() => {
                            animeer(img, stopX, naarX, duur * (1 - deel), () => { img.remove(); setTimeout(rijdTrein, 2000 + Math.random() * 5000); }, t => t * t * t);
                        }, 2000 + Math.random() * 2000);
                    }, t => 1 - Math.pow(1 - t, 3));
                } else {
                    animeer(img, vanX, naarX, duur, () => { img.remove(); setTimeout(rijdTrein, 2000 + Math.random() * 5000); });
                }
            };
            img.src = '{{ $treinPad }}' + naam;
        }

        setTimeout(rijdTrein, 1000 + Math.random() * 3000);
    })();
    </script>
    @endif

    @livewireScripts
</body>
</html>
