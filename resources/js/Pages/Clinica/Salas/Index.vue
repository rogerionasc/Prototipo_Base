<template>
    <Layout>
        <Head title="Salas" />
        <PageHeader title="Salas" pageTitle="Menu" />
        <TableGrid :columns="columns" :data="salas" :tableTitle="'Todas as Salas'" :showStatus="false"
            :searchPlaceholder="'Buscar por sala'"
            :actionsConfig="{ edit: true, delete: true, procedure: true }"
            :actionsLabels="{ procedure: 'Reservar Sala' }"
            :actionsIcons="{ procedure: 'ri-stethoscope-line' }"
            @delete="openModalDelete" @edit="openModalEdit" @add="openModalAdd" @procedure="openModalDoctor" />

        <Modal v-model="showModal" :title="modalTitle" size="md" :name-button="saveButtonText" :processing="saveProcessing" @save="onSaveSala">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Nome da Sala <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" v-model="form.nome" :class="{ 'is-invalid': form.errors.nome }" />
                    <div class="invalid-feedback" v-if="form.errors.nome">{{ form.errors.nome }}</div>
                </div>
            </div>
        </Modal>

        <Modal v-model="doctorModal" title="Reservar Sala" size="md" name-button="Salvar" :processing="saveProcessing" @save="onSaveDoctor">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Quem vai usar a sala?</label>
                    <select class="form-select" data-choices id="medicoSelect" ref="medicoSelect" v-model="doctorForm.pessoa_id" :class="{ 'is-invalid': doctorForm.errors.pessoa_id }">
                        <option value="">Sala Vazia</option>
                        <option v-for="prof in profissionais" :key="prof.id" :value="prof.id">{{ prof.nome }}</option>
                    </select>
                    <div class="invalid-feedback" v-if="doctorForm.errors.pessoa_id">{{ doctorForm.errors.pessoa_id }}</div>
                </div>
            </div>
        </Modal>

        <ModalDelete v-model="deleteModal" :title="'Excluir Sala'" :subTitle="deleteSubTitle" :item-delete="salaToDelete" @save="confirmDelete" />
    </Layout>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Modal from "@/Components/Modal.vue";
import ModalDelete from "@/Components/ModalDelete.vue";
import { html } from 'gridjs';

const props = defineProps({
    salas: {
        type: Array,
        default: () => []
    },
    profissionais: {
        type: Array,
        default: () => []
    }
});

const columns = [
    { name: 'Nome', id: 'nome' },
    { 
        name: 'Médico', 
        id: 'profissional_saude', 
        formatter: (val) => val ? val.nome : 'Sem médico' 
    },
    { 
        name: 'Status', 
        id: 'pessoa_id', 
        formatter: (val) => {
            if (val) return html(`<span class="badge bg-danger-subtle text-danger">Ocupada</span>`);
            return html(`<span class="badge bg-success-subtle text-success">Disponível</span>`);
        }
    },
];

const showModal = ref(false);
const saveProcessing = ref(false);
const modalMode = ref('add');
const salaId = ref(null);

const form = useForm({
    nome: '',
});

const doctorModal = ref(false);
const doctorForm = useForm({
    nome: '',
    status: true,
    pessoa_id: null,
});

const medicoSelect = ref(null);

watch(() => doctorForm.pessoa_id, async (v) => {
    await nextTick();
    if (window.syncChoiceValue && medicoSelect.value) {
        window.syncChoiceValue(medicoSelect.value, v != null ? String(v) : "");
    }
}, { immediate: true });

onMounted(() => {
    if (window.initChoices) window.initChoices();
});

const deleteModal = ref(false);
const salaToDelete = ref(null);

const modalTitle = computed(() => modalMode.value === 'add' ? 'Nova Sala' : 'Editar Sala');
const saveButtonText = computed(() => modalMode.value === 'add' ? 'Salvar' : 'Atualizar');
const deleteSubTitle = computed(() => salaToDelete.value ? `Tem certeza que deseja excluir a sala "${salaToDelete.value.nome}"?` : '');

const openModalAdd = () => {
    modalMode.value = 'add';
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openModalEdit = (id, sala) => {
    modalMode.value = 'edit';
    salaId.value = id;
    form.nome = sala.nome;
    form.clearErrors();
    showModal.value = true;
};

const openModalDoctor = (id, sala) => {
    salaId.value = id;
    doctorForm.nome = sala.nome;
    doctorForm.status = sala.status;
    doctorForm.pessoa_id = sala.pessoa_id;
    doctorForm.clearErrors();
    doctorModal.value = true;
};

const onSaveDoctor = () => {
    saveProcessing.value = true;
    doctorForm.put(route('salas.update', salaId.value), {
        onSuccess: () => {
            doctorModal.value = false;
        },
        onFinish: () => saveProcessing.value = false
    });
};

const onSaveSala = () => {
    saveProcessing.value = true;
    if (modalMode.value === 'add') {
        form.post(route('salas.store'), {
            onSuccess: () => {
                showModal.value = false;
            },
            onFinish: () => saveProcessing.value = false
        });
    } else {
        form.put(route('salas.update', salaId.value), {
            onSuccess: () => {
                showModal.value = false;
            },
            onFinish: () => saveProcessing.value = false
        });
    }
};

const openModalDelete = (sala) => {
    salaToDelete.value = sala;
    deleteModal.value = true;
};

const confirmDelete = () => {
    if (!salaToDelete.value) return;
    form.delete(route('salas.destroy', salaToDelete.value.id), {
        onSuccess: () => {
            deleteModal.value = false;
            salaToDelete.value = null;
        }
    });
};
</script>
