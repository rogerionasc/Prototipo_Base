<template>
    <Layout>
        <Head title="Convenio" />
        <PageHeader title="Convenio" pageTitle="Menu" />
        <TableGrid :columns="columns" :data="conveniosLocal" :tableTitle="'Todos os Convenios'" :showStatus="false"
            :searchPlaceholder="'Buscar por convenio'" @modalDdeletarMultiplos="openModalDeleteMulti"
            @delete="openModalDelete" @edit="openModalEdit" @show="openModalShow" @add="openModalAdd" />
  </Layout>
</template>

<script lang="js" setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { toRef, ref, computed } from "vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";

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

const columns = [
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
