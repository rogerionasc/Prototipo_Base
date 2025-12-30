<?php

namespace App\Http\Controllers;

use App\Models\AgendaMedica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AgendaMedicaController extends Controller
{
    public function showByProfissional(string $id)
    {
        $items = AgendaMedica::select('id','dia_semana','hora_inicio','hora_fim')
            ->where('profissional_saude_id', $id)
            ->orderBy('dia_semana')
            ->get();
        return response()->json([
            'itens' => $items,
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $data = $request->validate([
            'profissional_saude_id' => ['required', 'integer', 'exists:profissionais_saude,id'],
            'itens' => ['nullable', 'array'],
            'itens.*.dia_semana' => ['required', 'integer', 'between:0,6'],
            'itens.*.hora_inicio' => ['nullable', 'regex:/^\d{2}:\d{2}(?::\d{2})?$/'],
            'itens.*.hora_fim' => ['nullable', 'regex:/^\d{2}:\d{2}(?::\d{2})?$/'],
        ]);

        // dd($data);

        $psId = (int) $data['profissional_saude_id'];
        $items = $data['itens'] ?? [];

        if (empty($items)) {
            DB::transaction(function () use ($psId) {
                AgendaMedica::where('profissional_saude_id', $psId)->delete();
            });
            return back()->with('success', 'Agenda médica limpa com sucesso');
        } else {
            DB::transaction(function () use ($psId, $items) {
                $diasSubmitted = [];
                foreach ($items as $it) {
                    $dia = (int) ($it['dia_semana'] ?? null);
                    $hi = $it['hora_inicio'] ?? null;
                    $hf = $it['hora_fim'] ?? null;
                    if ($dia < 0 || $dia > 6) {
                        continue;
                    }
                    if (empty($hi) && empty($hf)) {
                        $hi = '00:00';
                        $hf = '23:59';
                    }
                    // Normaliza para HH:MM
                    $hi = is_string($hi) ? substr($hi, 0, 5) : $hi;
                    $hf = is_string($hf) ? substr($hf, 0, 5) : $hf;
                    if (empty($hi) || empty($hf)) {
                        continue;
                    }
                    if (strtotime($hi) >= strtotime($hf)) {
                        continue;
                    }
                    $diasSubmitted[] = $dia;
                    AgendaMedica::updateOrCreate(
                        ['profissional_saude_id' => $psId, 'dia_semana' => $dia],
                        ['hora_inicio' => $hi, 'hora_fim' => $hf]
                    );
                }
                $diasSubmitted = array_unique(array_filter($diasSubmitted, fn($d) => $d !== null));
                AgendaMedica::where('profissional_saude_id', $psId)
                    ->when(!empty($diasSubmitted), function ($q) use ($diasSubmitted) {
                        $q->whereNotIn('dia_semana', $diasSubmitted);
                    }, function ($q) {
                        // Se nada válido foi submetido, remove todos
                        $q->whereRaw('1 = 1');
                    })
                    ->delete();
            });
            return back()->with('success', 'Agenda médica salva com sucesso');
        }

    }

    public function destroy(string $id)
    {
        $agenda = AgendaMedica::findOrFail($id);
        $agenda->delete();
        return back()->with('success', 'Agenda médica excluída com sucesso');
    }
}
