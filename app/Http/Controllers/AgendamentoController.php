<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Agendamento;
use App\Models\Paciente;
use App\Models\ProfissionalSaude;
use App\Models\Procedimento;
use App\Models\AgendaMedica;
use App\Models\StatusAgendamento;
use App\Models\Orcamento;
use App\Models\OrcamentoProcedimento;
use App\Models\Pagamento;

class AgendamentoController extends Controller
{
    public function index()
    {
        $pacientes = Paciente::select('id','nome','cpf')->orderBy('nome')->get();
        $profissionais = ProfissionalSaude::with('especialidades:id')->select('id','nome')->orderBy('nome')->get();
        $procedimentos = Procedimento::select('id','nome','valor','eh_tratamento','quantidade_sessoes','especialidade_id')->orderBy('nome')->get();
        $status = StatusAgendamento::select('id','descricao')->orderBy('descricao')->get();
        $weekday = Carbon::now()->dayOfWeek;
        $agendasHoje = DB::table('agenda_medica as a')
            ->join('profissionais_saude as ps', 'ps.id', '=', 'a.profissional_saude_id')
            ->leftJoin('profissional_especialidade as pe', 'pe.profissional_saude_id', '=', 'ps.id')
            ->leftJoin('especialidades as e', 'e.id', '=', 'pe.especialidade_id')
            ->select(
                'a.profissional_saude_id',
                DB::raw("COALESCE(ps.nome,'') AS nome"),
                'a.hora_inicio',
                'a.hora_fim',
                DB::raw("GROUP_CONCAT(DISTINCT e.nome ORDER BY e.nome SEPARATOR ', ') AS especialidades")
            )
            ->where('a.dia_semana', $weekday)
            ->groupBy('a.profissional_saude_id','ps.nome','a.hora_inicio','a.hora_fim')
            ->orderBy('ps.nome')
            ->get();
        return Inertia::render('Atendimento/Agendamentos/Index', [
            'pacientes' => $pacientes,
            'profissionais' => $profissionais,
            'procedimentos' => $procedimentos,
            'status' => $status,
            'agendasHoje' => $agendasHoje,
        ]);
    }

    public function agendasByDate(Request $request)
    {
        $data = $request->validate([
            'data' => ['required','date_format:Y-m-d'],
        ]);
        $weekday = Carbon::createFromFormat('Y-m-d', $data['data'])->dayOfWeek;
        $agendas = DB::table('agenda_medica as a')
            ->join('profissionais_saude as ps', 'ps.id', '=', 'a.profissional_saude_id')
            ->leftJoin('profissional_especialidade as pe', 'pe.profissional_saude_id', '=', 'ps.id')
            ->leftJoin('especialidades as e', 'e.id', '=', 'pe.especialidade_id')
            ->select(
                'a.profissional_saude_id',
                DB::raw("COALESCE(ps.nome,'') AS nome"),
                DB::raw("MIN(a.hora_inicio) AS hora_inicio"),
                DB::raw("MAX(a.hora_fim) AS hora_fim"),
                DB::raw("GROUP_CONCAT(DISTINCT e.nome ORDER BY e.nome SEPARATOR ', ') AS especialidades")
            )
            ->where('a.dia_semana', $weekday)
            ->groupBy('a.profissional_saude_id','ps.nome')
            ->orderBy('ps.nome')
            ->get();
        return response()->json([
            'agendas' => $agendas,
        ]);
    }

    public function countsByWeekday()
    {
        $rows = DB::table('agenda_medica')
            ->select('dia_semana', DB::raw('COUNT(DISTINCT profissional_saude_id) AS cnt'))
            ->groupBy('dia_semana')
            ->get();
        $counts = [];
        foreach ($rows as $r) {
            $counts[(int)$r->dia_semana] = (int)$r->cnt;
        }
        return response()->json([
            'counts' => $counts,
        ]);
    }

    public function weekdayByDoctors(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required','array','min:1'],
            'ids.*' => ['integer','exists:profissionais_saude,id'],
        ]);
        $rows = DB::table('agenda_medica')
            ->select('dia_semana', DB::raw('GROUP_CONCAT(DISTINCT profissional_saude_id) AS prof_ids'))
            ->whereIn('profissional_saude_id', $data['ids'])
            ->groupBy('dia_semana')
            ->get();
        $map = [];
        foreach ($rows as $r) {
            $list = array_values(array_filter(array_map('intval', explode(',', (string)$r->prof_ids))));
            $map[(int)$r->dia_semana] = $list;
        }
        return response()->json([
            'weekday_map' => $map,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'paciente_id' => ['required','integer','exists:pacientes,id'],
            'profissional_saude_id' => ['required','integer','exists:profissionais_saude,id'],
            'procedimento_id' => ['required','integer','exists:procedimentos,id'],
            'data' => ['required','date_format:Y-m-d'],
            'hora' => ['required','regex:/^\d{2}:\d{2}$/'],
            'status_id' => ['nullable','integer','exists:status_agendamento,id'],
            'valor_cobrado' => ['nullable','numeric','min:0'],
            'observacoes' => ['nullable','string'],
            'orcamento_id' => ['nullable','integer','exists:orcamentos,id'],
        ]);

        $dt = Carbon::createFromFormat('Y-m-d', $data['data'])->startOfDay();
        if ($dt->lessThan(Carbon::today())) {
            return response()->json([
                'errors' => [
                    'data' => ['Não é permitido agendar em data passada.']
                ]
            ], 422);
        }
        $weekday = $dt->dayOfWeek;
        $agenda = AgendaMedica::where('profissional_saude_id', (int)$data['profissional_saude_id'])
            ->where('dia_semana', $weekday)
            ->first();
        if (!$agenda) {
            return response()->json([
                'errors' => [
                    'agenda' => ['Profissional não possui agenda para o dia selecionado.']
                ]
            ], 422);
        }
        $hora = $data['hora'];
        $hi = substr((string)$agenda->hora_inicio, 0, 5);
        $hf = substr((string)$agenda->hora_fim, 0, 5);
        if (strtotime($hora) < strtotime($hi) || strtotime($hora) > strtotime($hf)) {
            return response()->json([
                'errors' => [
                    'hora' => ['Horário fora do intervalo da agenda médica.']
                ]
            ], 422);
        }

        $ag = DB::transaction(function () use ($data, $agenda, $dt, $hora) {
            $orcamentoId = $data['orcamento_id'] ?? null;
            $procId = (int)$data['procedimento_id'];
            $pacId = (int)$data['paciente_id'];
            $valorCobrado = $data['valor_cobrado'] ?? null;

            if (empty($orcamentoId)) {
                $proc = Procedimento::select('id','valor')->findOrFail($procId);
                $valorUnit = $valorCobrado ?? ($proc->valor ?? 0);
                $valorBruto = (float)$valorUnit;
                $valorTotal = (float)$valorUnit;
                if ($valorCobrado === null) $valorCobrado = $valorUnit;

                $orcamento = Orcamento::create([
                    'numero' => 'ORC-' . now()->format('YmdHis'),
                    'data_emissao' => Carbon::now()->format('Y-m-d H:i:s'),
                    'validade' => now()->addDays(30)->toDateString(),
                    'paciente_id' => $pacId,
                    'convenio_id' => null,
                    'valor_bruto' => $valorBruto,
                    'desconto' => 0,
                    'valor_total' => $valorTotal,
                    'valor_avista' => null,
                    'faturamento_previsto' => false,
                    'aprovado' => true,
                ]);

                OrcamentoProcedimento::create([
                    'orcamento_id' => $orcamento->id,
                    'procedimento_id' => $procId,
                    'quantidade' => 1,
                    'valor_unitario' => $valorUnit,
                    'valor_total' => $valorTotal,
                    'observacoes' => null,
                ]);

                Pagamento::create([
                    'orcamento_id' => $orcamento->id,
                    'caixa_id' => null,
                    'movimentacao_id' => null,
                    'valor' => (float)($orcamento->valor_total ?? 0),
                    'forma_pagamento' => null,
                    'data_pagamento' => null,
                    'confirmado' => false,
                    'status' => 'pendente',
                ]);

                $orcamentoId = $orcamento->id;
            }

            $orcOk = DB::table('orcamentos as o')
                ->join('orcamento_procedimentos as op', 'op.orcamento_id', '=', 'o.id')
                ->where('o.id', (int)$orcamentoId)
                ->where('o.paciente_id', $pacId)
                ->where('o.aprovado', true)
                ->whereNull('o.deleted_at')
                ->whereNull('op.deleted_at')
                ->where('op.procedimento_id', $procId)
                ->exists();

            $payOk = DB::table('pagamentos as p')
                ->where('p.orcamento_id', (int)$orcamentoId)
                ->where(function ($q) {
                    $q->where('p.confirmado', true)
                      ->orWhere(function ($qq) {
                          $qq->where('p.confirmado', false)->where('p.status', 'pendente');
                      });
                })
                ->exists();

            if (!$orcOk || !$payOk) {
                throw new HttpResponseException(response()->json([
                    'errors' => [
                        'orcamento' => ['Orçamento inválido para este paciente/procedimento ou sem pagamento pendente/confirmado.']
                    ]
                ], 422));
            }

            $qtyRow = DB::table('orcamento_procedimentos as op')
                ->leftJoin('procedimentos as pr', 'pr.id', '=', 'op.procedimento_id')
                ->where('op.orcamento_id', (int)$orcamentoId)
                ->where('op.procedimento_id', $procId)
                ->whereNull('op.deleted_at')
                ->select('op.quantidade', 'pr.eh_tratamento', 'pr.quantidade_sessoes')
                ->first();
            $baseQty = max(1, (int)($qtyRow->quantidade ?? 1));
            $mult = ((bool)($qtyRow->eh_tratamento ?? false)) ? max(1, (int)($qtyRow->quantidade_sessoes ?? 0)) : 1;
            $allowedQty = $baseQty * $mult;
            $scheduledCount = DB::table('agendamentos as a')
                ->leftJoin('status_agendamento as s', 's.id', '=', 'a.status_id')
                ->whereNull('a.deleted_at')
                ->where('a.orcamento_id', (int)$orcamentoId)
                ->where('a.procedimento_id', $procId)
                ->where(function ($q) {
                    $q->whereNull('s.id')
                      ->orWhereRaw("LOWER(s.descricao) NOT LIKE '%cancel%'");
                })
                ->count();
            if ($scheduledCount >= $allowedQty) {
                throw new HttpResponseException(response()->json([
                    'errors' => [
                        'duplicidade' => ['Limite de agendamentos atingido para este orçamento e procedimento.']
                    ]
                ], 422));
            }

            $sessaoId = null;
            $procMeta = Procedimento::select('id','eh_tratamento','quantidade_sessoes')->find($procId);
            if ($procMeta && (bool)$procMeta->eh_tratamento) {
                $lastNum = DB::table('sessoes_tratamento')
                    ->where('paciente_id', $pacId)
                    ->where('procedimento_id', $procId)
                    ->max('numero_sessao');
                $nextNum = (int)($lastNum ?? 0) + 1;
                $sessaoId = DB::table('sessoes_tratamento')->insertGetId([
                    'procedimento_id' => $procId,
                    'paciente_id' => $pacId,
                    'numero_sessao' => $nextNum,
                    'data_prevista' => $dt->toDateString(),
                    'realizada' => false,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'deleted_at' => null,
                ]);
            }

            return Agendamento::create([
                'agenda_medica_id' => $agenda->id,
                'data' => $dt->toDateString(),
                'hora' => $hora,
                'paciente_id' => $pacId,
                'procedimento_id' => $procId,
                'sessao_tratamento_id' => $sessaoId,
                'orcamento_id' => $orcamentoId,
                'status_id' => $data['status_id'] ?? null,
                'agendamento_origem_id' => null,
                'valor_cobrado' => $valorCobrado,
                'observacoes' => $data['observacoes'] ?? null,
            ]);
        });

        return response()->json([
            'success' => true,
            'agendamento' => $ag,
        ]);
    }

    public function latest(Request $request)
    {
        $limit = (int)($request->query('limit', 20));
        $rows = DB::table('agendamentos as a')
            ->leftJoin('sessoes_tratamento as st', 'st.id', '=', 'a.sessao_tratamento_id')
            ->leftJoin('pacientes as p', 'p.id', '=', 'a.paciente_id')
            ->leftJoin('procedimentos as pr', 'pr.id', '=', 'a.procedimento_id')
            ->leftJoin('status_agendamento as s', 's.id', '=', 'a.status_id')
            ->select(
                'a.id',
                'a.data',
                'a.hora',
                'a.paciente_id',
                'a.procedimento_id',
                'a.sessao_tratamento_id',
                DB::raw('COALESCE(st.numero_sessao, NULL) AS sessao_numero'),
                DB::raw('COALESCE(pr.quantidade_sessoes, NULL) AS sessao_total'),
                DB::raw("COALESCE(p.nome,'') AS paciente"),
                DB::raw("COALESCE(pr.nome,'') AS procedimento"),
                DB::raw("COALESCE(s.descricao,'') AS status"),
                'a.observacoes',
                DB::raw("DATE_FORMAT(a.created_at, '%d/%m %H:%i') AS criado_em")
            )
            ->orderByDesc('a.created_at')
            ->orderByDesc('a.id')
            ->limit($limit)
            ->get();
        return response()->json([
            'agendamentos' => $rows,
        ]);
    }

    public function show(string $id)
    {
        $row = DB::table('agendamentos as a')
            ->leftJoin('pacientes as p', 'p.id', '=', 'a.paciente_id')
            ->leftJoin('procedimentos as pr', 'pr.id', '=', 'a.procedimento_id')
            ->leftJoin('status_agendamento as s', 's.id', '=', 'a.status_id')
            ->leftJoin('sessoes_tratamento as st', 'st.id', '=', 'a.sessao_tratamento_id')
            ->leftJoin('orcamentos as o', 'o.id', '=', 'a.orcamento_id')
            ->where('a.id', (int)$id)
            ->whereNull('a.deleted_at')
            ->select(
                'a.id',
                'a.paciente_id',
                'a.procedimento_id',
                'a.orcamento_id',
                'a.sessao_tratamento_id',
                'a.data',
                DB::raw("TIME_FORMAT(a.hora, '%H:%i') AS hora"),
                'a.status_id',
                DB::raw("COALESCE(s.descricao,'') AS status"),
                'a.valor_cobrado',
                'a.observacoes',
                DB::raw("COALESCE(p.nome,'') AS paciente_nome"),
                DB::raw("COALESCE(pr.nome,'') AS procedimento_nome"),
                DB::raw('COALESCE(st.numero_sessao, NULL) AS sessao_numero'),
                DB::raw('COALESCE(pr.quantidade_sessoes, NULL) AS sessao_total'),
                DB::raw("COALESCE(o.numero,'') AS orcamento_numero")
            )
            ->first();

        if (!$row) {
            return response()->json([
                'errors' => [
                    'agendamento' => ['Agendamento não encontrado.']
                ]
            ], 404);
        }

        return response()->json([
            'agendamento' => $row,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $agendamento = Agendamento::findOrFail($id);
        $data = $request->validate([
            'paciente_id' => ['nullable','integer','exists:pacientes,id'],
            'profissional_saude_id' => ['nullable','integer','exists:profissionais_saude,id'],
            'procedimento_id' => ['nullable','integer','exists:procedimentos,id'],
            'data' => ['nullable','date_format:Y-m-d'],
            'hora' => ['nullable','regex:/^\d{2}:\d{2}$/'],
            'status_id' => ['nullable','integer','exists:status_agendamento,id'],
            'valor_cobrado' => ['nullable','numeric','min:0'],
            'observacoes' => ['nullable','string'],
        ]);
        $payload = [];
        foreach (['paciente_id','profissional_saude_id','procedimento_id','data','hora','status_id','valor_cobrado','observacoes'] as $k) {
            if (array_key_exists($k, $data) && $data[$k] !== null) {
                $payload[$k] = $data[$k];
            }
        }
        if (array_key_exists('data', $payload)) {
            $dt = Carbon::createFromFormat('Y-m-d', $payload['data'])->startOfDay();
            if ($dt->lessThan(Carbon::today())) {
                return response()->json([
                    'errors' => [
                        'data' => ['Não é permitido agendar em data passada.']
                    ]
                ], 422);
            }
        }
        if (!empty($agendamento->orcamento_id)) {
            $procCheckId = array_key_exists('procedimento_id', $payload) && $payload['procedimento_id'] !== null
                ? (int)$payload['procedimento_id']
                : (int)$agendamento->procedimento_id;
            if ($procCheckId) {
                $qtyRow = DB::table('orcamento_procedimentos as op')
                    ->leftJoin('procedimentos as pr', 'pr.id', '=', 'op.procedimento_id')
                    ->where('op.orcamento_id', (int)$agendamento->orcamento_id)
                    ->where('op.procedimento_id', $procCheckId)
                    ->whereNull('op.deleted_at')
                    ->select('op.quantidade', 'pr.eh_tratamento', 'pr.quantidade_sessoes')
                    ->first();
                $baseQty = max(1, (int)($qtyRow->quantidade ?? 1));
                $mult = ((bool)($qtyRow->eh_tratamento ?? false)) ? max(1, (int)($qtyRow->quantidade_sessoes ?? 0)) : 1;
                $allowedQty = $baseQty * $mult;
                $scheduledCount = DB::table('agendamentos as a')
                    ->leftJoin('status_agendamento as s', 's.id', '=', 'a.status_id')
                    ->whereNull('a.deleted_at')
                    ->where('a.orcamento_id', (int)$agendamento->orcamento_id)
                    ->where('a.procedimento_id', $procCheckId)
                    ->where('a.id', '<>', (int)$agendamento->id)
                    ->where(function ($q) {
                        $q->whereNull('s.id')
                          ->orWhereRaw("LOWER(s.descricao) NOT LIKE '%cancel%'");
                    })
                    ->count();
                if ($scheduledCount >= $allowedQty) {
                    return response()->json([
                        'errors' => [
                            'duplicidade' => ['Limite de agendamentos atingido para este orçamento e procedimento.']
                        ]
                    ], 422);
                }
            }
        }
        if (!empty($payload)) {
            $agendamento->update($payload);
        }
        return response()->json([
            'success' => true,
            'agendamento' => $agendamento->fresh(),
        ]);
    }

    public function cancel(Request $request, string $id)
    {
        $agendamento = Agendamento::findOrFail($id);
        $status = StatusAgendamento::where(DB::raw('LOWER(descricao)'), 'like', '%cancel%')->first();
        if (!$status) {
            $status = StatusAgendamento::create(['descricao' => 'Cancelado']);
        }
        $sid = $status->id;
        $obs = $request->input('observacoes');
        $agendamento->update([
            'status_id' => $sid,
            'observacoes' => $obs ?? $agendamento->observacoes,
        ]);
        return response()->json([
            'success' => true,
        ]);
    }

    public function rescheduleSession(Request $request, string $id)
    {
        $agendamento = Agendamento::findOrFail($id);
        if (empty($agendamento->sessao_tratamento_id)) {
            return response()->json([
                'errors' => [
                    'sessao' => ['Este agendamento não é uma sessão de tratamento.']
                ]
            ], 422);
        }
        if (!empty($agendamento->status_id)) {
            $st = StatusAgendamento::select('id', 'descricao')->find((int)$agendamento->status_id);
            $desc = $st ? strtolower((string)$st->descricao) : '';
            if (!$desc || strpos($desc, 'cancel') === false) {
                return response()->json([
                    'errors' => [
                        'status' => ['Apenas sessões canceladas podem ser reagendadas.']
                    ]
                ], 422);
            }
        } else {
            return response()->json([
                'errors' => [
                    'status' => ['Apenas sessões canceladas podem ser reagendadas.']
                ]
            ], 422);
        }

        $data = $request->validate([
            'profissional_saude_id' => ['required','integer','exists:profissionais_saude,id'],
            'data' => ['required','date_format:Y-m-d'],
            'hora' => ['required','regex:/^\d{2}:\d{2}$/'],
        ]);

        $dt = Carbon::createFromFormat('Y-m-d', $data['data'])->startOfDay();
        if ($dt->lessThan(Carbon::today())) {
            return response()->json([
                'errors' => [
                    'data' => ['Não é permitido agendar em data passada.']
                ]
            ], 422);
        }

        $weekday = $dt->dayOfWeek;
        $agenda = AgendaMedica::where('profissional_saude_id', (int)$data['profissional_saude_id'])
            ->where('dia_semana', $weekday)
            ->first();
        if (!$agenda) {
            return response()->json([
                'errors' => [
                    'agenda' => ['Profissional não possui agenda para o dia selecionado.']
                ]
            ], 422);
        }

        $hora = $data['hora'];
        $hi = substr((string)$agenda->hora_inicio, 0, 5);
        $hf = substr((string)$agenda->hora_fim, 0, 5);
        if (strtotime($hora) < strtotime($hi) || strtotime($hora) > strtotime($hf)) {
            return response()->json([
                'errors' => [
                    'hora' => ['Horário fora do intervalo da agenda médica.']
                ]
            ], 422);
        }

        if (!empty($agendamento->orcamento_id) && !empty($agendamento->procedimento_id)) {
            $qtyRow = DB::table('orcamento_procedimentos as op')
                ->leftJoin('procedimentos as pr', 'pr.id', '=', 'op.procedimento_id')
                ->where('op.orcamento_id', (int)$agendamento->orcamento_id)
                ->where('op.procedimento_id', (int)$agendamento->procedimento_id)
                ->whereNull('op.deleted_at')
                ->select('op.quantidade', 'pr.eh_tratamento', 'pr.quantidade_sessoes')
                ->first();
            $baseQty = max(1, (int)($qtyRow->quantidade ?? 1));
            $mult = ((bool)($qtyRow->eh_tratamento ?? false)) ? max(1, (int)($qtyRow->quantidade_sessoes ?? 0)) : 1;
            $allowedQty = $baseQty * $mult;
            $scheduledCount = DB::table('agendamentos as a')
                ->leftJoin('status_agendamento as s', 's.id', '=', 'a.status_id')
                ->whereNull('a.deleted_at')
                ->where('a.orcamento_id', (int)$agendamento->orcamento_id)
                ->where('a.procedimento_id', (int)$agendamento->procedimento_id)
                ->where('a.id', '<>', (int)$agendamento->id)
                ->where(function ($q) {
                    $q->whereNull('s.id')
                      ->orWhereRaw("LOWER(s.descricao) NOT LIKE '%cancel%'");
                })
                ->count();
            if ($scheduledCount >= $allowedQty) {
                return response()->json([
                    'errors' => [
                        'duplicidade' => ['Limite de agendamentos atingido para este orçamento e procedimento.']
                    ]
                ], 422);
            }
        }

        $agendamento->update([
            'agenda_medica_id' => $agenda->id,
            'data' => $dt->toDateString(),
            'hora' => $hora,
            'status_id' => null,
        ]);

        return response()->json([
            'success' => true,
            'agendamento' => $agendamento->fresh(),
        ]);
    }

    public function profissionaisPorProcedimento(Request $request)
    {
        $procedimentoId = (int) $request->query('procedimento_id');
        if (!$procedimentoId) {
            return response()->json(['profissionais' => []]);
        }

        $procedimento = Procedimento::select('id', 'especialidade_id')->find($procedimentoId);
        if (!$procedimento || !$procedimento->especialidade_id) {
            return response()->json(['profissionais' => []]);
        }

        $especialidadeId = (int) $procedimento->especialidade_id;
        $profissionais = ProfissionalSaude::query()
            ->join('profissional_especialidade as pe', 'pe.profissional_saude_id', '=', 'profissionais_saude.id')
            ->where('pe.especialidade_id', $especialidadeId)
            ->select('profissionais_saude.id', 'profissionais_saude.nome')
            ->distinct()
            ->orderBy('profissionais_saude.nome')
            ->get();

        return response()->json([
            'profissionais' => $profissionais
        ]);
    }
}
