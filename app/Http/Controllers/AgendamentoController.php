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
use App\Models\Autorizacao;
use App\Models\Convenio;
use Illuminate\Support\Facades\Auth;

class AgendamentoController extends Controller
{
    public function index()
    {
        $pacientes = Paciente::select('id','nome','cpf')->orderBy('nome')->get();
        $profissionais = ProfissionalSaude::with('especialidades:id')->select('id','nome')->orderBy('nome')->get();
        $procedimentos = Procedimento::select('id','nome','valor','eh_tratamento','quantidade_sessoes')
            ->with('especialidades:id,nome')
            ->orderBy('nome')->get();
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
        $convenioIdInput = $request->input('convenio_id');
        $isConvenio = false;
        if (!empty($convenioIdInput)) {
            $conv = Convenio::select('tipo')->find($convenioIdInput);
            if ($conv && strtoupper((string)$conv->tipo) !== 'PARTICULAR') {
                $isConvenio = true;
            }
        }

        $procRule = $isConvenio ? ['required', 'integer', 'exists:tuss,id'] : ['required', 'integer', 'exists:procedimentos,id'];

        \Illuminate\Support\Facades\Log::info('Agendamento Store:', [
            'convenio_id' => $convenioIdInput,
            'isConvenio' => $isConvenio,
            'procRule' => $procRule,
            'payload' => $request->all(),
        ]);

        $data = $request->validate([
            'paciente_id' => ['required','integer','exists:pacientes,id'],
            'profissional_saude_id' => ['required','integer','exists:profissionais_saude,id'],
            'procedimento_id' => $procRule,
            'data' => ['required','date_format:Y-m-d'],
            'hora' => ['required','regex:/^\d{2}:\d{2}$/'],
            'status_id' => ['nullable','integer','exists:status_agendamento,id'],
            'valor_cobrado' => ['nullable','numeric','min:0'],
            'observacoes' => ['nullable','string'],
            'convenio_id' => ['nullable','integer','exists:convenios,id'],
            'is_retorno' => ['nullable','boolean'],
        ]);

        $isRetorno = $request->input('is_retorno', false);

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

        $agendamentoOrigemId = null;
        if ($isRetorno) {
            $diasRetorno = 0;
            if (!empty($convenioIdInput)) {
                $conv = Convenio::find($convenioIdInput);
                if ($conv) $diasRetorno = (int) $conv->dias_retorno;
            }

            $lastAgendamento = Agendamento::where('paciente_id', (int)$data['paciente_id'])
                ->whereHas('agendaMedica', function($q) use ($data) {
                    $q->where('profissional_saude_id', (int)$data['profissional_saude_id']);
                })
                ->where(function($q) use ($isConvenio, $data) {
                    if ($isConvenio) {
                        $q->where('tuss_id', (int)$data['procedimento_id']);
                    } else {
                        $q->where('procedimento_id', (int)$data['procedimento_id']);
                    }
                })
                ->whereNull('agendamento_origem_id')
                ->where(function($q) {
                    $q->whereNull('status_id')
                      ->orWhereHas('status', function($sub) {
                          $sub->whereRaw("LOWER(descricao) NOT LIKE '%cancel%'");
                      });
                })
                ->orderBy('data', 'desc')
                ->first();

            if (!$lastAgendamento) {
                return response()->json([
                    'errors' => [
                        'is_retorno' => ['Nenhum atendimento anterior elegível para retorno foi encontrado para este procedimento e profissional.']
                    ]
                ], 422);
            }

            $dataUltimo = Carbon::parse($lastAgendamento->data)->startOfDay();
            if ($dt->diffInDays($dataUltimo) > $diasRetorno) {
                return response()->json([
                    'errors' => [
                        'is_retorno' => ["Prazo para retorno excedido (limite de $diasRetorno dias do convênio)."]
                    ]
                ], 422);
            }

            $hasReturn = Agendamento::where('agendamento_origem_id', $lastAgendamento->id)
                ->where(function($q) {
                    $q->whereNull('status_id')
                      ->orWhereHas('status', function($sub) {
                          $sub->whereRaw("LOWER(descricao) NOT LIKE '%cancel%'");
                      });
                })
                ->exists();

            if ($hasReturn) {
                return response()->json([
                    'errors' => [
                        'is_retorno' => ['Este atendimento já consumiu o seu direito de 1 retorno.']
                    ]
                ], 422);
            }

            $agendamentoOrigemId = $lastAgendamento->id;
        }

        $ag = DB::transaction(function () use ($data, $agenda, $dt, $hora, $isConvenio, $isRetorno, $agendamentoOrigemId) {
            $procId = (int)$data['procedimento_id'];
            $pacId = (int)$data['paciente_id'];
            $valorCobrado = $data['valor_cobrado'] ?? null;
            $convenioId = $data['convenio_id'] ?? null;

            if ($isConvenio) {
                $proc = DB::table('tuss')->select('id', 'total as valor', 'eh_tratamento', 'quantidade_sessoes')->where('id', $procId)->first();
            } else {
                $proc = Procedimento::select('id','valor', 'eh_tratamento', 'quantidade_sessoes')->findOrFail($procId);
            }

            if ($valorCobrado === null) {
                $valorCobrado = (float)($proc->valor ?? 0);
            }

            $sessaoId = null;
            if ($proc && (bool)$proc->eh_tratamento) {
                $lastNumQuery = DB::table('sessoes_tratamento')->where('paciente_id', $pacId);

                if ($isConvenio) {
                    $lastNumQuery->where('tuss_id', $procId);
                } else {
                    $lastNumQuery->where('procedimento_id', $procId);
                }

                $lastNum = $lastNumQuery->max('numero_sessao');
                $nextNum = (int)($lastNum ?? 0) + 1;
                $sessaoId = DB::table('sessoes_tratamento')->insertGetId([
                    'procedimento_id' => $isConvenio ? null : $procId,
                    'tuss_id' => $isConvenio ? $procId : null,
                    'paciente_id' => $pacId,
                    'numero_sessao' => $nextNum,
                    'data_prevista' => $dt->toDateString(),
                    'realizada' => false,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'deleted_at' => null,
                ]);
            }

            $agendamentoStatusId = $data['status_id'] ?? null;
            if (!$agendamentoStatusId) {
                // Default to Agendado or similar if no status passed
                $statusAgendado = \App\Models\StatusAgendamento::firstOrCreate(['descricao' => 'Agendado']);
                $agendamentoStatusId = $statusAgendado->id;
            }

            $agendamento = Agendamento::create([
                'agenda_medica_id' => $agenda->id,
                'data' => $dt->toDateString(),
                'hora' => $hora,
                'paciente_id' => $pacId,
                'procedimento_id' => $isConvenio ? null : $procId,
                'tuss_id' => $isConvenio ? $procId : null,
                'sessao_tratamento_id' => $sessaoId,
                'orcamento_id' => null,
                'status_id' => $agendamentoStatusId,
                'agendamento_origem_id' => $agendamentoOrigemId,
                'valor_cobrado' => $valorCobrado,
                'observacoes' => $data['observacoes'] ?? null,
            ]);

            // Se for agendamento de convênio, verificar fluxo de autorização vs atendimento
            if ($isConvenio && $convenioId) {
                // Verificar se o procedimento deste convênio requer autorização
                $convenioTuss = DB::table('convenio_tuss')
                    ->where('convenio_id', $convenioId)
                    ->where('tuss_id', $procId)
                    ->whereNull('deleted_at')
                    ->first();

                $requerAutorizacao = $convenioTuss && $convenioTuss->requer_autorizacao;

                \Illuminate\Support\Facades\Log::info('Autorizacao Check:', [
                    'convenio_id' => $convenioId,
                    'tuss_id' => $procId,
                    'convenioTuss' => $convenioTuss,
                    'requerAutorizacao' => $requerAutorizacao,
                ]);

                if ($requerAutorizacao) {
                    $pacienteConvenio = DB::table('paciente_convenio')
                        ->where('paciente_id', $pacId)
                        ->where('convenio_id', $convenioId)
                        ->where('ativo', 1)
                        ->whereNull('deleted_at')
                        ->first();

                    Autorizacao::create([
                        'convenio_id' => $convenioId,
                        'carteira' => $pacienteConvenio->numero_carteira ?? null,
                        'numero_autorizacao' => null,
                        'status' => 'Pendente',
                        'validade' => null,
                        'data_solicitacao' => Carbon::now(),
                        'data_resposta' => null,
                        'observacao' => 'Gerado automaticamente pelo agendamento #' . $agendamento->id,
                        'usuario_id' => Auth::id() ?? 1,
                        'usuario_id_validou' => null,
                    ]);
                } else {
                    // TODO: Futuramente vai gerar um atendimento automático
                    // Atendimento::create([...]);
                }
            }

            // Gerar faturamento + pagamento PENDENTE automaticamente para particulares
            if (!$isConvenio) {
                $fatId = (int)DB::table('faturamentos')->insertGetId([
                    'paciente_id'      => $pacId,
                    'agendamento_id'   => $agendamento->id,
                    'valor_final'      => (float)$valorCobrado,
                    'tipo_pagador'     => 'PARTICULAR',
                    'convenio_id'      => null,
                    'valor_total'      => (float)$valorCobrado,
                    'valor_cobrado'    => (float)$valorCobrado,
                    'valor_aprovado'   => 0,
                    'valor_glosado'    => 0,
                    'status'           => 'AGUARDANDO_PAGAMENTO',
                    'data_faturamento' => Carbon::now()->format('Y-m-d H:i:s'),
                    'vencimento'       => Carbon::today()->toDateString(),
                    'created_at'       => Carbon::now(),
                    'updated_at'       => Carbon::now(),
                ]);

                DB::table('contas_receber')->insert([
                    'faturamento_id' => $fatId,
                    'paciente_id' => $pacId,
                    'convenio_id' => null,
                    'valor' => (float)$valorCobrado,
                    'vencimento' => Carbon::today()->toDateString(),
                    'status' => 'ABERTO',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                Pagamento::create([
                    'faturamento_id'  => $fatId,
                    'caixa_id'        => null,
                    'movimentacao_id' => null,
                    'valor'           => (float)$valorCobrado,
                    'forma_pagamento' => null,
                    'data_pagamento'  => null,
                    'status'          => 'PENDENTE',
                ]);
            }

            return $agendamento;
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
            ->leftJoin('tuss as t', 't.id', '=', 'a.tuss_id')
            ->leftJoin('status_agendamento as s', 's.id', '=', 'a.status_id')
            ->select(
                'a.id',
                'a.data',
                'a.hora',
                'a.paciente_id',
                DB::raw('COALESCE(a.procedimento_id, a.tuss_id) AS procedimento_id'),
                'a.sessao_tratamento_id',
                DB::raw('COALESCE(st.numero_sessao, NULL) AS sessao_numero'),
                DB::raw('COALESCE(pr.quantidade_sessoes, t.quantidade_sessoes, NULL) AS sessao_total'),
                DB::raw("COALESCE(p.nome,'') AS paciente"),
                DB::raw("COALESCE(pr.nome, t.descricao, '') AS procedimento"),
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
            ->leftJoin('tuss as t', 't.id', '=', 'a.tuss_id')
            ->leftJoin('status_agendamento as s', 's.id', '=', 'a.status_id')
            ->leftJoin('sessoes_tratamento as st', 'st.id', '=', 'a.sessao_tratamento_id')
            ->where('a.id', (int)$id)
            ->whereNull('a.deleted_at')
            ->select(
                'a.id',
                'a.paciente_id',
                DB::raw('COALESCE(a.procedimento_id, a.tuss_id) AS procedimento_id'),
                'a.sessao_tratamento_id',
                'a.data',
                DB::raw("TIME_FORMAT(a.hora, '%H:%i') AS hora"),
                'a.status_id',
                DB::raw("COALESCE(s.descricao,'') AS status"),
                'a.valor_cobrado',
                'a.observacoes',
                DB::raw("COALESCE(p.nome,'') AS paciente_nome"),
                DB::raw("COALESCE(pr.nome, t.descricao, '') AS procedimento_nome"),
                DB::raw('COALESCE(st.numero_sessao, NULL) AS sessao_numero'),
                DB::raw('COALESCE(pr.quantidade_sessoes, t.quantidade_sessoes, NULL) AS sessao_total')
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

        $isConvenio = false;
        if (!empty($agendamento->tuss_id)) {
            $isConvenio = true;
        }

        $procRule = $isConvenio ? ['nullable', 'integer', 'exists:tuss,id'] : ['nullable', 'integer', 'exists:procedimentos,id'];

        $data = $request->validate([
            'paciente_id' => ['nullable','integer','exists:pacientes,id'],
            'profissional_saude_id' => ['nullable','integer','exists:profissionais_saude,id'],
            'procedimento_id' => $procRule,
            'data' => ['nullable','date_format:Y-m-d'],
            'hora' => ['nullable','regex:/^\d{2}:\d{2}$/'],
            'status_id' => ['nullable','integer','exists:status_agendamento,id'],
            'valor_cobrado' => ['nullable','numeric','min:0'],
            'observacoes' => ['nullable','string'],
        ]);
        $payload = [];
        foreach (['paciente_id','profissional_saude_id','data','hora','status_id','valor_cobrado','observacoes'] as $k) {
            if (array_key_exists($k, $data) && $data[$k] !== null) {
                $payload[$k] = $data[$k];
            }
        }

        if (array_key_exists('procedimento_id', $data) && $data['procedimento_id'] !== null) {
            if ($isConvenio) {
                $payload['tuss_id'] = $data['procedimento_id'];
                $payload['procedimento_id'] = null;
            } else {
                $payload['procedimento_id'] = $data['procedimento_id'];
                $payload['tuss_id'] = null;
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

        // Sem validação de limite de orçamentos

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
        $convenioId = (int) $request->query('convenio_id');

        if (!$procedimentoId) {
            return response()->json(['profissionais' => []]);
        }

        // Se o convênio for informado e não for Particular, o procedimentoId na verdade é um tuss_id
        if ($convenioId) {
            $convenio = Convenio::select('tipo')->find($convenioId);
            if ($convenio && strtoupper((string)$convenio->tipo) !== 'PARTICULAR') {
                $profissionais = ProfissionalSaude::query()
                    ->join('convenio_medico_tuss as cmt', 'cmt.profissional_saude_id', '=', 'profissionais_saude.id')
                    ->where('cmt.convenio_id', $convenioId)
                    ->where('cmt.tuss_id', $procedimentoId)
                    ->select('profissionais_saude.id', 'profissionais_saude.nome')
                    ->distinct()
                    ->orderBy('profissionais_saude.nome')
                    ->get();

                return response()->json([
                    'profissionais' => $profissionais
                ]);
            }
        }

        $procedimento = Procedimento::with('especialidades:id')->find($procedimentoId);
        if (!$procedimento || $procedimento->especialidades->isEmpty()) {
            return response()->json(['profissionais' => []]);
        }

        $especialidadesIds = $procedimento->especialidades->pluck('id')->toArray();
        $profissionais = ProfissionalSaude::query()
            ->join('profissional_especialidade as pe', 'pe.profissional_saude_id', '=', 'profissionais_saude.id')
            ->whereIn('pe.especialidade_id', $especialidadesIds)
            ->select('profissionais_saude.id', 'profissionais_saude.nome')
            ->distinct()
            ->orderBy('profissionais_saude.nome')
            ->get();

        return response()->json([
            'profissionais' => $profissionais
        ]);
    }
    public function byPaciente(string $paciente_id)
    {
        $agendamentos = DB::table('agendamentos as a')
            ->leftJoin('sessoes_tratamento as st', 'st.id', '=', 'a.sessao_tratamento_id')
            ->leftJoin('procedimentos as pr', 'pr.id', '=', 'a.procedimento_id')
            ->leftJoin('tuss as t', 't.id', '=', 'a.tuss_id')
            ->leftJoin('status_agendamento as s', 's.id', '=', 'a.status_id')
            ->leftJoin('agenda_medica as am', 'am.id', '=', 'a.agenda_medica_id')
            ->leftJoin('profissionais_saude as prof', 'prof.id', '=', 'am.profissional_saude_id')
            ->leftJoin('orcamentos as orc', 'orc.id', '=', 'a.orcamento_id')
            ->where('a.paciente_id', $paciente_id)
            ->select(
                'a.id',
                'a.data',
                'a.hora',
                'a.procedimento_id',
                'a.tuss_id',
                'orc.convenio_id',
                'am.profissional_saude_id',
                DB::raw('COALESCE(pr.nome, t.descricao, "") AS procedimento_nome'),
                DB::raw('COALESCE(prof.nome, "") AS profissional_nome'),
                DB::raw('COALESCE(s.descricao, "") AS status')
            )
            ->orderBy('a.data', 'desc')
            ->orderBy('a.hora', 'desc')
            ->get();

        $result = $agendamentos->map(function ($ag) {
            $stRaw = strtolower(trim((string)$ag->status));
            $atendido = str_contains($stRaw, 'atendido');

            return [
                'id' => $ag->id,
                'data' => $ag->data,
                'hora' => substr($ag->hora, 0, 5),
                'procedimento' => $ag->procedimento_nome ?: 'Procedimento',
                'profissional' => $ag->profissional_nome ?: 'Profissional',
                'status' => $ag->status ?: 'Agendado',
                'atendido' => $atendido,
                'procedimento_id' => $ag->procedimento_id,
                'tuss_id' => $ag->tuss_id,
                'convenio_id' => $ag->convenio_id,
                'profissional_saude_id' => $ag->profissional_saude_id,
            ];
        });

        return response()->json([
            'success' => true,
            'agendamentos' => $result
        ]);
    }
}
