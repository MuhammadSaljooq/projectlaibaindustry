<template>
  <div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0 mb-4 sm:mb-6">
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Reports & Analytics</h1>
      <div class="w-full sm:w-48">
        <Select
          name="period"
          :modelValue="period"
          @update:modelValue="period = $event; fetchReports()"
          :options="[
            { value: 'daily', label: 'Daily' },
            { value: 'weekly', label: 'Weekly' },
            { value: 'monthly', label: 'Monthly' },
          ]"
        />
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-8">
      <div class="card p-4 sm:p-6">
        <div class="flex items-center">
          <div class="bg-blue-100 p-2 sm:p-3 rounded-full mr-3 sm:mr-4">
            <DollarSign class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" />
          </div>
          <div>
            <p class="text-xs sm:text-sm text-gray-600">Total Sales</p>
            <p class="text-xl sm:text-2xl font-bold">${{ totalSales.toFixed(2) }}</p>
          </div>
        </div>
      </div>
      <div class="card p-4 sm:p-6">
        <div class="flex items-center">
          <div class="bg-green-100 p-2 sm:p-3 rounded-full mr-3 sm:mr-4">
            <TrendingUp class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" />
          </div>
          <div>
            <p class="text-xs sm:text-sm text-gray-600">Total Profit</p>
            <p class="text-xl sm:text-2xl font-bold">${{ totalProfit.toFixed(2) }}</p>
          </div>
        </div>
      </div>
      <div class="card p-4 sm:p-6 sm:col-span-2 lg:col-span-1">
        <div class="flex items-center">
          <div class="bg-purple-100 p-2 sm:p-3 rounded-full mr-3 sm:mr-4">
            <BarChart3 class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" />
          </div>
          <div>
            <p class="text-xs sm:text-sm text-gray-600">Total Transactions</p>
            <p class="text-xl sm:text-2xl font-bold">{{ totalTransactions }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
      <!-- Sales Summary -->
      <div class="card p-3 sm:p-6">
        <h2 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Sales Summary ({{ period }})</h2>
        <div v-if="salesSummary.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sales</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Profit</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Transactions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in salesSummary" :key="item.period">
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.period }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${{ item.totalSales.toFixed(2) }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-green-600">${{ item.totalProfit.toFixed(2) }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.transactionCount }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-8 text-gray-500 text-sm">No data available</div>
      </div>

      <!-- Top Products -->
      <div class="card p-3 sm:p-6">
        <h2 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Top Selling Products</h2>
        <div v-if="topProductsData.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sales</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in topProductsData" :key="item.name">
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.name }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.quantity }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${{ item.sales.toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-8 text-gray-500 text-sm">No data available</div>
      </div>
    </div>

    <!-- Profit Margins -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <div class="card">
        <h2 class="text-xl font-semibold mb-4">Profit by Category</h2>
        <div v-if="profitMarginsData.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Profit</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in profitMarginsData" :key="item.name">
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.name }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-green-600">${{ item.profit.toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-8 text-gray-500">No data available</div>
      </div>

      <div class="card">
        <h2 class="text-xl font-semibold mb-4">Profit Margins by Category</h2>
        <div v-if="profitMarginsData.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Margin (%)</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in profitMarginsData" :key="item.name">
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.name }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-yellow-600">{{ item.margin.toFixed(2) }}%</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-8 text-gray-500">No data available</div>
      </div>
    </div>

    <!-- Top Products Table -->
    <div class="card">
      <h2 class="text-xl font-semibold mb-4">Top Products Details</h2>
      <div v-if="topProducts.length > 0" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity Sold</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Times Sold</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in topProducts" :key="item.product.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ item.product.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ item.product.category.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ item.totalQuantitySold }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ item.timesSold }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="text-center py-8 text-gray-500">No data available</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { BarChart3, TrendingUp, DollarSign } from 'lucide-vue-next'
// Note: Recharts is React-only. Using simple table/chart display for now.
// For production, consider using vue-echarts or vue-chartjs
import api from '../utils/api'
import Select from '../components/Select.vue'

const salesSummary = ref([])
const topProducts = ref([])
const profitMargins = ref([])
const period = ref('daily')
const loading = ref(true)

onMounted(() => {
  fetchReports()
})

watch(period, () => {
  fetchReports()
})

const fetchReports = async () => {
  try {
    loading.value = true
    const [summaryRes, productsRes, marginsRes] = await Promise.all([
      api.get(`/analytics/sales-summary?period=${period.value}`),
      api.get('/analytics/top-products?limit=10'),
      api.get('/analytics/profit-margins'),
    ])
    
    salesSummary.value = summaryRes.data
    topProducts.value = productsRes.data
    profitMargins.value = marginsRes.data
  } catch (error) {
    console.error('Failed to fetch reports:', error)
  } finally {
    loading.value = false
  }
}

const totalSales = computed(() => {
  return salesSummary.value.reduce((sum, item) => sum + item.totalSales, 0)
})

const totalProfit = computed(() => {
  return salesSummary.value.reduce((sum, item) => sum + item.totalProfit, 0)
})

const totalTransactions = computed(() => {
  return salesSummary.value.reduce((sum, item) => sum + item.transactionCount, 0)
})

const topProductsData = computed(() => {
  return topProducts.value.map(item => ({
    name: item.product.name,
    quantity: item.totalQuantitySold,
    sales: item.totalQuantitySold * parseFloat(item.product.sellingPrice || item.product.costPrice),
  }))
})

const profitMarginsData = computed(() => {
  return profitMargins.value.map(item => ({
    name: item.category,
    profit: item.totalProfit,
    margin: item.margin,
  }))
})
</script>

<style scoped>
.card {
  @apply bg-white rounded-lg shadow p-6;
}
</style>
