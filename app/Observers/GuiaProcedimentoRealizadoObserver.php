<?php

namespace App\Observers;

use App\Models\GuiaProcedimentoRealizado;

class GuiaProcedimentoRealizadoObserver
{
    /**
     * Handle the GuiaProcedimentoRealizado "created" event.
     */
    public function created(GuiaProcedimentoRealizado $guiaProcedimentoRealizado): void
    {
        $guia = $guiaProcedimentoRealizado->guia()->with('agendamento.convenio')->first();
        
        \Illuminate\Support\Facades\Log::info("GuiaProcedimentoRealizadoObserver::created triggered", [
            'guia_id' => $guia->id ?? null,
            'data_vencimento_faturar' => $guia->data_vencimento_faturar ?? null,
            'data_realizacao' => $guiaProcedimentoRealizado->data_realizacao
        ]);

        if ($guia && !$guia->data_vencimento_faturar && $guiaProcedimentoRealizado->data_realizacao) {
            $diasParaFaturar = $guia->agendamento?->convenio?->dias_para_faturar ?? 30;
            $dataExecucao = \Carbon\Carbon::parse($guiaProcedimentoRealizado->data_realizacao);
            $dataVencimento = $dataExecucao->copy()->addDays($diasParaFaturar);
            
            $guia->update([
                'data_vencimento_faturar' => $dataVencimento->format('Y-m-d')
            ]);
        }
    }

    /**
     * Handle the GuiaProcedimentoRealizado "updated" event.
     */
    public function updated(GuiaProcedimentoRealizado $guiaProcedimentoRealizado): void
    {
        $guia = $guiaProcedimentoRealizado->guia()->with('agendamento.convenio')->first();
        
        \Illuminate\Support\Facades\Log::info("GuiaProcedimentoRealizadoObserver::updated triggered", [
            'guia_id' => $guia->id ?? null,
            'data_vencimento_faturar' => $guia->data_vencimento_faturar ?? null,
            'data_realizacao' => $guiaProcedimentoRealizado->data_realizacao
        ]);

        // Se a guia ainda não tem data_vencimento_faturar (ex: esqueleto criado antes sem data)
        if ($guia && !$guia->data_vencimento_faturar && $guiaProcedimentoRealizado->data_realizacao) {
            $diasParaFaturar = $guia->agendamento?->convenio?->dias_para_faturar ?? 30;
            $dataExecucao = \Carbon\Carbon::parse($guiaProcedimentoRealizado->data_realizacao);
            $dataVencimento = $dataExecucao->copy()->addDays($diasParaFaturar);
            
            $guia->update([
                'data_vencimento_faturar' => $dataVencimento->format('Y-m-d')
            ]);
        }
    }

    /**
     * Handle the GuiaProcedimentoRealizado "deleted" event.
     */
    public function deleted(GuiaProcedimentoRealizado $guiaProcedimentoRealizado): void
    {
        //
    }

    /**
     * Handle the GuiaProcedimentoRealizado "restored" event.
     */
    public function restored(GuiaProcedimentoRealizado $guiaProcedimentoRealizado): void
    {
        //
    }

    /**
     * Handle the GuiaProcedimentoRealizado "force deleted" event.
     */
    public function forceDeleted(GuiaProcedimentoRealizado $guiaProcedimentoRealizado): void
    {
        //
    }
}
