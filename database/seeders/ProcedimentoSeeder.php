<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Procedimento;
use App\Models\CategoriaProcedimento;

class ProcedimentoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = CategoriaProcedimento::pluck('id', 'nome')->toArray();
        $items = [
            ['nome' => 'Consulta Clínica Geral', 'descricao' => null, 'categoria' => 'Consulta', 'eh_tratamento' => false, 'quantidade_sessoes' => null, 'valor' => 150.00, 'comissao_percentual' => 0, 'ativo' => true],
            ['nome' => 'Eletrocardiograma', 'descricao' => null, 'categoria' => 'Exame', 'eh_tratamento' => false, 'quantidade_sessoes' => null, 'valor' => 200.00, 'comissao_percentual' => 0, 'ativo' => true],
            ['nome' => 'Sessão de Fisioterapia', 'descricao' => null, 'categoria' => 'Tratamento', 'eh_tratamento' => true, 'quantidade_sessoes' => 10, 'valor' => 100.00, 'comissao_percentual' => 0, 'ativo' => true],
            ['nome' => 'Cirurgia de Pequeno Porte', 'descricao' => null, 'categoria' => 'Cirurgia', 'eh_tratamento' => false, 'quantidade_sessoes' => null, 'valor' => 1200.00, 'comissao_percentual' => 0, 'ativo' => true],
            ['nome' => 'Terapia Ocupacional', 'descricao' => null, 'categoria' => 'Terapia', 'eh_tratamento' => true, 'quantidade_sessoes' => 8, 'valor' => 130.00, 'comissao_percentual' => 0, 'ativo' => true],
        ];
        foreach ($items as $data) {
            $data['categoria_id'] = $categorias[$data['categoria']] ?? null;
            unset($data['categoria']);
            Procedimento::firstOrCreate(['nome' => $data['nome']], $data);
        }
    }
}

