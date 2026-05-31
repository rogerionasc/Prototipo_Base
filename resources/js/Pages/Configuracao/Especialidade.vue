<template>
  <BCard class="shadow-sm config-card">
    <BCardHeader class="bg-light-subtle p-3 border-0">
      <BCardTitle><i class="ri-first-aid-kit-line text-primary me-2"></i>Cadastro de Especialidades</BCardTitle>
    </BCardHeader>
    <BCardBody>
      <p class="text-muted mb-3">Gerencie especialidades médicas disponíveis no sistema.</p>
      <div class="border border-dashed rounded p-3 bg-light-subtle mb-3">
        <BRow class="g-3 align-items-end">
          <BCol md="4">
            <label for="espNome" class="form-label">Nome</label>
            <span class="text-danger ms-1">*</span>
            <input v-model="formCreate.nome" type="text" class="form-control" id="espNome" :class="{ 'is-invalid': formCreate.errors.nome }" placeholder="Ex.: Cardiologia" maxlength="120" />
            <div class="invalid-feedback">{{ formCreate.errors.nome }}</div>
          </BCol>
          <BCol md="3">
            <label for="espCodigo" class="form-label">Código</label>
            <input v-model="formCreate.codigo" type="text" class="form-control" id="espCodigo" placeholder="Ex.: CARD" maxlength="20" />
          </BCol>
          <BCol md="3">
            <label for="espAtivo" class="form-label">Ativa</label>
            <select v-model="formCreate.ativo" class="form-select" id="espAtivo">
              <option :value="true">Sim</option>
              <option :value="false">Não</option>
            </select>
          </BCol>
          <BCol md="2">
            <button type="button" class="btn btn-primary w-100" :disabled="formCreate.processing" @click="saveEspecialidade">Adicionar</button>
          </BCol>
        </BRow>
        <BRow class="mt-3 g-3">
          <BCol md="12">
            <label for="espProcedimentos" class="form-label">Procedimentos Realizados</label>
            <select
              multiple
              data-choices
              data-choices-removeItem
              class="form-control"
              id="espProcedimentos"
              ref="createProcedimentosSelect"
            >
              <option value="">Selecione os procedimentos</option>
              <option v-for="p in procedimentosOptions" :key="p.value" :value="p.value">
                {{ p.label }}
              </option>
            </select>
          </BCol>
        </BRow>
        <BRow class="mt-3 g-3">
          <BCol md="12">
            <label for="espDescricao" class="form-label">Descrição</label>
            <textarea v-model="formCreate.descricao" id="espDescricao" class="form-control" rows="2" placeholder="Descrição opcional"></textarea>
          </BCol>
        </BRow>
      </div>
      <TableGrid
        :columns="columns"
        :data="tableData"
        :tableTitle="'Lista de Especialidades Médicas'"
        :search="true"
        :searchPlaceholder="'Buscar por especialidade'"
        :showCheckbox="false"
        :showActions="true"
        :showStatus="true"
        :showPerPagination="true"
        :showAddButton="false"
        @delete="askDelete"
        @edit="startEditById"
        @show="openModalShow"
      />
      <Modal v-model="editModal" :title="'Editar Especialidade'" size="lg" :name-button="'Salvar'" :processing="formEdit.processing" @save="updateEspecialidade">
        <BRow class="g-3">
          <BCol md="6">
            <label for="espEditNome" class="form-label">Nome</label>
            <input v-model="formEdit.nome" type="text" id="espEditNome" class="form-control" :class="{ 'is-invalid': formEdit.errors.nome }" maxlength="120" />
            <div class="invalid-feedback">{{ formEdit.errors.nome }}</div>
          </BCol>
          <BCol md="6">
            <label for="espEditCodigo" class="form-label">Código</label>
            <input v-model="formEdit.codigo" type="text" id="espEditCodigo" class="form-control" maxlength="20" />
          </BCol>
        </BRow>
        <BRow class="g-3 mt-1">
          <BCol md="12">
            <label class="form-label">Procedimentos Realizados</label>
            <select
              multiple
              data-choices
              data-choices-removeItem
              class="form-control"
              id="espEditProcedimentos"
              ref="editProcedimentosSelect"
            >
              <option value="">Selecione os procedimentos</option>
              <option v-for="p in procedimentosOptions" :key="p.value" :value="p.value">
                {{ p.label }}
              </option>
            </select>
          </BCol>
        </BRow>
        <BRow class="g-3 mt-1">
          <BCol md="8">
            <label for="espEditDescricao" class="form-label">Descrição</label>
            <textarea v-model="formEdit.descricao" id="espEditDescricao" class="form-control" rows="3"></textarea>
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
      <ModalDelete
        v-model="deleteModal"
        :title="'Excluir Especialidade'"
        :subTitle="deleteSubTitle"
        :item-delete="especialidadeToDelete"
        @save="confirmDelete"
      />
    </BCardBody>
  </BCard>
</template>
<script setup>
import { useForm, router } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import ModalDelete from "@/Components/ModalDelete.vue";
import { ref, watch, computed, nextTick, onMounted } from "vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";

const props = defineProps({
  especialidades: { type: Array, default: () => [] },
  procedimentos: { type: Array, default: () => [] },
});
const especialidadesLocal = ref([...(props.especialidades || [])]);
watch(() => props.especialidades, (v) => { especialidadesLocal.value = [...(v || [])]; });

const createProcedimentosSelect = ref(null);
const editProcedimentosSelect = ref(null);

const procedimentosOptions = computed(() => {
  return (props.procedimentos || []).map(p => ({
    value: p.id,
    label: p.nome
  }));
});

onMounted(() => {
  if (window.initChoices) window.initChoices();
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
const formCreate = useForm({
  nome: "",
  codigo: "",
  descricao: "",
  ativo: true,
  procedimentos_ids: [],
});
function saveEspecialidade() {
  formCreate.procedimentos_ids = getSelectedValues(createProcedimentosSelect.value);
  formCreate.post("/especialidades", {
    preserveScroll: true,
    onSuccess: () => {
      formCreate.reset();
      setChoiceValues(createProcedimentosSelect.value, []);
      router.reload({ only: ['especialidades'] });
    },
  });
}
const editingId = ref(null);
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
  nextTick(() => {
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
  if (!editingId.value) return;
  formEdit.procedimentos_ids = getSelectedValues(editProcedimentosSelect.value);
  formEdit.put(`/especialidades/${editingId.value}`, {
    preserveScroll: true,
    onSuccess: () => {
      cancelEdit();
      router.reload({ only: ['especialidades'] });
    },
  });
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
    }
  });
}
</script>
<style scoped>
</style>
