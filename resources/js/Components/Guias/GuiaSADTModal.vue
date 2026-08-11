<template>
  <Modal
    v-model="internalShow"
    title="Guia de SP/SADT"
    size="xl"
    :show-footer="false"
    :z-index="10010"
    :backdrop-z-index="10000"
  >
    <div class="guia-tiss-container">
      <div class="guia-header text-center mb-3">
        <h4 class="mb-1 text-uppercase">Guia de Serviço Profissional / Serviço Auxiliar de Diagnóstico e Terapia - SP/SADT</h4>
        <small class="text-muted">Padrão TISS - Troca de Informação de Saúde Suplementar</small>
      </div>

      <form @submit.prevent="saveAndPrint" v-if="form">
        <!-- BLOCO 1 - CABEÇALHO -->
        <fieldset class="tiss-fieldset">
          <legend class="tiss-legend">Registro ANS / Identificação da Guia</legend>
          <div class="row g-2">
            <div class="col-md-2">
              <label>1 - Registro ANS</label>
              <input v-model="form.ans_registro" type="text" class="form-control form-control-sm" />
            </div>
            <div class="col-md-3">
              <label>2 - Número da Guia no Prestador</label>
              <input v-model="form.numero_guia_prestador" type="text" class="form-control form-control-sm" />
            </div>
            <div class="col-md-3">
              <label>3 - Número da Guia Principal</label>
              <input v-model="form.numero_guia_principal" type="text" class="form-control form-control-sm" />
            </div>
            <div class="col-md-2">
              <label>4 - Data de Autorização</label>
              <input v-model="form.data_autorizacao" type="date" class="form-control form-control-sm" />
            </div>
            <div class="col-md-2">
              <label>5 - Senha</label>
              <input v-model="form.senha" type="text" class="form-control form-control-sm" />
            </div>
            <div class="col-md-3">
              <label>6 - Data de Validade da Senha</label>
              <input v-model="form.data_validade_senha" type="date" class="form-control form-control-sm" />
            </div>
            <div class="col-md-3">
              <label>7 - Número da Guia (Operadora)</label>
              <input v-model="form.numero_guia_operadora" type="text" class="form-control form-control-sm" />
            </div>
          </div>
        </fieldset>

        <!-- BLOCO 2 - DADOS DO BENEFICIÁRIO -->
        <fieldset class="tiss-fieldset mt-3">
          <legend class="tiss-legend">Dados do Beneficiário</legend>
          <div class="row g-2">
            <div class="col-md-4">
              <label>8 - Número da Carteira</label>
              <input v-model="form.numero_carteira" type="text" class="form-control form-control-sm tiss-readonly" readonly />
            </div>
            <div class="col-md-2">
              <label>9 - Validade da Carteira</label>
              <input v-model="form.validade_carteira" type="date" class="form-control form-control-sm tiss-readonly" readonly />
            </div>
            <div class="col-md-6">
              <label>10 - Nome do Beneficiário</label>
              <input v-model="form.beneficiario_nome" type="text" class="form-control form-control-sm tiss-readonly" readonly />
            </div>
            <div class="col-md-4">
              <label>11 - Cartão Nacional de Saúde (CNS)</label>
              <input v-model="form.cns" type="text" class="form-control form-control-sm tiss-readonly" readonly />
            </div>
            <div class="col-md-2">
              <label>12 - Atendimento a RN?</label>
              <select v-model="form.atendimento_rn" class="form-select form-select-sm">
                <option :value="false">Não</option>
                <option :value="true">Sim</option>
              </select>
            </div>
          </div>
        </fieldset>

        <!-- BLOCO 3 - DADOS DO SOLICITANTE -->
        <fieldset class="tiss-fieldset mt-3">
          <legend class="tiss-legend">Dados do Solicitante</legend>
          <div class="row g-2">
            <div class="col-md-3">
              <label>13 - Código Prestador na Operadora</label>
              <input v-model="form.contratado_solicitante_codigo" type="text" class="form-control form-control-sm tiss-readonly" readonly />
            </div>
            <div class="col-md-5">
              <label>14 - Nome do Contratado Solicitante</label>
              <input v-model="form.contratado_solicitante_nome" type="text" class="form-control form-control-sm tiss-readonly" readonly />
            </div>
            <div class="col-md-4">
              <label>15 - Nome do Profissional Solicitante</label>
              <input v-model="form.profissional_solicitante_nome" type="text" class="form-control form-control-sm tiss-readonly" readonly />
            </div>
            <div class="col-md-2">
              <label>16 - Conselho</label>
              <input v-model="form.conselho_solicitante" type="text" class="form-control form-control-sm tiss-readonly" readonly />
            </div>
            <div class="col-md-3">
              <label>17 - Número no Conselho</label>
              <input v-model="form.numero_conselho_solicitante" type="text" class="form-control form-control-sm tiss-readonly" readonly />
            </div>
            <div class="col-md-2">
              <label>18 - UF</label>
              <input v-model="form.uf_conselho_solicitante" type="text" class="form-control form-control-sm tiss-readonly" readonly />
            </div>
            <div class="col-md-3">
              <label>19 - Código CBO-S</label>
              <input v-model="form.cbo_solicitante" type="text" class="form-control form-control-sm tiss-readonly" readonly />
            </div>
          </div>
        </fieldset>

        <!-- BLOCO 4 - DADOS DA SOLICITAÇÃO / PROCEDIMENTOS E EXAMES -->
        <fieldset class="tiss-fieldset mt-3">
          <legend class="tiss-legend">Dados da Solicitação / Procedimentos</legend>
          <div class="row g-2">
            <div class="col-md-2">
              <label>21 - Caráter de Atend.</label>
              <select v-model="form.carater_atendimento" class="form-select form-select-sm">
                <option value="1">Eletiva</option>
                <option value="2">Urgência/Emergência</option>
              </select>
            </div>
            <div class="col-md-3">
              <label>22 - Data Solicitação</label>
              <input v-model="form.data_solicitacao" type="date" class="form-control form-control-sm" />
            </div>
            <div class="col-md-7">
              <label>23 - Indicação Clínica</label>
              <input v-model="form.indicacao_clinica" type="text" class="form-control form-control-sm" />
            </div>
            
            <div class="col-md-12 mt-3">
              <h6>Procedimento Solicitado (1)</h6>
              <div class="row g-2">
                <div class="col-md-2">
                  <label>24 - Tabela</label>
                  <input v-model="form.tabela_procedimento_solicitado" type="text" class="form-control form-control-sm" />
                </div>
                <div class="col-md-3">
                  <label>25 - Código</label>
                  <input v-model="form.procedimento_solicitado_codigo" type="text" class="form-control form-control-sm" />
                </div>
                <div class="col-md-5">
                  <label>26 - Descrição</label>
                  <input v-model="form.procedimento_solicitado_descricao" type="text" class="form-control form-control-sm" />
                </div>
                <div class="col-md-2">
                  <label>27 - Qtd Solicitada</label>
                  <input v-model="form.quantidade_solicitada" type="number" class="form-control form-control-sm" />
                </div>
                <div class="col-md-2">
                  <label>28 - Qtd Autorizada</label>
                  <input v-model="form.quantidade_autorizada" type="number" class="form-control form-control-sm" />
                </div>
              </div>
            </div>
          </div>
        </fieldset>

        <!-- BLOCO 5 - DADOS DO EXECUTANTE -->
        <fieldset class="tiss-fieldset mt-3">
          <legend class="tiss-legend">Dados do Executante</legend>
          <div class="row g-2">
            <div class="col-md-3">
              <label>29 - Código na Operadora</label>
              <input v-model="form.contratado_executante_codigo" type="text" class="form-control form-control-sm" />
            </div>
            <div class="col-md-6">
              <label>30 - Nome do Contratado</label>
              <input v-model="form.contratado_executante_nome" type="text" class="form-control form-control-sm" />
            </div>
            <div class="col-md-3">
              <label>31 - CNES</label>
              <input v-model="form.cnes_executante" type="text" class="form-control form-control-sm" />
            </div>
          </div>
        </fieldset>

        <!-- BLOCO 6 - DADOS DO ATENDIMENTO -->
        <fieldset class="tiss-fieldset mt-3">
          <legend class="tiss-legend">Dados do Atendimento</legend>
          <div class="row g-2">
            <div class="col-md-3">
              <label>32 - Tipo Atendimento</label>
              <select v-model="form.tipo_atendimento" class="form-select form-select-sm">
                <option value="01">Remoção</option>
                <option value="02">Pequena Cirurgia</option>
                <option value="03">Terapias</option>
                <option value="04">Consulta</option>
                <option value="05">Exames</option>
                <option value="06">Atendimento Domiciliar</option>
                <!-- Add options as needed -->
              </select>
            </div>
            <div class="col-md-3">
              <label>33 - Indicação Acidente</label>
              <select v-model="form.indicacao_acidente" class="form-select form-select-sm">
                <option value="0">Trabalho</option>
                <option value="1">Trânsito</option>
                <option value="2">Outros</option>
                <option value="9">Não Acidente</option>
              </select>
            </div>
            <div class="col-md-3">
              <label>34 - Tipo Consulta</label>
              <select v-model="form.tipo_consulta" class="form-select form-select-sm">
                <option value="1">Primeira Consulta</option>
                <option value="2">Retorno</option>
                <option value="3">Pré-Natal</option>
                <option value="4">Por Encaminhamento</option>
              </select>
            </div>
            <div class="col-md-3">
              <label>35 - Motivo Encerramento</label>
              <input v-model="form.motivo_encerramento" type="text" class="form-control form-control-sm" />
            </div>
          </div>
        </fieldset>

        <!-- BLOCO 7 - VALORES -->
        <fieldset class="tiss-fieldset mt-3">
          <legend class="tiss-legend">Valores</legend>
          <div class="row g-2">
            <div class="col-md-2">
              <label>52 - Total Proced.</label>
              <input v-model="form.total_procedimentos" type="number" step="0.01" class="form-control form-control-sm" />
            </div>
            <div class="col-md-2">
              <label>53 - Taxas e Alugueis</label>
              <input v-model="form.total_taxas_alugueis" type="number" step="0.01" class="form-control form-control-sm" />
            </div>
            <div class="col-md-2">
              <label>54 - Materiais</label>
              <input v-model="form.total_materiais" type="number" step="0.01" class="form-control form-control-sm" />
            </div>
            <div class="col-md-2">
              <label>58 - OPME</label>
              <input v-model="form.total_opme" type="number" step="0.01" class="form-control form-control-sm" />
            </div>
            <div class="col-md-2">
              <label>55 - Medicamentos</label>
              <input v-model="form.total_medicamentos" type="number" step="0.01" class="form-control form-control-sm" />
            </div>
            <div class="col-md-2">
              <label>57 - Gases</label>
              <input v-model="form.total_gases_medicinais" type="number" step="0.01" class="form-control form-control-sm" />
            </div>
            <div class="col-md-3">
              <label>59 - Valor Total Geral</label>
              <input v-model="form.valor_total_geral" type="number" step="0.01" class="form-control form-control-sm" />
            </div>
            <div class="col-md-9">
              <label>87 - Observação / Justificativa</label>
              <input v-model="form.observacao_justificativa" type="text" class="form-control form-control-sm" />
            </div>
          </div>
        </fieldset>

        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
          <button type="button" class="btn btn-light" @click="closeModal">Cancelar</button>
          <button type="submit" class="btn btn-primary" :disabled="isSaving">
            <i class="ri-save-line align-bottom me-1"></i>
            {{ isSaving ? 'Salvando...' : 'Salvar e Imprimir' }}
          </button>
        </div>
      </form>
      <div v-else class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="mt-2 text-muted">Carregando dados da Guia...</div>
      </div>
    </div>
  </Modal>
</template>

<script>
import Modal from "@/Components/Modal.vue";

export default {
  components: { Modal },
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
      isSaving: false,
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
    closeModal() {
      this.internalShow = false;
    },
    async fetchGuiaDados() {
      try {
        const response = await window.axios.get(`/guias/${this.agendamentoId}/dados`);
        this.form = { ...response.data.guia };
      } catch (error) {
        console.error("Erro ao carregar dados da Guia SP/SADT", error);
        alert("Falha ao carregar dados da guia.");
        this.closeModal();
      }
    },
    async saveAndPrint() {
      if (!this.form || !this.form.id) return;
      this.isSaving = true;
      try {
        await window.axios.put(`/guias/${this.form.id}`, this.form);
        this.closeModal();
        window.open(`/guias/${this.agendamentoId}/imprimir`, '_blank');
      } catch (error) {
        console.error("Erro ao salvar guia", error);
        alert("Falha ao salvar a Guia. Verifique os dados e tente novamente.");
      } finally {
        this.isSaving = false;
      }
    },
  },
};
</script>

<style scoped>
.guia-tiss-container {
  font-family: Arial, sans-serif;
  color: #333;
}
.tiss-fieldset {
  border: 1px solid #dee2e6;
  border-radius: 4px;
  padding: 10px 15px 15px;
  position: relative;
  background-color: #fcfcfc;
}
.tiss-legend {
  font-size: 13px;
  font-weight: bold;
  text-transform: uppercase;
  width: auto;
  padding: 0 5px;
  margin-bottom: 0;
  color: #555;
  background: #fcfcfc;
}
.guia-tiss-container label {
  font-size: 11px;
  font-weight: bold;
  margin-bottom: 2px;
  color: #444;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
}
.guia-tiss-container .form-control-sm,
.guia-tiss-container .form-select-sm {
  font-size: 12px;
  padding: 0.25rem 0.5rem;
  height: 28px;
  border-radius: 3px;
  border-color: #ccc;
}
.guia-tiss-container .form-control-sm:focus,
.guia-tiss-container .form-select-sm:focus {
  border-color: #80bdff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
.tiss-readonly {
  background-color: #f0f2f5 !important;
  color: #6c757d !important;
  cursor: not-allowed !important;
  border-color: #e0e3e7 !important;
  font-style: italic;
}
</style>
