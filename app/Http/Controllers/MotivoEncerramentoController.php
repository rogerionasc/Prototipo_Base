<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MotivoEncerramentoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'codigo'   => ['required', 'string', 'max:2', 'unique:motivo_encerramentos,codigo'],
                'descricao' => ['required', 'string', 'max:255'],
            ],
            [
                'codigo.required'  => 'Código é obrigatório.',
                'codigo.unique'    => 'Este código já está cadastrado.',
                'descricao.required' => 'Descrição é obrigatória.',
            ]
        );

        \App\Models\MotivoEncerramento::create($data);

        return back()->with('success', 'Motivo de Encerramento criado com sucesso!');
    }

    public function update(Request $request, int $id)
    {
        $motivo = \App\Models\MotivoEncerramento::findOrFail($id);

        $data = $request->validate(
            [
                'codigo'    => ['required', 'string', 'max:2', 'unique:motivo_encerramentos,codigo,' . $id],
                'descricao' => ['required', 'string', 'max:255'],
            ],
            [
                'codigo.required'    => 'Código é obrigatório.',
                'codigo.unique'      => 'Este código já está cadastrado.',
                'descricao.required' => 'Descrição é obrigatória.',
            ]
        );

        $motivo->update($data);

        return back()->with('success', 'Motivo de Encerramento atualizado com sucesso!');
    }

    public function destroy(int $id)
    {
        $motivo = \App\Models\MotivoEncerramento::findOrFail($id);
        $motivo->delete();

        return back()->with('success', 'Motivo de Encerramento removido com sucesso!');
    }
}
