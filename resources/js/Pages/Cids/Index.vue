<template>
  <Layout>

    <Head title="Tabela CID" />
    <PageHeader title="CID" pageTitle="Configurações" />
    <BContainer fluid>
      <BCard class="shadow-sm border-0">
        <BCardHeader class="align-items-center d-flex border-bottom-dashed">
          <BCardTitle class="mb-0 flex-grow-1">Tabela CID</BCardTitle>
          <a href="#"
            class="link-primary fw-semibold text-nowrap link-offset-2 link-underline-opacity-0 link-underline-opacity-100-hover"
            :class="{ 'pe-none opacity-50': cidImportProcessing }"
            @click.prevent="cidImportProcessing ? null : openCidImportModal()">
            <i class="ri-upload-2-line me-1"></i>Importar CSV
          </a>
        </BCardHeader>
        <BCardBody>
          <p class="text-muted mb-3">Gerencie a tabela de CIDs (Classificação Internacional de Doenças).</p>

          <Modal v-model="cidImportModalOpen" title="Importar arquivo CSV de CIDs" size="lg" :name-button="'Importar'"
            :processing="cidImportProcessing" :disable-close="cidImportProcessing" @save="importCid">
            <div class="d-flex align-items-start justify-content-between gap-2 pb-2 mb-3 border-bottom">
              <div class="text-muted small d-flex align-items-center gap-2"><i class="ri-information-line"></i><span>Os
                  campos <span class="text-danger">*</span> são obrigatórios.</span></div>
              <a href="/cids/template" class="btn btn-sm btn-soft-primary text-nowrap"><i
                  class="ri-download-2-line me-1"></i>Baixar modelo CSV</a>
            </div>
            <BRow class="g-3">
              <BCol md="12">
                <label for="cidCsvPage" class="form-label d-flex align-items-center gap-2"><i
                    class="ri-file-upload-line text-primary"></i><span>Arquivo CSV <span
                      class="text-danger">*</span></span></label>
                <input id="cidCsvPage" type="file" accept=".csv,text/csv" class="form-control"
                  :class="{ 'is-invalid': !!cidImportForm.errors.file }" :disabled="cidImportProcessing"
                  @change="onCidFileChange" />
                <div class="form-text">Aceita .csv (separador ; ou ,). Colunas necessárias: codigo, descricao.</div>
                <div v-if="cidImportForm.file" class="small text-muted mt-1">Selecionado: <span class="fw-semibold">{{
                  cidImportForm.file.name }}</span></div>
              </BCol>
            </BRow>
            <div v-if="cidImportProgressVisible" class="mt-3 p-3 border rounded"
              :class="cidImportUiStatus === 'error' ? 'bg-danger-subtle border-danger' : 'bg-light-subtle'">
              <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                <div class="small fw-semibold d-flex align-items-center gap-2">
                  <i v-if="cidImportUiStatus === 'error'" class="ri-close-circle-line text-danger"></i>
                  <i v-else class="ri-loader-4-line text-primary"></i>
                  <span>{{ cidImportUiMessage || 'Validando arquivo' }}</span>
                </div>
                <div class="text-muted small text-nowrap">{{ cidImportPercent }}%</div>
              </div>
              <div class="progress progress-sm">
                <div class="progress-bar" role="progressbar" :class="cidImportBarClass"
                  :style="{ width: `${cidImportPercent}%` }"></div>
              </div>
            </div>
          </Modal>

          <div class="cid-table-wrapper">
            <TableGrid :key="cidTableKey" :columns="cidColumns" :data="[]" :serverUrl="'/cids/list'"
              :tableTitle="'Registros CID'" :showTitle="false" :showStatus="false"
              :searchPlaceholder="'Buscar por código ou descrição'" :showCheckbox="false" :showAddButton="false"
              :showActions="false" :compactSpacing="true" />
          </div>
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
const cidImportModalOpen = ref(false);
const cidImportUiStatus = ref('idle');
const cidImportUiMessage = ref('');
const cidImportLastPercent = ref(0);
const cidImportProcessing = ref(false);
const cidTableKey = ref(0);
const cidImportForm = useForm({ file: null });

const cidImportPercent = computed(() => Math.min(Math.max(cidImportLastPercent.value || 0, 0), 100));
const cidImportProgressVisible = computed(() => cidImportProcessing.value || cidImportUiStatus.value === 'error');
const cidImportBarClass = computed(() => {
  if (cidImportUiStatus.value === 'error') return 'bg-danger';
  if (cidImportPercent.value >= 100) return 'bg-success';
  return 'bg-primary progress-bar-striped progress-bar-animated';
});

const cidColumns = [
  { id: 'codigo', name: 'Código', sortable: true },
  { id: 'descricao', name: 'Descrição', sortable: true },
];

function openCidImportModal() {
  cidImportForm.reset();
  cidImportUiStatus.value = 'idle';
  cidImportUiMessage.value = '';
  cidImportLastPercent.value = 0;
  cidImportModalOpen.value = true;
}

function onCidFileChange(e) { cidImportForm.file = e.target.files[0] || null; }

async function importCid() {
  if (!cidImportForm.file) { cidImportForm.setError('file', 'Selecione um arquivo CSV.'); return; }
  cidImportProcessing.value = true;
  cidImportUiStatus.value = 'running';
  cidImportUiMessage.value = '';
  cidImportLastPercent.value = 0;
  const fd = new FormData();
  fd.append('file', cidImportForm.file);
  fd.append('_token', document.querySelector('meta[name="csrf-token"]')?.content ?? '');
  try {
    const resp = await fetch('/cids/import/progress', { 
      method: 'POST', 
      body: fd,
      headers: { 'Accept': 'application/json' }
    });
    if (!resp.ok) {
      if (resp.status === 422) {
        const data = await resp.json();
        throw new Error(data.errors?.file?.[0] || data.message || 'Erro de validação');
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
          if (ev.percent !== undefined) cidImportLastPercent.value = ev.percent;
          if (ev.message) cidImportUiMessage.value = ev.message;
          if (ev.status === 'error') { cidImportUiStatus.value = 'error'; }
          else if (ev.percent >= 100) { cidImportUiStatus.value = 'success'; }
        } catch (_) { }
      }
    }
  } catch (e) {
    cidImportUiStatus.value = 'error';
    cidImportUiMessage.value = e.message || 'Erro de conexão ao importar.';
    page.props.flash = { ...page.props.flash, error: cidImportUiMessage.value + ' ' + Date.now() };
  }
  cidImportProcessing.value = false;
  if (cidImportUiStatus.value === 'success') {
    page.props.flash = { ...page.props.flash, success: (cidImportUiMessage.value || 'Importação concluída.') + ' ' + Date.now() };
    cidTableKey.value++;
    setTimeout(() => { cidImportModalOpen.value = false; cidImportForm.reset(); }, 2000);
  } else if (cidImportUiStatus.value === 'error' && !page.props.flash?.error?.includes(Date.now().toString().substring(0,8))) {
     page.props.flash = { ...page.props.flash, error: cidImportUiMessage.value + ' ' + Date.now() };
  }
}
</script>

<style scoped>
.cid-table-wrapper :deep(table) {
  table-layout: fixed !important;
  width: 100% !important;
}

.cid-table-wrapper :deep(th:first-child),
.cid-table-wrapper :deep(td:first-child) {
  width: 110px !important;
  min-width: 110px !important;
  max-width: 110px !important;
  white-space: nowrap !important;
}
</style>
