<template>
    <Layout>

        <Head title="Validação de Guias" />
        <PageHeader title="Validação de Guias" pageTitle="Contas Médicas" />

        <div class="row">
            <div class="col-lg-12">
                <div class="card" id="guiasList">
                    <div class="card-body">
                        <TableGrid ref="tableGridPendentesRef" :columns="columnsPendentes" :data="guiasPendentes"
                            tableTitle="Guias a Validar" :showStatus="false" :showCheckbox="true"
                            :showMultiDelete="false" :showImage="false" :search="true" :showAddButton="false"
                            :searchPlaceholder="'Buscar guia, paciente ou convênio...'" 
                            :actionsConfig="{ delete: false, edit: false, show: true }"
                            :actionsLabels="{ show: 'Validar' }"
                            :actionsButtonText="{ show: 'Validar' }"
                            :actionsIcons="{ show: 'ri-edit-2-line' }"
                            @show="abrirGuia">
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

        <GuiaSADTModal v-if="showGuiaSADTModal" v-model="showGuiaSADTModal" :agendamento-id="selectedAgendamentoForGuia" :guia-id="selectedGuiaId"
            :ignorar-bloqueios="true" :permitir-validacao-faturamento="true" @saved="reloadPage" />

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
const selectedGuiaId = ref(null);
const processando = ref(false);
const tableGridPendentesRef = ref(null);

const selConvenioPendentes = ref(null);
const selStatusPendentes = ref(null);

const confirmEncaminharModal = ref(false);
const guiasParaEncaminhar = ref([]);

const filterConvenioPendentes = ref('');
const filterStatusPendentes = ref('');

const conveniosDisponiveis = computed(() => {
    const convs = props.guias.map(g => g.convenio_nome).filter(Boolean);
    return [...new Set(convs)].sort();
});

const guiasPendentes = computed(() => {
    let result = props.guias.filter(g => String(g.status).toUpperCase() !== 'PRONTA_FATURAMENTO');

    if (filterConvenioPendentes.value) {
        result = result.filter(g => g.convenio_nome === filterConvenioPendentes.value);
    }

    if (filterStatusPendentes.value) {
        let statusCheck = filterStatusPendentes.value.toUpperCase();
        if (statusCheck === 'CRIADA') {
            result = result.filter(g => !g.status || String(g.status).toUpperCase() === 'CRIADA');
        } else {
            result = result.filter(g => String(g.status).toUpperCase() === statusCheck);
        }
    }

    return result;
});


onMounted(() => {
    setTimeout(() => {
        nextTick(() => {
            if (selConvenioPendentes.value) {
                new Choices(selConvenioPendentes.value, { searchEnabled: false, itemSelectText: '' });
                selConvenioPendentes.value.addEventListener('change', (e) => filterConvenioPendentes.value = e.target.value);
            }
            if (selStatusPendentes.value) {
                new Choices(selStatusPendentes.value, { searchEnabled: false, itemSelectText: '' });
                selStatusPendentes.value.addEventListener('change', (e) => filterStatusPendentes.value = e.target.value);
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

const columnsPendentes = [
    { id: "id", name: "ID" },
    { id: "numero_guia_prestador", name: "Numero da Guia", formatter: (cell) => cell || "-" },
    { id: "convenio_nome", name: "Convenio" },
    { id: "tipo", name: "Tipo", formatter: (cell) => cell || "-" },
    { id: "paciente_nome", name: "Paciente" },
    { id: "medico_nome", name: "Médico", formatter: (cell) => cell || "-" },
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


const abrirGuia = (id) => {
    const guia = props.guias.find(g => g.id === parseInt(id));
    if (guia && guia.agendamento_id) {
        selectedAgendamentoForGuia.value = guia.agendamento_id;
        selectedGuiaId.value = guia.id;
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
    router.reload({ only: ['guias'] });
}
</script>
