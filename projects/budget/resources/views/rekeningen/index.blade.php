<x-layouts.app title="Rekeningen">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Rekeningen</h1>
        <a href="{{ route('rekeningen.aanmaken') }}" class="btn-primary">
            + Rekening toevoegen
        </a>
    </div>

    @if($rekeningen->isEmpty())
        <div class="bg-white rounded-2xl p-10 text-center shadow-sm">
            <p class="text-gray-400 text-sm">Nog geen rekeningen aangemaakt.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($rekeningen as $rekening)
                <div class="bg-white rounded-2xl p-6 shadow-sm flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $rekening->kleur }}1a; border: 2px solid {{ $rekening->kleur }}; color: {{ $rekening->kleur }}">
                            <i class="{{ $rekening->icoon }}"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-gray-800">{{ $rekening->naam }}</h2>
                            <span class="text-xs text-gray-400 capitalize">{{ $rekening->type }}{{ $rekening->bank ? ' · ' . strtoupper($rekening->bank) : '' }}</span>
                        </div>
                    </div>

                    <div class="text-2xl font-bold {{ $rekening->saldo >= 0 ? 'text-gray-800' : 'text-red-500' }}">
                        € {{ number_format($rekening->saldo, 2, ',', '.') }}
                    </div>

                    <div class="flex gap-2 text-sm pt-2 border-t border-gray-100">
                        <a href="{{ route('rekeningen.bewerken', $rekening) }}" class="text-indigo-600 hover:underline">Bewerken</a>
                        <form action="{{ route('rekeningen.verwijderen', $rekening) }}" method="POST" onsubmit="return confirm('Rekening verwijderen?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn-danger">Verwijderen</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</x-layouts.app>
