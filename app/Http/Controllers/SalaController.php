<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalaController extends Controller
{
    public function index()
    {
        $salas = Sala::with('profissionalSaude')->get();
        $profissionais = Pessoa::get(['id', 'nome']);

        return Inertia::render('Clinica/Salas/Index', [
            'salas' => $salas,
            'profissionais' => $profissionais,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'status' => 'boolean',
            'pessoa_id' => 'nullable|exists:pessoas,id',
        ]);

        Sala::create($validated);

        return redirect()->route('salas.index')->with('success', 'Sala criada com sucesso.');
    }

    public function update(Request $request, Sala $sala)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'status' => 'boolean',
            'pessoa_id' => 'nullable|exists:pessoas,id',
        ]);

        $sala->update($validated);

        return redirect()->route('salas.index')->with('success', 'Sala atualizada com sucesso.');
    }

    public function destroy(Sala $sala)
    {
        $sala->delete();

        return redirect()->route('salas.index')->with('success', 'Sala excluída com sucesso.');
    }
}
