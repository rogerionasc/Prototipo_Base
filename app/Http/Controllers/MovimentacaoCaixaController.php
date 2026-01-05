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
        $ultimos = DB::table('movimentacoes_caixa as m')
            ->leftJoin('caixas as c', 'c.id', '=', 'm.caixa_id')
            ->select(
                'm.id',
                'm.numero',
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
        $pagamentosPendentes = DB::table('pagamentos as p')
            ->leftJoin('orcamentos as o', 'o.id', '=', 'p.orcamento_id')
            ->leftJoin('pacientes as pa', 'pa.id', '=', 'o.paciente_id')
            ->select(
                'p.id',
                'p.orcamento_id',
                'p.caixa_id',
                'p.valor',
                'p.forma_pagamento',
                'p.confirmado',
                'p.status',
                DB::raw("COALESCE(pa.nome,'') AS paciente"),
                DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y %H:%i') AS data_orcamento")
            )
            ->where('p.confirmado', false)
            ->where('p.status', 'pendente')
            ->whereNull('o.deleted_at')
            ->where('o.aprovado', true)
            ->orderByDesc('p.created_at')
            ->limit(100)
            ->get();
        $ultimosPagamentos = DB::table('pagamentos as p')
            ->leftJoin('orcamentos as o', 'o.id', '=', 'p.orcamento_id')
            ->leftJoin('pacientes as pa', 'pa.id', '=', 'o.paciente_id')
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
            ->where('p.confirmado', true)
            ->orderByDesc('p.data_pagamento')
            ->orderByDesc('p.created_at')
            ->limit(10)
            ->get();
        $pagamentosRecusados = DB::table('pagamentos as p')
            ->leftJoin('orcamentos as o', 'o.id', '=', 'p.orcamento_id')
            ->leftJoin('pacientes as pa', 'pa.id', '=', 'o.paciente_id')
            ->select(
                'p.id',
                'p.orcamento_id',
                'p.valor',
                'p.forma_pagamento',
                'p.status',
                DB::raw("DATE_FORMAT(p.updated_at, '%d-%m-%Y %H:%i') AS data_recusa"),
                DB::raw("COALESCE(pa.nome,'') AS paciente"),
                DB::raw("COALESCE(o.numero,'') AS numero_orcamento"),
                DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y') AS data_orcamento")
            )
            ->where('p.confirmado', false)
            ->where('p.status', 'recusado')
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'caixa_id' => ['required','integer','exists:caixas,id'],
            'saldo_caixa' => ['nullable','numeric','min:0'],
        ]);
        $dateYmd = Carbon::today()->format('Y-m-d');
        $existsAberto = MovimentacaoCaixa::where('caixa_id', $data['caixa_id'])
            ->whereDate('data_movimento', $dateYmd)
            ->whereNull('fechado_em')
            ->exists();
        if ($existsAberto) {
            return back()->withErrors([
                'caixa_id' => 'Já existe movimentação aberta hoje para este caixa',
            ]);
        }
        $numero = 'MOV-' . $data['caixa_id'] . '-' . Carbon::today()->format('Ymd') . '-' . now()->format('His');
        $saldoInicial = (float)($data['saldo_caixa'] ?? 0);
        MovimentacaoCaixa::create([
            'caixa_id' => (int)$data['caixa_id'],
            'numero' => $numero,
            'data_movimento' => $dateYmd,
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
        ]);
        return back()->with('success', 'Caixa fechado com sucesso');
    }

    public function reopen(string $id)
    {
        $mov = MovimentacaoCaixa::findOrFail($id);
        $existsAberto = MovimentacaoCaixa::where('caixa_id', $mov->caixa_id)
            ->whereDate('data_movimento', $mov->data_movimento)
            ->whereNull('fechado_em')
            ->where('id', '!=', $mov->id)
            ->exists();
        if ($existsAberto) {
            return back()->withErrors([
                'reopen' => 'Já existe movimentação aberta para este caixa nesta data',
            ]);
        }
        $mov->update([
            'fechado_em' => null,
        ]);
        return back()->with('success', 'Movimentação reaberta');
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
            ->select(
                'm.id',
                'm.numero',
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
            ->leftJoin('orcamentos as o', 'o.id', '=', 'p.orcamento_id')
            ->leftJoin('pacientes as pa', 'pa.id', '=', 'o.paciente_id')
            ->select(
                'p.id',
                'p.orcamento_id',
                'p.caixa_id',
                'p.valor',
                'p.forma_pagamento',
                DB::raw("DATE_FORMAT(p.data_pagamento, '%d-%m-%Y') AS data_pagamento"),
                'p.confirmado',
                'p.status',
                DB::raw("COALESCE(pa.nome,'') AS paciente"),
                DB::raw("(SELECT GROUP_CONCAT(DISTINCT pr.nome ORDER BY pr.nome SEPARATOR ', ')
                          FROM orcamento_procedimentos AS op
                          LEFT JOIN procedimentos AS pr ON pr.id = op.procedimento_id
                          WHERE op.orcamento_id = p.orcamento_id
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
