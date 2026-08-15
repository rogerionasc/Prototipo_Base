<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Agendamento;
use App\Models\Paciente;
use App\Models\Pessoa;
use App\Models\Procedimento;
use App\Models\AgendaMedica;
use App\Models\StatusAgendamento;
use App\Models\Pagamento;
use App\Models\Autorizacao;
use App\Models\Convenio;
use Illuminate\Support\Facades\Auth;

class AgendamentoController extends Controller
{
    public function index()
    {
        $pacientes = Paciente::select('id','nome','cpf')->orderBy('nome')->get();
        $profissionais = Pessoa::with('especialidades:id')->select('id','nome')->orderBy('nome')->get();
        $procedimentos = Procedimento::select('id','nome','valor','eh_tratamento','quantidade_sessoes')
            ->with('especialidades:id,nome')
            ->orderBy('nome')->get();
        $status = StatusAgendamento::select('id','descricao')->orderBy('descricao')->get();
        $weekday = Carbon::now()->dayOfWeek;
        $agendasHoje = DB::table('agenda_medica as a')
            ->join('pessoas as ps', 'ps.id', '=', 'a.pessoa_id')
            ->leftJoin('profissional_especialidade as pe', 'pe.pessoa_id', '=', 'ps.id')
            ->leftJoin('especialidades as e', 'e.id', '=', 'pe.especialidade_id')
            ->select(
                'a.pessoa_id',
                DB::raw("COALESCE(ps.nome,'') AS nome"),
                'a.hora_inicio',
                'a.hora_fim',
                DB::raw("GROUP_CONCAT(DISTINCT e.nome ORDER BY e.nome SEPARATOR ', ') AS especialidades")
            )
            ->where('a.dia_semana', $weekday)
            ->groupBy('a.pessoa_id','ps.nome','a.hora_inicio','a.hora_fim')
            ->orderBy('ps.nome')
            ->get();
        return Inertia::render('Recepcao/Agendamentos/Index', [
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
            ->join('pessoas as ps', 'ps.id', '=', 'a.pessoa_id')
            ->leftJoin('profissional_especialidade as pe', 'pe.pessoa_id', '=', 'ps.id')
            ->leftJoin('especialidades as e', 'e.id', '=', 'pe.especialidade_id')
            ->select(
                'a.pessoa_id',
                DB::raw("COALESCE(ps.nome,'') AS nome"),
                DB::raw("MIN(a.hora_inicio) AS hora_inicio"),
                DB::raw("MAX(a.hora_fim) AS hora_fim"),
                DB::raw("GROUP_CONCAT(DISTINCT e.nome ORDER BY e.nome SEPARATOR ', ') AS especialidades")
            )
            ->where('a.dia_semana', $weekday)
            ->groupBy('a.pessoa_id','ps.nome')
            ->orderBy('ps.nome')
            ->get();
        return response()->json([
            'agendas' => $agendas,
        ]);
    }

    public function countsByWeekday()
    {
        $rows = DB::table('agenda_medica')
            ->select('dia_semana', DB::raw('COUNT(DISTINCT pessoa_id) AS cnt'))
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
            'ids.*' => ['integer','exists:pessoas,id'],
        ]);
        $rows = DB::table('agenda_medica')
            ->select('dia_semana', DB::raw('GROUP_CONCAT(DISTINCT pessoa_id) AS prof_ids'))
            ->whereIn('pessoa_id', $data['ids'])
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
            'pessoa_id' => ['required','integer','exists:pessoas,id'],
            'procedimento_id' => $procRule,
            'data' => ['required','date_format:Y-m-d'],
            'hora' => ['required','regex:/^\d{2}:\d{2}$/'],
            'status_id' => ['nullable','integer','exists:status_agendamento,id'],
            'valor_cobrado' => ['nullable','numeric','min:0'],
            'observacoes' => ['nullable','string'],
            'convenio_id' => ['nullable','integer','exists:convenios,id'],
            'is_retorno' => ['nullable','boolean'],
            'numero_autorizacao' => ['nullable','string'],
            'validade_autorizacao' => ['nullable','date'],
            'procedimentosAdicionais' => ['nullable','array'],
            'procedimentosAdicionais.*.procedimento_id' => $procRule,
            'procedimentosAdicionais.*.pessoa_id' => ['required','integer','exists:pessoas,id'],
            'procedimentosAdicionais.*.data' => ['required','date_format:Y-m-d'],
            'procedimentosAdicionais.*.hora' => ['required','regex:/^\d{2}:\d{2}$/'],
        ]);

        $isRetorno = $request->input('is_retorno', false);

        $itemsToCreate = [];
        $itemsToCreate[] = [
            'procedimento_id' => $data['procedimento_id'],
            'data' => $data['data'],
            'hora' => $data['hora'],
            'pessoa_id' => $data['pessoa_id'],
            'is_master' => true,
        ];

        if (!empty($data['procedimentosAdicionais'])) {
            foreach ($data['procedimentosAdicionais'] as $add) {
                if (!empty($add['procedimento_id'])) {
                    $itemsToCreate[] = [
                        'procedimento_id' => $add['procedimento_id'],
                        'data' => $add['data'],
                        'hora' => $add['hora'],
                        'pessoa_id' => $add['pessoa_id'],
                        'valor_cobrado' => isset($add['valor_cobrado']) && $add['valor_cobrado'] !== '' ? (float)str_replace(['.', ','], ['', '.'], $add['valor_cobrado']) : null,
                        'is_master' => false,
                    ];
                }
            }
        }

        foreach ($itemsToCreate as &$item) {
            $dtItem = Carbon::createFromFormat('Y-m-d', $item['data'])->startOfDay();
            if ($dtItem->lessThan(Carbon::today())) {
                return response()->json(['errors' => ['data' => ['Não é permitido agendar em data passada.']]], 422);
            }
            $weekdayItem = $dtItem->dayOfWeek;
            $agendaItem = AgendaMedica::where('pessoa_id', (int)$item['pessoa_id'])
                ->where('dia_semana', $weekdayItem)
                ->first();
            if (!$agendaItem) {
                return response()->json(['errors' => ['agenda' => ['Profissional não possui agenda para o dia selecionado.']]], 422);
            }
            $horaItem = $item['hora'];
            $hi = substr((string)$agendaItem->hora_inicio, 0, 5);
            $hf = substr((string)$agendaItem->hora_fim, 0, 5);
            if (strtotime($horaItem) < strtotime($hi) || strtotime($horaItem) > strtotime($hf)) {
                return response()->json(['errors' => ['hora' => ['Horário fora do intervalo da agenda médica.']]], 422);
            }
            $item['agenda_id'] = $agendaItem->id;
            $item['dt'] = $dtItem;
        }
        unset($item);

        $agendamentoOrigemId = null;
        if ($isRetorno) {
            $diasRetorno = 0;
            if (!empty($convenioIdInput)) {
                $conv = Convenio::find($convenioIdInput);
                if ($conv) $diasRetorno = (int) $conv->dias_retorno;
            }

            $lastAgendamento = Agendamento::where('paciente_id', (int)$data['paciente_id'])
                ->whereHas('agendaMedica', function($q) use ($data) {
                    $q->where('pessoa_id', (int)$data['pessoa_id']);
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
            if ($itemsToCreate[0]['dt']->diffInDays($dataUltimo) > $diasRetorno) {
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

        $createdAgendamentos = DB::transaction(function () use ($data, $itemsToCreate, $isConvenio, $isRetorno, $agendamentoOrigemId) {
            $pacId = (int)$data['paciente_id'];
            $valorCobrado = $data['valor_cobrado'] ?? null;
            $convenioId = $data['convenio_id'] ?? null;

            $masterId = null;
            $agendamentosInseridos = [];

            foreach ($itemsToCreate as $item) {
                $procId = (int)$item['procedimento_id'];

                if ($isConvenio) {
                    $proc = DB::table('tuss')->select('id', 'total as valor', 'eh_tratamento', 'quantidade_sessoes')->where('id', $procId)->first();
                } else {
                    $proc = Procedimento::select('id','valor', 'eh_tratamento', 'quantidade_sessoes')->findOrFail($procId);
                }

                $valorItem = array_key_exists('valor_cobrado', $item) ? $item['valor_cobrado'] : $valorCobrado;
                if ($valorItem === null || $valorItem === '') {
                    $valorItem = (float)($proc->valor ?? 0);
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
                        'data_prevista' => $item['dt']->toDateString(),
                        'realizada' => false,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'deleted_at' => null,
                    ]);
                }

                $agendamentoStatusId = $data['status_id'] ?? null;
                if (!$agendamentoStatusId) {
                    $statusAgendado = \App\Models\StatusAgendamento::firstOrCreate(['descricao' => 'Agendado']);
                    $agendamentoStatusId = $statusAgendado->id;
                }

                $origemIdToUse = $agendamentoOrigemId;
                if (!$item['is_master']) {
                    $origemIdToUse = $masterId ?? $agendamentoOrigemId;
                }

                $agendamento = Agendamento::create([
                    'agenda_medica_id' => $item['agenda_id'],
                    'data' => $item['dt']->toDateString(),
                    'hora' => $item['hora'],
                    'paciente_id' => $pacId,
                    'procedimento_id' => $isConvenio ? null : $procId,
                    'tuss_id' => $isConvenio ? $procId : null,
                    'sessao_tratamento_id' => $sessaoId,
                    'status_id' => $agendamentoStatusId,
                    'agendamento_origem_id' => $origemIdToUse,
                    'valor_cobrado' => $valorItem,
                    'observacoes' => $data['observacoes'] ?? null,
                    'convenio_id' => $convenioId,
                ]);

                if ($item['is_master'] && !$agendamentoOrigemId) {
                    $masterId = $agendamento->id;
                }

                if ($isConvenio && $convenioId) {
                    $convenioTuss = DB::table('convenio_tuss')
                        ->where('convenio_id', $convenioId)
                        ->where('tuss_id', $procId)
                        ->whereNull('deleted_at')
                        ->first();

                    $requerAutorizacao = $convenioTuss && $convenioTuss->requer_autorizacao;

                    $pacienteConvenio = DB::table('paciente_convenio')
                        ->where('paciente_id', $pacId)
                        ->where('convenio_id', $convenioId)
                        ->where('ativo', 1)
                        ->whereNull('deleted_at')
                        ->first();

                    Autorizacao::create([
                        'convenio_id' => $convenioId,
                        'agendamento_id' => $agendamento->id,
                        'tuss_id' => $isConvenio ? $procId : null,
                        'valor' => $valorItem,
                        'carteira' => $pacienteConvenio->numero_carteira ?? null,
                        'numero_autorizacao' => $data['numero_autorizacao'] ?? null,
                        'status' => $requerAutorizacao ? 'SOLICITADA' : 'AUTORIZADA',
                        'validade' => $data['validade_autorizacao'] ?? null,
                        'data_solicitacao' => Carbon::now(),
                        'data_resposta' => $requerAutorizacao ? null : Carbon::now(),
                        'observacao' => 'Gerado automaticamente pelo agendamento #' . $agendamento->id,
                        'usuario_id' => Auth::id() ?? 1,
                        'usuario_id_validou' => null,
                    ]);
                }

                if (!$isConvenio) {
                    $fatId = (int)DB::table('faturamentos')->insertGetId([
                        'paciente_id'      => $pacId,
                        'valor_final'      => (float)$valorItem,
                        'convenio_id'      => $convenioId,
                        'valor_total'      => (float)$valorItem,
                        'valor_cobrado'    => (float)$valorItem,
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
                        'convenio_id' => $convenioId,
                        'valor' => (float)$valorItem,
                        'vencimento' => Carbon::today()->toDateString(),
                        'status' => 'ABERTO',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);

                    Pagamento::create([
                        'faturamento_id'  => $fatId,
                        'caixa_id'        => null,
                        'movimentacao_id' => null,
                        'valor'           => (float)$valorItem,
                        'forma_pagamento' => null,
                        'data_pagamento'  => null,
                        'status'          => 'PENDENTE',
                    ]);
                }

                $agendamentosInseridos[] = $agendamento;
            }

            return $agendamentosInseridos;
        });

        return response()->json([
            'success' => true,
            'agendamento' => $createdAgendamentos[0]->fresh(), // Keep original structure for UI
            'agendamentos' => $createdAgendamentos, // Array with all created
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
            ->leftJoin('agenda_medica as am', 'am.id', '=', 'a.agenda_medica_id')
            ->leftJoin('pessoas as prof', 'prof.id', '=', 'am.pessoa_id')
            ->leftJoin('convenios as c', 'c.id', '=', 'a.convenio_id')
            ->where(function ($q) {
                $q->where('s.descricao', 'NOT LIKE', '%Atendido%')
                  ->orWhereNull('s.descricao');
            })
            ->select(
                'a.id',
                'a.data',
                'a.hora',
                'a.paciente_id',
                'a.convenio_id',
                DB::raw("COALESCE(c.tipo, c.descricao, 'Particular') AS convenio_tipo"),
                DB::raw('COALESCE(a.procedimento_id, a.tuss_id) AS procedimento_id'),
                'a.sessao_tratamento_id',
                DB::raw('COALESCE(st.numero_sessao, NULL) AS sessao_numero'),
                DB::raw('COALESCE(pr.quantidade_sessoes, t.quantidade_sessoes, NULL) AS sessao_total'),
                DB::raw("COALESCE(p.nome,'') AS paciente"),
                DB::raw("COALESCE(pr.nome, t.descricao, '') AS procedimento"),
                DB::raw("COALESCE(s.descricao,'') AS status"),
                'prof.id AS pessoa_id',
                'prof.nome AS medico',
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
            ->leftJoin('agenda_medica as am', 'am.id', '=', 'a.agenda_medica_id')
            ->leftJoin('pagamentos as pag', 'pag.agendamento_id', '=', 'a.id')
            ->leftJoin('faturamentos as f', 'f.id', '=', 'pag.faturamento_id')
            ->leftJoin('autorizacoes as au', 'au.agendamento_id', '=', 'a.id')
            ->leftJoin('atendimentos as at', 'at.agendamento_id', '=', 'a.id')
            ->where('a.id', (int)$id)
            ->whereNull('a.deleted_at')
            ->select(
                'a.id',
                'a.paciente_id',
                'am.pessoa_id',
                DB::raw('COALESCE(a.convenio_id, f.convenio_id) AS convenio_id'),
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
                DB::raw('COALESCE(pr.quantidade_sessoes, t.quantidade_sessoes, NULL) AS sessao_total'),
                DB::raw('COALESCE(pag.status, "") AS status_pagamento'),
                'au.id AS autorizacao_id',
                'au.numero_autorizacao',
                'au.validade AS validade_autorizacao',
                'at.status AS status_atendimento'
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

        $convenioIdInput = $request->input('convenio_id', $agendamento->convenio_id);
        $isConvenio = false;
        if (!empty($convenioIdInput)) {
            $conv = Convenio::select('tipo')->find($convenioIdInput);
            if ($conv && strtoupper((string)$conv->tipo) !== 'PARTICULAR') {
                $isConvenio = true;
            }
        }

        $procRule = $isConvenio ? ['nullable', 'integer', 'exists:tuss,id'] : ['nullable', 'integer', 'exists:procedimentos,id'];

        $data = $request->validate([
            'paciente_id' => ['nullable','integer','exists:pacientes,id'],
            'pessoa_id' => ['nullable','integer','exists:pessoas,id'],
            'procedimento_id' => $procRule,
            'data' => ['nullable','date_format:Y-m-d'],
            'hora' => ['nullable','regex:/^\d{2}:\d{2}$/'],
            'status_id' => ['nullable','integer','exists:status_agendamento,id'],
            'valor_cobrado' => ['nullable','numeric','min:0'],
            'observacoes' => ['nullable','string'],
            'convenio_id' => ['nullable','integer','exists:convenios,id'],
            'numero_autorizacao' => ['nullable','string'],
            'validade_autorizacao' => ['nullable','date'],
        ]);
        $payload = [];
        foreach (['paciente_id','pessoa_id','data','hora','status_id','valor_cobrado','observacoes', 'convenio_id'] as $k) {
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

        if (array_key_exists('data', $payload) && $payload['data']) {
            $dt = Carbon::createFromFormat('Y-m-d', $payload['data'])->startOfDay();
            if ($dt->lessThan(Carbon::today())) {
                return response()->json([
                    'errors' => [
                        'data' => ['Não é permitido agendar em data passada.']
                    ]
                ], 422);
            }
        }

        $pessoaId = $payload['pessoa_id'] ?? ($agendamento->agendaMedica->pessoa_id ?? null);
        $novaData = $payload['data'] ?? $agendamento->data;

        if (isset($payload['pessoa_id']) || isset($payload['data'])) {
            if ($pessoaId && $novaData) {
                $dt = Carbon::parse($novaData);
                $weekday = $dt->dayOfWeek;
                $agenda = \App\Models\AgendaMedica::firstOrCreate(
                    ['pessoa_id' => $pessoaId, 'dia_semana' => $weekday],
                    ['horario_inicio' => '08:00', 'horario_fim' => '18:00', 'duracao_consulta' => 30]
                );
                $payload['agenda_medica_id'] = $agenda->id;
            }
        }
        
        if (array_key_exists('pessoa_id', $payload)) {
            unset($payload['pessoa_id']);
        }

        // Se estiver atribuindo data para uma sessão pendente, cancelada ou reagendada, muda o status para 'Agendado'
        if (isset($payload['data']) && isset($payload['hora']) && !isset($payload['status_id'])) {
            $statusAtual = $agendamento->status_id ? \App\Models\StatusAgendamento::find($agendamento->status_id) : null;
            $desc = $statusAtual ? strtolower(trim((string)$statusAtual->descricao)) : '';
            if (empty($desc) || $desc === 'a agendar' || str_contains($desc, 'cancel') || str_contains($desc, 'reagend')) {
                $statusAgendado = \App\Models\StatusAgendamento::firstOrCreate(['descricao' => 'Agendado']);
                $payload['status_id'] = $statusAgendado->id;
            }
        }

        if (!empty($payload)) {
            if (isset($payload['data']) && $payload['data'] !== $agendamento->data) {
                $atendimento = \App\Models\Atendimento::where('agendamento_id', $agendamento->id)->first();
                if ($atendimento) {
                    if ($atendimento->status === 'AGUARDANDO') {
                        $atendimento->delete();
                    } elseif ($atendimento->status === 'NÃO ATENDIDO') {
                        $atendimento->update([
                            'data_atendimento' => $payload['data'],
                            'hora_prevista'    => $payload['data'] . ' ' . ($payload['hora'] ?? $agendamento->hora),
                        ]);
                    }
                }
            }
            $agendamento->update($payload);

            if (array_key_exists('numero_autorizacao', $data) || array_key_exists('validade_autorizacao', $data)) {
                $authPayload = [];
                if (array_key_exists('numero_autorizacao', $data)) $authPayload['numero_autorizacao'] = $data['numero_autorizacao'];
                if (array_key_exists('validade_autorizacao', $data)) $authPayload['validade'] = $data['validade_autorizacao'];
                
                if (!empty($authPayload)) {
                    $origemId = $agendamento->agendamento_origem_id ?? $agendamento->id;
                    $grupoIds = \App\Models\Agendamento::where('id', $origemId)
                        ->orWhere('agendamento_origem_id', $origemId)
                        ->pluck('id');

                    foreach ($grupoIds as $ag_id) {
                        $agAux = \App\Models\Agendamento::find($ag_id);
                        if (!$agAux || !$agAux->convenio_id) continue;

                        $autorizacao = \App\Models\Autorizacao::where('agendamento_id', $ag_id)->first();
                        if ($autorizacao) {
                            $autorizacao->update($authPayload);
                        } else {
                            $pacienteConvenio = \Illuminate\Support\Facades\DB::table('pacientes_convenios')
                                ->where('paciente_id', $agAux->paciente_id)
                                ->where('convenio_id', $agAux->convenio_id)
                                ->first();

                            \App\Models\Autorizacao::create([
                                'convenio_id' => $agAux->convenio_id,
                                'agendamento_id' => $ag_id,
                                'tuss_id' => $agAux->tuss_id,
                                'valor' => $agAux->valor_cobrado,
                                'carteira' => $pacienteConvenio->numero_carteira ?? null,
                                'numero_autorizacao' => $authPayload['numero_autorizacao'] ?? null,
                                'status' => 'AUTORIZADA',
                                'validade' => $authPayload['validade'] ?? null,
                                'data_solicitacao' => \Carbon\Carbon::now(),
                                'usuario_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                            ]);
                        }
                    }
                }
            }
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
            if ($desc && strpos($desc, 'atendido') !== false) {
                return response()->json([
                    'errors' => [
                        'status' => ['Sessões já atendidas não podem ser reagendadas.']
                    ]
                ], 422);
            }
        }

        $data = $request->validate([
            'pessoa_id' => ['required','integer','exists:pessoas,id'],
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
        $agenda = AgendaMedica::where('pessoa_id', (int)$data['pessoa_id'])
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

        if ($dt->toDateString() !== $agendamento->data) {
            $atendimento = \App\Models\Atendimento::where('agendamento_id', $agendamento->id)->first();
            if ($atendimento) {
                if ($atendimento->status === 'AGUARDANDO') {
                    // Check-in já feito mas data mudou: cancela presença
                    $atendimento->delete();
                } elseif ($atendimento->status === 'NÃO ATENDIDO') {
                    // Pagamento já feito: apenas atualiza a data do atendimento
                    $atendimento->update([
                        'data_atendimento' => $dt->toDateString(),
                        'hora_prevista'    => $dt->toDateString() . ' ' . $data['hora'],
                    ]);
                }
            }
        }

        $statusAgendado = \App\Models\StatusAgendamento::firstOrCreate(['descricao' => 'Agendado']);

        $agendamento->update([
            'agenda_medica_id' => $agenda->id,
            'data'             => $dt->toDateString(),
            'hora'             => $hora,
            'status_id'        => $statusAgendado->id,
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
                $profissionais = Pessoa::query()
                    ->join('convenio_medico_tuss as cmt', 'cmt.pessoa_id', '=', 'pessoas.id')
                    ->where('cmt.convenio_id', $convenioId)
                    ->where('cmt.tuss_id', $procedimentoId)
                    ->select('pessoas.id', 'pessoas.nome')
                    ->distinct()
                    ->orderBy('pessoas.nome')
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
        $profissionais = Pessoa::query()
            ->join('profissional_especialidade as pe', 'pe.pessoa_id', '=', 'pessoas.id')
            ->whereIn('pe.especialidade_id', $especialidadesIds)
            ->select('pessoas.id', 'pessoas.nome')
            ->distinct()
            ->orderBy('pessoas.nome')
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
            ->leftJoin('pessoas as prof', 'prof.id', '=', 'am.pessoa_id')

            ->leftJoin('pagamentos as pag', 'pag.agendamento_id', '=', 'a.id')
            ->leftJoin('faturamentos as f', 'f.id', '=', 'pag.faturamento_id')
            ->leftJoin('atendimentos as at', 'at.agendamento_id', '=', 'a.id')
            ->leftJoin('convenios as conv', 'conv.id', '=', DB::raw('COALESCE(at.convenio_id, a.convenio_id)'))
            ->where('a.paciente_id', $paciente_id)
            ->select(
                'a.id',
                'a.data',
                'a.hora',
                'a.procedimento_id',
                'a.tuss_id',
                DB::raw('COALESCE(at.convenio_id, a.convenio_id) AS convenio_id'),
                DB::raw('COALESCE(conv.descricao, conv.tipo, "Particular") AS convenio_nome'),
                'am.pessoa_id',
                DB::raw('COALESCE(CONCAT(pr.nome, CASE WHEN st.numero_sessao IS NOT NULL AND pr.quantidade_sessoes IS NOT NULL THEN CONCAT(" (Sessão ", st.numero_sessao, "/", pr.quantidade_sessoes, ")") WHEN st.numero_sessao IS NOT NULL THEN CONCAT(" (Sessão ", st.numero_sessao, ")") ELSE "" END), t.descricao, "") AS procedimento_nome'),
                DB::raw('COALESCE(prof.nome, "") AS profissional_nome'),
                DB::raw('COALESCE(s.descricao, "") AS status'),
                'pag.nu_pagamento',
                DB::raw('COALESCE(pag.status, "N/A") AS status_pagamento')
            )
            ->orderByRaw("CASE WHEN LOWER(COALESCE(s.descricao, '')) LIKE '%atendido%' OR LOWER(COALESCE(s.descricao, '')) LIKE '%cancelado%' THEN 1 ELSE 0 END ASC")
            ->orderByRaw("CASE WHEN LOWER(COALESCE(s.descricao, '')) LIKE '%atendido%' OR LOWER(COALESCE(s.descricao, '')) LIKE '%cancelado%' THEN a.data END DESC")
            ->orderByRaw("CASE WHEN a.data IS NULL THEN 0 ELSE 1 END ASC")
            ->orderBy('a.data', 'asc')
            ->orderBy('st.numero_sessao', 'asc')
            ->orderBy('a.hora', 'asc')
            ->get();

        $convenioParticularId = DB::table('convenios')
            ->whereNull('deleted_at')
            ->where(function($q) {
                $q->whereRaw('UPPER(tipo) = ?', ['PARTICULAR'])
                  ->orWhereRaw('UPPER(descricao) = ?', ['PARTICULAR']);
            })
            ->value('id');

        $result = $agendamentos->map(function ($ag) use ($convenioParticularId) {
            $stRaw = strtolower(trim((string)$ag->status));
            $atendido = str_contains($stRaw, 'atendido');

            $convenioId = $ag->convenio_id;
            $convenioNome = $ag->convenio_nome;

            if (!$convenioId && ($ag->procedimento_id || !$ag->tuss_id)) {
                $convenioId = $convenioParticularId;
                $convenioNome = 'Particular';
            }

            return [
                'id' => $ag->id,
                'data' => $ag->data,
                'hora' => substr((string)$ag->hora, 0, 5),
                'procedimento' => $ag->procedimento_nome ?: 'Procedimento',
                'profissional' => $ag->profissional_nome ?: 'Profissional',
                'status' => $ag->status ?: 'Agendado',
                'nu_pagamento' => $ag->nu_pagamento,
                'status_pagamento' => $ag->status_pagamento,
                'atendido' => $atendido,
                'procedimento_id' => $ag->procedimento_id,
                'tuss_id' => $ag->tuss_id,
                'convenio_id' => $convenioId,
                'convenio_nome' => $convenioNome,
                'pessoa_id' => $ag->pessoa_id,
                'is_virtual' => false,
            ];
        })->toArray();

        // Calcular sessões pendentes para tratamentos
        $maxSessoes = DB::table('sessoes_tratamento')
            ->select('procedimento_id', 'tuss_id', DB::raw('MAX(numero_sessao) as max_num'))
            ->where('paciente_id', $paciente_id)
            ->groupBy('procedimento_id', 'tuss_id')
            ->get();

        $virtuais = [];
        foreach ($maxSessoes as $ms) {
            if ($ms->procedimento_id) {
                $proc = DB::table('procedimentos')->select('id', 'nome', 'quantidade_sessoes')->find($ms->procedimento_id);
                if ($proc && $proc->quantidade_sessoes > 0) {
                    $qtd = (int)$proc->quantidade_sessoes;
                    $max = (int)$ms->max_num;
                    $expected = ceil($max / $qtd) * $qtd;
                    for ($i = $max + 1; $i <= $expected; $i++) {
                        $virtuais[] = [
                            'id' => null,
                            'data' => null,
                            'hora' => null,
                            'procedimento' => $proc->nome . " (Sessão $i/$qtd)",
                            'profissional' => '—',
                            'status' => 'Pendente de Agendamento',
                            'nu_pagamento' => null,
                            'status_pagamento' => '—',
                            'atendido' => false,
                            'procedimento_id' => $proc->id,
                            'convenio_id'      => $convenioParticularId,
                            'pessoa_id' => null,
                            'is_virtual' => true,
                        ];
                    }
                }
            } elseif ($ms->tuss_id) {
                $tuss = DB::table('tuss')->select('id', 'descricao', 'quantidade_sessoes')->find($ms->tuss_id);
                if ($tuss && $tuss->quantidade_sessoes > 0) {
                    $qtd = (int)$tuss->quantidade_sessoes;
                    $max = (int)$ms->max_num;
                    $expected = ceil($max / $qtd) * $qtd;
                    for ($i = $max + 1; $i <= $expected; $i++) {
                        $virtuais[] = [
                            'id' => null,
                            'data' => null,
                            'hora' => null,
                            'procedimento' => $tuss->descricao . " (Sessão $i/$qtd)",
                            'profissional' => '—',
                            'status' => 'Pendente de Agendamento',
                            'nu_pagamento' => null,
                            'status_pagamento' => '—',
                            'atendido' => false,
                            'procedimento_id' => null,
                            'tuss_id' => $tuss->id,
                            'convenio_id' => null,
                            'pessoa_id' => null,
                            'is_virtual' => true,
                        ];
                    }
                }
            }
        }

        $result = array_merge($result, $virtuais);

        return response()->json([
            'success' => true,
            'agendamentos' => $result
        ]);
    }
}
