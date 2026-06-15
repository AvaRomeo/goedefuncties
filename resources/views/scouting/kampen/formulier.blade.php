<x-layouts.portaal title="{{ $kamp->exists ? 'Kamp bewerken' : 'Kamp toevoegen' }} — Scouting">

    <div class="max-w-[600px] mx-auto px-4 pt-10 pb-16">

        <a href="{{ route('scouting.kampen.index') }}" class="text-gedempt text-sm hover:text-accent transition-colors mb-6 inline-flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Terug naar kampen
        </a>

        <h1 class="text-[1.45rem] font-semibold tracking-tight text-tekst mb-6">
            {{ $kamp->exists ? 'Kamp bewerken' : 'Kamp toevoegen' }}
        </h1>

        @if($errors->any())
            <div class="bg-fout/10 border border-fout rounded-lg px-4 py-3 mb-5 text-fout text-sm">
                @foreach($errors->all() as $fout)<p>{{ $fout }}</p>@endforeach
            </div>
        @endif

        <form method="post"
              action="{{ $kamp->exists ? route('scouting.kampen.bijwerken', $kamp) : route('scouting.kampen.opslaan') }}">
            @csrf
            @if($kamp->exists) @method('PUT') @endif

            <div class="flex flex-col gap-5">

                <div>
                    <label class="block text-sm text-gedempt mb-1.5">Naam *</label>
                    <input type="text" name="naam" value="{{ old('naam', $kamp->naam) }}" required
                           placeholder="bijv. Zomerkamp 2025"
                           class="w-full bg-paneel border border-rand rounded-lg px-3 py-2.5 text-tekst text-sm outline-none focus:border-accent transition-colors">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gedempt mb-1.5">Startdatum *</label>
                        <input type="date" name="start_datum" value="{{ old('start_datum', $kamp->start_datum?->format('Y-m-d')) }}" required
                               class="w-full bg-paneel border border-rand rounded-lg px-3 py-2.5 text-tekst text-sm outline-none focus:border-accent transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm text-gedempt mb-1.5">Einddatum *</label>
                        <input type="date" name="eind_datum" value="{{ old('eind_datum', $kamp->eind_datum?->format('Y-m-d')) }}" required
                               class="w-full bg-paneel border border-rand rounded-lg px-3 py-2.5 text-tekst text-sm outline-none focus:border-accent transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gedempt mb-1.5">Locatie</label>
                        <input type="text" name="locatie" value="{{ old('locatie', $kamp->locatie) }}"
                               class="w-full bg-paneel border border-rand rounded-lg px-3 py-2.5 text-tekst text-sm outline-none focus:border-accent transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm text-gedempt mb-1.5">Prijs (€)</label>
                        <input type="number" name="prijs" step="0.01" min="0"
                               value="{{ old('prijs', $kamp->prijs) }}"
                               class="w-full bg-paneel border border-rand rounded-lg px-3 py-2.5 text-tekst text-sm outline-none focus:border-accent transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-gedempt mb-1.5">Beschrijving</label>
                    <textarea name="beschrijving" rows="4"
                              class="w-full bg-paneel border border-rand rounded-lg px-3 py-2.5 text-tekst text-sm outline-none focus:border-accent transition-colors resize-none">{{ old('beschrijving', $kamp->beschrijving) }}</textarea>
                </div>

            </div>

            <div class="flex gap-3 mt-8">
                <button type="submit"
                        class="bg-accent text-[#10241a] rounded-lg px-6 py-2.5 font-semibold text-sm hover:opacity-90 transition-opacity cursor-pointer">
                    {{ $kamp->exists ? 'Opslaan' : 'Kamp toevoegen' }}
                </button>
                <a href="{{ route('scouting.kampen.index') }}"
                   class="border border-rand text-gedempt rounded-lg px-6 py-2.5 text-sm hover:border-accent hover:text-accent transition-colors">
                    Annuleren
                </a>
            </div>

        </form>
    </div>

</x-layouts.portaal>
