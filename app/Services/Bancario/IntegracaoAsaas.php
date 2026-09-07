<?php

namespace App\Services\Bancario;

class IntegracaoAsaas implements IntegracaoBancoInterface
{
    protected $isHomologacao = true;

    public function __construct(bool $isHomologacao = true)
    {
        $this->isHomologacao = $isHomologacao;
    }

    public function getEndpointAuth(): string
    {
        // Asaas não utiliza OAuth com geração de token dinâmico por requisição.
        // Utiliza uma API Key (access_token) fixa.
        // Retornamos vazio para que o Gerenciador pule a chamada HTTP de autenticação.
        return '';
    }

    public function getEndpointBoleto(): string
    {
        return $this->isHomologacao
            ? 'https://api-sandbox.asaas.com/v3/payments'
            : 'https://api.asaas.com/v3/payments';
    }

    public function exigeCustomerPreviamente(): bool
    {
        return true;
    }

    public function getEndpointCustomer(): string
    {
        return $this->isHomologacao 
            ? 'https://api-sandbox.asaas.com/v3/customers' 
            : 'https://api.asaas.com/v3/customers';
    }

    public function getPayloadCustomer(array $dadosBoleto): array
    {
        if (empty($dadosBoleto['pagador_numeroInscricao'])) {
            throw new \Exception('O paciente ou convênio associado não possui CPF/CNPJ cadastrado. É obrigatório informar o CPF/CNPJ para gerar cobranças no Asaas.');
        }

        return [
            'name' => $dadosBoleto['pagador_nome'],
            'cpfCnpj' => preg_replace('/[^0-9]/', '', $dadosBoleto['pagador_numeroInscricao']),
        ];
    }

    public function getHeadersCustomer(array $credenciais): array
    {
        return [
            'access_token' => $credenciais['access_token'],
            'Content-Type' => 'application/json'
        ];
    }

    public function getPayloadAuth(array $credenciais): array
    {
        return [];
    }

    public function getPayloadBoleto(array $dadosBoleto): array
    {
        // Para gerar um boleto no Asaas, é OBRIGATÓRIO ter um `customer` (ID do cliente no Asaas).
        // Caso a clínica ainda não tenha o cliente cadastrado lá, seria necessário chamar o 
        // endpoint /v3/customers antes, ou assumir que o ID já vem no $dadosBoleto.
        
        $validBillingTypes = ['UNDEFINED', 'BOLETO', 'CREDIT_CARD', 'PIX'];
        $billingType = strtoupper($dadosBoleto['billingType'] ?? 'BOLETO');
        
        if (!in_array($billingType, $validBillingTypes)) {
            throw new \Exception("Tipo de cobrança (billingType) inválido no Asaas. Permitidos: " . implode(', ', $validBillingTypes));
        }

        $payload = [
            "customer" => $dadosBoleto['customer_id'] ?? '',
            "billingType" => $billingType,
            "value" => (float)$dadosBoleto['valorOriginal'],
            "dueDate" => $dadosBoleto['dataVencimento'],
            "description" => $dadosBoleto['mensagemBloquetoOcorrencia'] ?? 'Emissão de Boleto',
            "daysAfterDueDateToRegistrationCancellation" => $dadosBoleto['diasCancelamento'] ?? null,
            "externalReference" => $dadosBoleto['numeroTituloCliente'] ?? '',
            
            "installmentCount" => $dadosBoleto['installmentCount'] ?? null,
            "totalValue" => $dadosBoleto['totalValue'] ?? null,
            "installmentValue" => $dadosBoleto['installmentValue'] ?? null,

            "discount" => $dadosBoleto['discount'] ?? null,
            "interest" => $dadosBoleto['interest'] ?? null,
            "fine" => $dadosBoleto['fine'] ?? null,
            "postalService" => $dadosBoleto['postalService'] ?? false,
            "split" => $dadosBoleto['split'] ?? null,
            "callback" => $dadosBoleto['callback'] ?? null,
            "pixAutomaticAuthorizationId" => $dadosBoleto['pixAutomaticAuthorizationId'] ?? null
        ];

        return array_filter($payload, function($val) {
            return $val !== null;
        });
    }

    public function getHeadersAuth(array $credenciais): array
    {
        return [];
    }

    public function getHeadersBoleto(string $token, array $credenciais): array
    {
        // O Asaas espera o header access_token contendo a API KEY
        // Aqui usamos a chave client_id ou access_token que vier nas configurações bancárias.
        $apiKey = $credenciais['access_token'] ?? $credenciais['client_id'] ?? $token;

        return [
            'access_token' => $apiKey,
            'User-Agent' => 'IntegraçãoBancaria/1.0.0',
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ];
    }
}
