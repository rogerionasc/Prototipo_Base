<?php

namespace App\Http\Controllers;

use App\Models\Especialidade;
use App\Models\Procedimento;
use App\Models\Tuss;
use Illuminate\Http\Request;

class EspecialidadeController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'codigo' => ['nullable', 'string', 'max:50'],
            'descricao' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
            'procedimentos_ids' => ['nullable', 'array'],
            'procedimentos_ids.*' => ['exists:procedimentos,id'],
        ]);

        $data['ativo'] = isset($data['ativo']) ? (bool)$data['ativo'] : true;
        $procedimentosIds = $data['procedimentos_ids'] ?? [];
        unset($data['procedimentos_ids']);

        $esp = Especialidade::create($data);

        if (!empty($procedimentosIds)) {
            $esp->procedimentos()->sync($procedimentosIds);
        }

        return back()->with('success', 'Especialidade cadastrada');
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'codigo' => ['nullable', 'string', 'max:50'],
            'descricao' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
            'procedimentos_ids' => ['nullable', 'array'],
            'procedimentos_ids.*' => ['exists:procedimentos,id'],
        ]);

        $esp = Especialidade::findOrFail($id);
        $procedimentosIds = $data['procedimentos_ids'] ?? [];
        unset($data['procedimentos_ids']);

        $esp->update($data);

        $esp->procedimentos()->sync($procedimentosIds);

        return back()->with('success', 'Especialidade atualizada');
    }

    public function destroy(string $id)
    {
        $esp = Especialidade::findOrFail($id);
        $esp->delete();
        return back()->with('success', 'Especialidade excluída');
    }
}
