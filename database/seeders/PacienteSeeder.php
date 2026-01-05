<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paciente;
use App\Models\EstadoCivil;
use App\Models\TipoSanguineo;
use App\Models\CanalAviso;

class PacienteSeeder extends Seeder
{
    public function run(): void
    {
        $estadoCivil = EstadoCivil::pluck('id', 'descricao')->toArray();
        $tipoSanguineo = TipoSanguineo::pluck('id', 'descricao')->toArray();
        $canalAviso = CanalAviso::pluck('id', 'nome')->toArray();
        $items = [
            ['nome' => 'Ana Silva', 'cpf' => '111.111.111-11', 'sexo' => 'Feminino', 'estado_civil_id' => $estadoCivil['Solteiro'] ?? null, 'data_nascimento' => '1990-05-10', 'celular' => '11911111111', 'email' => 'ana@example.com', 'canal_aviso_id' => $canalAviso['WhatsApp'] ?? null, 'tipo_sanguineo_id' => $tipoSanguineo['A+'] ?? null, 'tem_responsavel' => false],
            ['nome' => 'Bruno Souza', 'cpf' => '222.222.222-22', 'sexo' => 'Masculino', 'estado_civil_id' => $estadoCivil['Casado'] ?? null, 'data_nascimento' => '1985-08-22', 'celular' => '11922222222', 'email' => 'bruno@example.com', 'canal_aviso_id' => $canalAviso['E-mail'] ?? null, 'tipo_sanguineo_id' => $tipoSanguineo['O+'] ?? null, 'tem_responsavel' => false],
            ['nome' => 'Carla Mota', 'cpf' => '333.333.333-33', 'sexo' => 'Feminino', 'estado_civil_id' => $estadoCivil['Divorciado'] ?? null, 'data_nascimento' => '1992-01-15', 'celular' => '11933333333', 'email' => 'carla@example.com', 'canal_aviso_id' => $canalAviso['SMS'] ?? null, 'tipo_sanguineo_id' => $tipoSanguineo['B-'] ?? null, 'tem_responsavel' => false],
            ['nome' => 'Diego Alves', 'cpf' => '444.444.444-44', 'sexo' => 'Masculino', 'estado_civil_id' => $estadoCivil['Solteiro'] ?? null, 'data_nascimento' => '1998-11-01', 'celular' => '11944444444', 'email' => 'diego@example.com', 'canal_aviso_id' => $canalAviso['Telefone'] ?? null, 'tipo_sanguineo_id' => $tipoSanguineo['AB+'] ?? null, 'tem_responsavel' => false],
            ['nome' => 'Elaine Costa', 'cpf' => '555.555.555-55', 'sexo' => 'Feminino', 'estado_civil_id' => $estadoCivil['Viúvo'] ?? null, 'data_nascimento' => '1979-03-25', 'celular' => '11955555555', 'email' => 'elaine@example.com', 'canal_aviso_id' => $canalAviso['WhatsApp'] ?? null, 'tipo_sanguineo_id' => $tipoSanguineo['O-'] ?? null, 'tem_responsavel' => false],
        ];
        foreach ($items as $data) {
            Paciente::firstOrCreate(['cpf' => $data['cpf']], $data);
        }
    }
}

