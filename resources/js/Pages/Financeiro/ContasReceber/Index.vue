<template>
  <Layout>
    <Head title="Contas a Receber" />
    <PageHeader title="Contas a Receber" pageTitle="Financeiro" />

    <!-- Top KPIs -->
    <div class="row mb-4">
      <!-- Card Total Pendente (Geral) -->
      <div class="col">
        <div class="card shadow-sm border-0 h-100" style="cursor: pointer; border-left: 4px solid var(--vz-primary) !important;"
             :class="activeTab === 0 ? 'bg-primary-subtle' : ''"
             @click="activeTab = 0">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">Total Pendente (Geral)</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="text-primary">{{ formatCurrency(totalPendenteGeral) }}</span></h4>
              </div>
              <div class="avatar-sm flex-shrink-0">
                <span class="avatar-title bg-primary-subtle text-primary rounded fs-3">
                  <i class="bx bx-dollar-circle"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Card Pendências Particulares -->
      <div class="col">
        <div class="card shadow-sm border-0 h-100" style="cursor: pointer; border-left: 4px solid var(--vz-warning) !important;"
             :class="activeTab === 1 ? 'bg-warning-subtle' : ''"
             @click="activeTab = 1">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">Pendências Particulares</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="text-warning">{{ formatCurrency(totalPendenteParticular) }}</span></h4>
              </div>
              <div class="avatar-sm flex-shrink-0">
                <span class="avatar-title bg-warning-subtle text-warning rounded fs-3">
                  <i class="bx bx-user"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Card Pendências Convênios -->
      <div class="col">
        <div class="card shadow-sm border-0 h-100" style="cursor: pointer; border-left: 4px solid var(--vz-success) !important;"
             :class="activeTab === 2 ? 'bg-success-subtle' : ''"
             @click="activeTab = 2">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">Pendências Convênios</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="text-success">{{ formatCurrency(totalPendenteConvenio) }}</span></h4>
              </div>
              <div class="avatar-sm flex-shrink-0">
                <span class="avatar-title bg-success-subtle text-success rounded fs-3">
                  <i class="bx bx-building-house"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs Container -->
    <div class="card">
      <div class="card-body">
        <BTabs v-model="activeTab" nav-class="nav-tabs-custom nav-success nav-justified mb-3">
          
          <BTab title="Visão Geral" active>
            <TableGrid :columns="cols" :data="rows" :tableTitle="''" :showCheckbox="false" :search="true"
              :showAddButton="false" :showStatus="false" :showActions="true"
              :actionsConfig="{ delete: false, edit: false, show: false, diary: false, print: false, download: false, restore: false, receive: canReceive, charge: true }"
              @receive="onReceive" @charge="onCharge" @show="onShowGuias" />
          </BTab>
          
          <BTab title="Particular (Pacientes)">
            <TableGrid :columns="colsParticular" :data="rowsParticular" :tableTitle="''" :showCheckbox="false" :search="true"
              :showAddButton="false" :showStatus="false" :showActions="true"
              :actionsConfig="{ delete: false, edit: false, show: false, diary: false, print: false, download: false, restore: false, receive: false, charge: true }"
              @charge="onCharge" @show="onShowGuias" />
          </BTab>

          <BTab title="Convênios (Repasses)">
            <TableGrid :columns="colsConvenio" :data="rowsConvenio" :tableTitle="''" :showCheckbox="false" :search="true"
              :showAddButton="false" :showStatus="false" :showActions="true"
              :actionsConfig="{ delete: false, edit: false, show: false, diary: false, print: false, download: false, restore: false, receive: canReceive, charge: false }"
              @receive="onReceive" @show="onShowGuias" />
          </BTab>
          
        </BTabs>
      </div>
    </div>

    <Modal v-model="showReceive" title="Registrar Recebimento (Convênio)" name-button="Registrar"
      :processing="receiveForm.processing" size="md" @save="confirmReceive">
      <div class="vstack gap-3">
        <div class="row g-2">
          <div class="col-12">
            <div class="d-flex flex-column">
              <span class="text-muted">Convênio</span>
              <span class="fw-semibold">{{ receiveInfo.convenio }}</span>
            </div>
          </div>
          <div class="col-12">
            <div class="d-flex flex-column">
              <span class="text-muted">Paciente</span>
              <span class="fw-semibold">{{ receiveInfo.paciente }}</span>
            </div>
          </div>
          <div class="col-12">
            <div class="d-flex flex-column">
              <span class="text-muted">Valor em aberto</span>
              <span class="fw-semibold">{{ receiveInfo.valor }}</span>
            </div>
          </div>
        </div>

        <div class="row g-2">
          <div class="col-12">
            <label class="form-label">Valor recebido</label>
            <input v-model="receiveForm.valor" type="text" class="form-control" />
          </div>
          <div class="col-12">
            <label class="form-label">Forma</label>
            <select v-model="receiveForm.forma_pagamento" class="form-select">
              <option value="TRANSFERENCIA">TRANSFERENCIA</option>
              <option value="PIX">PIX</option>
              <option value="DINHEIRO">DINHEIRO</option>
              <option value="CARTAO">CARTAO</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Data</label>
            <input v-model="receiveForm.data_pagamento" type="date" class="form-control" />
          </div>
        </div>
      </div>
    </Modal>

    <Modal v-model="showCharge" title="Gerar Cobrança (Link de Pagamento)" name-button="Gerar"
      :processing="chargeForm.processing" size="md" @save="confirmCharge">
      <div class="vstack gap-3">
        <div class="row g-2">
          <div class="col-12">
            <label class="form-label">Método de Pagamento Autorizado</label>
            <select v-model="chargeForm.billingType" class="form-select">
              <option value="UNDEFINED">Qualquer um (Cliente escolhe entre Boleto, Pix e Cartão)</option>
              <option value="BOLETO">Apenas Boleto Bancário (C/ Pix Embutido)</option>
              <option value="PIX">Apenas PIX</option>
              <option value="CREDIT_CARD">Apenas Cartão de Crédito</option>
            </select>
          </div>
        </div>
      </div>
    </Modal>

    <!-- Modal Guias do Lote -->
    <Modal v-model="showGuiasModal" :title="'Guias do Lote ' + (guiasModalLoteName || '')" size="xl" :show-footer="false">
      <div v-if="loadingGuias" class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 text-muted">Buscando guias...</p>
      </div>
      <div v-else class="table-responsive">
          <table class="table table-bordered table-striped table-hover mb-0">
              <thead class="table-light">
                  <tr>
                      <th>Guia</th>
                      <th>Beneficiário</th>
                      <th>Profissional</th>
                      <th>Tipo</th>
                      <th>Valor (R$)</th>
                  </tr>
              </thead>
              <tbody>
                  <tr v-for="guia in guiasLoteList" :key="guia.id">
                      <td>{{ guia.numero_guia_prestador || guia.numero_guia_operadora || guia.id || '-' }}</td>
                      <td>{{ guia.beneficiario_nome || '-' }}</td>
                      <td>{{ guia.profissional_solicitante_nome || '-' }}</td>
                      <td>{{ guia.tipo || 'Guia de Consulta' }}</td>
                      <td>{{ formatCurrency(guia.valor_total_geral) }}</td>
                  </tr>
                  <tr v-if="guiasLoteList.length === 0">
                      <td colspan="5" class="text-center text-muted">Nenhuma guia encontrada para este lote.</td>
                  </tr>
              </tbody>
          </table>
      </div>
    </Modal>
  </Layout>
</template>

<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { toRef, ref, computed } from "vue";
import axios from "axios";
import { html } from "gridjs";
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
  contas: { type: Array, default: () => [] },
});

const rows = toRef(props, "contas");

const activeTab = ref(0);

// Helper para filtrar pendentes
const pendentes = computed(() => {
  return (rows.value || []).filter(r => String(r.status || "").toUpperCase() !== "RECEBIDO" && String(r.status || "").toUpperCase() !== "CANCELADO");
});

const rowsParticular = computed(() => {
  return (rows.value || []).filter(r => String(r.tipo_convenio || "").toUpperCase() !== "CONVENIO");
});

const rowsConvenio = computed(() => {
  return (rows.value || []).filter(r => String(r.tipo_convenio || "").toUpperCase() === "CONVENIO");
});

const totalPendenteGeral = computed(() => pendentes.value.reduce((acc, curr) => acc + Number(curr.valor || 0), 0));
const totalPendenteParticular = computed(() => pendentes.value.filter(r => String(r.tipo_convenio || "").toUpperCase() !== "CONVENIO").reduce((acc, curr) => acc + Number(curr.valor || 0), 0));
const totalPendenteConvenio = computed(() => pendentes.value.filter(r => String(r.tipo_convenio || "").toUpperCase() === "CONVENIO").reduce((acc, curr) => acc + Number(curr.valor || 0), 0));

function formatCurrency(n) {
  const v = Number(n || 0);
  try {
    return v.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  } catch (_) {
    return `R$ ${v.toFixed(2)}`.replace(".", ",");
  }
}

function formatStatus(val) {
  if (!val) return '';
  if (val === 'AGUARDANDO_COBRANCA') return 'Aguardando cobrança';
  return val.replace(/_/g, ' ');
}

function loteFormatter(cell, row) {
    const faturamentoId = row.cells[0].data; // Faturamento ID
    const loteNumber = cell || faturamentoId;
    if (!loteNumber) return html(`<span class="text-muted">-</span>`);
    return html(`<span data-action="show" data-id="${faturamentoId}" data-lote="${loteNumber}" class="text-primary fw-medium text-decoration-underline" style="cursor: pointer;">${loteNumber}</span>`);
}

const cols = [
  { id: "id", name: "Faturamento" },
  { id: "lote", name: "Lote", formatter: loteFormatter },
  { id: "conta_id", name: "ID Conta" },
  { id: "nu_pagamento", name: "Nº Pagamento" },
  { id: "tipo_convenio", name: "Tipo" },
  { id: "pagador", name: "Pagador/Convênio" },
  { id: "procedimento", name: "Procedimento" },
  { id: "vencimento", name: "Vencimento" },
  { id: "valor", name: "Valor", formatter: (cell) => formatCurrency(cell) },
  { id: "data_pagamento", name: "Dt Pagamento" },
  { id: "status", name: "Status", formatter: (cell) => formatStatus(cell) },
];

const colsParticular = [
  { id: "id", name: "Faturamento" },
  { id: "lote", name: "Lote", formatter: loteFormatter },
  { id: "conta_id", name: "ID Conta" },
  { id: "pagador", name: "Paciente" },
  { id: "procedimento", name: "Procedimento" },
  { id: "vencimento", name: "Vencimento" },
  { id: "valor", name: "Valor", formatter: (cell) => formatCurrency(cell) },
  { id: "data_pagamento", name: "Dt Pagamento" },
  { id: "status", name: "Status", formatter: (cell) => formatStatus(cell) },
];

const colsConvenio = [
  { id: "id", name: "Faturamento" },
  { id: "lote", name: "Lote", formatter: loteFormatter },
  { id: "conta_id", name: "ID Conta" },
  { id: "pagador", name: "Convênio" },
  { id: "paciente", name: "Paciente" },
  { id: "procedimento", name: "Procedimento" },
  { id: "vencimento", name: "Vencimento" },
  { id: "valor", name: "Valor", formatter: (cell) => formatCurrency(cell) },
  { id: "data_pagamento", name: "Dt Repasse" },
  { id: "status", name: "Status", formatter: (cell) => formatStatus(cell) },
];

function canReceive(row) {
  return String(row?.tipo_convenio || "").toUpperCase() === "CONVENIO" && String(row?.status || "").toUpperCase() !== "RECEBIDO";
}

const showReceive = ref(false);
const receiveId = ref(null);

const receiveForm = useForm({
  valor: "",
  forma_pagamento: "TRANSFERENCIA",
  data_pagamento: null,
});

const receiveInfo = computed(() => {
  const id = receiveId.value;
  const r = (rows.value || []).find(x => String(x.id) === String(id)) || {};
  return {
    convenio: r?.convenio || "—",
    paciente: r?.paciente || "—",
    valor: formatCurrency(r?.valor || 0),
  };
});

function toDateInput(dmy) {
  const s = String(dmy || "").trim();
  if (!s) return null;
  const m = s.match(/^(\d{2})-(\d{2})-(\d{4})$/);
  if (!m) return null;
  return `${m[3]}-${m[2]}-${m[1]}`;
}

function onReceive(id, row) {
  receiveId.value = id ?? row?.id ?? null;
  const r = (rows.value || []).find(x => String(x.id) === String(receiveId.value)) || {};
  receiveForm.valor = String(r?.valor ?? "");
  receiveForm.forma_pagamento = "TRANSFERENCIA";
  receiveForm.data_pagamento = toDateInput(r?.vencimento);
  showReceive.value = true;
}

function parseNumberOrNull(v) {
  const s = String(v ?? "").trim();
  if (!s) return null;
  const n = Number(s.replace(/\./g, "").replace(",", "."));
  return Number.isNaN(n) ? null : n;
}

const showCharge = ref(false);
const chargeId = ref(null);
const chargeForm = useForm({
  billingType: "UNDEFINED",
});

function onCharge(id, row) {
  chargeId.value = row?.conta_id || id;
  chargeForm.billingType = "UNDEFINED";
  showCharge.value = true;
}

function confirmCharge() {
  const crId = chargeId.value;
  if (!crId) {
    showCharge.value = false;
    return;
  }
  
  chargeForm.post(route('financeiro.contas_receber.gerar_cobranca', crId), {
    preserveScroll: true,
    onSuccess: () => {
      showCharge.value = false;
      chargeId.value = null;
    },
    onError: () => {
      showCharge.value = false;
    }
  });
}

function confirmReceive() {
  const id = receiveId.value;
  if (!id) { showReceive.value = false; return; }
  receiveForm
    .transform((d) => ({
      ...d,
      valor: parseNumberOrNull(d.valor),
    }))
    .post(`/faturamentos/${id}/receber-financeiro`, {
      onSuccess: async () => {
        showReceive.value = false;
        receiveId.value = null;
        await new Promise((resolve) => {
          router.reload({ only: ["contas"], onFinish: () => resolve() });
        });
      },
      onError: () => {
        showReceive.value = false;
      }
    });
}

const showGuiasModal = ref(false);
const guiasModalLoteName = ref("");
const guiasLoteList = ref([]);
const loadingGuias = ref(false);

function onShowGuias(id, row) {
    if (row && row.lote) {
        openGuiasModal(id, row.lote);
    } else {
        openGuiasModal(id, id);
    }
}

async function openGuiasModal(faturamentoId, loteName) {
  guiasModalLoteName.value = loteName || faturamentoId;
  showGuiasModal.value = true;
  loadingGuias.value = true;
  guiasLoteList.value = [];
  try {
    const res = await axios.get(`/faturamentos/${faturamentoId}/guias`);
    guiasLoteList.value = res.data;
  } catch (err) {
    console.error("Erro ao buscar guias:", err);
  } finally {
    loadingGuias.value = false;
  }
}

function getProcedimentoGuia(guia) {
    // Tenta extrair o nome do procedimento que está atrelado ao agendamento, se houver
    if (guia.atendimento?.agendamento?.procedimento?.nome) {
        return guia.atendimento.agendamento.procedimento.nome;
    }
    // Caso tenha uma lista de procedimentos, pega o primeiro
    if (guia.atendimento?.agendamento?.procedimentos && guia.atendimento.agendamento.procedimentos.length > 0) {
        return guia.atendimento.agendamento.procedimentos[0].nome;
    }
    return null;
}

function getGuiaStatusBadge(status) {
    if (!status) return 'bg-secondary';
    const s = status.toUpperCase();
    if (s === 'FATURADA') return 'bg-success';
    if (s === 'GLOSADA') return 'bg-danger';
    if (s === 'DEVOLVIDA') return 'bg-warning text-dark';
    return 'bg-primary';
}
</script>

<style scoped>
:deep(.gridjs-table thead th:nth-child(1)),
:deep(.gridjs-table tbody td:nth-child(1):not([colspan])) {
  display: none;
}
</style>
