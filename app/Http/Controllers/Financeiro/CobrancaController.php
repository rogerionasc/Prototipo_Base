<?php

namespace App\Http\Controllers\Financeiro;

use App\Http\Controllers\Controller;
use App\Models\Cobranca;
use App\Models\ContaReceber;
use App\Models\ConfiguracaoBancaria;
use App\Services\Billing\CobrancaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CobrancaController extends Controller
{
    protected CobrancaService $cobrancaService;

    public function __construct(CobrancaService $cobrancaService)
    {
        $this->cobrancaService = $cobrancaService;
    }

    public function webhook(Request $request, $gateway) {}

    public function gerarToken(Request $request)
    {
        $request->validate([
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'scope' => 'required|string',
            'grant_type' => 'required|string'
        ]);

        $integracao = \App\Services\Bancario\IntegracaoFactory::make('bb', true);
        $gerenciador = new \App\Services\Bancario\GerenciadorCobranca($integracao);

        $resultado = $gerenciador->gerarToken($request->only(['client_id', 'client_secret', 'scope', 'grant_type']));

        if ($resultado['success']) {
            return response()->json($resultado['data']);
        }

        return response()->json([
            'error' => $resultado['error'],
            'raw_body' => $resultado['raw_body'] ?? '',
            'details' => $resultado['data'] ?? []
        ], $resultado['status']);
    }

    public function gerarBoleto(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'app_key' => 'required|string',
            'numeroConvenio' => 'required|numeric',
            'numeroCarteira' => 'required|numeric',
            'numeroVariacaoCarteira' => 'required|numeric',
            'dataVencimento' => 'required|date_format:d.m.Y',
            'valorOriginal' => 'required|numeric',
            'pagador_nome' => 'required|string',
            'pagador_numeroInscricao' => 'required|string'
        ]);

        $integracao = \App\Services\Bancario\IntegracaoFactory::make('bb', true);
        $gerenciador = new \App\Services\Bancario\GerenciadorCobranca($integracao);

        $resultado = $gerenciador->registrarBoleto(
            $request->all(),
            $request->token,
            ['app_key' => $request->app_key]
        );

        if ($resultado['success']) {
            return response()->json($resultado['data']);
        }

        return response()->json([
            'error' => $resultado['error'],
            'status' => $resultado['status'],
            'raw_body' => $resultado['raw_body'] ?? '',
            'details' => $resultado['data'] ?? []
        ], $resultado['status']);
    }
}
