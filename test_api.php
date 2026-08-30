<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/procedimentos/tuss?tabela=AMB1992&limit=5', 'GET');
$controller = app()->make(App\Http\Controllers\ProcedimentoController::class);
$response = $controller->tussProcedimentosByTabela($request, 'AMB1992');
echo $response->getContent();
