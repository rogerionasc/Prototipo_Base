<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndicacaoIncidenciaController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'codigo'   => ['required', 'string', 'max:2', 'unique:indicacao_incidencias,codigo'],
                'descricao' => ['required', 'string', 'max:255'],
            ],
            [
                'codigo.required'  => 'Código é obrigatório.',
                'codigo.unique'    => 'Este código já está cadastrado.',
                'descricao.required' => 'Descrição é obrigatória.',
            ]
        );

        \App\Models\IndicacaoIncidencia::create($data);

        return back()->with('success', 'Indicação de Incidência criada com sucesso!');
    }

    public function update(Request $request, int $id)
    {
        $indicacao = \App\Models\IndicacaoIncidencia::findOrFail($id);

        $data = $request->validate(
            [
                'codigo'    => ['required', 'string', 'max:2', 'unique:indicacao_incidencias,codigo,' . $id],
                'descricao' => ['required', 'string', 'max:255'],
            ],
            [
                'codigo.required'    => 'Código é obrigatório.',
                'codigo.unique'      => 'Este código já está cadastrado.',
                'descricao.required' => 'Descrição é obrigatória.',
            ]
        );

        $indicacao->update($data);

        return back()->with('success', 'Indicação de Incidência atualizada com sucesso!');
    }

    public function destroy(int $id)
    {
        $indicacao = \App\Models\IndicacaoIncidencia::findOrFail($id);
        $indicacao->delete();

        return back()->with('success', 'Indicação de Incidência removida com sucesso!');
    }
}
