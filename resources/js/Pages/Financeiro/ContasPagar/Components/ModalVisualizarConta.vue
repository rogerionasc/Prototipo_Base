<template>
  <Modal v-model="isVisible" :title="modalTitle" size="lg" :showSave="showSaveButton" nameButton="Pagar Conta" cancelText="Fechar" @save="pagar">
    <div v-if="conta" class="d-flex flex-column gap-4">
      
      <!-- Cabeçalho de Status Rápido -->
      <div class="d-flex align-items-center justify-content-between p-3 rounded bg-light border border-dashed">
        <div class="d-flex align-items-center gap-3">
          <div class="avatar-sm flex-shrink-0">
            <span class="avatar-title rounded fs-3" 
                  :class="{'bg-success-subtle text-success': conta.status === 'Paga', 'bg-warning-subtle text-warning': conta.status === 'Pendente', 'bg-danger-subtle text-danger': conta.status === 'Vencida'}">
              <i class="ri-wallet-3-line" v-if="conta.status === 'Paga'"></i>
              <i class="ri-time-line" v-else-if="conta.status === 'Pendente'"></i>
              <i class="ri-error-warning-line" v-else></i>
            </span>
          </div>
          <div>
            <p class="text-muted text-uppercase fw-semibold fs-12 mb-1">Status da Parcela</p>
            <h5 class="mb-0 fs-15" :class="{'text-success': conta.status === 'Paga', 'text-warning': conta.status === 'Pendente', 'text-danger': conta.status === 'Vencida'}">
              {{ conta.status }}
            </h5>
          </div>
        </div>
        <div class="text-end">
          <p class="text-muted text-uppercase fw-semibold fs-12 mb-1">{{ conta.status === 'Paga' ? 'Valor Pago' : 'Valor Total' }}</p>
          <h4 class="mb-0 fw-bold" :class="conta.status === 'Paga' ? 'text-success' : 'text-primary'">
            {{ formatCurrency(conta.status === 'Paga' && conta.valor_pago !== null ? conta.valor_pago : conta.valor) }}
          </h4>
        </div>
      </div>

      <div class="row g-4">
        <!-- Detalhes da Despesa (Esquerda) -->
        <div class="col-lg-6 border-end-dashed">
          <h6 class="text-uppercase fw-bold text-muted fs-11 mb-3">Informações Gerais</h6>
          <ul class="list-unstyled mb-0 vstack gap-3">
            <li>
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0 text-muted">
                  <i class="ri-file-list-3-line fs-15 me-2 align-middle"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="fs-13 mb-0">Descrição</h6>
                  <p class="text-muted mb-0">{{ conta.descricao || 'N/A' }}</p>
                </div>
              </div>
            </li>
            <li>
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0 text-muted">
                  <i class="ri-price-tag-3-line fs-15 me-2 align-middle"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="fs-13 mb-0">Categoria</h6>
                  <p class="text-muted mb-0">{{ conta.categoria_nome || 'N/A' }}</p>
                </div>
              </div>
            </li>
          </ul>
        </div>

        <!-- Datas e Pagamentos (Direita) -->
        <div class="col-lg-6">
          <h6 class="text-uppercase fw-bold text-muted fs-11 mb-3">Cronograma Financeiro</h6>
          <ul class="list-unstyled mb-0 vstack gap-3">
            <li>
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0 text-muted">
                  <i class="ri-calendar-event-line fs-15 me-2 align-middle"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="fs-13 mb-0">Vencimento Original</h6>
                  <p class="mb-0 fw-medium" :class="conta.status === 'Paga' ? 'text-muted' : 'text-danger'">
                    {{ formatDateTime(conta.data_vencimento) }}
                  </p>
                </div>
              </div>
            </li>
            
            <li v-if="conta.status === 'Paga'">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0 text-muted">
                  <i class="ri-checkbox-circle-line fs-15 me-2 align-middle"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="fs-13 mb-0 text-muted">Data de Liquidação</h6>
                  <p class="fw-medium mb-0">{{ formatDateTimeWithTime(conta.data_pagamento) }}</p>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <!-- Anexos -->
      <div v-if="currentAnexo" class="mt-2">
         <h6 class="text-uppercase fw-bold text-muted fs-11 mb-3">Comprovante / Anexo</h6>
         
         <!-- Se for Imagem -->
         <div v-if="isImage" class="text-center border rounded p-2 bg-light">
            <img :src="'/storage/' + currentAnexo" alt="Comprovante" class="img-fluid rounded" style="max-height: 400px; object-fit: contain;">
            <div class="mt-2">
               <a :href="'/storage/' + currentAnexo" target="_blank" class="btn btn-sm btn-soft-primary">
                  <i class="ri-external-link-line align-middle me-1"></i> Abrir Original
               </a>
            </div>
         </div>

         <!-- Se for PDF -->
         <div v-else-if="isPdf" class="border rounded" style="height: 400px;">
            <iframe :src="'/storage/' + currentAnexo" width="100%" height="100%" style="border: none;"></iframe>
            <div class="text-center p-2 bg-light border-top">
               <a :href="'/storage/' + currentAnexo" target="_blank" class="btn btn-sm btn-soft-primary">
                  <i class="ri-external-link-line align-middle me-1"></i> Abrir PDF em Nova Aba
               </a>
            </div>
         </div>

         <!-- Outros Arquivos -->
         <div v-else class="p-3 border rounded bg-primary-subtle d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-xs">
                    <div class="avatar-title bg-primary rounded fs-18">
                        <i class="ri-file-text-fill"></i>
                    </div>
                </div>
                <div>
                    <h6 class="fs-14 fw-semibold text-primary mb-1">Documento em Anexo</h6>
                    <p class="text-muted fs-12 mb-0">Arquivo disponível para download.</p>
                </div>
            </div>
            <a :href="'/storage/' + currentAnexo" target="_blank" class="btn btn-primary btn-sm">
               Download <i class="ri-download-2-line ms-1 align-bottom"></i>
            </a>
         </div>
      </div>

    </div>
  </Modal>
</template>

<script setup>
import { ref, computed } from 'vue';
import Modal from '@/Components/modal.vue';

const emit = defineEmits(['cancelar', 'pagar']);
const isVisible = ref(false);
const conta = ref(null);

const modalTitle = computed(() => {
    return 'Detalhes do pagamento';
});

const currentAnexo = computed(() => {
    return conta.value ? (conta.value.anexo || conta.value.despesa_anexo) : null;
});

const isImage = computed(() => {
    if (!currentAnexo.value) return false;
    const ext = currentAnexo.value.split('.').pop().toLowerCase();
    return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
});

const isPdf = computed(() => {
    if (!currentAnexo.value) return false;
    const ext = currentAnexo.value.split('.').pop().toLowerCase();
    return ext === 'pdf';
});

const showSaveButton = computed(() => {
    return conta.value ? conta.value.status !== 'Paga' : false;
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
};

const formatDateTime = (dateString) => {
    if (!dateString) return '';
    // If it's a full datetime string (e.g. "2026-09-12 15:30:00"), extract just the date part
    let datePart = dateString;
    if (dateString.includes(' ')) {
        datePart = dateString.split(' ')[0];
    } else if (dateString.includes('T')) {
        datePart = dateString.split('T')[0];
    }
    const date = new Date(datePart + 'T12:00:00Z');
    return date.toLocaleDateString('pt-BR');
};

const formatDateTimeWithTime = (dateString) => {
    if (!dateString) return '';
    let isoString = dateString.replace(' ', 'T');
    const date = new Date(isoString);
    if (isNaN(date.getTime())) return formatDateTime(dateString);
    
    return date.toLocaleDateString('pt-BR') + ' às ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
};

const show = (data) => {
    console.log("ModalVisualizarConta show() chamado com:", data);
    conta.value = data;
    isVisible.value = true;
};

const cancelar = () => {
    isVisible.value = false;
    emit('cancelar', conta.value);
};

const pagar = () => {
    isVisible.value = false;
    emit('pagar', conta.value);
};

defineExpose({ show });
</script>
