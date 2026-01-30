<template>
  <div>
    <div v-if="loading || currencyLoading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      <p class="mt-4 text-gray-600">Loading settings...</p>
    </div>

    <div v-else>
      <div class="flex items-center mb-6">
        <SettingsIcon class="w-8 h-8 text-primary-600 mr-3" />
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $t('settings.title') }}</h1>
      </div>

      <Alert
        v-if="alert"
        :type="alert.type"
        :message="alert.message"
        :onClose="() => alert = null"
      />

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Tax Settings -->
        <div class="card">
          <h2 class="text-lg sm:text-xl font-semibold mb-4">{{ $t('settings.taxSettings') }}</h2>
          <form @submit.prevent="handleSubmit">
            <Input
              :label="$t('settings.defaultTaxRate')"
              name="defaultRate"
              type="number"
              step="0.01"
              min="0"
              max="100"
              v-model="formData.defaultRate"
              required
              :error="errors.defaultRate"
            />

            <Input
              :label="$t('common.description')"
              name="description"
              v-model="formData.description"
              :error="errors.description"
            />

            <div class="flex justify-end mt-6">
              <Button type="submit" variant="primary">
                {{ $t('settings.updateTaxRate') }}
              </Button>
            </div>
          </form>
        </div>

        <!-- Currency Settings -->
        <div class="card">
          <h2 class="text-lg sm:text-xl font-semibold mb-4">{{ $t('settings.currencySettings') }}</h2>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ $t('settings.defaultCurrency') }}
              </label>
              <div v-if="currencyLoading" class="text-sm text-gray-500">Loading...</div>
              <div v-else-if="defaultCurrency" class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-medium text-gray-900">{{ defaultCurrency.name }}</div>
                    <div class="text-sm text-gray-500">
                      {{ defaultCurrency.code }} - {{ defaultCurrency.symbol }}
                    </div>
                  </div>
                  <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">
                    {{ $t('currency.isDefault') }}
                  </span>
                </div>
              </div>
              <div v-else class="text-sm text-red-500">Default currency not available</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ $t('settings.selectCurrency') }}
              </label>
              <div v-if="currencyLoading" class="text-sm text-gray-500">Loading currencies...</div>
              <div v-else-if="selectedCurrency">
                <CurrencySelector />
                <div class="mt-2 text-sm text-gray-600">
                  {{ $t('common.current') }}: {{ selectedCurrency.name }} ({{ selectedCurrency.code }})
                </div>
              </div>
              <div v-else class="text-sm text-red-500">Failed to load currencies</div>
            </div>
          </div>
        </div>

        <!-- Language Settings -->
        <div class="card">
          <h2 class="text-lg sm:text-xl font-semibold mb-4">{{ $t('settings.languageSettings') }}</h2>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ $t('settings.selectLanguage') }}
              </label>
              <div class="flex items-center space-x-2">
                <LanguageSelector />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { Settings as SettingsIcon } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { useCurrencyStore } from '../stores/currency'
import api from '../utils/api'
import Button from '../components/Button.vue'
import Input from '../components/Input.vue'
import Alert from '../components/Alert.vue'
import CurrencySelector from '../components/CurrencySelector.vue'
import LanguageSelector from '../components/LanguageSelector.vue'

const { t } = useI18n()
const currencyStore = useCurrencyStore()

const taxSetting = ref(null)
const loading = ref(true)
const alert = ref(null)
const formData = reactive({
  defaultRate: '0',
  description: '',
})
const errors = ref({})

const selectedCurrency = computed(() => currencyStore.selectedCurrency)
const defaultCurrency = computed(() => currencyStore.defaultCurrency)
const currencyLoading = computed(() => currencyStore.loading)

onMounted(() => {
  fetchTaxSetting()
})

const fetchTaxSetting = async () => {
  try {
    loading.value = true
    const response = await api.get('/tax')
    taxSetting.value = response.data
    formData.defaultRate = response.data.defaultRate.toString()
    formData.description = response.data.description || ''
    alert.value = null
  } catch (error) {
    console.error('Failed to fetch tax settings:', error)
    alert.value = { 
      type: 'error', 
      message: error.response?.data?.error || 'Failed to fetch tax settings. Please check if the backend server is running.' 
    }
    formData.defaultRate = '0'
    formData.description = ''
  } finally {
    loading.value = false
  }
}

const handleSubmit = async () => {
  errors.value = {}

  if (!taxSetting.value || !taxSetting.value.id) {
    alert.value = { type: 'error', message: 'Tax settings not loaded. Please refresh the page.' }
    return
  }

  try {
    const response = await api.put(`/tax/${taxSetting.value.id}`, formData)
    taxSetting.value = response.data
    alert.value = { type: 'success', message: 'Tax settings updated successfully' }
  } catch (error) {
    if (error.response?.data?.errors) {
      const validationErrors = {}
      error.response.data.errors.forEach(err => {
        validationErrors[err.param] = err.msg
      })
      errors.value = validationErrors
    } else {
      alert.value = { type: 'error', message: error.response?.data?.error || 'Failed to update settings. Please check if the backend server is running.' }
    }
  }
}
</script>

<style scoped>
.card {
  @apply bg-white rounded-lg shadow p-6;
}
</style>
