<?php

namespace App\Services\Bancario;

class IntegracaoFactory
{
    public static function make(string $provedor, bool $isHomologacao = true): IntegracaoBancoInterface
    {
        return match (strtolower($provedor)) {
            'bb', 'banco do brasil', 'banco_do_brasil' => new IntegracaoBancoBrasil($isHomologacao),
            'asaas' => new IntegracaoAsaas($isHomologacao),
            // 'itau' => new IntegracaoItau($isHomologacao),
            default => throw new \Exception("Provedor bancário não suportado: {$provedor}"),
        };
    }
}
