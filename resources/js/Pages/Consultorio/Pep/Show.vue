<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { useChoicesRemoteSearch } from "@/Composables/useChoicesRemoteSearch";
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import Swal from 'sweetalert2';
import Choices from 'choices.js';

const props = defineProps({
    atendimento: Object,
    paciente: Object,
    pep: Object,
    historico: Array,
    tratamentos: Array,
    auth_profissional_id: Number,
    has_atendimento_em_andamento: Boolean,
});

// Utilities
const isMedicoResponsavel = computed(() => {
    const page = usePage();
    if (page.props.auth?.user?.id === 1) return true;
    return props.pep?.profissional_id === props.auth_profissional_id || props.atendimento?.medico_id === props.auth_profissional_id;
});

const canEditPep = computed(() => {
    return isMedicoResponsavel.value && props.atendimento?.status === 'EM ATENDIMENTO';
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
    queixa_principal: props.atendimento?.status === 'EM ATENDIMENTO' ? (props.pep?.anamnese?.queixa_principal || '') : '',
    historia_doenca_atual: props.atendimento?.status === 'EM ATENDIMENTO' ? (props.pep?.anamnese?.historia_doenca_atual || '') : '',
    antecedentes_pessoais: props.atendimento?.status === 'EM ATENDIMENTO' ? (props.pep?.anamnese?.antecedentes_pessoais || '') : '',
    alergias: props.atendimento?.status === 'EM ATENDIMENTO' ? (props.pep?.anamnese?.alergias || '') : '',
    medicamentos_uso: props.atendimento?.status === 'EM ATENDIMENTO' ? (props.pep?.anamnese?.medicamentos_uso || '') : '',
});

const sinaisVitaisAtuais = (props.atendimento?.status === 'EM ATENDIMENTO' && props.pep?.sinais_vitais && props.pep.sinais_vitais.length > 0) ? props.pep.sinais_vitais[0] : {};

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
    descricao: '',
    tratamento_id: (() => {
        if (props.tratamentos && props.atendimento) {
            const matching = props.tratamentos.filter(t => t.status === 'Em andamento' && (t.nome_tratamento || '').trim().toLowerCase() === (props.atendimento?.procedimento?.nome || props.atendimento?.tuss?.descricao || '').trim().toLowerCase());
            if (matching.length === 1) return matching[0].id;
        }
        return null;
    })()
});

const evolucaoTipoSelect = ref(null);
const evolucaoTratamentoSelect = ref(null);
let evolucaoChoicesInited = false;

function initEvolucaoChoices() {
    if (evolucaoChoicesInited) return;
    evolucaoChoicesInited = true;
    nextTick(() => {
        [evolucaoTipoSelect.value, evolucaoTratamentoSelect.value].forEach(el => {
            if (!el || el._choicesInstance) return;
            try {
                if (typeof window.destroyChoiceEl === 'function') window.destroyChoiceEl(el);
            } catch (_) {}
            try {
                const inst = new Choices(el, {
                    searchEnabled: false,
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: 'Selecione',
                    noResultsText: 'Nenhum resultado encontrado',
                });
                el._choicesInstance = inst;
                if (!canEditPep.value) inst.disable();
                else inst.enable();
                el.dataset.choicesInitialized = 'true';
                try { el.style.display = 'none'; } catch (_) {}
            } catch (e) {
                console.error('Choices evolucao init error:', e);
            }
        });
        // Sync initial values
        syncEvolucaoChoiceValue(evolucaoTipoSelect.value, evolucaoForm.tipo);
        syncEvolucaoChoiceValue(evolucaoTratamentoSelect.value, evolucaoForm.tratamento_id);
    });
}

function syncEvolucaoChoiceValue(el, value) {
    if (!el) return;
    const inst = el._choicesInstance;
    if (!inst) return;
    try {
        inst.setChoiceByValue(value != null ? String(value) : '');
    } catch (_) {}
}

watch(() => evolucaoForm.tipo, (val) => syncEvolucaoChoiceValue(evolucaoTipoSelect.value, val));
watch(() => evolucaoForm.tratamento_id, (val) => syncEvolucaoChoiceValue(evolucaoTratamentoSelect.value, val));

watch(() => canEditPep.value, (val) => {
    [evolucaoTipoSelect.value, evolucaoTratamentoSelect.value].forEach(el => {
        if (el && el._choicesInstance) {
            if (val) el._choicesInstance.enable();
            else el._choicesInstance.disable();
        }
    });
});

onMounted(() => {
    const tabEl = document.getElementById('v-pills-evolucao-tab');
    if (tabEl) {
        tabEl.addEventListener('shown.bs.tab', () => initEvolucaoChoices());
    }
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



const openDeleteTratamento = (tratamento) => {
    itemToDelete.value = { ...tratamento, nome: 'Plano de Tratamento' };
    deleteType.value = 'tratamento';
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
    } else if (deleteType.value === 'tratamento') {
        useForm({}).delete(route('atendimentos.pep.tratamento.delete', {
            atendimento: props.atendimento.id,
            tratamento: itemToDelete.value.id
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

const selectedTimelinePepId = ref(null);

const evolucoesPorAtendimento = computed(() => {
    const list = [];
    const currentPepId = props.pep?.id;
    const currentPepEvolucoes = props.pep?.evolucoes || [];

    const rawDateStr = formatDate(props.pep?.aberto_em || props.atendimento?.created_at);
    const cleanDateStr = rawDateStr.replace(',', '');
    const parts = cleanDateStr.split(' ');

    list.push({
        id: currentPepId || 'current',
        atendimento_id: props.atendimento?.id,
        data: props.pep?.aberto_em || props.atendimento?.created_at || new Date().toISOString(),
        data_formatada: parts[0] || 'N/A',
        hora_formatada: parts[1] || '',
        medico: props.atendimento?.medico?.nome || 'Profissional',
        procedimento: (() => {
            let nome = props.atendimento?.procedimento?.nome || props.atendimento?.tuss?.descricao || 'Consulta';
            const ag = props.atendimento?.agendamento;
            const st = ag?.sessao_tratamento || ag?.sessaoTratamento;
            if (st && st.numero_sessao) {
                const qtd = props.atendimento?.procedimento?.quantidade_sessoes;
                nome += qtd ? ` (Sessão ${st.numero_sessao}/${qtd})` : ` (Sessão ${st.numero_sessao})`;
            }
            return nome;
        })(),
        is_atual: true,
        evolucoes: currentPepEvolucoes
    });

    // Histórico de atendimentos anteriores (sem duplicar o atendimento atual)
    if (props.historico && props.historico.length > 0) {
        props.historico.forEach(h => {
            if (h.id !== currentPepId && h.atendimento_id !== props.atendimento?.id) {
                const hRawDateStr = formatDate(h.aberto_em || h.created_at);
                const hCleanDateStr = hRawDateStr.replace(',', '');
                const hParts = hCleanDateStr.split(' ');

                list.push({
                    id: h.id,
                    atendimento_id: h.atendimento_id,
                    data: h.aberto_em || h.created_at,
                    data_formatada: hParts[0] || 'N/A',
                    hora_formatada: hParts[1] || '',
                    medico: h.atendimento?.medico?.nome || h.profissional?.nome || 'Profissional',
                    procedimento: (() => {
                        let nome = h.atendimento?.procedimento?.nome || h.atendimento?.tuss?.descricao || 'Consulta';
                        const ag = h.atendimento?.agendamento;
                        const st = ag?.sessao_tratamento || ag?.sessaoTratamento;
                        if (st && st.numero_sessao) {
                            const qtd = h.atendimento?.procedimento?.quantidade_sessoes;
                            nome += qtd ? ` (Sessão ${st.numero_sessao}/${qtd})` : ` (Sessão ${st.numero_sessao})`;
                        }
                        return nome;
                    })(),
                    is_atual: false,
                    evolucoes: h.evolucoes || []
                });
            }
        });
    }

    return list.sort((a, b) => new Date(b.data) - new Date(a.data));
});

// Ponto selecionado padrão (primeiro da lista)
watch(evolucoesPorAtendimento, (newList) => {
    if (newList.length > 0 && !selectedTimelinePepId.value) {
        selectedTimelinePepId.value = newList[0].id;
    }
}, { immediate: true });

const selectedPepGroup = computed(() => {
    if (!selectedTimelinePepId.value && evolucoesPorAtendimento.value.length > 0) {
        return evolucoesPorAtendimento.value[0];
    }
    return evolucoesPorAtendimento.value.find(item => item.id === selectedTimelinePepId.value) || evolucoesPorAtendimento.value[0];
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

const cidSelect = ref(null);
let cidTimeout = null;

onMounted(() => {
    setTimeout(() => {
        const el = cidSelect.value;
        if (!el) return;
        
        if (window.initChoiceEl) {
            window.initChoiceEl(el);
        }
        
        const inst = el._choicesInstance || el.choices;
        if (!inst) return;

        el.addEventListener('search', (e) => {
            const query = e.detail.value;
            if (query && query.length >= 2) {
                clearTimeout(cidTimeout);
                cidTimeout = setTimeout(() => {
                    inst.setChoices(async () => {
                        return await fetchCids(query);
                    }, 'value', 'label', true);
                }, 300);
            }
        });
        
        el.addEventListener('change', (e) => {
            diagnosticoForm.cid_id = e.target.value;
        });
    }, 200);
});

const fetchCids = async (query) => {
    if (!query || query.length < 2) return [];
    try {
        const resp = await window.axios.get(route('cids.search'), { params: { q: query } });
        return Array.isArray(resp?.data) ? resp.data : [];
    } catch (e) {
        console.error(e);
        return [];
    }
};

const saveDiagnostico = () => {
    diagnosticoForm.post(route('atendimentos.pep.diagnostico.save', props.atendimento.id), {
        preserveScroll: true,
        onSuccess: () => {
            diagnosticoForm.reset();
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
import ModalConfirm from "@/Components/ModalConfirm.vue";

const calcularIdade = (dataNascimento) => {
    if (!dataNascimento) return 'N/A';
    const nascimento = new Date(dataNascimento);
    const hoje = new Date();
    let idade = hoje.getFullYear() - nascimento.getFullYear();
    const mes = hoje.getMonth() - nascimento.getMonth();
    if (mes < 0 || (mes === 0 && hoje.getDate() < nascimento.getDate())) {
        idade--;
    }
    return `${idade} anos`;
};

// Modals
const showAnamneseModal = ref(false);
const showFinalizarModal = ref(false);

const openFinalizarModal = () => {
    showFinalizarModal.value = true;
};

const finalizarAtendimento = () => {
    useForm({}).post(route('atendimentos.finalizar', props.atendimento.id), {
        onSuccess: () => {
            showFinalizarModal.value = false;
            anamneseForm.reset();
            sinaisForm.reset();
            evolucaoForm.reset();
            prescricaoForm.reset();
            diagnosticoForm.reset();
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
                                        <p class="text-muted mb-0">CPF: {{ paciente?.cpf || 'N/A' }} | Idade: {{
                                            calcularIdade(paciente?.data_nascimento) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-auto">
                                <div class="d-flex gap-2">
                                    <template v-if="atendimento.status === 'AGUARDANDO' || atendimento.status === 'CHAMADO'">
                                        <Link v-if="isMedicoResponsavel && !has_atendimento_em_andamento" :href="route('atendimentos.chamar', atendimento.id)" method="post" as="button" class="btn btn-soft-info shadow-sm" preserve-scroll title="Chamar no Painel">
                                            <i class="ri-volume-up-line align-bottom me-1"></i> Chamar
                                        </Link>
                                        <button v-else-if="isMedicoResponsavel && has_atendimento_em_andamento" class="btn btn-soft-info shadow-sm" disabled title="Você já possui um paciente em atendimento.">
                                            <i class="ri-volume-up-line align-bottom me-1"></i> Chamar
                                        </button>

                                        <Link v-if="isMedicoResponsavel && !has_atendimento_em_andamento" :href="route('atendimentos.iniciar', atendimento.id)" method="post" as="button" class="btn btn-success shadow-sm" preserve-scroll title="Iniciar Atendimento">
                                            <i class="ri-play-fill align-bottom me-1"></i> Iniciar
                                        </Link>
                                        <button v-else-if="isMedicoResponsavel && has_atendimento_em_andamento" class="btn btn-success shadow-sm" disabled title="Você já possui um paciente em atendimento.">
                                            <i class="ri-play-fill align-bottom me-1"></i> Iniciar
                                        </button>

                                        <Link v-if="isMedicoResponsavel && atendimento.status === 'CHAMADO' && !has_atendimento_em_andamento" :href="route('atendimentos.ausente', atendimento.id)" method="post" as="button" class="btn btn-soft-danger shadow-sm" preserve-scroll title="Paciente Não Compareceu">
                                            <i class="ri-user-unfollow-line align-bottom me-1"></i> Ausente
                                        </Link>
                                    </template>
                                    <template v-else-if="atendimento.status === 'EM ATENDIMENTO'">
                                        <button v-if="isMedicoResponsavel && pep?.status !== 'Encerrado'" @click="openFinalizarModal" class="btn btn-success shadow-sm">
                                            <i class="ri-check-double-line align-bottom me-1"></i> Finalizar Atendimento
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="atendimento?.status !== 'EM ATENDIMENTO'" class="alert alert-warning border-0 d-flex align-items-center shadow-sm mb-3" role="alert">
            <i class="ri-alert-line fs-18 me-2"></i>
            <div>
                <strong>Atenção:</strong> É necessário <strong>Iniciar o Atendimento</strong> para poder inserir ou alterar informações no prontuário.
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header border-0 pb-0">
                        <ul class="nav nav-tabs nav-tabs-custom nav-success nav-justified mb-0" id="v-pills-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="v-pills-resumo-tab" data-bs-toggle="tab"
                                    href="#v-pills-resumo" role="tab" aria-selected="true">
                                    <i class="ri-history-line d-inline-block text-center me-1"></i> Histórico / Resumo
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="v-pills-tratamentos-tab" data-bs-toggle="tab"
                                    href="#v-pills-tratamentos" role="tab" aria-selected="false">
                                    <i class="ri-survey-line d-inline-block text-center me-1"></i> Planos de Tratamento
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="v-pills-triagem-tab" data-bs-toggle="tab"
                                    href="#v-pills-triagem" role="tab" aria-selected="false">
                                    <i class="ri-heart-pulse-line d-inline-block text-center me-1"></i> Triagem
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="v-pills-anamnese-tab" data-bs-toggle="tab"
                                    href="#v-pills-anamnese" role="tab" aria-selected="false">
                                    <i class="ri-file-list-3-line d-inline-block text-center me-1"></i> Anamnese
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="v-pills-evolucao-tab" data-bs-toggle="tab"
                                    href="#v-pills-evolucao" role="tab" aria-selected="false">
                                    <i class="ri-pulse-line d-inline-block text-center me-1"></i> Evolução
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="v-pills-diagnosticos-tab" data-bs-toggle="tab"
                                    href="#v-pills-diagnosticos" role="tab" aria-selected="false">
                                    <i class="ri-stethoscope-line d-inline-block text-center me-1"></i> Diagnósticos
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" id="v-pills-prescricao-tab" data-bs-toggle="tab"
                                    href="#v-pills-prescricao" role="tab" aria-selected="false">
                                    <i class="ri-medicine-bottle-line d-inline-block text-center me-1"></i> Prescrições
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content text-muted" id="v-pills-tabContent">

                    <!-- RESUMO E HISTÓRICO -->
                    <div class="tab-pane fade show active" id="v-pills-resumo" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-header border-0">
                                <h5 class="card-title mb-0">Resumo do Atendimento Atual</h5>
                            </div>
                            <div class="card-body border-top border-top-dashed">
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="text-muted mb-1">Procedimento:</p>
                                        <h6 class="fs-14 mb-0">{{ atendimento?.procedimento_nome || atendimento?.procedimento?.nome || atendimento?.tuss?.descricao || 'N/A' }}</h6>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="text-muted mb-1">Médico Responsável:</p>
                                        <h6 class="fs-14 mb-0">{{ atendimento?.medico?.nome || 'N/A' }}</h6>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="text-muted mb-1">Iniciado em:</p>
                                        <h6 class="fs-14 mb-0">{{ formatDate(pep?.aberto_em) }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- SUB-ABAS DO HISTÓRICO -->
                        <div class="card shadow-sm">
                            <div class="card-header border-0 pb-0">
                                <ul class="nav nav-tabs nav-tabs-custom nav-success nav-justified mb-0" id="subtabs-historico" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="subtab-evolucoes-tab" data-bs-toggle="tab"
                                            href="#subtab-timeline-evolucoes" role="tab" aria-selected="true">
                                            <i class="ri-timeline-line me-1 align-middle"></i> Linha do Tempo de Evoluções
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="subtab-historico-tab" data-bs-toggle="tab"
                                            href="#subtab-historico-completo" role="tab" aria-selected="false">
                                            <i class="ri-history-line me-1 align-middle"></i> Histórico Completo de Prontuários ({{ historico?.length || 0 }})
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body p-3">
                                <div class="tab-content">
                                    
                                    <!-- SUB-ABA 1: LINHA DO TEMPO DE EVOLUÇÕES POR ATENDIMENTO -->
                                    <div class="tab-pane fade show active" id="subtab-timeline-evolucoes" role="tabpanel">
                                        <!-- BARRA DA TIMELINE HORIZONTAL -->
                                        <div v-if="evolucoesPorAtendimento.length > 0" class="py-4 my-2 px-3 bg-light bg-opacity-75 rounded-3 border">
                                            <div class="d-flex align-items-stretch justify-content-start overflow-auto py-2 position-relative" style="scrollbar-width: thin;">
                                                <div v-for="(item, index) in evolucoesPorAtendimento" :key="item.id" 
                                                    @click="selectedTimelinePepId = item.id"
                                                    class="d-flex flex-column align-items-center cursor-pointer text-center px-3 position-relative user-select-none flex-shrink-0"
                                                    style="min-width: 150px;">

                                                    <!-- Linha Conectora perfeitamente alinhada no centro dos círculos -->
                                                    <div v-if="index > 0" class="position-absolute border-top border-2 border-primary-subtle" style="top: 57px; right: 50%; left: -50%; z-index: 1;"></div>

                                                    <!-- 1. Data (Badge Topo) -->
                                                    <div class="mb-2 position-relative" style="z-index: 2; height: 28px;">
                                                        <span class="badge shadow-xs transition-all"
                                                            :class="selectedTimelinePepId === item.id ? 'bg-primary text-white fs-12 px-3 py-1 scale-105' : 'bg-white text-dark border fs-11'">
                                                            <i class="ri-calendar-event-line me-1"></i> {{ item.data_formatada }}
                                                        </span>
                                                    </div>

                                                    <!-- 2. Ponto Círculo (Alinhado exatamente na linha) -->
                                                    <div class="position-relative d-flex align-items-center justify-content-center my-1" style="z-index: 2; height: 40px;">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm transition-all"
                                                            :style="{ width: selectedTimelinePepId === item.id ? '38px' : '30px', height: selectedTimelinePepId === item.id ? '38px' : '30px' }"
                                                            :class="selectedTimelinePepId === item.id ? 'bg-primary text-white border border-2 border-white ring-2' : 'bg-white text-muted border border-2 border-primary-subtle'">
                                                            <i class="fs-16" :class="item.procedimento.includes('Sessão') ? ('ri-hand-heart-fill ' + (selectedTimelinePepId === item.id ? '' : 'text-success')) : 'ri-pulse-fill'"></i>
                                                        </div>
                                                    </div>

                                                    <!-- 3. Informações (Médico e Quantidade) -->
                                                    <div class="mt-2 position-relative" style="z-index: 2;">
                                                        <span class="d-block fw-semibold fs-11 text-truncate mx-auto" :class="selectedTimelinePepId === item.id ? 'text-primary' : 'text-muted'" style="max-width: 130px;">
                                                            {{ item.medico }}
                                                        </span>
                                                        <span class="badge rounded-pill fs-10 mt-1" :class="item.evolucoes.length > 0 ? 'bg-success-subtle text-success' : 'bg-light text-muted border'">
                                                            {{ item.evolucoes.length }} {{ item.evolucoes.length === 1 ? 'evolução' : 'evoluções' }}
                                                        </span>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <!-- PAINEL DE DETALHES DAS EVOLUÇÕES DO PONTO SELECIONADO -->
                                        <div v-if="selectedPepGroup" class="card shadow-none border mt-3 mb-0">
                                            <div class="card-header bg-soft-primary border-0 d-flex justify-content-between align-items-center py-2 flex-wrap gap-2">
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-calendar-check-line text-primary fs-18 me-2"></i>
                                                    <div>
                                                        <h6 class="card-title mb-0 fs-13">
                                                            Atendimento de <strong>{{ selectedPepGroup.data_formatada }}</strong> 
                                                            <span v-if="selectedPepGroup.hora_formatada" class="text-muted fs-12 ms-1">({{ selectedPepGroup.hora_formatada }})</span>
                                                            <span v-if="selectedPepGroup.is_atual" class="badge bg-success-subtle text-success ms-2 fs-11">Consulta Atual</span>
                                                        </h6>
                                                    </div>
                                                </div>
                                                <span class="text-muted fs-12 fw-medium">
                                                    <i class="ri-user-md-line me-1 text-primary"></i> {{ selectedPepGroup.medico }} &bull; {{ selectedPepGroup.procedimento }}
                                                </span>
                                            </div>
                                            
                                            <div class="card-body">
                                                <!-- Lista de Evoluções deste Atendimento -->
                                                <div v-if="selectedPepGroup.evolucoes && selectedPepGroup.evolucoes.length > 0" class="vstack gap-3">
                                                    <div v-for="ev in selectedPepGroup.evolucoes" :key="ev.id" class="p-3 border rounded-3 bg-white shadow-xs">
                                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                                            <span class="badge bg-success-subtle text-success fs-12 fw-medium">
                                                                <i class="ri-pulse-line me-1"></i> {{ ev.tipo || 'Evolução Clínica' }}
                                                            </span>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="fs-12 text-muted">
                                                                    <i class="ri-time-line me-1"></i> {{ formatDate(ev.created_at) }}
                                                                </span>
                                                                <span class="fs-12 text-dark fw-medium ms-1">
                                                                    <i class="ri-user-3-line me-1 text-muted"></i> {{ ev.profissional?.nome || 'Profissional' }}
                                                                </span>
                                                                <button v-if="props.auth_profissional_id && ev.profissional_id == props.auth_profissional_id && canEditPep"
                                                                    class="btn btn-sm btn-ghost-danger ms-2"
                                                                    @click="deleteEvolucao(ev)" title="Excluir Evolução">
                                                                    <i class="ri-delete-bin-line fs-14"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <p class="text-secondary fs-13 mb-0 ps-1" style="white-space: pre-line;">{{ ev.descricao }}</p>
                                                    </div>
                                                </div>

                                                <div v-else class="text-center py-4 text-muted">
                                                    <i class="ri-file-text-line fs-24 mb-1 d-block text-muted opacity-50"></i>
                                                    Nenhuma evolução registrada neste atendimento ({{ selectedPepGroup.data_formatada }}).
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SUB-ABA 2: HISTÓRICO COMPLETO DE ATENDIMENTOS (DETALHAMENTO) -->
                                    <div class="tab-pane fade" id="subtab-historico-completo" role="tabpanel">
                                        <div v-if="!historico || historico.length === 0" class="text-center py-4">
                                            <div class="avatar-md mx-auto mb-3">
                                                <div class="avatar-title bg-light text-muted rounded-circle fs-24"><i
                                                        class="ri-history-line"></i></div>
                                            </div>
                                            <h5 class="fs-15">Nenhum histórico de atendimento anterior</h5>
                                            <p class="text-muted">Este é o primeiro atendimento registrado para este paciente.</p>
                                        </div>
                                        <div v-else class="profile-timeline">
                                            <div class="accordion accordion-flush" id="accordionHistoricoTimeline">
                                                <div v-for="(hist, index) in historico" :key="hist.id" class="accordion-item border rounded shadow-sm mb-3 overflow-hidden">
                                                    <h2 class="accordion-header" :id="'headingHist' + index">
                                                        <button class="accordion-button" :class="{ 'collapsed': index !== 0 }" type="button"
                                                            data-bs-toggle="collapse" :data-bs-target="'#collapseHist' + index"
                                                            :aria-expanded="index === 0 ? 'true' : 'false'" :aria-controls="'collapseHist' + index">
                                                            <div class="d-flex w-100 justify-content-between align-items-center me-3 flex-wrap gap-2">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="avatar-xs me-2">
                                                                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-14">
                                                                            <i class="ri-calendar-event-fill"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <span class="fw-semibold text-dark fs-14">Atendimento em {{ formatDate(hist.aberto_em) }}</span>
                                                                        <span v-if="hist.atendimento?.procedimento_nome" class="badge bg-info-subtle text-info ms-2 fs-11">
                                                                            {{ hist.atendimento?.procedimento_nome }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <span class="fs-13 text-muted">
                                                                        <i class="ri-stethoscope-line me-1 text-primary align-middle"></i>
                                                                        {{ hist.atendimento?.medico?.nome || hist.profissional?.nome || 'Profissional' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </button>
                                                    </h2>

                                                    <div :id="'collapseHist' + index" class="accordion-collapse collapse" :class="{ 'show': index === 0 }"
                                                        :aria-labelledby="'headingHist' + index" data-bs-parent="#accordionHistoricoTimeline">
                                                        <div class="accordion-body bg-light bg-opacity-25 p-3">
                                                            
                                                            <!-- 1. TRIAGEM / SINAIS VITAIS -->
                                                            <div class="card shadow-none border mb-3">
                                                                <div class="card-header bg-soft-primary border-0 py-2">
                                                                    <h6 class="card-title mb-0 fs-13 text-primary d-flex align-items-center">
                                                                        <i class="ri-heart-pulse-line me-2 fs-16"></i> 1. Triagem &amp; Sinais Vitais
                                                                    </h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div v-if="hist.sinais_vitais && hist.sinais_vitais.length > 0">
                                                                        <div class="row g-2 mb-2">
                                                                            <div class="col-md-3 col-6" v-if="hist.sinais_vitais[0].pressao_sistolica || hist.sinais_vitais[0].pressao_diastolica">
                                                                                <div class="p-2 border border-dashed rounded text-center bg-white">
                                                                                    <p class="text-muted mb-1 fs-11 text-uppercase fw-medium">Pressão (PA)</p>
                                                                                    <h6 class="fs-13 mb-0 text-dark">{{ hist.sinais_vitais[0].pressao_sistolica || '-' }} / {{ hist.sinais_vitais[0].pressao_diastolica || '-' }} mmHg</h6>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-3 col-6" v-if="hist.sinais_vitais[0].frequencia_cardiaca">
                                                                                <div class="p-2 border border-dashed rounded text-center bg-white">
                                                                                    <p class="text-muted mb-1 fs-11 text-uppercase fw-medium">Freq. Cardíaca</p>
                                                                                    <h6 class="fs-13 mb-0 text-dark">{{ hist.sinais_vitais[0].frequencia_cardiaca }} bpm</h6>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-3 col-6" v-if="hist.sinais_vitais[0].temperatura">
                                                                                <div class="p-2 border border-dashed rounded text-center bg-white">
                                                                                    <p class="text-muted mb-1 fs-11 text-uppercase fw-medium">Temperatura</p>
                                                                                    <h6 class="fs-13 mb-0 text-dark">{{ hist.sinais_vitais[0].temperatura }} °C</h6>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-3 col-6" v-if="hist.sinais_vitais[0].saturacao">
                                                                                <div class="p-2 border border-dashed rounded text-center bg-white">
                                                                                    <p class="text-muted mb-1 fs-11 text-uppercase fw-medium">Saturação O²</p>
                                                                                    <h6 class="fs-13 mb-0 text-dark">{{ hist.sinais_vitais[0].saturacao }} %</h6>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-3 col-6" v-if="hist.sinais_vitais[0].peso">
                                                                                <div class="p-2 border border-dashed rounded text-center bg-white">
                                                                                    <p class="text-muted mb-1 fs-11 text-uppercase fw-medium">Peso</p>
                                                                                    <h6 class="fs-13 mb-0 text-dark">{{ hist.sinais_vitais[0].peso }} kg</h6>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-3 col-6" v-if="hist.sinais_vitais[0].altura">
                                                                                <div class="p-2 border border-dashed rounded text-center bg-white">
                                                                                    <p class="text-muted mb-1 fs-11 text-uppercase fw-medium">Altura</p>
                                                                                    <h6 class="fs-13 mb-0 text-dark">{{ hist.sinais_vitais[0].altura }} m</h6>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-3 col-6" v-if="hist.sinais_vitais[0].imc">
                                                                                <div class="p-2 border border-dashed rounded text-center bg-white">
                                                                                    <p class="text-muted mb-1 fs-11 text-uppercase fw-medium">IMC</p>
                                                                                    <h6 class="fs-13 mb-0 text-dark">{{ hist.sinais_vitais[0].imc }}</h6>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-3 col-6" v-if="hist.sinais_vitais[0].glicemia">
                                                                                <div class="p-2 border border-dashed rounded text-center bg-white">
                                                                                    <p class="text-muted mb-1 fs-11 text-uppercase fw-medium">Glicemia</p>
                                                                                    <h6 class="fs-13 mb-0 text-dark">{{ hist.sinais_vitais[0].glicemia }} mg/dL</h6>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-muted fs-12 mb-0" v-if="hist.sinais_vitais[0].observacao">
                                                                            <strong>Obs Triagem:</strong> {{ hist.sinais_vitais[0].observacao }}
                                                                        </p>
                                                                    </div>
                                                                    <div v-else class="text-muted fs-13 fst-italic">Nenhum sinal vital/triagem registrado neste atendimento.</div>
                                                                </div>
                                                            </div>

                                                            <!-- 2. ANAMNESE -->
                                                            <div class="card shadow-none border mb-3">
                                                                <div class="card-header bg-soft-info border-0 py-2">
                                                                    <h6 class="card-title mb-0 fs-13 text-info d-flex align-items-center">
                                                                        <i class="ri-file-text-line me-2 fs-16"></i> 2. Anamnese
                                                                    </h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div v-if="hist.anamnese" class="row g-3">
                                                                        <div class="col-md-12" v-if="hist.anamnese.queixa_principal">
                                                                            <p class="text-muted mb-1 fs-12 font-bold">Queixa Principal:</p>
                                                                            <p class="fs-13 mb-0 text-dark">{{ hist.anamnese.queixa_principal }}</p>
                                                                        </div>
                                                                        <div class="col-md-12" v-if="hist.anamnese.historia_doenca_atual">
                                                                            <p class="text-muted mb-1 fs-12 font-bold">História da Doença Atual (HDA):</p>
                                                                            <p class="fs-13 mb-0 text-dark">{{ hist.anamnese.historia_doenca_atual }}</p>
                                                                        </div>
                                                                        <div class="col-md-6" v-if="hist.anamnese.antecedentes_pessoais">
                                                                            <p class="text-muted mb-1 fs-12 font-bold">Antecedentes Pessoais:</p>
                                                                            <p class="fs-13 mb-0 text-dark">{{ hist.anamnese.antecedentes_pessoais }}</p>
                                                                        </div>
                                                                        <div class="col-md-6" v-if="hist.anamnese.alergias">
                                                                            <p class="text-muted mb-1 fs-12 font-bold">Alergias:</p>
                                                                            <p class="fs-13 mb-0 text-danger fw-medium">{{ hist.anamnese.alergias }}</p>
                                                                        </div>
                                                                        <div class="col-md-12" v-if="hist.anamnese.medicamentos_uso">
                                                                            <p class="text-muted mb-1 fs-12 font-bold">Medicamentos em Uso:</p>
                                                                            <p class="fs-13 mb-0 text-dark">{{ hist.anamnese.medicamentos_uso }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div v-else class="text-muted fs-13 fst-italic">Nenhuma anamnese registrada neste atendimento.</div>
                                                                </div>
                                                            </div>

                                                            <!-- 3. EVOLUÇÕES -->
                                                            <div class="card shadow-none border mb-3">
                                                                <div class="card-header bg-soft-success border-0 py-2">
                                                                    <h6 class="card-title mb-0 fs-13 text-success d-flex align-items-center">
                                                                        <i class="ri-pulse-line me-2 fs-16"></i> 3. Evoluções Clínica ({{ hist.evolucoes?.length || 0 }})
                                                                    </h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div v-if="hist.evolucoes && hist.evolucoes.length > 0" class="vstack gap-2">
                                                                        <div v-for="ev in hist.evolucoes" :key="ev.id" class="p-3 border rounded bg-white">
                                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                                <span class="badge bg-success-subtle text-success fs-11">{{ ev.tipo || 'Evolução' }}</span>
                                                                                <span class="fs-12 text-muted">{{ formatDate(ev.created_at) }}</span>
                                                                            </div>
                                                                            <p class="text-muted mb-0 fs-13">{{ ev.descricao }}</p>
                                                                            <small class="text-muted d-block mt-1">Por: {{ ev.profissional?.nome || 'Profissional' }}</small>
                                                                        </div>
                                                                    </div>
                                                                    <div v-else class="text-muted fs-13 fst-italic">Nenhuma evolução registrada neste atendimento.</div>
                                                                </div>
                                                            </div>

                                                            <!-- 4. DIAGNÓSTICOS -->
                                                            <div class="card shadow-none border mb-3">
                                                                <div class="card-header bg-soft-warning border-0 py-2">
                                                                    <h6 class="card-title mb-0 fs-13 text-warning d-flex align-items-center">
                                                                        <i class="ri-stethoscope-line me-2 fs-16"></i> 4. Diagnósticos / CID ({{ hist.diagnosticos?.length || 0 }})
                                                                    </h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div v-if="hist.diagnosticos && hist.diagnosticos.length > 0" class="vstack gap-2">
                                                                        <div v-for="diag in hist.diagnosticos" :key="diag.id" class="p-2 px-3 border rounded bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                                            <div>
                                                                                <span class="badge bg-warning-subtle text-warning me-2" v-if="diag.cid?.codigo">{{ diag.cid.codigo }}</span>
                                                                                <span class="fw-medium fs-13 text-dark">{{ diag.cid?.descricao || 'Diagnóstico sem CID' }}</span>
                                                                                <p class="text-muted fs-12 mb-0" v-if="diag.descricao">{{ diag.descricao }}</p>
                                                                            </div>
                                                                            <div>
                                                                                <span class="badge bg-danger-subtle text-danger me-1" v-if="diag.principal">Principal</span>
                                                                                <span class="badge bg-info-subtle text-info" v-if="diag.confirmado">Confirmado</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div v-else class="text-muted fs-13 fst-italic">Nenhum diagnóstico registrado neste atendimento.</div>
                                                                </div>
                                                            </div>

                                                            <!-- 5. PRESCRIÇÕES -->
                                                            <div class="card shadow-none border mb-0">
                                                                <div class="card-header bg-soft-danger border-0 py-2">
                                                                    <h6 class="card-title mb-0 fs-13 text-danger d-flex align-items-center">
                                                                        <i class="ri-capsule-line me-2 fs-16"></i> 5. Prescrições &amp; Medicamentos ({{ hist.prescricoes?.length || 0 }})
                                                                    </h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div v-if="hist.prescricoes && hist.prescricoes.length > 0" class="vstack gap-2">
                                                                        <div v-for="pres in hist.prescricoes" :key="pres.id" class="p-2 border border-dashed rounded bg-white">
                                                                            <div v-if="pres.observacao" class="text-muted fs-12 mb-1">
                                                                                <strong>Obs:</strong> {{ pres.observacao }}
                                                                            </div>
                                                                            <ul class="list-unstyled mb-0 ps-1 vstack gap-1" v-if="pres.itens && pres.itens.length > 0">
                                                                                <li v-for="item in pres.itens" :key="item.id" class="fs-13 text-dark">
                                                                                    <i class="ri-checkbox-blank-circle-fill fs-6 text-danger me-2 align-middle"></i>
                                                                                    <strong>{{ item.observacao || 'Medicamento' }}</strong>
                                                                                    <span v-if="item.dosagem"> - {{ item.dosagem }}</span>
                                                                                    <span v-if="item.via"> Via {{ item.via }}</span>
                                                                                    <span v-if="item.frequencia"> de {{ item.frequencia }}</span>
                                                                                    <span v-if="item.quantidade"> (Qtd: {{ item.quantidade }})</span>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                    <div v-else class="text-muted fs-13 fst-italic">Nenhuma prescrição registrada neste atendimento.</div>
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
                    </div> <!-- /v-pills-resumo -->
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
                                    v-if="!isMedicoResponsavel && atendimento?.medico">
                                    <i class="ri-information-line me-3 align-middle fs-16 text-info"></i>
                                    Esta anamnese foi iniciada pelo(a) <strong> {{ atendimento.medico.nome
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
                                    <fieldset :disabled="!canEditPep">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Tipo de Nota</label>
                                                <select ref="evolucaoTipoSelect" class="form-select" v-model="evolucaoForm.tipo" data-choices data-choices-search-false :disabled="!canEditPep" @change="evolucaoForm.tipo = $event.target.value">
                                                    <option value="" disabled>Selecione o tipo</option>
                                                    <option value="Evolução Clínica">Evolução Clínica</option>
                                                    <option value="Nota de Enfermagem">Nota de Enfermagem</option>
                                                    <option value="Parecer">Parecer</option>
                                                </select>
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label">Vincular a um Tratamento Ativo (Opcional)</label>
                                                <select ref="evolucaoTratamentoSelect" class="form-select" v-model="evolucaoForm.tratamento_id" data-choices data-choices-search-false :disabled="!canEditPep" @change="evolucaoForm.tratamento_id = $event.target.value || null">
                                                    <option :value="null">Nenhum (Evolução Avulsa)</option>
                                                    <option v-for="trat in props.tratamentos?.filter(t => t.status === 'Em andamento' && (t.nome_tratamento || '').trim().toLowerCase() === (props.atendimento?.procedimento?.nome || props.atendimento?.tuss?.descricao || '').trim().toLowerCase())" :key="trat.id" :value="trat.id">
                                                        {{ trat.nome_tratamento }} (Sessão {{ trat.quantidade_sessoes_realizadas + 1 }} de {{ trat.quantidade_sessoes_previstas }})
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">Descrição</label>
                                                <textarea class="form-control" v-model="evolucaoForm.descricao" rows="3"
                                                    placeholder="Descreva a evolução do paciente..." required></textarea>
                                            </div>
                                            <div class="col-12 text-end">
                                                <button type="submit" class="btn btn-primary shadow-sm"
                                                    :disabled="!canEditPep || evolucaoForm.processing">
                                                    <span v-if="evolucaoForm.processing"
                                                        class="spinner-border spinner-border-sm me-1" role="status"
                                                        aria-hidden="true"></span>
                                                    <i class="ri-add-line align-bottom me-1" v-else></i> Adicionar Evolução
                                                </button>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                        <!-- Lista de Evoluções deste Atendimento -->
                        <div class="card shadow-sm mt-3">
                            <div class="card-header border-0 bg-soft-light">
                                <h5 class="card-title mb-0">
                                    <i class="ri-list-check me-2 text-primary"></i> Evoluções do Atendimento Atual
                                </h5>
                            </div>
                            <div class="card-body">
                                <div v-if="pep?.evolucoes && pep.evolucoes.length > 0" class="vstack gap-3">
                                    <div v-for="ev in pep.evolucoes" :key="ev.id" class="p-3 border rounded-3 bg-white shadow-xs">
                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                            <span class="badge bg-success-subtle text-success fs-12 fw-medium">
                                                <i class="ri-pulse-line me-1"></i> {{ ev.tipo || 'Evolução Clínica' }}
                                            </span>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fs-12 text-muted">
                                                    <i class="ri-time-line me-1"></i> {{ formatDate(ev.created_at) }}
                                                </span>
                                                <span class="fs-12 text-dark fw-medium ms-1">
                                                    <i class="ri-user-3-line me-1 text-muted"></i> {{ ev.profissional?.nome || 'Profissional' }}
                                                </span>
                                                <button v-if="props.auth_profissional_id && ev.profissional_id == props.auth_profissional_id && canEditPep"
                                                    class="btn btn-sm btn-ghost-danger ms-2"
                                                    @click="deleteEvolucao(ev)" title="Excluir Evolução">
                                                    <i class="ri-delete-bin-line fs-14"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <p class="text-secondary fs-13 mb-0 ps-1" style="white-space: pre-line;">{{ ev.descricao }}</p>
                                    </div>
                                </div>
                                <div v-else class="text-center py-4 text-muted">
                                    <i class="ri-file-text-line fs-24 mb-1 d-block text-muted opacity-50"></i>
                                    Nenhuma evolução registrada neste atendimento.
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                    </div> <!-- /v-pills-evolucao -->
                    <!-- PLANOS DE TRATAMENTO -->
                    <div class="tab-pane fade" id="v-pills-tratamentos" role="tabpanel">


                        <!-- Lista de Tratamentos -->
                        <div class="card shadow-sm mt-3">
                            <div class="card-header border-0 bg-soft-light">
                                <h5 class="card-title mb-0">
                                    <i class="ri-survey-line me-2 text-primary"></i> Planos de Tratamento
                                </h5>
                            </div>
                            <div class="card-body">
                                <div v-if="props.tratamentos && props.tratamentos.length > 0" class="vstack gap-3">
                                    <div v-for="trat in props.tratamentos" :key="trat.id" class="p-3 border rounded-3 bg-white shadow-xs">
                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                            <div>
                                                <span class="fs-15 fw-bold text-dark">{{ trat.nome_tratamento }}</span>
                                                <span class="badge ms-2" :class="trat.status === 'Concluído' ? 'bg-success' : 'bg-primary'">{{ trat.status }}</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fs-12 text-muted"><i class="ri-calendar-event-line me-1"></i> Iniciado em {{ formatDate(trat.data_inicio) }}</span>
                                                <button v-if="props.auth_profissional_id && trat.profissional_id == props.auth_profissional_id && canEditPep"
                                                    class="btn btn-sm btn-ghost-danger ms-2" @click="openDeleteTratamento(trat)" title="Excluir Tratamento">
                                                    <i class="ri-delete-bin-line fs-14"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <p class="text-secondary fs-13 mb-2" v-if="trat.observacao">{{ trat.observacao }}</p>
                                        
                                        <!-- Barra de Progresso -->
                                        <div class="mt-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="fs-12 fw-medium text-muted">Progresso das Sessões</span>
                                                <span class="fs-12 fw-medium text-primary">{{ trat.quantidade_sessoes_realizadas }} de {{ trat.quantidade_sessoes_previstas }} Sessões</span>
                                            </div>
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-primary" role="progressbar" :style="{ width: ((trat.quantidade_sessoes_realizadas / trat.quantidade_sessoes_previstas) * 100) + '%' }" :aria-valuenow="trat.quantidade_sessoes_realizadas" aria-valuemin="0" :aria-valuemax="trat.quantidade_sessoes_previstas"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-4 text-muted">
                                    <i class="ri-survey-line fs-24 mb-1 d-block text-muted opacity-50"></i>
                                    Nenhum plano de tratamento registrado.
                                </div>
                            </div>
                        </div>
                    </div> <!-- /v-pills-tratamentos -->

                    <!-- PRESCRIÇÃO -->
                    <div class="tab-pane fade" id="v-pills-prescricao" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-header border-0 bg-soft-light">
                                <h5 class="card-title mb-0">Nova Prescrição</h5>
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="savePrescricao">
                                    <fieldset :disabled="!canEditPep">
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
                                                        @click="addPrescricaoItem" :disabled="!canEditPep"><i class="ri-add-line align-bottom"></i>
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
                                                                        :disabled="!canEditPep || prescricaoForm.itens.length === 1">
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
                                                    :disabled="!canEditPep || prescricaoForm.processing || prescricaoForm.itens.length === 0">
                                                    <span v-if="prescricaoForm.processing"
                                                        class="spinner-border spinner-border-sm me-1" role="status"
                                                        aria-hidden="true"></span>
                                                    <i class="ri-save-line align-bottom me-1" v-else></i> Salvar Prescrição
                                                </button>
                                            </div>
                                        </div>
                                    </fieldset>
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

                    </div> <!-- /v-pills-prescricao -->
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
                                            <select ref="cidSelect" class="form-select" data-choices data-choices-search-true v-model="diagnosticoForm.cid_id" :disabled="!canEditPep">
                                                <option value="">Digite o código ou nome da doença para buscar...</option>
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
                    </div> <!-- /v-pills-diagnosticos -->
                        </div> <!-- /tab-content -->
                    </div> <!-- /card-body -->
                </div> <!-- /card -->
            </div> <!-- /col-lg-12 -->
        </div> <!-- /row -->
        </div>
        <!-- Modal para Finalizar Atendimento -->
        <ModalConfirm v-model="showFinalizarModal" title="Finalizar Atendimento" subTitle="Atenção!"
            message="Você está prestes a encerrar este atendimento. O prontuário será fechado e o paciente sairá da lista de atendimentos em andamento."
            name-button="Sim, finalizar" buttonClass="btn-warning"
            @save="finalizarAtendimento" />

        <!-- Modal de Exclusão Padrão do Projeto -->
        <ModalDelete v-model="deleteModal" title="Excluir Registro" subTitle="Deseja realmente excluir este registro?"
            :item-delete="itemToDelete" @save="confirmDelete" />

    </Layout>
</template>
