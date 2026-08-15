<template>
  <form class="row g-3 needs-validation" novalidate ref="formEl" @submit.prevent="submit">
    <BTabs nav-class="nav-tabs-custom text-muted">
      <BTab title="Dados">
        <div class="row g-3 mt-2">
          <div class="col-md-6">
            <label class="form-label">Logo do convênio</label>
            <div class="logo-preview-box mb-2">
              <img v-if="logoDisplayUrl" :src="logoDisplayUrl" alt="Logo do convênio" class="logo-preview-img" />
              <div v-else class="text-muted small text-center">
                <i class="ri-image-line fs-4 d-block"></i>
                Logo
              </div>
            </div>
            <input ref="logoInput" type="file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp"
              class="form-control" @change="onLogoChange" />
            <div class="form-text">PNG, JPG ou WEBP (máx. 2MB).</div>
          </div>

          <div class="col-md-6">
            <div class="d-none d-md-block logo-preview-spacer mb-2"></div>
            <label for="descricao" class="form-label">Descrição</label>
            <span class="text-danger ms-1">*</span>
            <input v-model="form.descricao" type="text" class="form-control" id="descricao"
              placeholder="Nome do convênio" required maxlength="120" />
            <div class="invalid-feedback">Informe a descrição.</div>
          </div>

          <div class="col-md-6">
            <label for="tipo" class="form-label">Tipo</label>
            <select data-choices ref="tipoSelect" v-model="form.tipo" class="form-select" id="tipo">
              <option disabled value="">Selecione...</option>
              <option value="CONVENIO">CONVÊNIO</option>
              <option value="PARTICULAR">PARTICULAR</option>
            </select>
          </div>

          <div class="col-md-6">
            <label for="empresa" class="form-label">Empresa</label>
            <select v-model="form.empresa_id" data-choices class="form-select" id="empresa" ref="empresaSelect">
              <option disabled value="">Selecione...</option>
              <option v-for="c in props.contas" :key="c.id" :value="c.id">
                {{ c.nome }}
              </option>
            </select>
          </div>

          <div class="col-md-2">
            <label for="ans" class="form-label">ANS</label>
            <input v-model.number="form.ans" type="number" class="form-control" id="ans" placeholder="Código ANS" />
          </div>

          <div class="col-md-2">
            <label for="diasReceb" class="form-label">Dias Receb.</label>
            <input v-model.number="form.dias_recebimento" type="number" class="form-control" id="diasReceb"
              placeholder="Ex.: 30" />
          </div>

          <div class="col-md-2">
            <label for="diasRet" class="form-label">Dias Retorno</label>
            <input v-model.number="form.dias_retorno" type="number" class="form-control" id="diasRet"
              placeholder="Ex.: 7" />
          </div>
        </div>
      </BTab>

      <BTab title="Procedimentos (TUSS)">
        <div class="mt-2">
          <div v-if="isTipoParticular" class="alert alert-info mb-3">
            Convênio do tipo Particular não utiliza tabela TUSS.
          </div>

          <div class="border border-dashed rounded p-3 bg-light-subtle mb-3" v-if="!isTipoParticular">
            <div class="row g-3 align-items-end">
              <div class="col-lg-6">
                <label for="tussTabela" class="form-label mb-1">
                  <i class="ri-file-list-3-line text-primary me-1"></i>
                  Tabela TUSS
                </label>
                <select data-choices ref="tussTabelaSelect" v-model="form.tuss_tabela" class="form-select"
                  id="tussTabela" :disabled="isTipoParticular">
                  <option disabled value="">Selecione...</option>
                  <option v-for="t in allowedTabelas" :key="t" :value="t">{{ t }}</option>
                </select>
                <div v-if="form.errors.tuss_tabela" class="invalid-feedback d-block">{{ form.errors.tuss_tabela }}</div>
              </div>

              <div class="col-lg-6">
                <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                  <button type="button" class="btn btn-sm btn-soft-danger" @click="clearSelectedTuss"
                    :disabled="selectedTussRows.length === 0">
                    <i class="ri-delete-bin-6-line me-1"></i>
                    Limpar seleção
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div v-if="form.errors.tuss_ids" class="alert alert-danger mb-3">
            {{ form.errors.tuss_ids }}
          </div>

          <div v-if="!isTipoParticular" class="row g-3">
            <div class="col-lg-4">
              <div class="card mb-0 tuss-panel">
                <div class="card-header bg-light-subtle py-2">
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                      <i class="ri-check-double-line text-success"></i>
                      <strong>Selecionados</strong>
                    </div>
                    <span class="badge bg-primary-subtle text-primary">{{ selectedTussRows.length }}</span>
                  </div>
                </div>
                <div class="card-body pt-2">
                  <div class="search-box mb-2">
                    <input v-model="selectedTussQuery" type="text" class="form-control search"
                      placeholder="Buscar nos selecionados" :disabled="selectedTussRows.length === 0" />
                    <i class="ri-search-line search-icon"></i>
                  </div>

                  <div class="tuss-selected-scroll">
                    <ul class="list-group list-group-flush" v-if="filteredSelectedTussRows.length > 0">
                      <li v-for="r in filteredSelectedTussRows" :key="`sel-${r.id}`"
                        class="list-group-item px-2 py-2 mb-1 border rounded">
                        <div class="d-flex justify-content-between align-items-start">
                          <div class="d-flex flex-column me-2 flex-grow-1" style="min-width:0;">
                            <span class="d-flex align-items-center flex-wrap gap-2">
                              <span class="fw-bold">{{ r.codigo }}</span>
                              <span class="badge bg-secondary-subtle text-secondary">{{ r.tabela || "—" }}</span>
                              <button type="button" class="btn btn-sm p-0 m-0 border-0 d-flex align-items-center"
                                :class="r.requer_autorizacao ? 'text-success' : 'text-muted opacity-50'"
                                @click="r.requer_autorizacao = !r.requer_autorizacao"
                                :title="r.requer_autorizacao ? 'Requer autorização prévia (clique para alterar)' : 'Não requer autorização (clique para alterar)'">
                                <i
                                  :class="r.requer_autorizacao ? 'ri-shield-check-fill fs-5' : 'ri-shield-line fs-5'"></i>
                              </button>
                            </span>
                            <span class="text-muted small mt-1 text-truncate-2">{{ r.descricao }}</span>
                          </div>
                          <div class="d-flex align-items-start gap-2 flex-shrink-0">
                            <span v-if="r.total !== null && r.total !== undefined && String(r.total) !== ''"
                              class="text-muted small text-nowrap mt-1 fw-medium">
                              {{ formatMoney(r.total) }}
                            </span>
                            <button type="button" class="btn btn-sm btn-soft-danger px-2 py-1"
                              @click="removeTussRow(r.id)" title="Remover procedimento">
                              <i class="ri-close-line"></i>
                            </button>
                          </div>
                        </div>
                      </li>
                    </ul>
                    <div v-else class="tuss-empty text-center text-muted">
                      <div class="mb-2">
                        <i class="ri-inbox-2-line fs-2"></i>
                      </div>
                      <div class="fw-semibold">Nenhum procedimento selecionado</div>
                      <div class="small">Selecione na lista ao lado e clique em “Adicionar selecionados”.</div>
                    </div>
                  </div>
                  <div class="text-muted small mt-2 d-flex align-items-center gap-2">
                    <i class="ri-information-line"></i>
                    <span>Os procedimentos selecionados serão vinculados ao convênio ao salvar.</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-8">
              <div v-if="!form.tuss_tabela" class="card mb-0 tuss-panel">
                <div class="card-body p-4 text-center">
                  <div class="avatar-sm mx-auto mb-3">
                    <div class="avatar-title bg-warning-subtle text-warning rounded-circle">
                      <i class="ri-filter-3-line fs-4"></i>
                    </div>
                  </div>
                  <div class="fw-semibold">Selecione uma tabela TUSS</div>
                  <div class="text-muted small">Depois use a busca para encontrar o procedimento e adicionar ao
                    convênio.</div>
                </div>
              </div>
              <TableGrid v-if="form.tuss_tabela" ref="tussGridRef" :serverUrl="tussServerUrl" :columns="tussGridColumns"
                :search="true" :searchPlaceholder="'Buscar procedimento (código ou descrição)'" :showCheckbox="true"
                :showMultiDelete="false" :showAddButton="true" :addButtonText="'Adicionar selecionados'"
                :addButtonIconClass="'ri-add-circle-line'" :addButtonDisabled="tussGridSelectedIds.length === 0"
                :showActions="false" :showPerPagination="true" :compactSpacing="true"
                :tableTitle="`Procedimentos (${form.tuss_tabela})`" @add="addSelectedFromGrid"
                @selectionChange="onTussGridSelectionChange" />
            </div>
          </div>
        </div>
      </BTab>
      <BTab title="Médicos Conveniados">
        <div class="mt-2">
          <TableGrid :columns="medicosGridColumns" :data="selectedMedicosRows" :tableTitle="'Médicos Conveniados'"
            :search="true" :searchPlaceholder="'Buscar médico'" :showCheckbox="false" :showActions="true"
            :showStatus="false" :showPerPagination="true" :showAddButton="true" :addButtonText="'Adicionar Médico'"
            :addButtonIconClass="'ri-user-add-line'"
            :actionsConfig="{ edit: false, show: false, delete: true, procedure: true }" @add="openAddMedicoModal"
            @procedure="editMedico" @delete="removeMedico" />
        </div>
      </BTab>
    
      <BTab title="Config. Guia SP/SADT">
        <div class="mt-2">
          <h5 class="mb-3">Campos da Guia SP/SADT</h5>
          <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-bordered table-sm table-striped">
              <thead class="table-light position-sticky top-0 z-1">
                <tr>
                  <th class="align-middle">Campo</th>
                  <th class="text-center align-middle" style="width: 100px;">
                    Visível<br>
                    <input type="checkbox" class="form-check-input mt-1" @change="toggleAll('visivel', $event.target.checked)" :checked="isAllChecked('visivel')" />
                  </th>
                  <th class="text-center align-middle" style="width: 100px;">
                    Obrigatório<br>
                    <input type="checkbox" class="form-check-input mt-1" @change="toggleAll('obrigatorio', $event.target.checked)" :checked="isAllChecked('obrigatorio')" />
                  </th>
                  <th class="text-center align-middle" style="width: 100px;">
                    Bloqueado<br>
                    <input type="checkbox" class="form-check-input mt-1" @change="toggleAll('bloqueado', $event.target.checked)" :checked="isAllChecked('bloqueado')" />
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="field in spsadtFields" :key="field.key">
                  <td>{{ field.label }}</td>
                  <td class="text-center">
                    <input type="checkbox" class="form-check-input" :value="field.key" v-model="form.config_spsadt.visivel" />
                  </td>
                  <td class="text-center">
                    <input type="checkbox" class="form-check-input" :value="field.key" v-model="form.config_spsadt.obrigatorio" />
                  </td>
                  <td class="text-center">
                    <input type="checkbox" class="form-check-input" :value="field.key" v-model="form.config_spsadt.bloqueado" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </BTab>
    </BTabs>
  </form>

  <!-- Modal para adicionar médico -->
  <Teleport to="body">
    <Modal v-model="showAddMedicoModal" title="Adicionar Médico ao Convênio" :name-button="'Adicionar'" :zIndex="1060"
      :backdropZIndex="1055" @save="addMedicoFromModal">
      <div>
        <label class="form-label mb-1">Selecione o médico</label>
        <select ref="modalMedicoSelect" class="form-select" @change="handleModalMedicoSelect">
        </select>
      </div>
    </Modal>
  </Teleport>

  <!-- Modal para editar TUSS do médico -->
  <Teleport to="body">
    <Modal v-model="showEditMedicoTussModal" title="Procedimentos do Médico" :name-button="'Salvar'" :zIndex="1060"
      :backdropZIndex="1055" size="xl" @save="saveMedicoTussModal">
      <div v-if="editingMedicoData">
        <h6 class="mb-3">Médico: {{ editingMedicoData.nome }}</h6>
        <div class="alert alert-info py-2 px-3 small mb-3">
          <i class="ri-information-line me-1"></i> Apenas os procedimentos já selecionados na aba "Procedimentos (TUSS)"
          estão disponíveis para o médico.
        </div>
        <TableGrid v-if="showEditMedicoTussModal" ref="medicoTussGridRef" :columns="[
          { id: 'id', name: 'ID', sort: false, attributes: { style: 'display:none;' } },
          { id: 'codigo', name: 'Código' },
          { id: 'descricao', name: 'Descrição' }
        ]" :data="selectedTussRows" :search="true" :searchPlaceholder="'Buscar procedimento...'" :showCheckbox="true"
          :showMultiDelete="false" :showAddButton="false" :showActions="false" :showPerPagination="false"
          :compactSpacing="true" :tableTitle="'Selecione os procedimentos'" />
      </div>
    </Modal>
  </Teleport>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, defineExpose, onMounted, nextTick, watch, toRef, computed } from "vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Modal from "@/Components/Modal.vue";
import Choices from "choices.js";

const props = defineProps({
  contas: { type: Array, default: () => [] },
  tussTabelas: { type: Array, default: () => [] },
  profissionaisSaude: { type: Array, default: () => [] },
});

const spsadtFields = [
  { key: '1_registro_ans', label: '1 - Registro ANS' },
  { key: '2_numero_guia_prestador', label: '2 - Número da Guia no Prestador' },
  { key: '3_numero_guia_principal', label: '3 - Número da Guia Principal' },
  { key: '4_data_autorizacao', label: '4 - Data da Autorização' },
  { key: '5_senha', label: '5 - Senha' },
  { key: '6_validade_senha', label: '6 - Data de Validade da Senha' },
  { key: '7_numero_guia_operadora', label: '7 - Nº da Guia Atribuído pela Operadora' },
  { key: '8_numero_carteira', label: '8 - Número da Carteira' },
  { key: '9_validade_carteira', label: '9 - Validade da Carteira' },
  { key: '10_nome_beneficiario', label: '10 - Nome' },
  { key: '11_cartao_nacional_saude', label: '11 - Cartão Nacional de Saúde (CNS)' },
  { key: '12_atendimento_rn', label: '12 - Atendimento a RN' },
  
  { key: '13_codigo_operadora', label: '13 - Código na Operadora / CPF' },
  { key: '14_nome_contratado', label: '14 - Nome do Contratado Solicitante' },
  { key: '15_nome_profissional_solicitante', label: '15 - Nome do Profissional Solicitante' },
  { key: '16_conselho_profissional', label: '16 - Conselho' },
  { key: '17_numero_conselho', label: '17 - Número do Conselho' },
  { key: '18_uf_conselho', label: '18 - UF' },
  { key: '19_codigo_cbo', label: '19 - CBO S' },
  
  { key: '21_carater_atendimento', label: '21 - Caráter do Atendimento' },
  { key: '22_data_solicitacao', label: '22 - Data da Solicitação' },
  { key: '23_indicacao_clinica', label: '23 - Indicação Clínica' },
  
  { key: '24_tabela', label: '24 - Tabela' },
  { key: '25_codigo_procedimento', label: '25 - Código Proced.' },
  { key: '26_descricao', label: '26 - Descrição' },
  { key: '27_quantidade_solicitada', label: '27 - Qtd. Sol.' },
  { key: '28_quantidade_autorizada', label: '28 - Qtd. Aut.' },
  
  { key: '29_codigo_operadora_executante', label: '29 - Código na Operadora / CPF / CNPJ' },
  { key: '30_nome_contratado_executante', label: '30 - Nome do Contratado Executante' },
  { key: '31_codigo_cnes_executante', label: '31 - Código CNES' },
  
  { key: '32_tipo_atendimento', label: '32 - Tipo de Atendimento' },
  { key: '33_indicacao_acidente', label: '33 - Indicação de Acidente' },
  { key: '34_tipo_consulta', label: '34 - Tipo de Consulta' },
  { key: '35_motivo_encerramento', label: '35 - Motivo de Encerramento' },
  
  { key: '36_data_hora_execucao', label: '36 - Data de Realização' },
  { key: '37_hora_inicial', label: '37 - Hora Inicial' },
  { key: '38_hora_final', label: '38 - Hora Final' },
  { key: '39_tabela_realizado', label: '39 - Tab' },
  { key: '40_codigo_procedimento_realizado', label: '40 - Código Proced.' },
  { key: '41_descricao_realizado', label: '41 - Descrição' },
  { key: '42_quantidade_realizada', label: '42 - Qtd' },
  { key: '43_via_acesso', label: '43 - Via' },
  { key: '44_tecnica_utilizada', label: '44 - Téc' },
  { key: '45_fator_reducao_acrescimo', label: '45 - Fat' },
  { key: '46_valor_unitario', label: '46 - Val Unit' },
  { key: '47_valor_total', label: '47 - Val Tot' },
  { key: '48_sequencial_referencia', label: '48 - Seq. Ref.' },
  { key: '49_grau_participacao', label: '49 - Grau Part.' },
  { key: '50_codigo_operadora_profissional', label: '50 - Código na Operadora / CPF' },
  { key: '51_nome_profissional_executante', label: '51 - Nome do Profissional' },
  { key: '52_conselho_profissional_executante', label: '52 - Conselho Profissional' },
  { key: '53_numero_conselho_executante', label: '53 - Número no Conselho' },
  { key: '54_uf_conselho_executante', label: '54 - UF' },
  { key: '55_codigo_cbo_executante', label: '55 - Código CBO' },
  { key: '58_observacao', label: '58 - Observação / Justificativa' },
  { key: '59_valor_total_honorarios', label: '59 - Total Procedimentos' },
  { key: '60_valor_total_taxas', label: '60 - Total Taxas/Aluguéis' },
  { key: '61_valor_total_materiais', label: '61 - Total Materiais' },
  { key: '62_valor_total_opme', label: '62 - Total OPME' },
  { key: '63_valor_total_medicamentos', label: '63 - Total Medicamentos' },
  { key: '64_valor_total_gases', label: '64 - Total Gases Medicinais' },
  { key: '65_valor_total_geral', label: '65 - Valor Total Geral' }
];


const formEl = ref(null);
const tipoSelect = ref(null);
const tussTabelaSelect = ref(null);
const empresaSelect = ref(null);
const logoInput = ref(null);
const existingLogoPath = ref("");
const logoPreviewUrl = ref("");
const modalMedicoSelect = ref(null);

let tipoChoices = null;
let tussTabelaChoices = null;
let empresaChoices = null;
let modalMedicoChoicesInstance = null;

const allowedTabelas = computed(() => (props.tussTabelas || []).filter(t => !!t));
const medicosOptions = computed(() => {
  const selectedIds = new Set(selectedMedicosRows.value.map(m => Number(m.id)));
  return (props.profissionaisSaude || [])
    .filter((m) => !selectedIds.has(Number(m.id)))
    .map((m) => ({
      id: Number(m.id),
      nome: m.nome || "",
      crm: m.conselho ? `${m.conselho.sigla}/${m.uf_conselho} ${m.numero_conselho}` : "",
      especialidades: (m.especialidades || []).map(e => ({ id: e.id, nome: e.nome })),
    }));
});

const showAddMedicoModal = ref(false);
const modalSelectedMedicoId = ref("");

const showEditMedicoTussModal = ref(false);
const editingMedicoData = ref(null);
const medicoTussGridRef = ref(null);

function editMedico(rowId, rowData) {
  // If rowData is not provided, try to find it by rowId
  const id = rowData?.id || rowId;
  const m = selectedMedicosRows.value.find(r => Number(r.id) === Number(id));
  if (!m) return;
  editingMedicoData.value = { ...m };
  showEditMedicoTussModal.value = true;
  nextTick(() => {
    if (medicoTussGridRef.value) {
      const selectedIds = m.tuss_ids || [];
      medicoTussGridRef.value.setSelectedRowIds(selectedIds);
    }
  });
}

function saveMedicoTussModal() {
  if (!editingMedicoData.value) return;
  let selectedIds = [];
  if (medicoTussGridRef.value) {
    selectedIds = medicoTussGridRef.value.getSelectedRowIds().map(Number).filter(n => Number.isFinite(n));
  }

  const mIndex = selectedMedicosRows.value.findIndex(r => Number(r.id) === Number(editingMedicoData.value.id));
  if (mIndex !== -1) {
    selectedMedicosRows.value[mIndex].tuss_ids = selectedIds;
    form.medicos = selectedMedicosRows.value.map(m => ({
      id: m.id,
      tuss_ids: m.tuss_ids || []
    }));
  }

  showEditMedicoTussModal.value = false;
  editingMedicoData.value = null;
}

const form = useForm({
  config_spsadt: { visivel: [], obrigatorio: [], bloqueado: [] },
  id: null,
  descricao: "",
  logo: null,
  tipo: "Convenio",
  tuss_tabela: "",
  tuss_ids: [],
  medicos: [],
  empresa_id: "",
  ans: null,
  dias_recebimento: null,
  dias_retorno: null,
  _method: null,
});

const hasLogoFile = computed(() => {
  try {
    return !!form.logo && typeof File !== "undefined" && form.logo instanceof File;
  } catch (_) {
    return !!form.logo;
  }
});

const isTipoParticular = computed(() => String(form.tipo || '').toLowerCase() === 'particular');

const canLoadTussProcedimentos = computed(() => !isTipoParticular.value && !!form.tuss_tabela);

const tussGridRef = ref(null);
const tussGridSelectedIds = ref([]);
function onTussGridSelectionChange(ids) {
  tussGridSelectedIds.value = Array.isArray(ids) ? ids : [];
}
const tussServerUrl = computed(() => {
  if (!canLoadTussProcedimentos.value) return "";
  return `/tuss/tabelas/${encodeURIComponent(String(form.tuss_tabela || ""))}/procedimentos`;
});
const tussGridColumns = [
  { id: "id", name: "ID", sort: false, attributes: { style: "display:none;" } },
  { id: "codigo", name: "Código" },
  { id: "descricao", name: "Descrição" },
  { id: "total", name: "Total", formatter: (cell) => formatMoney(cell) },
];

const medicosGridColumns = [
  { id: "id", name: "ID", sort: false, attributes: { style: "display:none;" } },
  { id: "nome", name: "Nome" },
  { id: "crm", name: "Conselho" },
  {
    id: "especialidades",
    name: "Especialidades",
    formatter: (cell, row) => {
      console.log("formatter called", { cell, row });
      // Let's check if row has _cells or if we can get the actual data row
      const idIndex = 0; // since no checkbox, first cell is id
      const id = row.cells?.[idIndex]?.data;
      const dataRow = selectedMedicosRows.value.find(r => Number(r.id) === Number(id));
      console.log("found dataRow", dataRow);
      return (dataRow?.especialidades || []).map(e => e.nome).join(", ") || "-";
    }
  },
  {
    id: "procedimentos",
    name: "Procedimentos TUSS",
    formatter: (cell, row) => {
      const idIndex = 0;
      const id = row.cells?.[idIndex]?.data;
      const dataRow = selectedMedicosRows.value.find(r => Number(r.id) === Number(id));
      const count = (dataRow?.tuss_ids || []).length;
      return count > 0 ? `${count} procedimento(s)` : 'Nenhum selecionado';
    }
  },
];

const selectedTussRows = ref([]);
watch(selectedTussRows, (rows) => {
  form.tuss_ids = rows.map(r => ({ id: Number(r.id), requer_autorizacao: !!r.requer_autorizacao }));
}, { deep: true });

const selectedMedicosRows = ref([]);
const selectedTussIds = computed(() => selectedTussRows.value.map(r => Number(r.id)).filter(n => Number.isFinite(n)));
const selectedMedicoIds = computed(() => selectedMedicosRows.value.map(r => Number(r.id)).filter(n => Number.isFinite(n)));
const selectedTussQuery = ref("");
const selectedMedicosQuery = ref("");
const filteredSelectedTussRows = computed(() => {
  const q = String(selectedTussQuery.value || "").trim().toLowerCase();
  if (q === "") return selectedTussRows.value;
  return selectedTussRows.value.filter(r => {
    const tabela = String(r?.tabela || "").toLowerCase();
    const codigo = String(r?.codigo || "").toLowerCase();
    const desc = String(r?.descricao || "").toLowerCase();
    return tabela.includes(q) || codigo.includes(q) || desc.includes(q);
  });
});
const filteredSelectedMedicosRows = computed(() => {
  const q = String(selectedMedicosQuery.value || "").trim().toLowerCase();
  if (q === "") return selectedMedicosRows.value;
  return selectedMedicosRows.value.filter(m => {
    const nome = String(m?.nome || "").toLowerCase();
    const crm = String(m?.crm || "").toLowerCase();
    return nome.includes(q) || crm.includes(q);
  });
});

function isSelectedTussRow(row) {
  const id = Number(row?.id);
  if (!Number.isFinite(id)) return false;
  return selectedTussRows.value.some(r => Number(r.id) === id);
}

function addTussRow(row) {
  const id = Number(row?.id);
  if (!Number.isFinite(id) || isSelectedTussRow(row)) return;
  selectedTussRows.value.push({
    id,
    tabela: row?.tabela ?? form.tuss_tabela ?? "",
    codigo: row?.codigo ?? "",
    descricao: row?.descricao ?? "",
    total: row?.total ?? null,
    requer_autorizacao: false,
  });
}

function removeTussRow(id) {
  const tid = Number(id);
  selectedTussRows.value = selectedTussRows.value.filter(r => Number(r.id) !== tid);
}

function addSelectedTuss() {
  const newIds = tussGridSelectedIds.value.filter(id => !selectedTussRows.value.some(r => Number(r.id) === Number(id)));
  if (newIds.length === 0) return;
  const newRows = newIds.map(id => {
    const dataRow = tussGridRef.value?.getRowData?.(id) || { id, codigo: '?', descricao: 'Desconhecido', tabela: '' };
    return { ...dataRow, requer_autorizacao: false };
  });
  selectedTussRows.value.push(...newRows);
  tussGridSelectedIds.value = [];
  try { tussGridRef.value?.clearSelection?.(); } catch (_) { }
}

function clearSelectedTuss() {
  selectedTussRows.value = [];
  selectedTussQuery.value = "";
  try { tussGridRef.value?.clearSelection?.(); } catch (_) { }
  tussGridSelectedIds.value = [];
}

function removeSelectedTussRows() {
  selectedTussRows.value = selectedTussRows.value.filter(r => !tussGridSelectedIds.value.includes(Number(r.id)));
  tussGridSelectedIds.value = [];
  try { tussGridRef.value?.clearSelection?.(); } catch (_) { }
}

function clearSelectedMedicos() {
  selectedMedicosRows.value = [];
  form.medicos = [];
  // cleared medicos
}

function setSelectedMedicos(rows) {
  selectedMedicosRows.value = Array.isArray(rows)
    ? rows
      .map((m) => ({
        id: Number(m?.id),
        nome: m?.nome || "",
        crm: m?.crm || "",
        especialidades: (m?.especialidades || []).map(e => ({ id: e.id, nome: e.nome })),
        tuss_ids: Array.isArray(m?.tuss_ids) ? m.tuss_ids.map(Number) : []
      }))
      .filter((m) => Number.isFinite(m.id))
    : [];
  form.medicos = selectedMedicosRows.value.map(m => ({
    id: m.id,
    tuss_ids: m.tuss_ids || []
  }));
}

function refreshModalChoices() {
  // Esta função não é mais necessária pois usamos a API direta do Choices.js no openAddMedicoModal
}

function removeMedico(row) {
  const targetId = Number(row?.id);
  if (!Number.isFinite(targetId)) return;
  selectedMedicosRows.value = selectedMedicosRows.value.filter((m) => Number(m.id) !== targetId);
  form.medicos = selectedMedicosRows.value.map(m => ({
    id: m.id,
    tuss_ids: m.tuss_ids || []
  }));
}

function addSelectedFromGrid() {
  const rows = tussGridRef.value?.getSelectedRowObjects?.() || [];
  for (const r of rows) addTussRow(r);
  try { tussGridRef.value?.clearSelection?.(); } catch (_) { }
  tussGridSelectedIds.value = [];
}

function syncTussIdsBeforeSubmit() {
  if (isTipoParticular.value) {
    form.tuss_ids = [];
    return;
  }
  if (tussGridSelectedIds.value.length > 0) {
    addSelectedFromGrid();
  }
  // Garante que o form enviará a lista completa de objetos (id + requer_autorizacao)
  form.tuss_ids = selectedTussRows.value.map(r => ({ id: Number(r.id), requer_autorizacao: !!r.requer_autorizacao }));
}


function formatMoney(v) {
  const n = Number(v ?? 0);
  if (!Number.isFinite(n)) return "—";
  return n.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

const logoDisplayUrl = computed(() => {
  if (logoPreviewUrl.value) return logoPreviewUrl.value;
  if (existingLogoPath.value) return `/storage/${existingLogoPath.value}`;
  return "";
});

function clearLogoLocal() {
  form.logo = null;
  if (logoPreviewUrl.value) {
    try { URL.revokeObjectURL(logoPreviewUrl.value); } catch (_) { }
  }
  logoPreviewUrl.value = "";
  if (logoInput.value) logoInput.value.value = "";
}

function setExistingLogoPath(path) {
  existingLogoPath.value = String(path || "");
  clearLogoLocal();
}

function onLogoChange(e) {
  const f = e?.target?.files?.[0] || null;
  form.logo = f;
  if (logoPreviewUrl.value) {
    try { URL.revokeObjectURL(logoPreviewUrl.value); } catch (_) { }
  }
  logoPreviewUrl.value = f ? URL.createObjectURL(f) : "";
}

/* ======================
   GET CHOICES INSTANCE
====================== */
const getChoicesInstance = () => {
  return tipoSelect.value?._choicesInstance || tipoSelect.value?.choices || null;
};
const getTussTabelaChoicesInstance = () => {
  return tussTabelaSelect.value?._choicesInstance || tussTabelaSelect.value?.choices || null;
};
const getEmpresaChoicesInstance = () => {
  return empresaSelect.value?._choicesInstance || empresaSelect.value?.choices || null;
};

/* ======================
   SYNC VUE → CHOICES
====================== */
watch(
  () => form.tipo,
  async (value) => {
    await nextTick();
    if (window.syncChoiceValue && tipoSelect.value) {
      window.syncChoiceValue(tipoSelect.value, value || "");
    }
    if (String(value || '').toLowerCase() === 'particular') {
      form.tuss_tabela = '';
      clearSelectedTuss();
      await nextTick();
      if (window.syncChoiceValue && tussTabelaSelect.value) {
        window.syncChoiceValue(tussTabelaSelect.value, "");
      }
    }
  },
  { immediate: true }
);
watch(
  () => form.tuss_tabela,
  async (value) => {
    await nextTick();
    if (window.syncChoiceValue && tussTabelaSelect.value) {
      window.syncChoiceValue(tussTabelaSelect.value, value || "");
    }
  },
  { immediate: true }
);

/* ======================
   SYNC CHOICES → VUE
====================== */
const onTipoChange = (e) => {
  form.tipo = e?.target?.value ?? form.tipo;
};
const onTussTabelaChange = (e) => {
  form.tuss_tabela = e?.target?.value ?? form.tuss_tabela;
};
watch(
  () => form.empresa_id,
  async (value) => {
    await nextTick();
    if (window.syncChoiceValue && empresaSelect.value) {
      window.syncChoiceValue(empresaSelect.value, value ?? "");
    }
  },
  { immediate: true }
);
const onEmpresaChange = (e) => {
  form.empresa_id = e?.target?.value ?? form.empresa_id;
};

watch(showAddMedicoModal, (newVal) => {
  if (!newVal) {
    if (modalMedicoChoicesInstance) {
      // Remove o listener que adicionamos no mount/open
      if (modalMedicoSelect.value) {
        modalMedicoSelect.value.removeEventListener('change', handleModalMedicoSelect);
      }
      modalMedicoChoicesInstance.clearStore();
      modalMedicoChoicesInstance.destroy();
      modalMedicoChoicesInstance = null;
    }
  }
});

function openAddMedicoModal() {
  showAddMedicoModal.value = true;
  modalSelectedMedicoId.value = "";

  // Usamos um setTimeout para garantir que o DOM e os Computed Properties (medicosOptions)
  // do Vue já estejam 100% atualizados com o novo array antes de criarmos o Choices
  setTimeout(() => {
    if (!modalMedicoSelect.value) return;

    if (modalMedicoChoicesInstance) {
      modalMedicoSelect.value.removeEventListener('change', handleModalMedicoSelect);
      modalMedicoChoicesInstance.destroy();
      modalMedicoChoicesInstance = null;
    }

    modalMedicoChoicesInstance = new Choices(modalMedicoSelect.value, {
      searchEnabled: true,
      shouldSort: false,
      itemSelectText: '',
      noResultsText: 'Nenhum médico encontrado',
      noChoicesText: 'Nenhum médico disponível',
    });

    const choicesData = medicosOptions.value.map(m => ({
      value: m.id,
      label: m.crm ? `${m.nome} - ${m.crm}` : m.nome,
    }));

    choicesData.unshift({
      value: '',
      label: 'Selecione um médico...',
      selected: true,
      disabled: true
    });

    modalMedicoChoicesInstance.setChoices(choicesData, 'value', 'label', true);

    // Garante que o evento 'change' do Choices alimente a variável reativa do Vue
    modalMedicoSelect.value.addEventListener('change', handleModalMedicoSelect, { once: false });
  }, 50);
}

function handleModalMedicoSelect(e) {
  modalSelectedMedicoId.value = e.detail ? e.detail.value : e.target.value;
}

// Quando a seleção do Choices muda, precisamos atualizar o valor no Vue
onMounted(() => {
  if (modalMedicoSelect.value) {
    modalMedicoSelect.value.addEventListener('change', handleModalMedicoSelect);
  }
});

function addMedicoFromModal() {
  if (!modalSelectedMedicoId.value) return;

  const id = Number(modalSelectedMedicoId.value);
  const m = medicosOptions.value.find((o) => o.id === id);
  if (!m) return;

  selectedMedicosRows.value.push({
    id: m.id,
    nome: m.nome,
    crm: m.crm,
    especialidades: m.especialidades || []
  });
  form.medicos = selectedMedicosRows.value.map(m => ({
    id: m.id,
    tuss_ids: m.tuss_ids || []
  }));

  if (modalMedicoChoicesInstance) {
    if (modalMedicoSelect.value) {
      modalMedicoSelect.value.removeEventListener('change', handleModalMedicoSelect);
    }
    // Removemos os dados da instância antes de destruir
    modalMedicoChoicesInstance.clearStore();
    modalMedicoChoicesInstance.destroy();
    modalMedicoChoicesInstance = null;
  }

  showAddMedicoModal.value = false;
  modalSelectedMedicoId.value = "";
}

onMounted(async () => {
  await nextTick();

  // Inicialização automática do tema
  if (window.initChoices) {
    window.initChoices();
  }

  // Captura a instância após init
  await nextTick();
  tipoChoices = getChoicesInstance();
  tussTabelaChoices = getTussTabelaChoicesInstance();
  empresaChoices = getEmpresaChoicesInstance();

  // Escuta mudanças do Choices
  if (tipoSelect.value) {
    tipoSelect.value.addEventListener("change", onTipoChange);
  }
  if (tussTabelaSelect.value) {
    tussTabelaSelect.value.addEventListener("change", onTussTabelaChange);
  }
  if (empresaSelect.value) {
    empresaSelect.value.addEventListener("change", onEmpresaChange);
  }

  // Força valor inicial (update)
  if (window.syncChoiceValue && tipoSelect.value) {
    window.syncChoiceValue(tipoSelect.value, form.tipo || "");
  }
  if (window.syncChoiceValue && tussTabelaSelect.value) {
    window.syncChoiceValue(tussTabelaSelect.value, form.tuss_tabela || "");
  }
  if (window.syncChoiceValue && empresaSelect.value) {
    window.syncChoiceValue(empresaSelect.value, form.empresa_id ?? "");
  }
  setSelectedMedicos(selectedMedicosRows.value);
});

const submit = (onSuccess, hooks = {}) => {
  if (formEl.value && !formEl.value.checkValidity()) {
    formEl.value.classList.add("was-validated");
    return;
  }
  syncTussIdsBeforeSubmit();
  form.medicos = selectedMedicosRows.value.map(m => ({
    id: m.id,
    tuss_ids: m.tuss_ids || []
  }));
  const id = Number(form.id);
  if (Number.isFinite(id) && id > 0) {
    if (hasLogoFile.value) {
      form._method = "put";
      form.post(`/convenios/${id}`, {
        forceFormData: true,
        onStart: () => { try { hooks.onStart?.(); } catch (_) { } },
        onFinish: () => {
          try { delete form._method; } catch (_) { form._method = null; }
          try { hooks.onFinish?.(); } catch (_) { }
        },
        onSuccess: () => {
          formEl.value?.classList.remove("was-validated");
          if (onSuccess) onSuccess();
          clearLogoLocal();
          clearSelectedTuss();
          setSelectedMedicos([]);
        },
      });
    } else {
      form.put(`/convenios/${id}`, {
        onStart: () => { try { hooks.onStart?.(); } catch (_) { } },
        onFinish: () => { try { hooks.onFinish?.(); } catch (_) { } },
        onSuccess: () => {
          formEl.value?.classList.remove("was-validated");
          if (onSuccess) onSuccess();
          clearLogoLocal();
          clearSelectedTuss();
          setSelectedMedicos([]);
        },
      });
    }
  } else {
    form.post("/convenios", {
      forceFormData: true,
      onStart: () => { try { hooks.onStart?.(); } catch (_) { } },
      onFinish: () => { try { hooks.onFinish?.(); } catch (_) { } },
      onSuccess: () => {
        formEl.value?.classList.remove("was-validated");
        if (onSuccess) onSuccess();
        form.reset();
        existingLogoPath.value = "";
        clearLogoLocal();
        clearSelectedTuss();
        setSelectedMedicos([]);
      },
    });
  }
};
const submitUpdate = (id, onSuccess, hooks = {}) => {
  if (formEl.value && !formEl.value.checkValidity()) {
    formEl.value.classList.add("was-validated");
    return;
  }
  syncTussIdsBeforeSubmit();
  form.medicos = selectedMedicosRows.value.map(m => ({
    id: m.id,
    tuss_ids: m.tuss_ids || []
  }));
  const cid = Number(id);
  if (!Number.isFinite(cid) || cid <= 0) return;
  if (hasLogoFile.value) {
    form._method = "put";
    form.post(`/convenios/${cid}`, {
      forceFormData: true,
      onStart: () => { try { hooks.onStart?.(); } catch (_) { } },
      onFinish: () => {
        try { delete form._method; } catch (_) { form._method = null; }
        try { hooks.onFinish?.(); } catch (_) { }
      },
      onSuccess: () => {
        formEl.value?.classList.remove("was-validated");
        if (onSuccess) onSuccess();
        // Não limpa se formos voltar para a listagem para não perder os dados antes da transição da página
        if (!id) {
          clearLogoLocal();
          clearSelectedTuss();
          setSelectedMedicos([]);
        }
      },
    });
  } else {
    form.put(`/convenios/${cid}`, {
      onStart: () => { try { hooks.onStart?.(); } catch (_) { } },
      onFinish: () => { try { hooks.onFinish?.(); } catch (_) { } },
      onSuccess: () => {
        formEl.value?.classList.remove("was-validated");
        if (onSuccess) onSuccess();
        if (!cid) {
          clearLogoLocal();
          clearSelectedTuss();
          setSelectedMedicos([]);
        }
      },
    });
  }
};

function setSelectedTussRows(rows) {
  selectedTussRows.value = Array.isArray(rows) ? rows.map(r => ({
    id: Number(r?.id),
    tabela: r?.tabela ?? "",
    codigo: r?.codigo ?? "",
    descricao: r?.descricao ?? "",
    total: r?.total ?? null,
    requer_autorizacao: !!r?.requer_autorizacao,
  })).filter(r => Number.isFinite(r.id)) : [];
}

const toggleAll = (type, isChecked) => {
  if (isChecked) {
    form.config_spsadt[type] = spsadtFields.map(f => f.key);
  } else {
    form.config_spsadt[type] = [];
  }
};

const isAllChecked = (type) => {
  return form.config_spsadt[type] && form.config_spsadt[type].length === spsadtFields.length && spsadtFields.length > 0;
};

defineExpose({ form, submit, submitUpdate, processingRef: toRef(form, "processing"), setExistingLogoPath, clearLogoLocal, setSelectedTussRows, setSelectedMedicos });

</script>

<style scoped>
.choices {
  margin-bottom: 0 !important;
}

:deep(.nav-tabs-custom .nav-link.active) {
  background-color: transparent !important;
}

.tuss-panel {
  box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .04);
}

.tuss-selected-scroll {
  max-height: 320px;
  overflow: auto;
}

.tuss-results-scroll {
  max-height: 420px;
  overflow: auto;
}

.tuss-empty {
  border: 1px dashed rgba(0, 0, 0, .15);
  border-radius: .5rem;
  padding: 1rem;
  background: rgba(255, 255, 255, .6);
}

.logo-preview-box {
  width: 25%;
  height: 64px;
  border: 1px dashed rgba(0, 0, 0, .2);
  border-radius: .5rem;
  background: rgba(0, 0, 0, .015);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: .25rem;
}

.logo-preview-spacer {
  height: 64px;
}

.logo-preview-img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  display: block;
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
