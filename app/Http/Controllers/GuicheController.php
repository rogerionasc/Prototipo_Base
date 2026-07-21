<?php

namespace App\Http\Controllers;

use App\Models\Guiche;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuicheController extends Controller
{
    public function index()
    {
        $guiches = Guiche::all();
        return Inertia::render('Clinica/Guiches/Index', [
            'guiches' => $guiches
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'hostname' => 'nullable|string|max:255',
            'status' => 'boolean',
        ]);

        Guiche::create($validated);
        return redirect()->route('guiches.index')->with('success', 'Guichê criado com sucesso.');
    }

    public function update(Request $request, Guiche $guiche)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'hostname' => 'nullable|string|max:255',
            'status' => 'boolean',
        ]);

        $guiche->update($validated);
        return redirect()->route('guiches.index')->with('success', 'Guichê atualizado com sucesso.');
    }

    public function destroy(Guiche $guiche)
    {
        $guiche->delete();
        return redirect()->route('guiches.index')->with('success', 'Guichê excluído com sucesso.');
    }
}
