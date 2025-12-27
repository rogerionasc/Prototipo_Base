<template>
    <Layout>
        <Head title="Pacientes" />
        <PageHeader title="Pacientes" pageTitle="Menu" />
        <TableGrid :columns="columns" :data="pacientes" :tableTitle="'Todos os Pacientes'" :showStatus="false"
            :searchPlaceholder="'Buscar por paciente'" @modalDdeletarMultiplos="openModalDeleteMulti"
            @delete="openModalDelete" @edit="openModalEdit" @show="openModalShow" @add="openModalAdd" />
        <Modal v-model="showModal" :title="'Adicionar Paciente'" size="xl" @save="onSavePaciente">
            <PacienteForm
                ref="pacienteFormRef"
                :estados-civis="estadosCivis"
                :tipos-sanguineos="tiposSanguineos"
                :canais-aviso="canaisAviso"
            />
        </Modal>
    </Layout>
</template>
<script setup>
import "gridjs/dist/theme/mermaid.css";
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head } from '@inertiajs/vue3';
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Modal from "@/Components/Modal.vue";
import PacienteForm from "@/Pages/Pacientes/Create.vue";
import { ref } from "vue";

const { pacientes, estadosCivis, tiposSanguineos, canaisAviso } = defineProps({
    pacientes: { type: Array, default: () => [] },
    estadosCivis: { type: Array, default: () => [] },
    tiposSanguineos: { type: Array, default: () => [] },
    canaisAviso: { type: Array, default: () => [] },
});

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
function openModalAdd() { showModal.value = true; }
const pacienteFormRef = ref(null);

// chamar o método submit exposto pelo componente filho PacienteForm.
function onSavePaciente() {
    pacienteFormRef.value?.submit(() => {
        showModal.value = false;
    });
}
function openModalDeleteMulti(selectedIds) {
    alert('Remover Varios: ' + JSON.stringify(selectedIds));
}
function openModalDelete(row) {
    alert('Excluir: ' + JSON.stringify(row));
}
function openModalEdit(id) {
    alert('Editar: ID ' + JSON.stringify(id));
}
function openModalShow(id) {
    alert('Mostrar: ID ' + JSON.stringify(id));
}
</script>
