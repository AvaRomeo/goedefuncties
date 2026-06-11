<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactieController extends Controller
{
    public function index(Request $request)
    {
        $zoek = $request->input('zoek');

        $transacties = Transaction::with(['account', 'category'])
            ->when($zoek, fn($q) => $q->where('omschrijving', 'like', '%' . $zoek . '%'))
            ->orderByDesc('datum')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

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
