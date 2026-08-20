<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$t = Illuminate\Support\Facades\DB::table('tuss')->where('codigo', '15010023')->first();
echo $t->descricao;
