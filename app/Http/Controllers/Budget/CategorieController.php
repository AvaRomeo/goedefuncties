<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget\Category;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    public function index()
    {
        $categorieen = Category::orderBy('type')->orderBy('naam')->get();
        return view('categorieen.index', compact('categorieen'));
    }

    public function aanmaken()
    {
        return view('categorieen.aanmaken');
    }

    public function opslaan(Request $request)
    {
        $data = $request->validate([
            'naam'  => 'required|string|max:255',
            'type'  => 'required|in:inkomst,uitgave',
            'kleur' => 'required|string',
            'icoon' => 'nullable|string',
        ]);

        $data['trefwoorden'] = $this->parseTrefwoorden($request->input('trefwoorden'));

        Category::create($data);

        return redirect()->route('categorieen.index')->with('succes', 'Categorie aangemaakt.');
    }

    public function bewerken(Category $categorie)
    {
        return view('categorieen.bewerken', compact('categorie'));
    }

    public function bijwerken(Request $request, Category $categorie)
    {
        $data = $request->validate([
            'naam'  => 'required|string|max:255',
            'type'  => 'required|in:inkomst,uitgave',
            'kleur' => 'required|string',
            'icoon' => 'nullable|string',
        ]);

        $data['trefwoorden'] = $this->parseTrefwoorden($request->input('trefwoorden'));

        $categorie->update($data);

        return redirect()->route('categorieen.index')->with('succes', 'Categorie bijgewerkt.');
    }

    public function verwijderen(Category $categorie)
    {
        $categorie->delete();
        return redirect()->route('categorieen.index')->with('succes', 'Categorie verwijderd.');
    }

    private function parseTrefwoorden(?string $invoer): ?array
    {
        if (blank($invoer)) return null;

        return array_values(array_filter(
            array_map('trim', explode(',', strtolower($invoer)))
        ));
    }
}
