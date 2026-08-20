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
        $autorizacoes = Autorizacao::with(['convenio', 'tuss', 'guia', 'guia.agendamento.paciente', 'guia.agendamento.agendaMedica.profissionalSaude', 'guia.agendamento.status', 'guia.agendamento.sessaoTratamento', 'guia.agendamento.procedimento', 'usuario', 'usuarioValidou', 'procedimentoSolicitado'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function($aut) {
                if ($aut->procedimentoSolicitado && $aut->procedimentoSolicitado->procedimento_solicitado_descricao) {
                    $aut->procedimento_nome = $aut->procedimentoSolicitado->procedimento_solicitado_descricao;
                    return $aut;
                }

                $baseProcNome = '';
                if ($aut->tuss) {
                    $baseProcNome = $aut->tuss->codigo . ' - ' . $aut->tuss->descricao;
                } elseif ($aut->guia && $aut->guia->agendamento && $aut->guia->agendamento->procedimento) {
                    $baseProcNome = $aut->guia->agendamento->procedimento->nome;
                } else {
                    $baseProcNome = 'N/A';
                }

                $sessN = $aut->guia && $aut->guia->agendamento && $aut->guia->agendamento->sessaoTratamento 
                            ? $aut->guia->agendamento->sessaoTratamento->numero_sessao 
                            : null;
                $sessT = $aut->guia && $aut->guia->agendamento && $aut->guia->agendamento->procedimento 
                            ? $aut->guia->agendamento->procedimento->quantidade_sessoes 
                            : ($aut->tuss ? $aut->tuss->quantidade_sessoes : null);

                $procNome = $baseProcNome;
                if ($sessN !== null) {
                    if ($sessT !== null && $sessT > 0) {
                        $procNome .= " (Sessão {$sessN}/{$sessT})";
                    } else {
                        $procNome .= " (Sessão {$sessN})";
                    }
                }
                
                $aut->procedimento_nome = $procNome;
                return $aut;
            });
        $convenios = Convenio::select('id', 'descricao')->get();
        $usuarios = User::with('pessoa:id,nome')->select('id', 'pessoa_id')->get();

        return Inertia::render('Convenio/Autorizacoes/Index', [
            'autorizacoes' => $autorizacoes,
            'convenios' => $convenios,
            'usuarios' => $usuarios,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'protocolo' => ['nullable', 'string', 'max:100'],
            'convenio_id' => ['required', 'integer', 'exists:convenios,id'],
            'guia_id' => ['nullable', 'integer', 'exists:guias,id'],
            'procedimento_solicitado_id' => ['nullable', 'integer', 'exists:guia_procedimento_solicitados,id'],
            'numero_autorizacao' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:SOLICITADA,AUTORIZADA,Pendente,Aprovada,Negada,Expirada,Cancelada'],
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
            'protocolo' => ['nullable', 'string', 'max:100'],
            'convenio_id' => ['required', 'integer', 'exists:convenios,id'],
            'guia_id' => ['nullable', 'integer', 'exists:guias,id'],
            'procedimento_solicitado_id' => ['nullable', 'integer', 'exists:guia_procedimento_solicitados,id'],
            'numero_autorizacao' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:SOLICITADA,AUTORIZADA,Pendente,Aprovada,Negada,Expirada,Cancelada'],
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
