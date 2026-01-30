<template>
  <div class="mb-4">
    <label v-if="label" :for="name" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>
    <input
      :type="type"
      :id="name"
      :name="name"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :placeholder="placeholder"
      :class="inputClasses"
    />
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: String,
  name: String,
  type: {
    type: String,
    default: 'text'
  },
  modelValue: [String, Number],
  error: String,
  required: {
    type: Boolean,
    default: false
  },
  placeholder: String,
  className: {
    type: String,
    default: ''
  }
})

defineEmits(['update:modelValue'])

const inputClasses = computed(() => {
  const base = 'input-field'
  const errorClass = props.error ? 'border-red-500 focus:ring-red-500' : ''
  return `${base} ${errorClass} ${props.className}`
})
</script>
