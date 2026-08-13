<template>

    <Head title="Guia de Serviço Profissional / SADT" />
    <div class="print-page-wrapper">
        <div class="guia-container">
            <!-- LOGO & DADOS INICIAIS -->
            <div class="row" style="border-top: none; border-left: none; border-right: none;">
                <div style="width: 20%; padding: 5px;">
                    <!-- Logo Provider -->
                    <img src="/images/logo.png" style="max-width: 100%; max-height: 50px;" alt="Logo"
                        @error="$event.target.style.display = 'none'">
                </div>
                <div style="width: 80%;">
                    <div class="header-title">GUIA DE SERVIÇO PROFISSIONAL / SERVIÇO AUXILIAR DE DIAGNÓSTICO E TERAPIA -
                        SP/SADT</div>
                    <div class="row" style="border-top: none;">
                        <div class="box" style="width: 25%;">
                            <div class="box-title">1 - Registro ANS</div>
                            <div class="box-value">{{ guia.ans_registro }}</div>
                        </div>
                        <div class="box" style="width: 35%;">
                            <div class="box-title">2 - Número da Guia no Prestador</div>
                            <div class="box-value">{{ guia.numero_guia_prestador }}</div>
                        </div>
                        <div class="box" style="width: 40%;">
                            <div class="box-title">3 - Número da Guia Principal</div>
                            <div class="box-value">{{ guia.numero_guia_principal }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AUTORIZACAO -->
            <div class="row">
                <div class="box" style="width: 20%;">
                    <div class="box-title">4 - Data da Autorização</div>
                    <div class="box-value">{{ formatDate(guia.data_autorizacao) }}</div>
                </div>
                <div class="box" style="width: 30%;">
                    <div class="box-title">5 - Senha</div>
                    <div class="box-value">{{ guia.senha }}</div>
                </div>
                <div class="box" style="width: 20%;">
                    <div class="box-title">6 - Data de Validade da Senha</div>
                    <div class="box-value">{{ formatDate(guia.data_validade_senha) }}</div>
                </div>
                <div class="box" style="width: 30%;">
                    <div class="box-title">7 - Nº da Guia Atribuído pela Operadora</div>
                    <div class="box-value">{{ guia.numero_guia_operadora }}</div>
                </div>
            </div>

            <!-- DADOS DO BENEFICIARIO -->
            <div class="section-title">Dados do Beneficiário</div>
            <div class="row">
                <div class="box" style="width: 30%;">
                    <div class="box-title">8 - Número da Carteira</div>
                    <div class="box-value">{{ guia.numero_carteira }}</div>
                </div>
                <div class="box" style="width: 20%;">
                    <div class="box-title">9 - Validade da Carteira</div>
                    <div class="box-value">{{ formatDate(guia.validade_carteira) }}</div>
                </div>
                <div class="box" style="width: 50%;">
                    <div class="box-title">10 - Nome</div>
                    <div class="box-value">{{ guia.beneficiario_nome }}</div>
                </div>
            </div>
            <div class="row">
                <div class="box" style="width: 30%;">
                    <div class="box-title">11 - Cartão Nacional de Saúde (CNS)</div>
                    <div class="box-value">{{ guia.cns }}</div>
                </div>
                <div class="box" style="width: 70%;">
                    <div class="box-title">12 - Atendimento a RN</div>
                    <div class="box-value">{{ guia.atendimento_rn ? 'Sim' : 'Não' }}</div>
                </div>
            </div>

            <!-- DADOS DO SOLICITANTE -->
            <div class="section-title">Dados do Solicitante</div>
            <div class="row">
                <div class="box" style="width: 30%;">
                    <div class="box-title">13 - Código na Operadora / CPF</div>
                    <div class="box-value">{{ guia.contratado_solicitante_codigo }}</div>
                </div>
                <div class="box" style="width: 70%;">
                    <div class="box-title">14 - Nome do Contratado Solicitante</div>
                    <div class="box-value">{{ guia.contratado_solicitante_nome }}</div>
                </div>
            </div>
            <div class="row">
                <div class="box" style="width: 50%;">
                    <div class="box-title">15 - Nome do Profissional Solicitante</div>
                    <div class="box-value">{{ guia.profissional_solicitante_nome }}</div>
                </div>
                <div class="box" style="width: 10%;">
                    <div class="box-title">16 - Conselho</div>
                    <div class="box-value">{{ guia.conselho_solicitante }}</div>
                </div>
                <div class="box" style="width: 20%;">
                    <div class="box-title">17 - Número do Conselho</div>
                    <div class="box-value">{{ guia.numero_conselho_solicitante }}</div>
                </div>
                <div class="box" style="width: 10%;">
                    <div class="box-title">18 - UF</div>
                    <div class="box-value">{{ guia.uf_conselho_solicitante }}</div>
                </div>
                <div class="box" style="width: 10%;">
                    <div class="box-title">19 - CBO S</div>
                    <div class="box-value">{{ guia.cbo_solicitante }}</div>
                </div>
            </div>
            <div class="row">
                <div class="box" style="width: 100%; height: 40px; text-align: center; vertical-align: bottom;">
                    <div class="box-title">20 - Assinatura do Profissional Solicitante</div>
                    <div style="border-bottom: 1px solid #000; width: 80%; margin: 15px auto 0;"></div>
                </div>
            </div>

            <!-- DADOS DA SOLICITACAO -->
            <div class="section-title">Dados da Solicitação / Procedimentos e Exames Solicitados</div>
            <div class="row">
                <div class="box" style="width: 15%;">
                    <div class="box-title">21 - Caráter do Atendimento</div>
                    <div class="box-value">{{ guia.carater_atendimento == '1' ? '1 - Eletivo' : '2 - Urgência' }}</div>
                </div>
                <div class="box" style="width: 15%;">
                    <div class="box-title">22 - Data da Solicitação</div>
                    <div class="box-value">{{ formatDate(guia.data_solicitacao) }}</div>
                </div>
                <div class="box" style="width: 70%;">
                    <div class="box-title">23 - Indicação Clínica</div>
                    <div class="box-value">{{ guia.indicacao_clinica }}</div>
                </div>
            </div>

            <div class="row" style="background-color: #eee;">
                <div class="box" style="width: 10%;">
                    <div class="box-title">24 - Tabela</div>
                </div>
                <div class="box" style="width: 15%;">
                    <div class="box-title">25 - Código Proced.</div>
                </div>
                <div class="box" style="width: 55%;">
                    <div class="box-title">26 - Descrição</div>
                </div>
                <div class="box" style="width: 10%;">
                    <div class="box-title">27 - Qtd. Sol.</div>
                </div>
                <div class="box" style="width: 10%;">
                    <div class="box-title">28 - Qtd. Aut.</div>
                </div>
            </div>
            <div class="row">
                <div class="box" style="width: 10%;">
                    <div class="box-value">{{ guia.tabela_procedimento_solicitado }}</div>
                </div>
                <div class="box" style="width: 15%;">
                    <div class="box-value">{{ guia.procedimento_solicitado_codigo }}</div>
                </div>
                <div class="box" style="width: 55%;">
                    <div class="box-value">{{ guia.procedimento_solicitado_descricao }}</div>
                </div>
                <div class="box" style="width: 10%;">
                    <div class="box-value">{{ guia.quantidade_solicitada }}</div>
                </div>
                <div class="box" style="width: 10%;">
                    <div class="box-value">{{ guia.quantidade_autorizada }}</div>
                </div>
            </div>

            <!-- DADOS DO CONTRATADO EXECUTANTE -->
            <div class="section-title">Dados do Contratado Executante</div>
            <div class="row">
                <div class="box" style="width: 30%;">
                    <div class="box-title">29 - Código na Operadora / CPF / CNPJ</div>
                    <div class="box-value">{{ guia.contratado_executante_codigo }}</div>
                </div>
                <div class="box" style="width: 50%;">
                    <div class="box-title">30 - Nome do Contratado Executante</div>
                    <div class="box-value">{{ guia.contratado_executante_nome }}</div>
                </div>
                <div class="box" style="width: 20%;">
                    <div class="box-title">31 - Código CNES</div>
                    <div class="box-value">{{ guia.cnes_executante }}</div>
                </div>
            </div>

            <!-- DADOS DO ATENDIMENTO -->
            <div class="section-title">Dados do Atendimento</div>
            <div class="row">
                <div class="box" style="width: 20%;">
                    <div class="box-title">32 - Tipo de Atendimento</div>
                    <div class="box-value">{{ guia.tipo_atendimento }}</div>
                </div>
                <div class="box" style="width: 20%;">
                    <div class="box-title">33 - Indicação de Acidente</div>
                    <div class="box-value">{{ guia.indicacao_acidente }}</div>
                </div>
                <div class="box" style="width: 30%;">
                    <div class="box-title">34 - Tipo de Consulta</div>
                    <div class="box-value">{{ guia.tipo_consulta }}</div>
                </div>
                <div class="box" style="width: 30%;">
                    <div class="box-title">35 - Motivo de Encerramento</div>
                    <div class="box-value">{{ guia.motivo_encerramento }}</div>
                </div>
            </div>

            <div class="row" style="background-color: #eee;">
                <div class="box" style="width: 20%;">
                    <div class="box-title">36 - Data de Realização</div>
                </div>
                <div class="box" style="width: 20%;">
                    <div class="box-title">37 - Hora Inicial</div>
                </div>
                <div class="box" style="width: 60%;">
                    <div class="box-title">38 - Hora Final</div>
                </div>
            </div>
            <div class="row">
                <div class="box" style="width: 20%;">
                    <div class="box-value">{{ formatDate(guia.data_realizacao) }}</div>
                </div>
                <div class="box" style="width: 20%;">
                    <div class="box-value">{{ guia.hora_inicial }}</div>
                </div>
                <div class="box" style="width: 60%;">
                    <div class="box-value">{{ guia.hora_final }}</div>
                </div>
            </div>

            <!-- PROCEDIMENTOS REALIZADOS -->
            <div class="section-title">Dados da Execução / Procedimentos e Exames Realizados</div>
            <div class="row" style="background-color: #eee;">
                <div class="box" style="width: 5%;">
                    <div class="box-title">39-Tab</div>
                </div>
                <div class="box" style="width: 15%;">
                    <div class="box-title">40 - Código Proced.</div>
                </div>
                <div class="box" style="width: 40%;">
                    <div class="box-title">41 - Descrição</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-title">42-Qtd</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-title">43-Via</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-title">44-Téc</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-title">45-Fat</div>
                </div>
                <div class="box" style="width: 10%;">
                    <div class="box-title">46-Val Unit</div>
                </div>
                <div class="box" style="width: 10%;">
                    <div class="box-title">47-Val Tot</div>
                </div>
            </div>
            <div class="row">
                <div class="box" style="width: 5%;">
                    <div class="box-value">{{ guia.tabela_procedimento_realizado }}</div>
                </div>
                <div class="box" style="width: 15%;">
                    <div class="box-value">{{ guia.procedimento_realizado_codigo }}</div>
                </div>
                <div class="box" style="width: 40%;">
                    <div class="box-value">{{ guia.procedimento_realizado_descricao }}</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-value">{{ guia.quantidade_realizada }}</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-value">{{ guia.via_acesso }}</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-value">{{ guia.tecnica_utilizada }}</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-value">{{ guia.fator_reducao_acrescimo }}</div>
                </div>
                <div class="box" style="width: 10%;">
                    <div class="box-value">{{ guia.valor_unitario }}</div>
                </div>
                <div class="box" style="width: 10%;">
                    <div class="box-value">{{ guia.valor_total }}</div>
                </div>
            </div>

            <div class="row" v-for="i in 1" :key="'exec-' + i">
                <div class="box" style="width: 5%;">
                    <div class="box-value">&nbsp;</div>
                </div>
                <div class="box" style="width: 15%;">
                    <div class="box-value">&nbsp;</div>
                </div>
                <div class="box" style="width: 40%;">
                    <div class="box-value">&nbsp;</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-value">&nbsp;</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-value">&nbsp;</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-value">&nbsp;</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-value">&nbsp;</div>
                </div>
                <div class="box" style="width: 10%;">
                    <div class="box-value">&nbsp;</div>
                </div>
                <div class="box" style="width: 10%;">
                    <div class="box-value">&nbsp;</div>
                </div>
            </div>

            <!-- PROFISSIONAIS EXECUTANTES -->
            <div class="section-title">Identificação dos Profissionais Executantes</div>
            <div class="row" style="background-color: #eee;">
                <div class="box" style="width: 5%;">
                    <div class="box-title">48-Seq</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-title">49-Gra</div>
                </div>
                <div class="box" style="width: 15%;">
                    <div class="box-title">50-Cod na Operadora</div>
                </div>
                <div class="box" style="width: 30%;">
                    <div class="box-title">51-Nome do Profissional</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-title">52-Cons</div>
                </div>
                <div class="box" style="width: 10%;">
                    <div class="box-title">53-Nº Cons</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-title">54-UF</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-title">55-CBO</div>
                </div>
                <div class="box" style="width: 20%;">
                    <div class="box-title">56-Data / 57-Assinatura</div>
                </div>
            </div>
            <div class="row">
                <div class="box" style="width: 5%;">
                    <div class="box-value">{{ guia.sequencial_referencia }}</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-value">{{ guia.grau_participacao }}</div>
                </div>
                <div class="box" style="width: 15%;">
                    <div class="box-value">{{ guia.profissional_executante_codigo }}</div>
                </div>
                <div class="box" style="width: 30%;">
                    <div class="box-value">{{ guia.profissional_executante_nome }}</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-value">{{ guia.conselho_executante }}</div>
                </div>
                <div class="box" style="width: 10%;">
                    <div class="box-value">{{ guia.numero_conselho_executante }}</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-value">{{ guia.uf_conselho_executante }}</div>
                </div>
                <div class="box" style="width: 5%;">
                    <div class="box-value">{{ guia.cbo_executante }}</div>
                </div>
                <div class="box" style="width: 20%;">
                    <div class="box-value"></div>
                </div>
            </div>

            <!-- OBSERVACOES -->
            <div class="row">
                <div class="box" style="width: 100%;">
                    <div class="box-title">58 - Observação / Justificativa</div>
                    <div class="box-value" style="min-height: 40px; white-space: normal;">{{
                        guia.observacao_justificativa }}</div>
                </div>
            </div>

            <!-- TOTAIS -->
            <div class="row" style="background-color: #eee;">
                <div class="box" style="width: 15%;">
                    <div class="box-title">59 - Total Procedimentos</div>
                </div>
                <div class="box" style="width: 15%;">
                    <div class="box-title">60 - Total Taxas/Aluguéis</div>
                </div>
                <div class="box" style="width: 15%;">
                    <div class="box-title">61 - Total Materiais</div>
                </div>
                <div class="box" style="width: 15%;">
                    <div class="box-title">62 - Total OPME</div>
                </div>
                <div class="box" style="width: 20%;">
                    <div class="box-title">63 - Total Medicamentos</div>
                </div>
                <div class="box" style="width: 20%;">
                    <div class="box-title">64 - Total Gases Medicinais</div>
                </div>
            </div>
            <div class="row">
                <div class="box" style="width: 15%;">
                    <div class="box-value">{{ guia.total_procedimentos }}</div>
                </div>
                <div class="box" style="width: 15%;">
                    <div class="box-value">{{ guia.total_taxas_alugueis }}</div>
                </div>
                <div class="box" style="width: 15%;">
                    <div class="box-value">{{ guia.total_materiais }}</div>
                </div>
                <div class="box" style="width: 15%;">
                    <div class="box-value">{{ guia.total_opme }}</div>
                </div>
                <div class="box" style="width: 20%;">
                    <div class="box-value">{{ guia.total_medicamentos }}</div>
                </div>
                <div class="box" style="width: 20%;">
                    <div class="box-value">{{ guia.total_gases_medicinais }}</div>
                </div>
            </div>
            <div class="row">
                <div class="box" style="width: 70%;"></div>
                <div class="box" style="width: 30%; background-color: #eee;">
                    <div class="box-title" style="font-weight: bold; font-size: 10px;">65 - Valor Total Geral</div>
                    <div class="box-value" style="font-size: 14px;">R$ {{ formatCurrency(guia.valor_total_geral) }}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="box" style="width: 30%; height: 25px; text-align: center;">
                    <div class="box-title">66 - Assinatura do Responsável (Autorização)</div>
                    <div style="border-bottom: 1px solid #000; width: 80%; margin: 10px auto 0;"></div>
                </div>
                <div class="box" style="width: 35%; height: 25px; text-align: center;">
                    <div class="box-title">67 - Assinatura do Beneficiário</div>
                    <div style="border-bottom: 1px solid #000; width: 80%; margin: 10px auto 0;"></div>
                </div>
                <div class="box" style="width: 35%; height: 25px; text-align: center;">
                    <div class="box-title">68 - Assinatura do Contratado Executante</div>
                    <div style="border-bottom: 1px solid #000; width: 80%; margin: 10px auto 0;"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    guia: {
        type: Object,
        required: true,
    },
    agendamento: {
        type: Object,
        required: false,
    }
});

const printGuia = () => {
    window.print();
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    // Assumes YYYY-MM-DD
    const datePattern = /^(\d{4})-(\d{2})-(\d{2})/;
    const match = dateString.match(datePattern);
    if (match) {
        return `${match[3]}/${match[2]}/${match[1]}`;
    }
    // If it's a full ISO timestamp
    try {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('pt-BR').format(date);
    } catch (e) {
        return dateString;
    }
};

const formatCurrency = (value) => {
    const num = Number(value) || 0;
    return num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<style>
/* 
   We keep this unscoped and as a standard <style> tag so that @page and @media print 
   can properly affect the whole document body during printing without Vue's scoping issues.
*/
.print-page-wrapper * {
    box-sizing: border-box;
}

.print-page-wrapper {
    font-family: Arial, sans-serif;
    font-size: 9px;
    margin: 0;
    padding: 0;
    color: #000;
}

.guia-container {
    width: 100%;
    max-width: 1050px;
    margin: 0 auto !important;
    padding: 2px !important;
    border: 1px solid #000 !important;
    background-color: white;
}

.section-title {
    background-color: #eee;
    font-weight: bold;
    font-size: 9px;
    padding: 1px 2px !important;
    margin: 0 !important;
    border-bottom: 1px solid #000;
}

.header-title {
    text-align: center;
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 0px;
    background-color: #eee;
    padding: 2px;
    border-bottom: 1px solid #000;
    border-left: 1px solid #000;
}

.row {
    display: flex;
    width: 100%;
    margin: 0 !important;
    padding: 0 !important;
    border-bottom: 1px solid #000;
}

.row:first-of-type {
    /* guia-container already has top border */
}

.row:last-child {
    border-bottom: none;
}

.box {
    border-right: 1px solid #000;
    padding: 1px 2px !important;
    margin: 0 !important;
    display: flex;
    flex-direction: column;
    min-width: 0;
    overflow: hidden;
}

.box:last-child {
    border-right: none;
}

.box-title {
    font-size: 7px;
    color: #333;
    margin-bottom: 2px;
}

.box-value {
    font-size: 10px;
    font-weight: bold;
    min-height: 11px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

@page {
    size: A4 landscape;
    margin: 5mm;
}

.btn-print {
    display: block;
    margin: 10px auto;
    padding: 8px 16px;
    background: #007bff;
    color: #fff;
    border: none;
    cursor: pointer;
    font-size: 14px;
}

@media print {
    body {
        margin: 0;
        padding: 0;
        background-color: white;
    }

    .print-page-wrapper {
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 100%;
        border: none;
        margin: 0;
        padding: 0;
    }

    .btn-print {
        display: none !important;
    }

    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
