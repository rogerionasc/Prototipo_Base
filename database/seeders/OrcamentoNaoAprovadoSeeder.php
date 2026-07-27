<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Orcamento;
use App\Models\OrcamentoProcedimento;
use App\Models\Paciente;
use App\Models\Pessoa;
use App\Models\Procedimento;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OrcamentoNaoAprovadoSeeder extends Seeder
{
    public function run(): void
    {
        $pacientes = Paciente::pluck('id')->all();
        $procedimentos = Procedimento::where('ativo', true)->pluck('id', 'nome')->toArray();
        if (!$pacientes || !$procedimentos) {
            return;
        }
        $hoje = Carbon::today();
        for ($i = 0; $i < 5; $i++) {
            $numero = 'ORC-' . $hoje->format('Ymd') . '-' . Str::upper(Str::random(6));
            $pacienteId = $pacientes[array_rand($pacientes)];
            $orcamento = Orcamento::firstOrCreate(
                ['numero' => $numero],
                [
                    'data_emissao' => $hoje->toDateString(),
                    'validade' => $hoje->copy()->addDays(30)->toDateString(),
                    'convenio_id' => null,
                    'paciente_id' => $pacienteId,
                    'valor_bruto' => 0,
                    'desconto' => 0,
                    'valor_total' => 0,
                    'valor_avista' => null,
                    'aprovado' => false,
                ]
            );
            $itens = [
                'Consulta Clínica Geral' => 1,
                'Sessão de Fisioterapia' => 2,
            ];
            $valorBruto = 0;
            foreach ($itens as $nomeProcedimento => $quantidade) {
                if (!isset($procedimentos[$nomeProcedimento])) {
                    continue;
                }
                $proc = Procedimento::find($procedimentos[$nomeProcedimento]);
                if (!$proc) {
                    continue;
                }
                $vu = (float) ($proc->valor ?? 0);
                $vt = $vu * $quantidade;
                $valorBruto += $vt;
                OrcamentoProcedimento::create([
                    'orcamento_id' => $orcamento->id,
                    'procedimento_id' => $proc->id,
                    'quantidade' => $quantidade,
                    'valor_unitario' => $vu,
                    'valor_total' => $vt,
                    'observacoes' => null,
                ]);
            }
            $orcamento->update([
                'valor_bruto' => $valorBruto,
                'valor_total' => $valorBruto,
            ]);
        }
    }
}

