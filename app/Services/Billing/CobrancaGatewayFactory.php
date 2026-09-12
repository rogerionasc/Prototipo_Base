<?php

namespace App\Services\Billing;

use App\Contracts\Billing\CobrancaGateway;
use InvalidArgumentException;

class CobrancaGatewayFactory
{
    public function make(string $provedor): CobrancaGateway
    {
        $provedorLower = strtolower($provedor);
        return match($provedorLower) {
            'asaas' => new AsaasCobrancaGateway(),
            'mock' => new MockCobrancaGateway(),
            default => throw new \InvalidArgumentException("Gateway [{$provedor}] não suportado."),
        };
    }
}
