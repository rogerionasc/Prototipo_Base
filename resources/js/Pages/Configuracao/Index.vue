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
          <Especialidade :especialidades="props.especialidades" />
        </BTab>
        <BTab>
          <template #title>
            <i class="ri-layout-grid-line d-block fs-3xl mb-1"></i>Tabela TUSS
          </template>
          <BCard class="shadow-sm config-card">
            <BCardHeader class="bg-light-subtle p-3 border-0">
              <BCardTitle><i class="ri-layout-grid-line text-primary me-2"></i>Tabela TUSS</BCardTitle>
            </BCardHeader>
            <BCardBody>
              <p class="text-muted mb-3">Gerencie os procedimentos padronizados pela tabela TUSS.</p>
              <div class="d-flex justify-content-end">
                <Link href="/componentes" class="btn btn-soft-primary">Abrir Componentes</Link>
              </div>
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
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import axios from "axios";
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
</style>
