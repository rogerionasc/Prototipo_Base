<template>
  <Layout>
    <Head title="Especialidades" />
    <PageHeader title="Especialidades" pageTitle="Configurações" />
    <Especialidade :especialidades="props.especialidades" :procedimentos="props.procedimentos" :tuss="props.tuss" />
  </Layout>
</template>
<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head } from "@inertiajs/vue3";
import Especialidade from "./Especialidade.vue";
const props = defineProps({
  especialidades: { type: Array, default: () => [] },
  procedimentos: { type: Array, default: () => [] },
  tuss: { type: Array, default: () => [] },
});
</script>
