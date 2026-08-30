<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$atendimentos = \App\Models\Atendimento::whereIn('agendamento_id', [8, 9, 10])->get();
foreach ($atendimentos as $atendimento) {
    $ag = $atendimento->agendamento;
    if ($ag && $ag->sessao_tratamento_id) {
        $sessaoNumero = \Illuminate\Support\Facades\DB::table('sessoes_tratamento')->where('id', $ag->sessao_tratamento_id)->value('numero_sessao');
        if ($sessaoNumero) {
            $atendimento->update(['sessao' => $sessaoNumero]);
            echo "Updated Atendimento {$atendimento->id} to sessao {$sessaoNumero}\n";
        }
    }
}
