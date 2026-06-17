<?php

namespace App\Http\Controllers\Scouting;

use App\Http\Controllers\Controller;
use App\Models\Scouting\Kamp;
use App\Models\Scouting\Lid;
use App\Models\Scouting\Leiding;

class ScoutingController extends Controller
{
    public function index()
    {
        $aantalLeden    = Lid::count();
        $aantalLeiding  = Leiding::count();
        $komendKampen   = Kamp::where('eind_datum', '>=', today())->orderBy('start_datum')->take(3)->get();
        $aantalKampen   = Kamp::count();

        return view('scouting.home', compact('aantalLeden', 'aantalLeiding', 'komendKampen', 'aantalKampen'));
    }
}
