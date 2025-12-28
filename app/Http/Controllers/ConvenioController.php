<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Convenio;
use App\Models\Conta;

class ConvenioController extends Controller
{
    public function index()
    {
        $convenios = Convenio::select('id','descricao','tipo','empresa_id','ans','dias_recebimento','dias_retorno')->get();
        $contas = Conta::select('id','nome')->orderBy('nome')->get();
        return Inertia::render('Convenios/Index', [
            'convenios' => $convenios,
            'contas' => $contas,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'descricao' => ['required','string','max:255'],
            'tipo' => ['nullable','in:Convenio,Particular'],
            'empresa_id' => ['nullable','integer','exists:contas,id'],
            'ans' => ['nullable','integer'],
            'dias_recebimento' => ['nullable','integer'],
            'dias_retorno' => ['nullable','integer'],
        ]);

        Convenio::create($data);

        return back()->with('success','Convênio cadastrado');
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'descricao' => ['required','string','max:255'],
            'tipo' => ['nullable','in:Convenio,Particular'],
            'empresa_id' => ['nullable','integer','exists:contas,id'],
            'ans' => ['nullable','integer'],
            'dias_recebimento' => ['nullable','integer'],
            'dias_retorno' => ['nullable','integer'],
        ]);

        $convenio = Convenio::findOrFail($id);
        $convenio->update($data);

        return back()->with('success','Convênio atualizado');
    }

    public function destroy(string $id)
    {
        $convenio = Convenio::findOrFail($id);
        $convenio->delete();

        return back()->with('success','Convênio excluído');
    }
}
