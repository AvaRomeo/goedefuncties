<div>
    <div class="relative max-w-sm mb-6">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gedempt text-xs"></i>
        <input
            type="text"
            wire:model.live.debounce.200ms="zoek"
            placeholder="Zoek categorie..."
            class="form-input pl-9 pr-8"
        >
        @if($zoek)
            <button wire:click="$set('zoek', '')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gedempt hover:text-tekst text-xs">✕</button>
        @endif
    </div>

    @if($categorieen->isEmpty())
        <div class="bg-paneel border border-rand rounded-2xl p-10 text-center">
            <p class="text-gedempt text-sm">{{ $zoek ? 'Geen categorieën gevonden.' : 'Nog geen categorieën aangemaakt.' }}</p>
        </div>
    @else
        @foreach(['uitgave' => 'Uitgaven', 'inkomst' => 'Inkomsten'] as $type => $label)
            @php $groep = $categorieen->where('type', $type); @endphp
            @if($groep->isNotEmpty())
                <h2 class="text-sm font-medium text-gedempt uppercase tracking-wide mb-3 {{ $loop->first ? '' : 'mt-8' }}">{{ $label }}</h2>
                <div class="bg-paneel border border-rand rounded-2xl overflow-hidden mb-2">
                    @foreach($groep as $categorie)
                        <div class="flex items-center gap-4 px-6 py-4 {{ !$loop->last ? 'border-b border-rand/30' : '' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                style="background-color: {{ $categorie->kleur }}22; color: {{ $categorie->kleur }}">
                                @if($categorie->icoon)
                                    <i class="{{ $categorie->icoon }} text-xs"></i>
                                @else
                                    <span class="text-xs font-bold">{{ strtoupper(substr($categorie->naam, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-tekst text-sm">{{ $categorie->naam }}</p>
                                @if($categorie->trefwoorden)
                                    <p class="text-xs text-gedempt truncate">{{ implode(', ', $categorie->trefwoorden) }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-sm shrink-0">
                                <a href="{{ route('categorieen.bewerken', $categorie) }}" class="text-accent hover:underline">Bewerken</a>
                                <button
                                    wire:click="verwijderen({{ $categorie->id }})"
                                    wire:confirm="Categorie verwijderen?"
                                    class="btn-danger"
                                >Verwijderen</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach
    @endif
</div>
