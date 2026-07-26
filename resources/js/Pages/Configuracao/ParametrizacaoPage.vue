<template>
  <Layout>
    <Head title="Parametrização" />
    <PageHeader title="Parametrização" pageTitle="Configurações" />
    <Parametrizacao
      :estadosCivis="props.estadosCivis"
      :tiposSanguineos="props.tiposSanguineos"
      :canaisAviso="props.canaisAviso"
      :parentescos="props.parentescos"
      :categoriasProcedimento="props.categoriasProcedimento"
    />
  </Layout>
</template>
<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head } from "@inertiajs/vue3";
import Parametrizacao from "./Parametrizacao.vue";
const props = defineProps({
  estadosCivis: { type: Array, default: () => [] },
  tiposSanguineos: { type: Array, default: () => [] },
  canaisAviso: { type: Array, default: () => [] },
  parentescos: { type: Array, default: () => [] },
  categoriasProcedimento: { type: Array, default: () => [] },
});
</script>
