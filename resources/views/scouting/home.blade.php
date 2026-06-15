<x-layouts.portaal title="Scouting">

    <div class="max-w-[900px] mx-auto px-4 pt-10 pb-16">

        <h1 class="text-[1.45rem] font-semibold mb-1 tracking-tight text-tekst">Scouting</h1>
        <p class="text-gedempt mb-8">Overzicht van leden en kampen.</p>

        {{-- Statistieken --}}
        <div class="grid grid-cols-3 gap-4 mb-10">
            <div class="bg-paneel border border-rand rounded-xl p-5 text-center">
                <div class="text-3xl font-bold text-accent tabular-nums">{{ $aantalLeden }}</div>
                <div class="text-gedempt text-sm mt-1">actieve leden</div>
            </div>
            <div class="bg-paneel border border-rand rounded-xl p-5 text-center">
                <div class="text-3xl font-bold text-tekst tabular-nums">{{ $aantalKampen }}</div>
                <div class="text-gedempt text-sm mt-1">kampen totaal</div>
            </div>
            <div class="bg-paneel border border-rand rounded-xl p-5 text-center">
                <div class="text-3xl font-bold text-tekst tabular-nums">{{ $komendKampen->count() }}</div>
                <div class="text-gedempt text-sm mt-1">komende kampen</div>
            </div>
        </div>

        {{-- Snelkoppelingen --}}
        <div class="grid grid-cols-2 gap-4 mb-10">
            <a href="{{ route('scouting.leden.index') }}"
               class="bg-paneel border border-rand rounded-xl p-6 hover:border-accent hover:-translate-y-0.5 transition-all flex flex-col gap-2">
                <span class="text-2xl">👥</span>
                <span class="font-semibold text-tekst">Leden</span>
                <span class="text-gedempt text-sm">Bekijk en beheer alle leden</span>
            </a>
            <a href="{{ route('scouting.kampen.index') }}"
               class="bg-paneel border border-rand rounded-xl p-6 hover:border-accent hover:-translate-y-0.5 transition-all flex flex-col gap-2">
                <span class="text-2xl">⛺</span>
                <span class="font-semibold text-tekst">Kampen</span>
                <span class="text-gedempt text-sm">Beheer kampen en kampdeelnames</span>
            </a>
        </div>

        {{-- Komende kampen --}}
        @if($komendKampen->isNotEmpty())
            <h2 class="text-base font-semibold text-tekst mb-3">Komende kampen</h2>
            <div class="flex flex-col gap-3">
                @foreach($komendKampen as $kamp)
                    <a href="{{ route('scouting.kampen.tonen', $kamp) }}"
                       class="bg-paneel border border-rand rounded-xl px-5 py-4 flex items-center justify-between hover:border-accent transition-colors">
                        <div>
                            <div class="font-semibold text-tekst">{{ $kamp->naam }}</div>
                            <div class="text-gedempt text-sm mt-0.5">
                                {{ $kamp->start_datum->format('d M Y') }} – {{ $kamp->eind_datum->format('d M Y') }}
                                @if($kamp->locatie) · {{ $kamp->locatie }} @endif
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gedempt shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endforeach
            </div>
        @endif

    </div>

</x-layouts.portaal>
