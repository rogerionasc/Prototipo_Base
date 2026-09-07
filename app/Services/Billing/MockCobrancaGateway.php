<?php

namespace App\Services\Billing;

use App\Contracts\Billing\CobrancaGateway;
use App\Models\ConfiguracaoBancaria;
use Illuminate\Support\Str;

class MockCobrancaGateway implements CobrancaGateway
{
    public function criar(array $dados, ConfiguracaoBancaria $config): array
    {
        return [
            'gateway_id' => 'mock_' . Str::random(10),
            'nosso_numero' => rand(100000000, 999999999),
            'linha_digitavel' => rand(10000, 99999) . '.' . rand(10000, 99999) . ' ' . rand(10000, 99999) . '.' . rand(10000, 99999) . ' ' . rand(10000, 99999) . '.' . rand(10000, 99999) . ' 1 ' . rand(10000000000000, 99999999999999),
            'codigo_barras' => rand(10000000000000, 99999999999999) . rand(10000000000000, 99999999999999),
            'url' => url('/boletos/mock/' . Str::random(10)),
            'status' => 'REGISTRADA',
        ];
    }

    public function consultar(string $identificador, ConfiguracaoBancaria $config): array
    {
        return [
            'status' => 'REGISTRADA',
        ];
    }

    public function cancelar(string $identificador, ConfiguracaoBancaria $config): bool
    {
        return true;
    }
}
