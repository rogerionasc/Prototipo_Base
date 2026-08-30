<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$id = 9;
$agendamento = \App\Models\Agendamento::with(['agendaMedica', 'sessaoTratamento'])->findOrFail($id);

$sessN = $agendamento->sessaoTratamento ? $agendamento->sessaoTratamento->numero_sessao : null;
$sessT = $agendamento->procedimento ? $agendamento->procedimento->quantidade_sessoes : ($agendamento->tuss ? $agendamento->tuss->quantidade_sessoes : null);
if ($sessT > 1 && $sessN === null) {
    $sessN = 1;
}
echo "For Agendamento 9:\n";
echo "sessN computed: " . var_export($sessN, true) . "\n";
echo "sessT computed: " . var_export($sessT, true) . "\n";
echo "sessaoTratamento loaded? " . ($agendamento->relationLoaded('sessaoTratamento') ? 'yes' : 'no') . "\n";
