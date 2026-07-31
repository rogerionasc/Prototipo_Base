<script setup>
import { ref, computed, watch, nextTick } from "vue";
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head, useForm, router } from '@inertiajs/vue3';
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Modal from "@/Components/Modal.vue";
import ModalDelete from "@/Components/ModalDelete.vue";
import toggleAnimation from "@/Components/widgets/tdrtiskw.json";
import Choices from "choices.js";

const props = defineProps({
    usuarios: {
        type: Array,
        required: true
    }
});

const columns = [
    { id: "id", name: "ID", width: "70px" },
    { id: "nome_completo", name: "Nome" },
    { id: "email", name: "Email" },
    { id: "pessoas", name: "Pessoas" },
];

const usuariosFormatados = computed(() => {
    return props.usuarios.map(u => ({
        ...u,
        nome_completo: u.pessoa ? u.pessoa.nome : 'Sem Nome (Admin)',
        pessoas: u.pessoa ? u.pessoa.nome : 'Nenhum',
        status: u.is_active ? 'Ativo' : 'Inativo'
    }));
});

const showModal = ref(false);

const form = useForm({
    email: '',
    password: '',
    password_confirmation: '',
    pessoa_id: null
});

const isEditing = ref(false);
const editId = ref(null);

const pessoaSelectRef = ref(null);
let choicesInstance = null;
let searchTimeout = null;

watch(showModal, (newVal) => {
    if (!newVal && choicesInstance) {
        if (pessoaSelectRef.value) {
            pessoaSelectRef.value.removeEventListener('search', handleChoicesSearch);
            pessoaSelectRef.value.removeEventListener('change', handleChoicesChange);
        }
        choicesInstance.clearStore();
    }
});

const fetchAndSetChoices = async (query = '') => {
    try {
        const url = new URL(route('usuarios.pessoas_disponiveis'), window.location.origin);
        if (query) url.searchParams.append('q', query);
        if (isEditing.value && form.pessoa_id) url.searchParams.append('current_pessoa_id', form.pessoa_id);

        const response = await fetch(url.toString());
        const data = await response.json();

        const formatted = data.map(p => ({
            value: p.value,
            label: p.label,
            customProperties: { email: p.email }
        }));

        if (choicesInstance) {
            choicesInstance.setChoices(formatted, 'value', 'label', true);
            // Se já tem um pessoa_id preenchido e ele está na lista recém-carregada, setar a escolha
            if (form.pessoa_id && formatted.find(x => String(x.value) === String(form.pessoa_id))) {
                choicesInstance.setChoiceByValue(form.pessoa_id);
            }
        }
    } catch (e) {
        console.error(e);
    }
};

const handleChoicesSearch = (e) => {
    const keyword = e.detail.value;
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchAndSetChoices(keyword);
    }, 500);
};

const handleChoicesChange = (e) => {
    form.pessoa_id = e.detail ? e.detail.value : e.target.value;

    if (choicesInstance && form.pessoa_id) {
        const selected = choicesInstance.getValue();
        if (selected && selected.customProperties && selected.customProperties.email && !isEditing.value) {
            form.email = selected.customProperties.email;
        }
    }
};

const initChoices = async () => {
    await nextTick();

    if (window.initChoices) {
        window.initChoices();
    }

    await nextTick();

    choicesInstance = pessoaSelectRef.value?._choicesInstance || pessoaSelectRef.value?.choices || null;

    if (choicesInstance) {
        pessoaSelectRef.value.removeEventListener('search', handleChoicesSearch);
        pessoaSelectRef.value.removeEventListener('change', handleChoicesChange);

        choicesInstance.clearStore();

        await fetchAndSetChoices('');

        pessoaSelectRef.value.addEventListener('search', handleChoicesSearch);
        pessoaSelectRef.value.addEventListener('change', handleChoicesChange);
    }
};

function openModalAdd() {
    isEditing.value = false;
    editId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
    setTimeout(() => initChoices(), 100);
}

function openModalEdit(id) {
    isEditing.value = true;
    editId.value = id;

    const userToEdit = props.usuarios.find(u => u.id == id);
    if (userToEdit) {
        form.email = userToEdit.email;
        form.pessoa_id = userToEdit.pessoa_id;
        form.password = '';
        form.password_confirmation = '';
    }

    form.clearErrors();
    showModal.value = true;
    setTimeout(() => initChoices(), 100);
}

function submit() {
    if (isEditing.value) {
        form.put(route('usuarios.update', editId.value), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('usuarios.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
}

const toggleModal = ref(false);
const toggleUser = ref({});
const toggleSubTitle = ref('');
const toggleMessage = ref('');
const toggleButtonText = ref('');
const toggleButtonClass = ref('');

function toggleStatus(row) {
    if (typeof row !== 'object') {
        row = props.usuarios.find(u => String(u.id) === String(row)) || { id: row };
    }
    toggleUser.value = row;
    const isBlocking = row.is_active;

    toggleSubTitle.value = isBlocking ? 'Bloquear Usuário' : 'Desbloquear Usuário';
    const userName = row.nome_completo || row.email || '';
    toggleMessage.value = isBlocking
        ? `Deseja realmente bloquear o acesso de "<span class="text-danger fw-bold">${userName}</span>"?<br>Ele não poderá mais acessar o sistema.`
        : `Deseja realmente desbloquear o acesso de "<span class="text-success fw-bold">${userName}</span>"?<br>O acesso ao sistema será reestabelecido.`;

    toggleButtonText.value = isBlocking ? 'Sim, bloquear' : 'Sim, desbloquear';
    toggleButtonClass.value = isBlocking ? 'btn-danger' : 'btn-success';

    toggleModal.value = true;
}

function confirmToggle() {
    if (!toggleUser.value?.id) return;
    router.put(route('usuarios.toggle_status', toggleUser.value.id), {}, {
        onSuccess: () => {
            toggleModal.value = false;
        }
    });
}
</script>

<template>
    <Layout>

        <Head title="Usuários" />
        <PageHeader title="Usuários" pageTitle="Configurações" />

        <TableGrid :columns="columns" :data="usuariosFormatados" :tableTitle="'Lista de Usuários'" :showStatus="true"
            :actionsConfig="{ delete: true, edit: true, show: false, toggle: true }"
            :searchPlaceholder="'Buscar por usuário'" @add="openModalAdd" @edit="openModalEdit"
            @toggle="toggleStatus" />

        <Modal v-model="showModal" :title="isEditing ? 'Editar Usuário' : 'Criar Novo Usuário'" size="md"
            :name-button="isEditing ? 'Salvar Alterações' : 'Criar Usuário'" :processing="form.processing"
            @save="submit">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Selecionar Pessoa <span class="text-danger">*</span></label>
                    <select id="pessoaSelect" ref="pessoaSelectRef" class="form-select" data-choices></select>
                    <div v-if="form.errors.pessoa_id" class="text-danger mt-1">{{ form.errors.pessoa_id }}</div>
                </div>


                <div class="col-md-12">
                    <label for="email-field" class="form-label">Email</label>
                    <input type="email" id="email-field" class="form-control" placeholder="Digite o email" required
                        v-model="form.email" autocomplete="off" />
                    <div v-if="form.errors.email" class="text-danger mt-1">{{ form.errors.email }}</div>
                </div>

                <div class="col-md-12">
                    <label for="password-field" class="form-label">Senha</label>
                    <input type="password" id="password-field" class="form-control" required v-model="form.password"
                        autocomplete="new-password" />
                    <div v-if="form.errors.password" class="text-danger mt-1">{{ form.errors.password }}</div>
                </div>

                <div class="col-md-12">
                    <label for="password_confirmation-field" class="form-label">Confirmar Senha</label>
                    <input type="password" id="password_confirmation-field" class="form-control" required
                        v-model="form.password_confirmation" autocomplete="new-password" />
                </div>
            </div>
        </Modal>

        <ModalDelete v-model="toggleModal" :title="'Status de Acesso'" :subTitle="toggleSubTitle"
            :message="toggleMessage" :nameButton="toggleButtonText" :buttonClass="toggleButtonClass"
            :animationData="toggleAnimation" @save="confirmToggle" />
    </Layout>
</template>
