<template>
    <BContainer fluid>
      <BCard class="shadow-sm border-0">
        <BCardHeader class="align-items-center d-flex border-bottom-dashed">
          <BCardTitle class="mb-0 flex-grow-1">Cadastro de Especialidades</BCardTitle>
        </BCardHeader>
        <BCardBody>
          <p class="text-muted mb-0">Gerencie especialidades médicas disponíveis no sistema.</p>

          <TableGrid :columns="columns" :data="tableData" :tableTitle="'Lista de Especialidades Médicas'"
            :showTitle="false" :search="true" :searchPlaceholder="'Buscar por especialidade'" :showCheckbox="false"
            :showActions="true" :showStatus="true" :showPerPagination="true" :showAddButton="true" @add="startCreate"
            @delete="askDelete" @edit="startEditById" @show="openModalShow" />
          <Modal v-model="editModal" :title="editingId ? 'Editar Especialidade' : 'Adicionar Especialidade'" size="lg"
            :name-button="'Salvar'" :processing="formEdit.processing" @save="updateEspecialidade">
            <BRow class="g-3">
              <BCol md="6">
                <label for="espEditNome" class="form-label">Nome</label>
                <input placeholder="Ex.: Cardiologia" v-model="formEdit.nome" type="text" id="espEditNome"
                  class="form-control" :class="{ 'is-invalid': formEdit.errors.nome }" maxlength="120" />
                <div class="invalid-feedback">{{ formEdit.errors.nome }}</div>
              </BCol>
              <BCol md="6">
                <label for="espEditCodigo" class="form-label">Código</label>
                <input placeholder="Ex.: CARD" v-model="formEdit.codigo" type="text" id="espEditCodigo"
                  class="form-control" maxlength="20" />
              </BCol>
            </BRow>
            <BRow class="g-3 mt-1">
              <BCol md="12">
                <label class="form-label">Procedimentos Particular</label>
                <select multiple data-choices data-choices-removeItem class="form-control" id="espEditProcedimentos"
                  ref="editProcedimentosSelect">
                  <option v-for="p in procedimentosOptions" :key="p.value" :value="p.value">
                    {{ p.label }}
                  </option>
                </select>
              </BCol>
            </BRow>
            <BRow class="g-3 mt-1">
              <BCol md="8">
                <label for="espEditDescricao" class="form-label">Descrição</label>
                <textarea placeholder="Descrição opcional" v-model="formEdit.descricao" id="espEditDescricao"
                  class="form-control" rows="3"></textarea>
              </BCol>
              <BCol md="4">
                <label for="espEditAtivo" class="form-label">Ativa</label>
                <select v-model="formEdit.ativo" id="espEditAtivo" class="form-select">
                  <option :value="true">Sim</option>
                  <option :value="false">Não</option>
                </select>
              </BCol>
            </BRow>
          </Modal>
          <ModalDelete v-model="deleteModal" :title="'Excluir Especialidade'" :sub-title="deleteSubTitle"
            :item-delete="especialidadeToDelete" @save="confirmDelete" />
        </BCardBody>
      </BCard>
    </BContainer>
</template>
<script setup>
import { useForm, router } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import ModalDelete from "@/Components/ModalDelete.vue";
import { ref, watch, computed, nextTick } from "vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Choices from "choices.js";

const props = defineProps({
  especialidades: { type: Array, default: () => [] },
  procedimentos: { type: Array, default: () => [] },
});

const especialidadesLocal = ref([...(props.especialidades || [])]);
watch(() => props.especialidades, (v) => { especialidadesLocal.value = [...(v || [])]; });

const editProcedimentosSelect = ref(null);

const procedimentosOptions = computed(() => {
  return (props.procedimentos || []).map(p => ({
    value: p.id,
    label: p.nome
  }));
});

function getSelectedValues(el) {
  if (!el) return [];
  const inst = el._choicesInstance || el.choices;
  if (inst && typeof inst.getValue === 'function') {
    return inst.getValue(true);
  }
  return Array.from(el.selectedOptions).map(o => o.value);
}

function setChoiceValues(el, values) {
  if (!el) return;
  nextTick(() => {
    const inst = el._choicesInstance || el.choices;
    if (inst && typeof inst.setChoiceByValue === 'function') {
      inst.removeActiveItems();
      if (Array.isArray(values) && values.length > 0) {
        inst.setChoiceByValue(values.map(String));
      }
    }
  });
}

const columns = [
  { id: "id", name: "ID" },
  { id: "nome", name: "Nome" },
  { id: "codigo", name: "Código" },
  { id: "procedimentos_count", name: "Procedimentos" },
];

const tableData = computed(() => {
  return (especialidadesLocal.value || []).map(e => ({
    id: e.id,
    nome: e.nome,
    codigo: e.codigo || '-',
    procedimentos_count: (e.procedimentos || []).length + ' procedimento(s)',
    descricao: e.descricao || '-',
    status: e.ativo ? 'ativo' : 'inativo',
  }));
});

const editingId = ref(null);

function startCreate() {
  editingId.value = null;
  formEdit.reset();
  formEdit.nome = "";
  formEdit.codigo = "";
  formEdit.descricao = "";
  formEdit.ativo = true;
  formEdit.procedimentos_ids = [];

  editModal.value = true;
  nextTick(async () => {
    if (window.initChoices) window.initChoices();
    setChoiceValues(editProcedimentosSelect.value, []);
  });
}
const formEdit = useForm({
  nome: "",
  codigo: "",
  descricao: "",
  ativo: true,
  procedimentos_ids: [],
});
const editModal = ref(false);

function startEdit(e) {
  editingId.value = e.id;
  formEdit.nome = e.nome || "";
  formEdit.codigo = e.codigo || "";
  formEdit.descricao = e.descricao || "";
  formEdit.ativo = !!e.ativo;
  const pIds = (e.procedimentos || []).map(p => p.id);
  formEdit.procedimentos_ids = pIds;

  editModal.value = true;
  nextTick(async () => {
    if (window.initChoices) window.initChoices();
    setChoiceValues(editProcedimentosSelect.value, pIds);
  });
}

function startEditById(id) {
  const e = (especialidadesLocal.value || []).find(x => String(x.id) === String(id));
  if (!e) return;
  startEdit(e);
}

function cancelEdit() {
  editingId.value = null;
  formEdit.clearErrors();
  formEdit.reset();
  setChoiceValues(editProcedimentosSelect.value, []);
  editModal.value = false;
}

function updateEspecialidade() {
  formEdit.procedimentos_ids = (getSelectedValues(editProcedimentosSelect.value) || [])
    .filter(v => v != null && String(v).trim() !== "");

  if (editingId.value) {
    formEdit.put(`/especialidades/${editingId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        cancelEdit();
        router.reload({ only: ['especialidades'] });
      },
    });
  } else {
    formEdit.post('/especialidades', {
      preserveScroll: true,
      onSuccess: () => {
        cancelEdit();
        router.reload({ only: ['especialidades'] });
      },
    });
  }
}

function openModalShow(id) {
  const e = (especialidadesLocal.value || []).find(x => String(x.id) === String(id));
  if (!e) return;
  const procs = (e.procedimentos || []).map(p => p.nome).join(', ') || 'Nenhum';
  alert('Especialidade: ' + e.nome + '\nProcedimentos: ' + procs);
}

const deleteModal = ref(false);
const especialidadeToDelete = ref({});
const deleteSubTitle = ref('Deseja realmente excluir');
function askDelete(e) {
  especialidadeToDelete.value = { ...e, nome: e?.nome };
  deleteSubTitle.value = e?.nome ? `Deseja realmente excluir "${e.nome}"?` : 'Deseja realmente excluir';
  deleteModal.value = true;
}

function confirmDelete() {
  const id = especialidadeToDelete.value?.id;
  if (!id) { deleteModal.value = false; return; }
  const f = useForm({});
  f.delete(`/especialidades/${id}`, {
    preserveScroll: true,
    onSuccess: () => {
      deleteModal.value = false;
      especialidadeToDelete.value = {};
      especialidadesLocal.value = (especialidadesLocal.value || []).filter(x => String(x.id) !== String(id));
      router.reload({ only: ['especialidades'] });
    },
  });
}
</script>
<style scoped></style>
