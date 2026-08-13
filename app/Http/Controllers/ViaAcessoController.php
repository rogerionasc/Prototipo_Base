<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ViaAcessoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'codigo'   => ['required', 'string', 'max:2', 'unique:via_acessos,codigo'],
                'descricao' => ['required', 'string', 'max:255'],
            ],
            [
                'codigo.required'  => 'Código é obrigatório.',
                'codigo.unique'    => 'Este código já está cadastrado.',
                'descricao.required' => 'Descrição é obrigatória.',
            ]
        );

        \App\Models\ViaAcesso::create($data);

        return back()->with('success', 'Via de Acesso criada com sucesso!');
    }

    public function update(Request $request, int $id)
    {
        $via = \App\Models\ViaAcesso::findOrFail($id);

        $data = $request->validate(
            [
                'codigo'    => ['required', 'string', 'max:2', 'unique:via_acessos,codigo,' . $id],
                'descricao' => ['required', 'string', 'max:255'],
            ],
            [
                'codigo.required'    => 'Código é obrigatório.',
                'codigo.unique'      => 'Este código já está cadastrado.',
                'descricao.required' => 'Descrição é obrigatória.',
            ]
        );

        $via->update($data);

        return back()->with('success', 'Via de Acesso atualizada com sucesso!');
    }

    public function destroy(int $id)
    {
        $via = \App\Models\ViaAcesso::findOrFail($id);
        $via->delete();

        return back()->with('success', 'Via de Acesso removida com sucesso!');
    }
}
