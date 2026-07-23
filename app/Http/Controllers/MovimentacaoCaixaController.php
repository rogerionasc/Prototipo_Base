<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Caixa;
use App\Models\MovimentacaoCaixa;

class MovimentacaoCaixaController extends Controller
{
    public function index()
    {
        $caixas = Caixa::select('id','descricao','tipo','ativo','bloquear_receber','bloquear_pagar')->orderBy('descricao')->get();

        // Adicionar informação de movimentação aberta para cada caixa
        foreach ($caixas as $caixa) {
             $caixa->movimentacao_aberta = MovimentacaoCaixa::where('caixa_id', $caixa->id)
                 ->whereNull('fechado_em')
                 ->select(
                     'id',
                     'numero',
                     'aberto_por_id',
                     DB::raw("DATE_FORMAT(data_movimento, '%d-%m-%Y') AS data_movimento"),
                     DB::raw("DATE_FORMAT(created_at, '%H:%i') AS hora_abertura")
                 )
                 ->first();
         }

        $ultimos = DB::table('movimentacoes_caixa as m')
            ->leftJoin('caixas as c', 'c.id', '=', 'm.caixa_id')
            ->select(
                'm.id',
                'm.numero',
                'm.aberto_por_id',
                'm.fechado_por_id',
                DB::raw("DATE_FORMAT(m.data_movimento, '%d-%m-%Y') AS data_movimento"),
                DB::raw("DATE_FORMAT(m.created_at, '%H:%i') AS hora_abertura"),
                DB::raw("DATE_FORMAT(m.fechado_em, '%H:%i') AS hora_fechamento"),
                'm.fechado_em',
                'm.caixa_id',
                DB::raw("COALESCE(c.descricao,'') AS caixa"),
                'm.saldo_movimento',
                'm.total_entradas',
                'm.total_saidas',
                'm.saldo_caixa'
            )
            ->orderByDesc('m.data_movimento')
            ->orderByDesc('m.updated_at')
            ->limit(10)
            ->get();
        $movs = DB::table('movimentacoes_caixa as m')
            ->leftJoin('caixas as c', 'c.id', '=', 'm.caixa_id')
            ->select(
                'm.id',
                'm.numero',
                'm.aberto_por_id',
                'm.fechado_por_id',
                DB::raw("DATE_FORMAT(m.data_movimento, '%d-%m-%Y') AS data_movimento"),
                DB::raw("DATE_FORMAT(m.created_at, '%H:%i') AS hora_abertura"),
                DB::raw("DATE_FORMAT(m.fechado_em, '%H:%i') AS hora_fechamento"),
                'm.fechado_em',
                'm.caixa_id',
                DB::raw("COALESCE(c.descricao,'') AS caixa"),
                'm.saldo_movimento',
                'm.total_entradas',
                'm.total_saidas',
                'm.saldo_caixa',
                'm.total_conferencia',
                'm.valor_diferenca'
            )
            ->orderByDesc('m.data_movimento')
            ->orderByDesc('m.updated_at')
            ->limit(100)
            ->get();
        $pagamentosPendentes = $this->getPagamentosPendentes(request('data_pendentes'));
        $ultimosPagamentos = DB::table('pagamentos as p')
            ->leftJoin('faturamentos as f', 'f.id', '=', 'p.faturamento_id')
            ->leftJoin('orcamentos as o', 'o.id', '=', 'f.orcamento_id')
            ->leftJoin('pacientes as pa', 'pa.id', '=', 'f.paciente_id')
            ->leftJoin('caixas as c', 'c.id', '=', 'p.caixa_id')
            ->select(
                'p.id',
                'p.movimentacao_id',
                'p.caixa_id',
                'p.valor',
                'p.forma_pagamento',
                DB::raw("DATE_FORMAT(p.data_pagamento, '%d-%m-%Y') AS data_pagamento"),
                DB::raw("COALESCE(pa.nome,'') AS paciente"),
                DB::raw("COALESCE(c.descricao,'') AS caixa")
            )
            ->where('p.status', 'CONFIRMADO')
            ->where('f.tipo_pagador', 'PARTICULAR')
            ->orderByDesc('p.data_pagamento')
            ->orderByDesc('p.created_at')
            ->limit(10)
            ->get();
        $pagamentosRecusados = DB::table('pagamentos as p')
            ->leftJoin('faturamentos as f', 'f.id', '=', 'p.faturamento_id')
            ->leftJoin('orcamentos as o', 'o.id', '=', 'f.orcamento_id')
            ->leftJoin('pacientes as pa', 'pa.id', '=', 'f.paciente_id')
            ->select(
                'p.id as num_pagamento',
                'o.id as orcamento_id',
                'p.valor',
                'p.forma_pagamento',
                'p.status',
                DB::raw("DATE_FORMAT(p.updated_at, '%d-%m-%Y %H:%i') AS data_recusa"),
                DB::raw("COALESCE(pa.nome,'') AS paciente"),
                DB::raw("COALESCE(o.numero,'') AS numero_orcamento"),
                DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y') AS data_orcamento")
            )
            ->where('p.status', 'RECUSADO')
            ->where('f.tipo_pagador', 'PARTICULAR')
            ->orderByDesc('p.updated_at')
            ->limit(100)
            ->get();

        return Inertia::render('Financeiro/Caixa/Movimentacao', [
            'caixas' => $caixas,
            'ultimos' => $ultimos,
            'movs' => $movs,
            'pagamentosPendentes' => $pagamentosPendentes,
            'ultimosPagamentos' => $ultimosPagamentos,
            'pagamentosRecusados' => $pagamentosRecusados,
        ]);
    }

    private function getPagamentosPendentes($date = null)
    {
        $date = $date ?: now()->toDateString();
        $pendIds = DB::table('pagamentos as pg')
            ->select(DB::raw('MAX(pg.id) as id'), 'pg.faturamento_id')
            ->where('pg.status', 'PENDENTE')
            ->groupBy('pg.faturamento_id');

        return DB::table('faturamentos as f')
            ->leftJoin('orcamentos as o', 'o.id', '=', 'f.orcamento_id')
            ->leftJoin('pacientes as pa', 'pa.id', '=', 'f.paciente_id')
            ->leftJoin('contas_receber as cr', 'cr.faturamento_id', '=', 'f.id')
            ->leftJoinSub($pendIds, 'pp', function ($join) {
                $join->on('pp.faturamento_id', '=', 'f.id');
            })
            ->leftJoin('pagamentos as p', 'p.id', '=', 'pp.id')
            ->select(
                'f.id as faturamento_id',
                'o.id as orcamento_id',
                DB::raw('COALESCE(cr.valor, f.valor_final, f.valor_cobrado, f.valor_total, 0) AS valor'),
                DB::raw("COALESCE(pa.nome,'') AS paciente"),
                DB::raw("COALESCE(pa.cpf,'') AS paciente_documento"),
                DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y %H:%i') AS data_orcamento"),
                'p.id as pagamento_id',
                'p.caixa_id',
                'p.forma_pagamento',
                'p.status as pagamento_status'
            )
            ->where('f.tipo_pagador', 'PARTICULAR')
            ->where('f.status', 'AGUARDANDO_PAGAMENTO')
            ->whereDate('f.created_at', $date)
            ->whereNull('o.deleted_at')
            ->where('o.aprovado', true)
            ->orderByDesc('f.updated_at')
            ->orderByDesc('f.id')
            ->get();
    }

    public function pendentes()
    {
        return response()->json([
            'pagamentosPendentes' => $this->getPagamentosPendentes()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'caixa_id' => ['required','integer','exists:caixas,id'],
            'saldo_caixa' => ['nullable','numeric','min:0'],
        ]);
        $existsAberto = MovimentacaoCaixa::where('caixa_id', $data['caixa_id'])
            ->whereNull('fechado_em')
            ->exists();
        if ($existsAberto) {
            return back()->with('error', 'Já existe uma movimentação aberta para este caixa. Feche-a antes de abrir uma nova.');
        }
        $numero = 'MOV-' . $data['caixa_id'] . '-' . Carbon::today()->format('Ymd') . '-' . now()->format('His');
        $saldoInicial = (float)($data['saldo_caixa'] ?? 0);
        MovimentacaoCaixa::create([
            'caixa_id' => (int)$data['caixa_id'],
            'aberto_por_id' => auth()->id(),
            'numero' => $numero,
            'data_movimento' => Carbon::today()->format('Y-m-d'),
            'total_entradas' => 0,
            'total_saidas' => 0,
            'saldo_caixa' => $saldoInicial,
            'total_entrada_prazo' => 0,
            'total_saida_prazo' => 0,
            'total_transferencia' => 0,
            'total_conferencia' => 0,
            'saldo_movimento' => 0,
            'valor_diferenca' => 0,
            'observacoes_fechamento' => null,
        ]);
        return back()->with('success', 'Caixa aberto para hoje');
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'total_conferencia' => ['nullable','numeric','min:0'],
            'observacoes_fechamento' => ['nullable','string'],
        ]);
        $mov = MovimentacaoCaixa::findOrFail($id);
        $totEntradas = (float)($mov->total_entradas ?? 0);
        $totSaidas = (float)($mov->total_saidas ?? 0);
        $saldoInicial = (float)($mov->saldo_caixa ?? 0);
        $totConf = (float)($data['total_conferencia'] ?? (float)($mov->total_conferencia ?? 0));
        $saldoMov = $saldoInicial + $totEntradas - $totSaidas;
        $valorDif = $saldoInicial + $totEntradas - $totSaidas - $totConf;

        $mov->update([
            'total_conferencia' => $totConf,
            'saldo_movimento' => $saldoMov,
            'valor_diferenca' => $valorDif,
            'observacoes_fechamento' => $data['observacoes_fechamento'] ?? $mov->observacoes_fechamento,
            'fechado_em' => now(),
            'fechado_por_id' => auth()->id(),
        ]);
        return back()->with('success', 'Caixa fechado com sucesso');
    }

    public function reopen(string $id)
    {
        // Aceitar tanto o ID quanto o Número da movimentação
        $mov = MovimentacaoCaixa::where('id', $id)->orWhere('numero', $id)->firstOrFail();

        // Verificar se já existe uma movimentação aberta para este caixa (exceto a própria)
        $existsAberto = MovimentacaoCaixa::where('caixa_id', $mov->caixa_id)
            ->whereNull('fechado_em')
            ->where('id', '!=', $mov->id)
            ->exists();

        if ($existsAberto) {
            return back()->with('error', 'Já existe uma movimentação aberta para este caixa. Feche-a antes de reabrir outra.');
        }

        $mov->update([
            'fechado_em' => null,
            'fechado_por_id' => null,
            'reaberto_por_id' => auth()->id(),
        ]);

        return back()->with('success', 'Movimentação reaberta com sucesso');
    }

    public function destroy(string $id)
    {
        MovimentacaoCaixa::findOrFail($id)->delete();
        return back()->with('success', 'Movimentação removida');
    }

    public function show(string $id)
    {
        $mov = DB::table('movimentacoes_caixa as m')
            ->leftJoin('caixas as c', 'c.id', '=', 'm.caixa_id')
            ->leftJoin('users as ua', 'ua.id', '=', 'm.aberto_por_id')
            ->leftJoin('users as uf', 'uf.id', '=', 'm.fechado_por_id')
            ->select(
                'm.id',
                'm.numero',
                'm.aberto_por_id',
                'm.fechado_por_id',
                DB::raw("NULLIF(TRIM(CONCAT(COALESCE(ua.nome,''),' ',COALESCE(ua.sobrenome,''))), '') AS aberto_por"),
                DB::raw("NULLIF(TRIM(CONCAT(COALESCE(uf.nome,''),' ',COALESCE(uf.sobrenome,''))), '') AS fechado_por"),
                DB::raw("DATE_FORMAT(m.data_movimento, '%d-%m-%Y') AS data_movimento"),
                DB::raw("DATE_FORMAT(m.created_at, '%d-%m-%Y') AS data_abertura"),
                DB::raw("DATE_FORMAT(m.created_at, '%H:%i') AS hora_abertura"),
                DB::raw("DATE_FORMAT(m.fechado_em, '%H:%i') AS hora_fechamento"),
                DB::raw("DATE_FORMAT(m.fechado_em, '%d-%m-%Y') AS data_fechamento"),
                'm.fechado_em',
                'm.caixa_id',
                DB::raw("COALESCE(c.descricao,'') AS caixa"),
                'c.tipo',
                'c.bloquear_receber',
                'c.bloquear_pagar',
                'c.ativo',
                'm.saldo_movimento',
                'm.total_entradas',
                'm.total_saidas',
                'm.saldo_caixa',
                'm.total_conferencia',
                'm.valor_diferenca',
                'm.observacoes_fechamento'
            )
            ->where('m.id', $id)
            ->first();
        if (!$mov) {
            abort(404);
        }
        $pagamentos = DB::table('pagamentos as p')
            ->leftJoin('faturamentos as f', 'f.id', '=', 'p.faturamento_id')
            ->leftJoin('orcamentos as o', 'o.id', '=', 'f.orcamento_id')
            ->leftJoin('pacientes as pa', 'pa.id', '=', 'f.paciente_id')
            ->select(
                'p.id',
                'o.id as orcamento_id',
                'p.caixa_id',
                'p.valor',
                'p.forma_pagamento',
                DB::raw("DATE_FORMAT(p.data_pagamento, '%d-%m-%Y') AS data_pagamento"),
                'p.status',
                DB::raw("COALESCE(pa.nome,'') AS paciente"),
                DB::raw("(SELECT GROUP_CONCAT(DISTINCT pr.nome ORDER BY pr.nome SEPARATOR ', ')
                          FROM orcamento_procedimentos AS op
                          LEFT JOIN procedimentos AS pr ON pr.id = op.procedimento_id
                          WHERE op.orcamento_id = o.id
                            AND (op.deleted_at IS NULL)
                            AND (pr.deleted_at IS NULL)) AS procedimentos")
            )
            ->where('p.movimentacao_id', $id)
            ->orderByDesc('p.data_pagamento')
            ->orderByDesc('p.created_at')
            ->limit(500)
            ->get();
        return response()->json([
            'movimentacao' => $mov,
            'pagamentos' => $pagamentos,
        ]);
    }

}
