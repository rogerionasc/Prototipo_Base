<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TipoConsultaController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'codigo'   => ['required', 'string', 'max:2', 'unique:tipo_consultas,codigo'],
                'descricao' => ['required', 'string', 'max:255'],
            ],
            [
                'codigo.required'  => 'Código é obrigatório.',
                'codigo.unique'    => 'Este código já está cadastrado.',
                'descricao.required' => 'Descrição é obrigatória.',
            ]
        );

        \App\Models\TipoConsulta::create($data);

        return back()->with('success', 'Tipo de Consulta criado com sucesso!');
    }

    public function update(Request $request, int $id)
    {
        $tipoConsulta = \App\Models\TipoConsulta::findOrFail($id);

        $data = $request->validate(
            [
                'codigo'    => ['required', 'string', 'max:2', 'unique:tipo_consultas,codigo,' . $id],
                'descricao' => ['required', 'string', 'max:255'],
            ],
            [
                'codigo.required'    => 'Código é obrigatório.',
                'codigo.unique'      => 'Este código já está cadastrado.',
                'descricao.required' => 'Descrição é obrigatória.',
            ]
        );

        $tipoConsulta->update($data);

        return back()->with('success', 'Tipo de Consulta atualizado com sucesso!');
    }

    public function destroy(int $id)
    {
        $tipoConsulta = \App\Models\TipoConsulta::findOrFail($id);
        $tipoConsulta->delete();

        return back()->with('success', 'Tipo de Consulta removido com sucesso!');
    }
}
