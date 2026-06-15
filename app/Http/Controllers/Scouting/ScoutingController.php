<?php

namespace App\Http\Controllers\Scouting;

use App\Http\Controllers\Controller;
use App\Models\Scouting\Kamp;
use App\Models\Scouting\Lid;

class ScoutingController extends Controller
{
    public function index()
    {
        $aantalLeden    = Lid::where('actief', true)->count();
        $komendKampen   = Kamp::where('eind_datum', '>=', today())->orderBy('start_datum')->take(3)->get();
        $aantalKampen   = Kamp::count();

        return view('scouting.home', compact('aantalLeden', 'komendKampen', 'aantalKampen'));
    }
}
