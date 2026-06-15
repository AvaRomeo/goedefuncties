<x-layouts.portaal title="Leden — Scouting">

    <div class="max-w-225 mx-auto px-4 pt-10 pb-16">

        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-[1.45rem] font-semibold tracking-tight text-tekst">Leden</h1>
                <p class="text-gedempt text-sm mt-0.5">{{ $leden->count() }} {{ $leden->count() === 1 ? 'lid' : 'leden' }}</p>
            </div>
            <form method="post" action="{{ route('scouting.leden.opslaan') }}" class="flex gap-2">
                @csrf
                <input type="text" name="naam" placeholder="Naam nieuw lid…" required autofocus
                       class="bg-paneel border border-rand rounded-lg px-3 py-2 text-tekst text-sm outline-none focus:border-accent transition-colors w-52">
                <button type="submit"
                        class="bg-accent text-[#10241a] rounded-lg px-4 py-2 font-semibold text-sm hover:opacity-90 transition-opacity cursor-pointer whitespace-nowrap">
                    + Toevoegen
                </button>
            </form>
        </div>

        @if(session('succes'))
            <div class="bg-accent/10 border border-accent/30 rounded-lg px-4 py-3 mb-5 text-accent text-sm">
                {{ session('succes') }}
            </div>
        @endif

        @if($leden->isEmpty())
            <p class="text-gedempt text-sm">Nog geen leden. <a href="{{ route('scouting.leden.aanmaken') }}" class="text-accent hover:underline">Voeg het eerste lid toe.</a></p>
        @else
            <div class="rounded-xl border border-rand overflow-hidden">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-[#1a1f28]">
                            <th class="text-left px-4 py-2.5 text-gedempt font-semibold text-xs uppercase tracking-wide">Naam</th>
                            <th class="text-left px-4 py-2.5 text-gedempt font-semibold text-xs uppercase tracking-wide">Speltak</th>
                            <th class="text-left px-4 py-2.5 text-gedempt font-semibold text-xs uppercase tracking-wide">Leeftijd</th>
                            <th class="text-left px-4 py-2.5 text-gedempt font-semibold text-xs uppercase tracking-wide">Contact ouder</th>
                            <th class="text-left px-4 py-2.5 text-gedempt font-semibold text-xs uppercase tracking-wide">Status</th>
                            <th class="px-4 py-2.5 w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leden as $i => $lid)
                            <tr class="{{ $i % 2 === 0 ? 'bg-paneel' : 'bg-[#1e242d]' }} hover:bg-rand/40 transition-colors">
                                <td class="px-4 py-2.5 border-b border-rand/50 text-tekst font-medium">{{ $lid->naam }}</td>
                                <td class="px-4 py-2.5 border-b border-rand/50 text-gedempt">{{ $lid->speltak }}</td>
                                <td class="px-4 py-2.5 border-b border-rand/50 text-gedempt">
                                    {{ $lid->leeftijd !== null ? $lid->leeftijd . ' jaar' : '—' }}
                                </td>
                                <td class="px-4 py-2.5 border-b border-rand/50 text-gedempt text-xs">
                                    @if($lid->email_ouder)
                                        <div>{{ $lid->email_ouder }}</div>
                                    @endif
                                    @if($lid->telefoon_ouder)
                                        <div>{{ $lid->telefoon_ouder }}</div>
                                    @endif
                                    @if(!$lid->email_ouder && !$lid->telefoon_ouder) — @endif
                                </td>
                                <td class="px-4 py-2.5 border-b border-rand/50">
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $lid->actief ? 'bg-accent/15 text-accent' : 'bg-rand text-gedempt' }}">
                                        {{ $lid->actief ? 'Actief' : 'Inactief' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 border-b border-rand/50">
                                    <div class="flex items-center gap-2 justify-end">
                                        <a href="{{ route('scouting.leden.bewerken', $lid) }}" class="text-gedempt hover:text-accent transition-colors text-xs">Bewerken</a>
                                        <form method="post" action="{{ route('scouting.leden.verwijderen', $lid) }}"
                                              onsubmit="return confirm('Lid verwijderen?')">
                                            @csrf @method('DELETE')
                                            <button class="text-gedempt hover:text-fout transition-colors text-xs cursor-pointer">Verwijderen</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>

</x-layouts.portaal>
