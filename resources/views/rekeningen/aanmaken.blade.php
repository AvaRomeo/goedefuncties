<x-layouts.app title="Rekening toevoegen">

    <div class="mb-6">
        <a href="{{ route('rekeningen.index') }}" class="text-sm text-gedempt hover:text-tekst">← Terug</a>
        <h1 class="text-2xl font-semibold text-tekst mt-1">Rekening toevoegen</h1>
    </div>

    <div class="bg-paneel border border-rand rounded-2xl p-6 max-w-lg">
        <form action="{{ route('rekeningen.opslaan') }}" method="POST" class="flex flex-col gap-4">
            @csrf
            <x-rekening-formulier />
            <button type="submit" class="btn-primary self-start">
                Opslaan
            </button>
        </form>
    </div>

</x-layouts.app>
