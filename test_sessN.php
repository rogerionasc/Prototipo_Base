<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ag = \App\Models\Agendamento::find(9);
$sessN = $ag->sessaoTratamento ? $ag->sessaoTratamento->numero_sessao : null;
$sessT = $ag->procedimento ? $ag->procedimento->quantidade_sessoes : ($ag->tuss ? $ag->tuss->quantidade_sessoes : null);
if ($sessT > 1 && $sessN === null) {
    $sessN = 1;
}

echo "Agendamento 9:\n";
echo "sessao_tratamento_id = " . $ag->sessao_tratamento_id . "\n";
echo "sessN evaluated = " . var_export($sessN, true) . "\n";
