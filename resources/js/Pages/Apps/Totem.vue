<template>
    <Head title="Totem de Autoatendimento" />
    <div class="totem-wrapper bg-light min-vh-100 d-flex flex-column position-relative">
        
        <!-- HEADER (Claro e Institucional) -->
        <header class="p-4 bg-white border-bottom shadow-sm d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="logo-circle bg-primary-subtle d-flex justify-content-center align-items-center rounded-circle" style="width: 60px; height: 60px;">
                    <i class="ri-hospital-line text-primary display-5"></i>
                </div>
                <div>
                    <h1 class="text-primary m-0 fw-bold display-6">Clínica Velzon</h1>
                    <span class="text-muted fs-5 fw-medium" v-if="totem">Totem: {{ totem.nome }}</span>
                </div>
            </div>
            <div class="text-end text-dark">
                <div class="fs-4 text-muted">{{ currentDate }}</div>
                <div class="display-6 fw-bold text-primary">{{ currentTime }}</div>
            </div>
        </header>

        <!-- MAIN CONTENT -->
        <div class="flex-grow-1 d-flex flex-column justify-content-center align-items-center px-4 py-5">
            
            <div class="w-100 mx-auto" style="max-width: 1100px;">
                <h2 class="display-4 text-dark mb-2 fw-bolder text-center">Bem-vindo(a)!</h2>
                <p class="text-muted fs-3 mb-5 text-center">Toque na opção desejada para emitir a sua senha de atendimento:</p>
                
                <div class="row g-4 justify-content-center">
                    
                    <div class="col-md-6 col-lg-6" v-for="(opcao, index) in opcoes" :key="opcao.id">
                        <button class="btn btn-white w-100 p-5 rounded-4 d-flex align-items-center text-start position-relative shadow-sm hover-elevate border" @click="emitTicket(opcao)" :style="{ borderColor: (opcao.cor || '#0ab39c') + ' !important' }">
                            
                            <!-- Icon logic: use user defined icon or base it on index -->
                            <div class="btn-icon-wrapper rounded-circle d-flex justify-content-center align-items-center me-4" :style="{ backgroundColor: (opcao.cor || '#0ab39c') + '15', color: opcao.cor || '#0ab39c' }">
                                <i :class="opcao.icone || getIconForIndex(index)"></i>
                            </div>
                            
                            <div class="d-flex flex-column">
                                <span class="fs-1 fw-bold mb-1" :style="{ color: opcao.cor || '#0ab39c' }">{{ opcao.nome }}</span>
                            </div>
                        </button>
                    </div>

                    <!-- Mensagem caso não tenha opções -->
                    <div class="col-12 text-center py-5" v-if="!opcoes.length">
                        <i class="ri-error-warning-line display-1 text-muted mb-3"></i>
                        <h3 class="text-muted">Nenhuma opção de atendimento configurada para este Totem.</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <footer class="p-4 bg-white border-top text-center mt-auto">
            <p class="text-muted mb-0 fs-5"><i class="ri-information-line me-2"></i> Em caso de dúvidas, dirija-se à recepção principal.</p>
        </footer>

        <!-- TICKET MODAL -->
        <div class="modal fade show" tabindex="-1" :class="{ 'd-block': showTicketModal }" style="background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(5px);" v-if="showTicketModal">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
                <div class="modal-content ticket-receipt text-center border-0 p-0 shadow-lg">
                    <div class="ticket-header bg-white border-bottom p-4">
                        <h3 class="fw-bold mb-0 text-dark">Clínica Velzon</h3>
                        <p class="text-muted m-0">Comprovante de Senha</p>
                    </div>
                    <div class="ticket-body p-5 bg-white">
                        <p class="text-uppercase text-muted fw-bold mb-2 fs-5">Senha</p>
                        <h1 class="ticket-number text-dark fw-bolder mb-4 display-1">{{ currentTicket }}</h1>
                        
                        <div class="border-top border-bottom py-3 mb-4 text-start d-flex justify-content-between">
                            <div>
                                <small class="text-muted d-block text-uppercase">Atendimento</small>
                                <strong class="fs-5 text-dark">{{ currentTicketType }}</strong>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block text-uppercase">Horário</small>
                                <strong class="fs-5 text-dark">{{ currentTime }}</strong>
                            </div>
                        </div>
                        
                        <p class="fs-4 text-secondary m-0">
                            Aguarde ser chamado no <strong class="text-dark">Painel Eletrônico</strong>.
                        </p>
                    </div>
                    <div class="ticket-footer bg-light p-3 border-top">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" :style="{ width: progressWidth + '%' }"></div>
                        </div>
                        <p class="text-muted mt-2 mb-0 fw-semibold">Retire seu papel abaixo...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
const totem = computed(() => page.props.totem || null);
const opcoes = computed(() => (totem.value && totem.value.opcoes) ? totem.value.opcoes : []);

const showTicketModal = ref(false);
const currentTicket = ref('');
const currentTicketType = ref('');
const progressWidth = ref(0);

const currentTime = ref('');
const currentDate = ref('');
let clockInterval = null;

const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    currentDate.value = now.toLocaleDateString('pt-BR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
};

onMounted(() => {
    updateTime();
    clockInterval = setInterval(updateTime, 1000);
});

onUnmounted(() => {
    if (clockInterval) clearInterval(clockInterval);
});

const getIconForIndex = (index) => {
    const icons = [
        'ri-user-smile-line',
        'ri-wheelchair-line',
        'ri-test-tube-line',
        'ri-folder-received-line',
        'ri-file-list-3-line',
        'ri-stethoscope-line'
    ];
    return icons[index % icons.length];
};

const emitTicket = (opcao) => {
    const prefix = opcao.codigo || 'T';
    const number = Math.floor(Math.random() * 900) + 100;
    
    currentTicket.value = `${prefix}-${number}`;
    currentTicketType.value = opcao.nome;
    showTicketModal.value = true;
    progressWidth.value = 0;

    const progressInterval = setInterval(() => {
        progressWidth.value += 5;
    }, 200);

    setTimeout(() => {
        clearInterval(progressInterval);
        showTicketModal.value = false;
        currentTicket.value = '';
    }, 4000);
};
</script>

<style scoped>
.totem-wrapper {
    font-family: 'Inter', sans-serif;
    background-color: #f3f6f9; /* Velzon body bg */
}

.btn-white {
    background-color: #ffffff;
    border-color: #e2e5e8;
    color: #212529;
    transition: all 0.3s ease;
}

.hover-elevate:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
    border-color: #405189 !important; /* Velzon Primary */
}

.hover-elevate:active {
    transform: translateY(-2px);
}

.btn-icon-wrapper {
    width: 80px;
    height: 80px;
}

.btn-icon-wrapper i {
    font-size: 3rem;
}

/* TICKET MODAL */
.ticket-receipt {
    border-radius: 10px;
    overflow: hidden;
    transform-origin: top center;
    animation: ticketSlideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}

@keyframes ticketSlideIn {
    0% {
        opacity: 0;
        transform: translateY(-50px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
