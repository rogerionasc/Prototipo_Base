<?php

namespace App\Http\Controllers;

use App\Models\Totem;
use App\Models\TotemOpcao;
use Illuminate\Http\Request;

class TotemOpcaoController extends Controller
{
    public function sync(Request $request, Totem $totem)
    {
        $validated = $request->validate([
            'opcoes' => 'array',
            'opcoes.*.id' => 'nullable|integer',
            'opcoes.*.nome' => 'required|string|max:255',
            'opcoes.*.codigo' => 'nullable|string|max:10',
            'opcoes.*.cor' => 'nullable|string|max:20',
            'opcoes.*.icone' => 'nullable|string|max:50',
            'opcoes.*.status' => 'boolean',
        ]);

        $incomingIds = collect($validated['opcoes'] ?? [])->pluck('id')->filter()->toArray();

        // Remove the ones not in incoming
        $totem->opcoes()->whereNotIn('id', $incomingIds)->delete();

        foreach ($validated['opcoes'] ?? [] as $opcaoData) {
            if (isset($opcaoData['id']) && $opcaoData['id']) {
                $opcao = $totem->opcoes()->find($opcaoData['id']);
                if ($opcao) {
                    $opcao->update($opcaoData);
                }
            } else {
                $totem->opcoes()->create($opcaoData);
            }
        }

        return redirect()->back()->with('success', 'Opções sincronizadas com sucesso.');
    }

    public function store(Request $request, Totem $totem)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:10',
            'status' => 'boolean',
        ]);

        $totem->opcoes()->create($validated);

        return redirect()->back()->with('success', 'Opção cadastrada com sucesso.');
    }

    public function update(Request $request, Totem $totem, TotemOpcao $opcao)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:10',
            'status' => 'boolean',
        ]);

        $opcao->update($validated);

        return redirect()->back()->with('success', 'Opção atualizada com sucesso.');
    }

    public function destroy(Totem $totem, TotemOpcao $opcao)
    {
        $opcao->delete();
        return redirect()->back()->with('success', 'Opção removida com sucesso.');
    }
}
