<template>
  <Layout>
    <Head title="Configurações" />
    <PageHeader title="Configurações" pageTitle="Menu" />
    <BRow class="g-0">
      <BTabs
        nav-class="custom-verti-nav-pills text-center config-nav"
        nav-wrapper-class="col-lg-3 config-nav-wrapper"
        content-class="text-muted mt-3 mt-lg-0 col-lg-9"
        pills
        vertical
        justified
      >
        <BTab active>
          <template #title>
            <i class="ri-equalizer-line d-block fs-3xl mb-1"></i>Parametrização
          </template>
          <Parametrizacao
            :estadosCivis="props.estadosCivis"
            :tiposSanguineos="props.tiposSanguineos"
            :canaisAviso="props.canaisAviso"
            :parentescos="props.parentescos"
            :categoriasProcedimento="props.categoriasProcedimento"
          />
        </BTab>
        <BTab>
          <template #title>
            <i class="ri-first-aid-kit-line d-block fs-3xl mb-1"></i>Especialidades
          </template>
          <Especialidade :especialidades="props.especialidades" :procedimentos="props.procedimentos" />
        </BTab>
        <BTab>
          <template #title>
            <i class="ri-layout-grid-line d-block fs-3xl mb-1"></i>Tabela TUSS
          </template>
          <BCard class="shadow-sm config-card">
            <BCardHeader class="bg-light-subtle p-3 border-0">
              <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                <BCardTitle class="mb-0"><i class="ri-layout-grid-line text-primary me-2"></i>Tabela TUSS</BCardTitle>
                <div class="d-flex align-items-center gap-2">
                  <a
                    href="#"
                    class="link-primary fw-semibold text-nowrap link-offset-2 link-underline-opacity-0 link-underline-opacity-100-hover"
                    title="Importar registros via arquivo CSV"
                    :class="{ 'pe-none opacity-50': tussImportProcessing || tussCreateForm.processing }"
                    :aria-disabled="(tussImportProcessing || tussCreateForm.processing) ? 'true' : 'false'"
                    @click.prevent="(tussImportProcessing || tussCreateForm.processing) ? null : openTussImportModal()"
                  >
                    <i class="ri-upload-2-line me-1"></i>
                    Importar CSV
                  </a>
                </div>
              </div>
            </BCardHeader>
            <BCardBody>
              <p class="text-muted mb-3">Gerencie os procedimentos padronizados pela tabela TUSS.</p>
              <div class="d-flex justify-content-end align-items-center gap-2 mb-2">
                <BDropdown
                  class="position-static"
                  menu-class="shadow-lg dropdown-menu-end tuss-filter-menu"
                  toggle-class="btn btn-sm btn-soft-primary"
                  variant="soft-primary"
                  size="sm"
                >
                  <template #button-content>
                    <i class="ri-filter-3-line me-1"></i>
                    Tabela: <span class="fw-semibold">{{ tussTabelaFilter || 'Todas' }}</span>
                  </template>
                  <BDropdownItem :active="tussTabelaFilter === ''" @click="setTussTabelaFilter('')">Todas</BDropdownItem>
                  <BDropdownItem v-for="t in allowedTabelas" :key="t" :active="tussTabelaFilter === t" @click="setTussTabelaFilter(t)">
                    {{ t }}
                  </BDropdownItem>
                </BDropdown>
              </div>

              <TableGrid
                :columns="tussColumns"
                :data="[]"
                :serverUrl="'/tuss/list'"
                :serverQuery="tussServerQuery"
                :tableTitle="'Registros TUSS'"
                :showStatus="false"
                :searchPlaceholder="'Buscar por código, descrição ou tabela'"
                :showCheckbox="false"
                :showAddButton="true"
                :showActions="false"
                :compactSpacing="true"
                @add="openTussCreateModal"
              />

              <Modal
                v-model="tussCreateModalOpen"
                title="Adicionar registro"
                size="xl"
                :name-button="'Salvar'"
                :processing="tussCreateForm.processing"
                :disable-close="tussCreateForm.processing"
                @save="storeTuss"
              >
                <div class="text-muted small mb-3">Os campos <span class="text-danger">*</span> são obrigatórios</div>
                <BRow class="g-3">
                  <BCol md="3">
                    <label class="form-label">Tabela <span class="text-danger">*</span></label>
                    <select v-model.trim="tussCreateForm.tabela" data-choices class="form-select" :class="{ 'is-invalid': !!tussCreateForm.errors.tabela }">
                      <option value="">Selecione</option>
                      <option v-for="t in allowedTabelas" :key="t" :value="t">{{ t }}</option>
                    </select>
                    <div class="invalid-feedback">{{ tussCreateForm.errors.tabela }}</div>
                  </BCol>
                  <BCol md="3">
                    <label class="form-label">Código <span class="text-danger">*</span></label>
                    <input v-model.trim="tussCreateForm.codigo" type="text" class="form-control" placeholder="Ex.: 000000" :class="{ 'is-invalid': !!tussCreateForm.errors.codigo }" />
                    <div class="invalid-feedback">{{ tussCreateForm.errors.codigo }}</div>
                  </BCol>
                  <BCol md="6">
                    <label class="form-label">Descrição</label>
                    <input v-model.trim="tussCreateForm.descricao" type="text" class="form-control" placeholder="Ex.: Procedimento Exemplo" :class="{ 'is-invalid': !!tussCreateForm.errors.descricao }" />
                    <div class="invalid-feedback">{{ tussCreateForm.errors.descricao }}</div>
                  </BCol>
                </BRow>

                <details class="mt-3">
                  <summary class="small text-primary">Campos avançados</summary>
                  <BRow class="g-3 mt-1">
                    <BCol md="2">
                      <label class="form-label">m² Filme</label>
                      <input v-model.trim="tussCreateForm.m2_filme" type="text" class="form-control" placeholder="Ex.: 0" :class="{ 'is-invalid': !!tussCreateForm.errors.m2_filme }" />
                      <div class="invalid-feedback">{{ tussCreateForm.errors.m2_filme }}</div>
                    </BCol>
                    <BCol md="2">
                      <label class="form-label">Auxiliares</label>
                      <input v-model.trim="tussCreateForm.auxiliares" type="text" class="form-control" placeholder="Ex.: 0" :class="{ 'is-invalid': !!tussCreateForm.errors.auxiliares }" />
                      <div class="invalid-feedback">{{ tussCreateForm.errors.auxiliares }}</div>
                    </BCol>
                    <BCol md="2">
                      <label class="form-label">Incidência</label>
                      <input v-model.trim="tussCreateForm.incidencia" type="text" class="form-control" placeholder="Ex.: 0" :class="{ 'is-invalid': !!tussCreateForm.errors.incidencia }" />
                      <div class="invalid-feedback">{{ tussCreateForm.errors.incidencia }}</div>
                    </BCol>
                    <BCol md="2">
                      <label class="form-label">Porte</label>
                      <input v-model.trim="tussCreateForm.porte" type="text" class="form-control" placeholder="Ex.: A" :class="{ 'is-invalid': !!tussCreateForm.errors.porte }" />
                      <div class="invalid-feedback">{{ tussCreateForm.errors.porte }}</div>
                    </BCol>
                    <BCol md="2">
                      <label class="form-label">CH</label>
                      <input v-model.trim="tussCreateForm.ch" type="text" class="form-control" placeholder="Ex.: 100" :class="{ 'is-invalid': !!tussCreateForm.errors.ch }" />
                      <div class="invalid-feedback">{{ tussCreateForm.errors.ch }}</div>
                    </BCol>
                    <BCol md="2">
                      <label class="form-label">CO</label>
                      <input v-model.trim="tussCreateForm.co" type="text" class="form-control" placeholder="Ex.: 1" :class="{ 'is-invalid': !!tussCreateForm.errors.co }" />
                      <div class="invalid-feedback">{{ tussCreateForm.errors.co }}</div>
                    </BCol>
                    <BCol md="3">
                      <label class="form-label">Total</label>
                      <input :value="tussCreateTotalDisplay" type="text" class="form-control" placeholder="Calculado automaticamente: CH + CO" disabled />
                    </BCol>
                    <BCol cols="12">
                      <div class="form-text">Aceita valores com vírgula ou ponto (ex.: 10,5 ou 10.5).</div>
                    </BCol>
                  </BRow>
                </details>
              </Modal>

              <Modal
                v-model="tussImportModalOpen"
                title="Importar arquivo CSV"
                size="lg"
                :name-button="'Importar'"
                :processing="tussImportProcessing"
                :disable-close="tussImportProcessing"
                @save="importTuss"
              >
                <div class="d-flex justify-content-end mb-2">
                  <a href="/tuss/template" class="btn btn-sm btn-soft-primary">
                    <i class="ri-download-2-line me-1"></i>
                    Baixar modelo CSV
                  </a>
                </div>
                <div class="mb-3">
                  <label class="form-label">Tabela suportada <span class="text-danger">*</span></label>
                  <select v-model="tussImportForm.tabela_forcada" required data-choices class="form-select" :class="{ 'is-invalid': !!tussImportForm.errors.tabela_forcada }">
                    <option value="">Selecione</option>
                    <option v-for="t in allowedTabelas" :key="t" :value="t">{{ t }}</option>
                  </select>
                  <div class="invalid-feedback">{{ tussImportForm.errors.tabela_forcada }}</div>
                  <div class="form-text">Obrigatório. Será aplicado em todas as linhas importadas.</div>
                </div>
                <div class="mb-3">
                  <label for="tussCsv" class="form-label">Arquivo CSV</label>
                  <input
                    id="tussCsv"
                    type="file"
                    accept=".csv,text/csv"
                    class="form-control"
                    :class="{ 'is-invalid': !!tussImportForm.errors.file }"
                    @change="onTussFileChange"
                  />
                  <div v-if="tussImportForm.errors.file && tussImportUiStatus !== 'error'" class="invalid-feedback">{{ tussImportForm.errors.file }}</div>
                </div>
                <div v-if="tussImportProgressVisible" class="mb-2">
                  <div v-if="tussImportProcessing" class="small fw-semibold mb-1">
                    {{ tussImportUiMessage || 'Validando arquivo' }}
                    <span class="tuss-validating-dots" aria-hidden="true">
                      <span>.</span><span>.</span><span>.</span>
                    </span>
                  </div>
                  <div class="progress progress-sm">
                    <div
                      class="progress-bar"
                      role="progressbar"
                      :class="tussImportBarClass"
                      :style="{ width: `${tussImportPercent}%` }"
                      :aria-valuenow="tussImportPercent"
                      aria-valuemin="0"
                      aria-valuemax="100"
                    ></div>
                  </div>
                  <div v-if="tussImportProcessing" class="text-muted small mt-1">{{ tussImportPercent }}%</div>
                  <div v-if="tussImportUiStatus === 'error' && tussImportUiMessage" class="invalid-feedback d-block mt-1">{{ tussImportUiMessage }}</div>
                </div>
              </Modal>
            </BCardBody>
          </BCard>
        </BTab>
        <BTab>
          <template #title>
            <i class="ri-file-list-3-line d-block fs-3xl mb-1"></i>Procedimentos
          </template>
          <Procedimento :procedimentos="props.procedimentos" :categoriasProcedimento="props.categoriasProcedimento" />
        </BTab>
        <BTab>
          <template #title>
            <i class="ri-qr-code-line d-block fs-3xl mb-1"></i>PIX
          </template>
          <BCard class="shadow-sm config-card">
            <BCardHeader class="bg-light-subtle p-3 border-0">
              <BCardTitle><i class="ri-qr-code-line text-primary me-2"></i>Configuração de PIX</BCardTitle>
            </BCardHeader>
            <BCardBody>
              <p class="text-muted mb-3">Defina a chave PIX e os dados do recebedor para gerar o QR Code ao receber pagamentos.</p>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Chave PIX</label>
                  <input v-model.trim="pixConfig.chave" type="text" class="form-control" placeholder="e-mail, cpf/cnpj ou chave aleatória" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Descrição</label>
                  <input v-model.trim="pixConfig.descricao" type="text" class="form-control" placeholder="Descrição opcional" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Nome do recebedor</label>
                  <input v-model.trim="pixConfig.recebedor_nome" type="text" class="form-control" placeholder="Nome fantasia" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Cidade do recebedor</label>
                  <input v-model.trim="pixConfig.recebedor_cidade" type="text" class="form-control" placeholder="Cidade" />
                </div>
              </div>
              <div class="mt-3">
                <div class="invalid-feedback d-block" v-if="pixError">{{ pixError }}</div>
                <div class="alert alert-success d-flex align-items-center py-2" role="alert" v-if="pixSuccess">
                  <i class="ri-check-fill text-success me-2"></i>
                  <div>Configurações salvas com sucesso.</div>
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2 mt-2">
                <button class="btn btn-light" type="button" @click="carregarPixConfig" :disabled="pixLoading">
                  <span v-if="pixLoading" class="spinner-border spinner-border-sm me-1"></span>
                  Recarregar
                </button>
                <button class="btn btn-primary" type="button" @click="salvarPixConfig" :disabled="pixSaving">
                  <span v-if="pixSaving" class="spinner-border spinner-border-sm me-1"></span>
                  Salvar
                </button>
              </div>
            </BCardBody>
          </BCard>
        </BTab>
      </BTabs>
    </BRow>
  </Layout>
</template>
<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref, onMounted, watch, computed } from "vue";
import axios from "axios";
import Modal from "@/Components/Modal.vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Parametrizacao from "./Parametrizacao.vue";
import Especialidade from "./Especialidade.vue";
import Procedimento from "./Procedimento.vue";
const props = defineProps({
  estadosCivis: { type: Array, default: () => [] },
  tiposSanguineos: { type: Array, default: () => [] },
  canaisAviso: { type: Array, default: () => [] },
  parentescos: { type: Array, default: () => [] },
  especialidades: { type: Array, default: () => [] },
  procedimentos: { type: Array, default: () => [] },
  categoriasProcedimento: { type: Array, default: () => [] },
});

const pixConfig = ref({ chave: "", recebedor_nome: "", recebedor_cidade: "", descricao: "" });
const pixLoading = ref(false);
const pixSaving = ref(false);
const pixError = ref("");
const pixSuccess = ref(false);

const tussImportForm = useForm({
  file: null,
  tabela_forcada: '',
});
const tussImportModalOpen = ref(false);
const tussCreateModalOpen = ref(false);
const tussImportUiStatus = ref('idle');
const tussImportUiMessage = ref('');
const tussImportLastPercent = ref(0);
const tussImportProcessing = ref(false);
const tussImportPercent = computed(() => {
  return Math.min(Math.max(tussImportLastPercent.value || 0, 0), 100);
});
const tussImportProgressVisible = computed(() => {
  return tussImportProcessing.value || tussImportUiStatus.value === 'error';
});
const tussImportBarClass = computed(() => {
  if (tussImportUiStatus.value === 'error') return 'bg-danger';
  if (tussImportProcessing.value) return 'bg-primary progress-bar-striped progress-bar-animated';
  return 'bg-primary';
});

function formatCurrencyBR(v) {
  const n = Number(v);
  if (!Number.isFinite(n)) return v ?? '';
  return n.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}
function parseDecimalBR(v) {
  const s = String(v ?? '').trim();
  if (!s) return null;
  const cleaned = s.replace(/[^\d,.\-]/g, '');
  if (!cleaned) return null;
  const normalized = cleaned.includes(',') && cleaned.includes('.')
    ? cleaned.replace(/\./g, '').replace(',', '.')
    : cleaned.replace(',', '.');
  const n = Number(normalized);
  return Number.isFinite(n) ? n : null;
}
const tussColumns = [
  { id: "id", name: "ID" },
  { id: "tabela", name: "Tabela" },
  { id: "codigo", name: "Código" },
  { id: "descricao", name: "Descrição" },
  { id: "ch", name: "CH", formatter: (cell) => formatCurrencyBR(cell) },
  { id: "co", name: "CO", formatter: (cell) => formatCurrencyBR(cell) },
  { id: "total", name: "Total", formatter: (cell) => formatCurrencyBR(cell) },
];
const allowedTabelas = ['AMB1990', 'AMB1992', 'AMB1993', 'AMB1999', 'CBHPM3', 'CBHPM4', 'CBHPM5', 'TUSS'];
const tussTabelaFilter = ref('');
function setTussTabelaFilter(v) {
  tussTabelaFilter.value = String(v || '');
}
const tussTableReloadNonce = ref(0);
const tussServerQuery = computed(() => {
  const f = String(tussTabelaFilter.value || '').trim();
  const base = f ? { tabela: f } : {};
  return { ...base, _r: tussTableReloadNonce.value };
});
const tussCreateForm = useForm({
  tabela: '',
  codigo: '',
  descricao: '',
  m2_filme: '',
  auxiliares: '',
  incidencia: '',
  porte: '',
  ch: '',
  co: '',
});
const tussCreateTotal = computed(() => {
  const ch = parseDecimalBR(tussCreateForm.ch);
  const co = parseDecimalBR(tussCreateForm.co);
  if (ch === null || co === null) return null;
  return ch + co;
});
const tussCreateTotalDisplay = computed(() => {
  const n = tussCreateTotal.value;
  if (n === null) return '';
  return n.toLocaleString("pt-BR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
});
function resetTussCreate() {
  tussCreateForm.reset();
  tussCreateForm.clearErrors();
}
function openTussCreateModal() {
  tussCreateModalOpen.value = true;
}
function storeTuss() {
  tussCreateForm.clearErrors();
  if (!String(tussCreateForm.tabela || '').trim()) {
    tussCreateForm.setError('tabela', 'Informe a tabela.');
    return;
  }
  if (!String(tussCreateForm.codigo || '').trim()) {
    tussCreateForm.setError('codigo', 'Informe o código.');
    return;
  }
  tussCreateForm.post('/tuss', {
    preserveScroll: true,
    onSuccess: () => {
      tussCreateForm.reset();
      tussCreateModalOpen.value = false;
    },
  });
}
watch(tussCreateModalOpen, (v, old) => {
  if (!v && old) resetTussCreate();
});

function onTussFileChange(e) {
  const f = e?.target?.files?.[0] || null;
  tussImportForm.file = f;
  tussImportForm.clearErrors();
}
function resetTussImportForm() {
  tussImportForm.reset('file', 'tabela_forcada');
  tussImportForm.clearErrors();
  tussImportUiStatus.value = 'idle';
  tussImportUiMessage.value = '';
  tussImportLastPercent.value = 0;
  tussImportProcessing.value = false;
  const el = document.getElementById('tussCsv');
  if (el) el.value = '';
}
function openTussImportModal() {
  tussImportModalOpen.value = true;
}
function closeTussImportModal() {
  tussImportModalOpen.value = false;
  resetTussImportForm();
}
async function importTuss() {
  if (!String(tussImportForm.tabela_forcada || '').trim()) {
    tussImportForm.setError('tabela_forcada', 'Selecione a tabela suportada.');
    return;
  }
  if (!tussImportForm.file) {
    tussImportForm.setError('file', 'Selecione um arquivo CSV.');
    return;
  }
  tussImportForm.clearErrors();
  tussImportUiStatus.value = 'running';
  tussImportUiMessage.value = '';
  tussImportLastPercent.value = 1;
  tussImportProcessing.value = true;
  try {
    const fd = new FormData();
    fd.append('file', tussImportForm.file);
    fd.append('tabela_forcada', tussImportForm.tabela_forcada);

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const resp = await fetch('/tuss/import/progress', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      headers: csrf
        ? { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/x-ndjson, application/json' }
        : { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/x-ndjson, application/json' },
    });

    if (resp.redirected || /\/login\b/i.test(resp.url || '')) {
      tussImportProcessing.value = false;
      tussImportUiStatus.value = 'error';
      tussImportUiMessage.value = 'Sua sessão expirou. Recarregue a página e tente novamente.';
      return;
    }

    if (!resp.ok) {
      let msg = 'Falha ao iniciar importação.';
      if (resp.status === 419) msg = 'Sua sessão expirou. Recarregue a página e tente novamente.';
      try {
        const data = await resp.json();
        msg = data?.message || data?.errors?.file?.[0] || data?.errors?.tabela_forcada?.[0] || msg;
      } catch (e) {
      }
      tussImportProcessing.value = false;
      tussImportUiStatus.value = 'error';
      tussImportUiMessage.value = String(msg);
      return;
    }

    const ct = resp.headers.get('content-type') || '';
    if (!/application\/x-ndjson/i.test(ct)) {
      let msg = 'Falha ao iniciar importação.';
      try {
        const data = await resp.json();
        msg = data?.message || data?.errors?.file?.[0] || data?.errors?.tabela_forcada?.[0] || msg;
      } catch (e) {
        try {
          const t = await resp.text();
          if (t && t.length < 500) msg = t;
        } catch (e2) {
        }
      }
      tussImportProcessing.value = false;
      tussImportUiStatus.value = 'error';
      tussImportUiMessage.value = String(msg);
      return;
    }

    const reader = resp.body?.getReader();
    if (!reader) {
      tussImportProcessing.value = false;
      tussImportUiStatus.value = 'error';
      tussImportUiMessage.value = 'Não foi possível ler o progresso.';
      return;
    }

    const decoder = new TextDecoder('utf-8');
    let buf = '';
    let resultId = '';
    let finished = false;
    while (true) {
      const { value, done } = await reader.read();
      if (done) break;
      buf += decoder.decode(value, { stream: true });
      const lines = buf.split('\n');
      buf = lines.pop() || '';
      for (const line of lines) {
        const trimmed = line.trim();
        if (!trimmed) continue;
        let data;
        try {
          data = JSON.parse(trimmed);
        } catch (e) {
          continue;
        }
        if (!resultId) resultId = String(data?.id || '');
        const percent = Number(data?.percent);
        const status = String(data?.status || '');
        const message = String(data?.message || '');
        if (Number.isFinite(percent)) tussImportLastPercent.value = Math.min(Math.max(percent, 0), 100);
        if (message) tussImportUiMessage.value = message;
        if (status === 'error') {
          tussImportProcessing.value = false;
          tussImportUiStatus.value = 'error';
          finished = true;
          return;
        }
        if (status === 'success') {
          tussImportProcessing.value = false;
          finished = true;
          closeTussImportModal();
          if (resultId) {
            router.visit(`/tuss/import/complete/${resultId}`, {
              method: 'get',
              preserveScroll: true,
              preserveState: true,
              replace: true,
              onFinish: () => {
                tussTableReloadNonce.value += 1;
              },
            });
          } else {
            tussTableReloadNonce.value += 1;
          }
          return;
        }
      }
    }
    if (!finished) {
      tussImportProcessing.value = false;
      tussImportUiStatus.value = 'error';
      tussImportUiMessage.value = 'Falha ao iniciar importação.';
      return;
    }
  } catch (e) {
    tussImportProcessing.value = false;
    tussImportUiStatus.value = 'error';
    tussImportUiMessage.value = 'Falha ao iniciar importação.';
  }
}
watch(tussImportModalOpen, (v, old) => {
  if (!v && old) resetTussImportForm();
});

async function carregarPixConfig() {
  pixLoading.value = true;
  pixError.value = "";
  pixSuccess.value = false;
  try {
    const resp = await axios.get('/config/pix');
    pixConfig.value = {
      chave: resp.data?.chave || "",
      recebedor_nome: resp.data?.recebedor_nome || "",
      recebedor_cidade: resp.data?.recebedor_cidade || "",
      descricao: resp.data?.descricao || "",
    };
  } catch (e) {
    pixError.value = "Não foi possível carregar a configuração.";
  } finally {
    pixLoading.value = false;
  }
}
async function salvarPixConfig() {
  pixError.value = "";
  pixSuccess.value = false;
  if (!String(pixConfig.value.chave || "").trim()) {
    pixError.value = "Informe a chave PIX.";
    return;
  }
  pixSaving.value = true;
  try {
    await axios.put('/config/pix', pixConfig.value);
    pixSuccess.value = true;
  } catch (e) {
    pixError.value = "Falha ao salvar configuração.";
  } finally {
    pixSaving.value = false;
  }
}
onMounted(() => { carregarPixConfig(); });
</script>
<style scoped>
.config-nav .nav-link {
  border-radius: 12px;
  padding: 14px 10px;
  color: var(--vz-body-color);
}
.config-nav .nav-link.active {
  background: var(--vz-primary-bg-subtle);
  color: var(--vz-primary);
}
:deep(.config-card) {
  border: 1px dashed var(--vz-border-color);
  background: var(--vz-light-bg-subtle);
}
:deep(.config-nav-wrapper) {
  background: var(--vz-secondary-bg);
  border: 1px solid var(--vz-border-color);
  border-radius: 12px 0 0 12px;
  padding: 12px;
}
:global([data-bs-theme="light"] .config-nav-wrapper) {
  border-right: 0 !important;
}
:global([data-bs-theme="dark"] .config-nav-wrapper) {
  background: var(--vz-secondary-bg) !important;
  border-color: var(--vz-secondary-bg) !important;
}
:global([data-bs-theme="dark"] .config-card) {
  background: var(--vz-secondary-bg) !important;
  border-color: var(--vz-secondary-bg) !important;
}
:global([data-bs-theme="light"] .config-card) {
  background: #fff !important;
  border-color: #fff !important;
  border-left: 0 !important;
}
:deep(.tuss-filter-menu .dropdown-item:not(.active)) {
  color: var(--vz-body-color) !important;
}
:deep(.tuss-filter-menu .dropdown-item.active),
:deep(.tuss-filter-menu .dropdown-item:active) {
  color: var(--vz-body-color) !important;
  background-color: var(--vz-primary-bg-subtle) !important;
}
:deep(.tuss-filter-toggle) {
  color: var(--vz-body-color) !important;
}
.tuss-validating-dots span {
  display: inline-block;
  opacity: 0.25;
  animation: tussDotPulse 1.1s infinite;
}
.tuss-validating-dots span:nth-child(2) {
  animation-delay: 0.2s;
}
.tuss-validating-dots span:nth-child(3) {
  animation-delay: 0.4s;
}
@keyframes tussDotPulse {
  0% { opacity: 0.25; transform: translateY(0); }
  50% { opacity: 1; transform: translateY(-1px); }
  100% { opacity: 0.25; transform: translateY(0); }
}
</style>
