<?php

namespace App\Http\Controllers\Financeiro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CategoriaDespesa;
use App\Models\Financeiro\ContaPagar;
use App\Models\Financeiro\Despesa;
use App\Models\ConfiguracaoBancaria;
use Illuminate\Support\Facades\Storage;

class ContasPagarController extends Controller
{
    public function index(Request $request)
    {
        $categoriasFinanceiras = CategoriaDespesa::select('id', 'descricao')->orderBy('descricao')->get();
        $configuracoesBancarias = ConfiguracaoBancaria::select('id', 'provedor', 'tipo')->get();

        $query = ContaPagar::query()->from('conta_pagars as cp')
            ->leftJoin('categoria_despesas as cd', 'cp.categoria_id', '=', 'cd.id')
            ->leftJoin('configuracoes_bancarias as cb', 'cp.configuracao_bancaria_id', '=', 'cb.id')
            ->leftJoin('despesas as d', 'cp.despesa_id', '=', 'd.id')
            ->select(
                'cp.id',
                'cp.despesa_id',
                'cp.descricao',
                'cp.valor',
                'cp.valor_pago',
                'cp.status',
                'cp.data_vencimento',
                'cp.data_competencia',
                'cp.data_pagamento',
                'cp.categoria_id',
                'cd.descricao as categoria_nome',
                'cp.configuracao_bancaria_id',
                'cb.provedor as banco_nome',
                'cp.anexo',
                'd.anexo as despesa_anexo',
                'd.is_recorrente'
            );

        // Filtros (q)
        $q = $request->get('q', '');
        if (!empty($q)) {
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->where('cp.descricao', 'like', "%{$q}%")
                         ->orWhere('cd.descricao', 'like', "%{$q}%");
            });
        }

        // FilterType (PENDENTE, SEMANAL, MENSAL, ANUAL)
        $filterType = $request->get('filterType', 'PENDENTE');
        if ($filterType === 'PENDENTE') {
            $query->whereIn('cp.status', ['Pendente', 'Vencida']);
        } elseif ($filterType === 'SEMANAL') {
            $startOfWeek = now()->startOfWeek()->format('Y-m-d H:i:s');
            $endOfWeek = now()->endOfWeek()->format('Y-m-d H:i:s');
            $query->where('cp.status', 'Paga')
                  ->whereNotNull('cp.data_pagamento')
                  ->whereBetween('cp.data_pagamento', [$startOfWeek, $endOfWeek]);
        } elseif ($filterType === 'MENSAL') {
            $startOfMonth = now()->startOfMonth()->format('Y-m-d H:i:s');
            $endOfMonth = now()->endOfMonth()->format('Y-m-d H:i:s');
            $query->where('cp.status', 'Paga')
                  ->whereNotNull('cp.data_pagamento')
                  ->whereBetween('cp.data_pagamento', [$startOfMonth, $endOfMonth]);
        } elseif ($filterType === 'ANUAL') {
            $startOfYear = now()->startOfYear()->format('Y-m-d H:i:s');
            $endOfYear = now()->endOfYear()->format('Y-m-d H:i:s');
            $query->where('cp.status', 'Paga')
                  ->whereNotNull('cp.data_pagamento')
                  ->whereBetween('cp.data_pagamento', [$startOfYear, $endOfYear]);
        }

        if ($request->get('is_api') == 1) {
            $limit = (int) $request->get('limit', 10);
            $offset = (int) $request->get('offset', 0);

            $total = $query->count();
            $data = $query->orderBy('cp.data_vencimento')
                          ->offset($offset)
                          ->limit($limit)
                          ->get();

            return response()->json([
                'data' => $data,
                'total' => $total
            ]);
        }

        // KPIs (Calculados via Inertia)
        $todayStr = now()->format('Y-m-d');
        $baseKpiQuery = ContaPagar::query()->from('conta_pagars as cp');

        // Por padrão (PENDENTE) os cards retornam do mês, como solicitado.
        $kpiFilterType = $filterType === 'PENDENTE' ? 'MENSAL' : $filterType;

        $startDate = null;
        $endDate = null;
        
        if ($kpiFilterType === 'SEMANAL') {
            $startDate = now()->startOfWeek()->format('Y-m-d');
            $endDate = now()->endOfWeek()->format('Y-m-d');
        } elseif ($kpiFilterType === 'MENSAL') {
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d');
        } elseif ($kpiFilterType === 'ANUAL') {
            $startDate = now()->startOfYear()->format('Y-m-d');
            $endDate = now()->endOfYear()->format('Y-m-d');
        }

        // Helper para aplicar filtro de data
        $applyDateFilter = function ($q, $column) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $q->whereBetween($column, [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }
        };

        $qPendente = (clone $baseKpiQuery)->whereIn('status', ['Pendente', 'Vencida']);
        $applyDateFilter($qPendente, 'data_vencimento');
        $totalPendente = $qPendente->sum('valor');

        $qVencido = (clone $baseKpiQuery)->whereIn('status', ['Pendente', 'Vencida'])->whereDate('data_vencimento', '<', $todayStr);
        $applyDateFilter($qVencido, 'data_vencimento');
        $totalVencido = $qVencido->sum('valor');

        $qAVencer = (clone $baseKpiQuery)->where('status', 'Pendente')->where(function($q) use ($todayStr) {
            $q->whereNull('data_vencimento')->orWhereDate('data_vencimento', '>=', $todayStr);
        });
        $applyDateFilter($qAVencer, 'data_vencimento');
        $totalAVencer = $qAVencer->sum('valor');

        $qPago = (clone $baseKpiQuery)->where('status', 'Paga');
        $applyDateFilter($qPago, 'data_pagamento');
        $totalPago = $qPago->sum('valor_pago');

        return \Inertia\Inertia::render('Financeiro/ContasPagar/Index', [
            'categoriasFinanceiras' => $categoriasFinanceiras,
            'configuracoesBancarias' => $configuracoesBancarias,
            'kpis' => [
                'totalPendente' => (float)$totalPendente,
                'totalVencido' => (float)$totalVencido,
                'totalAVencer' => (float)$totalAVencer,
                'totalPago' => (float)$totalPago,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'categoria_id' => 'required|exists:categoria_despesas,id',
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0',
            'data_competencia' => 'nullable|date',
            'data_vencimento' => 'nullable|date',
            'is_recorrente' => 'boolean',
            'files.*' => 'nullable|file|max:5120',
        ]);

        $anexoPath = null;
        if ($request->hasFile('files')) {
            $file = $request->file('files')[0];
            $anexoPath = $file->store('anexos_financeiro', 'public');
        }

        $despesa = Despesa::create([
            'account_id' => auth()->user()->account_id,
            'descricao' => $data['descricao'],
            'valor' => $data['valor'],
            'categoria_id' => $data['categoria_id'],
            'data_competencia' => $data['data_competencia'] ?? null,
            'data_vencimento' => $data['data_vencimento'] ?? null,
            'anexo' => $anexoPath,
            'status' => 'Pendente',
            'is_recorrente' => $data['is_recorrente'] ?? false,
        ]);

        ContaPagar::create([
            'account_id' => auth()->user()->account_id,
            'despesa_id' => $despesa->id,
            'descricao' => $data['descricao'],
            'valor' => $data['valor'],
            'categoria_id' => $data['categoria_id'],
            'data_competencia' => $data['data_competencia'] ?? null,
            'data_vencimento' => $data['data_vencimento'] ?? null,
            'anexo' => $anexoPath,
            'status' => 'Pendente',
        ]);

        return back()->with('success', 'Despesa salva e conta a pagar gerada com sucesso.');
    }

    public function update(Request $request, $id)
    {
        $conta = ContaPagar::findOrFail($id);

        if ($conta->status === 'Paga') {
            return back()->with('error', 'Esta conta já está paga e não pode ser editada.');
        }

        $data = $request->validate([
            'categoria_id' => 'required|exists:categoria_despesas,id',
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0',
            'data_competencia' => 'nullable|date',
            'data_vencimento' => 'nullable|date',
            'status' => 'required|string',
            'is_recorrente' => 'boolean',
            'files.*' => 'nullable|file|max:5120',
        ]);

        $anexoPath = $conta->anexo;
        if ($request->hasFile('files')) {
            $file = $request->file('files')[0];
            $anexoPath = $file->store('anexos_financeiro', 'public');
        }

        $conta->update([
            'categoria_id' => $data['categoria_id'],
            'descricao' => $data['descricao'],
            'valor' => $data['valor'],
            'data_competencia' => $data['data_competencia'] ?? null,
            'data_vencimento' => $data['data_vencimento'] ?? null,
            'status' => $data['status'],
            'anexo' => $anexoPath
        ]);

        if ($conta->despesa_id) {
            Despesa::where('id', $conta->despesa_id)->update([
                'is_recorrente' => $data['is_recorrente'] ?? false
            ]);
        }

        return back()->with('success', 'Conta a pagar atualizada.');
    }

    public function destroy($id)
    {
        try {
            $conta = ContaPagar::findOrFail($id);
            $despesaId = $conta->despesa_id;
            
            $conta->delete();
            
            if ($despesaId) {
                $outrasContas = ContaPagar::where('despesa_id', $despesaId)->count();
                if ($outrasContas === 0) {
                    Despesa::where('id', $despesaId)->delete();
                }
            }
            
            return redirect()->back()->with('success', 'Conta cancelada com sucesso.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao cancelar a conta.');
        }
    }

    public function pagar(Request $request, $id)
    {
        $request->validate([
            'data_pagamento' => 'required|date',
            'conta_bancaria_id' => 'required|exists:configuracoes_bancarias,id',
            'valor_pago' => 'required|numeric|min:0',
            'files.*' => 'nullable|file|max:5120',
        ]);

        $conta = ContaPagar::findOrFail($id);

        if ($conta->status === 'Paga') {
            return back()->with('error', 'Esta conta já encontra-se paga.');
        }
        
        $anexoPath = $conta->anexo;
        if ($request->hasFile('files')) {
            $file = $request->file('files')[0];
            $anexoPath = $file->store('anexos_financeiro', 'public');
        }

        $dataPagamento = $request->data_pagamento;
        if (strpos($dataPagamento, ':') === false) {
            $dataPagamento .= ' ' . now()->format('H:i:s');
        }

        $conta->update([
            'status' => 'Paga',
            'data_pagamento' => $dataPagamento,
            'configuracao_bancaria_id' => $request->conta_bancaria_id,
            'valor_pago' => $request->valor_pago,
            'anexo' => $anexoPath
        ]);

        if ($conta->despesa_id) {
            $despesa = Despesa::find($conta->despesa_id);
            if ($despesa) {
                if ($despesa->is_recorrente) {
                    $vencimentoOriginal = \Carbon\Carbon::parse($conta->data_vencimento);
                    $novoVencimento = $vencimentoOriginal->copy()->addMonth()->format('Y-m-d');
                    
                    $novaCompetencia = null;
                    if ($conta->data_competencia) {
                        $novaCompetencia = \Carbon\Carbon::parse($conta->data_competencia)->copy()->addMonth()->format('Y-m-d');
                    }

                    ContaPagar::create([
                        'account_id' => $conta->account_id,
                        'despesa_id' => $despesa->id,
                        'descricao' => $conta->descricao,
                        'valor' => $conta->valor,
                        'categoria_id' => $conta->categoria_id,
                        'data_competencia' => $novaCompetencia,
                        'data_vencimento' => $novoVencimento,
                        'anexo' => null,
                        'status' => 'Pendente',
                    ]);

                    $despesa->update(['status' => 'Pendente']);
                } else {
                    $despesa->update(['status' => 'Paga']);
                }
            }
        }

        return redirect()->back()->with('success', 'Pagamento registrado com sucesso.');
    }
}
