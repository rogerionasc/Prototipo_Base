<template>
    <Layout>

        <Head title="Validação de Guias" />
        <PageHeader title="Validação de Guias" pageTitle="Contas Médicas" />

        <div class="row">
            <div class="col-lg-12">
                <div class="card" id="guiasList">
                    <div class="card-body">
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: activeTab === 'pendentes' }"
                                    @click.prevent="mudarAba('pendentes')" href="#">
                                    <i class="ri-file-list-3-line me-1 align-bottom"></i> A Validar
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: activeTab === 'encaminhadas' }"
                                    @click.prevent="mudarAba('encaminhadas')" href="#">
                                    <i class="ri-check-double-line me-1 align-bottom"></i> Prontas / Encaminhadas
                                </a>
                            </li>
                        </ul>

                        <TableGrid ref="tableGridRef" :columns="columns" :data="guiasFiltradas"
                            :tableTitle="activeTab === 'pendentes' ? 'Guias a Validar' : 'Guias Prontas para Faturar'"
                            :showStatus="false" :showCheckbox="activeTab === 'pendentes'" :showMultiDelete="false"
                            :showImage="false" :search="true" :showAddButton="false"
                            :searchPlaceholder="'Buscar guia, paciente ou convênio...'" @show="abrirGuia">
                            <template #custom-actions="{ selectedRows }">
                                <div class="d-flex align-items-center gap-2">
                                    <button v-if="activeTab === 'pendentes' && selectedRows && selectedRows.length > 0"
                                        class="btn btn-success" @click="encaminharSelecionadas" :disabled="processando">
                                        <i class="ri-send-plane-fill align-bottom me-1"></i> Encaminhar Selecionadas ({{
                                            selectedRows.length }})
                                    </button>
                                    <select ref="selConvenio" class="form-select" v-model="filterConvenio"
                                        style="width: 200px;">
                                        <option value="">Todos os Convênios</option>
                                        <option v-for="c in conveniosDisponiveis" :key="c" :value="c">{{ c }}</option>
                                    </select>
                                    <div v-show="activeTab === 'pendentes'">
                                        <select ref="selStatus" class="form-select" v-model="filterStatus"
                                            style="width: 200px;">
                                            <option value="">Todos os Status</option>
                                            <option value="CRIADA">Criada</option>
                                            <option value="EM_ATENDIMENTO">Em Atendimento</option>
                                            <option value="ATENDIMENTO_REALIZADO">Atendimento Realizado</option>
                                            <option value="VALIDADA">Validada</option>
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
            :ignorar-bloqueios="true" :permitir-validacao-faturamento="activeTab === 'pendentes'" @saved="reloadPage" />

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
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import { ref, computed, onMounted, nextTick } from "vue";
import GuiaSADTModal from "@/Components/Guias/GuiaSADTModal.vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";
import ModalConfirm from "@/Components/ModalConfirm.vue";

const props = defineProps({
    guias: { type: Array, default: () => [] }
});

const page = usePage();

const showGuiaSADTModal = ref(false);
const selectedAgendamentoForGuia = ref(null);
const activeTab = ref('pendentes');
const processando = ref(false);
const tableGridRef = ref(null);
const selConvenio = ref(null);
const selStatus = ref(null);

const confirmEncaminharModal = ref(false);
const guiasParaEncaminhar = ref([]);

const filterConvenio = ref('');
const filterStatus = ref('');

const conveniosDisponiveis = computed(() => {
    const convs = props.guias.map(g => g.convenio_nome).filter(Boolean);
    return [...new Set(convs)].sort();
});

function mudarAba(aba) {
    activeTab.value = aba;
}

const guiasFiltradas = computed(() => {
    let result = props.guias;

    if (activeTab.value === 'pendentes') {
        result = result.filter(g => String(g.status).toUpperCase() !== 'PRONTA_FATURAMENTO');
    } else {
        result = result.filter(g => String(g.status).toUpperCase() === 'PRONTA_FATURAMENTO');
    }

    if (filterConvenio.value) {
        result = result.filter(g => g.convenio_nome === filterConvenio.value);
    }

    if (activeTab.value === 'pendentes' && filterStatus.value) {
        let statusCheck = filterStatus.value.toUpperCase();
        if (statusCheck === 'CRIADA') {
            result = result.filter(g => !g.status || String(g.status).toUpperCase() === 'CRIADA');
        } else {
            result = result.filter(g => String(g.status).toUpperCase() === statusCheck);
        }
    }

    return result;
});

onMounted(() => {
    // Configurar Choices manualmente usando um pequeno timeout para aguardar a renderização do TableGrid
    setTimeout(() => {
        nextTick(() => {
            if (selConvenio.value) {
                new Choices(selConvenio.value, { searchEnabled: false, itemSelectText: '' });
                selConvenio.value.addEventListener('change', (e) => filterConvenio.value = e.target.value);
            }
            if (selStatus.value) {
                new Choices(selStatus.value, { searchEnabled: false, itemSelectText: '' });
                selStatus.value.addEventListener('change', (e) => filterStatus.value = e.target.value);
            }
        });
    }, 500);
});

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
        case 'VALIDADA': return 'badge bg-success-subtle text-success';
        case 'EM_ATENDIMENTO': return 'badge bg-info-subtle text-info';
        case 'ATENDIMENTO_REALIZADO': return 'badge bg-primary-subtle text-primary';
        case 'PRONTA_FATURAMENTO': return 'badge bg-success-subtle text-success';
        case 'ENVIADA_FATURAMENTO': return 'badge bg-warning-subtle text-warning';
        case 'FATURADA': return 'badge bg-dark-subtle text-dark';
        case 'GLOSADA': return 'badge bg-danger-subtle text-danger';
        case 'CANCELADA': return 'badge bg-danger text-white';
        default: return 'badge bg-light text-dark border';
    }
}

function formatStatusLabel(status) {
    const s = String(status || 'CRIADA').toUpperCase();
    if (s === 'PRONTA_FATURAMENTO') return 'LIBERADA';
    if (s === 'ENVIADA_FATURAMENTO') return 'ENVIADA P/ FATURAMENTO';
    return s.replace(/_/g, ' ');
}

const columns = [
    { id: "id", name: "ID Guia", formatter: (cell) => html(`<b>#${cell}</b>`) },
    { id: "data_solicitacao", name: "Data", formatter: (cell) => formatData(cell) },
    { id: "paciente_nome", name: "Paciente" },
    { id: "convenio_nome", name: "Convênio" },
    { id: "numero_guia_operadora", name: "Guia Operadora", formatter: (cell) => cell || "-" },
    {
        id: "status",
        name: "Status",
        formatter: (cell) => {
            const cls = getStatusBadgeClass(cell);
            const label = formatStatusLabel(cell);
            return html(`<span class="${cls} fs-12 px-2 py-1">${label}</span>`);
        }
    },
    {
        name: "Ação",
        formatter: (cell, row) => {
            const idIndex = activeTab.value === 'pendentes' ? 1 : 0;
            const idVal = row.cells[idIndex].data;
            return html(`
        <ul class="list-inline hstack gap-2 mb-0">
          <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Validar / Editar">
            <button type="button" class="btn btn-sm btn-soft-primary" data-action="show" data-id="${idVal}">
              <i class="ri-edit-2-line"></i> Validar
            </button>
          </li>
        </ul>
      `);
        }
    }
];

function abrirGuia(id) {
    const guiaObj = props.guias.find(g => g.id == id);
    if (!guiaObj || !guiaObj.agendamento_id) {
        window.dispatchEvent(new CustomEvent('flash:show', {
            detail: { type: 'warning', message: 'Esta guia não possui agendamento vinculado.' }
        }));
        return;
    }
    selectedAgendamentoForGuia.value = guiaObj.agendamento_id;
    showGuiaSADTModal.value = true;
}

function encaminharSelecionadas() {
    if (!tableGridRef.value) return;

    const ids = tableGridRef.value.getSelectedRowIds();

    if (!ids || ids.length === 0) {
        window.dispatchEvent(new CustomEvent('flash:show', {
            detail: { type: 'warning', message: 'Selecione pelo menos uma guia na tabela para encaminhar.' }
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
            tableGridRef.value?.clearSelection();
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
    router.reload({ only: ['guias'] });
}
</script>
