<template>
  <Teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-2 sm:px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div
          class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
          @click="onClose"
        ></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>

        <div
          :class="`inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full mx-2 sm:mx-0 ${sizeClasses[size]}`"
        >
          <div class="bg-white px-3 sm:px-4 lg:px-6 pt-4 sm:pt-5 pb-4 sm:pb-6">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
              <h3 class="text-base sm:text-lg font-medium text-gray-900">{{ title }}</h3>
              <button
                @click="onClose"
                class="text-gray-400 hover:text-gray-500 focus:outline-none touch-manipulation p-1"
                aria-label="Close"
              >
                <X class="w-5 h-5 sm:w-6 sm:h-6" />
              </button>
            </div>
            <div class="max-h-[70vh] sm:max-h-none overflow-y-auto">
              <slot />
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  onClose: {
    type: Function,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
  }
})

const sizeClasses = {
  sm: 'max-w-md',
  md: 'max-w-lg',
  lg: 'max-w-2xl',
  xl: 'max-w-4xl',
}
</script>
