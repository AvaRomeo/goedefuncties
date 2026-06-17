<x-layouts.portaal title="SQL Data extractor">

    {{-- Banner --}}
    <div class="sql-banner">
        <div class="sql-banner-top">
            <div class="sql-banner-icoon">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 30 30">
                    <ellipse cx="15" cy="7" rx="10" ry="4"/>
                    <path d="M5 7v6c0 2.21 4.48 4 10 4s10-1.79 10-4V7"/>
                    <path d="M5 13v6c0 2.21 4.48 4 10 4s10-1.79 10-4v-6"/>
                </svg>
            </div>
            <div class="sql-banner-tekst">
                <div class="sql-banner-titel">SQL Data extractor</div>
                <div class="sql-banner-sub">Upload een SQL-dump, kies een tabel en bekijk de inhoud.</div>
            </div>
        </div>

        {{-- Server-racks illustratie ------------------------------------ --}}
        @php
            $racks = [
                ['h'=>80, 'slots'=>[[0,'aan'],[1,''],[2,''],[3,'aan'],[4,''],[5,'aan']]],
                ['h'=>90, 'slots'=>[[0,''],[1,'aan'],[2,'aan'],[3,''],[4,'aan'],[5,''],[6,'aan']]],
                ['h'=>60, 'slots'=>[[0,'aan'],[1,''],[2,'aan'],[3,'']]],
                ['h'=>85, 'slots'=>[[0,''],[1,'aan'],[2,''],[3,'aan'],[4,''],[5,''],[6,'aan']]],
                ['h'=>70, 'slots'=>[[0,'aan'],[1,'aan'],[2,''],[3,''],[4,'aan']]],
                ['h'=>55, 'slots'=>[[0,''],[1,'aan'],[2,''],[3,'']]],
                ['h'=>88, 'slots'=>[[0,'aan'],[1,''],[2,'aan'],[3,''],[4,''],[5,'aan'],[6,'']]],
            ];
        @endphp
        <div class="sql-racks" aria-hidden="true">
            @foreach($racks as $rack)
                <div class="sql-rack" style="height:{{ $rack['h'] }}px">
                    @foreach($rack['slots'] as [$idx, $aan])
                        <div class="sql-slot {{ $aan }}"></div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    <div class="max-w-[900px] mx-auto px-4 pb-16" style="position:relative;z-index:1">

        @if($errors->any())
            <div class="bg-fout/10 border border-fout rounded-lg px-4 py-3 mb-5 text-fout text-sm">
                @foreach($errors->all() as $fout)<p>{{ $fout }}</p>@endforeach
            </div>
        @endif

        {{-- Upload form --}}
        <form method="post" action="{{ route('sql-data.uploaden') }}" enctype="multipart/form-data"
              class="{{ $tabellen ? 'flex gap-3 items-center mb-6' : 'mb-6' }}">
            @csrf
            @if($tabellen)
                {{-- Compacte versie als er al een bestand is --}}
                <label class="flex-1 bg-paneel border border-rand rounded-lg px-4 py-2.5 flex items-center gap-3 cursor-pointer hover:border-accent transition-colors relative">
                    <input type="file" name="dump" accept=".sql,.txt" required class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                    <svg class="w-4 h-4 text-gedempt shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    <span class="text-sm text-gedempt">
                        Huidig bestand: <span class="text-accent font-mono">{{ $bestandsnaam }}</span>
                        — klik om te vervangen
                    </span>
                    <span class="dz-bestand font-mono text-sm text-accent ml-auto break-all hidden"></span>
                </label>
                <button type="submit"
                    class="bg-accent text-[#10241a] rounded-lg px-5 py-2.5 font-semibold text-sm cursor-pointer hover:opacity-90 transition-opacity whitespace-nowrap shrink-0">
                    Nieuw bestand
                </button>
            @else
                {{-- Uitgebreide versie bij eerste upload --}}
                <label class="dropzone bg-paneel border-2 border-dashed border-rand rounded-xl p-8 flex flex-col items-center gap-2 text-center cursor-pointer transition-colors hover:border-accent relative mb-4">
                    <input type="file" name="dump" accept=".sql,.txt" required class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                    <svg class="w-8 h-8 text-gedempt mb-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    <span class="font-semibold text-tekst">Klik of sleep een SQL-bestand hier naartoe</span>
                    <span class="text-gedempt text-sm">.sql of .txt — max 100 MB</span>
                    <span class="dz-bestand font-mono text-sm text-accent mt-1 break-all block"></span>
                </label>
                <button type="submit"
                    class="bg-accent text-[#10241a] rounded-lg px-6 py-2.5 font-semibold text-[.95rem] cursor-pointer hover:opacity-90 transition-opacity">
                    Bestand inlezen
                </button>
            @endif
        </form>

        @if($tabellen)
            {{-- Tabellen-lijst --}}
            <div class="mb-8">
                <p class="text-gedempt text-sm mb-3">{{ count($tabellen) }} {{ count($tabellen) === 1 ? 'tabel' : 'tabellen' }} gevonden</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($tabellen as $naam => $aantalInserts)
                        @php $actief = $tabel === $naam; @endphp
                        <a href="{{ route('sql-data.index', ['tabel' => $naam]) }}"
                           class="{{ $actief
                               ? 'bg-accent text-[#10241a] border-accent'
                               : 'bg-paneel text-tekst border-rand hover:border-accent hover:text-accent' }}
                               border rounded-lg px-3 py-1.5 text-sm font-mono transition-colors">
                            {{ $naam }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Data tabel --}}
            @if($tabel)
                <div class="border-t border-rand pt-6">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div class="flex items-baseline gap-3">
                            <h2 class="text-base font-semibold text-tekst">{{ $tabel }}</h2>
                            @if($totaalRijen > 0)
                                @php $van = ($pagina - 1) * $perPagina + 1; $tot = min($pagina * $perPagina, $totaalRijen); @endphp
                                <span class="text-gedempt text-sm">
                                    {{ number_format($van) }}–{{ number_format($tot) }} van {{ number_format($totaalRijen) }} {{ $totaalRijen === 1 ? 'rij' : 'rijen' }}
                                </span>
                            @endif
                        </div>
                        @if($totaalRijen > 0)
                            <button id="btn-genereer"
                                    data-url="{{ route('sql-data.genereren', array_merge(['tabel' => $tabel], $filters ? ['f' => $filters] : [])) }}"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rand text-gedempt text-sm hover:border-accent hover:text-accent transition-colors whitespace-nowrap cursor-pointer">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5"/>
                                </svg>
                                INSERT INTO genereren
                            </button>
                        @endif
                    </div>

                    @php
                        $kolommenVoorFilter = $kolomNamen ?: array_keys($rijen[0] ?? []);
                        $aantalKolommen     = count($kolommenVoorFilter);
                    @endphp

                    {{-- INSERT INTO blok --}}
                    <div id="sql-blok" class="mb-5 rounded-xl border border-rand overflow-hidden" style="display:none">
                        <div class="flex items-center justify-between px-4 py-2.5 bg-[#1a1f28] border-b border-rand">
                            <span class="text-gedempt text-xs font-semibold uppercase tracking-wide">INSERT INTO</span>
                            <div class="flex items-center gap-2">
                                <button id="sql-kopieer"
                                        class="px-3 py-1 rounded-lg bg-accent text-[#10241a] text-xs font-semibold hover:opacity-90 transition-opacity cursor-pointer">
                                    Kopieer
                                </button>
                                <button id="sql-blok-sluit"
                                        class="text-gedempt hover:text-tekst transition-colors text-xl leading-none cursor-pointer">×</button>
                            </div>
                        </div>
                        <textarea id="sql-output" readonly
                                  class="w-full bg-[#161b23] text-tekst font-mono text-xs p-4 outline-none resize-none block"
                                  style="height:260px"></textarea>
                    </div>

                    @if(empty($rijen) && empty($filters))
                        <p class="text-gedempt text-sm">Geen rijen gevonden voor deze tabel.</p>
                    @else
                        <form method="get" action="{{ route('sql-data.index') }}" id="filter-form">
                            <input type="hidden" name="tabel" value="{{ $tabel }}">

                            <div class="overflow-x-auto rounded-xl border border-rand">
                                <table class="w-full border-collapse text-[.82rem]">
                                    <thead>
                                        {{-- Kolomheaders --}}
                                        <tr class="bg-[#1a1f28]">
                                            <th class="text-left px-3 py-2 border-b border-rand text-gedempt font-semibold text-xs uppercase tracking-[.04em] whitespace-nowrap w-8">#</th>
                                            @if($kolomNamen)
                                                @foreach($kolomNamen as $kol)
                                                    <th class="text-left px-3 py-2 border-b border-rand text-gedempt font-semibold text-xs uppercase tracking-[.04em] whitespace-nowrap">{{ $kol }}</th>
                                                @endforeach
                                            @else
                                                @foreach(($rijen[0] ?? []) as $i => $_)
                                                    <th class="text-left px-3 py-2 border-b border-rand text-gedempt font-semibold text-xs uppercase tracking-[.04em]">kolom {{ $i + 1 }}</th>
                                                @endforeach
                                            @endif
                                        </tr>
                                        {{-- Filterrij --}}
                                        @if($aantalKolommen > 0)
                                            <tr class="bg-[#161b23]">
                                                <td class="px-2 py-1.5 border-b border-rand/60 w-8 text-center">
                                                    @if($filters)
                                                        <a href="{{ route('sql-data.index', ['tabel' => $tabel]) }}"
                                                           title="Filters wissen"
                                                           class="inline-block text-gedempt hover:text-fout transition-colors text-lg leading-none">×</a>
                                                    @endif
                                                </td>
                                                @if($kolomNamen)
                                                    @foreach($kolomNamen as $kol)
                                                        <td class="px-2 py-1.5 border-b border-rand/60">
                                                            <input type="text" name="f[{{ $kol }}]" value="{{ $filters[$kol] ?? '' }}"
                                                                   placeholder="zoek…"
                                                                   class="w-full bg-transparent text-[.82rem] text-tekst placeholder-gedempt/40 outline-none border-b border-rand/40 focus:border-accent pb-0.5 transition-colors min-w-15">
                                                        </td>
                                                    @endforeach
                                                @else
                                                    @foreach(($rijen[0] ?? []) as $i => $_)
                                                        <td class="px-2 py-1.5 border-b border-rand/60">
                                                            <input type="text" name="f[{{ $i }}]" value="{{ $filters[$i] ?? '' }}"
                                                                   placeholder="zoek…"
                                                                   class="w-full bg-transparent text-[.82rem] text-tekst placeholder-gedempt/40 outline-none border-b border-rand/40 focus:border-accent pb-0.5 transition-colors min-w-15">
                                                        </td>
                                                    @endforeach
                                                @endif
                                            </tr>
                                        @endif
                                    </thead>
                                    <tbody>
                                        @if(empty($rijen))
                                            <tr>
                                                <td colspan="{{ $aantalKolommen + 1 }}" class="px-4 py-6 text-center text-gedempt text-sm">
                                                    Geen rijen gevonden voor deze filters. —
                                                    <a href="{{ route('sql-data.index', ['tabel' => $tabel]) }}" class="text-accent hover:underline">Wis filters</a>
                                                </td>
                                            </tr>
                                        @else
                                            @foreach($rijen as $i => $rij)
                                                @php $rijNummer = ($pagina - 1) * $perPagina + $i + 1; @endphp
                                                <tr class="{{ $i % 2 === 0 ? 'bg-paneel' : 'bg-[#1e242d]' }} hover:bg-rand/40 transition-colors">
                                                    <td class="px-3 py-1.5 border-b border-rand/50 text-gedempt tabular-nums">{{ $rijNummer }}</td>
                                                    @foreach($rij as $waarde)
                                                        <td class="px-3 py-1.5 border-b border-rand/50 max-w-[320px] truncate
                                                            {{ is_null($waarde) ? 'text-gedempt italic' : 'text-tekst' }}">
                                                            @if(is_null($waarde)) NULL @else {{ $waarde }} @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        {{-- Paginering --}}
                        @if($totaalPaginas > 1)
                            @php
                                $venster   = 2;
                                $paginaUrl = fn($p) => route('sql-data.index', array_merge(
                                    ['tabel' => $tabel, 'pagina' => $p],
                                    $filters ? ['f' => $filters] : []
                                ));
                            @endphp
                            <div class="flex items-center justify-between mt-4 text-sm">
                                {{-- Vorige --}}
                                @if($pagina > 1)
                                    <a href="{{ $paginaUrl($pagina - 1) }}"
                                       class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rand text-gedempt hover:border-accent hover:text-accent transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                        Vorige
                                    </a>
                                @else
                                    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rand/30 text-gedempt/40 cursor-default">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                        Vorige
                                    </span>
                                @endif

                                {{-- Paginanummers --}}
                                <div class="flex items-center gap-1">
                                    @for($p = 1; $p <= $totaalPaginas; $p++)
                                        @if($p === 1 || $p === $totaalPaginas || abs($p - $pagina) <= $venster)
                                            @if($p === $pagina)
                                                <span class="px-3 py-1.5 rounded-lg bg-accent text-[#10241a] font-semibold tabular-nums">{{ $p }}</span>
                                            @else
                                                <a href="{{ $paginaUrl($p) }}"
                                                   class="px-3 py-1.5 rounded-lg border border-rand text-gedempt hover:border-accent hover:text-accent transition-colors tabular-nums">{{ $p }}</a>
                                            @endif
                                        @elseif(abs($p - $pagina) === $venster + 1)
                                            <span class="px-1 text-gedempt/50">…</span>
                                        @endif
                                    @endfor
                                </div>

                                {{-- Volgende --}}
                                @if($pagina < $totaalPaginas)
                                    <a href="{{ $paginaUrl($pagina + 1) }}"
                                       class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rand text-gedempt hover:border-accent hover:text-accent transition-colors">
                                        Volgende
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                @else
                                    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rand/30 text-gedempt/40 cursor-default">
                                        Volgende
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                @endif
                            </div>
                        @endif
                    @endif
                </div>
            @endif
        @endif

    </div>

    <script>
    // INSERT INTO blok
    (function () {
        const btn    = document.getElementById('btn-genereer');
        const blok   = document.getElementById('sql-blok');
        const output = document.getElementById('sql-output');
        const kopieer = document.getElementById('sql-kopieer');
        const sluit  = document.getElementById('sql-blok-sluit');
        if (!btn) return;

        btn.addEventListener('click', async () => {
            const origLabel = btn.innerHTML;
            btn.textContent = 'Bezig…';
            btn.disabled = true;
            try {
                const resp = await fetch(btn.dataset.url);
                output.value = await resp.text();
                blok.style.display = 'block';
                blok.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } finally {
                btn.innerHTML = origLabel;
                btn.disabled = false;
            }
        });

        kopieer.addEventListener('click', () => {
            navigator.clipboard.writeText(output.value).then(() => {
                kopieer.textContent = 'Gekopieerd!';
                setTimeout(() => { kopieer.textContent = 'Kopieer'; }, 1500);
            });
        });

        sluit.addEventListener('click', () => { blok.style.display = 'none'; });
    })();

    // Filter auto-submit met debounce
    (function () {
        const form = document.getElementById('filter-form');
        if (!form) return;
        let timer;
        form.querySelectorAll('input[name^="f["]').forEach(input => {
            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => form.submit(), 450);
            });
        });
    })();

    document.querySelectorAll('.dropzone, form label:has(input[type="file"])').forEach(dz => {
        const input = dz.querySelector('input[type="file"]');
        const label = dz.querySelector('.dz-bestand');
        if (!input) return;
        input.addEventListener('change', () => {
            if (input.files.length && label) {
                label.textContent = input.files[0].name;
                label.classList.remove('hidden');
            }
        });
        dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('border-accent'); });
        dz.addEventListener('dragleave', () => dz.classList.remove('border-accent'));
        dz.addEventListener('drop', e => {
            e.preventDefault();
            dz.classList.remove('border-accent');
            if (e.dataTransfer.files.length) {
                const dt = new DataTransfer();
                dt.items.add(e.dataTransfer.files[0]);
                input.files = dt.files;
                if (label) { label.textContent = e.dataTransfer.files[0].name; label.classList.remove('hidden'); }
            }
        });
    });
    </script>
</x-layouts.portaal>
