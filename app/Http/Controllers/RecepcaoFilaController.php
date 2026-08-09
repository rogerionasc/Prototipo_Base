<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agendamento;
use App\Models\Atendimento;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecepcaoFilaController extends Controller
{
    public function index()
    {
        $hoje = Carbon::today()->format('Y-m-d');
        
        $agendamentos = Agendamento::with([
            'paciente.comorbidades',
            'agendaMedica.profissionalSaude',
            'procedimento',
            'tuss',
            'status',
            'atendimentos' // Para checar se já virou atendimento
        ])
        ->where('data', $hoje)
        ->where(function($q) use ($hoje) {
            $q->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('faturamentos')
                      ->whereColumn('faturamentos.agendamento_id', 'agendamentos.id')
                      ->whereIn('faturamentos.status', ['PAGO', 'RECEBIDO']);
            })
            ->orWhereExists(function ($query) use ($hoje) {
                $query->select(DB::raw(1))
                      ->from('autorizacoes')
                      ->whereColumn('autorizacoes.agendamento_id', 'agendamentos.id')
                      ->where('autorizacoes.status', 'AUTORIZADA')
                      ->whereNotNull('autorizacoes.numero_autorizacao')
                      ->where('autorizacoes.validade', '>=', $hoje);
            });
        })
        ->orderBy('hora', 'asc')
        ->get()
        ->map(function($ag) {
            $atendimento = $ag->atendimentos->first();
            $jaChegou = $atendimento && in_array($atendimento->status, ['AGUARDANDO', 'CHAMADO', 'EM ATENDIMENTO', 'ATENDIDO']);
            $emergencia = $atendimento ? (bool) $atendimento->emergencia : false;

            $idade = 0;
            if ($ag->paciente && $ag->paciente->data_nascimento) {
                $idade = Carbon::parse($ag->paciente->data_nascimento)->age;
            }

            $status = $atendimento ? $atendimento->status : 'NÃO ATENDIDO';
            $statusScore = match($status) {
                'EM ATENDIMENTO' => 1,
                'CHAMADO'        => 2,
                'AGUARDANDO'     => 3,
                'NÃO ATENDIDO'   => 4,
                'ATENDIDO'       => 5,
                default          => 6
            };

            return [
                'id' => $ag->id,
                'hora' => date('H:i', strtotime($ag->hora)),
                'paciente' => $ag->paciente ? $ag->paciente->nome : 'N/A',
                'paciente_id' => $ag->paciente ? $ag->paciente->id : null,
                'cpf' => $ag->paciente ? $ag->paciente->cpf : null,
                'idade' => $idade,
                'tem_comorbidade' => $ag->paciente && $ag->paciente->comorbidades->count() > 0,
                'super_prioridade' => $idade >= 80,
                'prioridade_idade' => $idade >= 60 && $idade < 80,
                'emergencia' => $emergencia,
                'procedimento' => $ag->procedimento 
                                  ? $ag->procedimento->nome 
                                  : ($ag->tuss ? $ag->tuss->descricao : 'N/A'),
                'medico' => $ag->agendaMedica && $ag->agendaMedica->profissionalSaude 
                            ? $ag->agendaMedica->profissionalSaude->nome 
                            : 'N/A',
                'medico_id' => $ag->agendaMedica ? $ag->agendaMedica->pessoa_id : null,
                'status' => $status,
                'status_score' => $statusScore,
                'ja_chegou' => $jaChegou,
                'raw_hora' => $ag->hora,
                'updated_at' => $atendimento ? $atendimento->updated_at : $ag->updated_at,
            ];
        })
        ->sort(function ($a, $b) {
            // Primeiro agrupa pelo status
            if ($a['status_score'] !== $b['status_score']) {
                return $a['status_score'] <=> $b['status_score'];
            }

            // Primeiro, verifica se alguém é emergência (absoluto)
            if ($a['emergencia'] !== $b['emergencia']) return $a['emergencia'] ? -1 : 1;
            // Se ambos são emergência, desempata IMEDIATAMENTE pela ordem que virou emergência
            // Ignorando idade, comorbidade, etc.
            if ($a['emergencia'] && $b['emergencia']) {
                return $a['updated_at'] <=> $b['updated_at'];
            }

            // Depois verifica super prioridade (80+)
            if ($a['super_prioridade'] !== $b['super_prioridade']) return $a['super_prioridade'] ? -1 : 1;

            // Depois verifica comorbidade
            if ($a['tem_comorbidade'] !== $b['tem_comorbidade']) return $a['tem_comorbidade'] ? -1 : 1;

            // Depois verifica prioridade idade (60+)
            if ($a['prioridade_idade'] !== $b['prioridade_idade']) return $a['prioridade_idade'] ? -1 : 1;

            // Se empatou em TUDO acima, usamos o desempate
            return $a['raw_hora'] <=> $b['raw_hora'];
        })->values();

        return Inertia::render('Recepcao/Fila/Index', [
            'fila' => $agendamentos
        ]);
    }

    public function confirmar(Request $request, $id)
    {
        $agendamento = Agendamento::with('agendaMedica')->findOrFail($id);

        $atendimento = Atendimento::where('agendamento_id', $id)->first();

        if ($atendimento) {
            // Atualiza para AGUARDANDO se ainda estiver em estado pré-check-in
            if (in_array($atendimento->status, ['NÃO ATENDIDO', 'AGUARDANDO'])) {
                $atendimento->update(['status' => 'AGUARDANDO']);
            }
        } else {
            // Sem atendimento (sem pagamento ou fluxo direto): cria o atendimento
            $defaultCategoria = \App\Models\CategoriaProcedimento::firstOrCreate(['nome' => 'Geral']);
            $catId = $agendamento->procedimento ? $agendamento->procedimento->categoria_id : $defaultCategoria->id;

            Atendimento::create([
                'paciente_id'               => $agendamento->paciente_id,
                'medico_id'                => $agendamento->agendaMedica->pessoa_id ?? null,
                'agendamento_id'           => $agendamento->id,
                'procedimento_id'          => $agendamento->procedimento_id ?? $agendamento->tuss_id,
                'categoria_procedimento_id' => $catId ?: $defaultCategoria->id,
                'data_atendimento'         => Carbon::today()->format('Y-m-d'),
                'hora_prevista'            => Carbon::today()->format('Y-m-d') . ' ' . $agendamento->hora,
                'status'                   => 'AGUARDANDO',
                'convenio_id'              => $agendamento->convenio_id,
            ]);
        }

        return redirect()->back()->with('success', 'Presença confirmada. Paciente adicionado ao Pronto Atendimento.');
    }

    public function cancelar(Request $request, $id)
    {
        $atendimento = Atendimento::where('agendamento_id', $id)->first();

        if ($atendimento) {
            // Bloqueia apenas se o médico já iniciou o processo
            if (in_array($atendimento->status, ['CHAMADO', 'EM ATENDIMENTO', 'ATENDIDO'])) {
                return redirect()->back()->with('error', 'Não é possível cancelar a presença de um paciente que já foi chamado pelo médico.');
            }
            // Reverte para NÃO ATENDIDO (preservando o registro de pagamento)
            $atendimento->update(['status' => 'NÃO ATENDIDO']);
        }

        return redirect()->back()->with('success', 'Presença cancelada com sucesso.');
    }

    public function toggleEmergencia(Request $request, $id)
    {
        $agendamento = Agendamento::with('agendaMedica')->findOrFail($id);
        
        $atendimento = Atendimento::where('agendamento_id', $id)->first();

        if (!$atendimento) {
            // Se ainda não confirmou presença, a ação de marcar emergência
            // implicitamente confirma a presença e cria o atendimento como emergência
            $defaultCategoria = \App\Models\CategoriaProcedimento::firstOrCreate(['nome' => 'Geral']);
            $catId = $agendamento->procedimento ? $agendamento->procedimento->categoria_id : $defaultCategoria->id;

            $atendimento = Atendimento::create([
                'paciente_id' => $agendamento->paciente_id,
                'medico_id' => $agendamento->agendaMedica->pessoa_id ?? null,
                'agendamento_id' => $agendamento->id,
                'procedimento_id' => $agendamento->procedimento_id ?? $agendamento->tuss_id,
                'categoria_procedimento_id' => $catId ?: $defaultCategoria->id,
                'data_atendimento' => Carbon::today()->format('Y-m-d'),
                'hora_prevista' => Carbon::today()->format('Y-m-d') . ' ' . $agendamento->hora,
                'status' => 'AGUARDANDO',
                'emergencia' => true,
                'convenio_id' => $agendamento->convenio_id,
            ]);
            
            return redirect()->back()->with('success', 'Presença confirmada e paciente marcado como emergência.');
        }

        // Se já existia, apenas alterna a flag de emergência
        $atendimento->emergencia = !$atendimento->emergencia;
        $atendimento->save();

        return redirect()->back()->with('success', 'Status de emergência atualizado com sucesso.');
    }
}
