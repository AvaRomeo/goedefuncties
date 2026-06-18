<?php

namespace App\Livewire\Budget;

use App\Models\Budget\Account;
use App\Models\Budget\Transaction;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TransactieZoeker extends Component
{
    use WithPagination;

    #[Url(as: 'zoek', except: '')]
    public string $zoek = '';

    public function updatingZoek(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $accountIds = Account::pluck('id');

        if ($this->zoek !== '') {
            $zoekLower = strtolower($this->zoek);
            $transacties = Transaction::with(['account', 'category'])
                ->whereIn('account_id', $accountIds)
                ->orderByDesc('datum')
                ->orderByDesc('id')
                ->get()
                ->filter(fn($t) => str_contains(strtolower((string) $t->omschrijving), $zoekLower))
                ->values();
        } else {
            $transacties = Transaction::with(['account', 'category'])
                ->whereIn('account_id', $accountIds)
                ->orderByDesc('datum')
                ->orderByDesc('id')
                ->paginate(50);
        }

        return view('livewire.budget.transactie-zoeker', compact('transacties'));
    }
}
