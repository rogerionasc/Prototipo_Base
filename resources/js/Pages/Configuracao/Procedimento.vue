<template>
  <BCard class="shadow-sm config-card">
    <BCardHeader class="bg-light-subtle p-3 border-0">
      <BCardTitle><i class="ri-file-list-3-line text-primary me-2"></i>Cadastro de Procedimentos</BCardTitle>
    </BCardHeader>
  <BCardBody>
      <p class="text-muted mb-3">Gerencie procedimentos próprios do sistema.</p>
      <div class="border border-dashed rounded p-3 bg-light-subtle mb-3">
        <BRow class="g-3">
          <BCol md="6">
            <label for="procNome" class="form-label">Nome</label>
            <span class="text-danger ms-1">*</span>
            <input placeholder="Ex: Consulta Geral" v-model="formCreate.nome" type="text" id="procNome" class="form-control" :class="{ 'is-invalid': formCreate.errors.nome }" />
            <div class="invalid-feedback">{{ formCreate.errors.nome }}</div>
          </BCol>
          <BCol md="6">
            <label for="procCategoria" class="form-label">Categoria</label>
            <select data-choices v-model="formCreate.categoria_id" id="procCategoria" class="form-select" :class="{ 'is-invalid': formCreate.errors.categoria_id }">
              <option :value="null">Sem categoria</option>
              <option v-for="c in categoriasLocal" :key="c.id" :value="c.id">{{ c.nome }}</option>
            </select>
            <div class="invalid-feedback">{{ formCreate.errors.categoria_id }}</div>
          </BCol>
        </BRow>
        <BRow class="g-3 mt-0 align-items-end">
          <BCol md="2">
            <label for="procEhTrat" class="form-label">É Tratamento?</label>
            <select data-choices v-model="formCreate.eh_tratamento" id="procEhTrat" class="form-select" :class="{ 'is-invalid': formCreate.errors.eh_tratamento }">
              <option :value="false">Não</option>
              <option :value="true">Sim</option>
            </select>
            <div class="invalid-feedback">{{ formCreate.errors.eh_tratamento }}</div>
          </BCol>
          <BCol md="2" v-if="formCreate.eh_tratamento">
            <label for="procQSess" class="form-label">Qtd. Sessões</label>
            <input v-model="formCreate.quantidade_sessoes" type="number" min="1" id="procQSess" class="form-control" :class="{ 'is-invalid': formCreate.errors.quantidade_sessoes }" />
            <div class="invalid-feedback">{{ formCreate.errors.quantidade_sessoes }}</div>
          </BCol>
          <BCol :md="formCreate.eh_tratamento ? 3 : 5">
            <label for="procValorBase" class="form-label">Valor(R$)</label>
            <input placeholder="Ex: 100.00" v-model="formCreate.valor" type="number" step="0.01" min="0" id="procValorBase" class="form-control" :class="{ 'is-invalid': formCreate.errors.valor }" />
            <div class="invalid-feedback">{{ formCreate.errors.valor }}</div>
          </BCol>
          <BCol md="3">
            <label for="procComissao" class="form-label">Comissão (%)</label>
            <input placeholder="Ex: 10.00" v-model="formCreate.comissao_percentual" type="number" step="0.01" min="0" max="100" id="procComissao" class="form-control" :class="{ 'is-invalid': formCreate.errors.comissao_percentual }" />
            <div class="invalid-feedback">{{ formCreate.errors.comissao_percentual }}</div>
          </BCol>
          <BCol md="2">
            <button type="button" class="btn btn-primary w-100" :disabled="formCreate.processing" @click="saveProcedimento">Adicionar</button>
          </BCol>
        </BRow>
      </div>
      <TableGrid
        :columns="columns"
        :data="tableData"
        :tableTitle="'Lista de Procedimentos'"
        :search="true"
        :searchPlaceholder="'Buscar por procedimento'"
        :showCheckbox="false"
        :showActions="true"
        :showStatus="true"
        :showPerPagination="true"
        :showAddButton="false"
        @delete="askDelete"
        @edit="startEditById"
        @show="openShow"
      />

      <Modal
        v-model="editModal"
        :title="'Editar Procedimento'"
        size="lg"
        :name-button="'Salvar'"
        :processing="formEdit.processing"
        @save="updateProcedimento"
      >
        <BRow class="g-3">
          <BCol md="6">
            <label for="procEditNome" class="form-label">Nome</label>
            <span class="text-danger ms-1">*</span>
            <input placeholder="Ex: Consulta Geral" v-model="formEdit.nome" type="text" id="procEditNome" class="form-control" :class="{ 'is-invalid': formEdit.errors.nome }" />
            <div class="invalid-feedback">{{ formEdit.errors.nome }}</div>
          </BCol>
          <BCol md="6">
            <label for="procEditCategoria" class="form-label">Categoria</label>
            <select data-choices v-model="formEdit.categoria_id" id="procEditCategoria" class="form-select" :class="{ 'is-invalid': formEdit.errors.categoria_id }">
              <option :value="null">Sem categoria</option>
              <option v-for="c in categoriasLocal" :key="c.id" :value="c.id">{{ c.nome }}</option>
            </select>
            <div class="invalid-feedback">{{ formEdit.errors.categoria_id }}</div>
          </BCol>
        </BRow>
        <BRow class="g-3 mt-0 align-items-end">
          <BCol md="3">
            <label for="procEditEhTrat" class="form-label">É Tratamento?</label>
            <select data-choices v-model="formEdit.eh_tratamento" id="procEditEhTrat" class="form-select" :class="{ 'is-invalid': formEdit.errors.eh_tratamento }">
              <option :value="false">Não</option>
              <option :value="true">Sim</option>
            </select>
            <div class="invalid-feedback">{{ formEdit.errors.eh_tratamento }}</div>
          </BCol>
          <BCol md="3" v-if="formEdit.eh_tratamento">
            <label for="procEditQSess" class="form-label">Qtd. Sessões</label>
            <input placeholder="Ex: 6" v-model="formEdit.quantidade_sessoes" type="number" min="1" id="procEditQSess" class="form-control" :class="{ 'is-invalid': formEdit.errors.quantidade_sessoes }" />
            <div class="invalid-feedback">{{ formEdit.errors.quantidade_sessoes }}</div>
          </BCol>
          <BCol :md="formEdit.eh_tratamento ? 3 : 5">
            <label for="procEditValorBase" class="form-label">Valor(R$)</label>
            <input placeholder="Ex: 100.00" v-model="formEdit.valor" type="number" step="0.01" min="0" id="procEditValorBase" class="form-control" :class="{ 'is-invalid': formEdit.errors.valor }" />
            <div class="invalid-feedback">{{ formEdit.errors.valor }}</div>
          </BCol>
          <BCol md="3">
            <label for="procEditComissao" class="form-label">Comissão (%)</label>
            <input placeholder="Ex: 10.00" v-model="formEdit.comissao_percentual" type="number" step="0.01" min="0" max="100" id="procEditComissao" class="form-control" :class="{ 'is-invalid': formEdit.errors.comissao_percentual }" />
            <div class="invalid-feedback">{{ formEdit.errors.comissao_percentual }}</div>
          </BCol>
        </BRow>
      </Modal>
      <ModalDelete
        v-model="deleteModal"
        :title="'Excluir Procedimento'"
        :subTitle="deleteSubTitle"
        :item-delete="procedimentoToDelete"
        @save="confirmDelete"
      />
    </BCardBody>
  </BCard>
  </template>
  <script setup>
  import { useForm, router } from "@inertiajs/vue3";
  import { ref, watch, computed } from "vue";
  import TableGrid from "@/Components/Tables/TableGrid.vue";
  import Modal from "@/Components/Modal.vue";
  import ModalDelete from "@/Components/ModalDelete.vue";
  const props = defineProps({
    procedimentos: { type: Array, default: () => [] },
    categoriasProcedimento: { type: Array, default: () => [] },
  });
  const procedimentosLocal = ref([...(props.procedimentos || [])]);
  const categoriasLocal = ref([...(props.categoriasProcedimento || [])]);
  watch(() => props.procedimentos, (v) => { procedimentosLocal.value = [...(v || [])]; });
  watch(() => props.categoriasProcedimento, (v) => { categoriasLocal.value = [...(v || [])]; });
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
  const formCreate = useForm({
    nome: "",
    categoria_id: null,
    eh_tratamento: false,
    quantidade_sessoes: null,
    valor: null,
    comissao_percentual: null,
  });
  function saveProcedimento() {
    formCreate.post("/procedimentos", {
      preserveScroll: true,
      onSuccess: () => {
        formCreate.reset();
        router.reload({ only: ['procedimentos'] });
      },
    });
  }
  const editingId = ref(null);
  const formEdit = useForm({
    nome: "",
    categoria_id: null,
    eh_tratamento: false,
    quantidade_sessoes: null,
    valor: null,
    comissao_percentual: null,
  });
  function startEdit(p) {
    editingId.value = p.id;
    formEdit.nome = p.nome || "";
    formEdit.categoria_id = p.categoria_id || null;
    formEdit.eh_tratamento = !!p.eh_tratamento;
    formEdit.quantidade_sessoes = p.quantidade_sessoes || null;
    formEdit.valor = p.valor || null;
    formEdit.comissao_percentual = p.comissao_percentual || null;
    editModal.value = true;
  }
  function startEditById(id) {
    const p = (procedimentosLocal.value || []).find(x => String(x.id) === String(id));
    if (!p) return;
    startEdit(p);
  }
  function updateProcedimento() {
    formEdit.put(`/procedimentos/${editingId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        editModal.value = false;
        formEdit.reset();
        editingId.value = null;
        router.reload({ only: ['procedimentos'] });
      },
    });
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
  </script>
