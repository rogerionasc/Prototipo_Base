<template>
    <!-- Componente de Modal reutilizável -->
    <Teleport to="body">
        <div v-show="modelValue" class="modal-backdrop" :style="{ zIndex: backdropZIndex }"></div>
        <div id="myModal" class="modal zoomIn" :class="[showClass]" tabindex="9999"
            aria-labelledby="myModalLabel" role="dialog" :aria-modal="modelValue ? 'true' : 'false'" :aria-hidden="modelValue ? 'false' : 'true'"
            :style="{ display: modelValue ? 'block' : 'none', zIndex: zIndex }">

            <!-- Conteúdo principal do modal -->
            <div :class="['modal-dialog','modal-dialog-centered', size ? `modal-${size}` : null]" ref="modalDialog" :style="customWidth ? { maxWidth: customWidth } : {}">
            <div class="modal-content">
              <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="myModalLabel">{{ title }}</h5>
                <!-- Botão para fechar o modal -->
                <button type="button" class="btn-close" :disabled="processing || disableClose" @click="$emit('update:modelValue', false)"></button>
              </div>
              <div class="modal-body">
                <!-- Espaço para inserir conteúdo personalizado -->
                <slot></slot>
              </div>
              <div class="modal-footer" v-if="showFooter">
                <div class="me-auto">
                  <slot name="extraFooterLeft"></slot>
                </div>
                <div class="d-flex">
                  <button type="button" class="btn btn-light" :disabled="processing || disableClose" @click="$emit('update:modelValue', false)">{{ cancelText }}</button>
                  <button v-if="showSave" type="button" class="btn btn-success ms-2" :disabled="processing || disableClose" @click="$emit('save')">
                    <span v-if="processing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    {{ nameButton }}
                  </button>
                </div>
              </div>
            </div>
          </div>
      </div>
    </Teleport>
</template>

<script setup>
// Importação de funções do Vue
import { ref, watch, nextTick, onBeforeUnmount } from 'vue'

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
        default: 'Título Padrão'
    },
    // Texto do botão de ação principal
    nameButton: {
        type: String,
        default: 'Salvar'
    },
    size: {
        type: String,
        default: 'lg'
    },
    customWidth: {
        type: String,
        default: ''
    },
    processing: {
        type: Boolean,
        default: false
    },
    disableClose: {
        type: Boolean,
        default: false
    },
    zIndex: {
        type: Number,
        default: 1050
    },
    backdropZIndex: {
        type: Number,
        default: 1040
    },
    showFooter: {
        type: Boolean,
        default: true
    },
    showSave: {
        type: Boolean,
        default: true
    },
    cancelText: {
        type: String,
        default: 'Cancelar'
    }
});

// Declara os eventos que o componente emite
defineEmits(['update:modelValue', 'save']);

// Classe para animação de exibição
const showClass = ref('');
// Referência ao elemento do dialog
const modalDialog = ref(null);

// Função para desabilitar a barra de rolagem
const disableScroll = () => {
    document.body.style.overflow = 'hidden';
};

// Função para habilitar a barra de rolagem
const enableScroll = () => {
    document.body.style.overflow = '';
};

// Observa mudanças na propriedade modelValue para controlar animações e a barra de rolagem
watch(() => props.modelValue, async (val) => {
    if (val) {
        // Modal está abrindo
        disableScroll(); // Desabilita a barra de rolagem
        showClass.value = '';
        await nextTick();
        showClass.value = 'show';
        if (modalDialog.value) {
            modalDialog.value.style.animation = 'zoomIn 0.3s ease';
        }
    } else {
        // Modal está fechando
        enableScroll(); // Habilita a barra de rolagem
        showClass.value = '';
        if (modalDialog.value) {
            modalDialog.value.style.animation = '';
        }
    }
});

// Garantir que a barra de rolagem seja restaurada se o componente for destruído
onBeforeUnmount(() => {
    enableScroll();
});
</script>

<style scoped>
/* Estilização do dialog do modal */
.modal-dialog {
    position: relative;
    z-index: 1060; /* opcional, se quiser animar o conteúdo separadamente */
}

.modal.zoomIn .modal-dialog {
    animation: zoomIn 0.3s ease;
}

/* Fundo escurecido do modal */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 1040;
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: #fff;
    opacity: 1 !important;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1050; /* acima do backdrop */
    width: 100vw;
    height: 100vh;
    overflow: auto;
}


</style>
