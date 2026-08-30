<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = \Illuminate\Http\Request::create('/recepcao/fila', 'GET', ['data' => '2026-08-30']);
$controller = new \App\Http\Controllers\RecepcaoFilaController();
$response = $controller->index($request);
if ($response instanceof \Inertia\Response) {
    $props = $response->toResponse($request)->getOriginalContent()->getData()['page']['props'];
    $ags = $props['agendamentos'];
    foreach ($ags as $ag) {
        echo "Agendamento: " . $ag['procedimento'] . "\n";
    }
}
