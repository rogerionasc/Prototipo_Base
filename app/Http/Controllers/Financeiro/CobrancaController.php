<?php

namespace App\Http\Controllers\Financeiro;

use App\Http\Controllers\Controller;
use App\Models\Cobranca;
use App\Models\ContaReceber;
use App\Models\ConfiguracaoBancaria;
use App\Services\Billing\CobrancaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CobrancaController extends Controller
{
    protected CobrancaService $cobrancaService;

    public function __construct(CobrancaService $cobrancaService)
    {
        $this->cobrancaService = $cobrancaService;
    }

    public function webhook(Request $request, $gateway)
    {
        Log::info("Webhook recebido do gateway: {$gateway}", $request->all());

        if (strtolower($gateway) === 'asaas') {
            $tokenEnv = env('ASAAS_WEBHOOK_TOKEN');
            $headerToken = $request->header('asaas-access-token');

            if ($tokenEnv && $headerToken !== $tokenEnv) {
                Log::warning("Webhook Asaas recusado: token inválido");
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $event = $request->input('event');
            $paymentId = $request->input('payment.id');

            if (!$paymentId) {
                return response()->json(['error' => 'Missing payment ID'], 400);
            }

            // Confirmação de recebimento (Boleto, Pix, Cartão)
            if (in_array($event, ['PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED'])) {
                
                DB::transaction(function () use ($paymentId, $request) {
                    $cobranca = Cobranca::where('gateway_id', $paymentId)->first();

                    // Fallback para externalReference caso o gateway_id esteja vazio no banco
                    if (!$cobranca) {
                        $ext = $request->input('payment.externalReference');
                        if ($ext && preg_match('/^CR(\d+)$/i', $ext, $m)) {
                            $cobranca = Cobranca::where('conta_receber_id', $m[1])->first();
                        }
                    }

                    if ($cobranca && $cobranca->status !== 'PAID') {
                        $cobranca->gateway_id = $paymentId; // Atualiza o gateway_id se estava vazio
                        $cobranca->status = 'PAID';
                        $cobranca->data_pagamento = now();
                        $cobranca->save();

                        // Baixar Conta a Receber
                        DB::table('contas_receber')
                            ->where('id', $cobranca->conta_receber_id)
                            ->update(['status' => 'RECEBIDO', 'updated_at' => now()]);

                        $cr = DB::table('contas_receber')->where('id', $cobranca->conta_receber_id)->first();
                        
                        if ($cr) {
                            // Baixar Pagamento gerado
                            DB::table('pagamentos')
                                ->where('faturamento_id', $cr->faturamento_id)
                                ->where('status', 'PENDENTE')
                                ->update([
                                    'status' => 'PAGO',
                                    'data_pagamento' => now(),
                                    'updated_at' => now()
                                ]);

                            // Baixar Faturamento
                            DB::table('faturamentos')
                                ->where('id', $cr->faturamento_id)
                                ->update(['status' => 'RECEBIDO', 'updated_at' => now()]);
                        }
                    }
                });
            }

            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Gateway não implementado'], 404);
    }

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
