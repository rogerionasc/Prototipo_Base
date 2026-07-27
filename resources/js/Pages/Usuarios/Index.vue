<script setup>
import { ref, computed, watch } from "vue";
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head, useForm } from '@inertiajs/vue3';
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Modal from "@/Components/Modal.vue";
import Multiselect from "@vueform/multiselect";
import "@vueform/multiselect/themes/default.css";
import Swal from 'sweetalert2';

const props = defineProps({
    usuarios: {
        type: Array,
        required: true
    },
    profissionais: {
        type: Array,
        required: true
    }
});

const columns = [
    { id: "id", name: "ID" },
    { id: "nome_completo", name: "Nome" },
    { id: "email", name: "Email" },
    { id: "medico_vinculado", name: "Médico Vinculado" },
];

const usuariosFormatados = computed(() => {
    return props.usuarios.map(u => ({
        ...u,
        nome_completo: `${u.nome} ${u.sobrenome || ''}`.trim(),
        medico_vinculado: u.pessoa ? u.pessoa.nome : 'Nenhum'
    }));
});

const showModal = ref(false);

const form = useForm({
    nome: '',
    sobrenome: '',
    cpf: '',
    telefone: '',
    data_nascimento: '',
    email: '',
    password: '',
    password_confirmation: '',
    pessoa_id: null
});

// Opções para o Multiselect
const profissionaisOptions = computed(() => {
    return props.profissionais.map(p => ({
        value: p.id,
        label: p.nome
    }));
});

// Preencher automaticamente quando selecionar o profissional
watch(() => form.pessoa_id, (newId) => {
    if (newId) {
        const prof = props.profissionais.find(p => p.id === newId);
        if (prof) {
            const nameParts = prof.nome.split(' ');
            form.nome = nameParts[0];
            form.sobrenome = nameParts.slice(1).join(' ');
            form.cpf = prof.cpf || '';
            form.telefone = prof.telefone || prof.celular || '';
            form.data_nascimento = prof.data_nascimento || '';
            form.email = prof.email || '';
        }
    }
});

function openModalAdd() {
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function submit() {
    form.post(route('usuarios.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
}
</script>

<template>
    <Layout>
        <Head title="Usuários" />
        <PageHeader title="Usuários" pageTitle="Configurações" />

        <TableGrid 
            :columns="columns" 
            :data="usuariosFormatados" 
            :tableTitle="'Lista de Usuários'" 
            :showStatus="false"
            :searchPlaceholder="'Buscar por usuário'" 
            @add="openModalAdd" 
        />

        <Modal v-model="showModal" title="Criar Novo Usuário" size="md" name-button="Criar Usuário" :processing="form.processing" @save="submit">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Vincular a Médico/Pessoa (Busca)</label>
                    <Multiselect
                        v-model="form.pessoa_id"
                        :options="profissionaisOptions"
                        :searchable="true"
                        placeholder="Pesquise por um profissional (Opcional)"
                    />
                    <div v-if="form.errors.pessoa_id" class="text-danger mt-1">{{ form.errors.pessoa_id }}</div>
                </div>

                <div class="col-md-12">
                    <label for="nome-field" class="form-label">Nome</label>
                    <input type="text" id="nome-field" class="form-control" placeholder="Digite o nome" required v-model="form.nome" />
                    <div v-if="form.errors.nome" class="text-danger mt-1">{{ form.errors.nome }}</div>
                </div>

                <div class="col-md-12">
                    <label for="sobrenome-field" class="form-label">Sobrenome</label>
                    <input type="text" id="sobrenome-field" class="form-control" placeholder="Digite o sobrenome" v-model="form.sobrenome" />
                    <div v-if="form.errors.sobrenome" class="text-danger mt-1">{{ form.errors.sobrenome }}</div>
                </div>

                <div class="col-md-12">
                    <label for="cpf-field" class="form-label">CPF</label>
                    <input type="text" id="cpf-field" class="form-control" placeholder="Digite o CPF" required v-model="form.cpf" v-mask="['###.###.###-##']" />
                    <div v-if="form.errors.cpf" class="text-danger mt-1">{{ form.errors.cpf }}</div>
                </div>

                <div class="col-md-6">
                    <label for="telefone-field" class="form-label">Telefone</label>
                    <input type="text" id="telefone-field" class="form-control" placeholder="Digite o telefone" required v-model="form.telefone" v-mask="['(##) ####-####', '(##) #####-####']" />
                    <div v-if="form.errors.telefone" class="text-danger mt-1">{{ form.errors.telefone }}</div>
                </div>

                <div class="col-md-6">
                    <label for="nascimento-field" class="form-label">Data de Nascimento</label>
                    <input type="date" id="nascimento-field" class="form-control" required v-model="form.data_nascimento" />
                    <div v-if="form.errors.data_nascimento" class="text-danger mt-1">{{ form.errors.data_nascimento }}</div>
                </div>

                <div class="col-md-12">
                    <label for="email-field" class="form-label">Email</label>
                    <input type="email" id="email-field" class="form-control" placeholder="Digite o email" required v-model="form.email" />
                    <div v-if="form.errors.email" class="text-danger mt-1">{{ form.errors.email }}</div>
                </div>

                <div class="col-md-12">
                    <label for="password-field" class="form-label">Senha</label>
                    <input type="password" id="password-field" class="form-control" required v-model="form.password" />
                    <div v-if="form.errors.password" class="text-danger mt-1">{{ form.errors.password }}</div>
                </div>

                <div class="col-md-12">
                    <label for="password_confirmation-field" class="form-label">Confirmar Senha</label>
                    <input type="password" id="password_confirmation-field" class="form-control" required v-model="form.password_confirmation" />
                </div>
            </div>
        </Modal>
    </Layout>
</template>
