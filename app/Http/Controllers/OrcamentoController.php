<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Orcamento;
use App\Models\OrcamentoProcedimento;
use App\Models\Paciente;
use App\Models\Pessoa;
use App\Models\Convenio;
use App\Models\Procedimento;
use App\Models\Pagamento;
use App\Models\MovimentacaoCaixa;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;

class OrcamentoController extends Controller
{
    private function isConvenio($convenioId)
    {
        if (empty($convenioId)) {
            return false;
        }
        $tipo = Convenio::where('id', $convenioId)->value('tipo');
        return strtoupper((string)$tipo) === 'CONVENIO';
    }

    private function validationRules(): array
    {
        return [
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'convenio_id' => ['required', 'integer', 'exists:convenios,id'],
            'data_emissao' => ['nullable', 'date_format:d-m-Y'],
            'validade' => ['nullable', 'date_format:d-m-Y'],
            'desconto' => ['nullable', 'numeric', 'min:0'],
            'validade' => ['nullable', 'date_format:d-m-Y'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.procedimento_id' => ['nullable', 'integer', 'exists:procedimentos,id'],
            'itens.*.tuss_id' => ['nullable', 'integer', 'exists:tuss,id'],
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
            'itens.*.procedimento_id.exists' => 'Selecione um procedimento válido.',
            'itens.*.tuss_id.exists' => 'Selecione um procedimento TUSS válido.',
            'itens.*.quantidade.required' => 'Informe a quantidade.',
            'itens.*.quantidade.min' => 'A quantidade deve ser maior que zero.',
        ];
    }

    private function validateOrcamentoPayload(Request $request): array
    {
        $data = $request->validate($this->validationRules(), $this->validationMessages());

        $convenioId = (int)($data['convenio_id'] ?? 0);
        $isConvenio = $this->isConvenio($convenioId);

        $itens = is_array($data['itens'] ?? null) ? $data['itens'] : [];
        if ($isConvenio) {
            foreach ($itens as $i => $it) {
                $tid = (int)($it['tuss_id'] ?? 0);
                if ($tid <= 0) {
                    throw ValidationException::withMessages([
                        "itens.{$i}.tuss_id" => ['Informe o procedimento TUSS.'],
                    ]);
                }
            }
        } else {
            foreach ($itens as $i => $it) {
                $pid = (int)($it['procedimento_id'] ?? 0);
                if ($pid <= 0) {
                    throw ValidationException::withMessages([
                        "itens.{$i}.procedimento_id" => ['Informe o procedimento.'],
                    ]);
                }
            }
        }

        return $data;
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


    private function getTussIdsPermitidosPorConvenio(int $convenioId): array
    {
        return DB::table('convenio_tuss as ct')
            ->join('tuss as t', function ($j) {
                $j->on('t.id', '=', 'ct.tuss_id')
                    ->whereNull('t.deleted_at');
            })
            ->where('ct.convenio_id', $convenioId)
            ->whereNull('ct.deleted_at')
            ->select('t.id')
            ->distinct()
            ->pluck('id')
            ->map(fn ($v) => (int)$v)
            ->values()
            ->all();
    }

    private function assertItensPermitidosPorConvenio(int $convenioId, array $itens): void
    {
        if (!$this->isConvenio($convenioId)) {
            return;
        }

        $hasTuss = DB::table('convenio_tuss')
            ->where('convenio_id', $convenioId)
            ->whereNull('deleted_at')
            ->exists();
        if (!$hasTuss) {
            throw ValidationException::withMessages([
                'itens' => ['Este convênio não possui procedimentos TUSS vinculados.'],
            ]);
        }

        $requestedTussIds = array_values(array_unique(array_map(
            fn ($i) => (int)($i['tuss_id'] ?? 0),
            is_array($itens) ? $itens : []
        )));
        $requestedTussIds = array_values(array_filter($requestedTussIds, fn ($id) => $id > 0));
        if (empty($requestedTussIds)) return;

        $allowedSet = array_fill_keys($this->getTussIdsPermitidosPorConvenio($convenioId), true);
        $invalid = array_values(array_filter($requestedTussIds, fn ($id) => !array_key_exists($id, $allowedSet)));
        if (!$invalid) return;

        $names = DB::table('tuss')
            ->whereNull('deleted_at')
            ->whereIn('id', array_slice($invalid, 0, 10))
            ->selectRaw("concat(COALESCE(tabela,''),' ',COALESCE(codigo,''),' - ',COALESCE(descricao,'')) as nome")
            ->pluck('nome')
            ->all();
        $names = array_map(fn ($n) => trim((string)$n) !== '' ? trim((string)$n) : '—', $names);
        $more = count($invalid) > 10 ? (' (+' . (count($invalid) - 10) . ' outros)') : '';
        throw ValidationException::withMessages([
            'itens' => ['Procedimentos não atendidos por este convênio: ' . implode(', ', $names) . $more . '.'],
        ]);
    }

    private function calcularItensValores(array $itens, int $convenioId): array
    {
        $valorBruto = 0.0;
        $itensValores = [];
        $isConvenio = $this->isConvenio($convenioId);

        if ($isConvenio) {
            $tussIds = array_values(array_unique(array_map(fn ($i) => (int)($i['tuss_id'] ?? 0), $itens)));
            $tussIds = array_values(array_filter($tussIds, fn ($id) => $id > 0));
            $tussMap = DB::table('tuss')
                ->whereNull('deleted_at')
                ->whereIn('id', $tussIds)
                ->pluck('total', 'id')
                ->all();

            foreach ($itens as $idx => $item) {
                $tid = (int)($item['tuss_id'] ?? 0);
                $valorUnit = array_key_exists($tid, $tussMap) ? (float)($tussMap[$tid] ?? 0) : 0;
                $qtd = (int)($item['quantidade'] ?? 1);
                $vTotal = ($valorUnit ?? 0) * $qtd;
                $valorBruto += $vTotal;
                $itensValores[$idx] = [
                    'procedimento_id' => null,
                    'tuss_id' => $tid,
                    'quantidade' => $qtd,
                    'valor_unitario' => $valorUnit ?? 0,
                    'valor_total' => $vTotal,
                    'observacoes' => $item['observacoes'] ?? null,
                ];
            }

            return [$valorBruto, $itensValores];
        }

        $procIds = array_values(array_unique(array_map(fn ($i) => (int)($item['procedimento_id'] ?? 0), $itens)));
        $procIds = array_values(array_filter($procIds, fn ($id) => $id > 0));
        $procs = Procedimento::select('id', 'valor')->whereIn('id', $procIds)->get()->keyBy('id');

        foreach ($itens as $idx => $item) {
            $procId = (int)($item['procedimento_id'] ?? 0);
            $proc = $procs->get($procId) ?? Procedimento::select('id', 'valor')->findOrFail($procId);
            $valorUnit = $proc->valor ?? 0;
            $qtd = (int)($item['quantidade'] ?? 1);
            $vTotal = ($valorUnit ?? 0) * $qtd;
            $valorBruto += $vTotal;
            $itensValores[$idx] = [
                'procedimento_id' => $proc->id,
                'tuss_id' => null,
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
        $procedimentos = Procedimento::select('id', 'nome', 'descricao', 'valor', 'categoria_id', 'eh_tratamento', 'quantidade_sessoes')
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get();
        $ultimos = DB::table('orcamentos as o')
            ->leftJoin('pacientes as p', 'p.id', '=', 'o.paciente_id')
            ->select(
                'o.id',
                'o.numero',
                DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y') AS data_emissao"),
                DB::raw("DATE_FORMAT(o.updated_at, '%d-%m-%Y %H:%i') AS criado_em"),
                'o.valor_total',
                'o.valor_total',
                DB::raw("COALESCE(p.nome,'') AS paciente"),
            )
            ->selectRaw("0 AS pago")
            ->selectSub(function ($q) {
                $q->from('convenios as c')
                    ->whereColumn('c.id', 'o.convenio_id')
                    ->whereRaw('UPPER(c.tipo) != ?', ['PARTICULAR'])
                    ->limit(1)
                    ->select(DB::raw('1'));
            }, 'is_convenio')
            ->orderByDesc('o.updated_at')
            ->limit(10)
            ->get();

        return Inertia::render('Recepcao/Orcamentos/Index', [
            'pacientes' => $pacientes,
            'convenios' => $convenios,
            'procedimentos' => $procedimentos,
            'ultimos' => $ultimos,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateOrcamentoPayload($request);
        $pacienteId = (int)$data['paciente_id'];
        $convenioId = (int)$data['convenio_id'];
        $this->assertPacienteConvenioAtivo($pacienteId, $convenioId);
        $this->assertItensPermitidosPorConvenio($convenioId, (array)($data['itens'] ?? []));
        $isConvenioPayor = $this->isConvenio($convenioId);
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
        $orcamento = null;
        DB::transaction(function () use (&$orcamento, $numero, $deYmd, $vaYmd, $data, $convenioId, $valorBruto, $desconto, $valorTotal, $itensValores, $isConvenioPayor) {
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
            ]);

            foreach ($itensValores as $iv) {
                $iv['orcamento_id'] = $orcamento->id;
                OrcamentoProcedimento::create($iv);
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
                'o.valor_total',
                'o.paciente_id',
                'o.convenio_id',
                DB::raw("COALESCE(pa.nome,'') AS paciente")
            )
            ->selectRaw("0 AS pago")
            ->selectSub(function ($q) {
                $q->from('convenios as c')
                    ->whereColumn('c.id', 'o.convenio_id')
                    ->whereRaw('UPPER(c.tipo) != ?', ['PARTICULAR'])
                    ->limit(1)
                    ->select(DB::raw('1'));
            }, 'is_convenio')
            ->where('o.paciente_id', $id)
            ->orderByDesc('o.created_at')
            ->get();
        return response()->json([
            'orcamentos' => $orcamentos,
        ]);
    }


    public function show(Request $request, string $id)
    {
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
                'o.valor_total',
                DB::raw("COALESCE(p.cpf,'') AS paciente_cpf")
            )
            ->selectRaw("0 AS pago")
            ->where('o.id', $id)
            ->first();
        if (!$o) {
            abort(404);
        }
        $includeAll = (bool)$request->query('include_all', false);
        $itensQuery = DB::table('orcamento_procedimentos as op')
            ->leftJoin('procedimentos as pr', 'pr.id', '=', 'op.procedimento_id');

        if (Schema::hasColumn('orcamento_procedimentos', 'tuss_id')) {
            $itensQuery->leftJoin('tuss as t', 't.id', '=', 'op.tuss_id');
        }

        $select = [
            'op.id',
            'op.procedimento_id',
            'op.quantidade',
            'op.valor_unitario',
            'op.valor_total',
            'op.observacoes',
        ];
        if (Schema::hasColumn('orcamento_procedimentos', 'tuss_id')) {
            $select[] = 'op.tuss_id';
            $select[] = DB::raw("CASE WHEN op.tuss_id IS NOT NULL THEN concat(COALESCE(t.tabela,''),' ',COALESCE(t.codigo,'')) ELSE COALESCE(pr.nome,'') END AS procedimento_nome");
            $select[] = DB::raw("CASE WHEN op.tuss_id IS NOT NULL THEN COALESCE(t.descricao,'') ELSE COALESCE(pr.descricao,'') END AS procedimento_desc");
            if (Schema::hasColumn('tuss', 'eh_tratamento') && Schema::hasColumn('procedimentos', 'eh_tratamento')) {
                $select[] = DB::raw("CASE WHEN op.tuss_id IS NOT NULL THEN COALESCE(t.eh_tratamento,0) ELSE COALESCE(pr.eh_tratamento,0) END AS eh_tratamento");
            }
            if (Schema::hasColumn('tuss', 'quantidade_sessoes') && Schema::hasColumn('procedimentos', 'quantidade_sessoes')) {
                $select[] = DB::raw("CASE WHEN op.tuss_id IS NOT NULL THEN COALESCE(t.quantidade_sessoes,0) ELSE COALESCE(pr.quantidade_sessoes,0) END AS quantidade_sessoes");
            }
        } else {
            $select[] = DB::raw("COALESCE(pr.nome,'') AS procedimento_nome");
            $select[] = DB::raw("COALESCE(pr.descricao,'') AS procedimento_desc");
            if (Schema::hasColumn('procedimentos', 'eh_tratamento')) {
                $select[] = DB::raw("COALESCE(pr.eh_tratamento,0) AS eh_tratamento");
            }
            if (Schema::hasColumn('procedimentos', 'quantidade_sessoes')) {
                $select[] = DB::raw("COALESCE(pr.quantidade_sessoes,0) AS quantidade_sessoes");
            }
        }

        $itensQuery->select($select)
            ->where('op.orcamento_id', $id)
            ->whereNull('op.deleted_at');
        $itens = $itensQuery->get();

        $agendamentos = [];

        return response()->json([
            'orcamento' => $o,
            'itens' => $itens,
            'agendamentos' => $agendamentos,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $orcamento = Orcamento::findOrFail($id);
        $isExpired = false;
        try {
            $vd = (string)$orcamento->validade;
            if ($vd) {
                $d = Carbon::parse($vd)->endOfDay();
                $isExpired = Carbon::now()->gt($d);
            }
        } catch (\Throwable $e) { }
        if ($isExpired) {
            return response()->json([
                'errors' => [
                    'orcamento' => ['Orçamento bloqueado por validade expirada ou pagamento confirmado.']
                ]
            ], 422);
        }
        $data = $this->validateOrcamentoPayload($request);
        $pacienteId = (int)$data['paciente_id'];
        $convenioId = (int)$data['convenio_id'];
        $this->assertPacienteConvenioAtivo($pacienteId, $convenioId);
        $this->assertItensPermitidosPorConvenio($convenioId, (array)($data['itens'] ?? []));
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
                'o.valor_total',
                DB::raw("COALESCE(p.nome,'') AS paciente"),
                DB::raw("COALESCE(p.cpf,'') AS cpf")
            )
            ->selectRaw("NULL AS pagamento_id")
            ->selectRaw("0 AS pago")
            ->selectSub(function ($q) {
                $q->from('convenios as c')
                    ->whereColumn('c.id', 'o.convenio_id')
                    ->whereRaw('UPPER(c.tipo) != ?', ['PARTICULAR'])
                    ->limit(1)
                    ->select(DB::raw('1'));
            }, 'is_convenio');
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

        ->selectRaw("NULL AS data_pagamento")
        ->selectSub(function ($q3) {
            $q3->from('orcamento_procedimentos as op')
               ->whereColumn('op.orcamento_id', 'o.id')
               ->whereNull('op.deleted_at')
               ->select(DB::raw('COALESCE(SUM(op.quantidade),0)'));
        }, 'procedimentos_total')
        ->selectRaw("0 AS agendamentos_ativos_total");

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

        $query->selectRaw("0 AS proc_agendados_ativos_count");
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
                'o.valor_total',
                DB::raw("COALESCE(p.nome,'') AS paciente_nome"),
                DB::raw("COALESCE(p.cpf,'') AS paciente_cpf"),
            )
            ->where('o.id', $id)
            ->first();
        if (!$o) {
            abort(404);
        }
        $itensQuery = DB::table('orcamento_procedimentos as op')
            ->leftJoin('procedimentos as pr', 'pr.id', '=', 'op.procedimento_id');
        if (Schema::hasColumn('orcamento_procedimentos', 'tuss_id')) {
            $itensQuery->leftJoin('tuss as t', 't.id', '=', 'op.tuss_id');
        }
        $select = [
            'op.id',
            'op.procedimento_id',
            'op.quantidade',
            'op.valor_unitario',
            'op.valor_total',
            'op.observacoes',
        ];
        if (Schema::hasColumn('orcamento_procedimentos', 'tuss_id')) {
            $select[] = 'op.tuss_id';
            $select[] = DB::raw("CASE WHEN op.tuss_id IS NOT NULL THEN concat(COALESCE(t.tabela,''),' ',COALESCE(t.codigo,'')) ELSE COALESCE(pr.nome,'') END AS procedimento_nome");
            $select[] = DB::raw("CASE WHEN op.tuss_id IS NOT NULL THEN COALESCE(t.descricao,'') ELSE COALESCE(pr.descricao,'') END AS procedimento_desc");
        } else {
            $select[] = DB::raw("COALESCE(pr.nome,'') AS procedimento_nome");
            $select[] = DB::raw("COALESCE(pr.descricao,'') AS procedimento_desc");
        }
        $itens = $itensQuery
            ->select($select)
            ->where('op.orcamento_id', $id)
            ->whereNull('op.deleted_at')
            ->get();
        return Inertia::render('Recepcao/Orcamentos/OrcamentoPrint', [
            'orcamento' => $o,
            'itens' => $itens,
        ]);
    }

}
