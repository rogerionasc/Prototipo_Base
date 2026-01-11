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
use Illuminate\Support\Facades\Log;



class OrcamentoController extends Controller
{
    public function index()
    {
        $pacientes = Paciente::select('id', 'nome', 'cpf', 'email', 'celular')->orderBy('nome')->get();
        $profissionais = ProfissionalSaude::select('id', 'nome', 'crm', 'celular', 'email')->orderBy('nome')->get();
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
                  ->whereColumn('pg.orcamento_id', 'o.id')
                  ->where('pg.confirmado', true)
                  ->limit(1)
                  ->select(DB::raw('1'));
            }, 'pago')
            ->orderByDesc('o.updated_at')
            ->limit(10)
            ->get();

        return Inertia::render('Atendimento/Orcamentos/Index', [
            'pacientes' => $pacientes,
            'profissionais' => $profissionais,
            'convenios' => $convenios,
            'procedimentos' => $procedimentos,
            'procedimentoConvenio' => $procConvenio,
            'ultimos' => $ultimos,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'profissional_saude_id' => ['required', 'integer', 'exists:profissionais_saude,id'],
            'convenio_id' => ['nullable', 'integer', 'exists:convenios,id'],
            'data_emissao' => ['nullable', 'date_format:d-m-Y'],
            'validade' => ['nullable', 'date_format:d-m-Y'],
            'desconto' => ['nullable', 'numeric', 'min:0'],
            'faturamento_previsto' => ['nullable', 'boolean'],
            'aprovado' => ['nullable', 'boolean'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.procedimento_id' => ['required', 'integer', 'exists:procedimentos,id'],
            'itens.*.quantidade' => ['required', 'integer', 'min:1'],
            'itens.*.observacoes' => ['nullable', 'string'],
        ]);

        $convenioId = $data['convenio_id'] ?? null;
        $valorBruto = 0;
        $itensValores = [];

        $mapConvenio = [];
        if ($convenioId) {
            $rows = DB::table('procedimento_convenio')
                ->where('convenio_id', $convenioId)
                ->select('procedimento_id', 'valor_convenio')
                ->get();
            foreach ($rows as $r) {
                $mapConvenio[(string)$r->procedimento_id] = $r->valor_convenio;
            }
        }

        foreach ($data['itens'] as $idx => $item) {
            $proc = Procedimento::select('id', 'valor')->findOrFail($item['procedimento_id']);
            $valorUnit = $proc->valor ?? 0;
            if ($convenioId && array_key_exists((string)$proc->id, $mapConvenio) && $mapConvenio[(string)$proc->id] !== null) {
                $valorUnit = $mapConvenio[(string)$proc->id];
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
        $orcamento = Orcamento::create([
            'numero' => $numero,
            'data_emissao' => $deYmd,
            'validade' => $vaYmd,
            'paciente_id' => $data['paciente_id'],
            'profissional_saude_id' => $data['profissional_saude_id'],
            'convenio_id' => $convenioId,
            'valor_bruto' => $valorBruto,
            'desconto' => $desconto,
            'valor_total' => $valorTotal,
            'valor_avista' => null,
            'faturamento_previsto' => (bool)($data['faturamento_previsto'] ?? false),
            'aprovado' => (bool)($data['aprovado'] ?? false),
        ]);

        foreach ($itensValores as $iv) {
            $iv['orcamento_id'] = $orcamento->id;
            OrcamentoProcedimento::create($iv);
        }

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
                'o.profissional_saude_id',
                DB::raw("COALESCE(pa.nome,'') AS paciente")
            )
            ->selectSub(function ($q) {
                $q->from('pagamentos as p')
                  ->whereColumn('p.orcamento_id', 'o.id')
                  ->where('p.confirmado', true)
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
                'o.profissional_saude_id',
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
                  ->whereColumn('pg.orcamento_id', 'o.id')
                  ->where('pg.confirmado', true)
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
        $hasPaid = DB::table('pagamentos')
            ->where('orcamento_id', $orcamento->id)
            ->where('confirmado', true)
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
        $data = $request->validate([
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'profissional_saude_id' => ['required', 'integer', 'exists:profissionais_saude,id'],
            'convenio_id' => ['nullable', 'integer', 'exists:convenios,id'],
            'data_emissao' => ['nullable', 'date_format:d-m-Y'],
            'validade' => ['nullable', 'date_format:d-m-Y'],
            'desconto' => ['nullable', 'numeric', 'min:0'],
            'faturamento_previsto' => ['nullable', 'boolean'],
            'aprovado' => ['nullable', 'boolean'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.procedimento_id' => ['required', 'integer', 'exists:procedimentos,id'],
            'itens.*.quantidade' => ['required', 'integer', 'min:1'],
            'itens.*.observacoes' => ['nullable', 'string'],
        ]);

        $convenioId = $data['convenio_id'] ?? null;
        $valorBruto = 0;
        $itensValores = [];

        $mapConvenio = [];
        if ($convenioId) {
            $rows = DB::table('procedimento_convenio')
                ->where('convenio_id', $convenioId)
                ->select('procedimento_id', 'valor_convenio')
                ->get();
            foreach ($rows as $r) {
                $mapConvenio[(string)$r->procedimento_id] = $r->valor_convenio;
            }
        }

        foreach ($data['itens'] as $idx => $item) {
            $proc = Procedimento::select('id', 'valor')->findOrFail($item['procedimento_id']);
            $valorUnit = $proc->valor ?? 0;
            if ($convenioId && array_key_exists((string)$proc->id, $mapConvenio) && $mapConvenio[(string)$proc->id] !== null) {
                $valorUnit = $mapConvenio[(string)$proc->id];
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
                'profissional_saude_id' => $data['profissional_saude_id'],
                'convenio_id' => $data['convenio_id'] ?? null,
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

    Log::debug('Chegou no search-paid', [
    'request' => request()->all()
]);

    $query = DB::table('orcamentos as o')
        ->leftJoin('pacientes as p', 'p.id', '=', 'o.paciente_id')
        ->select(
            'o.id',
            'o.numero',
            DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y') AS data_emissao"),
            'o.paciente_id',
            'o.profissional_saude_id',
            'o.valor_total',
            DB::raw("COALESCE(p.nome,'') AS paciente"),
            DB::raw("COALESCE(p.cpf,'') AS cpf")
        )

        // aprovado
        ->where('o.aprovado', true)

        // pagamento confirmado
        ->whereExists(function ($q2) {
            $q2->from('pagamentos as pg')
               ->whereColumn('pg.orcamento_id', 'o.id')
               ->where('pg.confirmado', true)
               ->select(DB::raw(1));
        })

        /**
         * 🔴 REGRA PRINCIPAL (ROBUSTA)
         * Total de procedimentos > total de agendamentos NÃO cancelados
         */
        ->whereRaw("
            (SELECT COALESCE(SUM(op.quantidade),0)
             FROM orcamento_procedimentos AS op
             WHERE op.orcamento_id = o.id
               AND op.deleted_at IS NULL)
            >
            (SELECT COUNT(*)
             FROM agendamentos AS a
             LEFT JOIN status_agendamento AS s ON s.id = a.status_id
             WHERE a.orcamento_id = o.id
               AND a.deleted_at IS NULL
               AND (s.id IS NULL OR LOWER(s.descricao) NOT LIKE '%cancel%'))
        ");

    // filtro por paciente
    if (!empty($pacienteId)) {
        $query->where('o.paciente_id', $pacienteId);
    }

    // filtro por procedimento específico
    if (!empty($procId)) {
        $pid = (int) $procId;

        $query->whereRaw("
            (SELECT COALESCE(SUM(op.quantidade),0)
             FROM orcamento_procedimentos AS op
             WHERE op.orcamento_id = o.id
               AND op.procedimento_id = ?
               AND op.deleted_at IS NULL)
            >
            (SELECT COUNT(*)
             FROM agendamentos AS a
             LEFT JOIN status_agendamento AS s ON s.id = a.status_id
             WHERE a.orcamento_id = o.id
               AND a.procedimento_id = ?
               AND a.deleted_at IS NULL
               AND (s.id IS NULL OR LOWER(s.descricao) NOT LIKE '%cancel%'))
        ", [$pid, $pid]);
    }

    // busca textual
    if ($q !== '') {
        $query->where(function ($qb) use ($q) {
            $qb->where('o.numero', 'like', "%{$q}%")
               ->orWhere('p.nome', 'like', "%{$q}%")
               ->orWhere('p.cpf', 'like', "%{$q}%");
        });
    }

    return response()->json([
        'orcamentos' => $query
            ->orderByDesc('o.updated_at')
            ->limit(100)
            ->get(),
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
                'o.profissional_saude_id',
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
        DB::transaction(function () use ($orcamento) {
            $orcamento->aprovado = true;
            $orcamento->save();
            // Criar pagamento pendente se não existir
            $exists = Pagamento::where('orcamento_id', $orcamento->id)->exists();
            if (!$exists) {
                Pagamento::create([
                    'orcamento_id' => $orcamento->id,
                    'caixa_id' => null,
                    'valor' => (float)($orcamento->valor_total ?? 0),
                    'forma_pagamento' => null,
                    'data_pagamento' => null,
                    'confirmado' => false,
                    'status' => 'pendente',
                ]);
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
            $pag = Pagamento::where('orcamento_id', $orcamento->id)->first();
            if ($pag && $pag->confirmado) {
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
                    'confirmado' => false,
                    'status' => 'pendente',
                ]);
            }
            $orcamento->aprovado = false;
            $orcamento->save();
        });
        return response()->json(['success' => true]);
    }
}
