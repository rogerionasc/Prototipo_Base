<template>
  <Layout>
    <Head title="Parametrização TISS" />
    <PageHeader title="Parametrização TISS" pageTitle="Configurações" />
    <Tiss
      :conselhos="props.conselhos"
      :caraterAtendimentos="props.caraterAtendimentos"
      :tabelasReferencia="props.tabelasReferencia"
      :tipoAtendimentos="props.tipoAtendimentos"
      :indicacaoIncidencias="props.indicacaoIncidencias"
      :tipoConsultas="props.tipoConsultas"
      :motivosEncerramento="props.motivosEncerramento"
      :viasAcesso="props.viasAcesso"
      :tecnicasUtilizadas="props.tecnicasUtilizadas"
      :grausParticipacao="props.grausParticipacao"
    />
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
  tipoAtendimentos: { type: Array, default: () => [] },
  indicacaoIncidencias: { type: Array, default: () => [] },
  tipoConsultas: { type: Array, default: () => [] },
  motivosEncerramento: { type: Array, default: () => [] },
  viasAcesso: { type: Array, default: () => [] },
  tecnicasUtilizadas: { type: Array, default: () => [] },
  grausParticipacao: { type: Array, default: () => [] },
});
</script>
