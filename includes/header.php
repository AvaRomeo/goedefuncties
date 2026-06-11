<?php $pageTitle = $pageTitle ?? 'Kleine Projecten'; ?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" href="<?= $rootPath ?>favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="<?= $rootPath ?>assets/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= $rootPath ?>assets/tailwind.config.js"></script>
    <style>
        .site-nav {
            background: #262c36;
            border-bottom: 1px solid #3a414d;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 0.88rem;
            flex-wrap: wrap;
        }

        .site-nav a {
            color: #4cc38a;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .site-nav a:hover {
            text-decoration: underline;
        }

        .site-nav .sep {
            color: #3a414d;
            padding: 0 4px;
        }

        .site-nav .nav-actief {
            color: #9aa4b2;
            padding: 0 2px;
        }
    </style>
    <?= $headExtra ?? '' ?>
</head>

<body>
    <?php
    $navProjecten = [
        [
            'naam'         => 'SQL Vergelijker',
            'href'         => 'projects/SQL_compare/db_vergelijker.php',
            'slug'         => 'SQL_compare',
            'emoji'        => '🗄️',
            'beschrijving' => 'Vergelijk twee databasestructuren en bekijk de verschillen tussen tabellen en kolommen.',
            'tag'          => 'SQL / PHP',
        ],
        [
            'naam'         => 'Budget',
            'href'         => 'projects/budget/public/index.php',
            'slug'         => 'budget',
            'emoji'        => '💰',
            'beschrijving' => 'Beheer je inkomsten en uitgaven, importeer transacties en houd je budget bij.',
            'tag'          => 'PHP / MySQL',
        ],
        [
            'naam'         => 'GPX Viewer',
            'href'         => 'projects/gpx_viewer/index.php',
            'slug'         => 'gpx_viewer',
            'emoji'        => '🗺️',
            'beschrijving' => 'Laad een GPX-bestand en bekijk de route op een interactieve kaart met statistieken.',
            'tag'          => 'Leaflet / GPX',
        ],
    ];

    $huidigUri = $_SERVER['REQUEST_URI'] ?? '';
    ?>
    <nav class="site-nav">

        <a href="<?= htmlspecialchars($rootPath) ?>">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
            </svg>
            Home
        </a>

        <?php foreach ($navProjecten as $project): ?>
            <span class="sep">·</span>
            <?php if (str_contains($huidigUri, $project['slug'])): ?>
                <span class="nav-actief"><?= htmlspecialchars($project['naam']) ?></span>
            <?php else: ?>
                <a href="<?= htmlspecialchars($rootPath . $project['href']) ?>">
                    <?= htmlspecialchars($project['naam']) ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>

    </nav>