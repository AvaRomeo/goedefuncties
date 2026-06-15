<?php

namespace App\Http\Controllers\Scouting;

use App\Http\Controllers\Controller;
use App\Models\Scouting\Kamp;
use App\Models\Scouting\Lid;
use Illuminate\Http\Request;

class KampController extends Controller
{
    public function index()
    {
        $kampen = Kamp::withCount('deelnames')->orderBy('start_datum', 'desc')->get();
        return view('scouting.kampen.index', compact('kampen'));
    }

    public function aanmaken()
    {
        return view('scouting.kampen.formulier', ['kamp' => new Kamp()]);
    }

    public function opslaan(Request $request)
    {
        $data = $this->valideer($request);
        $kamp = Kamp::create($data);
        return redirect()->route('scouting.kampen.tonen', $kamp)->with('succes', 'Kamp aangemaakt.');
    }

    public function tonen(Kamp $kamp)
    {
        $kamp->load('deelnames.lid');
        $lidIdsInKamp = $kamp->deelnames->pluck('lid_id');
        $beschikbareLeden = Lid::where('actief', true)
            ->whereNotIn('id', $lidIdsInKamp)
            ->orderBy('naam')
            ->get();

        return view('scouting.kampen.tonen', compact('kamp', 'beschikbareLeden'));
    }

    public function bewerken(Kamp $kamp)
    {
        return view('scouting.kampen.formulier', compact('kamp'));
    }

    public function bijwerken(Request $request, Kamp $kamp)
    {
        $kamp->update($this->valideer($request));
        return redirect()->route('scouting.kampen.tonen', $kamp)->with('succes', 'Kamp bijgewerkt.');
    }

    public function verwijderen(Kamp $kamp)
    {
        $kamp->delete();
        return redirect()->route('scouting.kampen.index')->with('succes', 'Kamp verwijderd.');
    }

    private function valideer(Request $request): array
    {
        return $request->validate([
            'naam'        => 'required|string|max:255',
            'start_datum' => 'required|date',
            'eind_datum'  => 'required|date|after_or_equal:start_datum',
            'locatie'     => 'nullable|string|max:255',
            'beschrijving'=> 'nullable|string',
            'prijs'       => 'nullable|numeric|min:0',
        ]);
    }
}
