<?php
require_once __DIR__ . '/includes/functions.php';

// ---------- DOWNLOAD-AFHANDELING ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download_sql'])) {
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="sync.sql"');
    echo $_POST['download_sql'];
    exit;
}

// ---------- FORMULIER VERWERKEN ----------

$error             = null;
$items             = null;
$syncSql           = null;
$aantalWijzigingen = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bron'], $_FILES['doel'])) {

    if ($_FILES['bron']['error'] !== UPLOAD_ERR_OK || $_FILES['doel']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload mislukt — controleer of je beide bestanden hebt gekozen.';
    } else {
        $source = parseDump(file_get_contents($_FILES['bron']['tmp_name']));
        $target = parseDump(file_get_contents($_FILES['doel']['tmp_name']));

        if (empty($source)) {
            $error = 'Geen CREATE TABLE statements gevonden in het bronbestand. Is het een structuur-export uit phpMyAdmin?';
        } elseif (empty($target)) {
            $error = 'Geen CREATE TABLE statements gevonden in het doelbestand. Is het een structuur-export uit phpMyAdmin?';
        } else {
            [$sqlOut, $items] = vergelijk($source, $target);

            $aantalWijzigingen = count(array_filter($items, fn($i) => in_array($i['type'], ['tabel', 'add', 'only-dev'])));

            $headerLines = [
                "-- Gegenereerd op " . date('Y-m-d H:i:s'),
                "-- Dev (bron): " . htmlspecialchars($_FILES['bron']['name']),
                "-- Live (doel): " . htmlspecialchars($_FILES['doel']['name']),
                "",
            ];

            $syncSql = implode("\n", array_merge($headerLines, $sqlOut));

            if ($aantalWijzigingen === 0) {
                $syncSql = implode("\n", $headerLines) . "-- Geen verschillen gevonden, databases zijn in sync.\n";
            }
        }
    }
}

// ---------- PAGINA OPZET ----------

$pageTitle = 'Database structuur vergelijken';
$rootPath  = '../../';
$headExtra = '<link rel="stylesheet" href="assets/style.css">';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-[880px] mx-auto px-4 pt-10 pb-16">

    <h1 class="text-[1.45rem] font-semibold mb-1 tracking-tight">Database structuur vergelijken</h1>
    <p class="text-gedempt mb-8">Upload twee phpMyAdmin structuur-exports. De pagina genereert de SQL om <strong>live</strong> gelijk te maken aan <strong>dev</strong>. Er wordt niets verwijderd.</p>

    <form class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4" method="post" enctype="multipart/form-data">

        <label class="dropzone bg-paneel border-2 border-dashed border-rand rounded-xl p-6 text-center cursor-pointer transition-colors relative hover:border-accent" id="dz-bron">
            <input type="file" name="bron" accept=".sql,.txt" required class="absolute inset-0 opacity-0 cursor-pointer">
            <span class="font-semibold block mb-1">Dev database</span>
            <span class="text-gedempt text-sm">De lokale export — dit wordt naar live gepusht</span>
            <span class="dz-bestand font-mono text-sm text-accent mt-2 break-all"></span>
        </label>

        <label class="dropzone bg-paneel border-2 border-dashed border-rand rounded-xl p-6 text-center cursor-pointer transition-colors relative hover:border-accent" id="dz-doel">
            <input type="file" name="doel" accept=".sql,.txt" required class="absolute inset-0 opacity-0 cursor-pointer">
            <span class="font-semibold block mb-1">Live database</span>
            <span class="text-gedempt text-sm">De productie-export — wordt bijgewerkt</span>
            <span class="dz-bestand font-mono text-sm text-accent mt-2 break-all"></span>
        </label>

        <div class="col-span-full">
            <button type="submit"
                class="bg-accent text-[#10241a] rounded-lg px-6 py-2.5 font-semibold text-[.95rem] cursor-pointer hover:bg-accent-donker hover:text-white focus-visible:outline-2 focus-visible:outline-tekst focus-visible:outline-offset-2 transition-colors">
                Vergelijk structuren
            </button>
        </div>

    </form>

    <?php if ($error): ?>
        <div class="bg-fout/10 border border-fout rounded-lg px-4 py-3 my-4">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($items !== null && !$error): ?>

        <div class="flex items-center gap-3 mt-8 mb-4">
            <span class="<?= $aantalWijzigingen === 0 ? 'bg-rand text-tekst' : 'bg-accent text-[#10241a]' ?> font-bold rounded-full px-3 py-0.5">
                <?= $aantalWijzigingen ?>
            </span>
            <span><?= $aantalWijzigingen === 1 ? 'wijziging nodig' : 'wijzigingen nodig' ?> om dev in sync te brengen met live</span>
        </div>

        <?php if (!empty($items)): ?>
            <?php
            $tagConfig = [
                'tabel'    => ['label' => 'NIEUW',      'class' => 'bg-accent/20 text-accent'],
                'add'      => ['label' => 'ADD',         'class' => 'bg-accent/20 text-accent'],
                'modify'   => ['label' => 'MODIFY',      'class' => 'bg-waarschuwing/20 text-waarschuwing'],
                'only-dev' => ['label' => 'ALLEEN DEV',  'class' => 'bg-fout/20 text-fout'],
            ];
            ?>
            <table class="w-full border-collapse bg-paneel rounded-xl overflow-hidden text-[.9rem]">
                <thead>
                    <tr>
                        <?php foreach (['Type', 'Tabel', 'Kolom', 'Omschrijving'] as $th): ?>
                            <th class="text-left px-3 py-2 border-b border-rand text-gedempt font-semibold text-xs uppercase tracking-[.04em]">
                                <?= $th ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="px-3 py-2 border-b border-rand">
                                <span class="text-xs font-bold px-2 py-0.5 rounded <?= $tagConfig[$item['type']]['class'] ?>">
                                    <?= $tagConfig[$item['type']]['label'] ?>
                                </span>
                            </td>
                            <td class="px-3 py-2 border-b border-rand font-mono text-[.85rem]"><?= htmlspecialchars($item['tabel']) ?></td>
                            <td class="px-3 py-2 border-b border-rand font-mono text-[.85rem]"><?= htmlspecialchars($item['kolom']) ?></td>
                            <td class="px-3 py-2 border-b border-rand"><?= htmlspecialchars($item['omschrijving']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="mt-6">
            <div class="flex justify-between items-center gap-2 mb-2">
                <h2 class="text-base font-semibold">Gegenereerde SQL</h2>
                <div class="flex gap-2">
                    <button type="button" id="kopieer"
                        class="bg-paneel text-tekst border border-rand rounded-md px-3.5 py-1.5 text-[.85rem] cursor-pointer hover:border-accent hover:text-accent transition-colors">
                        Kopieer SQL
                    </button>
                    <form method="post" class="inline">
                        <input type="hidden" name="download_sql" value="<?= htmlspecialchars($syncSql) ?>">
                        <button type="submit"
                            class="bg-paneel text-tekst border border-rand rounded-md px-3.5 py-1.5 text-[.85rem] cursor-pointer hover:border-accent hover:text-accent transition-colors">
                            Download sync.sql
                        </button>
                    </form>
                </div>
            </div>
            <textarea id="sql-output" readonly spellcheck="false"
                class="w-full min-h-[320px] bg-[#14181e] text-tekst border border-rand rounded-xl p-4 font-mono text-[.85rem] leading-relaxed resize-y"><?= htmlspecialchars($syncSql) ?></textarea>
        </div>

    <?php endif; ?>

</div>

<script src="assets/script.js"></script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>