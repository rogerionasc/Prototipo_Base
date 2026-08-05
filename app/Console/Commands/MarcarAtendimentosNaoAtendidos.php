<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Atendimento;
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
    protected $description = 'Altera para "NÃO ATENDIDO" o status dos atendimentos com data anterior à atual e que ainda constam como AGUARDANDO';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hoje = Carbon::today()->toDateString();

        $atendimentos = Atendimento::whereDate('data_atendimento', '<', $hoje)
            ->where('status', 'NÃO ATENDIDO')
            ->get();

        $statusReagendado = \App\Models\StatusAgendamento::firstOrCreate(['descricao' => 'Reagendado']);

        $count = 0;
        foreach ($atendimentos as $atendimento) {
            $atendimento->update(['status' => 'NÃO ATENDIDO']);
            
            if ($atendimento->agendamento_id) {
                \App\Models\Agendamento::where('id', $atendimento->agendamento_id)
                    ->update(['status_id' => $statusReagendado->id]);
            }
            $count++;
        }

        $this->info("{$count} atendimentos foram marcados como NÃO ATENDIDO e seus agendamentos como Reagendado.");
    }
}
