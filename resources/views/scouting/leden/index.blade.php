<x-layouts.portaal title="Leden — Scouting">

    <div class="max-w-2xl mx-auto px-4 pt-10 pb-16">

        <a href="{{ route('scouting.home') }}" class="text-gedempt text-sm hover:text-accent transition-colors mb-6 inline-flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Terug naar Scouting
        </a>

        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-[1.45rem] font-semibold tracking-tight text-tekst">Leden</h1>
                <p id="aantal-tekst" class="text-gedempt text-sm mt-0.5">{{ $leden->count() }} {{ $leden->count() === 1 ? 'lid' : 'leden' }}</p>
            </div>
            <form id="lid-toevoegen-form" method="post" action="{{ route('scouting.leden.opslaan') }}" class="flex gap-2">
                @csrf
                <input type="text" name="naam" placeholder="Naam nieuw lid…" required autofocus
                       class="bg-paneel border border-rand rounded-lg px-3 py-2 text-tekst text-sm outline-none focus:border-accent transition-colors w-52">
                <button type="submit"
                        class="bg-accent text-[#10241a] rounded-lg px-4 py-2 font-semibold text-sm hover:opacity-90 transition-opacity cursor-pointer whitespace-nowrap">
                    + Toevoegen
                </button>
            </form>
        </div>

        @if(session('succes'))
            <div class="bg-accent/10 border border-accent/30 rounded-lg px-4 py-3 mb-5 text-accent text-sm">
                {{ session('succes') }}
            </div>
        @endif

        <p id="leeg-melding" class="{{ $leden->isEmpty() ? '' : 'hidden' }} text-gedempt text-sm">
            Nog geen leden. <a href="{{ route('scouting.leden.aanmaken') }}" class="text-accent hover:underline">Voeg het eerste lid toe.</a>
        </p>

        <div id="leden-tabel" class="{{ $leden->isEmpty() ? 'hidden' : '' }} rounded-xl border border-rand overflow-hidden">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-[#1a1f28]">
                        <th class="text-left px-4 py-2.5 text-gedempt font-semibold text-xs uppercase tracking-wide">Naam</th>
                        <th class="px-4 py-2.5 w-20"></th>
                    </tr>
                </thead>
                <tbody id="leden-tbody">
                    @foreach($leden as $lid)
                        <tr class="odd:bg-paneel even:bg-[#1e242d] hover:bg-rand/40 transition-colors">
                            <td class="px-4 py-2.5 border-b border-rand/50 text-tekst font-medium">{{ $lid->naam }}</td>
                            <td class="px-4 py-2.5 border-b border-rand/50">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="{{ route('scouting.leden.bewerken', $lid) }}" class="text-gedempt hover:text-accent transition-colors text-xs">Bewerken</a>
                                    <form method="post" action="{{ route('scouting.leden.verwijderen', $lid) }}"
                                          onsubmit="return confirm('Lid verwijderen?')">
                                        @csrf @method('DELETE')
                                        <button class="text-gedempt hover:text-fout transition-colors text-xs cursor-pointer">Verwijderen</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    <script>
    (function () {
        const form      = document.getElementById('lid-toevoegen-form');
        const tbody     = document.getElementById('leden-tbody');
        const tabel     = document.getElementById('leden-tabel');
        const leeg      = document.getElementById('leeg-melding');
        const aantalEl  = document.getElementById('aantal-tekst');
        const csrfToken = form.querySelector('input[name="_token"]').value;

        const bewerkUrl   = '{{ route("scouting.leden.bewerken",   "__ID__") }}';
        const verwijderUrl = '{{ route("scouting.leden.verwijderen", "__ID__") }}';

        function updateAantal() {
            const n = tbody.querySelectorAll('tr').length;
            aantalEl.textContent = n + ' ' + (n === 1 ? 'lid' : 'leden');
        }

        function maakRij(lid) {
            const tr = document.createElement('tr');
            tr.className = 'odd:bg-paneel even:bg-[#1e242d] hover:bg-rand/40 transition-colors';
            tr.innerHTML = `
                <td class="px-4 py-2.5 border-b border-rand/50 text-tekst font-medium">${lid.naam}</td>
                <td class="px-4 py-2.5 border-b border-rand/50">
                    <div class="flex items-center gap-2 justify-end">
                        <a href="${bewerkUrl.replace('__ID__', lid.id)}" class="text-gedempt hover:text-accent transition-colors text-xs">Bewerken</a>
                        <form method="post" action="${verwijderUrl.replace('__ID__', lid.id)}" onsubmit="return confirm('Lid verwijderen?')">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button class="text-gedempt hover:text-fout transition-colors text-xs cursor-pointer">Verwijderen</button>
                        </form>
                    </div>
                </td>`;
            return tr;
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const res = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
            });

            if (!res.ok) { form.submit(); return; }

            const lid = await res.json();

            leeg.classList.add('hidden');
            tabel.classList.remove('hidden');
            tbody.appendChild(maakRij(lid));
            updateAantal();

            form.querySelector('input[name="naam"]').value = '';
            form.querySelector('input[name="naam"]').focus();
        });
    })();
    </script>

</x-layouts.portaal>
