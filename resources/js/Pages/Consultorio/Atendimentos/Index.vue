<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import SimpleTable from "@/Components/Tables/SimpleTable.vue";

const props = defineProps({
    atendimentos: Array,
});

// Aba ativa
const activeTab = ref('todos');

// Helpers
const hasAtendimentoEmAndamento = (medicoId) => {
    if (!medicoId) return false;
    return props.atendimentos.some(a => a.medico_id === medicoId && a.status === 'EM ATENDIMENTO');
};

const getInitials = (name) => {
    if (!name) return 'NA';
    const names = name.trim().split(' ');
    if (names.length >= 2) {
        return (names[0][0] + names[names.length - 1][0]).toUpperCase();
    }
    return names[0].substring(0, 2).toUpperCase();
};

const getRandomColorClass = (name) => {
    const colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary', 'bg-dark'];
    if (!name) return 'bg-primary';
    const index = name.charCodeAt(0) % colors.length;
    return colors[index];
};

// Estatísticas
const totalAguardando = computed(() => props.atendimentos.filter(a => ['AGUARDANDO', 'CHAMADO'].includes(a.status)).length);
const totalEmAtendimento = computed(() => props.atendimentos.filter(a => a.status === 'EM ATENDIMENTO').length);
const totalAtendidos = computed(() => props.atendimentos.filter(a => a.status === 'ATENDIDO').length);

// Filtro de lista
const filteredAtendimentos = computed(() => {
    if (activeTab.value === 'todos') return props.atendimentos;
    if (activeTab.value === 'aguardando') {
        return props.atendimentos.filter(a => ['AGUARDANDO', 'CHAMADO'].includes(a.status));
    }
    if (activeTab.value === 'em_atendimento') {
        return props.atendimentos.filter(a => a.status === 'EM ATENDIMENTO');
    }
    if (activeTab.value === 'atendidos') {
        return props.atendimentos.filter(a => a.status === 'ATENDIDO');
    }
    return props.atendimentos;
});

let interval = null;

onMounted(() => {
    interval = setInterval(() => {
        router.reload({ only: ['atendimentos'], preserveScroll: true, preserveState: true });
    }, 15000); // Atualiza a cada 15 segundos
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
});

const tableColumns = [
    { key: 'id', label: '#', width: '40px' },
    { key: 'paciente', label: 'Paciente' },
    { key: 'idade', label: 'Idade', width: '90px' },
    { key: 'detalhes', label: 'Detalhes' },
    { key: 'chegada', label: 'Chegada / Agendamento' },
    { key: 'status', label: 'Status' }
];

</script>

<template>
    <Layout>

        <Head title="Atendimentos" />
        <PageHeader title="Pronto Atendimento" pageTitle="Consultório" />



        <div class="row">
            <div class="col-lg-12">
                <SimpleTable title="Fila de Atendimento" :items="filteredAtendimentos" :columns="tableColumns"
                    has-actions :searchable="true" searchPlaceholder="Buscar paciente..."
                    :searchFields="['paciente.nome', 'paciente.cpf']" emptyTitle="Nenhum paciente encontrado"
                    emptyMessage="Não há pacientes na lista atual." emptyIcon="ri-inbox-line">
                    <!-- Abas como Top Slot -->
                    <template #top>
                        <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link fw-semibold" :class="{ active: activeTab === 'todos' }"
                                    @click.prevent="activeTab = 'todos'" href="#">
                                    Todos <span class="badge bg-secondary align-middle ms-1">{{ atendimentos.length
                                        }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-semibold" :class="{ active: activeTab === 'aguardando' }"
                                    @click.prevent="activeTab = 'aguardando'" href="#">
                                    Aguardando <span class="badge bg-warning align-middle ms-1">{{ totalAguardando
                                        }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-semibold" :class="{ active: activeTab === 'em_atendimento' }"
                                    @click.prevent="activeTab = 'em_atendimento'" href="#">
                                    Em Atendimento <span class="badge bg-primary align-middle ms-1">{{
                                        totalEmAtendimento }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-semibold" :class="{ active: activeTab === 'atendidos' }"
                                    @click.prevent="activeTab = 'atendidos'" href="#">
                                    Finalizados <span class="badge bg-success align-middle ms-1">{{ totalAtendidos
                                        }}</span>
                                </a>
                            </li>
                        </ul>
                    </template>

                    <template #cell(id)="{ index }">
                        <div class="fw-medium text-muted">{{ index + 1 }}</div>
                    </template>

                    <template #cell(paciente)="{ item }">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-xs">
                                    <div class="avatar-title rounded-circle shadow"
                                        :class="getRandomColorClass(item.paciente?.nome)">
                                        {{ getInitials(item.paciente?.nome) }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-2 name">
                                <h5 class="fs-14 mb-0">
                                    {{ item.paciente?.nome || 'N/A' }}
                                    <span v-if="item.emergencia" class="badge bg-danger ms-2"><i
                                            class="ri-alarm-warning-line align-middle"></i> EMERGÊNCIA</span>
                                </h5>
                                <p class="text-muted mb-0 fs-12" v-if="item.paciente?.cpf">CPF: {{ item.paciente.cpf }}
                                </p>
                            </div>
                        </div>
                    </template>

                    <template #cell(idade)="{ item }">
                        <span v-if="item.idade_paciente !== undefined">{{ item.idade_paciente }} anos</span>
                        <span v-else class="text-muted">-</span>
                    </template>

                    <template #cell(detalhes)="{ item }">
                        <div class="d-flex flex-column">
                            <span class="fw-medium">{{ item.procedimento?.nome || 'N/A' }}</span>
                            <span class="text-muted fs-12">{{ item.medico?.nome || 'N/A' }}</span>
                        </div>
                    </template>

                    <template #cell(chegada)="{ item }">
                        <div class="d-flex flex-column">
                            <span>{{ item.data_atendimento }}</span>
                            <span class="text-muted fs-12 fw-medium"><i
                                    class="ri-time-line align-bottom text-primary"></i> Previsto: {{ item.hora_prevista
                                }}</span>
                        </div>
                    </template>



                    <template #actions="{ item }">
                        <div class="d-flex gap-2">
                            <template v-if="item.status === 'AGUARDANDO' || item.status === 'CHAMADO'">
                                <Link v-if="!hasAtendimentoEmAndamento(item.medico_id)"
                                    :href="route('atendimentos.chamar', item.id)" method="post" as="button"
                                    class="btn btn-sm btn-soft-info" preserve-scroll title="Chamar no Painel">
                                    <i class="ri-volume-up-line align-bottom"></i>
                                </Link>
                                <button v-else class="btn btn-sm btn-soft-info" disabled title="Médico em atendimento">
                                    <i class="ri-volume-up-line align-bottom"></i>
                                </button>

                                <Link :href="route('atendimentos.pep', item.id)" class="btn btn-sm btn-success w-100"
                                    preserve-scroll title="Abrir Prontuário">
                                    <i class="ri-folder-open-line align-bottom me-1"></i> Prontuário
                                </Link>
                            </template>

                            <template v-else-if="item.status === 'EM ATENDIMENTO'">
                                <Link :href="route('atendimentos.pep', item.id)" class="btn btn-sm btn-primary w-100"
                                    preserve-scroll title="Continuar Atendimento">
                                    <i class="ri-file-text-line align-bottom me-1"></i> Continuar
                                </Link>
                            </template>

                            <template v-else-if="item.status === 'ATENDIDO' || item.status === 'NÃO ATENDIDO' || item.status === 'CANCELADO'">
                                <Link :href="route('atendimentos.pep', item.id)"
                                    class="btn btn-sm btn-soft-secondary w-100" preserve-scroll title="Ver PEP">
                                    <i class="ri-eye-line align-bottom me-1"></i> Prontuário
                                </Link>
                            </template>
                        </div>
                    </template>
                </SimpleTable>
            </div>
        </div>
    </Layout>
</template>
