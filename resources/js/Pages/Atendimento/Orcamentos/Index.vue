<template>
    <Layout>

        <Head title="Orçamentos" />
        <PageHeader title="Orçamento" pageTitle="Atendimento" />

        <div class="row">
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Paciente</label>
                                <span class="text-danger ms-1">*</span>
                                <select data-choices v-model="form.paciente_id" class="form-select" ref="selPaciente"
                                    :disabled="locked" required :class="{ 'is-invalid': !!form.errors.paciente_id }">
                                    <option value="">Selecione</option>
                                </select>
                                <div v-if="form.errors.paciente_id" class="invalid-feedback d-block">{{
                                    form.errors.paciente_id }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Convênio</label>
                                <span class="text-danger ms-1">*</span>
                                <select data-choices v-model="form.convenio_id" class="form-select" ref="selConvenio"
                                    :disabled="locked || convenioLoading" required
                                    :class="{ 'is-invalid': !!form.errors.convenio_id }">
                                    <option :value="null">Selecione</option>
                                    <option v-for="c in conveniosPacienteLocal" :key="c.id" :value="c.id">{{ c.descricao }}
                                    </option>
                                </select>
                                <div v-if="form.errors.convenio_id" class="invalid-feedback d-block">{{
                                    form.errors.convenio_id }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Procedimentos</label>
                                <span class="text-danger ms-1">*</span>
                                <select v-model="selectedProcId" class="form-select" data-choices :disabled="locked"
                                    @change="onSelectProcedure" required :class="{ 'is-invalid': !!form.errors.itens }">
                                    <option :value="null">Buscar procedimento</option>
                                    <option v-for="p in procedimentosLocal" :key="p.id" :value="p.id">{{ p.nome }}
                                    </option>
                                </select>
                                <div v-if="form.errors.itens" class="invalid-feedback d-block">{{ form.errors.itens }}</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Data de Emissão</label>
                                <flatPickr v-model="form.data_emissao" class="form-control" :config="flatpickrOptions"
                                    :disabled="locked" placeholder="Selecione a data" />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Validade</label>
                                <flatPickr v-model="form.validade" class="form-control" :config="flatpickrOptions"
                                    :disabled="locked" placeholder="Selecione a data" />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Desconto (R$)</label>
                                <input :value="formatCurrency(form.desconto)" type="text" class="form-control"
                                    :disabled="locked" @input="onCurrencyInputForm($event, 'desconto')" />
                            </div>

                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check">
                                    <input v-model="form.faturamento_previsto" class="form-check-input" type="checkbox"
                                        :disabled="locked" id="fatPrev" />
                                    <label class="form-check-label" for="fatPrev">Faturamento Previsto</label>
                                </div>
                            </div>
                        </div>



                        <hr class="my-4" />



                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%">Procedimento</th>
                                        <th style="width: 15%">Quantidade</th>
                                        <th style="width: 20%">Valor Unitário</th>
                                        <th style="width: 20%">Valor Total</th>
                                        <th style="width: 10%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="(it, idx) in itensLocal" :key="`group-${idx}`">
                                        <tr>
                                            <td>
                                                <span>{{ procedimentoNome(it.procedimento_id) }}</span>
                                            </td>
                                            <td>
                                                <span>{{ it.quantidade }}</span>
                                            </td>
                                            <td>
                                                <span>{{ formatCurrency(it.valor_unitario) }}</span>
                                            </td>
                                            <td>
                                                <span>{{ formatCurrency(it.valor_total) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-outline-danger btn-sm" type="button"
                                                    :disabled="locked" @click="removeItem(idx)">Remover</button>
                                            </td>
                                        </tr>
                                        <tr v-for="n in sessionCount(it.procedimento_id)" :key="`sess-${idx}-${n}`"
                                            class="session-row">
                                            <td colspan="4">
                                                <div class="session-line">
                                                    <span class="session-dot"></span>
                                                    <span class="session-badge"><i
                                                            class="ri-calendar-line me-1"></i>Sessão {{ n }}/{{
                                                        sessionCount(it.procedimento_id) }}</span>
                                                    <span class="session-text">Etapa do tratamento</span>
                                                </div>
                                            </td>
                                            <td></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="p-3 border rounded">
                                    <div class="d-flex justify-content-between">
                                        <span>Valor Bruto</span>
                                        <strong>{{ formatCurrency(valorBruto) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Desconto</span>
                                        <strong>- {{ formatCurrency(form.desconto || 0) }}</strong>
                                    </div>
                                    <hr />
                                    <div class="d-flex justify-content-between">
                                        <span>Valor Total</span>
                                        <strong>{{ formatCurrency(valorTotal) }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8 text-end d-flex align-items-end justify-content-end">
                                <button class="btn btn-soft-info me-2" type="button" @click="openConsultModal">Consultar
                                    Orçamento</button>
                                <button class="btn btn-success" type="button" :disabled="saveProcessing || locked"
                                    @click="save">
                                    <span v-if="saveProcessing" class="spinner-border spinner-border-sm me-2" />
                                    Salvar Orçamento
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Últimos Orçamentos</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li v-for="o in ultimosLocal" :key="o.id"
                                class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="d-flex flex-column">
                                    <span class="fw-bold">{{ o.numero }}</span>
                                    <span class="text-muted small">{{ o.criado_em }}</span>
                                </span>
                                <span class="d-flex gap-2">
                                    <button class="btn btn-sm btn-soft-info" type="button"
                                        @click="handleConsultEdit(o.id)" :disabled="o.pago"
                                        :title="o.pago ? 'Pagamento confirmado' : 'Editar'">
                                        <i class="ri-pencil-fill align-bottom"></i>
                                    </button>
                                    <button class="btn btn-sm btn-soft-primary" type="button"
                                        @click="handleUltimoApprove(o.id)" :disabled="o.aprovado || o.pago"
                                        :title="o.pago ? 'Pagamento confirmado' : (o.aprovado ? 'Já aprovado' : 'Aprovar')">
                                        <i class="ri-check-fill align-bottom"></i>
                                    </button>
                                    <button class="btn btn-sm btn-soft-warning" type="button"
                                        @click="handleConsultPrint(o.id)" title="Imprimir">
                                        <i class="ri-printer-fill align-bottom"></i>
                                    </button>
                                </span>
                            </li>
                            <li v-if="!ultimosLocal || ultimosLocal.length === 0" class="list-group-item text-muted">
                                Sem orçamentos recentes
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <Modal v-model="consultModal" :title="'Consultar Orçamento'" :name-button="'Fechar'" :processing="false"
            size="xl" @save="closeConsultModal">
            <div class="border border-dashed border-end-0 border-start-0 px-0 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="search-box" style="width: 360px;">
                        <input v-model="consultQuery" type="text" class="form-control search"
                            placeholder="Buscar por número, nome ou CPF" />
                        <i class="ri-search-line search-icon"></i>
                    </div>
                </div>
            </div>
            <TableGrid :columns="consultColumns" :data="orcamentosConsultaGrid" :tableTitle="'Resultados da Busca'"
                :search="false" :showCheckbox="false" :showAddButton="false" :showStatus="false" :showActions="true"
                :actionsConfig="{ delete: false, edit: true, show: false, diary: false, print: true, download: true }"
                @edit="handleConsultEdit" @print="handleConsultPrint" @download="handleConsultDownload" />
        </Modal>
        <Modal v-model="approveSuccessModal" :title="'Sucesso'" :name-button="'Ver Orçamento'" :processing="false"
            size="md" @save="viewApprovedBudget">
            <div class="text-center py-2">
                <div class="mx-auto rounded-circle bg-success-subtle"
                    style="width:72px;height:72px;display:flex;align-items:center;justify-content:center;">
                    <i class="ri-check-fill text-success" style="font-size:28px;"></i>
                </div>
                <h5 class="mt-3 mb-1">Orçamento aprovado</h5>
                <p class="text-muted mb-3">Número {{ approveSuccessInfo.numero || '—' }} foi aprovado com sucesso.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-soft-warning btn-sm" type="button" @click="printApprovedBudget">
                        <i class="ri-printer-fill align-bottom me-1"></i>Imprimir
                    </button>
                </div>
            </div>
        </Modal>
    </Layout>
    <div ref="pdfContainer" style="position: fixed; left: -10000px; top: -10000px; opacity: 0; pointer-events: none;">
        <OrcamentoPrint :orcamento="downloadOrcamento" :itens="downloadItens" :autoPrint="false" />
    </div>
</template>

<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { ref, watch, computed, nextTick } from "vue";
import Modal from "@/Components/Modal.vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.min.css";
import "flatpickr/dist/l10n/pt.js";
import html2pdf from "html2pdf.js";
import { html } from "gridjs";
import OrcamentoPrint from "@/Pages/Atendimento/Orcamentos/OrcamentoPrint.vue";
import { useChoicesRemoteSearch } from "@/Composables/useChoicesRemoteSearch";

const selPaciente = ref(null);
const selConvenio = ref(null);

const props = defineProps({
    pacientes: { type: Array, default: () => [] },
    convenios: { type: Array, default: () => [] },
    procedimentos: { type: Array, default: () => [] },
    procedimentoConvenio: { type: Array, default: () => [] },
    ultimos: { type: Array, default: () => [] },
});

const pacientesLocal = ref([...(props.pacientes || [])]);
const conveniosLocal = ref([...(props.convenios || [])]);
const conveniosPacienteLocal = ref([]);
const convenioLoading = ref(false);
const procedimentosLocal = ref([...(props.procedimentos || [])]);
const procConvLocal = ref([...(props.procedimentoConvenio || [])]);
const ultimosLocal = ref([...(props.ultimos || [])]);
const pdfContainer = ref(null);
const downloadOrcamento = ref({});
const downloadItens = ref([]);
const approveSuccessModal = ref(false);
const approveSuccessInfo = ref({ numero: null });
const approveSuccessId = ref(null);

watch(() => props.pacientes, v => pacientesLocal.value = [...(v || [])]);
watch(() => props.convenios, v => conveniosLocal.value = [...(v || [])]);
watch(() => props.procedimentos, v => procedimentosLocal.value = [...(v || [])]);
watch(() => props.procedimentoConvenio, v => procConvLocal.value = [...(v || [])]);
watch(() => props.ultimos, v => ultimosLocal.value = [...(v || [])]);

function refreshChoices(el) {
    try {
        if (!el) return;
        if (window.destroyChoiceEl) {
            try { window.destroyChoiceEl(el); } catch (e) { }
        } else {
            const inst = el._choicesInstance || el.choices;
            if (inst && typeof inst.destroy === "function") {
                try { inst.destroy(); } catch (e) { }
            }
            try { delete el._choicesInstance; } catch (e) { }
            try { el.dataset.choicesInitialized = "false"; } catch (e) { }
        }
        if (window.initChoiceEl) window.initChoiceEl(el);
    } catch (e) { }
}

let convenioReqSeq = 0;
function getChoicesWrapperFor(el) {
    try {
        if (!el) return null;
        const closest = el.closest ? el.closest('.choices') : null;
        if (closest) return closest;
        const parent = el.parentElement;
        if (!parent) return null;
        const kids = Array.from(parent.children || []);
        const idx = kids.indexOf(el);
        if (idx >= 0) {
            for (let i = idx + 1; i < kids.length; i += 1) {
                if (kids[i]?.classList?.contains('choices')) return kids[i];
            }
            for (let i = idx - 1; i >= 0; i -= 1) {
                if (kids[i]?.classList?.contains('choices')) return kids[i];
            }
        }
        return parent.querySelector('.choices');
    } catch (e) {
        return null;
    }
}
async function syncConvenioLoadingUI() {
    try {
        await nextTick();
        const el = selConvenio.value;
        if (!el) return;
        const w = getChoicesWrapperFor(el);
        if (!w) return;
        w.classList.toggle('convenio-loading', !!convenioLoading.value);
    } catch (e) { }
}

const pacientesChoicesRows = ref([]);
const pacienteSelectedRow = ref(null);

function findPacienteLocalById(id) {
    const sid = String(id ?? "");
    if (!sid) return null;
    const inRows = (pacientesChoicesRows.value || []).find(p => String(p?.id) === sid);
    if (inRows) return inRows;
    const inProps = (pacientesLocal.value || []).find(p => String(p?.id) === sid);
    return inProps || null;
}

const pacienteSearch = useChoicesRemoteSearch({
    selectRef: selPaciente,
    refreshChoices,
    attachRetries: 200,
    getSelectedValue: () => (form.paciente_id != null ? String(form.paciente_id) : ""),
    getRows: () => pacientesChoicesRows.value,
    fetchRows: async (q) => {
        const query = String(q || "").trim();
        if (!query) {
            const keep = pacienteSelectedRow.value ? [pacienteSelectedRow.value] : [];
            pacientesChoicesRows.value = keep;
            return keep;
        }
        const resp = await window.axios.get("/pacientes/search", { params: { q: query } });
        const rows = Array.isArray(resp?.data?.pacientes) ? resp.data.pacientes : [];
        const sel = pacienteSelectedRow.value;
        const withSelected = sel && !rows.some(p => String(p?.id) === String(sel?.id)) ? [sel, ...rows] : rows;
        pacientesChoicesRows.value = withSelected;
        return withSelected;
    },
    makeLabel: (p) => `${p?.nome || ""}${p?.cpf ? ` • ${p.cpf}` : ""}`,
    placeholderLabel: "Selecione",
    placeholderDisabled: true,
});

const convenioChoices = useChoicesRemoteSearch({
    selectRef: selConvenio,
    refreshChoices,
    attachSearch: false,
    getSelectedValue: () => (form.convenio_id != null ? String(form.convenio_id) : ""),
    getRows: () => conveniosPacienteLocal.value,
    fetchRows: async () => conveniosPacienteLocal.value,
    makeLabel: (c) => `${c?.descricao || ""}`,
    placeholderLabel: "Selecione",
    placeholderDisabled: false,
});

const form = useForm({
    paciente_id: "",
    convenio_id: null,
    data_emissao: formatDMY(new Date()),
    validade: formatDMY(new Date(Date.now() + 30 * 24 * 3600 * 1000)),
    desconto: 0,
    faturamento_previsto: false,
    aprovado: false,
    pago: false,
    itens: [],
});
const isEditing = ref(false);
const orcamentoEditId = ref(null);
const keepConvenioOnPacienteChange = ref(false);

function formatDMY(d) {
    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const yyyy = d.getFullYear();
    return `${dd}-${mm}-${yyyy}`;
}

const flatpickrOptions = { altInput: true, altFormat: "d M, Y", dateFormat: "d-m-Y", locale: "pt" };
const itensLocal = ref([]);
const selectedProcId = ref(null);

function addItem() {
    itensLocal.value.push({
        procedimento_id: null,
        quantidade: 1,
        valor_unitario: 0,
        valor_total: 0,
        observacoes: null,
    });
}

function removeItem(idx) {
    itensLocal.value.splice(idx, 1);
}

function onSelectProcedure() {
    if (!selectedProcId.value) return;
    addSelectedProcedure();
}

function addSelectedProcedure() {
    const pid = selectedProcId.value;
    if (!pid) return;
    let valorUnit = findValorProcedimento(pid);
    const vConv = findValorConvenio(pid, form.convenio_id);
    if (vConv !== null && vConv !== undefined) {
        valorUnit = vConv;
    }
    const item = {
        procedimento_id: pid,
        quantidade: 1,
        valor_unitario: Number(valorUnit || 0),
        valor_total: Number(valorUnit || 0),
        observacoes: null,
    };
    itensLocal.value.push(item);
    selectedProcId.value = null;
}

function findValorConvenio(procedimentoId, convenioId) {
    if (!procedimentoId || !convenioId) return null;
    const row = procConvLocal.value.find(r => String(r.procedimento_id) === String(procedimentoId) && String(r.convenio_id) === String(convenioId));
    return row ? row.valor_convenio : null;
}

function findValorProcedimento(procedimentoId) {
    const p = procedimentosLocal.value.find(x => String(x.id) === String(procedimentoId));
    return p ? (p.valor ?? 0) : 0;
}

function procedimentoNome(procedimentoId) {
    const p = procedimentosLocal.value.find(x => String(x.id) === String(procedimentoId));
    return p ? (p.nome || '-') : '-';
}

function sessionCount(procedimentoId) {
    const p = procedimentosLocal.value.find(x => String(x.id) === String(procedimentoId));
    const n = Number(p?.quantidade_sessoes || 0);
    return p?.eh_tratamento ? (n > 0 ? n : 0) : 0;
}

function recalcItem(idx) {
    const it = itensLocal.value[idx];
    if (!it) return;
    let valorUnit = findValorProcedimento(it.procedimento_id);
    const vConv = findValorConvenio(it.procedimento_id, form.convenio_id);
    if (vConv !== null && vConv !== undefined) {
        valorUnit = vConv;
    }
    it.valor_unitario = Number(valorUnit || 0);
    const qtd = Number(it.quantidade || 1);
    it.valor_total = Number((it.valor_unitario || 0) * qtd);
}

watch(() => [itensLocal.value, form.convenio_id], () => {
    if (!isEditing.value) {
        itensLocal.value.forEach((_, i) => recalcItem(i));
    }
}, { deep: true });

watch(() => form.convenio_id, (v) => {
    const ok = !!String(v ?? "").trim();
    if (ok) {
        try { form.clearErrors('convenio_id'); } catch (e) { }
    }
});

watch(() => (Array.isArray(itensLocal.value) ? itensLocal.value.length : 0), (n) => {
    if (Number(n || 0) > 0) {
        try { form.clearErrors('itens'); } catch (e) { }
    }
});

watch(() => form.faturamento_previsto, async (v) => {
    if (v) {
        // não auto-seleciona convênio; lista vem do paciente selecionado
    } else {
        form.convenio_id = null;
        await convenioChoices.syncChoices();
        await syncConvenioLoadingUI();
    }
    if (!isEditing.value) {
        itensLocal.value.forEach((_, i) => recalcItem(i));
    }
});

watch(() => form.paciente_id, async (nv, ov) => {
    if (String(nv ?? "") === String(ov ?? "")) return;
    if (String(nv ?? "").trim()) {
        try { form.clearErrors('paciente_id'); } catch (e) { }
    }
    const reqId = ++convenioReqSeq;
    convenioLoading.value = !!nv;
    await syncConvenioLoadingUI();
    if (nv) {
        let selected = findPacienteLocalById(nv);
        if (!selected) {
            try {
                const resp = await window.axios.get("/pacientes/search", { params: { q: String(nv) } });
                const rows = Array.isArray(resp?.data?.pacientes) ? resp.data.pacientes : [];
                selected = rows.find(p => String(p?.id) === String(nv)) || rows[0] || null;
            } catch (e) { }
        }
        pacienteSelectedRow.value = selected ? { ...selected } : null;
        pacientesChoicesRows.value = pacienteSelectedRow.value ? [pacienteSelectedRow.value] : [];
    } else {
        pacienteSelectedRow.value = null;
        pacientesChoicesRows.value = [];
    }
    await pacienteSearch.syncChoices();
    pacienteSearch.clearSearch();
    const desiredConvenio = form.convenio_id;
    if (!keepConvenioOnPacienteChange.value) {
        form.convenio_id = null;
    }
    conveniosPacienteLocal.value = [];
    await convenioChoices.syncChoices();
    await syncConvenioLoadingUI();
    try {
        if (!nv) {
            if (reqId === convenioReqSeq) convenioLoading.value = false;
            if (reqId === convenioReqSeq) await syncConvenioLoadingUI();
            if (reqId === convenioReqSeq) await convenioChoices.syncChoices();
            return;
        }
        const resp = await window.axios.get(`/pacientes/${nv}/convenios`);
        if (reqId !== convenioReqSeq) return;
        const convs = Array.isArray(resp?.data?.convenios) ? resp.data.convenios : [];
        conveniosPacienteLocal.value = convs;

        if (keepConvenioOnPacienteChange.value) {
            const ok = convs.some(c => String(c.id) === String(desiredConvenio));
            form.convenio_id = ok ? desiredConvenio : null;
        }
    } catch (e) {
        if (reqId !== convenioReqSeq) return;
        conveniosPacienteLocal.value = [];
        if (keepConvenioOnPacienteChange.value) form.convenio_id = null;
    } finally {
        if (reqId === convenioReqSeq) {
            convenioLoading.value = false;
            await syncConvenioLoadingUI();
            await convenioChoices.syncChoices();
        }
    }
});

const valorBruto = computed(() => {
    return itensLocal.value.reduce((sum, it) => sum + Number(it.valor_total || 0), 0);
});

const valorTotal = computed(() => {
    const d = Number(form.desconto || 0);
    return Math.max(0, Number(valorBruto.value || 0) - d);
});

const locked = computed(() => {
    try {
        const apr = !!form.aprovado;
        const pago = !!form.pago;
        const s = String(form.validade || '').trim();
        let exp = false;
        if (s && s.includes('-')) {
            const parts = s.split('-');
            const dd = Number(parts[0]);
            const mm = Number(parts[1]);
            const yyyy = Number(parts[2]);
            const v0 = new Date(yyyy, mm - 1, dd);
            const now = new Date();
            const n0 = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            exp = n0.getTime() > v0.getTime();
        }
        return apr || exp || pago;
    } catch (e) {
        return !!form.aprovado || !!form.pago;
    }
});

function formatCurrency(v) {
    const n = Number(v || 0);
    return n.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

function parseCurrencyToNumber(str) {
    const digits = String(str || '').replace(/\D/g, '');
    if (!digits) return 0;
    return Number(digits) / 100;
}

function onCurrencyInputForm(e, field) {
    const v = parseCurrencyToNumber(e.target.value);
    form[field] = v;
    e.target.value = formatCurrency(v);
}

function onCurrencyInputItem(e, idx) {
    const v = parseCurrencyToNumber(e.target.value);
    const it = itensLocal.value[idx];
    if (!it) return;
    it.valor_unitario = v;
    e.target.value = formatCurrency(v);
    recalcItem(idx);
}

const saveProcessing = ref(false);
async function save() {
    if (locked.value) { return; }
    try { form.clearErrors(); } catch (e) { }
    const hasPaciente = !!String(form.paciente_id ?? "").trim();
    const hasConvenio = !!String(form.convenio_id ?? "").trim();
    const hasItens = Array.isArray(itensLocal.value) && itensLocal.value.length > 0;
    if (!hasPaciente) {
        try { form.setError('paciente_id', 'Paciente é obrigatório.'); } catch (e) { }
    }
    if (!hasConvenio) {
        try { form.setError('convenio_id', 'Convênio é obrigatório.'); } catch (e) { }
    }
    if (!hasItens) {
        try { form.setError('itens', 'Selecione ao menos um procedimento.'); } catch (e) { }
    }
    if (!hasPaciente || !hasConvenio || !hasItens) return;
    form.itens = itensLocal.value.map(it => ({
        procedimento_id: it.procedimento_id,
        quantidade: it.quantidade,
        valor_unitario: it.valor_unitario,
        valor_total: it.valor_total,
        observacoes: it.observacoes,
    }));
    form.transform((d) => ({
        ...d,
        paciente_id: d.paciente_id ? Number(d.paciente_id) : null,
        convenio_id: d.convenio_id ? Number(d.convenio_id) : null,
    }));
    saveProcessing.value = true;
    try {
        const submit = isEditing.value && orcamentoEditId.value
            ? form.put(`/orcamentos/${orcamentoEditId.value}`, {
                preserveScroll: true,
                onSuccess: () => {
                    // limpar após atualizar
                    itensLocal.value = [];
                    selectedProcId.value = null;
                    isEditing.value = false;
                    orcamentoEditId.value = null;
                    form.reset();
                    form.clearErrors();
                    form.paciente_id = "";
                    form.convenio_id = null;
                    form.data_emissao = formatDMY(new Date());
                    form.validade = formatDMY(new Date(Date.now() + 30 * 24 * 3600 * 1000));
                    form.desconto = 0;
                    form.faturamento_previsto = false;
                    form.aprovado = false;
                    form.itens = [];
                },
                onFinish: () => {
                    saveProcessing.value = false;
                },
            })
            : form.post("/orcamentos", {
                preserveScroll: true,
                onSuccess: () => {
                    itensLocal.value = [];
                    selectedProcId.value = null;
                    form.reset();
                    form.clearErrors();
                    form.paciente_id = "";
                    form.convenio_id = null;
                    form.data_emissao = formatDMY(new Date());
                    form.validade = formatDMY(new Date(Date.now() + 30 * 24 * 3600 * 1000));
                    form.desconto = 0;
                    form.faturamento_previsto = false;
                    form.aprovado = false;
                    form.itens = [];
                },
                onFinish: () => {
                    saveProcessing.value = false;
                },
            });
        await submit;
    } catch (e) {
        saveProcessing.value = false;
    }
}

const consultModal = ref(false);
const orcamentosConsulta = ref([]);
const consultQuery = ref('');
const consultColumns = [
    { id: 'numero', name: 'Número' },
    { id: 'data_emissao', name: 'Emissão' },
    { id: 'validade', name: 'Validade' },
    { id: 'paciente', name: 'Paciente' },
    { id: 'cpf', name: 'CPF' },
    {
        id: 'status', name: 'Status', formatter: (cell) => {
            const v = String(cell || '').toLowerCase();
            const text = cell || '-';
            let cls = 'bg-light text-dark';
            if (v === 'aprovado') cls = 'bg-success-subtle text-success';
            else if (v === 'aguardando aprovação' || v === 'pendente') cls = 'bg-warning-subtle text-warning';
            return html(`<span class="badge ${cls}">${text}</span>`);
        }
    },
    { id: 'total', name: 'Total' },
];
const orcamentosConsultaGrid = computed(() => {
    return (orcamentosConsulta.value || []).map(o => ({
        id: o.id,
        numero: o.numero,
        data_emissao: o.data_emissao,
        validade: o.validade,
        paciente: o.paciente,
        cpf: o.cpf,
        status: o.aprovado ? 'Aprovado' : 'Aguardando aprovação',
        total: formatCurrency(o.valor_total),
    }));
});
function openConsultModal() {
    consultModal.value = true;
}
function closeConsultModal() {
    consultModal.value = false;
    orcamentosConsulta.value = [];
    consultQuery.value = '';
}
function execConsultSearch() {
    const q = String(consultQuery.value || '').trim();
    if (q.length < 2) {
        orcamentosConsulta.value = [];
        return;
    }
    const params = { q };
    orcamentosConsulta.value = [];
    try {
        window.axios.get(`/orcamentos/search`, { params }).then((res) => {
            const arr = Array.isArray(res?.data?.orcamentos) ? res.data.orcamentos : [];
            orcamentosConsulta.value = arr;
        }).catch(() => { });
    } catch (e) { }
}
let _searchT = null;
watch(consultQuery, (v) => {
    if (_searchT) clearTimeout(_searchT);
    _searchT = setTimeout(() => execConsultSearch(), 350);
});
function handleConsultEdit(id) {
    if (!id) return;
    carregarOrcamento(id);
}
function handleConsultPrint(id) {
    const budgetId = typeof id === 'object' ? (id?.id ?? null) : id;
    if (!budgetId) return;
    try { window.open(`/orcamentos/${budgetId}/print`, '_blank'); } catch (e) { }
}
function handleUltimoApprove(id) {
    const budgetId = typeof id === 'object' ? (id?.id ?? null) : id;
    if (!budgetId) return;
    try {
        window.axios.put(`/orcamentos/${budgetId}/approve`).then(() => {
            try {
                const i = (ultimosLocal.value || []).findIndex(x => String(x.id) === String(budgetId));
                if (i >= 0) {
                    ultimosLocal.value[i].aprovado = true;
                    approveSuccessInfo.value = { numero: ultimosLocal.value[i]?.numero || null };
                }
            } catch (e) { }
            approveSuccessId.value = budgetId;
            approveSuccessModal.value = true;
        }).catch(() => { });
    } catch (e) { }
}
function viewApprovedBudget() {
    const id = approveSuccessId.value;
    if (id) handleConsultEdit(id);
    approveSuccessModal.value = false;
}
function printApprovedBudget() {
    const id = approveSuccessId.value;
    if (id) handleConsultPrint(id);
    approveSuccessModal.value = false;
}
function handleConsultDownload(id) {
    const budgetId = typeof id === 'object' ? (id?.id ?? null) : id;
    if (!budgetId) return;
    try {
        window.axios.get(`/orcamentos/${budgetId}`).then((res) => {
            const o = res?.data?.orcamento || {};
            const itens = Array.isArray(res?.data?.itens) ? res.data.itens : [];
            const p = pacientesLocal.value.find(px => String(px.id) === String(o.paciente_id)) || {};
            const orc = {
                ...o,
                paciente_nome: p?.nome || '',
                paciente_cpf: p?.cpf || '',
            };
            const itensNomes = itens.map(it => ({
                ...it,
                procedimento_nome: procedimentoNome(it.procedimento_id) || '-',
            }));
            downloadOrcamento.value = orc;
            downloadItens.value = itensNomes;
            nextTick(() => {
                const rootEl = pdfContainer.value;
                const el = rootEl && rootEl.querySelector ? (rootEl.querySelector('.paper') || rootEl) : rootEl;
                const filename = 'orcamento-' + (o?.numero || o?.id || budgetId) + '.pdf';
                const opt = {
                    margin: 10,
                    filename,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };
                try {
                    setTimeout(() => {
                        html2pdf().set(opt).from(el).save().then(() => {
                            downloadOrcamento.value = {};
                            downloadItens.value = [];
                        });
                    }, 50);
                } catch (e) { }
            });
        }).catch(() => { });
    } catch (e) { }
}
function carregarOrcamento(id) {
    if (!id) return;
    try {
        window.axios.get(`/orcamentos/${id}?include_all=1`).then((res) => {
            const o = res?.data?.orcamento;
            const itens = Array.isArray(res?.data?.itens) ? res.data.itens : [];
            if (!o) return;
            isEditing.value = true;
            orcamentoEditId.value = o.id;
            keepConvenioOnPacienteChange.value = true;
            form.paciente_id = o.paciente_id ?? null;
            form.convenio_id = o.convenio_id ?? null;
            if (o.paciente_id) {
                pacienteSelectedRow.value = {
                    id: o.paciente_id,
                    nome: o.paciente_nome || o.paciente || '',
                    cpf: o.paciente_cpf || '',
                };
                pacientesChoicesRows.value = [pacienteSelectedRow.value];
            } else {
                pacienteSelectedRow.value = null;
                pacientesChoicesRows.value = [];
            }
            pacienteSearch.syncChoices();
            form.data_emissao = o.data_emissao || formatDMY(new Date());
            form.validade = o.validade || formatDMY(new Date(Date.now() + 30 * 24 * 3600 * 1000));
            form.desconto = Number(o.desconto || 0);
            form.faturamento_previsto = !!o.faturamento_previsto;
            form.aprovado = !!o.aprovado;
            form.pago = !!o.pago;
            itensLocal.value = itens.map(it => ({
                procedimento_id: it.procedimento_id,
                quantidade: it.quantidade,
                valor_unitario: Number(it.valor_unitario || 0),
                valor_total: Number(it.valor_total || 0),
                observacoes: it.observacoes || null,
            }));
            setTimeout(() => {
                try {
                    pacienteSearch.syncChoices();
                    convenioChoices.syncChoices();
                    syncConvenioLoadingUI();
                } catch (e) { }
            }, 0);
            setTimeout(() => {
                keepConvenioOnPacienteChange.value = false;
            }, 300);
            closeConsultModal();
        }).catch(() => { });
    } catch (e) { }
}
</script>
<style scoped>
.session-row td {
    background: #ffffff;
    font-size: 12px;
    color: #6c757d;
    padding: 6px 12px 6px 20px;
    border-top: 1px dashed rgba(9, 152, 133, 0.2);
    position: relative;
}

.session-row td::before {
    content: "";
    position: absolute;
    left: 10px;
    top: 6px;
    bottom: 6px;
    width: 2px;
    background: rgba(9, 152, 133, 0.25);
    border-radius: 2px;
}

.session-line {
    display: flex;
    align-items: center;
    gap: 8px;
}

.session-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #099885;
    box-shadow: 0 0 0 3px rgba(9, 152, 133, 0.12) inset, 0 0 0 2px rgba(9, 152, 133, 0.12);
}

.session-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 999px;
    background: rgba(9, 152, 133, 0.12);
    color: #099885;
    font-weight: 500;
    font-size: 11px;
    margin-right: 8px;
}

.session-text {
    color: #6c757d;
    font-size: 12px;
}

:deep(.choices.convenio-loading) {
    pointer-events: none;
}

:deep(.choices.convenio-loading .choices__inner) {
    position: relative;
}

:deep(.choices.convenio-loading .choices__inner::after) {
    content: "Carregando...";
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
    color: var(--vz-secondary-color, #6c757d);
}

:deep(.choices.convenio-loading .choices__list--single) {
    opacity: 0.55;
}
</style>
