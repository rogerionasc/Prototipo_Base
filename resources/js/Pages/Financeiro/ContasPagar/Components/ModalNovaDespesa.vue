<template>
  <Modal v-model="isVisible" :title="form.id ? 'Editar Despesa' : 'Nova Despesa'" size="xl" :nameButton="form.id ? 'Salvar Alterações' : 'Salvar e Gerar Contas'" :processing="form.processing" @save="save">
    <form @submit.prevent="save" v-if="isVisible">
      <div class="row g-3">
        <!-- Dados Principais -->
        <div class="col-md-12">
          <h6 class="fw-semibold text-uppercase text-muted border-bottom pb-2">Dados da Despesa</h6>
        </div>
        <div class="col-md-6">
          <label class="form-label">Categoria</label>
          <select class="form-select" data-choices v-model="form.categoria_id">
            <option value="" disabled>Selecione...</option>
            <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.descricao }}</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Descrição / Histórico</label>
          <input type="text" class="form-control" placeholder="Ex: Conta de Luz, Material de Escritório..." v-model="form.descricao">
        </div>
        <div class="col-md-4">
          <label class="form-label">Valor Total</label>
          <input type="number" step="0.01" class="form-control" v-model="form.valor">
        </div>
        <div class="col-md-4" v-if="form.id">
          <label class="form-label">Status</label>
          <select class="form-select" data-choices v-model="form.status">
            <option value="Pendente">Pendente</option>
            <option value="Agendada">Agendada</option>
            <option value="Cancelada">Cancelada</option>
          </select>
        </div>

        <!-- Datas -->
        <div class="col-md-4">
          <label class="form-label">Data Competência</label>
          <flatPickr v-model="form.data_competencia" :config="flatpickrConfig" class="form-control" placeholder="Selecione a data" />
        </div>
        <div class="col-md-4">
          <label class="form-label">Vencimento</label>
          <flatPickr v-model="form.data_vencimento" :config="flatpickrConfig" class="form-control" placeholder="Selecione a data" />
        </div>

        <!-- Recorrência -->
        <div class="col-md-12 mt-4">
          <h6 class="fw-semibold text-uppercase text-muted border-bottom pb-2">Recorrência</h6>
        </div>
        <div class="col-md-12">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="switchRecorrente" v-model="form.is_recorrente">
            <label class="form-check-label" for="switchRecorrente">É uma despesa recorrente?</label>
          </div>
        </div>


      </div>
    </form>
  </Modal>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
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
    categorias: { type: Array, default: () => [] }
});

const emit = defineEmits(['saved']);
const isVisible = ref(false);
const fileInput = ref(null);

const form = useForm({
    id: null,
    categoria_id: '',
    descricao: '',
    valor: 0,
    data_competencia: '',
    data_vencimento: '',
    is_recorrente: false,
    status: 'Pendente',
    files: [],
    _method: 'post'
});

const files = ref([]);

watch(files, (newVal) => {
    form.files = newVal;
}, { deep: true });

const show = (data = null) => {
    form.reset();
    form.clearErrors();
    files.value = [];
    
    if (data) {
        form.id = data.id;
        form.categoria_id = data.categoria_id;
        form.descricao = data.descricao;
        form.valor = data.valor;
        form.data_competencia = data.data_competencia || '';
        form.data_vencimento = data.data_vencimento || '';
        form.is_recorrente = !!data.is_recorrente;
        form.status = data.status || 'Pendente';
        form._method = 'put';
    } else {
        form.id = null;
        form._method = 'post';
    }
    
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
    const url = form.id ? `/contas-pagar/${form.id}` : '/contas-pagar';
    
    form.post(url, {
        onSuccess: () => {
            emit('saved');
            isVisible.value = false;
            form.reset();
            form.clearErrors();
            files.value = [];
        },
        preserveScroll: true
    });
};

defineExpose({ show });
</script>
