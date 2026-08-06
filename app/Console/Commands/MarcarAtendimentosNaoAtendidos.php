<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Atendimento;
use App\Models\Agendamento;
use App\Models\StatusAgendamento;
use Carbon\Carbon;

class MarcarAtendimentosNaoAtendidos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atendimentos:marcar-nao-atendidos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Altera para "Reagendar" o status dos agendamentos expirados e "NÃO ATENDIDO" o status dos atendimentos pendentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hoje = Carbon::today()->toDateString();

        $statusReagendar = StatusAgendamento::firstOrCreate(['descricao' => 'Reagendar']);

        // Statuses do Agendamento considerados finalizados
        $statusIgnoradosIds = StatusAgendamento::whereIn('descricao', [
            'Atendido', 'ATENDIDO', 'Cancelado', 'CANCELADO', 'Reagendar', 'REAGENDAR'
        ])->pluck('id');

        // 1. Processa Agendamentos com data anterior a hoje que não foram finalizados
        $agendamentos = Agendamento::whereDate('data', '<', $hoje)
            ->whereNotIn('status_id', $statusIgnoradosIds)
            ->get();

        $countAgendamentos = 0;
        foreach ($agendamentos as $agendamento) {
            $agendamento->update(['status_id' => $statusReagendar->id]);
            
            // Marca atendimentos vinculados como NÃO ATENDIDO
            Atendimento::where('agendamento_id', $agendamento->id)
                ->whereNotIn('status', ['ATENDIDO', 'CANCELADO', 'NÃO ATENDIDO'])
                ->update(['status' => 'NÃO ATENDIDO']);

            $countAgendamentos++;
        }

        // 2. Processa Atendimentos com data_atendimento anterior a hoje que ainda constam como pendentes
        $atendimentosPendentes = Atendimento::whereDate('data_atendimento', '<', $hoje)
            ->whereNotIn('status', ['ATENDIDO', 'CANCELADO', 'NÃO ATENDIDO'])
            ->get();

        $countAtendimentos = 0;
        foreach ($atendimentosPendentes as $atendimento) {
            $atendimento->update(['status' => 'NÃO ATENDIDO']);
            
            if ($atendimento->agendamento_id) {
                Agendamento::where('id', $atendimento->agendamento_id)
                    ->update(['status_id' => $statusReagendar->id]);
            }
            $countAtendimentos++;
        }

        $this->info("{$countAgendamentos} agendamento(s) alterados para Reagendar e {$countAtendimentos} atendimento(s) alterados para NÃO ATENDIDO.");
    }
}
