<x-layouts.app :title="$rekening->naam">

    <div class="mb-6">
        <a href="{{ route('rekeningen.index') }}" class="text-sm text-gedempt hover:text-tekst">← Rekeningen</a>
    </div>

    {{-- Rekening header --}}
    <div class="bg-paneel border border-rand rounded-2xl p-6 mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center"
                style="background-color: {{ $rekening->kleur }}22; border: 2px solid {{ $rekening->kleur }}; color: {{ $rekening->kleur }}">
                <i class="{{ $rekening->icoon }}"></i>
            </div>
            <div>
                <h1 class="text-xl font-semibold text-tekst">{{ $rekening->naam }}</h1>
                <span class="text-sm text-gedempt capitalize">{{ $rekening->type }}{{ $rekening->bank ? ' · ' . strtoupper($rekening->bank) : '' }}</span>
            </div>
        </div>
        <div class="text-right">
            <div class="text-2xl font-bold {{ $rekening->saldo >= 0 ? 'text-tekst' : 'text-fout' }}">
                € {{ number_format($rekening->saldo, 2, ',', '.') }}
            </div>
            <span class="text-xs text-gedempt">Huidig saldo</span>
        </div>
    </div>

    {{-- Acties --}}
    <div class="flex gap-3 mb-6 flex-wrap">
        <a href="{{ route('transacties.aanmaken', ['rekening_id' => $rekening->id]) }}" class="btn-primary">
            + Transactie toevoegen
        </a>
        <a href="{{ route('transacties.importeren') }}?rekening_id={{ $rekening->id }}"
            class="bg-paneel border border-rand text-gedempt hover:text-tekst hover:border-accent/40 font-medium px-5 py-2 rounded-xl transition-colors text-sm">
            CSV importeren
        </a>
        <a href="{{ route('rekeningen.bewerken', $rekening) }}"
            class="bg-paneel border border-rand text-gedempt hover:text-tekst hover:border-accent/40 font-medium px-5 py-2 rounded-xl transition-colors text-sm">
            Bewerken
        </a>
    </div>

    {{-- Transacties --}}
    @if($transacties->isEmpty())
        <div class="bg-paneel border border-rand rounded-2xl p-10 text-center">
            <p class="text-gedempt text-sm mb-3">Nog geen transacties voor deze rekening.</p>
            <a href="{{ route('transacties.aanmaken', ['rekening_id' => $rekening->id]) }}" class="btn-primary">Transactie toevoegen</a>
        </div>
    @else
        <div class="bg-paneel border border-rand rounded-2xl overflow-hidden pt-2">
            <table class="w-full text-sm table-fixed">
                <thead>
                    <tr class="border-b border-rand">
                        <th style="width:110px" class="px-6 pt-4 pb-3.5 text-left text-xs font-medium text-gedempt uppercase tracking-wider">Datum</th>
                        <th class="px-6 pt-4 pb-3.5 text-left text-xs font-medium text-gedempt uppercase tracking-wider max-w-0">Omschrijving</th>
                        <th style="width:140px" class="px-6 pt-4 pb-3.5 text-left text-xs font-medium text-gedempt uppercase tracking-wider">Categorie</th>
                        <th style="width:120px" class="px-6 pt-4 pb-3.5 text-right text-xs font-medium text-gedempt uppercase tracking-wider">Bedrag</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transacties as $transactie)
                        <tr class="border-b border-rand/30 hover:bg-rand/10 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gedempt tabular-nums">
                                {{ $transactie->datum->format('d-m-Y') }}
                            </td>
                            <td class="px-6 py-4 text-tekst overflow-hidden">
                                <span class="truncate block w-full" title="{{ $transactie->omschrijving }}">
                                    {{ $transactie->omschrijving ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($transactie->category)
                                    <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-lg bg-rand/40 text-gedempt">
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
