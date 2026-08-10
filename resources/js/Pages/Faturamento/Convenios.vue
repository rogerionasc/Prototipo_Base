<template>
    <Layout>

        <Head title="Lotes de Faturamento" />
        <PageHeader title="Lotes de Faturamento" pageTitle="Faturamento" />

        <div class="row mb-4">
            <div class="col-12 text-end">
                <button class="btn btn-primary" @click="openCriarLoteModal">
                    <i class="ri-add-line align-bottom me-1"></i> Criar Lote
                </button>
            </div>
        </div>

        <div class="card" v-if="faturamentos.length === 0">
            <div class="card-body text-center p-5">
                <h5 class="text-muted">Nenhum lote de faturamento encontrado.</h5>
                <p class="text-muted mb-0">Clique em "Criar Lote" para começar.</p>
            </div>
        </div>

        <div class="card ribbon-box border shadow-none mb-3" v-for="lote in faturamentos" :key="lote.id">
            <div class="card-body pb-3">
                <div class="ribbon ribbon-primary ribbon-shape" style="z-index: 10;">Lote #{{ lote.id }}</div>
                <div class="row align-items-center mt-3" style="cursor: pointer;" @click="toggleCollapse(lote.id)">

                    <!-- Convênio Icone e Nome -->
                    <div class="col-md-3 col-12 mb-3 mb-md-0 border-end-md pe-md-4 text-center text-md-start">
                        <div class="d-flex flex-column flex-md-row align-items-center">
                            <div class="flex-shrink-0 mb-2 mb-md-0 me-md-3">
                                <img v-if="lote.convenio_logo" :src="`/storage/${lote.convenio_logo}`" alt="Logo"
                                    class="rounded bg-light p-1"
                                    style="object-fit: contain; width: 70px; height: 70px;" />
                                <div v-else
                                    class="rounded bg-primary-subtle text-primary d-flex justify-content-center align-items-center"
                                    style="width: 70px; height: 70px;">
                                    <i class="ri-hospital-line" style="font-size: 40px;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fs-15 fw-semibold mb-1 text-primary">{{ lote.convenio }}</h5>
                                <span class="text-muted fs-12"><i class="ri-calendar-event-line align-middle me-1"></i>
                                    {{ lote.data_faturamento || '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Barra de Progresso das Guias -->
                    <div class="col-md-5 col-12 mb-3 mb-md-0 px-md-4">
                        <div class="d-flex justify-content-between align-items-end mb-2">
                            <span class="text-uppercase fw-semibold fs-11 text-muted">Progresso das Guias</span>
                            <span class="badge bg-info-subtle text-info fw-semibold px-2 py-1 fs-11">
                                <i class="ri-file-list-3-line align-middle me-1"></i> {{ lote.total_guias }} guias no
                                lote
                            </span>
                        </div>

                        <div v-if="lote.guias_timeline && lote.guias_timeline.length > 0">
                            <div class="progress animated-progress"
                                style="height: 8px; border-radius: 4px; background-color: #e9ebec;">
                                <div v-for="(t, index) in lote.guias_timeline" :key="index" class="progress-bar"
                                    :class="getProgressBarClass(t.status)" role="progressbar"
                                    :style="{ width: t.percentage + '%' }" :title="t.status + ' (' + t.count + ')'">
                                </div>
                            </div>

                            <!-- Legendas -->
                            <div class="d-flex flex-wrap mt-2 gap-2 justify-content-start">
                                <div v-for="(t, index) in lote.guias_timeline" :key="index"
                                    class="d-flex align-items-center fs-11 fw-medium"
                                    :class="getProgressTextClass(t.status)">
                                    <i class="ri-checkbox-blank-circle-fill me-1" style="font-size: 8px;"></i>
                                    {{ t.percentage }}% {{ t.status }} ({{ t.count }})
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-muted fs-12 d-flex align-items-center mt-2">
                            <i class="ri-information-line me-1"></i> Este lote não possui guias associadas.
                        </div>
                    </div>

                    <!-- Status e Total do Lote -->
                    <div class="col-md-3 col-12 text-center border-start-md ps-md-4">
                        <p class="text-muted text-uppercase fw-semibold fs-11 mb-1">Valor Total</p>
                        <h4 class="text-success fw-bold mb-2">{{ formatCurrency(lote.valor_total) }}</h4>
                        <span class="badge px-3 py-1 fs-12 shadow-sm rounded-pill"
                            :class="getLoteStatusClass(lote.status)">
                            {{ lote.status.replace('_', ' ') }}
                        </span>
                    </div>

                    <div class="col-md-1 col-12 text-center text-md-end mt-3 mt-md-0">
                        <button class="btn btn-sm btn-ghost-secondary rounded-circle shadow-none">
                            <i class="ri-arrow-down-s-line fs-20"
                                :class="{ 'ri-arrow-up-s-line': isExpanded(lote.id) }"></i>
                        </button>
                    </div>

                </div>

                <!-- Tabela Colapsável -->
                <div v-show="isExpanded(lote.id)" class="mt-4 ms-md-4 border-top pt-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fs-13 fw-semibold text-muted text-uppercase mb-0">Guias Atreladas ao Lote</h6>
                        <button class="btn btn-sm btn-soft-success" @click.stop="abrirAddModal(lote)">
                            <i class="ri-add-line align-bottom me-1"></i> Adicionar Guias
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-nowrap table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID Guia</th>
                                    <th>Nº da Guia</th>
                                    <th>Data</th>
                                    <th>Paciente</th>
                                    <th>Tipo</th>
                                    <th>Senha/Aut.</th>
                                    <th>Valor</th>
                                    <th>Glosa</th>
                                    <th>Status</th>
                                    <th class="text-end">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!lote.guias || lote.guias.length === 0">
                                    <td colspan="8" class="text-center p-3 text-muted">Nenhuma guia atrelada a este
                                        lote.</td>
                                </tr>
                                <tr v-for="guia in lote.guias" :key="guia.id">
                                    <td><span class="fw-medium text-primary">#{{ guia.id }}</span></td>
                                    <td>
                                        <a v-if="guia.agendamento_id"
                                            :href="route('guias.imprimirDaAgenda', guia.agendamento_id)" target="_blank"
                                            class="text-primary fw-medium text-decoration-underline">
                                            {{ guia.numero_guia_prestador || guia.numero_guia_operadora || 'Ver Guia' }}
                                        </a>
                                        <span v-else class="text-primary fw-medium">{{ guia.numero_guia_prestador ||
                                            guia.numero_guia_operadora || '-' }}</span>
                                    </td>
                                    <td>{{ guia.data_atendimento }}</td>
                                    <td>{{ guia.paciente_nome }}</td>
                                    <td>{{ guia.tipo || 'Guia de Consulta' }}</td>
                                    <td>{{ guia.senha || '-' }}</td>
                                    <td>{{ formatCurrency(guia.valor_total) }}</td>
                                    <td>
                                        <div v-if="guia.status === 'GLOSADA'">
                                            <input type="number" step="0.01" class="form-control form-control-sm" style="min-width: 90px; max-width: 100px;"
                                                v-model="guia.valor_glosado" @blur="atualizarValorGlosado(lote.id, guia.id, guia.valor_glosado)">
                                        </div>
                                        <span v-else-if="guia.valor_glosado > 0" class="text-danger fw-medium">{{ formatCurrency(guia.valor_glosado) }}</span>
                                        <span v-else class="text-muted">-</span>
                                    </td>
                                    <td>
                                        <Multiselect v-model="guia.status" :options="statusOptions" :canClear="false"
                                            :searchable="false" :append-to-body="true" style="min-width: 150px;"
                                            class="form-select-sm"
                                            @change="atualizarStatusGuia(lote.id, guia.id, $event)" />
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-soft-danger shadow-none"
                                            @click.stop="askDeleteGuia(lote.id, guia.id)"
                                            :disabled="removendoGuia === guia.id">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Criar Lote -->
        <Modal v-model="showLoteModal" title="Criar Lote de Faturamento" size="lg" :show-footer="true"
            @save="salvarLote" cancel-text="Cancelar">
            <div class="mb-3">
                <label class="form-label">Selecione o Convênio</label>
                <Multiselect v-model="loteForm.convenio_id" :options="conveniosOptions"
                    placeholder="Escolha um convênio..." @change="carregarGuias" />
            </div>

            <div v-if="loadingGuias" class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Buscando guias disponíveis...</p>
            </div>

            <div v-if="!loadingGuias && loteForm.convenio_id" class="mt-4">
                <h6 class="fs-14 mb-3">Guias Disponíveis para Faturamento ({{ guiasDisponiveis.length }})</h6>

                <div v-if="guiasDisponiveis.length === 0" class="alert alert-info">
                    Nenhuma guia pendente encontrada para este convênio.
                </div>

                <div v-else class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th scope="col" style="width: 50px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" @change="toggleAllGuias"
                                            :checked="allGuiasSelected">
                                    </div>
                                </th>
                                <th>ID/Código</th>
                                <th>Paciente</th>
                                <th>Senha/Aut.</th>
                                <th>Status</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="guia in guiasDisponiveis" :key="guia.id">
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" :value="guia.id"
                                            v-model="loteForm.guias">
                                    </div>
                                </td>
                                <td>#{{ guia.id }}</td>
                                <td>{{ guia.atendimento?.agendamento?.paciente?.nome || 'Não informado' }}</td>
                                <td>{{ guia.senha || '-' }}</td>
                                <td>
                                    <span class="badge" :class="getBadgeClass(guia.status)">{{ guia.status }}</span>
                                </td>
                                <td>{{ formatCurrency(guia.valor_total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </Modal>

        <!-- Modal Adicionar Nova Guia -->
        <Modal v-model="showAddModal" :title="'Adicionar Guias - Lote #' + (gerenciarLote?.id || '')" size="xl"
            :show-footer="false">
            <div v-if="loadingDisponiveisAdd" class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Buscando guias disponíveis...</p>
            </div>
            <div v-else>
                <div v-if="guiasDisponiveisAdd.length === 0" class="alert alert-info border-0 mb-0">
                    <i class="ri-information-line me-2"></i> Não há outras guias pendentes para o convênio <strong>{{
                        gerenciarLote?.convenio }}</strong>.
                </div>
                <div v-else>
                    <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                        <table class="table table-hover align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th scope="col" style="width: 50px;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" @change="toggleAllGuiasAdd"
                                                :checked="allGuiasAddSelected">
                                        </div>
                                    </th>
                                    <th>ID</th>
                                    <th>Nº da Guia</th>
                                    <th>Paciente</th>
                                    <th>Carteira</th>
                                    <th>Procedimento</th>
                                    <th>Profissional</th>
                                    <th>Tipo</th>
                                    <th>Senha/Aut.</th>
                                    <th>Status</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="guia in guiasDisponiveisAdd" :key="guia.id">
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" :value="guia.id"
                                                v-model="guiasParaAdicionar">
                                        </div>
                                    </td>
                                    <td>#{{ guia.id }}</td>
                                    <td>
                                        <a v-if="guia.atendimento?.agendamento_id"
                                            :href="route('guias.imprimirDaAgenda', guia.atendimento.agendamento_id)"
                                            target="_blank" class="text-primary fw-medium text-decoration-underline">
                                            {{ guia.numero_guia_prestador || guia.numero_guia_operadora || 'Ver Guia' }}
                                        </a>
                                        <span v-else class="text-primary fw-medium">{{ guia.numero_guia_prestador ||
                                            guia.numero_guia_operadora || '-' }}</span>
                                    </td>
                                    <td>
                                        {{ guia.atendimento?.agendamento?.paciente?.nome || 'Não informado' }}<br>
                                    </td>
                                    <td>{{ guia.numero_carteira || '-' }}</td>
                                    <td>{{ guia.procedimento_solicitado_descricao || '-' }}</td>
                                    <td>{{ guia.profissional_solicitante_nome || '-' }}</td>
                                    <td>{{ guia.tipo || 'Guia de Consulta' }}</td>
                                    <td>{{ guia.senha || '-' }}</td>
                                    <td><span class="badge" :class="getBadgeClass(guia.status)">{{ guia.status }}</span>
                                    </td>
                                    <td>{{ formatCurrency(guia.valor_total) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3 p-3 bg-light rounded">
                        <span class="me-3 fw-medium">Selecionadas: {{ guiasParaAdicionar.length }}</span>
                        <button class="btn btn-success" @click="adicionarGuiasAoLote"
                            :disabled="guiasParaAdicionar.length === 0 || adicionandoGuias">
                            <i class="ri-add-circle-line align-bottom me-1"></i>
                            <span v-if="adicionandoGuias">Adicionando...</span>
                            <span v-else>Adicionar Guias Selecionadas</span>
                        </button>
                    </div>
                </div>
            </div>
        </Modal>

        <ModalDelete v-model="deleteModal" title="Remover Guia" :subTitle="deleteSubTitle" @save="confirmDeleteGuia" />

    </Layout>
</template>

<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Modal from "@/Components/Modal.vue";
import ModalDelete from "@/Components/ModalDelete.vue";
import Multiselect from '@vueform/multiselect';
import "@vueform/multiselect/themes/default.css";
import axios from "axios";

const props = defineProps({
    faturamentos: { type: Array, default: () => [] },
    convenios_list: { type: Array, default: () => [] },
});

function formatCurrency(n) {
    const v = Number(n || 0);
    try {
        return v.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
    } catch (_) {
        return `R$ ${v.toFixed(2)}`.replace(".", ",");
    }
}

// Criar Lote Logic
const showLoteModal = ref(false);
const loadingGuias = ref(false);
const guiasDisponiveis = ref([]);

const conveniosOptions = computed(() => {
    return props.convenios_list.map(c => ({
        value: c.id,
        label: c.descricao
    }));
});

const loteForm = useForm({
    convenio_id: null,
    guias: []
});

function openCriarLoteModal() {
    loteForm.reset();
    guiasDisponiveis.value = [];
    showLoteModal.value = true;
}

function carregarGuias(val) {
    if (!val) {
        guiasDisponiveis.value = [];
        return;
    }
    loadingGuias.value = true;
    axios.get(route('faturamento.guias_disponiveis'), { params: { convenio_id: val } })
        .then(res => {
            guiasDisponiveis.value = res.data || [];
            loteForm.guias = [];
        })
        .finally(() => {
            loadingGuias.value = false;
        });
}

const allGuiasSelected = computed(() => {
    return guiasDisponiveis.value.length > 0 && loteForm.guias.length === guiasDisponiveis.value.length;
});

function toggleAllGuias(e) {
    if (e.target.checked) {
        loteForm.guias = guiasDisponiveis.value.map(g => g.id);
    } else {
        loteForm.guias = [];
    }
}

function salvarLote() {
    loteForm.post(route('faturamento.store_lote'), {
        onSuccess: () => {
            showLoteModal.value = false;
        }
    });
}

// Toggle Collapse Logic
const expandedLotes = ref([]);

function isExpanded(loteId) {
    return expandedLotes.value.includes(loteId);
}

function toggleCollapse(loteId) {
    if (isExpanded(loteId)) {
        expandedLotes.value = expandedLotes.value.filter(id => id !== loteId);
    } else {
        expandedLotes.value.push(loteId);
    }
}

// Remover Guia Logic
const removendoGuia = ref(null);
const deleteModal = ref(false);
const deleteSubTitle = ref('Deseja realmente remover esta guia do lote?');
const guiaToRemove = ref({ loteId: null, guiaId: null });

function askDeleteGuia(loteId, guiaId) {
    guiaToRemove.value = { loteId, guiaId };
    deleteModal.value = true;
}

function confirmDeleteGuia() {
    if (!guiaToRemove.value.loteId) return;

    removendoGuia.value = guiaToRemove.value.guiaId;
    router.delete(route('faturamentos.guias.remove', { lote: guiaToRemove.value.loteId, guia: guiaToRemove.value.guiaId }), {
        preserveScroll: true,
        onFinish: () => {
            removendoGuia.value = null;
            deleteModal.value = false;
        }
    });
}

const statusOptions = [
    { value: 'ATENDIDO', label: 'ATENDIDO' },
    { value: 'PAGA', label: 'PAGA' },
    { value: 'GLOSADA', label: 'GLOSADA' },
    { value: 'DEVOLVIDA', label: 'DEVOLVIDA' }
];

function atualizarStatusGuia(loteId, guiaId, novoStatus) {
    router.patch(route('faturamentos.guias.updateStatus', { lote: loteId, guia: guiaId }), { status: novoStatus }, {
        preserveScroll: true,
        onError: () => {
            alert('Erro ao atualizar o status da guia.');
        }
    });
}

function atualizarValorGlosado(loteId, guiaId, valorGlosado) {
    router.patch(route('faturamentos.guias.updateGlosa', { lote: loteId, guia: guiaId }), { valor_glosado: valorGlosado }, {
        preserveScroll: true,
        onError: () => {
            alert('Erro ao atualizar o valor glosado.');
        }
    });
}

// Adicionar Guia Logic
const showAddModal = ref(false);
const gerenciarLote = ref(null);
const guiasDisponiveisAdd = ref([]);
const loadingDisponiveisAdd = ref(false);
const guiasParaAdicionar = ref([]);
const adicionandoGuias = ref(false);

function abrirAddModal(lote) {
    gerenciarLote.value = lote;
    showAddModal.value = true;
    carregarGuiasDisponiveisAdd();
}

function carregarGuiasDisponiveisAdd() {
    loadingDisponiveisAdd.value = true;
    axios.get(route('faturamento.guias_disponiveis'), { params: { convenio_id: gerenciarLote.value.convenio_id } })
        .then(res => {
            guiasDisponiveisAdd.value = res.data || [];
            guiasParaAdicionar.value = [];
        })
        .finally(() => {
            loadingDisponiveisAdd.value = false;
        });
}

const allGuiasAddSelected = computed(() => {
    return guiasDisponiveisAdd.value.length > 0 && guiasParaAdicionar.value.length === guiasDisponiveisAdd.value.length;
});

function toggleAllGuiasAdd(e) {
    if (e.target.checked) {
        guiasParaAdicionar.value = guiasDisponiveisAdd.value.map(g => g.id);
    } else {
        guiasParaAdicionar.value = [];
    }
}

function adicionarGuiasAoLote() {
    adicionandoGuias.value = true;
    router.post(route('faturamentos.guias.add', gerenciarLote.value.id), {
        guias: guiasParaAdicionar.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showAddModal.value = false;
        },
        onFinish: () => {
            adicionandoGuias.value = false;
        }
    });
}

// Helpers
function getBadgeClass(status) {
    switch (status) {
        case 'CRIADA': return 'bg-secondary';
        case 'ATENDIDO': return 'bg-info';
        case 'EM_ANALISE': return 'bg-warning text-dark';
        case 'AUTORIZADA': return 'bg-success';
        case 'GLOSADA': return 'bg-danger';
        case 'DEVOLVIDA': return 'bg-dark';
        case 'PAGA': return 'bg-primary';
        default: return 'bg-info';
    }
}

function getProgressBarClass(status) {
    switch (status) {
        case 'CRIADA': return 'bg-secondary';
        case 'ATENDIDO': return 'bg-info';
        case 'EM_ANALISE': return 'bg-warning text-dark';
        case 'AUTORIZADA': return 'bg-success';
        case 'GLOSADA': return 'bg-danger';
        case 'DEVOLVIDA': return 'bg-dark';
        case 'PAGA': return 'bg-primary';
        default: return 'bg-info';
    }
}

function getProgressTextClass(status) {
    switch (status) {
        case 'CRIADA': return 'text-secondary';
        case 'ATENDIDO': return 'text-info';
        case 'EM_ANALISE': return 'text-warning';
        case 'AUTORIZADA': return 'text-success';
        case 'GLOSADA': return 'text-danger';
        case 'DEVOLVIDA': return 'text-dark';
        case 'PAGA': return 'text-primary';
        default: return 'text-info';
    }
}

function getLoteStatusClass(status) {
    if (status === 'ENVIADO') return 'bg-primary';
    if (status === 'RECEBIDO') return 'bg-success';
    if (status === 'AGUARDANDO_ENVIO') return 'bg-warning text-dark';
    return 'bg-secondary';
}
</script>
