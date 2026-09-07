<?php

namespace App\Services\Billing;

use App\Contracts\Billing\CobrancaGateway;
use InvalidArgumentException;

class CobrancaGatewayFactory
{
    public function make(string $provedor): CobrancaGateway
    {
        return match($provedor) {
            'mock' => new MockCobrancaGateway(),
            default => throw new InvalidArgumentException("Gateway [{$provedor}] não suportado."),
        };
    }
}
