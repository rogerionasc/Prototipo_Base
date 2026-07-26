<template>
    <Layout>

        <Head title="Orçamentos" />
        <PageHeader title="Orçamento" pageTitle="Atendimento" />

        <!-- Top Actions removed, button moved to Recent Quotes header -->

        <div class="row">
            <!-- Left Column: Main Form -->
            <div class="col-lg-8">
                
                <!-- Card 1: Patient Data -->
                <div class="card mb-3">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Dados do Paciente</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Paciente <span class="text-danger">*</span></label>
                                <select data-choices v-model="form.paciente_id" class="form-select" ref="selPaciente"
                                    :disabled="locked" required :class="{ 'is-invalid': !!form.errors.paciente_id }">
                                    <option value="">Buscar paciente...</option>
                                </select>
                                <div v-if="form.errors.paciente_id" class="invalid-feedback d-block">{{ form.errors.paciente_id }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Convênio Vinculado <span class="text-danger">*</span></label>
                                <select data-choices v-model="form.convenio_id" class="form-select" ref="selConvenio"
                                    :disabled="locked || convenioLoading" required :class="{ 'is-invalid': !!form.errors.convenio_id }">
                                    <option :value="null">Selecione o convênio...</option>
                                    <option v-for="c in conveniosPacienteLocal" :key="c.id" :value="c.id">{{ c.descricao }}</option>
                                </select>
                                <div v-if="form.errors.convenio_id" class="invalid-feedback d-block">{{ form.errors.convenio_id }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Data de Emissão</label>
                                <flatPickr v-model="form.data_emissao" class="form-control" :config="flatpickrOptions" :disabled="locked" placeholder="Selecione a data" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Validade do Orçamento</label>
                                <flatPickr v-model="form.validade" class="form-control" :config="flatpickrOptions" :disabled="locked" placeholder="Selecione a data" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Procedures -->
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Procedimentos</h4>
                    </div>
                    
                    <div class="card-body">
                        <!-- Search Bar -->
                        <div class="mb-3">
                            <label class="form-label">Buscar Procedimento</label>
                            <select ref="selProcedimento" v-model="selectedProcId" class="form-select" data-choices :disabled="locked || procedimentosSelectLoading"
                                @change="onSelectProcedure" required :class="{ 'is-invalid': !!form.errors.itens }">
                                <option value="">Digite para buscar por nome ou código...</option>
                                <option v-for="p in procedimentosSelectRows" :key="`${p.source || ''}:${p.id}`" :value="`${p.source || ''}:${p.id}`">{{ p.descricao ? `${p.nome} - ${p.descricao}` : p.nome }}</option>
                            </select>
                            <div v-if="form.errors.itens" class="invalid-feedback d-block mt-2">{{ form.errors.itens }}</div>
                        </div>

                        <!-- Invoice Table -->
                        <SimpleTable
                            variant="borderless"
                            tableClass="table-bordered"
                            :items="itensLocal"
                            :columns="procedimentosColumns"
                            emptyTitle="Nenhum procedimento"
                            emptyMessage="Nenhum procedimento adicionado ao orçamento."
                            emptyIcon="ri-file-list-3-line"
                        >
                            <template #body="{ items, columns }">
                                <template v-for="(it, idx) in items" :key="`group-${idx}`">
                                    <!-- Main Row -->
                                    <tr>
                                        <td>
                                            <h6 class="mb-1">{{ procedimentoNome(it) }}</h6>
                                            <span class="text-muted small">Convênio: {{ selectedConvenioRow?.descricao || '—' }}</span>
                                        </td>
                                        <td class="text-end">{{ formatCurrency(it.valor_unitario) }}</td>
                                        <td class="text-end fw-medium">{{ formatCurrency(it.valor_total) }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-soft-danger" type="button" :disabled="locked" @click="removeItem(idx)" title="Remover">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- Sessions Rows -->
                                    <tr v-for="n in sessionCount(it)" :key="`sess-${idx}-${n}`">
                                        <td colspan="4" class="py-2 ps-4">
                                            <div class="d-flex align-items-center text-muted small">
                                                <i class="ri-arrow-right-s-line me-2"></i>
                                                <span class="badge bg-light text-dark border me-2">Sessão {{ n }}/{{ sessionCount(it) }}</span>
                                                <span>Agendamento pendente</span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </SimpleTable>
                    </div>
                </div>
                <!-- Card 3: Financial Summary -->
                <div class="card mt-3">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Resumo do Orçamento</h4>
                    </div>
                    <div class="card-body">
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted fw-medium">Subtotal Bruto</span>
                            <span class="fw-medium text-dark">{{ formatCurrency(valorBruto) }}</span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted fw-medium">Desconto</span>
                            <div class="w-25">
                                <input :value="formatCurrency(form.desconto)" type="text" class="form-control text-end"
                                    :disabled="locked" @input="onCurrencyInputForm($event, 'desconto')" placeholder="R$ 0,00" />
                            </div>
                        </div>


                        <hr class="border-primary opacity-25">
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fw-medium fs-15">Valor Total Líquido</span>
                            <span class="fw-bolder fs-4 text-primary">{{ formatCurrency(valorTotal) }}</span>
                        </div>
                        
                        <button class="btn btn-success w-100" type="button" :disabled="saveProcessing || locked" @click="save">
                            <span v-if="saveProcessing" class="spinner-border spinner-border-sm me-2" />
                            <i v-else class="ri-save-3-line align-middle me-1"></i>
                            Salvar Orçamento
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column: Summary & History -->
            <div class="col-lg-4">
                
                <!-- Card 4: Recent History (Moved to top) -->
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Orçamentos Recentes</h4>
                        <div class="flex-shrink-0">
                            <button class="btn btn-sm btn-soft-primary" type="button" @click="openConsultModal">
                                <i class="ri-search-line align-bottom me-1"></i> Consultar
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="vstack gap-2" v-if="ultimosLocal && ultimosLocal.length > 0">
                            <div v-for="o in ultimosLocal" :key="o.id" class="d-flex align-items-center border rounded p-2">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">#{{ o.numero }}</h6>
                                    <p class="text-muted mb-0 small">{{ formatDateTimeBR(o.criado_em) }}</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-soft-info btn-icon" type="button"
                                            @click="handleConsultEdit(o.id)" :disabled="o.pago" title="Abrir/Editar">
                                            <i class="ri-pencil-fill"></i>
                                        </button>

                                        <button class="btn btn-sm btn-soft-secondary btn-icon" type="button"
                                            @click="handleConsultPrint(o.id)" title="Imprimir">
                                            <i class="ri-printer-fill"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-3 text-muted">
                            Sem orçamentos recentes.
                        </div>
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
import SimpleTable from "@/Components/SimpleTable.vue";

const procedimentosColumns = [
    { key: 'descricao', label: 'Descrição do Procedimento', width: '50%' },
    { key: 'vlr_unit', label: 'Vlr. Unitário', width: '20%', thClass: 'text-end', tdClass: 'text-end' },
    { key: 'vlr_total', label: 'Vlr. Total', width: '20%', thClass: 'text-end', tdClass: 'text-end fw-medium' },
    { key: 'acoes', label: 'Ações', width: '10%', thClass: 'text-center', tdClass: 'text-center' }
];

const selPaciente = ref(null);
const selConvenio = ref(null);
const selProcedimento = ref(null);

function formatDateTimeBR(dateString) {
    if (!dateString) return '';
    const d = new Date(dateString);
    if (isNaN(d.getTime())) return dateString;
    return d.toLocaleString('pt-BR');
}

const props = defineProps({
    pacientes: { type: Array, default: () => [] },
    convenios: { type: Array, default: () => [] },
    procedimentos: { type: Array, default: () => [] },
    ultimos: { type: Array, default: () => [] },
});

const pacientesLocal = ref([...(props.pacientes || [])]);
const conveniosLocal = ref([...(props.convenios || [])]);
const conveniosPacienteLocal = ref([]);
const convenioLoading = ref(false);
const procedimentosLocal = ref([...(props.procedimentos || [])]);
const procedimentosSelectRows = ref([]);
const procedimentosSelectLoading = ref(false);
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

async function syncProcedimentoLoadingUI() {
    try {
        await nextTick();
        const el = selProcedimento.value;
        if (!el) return;
        const w = getChoicesWrapperFor(el);
        if (!w) return;
        w.classList.toggle('procedimento-loading', !!procedimentosSelectLoading.value);
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
const selectedProcId = ref("");

const selectedConvenioRow = computed(() => {
    const sid = String(form.convenio_id ?? "");
    if (!sid) return null;
    return (conveniosPacienteLocal.value || []).find(c => String(c?.id) === sid) || null;
});
let procSelectReqSeq = 0;
watch(() => form.convenio_id, async () => {
    const cid = String(form.convenio_id ?? '').trim();
    
    if (!keepConvenioOnPacienteChange.value) {
        itensLocal.value = [];
    }

    selectedProcId.value = "";
    procedimentosSelectRows.value = [];
    procedimentosSelectLoading.value = false;

    if (!cid) {
        await syncProcedimentoChoices();
        return;
    }

    const reqId = ++procSelectReqSeq;
    procedimentosSelectLoading.value = true;
    await syncProcedimentoLoadingUI();
    try {
        const resp = await window.axios.get(`/convenios/${cid}/procedimentos-orcamento`);
        if (reqId !== procSelectReqSeq) return;
        const rows = Array.isArray(resp?.data?.procedimentos) ? resp.data.procedimentos : [];
        procedimentosSelectRows.value = rows;
    } catch (e) {
        if (reqId !== procSelectReqSeq) return;
        procedimentosSelectRows.value = [];
    } finally {
        if (reqId === procSelectReqSeq) {
            procedimentosSelectLoading.value = false;
            await syncProcedimentoChoices();
            await syncProcedimentoLoadingUI();
        }
    }
}, { immediate: false });

watch(procedimentosSelectLoading, async () => {
    await syncProcedimentoLoadingUI();
});

async function syncProcedimentoChoices() {
    await nextTick();
    const el = selProcedimento.value;
    if (!el) return;
    let inst = el._choicesInstance || el.choices;
    if (!inst || typeof inst.setChoices !== 'function') {
        try { if (window.initChoiceEl) window.initChoiceEl(el); } catch (e) { }
        inst = el._choicesInstance || el.choices;
    }
    if (!inst || typeof inst.setChoices !== 'function') return;

    const selectedValue = selectedProcId.value != null ? String(selectedProcId.value) : '';
    const rows = Array.isArray(procedimentosSelectRows.value) ? procedimentosSelectRows.value : [];
    const items = [
        { value: "", label: "Buscar procedimento", selected: selectedValue === "", disabled: true },
        ...rows.map((r) => ({
            value: `${String(r?.source || '')}:${String(r?.id ?? "")}`,
            label: String(r?.descricao ? `${r?.nome ?? ""} - ${r?.descricao ?? ""}` : (r?.nome ?? "")),
            selected: `${String(r?.source || '')}:${String(r?.id ?? "")}` === selectedValue,
        })),
    ];
    try {
        if (typeof inst.removeActiveItems === 'function') inst.removeActiveItems();
    } catch (e) { }
    try {
        if (typeof inst.clearChoices === 'function') inst.clearChoices();
    } catch (e) { }
    inst.setChoices(items, "value", "label", true);
    try {
        if (window.syncChoiceValue) window.syncChoiceValue(el, selectedValue);
    } catch (e) { }
    await syncProcedimentoLoadingUI();
}

function parseProcKey(key) {
    const s = String(key ?? '').trim();
    if (!s) return null;
    const parts = s.split(':');
    if (parts.length === 2) {
        const source = String(parts[0] || '').trim();
        const id = Number(parts[1] || 0);
        if (!source || !Number.isFinite(id) || id <= 0) return null;
        return { source, id };
    }
    const id = Number(s);
    if (!Number.isFinite(id) || id <= 0) return null;
    return { source: 'procedimento', id };
}

function findProcedimentoAnyByKey(key) {
    const parsed = parseProcKey(key);
    if (!parsed) return null;
    const inSelect = (procedimentosSelectRows.value || []).find(x => String(x?.source) === String(parsed.source) && Number(x?.id) === Number(parsed.id));
    if (inSelect) return inSelect;
    if (parsed.source === 'procedimento') {
        const inBase = (procedimentosLocal.value || []).find(x => Number(x?.id) === Number(parsed.id));
        return inBase || null;
    }
    return null;
}

function addItem() {
    itensLocal.value.push({
        procedimento_id: null,
        tuss_id: null,
        procedimento_nome: null,
        procedimento_desc: null,
        eh_tratamento: false,
        quantidade_sessoes: null,
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
    const key = selectedProcId.value;
    if (!key) return;
    const row = findProcedimentoAnyByKey(key);
    if (!row) return;
    const source = String(row?.source || '');
    const valorUnit = Number(row?.valor ?? 0);
    const item = {
        procedimento_id: source === 'procedimento' ? Number(row?.id ?? null) : null,
        tuss_id: source === 'tuss' ? Number(row?.id ?? null) : null,
        procedimento_nome: String(row?.nome ?? '') || null,
        procedimento_desc: row?.descricao ?? null,
        eh_tratamento: !!row?.eh_tratamento,
        quantidade_sessoes: row?.quantidade_sessoes ?? null,
        quantidade: 1,
        valor_unitario: Number(valorUnit || 0),
        valor_total: Number(valorUnit || 0),
        observacoes: null,
    };
    itensLocal.value.push(item);
    selectedProcId.value = "";
    syncProcedimentoChoices();
}

function procedimentoNome(item) {
    const nome = String(item?.procedimento_nome ?? '').trim();
    const desc = String(item?.procedimento_desc ?? '').trim();
    if (nome) return desc ? `${nome} - ${desc}` : nome;
    const pid = item?.procedimento_id;
    if (pid) {
        const p = (procedimentosLocal.value || []).find(x => String(x?.id) === String(pid));
        if (p) return p?.descricao ? `${p.nome || ''} - ${p.descricao}`.trim() || '-' : (p.nome || '-');
    }
    return '-';
}

function sessionCount(item) {
    const n = Number(item?.quantidade_sessoes || 0);
    return item?.eh_tratamento ? (n > 0 ? n : 0) : 0;
}

function recalcItem(idx) {
    const it = itensLocal.value[idx];
    if (!it) return;
    const valorUnit = Number(it.valor_unitario || 0);
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
        tuss_id: it.tuss_id,
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
    { id: 'id', name: 'ID' },
    { id: 'numero', name: 'Número' },
    { id: 'data_emissao', name: 'Emissão' },
    { id: 'validade', name: 'Validade' },
    { id: 'paciente', name: 'Paciente' },
    { id: 'cpf', name: 'CPF' },
    { id: 'total', name: 'Total' },
];
const orcamentosConsultaGrid = computed(() => {
    return (orcamentosConsulta.value || []).map(o => ({
        id: o.id,
        numero: o.numero,
        data_emissao: o.data_emissao,
        validade: o.validade,
        paciente: o.paciente || '-',
        cpf: o.cpf,
        status: o.is_convenio ? 'Convênio' : (o.aprovado ? 'Aprovado' : 'Aguardando aprovação'),
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
                procedimento_nome: procedimentoNome(it) || '-',
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
async function carregarOrcamento(id) {
    if (!id) return;
    try {
        const res = await window.axios.get(`/orcamentos/${id}?include_all=1`);
        const o = res?.data?.orcamento;
        const itens = Array.isArray(res?.data?.itens) ? res.data.itens : [];
        if (!o) return;
        
        isEditing.value = true;
        orcamentoEditId.value = o.id;
        keepConvenioOnPacienteChange.value = true;
        
        if (o.paciente_id) {
            form.paciente_id = o.paciente_id;
            pacienteSelectedRow.value = {
                id: o.paciente_id,
                nome: o.paciente_nome || o.paciente || '',
                cpf: o.paciente_cpf || '',
            };
            pacientesChoicesRows.value = [pacienteSelectedRow.value];
            await pacienteSearch.syncChoices();
            
            const nv = String(o.paciente_id ?? "");
            if (nv) {
                const resp = await window.axios.get(`/pacientes/${nv}/convenios`);
                const convs = Array.isArray(resp?.data?.convenios) ? resp.data.convenios : [];
                conveniosPacienteLocal.value = convs;
            }
        } else {
            form.paciente_id = null;
            pacienteSelectedRow.value = null;
            pacientesChoicesRows.value = [];
        }
        
        form.convenio_id = o.convenio_id ?? null;
        await convenioChoices.syncChoices();
        await syncConvenioLoadingUI();
        
        form.data_emissao = o.data_emissao || formatDMY(new Date());
        form.validade = o.validade || formatDMY(new Date(Date.now() + 30 * 24 * 3600 * 1000));
        form.desconto = Number(o.desconto || 0);
        form.faturamento_previsto = !!o.faturamento_previsto;
        form.aprovado = !!o.aprovado;
        form.pago = !!o.pago;
        itensLocal.value = itens.map(it => ({
            procedimento_id: it.procedimento_id,
            tuss_id: it.tuss_id ?? null,
            procedimento_nome: it.procedimento_nome || null,
            procedimento_desc: it.procedimento_desc || null,
            eh_tratamento: !!it.eh_tratamento,
            quantidade_sessoes: it.quantidade_sessoes ?? null,
            quantidade: it.quantidade,
            valor_unitario: Number(it.valor_unitario || 0),
            valor_total: Number(it.valor_total || 0),
            observacoes: it.observacoes || null,
        }));
        setTimeout(() => {
            keepConvenioOnPacienteChange.value = false;
        }, 300);
        closeConsultModal();
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

:deep(.choices.procedimento-loading) {
    pointer-events: none;
}

:deep(.choices.procedimento-loading .choices__inner) {
    position: relative;
}

:deep(.choices.procedimento-loading .choices__inner::after) {
    content: "Carregando...";
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
    color: var(--vz-secondary-color, #6c757d);
}

:deep(.choices.procedimento-loading .choices__list--single) {
    opacity: 0.55;
}

/* Estilo para texto longo no item selecionado do Choices.js */
:deep(.choices__list--single .choices__item) {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

/* Estilo para texto longo nas opções do dropdown */
:deep(.choices__list--dropdown .choices__item) {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
