<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$convenios = App\Models\Convenio::where('config_spsadt', 'like', '%59_valor_total_honorarios%')->get();
foreach($convenios as $c) {
    $json = json_encode($c->config_spsadt);
    $json = str_replace('59_valor_total_honorarios', '59_valor_total_procedimentos', $json);
    $c->config_spsadt = json_decode($json, true);
    $c->save();
    echo 'Updated Convenio ' . $c->id . PHP_EOL;
}
echo "Done";
