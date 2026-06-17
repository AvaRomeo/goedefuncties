<?php

namespace App\Http\Controllers\Scouting;

use App\Http\Controllers\Controller;
use App\Models\Scouting\Kamp;
use App\Models\Scouting\Kampleiding;
use Illuminate\Http\Request;

class KampleidingController extends Controller
{
    public function opslaan(Request $request, Kamp $kamp)
    {
        $data = $request->validate([
            'leiding_id' => 'required|exists:scouting_leiding,id',
        ]);

        $kamp->kampleiding()->create($data);

        return redirect()->route('scouting.kampen.tonen', $kamp)->with('succes', 'Leiding toegevoegd.');
    }

    public function verwijderen(Kampleiding $kampleiding)
    {
        $kampId = $kampleiding->kamp_id;
        $kampleiding->delete();
        return redirect()->route('scouting.kampen.tonen', $kampId)->with('succes', 'Leiding verwijderd.');
    }
}
