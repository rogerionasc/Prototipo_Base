<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Atendimento;

class AtendimentoController extends Controller
{
    public function index()
    {
        $hoje = \Carbon\Carbon::today()->format('Y-m-d');

        $atendimentos = Atendimento::with(['paciente.comorbidades', 'medico', 'procedimento', 'agendamento'])
            ->whereDate('data_atendimento', $hoje)
            ->get()
            ->map(function($atendimento) {
                $statusScore = match($atendimento->status) {
                    'EM ATENDIMENTO' => 1,
                    'CHAMADO' => 2,
                    'AGUARDANDO' => 3,
                    default => 4
                };

                $idade = 0;
                if ($atendimento->paciente && $atendimento->paciente->data_nascimento) {
                    $idade = \Carbon\Carbon::parse($atendimento->paciente->data_nascimento)->age;
                }

                $atendimento->status_score = $statusScore;
                $atendimento->emergencia = (bool) $atendimento->emergencia;
                $atendimento->super_prioridade = $idade >= 80;
                $atendimento->tem_comorbidade = $atendimento->paciente && $atendimento->paciente->comorbidades->count() > 0;
                $atendimento->prioridade_idade = $idade >= 60 && $idade < 80;
                
                return $atendimento;
            })
        ->sort(function ($a, $b) {
            // Primeiro agrupa pelo status
            if ($a->status_score !== $b->status_score) {
                return $a->status_score <=> $b->status_score;
            }

            // Emergência tem prioridade absoluta
            if ($a->emergencia !== $b->emergencia) return $a->emergencia ? -1 : 1;
            if ($a->emergencia && $b->emergencia) return $a->created_at <=> $b->created_at;

            // Super prioridade (80+)
            if ($a->super_prioridade !== $b->super_prioridade) return $a->super_prioridade ? -1 : 1;

            // Comorbidade
            if ($a->tem_comorbidade !== $b->tem_comorbidade) return $a->tem_comorbidade ? -1 : 1;

            // Prioridade Idade (60+)
            if ($a->prioridade_idade !== $b->prioridade_idade) return $a->prioridade_idade ? -1 : 1;

            // Sem prioridade (ou todos empatados), desempata pela hora que foi confirmado (created_at)
            return $a->created_at <=> $b->created_at;
        })->values();
            
        return Inertia::render('Consultorio/Atendimentos/Index', [
            'atendimentos' => $atendimentos
        ]);
    }

    public function chamar(Atendimento $atendimento)
    {
        // Verifica se o médico já possui um atendimento em andamento
        $emAndamento = Atendimento::where('medico_id', $atendimento->medico_id)
            ->where('status', 'EM ATENDIMENTO')
            ->exists();

        if ($emAndamento) {
            return redirect()->back()->with('error', 'O médico já possui um paciente em atendimento.');
        }

        // Altera o status para CHAMADO, caso ainda esteja AGUARDANDO
        if ($atendimento->status === 'AGUARDANDO') {
            $atendimento->update([
                'status' => 'CHAMADO'
            ]);
        }

        // Aqui também iria o código para disparar o evento no painel (broadcast)
        
        return redirect()->back()->with('success', 'Paciente chamado com sucesso!');
    }

    public function iniciar(Atendimento $atendimento)
    {
        // Verifica se o médico já possui um atendimento em andamento
        $emAndamento = Atendimento::where('medico_id', $atendimento->medico_id)
            ->where('status', 'EM ATENDIMENTO')
            ->exists();

        if ($emAndamento) {
            return redirect()->back()->with('error', 'O médico já possui um paciente em atendimento. Finalize-o antes de iniciar outro.');
        }

        $atendimento->update([
            'status' => 'EM ATENDIMENTO'
        ]);

        return redirect()->route('atendimentos.pep', $atendimento->id)->with('success', 'Atendimento iniciado.');
    }

    public function finalizar(Atendimento $atendimento)
    {
        // Verifica se o médico responsável é o mesmo que está finalizando
        // No momento assumimos que o médico pode finalizar seu próprio atendimento
        
        $atendimento->update([
            'status' => 'FINALIZADO'
        ]);

        // Também encerrar o PEP associado se existir
        $pep = \App\Models\Pep::where('atendimento_id', $atendimento->id)->where('status', 'Aberto')->first();
        if ($pep) {
            $pep->update([
                'status' => 'Encerrado',
                'encerrado_em' => now(),
            ]);
        }

        return redirect()->route('atendimentos.index')->with('success', 'Atendimento finalizado com sucesso.');
    }
}
