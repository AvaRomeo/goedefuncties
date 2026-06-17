<x-layouts.app title="Rekeningen">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-tekst">Rekeningen</h1>
        <a href="{{ route('rekeningen.aanmaken') }}" class="btn-primary">
            + Rekening toevoegen
        </a>
    </div>

    @if($rekeningen->isEmpty())
        <div class="bg-paneel border border-rand rounded-2xl p-10 text-center">
            <p class="text-gedempt text-sm">Nog geen rekeningen aangemaakt.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($rekeningen as $rekening)
                <div class="bg-paneel border border-rand rounded-2xl flex flex-col hover:border-accent/40 transition-colors">
                    <a href="{{ route('rekeningen.tonen', $rekening) }}" class="flex flex-col gap-4 p-6 flex-1">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $rekening->kleur }}22; border: 2px solid {{ $rekening->kleur }}; color: {{ $rekening->kleur }}">
                                <i class="{{ $rekening->icoon }}"></i>
                            </div>
                            <div>
                                <h2 class="font-semibold text-tekst">{{ $rekening->naam }}</h2>
                                <span class="text-xs text-gedempt capitalize">{{ $rekening->type }}{{ $rekening->bank ? ' · ' . strtoupper($rekening->bank) : '' }}</span>
                            </div>
                        </div>
                        <div class="text-2xl font-bold {{ $rekening->saldo >= 0 ? 'text-tekst' : 'text-fout' }}">
                            € {{ number_format($rekening->saldo, 2, ',', '.') }}
                        </div>
                    </a>
                    <div class="flex gap-2 text-sm px-6 py-3 border-t border-rand">
                        <a href="{{ route('rekeningen.bewerken', $rekening) }}" class="text-accent hover:underline">Bewerken</a>
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
