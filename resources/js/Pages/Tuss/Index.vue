<template>
  <Layout>

    <Head title="Tabela TUSS" />
    <PageHeader title="TUSS" pageTitle="Configurações" />
    <BContainer fluid>
      <BCard class="shadow-sm border-0">
        <BCardHeader class="align-items-center d-flex border-bottom-dashed">
          <BCardTitle class="mb-0 flex-grow-1">Tabela TUSS</BCardTitle>
          <div class="d-flex align-items-center gap-2">
            <a href="#"
              class="link-primary fw-semibold text-nowrap link-offset-2 link-underline-opacity-0 link-underline-opacity-100-hover"
              title="Importar registros via arquivo CSV"
              :class="{ 'pe-none opacity-50': tussImportProcessing || tussCreateForm.processing }"
              :aria-disabled="(tussImportProcessing || tussCreateForm.processing) ? 'true' : 'false'"
              @click.prevent="(tussImportProcessing || tussCreateForm.processing) ? null : openTussImportModal()">
              <i class="ri-upload-2-line me-1"></i>
              Importar CSV
            </a>
          </div>
        </BCardHeader>
        <BCardBody>
          <p class="text-muted mb-3">Gerencie os procedimentos padronizados pela tabela TUSS.</p>
          <div class="d-flex justify-content-end align-items-center gap-2 mb-2">
            <BDropdown class="position-static" menu-class="shadow-lg dropdown-menu-end tuss-filter-menu"
              toggle-class="btn btn-sm btn-soft-primary" variant="soft-primary" size="sm">
              <template #button-content>
                <i class="ri-filter-3-line me-1"></i>
                Tabela: <span class="fw-semibold">{{ tussTabelaFilter || 'Todas' }}</span>
              </template>
              <BDropdownItem :active="tussTabelaFilter === ''" @click="setTussTabelaFilter('')">Todas</BDropdownItem>
              <BDropdownItem v-for="t in allowedTabelas" :key="t" :active="tussTabelaFilter === t"
                @click="setTussTabelaFilter(t)">
                {{ t }}
              </BDropdownItem>
            </BDropdown>
          </div>

          <TableGrid :key="tussTableKey" :columns="tussColumns" :data="[]" :serverUrl="'/tuss/list'"
            :serverQuery="tussServerQuery" :tableTitle="'Registros TUSS'" :showTitle="false" :showStatus="false"
            :searchPlaceholder="'Buscar por código, descrição ou tabela'" :showCheckbox="false" :showAddButton="true"
            :showActions="true"
            :actionsConfig="{ delete: false, edit: true, show: false, diary: false, print: false, download: false, restore: false, receive: false }"
            :compactSpacing="true" @add="openTussCreateModal" @edit="openTussEditModal" />

          <Modal v-model="tussCreateModalOpen" title="Adicionar registro" size="xl" :name-button="'Salvar'"
            :processing="tussCreateForm.processing" :disable-close="tussCreateForm.processing" @save="storeTuss">
            <div class="text-muted small mb-3">Os campos <span class="text-danger">*</span> são obrigatórios</div>
            <BRow class="g-3">
              <BCol md="3">
                <label class="form-label">Tabela <span class="text-danger">*</span></label>
                <select v-model.trim="tussCreateForm.tabela" data-choices class="form-select"
                  :class="{ 'is-invalid': !!tussCreateForm.errors.tabela }">
                  <option value="">Selecione</option>
                  <option v-for="t in allowedTabelas" :key="t" :value="t">{{ t }}</option>
                </select>
                <div class="invalid-feedback">{{ tussCreateForm.errors.tabela }}</div>
              </BCol>
              <BCol md="3">
                <label class="form-label">Código <span class="text-danger">*</span></label>
                <input v-model.trim="tussCreateForm.codigo" type="text" class="form-control" placeholder="Ex.: 000000"
                  :class="{ 'is-invalid': !!tussCreateForm.errors.codigo }" />
                <div class="invalid-feedback">{{ tussCreateForm.errors.codigo }}</div>
              </BCol>
              <BCol md="6">
                <label class="form-label">Descrição</label>
                <input v-model.trim="tussCreateForm.descricao" type="text" class="form-control"
                  placeholder="Ex.: Procedimento Exemplo"
                  :class="{ 'is-invalid': !!tussCreateForm.errors.descricao }" />
                <div class="invalid-feedback">{{ tussCreateForm.errors.descricao }}</div>
              </BCol>
            </BRow>
            <details class="mt-3">
              <summary class="small text-primary">Campos avançados</summary>
              <BRow class="g-3 mt-1">
                <BCol md="3">
                  <label class="form-label">É tratamento?</label>
                  <select v-model="tussCreateForm.eh_tratamento" class="form-select">
                    <option :value="false">Não</option>
                    <option :value="true">Sim</option>
                  </select>
                </BCol>
                <BCol md="3" v-if="isTussCreateTratamento">
                  <label class="form-label">Qtd. Sessões</label>
                  <input v-model.number="tussCreateForm.quantidade_sessoes" type="number" min="1"
                    class="form-control" />
                </BCol>
                <BCol md="2"><label class="form-label">Qtd. CH</label><input v-model.trim="tussCreateForm.quantidade_ch" type="text"
                    class="form-control" placeholder="Ex.: 100" /></BCol>
                <BCol md="2"><label class="form-label">Qtd. CO</label><input v-model.trim="tussCreateForm.quantidade_co" type="text"
                    class="form-control" placeholder="Ex.: 1" /></BCol>
                <BCol md="3"><label class="form-label">Total</label><input :value="tussCreateTotalDisplay" type="text"
                    class="form-control" disabled /></BCol>
              </BRow>
            </details>
          </Modal>

          <Modal v-model="tussEditModalOpen" title="Editar registro" size="xl" :name-button="'Salvar alterações'"
            :processing="tussEditForm.processing" :disable-close="tussEditForm.processing" @save="updateTuss">
            <div class="text-muted small mb-3">Tabela e código não podem ser alterados.</div>
            <BRow class="g-3">
              <BCol md="3"><label class="form-label">Tabela</label><input v-model.trim="tussEditForm.tabela" type="text"
                  class="form-control" disabled /></BCol>
              <BCol md="3"><label class="form-label">Código</label><input v-model.trim="tussEditForm.codigo" type="text"
                  class="form-control" disabled /></BCol>
              <BCol md="6">
                <label class="form-label">Descrição</label>
                <input v-model.trim="tussEditForm.descricao" type="text" class="form-control"
                  :class="{ 'is-invalid': !!tussEditForm.errors.descricao }" />
                <div class="invalid-feedback">{{ tussEditForm.errors.descricao }}</div>
              </BCol>
            </BRow>
            <details class="mt-3">
              <summary class="small text-primary">Campos avançados</summary>
              <BRow class="g-3 mt-1">
                <BCol md="3">
                  <label class="form-label">É tratamento?</label>
                  <select v-model="tussEditForm.eh_tratamento" class="form-select">
                    <option :value="false">Não</option>
                    <option :value="true">Sim</option>
                  </select>
                </BCol>
                <BCol md="3" v-if="isTussEditTratamento">
                  <label class="form-label">Qtd. Sessões</label>
                  <input v-model.number="tussEditForm.quantidade_sessoes" type="number" min="1" class="form-control" />
                </BCol>
                <BCol md="2"><label class="form-label">Qtd. CH</label><input v-model.trim="tussEditForm.quantidade_ch" type="text"
                    class="form-control" /></BCol>
                <BCol md="2"><label class="form-label">Qtd. CO</label><input v-model.trim="tussEditForm.quantidade_co" type="text"
                    class="form-control" /></BCol>
                <BCol md="3"><label class="form-label">Total</label><input :value="tussEditTotalDisplay" type="text"
                    class="form-control" disabled /></BCol>
              </BRow>
            </details>
          </Modal>

          <Modal v-model="tussImportModalOpen" title="Importar arquivo CSV" size="lg" :name-button="'Importar'"
            :processing="tussImportProcessing" :disable-close="tussImportProcessing" @save="importTuss">
            <div class="d-flex align-items-start justify-content-between gap-2 pb-2 mb-3 border-bottom">
              <div class="text-muted small d-flex align-items-center gap-2">
                <i class="ri-information-line"></i>
                <span>Os campos <span class="text-danger">*</span> são obrigatórios.</span>
              </div>
              <a href="/tuss/template" class="btn btn-sm btn-soft-primary text-nowrap"><i
                  class="ri-download-2-line me-1"></i>Baixar modelo CSV</a>
            </div>
            <BRow class="g-3">
              <BCol md="6">
                <label class="form-label d-flex align-items-center gap-2"><i
                    class="ri-layout-grid-line text-primary"></i><span>Tabela <span
                      class="text-danger">*</span></span></label>
                <select v-model="tussImportForm.tabela_forcada" data-choices class="form-select"
                  :class="{ 'is-invalid': !!tussImportForm.errors.tabela_forcada }" :disabled="tussImportProcessing">
                  <option value="">Selecione</option>
                  <option v-for="t in allowedTabelas" :key="t" :value="t">{{ t }}</option>
                </select>
                <div class="invalid-feedback">{{ tussImportForm.errors.tabela_forcada }}</div>
              </BCol>
              <BCol md="6">
                <label for="tussCsvPage" class="form-label d-flex align-items-center gap-2"><i
                    class="ri-file-upload-line text-primary"></i><span>Arquivo CSV <span
                      class="text-danger">*</span></span></label>
                <input id="tussCsvPage" type="file" accept=".csv,text/csv" class="form-control"
                  :class="{ 'is-invalid': !!tussImportForm.errors.file }" :disabled="tussImportProcessing"
                  @change="onTussFileChange" />
                <div v-if="tussImportForm.file" class="small text-muted mt-1">Selecionado: <span class="fw-semibold">{{
                    tussImportForm.file.name }}</span></div>
              </BCol>
            </BRow>
            <div v-if="tussImportProgressVisible" class="mt-3 p-3 border rounded"
              :class="tussImportUiStatus === 'error' ? 'bg-danger-subtle border-danger' : 'bg-light-subtle'">
              <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                <div class="small fw-semibold d-flex align-items-center gap-2">
                  <i v-if="tussImportUiStatus === 'error'" class="ri-close-circle-line text-danger"></i>
                  <i v-else class="ri-loader-4-line text-primary"></i>
                  <span>{{ tussImportUiMessage || 'Validando arquivo' }}</span>
                </div>
                <div class="text-muted small text-nowrap">{{ tussImportPercent }}%</div>
              </div>
              <div class="progress progress-sm">
                <div class="progress-bar" role="progressbar" :class="tussImportBarClass"
                  :style="{ width: `${tussImportPercent}%` }"></div>
              </div>
            </div>
          </Modal>
        </BCardBody>
      </BCard>
    </BContainer>
  </Layout>
</template>

<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Modal from "@/Components/Modal.vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";

const page = usePage();
const tussTableKey = ref(0);
const tussImportModalOpen = ref(false);
const tussCreateModalOpen = ref(false);
const tussEditModalOpen = ref(false);
const tussImportUiStatus = ref('idle');
const tussImportUiMessage = ref('');
const tussImportLastPercent = ref(0);
const tussImportProcessing = ref(false);

const allowedTabelas = ['AMB1990', 'AMB1992', 'AMB1993', 'AMB1999', 'CBHPM3', 'CBHPM4', 'CBHPM5', 'TUSS'];
const tussTabelaFilter = ref('');

function setTussTabelaFilter(v) {
  tussTabelaFilter.value = v;
  tussTableKey.value++;
}

const tussServerQuery = computed(() => tussTabelaFilter.value ? { tabela: tussTabelaFilter.value } : {});

const tussImportPercent = computed(() => Math.min(Math.max(tussImportLastPercent.value || 0, 0), 100));
const tussImportProgressVisible = computed(() => tussImportProcessing.value || tussImportUiStatus.value === 'error');
const tussImportBarClass = computed(() => {
  if (tussImportUiStatus.value === 'error') return 'bg-danger';
  if (tussImportPercent.value >= 100) return 'bg-success';
  return 'bg-primary progress-bar-striped progress-bar-animated';
});

const tussColumns = [
  { id: "id", name: "ID" },
  { id: "tabela", name: "Tabela" },
  { id: "codigo", name: "Código" },
  { id: "descricao", name: "Descrição" },
  { id: "eh_tratamento", name: "Tratamento?", formatter: (cell) => (cell === true || cell === 1 || cell === '1' ? 'Sim' : 'Não') },
  { id: "quantidade_sessoes", name: "Qtd. Sessões" },
];

const tussCreateForm = useForm({ tabela: '', codigo: '', descricao: '', eh_tratamento: false, quantidade_sessoes: null, quantidade_ch: '', quantidade_co: '', m2_filme: '', auxiliares: '', incidencia: '', porte: '' });
const tussEditForm = useForm({ id: null, tabela: '', codigo: '', descricao: '', eh_tratamento: false, quantidade_sessoes: null, quantidade_ch: '', quantidade_co: '', m2_filme: '', auxiliares: '', incidencia: '', porte: '' });
const tussImportForm = useForm({ file: null, tabela_forcada: '' });

const isTussCreateTratamento = computed(() => tussCreateForm.eh_tratamento === true);
const isTussEditTratamento = computed(() => tussEditForm.eh_tratamento === true);

function parseNum(v) {
  const cleaned = String(v ?? '').trim().replace(/[^\d,.-]/g, '');
  if (!cleaned) return null;
  const normalized = cleaned.includes(',') && cleaned.includes('.') ? cleaned.replace(/\./g, '').replace(',', '.') : cleaned.replace(',', '.');
  const n = Number(normalized);
  return Number.isFinite(n) ? n : null;
}

const tussCreateTotalDisplay = computed(() => {
  const ch = parseNum(tussCreateForm.quantidade_ch); const co = parseNum(tussCreateForm.quantidade_co);
  if (ch === null && co === null) return '';
  return ((ch ?? 0) + (co ?? 0)).toFixed(2).replace('.', ',');
});
const tussEditTotalDisplay = computed(() => {
  const ch = parseNum(tussEditForm.quantidade_ch); const co = parseNum(tussEditForm.quantidade_co);
  if (ch === null && co === null) return '';
  return ((ch ?? 0) + (co ?? 0)).toFixed(2).replace('.', ',');
});

function openTussCreateModal() { tussCreateForm.reset(); tussCreateModalOpen.value = true; }
function openTussImportModal() { tussImportForm.reset(); tussImportUiStatus.value = 'idle'; tussImportUiMessage.value = ''; tussImportLastPercent.value = 0; tussImportModalOpen.value = true; }
function openTussEditModal(id, row) {
  tussEditForm.id = row?.id ?? id;
  tussEditForm.tabela = row?.tabela ?? '';
  tussEditForm.codigo = row?.codigo ?? '';
  tussEditForm.descricao = row?.descricao ?? '';
  tussEditForm.eh_tratamento = row?.eh_tratamento == true || row?.eh_tratamento == 1;
  tussEditForm.quantidade_sessoes = row?.quantidade_sessoes ?? null;
  tussEditForm.quantidade_ch = row?.quantidade_ch != null ? String(row.quantidade_ch).replace('.', ',') : '';
  tussEditForm.quantidade_co = row?.quantidade_co != null ? String(row.quantidade_co).replace('.', ',') : '';
  tussEditForm.m2_filme = row?.m2_filme != null ? String(row.m2_filme) : '';
  tussEditForm.auxiliares = row?.auxiliares != null ? String(row.auxiliares) : '';
  tussEditForm.incidencia = row?.incidencia != null ? String(row.incidencia) : '';
  tussEditForm.porte = row?.porte ?? '';
  tussEditModalOpen.value = true;
}

function storeTuss() {
  tussCreateForm.post('/tuss', {
    onSuccess: () => { tussCreateModalOpen.value = false; tussCreateForm.reset(); tussTableKey.value++; }
  });
}
function updateTuss() {
  tussEditForm.put(`/tuss/${tussEditForm.id}`, {
    onSuccess: () => { tussEditModalOpen.value = false; tussTableKey.value++; }
  });
}

function onTussFileChange(e) { tussImportForm.file = e.target.files[0] || null; }

async function importTuss() {
  if (!tussImportForm.file) { tussImportForm.setError('file', 'Selecione um arquivo CSV.'); return; }
  if (!tussImportForm.tabela_forcada) { tussImportForm.setError('tabela_forcada', 'Selecione a tabela.'); return; }
  tussImportProcessing.value = true;
  tussImportUiStatus.value = 'running';
  tussImportUiMessage.value = '';
  tussImportLastPercent.value = 0;
  const fd = new FormData();
  fd.append('file', tussImportForm.file);
  fd.append('tabela_forcada', tussImportForm.tabela_forcada);
  fd.append('_token', document.querySelector('meta[name="csrf-token"]')?.content ?? '');
  try {
    const resp = await fetch('/tuss/import/progress', { 
      method: 'POST', 
      body: fd,
      headers: { 'Accept': 'application/json' }
    });
    if (!resp.ok) {
      if (resp.status === 422) {
        const data = await resp.json();
        throw new Error(data.errors?.file?.[0] || data.errors?.tabela_forcada?.[0] || data.message || 'Erro de validação');
      }
      throw new Error(`Erro HTTP: ${resp.status}`);
    }
    const reader = resp.body.getReader();
    const decoder = new TextDecoder();
    let buf = '';
    while (true) {
      const { value, done } = await reader.read();
      if (done) break;
      buf += decoder.decode(value, { stream: true });
      const lines = buf.split('\n');
      buf = lines.pop();
      for (const line of lines) {
        const t = line.trim();
        if (!t) continue;
        try {
          const ev = JSON.parse(t);
          if (ev.percent !== undefined) tussImportLastPercent.value = ev.percent;
          if (ev.message) tussImportUiMessage.value = ev.message;
          if (ev.status === 'error') { tussImportUiStatus.value = 'error'; }
          else if (ev.percent >= 100) { tussImportUiStatus.value = 'success'; }
        } catch (_) { }
      }
    }
  } catch (e) {
    tussImportUiStatus.value = 'error';
    tussImportUiMessage.value = e.message || 'Erro de conexão ao importar.';
    page.props.flash = { ...page.props.flash, error: tussImportUiMessage.value + ' ' + Date.now() };
  }
  tussImportProcessing.value = false;
  if (tussImportUiStatus.value === 'success') {
    page.props.flash = { ...page.props.flash, success: (tussImportUiMessage.value || 'Importação concluída.') + ' ' + Date.now() };
    tussTableKey.value++;
    setTimeout(() => { tussImportModalOpen.value = false; tussImportForm.reset(); }, 2000);
  } else if (tussImportUiStatus.value === 'error' && !page.props.flash?.error?.includes(Date.now().toString().substring(0,8))) {
     page.props.flash = { ...page.props.flash, error: tussImportUiMessage.value + ' ' + Date.now() };
  }
}
</script>
