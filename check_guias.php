<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = Illuminate\Http\Request::create('/faturamentos/guias-disponiveis', 'GET', ['convenio_id' => 4]);
$res = app()->handle($req);
echo $res->getContent();
