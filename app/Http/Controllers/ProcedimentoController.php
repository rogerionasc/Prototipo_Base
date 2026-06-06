<?php

namespace App\Http\Controllers;

use App\Models\Procedimento;
use Illuminate\Http\Request;

class ProcedimentoController extends Controller
{
    private function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias_procedimento,id'],
            'eh_tratamento' => ['nullable', 'boolean'],
            'quantidade_sessoes' => ['nullable', 'integer', 'min:1'],
            'valor' => ['nullable', 'numeric'],
            'comissao_percentual' => ['nullable', 'numeric'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }

    private function normalizePayload(array $data): array
    {
        $data['eh_tratamento'] = isset($data['eh_tratamento']) ? (bool)$data['eh_tratamento'] : false;
        $data['ativo'] = isset($data['ativo']) ? (bool)$data['ativo'] : true;
        return $data;
    }

    public function store(Request $request)
    {
        $data = $this->normalizePayload($request->validate($this->rules()));
        Procedimento::create($data);
        return back()->with('success', 'Procedimento cadastrado');
    }

    public function update(Request $request, string $id)
    {
        $data = $this->normalizePayload($request->validate($this->rules()));
        $proc = Procedimento::findOrFail($id);
        $proc->update($data);
        return back()->with('success', 'Procedimento atualizado');
    }

    public function destroy(string $id)
    {
        $proc = Procedimento::findOrFail($id);
        $proc->delete();
        return back()->with('success', 'Procedimento excluído');
    }
}
