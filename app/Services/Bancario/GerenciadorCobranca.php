<?php

namespace App\Services\Bancario;

use Illuminate\Support\Facades\Http;

class GerenciadorCobranca
{
    protected IntegracaoBancoInterface $integracao;

    public function __construct(IntegracaoBancoInterface $integracao)
    {
        $this->integracao = $integracao;
    }

    public function gerarToken(array $credenciais)
    {
        $endpoint = $this->integracao->getEndpointAuth();

        if (empty($endpoint)) {
            // Algumas integrações (como Asaas) não usam endpoint de token (usam API Key fixa).
            return [
                'status' => 200,
                'success' => true,
                'data' => ['access_token' => $credenciais['access_token'] ?? $credenciais['client_id'] ?? ''],
                'raw_body' => '',
                'error' => null,
            ];
        }

        $payload = $this->integracao->getPayloadAuth($credenciais);
        $headers = $this->integracao->getHeadersAuth($credenciais);

        $response = Http::asForm()
            ->withHeaders($headers)
            ->post($endpoint, $payload);

        return [
            'status' => $response->status(),
            'success' => $response->successful(),
            'data' => $response->json(),
            'raw_body' => $response->body(),
            'error' => $response->successful() ? null : 'Falha ao gerar token',
        ];
    }

    public function registrarBoleto(array $dadosBoleto, string $token, array $credenciais)
    {
        if ($this->integracao->exigeCustomerPreviamente()) {
            $endpointCustomer = $this->integracao->getEndpointCustomer();
            $payloadCustomer = $this->integracao->getPayloadCustomer($dadosBoleto);
            $headersCustomer = $this->integracao->getHeadersCustomer($credenciais);

            $response = Http::withHeaders($headersCustomer)->post($endpointCustomer, $payloadCustomer);

            if (!$response->successful()) {
                $errorMsg = $response->json('errors.0.description') ?? $response->body();
                throw new \Exception('Falha ao registrar cliente: ' . $errorMsg);
            }

            $dadosBoleto['customer_id'] = $response->json('id');
        }

        $endpoint = $this->integracao->getEndpointBoleto();
        $payload = $this->integracao->getPayloadBoleto($dadosBoleto);
        $headers = $this->integracao->getHeadersBoleto($token, $credenciais);

        // Se tiver app_key nas credenciais, adicionar na query string (especifico do BB, mas parametrizável)
        if (isset($credenciais['app_key'])) {
            $queryParam = $this->integracao instanceof IntegracaoBancoBrasil ? 'gw-dev-app-key' : 'app_key';
            $endpoint .= '?' . $queryParam . '=' . $credenciais['app_key'];
        }

        $response = Http::withHeaders($headers)
            ->post($endpoint, $payload);

        $errorMsg = 'Falha ao registrar boleto';
        if (!$response->successful()) {
            $json = $response->json();
            if (isset($json['errors'][0]['description'])) {
                $errorMsg = $json['errors'][0]['description'];
            }
        }

        return [
            'status' => $response->status(),
            'success' => $response->successful(),
            'data' => $response->json(),
            'raw_body' => $response->body(),
            'error' => $response->successful() ? null : $errorMsg,
        ];
    }
}
