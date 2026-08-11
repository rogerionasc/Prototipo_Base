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
        $atendimento = Atendimento::where('agendamento_id', $agendamento->id)->first();
        
        // Get carteira info from pivot if available
        $pacienteConvenio = $agendamento->paciente?->convenios()
            ->where('convenios.id', $agendamento->convenio_id)
            ->first();
        $numeroCarteira = $pacienteConvenio?->pivot?->numero_carteira ?? '0000000000';
        $validadeCarteira = $pacienteConvenio?->pivot?->validade;
        $registroAns = $agendamento->convenio?->ans ?? '000000';

        $autorizacao = \App\Models\Autorizacao::where('agendamento_id', $agendamento->id)->first();
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
            // Create a generic Guia
            $guia = Guia::create([
                'faturamento_id' => null,
                'ans_registro' => $registroAns,
                'numero_guia_prestador' => 'G' . str_pad($agendamento->id, 8, '0', STR_PAD_LEFT),
                'numero_guia_principal' => null,
                'data_autorizacao' => $dataAutorizacao,
                'senha' => $senha,
                'data_validade_senha' => $dataValidadeSenha,
                'numero_guia_operadora' => null,
                'numero_carteira' => $numeroCarteira,
                'validade_carteira' => $validadeCarteira,
                'beneficiario_nome' => $agendamento->paciente?->nome ?? 'Paciente',
                'cns' => $agendamento->paciente?->cns,
                'atendimento_rn' => false,
                'contratado_solicitante_codigo' => $agendamento->agendaMedica?->profissionalSaude?->cpf ?? '000000000',
                'contratado_solicitante_nome' => $agendamento->agendaMedica?->profissionalSaude?->nome ?? 'Profissional',
                'profissional_solicitante_nome' => $agendamento->agendaMedica?->profissionalSaude?->nome ?? 'Profissional',
                'conselho_solicitante' => $agendamento->agendaMedica?->profissionalSaude?->conselho?->codigo ?? 'CR',
                'numero_conselho_solicitante' => $agendamento->agendaMedica?->profissionalSaude?->numero_conselho ?? '000000',
                'uf_conselho_solicitante' => $agendamento->agendaMedica?->profissionalSaude?->uf_conselho ?? 'SP',
                'cbo_solicitante' => $agendamento->agendaMedica?->profissionalSaude?->especialidades?->first()?->codigo ?? '2251',
                'assinatura_profissional_solicitante' => '',
                'carater_atendimento' => $agendamento->emergencia ? '2' : '1',
                'data_solicitacao' => $agendamento->data,
                'indicacao_clinica' => $agendamento->observacoes,
                'tabela_procedimento_solicitado' => '22',
                'procedimento_solicitado_codigo' => $agendamento->procedimento?->codigo ?? '00000000',
                'procedimento_solicitado_descricao' => $agendamento->procedimento?->descricao ?? 'Consulta',
                'quantidade_solicitada' => 1,
                'quantidade_autorizada' => 1,
                'contratado_executante_codigo' => '000000000',
                'contratado_executante_nome' => 'CLINICA PADRAO',
                'cnes_executante' => '0000000',
                'tipo_atendimento' => '01',
                'indicacao_acidente' => '9',
                'tipo_consulta' => null,
                'motivo_encerramento' => null,
                'data_realizacao' => null,
                'hora_inicial' => null,
                'hora_final' => null,
                'tabela_procedimento_realizado' => null,
                'procedimento_realizado_codigo' => null,
                'procedimento_realizado_descricao' => null,
                'quantidade_realizada' => null,
                'via_acesso' => null,
                'tecnica_utilizada' => null,
                'fator_reducao_acrescimo' => null,
                'valor_unitario' => null,
                'valor_total' => null,
                'sequencial_referencia' => null,
                'grau_participacao' => null,
                'profissional_executante_codigo' => null,
                'profissional_executante_nome' => null,
                'conselho_executante' => null,
                'numero_conselho_executante' => null,
                'uf_conselho_executante' => null,
                'cbo_executante' => null,
                'data_realizacao_serie' => null,
                'assinatura_beneficiario_serie' => '',
                'observacao_justificativa' => null,
                'total_procedimentos' => null,
                'total_taxas_alugueis' => null,
                'total_materiais' => null,
                'total_opme' => null,
                'total_medicamentos' => null,
                'total_gases_medicinais' => null,
                'valor_total_geral' => 0.00,
                'assinatura_responsavel_autorizacao' => '',
                'assinatura_beneficiario' => '',
                'assinatura_contratado' => '',
            ]);

            if ($atendimento) {
                $atendimento->update(['guia_id' => $guia->id]);
            }
        }

        return \Inertia\Inertia::render('Guias/Imprimir', [
            'guia' => $guia,
            'agendamento' => $agendamento
        ]);
    }
    public function getDadosDaAgenda($agendamentoId)
    {
        $agendamento = Agendamento::with(['paciente', 'convenio', 'procedimento', 'agendaMedica.profissionalSaude.conselho', 'agendaMedica.profissionalSaude.especialidades'])->findOrFail($agendamentoId);

        // Fetch or create Guia based on Agendamento -> Atendimento
        $atendimento = Atendimento::where('agendamento_id', $agendamento->id)->first();
        
        // Get carteira info from pivot if available
        $pacienteConvenio = $agendamento->paciente?->convenios()
            ->where('convenios.id', $agendamento->convenio_id)
            ->first();
        $numeroCarteira = $pacienteConvenio?->pivot?->numero_carteira ?? '0000000000';
        $validadeCarteira = $pacienteConvenio?->pivot?->validade;
        $registroAns = $agendamento->convenio?->ans ?? '000000';

        $autorizacao = \App\Models\Autorizacao::where('agendamento_id', $agendamento->id)->first();
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
            // Create a generic Guia
            $guia = Guia::create([
                'faturamento_id' => null,
                'ans_registro' => $registroAns,
                'numero_guia_prestador' => 'G' . str_pad($agendamento->id, 8, '0', STR_PAD_LEFT),
                'numero_guia_principal' => null,
                'data_autorizacao' => $dataAutorizacao,
                'senha' => $senha,
                'data_validade_senha' => $dataValidadeSenha,
                'numero_guia_operadora' => null,
                'numero_carteira' => $numeroCarteira,
                'validade_carteira' => $validadeCarteira,
                'beneficiario_nome' => $agendamento->paciente?->nome ?? 'Paciente',
                'cns' => $agendamento->paciente?->cns,
                'atendimento_rn' => false,
                'contratado_solicitante_codigo' => $agendamento->agendaMedica?->profissionalSaude?->cpf ?? '000000000',
                'contratado_solicitante_nome' => $agendamento->agendaMedica?->profissionalSaude?->nome ?? 'Profissional',
                'profissional_solicitante_nome' => $agendamento->agendaMedica?->profissionalSaude?->nome ?? 'Profissional',
                'conselho_solicitante' => $agendamento->agendaMedica?->profissionalSaude?->conselho?->codigo ?? 'CR',
                'numero_conselho_solicitante' => $agendamento->agendaMedica?->profissionalSaude?->numero_conselho ?? '000000',
                'uf_conselho_solicitante' => $agendamento->agendaMedica?->profissionalSaude?->uf_conselho ?? 'SP',
                'cbo_solicitante' => $agendamento->agendaMedica?->profissionalSaude?->especialidades?->first()?->codigo ?? '2251',
                'assinatura_profissional_solicitante' => '',
                'carater_atendimento' => $agendamento->emergencia ? '2' : '1',
                'data_solicitacao' => $agendamento->data,
                'indicacao_clinica' => $agendamento->observacoes,
                'tabela_procedimento_solicitado' => '22',
                'procedimento_solicitado_codigo' => $agendamento->procedimento?->codigo ?? '00000000',
                'procedimento_solicitado_descricao' => $agendamento->procedimento?->descricao ?? 'Consulta',
                'quantidade_solicitada' => 1,
                'quantidade_autorizada' => 1,
                'contratado_executante_codigo' => '000000000',
                'contratado_executante_nome' => 'CLINICA PADRAO',
                'cnes_executante' => '0000000',
                'tipo_atendimento' => '01',
                'indicacao_acidente' => '9',
                'tipo_consulta' => null,
                'motivo_encerramento' => null,
                'data_realizacao' => null,
                'hora_inicial' => null,
                'hora_final' => null,
                'tabela_procedimento_realizado' => null,
                'procedimento_realizado_codigo' => null,
                'procedimento_realizado_descricao' => null,
                'quantidade_realizada' => null,
                'via_acesso' => null,
                'tecnica_utilizada' => null,
                'fator_reducao_acrescimo' => null,
                'valor_unitario' => null,
                'valor_total' => null,
                'sequencial_referencia' => null,
                'grau_participacao' => null,
                'profissional_executante_codigo' => null,
                'profissional_executante_nome' => null,
                'conselho_executante' => null,
                'numero_conselho_executante' => null,
                'uf_conselho_executante' => null,
                'cbo_executante' => null,
                'data_realizacao_serie' => null,
                'assinatura_beneficiario_serie' => '',
                'observacao_justificativa' => null,
                'total_procedimentos' => null,
                'total_taxas_alugueis' => null,
                'total_materiais' => null,
                'total_opme' => null,
                'total_medicamentos' => null,
                'total_gases_medicinais' => null,
                'valor_total_geral' => 0.00,
                'assinatura_responsavel_autorizacao' => '',
                'assinatura_beneficiario' => '',
                'assinatura_contratado' => '',
            ]);

            if ($atendimento) {
                $atendimento->update(['guia_id' => $guia->id]);
            }
        }

        return response()->json([
            'guia' => $guia,
            'agendamento' => $agendamento
        ]);
    }

    public function update(Request $request, $id)
    {
        $guia = Guia::findOrFail($id);
        
        $data = $request->all();
        
        // A tabela guias não possui campos nullable. O Laravel converte strings vazias ('') 
        // para null por padrão (ConvertEmptyStringsToNull).
        // Precisamos reverter isso para os campos não falharem na restrição NOT NULL.
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                // Campos de data, hora, chaves estrangeiras e timestamps (deleted_at) são nullable no banco, então mantemos null
                if (str_starts_with($key, 'data_') || str_starts_with($key, 'hora_') || str_ends_with($key, '_id') || str_ends_with($key, '_at')) {
                    continue;
                }

                if (in_array($key, ['quantidade_solicitada', 'quantidade_autorizada', 'atendimento_rn', 'quantidade_realizada'])) {
                    $data[$key] = 0;
                } elseif (str_contains($key, 'valor_') || str_contains($key, 'total_') || str_contains($key, 'fator_')) {
                    $data[$key] = 0.00;
                } else {
                    $data[$key] = '';
                }
            }
        }

        $guia->update($data);
        
        return response()->json([
            'message' => 'Guia atualizada com sucesso',
            'guia' => $guia
        ]);
    }
}
