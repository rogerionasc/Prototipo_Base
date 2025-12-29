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
            <input v-model="formCreate.nome" type="text" class="form-control" id="espNome" :class="{ 'is-invalid': formCreate.errors.nome }" placeholder="Ex.: Cardiologia" />
            <div class="invalid-feedback">{{ formCreate.errors.nome }}</div>
          </BCol>
          <BCol md="3">
            <label for="espCodigo" class="form-label">Código</label>
            <input v-model="formCreate.codigo" type="text" class="form-control" id="espCodigo" placeholder="Ex.: CARD" />
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
        <BRow class="mt-3">
          <BCol>
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
            <input v-model="formEdit.nome" type="text" id="espEditNome" class="form-control" :class="{ 'is-invalid': formEdit.errors.nome }" />
            <div class="invalid-feedback">{{ formEdit.errors.nome }}</div>
          </BCol>
          <BCol md="6">
            <label for="espEditCodigo" class="form-label">Código</label>
            <input v-model="formEdit.codigo" type="text" id="espEditCodigo" class="form-control" />
          </BCol>
        </BRow>
        <BRow class="g-3 mt-0">
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
import { ref, watch, computed } from "vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";
const props = defineProps({
  especialidades: { type: Array, default: () => [] },
});
const especialidadesLocal = ref([...(props.especialidades || [])]);
watch(() => props.especialidades, (v) => { especialidadesLocal.value = [...(v || [])]; });
const columns = [
  { id: "id", name: "ID" },
  { id: "nome", name: "Nome" },
  { id: "codigo", name: "Código" },
  { id: "descricao", name: "Descrição" },
];
const tableData = computed(() => {
  return (especialidadesLocal.value || []).map(e => ({
    id: e.id,
    nome: e.nome,
    codigo: e.codigo || '-',
    descricao: e.descricao || '-',
    status: e.ativo ? 'ativo' : 'inativo',
  }));
});
const formCreate = useForm({
  nome: "",
  codigo: "",
  descricao: "",
  ativo: true,
});
function saveEspecialidade() {
  formCreate.post("/especialidades", {
    preserveScroll: true,
    onSuccess: () => {
      formCreate.reset();
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
});
const editModal = ref(false);
function startEdit(e) {
  editingId.value = e.id;
  formEdit.nome = e.nome || "";
  formEdit.codigo = e.codigo || "";
  formEdit.descricao = e.descricao || "";
  formEdit.ativo = !!e.ativo;
  editModal.value = true;
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
  editModal.value = false;
}
function updateEspecialidade() {
  if (!editingId.value) return;
  formEdit.put(`/especialidades/${editingId.value}`, {
    preserveScroll: true,
    onSuccess: () => {
      const idx = (especialidadesLocal.value || []).findIndex(x => String(x.id) === String(editingId.value));
      if (idx !== -1) {
        especialidadesLocal.value[idx] = {
          ...especialidadesLocal.value[idx],
          nome: formEdit.nome || '',
          codigo: formEdit.codigo || '',
          descricao: formEdit.descricao || '',
          ativo: !!formEdit.ativo,
        };
      }
      cancelEdit();
      router.reload({ only: ['especialidades'] });
    },
  });
}
function openModalShow(id) {
  alert('Especialidade: ' + JSON.stringify(id));
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
