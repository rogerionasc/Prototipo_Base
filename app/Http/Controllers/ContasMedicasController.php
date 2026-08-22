<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Guia;
use Illuminate\Support\Facades\DB;

class ContasMedicasController extends Controller
{
    public function validacaoGuias()
    {
        // 1. Fetch Guias where faturamento_id is null
        $guias = Guia::with([
            'agendamento.paciente',
            'agendamento.convenio',
            'agendamento.agendaMedica.profissionalSaude'
        ])
            ->whereNull('faturamento_id')
            ->orderBy('id', 'desc')
            ->get();

        $guiasData = $guias->map(function ($guia) {
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
                'status' => $guia->status ?: 'CRIADA', // Default to CRIADA se null
                'agendamento_id' => $guia->agendamento_id,
            ];
        });

        return Inertia::render('ContasMedicas/ValidacaoGuias/Index', [
            'guias' => $guiasData
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
