<?php

function parseDump(string $sql): array
{
    $tables = [];

    preg_match_all(
        '/CREATE TABLE (?:IF NOT EXISTS )?`([^`]+)`\s*\((.*?)\)\s*ENGINE[^;]*;/s',
        $sql,
        $matches,
        PREG_SET_ORDER
    );

    foreach ($matches as $m) {
        $columns = [];
        foreach (preg_split('/,\r?\n/', $m[2]) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] !== '`') {
                continue;
            }
            if (preg_match('/^`([^`]+)`\s+(.*)$/s', $line, $cm)) {
                $columns[$cm[1]] = preg_replace('/\s+/', ' ', rtrim(trim($cm[2]), ','));
            }
        }
        $tables[$m[1]] = ['create' => $m[0], 'columns' => $columns];
    }

    return $tables;
}

function vergelijk(array $source, array $target): array
{
    $sqlOut = [];
    $items  = [];

    foreach ($source as $table => $info) {

        if (!isset($target[$table])) {
            $sqlOut[] = "-- Tabel `$table` ontbreekt in doel";
            $sqlOut[] = $info['create'];
            $sqlOut[] = "";
            $items[]  = ['type' => 'tabel', 'tabel' => $table, 'kolom' => '—',
                         'omschrijving' => 'Tabel ontbreekt in doel → wordt aangemaakt'];
            continue;
        }

        $prev = null;
        foreach ($info['columns'] as $colName => $def) {
            $position = $prev ? "AFTER `$prev`" : "FIRST";

            if (!isset($target[$table]['columns'][$colName])) {
                $stmt     = "ALTER TABLE `$table` ADD COLUMN `$colName` $def $position;";
                $sqlOut[] = "-- Kolom `$colName` ontbreekt in `$table`";
                $sqlOut[] = $stmt;
                $sqlOut[] = "";
                $items[]  = ['type' => 'add', 'tabel' => $table, 'kolom' => $colName,
                             'omschrijving' => 'Kolom ontbreekt in live → wordt toegevoegd'];
            }

            $prev = $colName;
        }

        foreach ($target[$table]['columns'] as $colName => $def) {
            if (!isset($info['columns'][$colName])) {
                $sqlOut[] = "-- LET OP: kolom `$colName` bestaat in doel `$table` maar niet in bron (niet verwijderd)";
                $sqlOut[] = "";
                $items[]  = ['type' => 'only-dev', 'tabel' => $table, 'kolom' => $colName,
                             'omschrijving' => 'Staat alleen in live — niet aanwezig in dev'];
            }
        }
    }

    foreach ($target as $table => $info) {
        if (!isset($source[$table])) {
            $sqlOut[] = "-- LET OP: tabel `$table` bestaat alleen in dev (niet verwijderd)";
            $sqlOut[] = "";
            $items[]  = ['type' => 'only-dev', 'tabel' => $table, 'kolom' => '—',
                         'omschrijving' => 'Tabel staat alleen in live — niet aanwezig in dev'];
        }
    }

    return [$sqlOut, $items];
}
