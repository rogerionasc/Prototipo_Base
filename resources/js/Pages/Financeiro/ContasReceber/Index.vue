<template>
  <Layout>

    <Head title="Contas a Receber" />
    <PageHeader title="Contas a Receber" pageTitle="Financeiro" />

    <!-- Top KPIs -->
    <div class="row mb-4">
      <div class="col">
        <div class="card shadow-sm border-0 h-100" style="border-left: 4px solid var(--vz-primary) !important;">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">A Receber</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="text-primary">{{
                  formatCurrency(totalAReceber) }}</span></h4>
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
        <div class="card shadow-sm border-0 h-100" style="border-left: 4px solid var(--vz-success) !important;">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">Recebidas</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="text-success">{{
                  formatCurrency(totalRecebido) }}</span></h4>
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
        <TableGrid :columns="currentCols" :data="currentRows" tableTitle="Títulos a Receber" :showCheckbox="false"
          :search="true" :showAddButton="false" :showStatus="false" :showActions="true" :compactSpacing="true"
          :actionsConfig="currentActionsConfig" @receive="onReceive" @charge="onCharge" @show="onShowGuias"
          @procedure="onProcedureClick">

          <template #right-actions>
            <div class="btn-group" role="group">
              <input type="radio" class="btn-check" name="filterType" id="filterAll" value="TODOS"
                v-model="activeFilter">
              <label class="btn btn-outline-primary" for="filterAll">Geral</label>

              <input type="radio" class="btn-check" name="filterType" id="filterPart" value="PARTICULAR"
                v-model="activeFilter">
              <label class="btn btn-outline-primary" for="filterPart">Particular</label>

              <input type="radio" class="btn-check" name="filterType" id="filterConv" value="CONVENIO"
                v-model="activeFilter">
              <label class="btn btn-outline-primary" for="filterConv">Convênio</label>
            </div>
          </template>
        </TableGrid>
      </div>

      <!-- Detail Panel (Offcanvas style but split screen) -->
      <div v-if="selectedConta" class="col-lg-4">
        <div class="card sticky-side-div" style="position: sticky; top: 80px;">
          <div class="card-header border-bottom-dashed bg-light">
            <div class="d-flex align-items-center">
              <h5 class="card-title mb-0 flex-grow-1 text-primary">Conta #{{ selectedConta.conta_id }}</h5>
              <button type="button" class="btn-close" @click="selectedConta = null"></button>
            </div>
          </div>
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="flex-shrink-0">
                <div v-if="selectedConta.logo_path" class="avatar-sm">
                  <img
                    :src="selectedConta.logo_path.startsWith('http') ? selectedConta.logo_path : '/storage/' + selectedConta.logo_path"
                    alt="Logo" class="rounded-circle avatar-sm border bg-white"
                    style="object-fit: contain; padding: 2px;">
                </div>
                <div v-else class="avatar-sm">
                  <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                    <i class="bx"
                      :class="selectedConta.tipo_convenio === 'CONVENIO' ? 'bx-building-house' : 'bx-user'"></i>
                  </span>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <h6 class="fs-15 mb-1">{{ selectedConta.pagador }}</h6>
                <p class="text-muted mb-0">Lote: {{ selectedConta.lote || '-' }}</p>
              </div>
              <div>
                <span class="badge fs-12" :class="getBadgeClass(selectedConta.status)">{{
                  formatStatus(selectedConta.status)
                }}</span>
              </div>
            </div>

            <div class="row text-center border-top border-bottom py-3 mb-3">
              <div class="col-6 border-end">
                <p class="text-muted mb-1">Valor</p>
                <h5 class="fs-16 fw-semibold mb-0 text-primary">{{ formatCurrency(selectedConta.valor) }}</h5>
              </div>
              <div class="col-6">
                <p class="text-muted mb-1">Vencimento</p>
                <h5 class="fs-16 fw-semibold mb-0" :class="isVencida(selectedConta) ? 'text-danger' : ''">
                  {{ selectedConta.vencimento }}
                </h5>
              </div>
            </div>

            <div class="vstack gap-2 mt-3">
              <button class="btn btn-primary w-100" @click="onCharge(selectedConta.id, selectedConta)">
                <i class="bx bx-barcode fs-16 align-middle me-1"></i> Gerar Cobrança / Link
              </button>
            </div>
          </div>
        </div>
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

    <Modal v-model="showCharge" title="Gerar Cobrança (Link de Pagamento)" name-button="Gerar Link"
      :processing="chargeForm.processing" size="md" @save="confirmCharge">
      <div class="row g-4">
        <!-- Método de Pagamento -->
        <div class="col-12">
          <h6 class="text-muted text-uppercase fw-semibold mb-3 fs-12">Métodos de Pagamento</h6>
          <p class="text-muted mb-4 fs-13">Selecione quais métodos o cliente poderá utilizar para pagar este link.</p>

          <div class="vstack gap-3">
            <div>
              <input id="billingTypeAny" name="billingType" type="radio" class="custom-radio-input d-none"
                value="UNDEFINED" v-model="chargeForm.billingType">
              <label class="custom-radio-card m-0" for="billingTypeAny">
                <div class="d-flex align-items-center">
                  <span class="flex-shrink-0 avatar-xs">
                    <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-4">
                      <i class="bx bx-list-check"></i>
                    </span>
                  </span>
                  <span class="flex-grow-1 ms-3">
                    <span class="d-block fw-semibold mb-1">Qualquer Método</span>
                    <span class="d-block text-muted fs-12">Deixar que o cliente escolha como quer pagar.</span>
                  </span>
                </div>
              </label>
            </div>

            <div>
              <input id="billingTypePix" name="billingType" type="radio" class="custom-radio-input d-none" value="PIX"
                v-model="chargeForm.billingType">
              <label class="custom-radio-card m-0" for="billingTypePix">
                <div class="d-flex align-items-center">
                  <span class="flex-shrink-0 avatar-xs">
                    <span class="avatar-title bg-success-subtle text-success rounded-circle fs-4">
                      <i class="bx bx-qr-scan"></i>
                    </span>
                  </span>
                  <span class="flex-grow-1 ms-3">
                    <span class="d-block fw-semibold mb-1">PIX Exclusivo</span>
                    <span class="d-block text-muted fs-12">Gera um QR Code Pix dinâmico com baixa automática.</span>
                  </span>
                </div>
              </label>
            </div>

            <div>
              <input id="billingTypeBoleto" name="billingType" type="radio" class="custom-radio-input d-none"
                value="BOLETO" v-model="chargeForm.billingType">
              <label class="custom-radio-card m-0" for="billingTypeBoleto">
                <div class="d-flex align-items-center">
                  <span class="flex-shrink-0 avatar-xs">
                    <span class="avatar-title bg-info-subtle text-info rounded-circle fs-4">
                      <i class="bx bx-barcode"></i>
                    </span>
                  </span>
                  <span class="flex-grow-1 ms-3">
                    <span class="d-block fw-semibold mb-1">Boleto Bancário</span>
                    <span class="d-block text-muted fs-12">Boleto registrado (inclui linha digitável Pix).</span>
                  </span>
                </div>
              </label>
            </div>

            <div>
              <input id="billingTypeCreditCard" name="billingType" type="radio" class="custom-radio-input d-none"
                value="CREDIT_CARD" v-model="chargeForm.billingType">
              <label class="custom-radio-card m-0" for="billingTypeCreditCard">
                <div class="d-flex align-items-center">
                  <span class="flex-shrink-0 avatar-xs">
                    <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-4">
                      <i class="bx bx-credit-card"></i>
                    </span>
                  </span>
                  <span class="flex-grow-1 ms-3">
                    <span class="d-block fw-semibold mb-1">Cartão de Crédito</span>
                    <span class="d-block text-muted fs-12">Permite parcelamento com taxas.</span>
                  </span>
                </div>
              </label>
            </div>
          </div>
        </div>
      </div>
    </Modal>

    <!-- Modal Guias do Lote -->
    <Modal v-model="showGuiasModal" :title="'Guias do Lote ' + (guiasModalLoteName || '')" size="xl"
      :show-footer="false">
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

const activeFilter = ref("TODOS");
const selectedConta = ref(null);
const loadingGuias = ref(false);
const guiasLoteList = ref([]);

function parseDateBR(dmy) {
  if (!dmy) return null;
  const parts = dmy.split('-');
  if (parts.length === 3) return new Date(parts[2], parts[1] - 1, parts[0]);
  return null;
}

function isVencida(row) {
  if (String(row.status || "").toUpperCase() === "RECEBIDO") return false;
  const v = parseDateBR(row.vencimento);
  if (!v) return false;
  const h = new Date();
  h.setHours(0, 0, 0, 0);
  return v < h;
}

const totalAReceber = computed(() => {
  return (rows.value || [])
    .filter(r => String(r.status || "").toUpperCase() !== "RECEBIDO" && String(r.status || "").toUpperCase() !== "CANCELADO" && !isVencida(r))
    .reduce((acc, curr) => acc + Number(curr.valor || 0), 0);
});

const totalVencido = computed(() => {
  return (rows.value || [])
    .filter(r => String(r.status || "").toUpperCase() !== "RECEBIDO" && String(r.status || "").toUpperCase() !== "CANCELADO" && isVencida(r))
    .reduce((acc, curr) => acc + Number(curr.valor || 0), 0);
});

const totalRecebido = computed(() => {
  return (rows.value || [])
    .filter(r => String(r.status || "").toUpperCase() === "RECEBIDO")
    .reduce((acc, curr) => acc + Number(curr.valor || 0), 0);
});

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

function getBadgeClass(status) {
  if (!status) return 'bg-secondary';
  const s = status.toUpperCase();
  if (s === 'RECEBIDO') return 'bg-success';
  if (s === 'CANCELADO') return 'bg-dark';
  if (s === 'AGUARDANDO_COBRANCA') return 'bg-info';
  return 'bg-warning text-dark'; // Pendente
}

function loteFormatter(cell, row) {
  const faturamentoId = row.cells[1].data; // Faturamento ID is now the second column
  const loteNumber = cell || faturamentoId;
  if (!loteNumber) return html(`<span class="text-muted">-</span>`);
  return html(`<span data-action="procedure" data-id="${faturamentoId}" data-lote="${loteNumber}" class="text-primary fw-medium text-decoration-underline" style="cursor: pointer;">${loteNumber}</span>`);
}

function statusFormatter(cell, row) {
  const v = parseDateBR(row.cells[7].data); // Vencimento is at index 7 in all cols
  let badgeClass = getBadgeClass(cell);
  if (String(cell || "").toUpperCase() !== "RECEBIDO" && String(cell || "").toUpperCase() !== "CANCELADO") {
    const h = new Date();
    h.setHours(0, 0, 0, 0);
    if (v && v < h) {
      badgeClass = 'bg-danger';
      cell = 'VENCIDO';
    }
  }
  return html(`<span class="badge ${badgeClass}">${formatStatus(cell)}</span>`);
}

const cols = [
  { id: "conta_id", name: "ID Conta Receber" },
  { id: "id", name: "Faturamento", hidden: true },
  { id: "lote", name: "Lote", formatter: loteFormatter },
  { id: "nu_pagamento", name: "Nº Pgto", hidden: true },
  { id: "tipo_convenio", name: "Tipo" },
  { id: "pagador", name: "Pagador/Convênio" },
  { id: "procedimento", name: "Procedimento", hidden: true },
  { id: "vencimento", name: "Vencimento" },
  { id: "valor", name: "Valor", formatter: (cell) => formatCurrency(cell) },
  { id: "data_pagamento", name: "Dt Pagamento", hidden: true },
  { id: "status", name: "Status", formatter: statusFormatter },
];

const colsParticular = [
  { id: "conta_id", name: "ID Conta Receber" },
  { id: "id", name: "Faturamento", hidden: true },
  { id: "lote", name: "Lote", formatter: loteFormatter },
  { id: "nu_pagamento", name: "Nº Pgto", hidden: true },
  { id: "tipo_convenio", name: "Tipo", hidden: true },
  { id: "pagador", name: "Paciente" },
  { id: "procedimento", name: "Procedimento", hidden: true },
  { id: "vencimento", name: "Vencimento" },
  { id: "valor", name: "Valor", formatter: (cell) => formatCurrency(cell) },
  { id: "data_pagamento", name: "Dt Pagamento", hidden: true },
  { id: "status", name: "Status", formatter: statusFormatter },
];

const colsConvenio = [
  { id: "conta_id", name: "ID Conta Receber" },
  { id: "id", name: "Faturamento", hidden: true },
  { id: "lote", name: "Lote", formatter: loteFormatter },
  { id: "nu_pagamento", name: "Nº Pgto", hidden: true },
  { id: "tipo_convenio", name: "Tipo", hidden: true },
  { id: "pagador", name: "Convênio" },
  { id: "procedimento", name: "Procedimento", hidden: true },
  { id: "vencimento", name: "Vencimento" },
  { id: "valor", name: "Valor", formatter: (cell) => formatCurrency(cell) },
  { id: "data_pagamento", name: "Dt Repasse", hidden: true },
  { id: "status", name: "Status", formatter: statusFormatter },
];

const currentCols = computed(() => {
  if (activeFilter.value === 'PARTICULAR') return colsParticular;
  if (activeFilter.value === 'CONVENIO') return colsConvenio;
  return cols;
});

const currentRows = computed(() => {
  let list = rows.value || [];
  if (activeFilter.value === 'PARTICULAR') {
    list = list.filter(r => String(r.tipo_convenio || "").toUpperCase() !== "CONVENIO");
  } else if (activeFilter.value === 'CONVENIO') {
    list = list.filter(r => String(r.tipo_convenio || "").toUpperCase() === "CONVENIO");
  }
  return list;
});

const currentActionsConfig = computed(() => {
  if (activeFilter.value === 'PARTICULAR') {
    return { delete: false, edit: false, show: true, diary: false, print: false, download: false, restore: false, receive: false, charge: false };
  } else if (activeFilter.value === 'CONVENIO') {
    return { delete: false, edit: false, show: true, diary: false, print: false, download: false, restore: false, receive: canReceive, charge: false };
  }
  return { delete: false, edit: false, show: true, diary: false, print: false, download: false, restore: false, receive: canReceive, charge: false };
});

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

const chargeInfo = computed(() => {
  const id = chargeId.value;
  const r = (rows.value || []).find(x => String(x.conta_id) === String(id) || String(x.id) === String(id)) || {};
  return {
    pagador: r?.pagador || "—",
    valor: formatCurrency(r?.valor || 0),
    lote: r?.lote || "—"
  };
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

// Replaced the old guias modal with selectConta logic
function onShowGuias(id, row) {
  selectedConta.value = (rows.value || []).find(x => String(x.id) === String(id)) || null;
  if (selectedConta.value) {
    loadingGuias.value = true;
    guiasLoteList.value = [];
    axios.get(`/faturamentos/${id}/guias`).then(res => {
      guiasLoteList.value = res.data;
    }).catch(err => {
      console.error("Erro ao buscar guias:", err);
    }).finally(() => {
      loadingGuias.value = false;
    });
  }
}

const showGuiasModal = ref(false);
const guiasModalLoteName = ref("");

function onProcedureClick(id) {
  const r = (rows.value || []).find(x => String(x.id) === String(id));
  openGuiasModal(id, r?.lote || id);
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
</script>

<style scoped>
.modal-content-scroll {
  height: calc(100vh - 120px);
}

.custom-radio-card {
  border: 1px solid #e2e5e8;
  border-radius: 0.5rem;
  padding: 1rem;
  cursor: pointer;
  transition: all 0.2s;
  display: block;
}

.custom-radio-card:hover {
  border-color: #a3c4f7;
}

.custom-radio-input:checked+.custom-radio-card {
  border-color: #3b82f6;
  /* primary color */
  background-color: #eff6ff;
  box-shadow: 0 0 0 1px #3b82f6;
}
</style>
