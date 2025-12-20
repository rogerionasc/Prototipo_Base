<template>
  <transition name="fade">
    <div
      v-if="visible && currentMessage"
      :class="alertClasses"
      class="alert alert-dismissible alert-label-icon rounded-label fade show material-shadow position-fixed top-0 end-0 m-4 z-50"
      role="alert"
      style="z-index: 9999"
    >
      <i :class="iconClass + ' label-icon'"></i>
      <strong class="me-1">{{ alertTitle }}</strong> {{ currentMessage }}
      <button type="button" class="btn-close" @click="visible = false" aria-label="Close"></button>
    </div>
  </transition>
</template>

  <script setup>
  import { computed, ref, watch, onMounted } from 'vue'
  import { usePage } from '@inertiajs/vue3'

  const page = usePage()

  const currentMessage = ref(null)
  const currentType = ref('info')

  const type = computed(() => currentType.value || 'secondary')

  const visible = ref(false)

  const alertTitle = computed(() => {
    switch (type.value) {
      case 'success':
        return 'Sucesso:'
      case 'danger':
        return 'Erro:'
      case 'warning':
        return 'Atenção:'
      case 'info':
        return 'Informação:'
      default:
        return ''
    }
  })

  const iconClass = computed(() => {
    switch (type.value) {
      case 'success':
        return 'ri-checkbox-circle-line'
      case 'danger':
        return 'ri-close-circle-line'
      case 'warning':
        return 'ri-alert-line'
      case 'info':
        return 'ri-information-line'
      default:
        return 'ri-information-line'
    }
  })

  const alertClasses = computed(() => {
    return [
      `alert-${type.value}`
    ]
  })

  const triggerShow = () => {
    visible.value = true
    setTimeout(() => {
      visible.value = false
    }, 6000)
  }

  const pickMessage = () => {
    if (page.props.flash.success) {
      currentType.value = 'success'
      currentMessage.value = page.props.flash.success
      triggerShow()
      return
    }
    if (page.props.flash.error) {
      currentType.value = 'danger'
      currentMessage.value = page.props.flash.error
      triggerShow()
      return
    }
    if (page.props.flash.warning) {
      currentType.value = 'warning'
      currentMessage.value = page.props.flash.warning
      triggerShow()
      return
    }
    if (page.props.flash.info) {
      currentType.value = 'info'
      currentMessage.value = page.props.flash.info
      triggerShow()
      return
    }
  }

  onMounted(() => {
    pickMessage()
  })

  watch(() => page.props.flash, () => {
    pickMessage()
  }, { deep: true })
  </script>

  <style scoped>
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.3s ease;
  }
  .fade-enter-from,
  .fade-leave-to {
    opacity: 0;
  }
  </style>
