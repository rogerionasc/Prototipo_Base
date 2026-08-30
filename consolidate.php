<?php

$dir = __DIR__ . '/database/migrations/';

function inject_into_schema($file, $table_name, $injection) {
    global $dir;
    $path = $dir . $file;
    if (!is_file($path)) {
        // Try to find the file
        $files = glob($dir . '*_create_*_table*.php');
        $files = array_merge($files, glob($dir . '*_tables.php'));
        $found = false;
        foreach($files as $f) {
            $c = file_get_contents($f);
            if (strpos($c, "Schema::create('$table_name'") !== false) {
                $path = $f;
                $found = true;
                break;
            }
        }
        if (!$found) { echo "Could not find file for table $table_name\n"; return false; }
    }
    
    $content = file_get_contents($path);
    // Find the end of the Schema::create('$table_name', function (Blueprint $table) { block
    $pattern = "/Schema::create\('$table_name',\s*function\s*\(\w+\s*\\\$table\)\s*\{/";
    if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
        $insert_pos = $matches[0][1] + strlen($matches[0][0]) . "\n";
        // Insert right after the opening brace
        $new_content = substr($content, 0, $insert_pos) . "\n            " . $injection . "\n" . substr($content, $insert_pos);
        file_put_contents($path, $new_content);
        return true;
    }
    echo "Could not inject into $table_name in $path\n";
    return false;
}

// 1. Rename accounts table
$old_accounts = $dir . '2026_08_22_203722_create_accounts_table.php';
$new_accounts = $dir . '2014_10_11_000000_create_accounts_table.php';
if (file_exists($old_accounts)) {
    rename($old_accounts, $new_accounts);
}

// 2. Modify accounts table directly
$acc_content = file_get_contents($new_accounts);
$acc_injection = "\$table->string('endereco')->nullable();\n            \$table->string('telefone')->nullable();\n            \$table->string('email')->nullable();\n            \$table->boolean('ativo')->default(true);";
if (strpos($acc_content, "'endereco'") === false) {
    $acc_content = preg_replace("/Schema::create\('accounts',\s*function\s*\(Blueprint\s*\\\$table\)\s*\{/", "$0\n            $acc_injection", $acc_content);
    file_put_contents($new_accounts, $acc_content);
}

// 3. Inject account_id into users
inject_into_schema('2014_10_12_000000_create_users_table.php', 'users', "\$table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();");

// 4. Inject into 26 tenant tables
$tenant_tables = [
    'pacientes', 'agendamentos', 'faturamentos', 'guias', 
    'pessoas', 'convenios', 'procedimentos',
    'atendimentos', 'prontuarios', 'agenda_medica', 'orcamentos', 
    'caixas', 'movimentacoes_caixa', 'pagamentos', 'sessoes_tratamento', 
    'autorizacoes', 'responsaveis', 'modelos_documentos', 'documentos_prontuario', 
    'prescricoes', 'solicitacao_exames', 'salas', 'guiches', 'paineis', 
    'totens', 'especialidades'
];

foreach ($tenant_tables as $t) {
    inject_into_schema('', $t, "\$table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();");
}

// 5. Also put the constrained back to convenio_tuss since we removed it earlier
$ct_path = $dir . '2026_06_12_000001_create_convenio_tuss_table.php';
if (file_exists($ct_path)) {
    $c = file_get_contents($ct_path);
    $c = str_replace("\$table->unsignedBigInteger('account_id')->nullable();", "\$table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();", $c);
    file_put_contents($ct_path, $c);
}

// 6. Add dias_para_faturar to convenios
inject_into_schema('', 'convenios', "\$table->integer('dias_para_faturar')->nullable()->default(30);");

// 7. Add guia_origem_id to guias
inject_into_schema('', 'guias', "\$table->unsignedBigInteger('guia_origem_id')->nullable();\n            \$table->foreign('guia_origem_id')->references('id')->on('guias')->onDelete('set null');");

// 8. Add numero_lote to faturamentos
inject_into_schema('', 'faturamentos', "\$table->string('numero_lote', 20)->nullable();");

// 9. Add softDeletes to agenda_medica
inject_into_schema('', 'agenda_medica', "\$table->softDeletes();");

// 10. Make procedimento_id nullable in sessoes_tratamento
$st_path = '';
$files = glob($dir . '*_create_sessoes_tratamento_table*.php');
if (!empty($files)) {
    $st_path = $files[0];
    $c = file_get_contents($st_path);
    // Assuming the original had $table->foreignId('procedimento_id')->constrained(...);
    $c = preg_replace("/\\\$table->foreignId\('procedimento_id'\)(?!->nullable)/", "$0->nullable()", $c);
    file_put_contents($st_path, $c);
}

// 11. Delete all modifier migrations
$to_delete = [
    '2026_08_22_220311_add_fields_to_accounts_table.php',
    '2026_08_22_203754_add_account_id_to_users_table.php',
    '2026_08_18_194116_add_dias_para_faturar_to_convenios_table.php',
    '2026_08_22_004930_add_guia_origem_id_to_guias_table.php',
    '2026_08_23_164538_add_numero_lote_to_faturamentos_table.php',
    '2026_08_23_212407_change_descricao_column_in_tuss_table.php',
    '2026_08_19_201601_add_deleted_at_to_agenda_medica_table.php',
    '2026_08_19_210942_make_procedimento_id_nullable_in_sessoes_tratamento_table.php',
    '2026_08_22_205248_add_account_id_to_tenant_tables.php',
    '2026_08_22_212217_add_account_id_to_remaining_tenant_tables.php'
];

foreach ($to_delete as $d) {
    if (file_exists($dir . $d)) {
        unlink($dir . $d);
        echo "Deleted $d\n";
    }
}

echo "All consolidated.\n";
