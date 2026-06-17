<x-layouts.app title="Categorie bewerken">

    <div class="mb-6">
        <a href="{{ route('categorieen.index') }}" class="text-sm text-gedempt hover:text-tekst">← Terug</a>
        <h1 class="text-2xl font-semibold text-tekst mt-1">Categorie bewerken</h1>
    </div>

    <div class="bg-paneel border border-rand rounded-2xl p-6 max-w-lg">
        <form action="{{ route('categorieen.bijwerken', $categorie) }}" method="POST" class="flex flex-col gap-4">
            @csrf
            @method('PUT')
            <x-categorie-formulier :categorie="$categorie" />
            <button type="submit" class="btn-primary self-start">Opslaan</button>
        </form>
    </div>

</x-layouts.app>
