<template>
    <Layout>
        <Head title="Totens" />
        <PageHeader title="Totens" pageTitle="Clínica" />
        <TableGrid :columns="columns" :data="totens" :tableTitle="'Todos os Totens'" :showStatus="false"
            :searchPlaceholder="'Buscar por totem'"
            :actionsConfig="{ edit: true, delete: true, procedure: true }"
            :actionsLabels="{ procedure: 'Gerenciar Opções' }"
            :actionsIcons="{ procedure: 'ri-list-settings-line' }"
            @delete="openModalDelete" @edit="openModalEdit" @add="openModalAdd" @procedure="openModalOpcoes" />

        <Modal v-model="showModal" :title="modalTitle" size="md" :name-button="saveButtonText" :processing="saveProcessing" @save="onSave">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Nome do Totem <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" v-model="form.nome" :class="{ 'is-invalid': form.errors.nome }" />
                    <div class="invalid-feedback" v-if="form.errors.nome">{{ form.errors.nome }}</div>
                </div>
                <div class="col-12 mt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="statusSwitch" v-model="form.status">
                        <label class="form-check-label" for="statusSwitch">Ativo</label>
                    </div>
                </div>
            </div>
        </Modal>

        <ModalDelete v-model="deleteModal" :title="'Excluir Totem'" :subTitle="deleteSubTitle" :item-delete="itemToDelete" @save="confirmDelete" />

        <Modal v-model="opcoesModal" :title="`Opções do Totem: ${totemSelecionado?.nome}`" size="lg" :show-footer="true" name-button="Salvar" :processing="opcaoForm.processing" @save="syncOpcoes">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Nome da Opção <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" v-model="newOpcao.nome" :class="{ 'is-invalid': opcaoForm.errors.nome }" placeholder="Ex: Preferencial" />
                </div>
                <div class="col-md-3">
                    <label class="form-label">Código (Sigla)</label>
                    <input type="text" class="form-control" v-model="newOpcao.codigo" placeholder="Ex: PREF" maxlength="10" />
                </div>
                <div class="col-md-2">
                    <label class="form-label w-100 d-flex justify-content-between align-items-center">
                        <span>Ícone</span>
                        <a href="https://remixicon.com/" target="_blank" class="text-primary small text-decoration-underline" tabindex="-1" title="Ver lista de ícones">Catálogo <i class="ri-external-link-line"></i></a>
                    </label>
                    <input type="text" class="form-control" v-model="newOpcao.icone" placeholder="Ex: ri-heart-line" />
                </div>
                <div class="col-md-1">
                    <label class="form-label">Cor</label>
                    <input type="color" class="form-control form-control-color w-100" v-model="newOpcao.cor">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100" @click="addOpcaoLocal">Adicionar</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Código</th>
                            <th>
                                Ícone
                                <a href="https://remixicon.com/" target="_blank" class="text-muted ms-1" title="Catálogo de Ícones" tabindex="-1"><i class="ri-external-link-line"></i></a>
                            </th>
                            <th>Cor</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 80px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(opcao, index) in localOpcoes" :key="index">
                            <td>
                                <input type="text" class="form-control form-control-sm" v-model="opcao.nome" />
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" v-model="opcao.codigo" />
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" v-model="opcao.icone" placeholder="ri-heart-line" />
                            </td>
                            <td>
                                <input type="color" class="form-control form-control-color form-control-sm p-0 border-0" v-model="opcao.cor" style="width: 30px; height: 30px; cursor: pointer;">
                            </td>
                            <td>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" v-model="opcao.status" :id="'status_' + index">
                                    <label class="form-check-label" :for="'status_' + index">{{ opcao.status ? 'Ativo' : 'Inativo' }}</label>
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-soft-danger" @click="removeOpcaoLocal(index)" title="Excluir Opção">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!localOpcoes.length">
                            <td colspan="5" class="text-center text-muted">Nenhuma opção cadastrada para este totem.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Modal>
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
    totens: {
        type: Array,
        default: () => []
    }
});

const columns = [
    { name: 'Nome', id: 'nome' },
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

const opcoesModal = ref(false);
const totemSelecionado = ref(null);
const localOpcoes = ref([]);
const newOpcao = ref({ nome: '', codigo: '', icone: '', cor: '#0ab39c', status: true });

const form = useForm({
    nome: '',
    status: true,
});

const opcaoForm = useForm({
    nome: '',
    codigo: '',
    status: true,
});

const deleteModal = ref(false);
const itemToDelete = ref(null);

const modalTitle = computed(() => modalMode.value === 'add' ? 'Novo Totem' : 'Editar Totem');
const saveButtonText = computed(() => modalMode.value === 'add' ? 'Salvar' : 'Atualizar');
const deleteSubTitle = computed(() => itemToDelete.value ? `Tem certeza que deseja excluir o totem "${itemToDelete.value.nome}"?` : '');

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
    form.status = item.status;
    form.clearErrors();
    showModal.value = true;
};

const onSave = () => {
    saveProcessing.value = true;
    if (modalMode.value === 'add') {
        form.post(route('totens.store'), {
            onSuccess: () => {
                showModal.value = false;
            },
            onFinish: () => saveProcessing.value = false
        });
    } else {
        form.put(route('totens.update', itemId.value), {
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
    form.delete(route('totens.destroy', itemToDelete.value.id), {
        onSuccess: () => {
            deleteModal.value = false;
            itemToDelete.value = null;
        }
    });
};

const openModalOpcoes = (id, item) => {
    totemSelecionado.value = item;
    // Carrega uma cópia das opções atuais do totem
    localOpcoes.value = JSON.parse(JSON.stringify(item.opcoes || []));
    opcaoForm.reset();
    opcaoForm.clearErrors();
    opcoesModal.value = true;
};

const addOpcaoLocal = () => {
    if (!newOpcao.value.nome) {
        // Simple alert for now or we could use toast
        alert('O nome é obrigatório.');
        return;
    }
    localOpcoes.value.push({
        id: null,
        nome: newOpcao.value.nome,
        codigo: newOpcao.value.codigo,
        icone: newOpcao.value.icone,
        cor: newOpcao.value.cor,
        status: newOpcao.value.status
    });
    newOpcao.value = { nome: '', codigo: '', icone: '', cor: '#0ab39c', status: true };
};

const removeOpcaoLocal = (index) => {
    localOpcoes.value.splice(index, 1);
};

const syncOpcoes = () => {
    if (!totemSelecionado.value) return;
    
    const formSync = useForm({
        opcoes: localOpcoes.value
    });

    formSync.post(route('totens.opcoes.sync', totemSelecionado.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            opcoesModal.value = false;
        }
    });
};
</script>
