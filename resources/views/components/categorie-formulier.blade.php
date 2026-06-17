@props(['categorie' => null])

<div>
    <label class="form-label" for="naam">Naam</label>
    <input type="text" name="naam" id="naam" class="form-input {{ $errors->has('naam') ? 'form-input-error' : '' }}"
        value="{{ old('naam', $categorie?->naam) }}" required>
    @error('naam') <p class="form-error">{{ $message }}</p> @enderror
</div>

<div>
    <label class="form-label" for="type">Type</label>
    <select name="type" id="type" class="form-input" required>
        <option value="uitgave" {{ old('type', $categorie?->type) === 'uitgave' ? 'selected' : '' }}>Uitgave</option>
        <option value="inkomst" {{ old('type', $categorie?->type) === 'inkomst' ? 'selected' : '' }}>Inkomst</option>
    </select>
</div>

<div>
    <label class="form-label" for="kleur">Kleur</label>
    <div class="flex items-center gap-3">
        <input type="color" name="kleur" id="kleur" value="{{ old('kleur', $categorie?->kleur ?? '#6366f1') }}"
            class="h-10 w-16 rounded-xl border border-rand cursor-pointer p-1 bg-paneel">
        <span class="text-xs text-gedempt">Kies een kleur voor deze categorie</span>
    </div>
</div>

<div>
    <label class="form-label" for="icoon">Icoon <span class="text-gedempt font-normal">(Font Awesome klasse, optioneel)</span></label>
    <input type="text" name="icoon" id="icoon" class="form-input"
        value="{{ old('icoon', $categorie?->icoon) }}" placeholder="fa-solid fa-cart-shopping">
</div>

<div>
    <label class="form-label" for="trefwoorden">Trefwoorden voor automatisch categoriseren</label>
    <input type="text" name="trefwoorden" id="trefwoorden" class="form-input"
        value="{{ old('trefwoorden', $categorie?->trefwoorden ? implode(', ', $categorie->trefwoorden) : '') }}"
        placeholder="lidl, albert heijn, ah, jumbo">
    <p class="text-xs text-gedempt mt-1">Komma-gescheiden. Als een omschrijving een trefwoord bevat, wordt deze categorie automatisch toegewezen bij import.</p>
</div>
