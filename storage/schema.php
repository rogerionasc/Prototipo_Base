<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = Schema::getColumnListing('guias');
foreach($cols as $c) {
    echo $c . ':' . Schema::getColumnType('guias', $c) . PHP_EOL;
}
