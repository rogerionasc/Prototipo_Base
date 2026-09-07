<?php

namespace App\Contracts\Billing;

use App\Models\ConfiguracaoBancaria;

interface CobrancaGateway
{
    public function criar(array $dados, ConfiguracaoBancaria $config): array;
    public function consultar(string $identificador, ConfiguracaoBancaria $config): array;
    public function cancelar(string $identificador, ConfiguracaoBancaria $config): bool;
}
