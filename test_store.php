<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simulate request
\Auth::loginUsingId(1); // Login as user 1

$request = \Illuminate\Http\Request::create('/recepcao/agendamentos', 'POST', [
    'paciente_id' => 1,
    'pessoa_id' => '129',
    'procedimento_id' => '2527',
    'convenio_id' => 4,
    'status_id' => null,
    'valor_cobrado' => 240,
    'observacoes' => null,
    'is_retorno' => false,
    'numero_autorizacao' => null,
    'validade_autorizacao' => null,
    'procedimentosAdicionais' => [
        ['procedimento_id' => '2527', 'data' => '2026-08-20', 'hora' => '12:00', 'pessoa_id' => '129', 'valor_cobrado' => '240.00'],
        ['procedimento_id' => '2527', 'data' => '2026-08-20', 'hora' => '12:00', 'pessoa_id' => '129', 'valor_cobrado' => '240.00']
    ],
    'data' => '2026-08-20',
    'hora' => '12:00'
]);
$request->headers->set('Accept', 'application/json');
$request->setLaravelSession($app['session']->driver());

try {
    $controller = new \App\Http\Controllers\AgendamentoController();
    $response = $controller->store($request);
    
    echo "Response: " . get_class($response) . "\n";
    print_r(app('session.store')->all());
} catch (\Exception $e) {
    if ($e instanceof \Illuminate\Validation\ValidationException) {
        echo "Validation errors:\n";
        print_r($e->errors());
    } else {
        echo "Exception: " . $e->getMessage() . "\n";
    }
}
