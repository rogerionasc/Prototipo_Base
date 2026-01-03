<template>
  <Layout>
    <Head title="Cadastro de Caixa" />
    <PageHeader title="Cadastro de Caixa" pageTitle="Financeiro" />
    <TableGrid
      :columns="columns"
      :data="caixas"
      :tableTitle="'Todos os Caixas'"
      :showStatus="false"
      :searchPlaceholder="'Buscar caixa'"
      :showCheckbox="false"
      :showAddButton="true"
      :showActions="true"
      :actionsConfig="{ delete: true, edit: true, show: false, diary: false, print: false, download: false }"
      @add="openModalAdd"
      @delete="openModalDelete"
      @edit="openModalEdit"
      @show="openModalShow"
    />
    <Modal v-model="showModal" :title="modalTitle" size="lg" :name-button="saveButtonText" :processing="form.processing" @save="onSaveCaixa">
      <form class="row g-3 needs-validation" novalidate ref="formEl">
        <div class="row g-3 mt-2">
          <div class="col-md-6">
            <label for="descricao" class="form-label">Descrição</label>
            <span class="text-danger ms-1">*</span>
            <input v-model="form.descricao" type="text" class="form-control" :class="{ 'is-invalid': !!form.errors.descricao }" id="descricao" placeholder="Nome do caixa" maxlength="120" />
            <div class="invalid-feedback" v-if="form.errors.descricao">{{ form.errors.descricao }}</div>
          </div>
          <div class="col-md-6">
            <label for="tipo" class="form-label">Tipo</label>
            <span class="text-danger ms-1">*</span>
            <select v-model="form.tipo" class="form-select" :class="{ 'is-invalid': !!form.errors.tipo }" id="tipo" data-choices ref="tipoSelect">
              <option disabled value="">Selecione...</option>
              <option value="Banco">Banco</option>
              <option value="Local">Local</option>
            </select>
            <div class="invalid-feedback" v-if="form.errors.tipo">{{ form.errors.tipo }}</div>
          </div>
          <div class="col-md-4">
            <label class="form-label d-block">Bloquear Receber</label>
            <div class="form-check">
              <input v-model="form.bloquear_receber" class="form-check-input" type="checkbox" id="bloquearReceber" />
              <label class="form-check-label" for="bloquearReceber">Sim</label>
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label d-block">Bloquear Pagar</label>
            <div class="form-check">
              <input v-model="form.bloquear_pagar" class="form-check-input" type="checkbox" id="bloquearPagar" />
              <label class="form-check-label" for="bloquearPagar">Sim</label>
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label d-block">Ativo</label>
            <div class="form-check">
              <input v-model="form.ativo" class="form-check-input" type="checkbox" id="ativo" />
              <label class="form-check-label" for="ativo">Sim</label>
            </div>
          </div>
        </div>
      </form>
    </Modal>
    <ModalDelete
      v-model="deleteModal"
      :title="'Excluir Caixa'"
      :subTitle="deleteSubTitle"
      :item-delete="caixaToDelete"
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
 import { ref, nextTick, computed, watch, onMounted, toRef } from "vue";

 const props = defineProps({
   caixas: { type: Array, default: () => [] },
 });
 const caixas = toRef(props, 'caixas');

 function toBool(v) {
   if (v === true || v === false) return v;
   if (typeof v === 'number') return v === 1;
   if (typeof v === 'string') return v === '1' || v.toLowerCase() === 'true';
   return !!v;
 }
 function normalizeTipo(v) {
   const t = (v ?? '').toString().trim();
   return t === 'Banco' || t === 'Local' ? t : '';
 }

 const columns = [
   { id: "id", name: "ID" },
   { id: "descricao", name: "Descrição" },
   { id: "tipo", name: "Tipo" },
   {
     id: "bloquear_receber",
     name: "Bloq. Receber",
     formatter: (cell) => toBool(cell) ? 'Sim' : 'Não'
   },
   {
     id: "bloquear_pagar",
     name: "Bloq. Pagar",
     formatter: (cell) => toBool(cell) ? 'Sim' : 'Não'
   },
   {
     id: "ativo",
     name: "Ativo",
     formatter: (cell) => toBool(cell) ? 'Sim' : 'Não'
   },
 ];

 const showModal = ref(false);
 const modalTitle = ref('Adicionar Caixa');
 const formEl = ref(null);
 const tipoSelect = ref(null);
 const isEditing = ref(false);
 const editingId = ref(null);
 const saveButtonText = computed(() => isEditing.value ? 'Atualizar' : 'Salvar');

 const form = useForm({
   descricao: "",
   tipo: "",
   bloquear_receber: false,
   bloquear_pagar: false,
   ativo: true,
 });

 function openModalAdd() {
   isEditing.value = false;
   editingId.value = null;
   modalTitle.value = 'Adicionar Caixa';
   showModal.value = true;
   nextTick(() => {
     form.reset();
     form.ativo = true;
     if (window.syncChoiceValue && tipoSelect.value) {
       window.syncChoiceValue(tipoSelect.value, form.tipo || "");
     }
     formEl.value?.classList.remove("was-validated");
   });
 }

async function openModalEdit(rowOrId) {
  const id = typeof rowOrId === 'object' ? rowOrId?.id : rowOrId;
  isEditing.value = true;
  editingId.value = id;
  modalTitle.value = 'Editar Caixa';
  showModal.value = true;
  await nextTick();
  let c = caixas.value.find(x => String(x.id) === String(id));
  if (!c) {
    await new Promise((resolve) => {
      router.reload({ only: ['caixas'], onFinish: () => resolve() });
    });
    c = caixas.value.find(x => String(x.id) === String(id));
  }
  if (c) {
    form.descricao = c.descricao || '';
    form.tipo = normalizeTipo(c.tipo);
    form.bloquear_receber = toBool(c.bloquear_receber);
    form.bloquear_pagar = toBool(c.bloquear_pagar);
    form.ativo = toBool(c.ativo);
    await nextTick();
    if (window.syncChoiceValue && tipoSelect.value) {
      window.syncChoiceValue(tipoSelect.value, form.tipo || "");
    }
  } else {
    form.reset();
  }
  formEl.value?.classList.remove("was-validated");
}

 function onSaveCaixa() {
   if (isEditing.value && editingId.value) {
     form.put(`/caixas/${editingId.value}`, {
       onSuccess: () => {
         showModal.value = false;
         isEditing.value = false;
         editingId.value = null;
         router.reload({ only: ['caixas'] });
         if (form.clearErrors) { form.clearErrors(); } else { form.errors = {}; }
       },
     });
   } else {
     form.post("/caixas", {
       onSuccess: () => {
         showModal.value = false;
         router.reload({ only: ['caixas'] });
         form.reset();
         form.ativo = true;
         if (form.clearErrors) { form.clearErrors(); } else { form.errors = {}; }
       },
     });
   }
 }

 const deleteModal = ref(false);
 const caixaToDelete = ref({});
 const deleteSubTitle = ref('Deseja realmente excluir');

 function openModalDelete(row) {
   caixaToDelete.value = { ...row, nome: row?.descricao };
   deleteSubTitle.value = row?.descricao ? `Deseja realmente excluir o caixa "${row.descricao}"?` : 'Deseja realmente excluir';
   deleteModal.value = true;
 }

 function confirmDelete() {
   const id = caixaToDelete.value?.id;
   if (!id) { deleteModal.value = false; return; }
   const f = useForm({});
   f.delete(`/caixas/${id}`, {
     preserveScroll: true,
     onSuccess: () => {
       deleteModal.value = false;
       caixaToDelete.value = {};
       router.reload({ only: ['caixas'] });
     }
   });
 }

 function openModalShow(id) {}

 watch(() => form.tipo, async (value) => {
   await nextTick();
   if (window.syncChoiceValue && tipoSelect.value) {
     window.syncChoiceValue(tipoSelect.value, value || "");
   }
 }, { immediate: true });

 const onTipoChange = (e) => {
   form.tipo = e?.target?.value ?? form.tipo;
 };

 onMounted(async () => {
   await nextTick();
   if (tipoSelect.value) {
     tipoSelect.value.addEventListener("change", onTipoChange);
   }
 });
 </script>
