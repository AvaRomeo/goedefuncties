<?php

namespace App\Http\Controllers\Scouting;

use App\Http\Controllers\Controller;
use App\Models\Scouting\Kamp;
use App\Models\Scouting\Kampdeelname;
use Illuminate\Http\Request;

class KampdeelnameController extends Controller
{
    public function opslaan(Request $request, Kamp $kamp)
    {
        $data = $request->validate([
            'lid_id'         => 'required|exists:scouting_leden,id',
            'bedrag'         => 'nullable|numeric|min:0',
            'bijzonderheden' => 'nullable|string',
        ]);

        $kamp->deelnames()->create([
            'lid_id'         => $data['lid_id'],
            'bedrag'         => $data['bedrag'] ?? $kamp->prijs,
            'bijzonderheden' => $data['bijzonderheden'] ?? null,
        ]);

        return redirect()->route('scouting.kampen.tonen', $kamp)->with('succes', 'Deelnemer toegevoegd.');
    }

    public function bijwerken(Request $request, Kampdeelname $deelname)
    {
        $data = $request->validate([
            'bevestigd'      => 'boolean',
            'betaald'        => 'boolean',
            'bedrag'         => 'nullable|numeric|min:0',
            'bijzonderheden' => 'nullable|string',
        ]);

        $deelname->update([
            'bevestigd'      => $request->boolean('bevestigd'),
            'betaald'        => $request->boolean('betaald'),
            'bedrag'         => $data['bedrag'] ?? $deelname->bedrag,
            'bijzonderheden' => $data['bijzonderheden'] ?? $deelname->bijzonderheden,
        ]);

        return redirect()->route('scouting.kampen.tonen', $deelname->kamp_id)->with('succes', 'Bijgewerkt.');
    }

    public function verwijderen(Kampdeelname $deelname)
    {
        $kampId = $deelname->kamp_id;
        $deelname->delete();
        return redirect()->route('scouting.kampen.tonen', $kampId)->with('succes', 'Deelnemer verwijderd.');
    }
}
