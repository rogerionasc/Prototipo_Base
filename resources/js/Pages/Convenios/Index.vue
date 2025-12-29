<template>
  <Layout>
    <Head title="Convênios" />
    <PageHeader title="Convênios" pageTitle="Menu" />
    <TableGrid
      :columns="columns"
      :data="convenios"
      :tableTitle="'Todos os Convênios'"
      :showStatus="false"
      :searchPlaceholder="'Buscar convênio'"
      @add="openModalAdd"
      @delete="openModalDelete"
      @edit="openModalEdit"
      @show="openModalShow"
    />
    <Modal v-model="showModal" :title="modalTitle" size="lg" :name-button="saveButtonText" :processing="saveProcessing" @save="onSaveConvenio">
      <ConvenioForm ref="convenioFormRef" :contas="contas" />
    </Modal>
    <ModalDelete
      v-model="deleteModal"
      :title="'Excluir Convênio'"
      :subTitle="deleteSubTitle"
      :item-delete="convenioToDelete"
      @save="confirmDelete"
    />
  </Layout>
 </template>
 <script setup>
 import "gridjs/dist/theme/mermaid.css";
 import Layout from "@/Layouts/main.vue";
 import PageHeader from "@/Components/page-header.vue";
 import { Head, useForm } from '@inertiajs/vue3';
  import TableGrid from "@/Components/Tables/TableGrid.vue";
  import Modal from "@/Components/Modal.vue";
  import ModalDelete from "@/Components/ModalDelete.vue";
import ConvenioForm from "./Create.vue";
import { ref, nextTick, computed, watchEffect } from "vue";

 const { convenios, contas } = defineProps({
   convenios: { type: Array, default: () => [] },
   contas: { type: Array, default: () => [] },
 });

 const columns = [
   { id: "id", name: "ID" },
   { id: "descricao", name: "Descrição" },
   { id: "tipo", name: "Tipo" },
   { id: "ans", name: "ANS" },
 ];

const showModal = ref(false);
const modalTitle = ref('Adicionar Convênio');
const convenioFormRef = ref(null);
const saveProcessing = ref(false);
watchEffect(() => {
  const c = convenioFormRef.value;
  saveProcessing.value = !!(c?.processingRef?.value ?? c?.form?.processing);
});
const isEditing = ref(false);
const editingId = ref(null);
const saveButtonText = computed(() => isEditing.value ? 'Atualizar' : 'Salvar');
 function openModalAdd() {
   isEditing.value = false;
   modalTitle.value = 'Adicionar Convênio';
   showModal.value = true;
 }
async function onSaveConvenio() {
  if (!convenioFormRef.value) return;
  if (isEditing.value && editingId.value) {
    convenioFormRef.value?.submitUpdate(editingId.value, () => {
      showModal.value = false;
      isEditing.value = false;
      editingId.value = null;
      router.reload({ only: ['convenios'] });
    }, {
      onStart: () => { saveProcessing.value = true; },
      onFinish: () => { saveProcessing.value = false; },
    });
  } else {
    convenioFormRef.value?.submit(() => {
      showModal.value = false;
      router.reload({ only: ['convenios'] });
    }, {
      onStart: () => { saveProcessing.value = true; },
      onFinish: () => { saveProcessing.value = false; },
    });
  }
}
 const deleteModal = ref(false);
 const convenioToDelete = ref({});
 const deleteSubTitle = ref('Deseja realmente excluir');
 function openModalDelete(row) {
   convenioToDelete.value = { ...row, nome: row?.descricao };
   deleteSubTitle.value = row?.descricao ? `Deseja realmente excluir o convênio "${row.descricao}"?` : 'Deseja realmente excluir';
   deleteModal.value = true;
 }
 function confirmDelete() {
   const id = convenioToDelete.value?.id;
   if (!id) { deleteModal.value = false; return; }
   const f = useForm({});
   f.delete(`/convenios/${id}`, {
     preserveScroll: true,
     onSuccess: () => {
       deleteModal.value = false;
       convenioToDelete.value = {};
     }
   });
 }
 async function openModalEdit(id) {
   const c = convenios.find(cv => String(cv.id) === String(id));
   if (!c) return;
   isEditing.value = true;
   editingId.value = c.id;
   modalTitle.value = 'Editar Convênio';
   showModal.value = true;
   await nextTick();
   if (convenioFormRef.value?.form) {
     convenioFormRef.value.form.descricao = c.descricao || '';
     convenioFormRef.value.form.tipo = c.tipo || '';
     convenioFormRef.value.form.empresa_id = c.empresa_id || '';
     convenioFormRef.value.form.ans = c.ans ?? null;
     convenioFormRef.value.form.dias_recebimento = c.dias_recebimento ?? null;
     convenioFormRef.value.form.dias_retorno = c.dias_retorno ?? null;
   }
 }
 function openModalShow(id) { }
 </script>
