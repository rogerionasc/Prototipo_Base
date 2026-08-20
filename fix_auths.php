<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$auts = \App\Models\Autorizacao::whereNull('procedimento_solicitado_id')->orderBy('id', 'desc')->take(3)->get();
foreach ($auts as $index => $aut) {
    if ($aut->guia_id) {
        // Try to find the corresponding procedimento solicitado for this authorization
        // Assuming they were created in order and we can map them by created_at or just sequentially
        $procs = \App\Models\GuiaProcedimentoSolicitado::where('guia_id', $aut->guia_id)->orderBy('id', 'desc')->get();
        if ($procs->count() >= 3) {
            $aut->procedimento_solicitado_id = $procs[$index]->id;
            $aut->save();
            echo "Updated Auth ID {$aut->id} to Proc Solicitado ID {$procs[$index]->id}\n";
        }
    }
}
