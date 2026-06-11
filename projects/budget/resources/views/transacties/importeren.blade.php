<x-layouts.app title="CSV importeren">

    <div class="mb-6">
        <a href="{{ route('transacties.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Terug</a>
        <h1 class="text-2xl font-semibold text-gray-800 mt-1">CSV importeren</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 max-w-lg">

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form id="importForm" action="{{ route('transacties.importeren.opslaan') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-5">
            @csrf

            <div>
                <label class="form-label" for="rekening_id">Rekening</label>
                <select name="rekening_id" id="rekening_id" class="form-input" required>
                    <option value="">— Kies een rekening —</option>
                    @foreach($rekeningen as $rekening)
                        <option value="{{ $rekening->id }}" {{ old('rekening_id') == $rekening->id ? 'selected' : '' }}>
                            {{ $rekening->naam }}{{ $rekening->bank ? ' (' . strtoupper($rekening->bank) . ')' : '' }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">De bank die bij de rekening is ingesteld bepaalt hoe de CSV wordt gelezen.</p>
            </div>

            <div>
                <label class="form-label" for="csv">CSV-bestand</label>
                <input type="file" name="csv" id="csv" accept=".csv,.txt" required
                    class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
            </div>

            {{-- Voortgangsbalk (verborgen tot submit) --}}
            <div id="voortgang" class="hidden flex-col gap-2">
                <div class="flex justify-between text-xs text-gray-500">
                    <span id="voortgangLabel">Uploaden...</span>
                    <span id="voortgangProcent">0%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div id="voortgangBalk" class="h-2 rounded-full bg-indigo-500 transition-all duration-200" style="width: 0%"></div>
                </div>
            </div>

            <div class="pt-2 border-t border-gray-100 flex items-center gap-4">
                <button id="submitBtn" type="submit" class="btn-primary">Importeren</button>
                <span class="text-xs text-gray-400">Duplicaten worden automatisch overgeslagen.</span>
            </div>
        </form>
    </div>

    <div class="mt-6 max-w-lg text-xs text-gray-400 space-y-1">
        <p><strong class="text-gray-500">Rabobank:</strong> exporteer via Mijn Rabobank → Mutaties → Exporteren als CSV.</p>
        <p><strong class="text-gray-500">Bunq:</strong> exporteer via de Bunq-app → Rekening → Exporteren → CSV.</p>
    </div>

    <script>
        const form = document.getElementById('importForm');
        const voortgang = document.getElementById('voortgang');
        const voortgangBalk = document.getElementById('voortgangBalk');
        const voortgangProcent = document.getElementById('voortgangProcent');
        const voortgangLabel = document.getElementById('voortgangLabel');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            voortgang.classList.remove('hidden');
            voortgang.classList.add('flex');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Bezig...';

            const xhr = new XMLHttpRequest();
            const formData = new FormData(form);

            xhr.upload.addEventListener('progress', function (e) {
                if (e.lengthComputable) {
                    const pct = Math.round((e.loaded / e.total) * 100);
                    voortgangBalk.style.width = pct + '%';
                    voortgangProcent.textContent = pct + '%';
                }
            });

            xhr.upload.addEventListener('load', function () {
                voortgangBalk.style.width = '100%';
                voortgangProcent.textContent = '100%';
                voortgangLabel.textContent = 'Verwerken...';
                voortgangBalk.classList.remove('bg-indigo-500');
                voortgangBalk.classList.add('bg-emerald-500', 'animate-pulse');
            });

            xhr.addEventListener('load', function () {
                // Volg de redirect die Laravel teruggeeft
                const redirectUrl = xhr.responseURL;
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                } else {
                    window.location.reload();
                }
            });

            xhr.addEventListener('error', function () {
                voortgangLabel.textContent = 'Er is iets misgegaan.';
                voortgangBalk.classList.add('bg-red-500');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Importeren';
            });

            xhr.open('POST', form.action);
            xhr.send(formData);
        });
    </script>

</x-layouts.app>
