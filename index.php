<?php
$pageTitle = 'Kleine Projecten';
$rootPath  = './';

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-[1100px] mx-auto px-5 py-10">

    <header class="text-center mb-10">
        <h1 class="text-[2rem] text-tekst font-bold">Kleine Projecten</h1>
        <p class="text-gedempt mt-2">Kies een project om mee te werken</p>
    </header>

    <div class="grid grid-cols-[repeat(auto-fill,minmax(260px,1fr))] gap-6">

        <a class="bg-paneel border border-rand rounded-xl p-7 no-underline flex flex-col gap-2.5 transition-all hover:-translate-y-1 hover:border-accent hover:shadow-lg"
           href="projects/SQL_compare/db_vergelijker.php">
            <div class="text-[2rem]">🗄️</div>
            <h2 class="text-[1.1rem] text-tekst font-semibold">SQL Vergelijker</h2>
            <p class="text-[.9rem] text-gedempt flex-1">Vergelijk twee databasestructuren en bekijk de verschillen tussen tabellen en kolommen.</p>
            <span class="self-start bg-accent/20 text-accent text-xs px-2.5 py-0.5 rounded-full font-medium">SQL / PHP</span>
        </a>

        <!-- Voeg hier nieuwe projecten toe als extra <a> blokken -->

    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
