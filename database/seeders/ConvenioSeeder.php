<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Convenio;

class ConvenioSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['descricao' => 'Particular', 'tipo' => 'PARTICULAR', 'empresa_id' => null, 'ans' => null, 'dias_recebimento' => 0, 'dias_retorno' => 0],
            ['descricao' => 'Amil', 'tipo' => 'CONVENIO', 'empresa_id' => null, 'ans' => 123456, 'dias_recebimento' => 30, 'dias_retorno' => 15],
            ['descricao' => 'Unimed', 'tipo' => 'CONVENIO', 'empresa_id' => null, 'ans' => 654321, 'dias_recebimento' => 30, 'dias_retorno' => 15],
            ['descricao' => 'SulAmérica', 'tipo' => 'CONVENIO', 'empresa_id' => null, 'ans' => 111222, 'dias_recebimento' => 30, 'dias_retorno' => 15],
        ];
        foreach ($items as $data) {
            Convenio::firstOrCreate(['descricao' => $data['descricao']], $data);
        }
    }
}

