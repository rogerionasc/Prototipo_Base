<?php

use Illuminate\Support\Facades\Auth;
use App\Models\Convenio;
use App\Http\Controllers\AgendamentoController;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

Auth::loginUsingId(1);
$request = request();
try {
    $json = app(AgendamentoController::class)->latest($request)->content();
    print_r(json_decode($json, true));
} catch (\Exception $e) {
    echo $e->getMessage();
}
