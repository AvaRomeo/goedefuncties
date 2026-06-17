<x-layouts.app title="Transacties">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-tekst">Transacties</h1>
        <a href="{{ route('transacties.importeren') }}" class="btn-primary">+ Importeren</a>
    </div>

    <form method="GET" action="{{ route('transacties.index') }}" class="mb-4">
        <div class="relative max-w-sm">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gedempt text-xs"></i>
            <input type="text" name="zoek" value="{{ $zoek }}"
                placeholder="Zoek in omschrijving..."
                class="form-input pl-9 pr-8">
            @if($zoek)
                <a href="{{ route('transacties.index') }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-gedempt hover:text-tekst text-xs">✕</a>
            @endif
        </div>
    </form>

    @if($transacties->isEmpty())
        <div class="bg-paneel border border-rand rounded-2xl p-10 text-center">
            <p class="text-gedempt text-sm mb-3">Nog geen transacties.</p>
            <a href="{{ route('transacties.importeren') }}" class="btn-primary">CSV importeren</a>
        </div>
    @else
        <div class="bg-paneel border border-rand rounded-2xl overflow-hidden pt-2">
            <table class="w-full text-sm table-fixed">
                <thead>
                    <tr class="border-b border-rand">
                        <th style="width:110px" class="px-6 pt-6 pb-3.5 text-left text-xs font-medium text-gedempt uppercase tracking-wider">Datum</th>
                        <th style="width:180px" class="px-6 pt-6 pb-3.5 text-left text-xs font-medium text-gedempt uppercase tracking-wider">Rekening</th>
                        <th class="px-6 pt-6 pb-3.5 text-left text-xs font-medium text-gedempt uppercase tracking-wider max-w-0">Omschrijving</th>
                        <th style="width:130px" class="px-6 pt-6 pb-3.5 text-left text-xs font-medium text-gedempt uppercase tracking-wider">Categorie</th>
                        <th style="width:120px" class="px-6 pt-6 pb-3.5 text-right text-xs font-medium text-gedempt uppercase tracking-wider">Bedrag</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transacties as $transactie)
                        <tr class="border-b border-rand/30 hover:bg-rand/10 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gedempt tabular-nums">
                                {{ $transactie->datum->format('d-m-Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-lg"
                                    style="background-color: {{ $transactie->account->kleur }}22; color: {{ $transactie->account->kleur }}">
                                    <i class="{{ $transactie->account->icoon }} text-[10px]"></i>
                                    {{ $transactie->account->naam }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-tekst overflow-hidden">
                                <span class="truncate block w-full" title="{{ $transactie->omschrijving }}">{{ $transactie->omschrijving ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($transactie->category)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-lg bg-rand/40 text-gedempt">
                                        {{ $transactie->category->naam }}
                                    </span>
                                @else
                                    <span class="text-gedempt/40 text-xs">Geen</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap tabular-nums">
                                <span class="font-semibold {{ $transactie->type === 'inkomst' ? 'text-emerald-400' : 'text-fout' }}">
                                    {{ $transactie->type === 'inkomst' ? '+' : '−' }} € {{ number_format($transactie->bedrag, 2, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $transacties->links() }}
        </div>
    @endif

</x-layouts.app>
