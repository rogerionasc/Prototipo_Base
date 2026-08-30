<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/convenios/5/tuss-procedimentos', 'GET');
$user = App\Models\User::find(1);
$app->make('auth')->login($user);
$response = $kernel->handle($request);
echo $response->getContent();
