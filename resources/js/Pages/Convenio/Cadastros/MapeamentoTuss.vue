<template>
  <div>
    <TableGrid 
      ref="mapeamentosGridRef" 
      :serverUrl="route('tuss_mapeamentos.index')" 
      :columns="columns" 
      :search="true"
      :searchPlaceholder="'Buscar mapeamento...'"
      :showCheckbox="true" 
      :showActions="true"
      :showMultiDelete="false"
      :showAddButton="true"
      :addButtonText="'Adicionar Mapeamento'"
      :addButtonIconClass="'ri-add-line'"
      @add="openAddModal"
      :actionsConfig="{ edit: true, show: false, delete: true }"
      @delete="deleteMapeamento"
      @edit="editMapeamento"
      :tableTitle="'Mapeamentos Cadastrados'"
      :compactSpacing="true"
    >
      <template #custom-actions="{ selectedRows }">
        <button 
          v-if="selectedRows && selectedRows.length > 0"
          type="button" 
          class="btn btn-primary ms-2"
          @click="assignMapeamentos(selectedRows)"
        >
          <i class="ri-links-line align-bottom me-1"></i> Atribuir Selecionados
        </button>
      </template>
    </TableGrid>

    <!-- Modal para adicionar mapeamento -->
    <Teleport to="body">
      <Modal v-model="showAddModal" :title="isEditing ? 'Editar Mapeamento' : 'Adicionar Mapeamento'" :name-button="'Salvar'" :zIndex="1060"
        :backdropZIndex="1055" @save="saveMapeamento" :disabled="loading">
        <div v-show="isDataLoading" class="placeholder-wave row g-3">
          <div class="col-12">
            <h6 class="fw-semibold text-primary mb-1 border-bottom pb-2">Origem</h6>
          </div>
          <div class="col-md-4">
            <label class="form-label">Tabela de Origem</label>
            <div class="placeholder col-12 rounded" style="height: 38px; background-color: #adb5bd;"></div>
          </div>
          <div class="col-md-8">
            <label class="form-label">Procedimento de Origem</label>
            <div class="placeholder col-12 rounded" style="height: 38px; background-color: #adb5bd;"></div>
          </div>
          <div class="col-12 mt-4">
            <h6 class="fw-semibold text-primary mb-1 border-bottom pb-2">Referência</h6>
          </div>
          <div class="col-md-4">
            <label class="form-label">Tabela de Referência</label>
            <div class="placeholder col-12 rounded" style="height: 38px; background-color: #adb5bd;"></div>
          </div>
          <div class="col-md-8">
            <label class="form-label">Procedimento de Referência</label>
            <div class="placeholder col-12 rounded" style="height: 38px; background-color: #adb5bd;"></div>
          </div>
        </div>
        <div v-show="!isDataLoading" class="row g-3">
          <div class="col-12">
            <h6 class="fw-semibold text-primary mb-1 border-bottom pb-2">Origem</h6>
          </div>
          <div class="col-md-4">
            <label class="form-label">Tabela de Origem</label>
            <select ref="tabelaOrigemSelect" class="form-select">
              <option value="">Selecione...</option>
              <option v-for="t in allowedTabelas" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>
          <div class="col-md-8">
            <label class="form-label">Procedimento de Origem</label>
            <select ref="origemSelect" class="form-select" data-choices></select>
          </div>

          <div class="col-12 mt-4">
            <h6 class="fw-semibold text-primary mb-1 border-bottom pb-2">Referência</h6>
          </div>
          <div class="col-md-4">
            <label class="form-label">Tabela de Referência</label>
            <select ref="tabelaReferenciaSelect" class="form-select">
              <option value="">Selecione...</option>
              <option v-for="t in allowedTabelas" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>
          <div class="col-md-8">
            <label class="form-label">Procedimento de Referência</label>
            <select ref="referenciaSelect" class="form-select" data-choices></select>
          </div>
        </div>
      </Modal>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onBeforeUnmount, watch, nextTick } from 'vue';
import Choices from 'choices.js';
import axios from 'axios';
import TableGrid from '@/Components/Tables/TableGrid.vue';
import Modal from '@/Components/Modal.vue';
import Swal from 'sweetalert2';

const emit = defineEmits(['assign', 'delete']);

const allowedTabelas = ['AMB1990', 'AMB1992', 'AMB1993', 'AMB1999', 'CBHPM3', 'CBHPM4', 'CBHPM5', 'TUSS'];

const showAddModal = ref(false);
const isEditing = ref(false);
const isDataLoading = ref(false);
const editId = ref(null);

const tabelaOrigemSelect = ref(null);
const tabelaReferenciaSelect = ref(null);
const origemSelect = ref(null);
const referenciaSelect = ref(null);
const mapeamentosGridRef = ref(null);

let tabelaOrigemChoices = null;
let tabelaReferenciaChoices = null;
let origemChoices = null;
let referenciaChoices = null;

const loading = ref(false);

const form = ref({
  origem_procedimento_id: '',
  referencia_procedimento_id: '',
});

const columns = [
  { id: 'id', name: 'ID', sort: false, attributes: { style: 'width: 60px;' } },
  { id: 'codigo', name: 'Código' },
  { id: 'descricao', name: 'Descrição' },
  { id: 'quantidade_ch', name: 'Quantidade CH' },
  { id: 'quantidade_co', name: 'Quantidade CO' }
];

const loadProcedimentos = async (tabela, choicesInstance) => {
  if (!tabela) {
    choicesInstance.clearChoices();
    choicesInstance.setChoices([{ value: '', label: 'Selecione uma tabela primeiro...', disabled: true, selected: true }], 'value', 'label', true);
    return;
  }
  
  choicesInstance.clearChoices();
  choicesInstance.setChoices([{ value: '', label: 'Carregando...', disabled: true, selected: true }], 'value', 'label', true);
  
  try {
    const response = await axios.get(route('tuss.list', { tabela: tabela, limit: 100 }));
    const data = response.data.data || response.data;
    
    if (data.length === 0) {
      choicesInstance.setChoices([{ value: '', label: 'Nenhum procedimento encontrado', disabled: true, selected: true }], 'value', 'label', true);
      return;
    }

    const choices = data.map(item => ({
      value: String(item.id),
      label: `${item.codigo} - ${item.descricao}`,
    }));
    choices.unshift({ value: '', label: 'Selecione o procedimento...', disabled: true, selected: true });
    
    choicesInstance.setChoices(choices, 'value', 'label', true);
  } catch (error) {
    console.error(error);
    choicesInstance.setChoices([{ value: '', label: 'Erro ao carregar', disabled: true, selected: true }], 'value', 'label', true);
  }
};

watch(showAddModal, async (val) => {
  if (val) {
    await nextTick();
    
    // Iniciar Choices para Tabelas
    if (!tabelaOrigemChoices && tabelaOrigemSelect.value) {
      tabelaOrigemChoices = new Choices(tabelaOrigemSelect.value, {
        searchEnabled: false,
        shouldSort: false,
        itemSelectText: '',
      });
      tabelaOrigemSelect.value.addEventListener('change', (e) => {
        form.value.origem_procedimento_id = '';
        const val = e.detail ? e.detail.value : e.target.value;
        loadProcedimentos(val, origemChoices);
      });
    }

    if (!tabelaReferenciaChoices && tabelaReferenciaSelect.value) {
      tabelaReferenciaChoices = new Choices(tabelaReferenciaSelect.value, {
        searchEnabled: false,
        shouldSort: false,
        itemSelectText: '',
      });
      tabelaReferenciaSelect.value.addEventListener('change', (e) => {
        form.value.referencia_procedimento_id = '';
        const val = e.detail ? e.detail.value : e.target.value;
        loadProcedimentos(val, referenciaChoices);
      });
    }

    // Iniciar Choices para Procedimentos
    if (!origemChoices && origemSelect.value) {
      origemChoices = origemSelect.value?._choicesInstance || origemSelect.value?.choices || new Choices(origemSelect.value, {
        searchEnabled: true,
        shouldSort: false,
        itemSelectText: '',
        noResultsText: 'Nenhum encontrado',
      });
      origemChoices.clearChoices();
      origemChoices.setChoices([{ value: '', label: 'Selecione uma tabela primeiro...', disabled: true, selected: true }], 'value', 'label', true);
      origemSelect.value.addEventListener('change', (e) => form.value.origem_procedimento_id = e.detail ? e.detail.value : e.target.value);
    }

    if (!referenciaChoices && referenciaSelect.value) {
      referenciaChoices = referenciaSelect.value?._choicesInstance || referenciaSelect.value?.choices || new Choices(referenciaSelect.value, {
        searchEnabled: true,
        shouldSort: false,
        itemSelectText: '',
        noResultsText: 'Nenhum encontrado',
      });
      referenciaChoices.clearChoices();
      referenciaChoices.setChoices([{ value: '', label: 'Selecione uma tabela primeiro...', disabled: true, selected: true }], 'value', 'label', true);
      referenciaSelect.value.addEventListener('change', (e) => form.value.referencia_procedimento_id = e.detail ? e.detail.value : e.target.value);
    }
  }
});

onBeforeUnmount(() => {
  if (tabelaOrigemChoices) tabelaOrigemChoices.destroy();
  if (tabelaReferenciaChoices) tabelaReferenciaChoices.destroy();
  if (origemChoices) origemChoices.destroy();
  if (referenciaChoices) referenciaChoices.destroy();
});

const assignMapeamentos = (selectedIds) => {
  const selectedData = mapeamentosGridRef.value?.getSelectedRowObjects?.() || [];
  emit('assign', selectedData);
  try { mapeamentosGridRef.value?.clearSelection?.(); } catch(e) {}
};

const openAddModal = () => {
  isEditing.value = false;
  editId.value = null;
  form.value.origem_procedimento_id = '';
  form.value.referencia_procedimento_id = '';
  
  if (tabelaOrigemChoices) tabelaOrigemChoices.setChoiceByValue('');
  if (tabelaReferenciaChoices) tabelaReferenciaChoices.setChoiceByValue('');
  
  if (origemChoices) {
    origemChoices.clearChoices();
    origemChoices.setChoices([{ value: '', label: 'Selecione uma tabela primeiro...', disabled: true, selected: true }], 'value', 'label', true);
  }
  if (referenciaChoices) {
    referenciaChoices.clearChoices();
    referenciaChoices.setChoices([{ value: '', label: 'Selecione uma tabela primeiro...', disabled: true, selected: true }], 'value', 'label', true);
  }
  
  showAddModal.value = true;
};

const editMapeamento = async (id, row) => {
  isEditing.value = true;
  editId.value = id;
  isDataLoading.value = true;
  
  form.value.origem_procedimento_id = row.origem_procedimento_id;
  form.value.referencia_procedimento_id = row.referencia_procedimento_id;
  
  showAddModal.value = true;
  
  setTimeout(async () => {
    // Set tabela for origem
    if (tabelaOrigemChoices && row.origem_procedimento?.tabela) {
      tabelaOrigemChoices.setChoiceByValue(row.origem_procedimento.tabela);
      await loadProcedimentos(row.origem_procedimento.tabela, origemChoices);
      origemChoices.setChoices([{
        value: String(row.origem_procedimento_id),
        label: `${row.origem_procedimento.codigo} - ${row.origem_procedimento.descricao}`
      }], 'value', 'label', false);
      origemChoices.setChoiceByValue(String(row.origem_procedimento_id));
    }
    
    // Set tabela for referencia
    if (tabelaReferenciaChoices && row.referencia_procedimento?.tabela) {
      tabelaReferenciaChoices.setChoiceByValue(row.referencia_procedimento.tabela);
      await loadProcedimentos(row.referencia_procedimento.tabela, referenciaChoices);
      referenciaChoices.setChoices([{
        value: String(row.referencia_procedimento_id),
        label: `${row.referencia_procedimento.codigo} - ${row.referencia_procedimento.descricao}`
      }], 'value', 'label', false);
      referenciaChoices.setChoiceByValue(String(row.referencia_procedimento_id));
    }
    
    isDataLoading.value = false;
  }, 150);
};

const saveMapeamento = async () => {
  if (!form.value.origem_procedimento_id || !form.value.referencia_procedimento_id) {
    window.dispatchEvent(new CustomEvent('flash:show', { detail: { type: 'warning', message: 'Selecione os procedimentos de origem e referência.' } }));
    return;
  }

  loading.value = true;
  try {
    if (isEditing.value) {
      await axios.put(route('tuss_mapeamentos.update', editId.value), form.value);
    } else {
      await axios.post(route('tuss_mapeamentos.store'), form.value);
    }
    
    window.dispatchEvent(new CustomEvent('flash:show', { detail: { type: 'success', message: 'Mapeamento salvo com sucesso.' } }));
    if (mapeamentosGridRef.value) {
      mapeamentosGridRef.value.reload();
    }
    // Fechar modal e limpar
    showAddModal.value = false;
    form.value.origem_procedimento_id = '';
    form.value.referencia_procedimento_id = '';
    
    if (tabelaOrigemChoices) tabelaOrigemChoices.setChoiceByValue('');
    if (tabelaReferenciaChoices) tabelaReferenciaChoices.setChoiceByValue('');
    if (origemChoices) {
      origemChoices.clearChoices();
      origemChoices.setChoices([{ value: '', label: 'Selecione uma tabela primeiro...', disabled: true, selected: true }], 'value', 'label', true);
    }
    if (referenciaChoices) {
      referenciaChoices.clearChoices();
      referenciaChoices.setChoices([{ value: '', label: 'Selecione uma tabela primeiro...', disabled: true, selected: true }], 'value', 'label', true);
    }
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Erro ao salvar mapeamento.';
    window.dispatchEvent(new CustomEvent('flash:show', { detail: { type: 'danger', message: errorMsg } }));
  } finally {
    loading.value = false;
  }
};

const deleteMapeamento = async (id) => {
  const result = await Swal.fire({
    title: 'Tem certeza?',
    text: "Deseja realmente excluir este mapeamento?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Sim, excluir!',
    cancelButtonText: 'Cancelar'
  });

  if (result.isConfirmed) {
    try {
      await axios.delete(route('tuss_mapeamentos.destroy', id));
      window.dispatchEvent(new CustomEvent('flash:show', { detail: { type: 'success', message: 'Mapeamento removido.' } }));
      if (mapeamentosGridRef.value) {
        mapeamentosGridRef.value.reload();
      }
    } catch (error) {
      window.dispatchEvent(new CustomEvent('flash:show', { detail: { type: 'danger', message: 'Erro ao remover mapeamento.' } }));
    }
  }
};
</script>

<style scoped>
:deep(.choices__item) {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
