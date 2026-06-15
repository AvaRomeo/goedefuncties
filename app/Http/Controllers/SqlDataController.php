<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SqlDataController extends Controller
{
    private const PER_PAGINA = 50;

    public function index(Request $request)
    {
        $tabellen      = session('sql_tabellen', []);
        $bestandsnaam  = session('sql_bestandsnaam');
        $tabel         = $request->query('tabel');
        $kolomNamen    = [];
        $rijen         = [];
        $totaalRijen   = 0;
        $pagina        = 1;
        $totaalPaginas = 1;

        $filters = array_filter((array) $request->query('f', []), fn($v) => $v !== '' && $v !== null);

        if ($tabel && $this->heeftTempBestand()) {
            [$kolomNamen, $alleRijen] = $this->laadGefilterd($tabel, $filters);
            $totaalRijen   = count($alleRijen);
            $totaalPaginas = max(1, (int) ceil($totaalRijen / self::PER_PAGINA));
            $pagina        = min(max(1, (int) $request->query('pagina', 1)), $totaalPaginas);
            $rijen         = array_slice($alleRijen, ($pagina - 1) * self::PER_PAGINA, self::PER_PAGINA);
        }

        $perPagina = self::PER_PAGINA;

        return view('sql-data.index', compact('tabellen', 'bestandsnaam', 'tabel', 'kolomNamen', 'rijen', 'totaalRijen', 'pagina', 'totaalPaginas', 'perPagina', 'filters'));
    }

    public function uploaden(Request $request)
    {
        $request->validate([
            'dump' => 'required|file|max:102400',
        ], [
            'dump.required' => 'Selecteer een SQL-bestand.',
            'dump.max'      => 'Het bestand mag maximaal 100 MB zijn.',
        ]);

        $sql      = file_get_contents($request->file('dump')->getRealPath());
        $sql      = str_replace(["\r\n", "\r"], "\n", $sql); // normaliseer regeleinden
        $tabellen = $this->parseerTabellen($sql);

        if (empty($tabellen)) {
            return back()->withErrors(['dump' => 'Geen tabellen of INSERT-statements gevonden in dit bestand.']);
        }

        $this->ruimOudeTempBestandenOp();

        $sleutel = Str::uuid() . '.sql';
        Storage::put("sql-temp/{$sleutel}", $sql);

        session([
            'sql_temp_sleutel' => $sleutel,
            'sql_bestandsnaam' => $request->file('dump')->getClientOriginalName(),
            'sql_tabellen'     => $tabellen,
        ]);

        return redirect()->route('sql-data.index');
    }

    public function genereren(Request $request)
    {
        $tabel   = $request->query('tabel');
        $filters = array_filter((array) $request->query('f', []), fn($v) => $v !== '' && $v !== null);

        if (!$tabel || !$this->heeftTempBestand()) {
            abort(404);
        }

        [$kolomNamen, $rijen] = $this->laadGefilterd($tabel, $filters);

        if (empty($rijen)) {
            return redirect()->route('sql-data.index', array_merge(['tabel' => $tabel], $filters ? ['f' => $filters] : []))
                ->withErrors(['dump' => 'Geen rijen om te exporteren.']);
        }

        return response($this->bouwInsertSql($tabel, $kolomNamen, $rijen), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    private function laadGefilterd(string $tabel, array $filters): array
    {
        $sql = str_replace(["\r\n", "\r"], "\n", Storage::get('sql-temp/' . session('sql_temp_sleutel')));
        [$kolomNamen, $alleRijen] = $this->parseerTabelData($sql, $tabel);

        if (!empty($filters)) {
            $alleRijen = array_values(array_filter($alleRijen, function ($rij) use ($filters, $kolomNamen) {
                foreach ($filters as $kol => $zoekterm) {
                    $index = is_numeric($kol) ? (int) $kol : array_search($kol, $kolomNamen);
                    if ($index === false || $index === null) continue;
                    if (stripos((string) ($rij[$index] ?? ''), $zoekterm) === false) return false;
                }
                return true;
            }));
        }

        return [$kolomNamen, $alleRijen];
    }

    private function bouwInsertSql(string $tabel, array $kolomNamen, array $rijen): string
    {
        $kolomDeel = $kolomNamen ? ' (`' . implode('`, `', $kolomNamen) . '`)' : '';
        $waarden   = array_map(function ($rij) {
            return '(' . implode(', ', array_map(fn($w) => $this->formateerSqlWaarde($w), $rij)) . ')';
        }, $rijen);

        return 'INSERT INTO `' . $tabel . '`' . $kolomDeel . ' VALUES' . "\n"
            . implode(",\n", $waarden) . ";\n";
    }

    private function formateerSqlWaarde(mixed $waarde): string
    {
        if ($waarde === null) return 'NULL';
        $str = (string) $waarde;
        if ($str !== '' && is_numeric($str)) return $str;
        return "'" . str_replace(['\\', "'", "\n", "\r", "\0"], ['\\\\', "\\'", '\\n', '\\r', '\\0'], $str) . "'";
    }

    private function heeftTempBestand(): bool
    {
        $sleutel = session('sql_temp_sleutel');
        return $sleutel && Storage::exists("sql-temp/{$sleutel}");
    }

    private function parseerTabellen(string $sql): array
    {
        $tabellen = [];
        preg_match_all('/^INSERT INTO `([^`]+)`/mi', $sql, $matches);
        foreach ($matches[1] as $naam) {
            $tabellen[$naam] = ($tabellen[$naam] ?? 0) + 1;
        }
        ksort($tabellen);
        return $tabellen;
    }

    private function parseerTabelData(string $sql, string $tabel): array
    {
        $escaped    = preg_quote($tabel, '/');
        $kolomNamen = [];

        // Kolommen uit CREATE TABLE
        if (preg_match('/CREATE TABLE `' . $escaped . '`\s*\((.*?)\)\s*(?:ENGINE|;)/si', $sql, $m)) {
            preg_match_all('/^\s*`([^`]+)`/m', $m[1], $cols);
            $kolomNamen = array_values(array_filter(
                $cols[1],
                fn($k) => !preg_match('/^(PRIMARY|UNIQUE|KEY|INDEX|CONSTRAINT|CHECK|FOREIGN)$/i', $k)
            ));
        }

        // Rijen uit INSERT INTO — positie-gebaseerd zodat multi-line VALUES werken
        $rijen = [];
        preg_match_all(
            '/^INSERT\s+INTO\s+`' . $escaped . '`\s*(?:\([^)]*\)\s*)?VALUES\s*/mi',
            $sql,
            $insertMatches,
            PREG_OFFSET_CAPTURE
        );

        foreach ($insertMatches[0] as [$match, $offset]) {
            $pos = $offset + strlen($match);
            if ($pos < strlen($sql) && $sql[$pos] === '(') {
                $valuesPart = $this->verzamelValuesPart($sql, $pos);
                foreach ($this->splitRijen($valuesPart) as $rij) {
                    $rijen[] = $this->parseRij($rij);
                }
            }
        }

        // Kolommen uit INSERT als CREATE TABLE ontbreekt
        if (empty($kolomNamen) && !empty($rijen)) {
            if (preg_match('/^INSERT INTO `' . $escaped . '`\s*\(([^)]+)\)\s*VALUES/mi', $sql, $colMatch)) {
                preg_match_all('/`([^`]+)`/', $colMatch[1], $colNames);
                $kolomNamen = $colNames[1];
            }
        }

        return [$kolomNamen, $rijen];
    }

    private function verzamelValuesPart(string $sql, int $start): string
    {
        $len     = strlen($sql);
        $diepte  = 0;
        $inStr   = false;
        $escaped = false;

        for ($i = $start; $i < $len; $i++) {
            $c = $sql[$i];

            if ($escaped) { $escaped = false; continue; }
            if ($c === '\\' && $inStr) { $escaped = true; continue; }
            if ($c === "'" && !$inStr) { $inStr = true; continue; }
            if ($c === "'" && $inStr) {
                if (isset($sql[$i + 1]) && $sql[$i + 1] === "'") { $i++; continue; }
                $inStr = false; continue;
            }

            if (!$inStr) {
                if ($c === '(') {
                    $diepte++;
                } elseif ($c === ')') {
                    $diepte--;
                    if ($diepte === 0) {
                        // Kijk of er een volgende rij-groep volgt (komma + haakje openen)
                        $j = $i + 1;
                        while ($j < $len && ($sql[$j] === ',' || $sql[$j] === ' ' || $sql[$j] === "\t" || $sql[$j] === "\n" || $sql[$j] === "\r")) {
                            $j++;
                        }
                        if ($j < $len && $sql[$j] === '(') {
                            $i = $j - 1; // ga door naar de volgende rij-groep
                            continue;
                        }
                        return substr($sql, $start, $i - $start + 1);
                    }
                }
            }
        }

        return substr($sql, $start);
    }

    private function splitRijen(string $valuesPart): array
    {
        $rijen   = [];
        $diepte  = 0;
        $huidig  = '';
        $inStr   = false;
        $escaped = false;

        for ($i = 0, $len = strlen($valuesPart); $i < $len; $i++) {
            $c = $valuesPart[$i];

            if ($escaped) { $huidig .= $c; $escaped = false; continue; }
            if ($c === '\\' && $inStr) { $escaped = true; $huidig .= $c; continue; }
            if ($c === "'" && !$inStr) { $inStr = true; $huidig .= $c; continue; }
            if ($c === "'" && $inStr)  { $inStr = false; $huidig .= $c; continue; }

            if (!$inStr) {
                if ($c === '(') {
                    if (++$diepte === 1) { $huidig = ''; continue; }
                } elseif ($c === ')') {
                    if (--$diepte === 0) { $rijen[] = $huidig; $huidig = ''; continue; }
                }
            }

            $huidig .= $c;
        }

        return $rijen;
    }

    private function parseRij(string $rij): array
    {
        $waarden = [];
        $huidig  = '';
        $inStr   = false;
        $escaped = false;

        for ($i = 0, $len = strlen($rij); $i < $len; $i++) {
            $c = $rij[$i];

            if ($escaped) {
                $huidig .= match($c) { 'n' => "\n", 'r' => "\r", 't' => "\t", default => $c };
                $escaped = false;
                continue;
            }
            if ($c === '\\' && $inStr) { $escaped = true; continue; }
            if ($c === "'" && !$inStr) { $inStr = true; continue; }
            if ($c === "'" && $inStr) {
                if (isset($rij[$i + 1]) && $rij[$i + 1] === "'") { $huidig .= "'"; $i++; continue; }
                $inStr = false;
                continue;
            }
            if ($c === ',' && !$inStr) {
                $waarden[] = $huidig === 'NULL' ? null : $huidig;
                $huidig = '';
                continue;
            }

            $huidig .= $c;
        }

        $waarden[] = $huidig === 'NULL' ? null : $huidig;
        return $waarden;
    }

    private function ruimOudeTempBestandenOp(): void
    {
        foreach (Storage::files('sql-temp') as $bestand) {
            if (Storage::lastModified($bestand) < now()->subHours(2)->timestamp) {
                Storage::delete($bestand);
            }
        }
    }
}
