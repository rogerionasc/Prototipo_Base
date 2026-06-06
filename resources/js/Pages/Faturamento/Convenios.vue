<template>
  <Layout>
    <Head title="Faturamento Convênios" />
    <PageHeader title="Faturamento Convênios" pageTitle="Faturamento" />

    <TableGrid
      :columns="cols"
      :data="rows"
      :tableTitle="'Faturamentos (Convênios)'"
      :showCheckbox="false"
      :search="true"
      :showAddButton="false"
      :showStatus="false"
      :showActions="true"
      :actionsConfig="{ delete: false, edit: true, show: false, diary: false, print: false, download: false, restore: false, receive: false }"
      @edit="onEdit"
    />

    <Modal v-model="showEdit" title="Atualizar Faturamento (Convênio)" name-button="Salvar" :processing="editForm.processing" size="md" @save="salvar">
      <div class="row g-3">
        <div class="col-12">
          <div class="d-flex flex-column">
            <span class="text-muted">Convênio</span>
            <span class="fw-semibold">{{ editInfo.convenio }}</span>
          </div>
        </div>
        <div class="col-12">
          <div class="d-flex flex-column">
            <span class="text-muted">Paciente</span>
            <span class="fw-semibold">{{ editInfo.paciente }}</span>
          </div>
        </div>

        <div class="col-12">
          <label class="form-label">Status</label>
          <select v-model="editForm.status" class="form-select">
            <option v-for="s in statusOptions" :key="s" :value="s">{{ s }}</option>
          </select>
        </div>

        <div class="col-12">
          <label class="form-label">Vencimento</label>
          <input v-model="editForm.vencimento" type="date" class="form-control" />
        </div>

        <div class="col-12">
          <label class="form-label">Valor cobrado</label>
          <input v-model="editForm.valor_cobrado" type="text" class="form-control" />
        </div>
        <div class="col-12">
          <label class="form-label">Valor aprovado</label>
          <input v-model="editForm.valor_aprovado" type="text" class="form-control" />
        </div>
        <div class="col-12">
          <label class="form-label">Valor glosado</label>
          <input v-model="editForm.valor_glosado" type="text" class="form-control" />
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
  faturamentos: { type: Array, default: () => [] },
});

const rows = toRef(props, "faturamentos");

function formatCurrency(n) {
  const v = Number(n || 0);
  try {
    return v.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  } catch (_) {
    return `R$ ${v.toFixed(2)}`.replace(".", ",");
  }
}

const cols = [
  { id: "id", name: "ID" },
  { id: "convenio", name: "Convênio" },
  { id: "paciente", name: "Paciente" },
  { id: "numero_orcamento", name: "Orçamento" },
  { id: "data_faturamento", name: "Faturado em" },
  { id: "vencimento", name: "Vencimento" },
  { id: "valor_cobrado", name: "Cobrado", formatter: (cell) => formatCurrency(cell) },
  { id: "valor_aprovado", name: "Aprovado", formatter: (cell) => formatCurrency(cell) },
  { id: "valor_glosado", name: "Glosa", formatter: (cell) => formatCurrency(cell) },
  { id: "status", name: "Status" },
];

const statusOptions = [
  "AGUARDANDO_ENVIO",
  "ENVIADO",
  "EM_ANALISE",
  "APROVADO",
  "GLOSADO",
  "RECEBIDO",
  "CANCELADO",
];

const showEdit = ref(false);
const editId = ref(null);

const editForm = useForm({
  status: "AGUARDANDO_ENVIO",
  vencimento: null,
  valor_cobrado: "",
  valor_aprovado: "",
  valor_glosado: "",
});

const editInfo = computed(() => {
  const id = editId.value;
  const r = (rows.value || []).find(x => String(x.id) === String(id)) || {};
  return {
    convenio: r?.convenio || "—",
    paciente: r?.paciente || "—",
  };
});

function onEdit(id) {
  editId.value = id;
  const r = (rows.value || []).find(x => String(x.id) === String(id)) || {};
  editForm.status = r?.status || "AGUARDANDO_ENVIO";
  editForm.vencimento = toDateInput(r?.vencimento);
  editForm.valor_cobrado = String(r?.valor_cobrado ?? "");
  editForm.valor_aprovado = String(r?.valor_aprovado ?? "");
  editForm.valor_glosado = String(r?.valor_glosado ?? "");
  showEdit.value = true;
}

function toDateInput(dmy) {
  const s = String(dmy || "").trim();
  if (!s) return null;
  const m = s.match(/^(\d{2})-(\d{2})-(\d{4})$/);
  if (!m) return null;
  return `${m[3]}-${m[2]}-${m[1]}`;
}

function parseNumberOrNull(v) {
  const s = String(v ?? "").trim();
  if (!s) return null;
  const n = Number(s.replace(/\./g, "").replace(",", "."));
  return Number.isNaN(n) ? null : n;
}

function salvar() {
  const id = editId.value;
  if (!id) { showEdit.value = false; return; }
  editForm
    .transform((d) => ({
      ...d,
      valor_cobrado: parseNumberOrNull(d.valor_cobrado),
      valor_aprovado: parseNumberOrNull(d.valor_aprovado),
      valor_glosado: parseNumberOrNull(d.valor_glosado),
    }))
    .put(`/faturamentos/${id}/convenio`, {
    onSuccess: async () => {
      showEdit.value = false;
      editId.value = null;
      await new Promise((resolve) => {
        router.reload({ only: ["faturamentos"], onFinish: () => resolve() });
      });
    },
    onError: () => {
      showEdit.value = false;
    }
  });
}
</script>

<style scoped>
:deep(.table thead th:nth-child(1)),
:deep(.table tbody td:nth-child(1)) { display: none; }
</style>
