<?php

namespace App\Http\Controllers\Scouting;

use App\Http\Controllers\Controller;
use App\Models\Scouting\Lid;
use Illuminate\Http\Request;

class LidController extends Controller
{
    public function index()
    {
        $leden = Lid::orderBy('naam')->get();
        return view('scouting.leden.index', compact('leden'));
    }

    public function aanmaken()
    {
        return view('scouting.leden.formulier', ['lid' => new Lid()]);
    }

    public function opslaan(Request $request)
    {
        $data = $this->valideer($request);
        Lid::create($data);
        return redirect()->route('scouting.leden.index')->with('succes', 'Lid aangemaakt.');
    }

    public function bewerken(Lid $lid)
    {
        return view('scouting.leden.formulier', compact('lid'));
    }

    public function bijwerken(Request $request, Lid $lid)
    {
        $lid->update($this->valideer($request));
        return redirect()->route('scouting.leden.index')->with('succes', 'Lid bijgewerkt.');
    }

    public function verwijderen(Lid $lid)
    {
        $lid->delete();
        return redirect()->route('scouting.leden.index')->with('succes', 'Lid verwijderd.');
    }

    private function valideer(Request $request): array
    {
        return $request->validate([
            'naam'            => 'required|string|max:255',
            'geboortedatum'   => 'nullable|date',
            'speltak'         => 'required|string|max:100',
            'email_ouder'     => 'nullable|email|max:255',
            'telefoon_ouder'  => 'nullable|string|max:50',
            'actief'          => 'boolean',
            'opmerkingen'     => 'nullable|string',
        ]);
    }
}
