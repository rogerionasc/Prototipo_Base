<script setup>
import { onMounted, ref } from 'vue';
// import { Link, Head } from "@inertiajs/vue3";
import Layout from "@/Layouts/main.vue";

import PageHeader from "@/Components/page-header.vue";
// import { Grid } from "gridjs";
import "gridjs/dist/theme/mermaid.css";
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Modal from '@/Components/Modal.vue';
import ModalDelete from '@/Components/ModalDelete.vue';

// Dados para geração aleatória
const nomes = ["LucasNascimentoSantosNascimentos", "Maria", "João", "Ana", "Pedro", "Julia", "Carlos", "Fernanda", "Bruno", "Amanda", "Rafael", "Camila", "Diego", "Patricia", "Gabriel", "Laura"];
const sobrenomes = ["Silva", "Santos", "Oliveira", "Souza", "Rodrigues", "Ferreira", "Alves", "Pereira", "Lima", "Gomes", "Costa", "Ribeiro", "Martins", "Carvalho"];
const cargos = ["Analista de Dados", "Gerente de Projetos", "Desenvolvedor", "Arquiteto de Software", "Designer UI/UX", "Analista de Sistemas", "Engenheiro DevOps", "Product Owner", "Scrum Master", "Tech Lead"];
const empresas = ["Empresa Alpha", "Empresa Beta", "Empresa Gamma", "Empresa Delta", "Empresa Epsilon", "Empresa Zeta", "Empresa Theta", "Empresa Lambda", "Empresa Sigma", "Empresa Omega"];
const paises = ["Brasil", "Portugal", "Espanha", "Argentina", "Chile", "Canadá", "México", "Colômbia", "Peru", "Uruguai"];
const status = ["ativo", "inativo", "pendente", "suspenso"];

// Função para gerar dados aleatórios
function gerarUsuarios(quantidade) {
    return Array.from({ length: quantidade }, (_, index) => ({
        id: index + 1,
        nome: `${nomes[Math.floor(Math.random() * nomes.length)]} ${sobrenomes[Math.floor(Math.random() * sobrenomes.length)]}`,
        email: `usuario${index + 1}@exampleeeeeeeeeeeeeeeee.com`,
        cargo: cargos[Math.floor(Math.random() * cargos.length)],
        empresa: empresas[Math.floor(Math.random() * empresas.length)],
        pais: paises[Math.floor(Math.random() * paises.length)],
        status: status[Math.floor(Math.random() * status.length)]
    }));
}

// Gerar 1000 usuários
const users = gerarUsuarios(50000);

const columns = [
    { id: "id", name: "ID" },
    { id: "nome", name: "Nome" },
    { id: "email", name: "Email" },
    { id: "cargo", name: "Cargo" },
    { id: "empresa", name: "Empresa" },
    { id: "pais", name: "País" },
];

const showModal = ref(false);
const deleteModal = ref(false);
const userToDelete = ref(null);

function openModalAdd() {
    showModal.value = true;
}

function openModalDeleteMulti(selectedIds) {
    alert('Remover Varios: ' + JSON.stringify(selectedIds));
}

function openModalDelete(user) {
    userToDelete.value = user;
    deleteModal.value = true;
}
function openModalEdit(id) {
    alert('Editar: ID ' + JSON.stringify(id));
}
function openModalShow(id) {
    alert('Mostar: ID ' + JSON.stringify(id));
}


</script>

<template>
    <Layout>
        <PageHeader title="Componentes" pageTitle="Configuração" />
        <TableGrid
        :columns="columns"
        :data="users"
        :tableTitle="'Todos os Componentes'"
        :showActions="true"
        :showCheckbox="true"
        :search="true"
        :showAddButton="true"
        :showStatus="true"
        :searchPlaceholder="'Buscar por usuário'"
        @modalDdeletarMultiplos="openModalDeleteMulti"
        @delete="openModalDelete"
        @edit="openModalEdit"
        @show="openModalShow"
        @add="openModalAdd"
        />
        <Modal v-model="showModal" :name-button="'Salvar'" :title="'Adiconar Usuário'" />

        <ModalDelete v-model="deleteModal" :item-delete="userToDelete" />
    </Layout>



</template>
