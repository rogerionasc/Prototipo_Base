<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Pagamento;

class ContasReceberController extends Controller
{
    public function index()
    {
        $rows = DB::table('contas_receber as cr')
            ->leftJoin('faturamentos as f', 'f.id', '=', 'cr.faturamento_id')
            ->leftJoin('pacientes as p', 'p.id', '=', 'cr.paciente_id')
            ->leftJoin('convenios as c', 'c.id', '=', 'cr.convenio_id')
            ->select(
                'cr.faturamento_id as id',
                'cr.id as conta_id',
                'cr.faturamento_id',
                'cr.paciente_id',
                'cr.convenio_id',
                DB::raw("COALESCE(p.nome,'') AS paciente"),
                DB::raw("COALESCE((SELECT proc.nome FROM pagamentos pag JOIN agendamentos a ON a.id = pag.agendamento_id JOIN procedimentos proc ON proc.id = a.procedimento_id WHERE pag.faturamento_id = cr.faturamento_id LIMIT 1), '') AS procedimento"),
                DB::raw("COALESCE(c.descricao,'') AS convenio"),
                DB::raw("IFNULL(c.tipo, 'PARTICULAR') AS tipo_convenio"),
                DB::raw("CASE WHEN IFNULL(c.tipo, 'PARTICULAR') = 'CONVENIO' THEN COALESCE(c.descricao,'') ELSE COALESCE(p.nome,'') END AS pagador"),
                DB::raw("DATE_FORMAT(cr.vencimento, '%d-%m-%Y') AS vencimento"),
                'cr.valor',
                'cr.status',
                DB::raw("(SELECT p.nu_pagamento FROM pagamentos p WHERE p.faturamento_id = cr.faturamento_id ORDER BY p.id DESC LIMIT 1) as nu_pagamento"),
                DB::raw("(SELECT DATE_FORMAT(MAX(p.data_pagamento), '%d/%m/%Y %H:%i') FROM pagamentos p WHERE p.faturamento_id = cr.faturamento_id AND p.status = 'PAGO') as data_pagamento")
            )
            ->orderByDesc('cr.updated_at')
            ->orderByDesc('cr.id')
            ->limit(1000)
            ->get();

        return Inertia::render('Financeiro/ContasReceber/Index', [
            'contas' => $rows,
        ]);
    }

    public function receiveConvenio(Request $request, string $id)
    {
        $data = $request->validate([
            'valor' => ['required', 'numeric', 'min:0'],
            'forma_pagamento' => ['nullable', 'string', 'max:50'],
            'data_pagamento' => ['nullable', 'date'],
        ]);

        $fatId = (int)$id;
        $fat = DB::table('faturamentos as f')
            ->leftJoin('convenios as c', 'c.id', '=', 'f.convenio_id')
            ->select('f.id', 'c.tipo as tipo_convenio', 'f.status', 'f.convenio_id')
            ->where('f.id', $fatId)
            ->first();
        if (!$fat) {
            return back()->with('error', 'Faturamento não encontrado.');
        }
        $tipo = $fat->convenio_id ? strtoupper((string)$fat->tipo_convenio) : 'PARTICULAR';
        if ($tipo !== 'CONVENIO') {
            return back()->with('error', 'Este recebimento é apenas para faturamento de CONVÊNIO.');
        }
        if (strtoupper((string)$fat->status) === 'CANCELADO') {
            return back()->with('error', 'Faturamento cancelado.');
        }

        $dt = !empty($data['data_pagamento']) ? $data['data_pagamento'] : now()->format('Y-m-d H:i:s');
        $fp = $data['forma_pagamento'] ?? 'TRANSFERENCIA';

        DB::transaction(function () use ($fatId, $data, $dt, $fp) {
            Pagamento::create([
                'faturamento_id' => $fatId,
                'caixa_id' => null,
                'movimentacao_id' => null,
                'valor' => (float)$data['valor'],
                'forma_pagamento' => $fp,
                'data_pagamento' => $dt,
                'status' => 'PAGO',
            ]);

            DB::table('faturamentos')->where('id', $fatId)->update([
                'status' => 'RECEBIDO',
                'updated_at' => now(),
            ]);

            DB::table('contas_receber')->where('faturamento_id', $fatId)->update([
                'status' => 'RECEBIDO',
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'Recebimento registrado no financeiro.');
    }

    public function gerarCobranca(Request $request, string $id)
    {
        $fatId = (int)$id;
        $cr = DB::table('contas_receber as cr')
            ->leftJoin('faturamentos as f', 'f.id', '=', 'cr.faturamento_id')
            ->leftJoin('pacientes as p', 'p.id', '=', 'cr.paciente_id')
            ->leftJoin('convenios as c', 'c.id', '=', 'cr.convenio_id')
            ->select(
                'cr.id as conta_id',
                'cr.faturamento_id',
                'cr.valor',
                'cr.vencimento',
                'p.nome as paciente_nome',
                'p.cpf as paciente_cpf',
                'p.id as paciente_id',
                'c.descricao as convenio_nome',
                'c.cnpj as convenio_cnpj',
                'c.id as convenio_id',
                'f.account_id'
            )
            ->where('cr.id', $fatId) // ID here is now the cr.id (conta_id)
            ->first();

        if (!$cr) {
            return back()->with('error', 'Conta a receber não encontrada.');
        }

        $config = \App\Models\ConfiguracaoBancaria::where('account_id', $cr->account_id)
            ->where('is_padrao', true)
            ->where('ativo', true)
            ->first();

        if (!$config) {
            return back()->with('error', 'Nenhuma integração bancária padrão e ativa configurada para esta clínica.');
        }

        $integracao = \App\Services\Bancario\IntegracaoFactory::make($config->provedor, $config->ambiente === 'sandbox');
        $gerenciador = new \App\Services\Bancario\GerenciadorCobranca($integracao);

        $pagadorNome = $cr->convenio_id ? $cr->convenio_nome : $cr->paciente_nome;
        $pagadorDoc = $cr->convenio_id ? ($cr->convenio_cnpj ?? '') : ($cr->paciente_cpf ?? '');

        $dadosBoleto = [
            'numeroConvenio' => $config->numero_convenio,
            'numeroCarteira' => $config->numero_carteira,
            'numeroVariacaoCarteira' => $config->numero_variacao_carteira,
            'dataEmissao' => date('d.m.Y'),
            'dataVencimento' => date('Y-m-d', strtotime($cr->vencimento)),
            'valorOriginal' => (float)$cr->valor,
            'numeroTituloCliente' => 'CR' . $cr->conta_id,
            'pagador_nome' => $pagadorNome,
            'pagador_numeroInscricao' => preg_replace('/\D/', '', $pagadorDoc),
            'billingType' => $request->input('billingType', 'UNDEFINED'),
        ];

        if ($config->provedor === 'bb') {
            $dadosBoleto['dataVencimento'] = date('d.m.Y', strtotime($cr->vencimento));
        }

        $tokenData = $gerenciador->gerarToken([
            'client_id' => $config->client_id,
            'client_secret' => $config->client_secret,
            'access_token' => (strtolower($config->provedor) === 'asaas') ? $config->app_key : ($config->app_key ?: $config->client_id),
        ]);

        if (!$tokenData['success']) {
            return back()->with('error', 'Falha na autenticação bancária. Verifique as credenciais da integração.');
        }

        $token = $tokenData['data']['access_token'] ?? '';

        try {
            $boleto = $gerenciador->registrarBoleto($dadosBoleto, $tokenData['data']['access_token'] ?? '', [
                'app_key' => $config->app_key,
                'client_id' => $config->client_id,
                'access_token' => (strtolower($config->provedor) === 'asaas') ? $config->app_key : ($config->app_key ?: $config->client_id)
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        if (!$boleto['success']) {
            return back()->with('error', 'Falha ao gerar cobrança na API: ' . ($boleto['error'] ?? 'Erro desconhecido.'));
        }

        $linkBoleto = '';
        $nossoNumero = '';
        if ($config->provedor === 'asaas') {
            $linkBoleto = $boleto['data']['bankSlipUrl'] ?? $boleto['data']['invoiceUrl'] ?? '';
            $nossoNumero = $boleto['data']['id'] ?? '';
        } elseif ($config->provedor === 'bb') {
            $linkBoleto = 'Linha digitável: ' . ($boleto['data']['linhaDigitavel'] ?? '');
            $nossoNumero = $boleto['data']['numeroTransacao'] ?? '';
        }

        DB::table('cobrancas')->insert([
            'account_id' => $cr->account_id,
            'conta_receber_id' => $cr->conta_id,
            'configuracao_bancaria_id' => $config->id,
            'gateway' => $config->provedor,
            'gateway_id' => $nossoNumero,
            'tipo' => 'BOLETO',
            'nosso_numero' => $nossoNumero,
            'url' => $linkBoleto,
            'valor' => $cr->valor,
            'vencimento' => $cr->vencimento,
            'status' => 'PENDING',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Boleto gerado com sucesso!');
    }
}
