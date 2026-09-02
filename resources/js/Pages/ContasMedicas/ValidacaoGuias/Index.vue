<template>
    <Layout>

        <Head title="Validação de Guias" />
        <PageHeader title="Validação de Guias" pageTitle="Contas Médicas" />

        <div class="row mb-4">
            <!-- Card Vencidas -->
            <div class="col">
                <div class="card shadow-sm border-0 h-100" style="cursor: pointer; border-left: 4px solid var(--vz-danger) !important;"
                     :class="filterStatusPendentes === 'VENCIDAS' ? 'bg-danger-subtle' : ''"
                     @click="setFiltroStatus('VENCIDAS')">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">Vencidas</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="text-danger">{{ countStatus.vencidas }}</span></h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-danger-subtle text-danger rounded fs-3">
                                    <i class="ri-alert-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Próximo a Vencer -->
            <div class="col">
                <div class="card shadow-sm border-0 h-100" style="cursor: pointer; border-left: 4px solid var(--vz-secondary) !important;"
                     :class="filterStatusPendentes === 'PROXIMO_VENCER' ? 'bg-secondary-subtle' : ''"
                     @click="setFiltroStatus('PROXIMO_VENCER')">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">Próximo a Vencer</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="text-secondary">{{ countStatus.proximo_vencer }}</span></h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-secondary-subtle text-secondary rounded fs-3">
                                    <i class="ri-timer-flash-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Card Devolvidas -->
            <div class="col">
                <div class="card shadow-sm border-0 h-100" style="cursor: pointer; border-left: 4px solid var(--vz-warning) !important;"
                     :class="filterStatusPendentes === 'DEVOLVIDA' ? 'bg-warning-subtle' : ''" 
                     @click="setFiltroStatus('DEVOLVIDA')">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">Devolvidas</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="text-warning">{{ countStatus.devolvidas }}</span></h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-warning-subtle text-warning rounded fs-3">
                                    <i class="ri-arrow-go-back-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Validadas -->
            <div class="col">
                <div class="card shadow-sm border-0 h-100" style="cursor: pointer; border-left: 4px solid var(--vz-primary) !important;"
                     :class="filterStatusPendentes === 'VALIDADA' ? 'bg-primary-subtle' : ''"
                     @click="setFiltroStatus('VALIDADA')">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">Validadas</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="text-primary">{{ countStatus.validadas }}</span></h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded fs-3">
                                    <i class="ri-shield-check-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Pendentes -->
            <div class="col">
                <div class="card shadow-sm border-0 h-100" style="cursor: pointer; border-left: 4px solid var(--vz-info) !important;"
                     :class="filterStatusPendentes === 'PENDENTES' ? 'bg-info-subtle' : ''"
                     @click="setFiltroStatus('PENDENTES')">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">Pendentes</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span class="text-info">{{ countStatus.pendentes }}</span></h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle text-info rounded fs-3">
                                    <i class="ri-time-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card" id="guiasList">
                    <div class="card-body">
                        <TableGrid ref="tableGridPendentesRef" :columns="columnsPendentes" :serverUrl="serverUrl"
                            :serverQuery="serverQuery"
                            tableTitle="Guias a Validar" :showStatus="false" :showCheckbox="true"
                            :showMultiDelete="false" :showImage="false" :search="true" :showAddButton="false"
                            :searchPlaceholder="'Buscar guia, paciente ou convênio...'"
                            :actionsConfig="{ delete: false, edit: false, show: true }"
                            :actionsLabels="{ show: 'Validar' }" :actionsButtonText="{ show: 'Validar' }"
                            :actionsIcons="{ show: 'ri-edit-2-line' }" @show="abrirGuia">
                            <template #custom-actions="{ selectedRows }">
                                <div class="d-flex align-items-center gap-2">
                                    <button v-if="selectedRows && selectedRows.length > 0" class="btn btn-success"
                                        @click="encaminharSelecionadas" :disabled="processando">
                                        <i class="ri-send-plane-fill align-bottom me-1"></i> Encaminhar Selecionadas ({{
                                            selectedRows.length }})
                                    </button>
                                    <select ref="selConvenioPendentes" class="form-select"
                                        v-model="filterConvenioPendentes" style="width: 200px;">
                                        <option value="">Todos os Convênios</option>
                                        <option v-for="c in conveniosDisponiveis" :key="c" :value="c">{{ c }}</option>
                                    </select>
                                    <div>
                                        <select ref="selStatusPendentes" class="form-select"
                                            v-model="filterStatusPendentes" style="width: 200px;">
                                            <option value="">Todos os Status</option>
                                            <option value="VENCIDAS">Vencidas</option>
                                            <option value="DEVOLVIDA">Devolvidas</option>
                                            <option value="VALIDADA">Validadas</option>
                                            <option value="PENDENTES">Pendentes</option>
                                        </select>
                                    </div>
                                </div>
                            </template>
                        </TableGrid>


                    </div>
                </div>
            </div>
        </div>

        <GuiaSADTModal v-if="showGuiaSADTModal" v-model="showGuiaSADTModal" :agendamento-id="selectedAgendamentoForGuia"
            :guia-id="selectedGuiaId" :ignorar-bloqueios="true" :permitir-validacao-faturamento="true"
            @saved="reloadPage" />

        <ModalConfirm v-model="confirmEncaminharModal" title="Confirmação"
            :subTitle="`Deseja encaminhar ${guiasParaEncaminhar.length} guia(s) selecionada(s) para o faturamento?`"
            message="As guias sairão desta lista e serão enviadas para o setor de Faturamento."
            nameButton="Sim, encaminhar" buttonClass="btn-success" @save="executarEncaminharSelecionadas" />

    </Layout>
</template>

<script setup>
import "gridjs/dist/theme/mermaid.css";
import { html } from "gridjs";
import Choices from "choices.js";
import Swal from "sweetalert2";
import axios from "axios";
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import { ref, computed, onMounted, nextTick } from "vue";
import GuiaSADTModal from "@/Components/Guias/GuiaSADTModal.vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";
import ModalConfirm from "@/Components/ModalConfirm.vue";

const serverUrl = '/contas-medicas/validacao-guias/api';

const page = usePage();

const showGuiaSADTModal = ref(false);
const selectedAgendamentoForGuia = ref(null);
const selectedGuiaId = ref(null);
const processando = ref(false);
const tableGridPendentesRef = ref(null);

const selConvenioPendentes = ref(null);
const selStatusPendentes = ref(null);

const confirmEncaminharModal = ref(false);
const guiasParaEncaminhar = ref([]);

const filterConvenioPendentes = ref('');
const filterStatusPendentes = ref('');

const serverQuery = computed(() => {
    const q = {};
    if (filterConvenioPendentes.value) q.convenio = filterConvenioPendentes.value;
    if (filterStatusPendentes.value) q.status_filter = filterStatusPendentes.value;
    return q;
});

const conveniosDisponiveis = computed(() => {
    return [];
});

const countStatus = ref({
    devolvidas: 0,
    validadas: 0,
    vencidas: 0,
    pendentes: 0,
    proximo_vencer: 0,
});

async function fetchContadores() {
    try {
        const response = await axios.get('/contas-medicas/validacao-guias/contadores');
        countStatus.value = response.data;
    } catch (error) {
        console.error('Erro ao buscar contadores:', error);
    }
}

let choicesConvenioInstance = null;
let choicesStatusInstance = null;

onMounted(async () => {
    fetchContadores();
    setTimeout(() => {
        nextTick(() => {
            if (selConvenioPendentes.value) {
                choicesConvenioInstance = new Choices(selConvenioPendentes.value, { searchEnabled: false, itemSelectText: '' });
                selConvenioPendentes.value.addEventListener('change', (e) => filterConvenioPendentes.value = e.target.value);
            }
            if (selStatusPendentes.value) {
                choicesStatusInstance = new Choices(selStatusPendentes.value, { searchEnabled: false, itemSelectText: '' });
                selStatusPendentes.value.addEventListener('change', (e) => filterStatusPendentes.value = e.target.value);
            }
        });
    }, 500);
});

function setFiltroStatus(status) {
    // Toggle off if already selected
    if (filterStatusPendentes.value === status) {
        filterStatusPendentes.value = '';
    } else {
        filterStatusPendentes.value = status;
    }
    if (choicesStatusInstance) {
        choicesStatusInstance.setChoiceByValue(filterStatusPendentes.value);
    }
}

function formatData(dataStr) {
    if (!dataStr) return 'N/A';
    const partes = dataStr.split('-');
    if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }
    return dataStr;
}

function getStatusBadgeClass(status) {
    const s = String(status || '').toUpperCase();
    switch (s) {
        case 'CRIADA': return 'badge bg-light text-dark border';
        case 'VALIDADA': return 'badge bg-primary-subtle text-primary';
        case 'EM_ATENDIMENTO': return 'badge bg-info-subtle text-info';
        case 'ATENDIDA': return 'badge bg-info-subtle text-info';
        case 'PRONTA_FATURAMENTO': return 'badge bg-success-subtle text-success';
        case 'ENVIADA_FATURAMENTO': return 'badge bg-warning-subtle text-warning';
        case 'FATURADA': return 'badge bg-dark-subtle text-dark';
        case 'DEVOLVIDA': return 'badge bg-danger-subtle text-danger';
        case 'GLOSADA': return 'badge bg-danger-subtle text-danger';
        case 'CANCELADA': return 'badge bg-danger text-white';
        default: return 'badge bg-light text-dark border';
    }
}

function formatStatusLabel(status) {
    const s = String(status || 'CRIADA').toUpperCase();
    if (s === 'ENVIADA_FATURAMENTO') return 'ENVIADA P/ FATURAMENTO';
    return s.replace(/_/g, ' ');
}

const columnsPendentes = [
    { id: "id", name: "ID" },
    { id: "numero_guia_prestador", name: "Numero da Guia", formatter: (cell) => cell || "-" },
    { id: "convenio_nome", name: "Convenio" },
    { id: "tipo", name: "Tipo", formatter: (cell) => cell || "-" },
    { id: "paciente_nome", name: "Paciente" },
    { id: "medico_nome", name: "Médico", formatter: (cell) => cell || "-" },
    { id: "data_criacao", name: "Criado em" },
    { id: "data_execucao", name: "Data Execução", formatter: (cell) => cell || "-" },
    { 
        id: "data_limite_faturamento", 
        name: "Prazo Faturamento", 
        formatter: (cell, row) => {
            if (!cell) return "-";
            if (row.is_vencida) {
                return html(`<span class="text-danger fw-bold"><i class="ri-alert-line align-bottom me-1"></i>${cell} (Vencida)</span>`);
            }
            if (row.dias_vencer <= 5) {
                return html(`<span class="text-warning fw-bold"><i class="ri-time-line align-bottom me-1"></i>${cell} (${row.dias_vencer} dias)</span>`);
            }
            return cell;
        }
    },
    {
        id: "status",
        name: "Status",
        formatter: (cell) => {
            const cls = getStatusBadgeClass(cell);
            const label = formatStatusLabel(cell);
            return html(`<span class="${cls} fs-12 px-2 py-1">${label}</span>`);
        }
    }
];


const abrirGuia = (id, rowData) => {
    if (rowData && rowData.agendamento_id) {
        selectedAgendamentoForGuia.value = rowData.agendamento_id;
        selectedGuiaId.value = rowData.id || id;
        showGuiaSADTModal.value = true;
    }
};

function encaminharSelecionadas() {
    if (!tableGridPendentesRef.value) return;

    const ids = tableGridPendentesRef.value.getSelectedRowIds();

    if (!ids || ids.length === 0) {
        window.dispatchEvent(new CustomEvent('flash:show', {
            detail: { type: 'warning', message: 'Selecione pelo menos uma guia na tabela para encaminhar.' }
        }));
        return;
    }

    const selectedObjs = tableGridPendentesRef.value.getSelectedRowObjects ? tableGridPendentesRef.value.getSelectedRowObjects() : [];
    const invalidas = selectedObjs.filter(g => g.status !== 'VALIDADA');
    if (invalidas.length > 0) {
        window.dispatchEvent(new CustomEvent('flash:show', {
            detail: { type: 'warning', message: 'Apenas guias com status VALIDADA podem ser encaminhadas para faturamento.' }
        }));
        return;
    }

    guiasParaEncaminhar.value = ids;
    confirmEncaminharModal.value = true;
}

function executarEncaminharSelecionadas() {
    processando.value = true;
    confirmEncaminharModal.value = false;
    router.post(route('contas_medicas.encaminhar_faturamento'), {
        guias: guiasParaEncaminhar.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            processando.value = false;
            tableGridPendentesRef.value?.clearSelection();
            guiasParaEncaminhar.value = [];
        },
        onError: () => {
            processando.value = false;
            window.dispatchEvent(new CustomEvent('flash:show', {
                detail: { type: 'danger', message: 'Erro ao encaminhar guias.' }
            }));
        }
    });
}

function reloadPage() {
    if (tableGridPendentesRef.value?.reload) {
        tableGridPendentesRef.value.reload();
    }
    fetchContadores();
}
</script>
