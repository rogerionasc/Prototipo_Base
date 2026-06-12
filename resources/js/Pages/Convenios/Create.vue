<template>
  <form
    class="row g-3 needs-validation"
    novalidate
    ref="formEl"
    @submit.prevent="submit"
  >
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
            <input ref="logoInput" type="file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp" class="form-control" @change="onLogoChange" />
            <div class="form-text">PNG, JPG ou WEBP (máx. 2MB).</div>
          </div>

          <div class="col-md-6">
            <div class="d-none d-md-block logo-preview-spacer mb-2"></div>
            <label for="descricao" class="form-label">Descrição</label>
            <span class="text-danger ms-1">*</span>
            <input
              v-model="form.descricao"
              type="text"
              class="form-control"
              id="descricao"
              placeholder="Nome do convênio"
              required
              maxlength="120"
            />
            <div class="invalid-feedback">Informe a descrição.</div>
          </div>

          <div class="col-md-6">
            <label for="tipo" class="form-label">Tipo</label>
            <select
              data-choices
              ref="tipoSelect"
              v-model="form.tipo"
              class="form-select"
              id="tipo"
            >
              <option disabled value="">Selecione...</option>
              <option value="Convenio">Convênio</option>
              <option value="Particular">Particular</option>
            </select>
          </div>

          <div class="col-md-6">
            <label for="empresa" class="form-label">Empresa</label>
            <select
              v-model="form.empresa_id"
              data-choices
              class="form-select"
              id="empresa"
              ref="empresaSelect"
            >
              <option disabled value="">Selecione...</option>
              <option
                v-for="c in props.contas"
                :key="c.id"
                :value="c.id"
              >
                {{ c.nome }}
              </option>
            </select>
          </div>

          <div class="col-md-2">
            <label for="ans" class="form-label">ANS</label>
            <input
              v-model.number="form.ans"
              type="number"
              class="form-control"
              id="ans"
              placeholder="Código ANS"
            />
          </div>

          <div class="col-md-2">
            <label for="diasReceb" class="form-label">Dias Receb.</label>
            <input
              v-model.number="form.dias_recebimento"
              type="number"
              class="form-control"
              id="diasReceb"
              placeholder="Ex.: 30"
            />
          </div>

          <div class="col-md-2">
            <label for="diasRet" class="form-label">Dias Retorno</label>
            <input
              v-model.number="form.dias_retorno"
              type="number"
              class="form-control"
              id="diasRet"
              placeholder="Ex.: 7"
            />
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
                <select
                  data-choices
                  ref="tussTabelaSelect"
                  v-model="form.tuss_tabela"
                  class="form-select"
                  id="tussTabela"
                  :disabled="isTipoParticular"
                >
                  <option disabled value="">Selecione...</option>
                  <option v-for="t in allowedTabelas" :key="t" :value="t">{{ t }}</option>
                </select>
                <div v-if="form.errors.tuss_tabela" class="invalid-feedback d-block">{{ form.errors.tuss_tabela }}</div>
              </div>

              <div class="col-lg-6">
                <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                  <button
                    type="button"
                    class="btn btn-sm btn-soft-danger"
                    @click="clearSelectedTuss"
                    :disabled="selectedTussRows.length === 0"
                  >
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
                    <input
                      v-model="selectedTussQuery"
                      type="text"
                      class="form-control search"
                      placeholder="Buscar nos selecionados"
                      :disabled="selectedTussRows.length === 0"
                    />
                    <i class="ri-search-line search-icon"></i>
                  </div>

                  <div class="tuss-selected-scroll">
                    <ul class="list-group list-group-flush" v-if="filteredSelectedTussRows.length > 0">
                      <li
                        v-for="r in filteredSelectedTussRows"
                        :key="`sel-${r.id}`"
                        class="list-group-item px-0 py-2 d-flex justify-content-between align-items-start"
                      >
                        <span class="d-flex flex-column me-2 flex-grow-1" style="min-width:0;">
                          <span class="d-flex align-items-center flex-wrap gap-2">
                            <span class="fw-bold">{{ r.codigo }}</span>
                            <span class="badge bg-secondary-subtle text-secondary">{{ r.tabela || "—" }}</span>
                          </span>
                          <span class="text-muted small mt-1 text-truncate-2">{{ r.descricao }}</span>
                        </span>
                        <span class="d-flex align-items-start gap-2">
                          <span v-if="r.total !== null && r.total !== undefined && String(r.total) !== ''" class="text-muted small text-nowrap mt-1">
                            {{ formatMoney(r.total) }}
                          </span>
                          <button type="button" class="btn btn-sm btn-soft-danger" @click="removeTussRow(r.id)">
                            <i class="ri-close-line"></i>
                          </button>
                        </span>
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
                  <div class="text-muted small">Depois use a busca para encontrar o procedimento e adicionar ao convênio.</div>
                </div>
              </div>
              <TableGrid
                v-if="form.tuss_tabela"
                ref="tussGridRef"
                :serverUrl="tussServerUrl"
                :columns="tussGridColumns"
                :search="true"
                :searchPlaceholder="'Buscar procedimento (código ou descrição)'"
                :showCheckbox="true"
                :showMultiDelete="false"
                :showAddButton="true"
                :addButtonText="'Adicionar selecionados'"
                :addButtonIconClass="'ri-add-circle-line'"
                :addButtonDisabled="tussGridSelectedIds.length === 0"
                :showActions="false"
                :showPerPagination="true"
                :compactSpacing="true"
                :tableTitle="`Procedimentos (${form.tuss_tabela})`"
                @add="addSelectedFromGrid"
                @selectionChange="onTussGridSelectionChange"
              />
            </div>
          </div>
        </div>
      </BTab>
    </BTabs>
  </form>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, defineExpose, onMounted, nextTick, watch, toRef, computed } from "vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";

const props = defineProps({
  contas: { type: Array, default: () => [] },
  tussTabelas: { type: Array, default: () => [] },
});

const formEl = ref(null);
const tipoSelect = ref(null);
const tussTabelaSelect = ref(null);
const empresaSelect = ref(null);
const logoInput = ref(null);
const existingLogoPath = ref("");
const logoPreviewUrl = ref("");

let tipoChoices = null;
let tussTabelaChoices = null;
let empresaChoices = null;

const allowedTabelas = computed(() => (props.tussTabelas || []).filter(t => !!t));

const form = useForm({
  descricao: "",
  logo: null,
  tipo: "Convenio",
  tuss_tabela: "",
  tuss_ids: [],
  empresa_id: "",
  ans: null,
  dias_recebimento: null,
  dias_retorno: null,
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

const selectedTussRows = ref([]);
const selectedTussIds = computed(() => selectedTussRows.value.map(r => Number(r.id)).filter(n => Number.isFinite(n)));
const selectedTussQuery = ref("");
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
  });
  form.tuss_ids = selectedTussIds.value;
}

function removeTussRow(id) {
  const tid = Number(id);
  selectedTussRows.value = selectedTussRows.value.filter(r => Number(r.id) !== tid);
  form.tuss_ids = selectedTussIds.value;
}

function clearSelectedTuss() {
  selectedTussRows.value = [];
  form.tuss_ids = [];
  selectedTussQuery.value = "";
  try { tussGridRef.value?.clearSelection?.(); } catch (_) {}
  tussGridSelectedIds.value = [];
}

function addSelectedFromGrid() {
  const rows = tussGridRef.value?.getSelectedRowObjects?.() || [];
  for (const r of rows) addTussRow(r);
  try { tussGridRef.value?.clearSelection?.(); } catch (_) {}
  tussGridSelectedIds.value = [];
}

function syncTussIdsBeforeSubmit() {
  if (isTipoParticular.value) {
    form.tuss_ids = [];
    return;
  }
  if (tussGridSelectedIds.value.length > 0) {
    addSelectedFromGrid();
  } else {
    form.tuss_ids = selectedTussIds.value;
  }
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
    try { URL.revokeObjectURL(logoPreviewUrl.value); } catch (_) {}
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
    try { URL.revokeObjectURL(logoPreviewUrl.value); } catch (_) {}
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
});

const submit = (onSuccess, hooks = {}) => {
  if (formEl.value && !formEl.value.checkValidity()) {
    formEl.value.classList.add("was-validated");
    return;
  }
  syncTussIdsBeforeSubmit();
  form.post("/convenios", {
    forceFormData: true,
    onStart: () => { try { hooks.onStart?.(); } catch (_) {} },
    onFinish: () => { try { hooks.onFinish?.(); } catch (_) {} },
    onSuccess: () => {
      formEl.value?.classList.remove("was-validated");
      if (onSuccess) onSuccess();
      form.reset();
      existingLogoPath.value = "";
      clearLogoLocal();
      clearSelectedTuss();
    },
  });
};
const submitUpdate = (id, onSuccess, hooks = {}) => {
  if (formEl.value && !formEl.value.checkValidity()) {
    formEl.value.classList.add("was-validated");
    return;
  }
  syncTussIdsBeforeSubmit();
  form.put(`/convenios/${id}`, {
    forceFormData: true,
    onStart: () => { try { hooks.onStart?.(); } catch (_) {} },
    onFinish: () => { try { hooks.onFinish?.(); } catch (_) {} },
    onSuccess: () => {
      formEl.value?.classList.remove("was-validated");
      if (onSuccess) onSuccess();
      clearLogoLocal();
      clearSelectedTuss();
    },
  });
};

function setSelectedTussRows(rows) {
  selectedTussRows.value = Array.isArray(rows) ? rows.map(r => ({
    id: Number(r?.id),
    tabela: r?.tabela ?? "",
    codigo: r?.codigo ?? "",
    descricao: r?.descricao ?? "",
    total: r?.total ?? null,
  })).filter(r => Number.isFinite(r.id)) : [];
  form.tuss_ids = selectedTussIds.value;
}

defineExpose({ form, submit, submitUpdate, processingRef: toRef(form, "processing"), setExistingLogoPath, clearLogoLocal, setSelectedTussRows });

</script>

<style scoped>
.choices {
  margin-bottom: 0 !important;
}
.tuss-panel {
  box-shadow: 0 .125rem .25rem rgba(0,0,0,.04);
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
  border: 1px dashed rgba(0,0,0,.15);
  border-radius: .5rem;
  padding: 1rem;
  background: rgba(255,255,255,.6);
}
.logo-preview-box {
  width: 25%;
  height: 64px;
  border: 1px dashed rgba(0,0,0,.2);
  border-radius: .5rem;
  background: rgba(0,0,0,.015);
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
