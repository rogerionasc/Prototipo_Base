<?php
$f = 'app/Http/Controllers/ProcedimentoController.php';
$c = file_get_contents($f);
$c = str_replace("'ch'", "'quantidade_ch'", $c);
$c = str_replace("'co'", "'quantidade_co'", $c);
file_put_contents($f, $c);
echo "Done";
