<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Caixa;

class CaixaController extends Controller
{
    public function create()
    {
        $caixas = Caixa::select('id','descricao','tipo','bloquear_receber','bloquear_pagar','ativo')->get();
        return Inertia::render('Financeiro/Caixa/Index', [
            'caixas' => $caixas,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'descricao' => ['required','string','max:120'],
            'tipo' => ['required','in:Banco,Local'],
            'bloquear_receber' => ['nullable','boolean'],
            'bloquear_pagar' => ['nullable','boolean'],
            'ativo' => ['nullable','boolean'],
        ]);

        $data['bloquear_receber'] = $request->boolean('bloquear_receber');
        $data['bloquear_pagar'] = $request->boolean('bloquear_pagar');
        $data['ativo'] = $request->boolean('ativo');

        Caixa::create($data);

        return back()->with('success','Caixa cadastrado com sucesso');
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'descricao' => ['required','string','max:120'],
            'tipo' => ['required','in:Banco,Local'],
            'bloquear_receber' => ['nullable','boolean'],
            'bloquear_pagar' => ['nullable','boolean'],
            'ativo' => ['nullable','boolean'],
        ]);

        $data['bloquear_receber'] = $request->boolean('bloquear_receber');
        $data['bloquear_pagar'] = $request->boolean('bloquear_pagar');
        $data['ativo'] = $request->boolean('ativo');

        $caixa = Caixa::findOrFail($id);
        $caixa->update($data);

        return back()->with('success','Caixa atualizado com sucesso');
    }

    public function destroy(string $id)
    {
        $caixa = Caixa::findOrFail($id);
        $caixa->delete();
        return back()->with('success','Caixa excluído com sucesso');
    }
}
