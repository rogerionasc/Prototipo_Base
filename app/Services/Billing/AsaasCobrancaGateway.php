<?php

namespace App\Services\Billing;

use App\Contracts\Billing\CobrancaGateway;
use App\Models\ConfiguracaoBancaria;
use Illuminate\Support\Facades\Http;
use Exception;

class AsaasCobrancaGateway implements CobrancaGateway
{
    public function criar(array $dados, ConfiguracaoBancaria $config): array
    {
        $isHomologacao = in_array(strtolower($config->ambiente), ['homologacao', 'sandbox']);
        $baseUrl = $isHomologacao ? 'https://sandbox.asaas.com/api/v3' : 'https://api.asaas.com/v3';
        $apiKey = $config->app_key ?: ($config->client_id ?: $config->client_secret);

        $headers = [
            'access_token' => $apiKey,
            'Content-Type' => 'application/json',
            'User-Agent' => 'PrototipoBase'
        ];

        // Ensure customer exists
        $customerId = null;
        if (!empty($dados['paciente'])) {
            $cpfCnpj = preg_replace('/[^0-9]/', '', $dados['paciente']->cpf ?? '');
            if ($cpfCnpj) {
                // Search existing customer
                $searchResp = Http::withHeaders($headers)
                    ->get("{$baseUrl}/customers", ['cpfCnpj' => $cpfCnpj]);
                
                if ($searchResp->successful() && !empty($searchResp->json('data'))) {
                    $customerId = $searchResp->json('data.0.id');
                } else {
                    // Create customer
                    $createResp = Http::withHeaders($headers)
                        ->post("{$baseUrl}/customers", [
                            'name' => $dados['paciente']->nome,
                            'cpfCnpj' => $cpfCnpj,
                            'email' => $dados['paciente']->email ?? null,
                            'mobilePhone' => $dados['paciente']->telefone ?? null,
                        ]);
                    if ($createResp->successful()) {
                        $customerId = $createResp->json('id');
                    } else {
                        \Illuminate\Support\Facades\Log::error('Erro ao criar cliente Asaas', ['body' => $createResp->body()]);
                    }
                }
            }
        }

        if (!$customerId) {
            throw new Exception("Falha ao identificar ou criar cliente no Asaas. Erro de API ou Paciente precisa de CPF válido.");
        }

        $billingType = strtoupper($dados['tipo'] ?? 'BOLETO');
        if ($billingType === 'PIX') {
            $dueDate = date('Y-m-d');
        } else {
            $dueDate = $dados['vencimento'];
        }

        $description = 'Cobrança gerada pelo sistema';
        if (!empty($dados['nu_pagamento']) && !empty($dados['numero_lote'])) {
            $description = "Pagamento #{$dados['nu_pagamento']} - Lote #{$dados['numero_lote']}";
        } elseif (!empty($dados['numero_lote'])) {
            $description = "Cobrança referente ao Lote #{$dados['numero_lote']}";
        }

        $payload = [
            'customer' => $customerId,
            'billingType' => $billingType,
            'value' => (float)$dados['valor'],
            'dueDate' => $dueDate,
            'description' => $description,
        ];

        $paymentResp = Http::withHeaders($headers)
            ->post("{$baseUrl}/payments", $payload);

        if (!$paymentResp->successful()) {
            $error = $paymentResp->json('errors.0.description') ?? $paymentResp->body();
            throw new Exception("Falha ao gerar cobrança no Asaas: " . $error);
        }

        $paymentData = $paymentResp->json();
        $retorno = [
            'gateway_id' => $paymentData['id'],
            'status' => 'REGISTRADA',
            'url' => $paymentData['bankSlipUrl'] ?? $paymentData['invoiceUrl'] ?? null,
            'nosso_numero' => $paymentData['nossoNumero'] ?? null,
            'linha_digitavel' => $paymentData['bankSlipUrl'] ? $paymentData['identificationField'] ?? null : null,
        ];

        // Se for PIX, busca o QR Code
        if ($billingType === 'PIX') {
            $qrResp = Http::withHeaders($headers)
                ->get("{$baseUrl}/payments/{$paymentData['id']}/pixQrCode");
            
            if ($qrResp->successful()) {
                $qrData = $qrResp->json();
                $retorno['pix_txid'] = $qrData['payload'];
                $retorno['qr_code_base64'] = $qrData['encodedImage'];
            }
        }

        return $retorno;
    }

    public function consultar(string $identificador, ConfiguracaoBancaria $config): array
    {
        $isHomologacao = in_array(strtolower($config->ambiente), ['homologacao', 'sandbox']);
        $baseUrl = $isHomologacao ? 'https://sandbox.asaas.com/api/v3' : 'https://api.asaas.com/v3';
        $apiKey = $config->app_key ?: ($config->client_id ?: $config->client_secret);

        $resp = Http::withHeaders([
            'access_token' => $apiKey,
        ])->get("{$baseUrl}/payments/{$identificador}");

        if (!$resp->successful()) {
            throw new Exception("Falha ao consultar cobrança no Asaas");
        }

        $data = $resp->json();
        return [
            'status' => $data['status'],
        ];
    }

    public function cancelar(string $identificador, ConfiguracaoBancaria $config): bool
    {
        $isHomologacao = in_array(strtolower($config->ambiente), ['homologacao', 'sandbox']);
        $baseUrl = $isHomologacao ? 'https://sandbox.asaas.com/api/v3' : 'https://api.asaas.com/v3';
        $apiKey = $config->app_key ?: ($config->client_id ?: $config->client_secret);

        $resp = Http::withHeaders([
            'access_token' => $apiKey,
        ])->delete("{$baseUrl}/payments/{$identificador}");

        return $resp->successful();
    }
}
