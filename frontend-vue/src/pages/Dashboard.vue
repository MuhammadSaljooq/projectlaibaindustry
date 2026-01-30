<template>
  <div class="min-h-screen bg-gray-100">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">{{ $t('dashboard.totalSales') }}</h3>
        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(dashboardData.totalSales) }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">{{ $t('dashboard.totalProfit') }}</h3>
        <p class="text-2xl font-bold text-green-600">{{ formatCurrency(dashboardData.totalProfit) }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">{{ $t('dashboard.inventoryValue') }}</h3>
        <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(dashboardData.totalInventoryValue) }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">{{ $t('dashboard.lowStockItems') }}</h3>
        <p class="text-2xl font-bold text-red-600">{{ dashboardData.lowStockCount }}</p>
      </div>
    </div>

    <div v-if="loading" class="text-center py-8">
      <p>{{ $t('common.loading') }}</p>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">{{ $t('dashboard.salesTrend') }}</h2>
        <div class="h-64">
          <!-- Chart component would go here -->
          <p class="text-gray-500 text-center py-20">{{ $t('common.noData') }}</p>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">{{ $t('dashboard.quickStats') }}</h2>
        <div class="space-y-4">
          <div class="flex justify-between">
            <span class="text-gray-600">{{ $t('dashboard.totalProducts') }}</span>
            <span class="font-semibold">{{ dashboardData.totalProducts }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">{{ $t('dashboard.recentSales') }}</span>
            <span class="font-semibold">{{ dashboardData.recentSalesCount }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useCurrencyStore } from '../stores/currency'
import api from '../utils/api'

const { t } = useI18n()
const currencyStore = useCurrencyStore()

const loading = ref(true)
const dashboardData = ref({
  totalSales: 0,
  totalProfit: 0,
  totalInventoryValue: 0,
  lowStockCount: 0,
  totalProducts: 0,
  recentSalesCount: 0
})

const formatCurrency = (amount) => {
  return currencyStore.formatCurrency(amount)
}

const fetchDashboardData = async () => {
  try {
    loading.value = true
    const response = await api.get('/analytics/dashboard')
    dashboardData.value = response.data
  } catch (error) {
    console.error('Failed to fetch dashboard data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>
