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

        $query = Atendimento::with(['paciente.comorbidades', 'medico', 'procedimento', 'tuss', 'agendamento'])
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
                
                $atendimento->procedimento_nome = $atendimento->procedimento 
                    ? $atendimento->procedimento->nome 
                    : ($atendimento->tuss ? $atendimento->tuss->descricao : 'N/A');
                
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

        $outros = $atendimentos->whereNotIn('status', ['EM ATENDIMENTO', 'CHAMADO', 'AGUARDANDO', 'NÃO ATENDIDO', 'CANCELADO'])->sortBy('created_at')->values();

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
        if (!$sala && auth()->id() !== 1) {
            $msg = (auth()->user()->pessoa_id != $atendimento->medico_id) 
                ? 'O médico responsável precisa estar alocado em um consultório/sala para chamar o paciente.'
                : 'Você precisa estar alocado em um consultório/sala para chamar o paciente.';
            return redirect()->back()->with('error', $msg);
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
        if (!$sala && auth()->id() !== 1) {
            $msg = (auth()->user()->pessoa_id != $atendimento->medico_id) 
                ? 'O médico responsável precisa estar alocado em um consultório/sala para iniciar o atendimento.'
                : 'Você precisa estar alocado em um consultório/sala para iniciar o atendimento.';
            return redirect()->back()->with('error', $msg);
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

        // Atualizar status da Guia para ATENDIDA se existir e persistir procedimentos
        $guia = null;
        $agendamento = null;
        
        if ($atendimento->guia_id) {
            $guia = \App\Models\Guia::find($atendimento->guia_id);
            $agendamento = \App\Models\Agendamento::with('tuss')->find($atendimento->agendamento_id);
        } else if ($atendimento->agendamento_id) {
            $agendamento = \App\Models\Agendamento::with('tuss')->find($atendimento->agendamento_id);
            if ($agendamento) {
                $origemId = $agendamento->agendamento_origem_id ?? $agendamento->id;
                $numeroGuiaPrestador = 'G' . str_pad($origemId, 8, '0', STR_PAD_LEFT);
                $guia = \App\Models\Guia::where('numero_guia_prestador', $numeroGuiaPrestador)->first();
                if ($guia) {
                    $atendimento->update(['guia_id' => $guia->id]);
                }
            }
        }

        if ($guia) {
            $guia->update(['status' => 'ATENDIDA']);
            
            if ($agendamento) {
                $origemId = $agendamento->agendamento_origem_id ?? $agendamento->id;
                $allAgendamentos = \App\Models\Agendamento::where('id', $origemId)
                                            ->orWhere('agendamento_origem_id', $origemId)
                                            ->with('tuss')
                                            ->get();
                
                $totalProcedimentos = $guia->total_procedimentos ?? 0;
                $changed = false;

                foreach ($allAgendamentos as $ag) {
                    if ($ag->tuss) {
                        $procRealizado = $guia->procedimentosRealizados()->where('procedimento_realizado_codigo', $ag->tuss->codigo)->first();
                        
                        // Se não encontrou pelo código, procura o primeiro esqueleto vazio
                        if (!$procRealizado) {
                            $procRealizado = $guia->procedimentosRealizados()->whereNull('procedimento_realizado_codigo')->first();
                        }
                        
                        $vUnit = floatval($ag->tuss->total ?? 0);
                        $procValorTotal = round(1 * $vUnit * 1, 2);

                        if (!$procRealizado) {
                            $procRealizado = $guia->procedimentosRealizados()->create([
                                'tabela_procedimento_realizado' => '22',
                                'procedimento_realizado_codigo' => $ag->tuss->codigo,
                                'procedimento_realizado_descricao' => $ag->tuss->descricao,
                                'quantidade_realizada' => 1,
                                'data_realizacao' => $ag->data,
                                'hora_inicial' => $ag->hora,
                                'valor_unitario' => $vUnit,
                                'valor_total' => $procValorTotal,
                            ]);
                            $totalProcedimentos += $procValorTotal;
                            $changed = true;
                        } else {
                            if (!$procRealizado->valor_total) {
                                $procRealizado->update([
                                    'tabela_procedimento_realizado' => '22',
                                    'procedimento_realizado_codigo' => $ag->tuss->codigo,
                                    'procedimento_realizado_descricao' => $ag->tuss->descricao,
                                    'quantidade_realizada' => 1,
                                    'data_realizacao' => $ag->data,
                                    'hora_inicial' => $ag->hora,
                                    'valor_unitario' => $vUnit,
                                    'valor_total' => $procValorTotal,
                                ]);
                                $totalProcedimentos += $procValorTotal;
                                $changed = true;
                            }
                        }

                        // Adicionar profissional executante
                        $atendimentoMedico = \App\Models\Atendimento::where('agendamento_id', $ag->id)->with(['medico.conselho', 'medico.especialidades'])->first();
                        if ($atendimentoMedico && $atendimentoMedico->medico) {
                            $profissional = $atendimentoMedico->medico;
                            $cpf = $profissional->cpf ?? '00000000000';
                            
                            $profExecutante = $procRealizado->profissionaisExecutantes()->first();
                            if ($profExecutante && !$profExecutante->profissional_executante_codigo) {
                                // Atualiza o esqueleto vazio criado no agendamento
                                $profExecutante->update([
                                    'sequencial_referencia' => 1,
                                    'grau_participacao' => '01',
                                    'profissional_executante_codigo' => $cpf,
                                    'profissional_executante_nome' => $profissional->nome ?? 'Profissional',
                                    'conselho_executante' => $profissional->conselho?->codigo ?? 'CR',
                                    'numero_conselho_executante' => $profissional->numero_conselho ?? '000000',
                                    'uf_conselho_executante' => $profissional->uf_conselho ?? 'SP',
                                    'cbo_executante' => $profissional->especialidades?->first()?->codigo ?? '2251',
                                    'data_realizacao_serie' => $atendimentoMedico->hora_inicio ? \Carbon\Carbon::parse($atendimentoMedico->hora_inicio)->format('Y-m-d') : $ag->data,
                                ]);
                            } else {
                                $existeProf = $procRealizado->profissionaisExecutantes()->where('profissional_executante_codigo', $cpf)->exists();
                                
                                if (!$existeProf) {
                                    $count = $procRealizado->profissionaisExecutantes()->count();
                                    $procRealizado->profissionaisExecutantes()->create([
                                        'sequencial_referencia' => $count + 1,
                                        'grau_participacao' => '01',
                                        'profissional_executante_codigo' => $cpf,
                                        'profissional_executante_nome' => $profissional->nome ?? 'Profissional',
                                        'conselho_executante' => $profissional->conselho?->codigo ?? 'CR',
                                        'numero_conselho_executante' => $profissional->numero_conselho ?? '000000',
                                        'uf_conselho_executante' => $profissional->uf_conselho ?? 'SP',
                                        'cbo_executante' => $profissional->especialidades?->first()?->codigo ?? '2251',
                                        'data_realizacao_serie' => $atendimentoMedico->hora_inicio ? \Carbon\Carbon::parse($atendimentoMedico->hora_inicio)->format('Y-m-d') : $ag->data,
                                    ]);
                                }
                            }
                        }
                    }
                }

                if ($changed) {
                    $valorTotalGeral = 
                        $totalProcedimentos +
                        floatval($guia->total_taxas_alugueis ?? 0) +
                        floatval($guia->total_materiais ?? 0) +
                        floatval($guia->total_opme ?? 0) +
                        floatval($guia->total_medicamentos ?? 0) +
                        floatval($guia->total_gases_medicinais ?? 0);

                    $guia->update([
                        'total_procedimentos' => $totalProcedimentos,
                        'valor_total_geral' => $valorTotalGeral,
                    ]);
                }
            }
        }

        return redirect()->route('atendimentos.index')->with('success', 'Atendimento finalizado com sucesso.');
    }
}
