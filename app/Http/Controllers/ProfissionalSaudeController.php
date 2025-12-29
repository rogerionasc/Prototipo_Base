<?php

namespace App\Http\Controllers;

use App\Models\ProfissionalSaude;
use App\Models\Especialidade;
use App\Models\Endereco;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfissionalSaudeController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required','string','max:255'],
            'cpf' => ['nullable','string','max:20'],
            'rg' => ['nullable','string','max:50'],
            'sexo' => ['nullable','string','max:20'],
            'data_nascimento' => ['nullable','date'],
            'naturalidade' => ['nullable','string','max:255'],
            'estado_civil_id' => ['nullable','integer','exists:estado_civil,id'],
            'cnes' => ['nullable','string','max:255'],
            'crm' => ['nullable','string','max:255'],
            'email' => ['nullable','email','max:255'],
            'telefone' => ['nullable','string','max:30'],
            'celular' => ['nullable','string','max:30'],
            'observacoes' => ['nullable','string'],
        ]);

        $enderecoData = $request->validate([
            'cep' => ['nullable', 'string', 'max:9'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'complemento' => ['nullable', 'string', 'max:255'],
        ]);
        $hasEndereco = false;
        foreach (['cep', 'endereco', 'numero', 'bairro', 'cidade', 'complemento'] as $campo) {
            if (!empty($enderecoData[$campo])) {
                $hasEndereco = true;
                break;
            }
        }
        if ($hasEndereco) {
            $endereco = Endereco::create($enderecoData);
            $data['endereco_id'] = $endereco->id;
        } else {
            $data['endereco_id'] = null;
        }

        $prof = ProfissionalSaude::create($data);

        $pivot = $request->validate([
            'especialidades' => ['array'],
            'especialidades.*.id' => ['required','integer','exists:especialidades,id'],
            'especialidades.*.qre' => ['nullable','string','max:255'],
        ]);

        if (!empty($pivot['especialidades'])) {
            $sync = [];
            foreach ($pivot['especialidades'] as $item) {
                $sync[$item['id']] = ['qre' => $item['qre'] ?? null];
            }
            $prof->especialidades()->sync($sync);
        }

        return back()->with('success', 'Profissional cadastrado');
    }

    public function update(Request $request, int $id)
    {
        $prof = ProfissionalSaude::findOrFail($id);

        $data = $request->validate([
            'nome' => ['required','string','max:255'],
            'cpf' => ['nullable','string','max:20'],
            'rg' => ['nullable','string','max:50'],
            'sexo' => ['nullable','string','max:20'],
            'data_nascimento' => ['nullable','date'],
            'naturalidade' => ['nullable','string','max:255'],
            'estado_civil_id' => ['nullable','integer','exists:estado_civil,id'],
            'cnes' => ['nullable','string','max:255'],
            'crm' => ['nullable','string','max:255'],
            'email' => ['nullable','email','max:255'],
            'telefone' => ['nullable','string','max:30'],
            'celular' => ['nullable','string','max:30'],
            'observacoes' => ['nullable','string'],
        ]);

        $enderecoData = $request->validate([
            'cep' => ['nullable', 'string', 'max:9'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'complemento' => ['nullable', 'string', 'max:255'],
        ]);
        $hasEndereco = false;
        foreach (['cep', 'endereco', 'numero', 'bairro', 'cidade', 'complemento'] as $campo) {
            if (!empty($enderecoData[$campo])) {
                $hasEndereco = true;
                break;
            }
        }
        if ($hasEndereco) {
            if ($prof->endereco_id) {
                $endereco = Endereco::find($prof->endereco_id);
                if ($endereco) {
                    $endereco->update($enderecoData);
                } else {
                    $endereco = Endereco::create($enderecoData);
                    $data['endereco_id'] = $endereco->id;
                }
            } else {
                $endereco = Endereco::create($enderecoData);
                $data['endereco_id'] = $endereco->id;
            }
        } else {
            $data['endereco_id'] = null;
        }

        $prof->update($data);

        $pivot = $request->validate([
            'especialidades' => ['array'],
            'especialidades.*.id' => ['required','integer','exists:especialidades,id'],
            'especialidades.*.qre' => ['nullable','string','max:255'],
        ]);

        $sync = [];
        foreach (($pivot['especialidades'] ?? []) as $item) {
            $sync[$item['id']] = ['qre' => $item['qre'] ?? null];
        }
        $prof->especialidades()->sync($sync);

        return back()->with('success', 'Profissional atualizado');
    }

    public function destroy(int $id)
    {
        $prof = ProfissionalSaude::findOrFail($id);
        $prof->delete();
        return back()->with('success', 'Profissional removido');
    }
}
