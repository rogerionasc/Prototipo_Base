<template>
  <Modal v-model="isVisible" title="Editar Conta a Pagar" size="lg" nameButton="Salvar Alterações" @save="save">
    <form @submit.prevent="save">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Fornecedor</label>
          <input type="text" class="form-control" v-model="form.fornecedor_id">
        </div>
        <div class="col-md-6">
          <label class="form-label">Descrição</label>
          <input type="text" class="form-control" v-model="form.descricao">
        </div>
        <div class="col-md-4">
          <label class="form-label">Vencimento</label>
          <input type="date" class="form-control" v-model="form.data_vencimento">
        </div>
        <div class="col-md-4">
          <label class="form-label">Valor Base</label>
          <input type="number" step="0.01" class="form-control" v-model="form.valor_base">
        </div>
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select class="form-select" v-model="form.status">
            <option value="Pendente">Pendente</option>
            <option value="Agendada">Agendada</option>
            <option value="Cancelada">Cancelada</option>
          </select>
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

const form = ref({
    id: null,
    fornecedor_id: '',
    descricao: '',
    data_vencimento: '',
    valor_base: 0,
    status: 'Pendente'
});

const show = (data) => {
    form.value = { ...data };
    isVisible.value = true;
};

const save = () => {
    console.log("Salvando Conta a Pagar:", form.value);
    emit('saved');
    isVisible.value = false;
};

defineExpose({ show });
</script>
