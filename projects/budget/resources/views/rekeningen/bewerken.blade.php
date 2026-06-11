<x-layouts.app title="Rekening bewerken">

    <div class="mb-6">
        <a href="{{ route('rekeningen.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Terug</a>
        <h1 class="text-2xl font-semibold text-gray-800 mt-1">Rekening bewerken</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 max-w-lg">
        <form action="{{ route('rekeningen.bijwerken', $rekening) }}" method="POST" class="flex flex-col gap-4">
            @csrf
            @method('PUT')
            <x-rekening-formulier :rekening="$rekening" />
            <button type="submit" class="btn-primary self-start">
                Opslaan
            </button>
        </form>
    </div>

</x-layouts.app>
