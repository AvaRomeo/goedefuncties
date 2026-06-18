<?php

namespace App\Livewire\Scouting;

use App\Models\Scouting\Lid;
use Livewire\Component;

class LedenLijst extends Component
{
    public string $naam = '';

    public function toevoegen(): void
    {
        $this->validate(['naam' => 'required|string|max:255']);
        Lid::create(['naam' => $this->naam]);
        $this->naam = '';
    }

    public function verwijderen(int $id): void
    {
        Lid::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.scouting.leden-lijst', [
            'leden' => Lid::orderBy('naam')->get(),
        ]);
    }
}
