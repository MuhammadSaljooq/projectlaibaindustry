<template>
  <div class="min-h-screen bg-gray-100">
    <Alert
      v-if="alert"
      :type="alert.type"
      :message="alert.message"
      :onClose="() => alert = null"
      class="fixed top-2 sm:top-4 right-2 sm:right-4 z-50 max-w-[calc(100%-1rem)] sm:max-w-md"
    />

    <div class="bg-white border-b shadow-sm sticky top-0 z-40">
      <div class="max-w-full mx-auto px-2 sm:px-4 py-2 sm:py-3">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 sm:gap-0">
          <div class="flex items-center space-x-2 sm:space-x-4">
            <FileSpreadsheet class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" />
            <h1 class="text-lg sm:text-xl lg:text-2xl font-semibold text-gray-800">
              <span class="hidden sm:inline">Inventory Sheet</span>
              <span class="sm:hidden">Inventory</span>
            </h1>
          </div>
          <div class="flex items-center space-x-2 w-full sm:w-auto">
            <Button variant="secondary" @click="loadFromProducts" class="flex-1 sm:flex-none text-xs sm:text-sm px-2 sm:px-4">Load from Products</Button>
            <Button variant="primary" @click="addRow" class="flex-1 sm:flex-none text-xs sm:text-sm px-2 sm:px-4">
              <Plus class="w-4 h-4 inline mr-1 sm:mr-2" />
              <span class="hidden sm:inline">Add Row</span>
              <span class="sm:hidden">Add</span>
            </Button>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white mx-2 sm:mx-4 mt-2 sm:mt-4 border border-gray-300 rounded shadow-sm">
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-4 p-2 sm:p-4 border-b border-gray-300 bg-gray-50">
        <div class="text-center"><div class="text-xs text-gray-600 mb-1 font-medium">Total Stock</div><div class="text-base sm:text-lg font-bold text-gray-900">{{ totalStock }}</div></div>
        <div class="text-center"><div class="text-xs text-gray-600 mb-1 font-medium">Stock Value</div><div class="text-base sm:text-lg font-bold text-gray-900">${{ formatNum(totalStockValue) }}</div></div>
        <div class="text-center"><div class="text-xs text-gray-600 mb-1 font-medium">Sold-out</div><div class="text-base sm:text-lg font-bold text-gray-900">{{ totalSoldOut }}</div></div>
        <div class="text-center"><div class="text-xs text-gray-600 mb-1 font-medium">Ex Stock</div><div class="text-base sm:text-lg font-bold text-blue-600">{{ totalExStock }}</div></div>
        <div class="text-center border-l-0 sm:border-l-2 border-gray-400 sm:pl-4"><div class="text-xs text-gray-600 mb-1 font-medium">Ex Stock Value</div><div class="text-base sm:text-lg font-bold text-green-600">${{ formatNum(totalExStockValue) }}</div></div>
        <div class="text-center"><div class="text-xs text-gray-600 mb-1 font-medium">Boxes</div><div class="text-base sm:text-lg font-bold text-gray-900">{{ formatNum(totalBoxes) }}</div></div>
      </div>
    </div>

    <div class="bg-white mx-2 sm:mx-4 my-2 sm:my-4 border border-gray-300 rounded shadow-sm overflow-hidden">
      <div class="overflow-x-auto overflow-y-auto max-h-[70vh] sm:max-h-none" ref="gridRef" style="WebkitOverflowScrolling: touch">
        <table class="min-w-full border-collapse text-xs sm:text-sm" style="font-family: Calibri, Arial, sans-serif">
          <thead class="sticky top-0 z-10">
            <tr class="bg-gray-50 border-b-2 border-gray-400">
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2 sticky left-0 z-20 bg-gray-100" style="width: 40px; min-width: 40px">#</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 90px">Inventory</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 100px">Date</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 90px">Article no</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 120px">Product name</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 85px">Unit price</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 70px">Stock</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 95px">Stock value</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 75px">Sold-out</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 75px">Ex stock</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 100px">Ex stock value</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 70px">Boxes</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="width: 40px; min-width: 40px"><span class="hidden sm:inline">Action</span><span class="sm:hidden">✕</span></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, rowIndex) in rows" :key="row.id" class="hover:bg-blue-50">
              <td class="border border-gray-300 bg-gray-50 text-center text-gray-600 px-1 sm:px-2 py-1 sticky left-0 z-10 bg-gray-50">{{ rowIndex + 1 }}</td>
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0"><input v-model="row.inventory" type="text" placeholder="–" class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[36px] sm:min-h-[28px]" /></td>
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0"><input v-model="row.date" type="date" class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[36px] sm:min-h-[28px]" /></td>
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0"><input v-model="row.articleNo" type="text" placeholder="–" class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[36px] sm:min-h-[28px]" /></td>
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0"><input v-model="row.productName" type="text" placeholder="–" class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[36px] sm:min-h-[28px]" /></td>
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0"><input v-model.number="row.unitPrice" type="number" min="0" step="0.01" placeholder="0" class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm text-right border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[36px] sm:min-h-[28px]" /></td>
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0"><input v-model.number="row.stock" type="number" min="0" step="1" placeholder="0" class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm text-right border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[36px] sm:min-h-[28px]" /></td>
              <td class="border border-gray-300 px-1 sm:px-2 py-1.5 sm:py-1 text-right text-gray-700 tabular-nums text-xs sm:text-sm">{{ formatNum(stockValue(row)) }}</td>
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0"><input v-model.number="row.soldOut" type="number" min="0" step="1" placeholder="0" class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm text-right border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[36px] sm:min-h-[28px]" /></td>
              <td class="border border-gray-300 px-1 sm:px-2 py-1.5 sm:py-1 text-right text-gray-700 tabular-nums text-xs sm:text-sm">{{ exStock(row) }}</td>
              <td class="border border-gray-300 px-1 sm:px-2 py-1.5 sm:py-1 text-right text-gray-700 tabular-nums text-xs sm:text-sm">{{ formatNum(exStockValue(row)) }}</td>
              <td class="border border-gray-300 px-1 sm:px-2 py-1.5 sm:py-1 text-right text-gray-700 tabular-nums text-xs sm:text-sm">{{ formatNum(boxes(row)) }}</td>
              <td class="border border-gray-300 px-1 sm:px-2 py-1.5 sm:py-1 text-center"><button type="button" @click="removeRow(rowIndex)" class="p-1.5 text-red-600 hover:bg-red-50 rounded inline-flex items-center justify-center" aria-label="Remove row"><Trash2 class="w-4 h-4" /></button></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="rows.length === 0" class="text-center py-12 text-gray-500">
        <FileSpreadsheet class="w-12 h-12 mx-auto mb-4 text-gray-400" />
        <p class="text-sm">No rows yet. Add a row or load from products.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Plus, Trash2, FileSpreadsheet } from 'lucide-vue-next'
import api from '../utils/api'
import Button from '../components/Button.vue'
import Alert from '../components/Alert.vue'

const rows = ref([])
const alert = ref(null)
const gridRef = ref(null)
let nextId = 1

function newRow(overrides = {}) {
  const today = new Date().toISOString().split('T')[0]
  return { id: nextId++, inventory: '', date: today, articleNo: '', productName: '', unitPrice: 0, stock: 0, soldOut: 0, ...overrides }
}

function addRow() { rows.value.push(newRow()) }
function removeRow(index) { rows.value.splice(index, 1) }

function stockValue(row) { return (Number(row.unitPrice) || 0) * (Number(row.stock) || 0) }
function exStock(row) { return Math.max(0, (Number(row.stock) || 0) - (Number(row.soldOut) || 0)) }
function exStockValue(row) { return exStock(row) * (Number(row.unitPrice) || 0) }
function boxes(row) { const ex = exStock(row); return ex === 0 ? 0 : ex / 120 }

function formatNum(n) {
  if (n == null || Number.isNaN(n)) return '0.00'
  return Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const totalStock = computed(() => rows.value.reduce((sum, r) => sum + (Number(r.stock) || 0), 0))
const totalStockValue = computed(() => rows.value.reduce((sum, r) => sum + stockValue(r), 0))
const totalSoldOut = computed(() => rows.value.reduce((sum, r) => sum + (Number(r.soldOut) || 0), 0))
const totalExStock = computed(() => rows.value.reduce((sum, r) => sum + exStock(r), 0))
const totalExStockValue = computed(() => rows.value.reduce((sum, r) => sum + exStockValue(r), 0))
const totalBoxes = computed(() => rows.value.reduce((sum, r) => sum + boxes(r), 0))

async function loadFromProducts() {
  try {
    const res = await api.get('/products')
    const products = Array.isArray(res.data) ? res.data : (res.data?.data ?? [])
    const today = new Date().toISOString().split('T')[0]
    rows.value = products.map((p) => newRow({ inventory: p.category?.name ?? '', date: today, articleNo: p.sku ?? '', productName: p.name ?? '', unitPrice: parseFloat(p.sellingPrice) || parseFloat(p.costPrice) || 0, stock: parseInt(p.stockQuantity, 10) || 0, soldOut: 0 }))
    if (rows.value.length === 0) alert.value = { type: 'info', message: 'No products in the system. Add products first or add rows manually.' }
  } catch (err) {
    alert.value = { type: 'error', message: err.response?.data?.error || 'Failed to load products' }
  }
}

onMounted(() => { if (rows.value.length === 0) rows.value.push(newRow()) })
</script>
