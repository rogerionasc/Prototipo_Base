<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Guia;
use Illuminate\Support\Facades\DB;

class ContasMedicasController extends Controller
{
    public function getGuiasParaValidacaoApi(Request $request)
    {
        $query = Guia::with([
            'agendamento.paciente',
            'agendamento.convenio',
            'agendamento.agendaMedica.profissionalSaude',
            'procedimentosRealizados'
        ])
            ->whereNull('faturamento_id')
            ->whereNotIn('status', ['PRONTA_FATURAMENTO', 'ENVIADA_FATURAMENTO', 'FATURADA', 'GLOSADA', 'CANCELADA'])
            ->orderBy('id', 'desc');

        // Busca textual
        $search = $request->input('search', $request->input('q', ''));
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('numero_guia_prestador', 'like', "%{$search}%")
                  ->orWhere('numero_guia_operadora', 'like', "%{$search}%")
                  ->orWhere('beneficiario_nome', 'like', "%{$search}%")
                  ->orWhere('tipo', 'like', "%{$search}%")
                  ->orWhereHas('agendamento.paciente', function ($q2) use ($search) {
                      $q2->where('nome', 'like', "%{$search}%");
                  })
                  ->orWhereHas('agendamento.convenio', function ($q2) use ($search) {
                      $q2->where('descricao', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por convênio
        $convenio = $request->input('convenio', '');
        if ($convenio) {
            $query->whereHas('agendamento.convenio', function ($q) use ($convenio) {
                $q->where('descricao', $convenio);
            });
        }

        // Filtro por status (vencidas, devolvidas, validadas, pendentes)
        $statusFilter = $request->input('status_filter', '');
        if ($statusFilter) {
            $sf = strtoupper($statusFilter);
            if ($sf === 'DEVOLVIDA' || $sf === 'VALIDADA') {
                $query->where('status', $sf);
            }
            // VENCIDAS e PENDENTES serão filtrados após o cálculo (post-query)
        }

        $total = $query->count();

        // Paginação
        $limit = (int) $request->input('limit', $request->input('per_page', 10));
        $offset = (int) $request->input('offset', 0);
        $guias = $query->skip($offset)->take($limit)->get();

        $guiasData = $guias->map(function ($guia) {
            $dataExecucaoMaisAntiga = null;
            if ($guia->procedimentosRealizados->isNotEmpty()) {
                $primeiroProcedimento = $guia->procedimentosRealizados->sortBy('id')->first();
                if ($primeiroProcedimento && $primeiroProcedimento->data_realizacao) {
                    $dataExecucaoMaisAntiga = \Carbon\Carbon::parse($primeiroProcedimento->data_realizacao);
                }
            }

            if ($guia->data_vencimento_faturar) {
                $dataLimite = \Carbon\Carbon::parse($guia->data_vencimento_faturar);
                $diasVencer = (int) ceil(\Carbon\Carbon::now()->floatDiffInDays($dataLimite, false));
                $isVencida = $diasVencer < 0;
            } else {
                $dataLimite = null;
                $diasVencer = null;
                $isVencida = false;
            }

            return [
                'id' => $guia->id,
                'data_criacao' => $guia->created_at ? $guia->created_at->format('d/m/Y H:i') : '-',
                'data_solicitacao' => $guia->data_solicitacao ?? $guia->created_at?->format('Y-m-d'),
                'paciente_nome' => $guia->agendamento?->paciente?->nome ?? $guia->beneficiario_nome,
                'convenio_nome' => $guia->agendamento?->convenio?->descricao ?? 'Particular',
                'numero_guia_operadora' => $guia->numero_guia_operadora,
                'numero_guia_prestador' => $guia->numero_guia_prestador,
                'tipo' => $guia->tipo ?? 'Guia de Consulta',
                'medico_nome' => $guia->agendamento?->agendaMedica?->profissionalSaude?->nome ?? $guia->profissional_solicitante_nome,
                'status' => $guia->status ?: 'CRIADA',
                'agendamento_id' => $guia->agendamento_id,
                'is_vencida' => $isVencida,
                'dias_vencer' => $diasVencer,
                'data_execucao' => $dataExecucaoMaisAntiga ? $dataExecucaoMaisAntiga->format('d/m/Y') : null,
                'data_limite_faturamento' => $dataLimite ? $dataLimite->format('d/m/Y') : null,
            ];
        });

        return response()->json([
            'data' => $guiasData->values(),
            'total' => $total,
        ]);
    }

    public function validacaoGuias()
    {
        return Inertia::render('ContasMedicas/ValidacaoGuias/Index');
    }

    public function getContadoresApi()
    {
        $guias = Guia::with(['agendamento.convenio', 'procedimentosRealizados'])
            ->whereNull('faturamento_id')
            ->get();

        $vencidas = 0;
        $devolvidas = 0;
        $validadas = 0;
        $pendentes = 0;
        $proximoVencer = 0;

        foreach ($guias as $guia) {
            $status = strtoupper($guia->status ?: 'CRIADA');

            if ($status === 'DEVOLVIDA') {
                $devolvidas++;
                continue;
            }
            if ($status === 'VALIDADA') {
                $validadas++;
                continue;
            }
            if ($status === 'PRONTA_FATURAMENTO') {
                continue;
            }

            // Calcular vencimento usando a nova coluna
            if ($guia->data_vencimento_faturar) {
                $dataLimite = \Carbon\Carbon::parse($guia->data_vencimento_faturar);
                $diasVencer = (int) ceil(\Carbon\Carbon::now()->floatDiffInDays($dataLimite, false));
                
                if ($diasVencer < 0) {
                    $vencidas++;
                } elseif ($diasVencer <= 5) {
                    $proximoVencer++;
                } else {
                    $pendentes++;
                }
            } else {
                $pendentes++;
            }
        }

        return response()->json([
            'vencidas' => $vencidas,
            'devolvidas' => $devolvidas,
            'validadas' => $validadas,
            'pendentes' => $pendentes,
            'proximo_vencer' => $proximoVencer,
        ]);
    }

    public function encaminharFaturamento(Request $request)
    {
        $data = $request->validate([
            'guias' => 'required|array',
            'guias.*' => 'exists:guias,id'
        ]);

        $guiasNaoValidadas = Guia::whereIn('id', $data['guias'])->where('status', '!=', 'VALIDADA')->count();
        if ($guiasNaoValidadas > 0) {
            return back()->with('error', 'Apenas guias com status VALIDADA podem ser encaminhadas para o faturamento.');
        }

        Guia::whereIn('id', $data['guias'])->update([
            'status' => 'PRONTA_FATURAMENTO'
        ]);

        return back()->with('success', 'Guias encaminhadas para o faturamento com sucesso!');
    }
}
