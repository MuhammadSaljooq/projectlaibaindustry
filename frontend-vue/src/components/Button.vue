<template>
  <button
    :type="type"
    @click="onClick"
    :disabled="disabled"
    :class="buttonClasses"
  >
    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'danger', 'success'].includes(value)
  },
  type: {
    type: String,
    default: 'button'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  className: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['click'])

const onClick = (event) => {
  if (!props.disabled) {
    emit('click', event)
  }
}

const variants = {
  primary: 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500 disabled:bg-gray-400',
  secondary: 'bg-gray-200 text-gray-800 hover:bg-gray-300 focus:ring-gray-500 disabled:bg-gray-100',
  danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 disabled:bg-gray-400',
  success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 disabled:bg-gray-400',
}

const buttonClasses = computed(() => {
  const baseClasses = 'px-4 py-2 rounded-lg font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2'
  return `${baseClasses} ${variants[props.variant]} ${props.className}`
})
</script>
