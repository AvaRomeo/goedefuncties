<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Services\BunqCsvParser;
use App\Services\RabobankCsvParser;
use Illuminate\Http\Request;

class TransactieImportController extends Controller
{
    public function aanmaken()
    {
        $rekeningen = Account::orderBy('naam')->get();
        return view('transacties.importeren', compact('rekeningen'));
    }

    public function opslaan(Request $request)
    {
        $request->validate([
            'rekening_id' => 'required|exists:accounts,id',
            'csv'         => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $rekening = Account::findOrFail($request->rekening_id);
        $inhoud   = file_get_contents($request->file('csv')->getRealPath());
        $parser   = $this->parserVoorBank($rekening->bank);

        if ($parser === null) {
            return back()->withErrors(['csv' => "Geen importformaat bekend voor bank '{$rekening->bank}'. Stel de bank in op de rekening."]);
        }

        $transacties = $parser->parse($inhoud);
        $aangemaakt  = 0;
        $overgeslagen = 0;

        foreach ($transacties as $data) {
            $omschrijving = $data['omschrijving'] !== '' ? $data['omschrijving'] : null;

            $bestaatAl = Transaction::where('account_id', $rekening->id)
                ->whereDate('datum', $data['datum'])
                ->where('bedrag', round($data['bedrag'], 2))
                ->where('type', $data['type'])
                ->where(function ($q) use ($omschrijving) {
                    $omschrijving === null
                        ? $q->whereNull('omschrijving')
                        : $q->where('omschrijving', $omschrijving);
                })
                ->exists();

            if ($bestaatAl) {
                $overgeslagen++;
                continue;
            }

            Transaction::create([
                'account_id'   => $rekening->id,
                'category_id'  => null,
                'type'         => $data['type'],
                'bedrag'       => round($data['bedrag'], 2),
                'datum'        => $data['datum'],
                'omschrijving' => $omschrijving,
            ]);

            $aangemaakt++;
        }

        $bericht = "{$aangemaakt} transactie(s) geïmporteerd.";
        if ($overgeslagen > 0) {
            $bericht .= " {$overgeslagen} overgeslagen (al aanwezig).";
        }

        return redirect()->route('transacties.index')->with('succes', $bericht);
    }

    private function parserVoorBank(?string $bank): RabobankCsvParser|BunqCsvParser|null
    {
        return match (strtolower((string) $bank)) {
            'rabobank' => new RabobankCsvParser(),
            'bunq'     => new BunqCsvParser(),
            default    => null,
        };
    }
}
