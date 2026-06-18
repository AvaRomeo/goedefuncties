<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget\Account;
use App\Models\Budget\Category;
use App\Models\Budget\Transaction;
use Illuminate\Http\Request;

class TransactieController extends Controller
{
    public function index(Request $request)
    {
        $zoek = $request->input('zoek');

        // Beperk tot accounts van de ingelogde gebruiker (Account heeft global scope op user_id).
        $accountIds = Account::pluck('id');

        if ($zoek) {
            // Omschrijving is versleuteld — filteren in PHP na ophalen.
            $zoekLower = strtolower($zoek);
            $alle = Transaction::with(['account', 'category'])
                ->whereIn('account_id', $accountIds)
                ->orderByDesc('datum')
                ->orderByDesc('id')
                ->get()
                ->filter(fn($t) => str_contains(strtolower((string) $t->omschrijving), $zoekLower));

            $pagina = max(1, (int) $request->input('page', 1));
            $transacties = new \Illuminate\Pagination\LengthAwarePaginator(
                $alle->forPage($pagina, 50)->values(),
                $alle->count(),
                50,
                $pagina,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $transacties = Transaction::with(['account', 'category'])
                ->whereIn('account_id', $accountIds)
                ->orderByDesc('datum')
                ->orderByDesc('id')
                ->paginate(50)
                ->withQueryString();
        }

        return view('transacties.index', compact('transacties', 'zoek'));
    }

    public function aanmaken(Request $request)
    {
        $rekeningen  = Account::orderBy('naam')->get();
        $categorieen = Category::orderBy('naam')->get();
        $rekening_id = $request->input('rekening_id');
        return view('transacties.aanmaken', compact('rekeningen', 'categorieen', 'rekening_id'));
    }

    public function opslaan(Request $request)
    {
        $data = $request->validate([
            'account_id'  => 'required|exists:accounts,id',
            'type'        => 'required|in:inkomst,uitgave',
            'bedrag'      => 'required|numeric|min:0.01',
            'datum'       => 'required|date',
            'omschrijving'=> 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        Transaction::create($data);

        $terugNaar = $request->input('terug_rekening_id')
            ? route('rekeningen.tonen', $request->input('terug_rekening_id'))
            : route('transacties.index');

        return redirect($terugNaar)->with('succes', 'Transactie toegevoegd.');
    }
}
