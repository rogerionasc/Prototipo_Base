<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GrauParticipacaoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'codigo'   => ['required', 'string', 'max:2', 'unique:grau_participacaos,codigo'],
                'descricao' => ['required', 'string', 'max:255'],
            ],
            [
                'codigo.required'  => 'Código é obrigatório.',
                'codigo.unique'    => 'Este código já está cadastrado.',
                'descricao.required' => 'Descrição é obrigatória.',
            ]
        );

        \App\Models\GrauParticipacao::create($data);

        return back()->with('success', 'Grau de Participação criado com sucesso!');
    }

    public function update(Request $request, int $id)
    {
        $grau = \App\Models\GrauParticipacao::findOrFail($id);

        $data = $request->validate(
            [
                'codigo'    => ['required', 'string', 'max:2', 'unique:grau_participacaos,codigo,' . $id],
                'descricao' => ['required', 'string', 'max:255'],
            ],
            [
                'codigo.required'    => 'Código é obrigatório.',
                'codigo.unique'      => 'Este código já está cadastrado.',
                'descricao.required' => 'Descrição é obrigatória.',
            ]
        );

        $grau->update($data);

        return back()->with('success', 'Grau de Participação atualizado com sucesso!');
    }

    public function destroy(int $id)
    {
        $grau = \App\Models\GrauParticipacao::findOrFail($id);
        $grau->delete();

        return back()->with('success', 'Grau de Participação removido com sucesso!');
    }
}
