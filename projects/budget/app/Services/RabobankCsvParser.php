<?php

namespace App\Services;

class RabobankCsvParser
{
    public function parse(string $inhoud): array
    {
        $regels = array_filter(explode("\n", str_replace("\r\n", "\n", $inhoud)));
        $regels = array_values($regels);

        if (count($regels) < 2) {
            return [];
        }

        $headers = str_getcsv(ltrim($regels[0], "\xEF\xBB\xBF"), ',', '"');
        $headers = array_map('trim', $headers);

        $transacties = [];

        foreach (array_slice($regels, 1) as $regel) {
            if (trim($regel) === '') continue;

            $kolommen = str_getcsv($regel, ',', '"');

            if (count($kolommen) < count($headers)) continue;

            $rij = array_combine($headers, array_slice($kolommen, 0, count($headers)));

            $bedragRaw = trim($rij['Bedrag'] ?? '');
            $bedragRaw = str_replace(',', '.', $bedragRaw);
            $bedrag = (float) $bedragRaw;

            if ($bedrag == 0) continue;

            $omschrijvingDelen = array_filter([
                trim($rij['Naam tegenpartij'] ?? ''),
                trim($rij['Omschrijving-1'] ?? ''),
                trim($rij['Omschrijving-2'] ?? ''),
                trim($rij['Omschrijving-3'] ?? ''),
            ]);

            $transacties[] = [
                'datum'        => trim($rij['Datum'] ?? ''),
                'bedrag'       => abs($bedrag),
                'type'         => $bedrag < 0 ? 'uitgave' : 'inkomst',
                'omschrijving' => mb_substr(implode(' — ', $omschrijvingDelen), 0, 255),
                'category_id'  => null,
            ];
        }

        return $transacties;
    }
}
