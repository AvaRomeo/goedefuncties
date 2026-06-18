<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget\Account;
use App\Models\Budget\Category;
use App\Models\Budget\Transaction;
use Illuminate\Http\Request;

class TransactieController extends Controller
{
    public function index()
    {
        return view('transacties.index');
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
