<template>
  <Modal v-model="internalShow" title="Guia de SP/SADT" size="xl" customWidth="90vw" :show-footer="false"
    :z-index="10010" :backdrop-z-index="10000">
    <div class="guia-tiss-container">
      <div class="guia-header text-center mb-3">
        <h4 class="mb-1 text-uppercase">Guia de Serviço Profissional / Serviço Auxiliar de Diagnóstico e Terapia -
          SP/SADT</h4>
        <small class="text-muted">Padrão TISS - Troca de Informação de Saúde Suplementar</small>
      </div>

      <form v-if="form" @submit.prevent="saveAndPrint">

        <!-- BLOCO 1 -->
        <fieldset class="tiss-fieldset">
          <legend class="tiss-legend">Registro ANS / Identificação da Guia</legend>
          <div class="row g-3">
            <div class="col-md-3 tiss-modern-col" v-if="isExibido('1_registro_ans')">
              <label>1 - Registro ANS <span class="text-danger" v-if="isObrigatorio('1_registro_ans')">*</span></label>
              <input v-model="form.ans_registro" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('1_registro_ans')" :required="isObrigatorio('1_registro_ans')" />
            </div>
            <div class="col-md-3 tiss-modern-col" v-if="isExibido('2_numero_guia_prestador')">
              <label>2 - Número da Guia no Prestador <span class="text-danger"
                  v-if="isObrigatorio('2_numero_guia_prestador')">*</span></label>
              <input v-model="form.numero_guia_prestador" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('2_numero_guia_prestador')"
                :required="isObrigatorio('2_numero_guia_prestador')" />
            </div>
            <div class="col-md-3 tiss-modern-col" v-if="isExibido('3_numero_guia_principal')">
              <label>3 - Número da Guia Principal <span class="text-danger"
                  v-if="isObrigatorio('3_numero_guia_principal')">*</span></label>
              <input v-model="form.numero_guia_principal" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('3_numero_guia_principal')"
                :required="isObrigatorio('3_numero_guia_principal')" />
            </div>
            <div class="col-md-3 tiss-modern-col" v-if="isExibido('4_data_autorizacao')">
              <label>4 - Data da Autorização <span class="text-danger"
                  v-if="isObrigatorio('4_data_autorizacao')">*</span></label>
              <flatPickr v-model="form.data_autorizacao" class="form-control form-control-sm" :config="flatpickrDateOptions" :disabled="isBloqueado('4_data_autorizacao')" :required="isObrigatorio('4_data_autorizacao')" />
            </div>
            <div class="col-md-3 tiss-modern-col" v-if="isExibido('5_senha')">
              <label>5 - Senha <span class="text-danger" v-if="isObrigatorio('5_senha')">*</span></label>
              <input v-model="form.senha" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('5_senha')" :required="isObrigatorio('5_senha')" />
            </div>
            <div class="col-md-3 tiss-modern-col" v-if="isExibido('6_validade_senha')">
              <label>6 - Data de Validade da Senha <span class="text-danger"
                  v-if="isObrigatorio('6_validade_senha')">*</span></label>
              <flatPickr v-model="form.data_validade_senha" class="form-control form-control-sm" :config="flatpickrDateOptions" :disabled="isBloqueado('6_validade_senha')" :required="isObrigatorio('6_validade_senha')" />
            </div>
            <div class="col-md-6 tiss-modern-col" v-if="isExibido('7_numero_guia_operadora')">
              <label>7 - Nº da Guia Atribuído pela Operadora <span class="text-danger"
                  v-if="isObrigatorio('7_numero_guia_operadora')">*</span></label>
              <input v-model="form.numero_guia_operadora" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('7_numero_guia_operadora')"
                :required="isObrigatorio('7_numero_guia_operadora')" />
            </div>
          </div>
        </fieldset>

        <!-- BLOCO 2 -->
        <fieldset class="tiss-fieldset">
          <legend class="tiss-legend">Dados do Beneficiário</legend>
          <div class="row g-3">
            <div class="col-md-2 tiss-modern-col" v-if="isExibido('8_numero_carteira')">
              <label>8 - Número da Carteira <span class="text-danger"
                  v-if="isObrigatorio('8_numero_carteira')">*</span></label>
              <input v-model="form.numero_carteira" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('8_numero_carteira')" :required="isObrigatorio('8_numero_carteira')" />
            </div>
            <div class="col-md-2 tiss-modern-col" v-if="isExibido('9_validade_carteira')">
              <label>9 - Validade da Carteira <span class="text-danger"
                  v-if="isObrigatorio('9_validade_carteira')">*</span></label>
              <flatPickr v-model="form.validade_carteira" class="form-control form-control-sm" :config="flatpickrDateOptions" :disabled="isBloqueado('9_validade_carteira')" :required="isObrigatorio('9_validade_carteira')" />
            </div>
            <div class="col-md-4 tiss-modern-col" v-if="isExibido('10_nome_beneficiario')">
              <label>10 - Nome <span class="text-danger" v-if="isObrigatorio('10_nome_beneficiario')">*</span></label>
              <input v-model="form.beneficiario_nome" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('10_nome_beneficiario')" :required="isObrigatorio('10_nome_beneficiario')" />
            </div>
            <div class="col-md-3 tiss-modern-col" v-if="isExibido('11_cartao_nacional_saude')">
              <label>11 - Cartão Nacional de Saúde (CNS) <span class="text-danger"
                  v-if="isObrigatorio('11_cartao_nacional_saude')">*</span></label>
              <input v-model="form.cns" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('11_cartao_nacional_saude')"
                :required="isObrigatorio('11_cartao_nacional_saude')" />
            </div>
            <div class="col-md-1 tiss-modern-col" v-if="isExibido('12_atendimento_rn')">
              <label>12 - Atend. a RN <span class="text-danger"
                  v-if="isObrigatorio('12_atendimento_rn')">*</span></label>
              <select v-model="form.atendimento_rn" data-choices data-choices-search-false class="form-select"
                :disabled="isBloqueado('12_atendimento_rn')" :required="isObrigatorio('12_atendimento_rn')">
                <option :value="1">Sim</option>
                <option :value="0">Não</option>
              </select>
            </div>
          </div>
        </fieldset>

        <!-- BLOCO 3 -->
        <fieldset class="tiss-fieldset">
          <legend class="tiss-legend">Dados do Contratado Solicitante</legend>
          <div class="row g-3">
            <div class="col-md-3 tiss-modern-col" v-if="isExibido('13_codigo_operadora')">
              <label>13 - Código na Operadora / CPF <span class="text-danger"
                  v-if="isObrigatorio('13_codigo_operadora')">*</span></label>
              <input v-model="form.contratado_solicitante_codigo" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('13_codigo_operadora')" :required="isObrigatorio('13_codigo_operadora')" />
            </div>
            <div class="col-md-5 tiss-modern-col" v-if="isExibido('14_nome_contratado')">
              <label>14 - Nome do Contratado Solicitante <span class="text-danger"
                  v-if="isObrigatorio('14_nome_contratado')">*</span></label>
              <input v-model="form.contratado_solicitante_nome" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('14_nome_contratado')" :required="isObrigatorio('14_nome_contratado')" />
            </div>
            <div class="col-md-4 tiss-modern-col" v-if="isExibido('15_nome_profissional_solicitante')">
              <label>15 - Nome do Profissional Solicitante <span class="text-danger"
                  v-if="isObrigatorio('15_nome_profissional_solicitante')">*</span></label>
              <input v-model="form.profissional_solicitante_nome" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('15_nome_profissional_solicitante')"
                :required="isObrigatorio('15_nome_profissional_solicitante')" />
            </div>
            <div class="col-md-2 tiss-modern-col" v-if="isExibido('16_conselho_profissional')">
              <label>16 - Conselho <span class="text-danger"
                  v-if="isObrigatorio('16_conselho_profissional')">*</span></label>
              <select v-model="form.conselho_solicitante" data-choices class="form-select" :key="'conselho-' + conselhos.length"
                :disabled="isBloqueado('16_conselho_profissional')"
                :required="isObrigatorio('16_conselho_profissional')">
                <option value="">Selecione</option>
                <option v-for="c in conselhos" :key="c.id" :value="c.codigo">{{ c.sigla }}</option>
              </select>
            </div>
            <div class="col-md-3 tiss-modern-col" v-if="isExibido('17_numero_conselho')">
              <label>17 - Número do Conselho <span class="text-danger"
                  v-if="isObrigatorio('17_numero_conselho')">*</span></label>
              <input v-model="form.numero_conselho_solicitante" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('17_numero_conselho')" :required="isObrigatorio('17_numero_conselho')" />
            </div>
            <div class="col-md-2 tiss-modern-col" v-if="isExibido('18_uf_conselho')">
              <label>18 - UF <span class="text-danger" v-if="isObrigatorio('18_uf_conselho')">*</span></label>
              <select v-model="form.uf_conselho_solicitante" data-choices class="form-select" :key="'uf-' + (form.uf_conselho_solicitante || 'load')"
                :disabled="isBloqueado('18_uf_conselho')" :required="isObrigatorio('18_uf_conselho')">
                <option value="">Selecione</option>
                <option v-for="uf in ufOptions" :key="uf" :value="uf">{{ uf }}</option>
              </select>
            </div>
            <div class="col-md-5 tiss-modern-col" v-if="isExibido('19_codigo_cbo')">
              <label>19 - CBO S <span class="text-danger" v-if="isObrigatorio('19_codigo_cbo')">*</span></label>
              <select v-model="form.cbo_solicitante" data-choices class="form-select" :key="'cbo-' + especialidades.length"
                :disabled="isBloqueado('19_codigo_cbo')" :required="isObrigatorio('19_codigo_cbo')">
                <option value="">Selecione</option>
                <option v-for="e in especialidades" :key="e.id" :value="e.codigo">{{ e.codigo }} - {{ e.nome }}</option>
              </select>
            </div>
          </div>
        </fieldset>

        <!-- BLOCO 4 -->
        <fieldset class="tiss-fieldset">
          <legend class="tiss-legend">Dados da Solicitação / Procedimentos ou Itens Assistenciais Solicitados</legend>
          <div class="row g-3">
            <div class="col-md-3 tiss-modern-col" v-if="isExibido('21_carater_atendimento')">
              <label>21 - Caráter do Atendimento <span class="text-danger"
                  v-if="isObrigatorio('21_carater_atendimento')">*</span></label>
              <select v-model="form.carater_atendimento" data-choices class="form-select" :key="'carater-' + carateres.length"
                :disabled="isBloqueado('21_carater_atendimento')" :required="isObrigatorio('21_carater_atendimento')">
                <option value="">Selecione</option>
                <option v-for="c in carateres" :key="c.id" :value="c.codigo">{{ c.codigo }} - {{ c.descricao }}</option>
              </select>
            </div>
            <div class="col-md-3 tiss-modern-col" v-if="isExibido('22_data_solicitacao')">
              <label>22 - Data da Solicitação <span class="text-danger"
                  v-if="isObrigatorio('22_data_solicitacao')">*</span></label>
              <flatPickr v-model="form.data_solicitacao" class="form-control form-control-sm" :config="flatpickrDateOptions" :disabled="isBloqueado('22_data_solicitacao')" :required="isObrigatorio('22_data_solicitacao')" />
            </div>
            <div class="col-md-6 tiss-modern-col" v-if="isExibido('23_indicacao_clinica')">
              <label>23 - Indicação Clínica <span class="text-danger"
                  v-if="isObrigatorio('23_indicacao_clinica')">*</span></label>
              <input v-model="form.indicacao_clinica" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('23_indicacao_clinica')" :required="isObrigatorio('23_indicacao_clinica')" />
            </div>
            <div class="col-md-2 tiss-modern-col" v-if="isExibido('24_tabela')">
              <label>24 - Tabela <span class="text-danger" v-if="isObrigatorio('24_tabela')">*</span></label>
              <select v-model="form.tabela_procedimento_solicitado" data-choices class="form-select" :key="'tabela-' + tabelas.length"
                :disabled="isBloqueado('24_tabela')" :required="isObrigatorio('24_tabela')">
                <option value="">Selecione</option>
                <option v-for="t in tabelas" :key="t.id" :value="t.codigo">{{ t.codigo }} - {{ t.descricao }}</option>
              </select>
            </div>
            <div class="col-md-2 tiss-modern-col" v-if="isExibido('25_codigo_procedimento')">
              <label>25 - Código Proced. <span class="text-danger"
                  v-if="isObrigatorio('25_codigo_procedimento')">*</span></label>
              <select v-model="form.procedimento_solicitado_codigo" data-choices class="form-select" :key="'proced-' + procedimentos.length"
                :disabled="isBloqueado('25_codigo_procedimento')" :required="isObrigatorio('25_codigo_procedimento')"
                @change="updateProcedimentoDescricao">
                <option value="">Selecione</option>
                <option v-for="p in procedimentos" :key="p.codigo" :value="p.codigo">{{ p.codigo }} - {{ p.descricao }}</option>
              </select>
            </div>
            <div class="col-md-6 tiss-modern-col" v-if="isExibido('26_descricao')">
              <label>26 - Descrição <span class="text-danger" v-if="isObrigatorio('26_descricao')">*</span></label>
              <input v-model="form.procedimento_solicitado_descricao" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('26_descricao')" :required="isObrigatorio('26_descricao')" />
            </div>
            <div class="col-md-1 tiss-modern-col" v-if="isExibido('27_quantidade_solicitada')">
              <label>27 - Qtd. Sol. <span class="text-danger"
                  v-if="isObrigatorio('27_quantidade_solicitada')">*</span></label>
              <input v-model="form.quantidade_solicitada" type="number" class="form-control form-control-sm"
                :disabled="isBloqueado('27_quantidade_solicitada')"
                :required="isObrigatorio('27_quantidade_solicitada')" />
            </div>
            <div class="col-md-1 tiss-modern-col" v-if="isExibido('28_quantidade_autorizada')">
              <label>28 - Qtd. Aut. <span class="text-danger"
                  v-if="isObrigatorio('28_quantidade_autorizada')">*</span></label>
              <input v-model="form.quantidade_autorizada" type="number" class="form-control form-control-sm"
                :disabled="isBloqueado('28_quantidade_autorizada')"
                :required="isObrigatorio('28_quantidade_autorizada')" />
            </div>
          </div>
        </fieldset>

        <!-- BLOCO 5 -->
        <fieldset class="tiss-fieldset">
          <legend class="tiss-legend">Dados do Contratado Executante</legend>
          <div class="row g-3">
            <div class="col-md-4 tiss-modern-col" v-if="isExibido('29_codigo_operadora_executante')">
              <label>29 - Código na Operadora / CPF / CNPJ <span class="text-danger"
                  v-if="isObrigatorio('29_codigo_operadora_executante')">*</span></label>
              <input v-model="form.contratado_executante_codigo" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('29_codigo_operadora_executante')"
                :required="isObrigatorio('29_codigo_operadora_executante')" />
            </div>
            <div class="col-md-5 tiss-modern-col" v-if="isExibido('30_nome_contratado_executante')">
              <label>30 - Nome do Contratado Executante <span class="text-danger"
                  v-if="isObrigatorio('30_nome_contratado_executante')">*</span></label>
              <input v-model="form.contratado_executante_nome" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('30_nome_contratado_executante')"
                :required="isObrigatorio('30_nome_contratado_executante')" />
            </div>
            <div class="col-md-3 tiss-modern-col" v-if="isExibido('31_codigo_cnes_executante')">
              <label>31 - Código CNES <span class="text-danger"
                  v-if="isObrigatorio('31_codigo_cnes_executante')">*</span></label>
              <input v-model="form.cnes_executante" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('31_codigo_cnes_executante')"
                :required="isObrigatorio('31_codigo_cnes_executante')" />
            </div>
          </div>
        </fieldset>

        <!-- BLOCO 6 -->
        <fieldset class="tiss-fieldset">
          <legend class="tiss-legend">Dados do Atendimento</legend>
          <div class="row g-3">
            <div class="col-md-3 tiss-modern-col" v-if="isExibido('32_tipo_atendimento')">
              <label>32 - Tipo de Atendimento <span class="text-danger"
                  v-if="isObrigatorio('32_tipo_atendimento')">*</span></label>
              <select v-model="form.tipo_atendimento" data-choices class="form-select" :key="'tipoatend-' + tiposAtendimento.length"
                :disabled="isBloqueado('32_tipo_atendimento')" :required="isObrigatorio('32_tipo_atendimento')">
                <option value="">Selecione</option>
                <option v-for="tipo in tiposAtendimento" :key="tipo.codigo" :value="String(tipo.codigo)">
                  {{ tipo.codigo }} - {{ tipo.descricao }}
                </option>
              </select>
            </div>
            <div class="col-md-3 tiss-modern-col" v-if="isExibido('33_indicacao_acidente')">
              <label>33 - Indicação de Acidente <span class="text-danger"
                  v-if="isObrigatorio('33_indicacao_acidente')">*</span></label>
              <select v-model="form.indicacao_acidente" data-choices class="form-select" :key="'indaci-' + indicacoesAcidente.length"
                :disabled="isBloqueado('33_indicacao_acidente')" :required="isObrigatorio('33_indicacao_acidente')">
                <option value="">Selecione</option>
                <option v-for="ind in indicacoesAcidente" :key="ind.codigo" :value="String(ind.codigo)">
                  {{ ind.codigo }} - {{ ind.descricao }}
                </option>
              </select>
            </div>
            <div class="col-md-3 tiss-modern-col" v-if="isExibido('34_tipo_consulta')">
              <label>34 - Tipo de Consulta <span class="text-danger"
                  v-if="isObrigatorio('34_tipo_consulta')">*</span></label>
              <select v-model="form.tipo_consulta" data-choices class="form-select" :key="'tipocons-' + tiposConsulta.length"
                :disabled="isBloqueado('34_tipo_consulta')" :required="isObrigatorio('34_tipo_consulta')">
                <option value="">Selecione</option>
                <option v-for="tipo in tiposConsulta" :key="tipo.codigo" :value="String(tipo.codigo)">
                  {{ tipo.codigo }} - {{ tipo.descricao }}
                </option>
              </select>
            </div>
            <div class="col-md-3 tiss-modern-col" v-if="isExibido('35_motivo_encerramento')">
              <label>35 - Motivo de Encerramento <span class="text-danger"
                  v-if="isObrigatorio('35_motivo_encerramento')">*</span></label>
              <input v-model="form.motivo_encerramento" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('35_motivo_encerramento')" :required="isObrigatorio('35_motivo_encerramento')" />
            </div>
          </div>
        </fieldset>

        <!-- BLOCO 7 -->
        <fieldset class="tiss-fieldset">
          <legend class="tiss-legend">Dados da Execução / Procedimentos e Exames Realizados</legend>
          <div class="row g-3">
            <div class="col-md-1 tiss-modern-col" v-if="isExibido('36_data_hora_execucao')">
              <label>36/37 - Data/Hora <span class="text-danger"
                  v-if="isObrigatorio('36_data_hora_execucao')">*</span></label>
              <flatPickr v-model="form.data_hora_execucao" class="form-control form-control-sm" :config="flatpickrDateTimeOptions" :disabled="isBloqueado('36_data_hora_execucao')" :required="isObrigatorio('36_data_hora_execucao')" />
            </div>
            <div class="col-md-1 tiss-modern-col" v-if="isExibido('38_hora_final')">
              <label>38 - H.Fin <span class="text-danger" v-if="isObrigatorio('38_hora_final')">*</span></label>
              <flatPickr v-model="form.hora_final" :config="flatpickrTimeOptions" class="form-control form-control-sm"
                :disabled="isBloqueado('38_hora_final')" :required="isObrigatorio('38_hora_final')" />
            </div>
            <div class="col-md-1 tiss-modern-col" v-if="isExibido('39_tabela_realizado')">
              <label>39 - Tab <span class="text-danger" v-if="isObrigatorio('39_tabela_realizado')">*</span></label>
              <select v-model="form.tabela_procedimento_realizado" data-choices class="form-select" :key="'tabela-real-' + tabelas.length"
                :disabled="isBloqueado('39_tabela_realizado')" :required="isObrigatorio('39_tabela_realizado')">
                <option value="">Sel</option>
                <option v-for="t in tabelas" :key="t.id" :value="t.codigo">{{ t.codigo }} - {{ t.descricao }}</option>
              </select>
            </div>
            <div class="col-md-3 tiss-modern-col" v-if="isExibido('40_codigo_procedimento_realizado') || isExibido('41_descricao_realizado')">
              <label>40/41 - Cód/Descrição <span class="text-danger"
                  v-if="isObrigatorio('40_codigo_procedimento_realizado') || isObrigatorio('41_descricao_realizado')">*</span></label>
              <select v-model="form.procedimento_realizado_codigo" data-choices class="form-select" :key="'proced-real-' + procedimentos.length"
                :disabled="isBloqueado('40_codigo_procedimento_realizado') || isBloqueado('41_descricao_realizado')"
                :required="isObrigatorio('40_codigo_procedimento_realizado') || isObrigatorio('41_descricao_realizado')"
                @change="updateProcedimentoRealizadoDescricao">
                <option value="">Sel</option>
                <option v-for="p in procedimentos" :key="p.codigo" :value="p.codigo">{{ p.codigo }} - {{ p.descricao }}</option>
              </select>
            </div>
            <div class="col-md-1 tiss-modern-col" v-if="isExibido('42_quantidade_realizada')">
              <label>42 - Qtd <span class="text-danger" v-if="isObrigatorio('42_quantidade_realizada')">*</span></label>
              <input v-model="form.quantidade_realizada" type="number" class="form-control form-control-sm"
                :disabled="isBloqueado('42_quantidade_realizada')"
                :required="isObrigatorio('42_quantidade_realizada')" />
            </div>
            <div class="col-md-1 tiss-modern-col" v-if="isExibido('43_via_acesso')">
              <label>43 - Via <span class="text-danger" v-if="isObrigatorio('43_via_acesso')">*</span></label>
              <input v-model="form.via_acesso" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('43_via_acesso')" :required="isObrigatorio('43_via_acesso')" />
            </div>
            <div class="col-md-1 tiss-modern-col" v-if="isExibido('44_tecnica_utilizada')">
              <label>44 - Téc <span class="text-danger" v-if="isObrigatorio('44_tecnica_utilizada')">*</span></label>
              <input v-model="form.tecnica_utilizada" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('44_tecnica_utilizada')" :required="isObrigatorio('44_tecnica_utilizada')" />
            </div>
            <div class="col-md-1 tiss-modern-col" v-if="isExibido('45_fator_reducao_acrescimo')">
              <label>45 - Fat <span class="text-danger"
                  v-if="isObrigatorio('45_fator_reducao_acrescimo')">*</span></label>
              <input v-model="form.fator_reducao_acrescimo" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('45_fator_reducao_acrescimo')"
                :required="isObrigatorio('45_fator_reducao_acrescimo')" />
            </div>
            <div class="col-md-1 tiss-modern-col" v-if="isExibido('46_valor_unitario')">
              <label>46 - V.Unit <span class="text-danger" v-if="isObrigatorio('46_valor_unitario')">*</span></label>
              <input v-model="form.valor_unitario" type="number" class="form-control form-control-sm"
                :disabled="isBloqueado('46_valor_unitario')" :required="isObrigatorio('46_valor_unitario')" />
            </div>
            <div class="col-md-1 tiss-modern-col" v-if="isExibido('47_valor_total')">
              <label>47 - V.Tot <span class="text-danger" v-if="isObrigatorio('47_valor_total')">*</span></label>
              <input v-model="form.valor_total" type="number" class="form-control form-control-sm"
                :disabled="isBloqueado('47_valor_total')" :required="isObrigatorio('47_valor_total')" />
            </div>
          </div>
        </fieldset>

        <!-- BLOCO 8 -->
        <fieldset class="tiss-fieldset">
          <legend class="tiss-legend">Observação / Totais</legend>
          <div class="row g-3">
            <div class="col-md-12 tiss-modern-col" v-if="isExibido('58_observacao')">
              <label>58 - Observação / Justificativa <span class="text-danger"
                  v-if="isObrigatorio('58_observacao')">*</span></label>
              <input v-model="form.observacao_justificativa" type="text" class="form-control form-control-sm"
                :disabled="isBloqueado('58_observacao')" :required="isObrigatorio('58_observacao')" />
            </div>
            <div class="col-md-2 tiss-modern-col" v-if="isExibido('59_valor_total_honorarios')">
              <label>59 - Honorários <span class="text-danger"
                  v-if="isObrigatorio('59_valor_total_honorarios')">*</span></label>
              <input v-model="form.total_procedimentos" type="number" class="form-control form-control-sm"
                :disabled="isBloqueado('59_valor_total_honorarios')"
                :required="isObrigatorio('59_valor_total_honorarios')" />
            </div>
            <div class="col-md-2 tiss-modern-col" v-if="isExibido('60_valor_total_taxas')">
              <label>60 - Taxas <span class="text-danger"
                  v-if="isObrigatorio('60_valor_total_taxas')">*</span></label>
              <input v-model="form.total_taxas_alugueis" type="number" class="form-control form-control-sm"
                :disabled="isBloqueado('60_valor_total_taxas')" :required="isObrigatorio('60_valor_total_taxas')" />
            </div>
            <div class="col-md-1 tiss-modern-col" v-if="isExibido('61_valor_total_materiais')">
              <label>61 - Mat <span class="text-danger"
                  v-if="isObrigatorio('61_valor_total_materiais')">*</span></label>
              <input v-model="form.total_materiais" type="number" class="form-control form-control-sm"
                :disabled="isBloqueado('61_valor_total_materiais')"
                :required="isObrigatorio('61_valor_total_materiais')" />
            </div>
            <div class="col-md-1 tiss-modern-col" v-if="isExibido('62_valor_total_opme')">
              <label>62 - OPME <span class="text-danger"
                  v-if="isObrigatorio('62_valor_total_opme')">*</span></label>
              <input v-model="form.total_opme" type="number" class="form-control form-control-sm"
                :disabled="isBloqueado('62_valor_total_opme')" :required="isObrigatorio('62_valor_total_opme')" />
            </div>
            <div class="col-md-2 tiss-modern-col" v-if="isExibido('63_valor_total_medicamentos')">
              <label>63 - Med <span class="text-danger"
                  v-if="isObrigatorio('63_valor_total_medicamentos')">*</span></label>
              <input v-model="form.total_medicamentos" type="number" class="form-control form-control-sm"
                :disabled="isBloqueado('63_valor_total_medicamentos')"
                :required="isObrigatorio('63_valor_total_medicamentos')" />
            </div>
            <div class="col-md-2 tiss-modern-col" v-if="isExibido('64_valor_total_gases')">
              <label>64 - Gases <span class="text-danger"
                  v-if="isObrigatorio('64_valor_total_gases')">*</span></label>
              <input v-model="form.total_gases_medicinais" type="number" class="form-control form-control-sm"
                :disabled="isBloqueado('64_valor_total_gases')" :required="isObrigatorio('64_valor_total_gases')" />
            </div>
            <div class="col-md-2 tiss-modern-col" v-if="isExibido('65_valor_total_geral')">
              <label>65 - Total Geral <span class="text-danger"
                  v-if="isObrigatorio('65_valor_total_geral')">*</span></label>
              <input v-model="form.valor_total_geral" type="number" class="form-control form-control-sm"
                :disabled="isBloqueado('65_valor_total_geral')" :required="isObrigatorio('65_valor_total_geral')" />
            </div>
          </div>
        </fieldset>
      </form>
      <div v-else class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="mt-2 text-muted">Carregando dados da Guia...</div>
      </div>

      <!-- Footer com botões de ação -->
      <div v-if="form" class="guia-footer d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
        <button type="button" class="btn btn-light" @click="closeModal" :disabled="isSalvando || isImprimindo">
          <i class="ri-close-line me-1"></i> Cancelar
        </button>
        <button type="button" class="btn btn-primary" @click="salvarGuia" :disabled="isSalvando || isImprimindo">
          <span v-if="isSalvando" class="spinner-border spinner-border-sm me-1"></span>
          <i v-else class="ri-save-line me-1"></i> Salvar
        </button>
        <button type="button" class="btn btn-success" @click="imprimirGuia" :disabled="isSalvando || isImprimindo">
          <span v-if="isImprimindo" class="spinner-border spinner-border-sm me-1"></span>
          <i v-else class="ri-printer-line me-1"></i> Imprimir
        </button>
      </div>
    </div>
  </Modal>
</template>

<script>
import Modal from "@/Components/Modal.vue";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.min.css";
import { Portuguese } from "flatpickr/dist/l10n/pt.js";

export default {
  components: { Modal, flatPickr },
  props: {
    modelValue: {
      type: Boolean,
      default: false,
    },
    agendamentoId: {
      type: [Number, String],
      required: true,
    },
  },
  data() {
    return {
      internalShow: false,
      form: null,
      conselhos: [],
      especialidades: [],
      carateres: [],
      tabelas: [],
      procedimentos: [],
      tiposAtendimento: [],
      indicacoesAcidente: [],
      tiposConsulta: [],
      ufOptions: ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'],
      isSalvando: false,
      isImprimindo: false,
      flatpickrDateOptions: { altInput: true, altInputClass: "form-control tiss-flatpickr", altFormat: "d M, Y", dateFormat: "Y-m-d", locale: Portuguese },
      flatpickrTimeOptions: { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true, altInput: true, altInputClass: "form-control tiss-flatpickr" },
      flatpickrDateTimeOptions: { enableTime: true, time_24hr: true, altInput: true, altInputClass: "form-control tiss-flatpickr", altFormat: "d/m/Y H:i", dateFormat: "Y-m-d H:i", locale: Portuguese },
    };
  },
  watch: {
    modelValue: {
      immediate: true,
      handler(val) {
        if (val) {
          this.$nextTick(() => {
            this.internalShow = true;
          });
          if (this.agendamentoId) {
            this.fetchGuiaDados();
          }
        } else {
          this.internalShow = false;
          this.form = null;
        }
      }
    },
    internalShow(val) {
      if (!val) {
        this.$emit("update:modelValue", false);
      }
    }
  },
  methods: {
    updateProcedimentoDescricao() {
      if (this.form && this.form.procedimento_solicitado_codigo) {
        const proc = this.procedimentos.find(p => String(p.codigo) === String(this.form.procedimento_solicitado_codigo));
        if (proc) {
          this.form.procedimento_solicitado_descricao = proc.descricao;
        }
      }
    },
    updateProcedimentoRealizadoDescricao() {
      if (this.form && this.form.procedimento_realizado_codigo) {
        const proc = this.procedimentos.find(p => String(p.codigo) === String(this.form.procedimento_realizado_codigo));
        if (proc) {
          this.form.procedimento_realizado_descricao = proc.descricao;
        }
      }
    },
    isExibido(key) {
      const config = this.form?.convenio?.config_spsadt || this.form?.agendamento?.convenio?.config_spsadt;
      if (!config || !config.visivel || !Array.isArray(config.visivel) || config.visivel.length === 0) return true;
      return config.visivel.includes(key);
    },
    isObrigatorio(key) {
      const config = this.form?.convenio?.config_spsadt || this.form?.agendamento?.convenio?.config_spsadt;
      if (!config || !config.obrigatorio || !Array.isArray(config.obrigatorio)) return false;
      return config.obrigatorio.includes(key);
    },
    isBloqueado(key) {
      const config = this.form?.convenio?.config_spsadt || this.form?.agendamento?.convenio?.config_spsadt;
      if (!config || !config.bloqueado || !Array.isArray(config.bloqueado)) return false;
      return config.bloqueado.includes(key);
    },
    closeModal() {
      this.internalShow = false;
    },
    async fetchGuiaDados() {
      try {
        const response = await window.axios.get(`/guias/${this.agendamentoId}/dados`);
        this.form = { 
          ...response.data.guia,
          agendamento: response.data.agendamento 
        };
        // Garantir que códigos numéricos sejam string para o match do select
        if (this.form.procedimento_solicitado_codigo) {
          this.form.procedimento_solicitado_codigo = String(this.form.procedimento_solicitado_codigo);
        }
        if (this.form.procedimento_realizado_codigo) {
          this.form.procedimento_realizado_codigo = String(this.form.procedimento_realizado_codigo);
        }
        if (this.form.carater_atendimento) {
          this.form.carater_atendimento = String(this.form.carater_atendimento).padStart(2, '0');
        }
        if (this.form.tabela_procedimento_solicitado) {
          this.form.tabela_procedimento_solicitado = String(this.form.tabela_procedimento_solicitado);
        }
        if (this.form.tabela_procedimento_realizado) {
          this.form.tabela_procedimento_realizado = String(this.form.tabela_procedimento_realizado);
        }
        if (this.form.tipo_atendimento) {
          this.form.tipo_atendimento = String(this.form.tipo_atendimento);
        }
        if (this.form.indicacao_acidente) {
          this.form.indicacao_acidente = String(this.form.indicacao_acidente);
        }
        if (this.form.tipo_consulta) {
          this.form.tipo_consulta = String(this.form.tipo_consulta);
        }
        this.conselhos = response.data.conselhos || [];
        this.especialidades = response.data.especialidades || [];
        this.carateres = response.data.carateres || [];
        this.tabelas = response.data.tabelas || [];
        this.procedimentos = response.data.procedimentos || [];
        this.tiposAtendimento = response.data.tiposAtendimento || [];
        this.indicacoesAcidente = response.data.indicacoesAcidente || [];
        this.tiposConsulta = response.data.tiposConsulta || [];
      } catch (error) {
        console.error("Erro ao carregar dados da Guia SP/SADT", error);
        try {
          const fp = (this.$page?.props?.flash ?? {});
          this.$page.props.flash = { ...fp, error: "Falha ao carregar dados da guia." };
        } catch (_) { }
        this.closeModal();
      }
    },
    async salvarGuia() {
      if (!this.form || !this.form.id) return;
      this.isSalvando = true;
      try {
        await window.axios.put(`/guias/${this.form.id}`, this.form);
        this.closeModal();
      } catch (error) {
        console.error("Erro ao salvar guia", error);
        try {
          const fp = (this.$page?.props?.flash ?? {});
          this.$page.props.flash = { ...fp, error: "Falha ao salvar a Guia. Verifique os dados e tente novamente." };
        } catch (_) { }
      } finally {
        this.isSalvando = false;
      }
    },
    async imprimirGuia() {
      if (!this.form || !this.form.id) return;
      this.isImprimindo = true;
      try {
        await window.axios.put(`/guias/${this.form.id}`, this.form);
        this.closeModal();
        window.open(`/guias/${this.agendamentoId}/imprimir`, '_blank');
      } catch (error) {
        console.error("Erro ao salvar guia", error);
        try {
          const fp = (this.$page?.props?.flash ?? {});
          this.$page.props.flash = { ...fp, error: "Falha ao salvar a Guia. Verifique os dados e tente novamente." };
        } catch (_) { }
      } finally {
        this.isImprimindo = false;
      }
    },
  },
};
</script>


<style>
.guia-tiss-container {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.tiss-fieldset {
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 20px 24px;
  background-color: #f8fafc;
  margin-top: 24px !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.tiss-legend {
  font-size: 13px;
  font-weight: bold;
  text-transform: uppercase;
  width: auto;
  padding: 0 5px;
  margin-bottom: 0;
  color: #555;
  background: transparent;
  border: none;
  box-shadow: none;
  transform: translateY(-12px);
}

.guia-tiss-container label {
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  margin-bottom: 6px;
  display: block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.tiss-modern-col {
  margin-bottom: 4px;
}

.guia-tiss-container .form-control-sm,
.guia-tiss-container .form-select-sm {
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  padding: 6px 12px;
  height: 38px;
  font-size: 14px;
  color: #1e293b;
  background-color: #fff;
  transition: all 0.2s;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02) inset;
}

/* Força a altura exata do input visual (altInput) gerado pelo Flatpickr */
.guia-tiss-container input.tiss-flatpickr,
.guia-tiss-container input.form-control-sm[readonly] {
  height: 36px !important;
  min-height: 36px !important;
  padding: 6px 12px !important;
  box-sizing: border-box !important;
}

/* Evitar que os selects (Choices.js) quebrem linha e forçar altura */
.guia-tiss-container .choices {
  min-width: 0;
  max-width: 100%;
}
.guia-tiss-container .choices__inner {
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  height: 38px !important;
  min-height: 38px !important;
  display: flex !important;
  align-items: center !important;
  padding-top: 0 !important;
  padding-bottom: 0 !important;
}
.guia-tiss-container .choices__list--single {
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  width: 100%;
  padding-right: 15px !important;
}
.guia-tiss-container .choices__list--single .choices__item {
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  max-width: 100%;
  margin-bottom: 0 !important;
}

.guia-tiss-container .form-control-sm:focus,
.guia-tiss-container .form-select-sm:focus {
  border-color: #3b82f6;
  outline: 0;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.guia-tiss-container .text-danger {
  color: #ef4444 !important;
}

/* Estilo para campos bloqueados (cinza) */
.guia-tiss-container input:disabled, 
.guia-tiss-container select:disabled, 
.guia-tiss-container textarea:disabled, 
.guia-tiss-container .form-control:disabled, 
.guia-tiss-container .form-select:disabled,
.guia-tiss-container input[disabled] {
  background-color: #e9ecef !important;
  color: #6c757d !important;
  cursor: not-allowed;
}

.guia-tiss-container .choices.is-disabled .choices__inner {
  background-color: #e9ecef !important;
  color: #6c757d !important;
  cursor: not-allowed;
}
</style>
