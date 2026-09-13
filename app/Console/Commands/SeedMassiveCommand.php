<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SeedMassiveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-massive {--count=50000 : Numero de registros por tabela} {--chunk=1000 : Tamanho do lote}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Popula as tabelas (pacientes, agendamentos, atendimentos, contas_a_pagar, contas_a_receber, faturamento, guias) com dados em massa para stress testing.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');
        $count = (int) $this->option('count');
        $chunkSize = (int) $this->option('chunk');
        
        $this->info("Iniciando Seed Massivo de {$count} registros por tabela...");
        $this->warn("Tamanho do lote: {$chunkSize}. Isso pode levar alguns minutos.");

        // Desabilitar logs de DB e checks de chaves estrangeiras para performance
        DB::disableQueryLog();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Carregar IDs base para evitar erros de FK
        $this->info("Carregando IDs base na memoria...");
        
        $accounts = DB::table('accounts')->pluck('id')->toArray() ?: [1];
        $despesas = DB::table('despesas')->pluck('id')->toArray() ?: [1];
        $categorias = DB::table('categoria_despesas')->pluck('id')->toArray() ?: [1];
        $convenios = DB::table('convenios')->pluck('id')->toArray() ?: [1];
        $users = DB::table('users')->pluck('id')->toArray() ?: [1]; // Profissionais
        $procedimentos = DB::table('procedimentos')->pluck('id')->toArray() ?: [1];
        $catProcedimentos = DB::table('categorias_procedimento')->pluck('id')->toArray() ?: [1];
        
        // 1.1 Gerar Pacientes
        $this->info("Gerando Pacientes...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        $this->seedTable('pacientes', $count, $chunkSize, $bar, function() use ($accounts) {
            return [
                'account_id' => $this->randomElement($accounts),
                'nome' => 'Paciente Teste ' . Str::random(8),
                'cpf' => rand(100000000, 999999999) . rand(10, 99), // CPF falso basico
                'celular' => '119' . rand(10000000, 99999999),
                'data_nascimento' => Carbon::now()->subYears(rand(18, 80))->subDays(rand(1, 365)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
        $bar->finish();
        $this->newLine();

        $pacientes = DB::table('pacientes')->pluck('id')->toArray() ?: [1];

        // 1.2 Gerar Agendamentos
        $this->info("Gerando Agendamentos...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        $this->seedTable('agendamentos', $count, $chunkSize, $bar, function() use ($accounts, $pacientes, $convenios, $procedimentos) {
            return [
                'account_id' => $this->randomElement($accounts),
                'paciente_id' => $this->randomElement($pacientes),
                'convenio_id' => $this->randomElement($convenios),
                'procedimento_id' => $this->randomElement($procedimentos),
                'data' => Carbon::now()->addDays(rand(-30, 30))->format('Y-m-d'),
                'hora' => sprintf('%02d:%02d:00', rand(8, 18), $this->randomElement(['00', '15', '30', '45'])),
                'status_id' => rand(1, 5), // Assuming status are 1 to 5
                'valor_cobrado' => rand(50, 500),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
        $bar->finish();
        $this->newLine();

        $agendamentos = DB::table('agendamentos')->pluck('id')->toArray() ?: [1];

        // 1.3 Gerar Atendimentos
        $this->info("Gerando Atendimentos...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        $this->seedTable('atendimentos', $count, $chunkSize, $bar, function() use ($accounts, $agendamentos, $pacientes, $users, $catProcedimentos) {
            return [
                'account_id' => $this->randomElement($accounts),
                'agendamento_id' => $this->randomElement($agendamentos),
                'paciente_id' => $this->randomElement($pacientes),
                'medico_id' => $this->randomElement($users),
                'categoria_procedimento_id' => $this->randomElement($catProcedimentos),
                'data_atendimento' => Carbon::now()->subDays(rand(0, 30)),
                'status' => $this->randomElement(['Em Andamento', 'Finalizado', 'Aguardando']),
                'tipo_atendimento' => $this->randomElement(['Consulta', 'Retorno', 'Exame']),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
        $bar->finish();
        $this->newLine();


        // 2. Faturamentos
        $this->info("Gerando Faturamentos...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        $this->seedTable('faturamentos', $count, $chunkSize, $bar, function() use ($accounts, $convenios, $pacientes) {
            $now = Carbon::now()->subDays(rand(0, 30));
            return [
                'account_id' => $this->randomElement($accounts),
                'convenio_id' => $this->randomElement($convenios),
                'paciente_id' => $this->randomElement($pacientes),
                'numero_lote' => Str::random(10),
                'data_faturamento' => clone $now,
                'vencimento' => (clone $now)->addDays(30),
                'status' => $this->randomElement(['Aberto', 'Faturado', 'Glosado']),
                'valor_total' => rand(100, 5000),
                'valor_aprovado' => rand(100, 5000),
                'valor_glosado' => rand(0, 500),
                'valor_final' => rand(100, 5000),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
        $bar->finish();
        $this->newLine();

        $faturamentos = DB::table('faturamentos')->pluck('id')->toArray() ?: [1];

        // 3. Contas a Pagar (conta_pagars)
        $this->info("Gerando Contas a Pagar...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        $this->seedTable('conta_pagars', $count, $chunkSize, $bar, function() use ($accounts, $despesas, $categorias) {
            $status = $this->randomElement(['Pendente', 'Pago', 'Vencida']);
            $now = Carbon::now();
            return [
                'account_id' => $this->randomElement($accounts),
                'despesa_id' => $this->randomElement($despesas),
                'categoria_id' => $this->randomElement($categorias),
                'descricao' => 'Despesa de Teste Massivo ' . Str::random(5),
                'valor' => rand(100, 10000),
                'data_competencia' => $now->copy()->subDays(rand(1, 60)),
                'data_vencimento' => $now->copy()->addDays(rand(-30, 30)),
                'status' => $status,
                'data_pagamento' => $status === 'Pago' ? $now->copy()->subDays(rand(1, 10)) : null,
                'valor_pago' => $status === 'Pago' ? rand(100, 10000) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
        $bar->finish();
        $this->newLine();

        // 4. Contas a Receber
        $this->info("Gerando Contas a Receber...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        $this->seedTable('contas_receber', $count, $chunkSize, $bar, function() use ($accounts, $faturamentos, $pacientes, $convenios) {
            $status = $this->randomElement(['Pendente', 'Pago', 'Atrasado']);
            $now = Carbon::now();
            return [
                'account_id' => $this->randomElement($accounts),
                'faturamento_id' => $this->randomElement($faturamentos),
                'paciente_id' => $this->randomElement($pacientes),
                'convenio_id' => $this->randomElement($convenios),
                'valor' => rand(50, 2000),
                'vencimento' => $now->copy()->addDays(rand(-30, 30)),
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
        $bar->finish();
        $this->newLine();

        // 5. Guias (Executadas e Nao Atendidas)
        $this->info("Gerando Guias...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        $this->seedTable('guias', $count, $chunkSize, $bar, function() use ($accounts, $agendamentos, $faturamentos) {
            $status = $this->randomElement(['Executada', 'Nao_Atendida', 'Autorizada', 'Negada']);
            return [
                'account_id' => $this->randomElement($accounts),
                'agendamento_id' => $this->randomElement($agendamentos),
                'faturamento_id' => $this->randomElement($faturamentos),
                'numero_guia_prestador' => Str::random(10),
                'numero_guia_operadora' => Str::random(10),
                'beneficiario_nome' => 'Paciente Teste ' . Str::random(5),
                'numero_carteira' => rand(1000000000, 9999999999),
                'validade_carteira' => Carbon::now()->addYears(2),
                'data_solicitacao' => Carbon::now()->subDays(rand(1, 30)),
                'data_autorizacao' => Carbon::now()->subDays(rand(1, 29)),
                'status' => $status,
                'tipo' => $this->randomElement(['SADT', 'Consulta', 'Internacao']),
                'valor_solicitado' => rand(100, 1500),
                'valor_autorizado' => rand(100, 1500),
                'valor_total_geral' => rand(100, 1500),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
        $bar->finish();
        $this->newLine();

        // Restaurar checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info("Seed Massivo Concluído com Sucesso!");
    }

    private function seedTable(string $table, int $total, int $chunkSize, \Symfony\Component\Console\Helper\ProgressBar $bar, callable $dataGenerator)
    {
        $iterations = ceil($total / $chunkSize);
        
        for ($i = 0; $i < $iterations; $i++) {
            $data = [];
            $limit = min($chunkSize, $total - ($i * $chunkSize));
            
            for ($j = 0; $j < $limit; $j++) {
                $data[] = $dataGenerator();
            }
            
            DB::table($table)->insert($data);
            $bar->advance($limit);
            unset($data);
            gc_collect_cycles();
        }
    }

    private function randomElement(array $array)
    {
        if (empty($array)) {
            return null;
        }
        return $array[array_rand($array)];
    }
}
