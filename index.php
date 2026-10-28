<?php
// Overzicht van alle projecten: elke submap in deze map is een project.
// Mappen die geen project zijn (zoals de gedeelde assets) staan in GEEN_PROJECT.
const GEEN_PROJECT = ['assets'];

$projecten = [];
foreach (scandir(__DIR__) as $map) {
    if ($map[0] === '.' || in_array($map, GEEN_PROJECT, true) || !is_dir(__DIR__ . '/' . $map)) {
        continue;
    }
    $projecten[] = [
        'map'  => $map,
        'naam' => ucfirst(str_replace(['-', '_'], ' ', $map)),
    ];
}

function e(string $tekst): string
{
    return htmlspecialchars($tekst, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Goedefuncties</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/main.js" defer></script>
</head>
<body>
<main>
    <h1>Goedefuncties</h1>
    <p class="ondertitel">Kies een project</p>

    <?php if ($projecten): ?>
        <div class="projecten">
            <?php foreach ($projecten as $project): ?>
                <a class="project" href="<?= e(rawurlencode($project['map'])) ?>/">
                    <strong><?= e($project['naam']) ?></strong>
                    <span><?= e($project['map']) ?>/</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="leeg">Nog geen projecten. Maak een nieuwe map aan in deze map en hij verschijnt hier vanzelf.</p>
    <?php endif; ?>
</main>
</body>
</html>
