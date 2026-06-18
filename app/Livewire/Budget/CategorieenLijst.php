<?php

namespace App\Livewire\Budget;

use App\Models\Budget\Category;
use Livewire\Component;

class CategorieenLijst extends Component
{
    public string $zoek = '';

    public function verwijderen(int $id): void
    {
        Category::findOrFail($id)->delete();
    }

    public function render()
    {
        $categorieen = Category::orderBy('type')->orderBy('naam')->get();

        if ($this->zoek !== '') {
            $zoek = strtolower($this->zoek);
            $categorieen = $categorieen->filter(fn($c) => str_contains(strtolower($c->naam), $zoek));
        }

        return view('livewire.budget.categorieen-lijst', compact('categorieen'));
    }
}
