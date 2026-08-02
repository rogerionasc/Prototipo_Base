<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { useChoicesRemoteSearch } from "@/Composables/useChoicesRemoteSearch";
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import Multiselect from "@vueform/multiselect";
import "@vueform/multiselect/themes/default.css";
import Swal from 'sweetalert2';

const props = defineProps({
    atendimento: Object,
    paciente: Object,
    pep: Object,
    historico: Array,
    auth_profissional_id: Number,
});

// Utilities
const canEditPep = computed(() => {
    return props.pep?.profissional_id === props.auth_profissional_id;
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    // Substitui espaço por T para parse correto no Safari/iOS (ex: "2026-07-25 10:00:00" -> "2026-07-25T10:00:00")
    const safeDate = dateString.includes('T') ? dateString : dateString.replace(' ', 'T');
    const date = new Date(safeDate);
    if (isNaN(date.getTime())) return dateString;

    return date.toLocaleString('pt-BR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};

// ----------------------------------------------------------------------
// ANAMNESE
// ----------------------------------------------------------------------
const anamneseForm = useForm({
    queixa_principal: props.pep?.anamnese?.queixa_principal || '',
    historia_doenca_atual: props.pep?.anamnese?.historia_doenca_atual || '',
    antecedentes_pessoais: props.pep?.anamnese?.antecedentes_pessoais || '',
    alergias: props.pep?.anamnese?.alergias || '',
    medicamentos_uso: props.pep?.anamnese?.medicamentos_uso || '',
});

const sinaisVitaisAtuais = props.pep?.sinais_vitais && props.pep.sinais_vitais.length > 0 ? props.pep.sinais_vitais[0] : {};

const sinaisForm = useForm({
    pressao_sistolica: sinaisVitaisAtuais.pressao_sistolica || '',
    pressao_diastolica: sinaisVitaisAtuais.pressao_diastolica || '',
    frequencia_cardiaca: sinaisVitaisAtuais.frequencia_cardiaca || '',
    frequencia_respiratoria: sinaisVitaisAtuais.frequencia_respiratoria || '',
    temperatura: sinaisVitaisAtuais.temperatura || '',
    saturacao: sinaisVitaisAtuais.saturacao || '',
    peso: sinaisVitaisAtuais.peso || '',
    altura: sinaisVitaisAtuais.altura || '',
    imc: sinaisVitaisAtuais.imc || '',
    glicemia: sinaisVitaisAtuais.glicemia || '',
    circunferencia_abdominal: sinaisVitaisAtuais.circunferencia_abdominal || '',
    observacao: sinaisVitaisAtuais.observacao || ''
});

watch([() => sinaisForm.peso, () => sinaisForm.altura], ([peso, altura]) => {
    if (peso && altura && altura > 0) {
        // Supondo altura em metros (ex: 1.75). Se digitar 175, pode dar erro, vamos tentar converter de forma segura.
        let alt = parseFloat(altura);
        let p = parseFloat(peso);
        if (alt > 3) {
            alt = alt / 100; // se o usuário digitou em cm
        }
        if (alt > 0 && p > 0) {
            sinaisForm.imc = (p / (alt * alt)).toFixed(2);
        } else {
            sinaisForm.imc = '';
        }
    } else {
        sinaisForm.imc = '';
    }
});

const saveSinaisVitais = () => {
    sinaisForm.post(route('atendimentos.pep.sinais-vitais.save', props.atendimento.id), {
        preserveScroll: true,
    });
};

const saveAnamnese = () => {
    if (!canEditPep.value) return;
    anamneseForm.post(route('atendimentos.pep.anamnese.save', props.atendimento.id), {
        preserveScroll: true
    });
};

// ----------------------------------------------------------------------
// EVOLUÇÕES
// ----------------------------------------------------------------------
const evolucaoForm = useForm({
    tipo: 'Evolução Clínica',
    descricao: ''
});

const saveEvolucao = () => {
    evolucaoForm.post(route('atendimentos.pep.evolucao.save', props.atendimento.id), {
        preserveScroll: true,
        onSuccess: () => {
            evolucaoForm.reset();
        }
    });
};

import ModalDelete from "@/Components/ModalDelete.vue";

const deleteModal = ref(false);
const itemToDelete = ref(null);
const deleteType = ref('');

const openDeleteEvolucao = (evolucao) => {
    itemToDelete.value = { ...evolucao, nome: 'Evolução Clínica' };
    deleteType.value = 'evolucao';
    deleteModal.value = true;
};

const openDeletePrescricao = (prescricao) => {
    itemToDelete.value = { ...prescricao, nome: 'Prescrição Médica' };
    deleteType.value = 'prescricao';
    deleteModal.value = true;
};

const confirmDelete = () => {
    if (deleteType.value === 'evolucao') {
        useForm({}).delete(route('atendimentos.pep.evolucao.delete', {
            atendimento: props.atendimento.id,
            evolucao: itemToDelete.value.id
        }), {
            preserveScroll: true,
            onSuccess: () => {
                deleteModal.value = false;
            }
        });
    } else if (deleteType.value === 'prescricao') {
        useForm({}).delete(route('atendimentos.pep.prescricao.delete', {
            atendimento: props.atendimento.id,
            prescricao: itemToDelete.value.id
        }), {
            preserveScroll: true,
            onSuccess: () => {
                deleteModal.value = false;
            }
        });
    }
};

const deleteEvolucao = (evolucao) => openDeleteEvolucao(evolucao);
const deletePrescricao = (prescricao) => openDeletePrescricao(prescricao);

// Consolida evoluções do atendimento atual + histórico
const todasEvolucoes = computed(() => {
    let ev = [...(props.pep?.evolucoes || [])];
    props.historico?.forEach(h => {
        if (h.evolucoes) ev.push(...h.evolucoes);
    });
    return ev.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

// ----------------------------------------------------------------------
// PRESCRIÇÕES
// ----------------------------------------------------------------------
const itemPrescricaoInicial = { medicamento_nome: '', dosagem: '', frequencia: '', via: '', duracao: '', quantidade: 1 };
const prescricaoForm = useForm({
    observacao: '',
    itens: [{ ...itemPrescricaoInicial }]
});

const addPrescricaoItem = () => {
    prescricaoForm.itens.push({ ...itemPrescricaoInicial });
};

const removePrescricaoItem = (index) => {
    prescricaoForm.itens.splice(index, 1);
};

const savePrescricao = () => {
    prescricaoForm.post(route('atendimentos.pep.prescricao.save', props.atendimento.id), {
        preserveScroll: true,
        onSuccess: () => {
            prescricaoForm.reset();
            prescricaoForm.itens = [{ ...itemPrescricaoInicial }];
        }
    });
};

const diagnosticoForm = useForm({
    cid_id: null,
    descricao: '',
    principal: false,
    confirmado: false,
});

const selCid = ref(null);
const cidChoicesRows = ref([]);

const cidSearch = useChoicesRemoteSearch({
    selectRef: selCid,
    refreshChoices: () => {
        if (window.initChoices) window.initChoices();
    },
    getSelectedValue: () => (diagnosticoForm.cid_id != null ? String(diagnosticoForm.cid_id) : ""),
    getRows: () => cidChoicesRows.value,
    fetchRows: async (q) => {
        const query = String(q || "").trim();
        if (!query) {
            cidChoicesRows.value = [];
            return [];
        }
        const resp = await window.axios.get(route('cids.search'), { params: { q: query } });
        const rows = Array.isArray(resp?.data) ? resp.data : [];
        cidChoicesRows.value = rows;
        return rows;
    },
    makeLabel: (c) => c.label,
    placeholderLabel: "Digite o código ou nome da doença para buscar...",
    placeholderDisabled: true,
});

onMounted(() => {
    nextTick(() => {
        if (window.initChoices) window.initChoices();
    });
});

const saveDiagnostico = () => {
    diagnosticoForm.post(route('atendimentos.pep.diagnostico.save', props.atendimento.id), {
        onSuccess: () => {
            diagnosticoForm.reset();
            cidSearch.clearSearch(); // Usando o composable para limpar a busca
        }
    });
};

const todosDiagnosticos = computed(() => {
    let diags = [...(props.pep?.diagnosticos || [])];
    return diags.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

const todasPrescricoes = computed(() => {
    let prescricoes = [...(props.pep?.prescricoes || [])];
    return prescricoes.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

import Modal from "@/Components/Modal.vue";

const showFinalizarModal = ref(false);

const openFinalizarModal = () => {
    showFinalizarModal.value = true;
};

const finalizarAtendimento = () => {
    useForm({}).post(route('atendimentos.finalizar', props.atendimento.id), {
        onSuccess: () => {
            showFinalizarModal.value = false;
        }
    });
};

</script>

<template>
    <Layout>

        <Head title="Prontuário Eletrônico (PEP)" />
        <PageHeader title="PEP - Prontuário Eletrônico do Paciente" pageTitle="Consultório" />

        <!-- Cabeçalho do Paciente -->
        <div class="row">
            <div class="col-12">
                <div class="card overflow-hidden shadow-sm">
                    <div class="card-body bg-soft-primary pb-0 px-4">
                        <div class="row align-items-center">
                            <div class="col-md">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm">
                                            <div class="avatar-title bg-primary rounded-circle fs-20">
                                                <i class="ri-user-heart-line"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h4 class="mb-1">{{ paciente?.nome }}</h4>
                                        <p class="text-muted mb-0">CPF: {{ paciente?.cpf || 'N/A' }} | Nasc: {{
                                            formatDate(paciente?.data_nascimento).split(' ')[0] || 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-auto">
                                <div class="d-flex gap-2">
                                    <button v-if="canEditPep && pep?.status !== 'Encerrado'" @click="openFinalizarModal"
                                        class="btn btn-success shadow-sm">
                                        <i class="ri-check-double-line align-bottom me-1"></i> Finalizar Atendimento
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="nav flex-column nav-pills custom-hover-nav-tabs" id="v-pills-tab" role="tablist"
                            aria-orientation="vertical">
                            <a class="nav-link active mb-2" id="v-pills-resumo-tab" data-bs-toggle="pill"
                                href="#v-pills-resumo" role="tab" aria-selected="true">
                                <i class="ri-history-line d-inline-block text-center me-2 fs-16"></i> Histórico / Resumo
                            </a>
                            <a class="nav-link mb-2" id="v-pills-triagem-tab" data-bs-toggle="pill"
                                href="#v-pills-triagem" role="tab" aria-selected="false">
                                <i class="ri-heart-pulse-line d-inline-block text-center me-2 fs-16"></i> Triagem
                            </a>
                            <a class="nav-link mb-2" id="v-pills-anamnese-tab" data-bs-toggle="pill"
                                href="#v-pills-anamnese" role="tab" aria-selected="false">
                                <i class="ri-file-list-3-line d-inline-block text-center me-2 fs-16"></i> Anamnese
                            </a>
                            <a class="nav-link mb-2" id="v-pills-evolucao-tab" data-bs-toggle="pill"
                                href="#v-pills-evolucao" role="tab" aria-selected="false">
                                <i class="ri-pulse-line d-inline-block text-center me-2 fs-16"></i> Evolução
                            </a>
                            <a class="nav-link mb-2" id="v-pills-diagnosticos-tab" data-bs-toggle="pill"
                                href="#v-pills-diagnosticos" role="tab" aria-selected="false">
                                <i class="ri-stethoscope-line d-inline-block text-center me-2 fs-16"></i> Diagnósticos
                            </a>
                            <a class="nav-link mb-2" id="v-pills-prescricao-tab" data-bs-toggle="pill"
                                href="#v-pills-prescricao" role="tab" aria-selected="false">
                                <i class="ri-medicine-bottle-line d-inline-block text-center me-2 fs-16"></i>
                                Prescrições
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="tab-content text-muted mt-4 mt-md-0" id="v-pills-tabContent">

                    <!-- RESUMO E HISTÓRICO -->
                    <div class="tab-pane fade show active" id="v-pills-resumo" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-header border-0">
                                <h5 class="card-title mb-0">Resumo do Atendimento Atual</h5>
                            </div>
                            <div class="card-body border-top border-top-dashed">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <p class="text-muted mb-1">Procedimento:</p>
                                        <h6 class="fs-14 mb-3">{{ atendimento?.procedimento?.nome || 'N/A' }}</h6>
                                        <p class="text-muted mb-1">Status do PEP:</p>
                                        <span class="badge bg-success-subtle text-success fs-12">{{ pep?.status
                                        }}</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <p class="text-muted mb-1">Médico Responsável:</p>
                                        <h6 class="fs-14 mb-3">{{ atendimento?.medico?.nome || 'N/A' }}</h6>
                                        <p class="text-muted mb-1">Iniciado em:</p>
                                        <h6 class="fs-14 mb-0">{{ formatDate(pep?.aberto_em) }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header border-0">
                                <h5 class="card-title mb-0">Histórico de Prontuários ({{ historico?.length || 0 }})</h5>
                            </div>
                            <div class="card-body">
                                <div v-if="!historico || historico.length === 0" class="text-center py-4">
                                    <div class="avatar-md mx-auto mb-3">
                                        <div class="avatar-title bg-light text-muted rounded-circle fs-24"><i
                                                class="ri-history-line"></i></div>
                                    </div>
                                    <h5 class="fs-15">Nenhum histórico encontrado</h5>
                                    <p class="text-muted">Este é o primeiro atendimento registrado em prontuário para
                                        este paciente.</p>
                                </div>
                                <div v-else class="accordion custom-accordionwithicon" id="accordionHistorico">
                                    <div class="accordion-item" v-for="(hist, index) in historico" :key="hist.id">
                                        <h2 class="accordion-header" :id="'heading' + index">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" :data-bs-target="'#collapse' + index"
                                                aria-expanded="false" :aria-controls="'collapse' + index">
                                                <div
                                                    class="d-flex w-100 justify-content-between align-items-center me-3">
                                                    <span>
                                                        <i class="ri-calendar-event-line me-2 text-primary"></i>
                                                        Atendimento em {{ formatDate(hist.aberto_em) }}
                                                    </span>
                                                    <span class="fw-medium text-dark"><i
                                                            class="ri-stethoscope-line text-muted me-1 align-bottom"></i>{{
                                                                hist.atendimento?.medico?.nome }}</span>
                                                </div>
                                            </button>
                                        </h2>
                                        <div :id="'collapse' + index" class="accordion-collapse collapse"
                                            :aria-labelledby="'heading' + index" data-bs-parent="#accordionHistorico">
                                            <div class="accordion-body">
                                                <div v-if="hist.sinais_vitais && hist.sinais_vitais.length > 0">
                                                    <h6 class="fs-13 text-primary mb-2">Triagem / Sinais Vitais</h6>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-sm-3 col-6"
                                                            v-if="hist.sinais_vitais[0].pressao_sistolica || hist.sinais_vitais[0].pressao_diastolica">
                                                            <div
                                                                class="p-2 border border-dashed rounded text-center bg-light bg-opacity-50">
                                                                <p class="text-muted mb-1 fs-11 text-uppercase">Pressão
                                                                    (PA)</p>
                                                                <h6 class="fs-13 mb-0">{{
                                                                    hist.sinais_vitais[0].pressao_sistolica || '-' }} /
                                                                    {{ hist.sinais_vitais[0].pressao_diastolica || '-'
                                                                    }}</h6>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3 col-6"
                                                            v-if="hist.sinais_vitais[0].frequencia_cardiaca">
                                                            <div
                                                                class="p-2 border border-dashed rounded text-center bg-light bg-opacity-50">
                                                                <p class="text-muted mb-1 fs-11 text-uppercase">Freq.
                                                                    Card. (FC)</p>
                                                                <h6 class="fs-13 mb-0">{{
                                                                    hist.sinais_vitais[0].frequencia_cardiaca }} bpm
                                                                </h6>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3 col-6"
                                                            v-if="hist.sinais_vitais[0].temperatura">
                                                            <div
                                                                class="p-2 border border-dashed rounded text-center bg-light bg-opacity-50">
                                                                <p class="text-muted mb-1 fs-11 text-uppercase">
                                                                    Temperatura (T)</p>
                                                                <h6 class="fs-13 mb-0">{{
                                                                    hist.sinais_vitais[0].temperatura }} °C</h6>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3 col-6"
                                                            v-if="hist.sinais_vitais[0].saturacao">
                                                            <div
                                                                class="p-2 border border-dashed rounded text-center bg-light bg-opacity-50">
                                                                <p class="text-muted mb-1 fs-11 text-uppercase">
                                                                    Saturação (SpO2)</p>
                                                                <h6 class="fs-13 mb-0">{{
                                                                    hist.sinais_vitais[0].saturacao }} %</h6>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3 col-6" v-if="hist.sinais_vitais[0].peso">
                                                            <div
                                                                class="p-2 border border-dashed rounded text-center bg-light bg-opacity-50">
                                                                <p class="text-muted mb-1 fs-11 text-uppercase">Peso</p>
                                                                <h6 class="fs-13 mb-0">{{ hist.sinais_vitais[0].peso }}
                                                                    kg</h6>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3 col-6" v-if="hist.sinais_vitais[0].altura">
                                                            <div
                                                                class="p-2 border border-dashed rounded text-center bg-light bg-opacity-50">
                                                                <p class="text-muted mb-1 fs-11 text-uppercase">Altura
                                                                </p>
                                                                <h6 class="fs-13 mb-0">{{ hist.sinais_vitais[0].altura
                                                                }} m</h6>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3 col-6" v-if="hist.sinais_vitais[0].imc">
                                                            <div
                                                                class="p-2 border border-dashed rounded text-center bg-light bg-opacity-50">
                                                                <p class="text-muted mb-1 fs-11 text-uppercase">IMC</p>
                                                                <h6 class="fs-13 mb-0">{{ hist.sinais_vitais[0].imc }}
                                                                </h6>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3 col-6"
                                                            v-if="hist.sinais_vitais[0].glicemia">
                                                            <div
                                                                class="p-2 border border-dashed rounded text-center bg-light bg-opacity-50">
                                                                <p class="text-muted mb-1 fs-11 text-uppercase">Glicemia
                                                                </p>
                                                                <h6 class="fs-13 mb-0">{{ hist.sinais_vitais[0].glicemia
                                                                }}</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div v-if="hist.anamnese">
                                                    <h6 class="fs-13 text-primary mb-1">Queixa Principal</h6>
                                                    <p class="text-muted">{{ hist.anamnese.queixa_principal || '-' }}
                                                    </p>
                                                </div>
                                                <div class="mt-3" v-if="hist.evolucoes && hist.evolucoes.length > 0">
                                                    <h6 class="fs-13 text-primary mb-2">Evoluções ({{
                                                        hist.evolucoes.length }})</h6>
                                                    <div class="vstack gap-2">
                                                        <div v-for="ev in hist.evolucoes" :key="ev.id"
                                                            class="p-2 border border-dashed rounded bg-light bg-opacity-50">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center mb-1">
                                                                <span class="fs-12 fw-medium text-body"><i
                                                                        class="mdi mdi-circle-medium text-success"></i>
                                                                    {{ ev.profissional?.nome || 'Profissional'
                                                                    }}</span>
                                                                <span class="fs-12 text-muted">{{
                                                                    formatDate(ev.created_at) }}</span>
                                                            </div>
                                                            <p class="text-muted mb-0 fs-13 ps-3">{{ ev.descricao }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3"
                                                    v-if="hist.prescricoes && hist.prescricoes.length > 0">
                                                    <h6 class="fs-13 text-primary mb-2"><i
                                                            class="ri-file-list-3-fill me-1 align-middle"></i>Prescrições
                                                        ({{ hist.prescricoes.length }})</h6>
                                                    <div class="vstack gap-3">
                                                        <div v-for="pres in hist.prescricoes" :key="pres.id"
                                                            class="p-3 border border-dashed rounded bg-light bg-opacity-50 position-relative">
                                                            <div class="list-group list-group-flush mb-2"
                                                                v-if="pres.itens && pres.itens.length > 0">
                                                                <div class="list-group-item px-0 py-2 bg-transparent border-dashed"
                                                                    v-for="item in pres.itens" :key="item.id">
                                                                    <div
                                                                        class="d-flex justify-content-between align-items-start">
                                                                        <div>
                                                                            <h6 class="mb-1 text-primary fs-14"><i
                                                                                    class="mdi mdi-pill me-1 text-muted"></i>{{
                                                                                        item.observacao || 'Medicamento' }}</h6>
                                                                            <p class="mb-0 text-muted fs-13">
                                                                                <i
                                                                                    class="ri-time-line align-middle me-1"></i>{{
                                                                                        item.dosagem }} <span
                                                                                    class="mx-1">•</span> {{
                                                                                        item.frequencia }}
                                                                            </p>
                                                                        </div>
                                                                        <div class="text-end">
                                                                            <span
                                                                                class="badge bg-info-subtle text-info mb-1">{{
                                                                                    item.via }}</span>
                                                                            <div class="fs-12 text-muted fw-medium">Qtd:
                                                                                {{ item.quantidade }}</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="alert alert-secondary border-0 bg-secondary bg-opacity-10 rounded p-2 mb-2"
                                                                v-if="pres.observacao">
                                                                <span
                                                                    class="fw-semibold text-dark fs-12 d-block mb-1"><i
                                                                        class="ri-information-line me-1 align-middle text-secondary"></i>Observações
                                                                    Gerais</span>
                                                                <p class="mb-0 text-muted fs-13">{{ pres.observacao }}
                                                                </p>
                                                            </div>

                                                            <div class="text-end mt-1">
                                                                <span class="fs-12 text-muted"><i
                                                                        class="ri-calendar-line me-1"></i>{{
                                                                            formatDate(pres.created_at) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3"
                                                    v-if="hist.diagnosticos && hist.diagnosticos.length > 0">
                                                    <h6 class="fs-13 text-primary mb-2"><i
                                                            class="ri-stethoscope-fill me-1 align-middle"></i>Diagnósticos
                                                        ({{ hist.diagnosticos.length }})</h6>
                                                    <div class="vstack gap-2">
                                                        <div v-for="diag in hist.diagnosticos" :key="diag.id"
                                                            class="p-2 border border-dashed rounded bg-light bg-opacity-50">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center mb-1">
                                                                <span class="fs-13 fw-semibold text-dark">{{ diag.cid ?
                                                                    diag.cid.codigo + ' - ' + diag.cid.descricao :
                                                                    'Diagnóstico' }}</span>
                                                                <div>
                                                                    <span
                                                                        class="badge bg-danger-subtle text-danger me-1"
                                                                        v-if="diag.principal">Principal</span>
                                                                    <span class="badge bg-success-subtle text-success"
                                                                        v-if="diag.confirmado">Confirmado</span>
                                                                </div>
                                                            </div>
                                                            <p class="text-muted fs-13 mb-1" v-if="diag.descricao">{{
                                                                diag.descricao }}</p>
                                                            <div class="text-end mt-1">
                                                                <span class="fs-12 text-muted"><i
                                                                        class="ri-user-line me-1"></i>{{
                                                                            diag.profissional?.nome || 'Profissional' }} &bull;
                                                                    {{ formatDate(diag.created_at) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TRIAGEM (SINAIS VITAIS) -->
                    <div class="tab-pane fade" id="v-pills-triagem" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-header border-0 bg-soft-light">
                                <h5 class="card-title mb-0">Triagem (Sinais Vitais)</h5>
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="saveSinaisVitais">
                                    <fieldset :disabled="!canEditPep">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Pressão Sistólica (mmHg)</label>
                                                <input type="text" class="form-control"
                                                    v-model="sinaisForm.pressao_sistolica" placeholder="Ex: 120">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Pressão Diastólica (mmHg)</label>
                                                <input type="text" class="form-control"
                                                    v-model="sinaisForm.pressao_diastolica" placeholder="Ex: 80">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Frequência Cardíaca (bpm)</label>
                                                <input type="text" class="form-control"
                                                    v-model="sinaisForm.frequencia_cardiaca" placeholder="Ex: 75">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Frequência Respiratória (ipm)</label>
                                                <input type="text" class="form-control"
                                                    v-model="sinaisForm.frequencia_respiratoria" placeholder="Ex: 16">
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">Temperatura (°C)</label>
                                                <input type="text" class="form-control" v-model="sinaisForm.temperatura"
                                                    placeholder="Ex: 36.5">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Saturação O2 (%)</label>
                                                <input type="text" class="form-control" v-model="sinaisForm.saturacao"
                                                    placeholder="Ex: 98">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Glicemia (mg/dL)</label>
                                                <input type="text" class="form-control" v-model="sinaisForm.glicemia"
                                                    placeholder="Ex: 90">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Circunferência Abdominal (cm)</label>
                                                <input type="text" class="form-control"
                                                    v-model="sinaisForm.circunferencia_abdominal" placeholder="Ex: 85">
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label">Peso (kg)</label>
                                                <input type="number" step="0.01" class="form-control"
                                                    v-model="sinaisForm.peso" placeholder="Ex: 70.5">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Altura (m)</label>
                                                <input type="number" step="0.01" class="form-control"
                                                    v-model="sinaisForm.altura" placeholder="Ex: 1.75">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">IMC</label>
                                                <input type="text" class="form-control bg-light"
                                                    v-model="sinaisForm.imc" readonly placeholder="Calculado auto">
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label">Observações da Triagem</label>
                                                <textarea class="form-control" rows="3" v-model="sinaisForm.observacao"
                                                    placeholder="Anotações gerais sobre o estado físico do paciente..."></textarea>
                                            </div>

                                            <div class="col-12 text-end mt-4">
                                                <button type="submit" class="btn btn-primary"
                                                    :disabled="sinaisForm.processing">
                                                    <i class="ri-save-3-line align-bottom me-1"></i>
                                                    {{ sinaisForm.processing ? 'Salvando...' : 'Salvar Triagem' }}
                                                </button>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- ANAMNESE -->
                    <div class="tab-pane fade" id="v-pills-anamnese" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Anamnese do Atendimento</h5>
                                <span v-if="!canEditPep" class="badge bg-warning-subtle text-warning"><i
                                        class="ri-lock-line align-bottom me-1"></i> Somente Leitura</span>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info alert-border-left alert-dismissible fade show" role="alert"
                                    v-if="!canEditPep">
                                    <i class="ri-information-line me-3 align-middle fs-16 text-info"></i>
                                    Esta anamnese foi iniciada pelo <strong> {{ atendimento?.medico?.nome
                                    }}</strong>. Você não pode editá-la.
                                </div>

                                <form @submit.prevent="saveAnamnese">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Queixa Principal</label>
                                            <textarea class="form-control" v-model="anamneseForm.queixa_principal"
                                                rows="2" placeholder="Motivo da consulta..."
                                                :disabled="!canEditPep"></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">História da Doença Atual (HDA)</label>
                                            <textarea class="form-control" v-model="anamneseForm.historia_doenca_atual"
                                                rows="3" :disabled="!canEditPep"></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Antecedentes Pessoais e Familiares</label>
                                            <textarea class="form-control" v-model="anamneseForm.antecedentes_pessoais"
                                                rows="2" :disabled="!canEditPep"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Alergias</label>
                                            <textarea class="form-control" v-model="anamneseForm.alergias" rows="2"
                                                placeholder="Medicamentos, alimentos, etc."
                                                :disabled="!canEditPep"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Medicamentos em Uso</label>
                                            <textarea class="form-control" v-model="anamneseForm.medicamentos_uso"
                                                rows="2" :disabled="!canEditPep"></textarea>
                                        </div>
                                        <div class="col-12 text-end mt-4" v-if="canEditPep">
                                            <button type="submit" class="btn btn-primary shadow-sm"
                                                :disabled="anamneseForm.processing">
                                                <span v-if="anamneseForm.processing"
                                                    class="spinner-border spinner-border-sm me-1" role="status"
                                                    aria-hidden="true"></span>
                                                <i class="ri-save-line align-bottom me-1" v-else></i> Salvar Anamnese
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- EVOLUÇÃO -->
                    <div class="tab-pane fade" id="v-pills-evolucao" role="tabpanel">
                        <!-- Formulário de Nova Evolução -->
                        <div class="card shadow-sm">
                            <div class="card-header border-0 bg-soft-light">
                                <h5 class="card-title mb-0">Adicionar Evolução</h5>
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="saveEvolucao">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Tipo de Nota</label>
                                            <Multiselect v-model="evolucaoForm.tipo"
                                                :options="['Evolução Clínica', 'Nota de Enfermagem', 'Parecer']"
                                                placeholder="Selecione o tipo" :searchable="false" :can-clear="false" />
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Descrição</label>
                                            <textarea class="form-control" v-model="evolucaoForm.descricao" rows="3"
                                                placeholder="Descreva a evolução do paciente..." required></textarea>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-primary shadow-sm"
                                                :disabled="evolucaoForm.processing">
                                                <span v-if="evolucaoForm.processing"
                                                    class="spinner-border spinner-border-sm me-1" role="status"
                                                    aria-hidden="true"></span>
                                                <i class="ri-add-line align-bottom me-1" v-else></i> Adicionar Evolução
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Lista de Evoluções -->
                        <div class="card shadow-sm">
                            <div class="card-header border-0">
                                <h5 class="card-title mb-0">Linha do Tempo de Evoluções</h5>
                            </div>
                            <div class="card-body">
                                <div v-if="todasEvolucoes.length === 0" class="text-center py-4 text-muted">
                                    Nenhuma evolução registrada para este paciente.
                                </div>
                                <div class="profile-timeline" v-else>
                                    <div class="accordion accordion-flush" id="accordionEvolucoes">
                                        <div class="accordion-item border-0" v-for="ev in todasEvolucoes" :key="ev.id">
                                            <div class="accordion-header" :id="'evHeader' + ev.id">
                                                <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse"
                                                    :href="'#evCollapse' + ev.id" aria-expanded="true">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 avatar-xs">
                                                            <div class="avatar-title bg-success rounded-circle"><i
                                                                    class="ri-pulse-line"></i></div>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="fs-14 mb-0">{{ ev.tipo }} <span
                                                                    class="fw-normal text-muted ms-2 fs-12">{{
                                                                        formatDate(ev.created_at) }}</span></h6>
                                                            <p class="text-muted mb-0 fs-12"> {{
                                                                ev.profissional?.nome || 'Profissional' }}</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div :id="'evCollapse' + ev.id" class="accordion-collapse collapse show"
                                                data-bs-parent="#accordionEvolucoes">
                                                <div class="accordion-body ms-2 ps-5 pt-0">
                                                    <p class="text-muted mb-0">{{ ev.descricao }}</p>
                                                    <div class="mt-2"
                                                        v-if="props.auth_profissional_id && ev.profissional_id == props.auth_profissional_id">
                                                        <button class="btn btn-sm btn-ghost-danger"
                                                            @click="deleteEvolucao(ev)" title="Excluir Evolução"><i
                                                                class="ri-delete-bin-line fs-14"></i></button>
                                                    </div>
                                                    <div class="mt-2" v-else>
                                                        <span class="badge bg-light text-muted border"
                                                            title="Apenas o autor pode excluir"><i
                                                                class="ri-lock-line align-bottom"></i> Somente
                                                            Leitura</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PRESCRIÇÃO -->
                    <div class="tab-pane fade" id="v-pills-prescricao" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-header border-0 bg-soft-light">
                                <h5 class="card-title mb-0">Nova Prescrição</h5>
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="savePrescricao">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Observações Gerais</label>
                                            <textarea class="form-control" v-model="prescricaoForm.observacao" rows="2"
                                                placeholder="Dieta, repouso, cuidados..."></textarea>
                                        </div>

                                        <div class="col-12 mt-4">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fs-14 fw-medium mb-0">Itens da Prescrição</h6>
                                                <button type="button" class="btn btn-sm btn-soft-primary"
                                                    @click="addPrescricaoItem"><i class="ri-add-line align-bottom"></i>
                                                    Adicionar Item</button>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-nowrap align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Medicamento / Item</th>
                                                            <th width="15%">Dose</th>
                                                            <th width="20%">Via</th>
                                                            <th width="20%">Frequência</th>
                                                            <th width="10%">Qtd</th>
                                                            <th width="5%"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(item, index) in prescricaoForm.itens" :key="index">
                                                            <td>
                                                                <input type="text" class="form-control form-control-sm"
                                                                    v-model="item.medicamento_nome"
                                                                    placeholder="Ex: Dipirona 500mg" required>
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control form-control-sm"
                                                                    v-model="item.dosagem" placeholder="Ex: 1 comp"
                                                                    required>
                                                            </td>
                                                            <td>
                                                                <select class="form-select form-select-sm"
                                                                    v-model="item.via" required>
                                                                    <option value="">Selecione</option>
                                                                    <option value="Oral">Oral (VO)</option>
                                                                    <option value="Intravenosa">Intravenosa (IV)
                                                                    </option>
                                                                    <option value="Intramuscular">Intramuscular (IM)
                                                                    </option>
                                                                    <option value="Subcutânea">Subcutânea (SC)</option>
                                                                    <option value="Tópica">Tópica</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control form-control-sm"
                                                                    v-model="item.frequencia" placeholder="Ex: 8/8h"
                                                                    required>
                                                            </td>
                                                            <td>
                                                                <input type="number"
                                                                    class="form-control form-control-sm"
                                                                    v-model="item.quantidade" min="1" required>
                                                            </td>
                                                            <td>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-ghost-danger"
                                                                    @click="removePrescricaoItem(index)"
                                                                    :disabled="prescricaoForm.itens.length === 1">
                                                                    <i class="ri-delete-bin-line"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="col-12 text-end mt-3">
                                            <button type="submit" class="btn btn-primary shadow-sm"
                                                :disabled="prescricaoForm.processing || prescricaoForm.itens.length === 0">
                                                <span v-if="prescricaoForm.processing"
                                                    class="spinner-border spinner-border-sm me-1" role="status"
                                                    aria-hidden="true"></span>
                                                <i class="ri-save-line align-bottom me-1" v-else></i> Salvar Prescrição
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Histórico de Prescrições -->
                        <div class="card shadow-sm">
                            <div class="card-header border-0">
                                <h5 class="card-title mb-0">Receituários e Prescrições Deste Atendimento</h5>
                            </div>
                            <div class="card-body">
                                <div v-if="todasPrescricoes.length === 0" class="text-center py-4 text-muted">
                                    Nenhuma prescrição registrada para este paciente.
                                </div>
                                <div class="vstack gap-3" v-else>
                                    <div class="border rounded border-dashed p-3" v-for="pres in todasPrescricoes"
                                        :key="pres.id">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-grow-1">
                                                <h6 class="fs-14 mb-1">Prescrição - {{ formatDate(pres.created_at) }}
                                                </h6>
                                                <p class="text-muted fs-12 mb-0">Prescrito por: <span
                                                        class="fw-medium text-body"> {{ pres.profissional?.nome ||
                                                            'Profissional' }}</span></p>
                                            </div>
                                            <div class="flex-shrink-0 d-flex gap-2">
                                                <span
                                                    v-if="!props.auth_profissional_id || pres.profissional_id != props.auth_profissional_id"
                                                    class="badge bg-light text-muted border"
                                                    title="Apenas o autor pode editar/excluir"><i
                                                        class="ri-lock-line align-bottom"></i></span>
                                                <button
                                                    v-if="props.auth_profissional_id && pres.profissional_id == props.auth_profissional_id"
                                                    class="btn btn-sm btn-ghost-danger" @click="deletePrescricao(pres)"
                                                    title="Excluir"><i class="ri-delete-bin-line fs-14"></i></button>
                                                <!-- <button class="btn btn-sm btn-ghost-primary" title="Imprimir"><i class="ri-printer-line fs-14"></i></button> -->
                                            </div>
                                        </div>
                                        <p class="text-muted mb-2" v-if="pres.observacao"><i
                                                class="ri-file-info-line me-1"></i> {{ pres.observacao }}</p>

                                        <div class="table-responsive" v-if="pres.itens && pres.itens.length > 0">
                                            <table class="table table-sm table-borderless align-middle mb-0">
                                                <thead class="text-muted bg-light bg-opacity-50">
                                                    <tr>
                                                        <th>Item</th>
                                                        <th>Dose/Freq</th>
                                                        <th>Via</th>
                                                        <th>Qtd</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="item in pres.itens" :key="item.id">
                                                        <td class="fw-medium">{{ item.observacao || 'Medicamento' }}
                                                        </td>
                                                        <td>{{ item.dosagem }} - {{ item.frequencia }}</td>
                                                        <td><span class="badge bg-info-subtle text-info">{{ item.via
                                                        }}</span></td>
                                                        <td>{{ item.quantidade }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- DIAGNÓSTICOS -->
                    <div class="tab-pane fade" id="v-pills-diagnosticos" role="tabpanel">
                        <!-- Formulário -->
                        <div class="card shadow-sm" v-if="canEditPep">
                            <div class="card-header border-0 bg-soft-light">
                                <h5 class="card-title mb-0">Adicionar Diagnóstico</h5>
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="saveDiagnostico">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label">CID (Busca)</label>
                                            <select class="form-select" ref="selCid" id="cid_id_select" name="cid_id"
                                                v-model="diagnosticoForm.cid_id" data-choices>
                                                <option value="">Digite o código ou nome da doença para buscar...
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Descrição / Observação do Diagnóstico</label>
                                            <textarea class="form-control" v-model="diagnosticoForm.descricao" rows="2"
                                                placeholder="Descreva o diagnóstico com mais detalhes..."
                                                required></textarea>
                                        </div>
                                        <div class="col-md-12 d-flex gap-3">
                                            <div class="form-check form-switch form-switch-lg mb-0" dir="ltr">
                                                <input type="checkbox" class="form-check-input" id="diagPrincipal"
                                                    v-model="diagnosticoForm.principal">
                                                <label class="form-check-label" for="diagPrincipal">Diagnóstico
                                                    Principal</label>
                                            </div>
                                            <div class="form-check form-switch form-switch-lg mb-0 form-switch-success"
                                                dir="ltr">
                                                <input type="checkbox" class="form-check-input" id="diagConfirmado"
                                                    v-model="diagnosticoForm.confirmado">
                                                <label class="form-check-label" for="diagConfirmado">Diagnóstico
                                                    Confirmado</label>
                                            </div>
                                        </div>
                                        <div class="col-12 text-end mt-4">
                                            <button type="submit" class="btn btn-primary shadow-sm"
                                                :disabled="diagnosticoForm.processing">
                                                <span v-if="diagnosticoForm.processing"
                                                    class="spinner-border spinner-border-sm me-1" role="status"
                                                    aria-hidden="true"></span>
                                                <i class="ri-add-line align-bottom me-1" v-else></i> Salvar Diagnóstico
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Lista de Diagnósticos Deste Atendimento -->
                        <div class="card shadow-sm mt-3">
                            <div class="card-header border-0">
                                <h5 class="card-title mb-0">Diagnósticos Deste Atendimento</h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center py-4 text-muted" v-if="todosDiagnosticos.length === 0">
                                    <i class="ri-stethoscope-line display-6 text-light mb-2 d-inline-block"></i><br>
                                    Nenhum diagnóstico registrado para este atendimento.
                                </div>
                                <div class="vstack gap-3" v-else>
                                    <div class="border rounded border-dashed p-3" v-for="diag in todosDiagnosticos"
                                        :key="diag.id">
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="flex-grow-1">
                                                <h6 class="fs-15 mb-1 text-primary">
                                                    {{ diag.cid ? diag.cid.codigo + ' - ' + diag.cid.descricao :
                                                        'Diagnóstico' }}
                                                </h6>
                                                <div class="d-flex gap-2 mb-2">
                                                    <span class="badge bg-danger-subtle text-danger"
                                                        v-if="diag.principal">Principal</span>
                                                    <span class="badge bg-success-subtle text-success"
                                                        v-if="diag.confirmado">Confirmado</span>
                                                </div>
                                                <p class="text-muted fs-13 mb-1" v-if="diag.descricao">{{ diag.descricao
                                                }}</p>
                                                <p class="text-muted fs-12 mb-0">
                                                    Registrado por: <span class="fw-medium text-body"> {{
                                                        diag.profissional?.nome || 'Profissional' }}</span> em {{
                                                            formatDate(diag.created_at) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal para Finalizar Atendimento -->
        <Modal v-model="showFinalizarModal" title="Finalizar Atendimento" name-button="Sim, finalizar" size="md"
            @save="finalizarAtendimento">
            <div class="text-center p-3">
                <i class="ri-error-warning-line display-5 text-warning mb-3 d-inline-block"></i>
                <h4 class="mb-3">Atenção!</h4>
                <p class="text-muted mb-0">
                    Você está prestes a encerrar este atendimento. O prontuário será fechado e o paciente sairá da lista
                    de atendimentos em andamento.
                </p>
            </div>
        </Modal>

        <!-- Modal de Exclusão Padrão do Projeto -->
        <ModalDelete v-model="deleteModal" title="Excluir Registro" subTitle="Deseja realmente excluir este registro?"
            :item-delete="itemToDelete" @save="confirmDelete" />

    </Layout>
</template>
