<?php

namespace App\Http\Controllers;

use App\Models\TipoAtendimento;
use Illuminate\Http\Request;

class TipoAtendimentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function parametros_store_tipo_atendimento(Request $request)
    {
        $data = $request->validate(
            [
                'codigo'   => ['required', 'string', 'max:2', 'unique:tipo_atendimentos,codigo'],
                'descricao' => ['required', 'string', 'max:255'],
            ],
            [
                'codigo.required'  => 'Código é obrigatório.',
                'codigo.unique'    => 'Este código já está cadastrado.',
                'descricao.required' => 'Descrição é obrigatória.',
            ]
        );

        TipoAtendimento::create($data);

        return back()->with('success', 'Tipo de atendimento criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoAtendimento $tipoAtendimento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function parametros_edit_tipo_atendimento(TipoAtendimento $tipoAtendimento)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function parametros_update_tipo_atendimento(Request $request, int $id)
    {
        $tipoAtendimento = TipoAtendimento::findOrFail($id);

        $data = $request->validate(
            [
                'codigo'    => ['required', 'string', 'max:2', 'unique:tipo_atendimentos,codigo,' . $id],
                'descricao' => ['required', 'string', 'max:255'],
            ],
            [
                'codigo.required'    => 'Código é obrigatório.',
                'codigo.unique'      => 'Este código já está cadastrado.',
                'descricao.required' => 'Descrição é obrigatória.',
            ]
        );

        $tipoAtendimento->update($data);

        return back()->with('success', 'Tipo de atendimento atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function parametros_destroy_tipo_atendimento(int $id)
    {
        $tipoAtendimento = TipoAtendimento::findOrFail($id);
        $tipoAtendimento->delete();

        return back()->with('success', 'Tipo de atendimento removido com sucesso!');
    }
}
