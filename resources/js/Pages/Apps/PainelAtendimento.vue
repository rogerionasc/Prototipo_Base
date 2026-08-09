<template>
    <Head title="Painel de Senhas" />
    <div class="painel-wrapper vh-100 d-flex flex-column bg-light overflow-hidden">
        
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
                    <!-- Removed card header with Paciente Chamado -->
                    <div class="card-body bg-transparent d-flex flex-column justify-content-center align-items-center text-center position-relative z-1">
                        
                        <div class="ticket-box text-center mb-4">
                            <h1 class="fw-bolder" :class="isBlinking ? 'text-white' : 'text-dark'" style="font-size: 7rem; line-height: 1.1; letter-spacing: -2px; transition: color 0.5s ease;">{{ mainTicket.paciente }}</h1>
                        </div>

                        <div v-if="mainTicket.id" class="guiche-box px-5 py-4 rounded-4 text-center shadow" :class="isBlinking ? 'bg-white' : 'bg-primary'" style="transition: all 0.5s ease;">
                            <span class="fs-1 fw-bold text-uppercase d-block mb-1" :class="isBlinking ? 'text-success' : 'text-white'">Dirija-se ao</span>
                            <span class="display-3 fw-bolder mb-2 d-block" :class="isBlinking ? 'text-success' : 'text-white'">{{ mainTicket.local }}</span>
                            <span class="fs-2 fw-medium d-block" :class="isBlinking ? 'text-success' : 'text-white'"><i class="ri-stethoscope-line me-2"></i>{{ mainTicket.medico }}</span>
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
                            <li class="list-group-item bg-transparent p-3 border-bottom" v-for="(hist, index) in history" :key="index">
                                <div class="d-flex justify-content-between align-items-center w-100">
                                    <div class="me-3 text-truncate" style="max-width: 65%;">
                                        <span class="d-block fw-bold text-dark fs-3 text-truncate">{{ hist.paciente }}</span>
                                        <span class="d-block text-muted mt-1 fs-5 text-truncate"><i class="ri-stethoscope-line me-1"></i>{{ hist.medico }}</span>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <span class="badge bg-light text-primary border border-primary-subtle fs-4 px-3 py-2 shadow-sm">{{ hist.local }}</span>
                                    </div>
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

const mainTicket = ref({ id: null, paciente: 'Aguardando...', local: '-', medico: '-' });
const history = ref([]);
const lastFetchTime = ref(null);
let pollInterval = null;

const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    currentDate.value = now.toLocaleDateString('pt-BR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
};

let speechVoices = [];
if ('speechSynthesis' in window) {
    speechVoices = window.speechSynthesis.getVoices();
    window.speechSynthesis.onvoiceschanged = () => {
        speechVoices = window.speechSynthesis.getVoices();
    };
}

const speakCall = (paciente, local) => {
    if ('speechSynthesis' in window) {
        const msg = new SpeechSynthesisUtterance(`Paciente ${paciente}, favor dirigir-se ao ${local}`);
        msg.lang = 'pt-BR';
        msg.rate = 0.9;
        
        // Tenta encontrar uma voz feminina (Maria no Windows, Luciana no Mac, ou voz do Google)
        const femaleVoice = speechVoices.find(v => v.lang.includes('pt') && (v.name.includes('Maria') || v.name.includes('Luciana') || v.name.includes('Feminina') || v.name.includes('Female') || v.name.includes('Google')))
                         || speechVoices.find(v => v.lang.includes('pt-BR'));
                         
        if (femaleVoice) {
            msg.voice = femaleVoice;
        }

        window.speechSynthesis.speak(msg);
    }
};

const fetchPainelData = async () => {
    try {
        const response = await window.axios.get('/app/painel/data');
        const atendimentos = response.data;
        
        if (atendimentos.length > 0) {
            const current = atendimentos[0];
            const isInitial = lastFetchTime.value === null;
            
            // Check if the current called patient is NEW or was called again
            const isNewCall = !isInitial && (mainTicket.value.id !== current.id || new Date(current.updated_at).getTime() > new Date(lastFetchTime.value).getTime());
            
            if (isInitial) {
                mainTicket.value = current;
                lastFetchTime.value = current.updated_at;
            } else if (isNewCall) {
                mainTicket.value = current;
                lastFetchTime.value = current.updated_at;

                // Trigger blink effect and voice announcement
                isBlinking.value = true;
                
                // Play notification sound if needed, then speak
                speakCall(current.paciente, current.local);
                
                setTimeout(() => {
                    isBlinking.value = false;
                }, 4000);
            }
            
            // Always sync history from server data (excluding the current one)
            if (atendimentos.length > 1) {
                history.value = atendimentos.slice(1);
            } else {
                history.value = [];
            }
        } else {
            mainTicket.value = { id: null, paciente: 'Aguardando...', local: '-', medico: '-' };
            history.value = [];
        }
    } catch (error) {
        console.error("Erro ao buscar dados do painel", error);
    }
};

let clockInterval = null;

onMounted(() => {
    document.body.style.overflow = 'hidden';
    
    updateTime();
    clockInterval = setInterval(updateTime, 1000);

    fetchPainelData();
    pollInterval = setInterval(fetchPainelData, 3000); // Check every 3 seconds
});

onUnmounted(() => {
    document.body.style.overflow = 'auto';
    if (clockInterval) clearInterval(clockInterval);
    if (pollInterval) clearInterval(pollInterval);
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
