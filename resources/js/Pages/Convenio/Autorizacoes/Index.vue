<template>
  <Layout>
    <Head title="Autorizações" />
    <PageHeader title="Autorizações" pageTitle="Convênios" />
    <TableGrid
      :columns="columns"
      :data="autorizacoes"
      :tableTitle="'Todas as Autorizações'"
      :showStatus="false"
      :showCheckbox="false"
      :showImage="false"
      :searchPlaceholder="'Buscar autorização'"
      @add="openModalAdd"
      @delete="openModalDelete"
      @edit="openModalEdit"
      @show="openModalShow"
    />
    <Modal v-model="showModal" :title="modalTitle" size="xl" :name-button="saveButtonText" :processing="saveProcessing" @save="onSaveAutorizacao">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Convênio</label>
          <select v-model="form.convenio_id" class="form-select" required>
            <option value="">Selecione um convênio</option>
            <option v-for="conv in convenios" :key="conv.id" :value="conv.id">
              {{ conv.descricao }}
            </option>
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Carteirinha</label>
          <input v-model="form.carteira" type="text" class="form-control" placeholder="Número da carteirinha" />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Número da Autorização</label>
          <input v-model="form.numero_autorizacao" type="text" class="form-control" placeholder="Número da autorização" />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Status</label>
          <select v-model="form.status" class="form-select" required>
            <option value="Pendente">Pendente</option>
            <option value="Aprovada">Aprovada</option>
            <option value="Negada">Negada</option>
            <option value="Expirada">Expirada</option>
            <option value="Cancelada">Cancelada</option>
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Validade</label>
          <input v-model="form.validade" type="date" class="form-control" />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Data da Solicitação</label>
          <input v-model="form.data_solicitacao" type="date" class="form-control" />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Data da Resposta</label>
          <input v-model="form.data_resposta" type="date" class="form-control" />
        </div>
        <div class="col-md-12 mb-3">
          <label class="form-label">Observação</label>
          <textarea v-model="form.observacao" class="form-control" rows="3" placeholder="Observações"></textarea>
        </div>
      </div>
    </Modal>
    <ModalDelete
      v-model="deleteModal"
      :title="'Excluir Autorização'"
      :subTitle="deleteSubTitle"
      :item-delete="autorizacaoToDelete"
      @save="confirmDelete"
    />
  </Layout>
 </template>
 <script setup>
 import "gridjs/dist/theme/mermaid.css";
 import Layout from "@/Layouts/main.vue";
 import PageHeader from "@/Components/page-header.vue";
import { Head, useForm, router } from '@inertiajs/vue3';
 import TableGrid from "@/Components/Tables/TableGrid.vue";
 import Modal from "@/Components/Modal.vue";
 import ModalDelete from "@/Components/ModalDelete.vue";
import { ref, computed } from "vue";

 const { autorizacoes, convenios, usuarios } = defineProps({
   autorizacoes: { type: Array, default: () => [] },
   convenios: { type: Array, default: () => [] },
   usuarios: { type: Array, default: () => [] },
 });

 const columns = [
   { id: "id", name: "ID" },
   { id: "convenio_id", name: "Convênio", formatter: (cell) => convenios.find(c => c.id == cell)?.descricao || cell || "" },
   { id: "carteira", name: "Carteirinha" },
   { id: "numero_autorizacao", name: "Número Autorização" },
   { id: "status", name: "Status" },
   { id: "validade", name: "Validade" },
   { id: "data_solicitacao", name: "Data Solicitação" },
   { id: "data_resposta", name: "Data Resposta" },
   { id: "usuario_id", name: "Solicitado Por", formatter: (cell) => usuarios.find(u => u.id == cell)?.nome || cell || "" },
   { id: "usuario_id_validou", name: "Validado Por", formatter: (cell) => cell ? (usuarios.find(u => u.id == cell)?.nome || cell) : "" },
 ];

const showModal = ref(false);
const modalTitle = ref('Adicionar Autorização');
const isEditing = ref(false);
const editingId = ref(null);
const saveProcessing = ref(false);
const saveButtonText = computed(() => isEditing.value ? 'Atualizar' : 'Salvar');

const form = useForm({
  convenio_id: '',
  carteira: '',
  numero_autorizacao: '',
  status: 'Pendente',
  validade: '',
  data_solicitacao: '',
  data_resposta: '',
  observacao: '',
});

 function openModalAdd() {
   isEditing.value = false;
   editingId.value = null;
   modalTitle.value = 'Adicionar Autorização';
   form.reset();
   showModal.value = true;
 }

async function onSaveAutorizacao() {
  saveProcessing.value = true;
  if (isEditing.value && editingId.value) {
    form.put(`/convenios/autorizacoes/${editingId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        showModal.value = false;
        router.reload({ only: ['autorizacoes'], preserveScroll: true, preserveState: false });
      },
      onFinish: () => {
        saveProcessing.value = false;
      }
    });
  } else {
    form.post('/convenios/autorizacoes', {
      preserveScroll: true,
      onSuccess: () => {
        showModal.value = false;
        router.reload({ only: ['autorizacoes'], preserveScroll: true, preserveState: false });
      },
      onFinish: () => {
        saveProcessing.value = false;
      }
    });
  }
}

 const deleteModal = ref(false);
 const autorizacaoToDelete = ref({});
 const deleteSubTitle = ref('Deseja realmente excluir');

 function openModalDelete(row) {
   autorizacaoToDelete.value = { ...row, nome: `Autorização #${row.id}` };
   deleteSubTitle.value = `Deseja realmente excluir a autorização #${row.id}?`;
   deleteModal.value = true;
 }

 function confirmDelete() {
   const id = autorizacaoToDelete.value?.id;
   if (!id) { deleteModal.value = false; return; }
   const f = useForm({});
   f.delete(`/convenios/autorizacoes/${id}`, {
     preserveScroll: true,
     onSuccess: () => {
       deleteModal.value = false;
       autorizacaoToDelete.value = {};
     }
   });
 }

 async function openModalEdit(id) {
   const a = autorizacoes.find(au => String(au.id) === String(id));
   if (!a) return;
   isEditing.value = true;
   editingId.value = a.id;
   modalTitle.value = 'Editar Autorização';
   form.convenio_id = a.convenio_id;
   form.carteira = a.carteira || '';
   form.numero_autorizacao = a.numero_autorizacao || '';
   form.status = a.status;
   form.validade = a.validade || '';
   form.data_solicitacao = a.data_solicitacao ? new Date(a.data_solicitacao).toISOString().split('T')[0] : '';
   form.data_resposta = a.data_resposta ? new Date(a.data_resposta).toISOString().split('T')[0] : '';
   form.observacao = a.observacao || '';
   showModal.value = true;
 }

 function openModalShow(id) { }
 </script>
