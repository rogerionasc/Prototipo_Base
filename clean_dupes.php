<?php

$dir = __DIR__ . '/database/migrations/';
$files = glob($dir . '*.php');

foreach($files as $f) {
    $c = file_get_contents($f);
    // If we find multiple occurrences of account_id in the same file
    $count = substr_count($c, "\$table->foreignId('account_id')");
    if ($count > 1) {
        // Replace all but one
        $c = preg_replace("/\\s*\\\$table->foreignId\('account_id'\)->nullable\(\)->constrained\('accounts'\)->nullOnDelete\(\);/", "", $c, $count - 1);
        file_put_contents($f, $c);
        echo "Cleaned duplicate account_id in " . basename($f) . "\n";
    }
}
echo "Done cleaning.\n";
