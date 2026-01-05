<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Especialidade;

class EspecialidadeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nome' => 'Clínica Geral', 'codigo' => 'CG', 'descricao' => null, 'ativo' => true],
            ['nome' => 'Cardiologia', 'codigo' => 'CAR', 'descricao' => null, 'ativo' => true],
            ['nome' => 'Ortopedia', 'codigo' => 'ORT', 'descricao' => null, 'ativo' => true],
            ['nome' => 'Pediatria', 'codigo' => 'PED', 'descricao' => null, 'ativo' => true],
            ['nome' => 'Dermatologia', 'codigo' => 'DER', 'descricao' => null, 'ativo' => true],
        ];
        foreach ($items as $data) {
            Especialidade::firstOrCreate(['nome' => $data['nome']], $data);
        }
    }
}

