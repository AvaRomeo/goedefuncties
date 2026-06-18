<div>
    <div class="sc-page-header mb-6">
        <div class="sc-page-header-icoon">⛺</div>
        <div>
            <h1>Kampen</h1>
            <p>{{ $kampen->count() }} {{ $kampen->count() === 1 ? 'kamp' : 'kampen' }}</p>
        </div>
        <div class="flex items-center gap-2 ml-auto flex-wrap justify-end">
            <div class="flex rounded-lg border border-rand overflow-hidden text-sm">
                <button wire:click="$set('filter', 'alles')"
                    class="px-3 py-1.5 transition-colors {{ $filter === 'alles' ? 'bg-accent text-[#10241a] font-semibold' : 'text-gedempt hover:text-tekst' }}">
                    Alles
                </button>
                <button wire:click="$set('filter', 'komend')"
                    class="px-3 py-1.5 transition-colors border-l border-rand {{ $filter === 'komend' ? 'bg-accent text-[#10241a] font-semibold' : 'text-gedempt hover:text-tekst' }}">
                    Komend
                </button>
                <button wire:click="$set('filter', 'geweest')"
                    class="px-3 py-1.5 transition-colors border-l border-rand {{ $filter === 'geweest' ? 'bg-accent text-[#10241a] font-semibold' : 'text-gedempt hover:text-tekst' }}">
                    Geweest
                </button>
            </div>
            <a href="{{ route('scouting.kampen.aanmaken') }}"
               class="bg-accent text-[#10241a] rounded-lg px-4 py-2 font-semibold text-sm hover:opacity-90 transition-opacity">
                + Kamp toevoegen
            </a>
        </div>
    </div>

    @if($kampen->isEmpty())
        <p class="text-gedempt text-sm">
            @if($filter === 'komend')
                Geen komende kampen.
            @elseif($filter === 'geweest')
                Geen kampen geweest.
            @else
                Nog geen kampen. <a href="{{ route('scouting.kampen.aanmaken') }}" class="text-accent hover:underline">Maak het eerste kamp aan.</a>
            @endif
        </p>
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
                        <button
                            wire:click="verwijderen({{ $kamp->id }})"
                            wire:confirm="Kamp verwijderen? Alle deelnames worden ook verwijderd."
                            class="text-gedempt hover:text-fout transition-colors text-xs cursor-pointer"
                        >Verwijderen</button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
