<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use App\Models\ProfissionalSaude;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalaController extends Controller
{
    public function index()
    {
        $salas = Sala::with('profissionalSaude')->get();
        $profissionais = ProfissionalSaude::get(['id', 'nome']);

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
            'profissional_saude_id' => 'nullable|exists:profissionais_saude,id',
        ]);

        Sala::create($validated);

        return redirect()->route('salas.index')->with('success', 'Sala criada com sucesso.');
    }

    public function update(Request $request, Sala $sala)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'status' => 'boolean',
            'profissional_saude_id' => 'nullable|exists:profissionais_saude,id',
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
