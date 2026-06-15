<x-layouts.app :title="$rekening->naam">

    <div class="mb-6">
        <a href="{{ route('rekeningen.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Rekeningen</a>
    </div>

    {{-- Rekening header --}}
    <div class="bg-white rounded-2xl shadow-sm p-6 mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center"
                style="background-color: {{ $rekening->kleur }}1a; border: 2px solid {{ $rekening->kleur }}; color: {{ $rekening->kleur }}">
                <i class="{{ $rekening->icoon }}"></i>
            </div>
            <div>
                <h1 class="text-xl font-semibold text-gray-800">{{ $rekening->naam }}</h1>
                <span class="text-sm text-gray-400 capitalize">{{ $rekening->type }}{{ $rekening->bank ? ' · ' . strtoupper($rekening->bank) : '' }}</span>
            </div>
        </div>
        <div class="text-right">
            <div class="text-2xl font-bold {{ $rekening->saldo >= 0 ? 'text-gray-800' : 'text-red-500' }}">
                € {{ number_format($rekening->saldo, 2, ',', '.') }}
            </div>
            <span class="text-xs text-gray-400">Huidig saldo</span>
        </div>
    </div>

    {{-- Acties --}}
    <div class="flex gap-3 mb-6">
        <a href="{{ route('transacties.aanmaken', ['rekening_id' => $rekening->id]) }}" class="btn-primary">
            + Transactie toevoegen
        </a>
        <a href="{{ route('transacties.importeren') }}?rekening_id={{ $rekening->id }}"
            class="bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 font-medium px-5 py-2 rounded-xl transition-colors text-sm">
            CSV importeren
        </a>
        <a href="{{ route('rekeningen.bewerken', $rekening) }}"
            class="bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 font-medium px-5 py-2 rounded-xl transition-colors text-sm">
            Bewerken
        </a>
    </div>

    {{-- Transacties --}}
    @if($transacties->isEmpty())
        <div class="bg-white rounded-2xl p-10 text-center shadow-sm">
            <p class="text-gray-400 text-sm mb-3">Nog geen transacties voor deze rekening.</p>
            <a href="{{ route('transacties.aanmaken', ['rekening_id' => $rekening->id]) }}" class="btn-primary">Transactie toevoegen</a>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden pt-2">
            <table class="w-full text-sm table-fixed">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th style="width:110px" class="px-6 pt-4 pb-3.5 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Datum</th>
                        <th class="px-6 pt-4 pb-3.5 text-left text-xs font-medium text-gray-400 uppercase tracking-wider max-w-0">Omschrijving</th>
                        <th style="width:140px" class="px-6 pt-4 pb-3.5 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Categorie</th>
                        <th style="width:120px" class="px-6 pt-4 pb-3.5 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Bedrag</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($transacties as $transactie)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 tabular-nums">
                                {{ $transactie->datum->format('d-m-Y') }}
                            </td>
                            <td class="px-6 py-4 text-gray-700 overflow-hidden">
                                <span class="truncate block w-full" title="{{ $transactie->omschrijving }}">
                                    {{ $transactie->omschrijving ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($transactie->category)
                                    <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-lg bg-gray-100 text-gray-600">
                                        {{ $transactie->category->naam }}
                                    </span>
                                @else
                                    <span class="text-gray-300 text-xs">Geen</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap tabular-nums">
                                <span class="font-semibold {{ $transactie->type === 'inkomst' ? 'text-emerald-600' : 'text-red-500' }}">
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
