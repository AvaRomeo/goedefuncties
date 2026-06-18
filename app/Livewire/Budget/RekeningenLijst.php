<?php

namespace App\Livewire\Budget;

use App\Models\Budget\Account;
use Livewire\Component;

class RekeningenLijst extends Component
{
    public function verwijderen(int $id): void
    {
        Account::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.budget.rekeningen-lijst', [
            'rekeningen' => Account::all(),
        ]);
    }
}
