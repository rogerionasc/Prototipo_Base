<template>
    <Layout>
        <Head title="Guichês" />
        <PageHeader title="Guichês" pageTitle="Clínica" />
        <TableGrid :columns="columns" :data="guiches" :tableTitle="'Todos os Guichês'" :showStatus="false"
            :searchPlaceholder="'Buscar por guichê'"
            @delete="openModalDelete" @edit="openModalEdit" @add="openModalAdd" />

        <Modal v-model="showModal" :title="modalTitle" size="md" :name-button="saveButtonText" :processing="saveProcessing" @save="onSave">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Nome do Guichê <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" v-model="form.nome" :class="{ 'is-invalid': form.errors.nome }" />
                    <div class="invalid-feedback" v-if="form.errors.nome">{{ form.errors.nome }}</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Hostname da Máquina</label>
                    <input type="text" class="form-control" v-model="form.hostname" :class="{ 'is-invalid': form.errors.hostname }" />
                    <div class="invalid-feedback" v-if="form.errors.hostname">{{ form.errors.hostname }}</div>
                </div>
                <div class="col-12 mt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="statusSwitch" v-model="form.status">
                        <label class="form-check-label" for="statusSwitch">Ativo</label>
                    </div>
                </div>
            </div>
        </Modal>

        <ModalDelete v-model="deleteModal" :title="'Excluir Guichê'" :subTitle="deleteSubTitle" :item-delete="itemToDelete" @save="confirmDelete" />
    </Layout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Modal from "@/Components/Modal.vue";
import ModalDelete from "@/Components/ModalDelete.vue";

import { html } from 'gridjs';

const props = defineProps({
    guiches: {
        type: Array,
        default: () => []
    }
});

const columns = [
    { name: 'Nome', id: 'nome' },
    { name: 'Hostname', id: 'hostname' },
    { 
        name: 'Status', 
        id: 'status', 
        formatter: (val) => {
            if (val) return html(`<span class="badge bg-success-subtle text-success">Ativo</span>`);
            return html(`<span class="badge bg-danger-subtle text-danger">Inativo</span>`);
        }
    },
];

const showModal = ref(false);
const saveProcessing = ref(false);
const modalMode = ref('add');
const itemId = ref(null);

const form = useForm({
    nome: '',
    hostname: '',
    status: true,
});

const deleteModal = ref(false);
const itemToDelete = ref(null);

const modalTitle = computed(() => modalMode.value === 'add' ? 'Novo Guichê' : 'Editar Guichê');
const saveButtonText = computed(() => modalMode.value === 'add' ? 'Salvar' : 'Atualizar');
const deleteSubTitle = computed(() => itemToDelete.value ? `Tem certeza que deseja excluir o guichê "${itemToDelete.value.nome}"?` : '');

const openModalAdd = () => {
    modalMode.value = 'add';
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openModalEdit = (id, item) => {
    modalMode.value = 'edit';
    itemId.value = id;
    form.nome = item.nome;
    form.hostname = item.hostname || '';
    form.status = item.status;
    form.clearErrors();
    showModal.value = true;
};

const onSave = () => {
    saveProcessing.value = true;
    if (modalMode.value === 'add') {
        form.post(route('guiches.store'), {
            onSuccess: () => {
                showModal.value = false;
            },
            onFinish: () => saveProcessing.value = false
        });
    } else {
        form.put(route('guiches.update', itemId.value), {
            onSuccess: () => {
                showModal.value = false;
            },
            onFinish: () => saveProcessing.value = false
        });
    }
};

const openModalDelete = (item) => {
    itemToDelete.value = item;
    deleteModal.value = true;
};

const confirmDelete = () => {
    if (!itemToDelete.value) return;
    form.delete(route('guiches.destroy', itemToDelete.value.id), {
        onSuccess: () => {
            deleteModal.value = false;
            itemToDelete.value = null;
        }
    });
};
</script>
