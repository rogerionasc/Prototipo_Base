<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = app('App\Http\Controllers\GuiaController')->getDadosDaAgenda(39);
file_put_contents('response.json', $r->getContent());
echo "Done\n";
