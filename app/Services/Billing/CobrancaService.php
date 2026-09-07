<?php

namespace App\Services\Billing;

use App\Models\Cobranca;
use App\Models\ConfiguracaoBancaria;
use App\Models\ContaReceber;

class CobrancaService
{
    protected CobrancaGatewayFactory $factory;

    public function __construct(CobrancaGatewayFactory $factory)
    {
        $this->factory = $factory;
    }

    public function emitirCobranca(ContaReceber $contaReceber, ConfiguracaoBancaria $config, string $tipo = 'boleto'): Cobranca
    {
        $gateway = $this->factory->make($config->provedor);

        $dados = [
            'valor' => $contaReceber->valor,
            'vencimento' => $contaReceber->vencimento->format('Y-m-d'),
        ];

        $retorno = $gateway->criar($dados, $config);

        $cobranca = Cobranca::create([
            'account_id' => $config->account_id,
            'conta_receber_id' => $contaReceber->id,
            'configuracao_bancaria_id' => $config->id,
            'gateway' => $config->provedor,
            'gateway_id' => $retorno['gateway_id'] ?? null,
            'tipo' => $tipo,
            'nosso_numero' => $retorno['nosso_numero'] ?? null,
            'linha_digitavel' => $retorno['linha_digitavel'] ?? null,
            'codigo_barras' => $retorno['codigo_barras'] ?? null,
            'pix_txid' => $retorno['pix_txid'] ?? null,
            'url' => $retorno['url'] ?? null,
            'valor' => $contaReceber->valor,
            'vencimento' => $contaReceber->vencimento,
            'status' => $retorno['status'] ?? 'REGISTRADA',
            'payload' => $retorno,
        ]);

        return $cobranca;
    }

    public function processarPagamento(Cobranca $cobranca)
    {
        $cobranca->update([
            'status' => 'PAGA',
            'data_pagamento' => now(),
        ]);

        $contaReceber = $cobranca->contaReceber;
        $contaReceber->update([
            'status' => 'PAGA',
        ]);
        
        // Aqui futuramente será integrada a baixa no Caixa do sistema se houver
    }
}
