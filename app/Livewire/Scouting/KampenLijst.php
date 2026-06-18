<?php

namespace App\Livewire\Scouting;

use App\Models\Scouting\Kamp;
use Livewire\Component;

class KampenLijst extends Component
{
    public string $filter = 'alles';

    public function verwijderen(int $id): void
    {
        Kamp::findOrFail($id)->delete();
    }

    public function render()
    {
        $query = Kamp::withCount('deelnames')->orderByDesc('start_datum');

        match ($this->filter) {
            'komend'  => $query->where('eind_datum', '>=', now()),
            'geweest' => $query->where('eind_datum', '<', now()),
            default   => null,
        };

        return view('livewire.scouting.kampen-lijst', [
            'kampen' => $query->get(),
        ]);
    }
}
