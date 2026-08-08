<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Convenio;

class ConvenioSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'descricao' => 'Particular',
                'tipo' => 'PARTICULAR',
                'empresa_id' => null,
            ],
            [
                'descricao' => 'Unimed',
                'tipo' => 'CONVENIO',
                'empresa_id' => null,
                'ans' => 320498,
                'dias_recebimento' => 30,
                'dias_retorno' => 15,
                'logo_path' => 'convenios/logo1.png'
            ],
            [
                'descricao' => 'Humana Saúde',
                'tipo' => 'CONVENIO',
                'empresa_id' => null,
                'ans' => 318043,
                'dias_recebimento' => 30,
                'dias_retorno' => 15,
                'logo_path' => 'convenios/logo2.png'
            ],
            [
                'descricao' => 'Amil',
                'tipo' => 'CONVENIO',
                'empresa_id' => null,
                'ans' => 326305,
                'dias_recebimento' => 30,
                'dias_retorno' => 15,
                'logo_path' => 'convenios/logo3.png'
            ],
        ];

        foreach ($items as $data) {
            Convenio::updateOrCreate(['descricao' => $data['descricao']], $data);
        }
    }
}
