<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $query = \App\Models\Account::with(['totens.opcoes', 'paineis', 'salas.profissionalSaude', 'guiches', 'configuracoesBancarias', 'pixConfig']);
        
        $currentAccountId = session('current_account_id', auth()->user()->account_id ?? 1);
        if ($currentAccountId != 1) {
            $query->where('id', $currentAccountId);
        }
        
        $clinicas = $query->get();
        $profissionais = \App\Models\Pessoa::get(['id', 'nome']);
        $tiposIntegracaoBancaria = \App\Models\TipoIntegracaoBancaria::select('id', 'nome', 'logo')->get();

        return inertia('Clinicas/Index', [
            'clinicas' => $clinicas,
            'profissionais' => $profissionais,
            'tiposIntegracaoBancaria' => $tiposIntegracaoBancaria
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:20',
            'cnes' => 'nullable|digits:7',
            'endereco' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'ativo' => 'boolean',
            'pix_chave' => 'nullable|string|max:200',
        ]);

        if (isset($validated['cnpj'])) {
            $validated['cnpj'] = preg_replace('/[^0-9]/', '', $validated['cnpj']);
        }

        $account = \App\Models\Account::create($validated);

        if ($request->filled('pix_chave')) {
            $account->pixConfig()->create($request->only(['pix_chave']));
        }

        return redirect()->route('clinicas.index')->with('success', 'Clínica cadastrada com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $account = \App\Models\Account::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:20',
            'cnes' => 'nullable|digits:7',
            'endereco' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'ativo' => 'boolean',
            'pix_chave' => 'nullable|string|max:200',
        ]);

        if (isset($validated['cnpj'])) {
            $validated['cnpj'] = preg_replace('/[^0-9]/', '', $validated['cnpj']);
        }

        $account->update($validated);

        if ($request->filled('pix_chave')) {
            $account->pixConfig()->updateOrCreate(
                ['account_id' => $account->id],
                $request->only(['pix_chave'])
            );
        } else {
            $account->pixConfig()->delete();
        }

        return redirect()->route('clinicas.index')->with('success', 'Clínica atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $account = \App\Models\Account::findOrFail($id);
        $account->delete();

        return redirect()->route('clinicas.index')->with('success', 'Clínica removida com sucesso!');
    }

    public function switch(Request $request, $id)
    {
        $account = \App\Models\Account::findOrFail($id);
        
        session(['current_account_id' => $id]);

        return redirect()->back()->with('success', 'Clínica alterada para: ' . $account->name);
    }
}
