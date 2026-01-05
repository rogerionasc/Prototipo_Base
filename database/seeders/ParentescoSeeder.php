<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Parentesco;

class ParentescoSeeder extends Seeder
{
    public function run(): void
    {
        $items = ['Pai', 'Mãe', 'Filho', 'Filha', 'Responsável', 'Tutor', 'Avô', 'Avó', 'Irmão', 'Irmã'];
        foreach ($items as $descricao) {
            Parentesco::firstOrCreate(['descricao' => $descricao]);
        }
    }
}

