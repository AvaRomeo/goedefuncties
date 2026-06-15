<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Kleine Projecten' }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('tailwind.config.js') }}"></script>
    <style>
        .site-nav {
            background: #262c36;
            border-bottom: 1px solid #3a414d;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 0.88rem;
            flex-wrap: wrap;
        }
        .site-nav a { color: #4cc38a; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 5px; }
        .site-nav a:hover { text-decoration: underline; }
        .site-nav .sep { color: #3a414d; padding: 0 4px; }
        .site-nav .nav-actief { color: #9aa4b2; padding: 0 2px; }
    </style>
    {{ $head ?? '' }}
</head>
<body {{ request()->routeIs('home') ? 'style="padding-bottom: 96px"' : '' }}>

    <nav class="site-nav">
        <a href="{{ route('home') }}">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
            </svg>
            Home
        </a>
        @php
            $navProjecten = [
                ['naam' => 'SQL Vergelijker', 'route' => 'sql-vergelijker.index', 'patroon' => 'sql-vergelijker*'],
                ['naam' => 'SQL Data',         'route' => 'sql-data.index',        'patroon' => 'sql-data*'],
                ['naam' => 'Budget',           'route' => 'budget.home',           'patroon' => 'budget*|rekeningen*|transacties*|categorieen*'],
                ['naam' => 'GPX Viewer',       'route' => 'gpx-viewer.index',      'patroon' => 'gpx-viewer*'],
            ];
        @endphp
        @foreach($navProjecten as $project)
            <span class="sep">·</span>
            @if(request()->is(explode('|', $project['patroon'])))
                <span class="nav-actief">{{ $project['naam'] }}</span>
            @else
                <a href="{{ route($project['route']) }}">{{ $project['naam'] }}</a>
            @endif
        @endforeach
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

    <style>
        #trein-baan { position:fixed; bottom:0; left:0; width:100%; height:96px; pointer-events:none; overflow:hidden; z-index:50; }
        #trein-rails { position:absolute; bottom:0; left:0; width:100%; height:14px; background:repeating-linear-gradient(90deg,#5c4030 0px,#5c4030 10px,transparent 10px,transparent 36px); }
        #trein-rails::before,#trein-rails::after { content:''; position:absolute; left:0; width:100%; height:3px; background:#7a7a7a; }
        #trein-rails::before { top:1px; }
        #trein-rails::after  { top:8px; }
        #trein-bovenleiding { position:absolute; inset:0; bottom:14px; background:repeating-linear-gradient(90deg,transparent 0,transparent 149px,#5a5a5a 149px,#5a5a5a 151px); }
        #trein-bovenleiding::before { content:''; position:absolute; top:20px; left:0; right:0; height:1px; background:#bbb; }
        #trein-perron { position:absolute; bottom:14px; left:50%; width:240px; height:22px; transform:translateX(-50%); background-color:#c4bdb4; background-image:linear-gradient(rgba(86,78,72,.28) 0 1px,transparent 1px),linear-gradient(90deg,rgba(86,78,72,.28) 0 1px,transparent 1px),linear-gradient(180deg,#cec8be 0%,#b8b2a8 100%); background-size:18px 18px,18px 18px,100% 100%; border-top:2px solid #6e6560; z-index:1; box-shadow:0 2px 4px rgba(0,0,0,.22); overflow:visible; }
        #trein-perron::before { content:''; position:absolute; bottom:3px; left:0; right:0; height:4px; background:#FFCD00; }
        #trein-perron .studs { position:absolute; left:0; right:0; height:4px; pointer-events:none; opacity:.7; }
        #trein-perron .studs.top { display:none; }
        #trein-perron .studs.bottom { bottom:8px; background:repeating-linear-gradient(90deg,#9a9390 0 3px,transparent 3px 18px); }
        #trein-perron::after { content:''; position:absolute; bottom:0; left:0; right:0; height:3px; background:#5a5450; box-shadow:0 2px 3px rgba(0,0,0,.25); }
        #trein-perron-dak { position:absolute; bottom:100%; left:15px; right:15px; height:12px; background:#2d3340; border-radius:1px 1px 0 0; }
        #trein-perron-dak::before { content:''; position:absolute; inset:0; background:repeating-linear-gradient(90deg,transparent 0,transparent 37px,#3d4450 37px,#3d4450 40px); }
        #trein-perron-dak::after { content:''; position:absolute; top:0; left:0; right:0; height:2px; background:#4a5568; }
        #trein-baan img { z-index:60; will-change:left; }
        #perron-sign { position:absolute; bottom:64px; left:50%; transform:translateX(-50%); width:60px; height:12px; z-index:70; pointer-events:none; }
        #perron-sign .panel { position:absolute; left:0; top:0; right:0; height:12px; background:linear-gradient(180deg,#0b4b86 0%,#072d56 100%); border-radius:2px; box-shadow:0 1px 2px rgba(0,0,0,.28); border:1px solid rgba(0,0,0,.18); }
        .perron-post { position:absolute; bottom:36px; width:4px; height:28px; background:#3a3a3a; z-index:69; border-radius:1px 1px 0 0; }
        .perron-post.left { left:calc(50% - 30px); }
        .perron-post.right { left:calc(50% + 26px); }
        #trein-berm { position:absolute; bottom:13px; left:0; right:0; height:15px; background:repeating-linear-gradient(100deg,transparent 0 12px,rgba(255,255,255,.12) 12px 13px,transparent 13px 26px),repeating-linear-gradient(78deg,transparent 0 18px,rgba(33,97,31,.34) 18px 20px,transparent 20px 36px),linear-gradient(180deg,#7fbd58 0%,#5aa143 38%,#3f7c31 100%); z-index:0; box-shadow:inset 0 4px 0 rgba(255,255,255,.12),inset 0 -3px 0 rgba(34,84,32,.35); }
        #trein-berm::before { content:''; position:absolute; left:0; right:0; top:-4px; height:8px; background:repeating-linear-gradient(90deg,transparent 0 14px,#2f7430 14px 16px,transparent 16px 29px); opacity:.7; }
        #trein-berm::after { content:''; position:absolute; bottom:2px; left:0; right:0; height:7px; background:radial-gradient(circle at 8% 45%,#ffd657 0 1px,transparent 2px),radial-gradient(circle at 18% 65%,#f5f0a8 0 1px,transparent 2px),radial-gradient(circle at 29% 42%,#e85d73 0 1px,transparent 2px),radial-gradient(circle at 43% 58%,#fff1a2 0 1px,transparent 2px),radial-gradient(circle at 57% 43%,#f2c84b 0 1px,transparent 2px),radial-gradient(circle at 72% 64%,#e85d73 0 1px,transparent 2px),radial-gradient(circle at 88% 48%,#fff1a2 0 1px,transparent 2px); background-size:190px 7px; opacity:.82; }
        .landschap-object { position:absolute; z-index:2; pointer-events:none; }
        .loofboom { bottom:23px; width:32px; height:44px; }
        .loofboom::before { content:''; position:absolute; bottom:9px; left:50%; transform:translateX(-50%); width:30px; height:30px; border-radius:50%; background:radial-gradient(circle at 30% 32%,#78bd4b 0 27%,transparent 28%),radial-gradient(circle at 62% 28%,#5fa43d 0 30%,transparent 31%),radial-gradient(circle at 48% 58%,#3e822f 0 42%,transparent 43%),radial-gradient(circle at 50% 50%,#4f9837 0 70%,#2f6925 100%); box-shadow:-8px 5px 0 -3px #6aae42,8px 6px 0 -4px #34782c,0 -6px 0 -5px #86c85a; }
        .loofboom::after { content:''; position:absolute; bottom:0; left:50%; transform:translateX(-50%); width:6px; height:14px; background:linear-gradient(90deg,#6d421f 0%,#8a592c 52%,#5e371c 100%); border-radius:2px 2px 0 0; }
        .loofboom-s { width:24px; height:34px; }
        .loofboom-s::before { width:23px; height:23px; bottom:8px; box-shadow:-6px 4px 0 -3px #76b64a,6px 4px 0 -3px #34782c; }
        .loofboom-s::after { height:10px; width:5px; }
        .loofboom-l { width:40px; height:52px; }
        .loofboom-l::before { width:38px; height:37px; bottom:11px; box-shadow:-10px 6px 0 -4px #76b64a,10px 7px 0 -5px #34782c,0 -8px 0 -6px #8dca61; }
        .loofboom-l::after { height:17px; width:7px; }
        .dennenboom { bottom:22px; width:28px; height:45px; }
        .dennenboom::before,.dennenboom::after { content:''; position:absolute; left:50%; transform:translateX(-50%); width:0; height:0; border-left:14px solid transparent; border-right:14px solid transparent; }
        .dennenboom::before { bottom:10px; border-bottom:29px solid #2f7f43; }
        .dennenboom::after { bottom:0; border-bottom:25px solid #236b38; filter:drop-shadow(0 -7px 0 #3d9650); }
        .dennenboom .stam { position:relative; display:block; top:35px; left:12px; width:5px; height:10px; background:#714721; }
        .den-s { transform:scale(.82); transform-origin:bottom center; }
        .den-l { transform:scale(1.14); transform-origin:bottom center; }
        .rijtjeshuis { bottom:18px; width:55px; height:42px; }
        .rijtjeshuis::after { content:''; position:absolute; bottom:-2px; left:6px; width:43px; height:4px; background:rgba(58,77,42,.36); border-radius:50%; }
        .rijtjeshuis .dak { position:absolute; top:2px; left:2px; width:51px; height:12px; background:linear-gradient(135deg,#7d2c16 0%,#a33f1d 52%,#632010 100%); clip-path:polygon(0 100%,12% 18%,88% 18%,100% 100%); }
        .rijtjeshuis .dak::before { content:''; position:absolute; top:-5px; right:9px; width:6px; height:11px; background:#62311f; }
        .rijtjeshuis .muur { position:absolute; bottom:0; left:4px; width:47px; height:28px; background:repeating-linear-gradient(0deg,transparent 0 6px,rgba(112,45,28,.34) 6px 7px),repeating-linear-gradient(90deg,rgba(166,72,43,.34) 0 8px,transparent 8px 16px),linear-gradient(180deg,#c4683d 0%,#aa4f31 100%); border-bottom:2px solid #76331f; box-shadow:inset 0 0 0 1px rgba(105,43,28,.3); }
        .rijtjeshuis .muur::before { content:''; position:absolute; top:5px; left:6px; width:7px; height:7px; background:#b8d4e8; border:1px solid #5e879c; box-shadow:16px 0 0 #b8d4e8,16px 0 0 1px #5e879c,31px 0 0 #b8d4e8,31px 0 0 1px #5e879c; }
        .rijtjeshuis .muur::after { content:''; position:absolute; bottom:0; left:20px; width:8px; height:14px; background:linear-gradient(90deg,#4f2d20 0 65%,#332018 65% 100%); border-radius:2px 2px 0 0; box-shadow:inset 2px 0 0 rgba(255,255,255,.12); }
        .rijtjeshuis-s { transform:scale(.82); transform-origin:bottom center; }
        .rijtjeshuis-l { transform:scale(1.12); transform-origin:bottom center; }
        .boerderij { bottom:18px; width:68px; height:38px; }
        .boerderij::after { content:''; position:absolute; bottom:-2px; left:9px; width:50px; height:4px; background:rgba(58,77,42,.36); border-radius:50%; }
        .boerderij .dak { position:absolute; top:0; left:3px; width:62px; height:17px; background:linear-gradient(180deg,#343941 0%,#20242a 100%); clip-path:polygon(8% 100%,24% 12%,76% 12%,92% 100%); }
        .boerderij .muur { position:absolute; bottom:0; left:8px; width:52px; height:24px; background:linear-gradient(180deg,#e1d1ac 0%,#cdae79 100%); border-bottom:2px solid #8a6b45; }
        .boerderij .muur::before { content:''; position:absolute; top:6px; left:7px; width:7px; height:7px; background:#a9c8d9; border:1px solid #6a8796; box-shadow:30px 0 0 #a9c8d9,30px 0 0 1px #6a8796; }
        .boerderij .muur::after { content:''; position:absolute; bottom:0; left:22px; width:10px; height:15px; background:#6a4128; border-radius:5px 5px 0 0; }
        .stationsgebouw { bottom:36px; left:calc(50% + 135px); width:82px; height:42px; }
        .stationsgebouw::after { content:''; position:absolute; bottom:-2px; left:9px; width:64px; height:4px; background:rgba(70,64,58,.32); border-radius:50%; }
        .stationsgebouw .dak { position:absolute; top:0; left:3px; width:76px; height:14px; background:linear-gradient(180deg,#8d3420 0%,#5b2118 100%); clip-path:polygon(0 100%,13% 20%,87% 20%,100% 100%); }
        .stationsgebouw .muur { position:absolute; bottom:0; left:8px; width:66px; height:30px; background:repeating-linear-gradient(0deg,transparent 0 8px,rgba(139,85,53,.24) 8px 9px),linear-gradient(180deg,#d7b77d 0%,#bd9157 100%); border:1px solid #8b623e; border-bottom-width:2px; }
        .stationsgebouw .muur::before { content:''; position:absolute; top:7px; left:8px; width:8px; height:9px; background:#b8d7e8; border:1px solid #5d8496; box-shadow:21px 0 0 #b8d7e8,21px 0 0 1px #5d8496,42px 0 0 #b8d7e8,42px 0 0 1px #5d8496; }
        .stationsgebouw .muur::after { content:''; position:absolute; bottom:0; left:28px; width:11px; height:16px; background:#56311f; border-radius:2px 2px 0 0; }
        .bushokje { bottom:36px; left:calc(50% - 170px); width:42px; height:31px; border-left:3px solid #30363c; border-right:3px solid #30363c; background:linear-gradient(120deg,rgba(190,225,238,.72) 0%,rgba(236,250,255,.35) 48%,rgba(129,176,194,.55) 100%); box-shadow:inset 12px 0 0 rgba(255,255,255,.22); }
        .bushokje::before { content:''; position:absolute; top:-6px; left:-6px; width:50px; height:7px; background:#252b31; border-radius:2px 2px 0 0; }
        .bushokje::after { content:''; position:absolute; bottom:-2px; left:-4px; right:-4px; height:3px; background:#59616a; }
        .lantaarnpaal { bottom:28px; width:15px; height:42px; }
        .lantaarnpaal::before { content:''; position:absolute; bottom:0; left:6px; width:3px; height:35px; background:linear-gradient(90deg,#3d4246 0%,#6d747b 55%,#2f3337 100%); }
        .lantaarnpaal::after { content:''; position:absolute; top:2px; left:2px; width:11px; height:8px; border-radius:6px 6px 4px 4px; background:radial-gradient(circle at 50% 60%,#fff0a7 0 35%,#f5c94e 58%,#705f2b 100%); box-shadow:0 0 7px rgba(255,215,92,.55); }
        .windmolen { bottom:22px; width:52px; height:58px; opacity:.86; z-index:1; }
        .windmolen .molenhuis { position:absolute; bottom:0; left:17px; width:18px; height:31px; background:linear-gradient(180deg,#d9c79f 0%,#b99562 100%); clip-path:polygon(22% 0,78% 0,100% 100%,0 100%); border-bottom:2px solid #7a5a38; }
        .windmolen .molenhuis::before { content:''; position:absolute; top:-8px; left:1px; width:16px; height:11px; background:#57372a; clip-path:polygon(50% 0,100% 100%,0 100%); }
        .windmolen .molenhuis::after { content:''; position:absolute; bottom:5px; left:7px; width:5px; height:9px; background:#4d3324; }
        @keyframes windmolen-draaien { from { transform:rotate(0deg); } to { transform:rotate(360deg); } }
        .windmolen .wieken { position:absolute; top:17px; left:24px; width:4px; height:4px; background:#4f473e; border-radius:50%; animation:windmolen-draaien 4s linear infinite; transform-origin:2px 2px; }
        .windmolen .wiek { position:absolute; left:1px; top:-14px; width:2px; height:32px; background:#5d5145; transform-origin:1px 16px; }
        .windmolen .wiek::after { content:''; position:absolute; top:0; left:-3px; width:8px; height:12px; background:repeating-linear-gradient(0deg,rgba(255,255,255,.22) 0 2px,transparent 2px 4px); border:1px solid rgba(79,71,62,.6); }
        .windmolen .wiek:nth-child(2) { transform:rotate(90deg); }
        .windmolen .wiek:nth-child(3) { transform:rotate(180deg); }
        .windmolen .wiek:nth-child(4) { transform:rotate(270deg); }
        @media (max-width:760px) {
            .hide-sm { display:none; }
            .stationsgebouw { left:calc(50% + 118px); transform:scale(.82); transform-origin:bottom left; }
            .bushokje { left:calc(50% - 142px); transform:scale(.85); transform-origin:bottom center; }
            .lantaarnpaal { transform:scale(.88); transform-origin:bottom center; }
        }
        @media (max-width:520px) {
            .hide-xs { display:none; }
            .stationsgebouw { display:none; }
            .bushokje { left:calc(50% - 128px); }
            .loofboom-l,.den-l,.rijtjeshuis-l { transform:scale(.9); transform-origin:bottom center; }
        }
    </style>

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

</body>
</html>
