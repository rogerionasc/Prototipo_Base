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
                'f.numero_lote',
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
            ->where(function ($q) {
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
            ->whereHas('convenio', function ($q) {
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
            foreach ($statusCounts as $status => $count) {
                $timeline[] = [
                    'status' => $status,
                    'count' => $count,
                    'percentage' => $totalGuias > 0 ? round(($count / $totalGuias) * 100, 2) : 0
                ];
            }

            return [
                'id' => $fat->id,
                'numero_lote' => $fat->numero_lote,
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
                'guias' => $fat->guias->map(function ($g) {
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
        $guiasAlerta = \App\Models\Guia::with(['atendimento.agendamento.paciente', 'agendamento.paciente', 'atendimento.agendamento.convenio', 'agendamento.convenio', 'faturamento.convenio', 'procedimentosRealizados'])
            ->where('status', 'PRONTA_FATURAMENTO')
            ->whereNull('faturamento_id')
            ->get()
            ->map(function ($g) {
                $convenio = $g->atendimento?->agendamento?->convenio ?? $g->agendamento?->convenio;
                $diasParaFaturar = $convenio?->dias_para_faturar ?? 30;

                $diasVencer = null;
                if ($g->data_vencimento_faturar) {
                    $dataLimite = \Carbon\Carbon::parse($g->data_vencimento_faturar);
                    $diasVencer = (int) ceil(\Carbon\Carbon::now()->floatDiffInDays($dataLimite, false));
                }

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
                    ],
                    'data_vencimento' => $g->data_vencimento_faturar ? \Carbon\Carbon::parse($g->data_vencimento_faturar)->format('d/m/Y') : null,
                ];
            })
            ->filter(function ($item) {
                // Aparecer apenas quando faltar 20% ou menos do prazo máximo (ou se já estiver atrasado)
                return $item['dias_vencer'] <= $item['limite_20_porcento'];
            })
            ->sortBy('dias_vencer');

        $guiasVencidas = $guiasAlerta->filter(fn($g) => $g['dias_vencer'] < 0)->take(10)->values();
        $guiasPrestesVencer = $guiasAlerta->filter(fn($g) => $g['dias_vencer'] >= 0)->take(10)->values();

        return Inertia::render('Faturamento/Convenios', [
            'faturamentos' => $rows,
            'guiasPrestesAVencer' => $guiasPrestesVencer,
            'guiasVencidas' => $guiasVencidas,
            'convenios_list' => \App\Models\Convenio::where('tipo', 'CONVENIO')->orderBy('descricao')->get(['id', 'descricao'])
        ]);
    }

    public function getGuiasDisponiveis(Request $request)
    {
        $convenioId = $request->query('convenio_id');

        $guias = \App\Models\Guia::whereNull('faturamento_id')
            ->where('status', 'PRONTA_FATURAMENTO')
            ->where(function ($query) use ($convenioId) {
                $query->whereHas('atendimento', function ($q) use ($convenioId) {
                    $q->where('convenio_id', $convenioId)
                        ->orWhereHas('agendamento', function ($q2) use ($convenioId) {
                            $q2->where('convenio_id', $convenioId);
                        });
                })->orWhereHas('agendamento', function ($q) use ($convenioId) {
                    $q->where('convenio_id', $convenioId);
                });
            })
            ->with(['atendimento.agendamento.paciente', 'agendamento.paciente', 'procedimentosSolicitados']) // Include relation to display data in frontend
            ->get();

        $mapped = $guias->map(function ($g) {
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

        $accId = str_pad(session('current_account_id'), 2, '0', STR_PAD_LEFT);
        $fatId = str_pad($fat->id, 4, '0', STR_PAD_LEFT);
        $fat->numero_lote = date('ymd') . $accId . $fatId;
        $fat->save();

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

        if ($fat->status !== 'ABERTA') {
            return back()->with('error', 'Lote fechado ou processado não pode receber novas guias.');
        }

        $data = $request->validate([
            'guias' => 'required|array|min:1',
            'guias.*' => 'exists:guias,id'
        ]);

        $guias = \App\Models\Guia::with(['atendimento.agendamento', 'agendamento'])->whereIn('id', $data['guias'])->get();

        foreach ($guias as $guia) {
            $convId = $guia->atendimento?->convenio_id ?? $guia->atendimento?->agendamento?->convenio_id ?? $guia->agendamento?->convenio_id;
            if ($convId != $fat->convenio_id) {
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

    public function processarLote(string $id)
    {
        $faturamento = \App\Models\Faturamento::findOrFail($id);

        if ($faturamento->status !== 'FECHADA') {
            return back()->with('error', 'Apenas lotes fechados podem ser processados!');
        }

        $faturamento->update(['status' => 'PROCESSADA']);

        return back()->with('success', 'Lote processado com sucesso!');
    }

    public function gerarXml(string $id)
    {
        $faturamento = \App\Models\Faturamento::with(['guias.agendamento.paciente', 'guias.atendimento', 'convenio'])->findOrFail($id);

        // dd($faturamento);

        $account = \App\Models\Account::find(session('current_account_id'));
        $cnpjOrigem = preg_replace('/[^0-9]/', '', $account->cnpj);
        $numeroLote = $faturamento->numero_lote;

        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xmlString .= '<ans:mensagemTISS xmlns:ans="http://www.ans.gov.br/padroes/tiss/schemas" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.ans.gov.br/padroes/tiss/schemas http://www.ans.gov.br/padroes/tiss/schemas/tissV4_02_00.xsd">' . "\n";
        $xmlString .= '    <ans:cabecalho>' . "\n";
        $xmlString .= '        <ans:identificacaoTransacao>' . "\n";
        $xmlString .= '            <ans:tipoTransacao>ENVIO_LOTE_GUIAS</ans:tipoTransacao>' . "\n";
        $xmlString .= '            <ans:sequencialTransacao>' . str_pad($faturamento->id, 7, '0', STR_PAD_LEFT) . '</ans:sequencialTransacao>' . "\n";
        $xmlString .= '            <ans:dataRegistroTransacao>' . date('Y-m-d') . '</ans:dataRegistroTransacao>' . "\n";
        $xmlString .= '            <ans:horaRegistroTransacao>' . date('H:i:s') . '</ans:horaRegistroTransacao>' . "\n";
        $xmlString .= '        </ans:identificacaoTransacao>' . "\n";
        $xmlString .= '        <ans:origem>' . "\n";
        $xmlString .= '            <ans:identificacaoPrestador>' . "\n";
        $xmlString .= '                <ans:CNPJ>' . $cnpjOrigem . '</ans:CNPJ>' . "\n";
        $xmlString .= '            </ans:identificacaoPrestador>' . "\n";
        $xmlString .= '        </ans:origem>' . "\n";
        $xmlString .= '        <ans:destino>' . "\n";
        $xmlString .= '            <ans:registroANS>' . $faturamento->convenio->registro_ans . '</ans:registroANS>' . "\n";
        $xmlString .= '        </ans:destino>' . "\n";
        $xmlString .= '        <ans:Padrao>4.02.00</ans:Padrao>' . "\n";
        $xmlString .= '    </ans:cabecalho>' . "\n";
        $xmlString .= '    <ans:prestadorParaOperadora>' . "\n";
        $xmlString .= '        <ans:loteGuias>' . "\n";
        $xmlString .= '            <ans:numeroLote>' . $numeroLote . '</ans:numeroLote>' . "\n";
        $xmlString .= '            <ans:guiasTISS>' . "\n";

        dd($faturamento->guias);

        foreach ($faturamento->guias as $guia) {
            $tipoGuia = $guia->tipo ?? 'SADT';
            $registroANS = str_pad($faturamento->convenio->registro_ans ?? '000000', 6, '0', STR_PAD_LEFT);
            $numeroGuia = $guia->numero_guia_prestador ?? $guia->id;
            $carteira = $guia->atendimento->carteirinha ?? '0000000000000000';
            $valorTotal = $guia->valor_total > 0 ? number_format($guia->valor_total, 2, '.', '') : '100.00';

            $cnpj = $guia->contratado_executante_codigo ?? $cnpjOrigem;
            $nome = $guia->contratado_executante_nome ?? ($account->name ?? 'CLINICA PADRAO');
            $cnes = $guia->cnes_executante ?? preg_replace('/[^0-9]/', '', $account->cnes ?? '1234567');
            $medico = $guia->medico_nome ?? 'MEDICO';

            if ($tipoGuia == 'SADT') {
                $xmlString .= '                <ans:guiaSP-SADT>' . "\n";
                $xmlString .= '                    <ans:cabecalhoGuia>' . "\n";
                $xmlString .= '                        <ans:registroANS>' . $registroANS . '</ans:registroANS>' . "\n";
                $xmlString .= '                        <ans:numeroGuiaPrestador>' . $numeroGuia . '</ans:numeroGuiaPrestador>' . "\n";
                $xmlString .= '                    </ans:cabecalhoGuia>' . "\n";
                $xmlString .= '                    <ans:dadosBeneficiario>' . "\n";
                $xmlString .= '                        <ans:numeroCarteira>' . $carteira . '</ans:numeroCarteira>' . "\n";
                $xmlString .= '                        <ans:atendimentoRN>N</ans:atendimentoRN>' . "\n";
                $xmlString .= '                    </ans:dadosBeneficiario>' . "\n";
                $xmlString .= '                    <ans:dadosSolicitante>' . "\n";
                $xmlString .= '                        <ans:contratadoSolicitante>' . "\n";
                $xmlString .= '                            <ans:codigoPrestadorNaOperadora>' . $cnpj . '</ans:codigoPrestadorNaOperadora>' . "\n";
                $xmlString .= '                        </ans:contratadoSolicitante>' . "\n";
                $xmlString .= '                        <ans:nomeContratadoSolicitante>' . substr($nome, 0, 70) . '</ans:nomeContratadoSolicitante>' . "\n";
                $xmlString .= '                        <ans:profissionalSolicitante>' . "\n";
                $xmlString .= '                            <ans:nomeProfissional>' . substr($medico, 0, 70) . '</ans:nomeProfissional>' . "\n";
                $xmlString .= '                            <ans:conselhoProfissional>06</ans:conselhoProfissional>' . "\n";
                $xmlString .= '                            <ans:numeroConselhoProfissional>12345</ans:numeroConselhoProfissional>' . "\n";
                $xmlString .= '                            <ans:UF>35</ans:UF>' . "\n";
                $xmlString .= '                            <ans:CBOS>225125</ans:CBOS>' . "\n";
                $xmlString .= '                        </ans:profissionalSolicitante>' . "\n";
                $xmlString .= '                    </ans:dadosSolicitante>' . "\n";
                $xmlString .= '                    <ans:dadosSolicitacao>' . "\n";
                $xmlString .= '                        <ans:caraterAtendimento>1</ans:caraterAtendimento>' . "\n";
                $xmlString .= '                    </ans:dadosSolicitacao>' . "\n";
                $xmlString .= '                    <ans:dadosExecutante>' . "\n";
                $xmlString .= '                        <ans:contratadoExecutante>' . "\n";
                $xmlString .= '                            <ans:codigoPrestadorNaOperadora>' . $cnpj . '</ans:codigoPrestadorNaOperadora>' . "\n";
                $xmlString .= '                        </ans:contratadoExecutante>' . "\n";
                $xmlString .= '                        <ans:CNES>' . $cnes . '</ans:CNES>' . "\n";
                $xmlString .= '                    </ans:dadosExecutante>' . "\n";
                $xmlString .= '                    <ans:dadosAtendimento>' . "\n";
                $xmlString .= '                        <ans:tipoAtendimento>01</ans:tipoAtendimento>' . "\n";
                $xmlString .= '                        <ans:indicacaoAcidente>9</ans:indicacaoAcidente>' . "\n";
                $xmlString .= '                        <ans:regimeAtendimento>01</ans:regimeAtendimento>' . "\n";
                $xmlString .= '                    </ans:dadosAtendimento>' . "\n";
                $xmlString .= '                    <ans:valorTotal>' . "\n";
                $xmlString .= '                        <ans:valorTotalGeral>' . $valorTotal . '</ans:valorTotalGeral>' . "\n";
                $xmlString .= '                    </ans:valorTotal>' . "\n";
                $xmlString .= '                </ans:guiaSP-SADT>' . "\n";
            } else {
                $xmlString .= '                <ans:guiaConsulta>' . "\n";
                $xmlString .= '                    <ans:cabecalhoConsulta>' . "\n";
                $xmlString .= '                        <ans:registroANS>' . $registroANS . '</ans:registroANS>' . "\n";
                $xmlString .= '                        <ans:numeroGuiaPrestador>' . $numeroGuia . '</ans:numeroGuiaPrestador>' . "\n";
                $xmlString .= '                    </ans:cabecalhoConsulta>' . "\n";
                $xmlString .= '                    <ans:dadosBeneficiario>' . "\n";
                $xmlString .= '                        <ans:numeroCarteira>' . $carteira . '</ans:numeroCarteira>' . "\n";
                $xmlString .= '                        <ans:atendimentoRN>N</ans:atendimentoRN>' . "\n";
                $xmlString .= '                    </ans:dadosBeneficiario>' . "\n";
                $xmlString .= '                    <ans:contratadoExecutante>' . "\n";
                $xmlString .= '                        <ans:codigoPrestadorNaOperadora>' . $cnpj . '</ans:codigoPrestadorNaOperadora>' . "\n";
                $xmlString .= '                        <ans:CNES>' . $cnes . '</ans:CNES>' . "\n";
                $xmlString .= '                    </ans:contratadoExecutante>' . "\n";
                $xmlString .= '                    <ans:profissionalExecutante>' . "\n";
                $xmlString .= '                        <ans:nomeProfissional>' . substr($medico, 0, 70) . '</ans:nomeProfissional>' . "\n";
                $xmlString .= '                        <ans:conselhoProfissional>06</ans:conselhoProfissional>' . "\n";
                $xmlString .= '                        <ans:numeroConselhoProfissional>12345</ans:numeroConselhoProfissional>' . "\n";
                $xmlString .= '                        <ans:UF>35</ans:UF>' . "\n";
                $xmlString .= '                        <ans:CBOS>225125</ans:CBOS>' . "\n";
                $xmlString .= '                    </ans:profissionalExecutante>' . "\n";
                $xmlString .= '                    <ans:indicacaoAcidente>9</ans:indicacaoAcidente>' . "\n";
                $xmlString .= '                    <ans:dadosAtendimento>' . "\n";
                $xmlString .= '                        <ans:regimeAtendimento>01</ans:regimeAtendimento>' . "\n";
                $xmlString .= '                        <ans:dataAtendimento>' . date('Y-m-d', strtotime($guia->data_solicitacao ?? now())) . '</ans:dataAtendimento>' . "\n";
                $xmlString .= '                        <ans:tipoConsulta>1</ans:tipoConsulta>' . "\n";
                $xmlString .= '                        <ans:procedimento>' . "\n";
                $xmlString .= '                            <ans:codigoTabela>22</ans:codigoTabela>' . "\n";
                $xmlString .= '                            <ans:codigoProcedimento>10101012</ans:codigoProcedimento>' . "\n";
                $xmlString .= '                            <ans:valorProcedimento>' . $valorTotal . '</ans:valorProcedimento>' . "\n";
                $xmlString .= '                        </ans:procedimento>' . "\n";
                $xmlString .= '                    </ans:dadosAtendimento>' . "\n";
                $xmlString .= '                </ans:guiaConsulta>' . "\n";
            }
        }

        $xmlString .= '            </ans:guiasTISS>' . "\n";
        $xmlString .= '        </ans:loteGuias>' . "\n";
        $xmlString .= '    </ans:prestadorParaOperadora>' . "\n";
        // TISS Hash: MD5 dos VALORES concatenados (sem tags), conforme especificação ANS
        $stringToHash = strip_tags($xmlString);
        $stringToHash = preg_replace('/\s+/', '', $stringToHash);
        $hashCalculado = md5($stringToHash);

        $xmlString .= '    <ans:epilogo>' . "\n";
        $xmlString .= '        <ans:hash>' . $hashCalculado . '</ans:hash>' . "\n";
        $xmlString .= '    </ans:epilogo>' . "\n";
        $xmlString .= '</ans:mensagemTISS>';

        return response($xmlString, 200, [
            'Content-Type' => 'text/xml',
            'Content-Disposition' => 'attachment; filename="Lote_Faturamento_' . str_pad($faturamento->id, 6, '0', STR_PAD_LEFT) . '.xml"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
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
            ->where(function ($q) use ($id) {
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
