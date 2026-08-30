<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ag = \App\Models\Agendamento::find(9);
$sessaoNumero = null;
if ($ag->sessao_tratamento_id) {
    $sessaoNumero = \Illuminate\Support\Facades\DB::table('sessoes_tratamento')->where('id', $ag->sessao_tratamento_id)->value('numero_sessao');
}

if ($sessaoNumero === null) {
    $sessT = null;
    if ($ag->procedimento_id) {
        $sessT = \Illuminate\Support\Facades\DB::table('procedimentos')->where('id', $ag->procedimento_id)->value('quantidade_sessoes');
    } elseif ($ag->tuss_id) {
        $sessT = \Illuminate\Support\Facades\DB::table('tuss')->where('id', $ag->tuss_id)->value('quantidade_sessoes');
    }
    if ($sessT > 1) {
        $sessaoNumero = 1;
    }
}

echo "PagamentoController For Agendamento 9:\n";
echo "sessaoNumero computed: " . var_export($sessaoNumero, true) . "\n";
