<template>
  <Layout>

    <Head title="Contas a Pagar e Despesas" />
    <PageHeader title="Contas a Pagar e Despesas" pageTitle="Financeiro" />

    <!-- Top KPIs -->
    <div class="row mb-4">
      <div class="col">
        <div class="card shadow-sm border-0 h-100" style="border-left: 4px solid var(--vz-warning) !important;">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">Total Pendente</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="text-warning">{{
                  formatCurrency(props.kpis?.totalPendente || 0) }}</span></h4>
              </div>
              <div class="avatar-sm flex-shrink-0">
                <span class="avatar-title bg-warning-subtle text-warning rounded fs-3">
                  <i class="bx bx-time-five"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="card shadow-sm border-0 h-100" style="border-left: 4px solid var(--vz-danger) !important;">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">Vencidas</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="text-danger">{{
                  formatCurrency(props.kpis?.totalVencido || 0) }}</span></h4>
              </div>
              <div class="avatar-sm flex-shrink-0">
                <span class="avatar-title bg-danger-subtle text-danger rounded fs-3">
                  <i class="bx bx-error-circle"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="card shadow-sm border-0 h-100" style="border-left: 4px solid var(--vz-primary) !important;">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">A Vencer (30 dias)</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="text-primary">{{
                  formatCurrency(props.kpis?.totalAVencer || 0) }}</span></h4>
              </div>
              <div class="avatar-sm flex-shrink-0">
                <span class="avatar-title bg-primary-subtle text-primary rounded fs-3">
                  <i class="bx bx-calendar"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="card shadow-sm border-0 h-100" style="border-left: 4px solid var(--vz-success) !important;">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">Pagas</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="text-success">{{
                  formatCurrency(props.kpis?.totalPago || 0) }}</span></h4>
              </div>
              <div class="avatar-sm flex-shrink-0">
                <span class="avatar-title bg-success-subtle text-success rounded fs-3">
                  <i class="bx bx-check-shield"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Master Detail Layout -->
    <div class="row">
      <!-- Master List -->
      <div class="col-lg-12">
        <TableGrid ref="gridRef" :columns="currentCols" :serverUrl="route('financeiro.contas_pagar.index')" :serverQuery="{ filterType: activeFilter, is_api: 1 }" tableTitle="Contas a Pagar e Despesas" :showCheckbox="false"
          :search="true" :showAddButton="true" addButtonText="Nova Despesa" addButtonIconClass="ri-add-line"
          :showStatus="false" :showActions="true" :actionsConfig="currentActionsConfig" :actionsLabels="{ receive: 'Pagar', delete: 'Cancelar' }" :actionsIcons="{ delete: 'ri-close-circle-line' }" @add="openModalNovaDespesa"
          @edit="openModalEditarConta" @receive="openModalPagamento" @show="onShowDetalhes" @delete="cancelarConta">

          <template #right-actions>
            <div class="btn-group" role="group">
              <input type="radio" class="btn-check" name="filterType" id="filterPendentes" value="PENDENTE" v-model="activeFilter">
              <label class="btn btn-outline-primary" for="filterPendentes">Pendentes</label>
              
              <input type="radio" class="btn-check" name="filterType" id="filterSemanal" value="SEMANAL" v-model="activeFilter">
              <label class="btn btn-outline-primary" for="filterSemanal">Semanal</label>

              <input type="radio" class="btn-check" name="filterType" id="filterMensal" value="MENSAL" v-model="activeFilter">
              <label class="btn btn-outline-primary" for="filterMensal">Mensal</label>

              <input type="radio" class="btn-check" name="filterType" id="filterAnual" value="ANUAL" v-model="activeFilter">
              <label class="btn btn-outline-primary" for="filterAnual">Anual</label>
            </div>
          </template>
        </TableGrid>
      </div>
    </div>
    
    <!-- Modais -->
    <ModalNovaDespesa ref="modalNovaDespesa" :categorias="props.categoriasFinanceiras" @saved="fetchContas" />
    <ModalPagamento ref="modalPagamento" :bancos="props.configuracoesBancarias" @saved="fetchContas" />
    <ModalVisualizarConta ref="modalVisualizarConta" @cancelar="cancelarConta" @pagar="openModalPagamento" />
    <ModalConfirm 
      v-model="showDeleteModal" 
      title="Cancelar Despesa" 
      subTitle="Tem certeza que deseja cancelar esta despesa?" 
      message="Esta ação não pode ser desfeita. A conta e a despesa associada (se aplicável) serão removidas permanentemente." 
      nameButton="Sim, cancelar" 
      :processing="isDeleting"
      @save="confirmDelete" 
    />
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import TableGrid from '@/Components/Tables/TableGrid.vue';
import { html } from 'gridjs';
import ModalNovaDespesa from './Components/ModalNovaDespesa.vue';
import ModalPagamento from './Components/ModalPagamento.vue';
import ModalVisualizarConta from './Components/ModalVisualizarConta.vue';
import ModalConfirm from '@/Components/ModalConfirm.vue';

const props = defineProps({
    categoriasFinanceiras: { type: Array, default: () => [] },
    kpis: { type: Object, default: () => ({ totalPendente: 0, totalVencido: 0, totalAVencer: 0, totalPago: 0 }) },
    configuracoesBancarias: { type: Array, default: () => [] }
});

const today = new Date(new Date().setHours(0,0,0,0));

// Refs dos modais
const modalNovaDespesa = ref(null);
const modalContaPagar = ref(null);
const modalPagamento = ref(null);
const modalVisualizarConta = ref(null);

const showDeleteModal = ref(false);
const contaToDelete = ref(null);
const isDeleting = ref(false);

const selectedConta = ref(null);
const activeFilter = ref('PENDENTE');
const gridRef = ref(null);

watch(activeFilter, (newVal) => {
    router.reload({
        only: ['kpis'],
        data: { filterType: newVal },
        preserveState: true,
        preserveScroll: true
    });
    if (gridRef.value) gridRef.value.reload();
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return '';
    let datePart = dateStr;
    if (dateStr.includes(' ')) {
        datePart = dateStr.split(' ')[0];
    } else if (dateStr.includes('T')) {
        datePart = dateStr.split('T')[0];
    }
    const date = new Date(datePart + 'T12:00:00Z');
    return date.toLocaleDateString('pt-BR');
};

const formatDateTimeWithTime = (dateStr) => {
    if (!dateStr) return '';
    let isoString = dateStr.replace(' ', 'T');
    const date = new Date(isoString);
    if (isNaN(date.getTime())) return formatDateTime(dateStr);
    
    return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
};

const currentCols = computed(() => {
    let cols = [
        {
            id: "status", name: "Status", width: "120px", formatter: (cell) => {
                let color = 'secondary';
                if (cell === 'Pendente') color = 'warning';
                if (cell === 'Vencida') color = 'danger';
                if (cell === 'Paga') color = 'success';
                return html(`<span class="badge bg-${color}-subtle text-${color}">${cell}</span>`);
            }
        },
        { id: "categoria_nome", name: "Categoria", width: "150px" },
        { id: "descricao", name: "Descrição", width: "250px" },
        {
            id: "is_recorrente", name: "Tipo", width: "130px", formatter: (cell) => {
                if (cell) {
                    return html(`<span class="badge bg-primary-subtle text-primary"><i class="ri-refresh-line me-1"></i>Recorrente</span>`);
                }
                return html(`<span class="badge bg-light text-body">Única</span>`);
            }
        }
    ];

    if (activeFilter.value !== 'PENDENTE') {
        cols.push({ id: "banco_nome", name: "Gateway", width: "150px" });
    }

    cols.push({ id: "data_vencimento", name: "Vencimento", width: "120px", formatter: (cell) => formatDateTime(cell) });

    if (activeFilter.value !== 'PENDENTE') {
        cols.push({ id: "data_pagamento", name: "Pagamento", width: "170px", formatter: (cell) => formatDateTimeWithTime(cell) });
    }

    cols.push({ id: "valor_pago", hidden: true });
    
    cols.push({ 
        id: "valor", 
        name: "Valor", 
        width: "120px", 
        formatter: (cell, row) => {
            // Se tiver o row.cells, tentamos pegar o status e o valor_pago
            if (row && row.cells) {
                // Achando os índices
                let statusIdx = cols.findIndex(c => c.id === 'status');
                let vpIdx = cols.findIndex(c => c.id === 'valor_pago');
                if (statusIdx >= 0 && vpIdx >= 0) {
                    let status = row.cells[statusIdx]?.data;
                    let vp = row.cells[vpIdx]?.data;
                    if (status === 'Paga' && vp !== null && vp !== undefined) {
                        return html(`<div>
                                        <span class="text-success fw-medium">${formatCurrency(vp)}</span>
                                     </div>`);
                    }
                }
            }
            return formatCurrency(cell);
        }
    });

    return cols;
});

const currentActionsConfig = computed(() => ({
    delete: (row) => row.status !== 'Paga',
    edit: (row) => row.status !== 'Paga',
    show: true,
    receive: (row) => row.status !== 'Paga' // Using receive action for 'Pagar'
}));

const onShowDetalhes = (id, rowObj) => {
    console.log("onShowDetalhes clicado", id, rowObj);
    selectedConta.value = rowObj;
    console.log("modal ref:", modalVisualizarConta.value);
    if (modalVisualizarConta.value) modalVisualizarConta.value.show(rowObj);
};

const openModalNovaDespesa = () => {
    if (modalNovaDespesa.value) modalNovaDespesa.value.show();
};

const openModalEditarConta = (id, rowObj) => {
    if (modalNovaDespesa.value) modalNovaDespesa.value.show(rowObj);
};

const openModalPagamento = (id, rowObj) => {
    if (modalPagamento.value) modalPagamento.value.show(rowObj);
};

const cancelarConta = (id, rowObj) => {
    // If only conta object is passed (from modal), adapt it
    const contaId = rowObj?.id || id?.id || id;
    contaToDelete.value = contaId;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!contaToDelete.value) return;
    isDeleting.value = true;
    router.delete(route('financeiro.contas_pagar.destroy', contaToDelete.value), {
        onFinish: () => {
            isDeleting.value = false;
        },
        onSuccess: () => {
            showDeleteModal.value = false;
            contaToDelete.value = null;
            fetchContas();
        }
    });
};

const fetchContas = () => {
    router.reload({ only: ['kpis'], onFinish: () => {
        if (gridRef.value) gridRef.value.reload();
    } });
};

onMounted(() => {
});
</script>
