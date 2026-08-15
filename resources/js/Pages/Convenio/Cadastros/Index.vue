<template>
  <Layout>

    <Head title="Convênios" />
    <PageHeader title="Convênios" pageTitle="Menu" />
    <TableGrid :columns="columns" :data="props.convenios" :tableTitle="'Todos os Convênios'" :showStatus="false"
      :showCheckbox="false" :showImage="false" :searchPlaceholder="'Buscar convênio'" @add="openModalAdd"
      @delete="openModalDelete" @edit="openModalEdit" @show="openModalShow" />
    <Modal v-model="showModal" :title="modalTitle" size="xxl" custom-width="95vw" :name-button="saveButtonText"
      :processing="saveProcessing" @save="onSaveConvenio">
      <ConvenioForm ref="convenioFormRef" :contas="props.contas" :tussTabelas="props.tussTabelas"
        :profissionaisSaude="props.profissionaisSaude" />
    </Modal>
    <ModalDelete v-model="deleteModal" :title="'Excluir Convênio'" :subTitle="deleteSubTitle"
      :item-delete="convenioToDelete" @save="confirmDelete" />
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
import ConvenioForm from "./Create.vue";
import { ref, nextTick, computed, watchEffect } from "vue";

const props = defineProps({
  convenios: { type: Array, default: () => [] },
  contas: { type: Array, default: () => [] },
  tussTabelas: { type: Array, default: () => [] },
  profissionaisSaude: { type: Array, default: () => [] },
});

const columns = [
  { id: "id", name: "ID" },
  { id: "logo_path", name: "Logo", sort: false, showImage: true, attributes: { style: "width: 72px;" } },
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
  if (convenioFormRef.value?.form) {
    convenioFormRef.value.form.id = null;
    convenioFormRef.value.form.tipo = 'CONVENIO';
    convenioFormRef.value.form.dias_retorno = null;
    convenioFormRef.value.form.config_spsadt.visivel = [];
    convenioFormRef.value.form.config_spsadt.obrigatorio = [];
    convenioFormRef.value.form.config_spsadt.bloqueado = [];
  }
  convenioFormRef.value?.setExistingLogoPath?.('');
  convenioFormRef.value?.setSelectedTussRows?.([]);
  convenioFormRef.value?.setSelectedMedicos?.([]);
  showModal.value = true;
}
async function onSaveConvenio() {
  if (!convenioFormRef.value) return;
  const isUp = isEditing.value;
  const id = editingId.value;

  const submitFn = isUp && id ? convenioFormRef.value.submitUpdate : convenioFormRef.value.submit;

  const callback = () => {
    showModal.value = false;
    isEditing.value = false;
    editingId.value = null;
    router.reload({ only: ['convenios'], preserveScroll: true, preserveState: false });
  };

  const hooks = {
    onStart: () => { saveProcessing.value = true; },
    onFinish: () => { saveProcessing.value = false; },
  };

  if (isUp && id) {
    submitFn(id, callback, hooks);
  } else {
    submitFn(callback, hooks);
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
  const c = props.convenios.find(cv => String(cv.id) === String(id));
  if (!c) return;
  isEditing.value = true;
  editingId.value = c.id;
  modalTitle.value = 'Editar Convênio';
  if (convenioFormRef.value?.form) {
    convenioFormRef.value.form.id = c.id;
    convenioFormRef.value.form.descricao = c.descricao || '';
    convenioFormRef.value.form.tuss_tabela = '';
    convenioFormRef.value.form.tipo = c.tipo || 'CONVENIO';
    convenioFormRef.value.form.empresa_id = c.empresa_id || '';
    convenioFormRef.value.form.ans = c.ans ?? null;
    convenioFormRef.value.form.dias_recebimento = c.dias_recebimento ?? null;
    convenioFormRef.value.form.dias_retorno = c.dias_retorno ?? null;
    convenioFormRef.value.form.config_spsadt.visivel = Object.values(c.config_spsadt?.visivel || []);
    convenioFormRef.value.form.config_spsadt.obrigatorio = Object.values(c.config_spsadt?.obrigatorio || []);
    convenioFormRef.value.form.config_spsadt.bloqueado = Object.values(c.config_spsadt?.bloqueado || []);
    const medicosMap = (c.medicos || []).map(m => {
      const mId = Number(m.id);
      // c.medico_tuss is eager loaded in ConvenioController as medico_tuss or medicoTuss
      const tList = c.medicoTuss || c.medico_tuss || [];
      const tussIds = tList.filter(mt => Number(mt.pivot.pessoa_id) === mId).map(mt => mt.id);
      return { ...m, tuss_ids: tussIds };
    });
    convenioFormRef.value.form.medicos = medicosMap.map(m => ({ id: m.id, tuss_ids: m.tuss_ids }));
    convenioFormRef.value?.setExistingLogoPath?.(c.logo_path || '');
    convenioFormRef.value?.setSelectedMedicos?.(medicosMap);
  }
  showModal.value = true;

  if (convenioFormRef.value?.setSelectedTussRows) {
    try {
      const rows = [];
      let page = 1;
      let totalPages = 1;
      while (page <= totalPages) {
        const resp = await window.axios.get(`/convenios/${c.id}/tuss-procedimentos`, { params: { page, per_page: 100 } });
        const payload = resp?.data || {};
        const meta = payload?.meta || {};
        const data = Array.isArray(payload?.data) ? payload.data : [];
        rows.push(...data);
        totalPages = Number(meta.total_pages || 1);
        page += 1;
      }
      convenioFormRef.value.setSelectedTussRows(rows);
      if (convenioFormRef.value?.form) {
        convenioFormRef.value.form.tuss_tabela = rows?.[0]?.tabela || '';
      }
    } catch (_) {
      convenioFormRef.value.setSelectedTussRows([]);
    }
  }
}
function openModalShow(id) { }
</script>
