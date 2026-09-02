<?php
$file = 'c:\laragon\www\Prototipo_Base\Prototipo_Base\app\Http\Controllers\AgendamentoController.php';
$content = file_get_contents($file);

// Fix latest()
$content = preg_replace(
    "/->leftJoin\('tuss as t', 't.id', '=', 'a.tuss_id'\)/",
    "->leftJoin('tuss as t', 't.id', '=', 'a.tuss_id')\n            ->leftJoin('convenio_tuss as ct', function(\$join) {\n                \$join->on('ct.tuss_id', '=', 'a.tuss_id')\n                     ->on('ct.convenio_id', '=', 'a.convenio_id');\n            })",
    $content
);

// Replace t.quantidade_sessoes with ct.quantidade_sessoes
$content = str_replace(
    "DB::raw('COALESCE(pr.quantidade_sessoes, t.quantidade_sessoes, NULL) AS sessao_total')",
    "DB::raw('COALESCE(pr.quantidade_sessoes, ct.quantidade_sessoes, NULL) AS sessao_total')",
    $content
);
$content = str_replace(
    "IF(COALESCE(pr.quantidade_sessoes, t.quantidade_sessoes) IS NOT NULL",
    "IF(COALESCE(pr.quantidade_sessoes, ct.quantidade_sessoes) IS NOT NULL",
    $content
);
$content = str_replace(
    "CONCAT(' (Sessão ', st.numero_sessao, '/', COALESCE(pr.quantidade_sessoes, t.quantidade_sessoes), ')')",
    "CONCAT(' (Sessão ', st.numero_sessao, '/', COALESCE(pr.quantidade_sessoes, ct.quantidade_sessoes), ')')",
    $content
);

// Replace in byPaciente()
$content = str_replace(
    "AND COALESCE(pr.quantidade_sessoes, t.quantidade_sessoes) IS NOT NULL AND COALESCE(pr.quantidade_sessoes, t.quantidade_sessoes) > 0",
    "AND COALESCE(pr.quantidade_sessoes, ct.quantidade_sessoes) IS NOT NULL AND COALESCE(pr.quantidade_sessoes, ct.quantidade_sessoes) > 0",
    $content
);
$content = str_replace(
    "CONCAT(\" (Sessão \", st.numero_sessao, \"/\", COALESCE(pr.quantidade_sessoes, t.quantidade_sessoes), \")\")",
    "CONCAT(\" (Sessão \", st.numero_sessao, \"/\", COALESCE(pr.quantidade_sessoes, ct.quantidade_sessoes), \")\")",
    $content
);

file_put_contents($file, $content);
echo "AgendamentoController updated successfully!\n";
