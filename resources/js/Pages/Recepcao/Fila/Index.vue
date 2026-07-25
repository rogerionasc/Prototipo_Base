<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import Modal from "@/Components/Modal.vue";
import SimpleTable from "@/Components/SimpleTable.vue";

const props = defineProps({
    fila: Array,
});

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

const processingIds = ref([]);

const confirmarPresenca = (agendamentoId) => {
    processingIds.value.push(agendamentoId);
    const form = useForm({});
    form.post(route('recepcao.fila.confirmar', agendamentoId), {
        preserveScroll: true,
        onFinish: () => {
            processingIds.value = processingIds.value.filter(id => id !== agendamentoId);
        }
    });
};

const showCancelModal = ref(false);
const selectedPacienteId = ref(null);
const isCanceling = ref(false);

const openCancelModal = (agendamentoId) => {
    selectedPacienteId.value = agendamentoId;
    showCancelModal.value = true;
};

const confirmarCancelamento = () => {
    if (!selectedPacienteId.value) return;
    
    isCanceling.value = true;
    const form = useForm({});
    form.post(route('recepcao.fila.cancelar', selectedPacienteId.value), {
        preserveScroll: true,
        onFinish: () => {
            isCanceling.value = false;
            showCancelModal.value = false;
            selectedPacienteId.value = null;
        }
    });
};

const searchQuery = ref('');

const filteredFila = computed(() => {
    if (!searchQuery.value) return props.fila;
    const q = searchQuery.value.toLowerCase();
    return props.fila.filter(p => 
        (p.paciente && p.paciente.toLowerCase().includes(q)) || 
        (p.cpf && p.cpf.includes(q)) ||
        (p.medico && p.medico.toLowerCase().includes(q))
    );
});

let interval = null;

onMounted(() => {
    interval = setInterval(() => {
        router.reload({ only: ['fila'], preserveScroll: true, preserveState: true });
    }, 15000); // Atualiza a cada 15 segundos
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
});

const tableColumns = [
    { key: 'hora', label: 'Horário', thClass: 'px-3', tdClass: 'px-3', width: '1%' },
    { key: 'paciente', label: 'Paciente' },
    { key: 'procedimento', label: 'Profissional / Procedimento' },
    { key: 'status', label: 'Status Atual' },
    { key: 'acoes', label: 'Ações', thClass: 'text-end', tdClass: 'text-end', width: '1%' }
];

const getRowClass = (item) => {
    return { 'bg-success-subtle': item.ja_chegou };
};

</script>

<template>
    <Layout>
        <Head title="Fila da Recepção" />
        <PageHeader title="Fila da Recepção" pageTitle="Recepção" />

        <div class="row">
            <div class="col-lg-12">
                <SimpleTable
                    title="Pacientes Agendados para Hoje"
                    :items="fila"
                    :columns="tableColumns"
                    :searchable="true"
                    searchPlaceholder="Buscar paciente, cpf ou médico..."
                    :searchFields="['paciente', 'cpf', 'medico']"
                    emptyTitle="Nenhum paciente encontrado"
                    emptyMessage="Não há pacientes agendados para hoje ou com pagamentos confirmados."
                    emptyIcon="ri-calendar-event-line"
                    :rowClass="getRowClass"
                >
                    <template #cell(hora)="{ item }">
                        <div class="fw-medium text-dark"><i class="ri-time-line text-muted me-1"></i> {{ item.hora }}</div>
                    </template>
                    
                    <template #cell(paciente)="{ item }">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-xs">
                                    <div class="avatar-title rounded-circle shadow" :class="getRandomColorClass(item.paciente)">
                                        {{ getInitials(item.paciente) }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-2 name">
                                <h5 class="fs-14 mb-0 text-dark">{{ item.paciente }}</h5>
                                <p class="text-muted mb-0 fs-12" v-if="item.cpf">CPF: {{ item.cpf }}</p>
                            </div>
                        </div>
                    </template>
                    
                    <template #cell(procedimento)="{ item }">
                        <div class="d-flex flex-column">
                            <span class="fw-medium text-dark">Dr(a). {{ item.medico }}</span>
                            <span class="text-muted fs-12">{{ item.procedimento }}</span>
                        </div>
                    </template>
                    
                    <template #cell(status)="{ item }">
                        <span class="badge px-2 py-1 fs-12" 
                              :class="{
                                  'bg-success-subtle text-success': item.ja_chegou,
                                  'bg-warning-subtle text-warning': !item.ja_chegou,
                              }">
                            <i class="mdi mdi-circle-medium"></i> {{ item.status }}
                        </span>
                    </template>
                    
                    <template #cell(acoes)="{ item }">
                        <button v-if="!item.ja_chegou" 
                                @click="confirmarPresenca(item.id)" 
                                class="btn btn-sm btn-primary shadow-sm"
                                :disabled="processingIds.includes(item.id)">
                            <span v-if="processingIds.includes(item.id)" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            <i v-else class="ri-check-line align-bottom me-1"></i> Confirmar Presença
                        </button>
                        <div v-else class="d-flex align-items-center justify-content-end gap-2">
                            <span class="text-success fw-medium fs-13">
                                <i class="ri-checkbox-circle-fill align-middle me-1"></i> Presente
                            </span>
                            <button @click="openCancelModal(item.id)" 
                                    class="btn btn-sm btn-soft-danger shadow-sm"
                                    title="Cancelar presença">
                                <i class="ri-close-line align-bottom"></i>
                            </button>
                        </div>
                    </template>
                </SimpleTable>
            </div>
        </div>

        <Modal v-model="showCancelModal" title="Cancelar Presença" name-button="Sim, Cancelar" :processing="isCanceling" size="md" @save="confirmarCancelamento">
            <div class="text-center p-3">
                <i class="ri-error-warning-line display-5 text-danger mb-3"></i>
                <h5 class="fs-16">Deseja realmente cancelar a presença deste paciente?</h5>
                <p class="text-muted mb-0">Esta ação removerá o paciente da fila do médico. Ele voltará ao status de aguardando na recepção.</p>
            </div>
        </Modal>
    </Layout>
</template>
