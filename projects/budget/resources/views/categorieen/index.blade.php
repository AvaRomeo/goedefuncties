<x-layouts.app title="Categorieën">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Categorieën</h1>
        <a href="{{ route('categorieen.aanmaken') }}" class="btn-primary">+ Categorie toevoegen</a>
    </div>

    @if($categorieen->isEmpty())
        <div class="bg-white rounded-2xl p-10 text-center shadow-sm">
            <p class="text-gray-400 text-sm">Nog geen categorieën aangemaakt.</p>
        </div>
    @else
        @foreach(['uitgave' => 'Uitgaven', 'inkomst' => 'Inkomsten'] as $type => $label)
            @php $groep = $categorieen->where('type', $type); @endphp
            @if($groep->isNotEmpty())
                <h2 class="text-sm font-medium text-gray-400 uppercase tracking-wide mb-3 {{ $loop->first ? '' : 'mt-8' }}">{{ $label }}</h2>
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-2">
                    @foreach($groep as $categorie)
                        <div class="flex items-center gap-4 px-6 py-4 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                style="background-color: {{ $categorie->kleur }}1a; color: {{ $categorie->kleur }}">
                                @if($categorie->icoon)
                                    <i class="{{ $categorie->icoon }} text-xs"></i>
                                @else
                                    <span class="text-xs font-bold">{{ strtoupper(substr($categorie->naam, 0, 1)) }}</span>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-800 text-sm">{{ $categorie->naam }}</p>
                                @if($categorie->trefwoorden)
                                    <p class="text-xs text-gray-400 truncate">{{ implode(', ', $categorie->trefwoorden) }}</p>
                                @endif
                            </div>

                            <div class="flex items-center gap-3 text-sm shrink-0">
                                <a href="{{ route('categorieen.bewerken', $categorie) }}" class="text-indigo-600 hover:underline">Bewerken</a>
                                <form action="{{ route('categorieen.verwijderen', $categorie) }}" method="POST" onsubmit="return confirm('Categorie verwijderen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-danger">Verwijderen</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach
    @endif

</x-layouts.app>
