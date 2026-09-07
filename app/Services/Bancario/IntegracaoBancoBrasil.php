<?php

namespace App\Services\Bancario;

class IntegracaoBancoBrasil implements IntegracaoBancoInterface
{
    protected $isHomologacao = true;

    public function __construct(bool $isHomologacao = true)
    {
        $this->isHomologacao = $isHomologacao;
    }

    public function getEndpointAuth(): string
    {
        return $this->isHomologacao
            ? 'https://oauth.hm.bb.com.br/oauth/token'
            : 'https://oauth.bb.com.br/oauth/token';
    }

    public function getEndpointBoleto(): string
    {
        // O gw-dev-app-key normalmente deve ir na query string, o GerenciadorCobranca pode anexar isso ou podemos retornar com ele.
        // Como o BB exige o app_key na URL de cobranca, vamos retornar a URL base e adicionar os params na chamada.
        return $this->isHomologacao
            ? 'https://api.hm.bb.com.br/cobrancas/v2/boletos'
            : 'https://api.bb.com.br/cobrancas/v2/boletos';
    }

    public function exigeCustomerPreviamente(): bool
    {
        return false;
    }

    public function getEndpointCustomer(): string
    {
        return '';
    }

    public function getPayloadCustomer(array $dadosBoleto): array
    {
        return [];
    }

    public function getHeadersCustomer(array $credenciais): array
    {
        return [];
    }

    public function getPayloadAuth(array $credenciais): array
    {
        return [
            'grant_type' => $credenciais['grant_type'] ?? 'client_credentials',
            'scope' => $credenciais['scope'] ?? 'cobrancas.boletos-info cobrancas.boletos-requisicao'
        ];
    }

    public function getPayloadBoleto(array $dadosBoleto): array
    {
        // Tratamento do Nosso Número (numeroTituloCliente) que o BB exige 20 digitos para convenios de 7 posições
        $numeroConvenio = $dadosBoleto['numeroConvenio'] ?? '';

        // Aqui mantemos a mesma lógica que estava no controller
        $numeroTituloCliente = "000" . substr(str_pad($numeroConvenio, 7, '0', STR_PAD_LEFT), 0, 7) . str_pad((string)rand(1, 999999999), 10, '0', STR_PAD_LEFT);

        return [
            "numeroConvenio" => (int)$numeroConvenio,
            "numeroCarteira" => (int)($dadosBoleto['numeroCarteira']), //17
            "numeroVariacaoCarteira" => (int)($dadosBoleto['numeroVariacaoCarteira']), //35
            "codigoModalidade" => (int)($dadosBoleto['codigoModalidade'] ?? 1),
            "dataEmissao" => $dadosBoleto['dataEmissao'] ?? date('d.m.Y'),
            "dataVencimento" => $dadosBoleto['dataVencimento'],
            "valorOriginal" => (float)$dadosBoleto['valorOriginal'],
            "valorAbatimento" => (float)($dadosBoleto['valorAbatimento'] ?? 0.0),
            "quantidadeDiasProtesto" => (int)($dadosBoleto['quantidadeDiasProtesto'] ?? 0),
            "quantidadeDiasNegativacao" => (int)($dadosBoleto['quantidadeDiasNegativacao'] ?? 0),
            "orgaoNegativador" => (int)($dadosBoleto['orgaoNegativador'] ?? 0),
            "indicadorAceiteTituloVencido" => $dadosBoleto['indicadorAceiteTituloVencido'] ?? "S",
            "numeroDiasLimiteRecebimento" => (int)($dadosBoleto['numeroDiasLimiteRecebimento'] ?? 30),
            "codigoAceite" => $dadosBoleto['codigoAceite'] ?? "A",
            "codigoTipoTitulo" => (int)($dadosBoleto['codigoTipoTitulo'] ?? 2),
            "descricaoTipoTitulo" => $dadosBoleto['descricaoTipoTitulo'], //Duplicata Mercantil
            "indicadorPermissaoRecebimentoParcial" => $dadosBoleto['indicadorPermissaoRecebimentoParcial'] ?? "N",
            "numeroTituloBeneficiario" => $dadosBoleto['numeroTituloBeneficiario'], //12345678
            "campoUtilizacaoBeneficiario" => $dadosBoleto['campoUtilizacaoBeneficiario'], //LOTE000125CONVENIO
            "numeroTituloCliente" => $numeroTituloCliente,
            "mensagemBloquetoOcorrencia" => $dadosBoleto['mensagemBloquetoOcorrencia'],
            "desconto" => [
                "tipo" => (int)($dadosBoleto['desconto_tipo'] ?? 0)
            ],
            "segundoDesconto" => [
                "porcentagem" => (float)($dadosBoleto['segundoDesconto_porcentagem'] ?? 0.0),
                "valor" => (float)($dadosBoleto['segundoDesconto_valor'] ?? 0.0)
            ],
            "terceiroDesconto" => [
                "porcentagem" => (float)($dadosBoleto['terceiroDesconto_porcentagem'] ?? 0.0),
                "valor" => (float)($dadosBoleto['terceiroDesconto_valor'] ?? 0.0)
            ],
            "jurosMora" => [
                "tipo" => (int)($dadosBoleto['jurosMora_tipo'] ?? 0)
            ],
            "multa" => [
                "tipo" => (int)($dadosBoleto['multa_tipo'] ?? 0)
            ],
            "pagador" => [
                "tipoInscricao" => strlen(preg_replace('/[^0-9]/', '', $dadosBoleto['pagador_numeroInscricao'] ?? '')) > 11 ? 2 : 1,
                "numeroInscricao" => (int)preg_replace('/[^0-9]/', '', $dadosBoleto['pagador_numeroInscricao'] ?? '0'),
                "nome" => substr($dadosBoleto['pagador_nome'], 0, 30),
                "endereco" => substr($dadosBoleto['pagador_endereco'], 0, 100),
                "cep" => (int)preg_replace('/[^0-9]/', '', $dadosBoleto['pagador_cep']),
                "cidade" => substr($dadosBoleto['pagador_cidade'], 0, 50),
                "bairro" => substr($dadosBoleto['pagador_bairro'], 0, 30),
                "uf" => substr($dadosBoleto['pagador_uf'], 0, 2),
                "telefone" => preg_replace('/[^0-9]/', '', $dadosBoleto['pagador_telefone']),
                "email" => substr($dadosBoleto['pagador_email'], 0, 50)
            ],
            "indicadorPix" => $dadosBoleto['indicadorPix'] ?? "S"
        ];
    }

    public function getHeadersAuth(array $credenciais): array
    {
        return [
            'Authorization' => 'Basic ' . base64_encode(($credenciais['client_id'] ?? '') . ':' . ($credenciais['client_secret'] ?? ''))
        ];
    }

    public function getHeadersBoleto(string $token, array $credenciais): array
    {
        return [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
    }
}
