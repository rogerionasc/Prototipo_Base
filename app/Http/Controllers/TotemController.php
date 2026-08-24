<?php

namespace App\Http\Controllers;

use App\Models\Totem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TotemController extends Controller
{
    public function index()
    {
        $totens = Totem::with('opcoes')->get();
        return Inertia::render('Clinica/Totens/Index', [
            'totens' => $totens
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'status' => 'boolean',
        ]);

        Totem::create($validated);
        return redirect()->back()->with('success', 'Totem criado com sucesso.');
    }

    public function update(Request $request, Totem $totem)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'status' => 'boolean',
        ]);

        $totem->update($validated);
        return redirect()->back()->with('success', 'Totem atualizado com sucesso.');
    }

    public function destroy(Totem $totem)
    {
        $totem->delete();
        return redirect()->back()->with('success', 'Totem excluído com sucesso.');
    }
}
