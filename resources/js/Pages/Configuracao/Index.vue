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
      </BTabs>
    </BRow>
  </Layout>
</template>
<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head, Link } from "@inertiajs/vue3";
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
