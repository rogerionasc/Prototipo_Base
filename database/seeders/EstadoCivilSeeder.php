<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoCivil;

class EstadoCivilSeeder extends Seeder
{
    public function run(): void
    {
        $items = ['Solteiro', 'Casado', 'Divorciado', 'Viúvo', 'União Estável'];
        foreach ($items as $descricao) {
            EstadoCivil::firstOrCreate(['descricao' => $descricao]);
        }
    }
}

