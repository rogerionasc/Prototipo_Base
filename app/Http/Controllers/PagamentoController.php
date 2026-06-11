<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Pagamento;
use App\Models\MovimentacaoCaixa;
use App\Models\Caixa;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PagamentoController extends Controller
{
    public function displayPix(Request $request)
    {
        $caixas = \App\Models\Caixa::select('id','descricao','ativo')->orderBy('descricao')->get();
        $selected = (int)$request->query('caixa_id', 0);
        return Inertia::render('Financeiro/Caixa/PixDisplay', [
            'caixas' => $caixas,
            'selectedCaixaId' => $selected > 0 ? $selected : null,
        ]);
    }

    public function currentPix(Request $request)
    {
        $data = $request->validate([
            'caixa_id' => ['required','integer','exists:caixas,id'],
        ]);
        $p = DB::table('pagamentos as p')
            ->leftJoin('faturamentos as f', 'f.id', '=', 'p.faturamento_id')
            ->leftJoin('orcamentos as o', 'o.id', '=', 'f.orcamento_id')
            ->leftJoin('pacientes as pa', 'pa.id', '=', 'f.paciente_id')
            ->select(
                'p.id',
                'o.id as orcamento_id',
                'p.caixa_id',
                'p.valor',
                'p.forma_pagamento',
                'p.status',
                DB::raw("COALESCE(pa.nome,'') AS paciente"),
                DB::raw("COALESCE(o.numero,'') AS numero_orcamento"),
                DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y') AS data_orcamento")
            )
            ->where('p.status', 'PENDENTE')
            ->where('p.forma_pagamento', 'PIX')
            ->where('f.tipo_pagador', 'PARTICULAR')
            ->where('f.status', 'AGUARDANDO_PAGAMENTO')
            ->where('p.caixa_id', (int)$data['caixa_id'])
            ->orderByDesc('p.created_at')
            ->first();
        return response()->json([
            'pagamento' => $p ?: null,
        ]);
    }

    public function startForFaturamento(Request $request, string $id)
    {
        $data = $request->validate([
            'valor' => ['nullable', 'numeric', 'min:0'],
        ]);
        $fatId = (int)$id;
        $fat = DB::table('faturamentos')
            ->select('id', 'tipo_pagador', 'status', 'valor_final', 'valor_cobrado', 'valor_total')
            ->where('id', $fatId)
            ->first();
        if (!$fat) {
            return response()->json(['error' => 'Faturamento não encontrado'], 404);
        }
        $tipo = strtoupper((string)$fat->tipo_pagador);
        if ($tipo !== 'PARTICULAR') {
            return response()->json(['error' => 'Faturamento não é do tipo PARTICULAR'], 422);
        }
        if (strtoupper((string)$fat->status) !== 'AGUARDANDO_PAGAMENTO') {
            return response()->json(['error' => 'Faturamento não está AGUARDANDO_PAGAMENTO'], 422);
        }

        $existing = Pagamento::where('faturamento_id', $fatId)
            ->where('status', 'PENDENTE')
            ->orderByDesc('id')
            ->first();
        if ($existing) {
            return response()->json(['pagamento_id' => $existing->id]);
        }

        $valor = $data['valor'] ?? null;
        if ($valor === null) {
            $valor = (float)($fat->valor_final ?? $fat->valor_cobrado ?? $fat->valor_total ?? 0);
        }
        $pag = Pagamento::create([
            'faturamento_id' => $fatId,
            'caixa_id' => null,
            'movimentacao_id' => null,
            'valor' => (float)$valor,
            'forma_pagamento' => null,
            'data_pagamento' => null,
            'status' => 'PENDENTE',
        ]);
        return response()->json(['pagamento_id' => $pag->id]);
    }

    public function preparePix(Request $request, string $id)
    {
        $data = $request->validate([
            'caixa_id' => ['required','integer','exists:caixas,id'],
        ]);
        $pag = Pagamento::findOrFail($id);
        if (strtoupper((string)$pag->status) === 'CONFIRMADO') {
            return back()->withErrors([
                'pagamento' => 'Pagamento já confirmado.',
            ], 422);
        }
        $pag->update([
            'caixa_id' => (int)$data['caixa_id'],
            'forma_pagamento' => 'PIX',
            'status' => 'PENDENTE',
        ]);
        return back()->with('success', 'Pagamento preparado para PIX no caixa selecionado');
    }

    public function cancelPix(Request $request, string $id)
    {
        $pag = Pagamento::findOrFail($id);
        if (strtoupper((string)$pag->status) === 'CONFIRMADO') {
            return back()->withErrors([
                'pagamento' => 'Pagamento já confirmado.',
            ], 422);
        }
        if (strtoupper((string)($pag->forma_pagamento ?? '')) !== 'PIX' || strtoupper((string)($pag->status ?? '')) !== 'PENDENTE') {
            return back()->withErrors([
                'pagamento' => 'Pagamento não está aguardando confirmação PIX.',
            ], 422);
        }
        $pag->update([
            'forma_pagamento' => null,
            'status' => 'PENDENTE',
        ]);
        return back()->with('success', 'Pagamento cancelado. Selecione outra forma de pagamento.');
    }

    public function mpCheckout(Request $request)
    {
        $data = $request->validate([
            'pagamento_id' => ['required','integer','exists:pagamentos,id'],
        ]);
        $pag = Pagamento::findOrFail((int)$data['pagamento_id']);
        if (strtoupper((string)$pag->status) === 'CONFIRMADO') {
            return response()->json(['error' => 'Pagamento já confirmado'], 422);
        }
        if (($pag->forma_pagamento ?? '') !== 'PIX') {
            return response()->json(['error' => 'Forma de pagamento inválida'], 422);
        }
        $token = env('MERCADO_PAGO_ACCESS_TOKEN');
        if (!$token) {
            return response()->json(['error' => 'Token do Mercado Pago não configurado'], 422);
        }
        $payer = DB::table('faturamentos as f')
            ->leftJoin('pacientes as pa', 'pa.id', '=', 'f.paciente_id')
            ->select('pa.id as paciente_id', 'pa.email')
            ->where('f.id', (int)($pag->faturamento_id ?? 0))
            ->first();
        $payerEmail = trim((string)($payer->email ?? ''));
        if ($payerEmail === '' || !filter_var($payerEmail, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Paciente sem e-mail válido cadastrado'], 422);
        }
        $valor = (float)($pag->valor ?? 0);
        $url = 'https://api.mercadopago.com/v1/payments';
        $baseUrl = env('MP_WEBHOOK_URL');
        $notificationUrl = $baseUrl ? rtrim((string)$baseUrl, '/') . '/pix/mp/webhook' : null;
        $idempotency = 'pix-' . $pag->id . '-' . (string)microtime(true);
        $body = [
            'transaction_amount' => $valor,
            'description' => 'Pagamento ' . $pag->id,
            'payment_method_id' => 'pix',
            'external_reference' => 'pag:' . $pag->id,
            'payer' => [
                'email' => $payerEmail,
            ],
        ];
        if ($notificationUrl) {
            $body['notification_url'] = $notificationUrl;
        }
        $resp = Http::withToken($token)->withHeaders(['X-Idempotency-Key' => $idempotency])->post($url, $body);
        if (!$resp->successful()) {
            try { Log::warning('MP checkout error', ['details' => $resp->json()]); } catch (\Throwable $e) {}
            return response()->json(['error' => 'Falha ao criar pagamento no Mercado Pago', 'details' => $resp->json()], 422);
        }
        $data = $resp->json();
        try { Log::info('MP checkout ok', ['payment_id' => $data['id'] ?? null, 'status' => $data['status'] ?? null]); } catch (\Throwable $e) {}
        $poi = $data['point_of_interaction']['transaction_data'] ?? [];
        return response()->json([
            'qr_code' => $poi['qr_code'] ?? null,
            'qr_code_base64' => $poi['qr_code_base64'] ?? null,
            'payment_id' => $data['id'] ?? null,
            'status' => $data['status'] ?? null,
        ]);
    }

    public function mpStatusCheck(Request $request)
    {
        $data = $request->validate([
            'pagamento_id' => ['required','integer','exists:pagamentos,id'],
        ]);
        $simulate = filter_var($request->input('simulate', false), FILTER_VALIDATE_BOOLEAN);
        $mpPaymentId = $request->input('mp_payment_id');
        $pag = Pagamento::findOrFail((int)$data['pagamento_id']);
        if (strtoupper((string)$pag->status) === 'CONFIRMADO') {
            return response()->json(['success' => true, 'message' => 'Pagamento já confirmado']);
        }
        if (($pag->forma_pagamento ?? '') !== 'PIX') {
            return response()->json(['error' => 'Forma de pagamento inválida'], 422);
        }
        if ($simulate && filter_var(env('PIX_LOCAL_SIMULATION', false), FILTER_VALIDATE_BOOLEAN)) {
            $caixaId = (int)($pag->caixa_id ?? 0);
            if (!$caixaId) {
                return response()->json(['error' => 'Pagamento não vinculado a caixa'], 422);
            }
            $caixa = Caixa::select('id','ativo','bloquear_receber')->find($caixaId);
            if (!$caixa || !$caixa->ativo || $caixa->bloquear_receber) {
                return response()->json(['error' => 'Caixa indisponível'], 422);
            }
            $mov = MovimentacaoCaixa::where('caixa_id', $caixaId)
                ->whereNull('fechado_em')
                ->first();
            if (!$mov) {
                return response()->json(['error' => 'Caixa sem movimentação aberta'], 422);
            }
            DB::transaction(function () use ($pag, $mov) {
                $pag->update([
                    'movimentacao_id' => $mov->id,
                    'data_pagamento' => Carbon::today()->format('Y-m-d'),
                    'status' => 'CONFIRMADO',
                ]);
                $totEntradas = (float)($mov->total_entradas ?? 0) + (float)($pag->valor ?? 0);
                $totSaidas = (float)($mov->total_saidas ?? 0);
                $saldoInicial = (float)($mov->saldo_caixa ?? 0);
                $saldoMov = $saldoInicial + $totEntradas - $totSaidas;
                $mov->update([
                    'total_entradas' => $totEntradas,
                    'saldo_movimento' => $saldoMov,
                ]);
                $this->syncFaturamentoFromPagamento($pag);
            });
            return response()->json(['success' => true, 'simulated' => true]);
        }
        $token = env('MERCADO_PAGO_ACCESS_TOKEN');
        if (!$token) {
            return response()->json(['error' => 'Token do Mercado Pago não configurado'], 422);
        }
        if ($mpPaymentId) {
            $resp = Http::withToken($token)->get('https://api.mercadopago.com/v1/payments/' . $mpPaymentId);
            if (!$resp->successful()) {
                try { Log::info('MP status by id failed', ['mp_payment_id' => $mpPaymentId, 'details' => $resp->json()]); } catch (\Throwable $e) {}
            } else {
                $p = $resp->json();
                $status = $p['status'] ?? null;
                $method = $p['payment_method_id'] ?? null;
                $amount = (float)($p['transaction_amount'] ?? 0);
                $ext = $p['external_reference'] ?? '';
                if (preg_match('/^pag:(\d{1,20})$/i', (string)$ext)) {
                    if ($status === 'approved' && $method === 'pix') {
                        if (number_format((float)$pag->valor, 2, '.', '') !== number_format($amount, 2, '.', '')) {
                            return response()->json(['error' => 'Valor divergente'], 422);
                        }
                        $caixaId = (int)($pag->caixa_id ?? 0);
                        if (!$caixaId) {
                            return response()->json(['error' => 'Pagamento não vinculado a caixa'], 422);
                        }
                        $caixa = Caixa::select('id','ativo','bloquear_receber')->find($caixaId);
                        if (!$caixa || !$caixa->ativo || $caixa->bloquear_receber) {
                            return response()->json(['error' => 'Caixa indisponível'], 422);
                        }
                        $mov = MovimentacaoCaixa::where('caixa_id', $caixaId)
                            ->whereNull('fechado_em')
                            ->first();
                        if (!$mov) {
                            return response()->json(['error' => 'Caixa sem movimentação aberta'], 422);
                        }
                        DB::transaction(function () use ($pag, $mov) {
                            $pag->update([
                                'movimentacao_id' => $mov->id,
                                'data_pagamento' => Carbon::today()->format('Y-m-d'),
                                'status' => 'CONFIRMADO',
                            ]);
                            $totEntradas = (float)($mov->total_entradas ?? 0) + (float)($pag->valor ?? 0);
                            $totSaidas = (float)($mov->total_saidas ?? 0);
                            $saldoInicial = (float)($mov->saldo_caixa ?? 0);
                            $saldoMov = $saldoInicial + $totEntradas - $totSaidas;
                            $mov->update([
                                'total_entradas' => $totEntradas,
                                'saldo_movimento' => $saldoMov,
                            ]);
                            $this->syncFaturamentoFromPagamento($pag);
                        });
                        try { Log::info('MP status approved by id', ['mp_payment_id' => $mpPaymentId, 'pagamento_id' => $pag->id]); } catch (\Throwable $e) {}
                        return response()->json(['success' => true]);
                    } else {
                        try { Log::info('MP status not approved by id', ['status' => $status, 'method' => $method]); } catch (\Throwable $e) {}
                        return response()->json(['ignored' => true]);
                    }
                } else {
                    try { Log::info('MP status id external_reference mismatch', ['ext' => $ext, 'expected' => 'pag:' . $pag->id]); } catch (\Throwable $e) {}
                }
            }
        }
        $query = [
            'external_reference' => 'pag:' . $pag->id,
            'sort' => 'date_created',
            'order' => 'desc',
            'limit' => 1,
        ];
        $resp = Http::withToken($token)->get('https://api.mercadopago.com/v1/payments/search', $query);
        if (!$resp->successful()) {
            try { Log::info('MP search failed', ['query' => $query, 'details' => $resp->json()]); } catch (\Throwable $e) {}
            return response()->json(['ignored' => true]);
        }
        $j = $resp->json();
        $results = $j['results'] ?? [];
        if (!$results || count($results) === 0) {
            try { Log::info('MP search empty', ['external_reference' => 'pag:' . $pag->id]); } catch (\Throwable $e) {}
            return response()->json(['ignored' => true]);
        }
        $p = $results[0];
        $status = $p['status'] ?? null;
        $method = $p['payment_method_id'] ?? null;
        $amount = (float)($p['transaction_amount'] ?? 0);
        if (!($status === 'approved' && $method === 'pix')) {
            try { Log::info('MP search not approved', ['status' => $status, 'method' => $method]); } catch (\Throwable $e) {}
            return response()->json(['ignored' => true]);
        }
        if (number_format((float)$pag->valor, 2, '.', '') !== number_format($amount, 2, '.', '')) {
            return response()->json(['error' => 'Valor divergente'], 422);
        }
        $caixaId = (int)($pag->caixa_id ?? 0);
        if (!$caixaId) {
            return response()->json(['error' => 'Pagamento não vinculado a caixa'], 422);
        }
        $caixa = Caixa::select('id','ativo','bloquear_receber')->find($caixaId);
        if (!$caixa || !$caixa->ativo || $caixa->bloquear_receber) {
            return response()->json(['error' => 'Caixa indisponível'], 422);
        }
        $mov = MovimentacaoCaixa::where('caixa_id', $caixaId)
            ->whereNull('fechado_em')
            ->first();
        if (!$mov) {
            return response()->json(['error' => 'Caixa sem movimentação aberta'], 422);
        }
        DB::transaction(function () use ($pag, $mov) {
            $pag->update([
                'movimentacao_id' => $mov->id,
                'data_pagamento' => Carbon::today()->format('Y-m-d'),
                'status' => 'CONFIRMADO',
            ]);
            $totEntradas = (float)($mov->total_entradas ?? 0) + (float)($pag->valor ?? 0);
            $totSaidas = (float)($mov->total_saidas ?? 0);
            $saldoInicial = (float)($mov->saldo_caixa ?? 0);
            $saldoMov = $saldoInicial + $totEntradas - $totSaidas;
            $mov->update([
                'total_entradas' => $totEntradas,
                'saldo_movimento' => $saldoMov,
            ]);
            $this->syncFaturamentoFromPagamento($pag);
        });
        return response()->json(['success' => true]);
    }

    public function mpWebhook(Request $request)
    {
        $token = env('MERCADO_PAGO_ACCESS_TOKEN');
        $type = $request->input('type') ?? $request->input('action');
        $paymentId = $request->input('data.id') ?? $request->input('id');
        if ($type !== 'payment' || !$paymentId) {
            return response()->json(['ignored' => true]);
        }
        if (!$token) {
            return response()->json(['error' => 'Token do Mercado Pago não configurado'], 422);
        }
        $resp = Http::withToken($token)->get('https://api.mercadopago.com/v1/payments/' . $paymentId);
        if (!$resp->successful()) {
            return response()->json(['error' => 'Falha ao consultar pagamento'], 422);
        }
        $p = $resp->json();
        $status = $p['status'] ?? null;
        $method = $p['payment_method_id'] ?? null;
        $amount = (float)($p['transaction_amount'] ?? 0);
        $ext = $p['external_reference'] ?? '';
        if (!($status === 'approved' && $method === 'pix')) {
            return response()->json(['ignored' => true]);
        }
        $id = null;
        if (preg_match('/^pag:(\d{1,20})$/i', $ext, $m)) {
            $id = (int)$m[1];
        }
        $pag = $id ? Pagamento::find($id) : null;
        if (!$pag) {
            return response()->json(['error' => 'Pagamento não localizado'], 404);
        }
        if (number_format((float)$pag->valor, 2, '.', '') !== number_format($amount, 2, '.', '')) {
            return response()->json(['error' => 'Valor divergente'], 422);
        }
        $caixaId = (int)($pag->caixa_id ?? 0);
        if (!$caixaId) {
            return response()->json(['error' => 'Pagamento não vinculado a caixa'], 422);
        }
        $caixa = Caixa::select('id','ativo','bloquear_receber')->find($caixaId);
        if (!$caixa || !$caixa->ativo || $caixa->bloquear_receber) {
            return response()->json(['error' => 'Caixa indisponível'], 422);
        }
        $mov = MovimentacaoCaixa::where('caixa_id', $caixaId)
            ->whereNull('fechado_em')
            ->first();
        if (!$mov) {
            return response()->json(['error' => 'Caixa sem movimentação aberta'], 422);
        }
        DB::transaction(function () use ($pag, $mov) {
            $pag->update([
                'movimentacao_id' => $mov->id,
                'data_pagamento' => Carbon::today()->format('Y-m-d'),
                'status' => 'CONFIRMADO',
            ]);
            $totEntradas = (float)($mov->total_entradas ?? 0) + (float)($pag->valor ?? 0);
            $totSaidas = (float)($mov->total_saidas ?? 0);
            $saldoInicial = (float)($mov->saldo_caixa ?? 0);
            $saldoMov = $saldoInicial + $totEntradas - $totSaidas;
            $mov->update([
                'total_entradas' => $totEntradas,
                'saldo_movimento' => $saldoMov,
            ]);
            $this->syncFaturamentoFromPagamento($pag);
        });
        return response()->json(['success' => true]);
    }

    public function pixWebhook(Request $request)
    {
        $txid = null;
        $valor = null;
        $chave = null;
        $e2eid = null;
        $raw = $request->getContent();
        $ct = strtolower((string)$request->header('Content-Type'));
        try {
            if (str_contains($ct, 'xml') || preg_match('/<\?xml|<\/?\w+>/', $raw)) {
                $xml = @simplexml_load_string($raw);
                if ($xml) {
                    $arr = json_decode(json_encode($xml), true);
                    $txid = $arr['txid'] ?? $arr['TxId'] ?? $arr['TXID'] ?? null;
                    $valor = $arr['valor'] ?? $arr['Valor'] ?? $arr['amount'] ?? null;
                    $chave = $arr['chave'] ?? $arr['Chave'] ?? $arr['key'] ?? null;
                    $e2eid = $arr['e2eid'] ?? $arr['E2EId'] ?? $arr['E2EID'] ?? null;
                }
            } else {
                $txid = $request->input('txid');
                $valor = $request->input('valor');
                $chave = $request->input('chave');
                $e2eid = $request->input('e2eid');
            }
            // query params fallback
            $txid = $txid ?? $request->query('txid');
            $valor = $valor ?? $request->query('valor');
            $chave = $chave ?? $request->query('chave');
            $e2eid = $e2eid ?? $request->query('e2eid');
        } catch (\Throwable $e) {
            Log::warning('PIX webhook parse error', ['error' => $e->getMessage()]);
        }
        if (!$txid || $valor === null) {
            return response()->json(['error' => 'Dados insuficientes'], 422);
        }
        $txid = strtoupper((string)$txid);
        $valor = (float)$valor;
        $id = null;
        if (preg_match('/^PAG-?(\d{1,20})$/', $txid, $m)) {
            $id = (int)$m[1];
        }
        $pag = $id ? Pagamento::find($id) : null;
        if (!$pag) {
            // Fallback: localizar por valor e pendência PIX
            $pag = Pagamento::where('status', 'PENDENTE')
                ->where('forma_pagamento', 'PIX')
                ->whereRaw('CAST(valor AS DECIMAL(10,2)) = ?', [number_format($valor, 2, '.', '')])
                ->orderByDesc('created_at')
                ->first();
            if (!$pag) {
                return response()->json(['error' => 'Pagamento não localizado'], 404);
            }
        }
        if (strtoupper((string)$pag->status) === 'CONFIRMADO') {
            return response()->json(['success' => true, 'message' => 'Pagamento já confirmado'], 200);
        }
        if ($pag->forma_pagamento !== 'PIX') {
            return response()->json(['error' => 'Forma de pagamento incompatível'], 422);
        }
        if ((float)$pag->valor !== $valor) {
            return response()->json(['error' => 'Valor divergente'], 422);
        }
        $caixaId = (int)($pag->caixa_id ?? 0);
        if (!$caixaId) {
            return response()->json(['error' => 'Pagamento não vinculado a caixa'], 422);
        }
        $caixa = Caixa::select('id','ativo','bloquear_receber')->find($caixaId);
        if (!$caixa || !$caixa->ativo || $caixa->bloquear_receber) {
            return response()->json(['error' => 'Caixa indisponível'], 422);
        }
        $mov = MovimentacaoCaixa::where('caixa_id', $caixaId)
            ->whereNull('fechado_em')
            ->first();
        if (!$mov) {
            return response()->json(['error' => 'Caixa sem movimentação aberta'], 422);
        }
        DB::transaction(function () use ($pag, $mov) {
            $pag->update([
                'movimentacao_id' => $mov->id,
                'data_pagamento' => Carbon::today()->format('Y-m-d'),
                'status' => 'CONFIRMADO',
            ]);
            $totEntradas = (float)($mov->total_entradas ?? 0) + (float)($pag->valor ?? 0);
            $totSaidas = (float)($mov->total_saidas ?? 0);
            $saldoInicial = (float)($mov->saldo_caixa ?? 0);
            $saldoMov = $saldoInicial + $totEntradas - $totSaidas;
            $mov->update([
                'total_entradas' => $totEntradas,
                'saldo_movimento' => $saldoMov,
            ]);
            $this->syncFaturamentoFromPagamento($pag);
        });
        return response()->json(['success' => true]);
    }

    public function confirm(Request $request, string $id)
    {
        $data = $request->validate([
            'caixa_id' => ['required','integer','exists:caixas,id'],
            'forma_pagamento' => ['nullable','string'],
        ]);
        $pag = Pagamento::findOrFail($id);
        if (strtoupper((string)$pag->status) === 'CONFIRMADO') {
            return back()->with('success', 'Pagamento já confirmado');
        }
        // Verificar disponibilidade do caixa
        $caixa = Caixa::select('id','ativo','bloquear_receber')->findOrFail((int)$data['caixa_id']);
        if (!$caixa->ativo || $caixa->bloquear_receber) {
            return back()->with('error', 'Caixa indisponível para receber');
        }
        // Verificar movimentação aberta para o caixa
        $mov = MovimentacaoCaixa::where('caixa_id', (int)$data['caixa_id'])
            ->whereNull('fechado_em')
            ->first();
        if (!$mov) {
            return back()->with('error', 'Caixa sem movimentação aberta');
        }
        DB::transaction(function () use ($pag, $data, $mov) {
            // Atualizar movimentação aberta do dia para o caixa
            $pag->update([
                'caixa_id' => (int)$data['caixa_id'],
                'movimentacao_id' => $mov->id,
                'forma_pagamento' => $data['forma_pagamento'] ?? $pag->forma_pagamento,
                'data_pagamento' => Carbon::today()->format('Y-m-d'),
                'status' => 'CONFIRMADO',
            ]);
            $totEntradas = (float)($mov->total_entradas ?? 0) + (float)($pag->valor ?? 0);
            $totSaidas = (float)($mov->total_saidas ?? 0);
            $saldoInicial = (float)($mov->saldo_caixa ?? 0);
            $saldoMov = $saldoInicial + $totEntradas - $totSaidas;
            $mov->update([
                'total_entradas' => $totEntradas,
                'saldo_movimento' => $saldoMov,
            ]);
            $this->syncFaturamentoFromPagamento($pag);
        });
        return back()->with('success', 'Pagamento confirmado e lançado no caixa');
    }

    public function refuse(Request $request, string $id)
    {
        $data = $request->validate([
            'recusa_justificativa' => ['required','string','max:1000'],
        ]);
        $pag = Pagamento::findOrFail($id);
        if (strtoupper((string)$pag->status) === 'CONFIRMADO') {
            return back()->with('error', 'Pagamento já confirmado, não é possível recusar.');
        }
        if (strtoupper((string)$pag->status) === 'RECUSADO') {
            return back()->with('success', 'Pagamento já recusado');
        }
        $pag->update([
            'status' => 'RECUSADO',
            'caixa_id' => $pag->caixa_id ?? null,
            'movimentacao_id' => $pag->movimentacao_id ?? null,
            'forma_pagamento' => $pag->forma_pagamento ?? null,
            'data_pagamento' => null,
            'recusa_justificativa' => $data['recusa_justificativa'] ?? null,
            'recusado_por' => $request->user()?->id,
        ]);
        return back()->with('success', 'Pagamento recusado');
    }

    public function recusados()
    {
        $pagamentosRecusados = DB::table('pagamentos as p')
            ->leftJoin('faturamentos as f', 'f.id', '=', 'p.faturamento_id')
            ->leftJoin('orcamentos as o', 'o.id', '=', 'f.orcamento_id')
            ->leftJoin('pacientes as pa', 'pa.id', '=', 'f.paciente_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.recusado_por')
            ->select(
                'p.id',
                'o.id as orcamento_id',
                'p.valor',
                'p.forma_pagamento',
                'p.status',
                'p.recusa_justificativa',
                DB::raw("DATE_FORMAT(p.updated_at, '%d-%m-%Y %H:%i') AS data_recusa"),
                DB::raw("COALESCE(pa.nome,'') AS paciente"),
                DB::raw("COALESCE(o.numero,'') AS numero_orcamento"),
                DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y') AS data_orcamento"),
                DB::raw("COALESCE(CONCAT(u.nome, ' ', u.sobrenome),'') AS recusado_por_nome")
            )
            ->where('p.status', 'RECUSADO')
            ->where('f.tipo_pagador', 'PARTICULAR')
            ->orderByDesc('p.updated_at')
            ->limit(100)
            ->get();
        return Inertia::render('Financeiro/Caixa/Recusados', [
            'pagamentosRecusados' => $pagamentosRecusados,
        ]);
    }

    public function unrefuse(Request $request, string $id)
    {
        $pag = Pagamento::findOrFail($id);
        if (strtoupper((string)$pag->status) === 'CONFIRMADO') {
            return back()->with('error', 'Pagamento confirmado não pode ser alterado.');
        }
        if (strtoupper((string)$pag->status) !== 'RECUSADO') {
            return back()->with('success', 'Pagamento não está recusado');
        }
        $pag->update([
            'status' => 'PENDENTE',
            'data_pagamento' => null,
            'recusa_justificativa' => null,
            'recusado_por' => null,
        ]);
        return back()->with('success', 'Recusa cancelada. Pagamento retornou para pendentes');
    }

    private function syncFaturamentoFromPagamento(Pagamento $pag): void
    {
        $fatId = (int)($pag->faturamento_id ?? 0);
        if (!$fatId) {
            return;
        }

        $fat = DB::table('faturamentos')
            ->select('id', 'tipo_pagador', 'status', 'valor_final', 'valor_cobrado', 'valor_aprovado')
            ->where('id', $fatId)
            ->first();
        if (!$fat) {
            return;
        }
        if (strtoupper((string)$fat->status) === 'CANCELADO') {
            return;
        }

        $tipo = strtoupper((string)($fat->tipo_pagador ?? ''));
        $valorFinal = (float)($fat->valor_final ?? 0);
        $valorCobrado = (float)($fat->valor_cobrado ?? 0);
        $valorAprovado = (float)($fat->valor_aprovado ?? 0);
        $alvo = $tipo === 'CONVENIO'
            ? (($valorAprovado > 0 ? $valorAprovado : ($valorCobrado > 0 ? $valorCobrado : $valorFinal)))
            : $valorFinal;
        $recebido = (float)DB::table('pagamentos')->where('faturamento_id', $fatId)->where('status', 'CONFIRMADO')->sum('valor');
        $quitado = ($alvo > 0 && ($recebido + 0.00001) >= $alvo);

        $novoStatusCR = $quitado ? 'RECEBIDO' : 'ABERTO';
        $crStatusAtual = (string)DB::table('contas_receber')->where('faturamento_id', $fatId)->value('status');
        if (strtoupper($crStatusAtual) !== 'CANCELADO') {
            DB::table('contas_receber')->where('faturamento_id', $fatId)->update([
                'status' => $novoStatusCR,
                'updated_at' => now(),
            ]);
        }

        if ($tipo === 'PARTICULAR') {
            $novoStatusFat = $quitado ? 'PAGO' : 'AGUARDANDO_PAGAMENTO';
            DB::table('faturamentos')->where('id', $fatId)->update([
                'status' => $novoStatusFat,
                'updated_at' => now(),
            ]);
        } elseif ($tipo === 'CONVENIO') {
            if ($quitado) {
                DB::table('faturamentos')->where('id', $fatId)->update([
                    'status' => 'RECEBIDO',
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
