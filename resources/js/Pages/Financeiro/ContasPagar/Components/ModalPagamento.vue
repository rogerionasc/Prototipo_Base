<template>
  <Modal v-model="isVisible" title="Registrar Pagamento / Baixa" size="lg" nameButton="Registrar Pagamento" :processing="form.processing" @save="save">
    <form @submit.prevent="save" v-if="isVisible">
      <div class="row g-3">
        <div class="col-md-12">
          <div class="alert alert-info">
              <strong>Conta:</strong> {{ form.conta_id }}<br>
              <strong>Categoria:</strong> {{ form.categoria }}<br>
              <strong>Valor:</strong> {{ formatCurrency(form.valor) }}
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Data do Pagamento</label>
          <flatPickr v-model="form.data_pagamento" :config="flatpickrConfig" :class="{'form-control': true, 'is-invalid': form.errors.data_pagamento}" placeholder="Selecione a data" />
          <div class="invalid-feedback" v-if="form.errors.data_pagamento">{{ form.errors.data_pagamento }}</div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Conta Bancária / Caixa de Origem</label>
          <select data-choices :class="{'form-select': true, 'is-invalid': form.errors.conta_bancaria_id}" v-model="form.conta_bancaria_id">
            <option value="" disabled>Selecione o banco / origem...</option>
            <option v-for="b in bancos" :key="b.id" :value="b.id">
                {{ b.provedor }}
            </option>
          </select>
          <div class="invalid-feedback" v-if="form.errors.conta_bancaria_id">{{ form.errors.conta_bancaria_id }}</div>
        </div>
      </div>
      <div class="row g-3 mt-1">
        <div class="col-md-6">
          <label class="form-label">Valor Efetivamente Pago</label>
          <div class="input-group">
            <span class="input-group-text">R$</span>
            <input type="number" step="0.01" :class="{'form-control': true, 'is-invalid': form.errors.valor_pago}" v-model="form.valor_pago">
          </div>
          <div class="text-danger small mt-1" v-if="form.errors.valor_pago">{{ form.errors.valor_pago }}</div>
          <small class="text-muted">Informe o valor exato pago.</small>
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
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/modal.vue';

import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import { Portuguese } from 'flatpickr/dist/l10n/pt.js';

const flatpickrConfig = {
    locale: Portuguese,
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "d M, Y"
};

const props = defineProps({
    bancos: { type: Array, default: () => [] }
});

const emit = defineEmits(['saved']);
const isVisible = ref(false);
const fileInput = ref(null);

const form = useForm({
    conta_id: null,
    categoria: '',
    valor: 0,
    data_pagamento: new Date().toISOString().split('T')[0],
    conta_bancaria_id: '',
    valor_pago: 0,
    files: []
});

const files = ref([]);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
};

import { watch } from 'vue';

watch(files, (newVal) => {
    form.files = newVal;
}, { deep: true });

const show = (conta) => {
    form.reset();
    form.clearErrors();
    if (fileInput.value) fileInput.value.value = null;
    
    form.conta_id = conta.id;
    form.categoria = conta.categoria_nome || '';
    form.valor = conta.valor;
    form.data_pagamento = new Date().toISOString().split('T')[0];
    form.conta_bancaria_id = '';
    form.valor_pago = conta.valor;
    form.files = [];
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
    form.files = files.value;
    form.post(route('financeiro.contas_pagar.pagar', form.conta_id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            emit('saved');
            isVisible.value = false;
            form.reset();
            form.clearErrors();
            files.value = [];
            if (fileInput.value) fileInput.value.value = null;
        }
    });
};

defineExpose({ show });
</script>
