<?php

function fixFile($path) {
    $c = file_get_contents($path);
    // Fix array headers
    $c = str_replace("'quantidade_co', 'total'", "'quantidade_co'", $c);
    // Fix updateCols
    $c = preg_replace("/'quantidade_co',\s*'total'/", "'quantidade_co'", $c);
    // Fix select
    $c = str_replace("'descricao', 'total'", "'descricao'", $c);
    
    // Remove 'total' => $total,
    $c = preg_replace("/'total'\s*=>\s*\\\$total,/", "", $c);
    
    file_put_contents($path, $c);
}

fixFile('app/Http/Controllers/ProcedimentoController.php');
fixFile('app/Jobs/ImportTussCsvJob.php');
echo "Done";
