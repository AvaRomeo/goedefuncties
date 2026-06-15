@props(['rekening' => null])

@php
    $kleuren = [
        '#6366f1', '#3b82f6', '#06b6d4', '#10b981',
        '#22c55e', '#eab308', '#f97316', '#ef4444',
        '#ec4899', '#a855f7', '#6b7280', '#1e293b',
    ];

    $iconen = [
        'fa-solid fa-building-columns',
        'fa-solid fa-credit-card',
        'fa-solid fa-coins',
        'fa-solid fa-money-bill-wave',
        'fa-solid fa-piggy-bank',
        'fa-solid fa-wallet',
        'fa-solid fa-landmark',
        'fa-solid fa-house',
        'fa-solid fa-car',
        'fa-solid fa-plane',
        'fa-solid fa-globe',
        'fa-solid fa-star',
        'fa-solid fa-heart',
        'fa-solid fa-graduation-cap',
        'fa-solid fa-briefcase',
        'fa-solid fa-cart-shopping',
    ];

    $huidigKleur = old('kleur', $rekening?->kleur ?? '#6366f1');
    $huidigIcoon = old('icoon', $rekening?->icoon ?? 'fa-solid fa-building-columns');
@endphp

<div>
    <label class="form-label">Naam</label>
    <input type="text" name="naam" value="{{ old('naam', $rekening?->naam) }}"
        placeholder="bijv. Betaalrekening"
        class="form-input @error('naam') form-input-error @enderror">
    @error('naam') <p class="form-error">{{ $message }}</p> @enderror
</div>

<div>
    <label class="form-label">Type</label>
    <select name="type" class="form-input">
        <option value="betaal" {{ old('type', $rekening?->type) == 'betaal' ? 'selected' : '' }}>Betaalrekening</option>
        <option value="spaar"  {{ old('type', $rekening?->type) == 'spaar'  ? 'selected' : '' }}>Spaarrekening</option>
        <option value="overig" {{ old('type', $rekening?->type) == 'overig' ? 'selected' : '' }}>Overig</option>
    </select>
</div>

<div>
    <label class="form-label">Bank</label>
    <select name="bank" class="form-input">
        <option value="">— Geen / overig —</option>
        <option value="bunq"     {{ old('bank', $rekening?->bank) == 'bunq'     ? 'selected' : '' }}>Bunq</option>
        <option value="ing"      {{ old('bank', $rekening?->bank) == 'ing'      ? 'selected' : '' }}>ING</option>
        <option value="rabobank" {{ old('bank', $rekening?->bank) == 'rabobank' ? 'selected' : '' }}>Rabobank</option>
        <option value="abn"      {{ old('bank', $rekening?->bank) == 'abn'      ? 'selected' : '' }}>ABN AMRO</option>
        <option value="sns"      {{ old('bank', $rekening?->bank) == 'sns'      ? 'selected' : '' }}>SNS Bank</option>
        <option value="asn"      {{ old('bank', $rekening?->bank) == 'asn'      ? 'selected' : '' }}>ASN Bank</option>
        <option value="triodos"  {{ old('bank', $rekening?->bank) == 'triodos'  ? 'selected' : '' }}>Triodos</option>
        <option value="revolut"  {{ old('bank', $rekening?->bank) == 'revolut'  ? 'selected' : '' }}>Revolut</option>
        <option value="n26"      {{ old('bank', $rekening?->bank) == 'n26'      ? 'selected' : '' }}>N26</option>
    </select>
</div>

<div>
    <label class="form-label">Kleur</label>
    <input type="hidden" name="kleur" id="kleur-input" value="{{ $huidigKleur }}">
    <div class="flex flex-wrap gap-2">
        @foreach($kleuren as $kleur)
            <button type="button"
                data-kleur="{{ $kleur }}"
                class="kleur-knop w-8 h-8 rounded-full border-2 transition-all {{ $huidigKleur === $kleur ? 'border-gray-800 scale-110' : 'border-transparent' }}"
                style="background-color: {{ $kleur }}">
            </button>
        @endforeach
    </div>
</div>

<div>
    <label class="form-label">Icoon</label>
    <input type="hidden" name="icoon" id="icoon-input" value="{{ $huidigIcoon }}">
    <div class="flex flex-wrap gap-2">
        @foreach($iconen as $icoon)
            <button type="button"
                data-icoon="{{ $icoon }}"
                class="icoon-knop w-10 h-10 rounded-xl flex items-center justify-center border-2 transition-all {{ $huidigIcoon === $icoon ? 'border-indigo-500 bg-indigo-50 text-indigo-600' : 'border-transparent bg-gray-100 hover:bg-gray-200 text-gray-600' }}">
                <i class="{{ $icoon }}"></i>
            </button>
        @endforeach
    </div>
</div>

<div>
    <label class="form-label">Beginsaldo (€)</label>
    <input type="number" name="beginsaldo" step="0.01"
        value="{{ old('beginsaldo', $rekening?->beginsaldo ?? '0') }}"
        class="form-input @error('beginsaldo') form-input-error @enderror">
    @error('beginsaldo') <p class="form-error">{{ $message }}</p> @enderror
</div>
