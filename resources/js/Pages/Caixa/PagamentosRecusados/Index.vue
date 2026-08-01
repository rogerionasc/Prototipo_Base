<template>
  <Layout>
    <Head title="Pagamentos Recusados" />
    <PageHeader title="Pagamentos Recusados" pageTitle="Caixa" />
    <TableGrid
      :columns="recusadosCols"
      :data="recusadosData"
      :tableTitle="'Pagamentos Recusados'"
      :showCheckbox="false"
      :search="true"
      :showAddButton="false"
      :showStatus="false"
      :showActions="false"
      @restore="onRestore"
    />
    <Modal v-model="showUnrefuseModal" :title="'Cancelar Recusa'" :name-button="'Cancelar recusa'" :processing="false" size="md" @save="confirmarCancelarRecusa">
      <div class="vstack gap-3">
        <div class="alert alert-warning" role="alert">
          Esta ação retornará o pagamento para pendentes.
        </div>
        <div class="row g-2">
          <div class="col-6">
            <div class="d-flex flex-column">
              <span class="text-muted">Paciente</span>
              <span class="fw-semibold">{{ unrefuseInfo.paciente }}</span>
            </div>
          </div>
          <div class="col-3">
            <div class="d-flex flex-column">
              <span class="text-muted">Valor</span>
              <span class="fw-semibold">{{ unrefuseInfo.valor }}</span>
            </div>
          </div>
          <div class="col-3">
            <div class="d-flex flex-column">
              <span class="text-muted">Emissão</span>
              <span class="fw-semibold">{{ unrefuseInfo.emissao }}</span>
            </div>
          </div>
        </div>
      </div>
    </Modal>
  </Layout>
 </template>

 <script setup>
 import Layout from "@/Layouts/main.vue";
 import PageHeader from "@/Components/page-header.vue";
 import { Head, useForm, router } from "@inertiajs/vue3";
 import { toRef, computed, ref } from "vue";
 import Modal from "@/Components/Modal.vue";
 import TableGrid from "@/Components/Tables/TableGrid.vue";
 import { html } from "gridjs";

 const props = defineProps({
   pagamentosRecusados: { type: Array, default: () => [] },
 });
 const recusados = toRef(props, "pagamentosRecusados");
 const showUnrefuseModal = ref(false);
 const unrefuseId = ref(null);
 const recusadosCols = [
   { id: "pagamento_id_str", name: "Nº Pag." },
   { id: "paciente", name: "Paciente" },
   { id: "numero_faturamento", name: "Faturamento" },
   { id: "data_emissao", name: "Emissão" },
   { id: "data_recusa", name: "Recusa" },
   { id: "recusa_justificativa", name: "Justificativa" },
   { id: "recusado_por_nome", name: "Usuário" },
   { id: "valor", name: "Valor", formatter: (cell) => formatCurrency(cell) },
   {
     id: "actions",
     name: "Ações",
     formatter: (cell, row) => {
       const rowId = row?.cells?.[0]?.data;
       return html(`<div class="d-flex gap-2">
         <button class="btn btn-sm btn-soft-warning" type="button" data-action="restore" data-id="${rowId}" title="Cancelar Recusa">
           <i class="ri-arrow-go-back-line align-bottom"></i>
         </button>
       </div>`);
     }
   }
 ];

 // Pre-processa os dados adicionando pagamento_id_str como string
 const recusadosData = computed(() =>
   (recusados.value || []).map(r => ({ ...r, pagamento_id_str: String(r.num_pagamento ?? '') }))
 );

 const unrefuseInfo = computed(() => {
   const id = unrefuseId.value;
   if (!id) return {};
   const r = (recusados.value || []).find(x => String(x.num_pagamento) === String(id));
   return {
     paciente: r?.paciente || "—",
     valor: formatCurrency(r?.valor || 0),
     emissao: r?.data_emissao || "—",
   };
 });

 function formatCurrency(n) {
   const v = Number(n || 0);
   try {
     return v.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
   } catch (e) {
     return `R$ ${v.toFixed(2)}`.replace(".", ",");
   }
 }
 function onRestore(id, row) {
   unrefuseId.value = id ?? row?.id ?? null;
   showUnrefuseModal.value = true;
 }
 function confirmarCancelarRecusa() {
   const id = unrefuseId.value;
   if (!id) { showUnrefuseModal.value = false; return; }
   const f = useForm({});
   f.put(`/pagamentos/${id}/unrefuse`, {
     onSuccess: async () => {
       showUnrefuseModal.value = false;
       unrefuseId.value = null;
       await new Promise((resolve) => {
         router.reload({ only: ["pagamentosRecusados"] , onFinish: () => resolve() });
       });
     },
     onError: () => {
       showUnrefuseModal.value = false;
     },
   });
 }
 </script>

 <style scoped>
 .table-clean thead th { border-bottom: 1px solid var(--bs-border-color); }
 .table-clean tbody tr:not(:last-child) td { border-bottom: 1px solid var(--bs-border-color); }
 .table-clean td, .table-clean th { vertical-align: middle; }
  </style>
