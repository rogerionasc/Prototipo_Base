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
            <BCardBody>
              <div class="d-flex align-items-start">
                <div class="flex-grow-1">
                  <h5 class="mb-2">Tabela TUSS</h5>
                  <p class="text-muted mb-3">Gerencie os procedimentos padronizados pela tabela TUSS.</p>
                </div>
                <i class="ri-layout-grid-line text-primary fs-20 ms-3"></i>
              </div>
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
          <BCard class="shadow-sm config-card">
            <BCardBody>
              <div class="d-flex align-items-start">
                <div class="flex-grow-1">
                  <h5 class="mb-2">Procedimentos</h5>
                  <p class="text-muted mb-3">Gerencie os procedimentos da tabela propria.</p>
                </div>
                <i class="ri-file-list-3-line text-primary fs-20 ms-3"></i>
              </div>
              <div class="d-flex justify-content-end">
                <Link href="/componentes" class="btn btn-soft-primary">Abrir Componentes</Link>
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
import Parametrizacao from "./Parametrizacao.vue";
import Especialidade from "./Especialidade.vue";
const props = defineProps({
  estadosCivis: { type: Array, default: () => [] },
  tiposSanguineos: { type: Array, default: () => [] },
  canaisAviso: { type: Array, default: () => [] },
  parentescos: { type: Array, default: () => [] },
  especialidades: { type: Array, default: () => [] },
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
  background: #fff;
  border: 1px solid var(--vz-border-color);
  border-radius: 12px;
  padding: 12px;
}
</style>
