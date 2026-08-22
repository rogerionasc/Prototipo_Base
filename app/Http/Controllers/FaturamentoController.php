<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FaturamentoController extends Controller
{
    public function particular()
    {
        $rows = DB::table('faturamentos as f')
            ->leftJoin('pacientes as p', 'p.id', '=', 'f.paciente_id')
            ->select(
                'f.id',
                'f.paciente_id',
                DB::raw("COALESCE(p.nome,'') AS paciente"),
                DB::raw("COALESCE(p.cpf,'') AS paciente_documento"),
                DB::raw("DATE_FORMAT(f.data_faturamento, '%d-%m-%Y %H:%i') AS data_faturamento"),
                DB::raw("DATE_FORMAT(f.vencimento, '%d-%m-%Y') AS vencimento"),
                'f.valor_total',
                'f.valor_final',
                'f.status'
            )
            ->leftJoin('convenios as c', 'c.id', '=', 'f.convenio_id')
            ->where(function($q) {
                $q->whereRaw('UPPER(c.tipo) = ?', ['PARTICULAR'])
                  ->orWhereNull('f.convenio_id');
            })
            ->orderByDesc('f.updated_at')
            ->orderByDesc('f.id')
            ->limit(500)
            ->get();

        return Inertia::render('Faturamento/Particular', [
            'faturamentos' => $rows,
        ]);
    }

    public function convenios()
    {
        $faturamentos = \App\Models\Faturamento::with(['guias.atendimento.agendamento.paciente', 'guias.agendamento.paciente', 'guias.procedimentosSolicitados', 'convenio'])
            ->whereHas('convenio', function($q) {
                $q->where('tipo', 'CONVENIO');
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(500)
            ->get();

        $rows = $faturamentos->map(function ($fat) {
            $totalGuias = $fat->guias->count();
            $statusCounts = $fat->guias->groupBy('status')->map->count();
            
            $timeline = [];
            foreach($statusCounts as $status => $count) {
                $timeline[] = [
                    'status' => $status,
                    'count' => $count,
                    'percentage' => $totalGuias > 0 ? round(($count / $totalGuias) * 100, 2) : 0
                ];
            }

            return [
                'id' => $fat->id,
                'convenio_id' => $fat->convenio_id,
                'convenio' => $fat->convenio ? $fat->convenio->descricao : '',
                'convenio_logo' => $fat->convenio ? $fat->convenio->logo_path : null,
                'data_faturamento' => $fat->data_faturamento ? \Carbon\Carbon::parse($fat->data_faturamento)->format('d-m-Y H:i') : null,
                'vencimento' => $fat->vencimento ? \Carbon\Carbon::parse($fat->vencimento)->format('d-m-Y') : null,
                'valor_total' => $fat->valor_total,
                'valor_aprovado' => $fat->valor_aprovado,
                'status' => $fat->status,
                'total_guias' => $totalGuias,
                'guias_timeline' => $timeline,
                'guias' => $fat->guias->map(function($g) {
                    return [
                        'id' => $g->id,
                        'senha' => $g->senha,
                        'status' => $g->status,
                        'valor_total' => $g->valor_total_geral,
                        'valor_glosado' => $g->valor_glosado,
                        'tipo' => $g->tipo,
                        'numero_guia_prestador' => $g->numero_guia_prestador,
                        'numero_guia_operadora' => $g->numero_guia_operadora,
                        'numero_carteira' => $g->numero_carteira,
                        'agendamento_id' => $g->atendimento?->agendamento_id ?? $g->agendamento_id,
                        'paciente_nome' => $g->atendimento?->agendamento?->paciente?->nome ?? $g->agendamento?->paciente?->nome ?? $g->beneficiario_nome ?? 'Não informado',
                        'data_atendimento' => $g->atendimento?->agendamento?->data ? \Carbon\Carbon::parse($g->atendimento->agendamento->data)->format('d/m/Y') : ($g->agendamento?->data ? \Carbon\Carbon::parse($g->agendamento->data)->format('d/m/Y') : ($g->data_solicitacao ? \Carbon\Carbon::parse($g->data_solicitacao)->format('d/m/Y') : '-')),
                        'procedimento_solicitado_descricao' => $g->procedimentosSolicitados->first()?->procedimento_solicitado_descricao ?? '-',
                        'profissional_solicitante_nome' => $g->profissional_solicitante_nome ?? '-'
                    ];
                })->toArray()
            ];
        });

        // Guias Prestes a Vencer
        $guiasPrestesVencer = \App\Models\Guia::with(['atendimento.agendamento.paciente', 'agendamento.paciente', 'atendimento.agendamento.convenio', 'agendamento.convenio', 'faturamento.convenio', 'procedimentosRealizados'])
            ->where('status', 'PRONTA_FATURAMENTO')
            ->whereNull('faturamento_id')
            ->get()
            ->map(function($g) {
                $convenio = $g->atendimento?->agendamento?->convenio ?? $g->agendamento?->convenio;
                $diasParaFaturar = $convenio?->dias_para_faturar ?? 30;

                $dataExecucaoMaisAntiga = null;
                if ($g->procedimentosRealizados->isNotEmpty()) {
                    $minData = $g->procedimentosRealizados->min('data_realizacao');
                    if ($minData) {
                        $dataExecucaoMaisAntiga = \Carbon\Carbon::parse($minData);
                    }
                }
                
                if (!$dataExecucaoMaisAntiga) {
                    $dataExecucaoMaisAntiga = $g->created_at->copy();
                }

                $dataLimite = $dataExecucaoMaisAntiga->copy()->addDays($diasParaFaturar);
                $diasVencer = (int) ceil(\Carbon\Carbon::now()->floatDiffInDays($dataLimite, false));
                
                return [
                    'id' => $g->id,
                    'agendamento_id' => $g->atendimento?->agendamento_id ?? $g->agendamento_id,
                    'numero_guia' => $g->numero_guia_prestador,
                    'valor_total' => $g->valor_total_geral,
                    'dias_vencer' => $diasVencer,
                    'limite_20_porcento' => $diasParaFaturar * 0.20,
                    'atendimento' => [
                        'agendamento' => [
                            'paciente' => [
                                'nome' => $g->atendimento?->agendamento?->paciente?->nome ?? $g->agendamento?->paciente?->nome ?? $g->beneficiario_nome ?? 'Não informado'
                            ]
                        ]
                    ],
                    'faturamento' => [
                        'convenio' => [
                            'nome' => $convenio?->descricao ?? 'Sem Convênio'
                        ]
                    ]
                ];
            })
            ->filter(function($item) {
                // Aparecer apenas quando faltar 20% ou menos do prazo máximo (ou se já estiver atrasado)
                return $item['dias_vencer'] <= $item['limite_20_porcento'];
            })
            ->sortBy('dias_vencer')
            ->take(10)
            ->values();

        return Inertia::render('Faturamento/Convenios', [
            'faturamentos' => $rows,
            'guiasPrestesAVencer' => $guiasPrestesVencer,
            'convenios_list' => \App\Models\Convenio::where('tipo', 'CONVENIO')->orderBy('descricao')->get(['id', 'descricao'])
        ]);
    }

    public function getGuiasDisponiveis(Request $request)
    {
        $convenioId = $request->query('convenio_id');
        
        $guias = \App\Models\Guia::whereNull('faturamento_id')
            ->where('status', 'PRONTA_FATURAMENTO')
            ->where(function ($query) use ($convenioId) {
                $query->whereHas('atendimento', function($q) use ($convenioId) {
                    $q->where('convenio_id', $convenioId)
                      ->orWhereHas('agendamento', function($q2) use ($convenioId) {
                          $q2->where('convenio_id', $convenioId);
                      });
                })->orWhereHas('agendamento', function($q) use ($convenioId) {
                    $q->where('convenio_id', $convenioId);
                });
            })
            ->with(['atendimento.agendamento.paciente', 'agendamento.paciente', 'procedimentosSolicitados']) // Include relation to display data in frontend
            ->get();
            
        $mapped = $guias->map(function($g) {
            return [
                'id' => $g->id,
                'senha' => $g->senha,
                'status' => $g->status,
                'valor_total' => $g->valor_total_geral,
                'valor_glosado' => $g->valor_glosado,
                'tipo' => $g->tipo,
                'numero_guia_prestador' => $g->numero_guia_prestador,
                'numero_guia_operadora' => $g->numero_guia_operadora,
                'numero_carteira' => $g->numero_carteira,
                'agendamento_id' => $g->atendimento?->agendamento_id ?? $g->agendamento_id,
                'paciente_nome' => $g->atendimento?->agendamento?->paciente?->nome ?? $g->agendamento?->paciente?->nome ?? $g->beneficiario_nome ?? 'Não informado',
                'data_atendimento' => $g->atendimento?->agendamento?->data ? \Carbon\Carbon::parse($g->atendimento->agendamento->data)->format('d/m/Y') : ($g->agendamento?->data ? \Carbon\Carbon::parse($g->agendamento->data)->format('d/m/Y') : ($g->data_solicitacao ? \Carbon\Carbon::parse($g->data_solicitacao)->format('d/m/Y') : '-')),
                'procedimento_solicitado_descricao' => $g->procedimentosSolicitados->first()?->procedimento_solicitado_descricao ?? '-',
                'profissional_solicitante_nome' => $g->profissional_solicitante_nome ?? '-'
            ];
        });

        return response()->json($mapped);
    }

    public function devolverGuia($id)
    {
        $guia = \App\Models\Guia::findOrFail($id);
        if ($guia->faturamento_id) {
            return response()->json(['message' => 'Guia já está em um lote.'], 400);
        }
        $guia->status = 'CRIADA';
        $guia->save();

        return response()->json(['message' => 'Guia devolvida para o Contas Médicas com sucesso!']);
    }

    public function storeLote(Request $request)
    {
        $data = $request->validate([
            'convenio_id' => 'required|exists:convenios,id',
            'guias' => 'nullable|array',
            'guias.*' => 'exists:guias,id'
        ]);

        $guias = collect();
        if (!empty($data['guias'])) {
            $guias = \App\Models\Guia::whereIn('id', $data['guias'])->get();
        }
        
        $total = $guias->where('status', '!=', 'DEVOLVIDA')->sum('valor_total_geral');

        $fat = \App\Models\Faturamento::create([
            'convenio_id' => $data['convenio_id'],
            'valor_total' => $total,
            'valor_cobrado' => $total,
            'valor_aprovado' => $total,
            'status' => 'ABERTA',
            'data_faturamento' => now(),
            'paciente_id' => 1 // Temporary fallback until patient logic is refined
        ]);

        if (!empty($data['guias'])) {
            \App\Models\Guia::whereIn('id', $data['guias'])->update([
                'faturamento_id' => $fat->id,
                'status' => 'FATURADA'
            ]);
        }

        return back()->with('success', 'Lote criado com sucesso!');
    }

    public function getGuiasLote(string $id)
    {
        $guias = \App\Models\Guia::with(['atendimento.agendamento.paciente'])
            ->where('faturamento_id', $id)
            ->get();
        return response()->json($guias);
    }

    public function addGuiasLote(Request $request, string $id)
    {
        $fat = \App\Models\Faturamento::findOrFail($id);
        
        $data = $request->validate([
            'guias' => 'required|array|min:1',
            'guias.*' => 'exists:guias,id'
        ]);

        $guias = \App\Models\Guia::with(['atendimento.agendamento', 'agendamento'])->whereIn('id', $data['guias'])->get();
        
        foreach($guias as $guia) {
            $convId = $guia->atendimento?->convenio_id ?? $guia->atendimento?->agendamento?->convenio_id ?? $guia->agendamento?->convenio_id;
            if($convId != $fat->convenio_id) {
                return back()->with('error', 'Uma ou mais guias não pertencem ao convênio deste lote.');
            }
        }

        \App\Models\Guia::whereIn('id', $data['guias'])->update([
            'faturamento_id' => $fat->id,
            'status' => 'FATURADA'
        ]);
        
        $this->recalcularLote($fat);

        return back()->with('success', 'Guias adicionadas ao lote com sucesso!');
    }

    public function removeGuiaLote(string $lote_id, string $guia_id)
    {
        $fat = \App\Models\Faturamento::findOrFail($lote_id);
        $guia = \App\Models\Guia::where('id', $guia_id)->where('faturamento_id', $lote_id)->firstOrFail();

        $guia->update([
            'faturamento_id' => null,
            'status' => 'PRONTA_FATURAMENTO'
        ]);

        $this->recalcularLote($fat);

        return back()->with('success', 'Guia removida do lote com sucesso!');
    }

    public function updateGuiaStatus(Request $request, string $lote_id, string $guia_id)
    {
        $data = $request->validate([
            'status' => ['required', 'string']
        ]);

        $guia = \App\Models\Guia::where('id', $guia_id)->where('faturamento_id', $lote_id)->firstOrFail();
        
        $updateData = ['status' => $data['status']];
        
        if ($data['status'] !== 'GLOSADA') {
            $updateData['valor_glosado'] = 0;
        }

        $guia->update($updateData);

        $fat = \App\Models\Faturamento::findOrFail($lote_id);
        $this->recalcularLote($fat);

        return back()->with('success', 'Status da guia atualizado com sucesso!');
    }

    public function updateGuiaGlosa(Request $request, string $lote_id, string $guia_id)
    {
        $data = $request->validate([
            'valor_glosado' => ['nullable', 'numeric', 'min:0']
        ]);

        $guia = \App\Models\Guia::where('id', $guia_id)->where('faturamento_id', $lote_id)->firstOrFail();
        
        $guia->update([
            'valor_glosado' => $data['valor_glosado']
        ]);

        $fat = \App\Models\Faturamento::findOrFail($lote_id);
        $this->recalcularLote($fat);

        return back()->with('success', 'Valor glosado atualizado com sucesso!');
    }

    public function fecharLote(string $id)
    {
        $faturamento = \App\Models\Faturamento::findOrFail($id);

        if ($faturamento->status === 'ABERTA') {
            $faturamento->update(['status' => 'FECHADA']);
            return back()->with('success', 'Lote fechado com sucesso!');
        } else {
            $faturamento->update(['status' => 'ABERTA']);
            return back()->with('success', 'Lote reaberto com sucesso!');
        }
    }

    public function updateConvenio(Request $request, string $id)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:ABERTA,FECHADA'],
            'valor_cobrado' => ['nullable', 'numeric', 'min:0'],
            'valor_aprovado' => ['nullable', 'numeric', 'min:0'],
            'valor_glosado' => ['nullable', 'numeric', 'min:0'],
            'vencimento' => ['nullable', 'date'],
        ]);

        $fatId = (int)$id;
        $fat = DB::table('faturamentos as f')
            ->leftJoin('convenios as c', 'c.id', '=', 'f.convenio_id')
            ->select('f.id', 'c.tipo')
            ->where('f.id', $fatId)
            ->first();
        if (!$fat) {
            return back()->with('error', 'Faturamento não encontrado.');
        }
        if (strtoupper((string)$fat->tipo) !== 'CONVENIO') {
            return back()->with('error', 'Faturamento não é do tipo CONVÊNIO.');
        }

        $vc = array_key_exists('valor_cobrado', $data) ? (float)($data['valor_cobrado'] ?? 0) : null;
        $va = array_key_exists('valor_aprovado', $data) ? (float)($data['valor_aprovado'] ?? 0) : null;
        $vg = array_key_exists('valor_glosado', $data) ? (float)($data['valor_glosado'] ?? 0) : null;

        if ($vg === null && $vc !== null && $va !== null) {
            $vg = max(0, $vc - $va);
        }

        DB::transaction(function () use ($fatId, $data, $vc, $va, $vg) {
            $upd = [
                'status' => $data['status'],
                'updated_at' => now(),
            ];
            if ($vc !== null) $upd['valor_cobrado'] = $vc;
            if ($va !== null) $upd['valor_aprovado'] = $va;
            if ($vg !== null) $upd['valor_glosado'] = $vg;
            if (!empty($data['vencimento'])) $upd['vencimento'] = $data['vencimento'];

            DB::table('faturamentos')->where('id', $fatId)->update($upd);

            $crValor = null;
            if ($va !== null && $va > 0) $crValor = $va;
            elseif ($vc !== null && $vc > 0) $crValor = $vc;

            if ($crValor !== null) {
                DB::table('contas_receber')->where('faturamento_id', $fatId)->update([
                    'valor' => (float)$crValor,
                    'updated_at' => now(),
                ]);
            }

            if ($data['status'] === 'RECEBIDO') {
                DB::table('contas_receber')->where('faturamento_id', $fatId)->update([
                    'status' => 'RECEBIDO',
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Faturamento atualizado.');
    }
    public function detalhes(string $id)
    {
        $faturamento = DB::table('faturamentos as f')
            ->leftJoin('pacientes as p', 'p.id', '=', 'f.paciente_id')
            ->select(
                'f.id',
                'f.valor_total',
                DB::raw("DATE_FORMAT(f.data_faturamento, '%d/%m/%Y') as data_emissao"),
                DB::raw("DATE_FORMAT(f.vencimento, '%d/%m/%Y') as validade"),
                'f.id as numero',
                DB::raw("COALESCE(p.nome,'') AS paciente_nome"),
                DB::raw("COALESCE(p.cpf,'') AS paciente_cpf")
            )
            ->where('f.id', $id)
            ->first();

        if (!$faturamento) {
            abort(404);
        }

        $agendamentos = DB::table('agendamentos as a')
            ->leftJoin('pagamentos as pag', 'pag.agendamento_id', '=', 'a.id')
            ->leftJoin('atendimentos as at', 'at.agendamento_id', '=', 'a.id')
            ->leftJoin('guias as g', 'g.id', '=', 'at.guia_id')
            ->leftJoin('pacientes as p', 'p.id', '=', 'a.paciente_id')
            ->leftJoin('procedimentos as pr', 'pr.id', '=', 'a.procedimento_id')
            ->leftJoin('agenda_medica as am', 'am.id', '=', 'a.agenda_medica_id')
            ->leftJoin('pessoas as doc', 'doc.id', '=', 'am.pessoa_id')
            ->leftJoin('status_agendamento as st', 'st.id', '=', 'a.status_id')
            ->select(
                'a.id',
                DB::raw("DATE_FORMAT(a.data, '%d/%m/%Y') AS data"),
                DB::raw("TIME_FORMAT(a.hora, '%H:%i') AS hora"),
                'a.valor_cobrado',
                'p.nome as paciente_nome',
                'pr.nome as procedimento_nome',
                'doc.nome as medico_nome',
                'st.descricao as status_nome'
            )
            ->where(function($q) use ($id) {
                $q->where('pag.faturamento_id', $id)
                  ->orWhere('g.faturamento_id', $id);
            })
            ->whereNull('a.deleted_at')
            ->distinct()
            ->get();

        return response()->json([
            'faturamento' => $faturamento,
            'agendamentos' => $agendamentos,
        ]);
    }

    private function recalcularLote($fat)
    {
        $guias = \App\Models\Guia::where('faturamento_id', $fat->id)->where('status', '!=', 'DEVOLVIDA')->get();
        $novoTotal = $guias->sum('valor_total_geral');
        $novoGlosado = $guias->sum('valor_glosado');
        $novoAprovado = max(0, $novoTotal - $novoGlosado);

        $fat->update([
            'valor_total' => $novoTotal,
            'valor_cobrado' => $novoTotal,
            'valor_aprovado' => $novoAprovado
        ]);
    }
}
