<div>
    <div class="sc-page-header mb-6">
        <div class="sc-page-header-icoon">👥</div>
        <div>
            <h1>Leden</h1>
            <p>{{ $leden->count() }} {{ $leden->count() === 1 ? 'lid' : 'leden' }}</p>
        </div>
        <div class="flex gap-2 ml-auto">
            <input
                type="text"
                wire:model="naam"
                wire:keydown.enter="toevoegen"
                placeholder="Naam nieuw lid…"
                class="bg-paneel border border-rand rounded-lg px-3 py-2 text-tekst text-sm outline-none focus:border-accent transition-colors w-48"
            >
            <button
                wire:click="toevoegen"
                class="bg-accent text-[#10241a] rounded-lg px-4 py-2 font-semibold text-sm hover:opacity-90 transition-opacity cursor-pointer whitespace-nowrap"
            >+ Toevoegen</button>
        </div>
    </div>

    @error('naam')
        <div class="bg-fout/10 border border-fout/30 rounded-lg px-4 py-3 mb-5 text-fout text-sm">
            {{ $message }}
        </div>
    @enderror

    @if($leden->isEmpty())
        <p class="text-gedempt text-sm">Nog geen leden. Vul een naam in en klik op Toevoegen.</p>
    @else
        <div class="rounded-xl border border-rand overflow-hidden">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-[#1a1f28]">
                        <th class="text-left px-4 py-2.5 text-gedempt font-semibold text-xs uppercase tracking-wide">Naam</th>
                        <th class="px-4 py-2.5 w-20"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leden as $lid)
                        <tr class="odd:bg-paneel even:bg-[#1e242d] hover:bg-rand/40 transition-colors">
                            <td class="px-4 py-2.5 border-b border-rand/50 text-tekst font-medium">{{ $lid->naam }}</td>
                            <td class="px-4 py-2.5 border-b border-rand/50">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="{{ route('scouting.leden.bewerken', $lid) }}" class="text-gedempt hover:text-accent transition-colors text-xs">Bewerken</a>
                                    <button
                                        wire:click="verwijderen({{ $lid->id }})"
                                        wire:confirm="Lid verwijderen?"
                                        class="text-gedempt hover:text-fout transition-colors text-xs cursor-pointer"
                                    >Verwijderen</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
