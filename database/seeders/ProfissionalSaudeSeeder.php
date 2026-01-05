<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProfissionalSaude;
use App\Models\EstadoCivil;
use App\Models\Especialidade;
use Illuminate\Support\Facades\DB;

class ProfissionalSaudeSeeder extends Seeder
{
    public function run(): void
    {
        $estadoCivil = EstadoCivil::pluck('id', 'descricao')->toArray();
        $items = [
            ['nome' => 'Dr. João Pereira', 'crm' => '12345-SP', 'sexo' => 'Masculino', 'estado_civil_id' => $estadoCivil['Casado'] ?? null, 'email' => 'joao.pereira@example.com'],
            ['nome' => 'Dra. Maria Fernandes', 'crm' => '54321-SP', 'sexo' => 'Feminino', 'estado_civil_id' => $estadoCivil['Solteiro'] ?? null, 'email' => 'maria.fernandes@example.com'],
            ['nome' => 'Dr. Carlos Lima', 'crm' => '98765-SP', 'sexo' => 'Masculino', 'estado_civil_id' => $estadoCivil['Viúvo'] ?? null, 'email' => 'carlos.lima@example.com'],
        ];
        foreach ($items as $data) {
            ProfissionalSaude::firstOrCreate(['crm' => $data['crm']], $data);
        }
        $especialidades = Especialidade::pluck('id', 'nome')->toArray();
        $map = [
            '12345-SP' => ['Clínica Geral', 'Cardiologia'],
            '54321-SP' => ['Clínica Geral', 'Dermatologia'],
            '98765-SP' => ['Ortopedia'],
        ];
        foreach ($map as $crm => $lista) {
            $prof = ProfissionalSaude::where('crm', $crm)->first();
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

