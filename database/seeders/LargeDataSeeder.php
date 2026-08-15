<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class LargeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        // ==== SEED ESPECIALIDADES ====
        $this->command->info('Criando Especialidades...');
        $especialidadesNomes = [
            'Cardiologia',
            'Clinica Geral',
            'Dermatologia',
            'Endocrinologia',
            'Gastroenterologia',
            'Geriatria',
            'Ginecologia',
            'Neurologia',
            'Oftalmologia',
            'Ortopedia',
            'Otorrinolaringologia',
            'Pediatria',
            'Psiquiatria',
            'Urologia',
            'Reumatologia',
            'Infectologia',
            'Pneumologia',
            'Nefrologia',
            'Hematologia',
            'Oncologia',
            'Mastologia',
            'Cirurgia Geral',
            'Cirurgia Plástica',
            'Vascular',
            'Anestesiologia',
            'Radiologia'
        ];

        $especialidadesIds = [];
        foreach ($especialidadesNomes as $idx => $nome) {
            // Insere ignorando duplicatas pelo nome se tivesse chave unica, mas usaremos firstOrCreate
            $esp = DB::table('especialidades')->where('nome', $nome)->first();
            if (!$esp) {
                $id = DB::table('especialidades')->insertGetId([
                    'nome' => $nome,
                    'codigo' => 'ESP' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                    'ativo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $especialidadesIds[] = $id;
            } else {
                $especialidadesIds[] = $esp->id;
            }
        }

        // ==== SEED PROCEDIMENTOS ====
        $this->command->info('Criando 100 Procedimentos...');
        $procedimentosPrefixos = ['Consulta', 'Exame', 'Cirurgia', 'Avaliação', 'Terapia', 'Retorno', 'Sessão de'];
        $procedimentosSufixos = ['de Rotina', 'Especializada', 'Geral', 'com Laudo', 'de Urgência', 'Estética', 'Preventiva', 'Diagnóstica'];

        $procedimentos = [];
        $pivotData = [];
        for ($i = 0; $i < 100; $i++) {
            $prefix = $faker->randomElement($procedimentosPrefixos);
            $sufix = $faker->randomElement($procedimentosSufixos);
            $word = $faker->word;
            $nome = "$prefix $sufix " . ucfirst($word);

            $ehTratamento = $faker->boolean(20);

            $procId = DB::table('procedimentos')->insertGetId([
                'nome' => substr(ucfirst(trim($nome)), 0, 120),
                'descricao' => substr($faker->sentence, 0, 120),
                'eh_tratamento' => $ehTratamento,
                'quantidade_sessoes' => $ehTratamento ? $faker->numberBetween(2, 10) : null,
                'valor' => $faker->randomFloat(2, 50, 1500),
                'comissao_percentual' => $faker->randomFloat(2, 0, 30),
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $pivotData[] = [
                'especialidade_id' => $faker->randomElement($especialidadesIds),
                'procedimento_id' => $procId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($pivotData, 50) as $chunk) {
            DB::table('especialidade_procedimento')->insert($chunk);
        }
        $this->command->info('Especialidades e Procedimentos criados com sucesso!');

        // ==== SEED MEDICOS ====
        $this->command->info('Criando 500 Médicos...');
        $medicos = [];
        $crmId = DB::table('conselhos')->where('sigla', 'CRM')->value('id');
        if (!$crmId) {
            $crmId = DB::table('conselhos')->insertGetId([
                'codigo' => '06',
                'sigla' => 'CRM',
                'descricao' => 'Conselho Regional de Medicina',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        for ($i = 0; $i < 500; $i++) {
            $medicos[] = [
                'nome' => $faker->name,
                'cpf' => $this->generateCpf(),
                'rg' => $faker->numerify('##########'),
                'sexo' => $faker->randomElement(['Masculino', 'Feminino']),
                'data_nascimento' => $faker->dateTimeBetween('-70 years', '-25 years')->format('Y-m-d'),
                'cargo' => 'Médico',
                'conselho_id' => $crmId,
                'numero_conselho' => $faker->numerify('#####'),
                'uf_conselho' => $faker->stateAbbr,
                'celular' => $faker->cellphoneNumber,
                'email' => $faker->unique()->safeEmail,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($medicos, 100) as $chunk) {
            DB::table('pessoas')->insert($chunk);
        }
        $this->command->info('500 Médicos criados com sucesso!');


        // ==== SEED PACIENTES ====
        $this->command->info('Criando 7000 Pacientes (Isso pode demorar um pouco)...');

        $total = 7000;
        $chunkSize = 1000;

        for ($j = 0; $j < ($total / $chunkSize); $j++) {
            $pacientes = [];
            for ($i = 0; $i < $chunkSize; $i++) {
                $pacientes[] = [
                    'nome' => $faker->name,
                    'cpf' => $this->generateCpf(),
                    'cns' => $faker->numerify('###############'),
                    'rg' => $faker->numerify('##########'),
                    'sexo' => $faker->randomElement(['Masculino', 'Feminino', 'Outro']),
                    'data_nascimento' => $faker->dateTimeBetween('-90 years', 'now')->format('Y-m-d'),
                    'celular' => $faker->cellphoneNumber,
                    'email' => $faker->unique()->safeEmail,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('pacientes')->insert($pacientes);
            $this->command->info('Criados ' . (($j + 1) * $chunkSize) . ' pacientes...');
        }

        $this->command->info('7000 Pacientes criados com sucesso!');
    }

    /**
     * Gera um CPF válido formatado (xxx.xxx.xxx-xx)
     */
    private function generateCpf(): string
    {
        $n = [];
        for ($i = 0; $i < 9; $i++) {
            $n[] = rand(0, 9);
        }

        $d1 = 0;
        for ($i = 0; $i < 9; $i++) {
            $d1 += $n[$i] * (10 - $i);
        }
        $d1 = 11 - ($d1 % 11);
        if ($d1 >= 10) $d1 = 0;

        $n[] = $d1;

        $d2 = 0;
        for ($i = 0; $i < 10; $i++) {
            $d2 += $n[$i] * (11 - $i);
        }
        $d2 = 11 - ($d2 % 11);
        if ($d2 >= 10) $d2 = 0;

        $n[] = $d2;

        return sprintf('%s%s%s.%s%s%s.%s%s%s-%s%s', ...$n);
    }
}
