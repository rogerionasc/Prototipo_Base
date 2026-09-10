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
                  formatCurrency(totalPendente) }}</span></h4>
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
                  formatCurrency(totalVencido) }}</span></h4>
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
                  formatCurrency(totalAVencer) }}</span></h4>
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
                  formatCurrency(totalPago) }}</span></h4>
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
      <div :class="selectedConta ? 'col-lg-8' : 'col-lg-12'">
        <TableGrid :columns="currentCols" :data="currentRows" tableTitle="Contas a Pagar e Despesas" :showCheckbox="false"
          :search="true" :showAddButton="true" addButtonText="Nova Despesa" addButtonIconClass="ri-add-line"
          :showStatus="false" :showActions="true" :actionsConfig="currentActionsConfig" @add="openModalNovaDespesa"
          @edit="openModalEditarConta" @receive="openModalPagamento" @show="onShowDetalhes">

          <template #right-actions>
            <div class="btn-group" role="group">
              <input type="radio" class="btn-check" name="filterType" id="filterAll" value="TODOS"
                v-model="activeFilter">
              <label class="btn btn-outline-primary" for="filterAll">Todas</label>

              <input type="radio" class="btn-check" name="filterType" id="filterPendentes" value="PENDENTE"
                v-model="activeFilter">
              <label class="btn btn-outline-primary" for="filterPendentes">Pendentes</label>

              <input type="radio" class="btn-check" name="filterType" id="filterPagas" value="PAGA"
                v-model="activeFilter">
              <label class="btn btn-outline-primary" for="filterPagas">Pagas</label>
            </div>
          </template>
        </TableGrid>
      </div>

      <!-- Detail Panel (Offcanvas style but split screen) -->
      <div v-if="selectedConta" class="col-lg-4">
        <div class="card sticky-side-div" style="position: sticky; top: 80px;">
          <div class="card-header border-bottom-dashed bg-light">
            <div class="d-flex align-items-center">
              <h5 class="card-title mb-0 flex-grow-1 text-primary">Conta #{{ selectedConta.id }} - Parcela {{ selectedConta.parcela_num }}</h5>
              <button type="button" class="btn-close" @click="selectedConta = null"></button>
            </div>
          </div>
          <div class="card-body pt-2" style="max-height: calc(100vh - 200px); overflow-y: auto;">
            <div class="d-flex flex-column gap-3">
              <!-- Resumo da Conta -->
              <div class="border rounded p-3 bg-light-subtle">
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Fornecedor:</span>
                  <span class="fw-medium text-end">{{ selectedConta.fornecedor_id || 'Não informado' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Categoria:</span>
                  <span class="fw-medium text-end">{{ selectedConta.categoria_id || 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Centro de Custo:</span>
                  <span class="fw-medium text-end">{{ selectedConta.centro_custo_id || 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Vencimento:</span>
                  <span class="fw-bold text-danger">{{ formatDateTime(selectedConta.data_vencimento) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Valor Base:</span>
                  <span class="fw-bold fs-15">{{ formatCurrency(selectedConta.valor_base) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Desconto:</span>
                  <span class="fw-medium text-success">{{ formatCurrency(selectedConta.desconto) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Juros/Multa:</span>
                  <span class="fw-medium text-danger">{{ formatCurrency((selectedConta.juros || 0) + (selectedConta.multa || 0)) }}</span>
                </div>
                <hr class="my-2 border-dashed">
                <div class="d-flex justify-content-between">
                  <span class="fw-bold text-dark">Valor Líquido:</span>
                  <span class="fw-bold text-primary fs-16">{{ formatCurrency(calcularLiquido(selectedConta)) }}</span>
                </div>
              </div>
              
              <!-- Anexos -->
              <div>
                 <h6 class="fs-13 fw-bold text-uppercase mb-2">Anexos / Documentos</h6>
                 <div class="alert alert-secondary text-center mb-0" v-if="!selectedConta.anexos || selectedConta.anexos.length === 0">
                    Nenhum anexo encontrado.
                 </div>
                 <div class="d-flex flex-wrap gap-2" v-else>
                    <a v-for="(anexo, idx) in selectedConta.anexos" :key="idx" :href="anexo.url" target="_blank" class="btn btn-sm btn-outline-info">
                       <i class="ri-attachment-line me-1"></i> {{ anexo.nome }}
                    </a>
                 </div>
              </div>

              <!-- Histórico de Pagamentos -->
              <div>
                <h6 class="fs-13 fw-bold text-uppercase mb-2">Histórico de Pagamentos</h6>
                <div class="alert alert-secondary text-center mb-0" v-if="!selectedConta.pagamentos || selectedConta.pagamentos.length === 0">
                  Nenhum pagamento registrado.
                </div>
                <div class="list-group" v-else>
                  <div class="list-group-item" v-for="pag in selectedConta.pagamentos" :key="pag.id">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <span class="fw-medium fs-13">{{ formatDateTime(pag.data_pagamento) }}</span>
                      <span class="badge bg-success-subtle text-success">{{ formatCurrency(pag.valor_pago) }}</span>
                    </div>
                    <small class="text-muted d-block">Forma: {{ pag.forma_pagamento_id || 'Dinheiro' }}</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer bg-light border-top-dashed d-flex gap-2">
            <button class="btn btn-outline-danger w-50" @click="cancelarConta(selectedConta)">Cancelar Conta</button>
            <button class="btn btn-success w-50" @click="openModalPagamento(selectedConta)">Pagar</button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Modais -->
    <ModalNovaDespesa ref="modalNovaDespesa" @saved="fetchContas" />
    <ModalContaPagar ref="modalContaPagar" @saved="fetchContas" />
    <ModalPagamento ref="modalPagamento" @saved="fetchContas" />
    
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import TableGrid from '@/Components/Tables/TableGrid.vue';
import { html } from 'gridjs';
import ModalNovaDespesa from './Components/ModalNovaDespesa.vue';
import ModalContaPagar from './Components/ModalContaPagar.vue';
import ModalPagamento from './Components/ModalPagamento.vue';

// KPIs fictícios
const totalPendente = ref(15230.50);
const totalVencido = ref(2100.00);
const totalAVencer = ref(8400.00);
const totalPago = ref(43200.75);

// Refs dos modais
const modalNovaDespesa = ref(null);
const modalContaPagar = ref(null);
const modalPagamento = ref(null);

const selectedConta = ref(null);
const activeFilter = ref('TODOS');
const rawContas = ref([
    {
        id: 1, despesa_id: 1, fornecedor_id: 'Distribuidora Medica S/A', categoria_id: 'Insumos', centro_custo_id: 'Clínica Geral',
        descricao: 'Compra de luvas e máscaras', parcela_num: 1, data_vencimento: '2026-09-15', valor_base: 1500.00,
        desconto: 0, juros: 0, multa: 0, valor_pago: 0, status: 'Pendente',
        pagamentos: [], anexos: [{ nome: 'NF-1234.pdf', url: '#' }]
    },
    {
        id: 2, despesa_id: 2, fornecedor_id: 'Imobiliária Central', categoria_id: 'Aluguel', centro_custo_id: 'Administrativo',
        descricao: 'Aluguel do mês', parcela_num: 1, data_vencimento: '2026-09-05', valor_base: 5000.00,
        desconto: 0, juros: 100, multa: 50, valor_pago: 0, status: 'Vencida',
        pagamentos: [], anexos: []
    },
    {
        id: 3, despesa_id: 3, fornecedor_id: 'Companhia de Energia', categoria_id: 'Energia', centro_custo_id: 'Administrativo',
        descricao: 'Conta de luz', parcela_num: 1, data_vencimento: '2026-09-10', valor_base: 850.00,
        desconto: 0, juros: 0, multa: 0, valor_pago: 850.00, status: 'Paga',
        pagamentos: [{ id: 1, data_pagamento: '2026-09-08', valor_pago: 850.00, forma_pagamento_id: 'PIX' }], anexos: []
    }
]);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr + 'T00:00:00');
    return date.toLocaleDateString('pt-BR');
};

const calcularLiquido = (conta) => {
    const base = parseFloat(conta.valor_base) || 0;
    const desc = parseFloat(conta.desconto) || 0;
    const jur = parseFloat(conta.juros) || 0;
    const mult = parseFloat(conta.multa) || 0;
    return (base - desc + jur + mult);
};

const currentRows = computed(() => {
    let filtered = rawContas.value;
    if (activeFilter.value === 'PENDENTE') {
        filtered = filtered.filter(c => c.status === 'Pendente' || c.status === 'Vencida');
    } else if (activeFilter.value === 'PAGA') {
        filtered = filtered.filter(c => c.status === 'Paga');
    }
    return filtered;
});

const currentCols = computed(() => [
    {
        id: "status", name: "Status", width: "120px", formatter: (cell) => {
            let color = 'secondary';
            if (cell === 'Pendente') color = 'warning';
            if (cell === 'Vencida') color = 'danger';
            if (cell === 'Paga') color = 'success';
            return html(`<span class="badge bg-${color}-subtle text-${color}">${cell}</span>`);
        }
    },
    { id: "fornecedor_id", name: "Fornecedor", width: "200px" },
    { id: "descricao", name: "Descrição", width: "250px" },
    { id: "parcela_num", name: "Parc.", width: "80px" },
    { id: "data_vencimento", name: "Vencimento", width: "120px", formatter: (cell) => formatDateTime(cell) },
    { id: "valor_base", name: "Valor", width: "120px", formatter: (cell) => formatCurrency(cell) }
]);

const currentActionsConfig = computed(() => ({
    delete: false,
    edit: true,
    show: true,
    receive: true // Using receive action for 'Pagar'
}));

const onShowDetalhes = (row) => {
    selectedConta.value = row;
};

const openModalNovaDespesa = () => {
    if (modalNovaDespesa.value) modalNovaDespesa.value.show();
};

const openModalEditarConta = (row) => {
    if (modalContaPagar.value) modalContaPagar.value.show(row);
};

const openModalPagamento = (row) => {
    if (modalPagamento.value) modalPagamento.value.show(row);
};

const cancelarConta = (conta) => {
    alert("Função de cancelar conta a pagar.");
};

const fetchContas = () => {
    console.log("Atualizando lista de contas...");
};

onMounted(() => {
    fetchContas();
});
</script>
