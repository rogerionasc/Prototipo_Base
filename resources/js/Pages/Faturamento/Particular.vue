<template>
    <Layout>
        <Head title="Convenio" />
        <PageHeader title="Convenio" pageTitle="Menu" />
        <TableGrid :columns="columns" :data="conveniosLocal" :tableTitle="'Todos os Convenios'" :showStatus="false"
            :searchPlaceholder="'Buscar por convenio'" @modalDdeletarMultiplos="openModalDeleteMulti"
            @delete="openModalDelete" @edit="openModalEdit" @show="openModalShow" @add="openModalAdd" />
  </Layout>
</template>
<script lang="js" setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head } from "@inertiajs/vue3";
import { toRef } from "vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";
</script>
