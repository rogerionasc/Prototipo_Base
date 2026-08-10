<template>
  <Layout>
    <Head title="Parametrização TISS" />
    <PageHeader title="Parametrização TISS" pageTitle="Configurações" />
    <Tiss :conselhos="props.conselhos" :caraterAtendimentos="props.caraterAtendimentos" :tabelasReferencia="props.tabelasReferencia" />
  </Layout>
</template>

<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head } from "@inertiajs/vue3";
import Tiss from "./Tiss.vue";

const props = defineProps({
  conselhos: { type: Array, default: () => [] },
  caraterAtendimentos: { type: Array, default: () => [] },
  tabelasReferencia: { type: Array, default: () => [] },
});
</script>
