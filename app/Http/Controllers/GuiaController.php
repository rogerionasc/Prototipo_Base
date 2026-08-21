<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agendamento;
use App\Models\Guia;
use App\Models\Atendimento;

class GuiaController extends Controller
{
    public function imprimirDaAgenda($agendamentoId)
    {
        $agendamento = Agendamento::with(['paciente', 'convenio', 'procedimento', 'agendaMedica.profissionalSaude.conselho', 'agendaMedica.profissionalSaude.especialidades'])->findOrFail($agendamentoId);

        // Fetch or create Guia based on Agendamento -> Atendimento
        $atendimento = Atendimento::with(['medico.conselho', 'medico.especialidades'])->where('agendamento_id', $agendamento->id)->first();
        
        // Get carteira info from pivot if available
        $pacienteConvenio = $agendamento->paciente?->convenios()
            ->where('convenios.id', $agendamento->convenio_id)
            ->first();
        $numeroCarteira = $pacienteConvenio?->pivot?->numero_carteira ?? '0000000000';
        $validadeCarteira = $pacienteConvenio?->pivot?->validade_carteira;
        $registroAns = $agendamento->convenio?->ans ?? '000000';

        $autorizacao = \App\Models\Autorizacao::whereHas('guia', function($q) use ($agendamento) { $q->where('agendamento_id', $agendamento->id); })->first();
        $senha = $autorizacao?->numero_autorizacao;
        $dataValidadeSenha = $autorizacao?->validade;
        $dataAutorizacao = $autorizacao ? ($autorizacao->data_resposta ?? $autorizacao->data_solicitacao ?? $autorizacao->created_at?->format('Y-m-d')) : null;

        $guia = null;
        if ($atendimento && $atendimento->guia_id) {
            $guia = Guia::find($atendimento->guia_id);
            if ($guia) {
                // Sincroniza dados básicos de convênio caso tenham sido alterados/não carregados
                $guia->update([
                    'ans_registro' => $registroAns,
                    'numero_carteira' => $numeroCarteira,
                    'validade_carteira' => $validadeCarteira,
                    'data_autorizacao' => $dataAutorizacao,
                    'senha' => $senha,
                    'data_validade_senha' => $dataValidadeSenha,
                    'beneficiario_nome' => $agendamento->paciente?->nome ?? 'Paciente',
                    'cns' => $agendamento->paciente?->cns,
                    'contratado_solicitante_codigo' => $agendamento->agendaMedica?->profissionalSaude?->cpf ?? '000000000',
                    'contratado_solicitante_nome' => $agendamento->agendaMedica?->profissionalSaude?->nome ?? 'Profissional',
                    'conselho_solicitante' => $agendamento->agendaMedica?->profissionalSaude?->conselho?->codigo ?? 'CR',
                    'numero_conselho_solicitante' => $agendamento->agendaMedica?->profissionalSaude?->numero_conselho ?? '000000',
                    'uf_conselho_solicitante' => $agendamento->agendaMedica?->profissionalSaude?->uf_conselho ?? 'SP',
                    'cbo_solicitante' => $agendamento->agendaMedica?->profissionalSaude?->especialidades?->first()?->codigo ?? '2251',
                ]);
            }
        }

        if (!$guia) {
            $origemId = $agendamento->agendamento_origem_id ?? $agendamento->id;
            $numeroGuiaPrestador = 'G' . str_pad($origemId, 8, '0', STR_PAD_LEFT);
            $guia = Guia::where('numero_guia_prestador', $numeroGuiaPrestador)->first();
        }

        if (!$guia) {
            abort(404, 'Nenhuma guia foi gerada para este agendamento.');
        }

        // Removido o bloco que forçava inserção de dados no banco durante a visualização da guia,
        // já que a impressão deve ser estritamente de leitura (read-only).

        $guia->load(['procedimentosSolicitados', 'procedimentosRealizados', 'profissionaisExecutantes']);

        return \Inertia\Inertia::render('Guias/Imprimir', [
            'guia' => $guia,
            'agendamento' => $agendamento
        ]);
    }
    public function getDadosDaAgenda($agendamentoId)
    {
        $agendamento = Agendamento::with(['paciente', 'convenio', 'procedimento', 'tuss', 'agendaMedica.profissionalSaude.conselho', 'agendaMedica.profissionalSaude.especialidades'])->findOrFail($agendamentoId);

        // Fetch or create Guia based on Agendamento -> Atendimento
        $atendimento = Atendimento::with(['medico.conselho', 'medico.especialidades'])->where('agendamento_id', $agendamento->id)->first();
        
        // Get carteira info from pivot if available
        $pacienteConvenio = $agendamento->paciente?->convenios()
            ->where('convenios.id', $agendamento->convenio_id)
            ->first();
        $numeroCarteira = $pacienteConvenio?->pivot?->numero_carteira ?? '0000000000';
        $validadeCarteira = $pacienteConvenio?->pivot?->validade_carteira;
        $registroAns = $agendamento->convenio?->ans ?? '000000';

        $autorizacao = \App\Models\Autorizacao::whereHas('guia', function($q) use ($agendamento) { $q->where('agendamento_id', $agendamento->id); })->first();
        $senha = $autorizacao?->numero_autorizacao;
        $dataValidadeSenha = $autorizacao?->validade;
        $dataAutorizacao = $autorizacao ? ($autorizacao->data_resposta ?? $autorizacao->data_solicitacao ?? $autorizacao->created_at?->format('Y-m-d')) : null;

        $guia = null;
        if ($atendimento && $atendimento->guia_id) {
            $guia = Guia::find($atendimento->guia_id);
            // We do NOT update the basic data here automatically when just fetching to edit,
            // otherwise we'd overwrite what the user might have saved previously.
        }

        if (!$guia) {
            $origemId = $agendamento->agendamento_origem_id ?? $agendamento->id;
            $numeroGuiaPrestador = 'G' . str_pad($origemId, 8, '0', STR_PAD_LEFT);
            $guia = Guia::where('numero_guia_prestador', $numeroGuiaPrestador)->first();
        }

        if (!$guia) {
            return response()->json([
                'error' => 'Nenhuma guia foi gerada para este agendamento. Se este agendamento é antigo, a geração automática estava desabilitada.'
            ], 404);
        }

        if ($guia->procedimentosSolicitados()->count() === 0) {
            $origemId = $agendamento->agendamento_origem_id ?? $agendamento->id;
            $allAgendamentos = Agendamento::where('id', $origemId)
                                          ->orWhere('agendamento_origem_id', $origemId)
                                          ->with('tuss')
                                          ->get();
            foreach ($allAgendamentos as $ag) {
                if ($ag->tuss) {
                    $guia->procedimentosSolicitados()->create([
                        'tabela_procedimento_solicitado' => '22',
                        'procedimento_solicitado_codigo' => $ag->tuss->codigo,
                        'procedimento_solicitado_descricao' => $ag->tuss->descricao,
                        'quantidade_solicitada' => 1,
                        'quantidade_autorizada' => 1,
                    ]);
                }
            }
        }

        if ($guia->procedimentosRealizados()->count() === 0 && $atendimento && $atendimento->status === 'ATENDIDO') {
            $origemId = $agendamento->agendamento_origem_id ?? $agendamento->id;
            $allAgendamentos = Agendamento::where('id', $origemId)
                                          ->orWhere('agendamento_origem_id', $origemId)
                                          ->with('tuss')
                                          ->get();
            foreach ($allAgendamentos as $ag) {
                if ($ag->tuss) {
                    $agAtendimento = \App\Models\Atendimento::where('agendamento_id', $ag->id)->where('status', 'ATENDIDO')->first();
                    if ($agAtendimento) {
                        $guia->procedimentosRealizados()->create([
                            'tabela_procedimento_realizado' => '22',
                            'procedimento_realizado_codigo' => $ag->tuss->codigo,
                            'procedimento_realizado_descricao' => $ag->tuss->descricao,
                            'quantidade_realizada' => 1,
                            'data_realizacao' => $ag->data,
                            'hora_inicial' => $ag->hora,
                            'valor_unitario' => $ag->tuss->total ?? 0,
                            'valor_total' => $ag->tuss->total ?? 0,
                        ]);
                    }
                }
            }
        }

        $conselhos = \App\Models\Conselho::orderBy('sigla')->get();
        $especialidades = \App\Models\Especialidade::orderBy('nome')->get();
        $carateresAtendimento = \App\Models\CaraterAtendimento::all();
        $tabelasReferencia = \App\Models\TabelaReferencia::all();
        $procedimentosTuss = \App\Models\Tuss::select('codigo', 'descricao', 'total')->orderBy('descricao')->get();
        $tiposAtendimento = \App\Models\TipoAtendimento::orderBy('codigo')->get();
        $indicacoesAcidente = \App\Models\IndicacaoIncidencia::orderBy('codigo')->get();
        $tiposConsulta = \App\Models\TipoConsulta::orderBy('codigo')->get();

        $origemId = $agendamento->agendamento_origem_id ?? $agendamento->id;
        $allAgendamentos = Agendamento::where('id', $origemId)
                                      ->orWhere('agendamento_origem_id', $origemId)
                                      ->get();
        foreach ($allAgendamentos as $ag) {
            $agAtendimento = \App\Models\Atendimento::where('agendamento_id', $ag->id)->with(['medico.conselho', 'medico.especialidades'])->first();
            if ($agAtendimento && $agAtendimento->medico) {
                $procRealizado = $guia->procedimentosRealizados()->where('procedimento_realizado_codigo', $ag->tuss->codigo ?? null)->first();
                if ($procRealizado) {
                    $profissional = $agAtendimento->medico;
                    $cpf = $profissional->cpf ?? '00000000000';
                    $existe = $procRealizado->profissionaisExecutantes()->where('profissional_executante_codigo', $cpf)->exists();
                    if (!$existe) {
                        $count = $procRealizado->profissionaisExecutantes()->count();
                        $procRealizado->profissionaisExecutantes()->create([
                        'sequencial_referencia' => $count + 1,
                        'grau_participacao' => '01',
                        'profissional_executante_codigo' => $cpf,
                        'profissional_executante_nome' => $profissional->nome ?? 'Profissional',
                        'conselho_executante' => $profissional->conselho?->codigo ?? 'CR',
                        'numero_conselho_executante' => $profissional->numero_conselho ?? '000000',
                        'uf_conselho_executante' => $profissional->uf_conselho ?? 'SP',
                        'cbo_executante' => $profissional->especialidades?->first()?->codigo ?? '2251',
                        'data_realizacao_serie' => $agAtendimento->hora_inicio ? \Carbon\Carbon::parse($agAtendimento->hora_inicio)->format('Y-m-d') : $ag->data,
                    ]);
                    }
                }
            }
        }

        $guia->load(['procedimentosSolicitados', 'procedimentosRealizados', 'profissionaisExecutantes']);

        return response()->json([
            'guia' => $guia,
            'agendamento' => $agendamento,
            'conselhos' => $conselhos,
            'especialidades' => $especialidades,
            'carateres' => $carateresAtendimento,
            'tabelas' => $tabelasReferencia,
            'procedimentos' => $procedimentosTuss,
            'tiposAtendimento' => $tiposAtendimento,
            'indicacoesAcidente' => $indicacoesAcidente,
            'tiposConsulta' => $tiposConsulta
        ]);
    }

    public function update(Request $request, $id)
    {
        $guia = Guia::findOrFail($id);
        
        $data = $request->except(['procedimentos_solicitados', 'procedimentos_realizados', 'profissionais_executantes']);
        
        // A tabela guias não possui campos nullable. O Laravel converte strings vazias ('') 
        // para null por padrão (ConvertEmptyStringsToNull).
        // Precisamos reverter isso para os campos não falharem na restrição NOT NULL.
        foreach ($data as $key => $value) {
            if (is_null($value) || $value === '') {
                // Campos de data, hora, chaves estrangeiras e timestamps (deleted_at) são nullable no banco, então mantemos null
                if (str_starts_with($key, 'data_') || str_starts_with($key, 'hora_') || str_ends_with($key, '_id') || str_ends_with($key, '_at') || $key === 'validade_carteira') {
                    continue;
                }

                if (in_array($key, ['atendimento_rn', 'tempo_doenca', 'quantidade_diarias'])) {
                    $data[$key] = 0;
                } elseif (str_contains($key, 'valor_') || str_contains($key, 'total_') || str_contains($key, 'fator_') || str_contains($key, 'taxa_')) {
                    $data[$key] = 0.00;
                } else {
                    $data[$key] = '';
                }
            } elseif (is_string($value) && str_contains($value, 'T') && str_ends_with($value, 'Z')) {
                $data[$key] = date('Y-m-d H:i:s', strtotime($value));
            }
        }

        if (isset($data['carater_atendimento'])) {
            $data['carater_atendimento'] = (string) (int) $data['carater_atendimento'];
        }

        // Sync relationships
        if ($request->has('procedimentos_solicitados')) {
            $keepIds = [];
            foreach ($request->input('procedimentos_solicitados', []) as $proc) {
                unset($proc['created_at'], $proc['updated_at']);
                if (!empty($proc['id'])) {
                    $guia->procedimentosSolicitados()->where('id', $proc['id'])->update($proc);
                    $keepIds[] = $proc['id'];
                } else {
                    $keepIds[] = $guia->procedimentosSolicitados()->create($proc)->id;
                }
            }
            $guia->procedimentosSolicitados()->whereNotIn('id', $keepIds)->delete();
        }

        $totalProcedimentos = 0;
        if ($request->has('procedimentos_realizados')) {
            $keepIds = [];
            foreach ($request->input('procedimentos_realizados', []) as $proc) {
                unset($proc['created_at'], $proc['updated_at']);
                
                $qtd = floatval($proc['quantidade_realizada'] ?? 0);
                $vUnit = floatval($proc['valor_unitario'] ?? 0);
                $fatorStr = trim(str_replace(',', '.', $proc['fator_reducao_acrescimo'] ?? ''));
                $fator = $fatorStr === '' ? 1 : floatval($fatorStr);
                
                $proc['valor_total'] = round($qtd * $vUnit * $fator, 2);
                $totalProcedimentos += $proc['valor_total'];

                if (!empty($proc['id'])) {
                    $guia->procedimentosRealizados()->where('id', $proc['id'])->update($proc);
                    $keepIds[] = $proc['id'];
                } else {
                    $keepIds[] = $guia->procedimentosRealizados()->create($proc)->id;
                }
            }
            $guia->procedimentosRealizados()->whereNotIn('id', $keepIds)->delete();
        }

        $data['total_procedimentos'] = $totalProcedimentos;
        $data['valor_total_geral'] = 
            $totalProcedimentos +
            floatval($data['total_taxas_alugueis'] ?? 0) +
            floatval($data['total_materiais'] ?? 0) +
            floatval($data['total_opme'] ?? 0) +
            floatval($data['total_medicamentos'] ?? 0) +
            floatval($data['total_gases_medicinais'] ?? 0);

        if ($request->has('profissionais_executantes')) {
            $keepIds = [];
            foreach ($request->input('profissionais_executantes', []) as $prof) {
                unset($prof['created_at'], $prof['updated_at'], $prof['laravel_through_key']);
                if (!empty($prof['id'])) {
                    \App\Models\GuiaProfissionalExecutante::where('id', $prof['id'])->update($prof);
                    $keepIds[] = $prof['id'];
                } else {
                    $keepIds[] = \App\Models\GuiaProfissionalExecutante::create($prof)->id;
                }
            }
            $guia->profissionaisExecutantes()->whereNotIn('guia_profissional_executantes.id', $keepIds)->delete();
        }
        
        $guia->update($data);

        return response()->json([
            'message' => 'Guia atualizada com sucesso',
            'guia' => $guia->load(['procedimentosSolicitados', 'procedimentosRealizados', 'profissionaisExecutantes'])
        ]);
    }
}