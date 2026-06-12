<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Orcamento;
use App\Models\OrcamentoProcedimento;
use App\Models\Paciente;
use App\Models\ProfissionalSaude;
use App\Models\Convenio;
use App\Models\Procedimento;
use App\Models\Pagamento;
use App\Models\MovimentacaoCaixa;
use Illuminate\Validation\ValidationException;

class OrcamentoController extends Controller
{
    private function resolvePayorByConvenioId(?int $convenioId): array
    {
        if (empty($convenioId)) {
            return ['tipo_pagador' => 'PARTICULAR', 'convenio_id' => null];
        }
        $tipo = Convenio::where('id', $convenioId)->value('tipo');
        if (strtoupper((string)$tipo) === 'PARTICULAR') {
            return ['tipo_pagador' => 'PARTICULAR', 'convenio_id' => null];
        }
        return ['tipo_pagador' => 'CONVENIO', 'convenio_id' => $convenioId];
    }

    private function validationRules(): array
    {
        return [
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'convenio_id' => ['required', 'integer', 'exists:convenios,id'],
            'data_emissao' => ['nullable', 'date_format:d-m-Y'],
            'validade' => ['nullable', 'date_format:d-m-Y'],
            'desconto' => ['nullable', 'numeric', 'min:0'],
            'faturamento_previsto' => ['nullable', 'boolean'],
            'aprovado' => ['nullable', 'boolean'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.procedimento_id' => ['required', 'integer', 'exists:procedimentos,id'],
            'itens.*.quantidade' => ['required', 'integer', 'min:1'],
            'itens.*.observacoes' => ['nullable', 'string'],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'paciente_id.required' => 'Informe o paciente.',
            'paciente_id.exists' => 'Selecione um paciente válido.',
            'convenio_id.required' => 'Informe o convênio.',
            'convenio_id.exists' => 'Selecione um convênio válido.',
            'itens.required' => 'Selecione ao menos um procedimento.',
            'itens.array' => 'Selecione ao menos um procedimento.',
            'itens.min' => 'Selecione ao menos um procedimento.',
            'itens.*.procedimento_id.required' => 'Informe o procedimento.',
            'itens.*.procedimento_id.exists' => 'Selecione um procedimento válido.',
            'itens.*.quantidade.required' => 'Informe a quantidade.',
            'itens.*.quantidade.min' => 'A quantidade deve ser maior que zero.',
        ];
    }

    private function validateOrcamentoPayload(Request $request): array
    {
        return $request->validate($this->validationRules(), $this->validationMessages());
    }

    private function assertPacienteConvenioAtivo(int $pacienteId, int $convenioId): void
    {
        $ok = DB::table('paciente_convenio as pc')
            ->join('convenios as cv', 'cv.id', '=', 'pc.convenio_id')
            ->where('pc.paciente_id', $pacienteId)
            ->where('pc.convenio_id', $convenioId)
            ->where('pc.ativo', 1)
            ->whereNull('pc.deleted_at')
            ->whereNull('cv.deleted_at')
            ->exists();
        if (!$ok) {
            throw ValidationException::withMessages([
                'convenio_id' => ['Selecione um convênio válido para este paciente.'],
            ]);
        }
    }

    private function getProcedimentoConvenioMap(int $convenioId): array
    {
        $map = [];
        $rows = DB::table('procedimento_convenio')
            ->where('convenio_id', $convenioId)
            ->select('procedimento_id', 'valor_convenio')
            ->get();
        foreach ($rows as $r) {
            $map[(string)$r->procedimento_id] = $r->valor_convenio;
        }
        return $map;
    }

    private function calcularItensValores(array $itens, int $convenioId): array
    {
        $valorBruto = 0.0;
        $itensValores = [];
        $mapConvenio = $this->getProcedimentoConvenioMap($convenioId);
        $tussTabela = trim((string)Convenio::where('id', $convenioId)->value('tuss_tabela'));

        $procIds = array_values(array_unique(array_map(fn($i) => (int)($i['procedimento_id'] ?? 0), $itens)));
        $procIds = array_values(array_filter($procIds, fn($id) => $id > 0));
        $procs = Procedimento::select('id', 'valor', 'nome')->whereIn('id', $procIds)->get()->keyBy('id');

        $tussMap = [];
        if (!$procs->isEmpty()) {
            $names = $procs->map(fn($p) => trim((string)($p->nome ?? '')))->filter(fn($n) => $n !== '')->unique()->values()->all();
            if (!empty($names)) {
                $rows = DB::table('convenio_tuss as ct')
                    ->join('tuss as t', 't.id', '=', 'ct.tuss_id')
                    ->where('ct.convenio_id', $convenioId)
                    ->whereNull('ct.deleted_at')
                    ->whereNull('t.deleted_at')
                    ->whereIn('t.descricao', $names)
                    ->select('t.descricao', 't.total', 'ct.created_at')
                    ->orderByDesc('ct.created_at')
                    ->orderByDesc('t.id')
                    ->get();

                if ($rows->isEmpty() && $tussTabela !== '') {
                    $rows = DB::table('tuss')
                        ->where('tabela', $tussTabela)
                        ->whereIn('descricao', $names)
                        ->select('descricao', 'total')
                        ->get();
                }
                foreach ($rows as $r) {
                    $k = mb_strtolower(trim((string)($r->descricao ?? '')));
                    if ($k !== '' && !array_key_exists($k, $tussMap)) $tussMap[$k] = $r->total;
                }
            }
        }

        foreach ($itens as $idx => $item) {
            $procId = (int)($item['procedimento_id'] ?? 0);
            $proc = $procs->get($procId) ?? Procedimento::select('id', 'valor', 'nome')->findOrFail($procId);
            $valorUnit = $proc->valor ?? 0;
            $k = (string)$proc->id;
            if (array_key_exists($k, $mapConvenio) && $mapConvenio[$k] !== null) {
                $valorUnit = $mapConvenio[$k];
            } elseif (!empty($tussMap) || $tussTabela !== '') {
                $nameKey = mb_strtolower(trim((string)($proc->nome ?? '')));
                if ($nameKey !== '' && array_key_exists($nameKey, $tussMap) && $tussMap[$nameKey] !== null) {
                    $valorUnit = $tussMap[$nameKey];
                }
            }
            $qtd = (int)($item['quantidade'] ?? 1);
            $vTotal = ($valorUnit ?? 0) * $qtd;
            $valorBruto += $vTotal;
            $itensValores[$idx] = [
                'procedimento_id' => $proc->id,
                'quantidade' => $qtd,
                'valor_unitario' => $valorUnit ?? 0,
                'valor_total' => $vTotal,
                'observacoes' => $item['observacoes'] ?? null,
            ];
        }

        return [$valorBruto, $itensValores];
    }

    public function index()
    {
        $pacientes = collect([]);
        $convenios = Convenio::select('id', 'descricao')->orderBy('descricao')->get();
        $procedimentos = Procedimento::select('id', 'nome', 'valor', 'categoria_id', 'eh_tratamento', 'quantidade_sessoes')->orderBy('nome')->get();
        $procConvenio = DB::table('procedimento_convenio')
            ->select('procedimento_id', 'convenio_id', 'valor_convenio')
            ->get();
        $ultimos = DB::table('orcamentos as o')
            ->leftJoin('pacientes as p', 'p.id', '=', 'o.paciente_id')
            ->select(
                'o.id',
                'o.numero',
                DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y') AS data_emissao"),
                DB::raw("DATE_FORMAT(o.updated_at, '%d-%m-%Y %H:%i') AS criado_em"),
                'o.valor_total',
                'o.aprovado',
                DB::raw("COALESCE(p.nome,'') AS paciente"),
            )
            ->selectSub(function ($q) {
                $q->from('pagamentos as pg')
                    ->join('faturamentos as f', 'f.id', '=', 'pg.faturamento_id')
                    ->whereColumn('f.orcamento_id', 'o.id')
                    ->where('pg.status', 'CONFIRMADO')
                    ->limit(1)
                    ->select(DB::raw('1'));
            }, 'pago')
            ->orderByDesc('o.updated_at')
            ->limit(10)
            ->get();

        return Inertia::render('Atendimento/Orcamentos/Index', [
            'pacientes' => $pacientes,
            'convenios' => $convenios,
            'procedimentos' => $procedimentos,
            'procedimentoConvenio' => $procConvenio,
            'ultimos' => $ultimos,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateOrcamentoPayload($request);
        $pacienteId = (int)$data['paciente_id'];
        $convenioId = (int)$data['convenio_id'];
        $this->assertPacienteConvenioAtivo($pacienteId, $convenioId);
        $payor = $this->resolvePayorByConvenioId($convenioId);
        $isConvenioPayor = $payor['tipo_pagador'] === 'CONVENIO';
        $faturamentoConvenioId = $payor['convenio_id'];
        [$valorBruto, $itensValores] = $this->calcularItensValores($data['itens'], $convenioId);

        $desconto = (float)($data['desconto'] ?? 0);
        $valorTotal = max(0, $valorBruto - $desconto);

        $numero = 'ORC-' . now()->format('YmdHis');
        $de = isset($data['data_emissao'])
            ? Carbon::createFromFormat('d-m-Y', $data['data_emissao'])->setTimeFrom(Carbon::now())
            : Carbon::now();
        $deYmd = $de->format('Y-m-d H:i:s');
        $vaYmd = isset($data['validade'])
            ? Carbon::createFromFormat('d-m-Y', $data['validade'])->format('Y-m-d')
            : now()->addDays(30)->toDateString();
        $isApproved = (bool)($data['aprovado'] ?? false);
        $orcamento = null;
        DB::transaction(function () use (&$orcamento, $numero, $deYmd, $vaYmd, $data, $convenioId, $valorBruto, $desconto, $valorTotal, $itensValores, $isApproved, $isConvenioPayor, $faturamentoConvenioId) {
            $orcamento = Orcamento::create([
                'numero' => $numero,
                'data_emissao' => $deYmd,
                'validade' => $vaYmd,
                'paciente_id' => $data['paciente_id'],
                'convenio_id' => $convenioId,
                'valor_bruto' => $valorBruto,
                'desconto' => $desconto,
                'valor_total' => $valorTotal,
                'valor_avista' => null,
                'faturamento_previsto' => (bool)($data['faturamento_previsto'] ?? false),
                'aprovado' => $isApproved,
                'status' => $isApproved ? 'APROVADO' : 'ABERTO',
            ]);

            foreach ($itensValores as $iv) {
                $iv['orcamento_id'] = $orcamento->id;
                OrcamentoProcedimento::create($iv);
            }

            if ($isApproved) {
                $fatId = (int)DB::table('faturamentos')->where('orcamento_id', $orcamento->id)->value('id');
                if (!$fatId) {
                    $fatId = (int)DB::table('faturamentos')->insertGetId([
                        'paciente_id' => $orcamento->paciente_id,
                        'orcamento_id' => $orcamento->id,
                        'valor_final' => (float)($orcamento->valor_total ?? 0),
                        'tipo_pagador' => $isConvenioPayor ? 'CONVENIO' : 'PARTICULAR',
                        'convenio_id' => $isConvenioPayor ? $faturamentoConvenioId : null,
                        'valor_total' => (float)($orcamento->valor_bruto ?? 0),
                        'valor_cobrado' => (float)($orcamento->valor_total ?? 0),
                        'valor_aprovado' => 0,
                        'valor_glosado' => 0,
                        'status' => $isConvenioPayor ? 'AGUARDANDO_ENVIO' : 'AGUARDANDO_PAGAMENTO',
                        'data_faturamento' => now()->format('Y-m-d H:i:s'),
                        'vencimento' => $isConvenioPayor ? now()->addDays(30)->toDateString() : now()->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if ($fatId) {
                    $crId = (int)DB::table('contas_receber')->where('faturamento_id', $fatId)->value('id');
                    if (!$crId) {
                        DB::table('contas_receber')->insert([
                            'faturamento_id' => $fatId,
                            'paciente_id' => $orcamento->paciente_id,
                            'convenio_id' => $isConvenioPayor ? $faturamentoConvenioId : null,
                            'valor' => (float)($orcamento->valor_total ?? 0),
                            'vencimento' => $isConvenioPayor ? now()->addDays(30)->toDateString() : now()->toDateString(),
                            'status' => 'ABERTO',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                if ($fatId && !$isConvenioPayor) {
                    $exists = \App\Models\Pagamento::where('faturamento_id', $fatId)->where('status', 'PENDENTE')->exists();
                    if (!$exists) {
                        \App\Models\Pagamento::create([
                            'faturamento_id' => $fatId,
                            'caixa_id' => null,
                            'movimentacao_id' => null,
                            'valor' => (float)($orcamento->valor_total ?? 0),
                            'forma_pagamento' => null,
                            'data_pagamento' => null,
                            'status' => 'PENDENTE',
                        ]);
                    }
                }
            }
        });

        return back()->with('success', 'Orçamento criado com sucesso');
    }

    public function byPaciente(string $id)
    {
        $orcamentos = DB::table('orcamentos as o')
            ->leftJoin('pacientes as pa', 'pa.id', '=', 'o.paciente_id')
            ->select(
                'o.id',
                'o.numero',
                DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y') AS data_emissao"),
                DB::raw("DATE_FORMAT(o.validade, '%d-%m-%Y') AS validade"),
                'o.valor_bruto',
                'o.desconto',
                'o.valor_total',
                'o.aprovado',
                'o.paciente_id',
                DB::raw("COALESCE(pa.nome,'') AS paciente")
            )
            ->selectSub(function ($q) {
                $q->from('pagamentos as pg')
                    ->join('faturamentos as f', 'f.id', '=', 'pg.faturamento_id')
                    ->whereColumn('f.orcamento_id', 'o.id')
                    ->where('pg.status', 'CONFIRMADO')
                    ->limit(1)
                    ->select(DB::raw('1'));
            }, 'pago')
            ->where('o.paciente_id', $id)
            ->orderByDesc('o.created_at')
            ->get();
        return response()->json([
            'orcamentos' => $orcamentos,
        ]);
    }


    public function show(Request $request, string $id)
    {
        // dd($id);
        $o = DB::table('orcamentos as o')
            ->leftJoin('pacientes as p', 'p.id', '=', 'o.paciente_id')
            ->select(
                'o.id',
                'o.numero',
                DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y') AS data_emissao"),
                DB::raw("DATE_FORMAT(o.validade, '%d-%m-%Y') AS validade"),
                'o.paciente_id',
                DB::raw("COALESCE(p.nome,'') AS paciente_nome"),
                'o.convenio_id',
                'o.valor_bruto',
                'o.desconto',
                'o.valor_total',
                'o.faturamento_previsto',
                'o.aprovado',
                DB::raw("COALESCE(p.cpf,'') AS paciente_cpf")
            )
            ->selectSub(function ($q) {
                $q->from('pagamentos as pg')
                    ->join('faturamentos as f', 'f.id', '=', 'pg.faturamento_id')
                    ->whereColumn('f.orcamento_id', 'o.id')
                    ->where('pg.status', 'CONFIRMADO')
                    ->limit(1)
                    ->select(DB::raw('1'));
            }, 'pago')
            ->where('o.id', $id)
            ->first();
        if (!$o) {
            abort(404);
        }
        $includeAll = (bool)$request->query('include_all', false);
        $itensQuery = DB::table('orcamento_procedimentos as op')
            ->leftJoin('procedimentos as pr', 'pr.id', '=', 'op.procedimento_id')
            ->select(
                'op.id',
                'op.procedimento_id',
                'op.quantidade',
                'op.valor_unitario',
                'op.valor_total',
                'op.observacoes',
                DB::raw("COALESCE(pr.nome,'') AS procedimento_nome")
            )
            ->where('op.orcamento_id', $id)
            ->whereNull('op.deleted_at');
        if (!$includeAll) {
            $itensQuery->whereNotExists(function ($q) use ($id) {
                $q->from('agendamentos as a')
                  ->leftJoin('status_agendamento as s', 's.id', '=', 'a.status_id')
                  ->where('a.orcamento_id', $id)
                  ->whereNull('a.deleted_at')
                  ->whereColumn('a.procedimento_id', 'op.procedimento_id')
                  ->where(function ($qq) {
                      $qq->whereNull('s.id')
                         ->orWhereRaw("LOWER(s.descricao) NOT LIKE '%cancel%'");
                  });
            });
        }
        $itens = $itensQuery->get();
        return response()->json([
            'orcamento' => $o,
            'itens' => $itens,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $orcamento = Orcamento::findOrFail($id);
        $isApproved = !!$orcamento->aprovado;
        $hasPaid = DB::table('pagamentos as pg')
            ->join('faturamentos as f', 'f.id', '=', 'pg.faturamento_id')
            ->where('f.orcamento_id', $orcamento->id)
            ->where('pg.status', 'CONFIRMADO')
            ->exists();
        $isExpired = false;
        try {
            $vd = (string)$orcamento->validade;
            if ($vd) {
                $d = Carbon::parse($vd)->endOfDay();
                $isExpired = Carbon::now()->gt($d);
            }
        } catch (\Throwable $e) { }
        if ($isApproved || $isExpired || $hasPaid) {
            return response()->json([
                'errors' => [
                    'orcamento' => ['Orçamento bloqueado por aprovação, validade expirada ou pagamento confirmado.']
                ]
            ], 422);
        }
        $data = $this->validateOrcamentoPayload($request);
        $pacienteId = (int)$data['paciente_id'];
        $convenioId = (int)$data['convenio_id'];
        $this->assertPacienteConvenioAtivo($pacienteId, $convenioId);
        [$valorBruto, $itensValores] = $this->calcularItensValores($data['itens'], $convenioId);

        $desconto = (float)($data['desconto'] ?? 0);
        $valorTotal = max(0, $valorBruto - $desconto);

        $de = isset($data['data_emissao'])
            ? Carbon::createFromFormat('d-m-Y', $data['data_emissao'])->setTimeFrom(Carbon::now())
            : Carbon::parse($orcamento->data_emissao);
        $deYmd = $de->format('Y-m-d H:i:s');
        $vaYmd = isset($data['validade'])
            ? Carbon::createFromFormat('d-m-Y', $data['validade'])->format('Y-m-d')
            : $orcamento->validade;

        DB::transaction(function () use ($orcamento, $data, $deYmd, $vaYmd, $valorBruto, $desconto, $valorTotal, $itensValores) {
            $orcamento->update([
                'data_emissao' => $deYmd,
                'validade' => $vaYmd,
                'paciente_id' => $data['paciente_id'],
                'convenio_id' => $data['convenio_id'],
                'valor_bruto' => $valorBruto,
                'desconto' => $desconto,
                'valor_total' => $valorTotal,
                'faturamento_previsto' => (bool)($data['faturamento_previsto'] ?? false),
                'aprovado' => (bool)($data['aprovado'] ?? $orcamento->aprovado),
            ]);
            OrcamentoProcedimento::where('orcamento_id', $orcamento->id)->delete();
            foreach ($itensValores as $iv) {
                $iv['orcamento_id'] = $orcamento->id;
                OrcamentoProcedimento::create($iv);
            }
        });

        return back()->with('success', 'Orçamento atualizado com sucesso');
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if ($q === '') {
            return response()->json([
                'orcamentos' => [],
            ]);
        }
        $pacienteId = $request->get('paciente_id');
        $query = DB::table('orcamentos as o')
            ->leftJoin('pacientes as p', 'p.id', '=', 'o.paciente_id')
            ->select(
                'o.id',
                'o.numero',
                DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y') AS data_emissao"),
                DB::raw("DATE_FORMAT(o.validade, '%d-%m-%Y') AS validade"),
                'o.valor_total',
                'o.aprovado',
                DB::raw("COALESCE(p.nome,'') AS paciente"),
                DB::raw("COALESCE(p.cpf,'') AS cpf"),
            );
        if (!empty($pacienteId)) {
            $query->where('o.paciente_id', $pacienteId);
        }
        if ($q !== '') {
            $query->where(function ($qb) use ($q) {
                $qb->where('o.numero', 'like', '%' . $q . '%')
                   ->orWhere('p.nome', 'like', '%' . $q . '%')
                   ->orWhere('p.cpf', 'like', '%' . $q . '%');
            });
        }
        $results = $query->orderByDesc('o.created_at')->limit(100)->get();
        return response()->json([
            'orcamentos' => $results,
        ]);
    }
public function searchPaid(Request $request)
{
    $q = trim((string) $request->get('q', ''));
    $pacienteId = $request->get('paciente_id');
    $procId = $request->get('procedimento_id');

    $query = DB::table('orcamentos as o')
        ->leftJoin('pacientes as p', 'p.id', '=', 'o.paciente_id')
        ->select(
            'o.id',
            'o.numero',
            DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y') AS data_emissao"),
            'o.paciente_id',
            'o.valor_total',
            DB::raw("COALESCE(p.nome,'') AS paciente"),
            DB::raw("COALESCE(p.cpf,'') AS cpf")
        )

        // data do pagamento confirmado
        ->selectSub(function ($q2) {
            $q2->from('pagamentos as pg')
               ->join('faturamentos as f', 'f.id', '=', 'pg.faturamento_id')
               ->whereColumn('f.orcamento_id', 'o.id')
               ->where('pg.status', 'CONFIRMADO')
               ->select(DB::raw("DATE_FORMAT(MAX(pg.data_pagamento), '%d-%m-%Y')"));
        }, 'data_pagamento')

        // total de procedimentos do orçamento
        ->selectSub(function ($q3) {
            $q3->from('orcamento_procedimentos as op')
               ->whereColumn('op.orcamento_id', 'o.id')
               ->whereNull('op.deleted_at')
               ->select(DB::raw('COALESCE(SUM(op.quantidade),0)'));
        }, 'procedimentos_total')

        // total de agendamentos ativos do orçamento
        ->selectSub(function ($q4) {
            $q4->from('agendamentos as a')
               ->leftJoin('status_agendamento as s', 's.id', '=', 'a.status_id')
               ->whereColumn('a.orcamento_id', 'o.id')
               ->whereNull('a.deleted_at')
               ->where(function ($qq) {
                   $qq->whereNull('s.id')
                      ->orWhereRaw("LOWER(s.descricao) NOT LIKE '%cancel%'");
               })
               ->select(DB::raw('COUNT(*)'));
        }, 'agendamentos_ativos_total')

        // aprovado
        ->where('o.aprovado', true)

        // pagamento confirmado
        ->whereExists(function ($q5) {
            $q5->from('pagamentos as pg')
               ->join('faturamentos as f', 'f.id', '=', 'pg.faturamento_id')
               ->whereColumn('f.orcamento_id', 'o.id')
               ->where('pg.status', 'CONFIRMADO')
               ->select(DB::raw(1));
        })

        /**
         * 🔴 REGRA PRINCIPAL
         * Só retorna se ainda existir procedimento NÃO totalmente agendado
         */
        ->havingRaw('procedimentos_total > agendamentos_ativos_total');

    // filtro por paciente
    if (!empty($pacienteId)) {
        $query->where('o.paciente_id', $pacienteId);
    }

    // filtro por procedimento específico
    if (!empty($procId)) {
        $pid = (int) $procId;

        // quantidade do procedimento no orçamento
        $query->selectSub(function ($sub) use ($pid) {
            $sub->from('orcamento_procedimentos as op')
                ->whereColumn('op.orcamento_id', 'o.id')
                ->where('op.procedimento_id', $pid)
                ->whereNull('op.deleted_at')
                ->select(DB::raw('COALESCE(SUM(op.quantidade),0)'));
        }, 'proc_qtd_total');

        // quantidade de agendamentos ativos desse procedimento
        $query->selectSub(function ($sub) use ($pid) {
            $sub->from('agendamentos as a')
                ->leftJoin('status_agendamento as s', 's.id', '=', 'a.status_id')
                ->whereColumn('a.orcamento_id', 'o.id')
                ->where('a.procedimento_id', $pid)
                ->whereNull('a.deleted_at')
                ->where(function ($qq) {
                    $qq->whereNull('s.id')
                       ->orWhereRaw("LOWER(s.descricao) NOT LIKE '%cancel%'");
                })
                ->select(DB::raw('COUNT(*)'));
        }, 'proc_agendados_ativos_count')

        // regra específica do procedimento
        ->havingRaw('proc_qtd_total > proc_agendados_ativos_count');
    }

    // busca textual
    if ($q !== '') {
        $query->where(function ($qb) use ($q) {
            $qb->where('o.numero', 'like', "%{$q}%")
               ->orWhere('p.nome', 'like', "%{$q}%")
               ->orWhere('p.cpf', 'like', "%{$q}%");
        });
    }

    $results = $query
        ->orderByDesc('o.updated_at')
        ->limit(100)
        ->get();

    return response()->json([
        'orcamentos' => $results,
    ]);
}

    public function print(string $id)
    {
        $o = DB::table('orcamentos as o')
            ->leftJoin('pacientes as p', 'p.id', '=', 'o.paciente_id')
            ->select(
                'o.id',
                'o.numero',
                DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y') AS data_emissao"),
                DB::raw("DATE_FORMAT(o.validade, '%d-%m-%Y') AS validade"),
                'o.paciente_id',
                'o.convenio_id',
                'o.valor_bruto',
                'o.desconto',
                'o.valor_total',
                'o.faturamento_previsto',
                'o.aprovado',
                DB::raw("COALESCE(p.nome,'') AS paciente_nome"),
                DB::raw("COALESCE(p.cpf,'') AS paciente_cpf"),
            )
            ->where('o.id', $id)
            ->first();
        if (!$o) {
            abort(404);
        }
        $itens = DB::table('orcamento_procedimentos as op')
            ->leftJoin('procedimentos as pr', 'pr.id', '=', 'op.procedimento_id')
            ->select(
                'op.id',
                'op.procedimento_id',
                'op.quantidade',
                'op.valor_unitario',
                'op.valor_total',
                'op.observacoes',
                DB::raw("COALESCE(pr.nome,'') AS procedimento_nome")
            )
            ->where('op.orcamento_id', $id)
            ->whereNull('op.deleted_at')
            ->get();
        return Inertia::render('Atendimento/Orcamentos/OrcamentoPrint', [
            'orcamento' => $o,
            'itens' => $itens,
        ]);
    }
    public function approve(string $id)
    {
        $orcamento = Orcamento::findOrFail($id);
        if ($orcamento->aprovado) {
            return response()->json(['success' => true, 'message' => 'Orçamento já aprovado']);
        }
        $payor = $this->resolvePayorByConvenioId($orcamento->convenio_id ? (int)$orcamento->convenio_id : null);
        $isConvenioPayor = $payor['tipo_pagador'] === 'CONVENIO';
        $faturamentoConvenioId = $payor['convenio_id'];
        DB::transaction(function () use ($orcamento, $isConvenioPayor, $faturamentoConvenioId) {
            $orcamento->aprovado = true;
            $orcamento->status = 'APROVADO';
            $orcamento->save();
            $fatId = (int)DB::table('faturamentos')->where('orcamento_id', $orcamento->id)->value('id');
            if (!$fatId) {
                $fatId = (int)DB::table('faturamentos')->insertGetId([
                    'paciente_id' => $orcamento->paciente_id,
                    'orcamento_id' => $orcamento->id,
                    'valor_final' => (float)($orcamento->valor_total ?? 0),
                    'tipo_pagador' => $isConvenioPayor ? 'CONVENIO' : 'PARTICULAR',
                    'convenio_id' => $isConvenioPayor ? $faturamentoConvenioId : null,
                    'valor_total' => (float)($orcamento->valor_bruto ?? 0),
                    'valor_cobrado' => (float)($orcamento->valor_total ?? 0),
                    'valor_aprovado' => 0,
                    'valor_glosado' => 0,
                    'status' => $isConvenioPayor ? 'AGUARDANDO_ENVIO' : 'AGUARDANDO_PAGAMENTO',
                    'data_faturamento' => now()->format('Y-m-d H:i:s'),
                    'vencimento' => $isConvenioPayor ? now()->addDays(30)->toDateString() : now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($fatId) {
                $crId = (int)DB::table('contas_receber')->where('faturamento_id', $fatId)->value('id');
                if (!$crId) {
                    DB::table('contas_receber')->insert([
                        'faturamento_id' => $fatId,
                        'paciente_id' => $orcamento->paciente_id,
                        'convenio_id' => $isConvenioPayor ? $faturamentoConvenioId : null,
                        'valor' => (float)($orcamento->valor_total ?? 0),
                        'vencimento' => $isConvenioPayor ? now()->addDays(30)->toDateString() : now()->toDateString(),
                        'status' => 'ABERTO',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($fatId && !$isConvenioPayor) {
                $exists = \App\Models\Pagamento::where('faturamento_id', $fatId)->where('status', 'PENDENTE')->exists();
                if (!$exists) {
                    \App\Models\Pagamento::create([
                        'faturamento_id' => $fatId,
                        'caixa_id' => null,
                        'movimentacao_id' => null,
                        'valor' => (float)($orcamento->valor_total ?? 0),
                        'forma_pagamento' => null,
                        'data_pagamento' => null,
                        'status' => 'PENDENTE',
                    ]);
                }
            }
        });
        return response()->json(['success' => true]);
    }

    public function unapprove(string $id)
    {
        $orcamento = Orcamento::findOrFail($id);
        if (!$orcamento->aprovado) {
            return response()->json(['success' => true, 'message' => 'Orçamento já está aguardando aprovação']);
        }
        DB::transaction(function () use ($orcamento) {
            $fatId = (int)DB::table('faturamentos')->where('orcamento_id', $orcamento->id)->value('id');
            $pag = $fatId ? Pagamento::where('faturamento_id', $fatId)->orderByDesc('id')->first() : null;
            if ($pag && strtoupper((string)$pag->status) === 'CONFIRMADO') {
                $movId = (int)($pag->movimentacao_id ?? 0);
                $mov = $movId ? MovimentacaoCaixa::find($movId) : null;
                if ($mov) {
                    $totEntradas = max(0, (float)($mov->total_entradas ?? 0) - (float)($pag->valor ?? 0));
                    $totSaidas = (float)($mov->total_saidas ?? 0);
                    $saldoInicial = (float)($mov->saldo_caixa ?? 0);
                    $saldoMov = $saldoInicial + $totEntradas - $totSaidas;
                    $mov->update([
                        'total_entradas' => $totEntradas,
                        'saldo_movimento' => $saldoMov,
                    ]);
                }
                $pag->update([
                    'caixa_id' => null,
                    'movimentacao_id' => null,
                    'forma_pagamento' => null,
                    'data_pagamento' => null,
                    'status' => 'CANCELADO',
                ]);
            }
            if ($fatId) {
                DB::table('faturamentos')
                    ->where('id', $fatId)
                    ->update([
                        'status' => 'CANCELADO',
                        'updated_at' => now(),
                    ]);
                DB::table('contas_receber')
                    ->where('faturamento_id', $fatId)
                    ->update([
                        'status' => 'CANCELADO',
                        'updated_at' => now(),
                    ]);
            }
            $orcamento->aprovado = false;
            $orcamento->status = 'ABERTO';
            $orcamento->save();
        });
        return response()->json(['success' => true]);
    }
}
