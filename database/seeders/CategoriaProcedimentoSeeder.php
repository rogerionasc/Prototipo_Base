<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoriaProcedimento;

class CategoriaProcedimentoSeeder extends Seeder
{
    public function run(): void
    {
        $items = ['Consulta', 'Exame', 'Tratamento', 'Cirurgia', 'Terapia'];
        foreach ($items as $nome) {
            CategoriaProcedimento::firstOrCreate(['nome' => $nome]);
        }
    }
}

