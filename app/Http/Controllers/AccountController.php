<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        // Ao carregar todas as clínicas, já incluímos os itens secundários
        $clinicas = \App\Models\Account::with(['totens.opcoes', 'paineis', 'salas.profissionalSaude', 'guiches', 'configuracoesBancarias'])->get();
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
            'ativo' => 'boolean'
        ]);

        if (isset($validated['cnpj'])) {
            $validated['cnpj'] = preg_replace('/[^0-9]/', '', $validated['cnpj']);
        }

        \App\Models\Account::create($validated);

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
            'ativo' => 'boolean'
        ]);

        if (isset($validated['cnpj'])) {
            $validated['cnpj'] = preg_replace('/[^0-9]/', '', $validated['cnpj']);
        }

        $account->update($validated);

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
