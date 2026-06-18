<x-layouts.portaal title="Leden — Scouting">

    <div class="max-w-2xl mx-auto px-4 pt-10 pb-16">

        <a href="{{ route('scouting.home') }}" class="sc-terug">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Terug naar Scouting
        </a>

        <livewire:scouting.leden-lijst />

    </div>

</x-layouts.portaal>