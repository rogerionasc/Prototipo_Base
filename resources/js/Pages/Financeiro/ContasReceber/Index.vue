<template>
  <Layout>

    <Head title="Contas a Receber" />
    <PageHeader title="Contas a Receber" pageTitle="Financeiro" />

    <TableGrid :columns="cols" :data="rows" :tableTitle="'Contas a Receber'" :showCheckbox="false" :search="true"
      :showAddButton="false" :showStatus="false" :showActions="true"
      :actionsConfig="{ delete: false, edit: false, show: false, diary: false, print: false, download: false, restore: false, receive: canReceive }"
      @receive="onReceive" />

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
  </Layout>
</template>

<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { toRef, ref, computed } from "vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
  contas: { type: Array, default: () => [] },
});

const rows = toRef(props, "contas");

function formatCurrency(n) {
  const v = Number(n || 0);
  try {
    return v.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  } catch (_) {
    return `R$ ${v.toFixed(2)}`.replace(".", ",");
  }
}

const cols = [
  { id: "id", name: "Faturamento" },
  { id: "nu_pagamento", name: "Nº Pagamento" },
  { id: "tipo_convenio", name: "Convênio" },
  { id: "paciente", name: "Paciente" },
  { id: "procedimento", name: "Procedimento" },
  { id: "vencimento", name: "Vencimento" },
  { id: "valor", name: "Valor", formatter: (cell) => formatCurrency(cell) },
  { id: "data_pagamento", name: "Dt Pagamento" },
  { id: "status", name: "Status" },
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
</script>

<style scoped>
:deep(.table thead th:nth-child(1)),
:deep(.table tbody td:nth-child(1):not([colspan])) {
  display: none;
}
</style>
