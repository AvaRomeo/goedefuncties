<x-layouts.portaal title="Kleine Projecten">
<style>
    .module-kaart {
        background: #1e242d;
        border: 1px solid #2d3540;
        border-radius: 16px;
        overflow: hidden;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        transition: transform .18s, box-shadow .18s, border-color .18s;
        position: relative;
    }
    .module-kaart:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 40px rgba(0,0,0,.45);
        text-decoration: none;
    }

    /* Gekleurde bovenbalk */
    .module-accent {
        height: 4px;
        width: 100%;
        flex-shrink: 0;
    }

    /* Illustratie-blok */
    .module-illu {
        height: 88px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: flex-end;
        flex-shrink: 0;
    }

    .module-body {
        padding: 20px 22px 22px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex: 1;
    }

    .module-naam {
        font-size: 1.05rem;
        font-weight: 700;
        color: #e6e9ee;
        line-height: 1.2;
    }
    .module-omschrijving {
        font-size: .875rem;
        color: #8a94a2;
        line-height: 1.55;
        flex: 1;
    }
    .module-tag {
        display: inline-flex;
        align-items: center;
        font-size: .75rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 999px;
        align-self: flex-start;
        margin-top: 4px;
        letter-spacing: .01em;
    }
    .module-pijl {
        position: absolute;
        right: 18px;
        bottom: 20px;
        font-size: .8rem;
        opacity: 0;
        transition: opacity .15s, transform .15s;
        color: #8a94a2;
    }
    .module-kaart:hover .module-pijl { opacity: 1; transform: translateX(3px); }

    /* ── SQL illustratie: server-racks ──────────────────────────── */
    .illu-sql { background: linear-gradient(180deg, #1c1408 0%, #211a08 100%); justify-content: flex-end; gap: 5px; padding: 0 18px; align-items: flex-end; }
    .illu-rack { background: #1a1408; border: 1px solid #3d2c10; border-bottom: none; border-radius: 3px 3px 0 0; display: flex; flex-direction: column; padding: 4px 3px 0; gap: 2px; flex-shrink: 0; width: 36px; }
    .illu-slot { height: 4px; background: rgba(245,158,11,.1); border-radius: 1px; }
    .illu-slot.aan { background: rgba(245,158,11,.45); box-shadow: 0 0 4px rgba(245,158,11,.3); }

    /* ── Budget illustratie: stadssilhouet ──────────────────────── */
    .illu-budget { background: linear-gradient(180deg, #1c1508 0%, #1e1a06 100%); align-items: flex-end; gap: 2px; padding: 0; }
    .illu-gebouw { flex-shrink: 0; background: #211a06; border-top: 1px solid #3d2e0a; background-image:
        repeating-linear-gradient(0deg, transparent, transparent 7px, rgba(240,192,64,.08) 7px, rgba(240,192,64,.08) 8px),
        repeating-linear-gradient(90deg, transparent, transparent 5px, rgba(240,192,64,.08) 5px, rgba(240,192,64,.08) 6px);
        background-color: #211a06; }
    .illu-gebouw:nth-child(4n) { background-image:
        repeating-linear-gradient(0deg, transparent, transparent 7px, rgba(240,192,64,.18) 7px, rgba(240,192,64,.18) 8px),
        repeating-linear-gradient(90deg, transparent, transparent 5px, rgba(240,192,64,.18) 5px, rgba(240,192,64,.18) 6px); }

    /* ── GPX illustratie: bergen ────────────────────────────────── */
    .illu-gpx { background: linear-gradient(180deg, #081424 0%, #0f1825 100%); align-items: flex-end; gap: -4px; padding: 0; }
    .illu-berg { flex-shrink: 0; position: relative; }

    /* ── Scouting illustratie: bomen ────────────────────────────── */
    .illu-scouting { background: linear-gradient(180deg, #141e10 0%, #1b2918 100%); align-items: flex-end; gap: 6px; padding: 0 10px; }
    .illu-kroon  { background: #2d4a1e; clip-path: polygon(50% 0%, 0% 100%, 100% 100%); flex-shrink: 0; }
    .illu-kroon2 { background: #243d16; clip-path: polygon(50% 0%, 0% 100%, 100% 100%); flex-shrink: 0; margin-top: -8px; }
    .illu-stam   { background: #5a3a1a; flex-shrink: 0; margin: 0 auto; }
    .illu-boom   { display: flex; flex-direction: column; align-items: center; flex-shrink: 0; }
</style>

<div class="max-w-275 mx-auto px-5 py-12">

    {{-- Header --}}
    <header class="text-center mb-12">
        <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:16px;background:rgba(76,195,138,.12);border:1px solid rgba(76,195,138,.2);margin-bottom:16px">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="rgba(76,195,138,.9)">
                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
            </svg>
        </div>
        <h1 class="text-[2rem] text-tekst font-bold tracking-tight leading-none">Kleine Projecten</h1>
        <p class="text-gedempt mt-2 text-[.95rem]">Kies een project om mee aan de slag te gaan</p>
    </header>

    {{-- Kaarten --}}
    <div class="grid grid-cols-[repeat(auto-fill,minmax(280px,1fr))] gap-5">

        {{-- SQL Vergelijker --}}
        <a class="module-kaart" href="{{ route('sql-vergelijker.index') }}" style="--c:#f59e0b">
            <div class="module-accent" style="background:linear-gradient(90deg,#f59e0b,#d97706)"></div>
            <div class="module-illu illu-sql">
                @php $racks2 = [
                    ['h'=>62,'s'=>['aan','','aan','','aan','']],
                    ['h'=>46,'s'=>['','aan','','aan','']],
                    ['h'=>68,'s'=>['aan','aan','','','aan','','aan']],
                    ['h'=>40,'s'=>['','aan','','']],
                    ['h'=>58,'s'=>['aan','','aan','','','aan']],
                    ['h'=>70,'s'=>['','aan','','aan','','aan','']],
                    ['h'=>50,'s'=>['aan','','','aan']],
                ]; @endphp
                @foreach($racks2 as $r)
                    <div class="illu-rack" style="height:{{$r['h']}}px">
                        @foreach($r['s'] as $s)<div class="illu-slot {{$s}}"></div>@endforeach
                    </div>
                @endforeach
            </div>
            <div class="module-body">
                <div class="module-naam">SQL Vergelijker</div>
                <div class="module-omschrijving">Vergelijk twee databasestructuren en genereer de sync-SQL voor tabellen en kolommen.</div>
                <span class="module-tag" style="background:rgba(245,158,11,.12);color:#f59e0b">SQL · PHP</span>
            </div>
            <span class="module-pijl">→</span>
        </a>

        {{-- SQL Data --}}
        <a class="module-kaart" href="{{ route('sql-data.index') }}" style="--c:#f59e0b">
            <div class="module-accent" style="background:linear-gradient(90deg,#d97706,#b45309)"></div>
            <div class="module-illu illu-sql">
                @php $racks3 = [
                    ['h'=>55,'s'=>['','aan','aan','','aan']],
                    ['h'=>72,'s'=>['aan','','','aan','','aan','']],
                    ['h'=>44,'s'=>['aan','aan','']],
                    ['h'=>66,'s'=>['','aan','','aan','aan','']],
                    ['h'=>38,'s'=>['aan','','aan']],
                    ['h'=>60,'s'=>['','','aan','','aan','aan']],
                    ['h'=>52,'s'=>['aan','','aan','']],
                ]; @endphp
                @foreach($racks3 as $r)
                    <div class="illu-rack" style="height:{{$r['h']}}px">
                        @foreach($r['s'] as $s)<div class="illu-slot {{$s}}"></div>@endforeach
                    </div>
                @endforeach
            </div>
            <div class="module-body">
                <div class="module-naam">SQL Data extractor</div>
                <div class="module-omschrijving">Upload een SQL-dump, kies een tabel en exporteer de INSERT-statements.</div>
                <span class="module-tag" style="background:rgba(217,119,6,.12);color:#d97706">SQL · PHP</span>
            </div>
            <span class="module-pijl">→</span>
        </a>

        {{-- Budget (alleen zichtbaar als ingelogd) --}}
        @auth
        <a class="module-kaart" href="{{ route('budget.home') }}" style="--c:#f0c040">
            <div class="module-accent" style="background:linear-gradient(90deg,#f0c040,#d4a820)"></div>
            <div class="module-illu illu-budget">
                @php $geb = [
                    ['h'=>42,'w'=>22],['h'=>70,'w'=>16],['h'=>55,'w'=>28],['h'=>80,'w'=>18],
                    ['h'=>48,'w'=>24],['h'=>66,'w'=>14],['h'=>35,'w'=>30],['h'=>75,'w'=>18],
                    ['h'=>58,'w'=>26],['h'=>40,'w'=>20],['h'=>68,'w'=>16],['h'=>50,'w'=>28],
                    ['h'=>88,'w'=>14],['h'=>44,'w'=>22],['h'=>62,'w'=>18],['h'=>36,'w'=>28],
                    ['h'=>72,'w'=>16],['h'=>46,'w'=>24],['h'=>54,'w'=>20],['h'=>82,'w'=>14],
                ]; @endphp
                @foreach($geb as $g)
                    <div class="illu-gebouw" style="height:{{$g['h']}}px;width:{{$g['w']}}px"></div>
                @endforeach
            </div>
            <div class="module-body">
                <div class="module-naam">Budget</div>
                <div class="module-omschrijving">Beheer rekeningen, importeer transacties en houd je budget overzichtelijk bij.</div>
                <span class="module-tag" style="background:rgba(240,192,64,.12);color:#f0c040">Laravel · SQLite</span>
            </div>
            <span class="module-pijl">→</span>
        </a>
        @endauth

        {{-- GPX Viewer --}}
        <a class="module-kaart" href="{{ route('gpx-viewer.index') }}" style="--c:#38bdf8">
            <div class="module-accent" style="background:linear-gradient(90deg,#38bdf8,#0ea5e9)"></div>
            <div class="module-illu illu-gpx" style="gap:0">
                @php $bergen = [
                    ['w'=>70,'h'=>38,'p'=>'45%','c'=>'#091526'],
                    ['w'=>90,'h'=>54,'p'=>'38%','c'=>'#0a1a30'],
                    ['w'=>60,'h'=>44,'p'=>'50%','c'=>'#0c1e36'],
                    ['w'=>110,'h'=>68,'p'=>'35%','c'=>'#0e2240'],
                    ['w'=>75,'h'=>50,'p'=>'42%','c'=>'#091e38'],
                    ['w'=>55,'h'=>36,'p'=>'48%','c'=>'#0b1c34'],
                    ['w'=>95,'h'=>60,'p'=>'40%','c'=>'#0d2040'],
                    ['w'=>65,'h'=>46,'p'=>'44%','c'=>'#091a30'],
                    ['w'=>80,'h'=>52,'p'=>'38%','c'=>'#0a1c38'],
                    ['w'=>50,'h'=>32,'p'=>'50%','c'=>'#081428'],
                ]; @endphp
                @foreach($bergen as $b)
                    <div class="illu-berg" style="width:{{$b['w']}}px;height:{{$b['h']}}px;background:{{$b['c']}};clip-path:polygon({{$b['p']}} 0%,100% 100%,0% 100%);margin-right:-14px"></div>
                @endforeach
            </div>
            <div class="module-body">
                <div class="module-naam">GPX Viewer</div>
                <div class="module-omschrijving">Laad een GPX-bestand en bekijk de route op een interactieve kaart met hoogteprofiel.</div>
                <span class="module-tag" style="background:rgba(56,189,248,.12);color:#38bdf8">Leaflet · GPX</span>
            </div>
            <span class="module-pijl">→</span>
        </a>

        {{-- Scouting (alleen zichtbaar als ingelogd) --}}
        @auth
        <a class="module-kaart" href="{{ route('scouting.home') }}" style="--c:#4ade80">
            <div class="module-accent" style="background:linear-gradient(90deg,#4ade80,#22c55e)"></div>
            <div class="module-illu illu-scouting">
                @php $bomen = [
                    [14,10,5],[10,8,4],[18,13,6],[12,9,4],[16,12,5],[10,7,4],[20,14,7],[13,10,4],[15,11,5],[11,8,4],[17,12,6],
                ]; @endphp
                @foreach($bomen as [$k1,$k2,$s])
                    <div class="illu-boom">
                        <div class="illu-kroon"  style="width:{{$k1*2}}px;height:{{$k1}}px"></div>
                        <div class="illu-kroon2" style="width:{{$k2*2}}px;height:{{$k2}}px"></div>
                        <div class="illu-stam"   style="width:{{max(4,intval($k1*.25))}}px;height:{{$s}}px"></div>
                    </div>
                @endforeach
            </div>
            <div class="module-body">
                <div class="module-naam">Scouting</div>
                <div class="module-omschrijving">Beheer scoutingleden, kampen en kampdeelnames inclusief betalingsoverzicht.</div>
                <span class="module-tag" style="background:rgba(74,222,128,.12);color:#4ade80">Laravel · SQLite</span>
            </div>
            <span class="module-pijl">→</span>
        </a>
        @endauth

    </div>
</div>
</x-layouts.portaal>
