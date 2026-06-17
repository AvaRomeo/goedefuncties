<?php

namespace App\Http\Controllers\Scouting;

use App\Http\Controllers\Controller;
use App\Models\Scouting\Leiding;
use Illuminate\Http\Request;

class LeidingController extends Controller
{
    public function index()
    {
        $leiding = Leiding::orderBy('naam')->get();
        return view('scouting.leiding.index', compact('leiding'));
    }

    public function aanmaken()
    {
        return view('scouting.leiding.formulier', ['persoon' => new Leiding()]);
    }

    public function opslaan(Request $request)
    {
        $data = $this->valideer($request);
        $persoon = Leiding::create($data);

        if ($request->wantsJson()) {
            return response()->json(['id' => $persoon->id, 'naam' => $persoon->naam]);
        }

        return redirect()->route('scouting.leiding.index')->with('succes', 'Leidinggevende aangemaakt.');
    }

    public function bewerken(Leiding $persoon)
    {
        return view('scouting.leiding.formulier', compact('persoon'));
    }

    public function bijwerken(Request $request, Leiding $persoon)
    {
        $persoon->update($this->valideer($request));
        return redirect()->route('scouting.leiding.index')->with('succes', 'Leidinggevende bijgewerkt.');
    }

    public function verwijderen(Leiding $persoon)
    {
        $persoon->delete();
        return redirect()->route('scouting.leiding.index')->with('succes', 'Leidinggevende verwijderd.');
    }

    private function valideer(Request $request): array
    {
        return $request->validate([
            'naam' => 'required|string|max:255',
        ]);
    }
}
