<?php

namespace App\Services\Bancario;

interface IntegracaoBancoInterface
{
    public function getEndpointAuth(): string;
    public function getEndpointBoleto(): string;
    
    // Métodos para provedores que exigem criação de Customer (ex: Asaas)
    public function exigeCustomerPreviamente(): bool;
    public function getEndpointCustomer(): string;
    public function getPayloadCustomer(array $dadosBoleto): array;
    public function getHeadersCustomer(array $credenciais): array;

    public function getPayloadAuth(array $credenciais): array;
    public function getPayloadBoleto(array $dadosBoleto): array;
    public function getHeadersAuth(array $credenciais): array;
    public function getHeadersBoleto(string $token, array $credenciais): array;
}
