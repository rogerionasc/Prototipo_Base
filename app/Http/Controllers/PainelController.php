<?php

namespace App\Http\Controllers;

use App\Models\Painel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PainelController extends Controller
{
    public function index()
    {
        $paineis = Painel::all();
        return Inertia::render('Clinica/Paineis/Index', [
            'paineis' => $paineis
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'status' => 'boolean',
        ]);

        Painel::create($validated);
        return redirect()->route('paineis.index')->with('success', 'Painel criado com sucesso.');
    }

    public function update(Request $request, Painel $painel)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'status' => 'boolean',
        ]);

        $painel->update($validated);
        return redirect()->route('paineis.index')->with('success', 'Painel atualizado com sucesso.');
    }

    public function destroy(Painel $painel)
    {
        $painel->delete();
        return redirect()->route('paineis.index')->with('success', 'Painel excluído com sucesso.');
    }
}
