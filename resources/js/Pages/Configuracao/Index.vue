<template>
  <Layout>
    <Head title="Configuração PIX" />
    <PageHeader title="Configuração PIX" pageTitle="Configurações" />
    
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
  </Layout>
</template>

<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import axios from "axios";

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
:deep(.config-card) {
  border: 1px dashed var(--vz-border-color);
  background: var(--vz-light-bg-subtle);
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
