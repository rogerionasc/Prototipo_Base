<?php
require 'c:/laragon/www/Prototipo_Base/Prototipo_Base/vendor/autoload.php';
$app = require_once 'c:/laragon/www/Prototipo_Base/Prototipo_Base/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$pendIds = DB::table('pagamentos as pg')
    ->select(DB::raw('MAX(pg.id) as id'), 'pg.faturamento_id')
    ->where('pg.status', 'PENDENTE')
    ->groupBy('pg.faturamento_id');

$rows = DB::table('faturamentos as f')
    ->leftJoin('orcamentos as o', 'o.id', '=', 'f.orcamento_id')
    ->leftJoin('pacientes as pa', 'pa.id', '=', 'f.paciente_id')
    ->leftJoin('contas_receber as cr', 'cr.faturamento_id', '=', 'f.id')
    ->leftJoinSub($pendIds, 'pp', function ($join) {
        $join->on('pp.faturamento_id', '=', 'f.id');
    })
    ->leftJoin('pagamentos as p', 'p.id', '=', 'pp.id')
    ->select('f.id as fat_id', 'p.id as pag_id', 'f.status as fat_status', 'p.status as pag_status')
    ->where('f.tipo_pagador', 'PARTICULAR')
    ->where('f.status', 'AGUARDANDO_PAGAMENTO')
    ->where('o.aprovado', true)
    ->get();

foreach($rows as $r) {
    echo 'Fat: ' . $r->fat_id . ' Pag: ' . ($r->pag_id ?? 'NULL') . PHP_EOL;
}
