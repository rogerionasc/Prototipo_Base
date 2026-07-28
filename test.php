<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cr = DB::table('contas_receber as cr')
    ->select(
        'cr.id',
        DB::raw("(SELECT DATE_FORMAT(MAX(p.data_pagamento), '%d-%m-%Y') FROM pagamentos p WHERE p.faturamento_id = cr.faturamento_id AND p.status = 'PAGO') as data_pagamento")
    )->get();
print_r($cr);
