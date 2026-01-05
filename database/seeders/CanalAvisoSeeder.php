<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CanalAviso;

class CanalAvisoSeeder extends Seeder
{
    public function run(): void
    {
        $items = ['WhatsApp', 'SMS', 'E-mail', 'Telefone'];
        foreach ($items as $nome) {
            CanalAviso::firstOrCreate(['nome' => $nome]);
        }
    }
}

