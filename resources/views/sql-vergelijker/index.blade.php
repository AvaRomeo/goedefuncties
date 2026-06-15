<x-layouts.portaal title="Database structuur vergelijken">

    <div class="max-w-[880px] mx-auto px-4 pt-10 pb-16">

        <h1 class="text-[1.45rem] font-semibold mb-1 tracking-tight text-tekst">Database structuur vergelijken</h1>
        <p class="text-gedempt mb-8">Upload twee phpMyAdmin structuur-exports. De pagina genereert de SQL om <strong>live</strong> gelijk te maken aan <strong>dev</strong>. Er wordt niets verwijderd.</p>

        @if($errors->any())
            <div class="bg-fout/10 border border-fout rounded-lg px-4 py-3 mb-4 text-fout">
                @foreach($errors->all() as $fout)
                    <p>{{ $fout }}</p>
                @endforeach
            </div>
        @endif

        <form class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4" method="post"
              action="{{ route('sql-vergelijker.vergelijk') }}" enctype="multipart/form-data">
            @csrf

            <label class="dropzone bg-paneel border-2 border-dashed border-rand rounded-xl p-6 text-center cursor-pointer transition-colors relative hover:border-accent" id="dz-bron">
                <input type="file" name="bron" accept=".sql,.txt" required class="absolute inset-0 opacity-0 cursor-pointer">
                <span class="font-semibold block mb-1 text-tekst">Dev database</span>
                <span class="text-gedempt text-sm">De lokale export — dit wordt naar live gepusht</span>
                <span class="dz-bestand font-mono text-sm text-accent mt-2 break-all block"></span>
            </label>

            <label class="dropzone bg-paneel border-2 border-dashed border-rand rounded-xl p-6 text-center cursor-pointer transition-colors relative hover:border-accent" id="dz-doel">
                <input type="file" name="doel" accept=".sql,.txt" required class="absolute inset-0 opacity-0 cursor-pointer">
                <span class="font-semibold block mb-1 text-tekst">Live database</span>
                <span class="text-gedempt text-sm">De productie-export — wordt bijgewerkt</span>
                <span class="dz-bestand font-mono text-sm text-accent mt-2 break-all block"></span>
            </label>

            <div class="col-span-full">
                <button type="submit"
                    class="bg-accent text-[#10241a] rounded-lg px-6 py-2.5 font-semibold text-[.95rem] cursor-pointer hover:bg-accent-donker hover:text-white transition-colors">
                    Vergelijk structuren
                </button>
            </div>
        </form>

        @isset($items)
            <div class="flex items-center gap-3 mt-8 mb-4">
                <span class="{{ $aantalWijzigingen === 0 ? 'bg-rand text-tekst' : 'bg-accent text-[#10241a]' }} font-bold rounded-full px-3 py-0.5">
                    {{ $aantalWijzigingen }}
                </span>
                <span class="text-tekst">{{ $aantalWijzigingen === 1 ? 'wijziging nodig' : 'wijzigingen nodig' }} om dev in sync te brengen met live</span>
            </div>

            @if(!empty($items))
                @php
                    $tagConfig = [
                        'tabel'    => ['label' => 'NIEUW',       'class' => 'bg-accent/20 text-accent'],
                        'add'      => ['label' => 'ADD',          'class' => 'bg-accent/20 text-accent'],
                        'modify'   => ['label' => 'MODIFY',       'class' => 'bg-waarschuwing/20 text-waarschuwing'],
                        'only-dev' => ['label' => 'ALLEEN LIVE',  'class' => 'bg-fout/20 text-fout'],
                    ];
                @endphp
                <table class="w-full border-collapse bg-paneel rounded-xl overflow-hidden text-[.9rem]">
                    <thead>
                        <tr>
                            @foreach(['Type', 'Tabel', 'Kolom', 'Omschrijving'] as $th)
                                <th class="text-left px-3 py-2 border-b border-rand text-gedempt font-semibold text-xs uppercase tracking-[.04em]">{{ $th }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td class="px-3 py-2 border-b border-rand">
                                    <span class="text-xs font-bold px-2 py-0.5 rounded {{ $tagConfig[$item['type']]['class'] }}">
                                        {{ $tagConfig[$item['type']]['label'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 border-b border-rand font-mono text-[.85rem] text-tekst">{{ $item['tabel'] }}</td>
                                <td class="px-3 py-2 border-b border-rand font-mono text-[.85rem] text-tekst">{{ $item['kolom'] }}</td>
                                <td class="px-3 py-2 border-b border-rand text-gedempt">{{ $item['omschrijving'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div class="mt-6">
                <div class="flex justify-between items-center gap-2 mb-2">
                    <h2 class="text-base font-semibold text-tekst">Gegenereerde SQL</h2>
                    <div class="flex gap-2">
                        <button type="button" id="kopieer"
                            class="bg-paneel text-tekst border border-rand rounded-md px-3.5 py-1.5 text-[.85rem] cursor-pointer hover:border-accent hover:text-accent transition-colors">
                            Kopieer SQL
                        </button>
                        <form method="post" action="{{ route('sql-vergelijker.vergelijk') }}" class="inline">
                            @csrf
                            <input type="hidden" name="download_sql" value="{{ $syncSql }}">
                            <button type="submit"
                                class="bg-paneel text-tekst border border-rand rounded-md px-3.5 py-1.5 text-[.85rem] cursor-pointer hover:border-accent hover:text-accent transition-colors">
                                Download sync.sql
                            </button>
                        </form>
                    </div>
                </div>
                <textarea id="sql-output" readonly spellcheck="false"
                    class="w-full min-h-[320px] bg-[#14181e] text-tekst border border-rand rounded-xl p-4 font-mono text-[.85rem] leading-relaxed resize-y">{{ $syncSql }}</textarea>
            </div>
        @endisset

    </div>

    <script src="{{ asset('js/sql-vergelijker.js') }}"></script>
</x-layouts.portaal>
