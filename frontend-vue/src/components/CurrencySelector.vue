<template>
  <div class="relative w-full">
    <div v-if="loading || !selectedCurrency || !currencies.length" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-500 bg-gray-50">
      Loading currencies...
    </div>
    <div v-else class="relative">
      <DollarSign class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
      <select
        :value="selectedCurrency.id"
        @change="handleChange"
        class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none cursor-pointer"
      >
        <option v-for="currency in currencies" :key="currency.id" :value="currency.id">
          {{ currency.code }} - {{ currency.name }} ({{ currency.symbol }})
          <span v-if="currency.isDefault"> [Default]</span>
        </option>
      </select>
      <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </div>
    
    <div v-if="selectedCurrency && !loading" class="mt-2 text-xs text-gray-500">
      Selected: <span class="font-medium">{{ selectedCurrency.name }}</span> ({{ selectedCurrency.code }})
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { DollarSign } from 'lucide-vue-next'
import { useCurrencyStore } from '../stores/currency'

const currencyStore = useCurrencyStore()

const currencies = computed(() => currencyStore.currencies)
const selectedCurrency = computed(() => currencyStore.selectedCurrency)
const loading = computed(() => currencyStore.loading)

const handleChange = (e) => {
  const currencyId = parseInt(e.target.value)
  const currency = currencies.value.find(c => c.id === currencyId)
  if (currency) {
    currencyStore.changeCurrency(currency)
  }
}
</script>
