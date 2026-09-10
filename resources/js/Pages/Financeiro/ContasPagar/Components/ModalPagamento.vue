<template>
  <Modal v-model="isVisible" title="Registrar Pagamento / Baixa" size="lg" nameButton="Registrar Pagamento" @save="save">
    <form @submit.prevent="save">
      <div class="row g-3">
        <div class="col-md-12">
          <div class="alert alert-info">
              <strong>Conta:</strong> {{ form.conta_id }} - Parcela {{ form.parcela_num }}<br>
              <strong>Fornecedor:</strong> {{ form.fornecedor_id }}<br>
              <strong>Valor Base:</strong> {{ formatCurrency(form.valor_base) }}
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Data do Pagamento</label>
          <input type="date" class="form-control" v-model="form.data_pagamento">
        </div>
        <div class="col-md-6">
          <label class="form-label">Conta Bancária / Caixa de Origem</label>
          <input type="text" class="form-control" v-model="form.conta_bancaria_id" placeholder="Ex: Conta Banco do Brasil">
        </div>
        <div class="col-md-4">
          <label class="form-label">Juros (R$)</label>
          <input type="number" step="0.01" class="form-control" v-model="form.juros">
        </div>
        <div class="col-md-4">
          <label class="form-label">Multa (R$)</label>
          <input type="number" step="0.01" class="form-control" v-model="form.multa">
        </div>
        <div class="col-md-4">
          <label class="form-label">Desconto (R$)</label>
          <input type="number" step="0.01" class="form-control" v-model="form.desconto">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-bold text-primary">Valor a Pagar (Líquido)</label>
          <input type="text" class="form-control" disabled :value="formatCurrency(valorLiquido)">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-bold text-success">Valor Efetivamente Pago</label>
          <input type="number" step="0.01" class="form-control" v-model="form.valor_pago">
          <small class="text-muted">Altere para registrar pagamento parcial.</small>
        </div>
        
        <!-- Drag and Drop Anexos -->
        <div class="col-md-12 mt-4">
          <h6 class="fw-semibold text-uppercase text-muted border-bottom pb-2">Comprovante de Pagamento</h6>
          <div class="p-4 border border-dashed rounded text-center bg-light" 
                @dragover.prevent 
                @drop.prevent="onFileDrop"
                style="cursor: pointer;"
                @click="triggerFileInput">
            <i class="ri-upload-cloud-2-line fs-1 mb-2 text-muted"></i>
            <p class="mb-0 text-muted">Arraste e solte o comprovante de pagamento aqui, ou <strong>clique para selecionar</strong>.</p>
            <input type="file" ref="fileInput" class="d-none" multiple @change="onFileSelect">
          </div>
          <div class="mt-3 d-flex flex-wrap gap-2" v-if="files.length > 0">
              <div v-for="(file, index) in files" :key="index" class="badge bg-success fs-12 p-2">
                {{ file.name }} <i class="ri-close-line ms-1 cursor-pointer" @click.stop="removeFile(index)"></i>
              </div>
          </div>
        </div>
      </div>
    </form>
  </Modal>
</template>

<script setup>
import { ref, computed } from 'vue';
import Modal from '@/Components/modal.vue';

const emit = defineEmits(['saved']);
const isVisible = ref(false);
const fileInput = ref(null);

const form = ref({
    conta_id: null,
    parcela_num: 1,
    fornecedor_id: '',
    valor_base: 0,
    data_pagamento: new Date().toISOString().split('T')[0],
    conta_bancaria_id: '',
    juros: 0,
    multa: 0,
    desconto: 0,
    valor_pago: 0
});

const files = ref([]);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
};

const valorLiquido = computed(() => {
    const base = parseFloat(form.value.valor_base) || 0;
    const jur = parseFloat(form.value.juros) || 0;
    const mult = parseFloat(form.value.multa) || 0;
    const desc = parseFloat(form.value.desconto) || 0;
    return base + jur + mult - desc;
});

const show = (conta) => {
    form.value = {
        conta_id: conta.id,
        parcela_num: conta.parcela_num,
        fornecedor_id: conta.fornecedor_id,
        valor_base: conta.valor_base,
        data_pagamento: new Date().toISOString().split('T')[0],
        conta_bancaria_id: '',
        juros: 0,
        multa: 0,
        desconto: 0,
        valor_pago: conta.valor_base
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
    console.log("Registrando Pagamento:", form.value);
    console.log("Comprovantes anexados:", files.value);
    emit('saved');
    isVisible.value = false;
};

defineExpose({ show });
</script>
