<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pessoa;
use App\Models\EstadoCivil;
use App\Models\Especialidade;
use Illuminate\Support\Facades\DB;

class PessoaSeeder extends Seeder
{
    public function run(): void
    {
        $estadoCivil = EstadoCivil::pluck('id', 'descricao')->toArray();
        $conselhoId = \App\Models\Conselho::where('sigla', 'CRM')->value('id');

        $items = [
            ['nome' => 'Dr. João Pereira', 'conselho_id' => $conselhoId, 'numero_conselho' => '12345', 'uf_conselho' => 'SP', 'sexo' => 'Masculino', 'estado_civil_id' => $estadoCivil['Casado'] ?? null, 'email' => 'joao.pereira@example.com'],
            ['nome' => 'Dra. Maria Fernandes', 'conselho_id' => $conselhoId, 'numero_conselho' => '54321', 'uf_conselho' => 'SP', 'sexo' => 'Feminino', 'estado_civil_id' => $estadoCivil['Solteiro'] ?? null, 'email' => 'maria.fernandes@example.com'],
            ['nome' => 'Dr. Carlos Lima', 'conselho_id' => $conselhoId, 'numero_conselho' => '98765', 'uf_conselho' => 'SP', 'sexo' => 'Masculino', 'estado_civil_id' => $estadoCivil['Viúvo'] ?? null, 'email' => 'carlos.lima@example.com'],
        ];
        foreach ($items as $data) {
            Pessoa::firstOrCreate(['numero_conselho' => $data['numero_conselho']], $data);
        }
        $especialidades = Especialidade::pluck('id', 'nome')->toArray();
        $map = [
            '12345' => ['Clínica Geral', 'Cardiologia'],
            '54321' => ['Clínica Geral', 'Dermatologia'],
            '98765' => ['Ortopedia'],
        ];
        foreach ($map as $num => $lista) {
            $prof = Pessoa::where('numero_conselho', $num)->first();
            if (!$prof) {
                continue;
            }
            $ids = [];
            foreach ($lista as $esp) {
                if (isset($especialidades[$esp])) {
                    $ids[] = $especialidades[$esp];
                }
            }
            if ($ids) {
                $attach = [];
                foreach ($ids as $id) {
                    $attach[$id] = ['qre' => null];
                }
                $prof->especialidades()->syncWithoutDetaching($attach);
            }
        }
    }
}

