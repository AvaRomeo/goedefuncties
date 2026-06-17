<x-layouts.portaal title="Kampen — Scouting">

    <div class="max-w-[900px] mx-auto px-4 pt-10 pb-16">

        <a href="{{ route('scouting.home') }}" class="sc-terug">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Terug naar Scouting
        </a>

        <div class="sc-page-header mb-6">
            <div class="sc-page-header-icoon">⛺</div>
            <div>
                <h1>Kampen</h1>
                <p>{{ $kampen->count() }} {{ $kampen->count() === 1 ? 'kamp' : 'kampen' }}</p>
            </div>
            <a href="{{ route('scouting.kampen.aanmaken') }}"
               class="bg-accent text-[#10241a] rounded-lg px-4 py-2 font-semibold text-sm hover:opacity-90 transition-opacity ml-auto">
                + Kamp toevoegen
            </a>
        </div>

        @if(session('succes'))
            <div class="bg-accent/10 border border-accent/30 rounded-lg px-4 py-3 mb-5 text-accent text-sm">
                {{ session('succes') }}
            </div>
        @endif

        @if($kampen->isEmpty())
            <p class="text-gedempt text-sm">Nog geen kampen. <a href="{{ route('scouting.kampen.aanmaken') }}" class="text-accent hover:underline">Maak het eerste kamp aan.</a></p>
        @else
            <div class="flex flex-col gap-3">
                @foreach($kampen as $kamp)
                    <div class="bg-paneel border border-rand rounded-xl px-5 py-4 flex items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('scouting.kampen.tonen', $kamp) }}"
                               class="font-semibold text-tekst hover:text-accent transition-colors">{{ $kamp->naam }}</a>
                            <div class="text-gedempt text-sm mt-0.5 flex flex-wrap gap-x-3">
                                <span>{{ $kamp->start_datum->format('d M Y') }} – {{ $kamp->eind_datum->format('d M Y') }}</span>
                                @if($kamp->locatie)<span>· {{ $kamp->locatie }}</span>@endif
                                <span>· {{ $kamp->deelnames_count }} {{ $kamp->deelnames_count === 1 ? 'deelnemer' : 'deelnemers' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            @php $verleden = $kamp->eind_datum->isPast(); @endphp
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $verleden ? 'bg-rand text-gedempt' : 'bg-accent/15 text-accent' }}">
                                {{ $verleden ? 'Geweest' : 'Komend' }}
                            </span>
                            <a href="{{ route('scouting.kampen.tonen', $kamp) }}"
                               class="text-gedempt hover:text-accent transition-colors text-xs">Details</a>
                            <a href="{{ route('scouting.kampen.bewerken', $kamp) }}"
                               class="text-gedempt hover:text-accent transition-colors text-xs">Bewerken</a>
                            <form method="post" action="{{ route('scouting.kampen.verwijderen', $kamp) }}"
                                  onsubmit="return confirm('Kamp verwijderen? Alle deelnames worden ook verwijderd.')">
                                @csrf @method('DELETE')
                                <button class="text-gedempt hover:text-fout transition-colors text-xs cursor-pointer">Verwijderen</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>

</x-layouts.portaal>
