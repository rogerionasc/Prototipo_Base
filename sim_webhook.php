<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cobranca = \DB::table('cobrancas')->where('conta_receber_id', 1000)->first();
if ($cobranca) {
    $response = \Illuminate\Support\Facades\Http::post('http://127.0.0.1:8000/api/webhooks/cobrancas/asaas', [
        'event' => 'PAYMENT_RECEIVED',
        'payment' => [
            'id' => $cobranca->gateway_id
        ]
    ]);
    echo 'Webhook simulated! Status: ' . $response->status();
} else {
    echo 'Cobranca not found for conta_receber_id = 1000!';
}
