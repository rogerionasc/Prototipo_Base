<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\Pagamento::whereNull('nu_pagamento')->get()->each(function($p) {
    $p->nu_pagamento = \Carbon\Carbon::parse($p->created_at)->format('dmYHi') . str_pad($p->id, 4, '0', STR_PAD_LEFT);
    $p->saveQuietly();
});

echo "Fixed pagamentos\n";
