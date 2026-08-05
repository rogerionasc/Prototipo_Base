<template>
    <Head title="Painel de Senhas" />
    <div class="painel-wrapper vh-100 d-flex flex-column bg-light">
        
        <!-- HEADER -->
        <header class="d-flex justify-content-between align-items-center p-4 bg-white border-bottom shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="logo-circle bg-primary-subtle d-flex justify-content-center align-items-center rounded-circle" style="width: 60px; height: 60px;">
                    <i class="ri-hospital-line text-primary display-5"></i>
                </div>
                <h1 class="text-primary m-0 fw-bold display-5">Clínica Velzon</h1>
            </div>
            <div class="text-end">
                <div class="display-6 fw-bold text-primary">{{ currentTime }}</div>
                <div class="fs-4 text-muted fw-medium">{{ currentDate }}</div>
            </div>
        </header>

        <!-- MAIN -->
        <div class="flex-grow-1 row g-0 p-4 gap-4">
            <!-- CURRENT CALLED TICKET -->
            <div class="col d-flex flex-column">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden" :class="isBlinking ? 'bg-success' : 'bg-white'" style="transition: all 0.5s ease;">
                    <div class="card-header bg-transparent border-bottom-0 pt-5 pb-0 text-center position-relative z-1">
                        <span class="badge fs-3 px-4 py-2 rounded-pill text-uppercase tracking-wide" :class="isBlinking ? 'bg-white text-success' : 'bg-success-subtle text-success'">Senha Atual</span>
                    </div>
                    <div class="card-body bg-transparent d-flex flex-column justify-content-center align-items-center text-center position-relative z-1">
                        
                        <div class="ticket-box text-center mb-4">
                            <h1 class="fw-bolder" :class="isBlinking ? 'text-white' : 'text-dark'" style="font-size: 14rem; line-height: 1; letter-spacing: -5px; transition: color 0.5s ease;">{{ mainTicket.senha }}</h1>
                        </div>

                        <div class="guiche-box px-5 py-4 rounded-4 text-center shadow" :class="isBlinking ? 'bg-white' : 'bg-primary'" style="transition: all 0.5s ease;">
                            <span class="fs-1 fw-bold text-uppercase d-block mb-1" :class="isBlinking ? 'text-success' : 'text-white'">Dirija-se ao</span>
                            <span class="display-4 fw-bolder" :class="isBlinking ? 'text-success' : 'text-white'">{{ mainTicket.guiche }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HISTORY SIDEBAR -->
            <div class="col-auto" style="width: 450px;">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom pt-4 pb-3">
                        <h4 class="m-0 text-uppercase text-muted fw-bold fs-3 text-center"><i class="ri-history-line me-2"></i>Últimas Chamadas</h4>
                    </div>
                    
                    <div class="card-body p-0 bg-white">
                        <ul class="list-group list-group-flush h-100">
                            <li class="list-group-item bg-transparent p-4 border-bottom d-flex justify-content-between align-items-center" v-for="(hist, index) in history" :key="index">
                                <div>
                                    <span class="d-block text-muted text-uppercase fw-semibold mb-1 fs-5">Senha</span>
                                    <span class="d-block fw-bold text-dark" style="font-size: 3rem; line-height: 1;">{{ hist.senha }}</span>
                                </div>
                                <div class="text-end">
                                    <span class="d-block text-muted text-uppercase fw-semibold mb-1 fs-5">Local</span>
                                    <span class="badge bg-light text-primary border border-primary-subtle fs-3 px-3 py-2">{{ hist.guiche }}</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER NEWS TICKER -->
        <footer class="p-3 bg-primary text-white overflow-hidden d-flex align-items-center shadow-lg z-index-1 position-relative">
            <i class="ri-megaphone-line display-6 me-4 ms-2"></i>
            <div class="ticker-wrap flex-grow-1">
                <div class="ticker">
                    <span class="fs-2 fw-medium me-5">Bem-vindo à Clínica Velzon!</span>
                    <span class="fs-2 fw-medium me-5">Acesse o portal do paciente para ver os resultados dos seus exames diretamente de casa.</span>
                    <span class="fs-2 fw-medium me-5">Mantenha seus dados cadastrais atualizados na recepção.</span>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';

const currentTime = ref('');
const currentDate = ref('');
const isBlinking = ref(false);

const mainTicket = ref({ senha: 'P-105', guiche: 'Guichê 03' });

const history = ref([
    { senha: 'G-244', guiche: 'Guichê 01' },
    { senha: 'R-012', guiche: 'Guichê 05' },
    { senha: 'E-088', guiche: 'Guichê 02' },
    { senha: 'G-243', guiche: 'Guichê 04' }
]);

const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    currentDate.value = now.toLocaleDateString('pt-BR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
};

let clockInterval = null;

onMounted(() => {
    updateTime();
    clockInterval = setInterval(updateTime, 1000);

    // Simulate calling a new ticket every 15 seconds
    setInterval(() => {
        // Move current to history
        history.value.unshift({ ...mainTicket.value });
        if (history.value.length > 5) history.value.pop();

        // Generate new
        const types = ['G', 'P', 'E', 'R'];
        const num = Math.floor(Math.random() * 900) + 100;
        const g = Math.floor(Math.random() * 5) + 1;
        mainTicket.value = {
            senha: `${types[Math.floor(Math.random()*types.length)]}-${num}`,
            guiche: `Guichê 0${g}`
        };

        // Trigger blink effect
        isBlinking.value = true;
        // Se houver áudio, tocaria aqui: new Audio('/sounds/ding.mp3').play();
        
        setTimeout(() => {
            isBlinking.value = false;
        }, 4000);
    }, 15000);
});

onUnmounted(() => {
    if (clockInterval) clearInterval(clockInterval);
});

</script>

<style scoped>
.painel-wrapper {
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.tracking-wide {
    letter-spacing: 2px;
}

/* News Ticker Animation */
.ticker-wrap {
    width: 100%;
    overflow: hidden;
    white-space: nowrap;
}
.ticker {
    display: inline-block;
    animation: ticker-anim 25s linear infinite;
}
@keyframes ticker-anim {
    0% { transform: translateX(100vw); }
    100% { transform: translateX(-100%); }
}
</style>
