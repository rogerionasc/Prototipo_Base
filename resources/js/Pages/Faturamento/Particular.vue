<template>
  <Layout>
    <Head title="Faturamento Particular" />
    <PageHeader title="Faturamento Particular" pageTitle="Faturamento" />
    <TableGrid
      :columns="cols"
      :data="rows"
      :tableTitle="'Faturamentos (Particular)'"
      :showCheckbox="false"
      :search="true"
      :showAddButton="false"
      :showStatus="false"
      :showActions="false"
    />
  </Layout>
</template>

<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head } from "@inertiajs/vue3";
import { toRef } from "vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";

const props = defineProps({
  faturamentos: { type: Array, default: () => [] },
});

const rows = toRef(props, "faturamentos");

function formatCurrency(n) {
  const v = Number(n || 0);
  try {
    return v.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  } catch (_) {
    return `R$ ${v.toFixed(2)}`.replace(".", ",");
  }
}

const cols = [
  { id: "id", name: "ID" },
  { id: "paciente", name: "Paciente" },
  { id: "paciente_documento", name: "Documento" },
  { id: "numero_orcamento", name: "Orçamento" },
  { id: "data_faturamento", name: "Faturado em" },
  { id: "vencimento", name: "Vencimento" },
  { id: "valor_final", name: "Valor", formatter: (cell) => formatCurrency(cell) },
  { id: "status", name: "Status" },
];
</script>

<style scoped>
:deep(.table thead th:nth-child(1)),
:deep(.table tbody td:nth-child(1)) { display: none; }
</style>

