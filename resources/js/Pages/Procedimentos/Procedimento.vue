<template>
    <BContainer fluid>
      <BCard class="shadow-sm border-0">
        <BCardHeader class="align-items-center d-flex border-bottom-dashed">
          <BCardTitle class="mb-0 flex-grow-1">Cadastro de Procedimentos</BCardTitle>
        </BCardHeader>
        <BCardBody>
          <p class="text-muted mb-0">Gerencie procedimentos próprios do sistema.</p>

          <TableGrid :columns="columns" :data="tableData" :tableTitle="'Lista de Procedimentos'" :showTitle="false"
            :search="true" :searchPlaceholder="'Buscar por procedimento'" :showCheckbox="false" :showActions="true"
            :showPerPagination="true" :showAddButton="true" :actionsConfig="{ delete: true, edit: true, show: false }"
            @add="startCreate" @delete="askDelete" @edit="startEditById" />

          <Modal v-model="editModal" :title="editingId ? 'Editar Procedimento' : 'Adicionar Procedimento'" size="lg"
            :name-button="'Salvar'" :processing="formEdit.processing" @save="updateProcedimento">
            <BRow class="g-3">
              <BCol md="8">
                <label for="procEditNome" class="form-label">Nome</label>
                <span class="text-danger ms-1">*</span>
                <input placeholder="Ex: Consulta Geral" v-model="formEdit.nome" type="text" id="procEditNome"
                  class="form-control" :class="{ 'is-invalid': formEdit.errors.nome }" maxlength="120" />
                <div class="invalid-feedback">{{ formEdit.errors.nome }}</div>
              </BCol>
              <BCol md="4">
                <label for="procEditCategoria" class="form-label">Categoria</label>
                <select data-choices v-model="formEdit.categoria_id" id="procEditCategoria" class="form-select"
                  :class="{ 'is-invalid': formEdit.errors.categoria_id }">
                  <option :value="null">Sem categoria</option>
                  <option v-for="c in categoriasLocal" :key="c.id" :value="c.id">{{ c.nome }}</option>
                </select>
                <div class="invalid-feedback">{{ formEdit.errors.categoria_id }}</div>
              </BCol>
            </BRow>
            <BRow class="g-3 mt-0 align-items-end">
              <BCol md="3">
                <label for="procEditEhTrat" class="form-label">É Tratamento?</label>
                <select data-choices v-model="formEdit.eh_tratamento" id="procEditEhTrat" class="form-select"
                  :class="{ 'is-invalid': formEdit.errors.eh_tratamento }">
                  <option :value="false">Não</option>
                  <option :value="true">Sim</option>
                </select>
                <div class="invalid-feedback">{{ formEdit.errors.eh_tratamento }}</div>
              </BCol>
              <BCol md="3" v-if="formEdit.eh_tratamento">
                <label for="procEditQSess" class="form-label">Qtd. Sessões</label>
                <input placeholder="Ex: 6" v-model="formEdit.quantidade_sessoes" type="number" min="1"
                  id="procEditQSess" class="form-control"
                  :class="{ 'is-invalid': formEdit.errors.quantidade_sessoes }" />
                <div class="invalid-feedback">{{ formEdit.errors.quantidade_sessoes }}</div>
              </BCol>
              <BCol :md="formEdit.eh_tratamento ? 3 : 4">
                <label for="procEditValorBase" class="form-label">Valor(R$)</label>
                <input placeholder="Ex: 100.00" v-model="formEdit.valor" type="number" step="0.01" min="0"
                  id="procEditValorBase" class="form-control" :class="{ 'is-invalid': formEdit.errors.valor }" />
                <div class="invalid-feedback">{{ formEdit.errors.valor }}</div>
              </BCol>
              <BCol :md="formEdit.eh_tratamento ? 3 : 5">
                <label for="procEditComissao" class="form-label">Comissão (%)</label>
                <input placeholder="Ex: 10.00" v-model="formEdit.comissao_percentual" type="number" step="0.01" min="0"
                  max="100" id="procEditComissao" class="form-control"
                  :class="{ 'is-invalid': formEdit.errors.comissao_percentual }" />
                <div class="invalid-feedback">{{ formEdit.errors.comissao_percentual }}</div>
              </BCol>
            </BRow>
          </Modal>
          <ModalDelete v-model="deleteModal" :title="'Excluir Procedimento'" :subTitle="deleteSubTitle"
            :item-delete="procedimentoToDelete" @save="confirmDelete" />
        </BCardBody>
      </BCard>
    </BContainer>
</template>
<script setup>
import { useForm, router } from "@inertiajs/vue3";
import { ref, watch, computed, nextTick } from "vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Modal from "@/Components/Modal.vue";
import ModalDelete from "@/Components/ModalDelete.vue";
import Multiselect from '@vueform/multiselect';
import "@vueform/multiselect/themes/default.css";

const props = defineProps({
  procedimentos: { type: Array, default: () => [] },
  categoriasProcedimento: { type: Array, default: () => [] },

});
const procedimentosLocal = ref([...(props.procedimentos || [])]);
const categoriasLocal = ref([...(props.categoriasProcedimento || [])]);

watch(() => props.procedimentos, (v) => { procedimentosLocal.value = [...(v || [])]; });
watch(() => props.categoriasProcedimento, (v) => {
  categoriasLocal.value = [...(v || [])];
  nextTick(() => {
    refreshChoicesById('procCategoria');
    refreshChoicesById('procEditCategoria');
    if (window.autoSyncChoices) window.autoSyncChoices();
  });
});
const columns = [
  { id: "id", name: "ID" },
  { id: "nome", name: "Nome" },
  { id: "categoria", name: "Categoria" },
  { id: "valor", name: "Valor(R$)" },
];
const tableData = computed(() => {
  return (procedimentosLocal.value || []).map(p => {
    const cat = (categoriasLocal.value || []).find(c => String(c.id) === String(p.categoria_id));
    return {
      id: p.id,
      nome: p.nome,
      categoria: cat?.nome || '-',
      valor: typeof p.valor === 'number' || p.valor ? p.valor : '-',
    };
  });
});
const editModal = ref(false);
const deleteModal = ref(false);
const deleteSubTitle = ref('Deseja realmente excluir');
const procedimentoToDelete = ref({});
const editingId = ref(null);

function startCreate() {
  editingId.value = null;
  formEdit.reset();
  formEdit.nome = "";
  formEdit.categoria_id = null;
  formEdit.eh_tratamento = false;
  formEdit.quantidade_sessoes = null;
  formEdit.valor = null;
  formEdit.comissao_percentual = null;
  editModal.value = true;
  nextTick(() => {
    if (window.initChoices) window.initChoices();
    if (window.autoSyncChoices) window.autoSyncChoices();
  });
}
const formEdit = useForm({
  nome: "",
  categoria_id: null,
  eh_tratamento: false,
  quantidade_sessoes: null,
  valor: null,
  comissao_percentual: null,

});
function toBool(v) {
  return v === true || v === 1 || v === '1';
}
function startEdit(p) {
  editingId.value = p.id;
  formEdit.nome = p.nome || "";
  formEdit.categoria_id = p.categoria_id || null;
  formEdit.eh_tratamento = toBool(p.eh_tratamento);
  formEdit.quantidade_sessoes = p.quantidade_sessoes || null;
  formEdit.valor = p.valor || null;
  formEdit.comissao_percentual = p.comissao_percentual || null;

  editModal.value = true;
  nextTick(() => {
    if (window.initChoices) window.initChoices();
    if (window.autoSyncChoices) window.autoSyncChoices();
  });
}
function startEditById(id) {
  const p = (procedimentosLocal.value || []).find(x => String(x.id) === String(id));
  if (!p) return;
  startEdit(p);
}
function updateProcedimento() {
  if (editingId.value) {
    formEdit.put(`/procedimentos/${editingId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        editModal.value = false;
        formEdit.reset();
        editingId.value = null;
        router.reload({ only: ['procedimentos'] });
      },
    });
  } else {
    formEdit.post("/procedimentos", {
      preserveScroll: true,
      onSuccess: () => {
        editModal.value = false;
        formEdit.reset();
        router.reload({ only: ['procedimentos'] });
      },
    });
  }
}
function askDelete(rowObj) {
  const p = (procedimentosLocal.value || []).find(x => String(x.id) === String(rowObj.id));
  if (!p) return;
  procedimentoToDelete.value = { ...p, nome: p?.nome };
  deleteSubTitle.value = p?.nome ? `Deseja realmente excluir "${p.nome}"?` : 'Deseja realmente excluir';
  deleteModal.value = true;
}
function confirmDelete() {
  const id = procedimentoToDelete.value?.id;
  if (!id) { deleteModal.value = false; return; }
  const f = useForm({});
  f.delete(`/procedimentos/${id}`, {
    preserveScroll: true,
    onSuccess: () => {
      deleteModal.value = false;
      procedimentoToDelete.value = {};
      procedimentosLocal.value = (procedimentosLocal.value || []).filter(x => String(x.id) !== String(id));
      router.reload({ only: ['procedimentos'] });
    }
  });
}
function openShow(id) {
  alert('Procedimento: ' + JSON.stringify(id));
}
function refreshChoicesById(id) {
  try {
    const el = document.getElementById(id);
    if (!el) return;
    const inst = el._choicesInstance || el.choices;
    if (inst && typeof inst.destroy === 'function') {
      try { inst.destroy(); } catch (_) { }
      delete el.dataset.choicesInitialized;
      delete el._choicesInstance;
    }
    if (window.initChoices) window.initChoices();
    if (window.autoSyncChoices) window.autoSyncChoices();
  } catch (e) { /* noop */ }
}
</script>
