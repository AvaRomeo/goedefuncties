<x-layouts.app title="Categorie toevoegen">

    <div class="mb-6">
        <a href="{{ route('categorieen.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Terug</a>
        <h1 class="text-2xl font-semibold text-gray-800 mt-1">Categorie toevoegen</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 max-w-lg">
        <form action="{{ route('categorieen.opslaan') }}" method="POST" class="flex flex-col gap-4">
            @csrf
            <x-categorie-formulier />
            <button type="submit" class="btn-primary self-start">Opslaan</button>
        </form>
    </div>

</x-layouts.app>
