<?php

namespace App\Http\Controllers\Financeiro;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracaoBancaria;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConfiguracaoBancariaController extends Controller
{
    public function index()
    {
        $configuracoes = ConfiguracaoBancaria::where('account_id', session('current_account_id', auth()->user()->account_id ?? null))->get();
        $contasReceber = \App\Models\ContaReceber::with('cobrancas')->get();

        return Inertia::render('Financeiro/ConfiguracoesBancarias/Index', [
            'configuracoes' => $configuracoes,
            'contasReceber' => $contasReceber
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'provedor' => 'required|string',
            'tipo' => 'required|string',
            'ambiente' => 'nullable|string',
            'numero_convenio' => 'nullable|string',
            'numero_carteira' => 'nullable|string',
            'numero_variacao_carteira' => 'nullable|string',
            'app_key' => 'nullable|string',
            'is_padrao' => 'boolean',
        ]);

        $accountId = session('current_account_id', auth()->user()->account_id ?? null);

        if (isset($validated['is_padrao']) && $validated['is_padrao']) {
            ConfiguracaoBancaria::where('account_id', $accountId)->update(['is_padrao' => false]);
        } else {
            $exists = ConfiguracaoBancaria::where('account_id', $accountId)->exists();
            if (!$exists) {
                $validated['is_padrao'] = true;
            }
        }

        ConfiguracaoBancaria::create(array_merge($validated, [
            'account_id' => $accountId,
            'ativo' => true,
        ]));

        return redirect()->back()->with('success', 'Configuração salva com sucesso.');
    }

    public function update(Request $request, ConfiguracaoBancaria $configuracaoBancaria)
    {
        $accountId = session('current_account_id', auth()->user()->account_id ?? null);
        if ($configuracaoBancaria->account_id !== $accountId) {
            abort(403);
        }

        $validated = $request->validate([
            'ativo' => 'boolean',
            'is_padrao' => 'boolean',
        ]);

        if (isset($validated['is_padrao']) && $validated['is_padrao']) {
            ConfiguracaoBancaria::where('account_id', $accountId)
                ->where('id', '!=', $configuracaoBancaria->id)
                ->update(['is_padrao' => false]);
        }

        $configuracaoBancaria->update($validated);

        return redirect()->back()->with('success', 'Configuração atualizada.');
    }

    public function storeForAccount(Request $request, $accountId)
    {
        $validated = $request->validate([
            'provedor' => 'required|string',
            'tipo' => 'nullable|string',
            'ambiente' => 'nullable|string',
            'numero_convenio' => 'nullable|string',
            'numero_carteira' => 'nullable|string',
            'numero_variacao_carteira' => 'nullable|string',
            'client_id' => 'nullable|string',
            'client_secret' => 'nullable|string',
            'app_key' => 'nullable|string',
            'certificado' => 'nullable|string',
            'ativo' => 'boolean',
            'is_padrao' => 'boolean',
        ]);

        if (isset($validated['is_padrao']) && $validated['is_padrao']) {
            ConfiguracaoBancaria::where('account_id', $accountId)->update(['is_padrao' => false]);
        } else {
            // Se for a primeira integração desta conta, podemos torná-la padrão por default
            $exists = ConfiguracaoBancaria::where('account_id', $accountId)->exists();
            if (!$exists) {
                $validated['is_padrao'] = true;
            }
        }

        $config = ConfiguracaoBancaria::updateOrCreate(
            ['account_id' => $accountId, 'provedor' => $validated['provedor']],
            $validated
        );

        return redirect()->back()->with('success', 'Integração bancária configurada com sucesso.');
    }

    public function testConnection(Request $request)
    {
        $provedor = $request->input('provedor');
        $ambiente = $request->input('ambiente', 'sandbox');
        
        // Configura a factory baseado nos dados enviados e não no banco, pois estamos testando antes de salvar.
        $integracao = \App\Services\Bancario\IntegracaoFactory::make($provedor, $ambiente === 'sandbox');
        $gerenciador = new \App\Services\Bancario\GerenciadorCobranca($integracao);

        $credenciais = [
            'client_id' => $request->input('client_id'),
            'client_secret' => $request->input('client_secret'),
            'app_key' => $request->input('app_key')
        ];

        // Se for mock, retornar sucesso direto
        if ($provedor === 'Mock' || strtolower($provedor) === 'mock') {
            return response()->json(['success' => true, 'message' => 'Conexão (Mock) testada com sucesso!']);
        }

        try {
            $resultado = $gerenciador->gerarToken($credenciais);
            if ($resultado['success']) {
                return response()->json(['success' => true, 'message' => 'Conexão bem-sucedida! Credenciais validadas com sucesso.']);
            }
            return response()->json(['success' => false, 'message' => 'Falha na conexão: ' . ($resultado['data']['error_description'] ?? $resultado['error'])], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao testar conexão: ' . $e->getMessage()], 500);
        }
    }

    public function storeFakeContaReceber(Request $request)
    {
        $conta = \App\Models\ContaReceber::create([
            'faturamento_id' => \App\Models\Faturamento::first()->id ?? null,
            'paciente_id' => \App\Models\Paciente::first()->id ?? null,
            'convenio_id' => \App\Models\Convenio::first()->id ?? null,
            'valor' => 10500.00,
            'vencimento' => '2026-09-30',
            'status' => 'ABERTO'
        ]);

        return redirect()->back()->with('success', 'Conta a Receber teste criada.');
    }


}
