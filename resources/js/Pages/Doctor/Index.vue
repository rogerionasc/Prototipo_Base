<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";

const medico = [
    [1, "Lucas", "lucas@example.com", "Analista de Dados", "Empresa Alpha", "Brasil", "Ativo"],
    [2, "Maria", "maria@example.com", "Gerente de Projetos", "Empresa Beta", "Portugal", "Inativo"],
    [3, "João", "joao@example.com", "Desenvolvedor", "Empresa Gamma", "Espanha", "Pendente"],
    [4, "Jonathan", "jonathan@example.com", "Arquiteto de Software", "Empresa Delta", "Vanuatu", "Suspenso"],
    [5, "Harold", "harold@example.com", "Coordenador Criativo", "Empresa Epsilon", "Niger"],
    [6, "Shannan", "shannan@example.com", "Analista de Funcionalidades", "Empresa Zeta", "Niue"],
    [7, "Carlos", "carlos@example.com", "Designer UI/UX", "Empresa Theta", "Brasil"],
    [8, "Ana", "ana@example.com", "Especialista em Marketing", "Empresa Lambda", "Argentina"],
    [9, "Fernanda", "fernanda@example.com", "Analista de Sistemas", "Empresa Sigma", "Chile"],
    [10, "Bruno", "bruno@example.com", "Engenheiro DevOps", "Empresa Omega", "Canadá"],
    [11, "Fernando", "fernando@example.com", "Analista de Sistemas", "Empresa Sigma", "Chile"],
    [12, "Bruno", "bruno@example.com", "Engenheiro DevOps", "Empresa Omega", "Canadá"],
    [13, "Fernando", "fernando@example.com", "Analista de Sistemas", "Empresa Sigma", "Chile"],
    [14, "Bruno", "bruno@example.com", "Engenheiro DevOps", "Empresa Omega", "Canadá"],
    [15, "Fernando", "fernando@example.com", "Analista de Sistemas", "Empresa Sigma", "Chile"],


];

const columns = [
    { id: "id", name: "ID" },
    { id: "nome", name: "Nome" },
    { id: "email", name: "Email" },
    { id: "especialidade", name: "Especialidade" },
    { id: "empresa", name: "Empresa" },
    { id: "pais", name: "País" }
];

const showModal = ref(false);

function openModalAdd() {
    alert('Adicionar');
}

function openModalDeleteMulti(selectedIds) {
    alert('Remover Varios: ' + JSON.stringify(selectedIds));
}

function openModalDelete(id) {
    alert('Excluir: ID '+ JSON.stringify(id));
}
function openModalEdit(id) {
    alert('Editar: ID '+ JSON.stringify(id));
}
function openModalShow(id) {
    alert('Mostar: ID '+ JSON.stringify(id));
}


</script>

<template>
  <Layout>
    <Head title="Médicos" />
    <PageHeader title="Médicos" pageTitle="Menu"/>
        <TableGrid
        :columns="columns"
        :data="medico"
        :searchPlaceholder="'Buscar médico'"
        :show-actions="false"
        :showCheckbox="true"
        :search="true"
        :showAddButton="true"
        :showStatus="false"
        @abrir-modal-adicionar="openModalAdd"
        @abrir-modal-deletar-varios="openModalDeleteMulti"
        @delete-item="openModalDelete"
        @edit-item="openModalEdit"
        @show-item="openModalShow"

        />
  </Layout>
</template>
