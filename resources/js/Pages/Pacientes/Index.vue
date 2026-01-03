<template>
    <Layout>
        <Head title="Pacientes" />
        <PageHeader title="Pacientes" pageTitle="Menu" />
        <TableGrid :columns="columns" :data="pacientesLocal" :tableTitle="'Todos os Pacientes'" :showStatus="false"
            :searchPlaceholder="'Buscar por paciente'" @modalDdeletarMultiplos="openModalDeleteMulti"
            @delete="openModalDelete" @edit="openModalEdit" @show="openModalShow" @add="openModalAdd" />
        <Modal v-model="showModal" :title="modalTitle" size="xl" :name-button="saveButtonText" :processing="saveProcessing" @save="onSavePaciente">
            <PacienteForm
                ref="pacienteFormRef"
                :key="formKey"
                :estados-civis="props.estadosCivis"
                :tipos-sanguineos="props.tiposSanguineos"
                :canais-aviso="props.canaisAviso"
                :convenios="props.convenios"
                :parentescos="props.parentescos"
            />
        </Modal>
        <ModalDelete v-model="deleteModal" :title="'Excluir Paciente'" :subTitle="deleteSubTitle" :item-delete="pacienteToDelete" @save="confirmDelete" />
        <ModalDelete v-model="bulkDeleteModal" :title="'Excluir Pacientes'" :subTitle="bulkDeleteSubTitle" :item-delete="bulkDeleteSummary" @save="confirmBulkDelete" />
        <Modal
            v-model="showViewModal"
            :title="'Paciente'"
            size="xl"
            :name-button="'Fechar'"
            :processing="false"
            @save="fecharViewModal"
        >
            <BTabs nav-class="nav-tabs-custom text-muted">
                <BTab title="Informações">
                    <div class="row g-3 mt-2" v-if="selectedPaciente">
                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
                            <div class="fw-medium">{{ selectedPaciente.nome }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CPF</label>
                            <div class="fw-medium">{{ selectedPaciente.cpf }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <div class="fw-medium">{{ selectedPaciente.email || '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Celular</label>
                            <div class="fw-medium">{{ selectedPaciente.celular || '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sexo</label>
                            <div class="fw-medium">{{ selectedPaciente.sexo || '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nascimento</label>
                            <div class="fw-medium">{{ selectedPaciente.data_nascimento || '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Convênio</label>
                            <div class="fw-medium">{{ selectedPaciente.convenio || 'Particular' }}</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Endereço</label>
                            <div class="fw-medium">
                                {{ [selectedPaciente.endereco, selectedPaciente.numero, selectedPaciente.bairro, selectedPaciente.cidade].filter(Boolean).join(', ') || '-' }}
                            </div>
                        </div>
                        <div class="col-md-12" v-if="selectedPaciente.observacoes">
                            <label class="form-label">Observações</label>
                            <div class="fw-medium">{{ selectedPaciente.observacoes }}</div>
                        </div>
                    </div>
                </BTab>
                <BTab title="Orçamentos">
                    <div class="mt-2">
                        <div v-if="(orcamentosPaciente || []).length === 0" class="text-muted">Nenhum orçamento encontrado</div>
                        <div v-else class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Emissão</th>
                                        <th>Validade</th>
                                        <th class="text-end">Bruto</th>
                                        <th class="text-end">Desconto</th>
                                        <th class="text-end">Total</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="o in orcamentosPaciente" :key="o.id">
                                        <td>{{ o.numero }}</td>
                                        <td>{{ o.data_emissao }}</td>
                                        <td>{{ o.validade }}</td>
                                        <td class="text-end">{{ formatCurrencyBR(o.valor_bruto) }}</td>
                                        <td class="text-end">{{ formatCurrencyBR(o.desconto) }}</td>
                                        <td class="text-end">{{ formatCurrencyBR(o.valor_total) }}</td>
                                        <td>
                                            <span v-if="o.aprovado" class="badge bg-success">Aprovado</span>
                                            <span v-else class="badge bg-warning">Aguardando aprovação</span>
                                        </td>
                                        <td>
                                            <button v-if="!o.aprovado" class="btn btn-sm btn-success me-2" @click="aprovarOrcamento(o.id)">Aprovar</button>
                                            <button v-else class="btn btn-sm btn-danger" @click="cancelarAprovacao(o.id)">Cancelar aprovação</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </BTab>
            </BTabs>
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
import { ref, nextTick, watch, computed, watchEffect } from "vue";

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
function formatCurrencyBR(v) {
    const n = Number(v || 0);
    return n.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
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
                        const cid = f.convenio_id != null && f.convenio_id !== '' ? String(f.convenio_id) : '';
                        const cobj = (props.convenios || []).find(cv => String(cv.id) === cid);
                        return cobj?.descricao || existing?.convenio || '';
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
                        convenio_id: f.convenio_id ?? '',
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
            } catch (e) {}
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
    if (pacienteFormRef.value?.form) {
        const f = pacienteFormRef.value.form;
        f.nome = p.nome || '';
        f.cpf = p.cpf || '';
        f.rg = p.rg || '';
        f.email = p.email || '';
        f.celular = p.celular || '';
        f.data_nascimento = p.data_nascimento || '';
        f.naturalidade = p.naturalidade || '';
        f.convenio_id = p.convenio_id ?? '';
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
                aprovado: !!o.aprovado,
            }));
        }).catch(() => {});
    } catch (e) {}
}
function fecharViewModal() {
    showViewModal.value = false;
    selectedPaciente.value = null;
    orcamentosPaciente.value = [];
}
function aprovarOrcamento(id) {
    if (!id) return;
    try {
        window.axios.put(`/orcamentos/${id}/approve`).then(() => {
            const idx = (orcamentosPaciente.value || []).findIndex(x => String(x.id) === String(id));
            if (idx !== -1) {
                orcamentosPaciente.value[idx] = { ...orcamentosPaciente.value[idx], aprovado: true };
            }
        }).catch(() => {});
    } catch (e) {}
}
function cancelarAprovacao(id) {
    if (!id) return;
    try {
        window.axios.put(`/orcamentos/${id}/unapprove`).then(() => {
            const idx = (orcamentosPaciente.value || []).findIndex(x => String(x.id) === String(id));
            if (idx !== -1) {
                orcamentosPaciente.value[idx] = { ...orcamentosPaciente.value[idx], aprovado: false };
            }
        }).catch(() => {});
    } catch (e) {}
}
</script>
