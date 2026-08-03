<template>
    <Teleport to="body">
        <div v-show="modelValue" class="offcanvas-backdrop fade" :class="{'show': showClass}" :style="{ zIndex: backdropZIndex }" @click="!disableClose ? $emit('update:modelValue', false) : null"></div>
        <div class="offcanvas offcanvas-end" :class="{'show': showClass}" tabindex="-1" :style="{ visibility: modelValue || showClass ? 'visible' : 'hidden', zIndex: zIndex, width: customWidth || (size === 'lg' ? '600px' : size === 'xl' ? '800px' : '400px') }" aria-labelledby="offcanvasRightLabel">
            <div class="offcanvas-header bg-light border-bottom">
                <h5 id="offcanvasRightLabel" class="offcanvas-title">{{ title }}</h5>
                <button type="button" class="btn-close text-reset" :disabled="processing || disableClose" @click="$emit('update:modelValue', false)"></button>
            </div>
            <div class="offcanvas-body">
                <slot></slot>
            </div>
            <div class="offcanvas-footer border-top p-3 bg-light d-flex justify-content-between">
                <div class="me-auto">
                    <slot name="extraFooterLeft"></slot>
                </div>
                <div class="d-flex">
                    <button type="button" class="btn btn-light" :disabled="processing || disableClose" @click="$emit('update:modelValue', false)">Cancelar</button>
                    <button type="button" class="btn btn-success ms-2" :disabled="processing || disableClose" @click="$emit('save')">
                        <span v-if="processing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        {{ nameButton }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, watch, nextTick, onBeforeUnmount } from 'vue'

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    title: { type: String, default: 'Título Padrão' },
    nameButton: { type: String, default: 'Salvar' },
    size: { type: String, default: 'md' }, // md, lg, xl
    customWidth: { type: String, default: '' },
    processing: { type: Boolean, default: false },
    disableClose: { type: Boolean, default: false },
    zIndex: { type: Number, default: 1045 },
    backdropZIndex: { type: Number, default: 1040 },
});

defineEmits(['update:modelValue', 'save']);

const showClass = ref(false);

const disableScroll = () => { document.body.style.overflow = 'hidden'; };
const enableScroll = () => { document.body.style.overflow = ''; };

watch(() => props.modelValue, async (val) => {
    try {
        if (val) {
            disableScroll();
            await nextTick();
            setTimeout(() => { showClass.value = true; }, 10);
        } else {
            enableScroll();
            showClass.value = false;
        }
    } catch (e) {
        console.error("Erro no watcher do Offcanvas", e);
    }
});

onBeforeUnmount(() => {
    enableScroll();
});
</script>

<style>
.offcanvas-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.5);
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
    z-index: 9990 !important;
}
.offcanvas-backdrop.show {
    opacity: 1 !important;
    backdrop-filter: blur(5px);
}
.offcanvas {
    transition: transform 0.3s ease-in-out;
    background-color: #fff;
    z-index: 9999 !important;
}
.offcanvas-footer {
    position: sticky;
    bottom: 0;
    z-index: 10;
}
</style>
