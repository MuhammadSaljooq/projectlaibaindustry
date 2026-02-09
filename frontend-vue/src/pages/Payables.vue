<template>
  <div class="min-h-screen bg-gray-100">
    <Alert v-if="alert" :type="alert.type" :message="alert.message" :onClose="() => alert = null" class="fixed top-2 sm:top-4 right-2 sm:right-4 z-50 max-w-[calc(100%-1rem)] sm:max-w-md" />
    <div class="bg-white border-b shadow-sm sticky top-0 z-40">
      <div class="max-w-full mx-auto px-2 sm:px-4 py-2 sm:py-3">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 sm:gap-0">
          <div class="flex items-center space-x-2 sm:space-x-4">
            <CreditCard class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600" />
            <h1 class="text-lg sm:text-xl lg:text-2xl font-semibold text-gray-800">Payables</h1>
          </div>
          <Button variant="primary" @click="addRow" class="flex-1 sm:flex-none text-xs sm:text-sm px-2 sm:px-4"><Plus class="w-4 h-4 inline mr-1 sm:mr-2" /><span class="hidden sm:inline">Add Row</span><span class="sm:hidden">Add</span></Button>
        </div>
      </div>
    </div>
    <div class="bg-white mx-2 sm:mx-4 mt-2 sm:mt-4 border border-gray-300 rounded shadow-sm">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-4 p-2 sm:p-4 border-b border-gray-300 bg-gray-50">
        <div class="text-center"><div class="text-xs text-gray-600 mb-1 font-medium">Total Amount</div><div class="text-base sm:text-lg font-bold text-gray-900">${{ formatNum(totalAmount) }}</div></div>
        <div class="text-center border-l-0 sm:border-l-2 border-gray-400 sm:pl-4"><div class="text-xs text-gray-600 mb-1 font-medium">Entries</div><div class="text-xl sm:text-2xl font-bold text-green-600">{{ rows.length }}</div></div>
      </div>
    </div>
    <div class="bg-white mx-2 sm:mx-4 my-2 sm:my-4 border border-gray-300 rounded shadow-sm overflow-hidden">
      <div class="overflow-x-auto overflow-y-auto max-h-[70vh] sm:max-h-none" ref="gridRef" style="WebkitOverflowScrolling: touch">
        <table class="min-w-full border-collapse text-xs sm:text-sm" style="font-family: Calibri, Arial, sans-serif">
          <thead class="sticky top-0 z-10">
            <tr class="bg-gray-50 border-b-2 border-gray-400">
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2 sticky left-0 z-20 bg-gray-100" style="width: 40px; min-width: 40px">#</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 100px">Date</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 100px">Invoice number</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 120px">Customer name</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 95px">Customer code</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 90px">Amount</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 110px">Received date</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="min-width: 110px">Remaining date</th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="width: 40px; min-width: 40px"><span class="hidden sm:inline">Action</span><span class="sm:hidden">✕</span></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, rowIndex) in rows" :key="row.id" class="hover:bg-blue-50">
              <td class="border border-gray-300 bg-gray-50 text-center text-gray-600 px-1 sm:px-2 py-1 sticky left-0 z-10 bg-gray-50">{{ rowIndex + 1 }}</td>
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0"><input v-model="row.date" type="date" class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[36px] sm:min-h-[28px]" /></td>
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0"><input v-model="row.invoiceNumber" type="text" placeholder="–" class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[36px] sm:min-h-[28px]" /></td>
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0"><input v-model="row.customerName" type="text" placeholder="–" class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[36px] sm:min-h-[28px]" /></td>
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0"><input v-model="row.customerCode" type="text" placeholder="–" class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[36px] sm:min-h-[28px]" /></td>
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0"><input v-model.number="row.amount" type="number" min="0" step="0.01" placeholder="0" class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm text-right border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[36px] sm:min-h-[28px]" /></td>
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0"><input v-model="row.receivedDate" type="date" class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[36px] sm:min-h-[28px]" /></td>
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0"><input v-model="row.remainingDate" type="date" class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[36px] sm:min-h-[28px]" /></td>
              <td class="border border-gray-300 px-1 sm:px-2 py-1.5 sm:py-1 text-center"><button type="button" @click="removeRow(rowIndex)" class="p-1.5 text-red-600 hover:bg-red-50 rounded inline-flex items-center justify-center" aria-label="Remove row"><Trash2 class="w-4 h-4" /></button></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="rows.length === 0" class="text-center py-12 text-gray-500"><CreditCard class="w-12 h-12 mx-auto mb-4 text-gray-400" /><p class="text-sm">No payables yet. Add a row to enter payables.</p></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Plus, Trash2, CreditCard } from 'lucide-vue-next'
import Button from '../components/Button.vue'
import Alert from '../components/Alert.vue'

const rows = ref([])
const alert = ref(null)
const gridRef = ref(null)
let nextId = 1

function newRow(overrides = {}) {
  const today = new Date().toISOString().split('T')[0]
  return { id: nextId++, date: today, invoiceNumber: '', customerName: '', customerCode: '', amount: 0, receivedDate: '', remainingDate: '', ...overrides }
}

function addRow() { rows.value.push(newRow()) }
function removeRow(index) { rows.value.splice(index, 1) }

function formatNum(n) {
  if (n == null || Number.isNaN(n)) return '0.00'
  return Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const totalAmount = computed(() => rows.value.reduce((sum, r) => sum + (Number(r.amount) || 0), 0))

onMounted(() => { if (rows.value.length === 0) rows.value.push(newRow()) })
</script>
