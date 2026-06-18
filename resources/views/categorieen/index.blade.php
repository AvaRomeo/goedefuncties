<x-layouts.app title="Categorieën">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-tekst">Categorieën</h1>
        <a href="{{ route('categorieen.aanmaken') }}" class="btn-primary">+ Categorie toevoegen</a>
    </div>

    <livewire:budget.categorieen-lijst />

</x-layouts.app>