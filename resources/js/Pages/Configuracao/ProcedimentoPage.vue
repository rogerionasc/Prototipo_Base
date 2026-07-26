<template>
  <Layout>
    <Head title="Procedimentos" />
    <PageHeader title="Procedimentos" pageTitle="Configurações" />
    <Procedimento :procedimentos="props.procedimentos" :categoriasProcedimento="props.categoriasProcedimento" />
  </Layout>
</template>
<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head } from "@inertiajs/vue3";
import Procedimento from "./Procedimento.vue";
const props = defineProps({
  procedimentos: { type: Array, default: () => [] },
  categoriasProcedimento: { type: Array, default: () => [] },
});
</script>
