<template>
    <!-- Componente de Modal de Confirmação de Exclusão -->
    <div>
        <div id="myModal" class="modal zoomIn" :class="[showClass]" tabindex="9999" aria-labelledby="myModalLabel"
            aria-hidden="true" :style="{ display: modelValue ? 'block' : 'none' }">

            <!-- Backdrop (fundo escurecido) vem primeiro DENTRO do modal -->
            <div v-show="modelValue" class="modal-backdrop"></div>

            <!-- Conteúdo principal do modal -->
            <div class="modal-dialog modal-dialog-centered modal-md" ref="modalDialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="myModalLabel">{{ title }}</h5>
                        <!-- Botão para fechar o modal -->
                        <button type="button" class="btn-close" @click="$emit('update:modelValue', false)"></button>
                    </div>
                    <div class="modal-body text-center p-3">
                        <!-- Espaço para animação e mensagem de confirmação -->
                        <div
                            style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <!-- Animação Lottie -->
                            <Lottie :options="lottieOptions" :height="100" :width="100" />

                            <div class="mt-4">
                                <h4 class="mb-3">{{ subTitle }}</h4>
                                <p class="text-muted" v-if="itemDelete && itemDelete.nome"> Esta ação não poderá
                                    ser desfeita. "<span class="text-danger fs-5">{{ itemDelete.nome }}</span>" será
                                    removido permanentemente do sistema.</p>

                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <!-- Botão de cancelar -->
                        <button type="button" class="btn btn-light"
                            @click="$emit('update:modelValue', false)">Cancelar</button>
                        <!-- Botão de confirmação de exclusão -->
                        <button type="button" class="btn btn-danger" @click="$emit('save')">{{ nameButton }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// Importação de funções do Vue
import { ref, watch, nextTick } from 'vue';
// Importação do componente de animação Lottie
import Lottie from "@/Components/widgets/lottie.vue";
import animationData from "@/Components/widgets/gsqxdxog.json";

// Definição das propriedades recebidas pelo componente
const props = defineProps({
    // Controla a visibilidade do modal
    modelValue: {
        type: Boolean,
        default: false
    },
    // Título exibido no cabeçalho do modal
    title: {
        type: String,
        default: ''
    },
    // Subtítulo exibido na mensagem de confirmação
    subTitle: {
        type: String,
        default: 'Deseja realmente excluir'
    },
    // Objeto do item a ser excluído
    itemDelete: {
        type: Object,
        default: () => ({})
    },
    // Texto do botão de confirmação
    nameButton: {
        type: String,
        default: 'Sim, desejo excluir'
    }
});

// Classe para animação de exibição
const showClass = ref('');
// Referência ao elemento do dialog
const modalDialog = ref(null);

// Observa mudanças na propriedade modelValue para controlar animações
watch(() => props.modelValue, async (val) => {
    if (val) {
        showClass.value = '';
        await nextTick();
        showClass.value = 'show';
        if (modalDialog.value) {
            modalDialog.value.style.animation = 'zoomIn 0.3s ease';
        }
    } else {
        showClass.value = '';
        if (modalDialog.value) {
            modalDialog.value.style.animation = '';
        }
    }
});

// Opções para animação Lottie
const lottieOptions = {
    animationData: animationData,
    loop: true,
    autoplay: true
};
</script>

<style scoped>
/* Estilização do dialog do modal */
.modal-dialog {
    z-index: 1060;
    /* opcional, se quiser animar o conteúdo separadamente */
}

.modal.zoomIn .modal-dialog {
    animation: zoomIn 0.3s ease;
}

/* Fundo escurecido do modal */
.modal-backdrop {
    position: absolute;
    /* dentro da modal */
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1040;
    background-color: rgba(0, 0, 0, 0.1);
}

.modal-content {
    background-color: #fff;
    opacity: 1 !important;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1050;
    /* acima do backdrop */
    width: 100%;
    height: 100%;
    overflow: auto;
}
</style>
