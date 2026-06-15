<x-layouts.portaal title="{{ $kamp->naam }} — Scouting">

    <div class="max-w-[900px] mx-auto px-4 pt-10 pb-16">

        <a href="{{ route('scouting.kampen.index') }}" class="text-gedempt text-sm hover:text-accent transition-colors mb-6 inline-flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Terug naar kampen
        </a>

        @if(session('succes'))
            <div class="bg-accent/10 border border-accent/30 rounded-lg px-4 py-3 mb-5 text-accent text-sm">
                {{ session('succes') }}
            </div>
        @endif

        {{-- Kamp header --}}
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-[1.45rem] font-semibold tracking-tight text-tekst">{{ $kamp->naam }}</h1>
                <p class="text-gedempt mt-0.5">
                    {{ $kamp->start_datum->format('d M Y') }} – {{ $kamp->eind_datum->format('d M Y') }}
                    ({{ $kamp->duur }} {{ $kamp->duur === 1 ? 'dag' : 'dagen' }})
                    @if($kamp->locatie) · {{ $kamp->locatie }} @endif
                </p>
                @if($kamp->beschrijving)
                    <p class="text-gedempt text-sm mt-2">{{ $kamp->beschrijving }}</p>
                @endif
            </div>
            <a href="{{ route('scouting.kampen.bewerken', $kamp) }}"
               class="border border-rand text-gedempt rounded-lg px-4 py-2 text-sm hover:border-accent hover:text-accent transition-colors whitespace-nowrap shrink-0">
                Bewerken
            </a>
        </div>

        {{-- Betalingsoverzicht --}}
        @if($kamp->deelnames->isNotEmpty())
            @php
                $totaal       = $kamp->deelnames->count();
                $betaald      = $kamp->deelnames->where('betaald', true)->count();
                $totaalBedrag = $kamp->deelnames->sum('bedrag');
                $betaaldBedrag = $kamp->deelnames->where('betaald', true)->sum('bedrag');
            @endphp
            <div class="grid grid-cols-4 gap-3 mb-8">
                <div class="bg-paneel border border-rand rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-tekst tabular-nums">{{ $totaal }}</div>
                    <div class="text-gedempt text-xs mt-1">deelnemers</div>
                </div>
                <div class="bg-paneel border border-rand rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-accent tabular-nums">{{ $betaald }}</div>
                    <div class="text-gedempt text-xs mt-1">betaald</div>
                </div>
                <div class="bg-paneel border border-rand rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-tekst tabular-nums">{{ $totaal - $betaald }}</div>
                    <div class="text-gedempt text-xs mt-1">openstaand</div>
                </div>
                <div class="bg-paneel border border-rand rounded-xl p-4 text-center">
                    <div class="text-lg font-bold text-tekst tabular-nums">€ {{ number_format($betaaldBedrag, 2, ',', '.') }}</div>
                    <div class="text-gedempt text-xs mt-1">van € {{ number_format($totaalBedrag, 2, ',', '.') }}</div>
                </div>
            </div>
        @endif

        {{-- Deelnemers tabel --}}
        <div class="mb-8">
            <h2 class="text-base font-semibold text-tekst mb-3">Deelnemers</h2>

            @if($kamp->deelnames->isEmpty())
                <p class="text-gedempt text-sm">Nog geen deelnemers.</p>
            @else
                <div class="rounded-xl border border-rand overflow-hidden mb-4">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr class="bg-[#1a1f28]">
                                <th class="text-left px-4 py-2.5 text-gedempt font-semibold text-xs uppercase tracking-wide">Naam</th>
                                <th class="text-left px-4 py-2.5 text-gedempt font-semibold text-xs uppercase tracking-wide">Speltak</th>
                                <th class="text-left px-4 py-2.5 text-gedempt font-semibold text-xs uppercase tracking-wide">Bedrag</th>
                                <th class="text-left px-4 py-2.5 text-gedempt font-semibold text-xs uppercase tracking-wide">Betaald</th>
                                <th class="text-left px-4 py-2.5 text-gedempt font-semibold text-xs uppercase tracking-wide">Bijzonderheden</th>
                                <th class="px-4 py-2.5 w-24"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kamp->deelnames as $i => $deelname)
                                <tr class="{{ $i % 2 === 0 ? 'bg-paneel' : 'bg-[#1e242d]' }} hover:bg-rand/40 transition-colors">
                                    <td class="px-4 py-2.5 border-b border-rand/50 text-tekst font-medium">{{ $deelname->lid->naam }}</td>
                                    <td class="px-4 py-2.5 border-b border-rand/50 text-gedempt text-xs">{{ $deelname->lid->speltak }}</td>
                                    <td class="px-4 py-2.5 border-b border-rand/50 text-tekst tabular-nums">
                                        {{ $deelname->bedrag !== null ? '€ ' . number_format($deelname->bedrag, 2, ',', '.') : '—' }}
                                    </td>
                                    <td class="px-4 py-2.5 border-b border-rand/50">
                                        <form method="post" action="{{ route('scouting.deelnames.bijwerken', $deelname) }}">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="bevestigd" value="{{ $deelname->bevestigd ? 1 : 0 }}">
                                            <input type="hidden" name="bedrag" value="{{ $deelname->bedrag }}">
                                            <input type="hidden" name="bijzonderheden" value="{{ $deelname->bijzonderheden }}">
                                            <button type="submit" name="betaald" value="{{ $deelname->betaald ? 0 : 1 }}"
                                                    class="text-xs px-2.5 py-1 rounded-full cursor-pointer transition-colors
                                                        {{ $deelname->betaald ? 'bg-accent/15 text-accent hover:bg-fout/15 hover:text-fout' : 'bg-rand text-gedempt hover:bg-accent/15 hover:text-accent' }}">
                                                {{ $deelname->betaald ? 'Betaald' : 'Niet betaald' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-4 py-2.5 border-b border-rand/50 text-gedempt text-xs max-w-[200px] truncate">
                                        {{ $deelname->bijzonderheden ?: '—' }}
                                    </td>
                                    <td class="px-4 py-2.5 border-b border-rand/50">
                                        <form method="post" action="{{ route('scouting.deelnames.verwijderen', $deelname) }}"
                                              onsubmit="return confirm('Deelnemer verwijderen uit dit kamp?')">
                                            @csrf @method('DELETE')
                                            <button class="text-gedempt hover:text-fout transition-colors text-xs cursor-pointer">Verwijderen</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Deelnemer toevoegen --}}
        @if($beschikbareLeden->isNotEmpty())
            <div class="bg-paneel border border-rand rounded-xl p-5">
                <h3 class="text-sm font-semibold text-tekst mb-4">Deelnemer toevoegen</h3>
                <form method="post" action="{{ route('scouting.deelnames.opslaan', $kamp) }}" class="flex flex-wrap gap-3 items-end">
                    @csrf
                    <div class="flex-1 min-w-[160px]">
                        <label class="block text-xs text-gedempt mb-1">Lid *</label>
                        <select name="lid_id" required
                                class="w-full bg-[#161b23] border border-rand rounded-lg px-3 py-2 text-tekst text-sm outline-none focus:border-accent transition-colors">
                            <option value="">Kies een lid…</option>
                            @foreach($beschikbareLeden as $lid)
                                <option value="{{ $lid->id }}">{{ $lid->naam }} ({{ $lid->speltak }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-28">
                        <label class="block text-xs text-gedempt mb-1">Bedrag (€)</label>
                        <input type="number" name="bedrag" step="0.01" min="0"
                               value="{{ $kamp->prijs }}"
                               class="w-full bg-[#161b23] border border-rand rounded-lg px-3 py-2 text-tekst text-sm outline-none focus:border-accent transition-colors">
                    </div>
                    <div class="flex-1 min-w-[160px]">
                        <label class="block text-xs text-gedempt mb-1">Bijzonderheden</label>
                        <input type="text" name="bijzonderheden" placeholder="bijv. glutenvrij, medicatie…"
                               class="w-full bg-[#161b23] border border-rand rounded-lg px-3 py-2 text-tekst text-sm outline-none focus:border-accent transition-colors">
                    </div>
                    <button type="submit"
                            class="bg-accent text-[#10241a] rounded-lg px-4 py-2 font-semibold text-sm hover:opacity-90 transition-opacity cursor-pointer whitespace-nowrap">
                        Toevoegen
                    </button>
                </form>
            </div>
        @elseif($kamp->deelnames->isNotEmpty())
            <p class="text-gedempt text-sm">Alle actieve leden doen al mee aan dit kamp.</p>
        @endif

    </div>

</x-layouts.portaal>
