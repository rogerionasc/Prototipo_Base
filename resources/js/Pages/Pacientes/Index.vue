<template>
    <Layout>

        <Head title="Pacientes" />
        <PageHeader title="Pacientes" pageTitle="Menu" />
        <TableGrid :columns="columns" :data="pacientesLocal" :tableTitle="'Todos os Pacientes'" :showStatus="false"
            :searchPlaceholder="'Buscar por paciente'" @modalDdeletarMultiplos="openModalDeleteMulti"
            @delete="openModalDelete" @edit="openModalEdit" @show="openModalShow" @add="openModalAdd" />
        <Modal v-model="showModal" :title="modalTitle" size="xl" :name-button="saveButtonText"
            :processing="saveProcessing" @save="onSavePaciente">
            <PacienteForm ref="pacienteFormRef" :key="formKey" :estados-civis="props.estadosCivis"
                :tipos-sanguineos="props.tiposSanguineos" :canais-aviso="props.canaisAviso" :convenios="props.convenios"
                :parentescos="props.parentescos" />
        </Modal>
        <ModalDelete v-model="deleteModal" :title="'Excluir Paciente'" :subTitle="deleteSubTitle"
            :item-delete="pacienteToDelete" @save="confirmDelete" />
        <ModalDelete v-model="bulkDeleteModal" :title="'Excluir Pacientes'" :subTitle="bulkDeleteSubTitle"
            :item-delete="bulkDeleteSummary" @save="confirmBulkDelete" />
        <Modal v-model="showViewModal" :title="'Paciente'" size="xl" :name-button="'Fechar'" :processing="false"
            @save="fecharViewModal">
            <BTabs nav-class="nav-tabs-custom text-muted">
                <BTab title="Informações">
                    <div class="row g-3 mt-2" v-if="selectedPaciente">
                        <h6 class="fs-14 mb-0 text-primary">Dados Pessoais</h6>
                        <hr class="mt-2 mb-0 border-primary" style="opacity: 0.1;" />
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted mb-1">Nome</label>
                            <div class="fw-medium fs-14">{{ selectedPaciente.nome }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1">CPF</label>
                            <div class="fw-medium fs-14">{{ selectedPaciente.cpf }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1">Nascimento</label>
                            <div class="fw-medium fs-14">{{ formatDateTimeBR(selectedPaciente.data_nascimento) || '-' }}</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted mb-1">Convênio</label>
                            <div class="fw-medium fs-14">{{ selectedPaciente.convenio || 'Particular' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted mb-1">Sexo</label>
                            <div class="fw-medium fs-14">{{ selectedPaciente.sexo || '-' }}</div>
                        </div>

                        <h6 class="fs-14 mb-0 mt-4 text-primary">Contato e Endereço</h6>
                        <hr class="mt-2 mb-0 border-primary" style="opacity: 0.1;" />
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted mb-1">Celular</label>
                            <div class="fw-medium fs-14">{{ selectedPaciente.celular || '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted mb-1">Email</label>
                            <div class="fw-medium fs-14">{{ selectedPaciente.email || '-' }}</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-muted mb-1">Endereço</label>
                            <div class="fw-medium fs-14">
                                {{ [selectedPaciente.endereco, selectedPaciente.numero, selectedPaciente.bairro,
                                selectedPaciente.cidade].filter(Boolean).join(', ') || '-' }}
                            </div>
                        </div>
                        <div class="col-md-12" v-if="selectedPaciente.observacoes">
                            <label class="form-label text-muted mb-1">Observações</label>
                            <div class="fw-medium fs-14 text-muted">{{ selectedPaciente.observacoes }}</div>
                        </div>
                    </div>
                </BTab>
                <BTab title="Orçamentos">
                    <div class="mt-2">
                        <div v-if="(orcamentosPaciente || []).length === 0" class="text-muted">Nenhum orçamento
                            encontrado</div>
                        <SimpleTable v-else variant="borderless" compact tableClass="table-sm align-middle"
                            :items="orcamentosPaciente" :columns="orcamentosColumns">
                            <template #body="{ items, columns }">
                                <tr v-for="o in items" :key="o.id">
                                    <td>{{ o.numero }}</td>
                                    <td>{{ formatDateTimeBR(o.data_emissao) }}</td>
                                    <td>{{ formatDateTimeBR(o.validade) }}</td>
                                    <td class="text-end">{{ formatCurrencyBR(o.valor_bruto) }}</td>
                                    <td class="text-end">{{ formatCurrencyBR(o.desconto) }}</td>
                                    <td class="text-end">{{ formatCurrencyBR(o.valor_total) }}</td>
                                </tr>
                            </template>
                        </SimpleTable>
                    </div>
                </BTab>
                <BTab title="Agendamentos">
                    <div class="mt-2">
                        <SimpleTable variant="borderless" compact tableClass="table-sm align-middle"
                            :items="agendamentosPaciente"
                            :columns="[{ key: 'data', label: 'Data' }, { key: 'hora', label: 'Hora' }, { key: 'procedimento', label: 'Procedimento' }, { key: 'profissional', label: 'Profissional' }, { key: 'status', label: 'Status' }]"
                            has-actions
                            :searchable="true"
                            searchPlaceholder="Buscar agendamento..."
                            :searchFields="['procedimento', 'profissional', 'status']"
                            emptyTitle="Nenhum agendamento encontrado">

                            <template #cell(data)="{ item }">
                                {{ item.data ? formatDateTimeBR(item.data).split(' ')[0] : 'A Agendar' }}
                            </template>
                            
                            <template #cell(hora)="{ item }">
                                {{ item.hora || '--:--' }}
                            </template>

                            <template #actions="{ item }">
                                <button v-if="!item.atendido" type="button" class="btn btn-sm btn-light" title="Reagendar"
                                    @click="abrirModalReagendar(item)">
                                    <i class="ri-edit-line me-1"></i> Reagendar
                                </button>
                                <span v-else class="text-muted small">—</span>
                            </template>
                        </SimpleTable>
                    </div>
                </BTab>
            </BTabs>
        </Modal>

        <!-- Modal de Reagendamento Rápido -->
        <Modal v-model="reagendarModal" title="Reagendar Agendamento" size="xl" name-button="Confirmar"
            :processing="reagendarProcessing" @save="confirmarReagendamento">
            <div class="mb-4 p-3 bg-light rounded border" v-if="reagendarAgendamentoData">
                <h6 class="mb-2 text-primary"><i class="ri-calendar-event-line me-1"></i> Agendamento Atual</h6>
                <div class="row text-muted small">
                    <div class="col-sm-6">
                        <strong>Data:</strong> {{ formatDateTimeBR(reagendarAgendamentoData.data).split(' ')[0] }} às {{
                        reagendarAgendamentoData.hora }}<br>
                        <strong>Médico:</strong> {{ reagendarAgendamentoData.profissional || 'Não definido' }}
                    </div>
                    <div class="col-sm-6">
                        <strong>Procedimento:</strong> {{ reagendarAgendamentoData.procedimento || 'Não definido' }}<br>
                        <strong>Status:</strong> {{ reagendarAgendamentoData.status }}
                    </div>
                </div>
            </div>

            <h6 class="mb-3 border-bottom pb-2">Detalhes do Reagendamento</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-medium">Nova Data</label>
                    <flatPickr v-model="reagendarForm.data" class="form-control" :config="opcoesFlatpickrData"
                        placeholder="Selecione a data" />
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Nova Hora</label>
                    <flatPickr v-model="reagendarForm.hora" class="form-control" :config="opcoesFlatpickrHora"
                        placeholder="Selecione a hora" />
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Médico / Profissional</label>
                    <select v-model="reagendarForm.pessoa_id" data-choices class="form-select"
                        ref="reagendarProfissionalSelect">
                        <option :value="null">Selecione um médico</option>
                        <option v-for="prof in reagendarProfissionais" :key="prof.id" :value="prof.id">
                            {{ prof.nome }}
                        </option>
                    </select>
                </div>
            </div>
        </Modal>
    </Layout>
</template>
<script setup>
import "gridjs/dist/theme/mermaid.css";
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head, useForm } from '@inertiajs/vue3';
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Modal from "@/Components/Modal.vue";
import ModalDelete from "@/Components/ModalDelete.vue";
import PacienteForm from "@/Pages/Pacientes/Create.vue";
import SimpleTable from "@/Components/Tables/SimpleTable.vue";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import "flatpickr/dist/l10n/pt.js";
import { ref, nextTick, watch, computed, watchEffect } from "vue";

const opcoesFlatpickrData = {
    altInput: true,
    altFormat: "d M, Y",
    dateFormat: "Y-m-d",
    locale: "pt",
    minDate: "today",
};
const opcoesFlatpickrHora = {
    enableTime: true,
    noCalendar: true,
    altInput: true,
    altFormat: "H:i",
    dateFormat: "H:i",
    time_24hr: true,
    locale: "pt",
};

const orcamentosColumns = [
    { key: 'numero', label: 'Número' },
    { key: 'emissao', label: 'Emissão' },
    { key: 'validade', label: 'Validade' },
    { key: 'bruto', label: 'Bruto', thClass: 'text-end' },
    { key: 'desconto', label: 'Desconto', thClass: 'text-end' },
    { key: 'total', label: 'Total', thClass: 'text-end' }
];

const props = defineProps({
    pacientes: { type: Array, default: () => [] },
    estadosCivis: { type: Array, default: () => [] },
    tiposSanguineos: { type: Array, default: () => [] },
    canaisAviso: { type: Array, default: () => [] },
    convenios: { type: Array, default: () => [] },
    parentescos: { type: Array, default: () => [] },
});
const pacientesLocal = ref([...(props.pacientes || [])]);
watch(() => props.pacientes, (v) => { pacientesLocal.value = [...(v || [])]; });

const columns = [
    { id: "id", name: "ID" },
    { id: "nome", name: "Nome" },
    { id: "cpf", name: "CPF" },
    { id: "celular", name: "Celular" },
    { id: "email", name: "Email" },
    { id: "data_nascimento", name: "Nascimento" },
    { id: "convenio", name: "Convênio" },
];

const showModal = ref(false);
const modalTitle = ref('Adicionar Paciente');
const formKey = ref(0);
function openModalAdd() {
    modalTitle.value = 'Adicionar Paciente';
    showModal.value = true;
}
const pacienteFormRef = ref(null);
const saveProcessing = ref(false);
watchEffect(() => {
    const c = pacienteFormRef.value;
    saveProcessing.value = !!(c?.processingRef?.value ?? c?.form?.processing);
});
const isEditing = ref(false);
const saveButtonText = computed(() => isEditing.value ? 'Atualizar' : 'Salvar');

const showViewModal = ref(false);
const selectedPaciente = ref(null);
const orcamentosPaciente = ref([]);
const agendamentosPaciente = ref([]);

const reagendarModal = ref(false);
const reagendarAgendamentoId = ref(null);
const reagendarAgendamentoData = ref(null);
const reagendarForm = ref({ data: '', hora: '', pessoa_id: null });
const reagendarProcessing = ref(false);
const reagendarProfissionais = ref([]);
const reagendarProfissionalSelect = ref(null);

async function abrirModalReagendar(ag) {
    reagendarAgendamentoId.value = ag.id;
    reagendarAgendamentoData.value = ag;
    reagendarForm.value.data = ag.data || '';
    reagendarForm.value.hora = ag.hora || '';
    reagendarForm.value.pessoa_id = ag.pessoa_id || null;
    reagendarProfissionais.value = [];
    reagendarModal.value = true;

    // Fetch professionals
    const procId = ag.procedimento_id || ag.tuss_id;
    if (procId) {
        try {
            const targetPessoaId = ag.pessoa_id ? String(ag.pessoa_id) : '';
            const res = await window.axios.get('/agendamentos/profissionais-por-procedimento', {
                params: {
                    procedimento_id: procId,
                    convenio_id: ag.convenio_id || null
                }
            });
            reagendarProfissionais.value = Array.isArray(res?.data?.profissionais) ? res.data.profissionais : [];
            await nextTick();

            setTimeout(() => {
                const el = reagendarProfissionalSelect.value;
                if (el) {
                    if (el._choicesInstance) {
                        el._choicesInstance.destroy();
                        el._choicesInstance = null;
                        el.dataset.choicesInitialized = 'false';
                    }
                    if (window.initChoiceEl) {
                        window.initChoiceEl(el);
                    } else if (window.initChoices) {
                        window.initChoices();
                    }
                    if (el._choicesInstance && targetPessoaId) {
                        try {
                            el._choicesInstance.setChoiceByValue(targetPessoaId);
                        } catch (e) {}
                    }
                    if (window.syncChoiceValue) {
                        window.syncChoiceValue(el, targetPessoaId);
                    }
                }
            }, 100);
        } catch (e) { }
    }
}

async function confirmarReagendamento() {
    if (!reagendarAgendamentoId.value) return;
    reagendarProcessing.value = true;
    try {
        await window.axios.put(`/agendamentos/${reagendarAgendamentoId.value}`, {
            data: reagendarForm.value.data,
            hora: reagendarForm.value.hora,
            pessoa_id: reagendarForm.value.pessoa_id
        });
        reagendarModal.value = false;
        // Recarregar os agendamentos do paciente aberto
        if (selectedPaciente.value) {
            carregarAgendamentos(selectedPaciente.value.id);
        }
    } catch (e) {
        console.error("Erro ao reagendar", e);
    } finally {
        reagendarProcessing.value = false;
    }
}

function carregarAgendamentos(id) {
    window.axios.get(`/pacientes/${id}/agendamentos`).then((res) => {
        agendamentosPaciente.value = Array.isArray(res?.data?.agendamentos) ? res.data.agendamentos : [];
    }).catch(() => { });
}

function formatCurrencyBR(v) {
    const n = Number(v || 0);
    return n.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

function formatDateTimeBR(dateString) {
    if (!dateString) return '';
    // se for apenas YYYY-MM-DD, a conversão ajusta fuso horário. Adicionamos T00:00:00 se não tiver hora
    const dt = dateString.includes('T') || dateString.includes(' ') ? dateString : `${dateString}T00:00:00`;
    const d = new Date(dt);
    if (isNaN(d.getTime())) return dateString;
    return d.toLocaleString('pt-BR');
}

// chamar o método submit exposto pelo componente filho PacienteForm.
function onSavePaciente() {
    if (isEditing.value && editingId.value) {
        const id = editingId.value;
        pacienteFormRef.value?.submitUpdate(id, () => {
            const f = pacienteFormRef.value?.form;
            if (f) {
                const idx = pacientesLocal.value.findIndex(px => String(px.id) === String(id));
                if (idx !== -1) {
                    const existing = pacientesLocal.value[idx];
                    const selectedConvenioDesc = (() => {
                        const convs = Array.isArray(f.convenios) ? f.convenios : [];
                        if (!convs.length) return '';
                        const descs = convs.map((c) => (props.convenios || []).find(cv => String(cv.id) === String(c.convenio_id))?.descricao).filter(Boolean);
                        return descs.join(', ');
                    })();
                    pacientesLocal.value[idx] = {
                        ...existing,
                        nome: f.nome || '',
                        cpf: f.cpf || '',
                        email: f.email || '',
                        celular: f.celular || '',
                        sexo: f.sexo || '',
                        estado_civil_id: f.estado_civil_id ?? '',
                        tipo_sanguineo_id: f.tipo_sanguineo_id ?? '',
                        canal_aviso_id: f.canal_aviso_id ?? '',
                        receber_avisos: !!f.receber_avisos,
                        tem_responsavel: !!f.tem_responsavel,
                        convenio_ids: Array.isArray(f.convenios) ? f.convenios.map(c => c.convenio_id).join(',') : '',
                        convenio: selectedConvenioDesc,
                        rg: f.rg || '',
                        naturalidade: f.naturalidade || '',
                        altura: f.altura ?? null,
                        peso: f.peso ?? null,
                        cor_pele: f.cor_pele || '',
                        telefone: f.telefone || '',
                        profissao: f.profissao || '',
                        escolaridade: f.escolaridade || '',
                        nome_mae: f.nome_mae || '',
                        nome_pai: f.nome_pai || '',
                        observacoes: f.observacoes || '',
                        cep: f.cep || '',
                        endereco: f.endereco || '',
                        numero: f.numero || '',
                        bairro: f.bairro || '',
                        cidade: f.cidade || '',
                        complemento: f.complemento || '',
                    };
                }
            }
            showModal.value = false;
            isEditing.value = false;
            editingId.value = null;
        }, {
            onStart: () => { saveProcessing.value = true; },
            onFinish: () => { saveProcessing.value = false; },
        });
    } else {
        pacienteFormRef.value?.submit(() => {
            showModal.value = false;
        }, {
            onStart: () => { saveProcessing.value = true; },
            onFinish: () => { saveProcessing.value = false; },
        });
    }
}
const deleteModal = ref(false);
const pacienteToDelete = ref({});
const deleteSubTitle = ref('Deseja realmente excluir');
function openModalDelete(row) {
    pacienteToDelete.value = { ...row, nome: row?.nome };
    deleteSubTitle.value = row?.nome ? `Deseja realmente excluir o paciente "${row.nome}"?` : 'Deseja realmente excluir';
    deleteModal.value = true;
}
function confirmDelete() {
    const id = pacienteToDelete.value?.id;
    if (!id) { deleteModal.value = false; return; }
    const f = useForm({});
    f.delete(`/pacientes/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteModal.value = false;
            pacienteToDelete.value = {};
        }
    });
}
const bulkDeleteModal = ref(false);
const selectedIdsForDelete = ref([]);
const bulkDeleteSummary = ref({ nome: '' });
const bulkDeleteSubTitle = ref('Deseja realmente excluir os pacientes selecionados?');
function openModalDeleteMulti(selectedIds) {
    selectedIdsForDelete.value = Array.isArray(selectedIds) ? selectedIds.map(String) : [];
    const total = selectedIdsForDelete.value.length;
    bulkDeleteSummary.value = { nome: `${total} paciente(s) selecionado(s)` };
    bulkDeleteSubTitle.value = `Deseja realmente excluir ${total} paciente(s) selecionado(s)?`;
    bulkDeleteModal.value = true;
}
function confirmBulkDelete() {
    const ids = selectedIdsForDelete.value;
    if (!ids || ids.length === 0) { bulkDeleteModal.value = false; return; }
    const f = useForm({ ids });
    f.delete(`/pacientes/bulk`, {
        preserveScroll: true,
        onSuccess: () => {
            bulkDeleteModal.value = false;
            selectedIdsForDelete.value = [];
        }
    });
}
const editingId = ref(null);
watch(showModal, async (v) => {
    if (!v) {
        isEditing.value = false;
        editingId.value = null;
        await nextTick();
        if (pacienteFormRef.value?.form) {
            try {
                pacienteFormRef.value.form.clearErrors?.();
                pacienteFormRef.value.form.reset?.();
            } catch (e) { }
        }
        formKey.value += 1;
    }
});
async function openModalEdit(id) {
    const p = pacientesLocal.value.find(px => String(px.id) === String(id));
    if (!p) return;
    isEditing.value = true;
    editingId.value = p.id;
    modalTitle.value = 'Editar Paciente';
    showModal.value = true;
    await nextTick();

    // Load convenios with details
    let conveniosData = [];
    try {
        const res = await window.axios.get(`/pacientes/${p.id}/convenios`);
        conveniosData = res?.data?.convenios || [];
    } catch (e) {
        console.error('Error loading convenios:', e);
    }

    if (pacienteFormRef.value?.form) {
        const f = pacienteFormRef.value.form;
        f.nome = p.nome || '';
        f.cpf = p.cpf || '';
        f.cns = p.cns || '';
        f.rg = p.rg || '';
        f.email = p.email || '';
        f.celular = p.celular || '';
        f.data_nascimento = p.data_nascimento || '';
        f.naturalidade = p.naturalidade || '';
        f.convenios = conveniosData.map(c => ({
            convenio_id: c.id,
            numero_carteira: c.numero_carteira || ''
        }));
        if (pacienteFormRef.value?.loadConvenios) {
            pacienteFormRef.value.loadConvenios(f.convenios);
        }
        f.sexo = p.sexo || '';
        f.receber_avisos = !!p.receber_avisos;
        f.tem_responsavel = !!p.tem_responsavel;
        f.altura = p.altura ?? null;
        f.peso = p.peso ?? null;
        f.cor_pele = p.cor_pele || '';
        f.telefone = p.telefone || '';
        f.profissao = p.profissao || '';
        f.escolaridade = p.escolaridade || '';
        f.nome_mae = p.nome_mae || '';
        f.nome_pai = p.nome_pai || '';
        f.observacoes = p.observacoes || '';
        f.cep = p.cep || '';
        f.endereco = p.endereco || '';
        f.numero = p.numero || '';
        f.bairro = p.bairro || '';
        f.cidade = p.cidade || '';
        f.complemento = p.complemento || '';
        f.estado_civil_id = p.estado_civil_id ?? '';
        f.tipo_sanguineo_id = p.tipo_sanguineo_id ?? '';
        f.canal_aviso_id = p.canal_aviso_id ?? '';
        f.responsavel_nome = p.responsavel_nome || '';
        f.responsavel_parentesco_id = p.responsavel_parentesco_id ?? '';
        f.responsavel_cpf = p.responsavel_cpf || '';
        f.responsavel_rg = p.responsavel_rg || '';
        f.responsavel_data_nascimento = p.responsavel_data_nascimento || '';
        f.responsavel_celular = p.responsavel_celular || '';
        f.responsavel_telefone = p.responsavel_telefone || '';
        f.responsavel_email = p.responsavel_email || '';
    }
    await nextTick();
    if (pacienteFormRef.value?.syncChoices) {
        pacienteFormRef.value.syncChoices();
    }
}
function openModalShow(id) {
    const p = pacientesLocal.value.find(px => String(px.id) === String(id));
    if (!p) return;
    selectedPaciente.value = { ...p };
    showViewModal.value = true;
    orcamentosPaciente.value = [];
    agendamentosPaciente.value = [];
    carregarAgendamentos(p.id);
    try {
        window.axios.get(`/pacientes/${p.id}/orcamentos`).then((res) => {
            const arr = Array.isArray(res?.data?.orcamentos) ? res.data.orcamentos : [];
            orcamentosPaciente.value = arr.map(o => ({
                id: o.id,
                numero: o.numero,
                data_emissao: o.data_emissao,
                validade: o.validade,
                valor_bruto: o.valor_bruto,
                desconto: o.desconto,
                valor_total: o.valor_total,
            }));
        }).catch(() => { });
    } catch (e) { }
}
function fecharViewModal() {
    showViewModal.value = false;
    selectedPaciente.value = null;
    orcamentosPaciente.value = [];
}
// Funções de aprovação removidas

</script>

<style scoped>
:deep(.nav-tabs-custom .nav-item .nav-link.active) {
    background-color: #ffffff !important;
}
</style>
