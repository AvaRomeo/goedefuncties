<?php

namespace App\Http\Controllers;

use App\Models\Transaction;

class TransactieController extends Controller
{
    public function index()
    {
        $transacties = Transaction::with(['account', 'category'])
            ->orderByDesc('datum')
            ->orderByDesc('id')
            ->paginate(50);

        return view('transacties.index', compact('transacties'));
    }
}
