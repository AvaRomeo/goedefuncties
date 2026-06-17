<x-layouts.portaal title="{{ $lid->exists ? 'Lid bewerken' : 'Lid toevoegen' }} — Scouting">

    <div class="max-w-[600px] mx-auto px-4 pt-10 pb-16">

        <a href="{{ route('scouting.leden.index') }}" class="text-gedempt text-sm hover:text-accent transition-colors mb-6 inline-flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Terug naar leden
        </a>

        <h1 class="text-[1.45rem] font-semibold tracking-tight text-tekst mb-6">
            {{ $lid->exists ? 'Lid bewerken' : 'Lid toevoegen' }}
        </h1>

        @if($errors->any())
            <div class="bg-fout/10 border border-fout rounded-lg px-4 py-3 mb-5 text-fout text-sm">
                @foreach($errors->all() as $fout)<p>{{ $fout }}</p>@endforeach
            </div>
        @endif

        <form method="post"
              action="{{ $lid->exists ? route('scouting.leden.bijwerken', $lid) : route('scouting.leden.opslaan') }}">
            @csrf
            @if($lid->exists) @method('PUT') @endif

            <div class="flex flex-col gap-5">

                <div>
                    <label class="block text-sm text-gedempt mb-1.5">Naam *</label>
                    <input type="text" name="naam" value="{{ old('naam', $lid->naam) }}" required autofocus
                           class="w-full bg-paneel border border-rand rounded-lg px-3 py-2.5 text-tekst text-sm outline-none focus:border-accent transition-colors">
                </div>

            </div>

            <div class="flex gap-3 mt-8">
                <button type="submit"
                        class="bg-accent text-[#10241a] rounded-lg px-6 py-2.5 font-semibold text-sm hover:opacity-90 transition-opacity cursor-pointer">
                    {{ $lid->exists ? 'Opslaan' : 'Lid toevoegen' }}
                </button>
                <a href="{{ route('scouting.leden.index') }}"
                   class="border border-rand text-gedempt rounded-lg px-6 py-2.5 text-sm hover:border-accent hover:text-accent transition-colors">
                    Annuleren
                </a>
            </div>

        </form>
    </div>

</x-layouts.portaal>
