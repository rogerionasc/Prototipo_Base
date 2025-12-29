<?php

namespace App\Http\Controllers;

use App\Models\Especialidade;
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
        ]);
        $data['ativo'] = isset($data['ativo']) ? (bool)$data['ativo'] : true;
        Especialidade::create($data);
        return back()->with('success', 'Especialidade cadastrada');
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'codigo' => ['nullable', 'string', 'max:50'],
            'descricao' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
        ]);
        $esp = Especialidade::findOrFail($id);
        $esp->update($data);
        return back()->with('success', 'Especialidade atualizada');
    }

    public function destroy(string $id)
    {
        $esp = Especialidade::findOrFail($id);
        $esp->delete();
        return back()->with('success', 'Especialidade excluída');
    }
}
