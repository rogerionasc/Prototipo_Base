<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Autorizacao;
use App\Models\Convenio;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AutorizacaoController extends Controller
{
    public function index()
    {
        $autorizacoes = Autorizacao::with(['convenio', 'usuario', 'usuarioValidou'])->latest()->get();
        $convenios = Convenio::select('id', 'descricao')->get();
        $usuarios = User::with('pessoa:id,nome')->select('id', 'pessoa_id')->get();

        return Inertia::render('Convenios/Autorizacoes/Index', [
            'autorizacoes' => $autorizacoes,
            'convenios' => $convenios,
            'usuarios' => $usuarios,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'convenio_id' => ['required', 'integer', 'exists:convenios,id'],
            'carteira' => ['nullable', 'string', 'max:100'],
            'numero_autorizacao' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:Pendente,Aprovada,Negada,Expirada,Cancelada'],
            'validade' => ['nullable', 'date'],
            'data_solicitacao' => ['nullable', 'date'],
            'data_resposta' => ['nullable', 'date'],
            'observacao' => ['nullable', 'string'],
        ]);

        $data['usuario_id'] = Auth::id();

        Autorizacao::create($data);

        return back()->with('success', 'Autorização criada');
    }

    public function update(Request $request, $id)
    {
        $autorizacao = Autorizacao::findOrFail($id);

        $data = $request->validate([
            'convenio_id' => ['required', 'integer', 'exists:convenios,id'],
            'carteira' => ['nullable', 'string', 'max:100'],
            'numero_autorizacao' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:Pendente,Aprovada,Negada,Expirada,Cancelada'],
            'validade' => ['nullable', 'date'],
            'data_solicitacao' => ['nullable', 'date'],
            'data_resposta' => ['nullable', 'date'],
            'observacao' => ['nullable', 'string'],
        ]);

        if ($request->has('usuario_id_validou')) {
            $data['usuario_id_validou'] = $request->input('usuario_id_validou');
        }

        $autorizacao->update($data);

        return back()->with('success', 'Autorização atualizada');
    }

    public function destroy($id)
    {
        $autorizacao = Autorizacao::findOrFail($id);
        $autorizacao->delete();

        return back()->with('success', 'Autorização excluída');
    }
}
