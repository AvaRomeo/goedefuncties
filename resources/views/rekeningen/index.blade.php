<x-layouts.app title="Rekeningen">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-tekst">Rekeningen</h1>
        <a href="{{ route('rekeningen.aanmaken') }}" class="btn-primary">
            + Rekening toevoegen
        </a>
    </div>

    <livewire:budget.rekeningen-lijst />

</x-layouts.app>