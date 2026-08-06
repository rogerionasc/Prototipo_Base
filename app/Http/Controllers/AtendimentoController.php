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

        $query = Atendimento::with(['paciente.comorbidades', 'medico', 'procedimento', 'agendamento'])
            ->whereDate('data_atendimento', $hoje);

        if (auth()->check() && auth()->id() !== 1 && auth()->user()->pessoa_id) {
            $query->where('medico_id', auth()->user()->pessoa_id);
        }

        $atendimentos = $query->get()
            ->map(function($atendimento) {
                $idade = 0;
                if ($atendimento->paciente && $atendimento->paciente->data_nascimento) {
                    $idade = \Carbon\Carbon::parse($atendimento->paciente->data_nascimento)->age;
                }

                $atendimento->emergencia = (bool) $atendimento->emergencia;
                $atendimento->super_prioridade = $idade >= 80;
                $atendimento->tem_comorbidade = $atendimento->paciente && $atendimento->paciente->comorbidades->count() > 0;
                $atendimento->prioridade_idade = $idade >= 60 && $idade < 80;
                $atendimento->idade_paciente = $idade;
                
                return $atendimento;
            });

        $emAtendimento = $atendimentos->where('status', 'EM ATENDIMENTO')->sortBy('created_at')->values();
        $chamados = $atendimentos->where('status', 'CHAMADO')->sortBy('created_at')->values();
        
        $aguardando = $atendimentos->where('status', 'AGUARDANDO');
        $emergencias = $aguardando->where('emergencia', true)->sortBy('created_at')->values();
        $restoAguardando = $aguardando->where('emergencia', false);

        $preferenciais = $restoAguardando->filter(function($a) {
            return $a->super_prioridade || $a->tem_comorbidade || $a->prioridade_idade;
        })->sort(function($a, $b) {
            if ($a->super_prioridade !== $b->super_prioridade) return $a->super_prioridade ? -1 : 1;
            if ($a->tem_comorbidade !== $b->tem_comorbidade) return $a->tem_comorbidade ? -1 : 1;
            if ($a->prioridade_idade !== $b->prioridade_idade) return $a->prioridade_idade ? -1 : 1;
            return $a->created_at <=> $b->created_at;
        })->values();

        $normais = $restoAguardando->reject(function($a) {
            return $a->super_prioridade || $a->tem_comorbidade || $a->prioridade_idade;
        })->sortBy('created_at')->values();

        $interleavedAguardando = collect();
        $maxLen = max($preferenciais->count(), $normais->count());
        for ($i = 0; $i < $maxLen; $i++) {
            if ($i < $preferenciais->count()) {
                $interleavedAguardando->push($preferenciais[$i]);
            }
            if ($i < $normais->count()) {
                $interleavedAguardando->push($normais[$i]);
            }
        }

        $outros = $atendimentos->whereNotIn('status', ['EM ATENDIMENTO', 'CHAMADO', 'AGUARDANDO'])->sortBy('created_at')->values();

        $finalList = $emAtendimento->concat($chamados)
                                   ->concat($emergencias)
                                   ->concat($interleavedAguardando)
                                   ->concat($outros)
                                   ->values();

        return Inertia::render('Consultorio/Atendimentos/Index', [
            'atendimentos' => $finalList
        ]);
    }

    public function chamar(Atendimento $atendimento)
    {
        if (auth()->id() !== 1 && auth()->user()->pessoa_id != $atendimento->medico_id) {
            return redirect()->back()->with('error', 'Apenas o médico responsável pode realizar esta ação.');
        }

        // Verifica se o médico está alocado em alguma sala
        $sala = \App\Models\Sala::where('pessoa_id', $atendimento->medico_id)->first();
        if (!$sala) {
            return redirect()->back()->with('error', 'Você precisa estar alocado em um consultório/sala para chamar o paciente.');
        }

        // Verifica se o médico já possui um atendimento em andamento
        $emAndamento = Atendimento::where('medico_id', $atendimento->medico_id)
            ->where('status', 'EM ATENDIMENTO')
            ->exists();

        if ($emAndamento) {
            return redirect()->back()->with('error', 'O médico já possui um paciente em atendimento.');
        }

        // Altera o status para CHAMADO e atualiza o timestamp para o Painel detectar
        $atendimento->status = 'CHAMADO';
        $atendimento->touch();
        $atendimento->save();

        // Aqui também iria o código para disparar o evento no painel (broadcast)
        
        return redirect()->back()->with('success', 'Paciente chamado com sucesso!');
    }

    public function iniciar(Atendimento $atendimento)
    {
        if (auth()->id() !== 1 && auth()->user()->pessoa_id != $atendimento->medico_id) {
            return redirect()->back()->with('error', 'Apenas o médico responsável pode realizar esta ação.');
        }

        // Verifica se o médico está alocado em alguma sala
        $sala = \App\Models\Sala::where('pessoa_id', $atendimento->medico_id)->first();
        if (!$sala) {
            return redirect()->back()->with('error', 'Você precisa estar alocado em um consultório/sala para iniciar o atendimento.');
        }

        // Verifica se o médico já possui um atendimento em andamento
        $emAndamento = Atendimento::where('medico_id', $atendimento->medico_id)
            ->where('status', 'EM ATENDIMENTO')
            ->exists();

        if ($emAndamento) {
            return redirect()->back()->with('error', 'O médico já possui um paciente em atendimento. Finalize-o antes de iniciar outro.');
        }

        $atendimento->update([
            'status'      => 'EM ATENDIMENTO',
            'hora_inicio' => now(),
        ]);

        return redirect()->route('atendimentos.pep', $atendimento->id)->with('success', 'Atendimento iniciado.');
    }

    public function ausente(Atendimento $atendimento)
    {
        if (auth()->id() !== 1 && auth()->user()->pessoa_id != $atendimento->medico_id) {
            return redirect()->back()->with('error', 'Apenas o médico responsável pode realizar esta ação.');
        }

        $atendimento->update([
            'status' => 'NÃO ATENDIDO'
        ]);

        return redirect()->back()->with('success', 'Paciente marcado como ausente.');
    }

    public function finalizar(Atendimento $atendimento)
    {
        if (auth()->id() !== 1 && auth()->user()->pessoa_id != $atendimento->medico_id) {
            return redirect()->back()->with('error', 'Apenas o médico responsável pode realizar esta ação.');
        }

        $atendimento->update([
            'status' => 'ATENDIDO',
            'hora_fim' => now(),
        ]);

        // Encerrar o PEP associado se existir
        $pep = \App\Models\Pep::where('atendimento_id', $atendimento->id)->where('status', 'Aberto')->first();
        if ($pep) {
            $pep->update([
                'status' => 'Encerrado',
                'encerrado_em' => now(),
            ]);
        }

        // Marcar o agendamento como Concluído
        if ($atendimento->agendamento_id) {
            $statusConcluido = \App\Models\StatusAgendamento::firstOrCreate(['descricao' => 'Concluído']);
            \App\Models\Agendamento::where('id', $atendimento->agendamento_id)
                ->update(['status_id' => $statusConcluido->id]);
        }

        return redirect()->route('atendimentos.index')->with('success', 'Atendimento finalizado com sucesso.');
    }
}
