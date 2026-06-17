<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Budget' }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/budget.css') }}?v={{ filemtime(public_path('css/budget.css')) }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('tailwind.config.js') }}"></script>
</head>
<body>

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
                ['naam' => 'SQL Vergelijker', 'route' => 'sql-vergelijker.index', 'patroon' => 'sql-vergelijker*'],
                ['naam' => 'SQL Data',         'route' => 'sql-data.index',        'patroon' => 'sql-data*'],
                ['naam' => 'Budget',           'route' => 'budget.home',           'patroon' => 'budget*|rekeningen*|transacties*|categorieen*'],
                ['naam' => 'GPX Viewer',       'route' => 'gpx-viewer.index',      'patroon' => 'gpx-viewer*'],
                ['naam' => 'Scouting',         'route' => 'scouting.home',         'patroon' => 'scouting*'],
            ];
        @endphp
        @foreach($navProjecten as $project)
            @if(request()->is(explode('|', $project['patroon'])))
                <span class="nav-actief">{{ $project['naam'] }}</span>
            @else
                <a href="{{ route($project['route']) }}">{{ $project['naam'] }}</a>
            @endif
        @endforeach

        <div style="width:1px;height:20px;background:#2d3540;margin:0 6px;flex-shrink:0"></div>

        @if(request()->is('rekeningen*'))
            <span class="nav-actief nav-sub">Rekeningen</span>
        @else
            <a href="{{ route('rekeningen.index') }}" class="nav-sub">Rekeningen</a>
        @endif

        @if(request()->is('transacties*'))
            <span class="nav-actief nav-sub">Transacties</span>
        @else
            <a href="{{ route('transacties.index') }}" class="nav-sub">Transacties</a>
        @endif

        @if(request()->is('categorieen*'))
            <span class="nav-actief nav-sub">Categorieën</span>
        @else
            <a href="{{ route('categorieen.index') }}" class="nav-sub">Categorieën</a>
        @endif

        <div style="flex:1"></div>

        <span style="font-size:.8rem;color:#5a6472;padding:0 4px">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('uitloggen') }}" style="display:inline">
            @csrf
            <button type="submit" style="background:none;border:none;cursor:pointer;color:#8a94a2;font-size:.8rem;padding:5px 10px;border-radius:7px;transition:color .15s,background .15s" onmouseover="this.style.color='#e6e9ee';this.style.background='rgba(255,255,255,.07)'" onmouseout="this.style.color='#8a94a2';this.style.background='none'">
                Uitloggen
            </button>
        </form>
    </nav>

    <div class="max-w-5xl mx-auto px-6 py-10">

        @if(session('succes'))
            <div class="mb-6 bg-accent/10 border border-accent/30 text-accent rounded-xl px-4 py-3 text-sm">
                {{ session('succes') }}
            </div>
        @endif

        {{ $slot }}

    </div>

</body>
</html>
