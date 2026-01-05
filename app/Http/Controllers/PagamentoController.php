<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Pagamento;
use App\Models\MovimentacaoCaixa;
use App\Models\Caixa;
use Inertia\Inertia;

class PagamentoController extends Controller
{
    public function confirm(Request $request, string $id)
    {
        $data = $request->validate([
            'caixa_id' => ['required','integer','exists:caixas,id'],
            'forma_pagamento' => ['nullable','string'],
        ]);
        $pag = Pagamento::findOrFail($id);
        if ($pag->confirmado) {
            return back()->with('success', 'Pagamento já confirmado');
        }
        // Verificar disponibilidade do caixa
        $caixa = Caixa::select('id','ativo','bloquear_receber')->findOrFail((int)$data['caixa_id']);
        if (!$caixa->ativo || $caixa->bloquear_receber) {
            return back()->withErrors([
                'caixa' => 'Caixa indisponível para receber',
            ], 422);
        }
        // Verificar movimentação aberta no dia para o caixa
        $mov = MovimentacaoCaixa::where('caixa_id', (int)$data['caixa_id'])
            ->whereDate('data_movimento', Carbon::today()->format('Y-m-d'))
            ->whereNull('fechado_em')
            ->first();
        if (!$mov) {
            return back()->withErrors([
                'caixa' => 'Caixa sem movimentação aberta hoje',
            ], 422);
        }
        DB::transaction(function () use ($pag, $data, $mov) {
            // Atualizar movimentação aberta do dia para o caixa
            $pag->update([
                'caixa_id' => (int)$data['caixa_id'],
                'movimentacao_id' => $mov->id,
                'forma_pagamento' => $data['forma_pagamento'] ?? $pag->forma_pagamento,
                'data_pagamento' => Carbon::today()->format('Y-m-d'),
                'confirmado' => true,
                'status' => 'confirmado',
            ]);
            $totEntradas = (float)($mov->total_entradas ?? 0) + (float)($pag->valor ?? 0);
            $totSaidas = (float)($mov->total_saidas ?? 0);
            $saldoInicial = (float)($mov->saldo_caixa ?? 0);
            $saldoMov = $saldoInicial + $totEntradas - $totSaidas;
            $mov->update([
                'total_entradas' => $totEntradas,
                'saldo_movimento' => $saldoMov,
            ]);
        });
        return back()->with('success', 'Pagamento confirmado e lançado no caixa');
    }

    public function refuse(Request $request, string $id)
    {
        $data = $request->validate([
            'recusa_justificativa' => ['required','string','max:1000'],
        ]);
        $pag = Pagamento::findOrFail($id);
        if ($pag->confirmado) {
            return back()->withErrors([
                'pagamento' => 'Pagamento já confirmado, não é possível recusar.',
            ], 422);
        }
        if ($pag->status === 'recusado') {
            return back()->with('success', 'Pagamento já recusado');
        }
        $pag->update([
            'status' => 'recusado',
            'confirmado' => false,
            'caixa_id' => $pag->caixa_id ?? null,
            'movimentacao_id' => $pag->movimentacao_id ?? null,
            'forma_pagamento' => $pag->forma_pagamento ?? null,
            'data_pagamento' => null,
            'recusa_justificativa' => $data['recusa_justificativa'] ?? null,
            'recusado_por' => $request->user()?->id,
        ]);
        return back()->with('success', 'Pagamento recusado');
    }

    public function recusados()
    {
        $pagamentosRecusados = DB::table('pagamentos as p')
            ->leftJoin('orcamentos as o', 'o.id', '=', 'p.orcamento_id')
            ->leftJoin('pacientes as pa', 'pa.id', '=', 'o.paciente_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.recusado_por')
            ->select(
                'p.id',
                'p.orcamento_id',
                'p.valor',
                'p.forma_pagamento',
                'p.status',
                'p.recusa_justificativa',
                DB::raw("DATE_FORMAT(p.updated_at, '%d-%m-%Y %H:%i') AS data_recusa"),
                DB::raw("COALESCE(pa.nome,'') AS paciente"),
                DB::raw("COALESCE(o.numero,'') AS numero_orcamento"),
                DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y') AS data_orcamento"),
                DB::raw("COALESCE(CONCAT(u.nome, ' ', u.sobrenome),'') AS recusado_por_nome")
            )
            ->where('p.confirmado', false)
            ->where('p.status', 'recusado')
            ->orderByDesc('p.updated_at')
            ->limit(100)
            ->get();
        return Inertia::render('Financeiro/Caixa/Recusados', [
            'pagamentosRecusados' => $pagamentosRecusados,
        ]);
    }

    public function unrefuse(Request $request, string $id)
    {
        $pag = Pagamento::findOrFail($id);
        if ($pag->confirmado) {
            return back()->withErrors([
                'pagamento' => 'Pagamento confirmado não pode ser alterado.',
            ], 422);
        }
        if ($pag->status !== 'recusado') {
            return back()->with('success', 'Pagamento não está recusado');
        }
        $pag->update([
            'status' => 'pendente',
            'confirmado' => false,
            'data_pagamento' => null,
        ]);
        return back()->with('success', 'Recusa cancelada. Pagamento retornou para pendentes');
    }
}
