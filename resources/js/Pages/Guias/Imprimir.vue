<template>

    <Head title="Guia de Serviço Profissional / SADT" />



    <div class="print-page-wrapper">
        <div class="guia-container">

            <!-- ══ CABEÇALHO ══ -->
            <div class="g-row header-row">
                <div class="logo-cell">
                    <img :src="agendamento?.convenio?.logo_path ? '/storage/' + agendamento.convenio.logo_path : '/images/logo.png'"
                        alt="Logo" style="max-height:45px; max-width:100%;"
                        @error="$event.target.src = '/images/logo.png'" />
                </div>
                <div style="flex:1; display:flex; flex-direction:column; border-left:1px solid #000;">
                    <div class="guia-title">
                        GUIA DE SERVIÇO PROFISSIONAL / SERVIÇO AUXILIAR DE DIAGNÓSTICO E TERAPIA - SP/SADT
                    </div>
                    <div class="g-row" style="border-top:1px solid #000; border-bottom:none;">
                        <div class="g-cell" style="width:28%;">
                            <span class="cell-label">1 - Registro ANS</span>
                            <span class="cell-value">{{ guia.ans_registro }}</span>
                        </div>
                        <div class="g-cell" style="width:35%;">
                            <span class="cell-label">2 - Número da Guia no Prestador</span>
                            <span class="cell-value">{{ guia.numero_guia_prestador }}</span>
                        </div>
                        <div class="g-cell" style="width:37%; border-right:none;">
                            <span class="cell-label">3 - Número da Guia Principal</span>
                            <span class="cell-value">{{ guia.numero_guia_principal }}</span>
                        </div>
                    </div>
                </div>
                <div class="guia-num-cell">
                    <span class="cell-label">2 - No. Guia do Prestador</span>
                    <span class="cell-value">{{ guia.numero_guia_prestador }}</span>
                </div>
            </div>

            <!-- ══ AUTORIZAÇÃO ══ -->
            <div class="g-row">
                <div class="g-cell" style="width:18%;">
                    <span class="cell-label">4 - Data da Autorização</span>
                    <span class="cell-value">{{ formatDate(guia.data_autorizacao) }}</span>
                </div>
                <div class="g-cell" style="width:18%;">
                    <span class="cell-label">5 - Senha</span>
                    <span class="cell-value">{{ guia.senha }}</span>
                </div>
                <div class="g-cell" style="width:20%;">
                    <span class="cell-label">6 - Data Validade da Senha</span>
                    <span class="cell-value">{{ formatDate(guia.data_validade_senha) }}</span>
                </div>
                <div class="g-cell" style="width:44%; border-right:none;">
                    <span class="cell-label">7 - Número da Guia Atribuído pela Operadora</span>
                    <span class="cell-value">{{ guia.numero_guia_operadora }}</span>
                </div>
            </div>

            <!-- ══ BENEFICIÁRIO ══ -->
            <div class="section-bar">Dados do Beneficiário</div>
            <div class="g-row">
                <div class="g-cell" style="width:22%;">
                    <span class="cell-label">8 - Número da Carteira</span>
                    <span class="cell-value">{{ guia.numero_carteira }}</span>
                </div>
                <div class="g-cell" style="width:18%;">
                    <span class="cell-label">9 - Validade da Carteira</span>
                    <span class="cell-value">{{ formatDate(guia.validade_carteira) }}</span>
                </div>
                <div class="g-cell" style="width:48%;">
                    <span class="cell-label">89 - Nome Social</span>
                    <span class="cell-value">{{ guia.beneficiario_nome }}</span>
                </div>
                <div class="g-cell" style="width:12%; border-right:none; text-align:center;">
                    <span class="cell-label">12 - Atendimento a RN</span>
                    <span class="cell-value">{{ guia.atendimento_rn ? 'S' : 'N' }}</span>
                </div>
            </div>
            <div class="g-row">
                <div class="g-cell" style="width:22%;">
                    <span class="cell-label">11 - Cartão Nacional de Saúde</span>
                    <span class="cell-value">{{ guia.cns }}</span>
                </div>
                <div class="g-cell" style="width:78%; border-right:none;">
                    <span class="cell-label">10 - Nome</span>
                    <span class="cell-value">{{ guia.beneficiario_nome }}</span>
                </div>
            </div>

            <!-- ══ CONTRATADO SOLICITANTE ══ -->
            <div class="section-bar">Dados do Contratado Solicitante</div>
            <div class="g-row">
                <div class="g-cell" style="width:28%;">
                    <span class="cell-label">13 - Código na Operadora / CNPJ / CPF</span>
                    <span class="cell-value">{{ guia.contratado_solicitante_codigo }}</span>
                </div>
                <div class="g-cell" style="width:72%; border-right:none;">
                    <span class="cell-label">14 - Nome do Contratado</span>
                    <span class="cell-value">{{ guia.contratado_solicitante_nome }}</span>
                </div>
            </div>
            <div class="g-row">
                <div class="g-cell" style="width:35%;">
                    <span class="cell-label">15 - Nome do Profissional Solicitante</span>
                    <span class="cell-value">{{ guia.profissional_solicitante_nome }}</span>
                </div>
                <div class="g-cell" style="width:8%;">
                    <span class="cell-label">16 - Conselho Profissional</span>
                    <span class="cell-value">{{ guia.conselho_solicitante }}</span>
                </div>
                <div class="g-cell" style="width:15%;">
                    <span class="cell-label">17 - Número do Conselho</span>
                    <span class="cell-value">{{ guia.numero_conselho_solicitante }}</span>
                </div>
                <div class="g-cell" style="width:8%;">
                    <span class="cell-label">18 - UF</span>
                    <span class="cell-value">{{ guia.uf_conselho_solicitante }}</span>
                </div>
                <div class="g-cell" style="width:9%;">
                    <span class="cell-label">19 - Código CBO</span>
                    <span class="cell-value">{{ guia.cbo_solicitante }}</span>
                </div>
                <div class="g-cell assinatura-cell"
                    style="width:25%; border-right:none; text-align:center; justify-content: space-between;">
                    <span class="cell-label">20 - Assinatura do Profissional Solicitante</span>
                    <div class="assinatura-line"></div>
                </div>
            </div>

            <!-- ══ DADOS DA SOLICITAÇÃO ══ -->
            <div class="section-bar">Dados de Solicitação / Procedimentos e Exames Solicitados</div>
            <div class="g-row">
                <div class="g-cell" style="width:13%;">
                    <span class="cell-label">21 - Caráter do Atendimento</span>
                    <span class="cell-value">{{ guia.carater_atendimento }}</span>
                </div>
                <div class="g-cell" style="width:15%;">
                    <span class="cell-label">22 - Data da Solicitação</span>
                    <span class="cell-value">{{ formatDate(guia.data_solicitacao) }}</span>
                </div>
                <div class="g-cell" style="width:50%;">
                    <span class="cell-label">23 - Indicação Clínica</span>
                    <span class="cell-value">{{ guia.indicacao_clinica }}</span>
                </div>
                <div class="g-cell" style="width:22%; border-right:none;">
                    <span class="cell-label">90 - Indicador de Cobertura Especial</span>
                    <span class="cell-value">&nbsp;</span>
                </div>
            </div>

            <!-- Cabeçalho tabela itens solicitados -->
            <div class="g-row header-table-row">
                <div class="g-cell" style="width:3%;">
                    <span class="cell-label">&nbsp;</span>
                </div>
                <div class="g-cell" style="width:5%;">
                    <span class="cell-label">24 - Tabela</span>
                </div>
                <div class="g-cell" style="width:17%;">
                    <span class="cell-label">25 - Código do Procedimento</span>
                </div>
                <div class="g-cell" style="width:55%;">
                    <span class="cell-label">26 - Descrição</span>
                </div>
                <div class="g-cell" style="width:10%;">
                    <span class="cell-label">27 - Qt. Solic.</span>
                </div>
                <div class="g-cell" style="width:10%; border-right:none;">
                    <span class="cell-label">28 - Qt. Autoriz.</span>
                </div>
            </div>
            <!-- Linhas de itens solicitados -->
            <div v-for="(proc, i) in (guia.procedimentos_solicitados && guia.procedimentos_solicitados.length ? guia.procedimentos_solicitados : [{}])"
                :key="'sol-' + i" class="g-row" style="border-bottom:none;">
                <div class="g-cell" style="width:3%; border-right:none;"><span class="cell-value">{{ i + 1 }} -</span>
                </div>
                <div class="g-cell" style="width:5%; border-right:none;"><span class="cell-value">{{
                    proc.tabela_procedimento_solicitado || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:17%; border-right:none;"><span class="cell-value">{{
                    proc.procedimento_solicitado_codigo || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:55%; border-right:none;"><span class="cell-value">{{
                    proc.procedimento_solicitado_descricao || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:10%; border-right:none;"><span class="cell-value">{{
                    proc.quantidade_solicitada || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:10%; border-right:none;"><span class="cell-value">{{
                    proc.quantidade_autorizada || '&nbsp;' }}</span></div>
            </div>
            <!-- Linhas extras (em branco) para fechar 5 linhas -->
            <div v-for="n in Math.max(0, 5 - (guia.procedimentos_solicitados ? guia.procedimentos_solicitados.length : 1))"
                :key="'sol-blank-' + n" class="g-row" style="border-bottom:none;">
                <div class="g-cell" style="width:3%; border-right:none;"><span class="cell-value">{{
                    (guia.procedimentos_solicitados ? guia.procedimentos_solicitados.length : 1) + n }} -</span>
                </div>
                <div class="g-cell" style="width:5%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:17%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:55%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:10%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:10%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
            </div>

            <!-- ══ CONTRATADO EXECUTANTE ══ -->
            <div class="section-bar">Dados do Contratado Executante</div>
            <div class="g-row">
                <div class="g-cell" style="width:25%;">
                    <span class="cell-label">29 - Código na Operadora</span>
                    <span class="cell-value">{{ guia.contratado_executante_codigo }}</span>
                </div>
                <div class="g-cell" style="width:55%;">
                    <span class="cell-label">30 - Nome do Contratado</span>
                    <span class="cell-value">{{ guia.contratado_executante_nome }}</span>
                </div>
                <div class="g-cell" style="width:20%; border-right:none;">
                    <span class="cell-label">31 - Código CNES</span>
                    <span class="cell-value">{{ guia.cnes_executante }}</span>
                </div>
            </div>

            <!-- ══ DADOS DO ATENDIMENTO ══ -->
            <div class="section-bar">Dados do Atendimento</div>
            <div class="g-row">
                <div class="g-cell" style="width:15%;">
                    <span class="cell-label">32 - Tipo de Atendimento</span>
                    <span class="cell-value">{{ guia.tipo_atendimento }}</span>
                </div>
                <div class="g-cell" style="width:30%;">
                    <span class="cell-label">33 - Indicação de Acidente (acidente ou doença relacionada)</span>
                    <span class="cell-value">{{ guia.indicacao_acidente }}</span>
                </div>
                <div class="g-cell" style="width:15%;">
                    <span class="cell-label">34 - Tipo de Consulta</span>
                    <span class="cell-value">{{ guia.tipo_consulta }}</span>
                </div>
                <div class="g-cell" style="width:25%;">
                    <span class="cell-label">35 - Motivo de Encerramento do Atendimento</span>
                    <span class="cell-value">{{ guia.motivo_encerramento }}</span>
                </div>
                <div class="g-cell" style="width:8%;">
                    <span class="cell-label">91 - Regime de Atendimento</span>
                    <span class="cell-value">&nbsp;</span>
                </div>
                <div class="g-cell" style="width:7%; border-right:none;">
                    <span class="cell-label">92 - Saúde Ocup.</span>
                    <span class="cell-value">&nbsp;</span>
                </div>
            </div>

            <!-- ══ PROCEDIMENTOS REALIZADOS ══ -->
            <div class="section-bar">Dados da Execução / Procedimentos e Exames Realizados</div>

            <!-- Cabeçalho tabela realizados -->
            <div class="g-row header-table-row">
                <div class="g-cell" style="width:3%;"><span class="cell-label">&nbsp;</span></div>
                <div class="g-cell" style="width:9%;"><span class="cell-label">36 - Data</span></div>
                <div class="g-cell" style="width:8%;"><span class="cell-label">37 - Hora Inicial</span></div>
                <div class="g-cell" style="width:8%;"><span class="cell-label">38 - Hora Final</span></div>
                <div class="g-cell" style="width:5%;"><span class="cell-label">39 - Tabela</span></div>
                <div class="g-cell" style="width:13%;"><span class="cell-label">40 - Código do Procedimento</span></div>
                <div class="g-cell" style="width:25%;"><span class="cell-label">41 - Descrição</span></div>
                <div class="g-cell" style="width:5%;"><span class="cell-label">42 - Qtde.</span></div>
                <div class="g-cell" style="width:5%;"><span class="cell-label">43 - Via</span></div>
                <div class="g-cell" style="width:5%;"><span class="cell-label">44 - Téc.</span></div>
                <div class="g-cell" style="width:5%;"><span class="cell-label">45 - % Re./Acresc.</span></div>
                <div class="g-cell" style="width:7%;"><span class="cell-label">46 - Valor Unitário (R$)</span></div>
                <div class="g-cell" style="width:7%; border-right:none;"><span class="cell-label">47 - Valor Total
                        (R$)</span></div>
            </div>
            <!-- Linhas de itens realizados -->
            <div v-for="(proc, i) in ((guia.procedimentos_realizados || guia.procedimentosRealizados)?.length ? (guia.procedimentos_realizados || guia.procedimentosRealizados) : [{}])"
                :key="'real-' + i" class="g-row" style="border-bottom:none;">
                <div class="g-cell" style="width:3%; border-right:none;"><span class="cell-value">{{ i + 1 }} -</span>
                </div>
                <div class="g-cell" style="width:9%; border-right:none;"><span class="cell-value">{{
                    formatDate(proc.data_realizacao || guia.data_realizacao) || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:8%; border-right:none;"><span class="cell-value">{{
                    proc.hora_inicial || guia.hora_inicial || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:8%; border-right:none;"><span class="cell-value">{{
                    proc.hora_final || guia.hora_final || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:5%; border-right:none;"><span class="cell-value">{{
                    proc.tabela_procedimento_realizado || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:13%; border-right:none;"><span class="cell-value">{{
                    proc.procedimento_realizado_codigo || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:25%; border-right:none;"><span class="cell-value">{{
                    proc.procedimento_realizado_descricao || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:5%; border-right:none;"><span class="cell-value">{{
                    proc.quantidade_realizada || '&nbsp;' }}</span>
                </div>
                <div class="g-cell" style="width:5%; border-right:none;"><span class="cell-value">{{
                    proc.via_acesso || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:5%; border-right:none;"><span class="cell-value">{{
                    proc.tecnica_utilizada || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:5%; border-right:none;"><span class="cell-value">{{
                    proc.fator_reducao_acrescimo || '&nbsp;' }}</span>
                </div>
                <div class="g-cell" style="width:7%; border-right:none;"><span class="cell-value">{{
                    formatCurrency(proc.valor_unitario) || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:7%; border-right:none;"><span class="cell-value">{{
                    formatCurrency(proc.valor_total) || '&nbsp;' }}</span></div>
            </div>
            <!-- Linhas extras em branco para fechar 5 linhas -->
            <div v-for="n in Math.max(0, 5 - ((guia.procedimentos_realizados || guia.procedimentosRealizados) ? (guia.procedimentos_realizados || guia.procedimentosRealizados).length : 1))"
                :key="'exec-blank-' + n" class="g-row" style="border-bottom:none;">
                <div class="g-cell" style="width:3%; border-right:none;"><span class="cell-value">{{
                    ((guia.procedimentos_realizados || guia.procedimentosRealizados) ? (guia.procedimentos_realizados || guia.procedimentosRealizados).length : 1) + n }} -</span></div>
                <div class="g-cell" style="width:9%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:8%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:8%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:5%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:13%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:25%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:5%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:5%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:5%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:5%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:7%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:7%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
            </div>

            <!-- ══ PROFISSIONAIS EXECUTANTES ══ -->
            <div class="section-bar">Identificação do(s) Profissional(is) Executante(s)</div>
            <div class="g-row header-table-row">
                <div class="g-cell" style="width:5%;"><span class="cell-label">48 - Seq. Ref.</span></div>
                <div class="g-cell" style="width:7%;"><span class="cell-label">49 - Grau Part.</span></div>
                <div class="g-cell" style="width:17%;"><span class="cell-label">50 - Código na Operadora / CPF</span>
                </div>
                <div class="g-cell" style="width:25%;"><span class="cell-label">51 - Nome do Profissional</span></div>
                <div class="g-cell" style="width:12%;"><span class="cell-label">52 - Conselho Profissional</span></div>
                <div class="g-cell" style="width:12%;"><span class="cell-label">53 - Número no Conselho</span></div>
                <div class="g-cell" style="width:7%;"><span class="cell-label">54 - UF</span></div>
                <div class="g-cell" style="width:15%; border-right:none;"><span class="cell-label">55 - Código
                        CBO</span></div>
            </div>
            <!-- Linhas de profissionais -->
            <div v-for="(prof, i) in ((guia.profissionais_executantes || guia.profissionaisExecutantes)?.length ? (guia.profissionais_executantes || guia.profissionaisExecutantes) : [{}])"
                :key="'prof-exec-' + i" class="g-row" style="border-bottom:none;">
                <div class="g-cell" style="width:5%; border-right:none;"><span class="cell-value">{{ prof.sequencial_referencia || (i + 1) }}</span></div>
                <div class="g-cell" style="width:7%; border-right:none;"><span class="cell-value">{{ prof.grau_participacao || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:17%; border-right:none;"><span class="cell-value">{{ prof.profissional_executante_codigo || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:25%; border-right:none;"><span class="cell-value">{{ prof.profissional_executante_nome || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:12%; border-right:none;"><span class="cell-value">{{ prof.conselho_executante || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:12%; border-right:none;"><span class="cell-value">{{ prof.numero_conselho_executante || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:7%; border-right:none;"><span class="cell-value">{{ prof.uf_conselho_executante || '&nbsp;' }}</span></div>
                <div class="g-cell" style="width:15%; border-right:none;"><span class="cell-value">{{ prof.cbo_executante || '&nbsp;' }}</span></div>
            </div>
            <!-- Linhas extras -->
            <div v-for="n in Math.max(0, 4 - ((guia.profissionais_executantes || guia.profissionaisExecutantes) ? ((guia.profissionais_executantes || guia.profissionaisExecutantes).length - 1) : 0))"
                :key="'prof-blank-' + n" class="g-row" :style="n < Math.max(0, 4 - ((guia.profissionais_executantes || guia.profissionaisExecutantes) ? ((guia.profissionais_executantes || guia.profissionaisExecutantes).length - 1) : 0)) ? 'border-bottom:none;' : ''">
                <div class="g-cell" style="width:5%; border-right:none;"><span class="cell-value">{{ ((guia.profissionais_executantes || guia.profissionaisExecutantes) ? (guia.profissionais_executantes || guia.profissionaisExecutantes).length : 1) + n }}</span></div>
                <div class="g-cell" style="width:7%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:17%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:25%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:12%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:12%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:7%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
                <div class="g-cell" style="width:15%; border-right:none;"><span class="cell-value">&nbsp;</span></div>
            </div>

            <!-- ══ DATA REALIZAÇÃO EM SÉRIE ══ -->
            <div class="g-row" style="border-bottom:none;">
                <div class="g-cell" style="width:100%; border-right:none;">
                    <span class="cell-label">56 - Data de Realização de Procedimento em Série</span>
                </div>
            </div>
            <div class="g-row" style="border-bottom:none;">
                <div class="g-cell" style="width:16%; border-right:none;">
                    <span class="cell-value-sm">1 - {{ formatDate((guia.profissionais_executantes || guia.profissionaisExecutantes)?.[0]?.data_realizacao_serie) }}</span>
                </div>
                <div class="g-cell" style="width:16%; border-right:none;">
                    <span class="cell-value-sm">3 - {{ formatDate(guia.profissionais_executantes?.[2]?.data_realizacao_serie) }}</span>
                </div>
                <div class="g-cell" style="width:16%; border-right:none;">
                    <span class="cell-value-sm">5 - {{ formatDate(guia.profissionais_executantes?.[4]?.data_realizacao_serie) }}</span>
                </div>
                <div class="g-cell" style="width:16%; border-right:none;">
                    <span class="cell-value-sm">7 - {{ formatDate(guia.profissionais_executantes?.[6]?.data_realizacao_serie) }}</span>
                </div>
                <div class="g-cell" style="width:16%; border-right:none;">
                    <span class="cell-value-sm">9 - {{ formatDate(guia.profissionais_executantes?.[8]?.data_realizacao_serie) }}</span>
                </div>
                <div class="g-cell" style="width:20%; border-right:none;"><span class="cell-value-sm">&nbsp;</span>
                </div>
            </div>
            <div class="g-row">
                <div class="g-cell" style="width:16%; border-right:none;"><span class="cell-value-sm">2 - {{ formatDate(guia.profissionais_executantes?.[1]?.data_realizacao_serie) }}</span></div>
                <div class="g-cell" style="width:16%; border-right:none;"><span class="cell-value-sm">4 - {{ formatDate(guia.profissionais_executantes?.[3]?.data_realizacao_serie) }}</span></div>
                <div class="g-cell" style="width:16%; border-right:none;"><span class="cell-value-sm">6 - {{ formatDate(guia.profissionais_executantes?.[5]?.data_realizacao_serie) }}</span></div>
                <div class="g-cell" style="width:16%; border-right:none;"><span class="cell-value-sm">8 - {{ formatDate(guia.profissionais_executantes?.[7]?.data_realizacao_serie) }}</span></div>
                <div class="g-cell" style="width:16%; border-right:none;"><span class="cell-value-sm">10 - {{ formatDate(guia.profissionais_executantes?.[9]?.data_realizacao_serie) }}</span></div>
                <div class="g-cell" style="width:20%; border-right:none;"><span class="cell-value-sm">&nbsp;</span>
                </div>
            </div>
            <div class="g-row">
                <div class="g-cell" style="width:100%; border-right:none;">
                    <span class="cell-label">57 - Assinatura do Beneficiário ou Responsável</span>
                </div>
            </div>

            <!-- ══ OBSERVAÇÃO ══ -->
            <div class="g-row">
                <div class="g-cell" style="width:100%; border-right:none; min-height:28px;">
                    <span class="cell-label">58 - Observação / Justificativa</span>
                    <span class="cell-value" style="white-space:normal;">{{ guia.observacao_justificativa }}</span>
                </div>
            </div>

            <!-- ══ TOTAIS ══ -->
            <div class="g-row">
                <div class="g-cell" style="width:14%;">
                    <span class="cell-label">59 - Total Procedimentos (R$)</span>
                    <span class="cell-value">{{ formatCurrency(guia.total_procedimentos) }}</span>
                </div>
                <div class="g-cell" style="width:14%;">
                    <span class="cell-label">60 - Total Taxas e Aluguéis (R$)</span>
                    <span class="cell-value">{{ formatCurrency(guia.total_taxas_alugueis) }}</span>
                </div>
                <div class="g-cell" style="width:14%;">
                    <span class="cell-label">61 - Total Materiais (R$)</span>
                    <span class="cell-value">{{ formatCurrency(guia.total_materiais) }}</span>
                </div>
                <div class="g-cell" style="width:14%;">
                    <span class="cell-label">62 - Total de OPME (R$)</span>
                    <span class="cell-value">{{ formatCurrency(guia.total_opme) }}</span>
                </div>
                <div class="g-cell" style="width:14%;">
                    <span class="cell-label">63 - Total Medicamentos (R$)</span>
                    <span class="cell-value">{{ formatCurrency(guia.total_medicamentos) }}</span>
                </div>
                <div class="g-cell" style="width:15%;">
                    <span class="cell-label">64 - Total Gases Medicinais (R$)</span>
                    <span class="cell-value">{{ formatCurrency(guia.total_gases_medicinais) }}</span>
                </div>
                <div class="g-cell" style="width:15%; background:#eee; border-right:none;">
                    <span class="cell-label" style="font-weight:bold;">65 - Total Geral (R$)</span>
                    <span class="cell-value" style="font-weight:bold;">{{ formatCurrency(guia.valor_total_geral)
                    }}</span>
                </div>
            </div>

            <!-- ══ ASSINATURAS ══ -->
            <div class="g-row" style="border-bottom:none;">
                <div class="g-cell assinatura-cell" style="width:33%;">
                    <span class="cell-label">66 - Assinatura do Responsável pela Autorização</span>
                    <div class="assinatura-line"></div>
                </div>
                <div class="g-cell assinatura-cell" style="width:33%;">
                    <span class="cell-label">67 - Assinatura do Beneficiário ou Responsável</span>
                    <div class="assinatura-line"></div>
                </div>
                <div class="g-cell assinatura-cell" style="width:34%; border-right:none;">
                    <span class="cell-label">68 - Assinatura do Contratado</span>
                    <div class="assinatura-line"></div>
                </div>
            </div>

        </div><!-- /guia-container -->
    </div><!-- /print-page-wrapper -->
</template>

<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    guia: { type: Object, required: true },
    agendamento: { type: Object, required: false },
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    const m = String(dateString).match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (m) return `${m[3]}/${m[2]}/${m[1]}`;
    try { return new Intl.DateTimeFormat('pt-BR').format(new Date(dateString)); }
    catch { return dateString; }
};

const formatCurrency = (value) => {
    const num = Number(value) || 0;
    return num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<style>
/* ─── Reset / Base ─────────────────────────────────────── */
.print-page-wrapper *,
.print-page-wrapper *::before,
.print-page-wrapper *::after {
    box-sizing: border-box;
}

.print-page-wrapper {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 8.5px;
    color: #000;
    background: #fff;
    margin: 0;
    padding: 0;
}

/* ─── Container principal ───────────────────────────────── */
.guia-container {
    width: 100%;
    max-width: 1050px;
    margin: 0 auto;
    border: 1px solid #555;
    background: #fff;
}

/* ─── Cabeçalho especial ────────────────────────────────── */
.header-row {
    display: flex;
    align-items: stretch;
    border-bottom: 1px solid #555;
}

.logo-cell {
    width: 130px;
    min-width: 130px;
    padding: 4px 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-right: 1px solid #555;
}

.guia-title {
    font-size: 10px;
    font-weight: bold;
    text-align: center;
    background: #f0f0f0;
    padding: 3px 4px;
    border-bottom: none;
}

.guia-num-cell {
    width: 130px;
    min-width: 130px;
    padding: 2px 4px;
    border-left: 1px solid #555;
    font-size: 7.5px;
    display: flex;
    flex-direction: column;
}

/* ─── Linhas genéricas ──────────────────────────────────── */
.g-row {
    display: flex;
    width: 100%;
    border-bottom: 1px solid #555;
    margin: 0;
    padding: 0;
}

.g-row:last-child {
    border-bottom: none;
}

/* ─── Células ───────────────────────────────────────────── */
.g-cell {
    border-right: 1px solid #555;
    padding: 1px 3px;
    display: flex;
    flex-direction: column;
    min-width: 0;
    overflow: hidden;
}

.g-cell:last-child {
    border-right: none;
}

/* ─── Textos ────────────────────────────────────────────── */
.cell-label {
    font-size: 7px;
    color: #333;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}

.cell-value {
    font-size: 9px;
    font-weight: bold;
    min-height: 11px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
    line-height: 1.3;
}

.cell-value-sm {
    font-size: 8px;
    display: block;
    min-height: 10px;
}

/* ─── Barra de seção ────────────────────────────────────── */
.section-bar {
    background: #ddd;
    font-weight: bold;
    font-size: 8px;
    padding: 1px 3px;
    border-bottom: 1px solid #555;
    width: 100%;
}

/* ─── Cabeçalho de tabelas ──────────────────────────────── */
.header-table-row {
    background: #eee;
}

/* ─── Linhas de dados sem grades internas ───────────────── */
.no-grid {
    border-bottom: none !important;
}

.no-grid .g-cell,
.no-grid>.g-cell,
.no-grid>div {
    border-right: none !important;
    border-bottom: none !important;
}

/* ─── Assinatura ────────────────────────────────────────── */
.assinatura-cell {
    min-height: 30px;
    text-align: center;
    align-items: center;
    justify-content: space-between;
}

.assinatura-line {
    border-bottom: 1px solid #000;
    width: 80%;
    margin: 0 auto 2px auto;
}

/* ─── Botão print (some na impressão) ──────────────────── */
.btn-print {
    background: #1a56db;
    color: #fff;
    border: none;
    padding: 8px 24px;
    font-size: 14px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    letter-spacing: 0.5px;
}

.btn-print:hover {
    background: #1240a8;
}

/* ─── Impressão ─────────────────────────────────────────── */
@page {
    size: A4 landscape;
    margin: 6mm;
}

@media print {
    body {
        margin: 0;
        padding: 0;
        background: #fff;
    }

    .no-print {
        display: none !important;
    }

    .guia-container {
        max-width: 100%;
        border: 1px solid #000;
    }

    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
