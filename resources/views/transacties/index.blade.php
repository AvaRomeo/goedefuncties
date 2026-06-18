<x-layouts.app title="Transacties">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-tekst">Transacties</h1>
        <a href="{{ route('transacties.importeren') }}" class="btn-primary">+ Importeren</a>
    </div>

    <livewire:budget.transactie-zoeker />

</x-layouts.app>