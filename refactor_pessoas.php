<?php
$baseDir = __DIR__;

function renameFile($old, $new) {
    if (file_exists($old)) {
        rename($old, $new);
        echo "Renamed: $old -> $new\n";
    }
}

function processDirectory($dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['php', 'vue', 'js'])) {
            $content = file_get_contents($file->getPathname());
            $originalContent = $content;

            // Replacements
            $content = str_replace('profissionais_saude', 'pessoas', $content);
            $content = str_replace('ProfissionalSaude', 'Pessoa', $content);
            $content = str_replace('profissional_saude_id', 'pessoa_id', $content);
            $content = str_replace('profissional_saude', 'pessoa', $content);
            $content = str_replace('Profissional de Saude', 'Pessoa', $content);
            $content = str_replace('Profissional de Saúde', 'Pessoa', $content);
            $content = str_replace('ProfissionalSaudeController', 'PessoaController', $content);
            $content = str_replace('ProfissionalSaudeSeeder', 'PessoaSeeder', $content);

            if ($content !== $originalContent) {
                file_put_contents($file->getPathname(), $content);
                echo "Modified: " . $file->getPathname() . "\n";
            }
        }
    }
}

// 1. Rename Migration files
$migrationsDir = $baseDir . '/database/migrations';
$migrationFiles = scandir($migrationsDir);
foreach ($migrationFiles as $file) {
    if (strpos($file, 'create_profissionais_saude_table') !== false) {
        $newName = str_replace('create_profissionais_saude_table', 'create_pessoas_table', $file);
        renameFile($migrationsDir . '/' . $file, $migrationsDir . '/' . $newName);
    }
    if (strpos($file, 'create_profissional_especialidade_table') !== false) {
        $newName = str_replace('create_profissional_especialidade_table', 'create_pessoa_especialidade_table', $file);
        renameFile($migrationsDir . '/' . $file, $migrationsDir . '/' . $newName);
    }
}

// 2. Rename Model
renameFile($baseDir . '/app/Models/ProfissionalSaude.php', $baseDir . '/app/Models/Pessoa.php');

// 3. Rename Controller
renameFile($baseDir . '/app/Http/Controllers/ProfissionalSaudeController.php', $baseDir . '/app/Http/Controllers/PessoaController.php');

// 4. Rename Seeder
renameFile($baseDir . '/database/seeders/ProfissionalSaudeSeeder.php', $baseDir . '/database/seeders/PessoaSeeder.php');

// 5. Replace contents in directories
processDirectory($baseDir . '/app');
processDirectory($baseDir . '/database');
processDirectory($baseDir . '/resources');
processDirectory($baseDir . '/routes');

echo "Refactoring completed.\n";
