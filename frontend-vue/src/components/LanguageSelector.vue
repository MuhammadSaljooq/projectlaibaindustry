<template>
  <div class="relative w-full">
    <button
      @click="isOpen = !isOpen"
      class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-colors bg-white"
    >
      <div class="flex items-center space-x-2">
        <Globe class="w-4 h-4" />
        <span class="text-lg">{{ currentLanguage.flag }}</span>
        <span>{{ currentLanguage.name }}</span>
      </div>
      <ChevronDown class="w-4 h-4" />
    </button>

    <div v-if="isOpen">
      <div
        class="fixed inset-0 z-10"
        @click="isOpen = false"
      />
      <div class="absolute left-0 right-0 mt-2 w-full bg-white rounded-md shadow-lg z-20 border border-gray-200">
        <div class="py-1">
          <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase border-b sticky top-0 bg-white">
            Select Language
          </div>
          <button
            v-for="language in languages"
            :key="language.code"
            @click="changeLanguage(language.code)"
            :class="`w-full text-left px-4 py-2 text-sm hover:bg-gray-100 transition-colors flex items-center space-x-2 ${
              currentLanguage.code === language.code ? 'bg-blue-50 text-blue-700' : 'text-gray-700'
            }`"
          >
            <span class="text-lg">{{ language.flag }}</span>
            <span>{{ language.name }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Globe, ChevronDown } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'

const { locale } = useI18n()
const isOpen = ref(false)

const languages = [
  { code: 'en', name: 'English', flag: '🇺🇸' },
  { code: 'es', name: 'Español', flag: '🇪🇸' },
  { code: 'fr', name: 'Français', flag: '🇫🇷' },
  { code: 'ar', name: 'العربية', flag: '🇸🇦' },
  { code: 'ur', name: 'اردو', flag: '🇵🇰' },
]

const currentLanguage = computed(() => {
  return languages.find(lang => lang.code === locale.value) || languages[0]
})

const changeLanguage = (langCode) => {
  locale.value = langCode
  localStorage.setItem('language', langCode)
  isOpen.value = false
}
</script>
