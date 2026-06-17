<x-layouts.portaal title="Scouting">

    {{-- Hero banner --}}
    <div class="sc-banner">

        {{-- Fleur-de-lis --}}
        <svg class="sc-fleur" viewBox="0 0 52 60" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M26 2C26 2 18 10 18 18c0 4 2 7 4 9l-2 4h12l-2-4c2-2 4-5 4-9 0-8-8-16-8-16z" fill="#4a8c3f"/>
            <path d="M26 28c0 0-14-4-18-12C6 10 10 4 10 4s2 8 8 12c2 1.5 5 2.5 8 2.5s6-1 8-2.5c6-4 8-12 8-12s4 6 2 12c-4 8-18 12-18 12z" fill="#3d7435"/>
            <rect x="22" y="30" width="8" height="4" rx="2" fill="#2f5c28"/>
            <path d="M16 34 Q26 40 36 34 L34 44 Q26 48 18 44z" fill="#2f5c28"/>
        </svg>

        <div class="sc-banner-titel">Scouting</div>
        <div class="sc-banner-sub">Leden · Leiding · Kampen</div>

        {{-- Bomenrij --}}
        <div class="sc-bos" aria-hidden="true">
            @foreach([
                [16, 28, 8],  [11, 20, 6],  [19, 34, 9],  [13, 24, 7],
                [22, 40, 10], [14, 26, 7],  [18, 32, 9],  [12, 22, 6],
                [20, 36, 10], [15, 28, 8],  [17, 30, 9],  [13, 24, 7],
            ] as [$k1, $k2, $s])
                <div class="sc-boom">
                    <div class="kroon"   style="width:{{ $k1*2 }}px;height:{{ $k1 }}px"></div>
                    <div class="kroon-2" style="width:{{ $k2*2 }}px;height:{{ $k2 }}px"></div>
                    <div class="stam"    style="height:{{ $s }}px"></div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="max-w-[900px] mx-auto px-4 pb-16">

        {{-- Statistieken --}}
        <div class="grid grid-cols-4 gap-4 mb-10">
            <div class="sc-stat">
                <div class="sc-stat-getal">{{ $aantalLeden }}</div>
                <div class="sc-stat-label">leden</div>
            </div>
            <div class="sc-stat">
                <div class="sc-stat-getal">{{ $aantalLeiding }}</div>
                <div class="sc-stat-label">leiding</div>
            </div>
            <div class="sc-stat neutraal">
                <div class="sc-stat-getal">{{ $aantalKampen }}</div>
                <div class="sc-stat-label">kampen totaal</div>
            </div>
            <div class="sc-stat neutraal">
                <div class="sc-stat-getal">{{ $komendKampen->count() }}</div>
                <div class="sc-stat-label">komende kampen</div>
            </div>
        </div>

        {{-- Snelkoppelingen --}}
        <div class="grid grid-cols-3 gap-4 mb-10">
            <a href="{{ route('scouting.leden.index') }}" class="sc-kaart">
                <div class="sc-kaart-icoon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20H7a4 4 0 01-.88-7.9A6 6 0 1117.92 14H18a3 3 0 010 6h-1z"/>
                        <circle cx="12" cy="8" r="3" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="sc-kaart-naam">Leden</div>
                <div class="sc-kaart-omsch">Bekijk en beheer alle leden</div>
            </a>
            <a href="{{ route('scouting.leiding.index') }}" class="sc-kaart">
                <div class="sc-kaart-icoon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <div class="sc-kaart-naam">Leiding</div>
                <div class="sc-kaart-omsch">Bekijk en beheer alle leiding</div>
            </a>
            <a href="{{ route('scouting.kampen.index') }}" class="sc-kaart">
                <div class="sc-kaart-icoon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 20h18M6 20L12 4l6 16M9 20l3-8 3 8"/>
                    </svg>
                </div>
                <div class="sc-kaart-naam">Kampen</div>
                <div class="sc-kaart-omsch">Beheer kampen en kampdeelnames</div>
            </a>
        </div>

        {{-- Komende kampen --}}
        @if($komendKampen->isNotEmpty())
            <h2 class="text-sm font-semibold uppercase tracking-widest mb-4" style="color:#4d7347">Komende kampen</h2>
            <div class="flex flex-col gap-3">
                @foreach($komendKampen as $kamp)
                    <a href="{{ route('scouting.kampen.tonen', $kamp) }}" class="sc-kamp-rij">
                        <div class="flex items-center gap-3">
                            <div style="width:36px;height:36px;border-radius:10px;background:#2b3e24;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <svg style="width:18px;height:18px;stroke:#6bc265" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 20h18M6 20L12 4l6 16M9 20l3-8 3 8"/>
                                </svg>
                            </div>
                            <div>
                                <div class="sc-kamp-naam">{{ $kamp->naam }}</div>
                                <div class="sc-kamp-info">
                                    {{ $kamp->start_datum->format('d M Y') }} – {{ $kamp->eind_datum->format('d M Y') }}
                                    @if($kamp->locatie) · {{ $kamp->locatie }} @endif
                                </div>
                            </div>
                        </div>
                        <svg style="width:16px;height:16px;stroke:#4d7347;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endforeach
            </div>
        @endif

    </div>

</x-layouts.portaal>
