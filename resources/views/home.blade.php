<x-layouts.portaal title="Kleine Projecten">
    <div class="max-w-[1100px] mx-auto px-5 py-10">

        <header class="text-center mb-10">
            <h1 class="text-[2rem] text-tekst font-bold">Kleine Projecten</h1>
            <p class="text-gedempt mt-2">Kies een project om mee te werken</p>
        </header>

        <div class="grid grid-cols-[repeat(auto-fill,minmax(260px,1fr))] gap-6">

            <a class="bg-paneel border border-rand rounded-xl p-7 no-underline flex flex-col gap-2.5 transition-all hover:-translate-y-1 hover:border-accent hover:shadow-lg"
               href="{{ route('sql-vergelijker.index') }}">
                <div class="text-[2rem]">🗄️</div>
                <h2 class="text-[1.1rem] text-tekst font-semibold">SQL Vergelijker</h2>
                <p class="text-[.9rem] text-gedempt flex-1">Vergelijk twee databasestructuren en bekijk de verschillen tussen tabellen en kolommen.</p>
                <span class="self-start bg-accent/20 text-accent text-xs px-2.5 py-0.5 rounded-full font-medium">SQL / PHP</span>
            </a>

            <a class="bg-paneel border border-rand rounded-xl p-7 no-underline flex flex-col gap-2.5 transition-all hover:-translate-y-1 hover:border-accent hover:shadow-lg"
               href="{{ route('sql-data.index') }}">
                <div class="text-[2rem]">📤</div>
                <h2 class="text-[1.1rem] text-tekst font-semibold">SQL Data extractor</h2>
                <p class="text-[.9rem] text-gedempt flex-1">Upload een SQL-dump, kies een tabel en exporteer of kopieer de INSERT-statements.</p>
                <span class="self-start bg-accent/20 text-accent text-xs px-2.5 py-0.5 rounded-full font-medium">SQL / PHP</span>
            </a>

            <a class="bg-paneel border border-rand rounded-xl p-7 no-underline flex flex-col gap-2.5 transition-all hover:-translate-y-1 hover:border-accent hover:shadow-lg"
               href="{{ route('budget.home') }}">
                <div class="text-[2rem]">💰</div>
                <h2 class="text-[1.1rem] text-tekst font-semibold">Budget</h2>
                <p class="text-[.9rem] text-gedempt flex-1">Beheer je inkomsten en uitgaven, importeer transacties en houd je budget bij.</p>
                <span class="self-start bg-accent/20 text-accent text-xs px-2.5 py-0.5 rounded-full font-medium">Laravel / SQLite</span>
            </a>

            <a class="bg-paneel border border-rand rounded-xl p-7 no-underline flex flex-col gap-2.5 transition-all hover:-translate-y-1 hover:border-accent hover:shadow-lg"
               href="{{ route('gpx-viewer.index') }}">
                <div class="text-[2rem]">🗺️</div>
                <h2 class="text-[1.1rem] text-tekst font-semibold">GPX Viewer</h2>
                <p class="text-[.9rem] text-gedempt flex-1">Laad een GPX-bestand en bekijk de route op een interactieve kaart met statistieken.</p>
                <span class="self-start bg-accent/20 text-accent text-xs px-2.5 py-0.5 rounded-full font-medium">Leaflet / GPX</span>
            </a>

            <a class="bg-paneel border border-rand rounded-xl p-7 no-underline flex flex-col gap-2.5 transition-all hover:-translate-y-1 hover:border-accent hover:shadow-lg"
               href="{{ route('scouting.home') }}">
                <div class="text-[2rem]">⛺</div>
                <h2 class="text-[1.1rem] text-tekst font-semibold">Scouting</h2>
                <p class="text-[.9rem] text-gedempt flex-1">Beheer scoutingleden, kampen en kampdeelnames inclusief betalingsoverzicht.</p>
                <span class="self-start bg-accent/20 text-accent text-xs px-2.5 py-0.5 rounded-full font-medium">Laravel / SQLite</span>
            </a>

        </div>
    </div>
</x-layouts.portaal>
