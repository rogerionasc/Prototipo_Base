<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ag = \App\Models\Agendamento::find(9);
echo "Agendamento 9: sessao_tratamento_id = " . $ag->sessao_tratamento_id . "\n";
echo "Lazy load sessaoTratamento: " . ($ag->sessaoTratamento ? 'FOUND' : 'NULL') . "\n";
if ($ag->sessaoTratamento) {
    echo "Numero sessao: " . $ag->sessaoTratamento->numero_sessao . "\n";
}

echo "Testing index controller behavior...\n";
$ags = \App\Models\Agendamento::with(['procedimento', 'tuss'])->where('id', 9)->get();
foreach ($ags as $ag2) {
    $sessN = $ag2->sessaoTratamento ? $ag2->sessaoTratamento->numero_sessao : null;
    $sessT = $ag2->procedimento ? $ag2->procedimento->quantidade_sessoes : ($ag2->tuss ? $ag2->tuss->quantidade_sessoes : null);
    if ($sessT > 1 && $sessN === null) {
        $sessN = 1;
    }
    echo "Agendamento 9 index simulation: sessN = " . var_export($sessN, true) . "\n";
}
