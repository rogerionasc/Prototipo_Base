<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConselhoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conselhos = [
            ['codigo' => '01', 'sigla' => 'CRAS', 'descricao' => 'Conselho Regional de Assistência Social'],
            ['codigo' => '02', 'sigla' => 'COREN', 'descricao' => 'Conselho Regional de Enfermagem'],
            ['codigo' => '03', 'sigla' => 'CRF', 'descricao' => 'Conselho Regional de Farmácia'],
            ['codigo' => '04', 'sigla' => 'CRFA', 'descricao' => 'Conselho Regional de Fonoaudiologia'],
            ['codigo' => '05', 'sigla' => 'CREFITO', 'descricao' => 'Conselho Regional de Fisioterapia e Terapia Ocupacional'],
            ['codigo' => '06', 'sigla' => 'CRM', 'descricao' => 'Conselho Regional de Medicina'],
            ['codigo' => '07', 'sigla' => 'CRN', 'descricao' => 'Conselho Regional de Nutrição'],
            ['codigo' => '08', 'sigla' => 'CRO', 'descricao' => 'Conselho Regional de Odontologia'],
            ['codigo' => '09', 'sigla' => 'CRP', 'descricao' => 'Conselho Regional de Psicologia'],
            ['codigo' => '10', 'sigla' => 'OUT', 'descricao' => 'Outros Conselhos'],
        ];

        foreach ($conselhos as $conselho) {
            \App\Models\Conselho::firstOrCreate(['sigla' => $conselho['sigla']], $conselho);
        }
    }
}
