<template>
  <div class="mb-4">
    <label v-if="label" :for="name" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>
    <select
      :id="name"
      :name="name"
      :value="modelValue"
      @change="$emit('update:modelValue', $event.target.value)"
      :class="selectClasses"
    >
      <option value="">{{ placeholder }}</option>
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: String,
  name: String,
  modelValue: [String, Number],
  options: {
    type: Array,
    default: () => []
  },
  error: String,
  required: {
    type: Boolean,
    default: false
  },
  placeholder: {
    type: String,
    default: 'Select...'
  },
  className: {
    type: String,
    default: ''
  }
})

defineEmits(['update:modelValue'])

const selectClasses = computed(() => {
  const base = 'input-field'
  const errorClass = props.error ? 'border-red-500 focus:ring-red-500' : ''
  return `${base} ${errorClass} ${props.className}`
})
</script>
