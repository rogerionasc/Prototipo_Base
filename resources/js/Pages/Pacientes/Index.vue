<template>
    <Layout>
        <Head title="Pacientes" />
        <PageHeader title="Pacientes" pageTitle="Menu" />
        <TableGrid :columns="columns" :data="pacientesLocal" :tableTitle="'Todos os Pacientes'" :showStatus="false"
            :searchPlaceholder="'Buscar por paciente'" @modalDdeletarMultiplos="openModalDeleteMulti"
            @delete="openModalDelete" @edit="openModalEdit" @show="openModalShow" @add="openModalAdd" />
        <Modal v-model="showModal" :title="modalTitle" size="xl" @save="onSavePaciente">
            <PacienteForm
                ref="pacienteFormRef"
                :estados-civis="props.estadosCivis"
                :tipos-sanguineos="props.tiposSanguineos"
                :canais-aviso="props.canaisAviso"
                :convenios="props.convenios"
            />
        </Modal>
        <ModalDelete v-model="deleteModal" :title="'Excluir Paciente'" :subTitle="deleteSubTitle" :item-delete="pacienteToDelete" @save="confirmDelete" />
        <ModalDelete v-model="bulkDeleteModal" :title="'Excluir Pacientes'" :subTitle="bulkDeleteSubTitle" :item-delete="bulkDeleteSummary" @save="confirmBulkDelete" />
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
import { ref, nextTick, watch } from "vue";

const props = defineProps({
    pacientes: { type: Array, default: () => [] },
    estadosCivis: { type: Array, default: () => [] },
    tiposSanguineos: { type: Array, default: () => [] },
    canaisAviso: { type: Array, default: () => [] },
    convenios: { type: Array, default: () => [] },
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
function openModalAdd() {
    modalTitle.value = 'Adicionar Paciente';
    showModal.value = true;
}
const pacienteFormRef = ref(null);

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
        });
    } else {
        pacienteFormRef.value?.submit(() => {
            showModal.value = false;
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
const isEditing = ref(false);
const editingId = ref(null);
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
    }
    await nextTick();
    if (pacienteFormRef.value?.syncChoices) {
        pacienteFormRef.value.syncChoices();
    }
}
function openModalShow(id) {
    alert('Mostrar: ID ' + JSON.stringify(id));
}
</script>
