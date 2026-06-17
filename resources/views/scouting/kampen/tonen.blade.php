<x-layouts.portaal title="{{ $kamp->naam }} — Scouting">

    <div class="max-w-[900px] mx-auto px-4 pt-10 pb-16">

        <a href="{{ route('scouting.kampen.index') }}" class="sc-terug">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Terug naar kampen
        </a>

        @if(session('succes'))
            <div class="bg-accent/10 border border-accent/30 rounded-lg px-4 py-3 mb-5 text-accent text-sm">
                {{ session('succes') }}
            </div>
        @endif

        {{-- Kamp header --}}
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-[1.45rem] font-semibold tracking-tight text-tekst">{{ $kamp->naam }}</h1>
                <p class="text-gedempt mt-0.5">
                    {{ $kamp->start_datum->format('d M Y') }} – {{ $kamp->eind_datum->format('d M Y') }}
                    ({{ $kamp->duur }} {{ $kamp->duur === 1 ? 'dag' : 'dagen' }})
                    @if($kamp->locatie) · {{ $kamp->locatie }} @endif
                </p>
                @if($kamp->beschrijving)
                    <p class="text-gedempt text-sm mt-2">{{ $kamp->beschrijving }}</p>
                @endif
            </div>
            <a href="{{ route('scouting.kampen.bewerken', $kamp) }}"
               class="border border-rand text-gedempt rounded-lg px-4 py-2 text-sm hover:border-accent hover:text-accent transition-colors whitespace-nowrap shrink-0">
                Bewerken
            </a>
        </div>

        {{-- Betalingsoverzicht --}}
        @if($kamp->deelnames->isNotEmpty())
            @php
                $totaal       = $kamp->deelnames->count();
                $betaald      = $kamp->deelnames->where('betaald', true)->count();
                $totaalBedrag = $kamp->deelnames->sum('bedrag');
                $betaaldBedrag = $kamp->deelnames->where('betaald', true)->sum('bedrag');
            @endphp
            <div class="grid grid-cols-4 gap-3 mb-8">
                <div class="bg-paneel border border-rand rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-tekst tabular-nums">{{ $totaal }}</div>
                    <div class="text-gedempt text-xs mt-1">leden</div>
                </div>
                <div class="bg-paneel border border-rand rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-accent tabular-nums">{{ $betaald }}</div>
                    <div class="text-gedempt text-xs mt-1">betaald</div>
                </div>
                <div class="bg-paneel border border-rand rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-tekst tabular-nums">{{ $totaal - $betaald }}</div>
                    <div class="text-gedempt text-xs mt-1">openstaand</div>
                </div>
                <div class="bg-paneel border border-rand rounded-xl p-4 text-center">
                    <div class="text-lg font-bold text-tekst tabular-nums">€ {{ number_format($betaaldBedrag, 2, ',', '.') }}</div>
                    <div class="text-gedempt text-xs mt-1">van € {{ number_format($totaalBedrag, 2, ',', '.') }}</div>
                </div>
            </div>
        @endif

        {{-- Leden & Leiding naast elkaar --}}
        <div class="grid grid-cols-[1fr_260px] gap-6 mb-8">

        {{-- Linkerkolom: leden --}}
        <div>

        {{-- Leden tabel header + knop --}}
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-semibold text-tekst">Leden</h2>
            @if($beschikbareLeden->isNotEmpty())
                <button id="toon-lid-form" type="button"
                        class="bg-accent text-[#10241a] rounded-lg px-3 py-1.5 font-semibold text-xs hover:opacity-90 transition-opacity cursor-pointer">
                    + Lid toevoegen
                </button>
            @endif
        </div>

        @if($beschikbareLeden->isNotEmpty())
            <div class="mb-4">
                <div id="lid-form" class="hidden mb-4 bg-paneel border border-rand rounded-xl p-5">
                    <form method="post" action="{{ route('scouting.deelnames.opslaan', $kamp) }}" class="flex flex-wrap gap-3 items-end">
                        @csrf
                        <div class="flex-1 min-w-40">
                            <label class="block text-xs text-gedempt mb-1">Lid *</label>
                            <select name="lid_id" required
                                    class="w-full bg-[#161b23] border border-rand rounded-lg px-3 py-2 text-tekst text-sm outline-none focus:border-accent transition-colors">
                                <option value="">Kies een lid…</option>
                                @foreach($beschikbareLeden as $lid)
                                    <option value="{{ $lid->id }}">{{ $lid->naam }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-28">
                            <label class="block text-xs text-gedempt mb-1">Bedrag (€)</label>
                            <input type="number" name="bedrag" step="0.01" min="0"
                                   class="w-full bg-[#161b23] border border-rand rounded-lg px-3 py-2 text-tekst text-sm outline-none focus:border-accent transition-colors">
                        </div>
                        <div class="flex-1 min-w-40">
                            <label class="block text-xs text-gedempt mb-1">Bijzonderheden</label>
                            <input type="text" name="bijzonderheden" placeholder="bijv. glutenvrij, medicatie…"
                                   class="w-full bg-[#161b23] border border-rand rounded-lg px-3 py-2 text-tekst text-sm outline-none focus:border-accent transition-colors">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="bg-accent text-[#10241a] rounded-lg px-4 py-2 font-semibold text-sm hover:opacity-90 transition-opacity cursor-pointer whitespace-nowrap">
                                Toevoegen
                            </button>
                            <button type="button" id="verberg-lid-form"
                                    class="border border-rand text-gedempt rounded-lg px-4 py-2 text-sm hover:border-accent hover:text-accent transition-colors cursor-pointer">
                                Annuleren
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Leden tabel --}}
        <div>
            @if($kamp->deelnames->isEmpty())
                <p class="text-gedempt text-sm">Nog geen leden.</p>
            @else
                <div class="rounded-xl border border-rand overflow-hidden mb-4">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr class="bg-[#1a1f28]">
                                <th class="text-left px-4 py-2.5 text-gedempt font-semibold text-xs uppercase tracking-wide">Naam</th>
                                <th class="text-left px-4 py-2.5 text-gedempt font-semibold text-xs uppercase tracking-wide">Bedrag</th>
                                <th class="text-left px-4 py-2.5 text-gedempt font-semibold text-xs uppercase tracking-wide">Betaald</th>
                                <th class="text-left px-4 py-2.5 text-gedempt font-semibold text-xs uppercase tracking-wide">Bijzonderheden</th>
                                <th class="px-4 py-2.5 w-24"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kamp->deelnames as $deelname)
                                <tr class="odd:bg-paneel even:bg-[#1e242d] hover:bg-rand/40 transition-colors">
                                    {{-- Weergavemodus --}}
                                    <td class="view-cel px-4 py-2.5 border-b border-rand/50 text-tekst font-medium">{{ $deelname->lid->naam }}</td>
                                    <td class="view-cel px-4 py-2.5 border-b border-rand/50 text-tekst tabular-nums">
                                        {{ $deelname->bedrag !== null ? '€ ' . number_format($deelname->bedrag, 2, ',', '.') : '—' }}
                                    </td>
                                    <td class="view-cel px-4 py-2.5 border-b border-rand/50">
                                        <span class="text-xs px-2.5 py-1 rounded-full {{ $deelname->betaald ? 'bg-accent/15 text-accent' : 'bg-rand text-gedempt' }}">
                                            {{ $deelname->betaald ? 'Betaald' : 'Niet betaald' }}
                                        </span>
                                    </td>
                                    <td class="view-cel px-4 py-2.5 border-b border-rand/50 text-gedempt text-xs">
                                        {{ $deelname->bijzonderheden ?: '—' }}
                                    </td>
                                    <td class="view-cel px-4 py-2.5 border-b border-rand/50">
                                        <div class="flex items-center gap-2 justify-end">
                                            <button type="button" onclick="bewerkRij(this)"
                                                    class="text-gedempt hover:text-accent transition-colors text-xs cursor-pointer">Bewerken</button>
                                            <form method="post" action="{{ route('scouting.deelnames.verwijderen', $deelname) }}"
                                                  onsubmit="return confirm('Lid verwijderen uit dit kamp?')">
                                                @csrf @method('DELETE')
                                                <button class="text-gedempt hover:text-fout transition-colors text-xs cursor-pointer">Verwijderen</button>
                                            </form>
                                        </div>
                                    </td>

                                    {{-- Bewerkingsmodus --}}
                                    <td class="edit-cel hidden px-4 py-2.5 border-b border-rand/50 text-tekst font-medium">{{ $deelname->lid->naam }}</td>
                                    <form method="post" action="{{ route('scouting.deelnames.bijwerken', $deelname) }}" style="display:contents">
                                        @csrf @method('PUT')
                                        <td class="edit-cel hidden px-4 py-2.5 border-b border-rand/50">
                                            <input type="number" name="bedrag" step="0.01" min="0"
                                                   value="{{ $deelname->bedrag }}"
                                                   class="w-24 bg-paneel border border-rand rounded px-2 py-1 text-tekst text-sm tabular-nums outline-none focus:border-accent transition-colors">
                                        </td>
                                        <td class="edit-cel hidden px-4 py-2.5 border-b border-rand/50">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="hidden" name="betaald" value="0">
                                                <input type="checkbox" name="betaald" value="1" {{ $deelname->betaald ? 'checked' : '' }}
                                                       class="w-4 h-4 accent-[#4cc38a]">
                                                <span class="text-xs text-tekst">Betaald</span>
                                            </label>
                                        </td>
                                        <td class="edit-cel hidden px-4 py-2.5 border-b border-rand/50">
                                            <input type="text" name="bijzonderheden"
                                                   value="{{ $deelname->bijzonderheden }}"
                                                   placeholder="bijv. glutenvrij…"
                                                   class="w-full bg-paneel border border-rand rounded px-2 py-1 text-tekst text-xs outline-none focus:border-accent transition-colors">
                                        </td>
                                        <td class="edit-cel hidden px-4 py-2.5 border-b border-rand/50">
                                            <div class="flex items-center gap-2 justify-end">
                                                <button type="submit" class="text-accent hover:opacity-75 transition-opacity text-xs cursor-pointer font-medium">Opslaan</button>
                                                <button type="button" onclick="annuleerRij(this)"
                                                        class="text-gedempt hover:text-tekst transition-colors text-xs cursor-pointer">Annuleren</button>
                                            </div>
                                        </td>
                                    </form>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        </div>{{-- einde linkerkolom --}}

        {{-- Rechterkolom: leiding --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-tekst">Leiding</h2>
                @if($beschikbareLeiding->isNotEmpty())
                    <button id="toon-leiding-form" type="button"
                            class="bg-accent text-[#10241a] rounded-lg px-3 py-1.5 font-semibold text-xs hover:opacity-90 transition-opacity cursor-pointer">
                        + Toevoegen
                    </button>
                @endif
            </div>

            <div id="leiding-form" class="hidden mb-4 bg-paneel border border-rand rounded-xl p-4">
                    <form method="post" action="{{ route('scouting.kampleiding.opslaan', $kamp) }}" class="flex flex-wrap gap-3 items-end">
                        @csrf
                        <div class="flex-1 min-w-40">
                            <label class="block text-xs text-gedempt mb-1">Leiding *</label>
                            <select name="leiding_id" required
                                    class="w-full bg-[#161b23] border border-rand rounded-lg px-3 py-2 text-tekst text-sm outline-none focus:border-accent transition-colors">
                                <option value="">Kies een leidinggevende…</option>
                                @foreach($beschikbareLeiding as $persoon)
                                    <option value="{{ $persoon->id }}">{{ $persoon->naam }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="bg-accent text-[#10241a] rounded-lg px-4 py-2 font-semibold text-sm hover:opacity-90 transition-opacity cursor-pointer">
                                Toevoegen
                            </button>
                            <button type="button" id="verberg-leiding-form"
                                    class="border border-rand text-gedempt rounded-lg px-4 py-2 text-sm hover:border-accent hover:text-accent transition-colors cursor-pointer">
                                Annuleren
                            </button>
                        </div>
                    </form>
                </div>
            @if($kamp->kampleiding->isNotEmpty())
                <div class="rounded-xl border border-rand overflow-hidden">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr class="bg-[#1a1f28]">
                                <th class="text-left px-4 py-2.5 text-gedempt font-semibold text-xs uppercase tracking-wide">Naam</th>
                                <th class="px-4 py-2.5 w-20"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kamp->kampleiding as $kl)
                                <tr class="odd:bg-paneel even:bg-[#1e242d] hover:bg-rand/40 transition-colors">
                                    <td class="px-4 py-2.5 border-b border-rand/50 text-tekst font-medium">{{ $kl->leiding->naam }}</td>
                                    <td class="px-4 py-2.5 border-b border-rand/50">
                                        <form method="post" action="{{ route('scouting.kampleiding.verwijderen', $kl) }}"
                                              onsubmit="return confirm('Leiding verwijderen uit dit kamp?')" class="flex justify-end">
                                            @csrf @method('DELETE')
                                            <button class="text-gedempt hover:text-fout transition-colors text-xs cursor-pointer">Verwijderen</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gedempt text-sm">Nog geen leiding toegevoegd.</p>
            @endif
        </div>
        </div>{{-- einde rechterkolom --}}
        </div>{{-- einde grid --}}

    </div>

    <script>
    const toonKnop    = document.getElementById('toon-lid-form');
    const verbergKnop = document.getElementById('verberg-lid-form');
    const lidForm     = document.getElementById('lid-form');

    if (toonKnop) {
        toonKnop.addEventListener('click', () => {
            lidForm.classList.remove('hidden');
            toonKnop.classList.add('hidden');
            lidForm.querySelector('select').focus();
        });
    }
    if (verbergKnop) {
        verbergKnop.addEventListener('click', () => {
            lidForm.classList.add('hidden');
            toonKnop.classList.remove('hidden');
        });
    }

    const toonLeidingKnop    = document.getElementById('toon-leiding-form');
    const verbergLeidingKnop = document.getElementById('verberg-leiding-form');
    const leidingForm        = document.getElementById('leiding-form');

    if (toonLeidingKnop) {
        toonLeidingKnop.addEventListener('click', () => {
            leidingForm.classList.remove('hidden');
            toonLeidingKnop.classList.add('hidden');
            leidingForm.querySelector('select').focus();
        });
    }
    if (verbergLeidingKnop) {
        verbergLeidingKnop.addEventListener('click', () => {
            leidingForm.classList.add('hidden');
            toonLeidingKnop.classList.remove('hidden');
        });
    }

    function bewerkRij(knop) {
        const rij = knop.closest('tr');
        rij.querySelectorAll('.view-cel').forEach(cel => cel.classList.add('hidden'));
        rij.querySelectorAll('.edit-cel').forEach(cel => cel.classList.remove('hidden'));
    }

    function annuleerRij(knop) {
        const rij = knop.closest('tr');
        rij.querySelectorAll('.edit-cel').forEach(cel => cel.classList.add('hidden'));
        rij.querySelectorAll('.view-cel').forEach(cel => cel.classList.remove('hidden'));
    }

    document.querySelectorAll('.edit-cel form').forEach(function (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const res = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
            });

            if (!res.ok) { form.submit(); return; }

            const data = await res.json();
            const rij = form.closest('tr');
            const viewCellen = rij.querySelectorAll('.view-cel');

            // Bedrag (index 1)
            const bedragTekst = data.bedrag !== null
                ? '€ ' + parseFloat(data.bedrag).toLocaleString('nl-NL', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                : '—';
            viewCellen[1].textContent = bedragTekst;

            // Betaald (index 2)
            const badge = viewCellen[2].querySelector('span');
            badge.textContent = data.betaald ? 'Betaald' : 'Niet betaald';
            badge.className = 'text-xs px-2.5 py-1 rounded-full ' + (data.betaald ? 'bg-accent/15 text-accent' : 'bg-rand text-gedempt');

            // Bijzonderheden (index 3)
            viewCellen[3].textContent = data.bijzonderheden || '—';

            // Checkbox in edit-modus syncen met werkelijke waarde
            const checkbox = rij.querySelector('.edit-cel input[type="checkbox"][name="betaald"]');
            if (checkbox) checkbox.checked = data.betaald;

            annuleerRij(rij.querySelector('.edit-cel button[type="button"]'));
        });
    });
    </script>

</x-layouts.portaal>
