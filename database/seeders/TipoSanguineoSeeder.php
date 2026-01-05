<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoSanguineo;

class TipoSanguineoSeeder extends Seeder
{
    public function run(): void
    {
        $items = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        foreach ($items as $descricao) {
            TipoSanguineo::firstOrCreate(['descricao' => $descricao]);
        }
    }
}

