<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TussMapeamento;
use Illuminate\Validation\Rule;

class TussMapeamentoController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 50);
        $offset = $request->input('offset', 0);

        $query = TussMapeamento::with([
            'origemProcedimento:id,codigo,descricao,tabela',
            'referenciaProcedimento:id,codigo,descricao,tabela,quantidade_ch,quantidade_co'
        ])
        ->orderByDesc('id');

        if ($request->filled('q')) {
            $searchTerm = $request->input('q');
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('origemProcedimento', function ($subQ) use ($searchTerm) {
                    $subQ->where('codigo', 'like', "%{$searchTerm}%")
                         ->orWhere('descricao', 'like', "%{$searchTerm}%")
                         ->orWhere('tabela', 'like', "%{$searchTerm}%");
                })->orWhereHas('referenciaProcedimento', function ($subQ) use ($searchTerm) {
                    $subQ->where('codigo', 'like', "%{$searchTerm}%")
                         ->orWhere('descricao', 'like', "%{$searchTerm}%")
                         ->orWhere('tabela', 'like', "%{$searchTerm}%");
                });
            });
        }

        $total = $query->count();
        
        $mapeamentos = $query->limit($limit)->offset($offset)->get()->map(function ($item) {
            $item->codigo = $item->origemProcedimento ? $item->origemProcedimento->codigo : null;
            $item->descricao = $item->origemProcedimento ? $item->origemProcedimento->descricao : null;
            $item->quantidade_ch = $item->referenciaProcedimento ? $item->referenciaProcedimento->quantidade_ch : null;
            $item->quantidade_co = $item->referenciaProcedimento ? $item->referenciaProcedimento->quantidade_co : null;
            return $item;
        });

        return response()->json([
            'data' => $mapeamentos,
            'meta' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'origem_procedimento_id' => 'required|exists:tuss,id',
            'referencia_procedimento_id' => 'required|exists:tuss,id',
        ]);

        $exists = TussMapeamento::where('origem_procedimento_id', $validated['origem_procedimento_id'])
            ->where('referencia_procedimento_id', $validated['referencia_procedimento_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Este mapeamento já existe.'], 422);
        }

        $mapeamento = TussMapeamento::create([
            'origem_procedimento_id' => $validated['origem_procedimento_id'],
            'referencia_procedimento_id' => $validated['referencia_procedimento_id'],
        ]);

        return response()->json([
            'message' => 'Mapeamento criado com sucesso.',
            'data' => $mapeamento->load('origemProcedimento:id,codigo,descricao,tabela', 'referenciaProcedimento:id,codigo,descricao,tabela,quantidade_ch,quantidade_co')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'origem_procedimento_id' => 'required|exists:tuss,id',
            'referencia_procedimento_id' => 'required|exists:tuss,id',
        ]);

        $exists = TussMapeamento::where('origem_procedimento_id', $validated['origem_procedimento_id'])
            ->where('referencia_procedimento_id', $validated['referencia_procedimento_id'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Este mapeamento já existe.'], 422);
        }

        $mapeamento = TussMapeamento::findOrFail($id);
        $mapeamento->update([
            'origem_procedimento_id' => $validated['origem_procedimento_id'],
            'referencia_procedimento_id' => $validated['referencia_procedimento_id'],
        ]);

        return response()->json([
            'message' => 'Mapeamento atualizado com sucesso.',
            'data' => $mapeamento->load('origemProcedimento:id,codigo,descricao,tabela', 'referenciaProcedimento:id,codigo,descricao,tabela,quantidade_ch,quantidade_co')
        ]);
    }
    public function destroy($id)
    {
        $mapeamento = TussMapeamento::findOrFail($id);
        $mapeamento->delete();

        return response()->json(['message' => 'Mapeamento removido com sucesso.']);
    }

    public function atribuirConvenio(Request $request)
    {
        $validated = $request->validate([
            'convenio_id' => 'required|integer|exists:convenios,id',
            'mapeamento_ids' => 'required|array',
            'mapeamento_ids.*' => 'integer|exists:tuss_mapeamentos,id',
        ]);

        $accountId = auth()->user()->account_id ?? null;

        $mapeamentos = TussMapeamento::whereIn('id', $validated['mapeamento_ids'])->get();
        $convenio = \App\Models\Convenio::findOrFail($validated['convenio_id']);

        $syncData = [];
        foreach ($mapeamentos as $map) {
            $syncData[$map->origem_procedimento_id] = [
                'account_id' => $accountId,
                'requer_autorizacao' => false,
                'valor_ch' => 0,
                'valor_co' => 0,
                'valor_procedimento' => 0,
            ];
        }

        $convenio->tuss()->syncWithoutDetaching($syncData);

        return response()->json(['message' => 'Procedimentos atribuídos ao convênio com sucesso.']);
    }
}
