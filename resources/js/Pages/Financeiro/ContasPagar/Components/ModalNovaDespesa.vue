<template>
  <Modal v-model="isVisible" title="Nova Despesa" size="xl" nameButton="Salvar e Gerar Contas" @save="save">
    <form @submit.prevent="save">
      <div class="row g-3">
        <!-- Dados Principais -->
        <div class="col-md-12">
          <h6 class="fw-semibold text-uppercase text-muted border-bottom pb-2">Dados da Despesa</h6>
        </div>
        <div class="col-md-6">
          <label class="form-label">Fornecedor</label>
          <input type="text" class="form-control" v-model="form.fornecedor_id" placeholder="Ex: CEMIG, Fornecedor S/A">
        </div>
        <div class="col-md-3">
          <label class="form-label">Categoria</label>
          <input type="text" class="form-control" v-model="form.categoria_id" placeholder="Ex: Energia">
        </div>
        <div class="col-md-3">
          <label class="form-label">Centro de Custo</label>
          <input type="text" class="form-control" v-model="form.centro_custo_id" placeholder="Ex: Administrativo">
        </div>
        <div class="col-md-6">
          <label class="form-label">Descrição / Histórico</label>
          <input type="text" class="form-control" v-model="form.descricao">
        </div>
        <div class="col-md-3">
          <label class="form-label">Documento / NF</label>
          <input type="text" class="form-control" v-model="form.documento_nf">
        </div>
        <div class="col-md-3">
          <label class="form-label">Valor Total</label>
          <input type="number" step="0.01" class="form-control" v-model="form.valor_total">
        </div>

        <!-- Datas -->
        <div class="col-md-4">
          <label class="form-label">Data Emissão</label>
          <input type="date" class="form-control" v-model="form.data_emissao">
        </div>
        <div class="col-md-4">
          <label class="form-label">Data Competência</label>
          <input type="date" class="form-control" v-model="form.data_competencia">
        </div>
        <div class="col-md-4">
          <label class="form-label">1º Vencimento</label>
          <input type="date" class="form-control" v-model="form.data_vencimento">
        </div>

        <!-- Recorrência e Parcelamento -->
        <div class="col-md-12 mt-4">
          <h6 class="fw-semibold text-uppercase text-muted border-bottom pb-2">Parcelamento e Recorrência</h6>
        </div>
        <div class="col-md-4">
          <label class="form-label">Nº de Parcelas</label>
          <input type="number" min="1" class="form-control" v-model="form.parcelas">
          <small class="text-muted">Gera N contas a pagar.</small>
        </div>
        <div class="col-md-4">
          <label class="form-label">Recorrência</label>
          <select class="form-select" v-model="form.recorrencia_tipo">
            <option value="">Nenhuma</option>
            <option value="mensal">Mensal</option>
            <option value="semanal">Semanal</option>
            <option value="anual">Anual</option>
          </select>
        </div>
        <div class="col-md-4" v-if="form.recorrencia_tipo">
          <label class="form-label">Fim da Recorrência</label>
          <input type="date" class="form-control" v-model="form.recorrencia_fim">
        </div>

        <!-- Drag and Drop Anexos -->
        <div class="col-md-12 mt-4">
          <h6 class="fw-semibold text-uppercase text-muted border-bottom pb-2">Anexos / Documentos</h6>
          <div class="p-5 border border-dashed rounded text-center bg-light" 
                @dragover.prevent 
                @drop.prevent="onFileDrop"
                style="cursor: pointer;"
                @click="triggerFileInput">
            <i class="ri-upload-cloud-2-line fs-1 mb-2 text-muted"></i>
            <p class="mb-0 text-muted">Arraste e solte o comprovante ou NF aqui, ou <strong>clique para selecionar</strong>.</p>
            <input type="file" ref="fileInput" class="d-none" multiple @change="onFileSelect">
          </div>
          <div class="mt-3 d-flex flex-wrap gap-2" v-if="files.length > 0">
              <div v-for="(file, index) in files" :key="index" class="badge bg-primary fs-12 p-2">
                {{ file.name }} <i class="ri-close-line ms-1 cursor-pointer" @click.stop="removeFile(index)"></i>
              </div>
          </div>
        </div>
      </div>
    </form>
  </Modal>
</template>

<script setup>
import { ref } from 'vue';
import Modal from '@/Components/modal.vue';

const emit = defineEmits(['saved']);
const isVisible = ref(false);
const fileInput = ref(null);

const form = ref({
    fornecedor_id: '',
    categoria_id: '',
    centro_custo_id: '',
    descricao: '',
    documento_nf: '',
    valor_total: 0,
    data_emissao: '',
    data_competencia: '',
    data_vencimento: '',
    parcelas: 1,
    recorrencia_tipo: '',
    recorrencia_fim: ''
});

const files = ref([]);

const show = () => {
    form.value = {
        fornecedor_id: '', categoria_id: '', centro_custo_id: '', descricao: '', documento_nf: '',
        valor_total: 0, data_emissao: '', data_competencia: '', data_vencimento: '',
        parcelas: 1, recorrencia_tipo: '', recorrencia_fim: ''
    };
    files.value = [];
    isVisible.value = true;
};

const triggerFileInput = () => {
    fileInput.value.click();
};

const onFileSelect = (e) => {
    const selected = Array.from(e.target.files);
    files.value = [...files.value, ...selected];
};

const onFileDrop = (e) => {
    const dropped = Array.from(e.dataTransfer.files);
    files.value = [...files.value, ...dropped];
};

const removeFile = (index) => {
    files.value.splice(index, 1);
};

const save = () => {
    console.log("Salvando despesa:", form.value);
    console.log("Anexos:", files.value);
    emit('saved');
    isVisible.value = false;
};

defineExpose({ show });
</script>
