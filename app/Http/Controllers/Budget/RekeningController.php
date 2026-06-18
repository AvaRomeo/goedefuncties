<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget\Account;
use Illuminate\Http\Request;

class RekeningController extends Controller
{
    public function index()
    {
        $rekeningen = Account::all();
        return view('rekeningen.index', compact('rekeningen'));
    }

    public function aanmaken()
    {
        return view('rekeningen.aanmaken');
    }

    public function opslaan(Request $request)
    {
        $request->validate([
            'naam'       => 'required|string|max:255',
            'type'       => 'required|in:betaal,spaar,overig',
            'bank'       => 'nullable|string',
            'kleur'      => 'required|string',
            'icoon'      => 'required|string',
            'beginsaldo' => 'required|numeric',
        ]);

        Account::create($request->only('naam', 'type', 'bank', 'kleur', 'icoon', 'beginsaldo'));

        return redirect()->route('rekeningen.index')->with('succes', 'Rekening aangemaakt.');
    }

    public function tonen(Account $rekening)
    {
        $transacties = $rekening->transactions()
            ->with('category')
            ->orderByDesc('datum')
            ->orderByDesc('id')
            ->paginate(50);

        return view('rekeningen.tonen', compact('rekening', 'transacties'));
    }

    public function bewerken(Account $rekening)
    {
        return view('rekeningen.bewerken', compact('rekening'));
    }

    public function bijwerken(Request $request, Account $rekening)
    {
        $request->validate([
            'naam'       => 'required|string|max:255',
            'type'       => 'required|in:betaal,spaar,overig',
            'bank'       => 'nullable|string',
            'kleur'      => 'required|string',
            'icoon'      => 'required|string',
            'beginsaldo' => 'required|numeric',
        ]);

        $rekening->update($request->only('naam', 'type', 'bank', 'kleur', 'icoon', 'beginsaldo'));

        return redirect()->route('rekeningen.index')->with('succes', 'Rekening bijgewerkt.');
    }

    public function verwijderen(Account $rekening)
    {
        $rekening->delete();
        return redirect()->route('rekeningen.index')->with('succes', 'Rekening verwijderd.');
    }
}
