<template>
  <div
    v-if="isVisible"
    :class="alertClasses"
  >
    <div class="flex items-center">
      <component :is="iconComponent" class="w-5 h-5 mr-3" />
      <p class="flex-1">{{ message }}</p>
      <button v-if="onClose" @click="handleClose" class="ml-4">
        <X class="w-5 h-5" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { X, CheckCircle, AlertCircle, Info, AlertTriangle } from 'lucide-vue-next'

const props = defineProps({
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  },
  message: {
    type: String,
    required: true
  },
  onClose: {
    type: Function,
    default: null
  },
  className: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['close'])

const isVisible = ref(true)

const handleClose = () => {
  isVisible.value = false
  if (props.onClose) {
    props.onClose()
  }
  emit('close')
}

const types = {
  success: {
    bg: 'bg-green-50',
    border: 'border-green-400',
    text: 'text-green-800',
    icon: CheckCircle,
  },
  error: {
    bg: 'bg-red-50',
    border: 'border-red-400',
    text: 'text-red-800',
    icon: AlertCircle,
  },
  warning: {
    bg: 'bg-yellow-50',
    border: 'border-yellow-400',
    text: 'text-yellow-800',
    icon: AlertTriangle,
  },
  info: {
    bg: 'bg-blue-50',
    border: 'border-blue-400',
    text: 'text-blue-800',
    icon: Info,
  },
}

const config = computed(() => types[props.type] || types.info)
const iconComponent = computed(() => config.value.icon)
const alertClasses = computed(() => 
  `${config.value.bg} ${config.value.border} ${config.value.text} border-l-4 p-4 mb-4 rounded ${props.className}`
)
</script>
