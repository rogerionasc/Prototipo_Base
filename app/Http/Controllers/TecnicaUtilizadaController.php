<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TecnicaUtilizadaController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'codigo'   => ['required', 'string', 'max:2', 'unique:tecnica_utilizadas,codigo'],
                'descricao' => ['required', 'string', 'max:255'],
            ],
            [
                'codigo.required'  => 'Código é obrigatório.',
                'codigo.unique'    => 'Este código já está cadastrado.',
                'descricao.required' => 'Descrição é obrigatória.',
            ]
        );

        \App\Models\TecnicaUtilizada::create($data);

        return back()->with('success', 'Técnica Utilizada criada com sucesso!');
    }

    public function update(Request $request, int $id)
    {
        $tecnica = \App\Models\TecnicaUtilizada::findOrFail($id);

        $data = $request->validate(
            [
                'codigo'    => ['required', 'string', 'max:2', 'unique:tecnica_utilizadas,codigo,' . $id],
                'descricao' => ['required', 'string', 'max:255'],
            ],
            [
                'codigo.required'    => 'Código é obrigatório.',
                'codigo.unique'      => 'Este código já está cadastrado.',
                'descricao.required' => 'Descrição é obrigatória.',
            ]
        );

        $tecnica->update($data);

        return back()->with('success', 'Técnica Utilizada atualizada com sucesso!');
    }

    public function destroy(int $id)
    {
        $tecnica = \App\Models\TecnicaUtilizada::findOrFail($id);
        $tecnica->delete();

        return back()->with('success', 'Técnica Utilizada removida com sucesso!');
    }
}
