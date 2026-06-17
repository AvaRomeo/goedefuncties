<x-layouts.app title="Transactie toevoegen">

    <div class="mb-6">
        <a href="{{ $rekening_id ? route('rekeningen.tonen', $rekening_id) : route('transacties.index') }}" class="text-sm text-gedempt hover:text-tekst">← Terug</a>
        <h1 class="text-2xl font-semibold text-tekst mt-1">Transactie toevoegen</h1>
    </div>

    <div class="bg-paneel border border-rand rounded-2xl p-6 max-w-lg">
        <form action="{{ route('transacties.opslaan') }}" method="POST" class="flex flex-col gap-4">
            @csrf
            <input type="hidden" name="terug_rekening_id" value="{{ $rekening_id }}">

            <div>
                <label class="form-label" for="account_id">Rekening</label>
                <select name="account_id" id="account_id" class="form-input" required>
                    <option value="">— Kies een rekening —</option>
                    @foreach($rekeningen as $rekening)
                        <option value="{{ $rekening->id }}" {{ old('account_id', $rekening_id) == $rekening->id ? 'selected' : '' }}>
                            {{ $rekening->naam }}
                        </option>
                    @endforeach
                </select>
                @error('account_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label" for="type">Type</label>
                    <select name="type" id="type" class="form-input" required>
                        <option value="uitgave" {{ old('type') === 'uitgave' ? 'selected' : '' }}>Uitgave</option>
                        <option value="inkomst" {{ old('type') === 'inkomst' ? 'selected' : '' }}>Inkomst</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="bedrag">Bedrag (€)</label>
                    <input type="number" name="bedrag" id="bedrag" step="0.01" min="0.01"
                        class="form-input {{ $errors->has('bedrag') ? 'form-input-error' : '' }}"
                        value="{{ old('bedrag') }}" required placeholder="0,00">
                    @error('bedrag') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="form-label" for="datum">Datum</label>
                <input type="date" name="datum" id="datum"
                    class="form-input {{ $errors->has('datum') ? 'form-input-error' : '' }}"
                    value="{{ old('datum', date('Y-m-d')) }}" required>
                @error('datum') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label" for="omschrijving">Omschrijving <span class="text-gedempt font-normal">(optioneel)</span></label>
                <input type="text" name="omschrijving" id="omschrijving" class="form-input"
                    value="{{ old('omschrijving') }}" placeholder="Bijv. Boodschappen Lidl">
            </div>

            <div>
                <label class="form-label" for="category_id">Categorie <span class="text-gedempt font-normal">(optioneel)</span></label>
                <select name="category_id" id="category_id" class="form-input">
                    <option value="">— Geen categorie —</option>
                    @foreach($categorieen->where('type', 'uitgave') as $cat)
                        @if($loop->first) <option disabled class="text-gedempt">── Uitgaven ──</option> @endif
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->naam }}</option>
                    @endforeach
                    @foreach($categorieen->where('type', 'inkomst') as $cat)
                        @if($loop->first) <option disabled class="text-gedempt">── Inkomsten ──</option> @endif
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->naam }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-2 border-t border-rand">
                <button type="submit" class="btn-primary">Opslaan</button>
            </div>
        </form>
    </div>

</x-layouts.app>
