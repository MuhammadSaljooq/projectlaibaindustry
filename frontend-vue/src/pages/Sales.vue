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
              <span class="hidden sm:inline">Sales Entry - Excel Mode</span>
              <span class="sm:hidden">Sales</span>
            </h1>
          </div>
          <div class="flex items-center space-x-2 w-full sm:w-auto">
            <span v-if="autoSaving" class="text-xs text-blue-600 flex items-center">
              <span class="animate-spin mr-1">⏳</span>
              Auto-saving...
            </span>
            <span v-if="lastSaved && !autoSaving" class="text-xs text-green-600 flex items-center">
              ✓ Saved {{ formatTime(lastSaved) }}
            </span>
            <Button variant="primary" @click="handleSave(true)" class="flex-1 sm:flex-none text-xs sm:text-sm px-2 sm:px-4">
              <Save class="w-4 h-4 inline mr-1 sm:mr-2" />
              <span class="hidden sm:inline">Save Now</span>
              <span class="sm:hidden">Save</span>
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Totals Summary -->
    <div class="bg-white mx-2 sm:mx-4 mt-2 sm:mt-4 border border-gray-300 rounded shadow-sm">
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-4 p-2 sm:p-4 border-b border-gray-300 bg-gray-50">
        <div class="text-center">
          <div class="text-xs text-gray-600 mb-1 font-medium">Subtotal (After Discount)</div>
          <div class="text-base sm:text-lg lg:text-xl font-bold text-gray-900">${{ calculations.subtotal.toFixed(2) }}</div>
        </div>
        <div class="text-center">
          <div class="text-xs text-gray-600 mb-1 font-medium">Discount</div>
          <div class="text-base sm:text-lg lg:text-xl font-bold text-red-600">-${{ calculations.discount.toFixed(2) }}</div>
        </div>
        <div class="text-center">
          <div class="text-xs text-gray-600 mb-1 font-medium">VAT ({{ headerData.applyTax ? `${headerData.vatPercentage}%` : '0%' }})</div>
          <div class="text-base sm:text-lg lg:text-xl font-bold text-gray-900">${{ calculations.tax.toFixed(2) }}</div>
        </div>
        <div class="text-center border-l-0 sm:border-l-2 border-gray-400 sm:pl-4 col-span-2 sm:col-span-1">
          <div class="text-xs text-gray-600 mb-1 font-medium">Total</div>
          <div class="text-xl sm:text-2xl font-bold text-green-600">${{ calculations.total.toFixed(2) }}</div>
        </div>
        <div class="text-center col-span-2 sm:col-span-1 lg:col-span-1">
          <div class="text-xs text-gray-600 mb-1 font-medium">Profit</div>
          <div class="text-base sm:text-lg lg:text-xl font-bold text-blue-600">${{ calculations.profit.toFixed(2) }}</div>
        </div>
      </div>
      <!-- Discount Settings -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 p-2 sm:p-4">
        <div class="flex items-center">
          <label class="flex items-center text-sm">
            <input
              type="checkbox"
              v-model="headerData.applyTax"
              class="mr-2 w-4 h-4"
            />
            Apply Tax
          </label>
        </div>
        <div>
          <label class="text-xs text-gray-600 mb-1 block">VAT Adjustment (%)</label>
          <input
            type="number"
            step="0.01"
            min="0"
            max="100"
            v-model="headerData.vatPercentage"
            :disabled="!headerData.applyTax"
            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="text-xs text-gray-600 mb-1 block">Discount Type</label>
          <select
            v-model="headerData.discountType"
            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
          >
            <option value="fixed">Fixed Amount</option>
            <option value="percentage">Percentage</option>
          </select>
        </div>
        <div>
          <label class="text-xs text-gray-600 mb-1 block">
            Discount {{ headerData.discountType === 'percentage' ? '(%)' : '($)' }}
          </label>
          <input
            type="number"
            step="0.01"
            v-model="headerData.discountAmount"
            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
        </div>
      </div>
    </div>

    <!-- Excel-like Grid -->
    <div class="bg-white mx-2 sm:mx-4 my-2 sm:my-4 border border-gray-300 rounded shadow-sm overflow-hidden">
      <div class="overflow-x-auto overflow-y-auto max-h-[70vh] sm:max-h-none" ref="gridRef" style="WebkitOverflowScrolling: touch">
        <table class="min-w-full border-collapse text-xs sm:text-sm" style="font-family: Calibri, Arial, sans-serif">
          <thead class="sticky top-0 z-10">
            <tr class="bg-gray-50 border-b-2 border-gray-400">
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2 sticky left-0 z-20 bg-gray-100" style="width: 40px; min-width: 40px">#</th>
              <th
                v-for="col in columns"
                :key="col.key"
                class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2"
                :style="{ width: col.width, minWidth: col.width }"
              >
                <span class="hidden sm:inline">{{ col.label }}</span>
                <span class="sm:hidden text-[10px]">{{ col.label.split(' ')[0] }}</span>
              </th>
              <th class="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style="width: 40px; min-width: 40px">
                <span class="hidden sm:inline">Action</span>
                <span class="sm:hidden">✕</span>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(row, rowIndex) in rows"
              :key="row.id"
              :class="`hover:bg-blue-50 ${selectedCell?.rowId === row.id ? 'bg-blue-100' : ''}`"
            >
              <td class="border border-gray-300 bg-gray-50 text-center text-gray-600 px-1 sm:px-2 py-1 sticky left-0 z-10 bg-gray-50">
                {{ rowIndex + 1 }}
              </td>
              
              <!-- Date Column -->
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0">
                <input
                  v-if="editingCell?.rowId === row.id && editingCell?.field === 'date'"
                  v-focus
                  type="date"
                  :value="row.date || ''"
                  @input="handleCellChange(row.id, 'date', $event.target.value)"
                  @blur="handleCellBlur"
                  @keydown="handleKeyDown($event, row.id, 'date')"
                  class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <div
                  v-else
                  @click="handleCellClick(row.id, 'date')"
                  class="px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm cursor-pointer min-h-[36px] sm:min-h-[28px] flex items-center touch-manipulation"
                >
                  <span v-if="row.date" class="text-[10px] sm:text-xs">{{ formatDate(row.date) }}</span>
                  <span v-else class="text-gray-400 text-[10px]">Date</span>
                </div>
              </td>
              
              <!-- Customer Code Column -->
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0">
                <input
                  v-if="editingCell?.rowId === row.id && editingCell?.field === 'customerCode'"
                  v-focus
                  type="text"
                  :value="row.customerCode || ''"
                  @input="handleCellChange(row.id, 'customerCode', $event.target.value)"
                  @blur="handleCellBlur"
                  @keydown="handleKeyDown($event, row.id, 'customerCode')"
                  placeholder="Code..."
                  class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <div
                  v-else
                  @click="handleCellClick(row.id, 'customerCode')"
                  class="px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm cursor-pointer min-h-[36px] sm:min-h-[28px] flex items-center touch-manipulation truncate"
                >
                  <span v-if="row.customerCode">{{ row.customerCode }}</span>
                  <span v-else class="text-gray-400 text-[10px]">Code</span>
                </div>
              </td>
              
              <!-- Customer Name Column -->
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0">
                <input
                  v-if="editingCell?.rowId === row.id && editingCell?.field === 'customerName'"
                  v-focus
                  type="text"
                  :value="row.customerName || ''"
                  @input="handleCellChange(row.id, 'customerName', $event.target.value)"
                  @blur="handleCellBlur"
                  @keydown="handleKeyDown($event, row.id, 'customerName')"
                  placeholder="Customer..."
                  class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <div
                  v-else
                  @click="handleCellClick(row.id, 'customerName')"
                  class="px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm cursor-pointer min-h-[36px] sm:min-h-[28px] flex items-center touch-manipulation truncate"
                >
                  <span v-if="row.customerName">{{ row.customerName }}</span>
                  <span v-else class="text-gray-400 text-[10px]">Customer</span>
                </div>
              </td>
              
              <!-- Invoice Number Column -->
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0">
                <input
                  v-if="editingCell?.rowId === row.id && editingCell?.field === 'invoiceNumber'"
                  v-focus
                  type="text"
                  :value="row.invoiceNumber || ''"
                  @input="handleCellChange(row.id, 'invoiceNumber', $event.target.value)"
                  @blur="handleCellBlur"
                  @keydown="handleKeyDown($event, row.id, 'invoiceNumber')"
                  placeholder="Invoice..."
                  class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <div
                  v-else
                  @click="handleCellClick(row.id, 'invoiceNumber')"
                  class="px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm cursor-pointer min-h-[36px] sm:min-h-[28px] flex items-center touch-manipulation truncate"
                >
                  <span v-if="row.invoiceNumber">{{ row.invoiceNumber }}</span>
                  <span v-else class="text-gray-400 text-[10px]">Invoice</span>
                </div>
              </td>
              
              <!-- Product Name Column -->
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0">
                <input
                  v-if="editingCell?.rowId === row.id && editingCell?.field === 'productName'"
                  v-focus
                  type="text"
                  :value="row.productName"
                  @input="handleCellChange(row.id, 'productName', $event.target.value)"
                  @blur="handleCellBlur"
                  @keydown="handleKeyDown($event, row.id, 'productName')"
                  placeholder="Product..."
                  class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :list="`product-suggestions-${row.id}`"
                />
                <div
                  v-else
                  @click="handleCellClick(row.id, 'productName')"
                  class="px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm cursor-pointer min-h-[36px] sm:min-h-[28px] flex items-center touch-manipulation truncate"
                >
                  <span v-if="row.productName">{{ row.productName }}</span>
                  <span v-else class="text-gray-400 text-[10px]">Product</span>
                </div>
              </td>
              
              <!-- Price Column -->
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0">
                <input
                  v-if="editingCell?.rowId === row.id && editingCell?.field === 'sellingPrice'"
                  v-focus
                  type="number"
                  step="0.01"
                  min="0"
                  :value="row.sellingPrice"
                  @input="handleCellChange(row.id, 'sellingPrice', $event.target.value)"
                  @blur="() => { handleCellBlur(); handlePriceBlur(row.id); }"
                  @keydown="handleKeyDown($event, row.id, 'sellingPrice')"
                  class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 text-right"
                />
                <div
                  v-else
                  @click="handleCellClick(row.id, 'sellingPrice')"
                  class="px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm cursor-pointer min-h-[36px] sm:min-h-[28px] flex items-center justify-end touch-manipulation"
                >
                  <span v-if="row.sellingPrice" class="text-[10px] sm:text-xs">${{ parseFloat(row.sellingPrice).toFixed(2) }}</span>
                  <span v-else class="text-gray-400 text-[10px]">0.00</span>
                </div>
              </td>
              
              <!-- Quantity Column -->
              <td class="border border-gray-300 px-0.5 sm:px-1 py-0">
                <input
                  v-if="editingCell?.rowId === row.id && editingCell?.field === 'quantity'"
                  v-focus
                  type="number"
                  min="1"
                  :value="row.quantity"
                  @input="handleCellChange(row.id, 'quantity', $event.target.value)"
                  @blur="handleCellBlur"
                  @keydown="handleKeyDown($event, row.id, 'quantity')"
                  class="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 text-right"
                />
                <div
                  v-else
                  @click="handleCellClick(row.id, 'quantity')"
                  class="px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm cursor-pointer min-h-[36px] sm:min-h-[28px] flex items-center justify-end touch-manipulation"
                >
                  <span v-if="row.quantity">{{ row.quantity }}</span>
                  <span v-else class="text-gray-400 text-[10px]">0</span>
                </div>
              </td>
              
              <!-- Amount Column (Read-only) -->
              <td class="border border-gray-300 px-1 sm:px-2 py-1 text-xs sm:text-sm text-right font-medium bg-gray-50">
                <span v-if="row.amount" class="text-[10px] sm:text-xs">${{ row.amount }}</span>
                <span v-else class="text-[10px] text-gray-400">$0.00</span>
              </td>
              
              <!-- VAT Column (Read-only) -->
              <td class="border border-gray-300 px-1 sm:px-2 py-1 text-xs sm:text-sm text-right font-medium bg-gray-50">
                <span v-if="row.vat" class="text-[10px] sm:text-xs">${{ row.vat }}</span>
                <span v-else class="text-[10px] text-gray-400">$0.00</span>
              </td>
              
              <!-- Subtotal Column (Read-only) -->
              <td class="border border-gray-300 px-1 sm:px-2 py-1 text-xs sm:text-sm text-right font-medium bg-gray-50">
                <span v-if="row.subtotal" class="text-[10px] sm:text-xs">${{ row.subtotal }}</span>
                <span v-else class="text-[10px] text-gray-400">$0.00</span>
              </td>
              
              <!-- Delete Button -->
              <td class="border border-gray-300 px-1 sm:px-2 py-1 text-center">
                <button
                  v-if="rows.length > 1"
                  @click="deleteRow(row.id)"
                  class="text-red-600 hover:text-red-800 touch-manipulation p-1"
                  aria-label="Delete row"
                >
                  <Trash2 class="w-4 h-4 sm:w-5 sm:h-5" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Add Row Button -->
      <div class="border-t border-gray-300 bg-gray-50 px-4 py-2">
        <button
          @click="addRow"
          class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center"
        >
          <Plus class="w-4 h-4 mr-1" />
          Add Row
        </button>
      </div>
    </div>

    <!-- Search Bar - Always Visible -->
    <div class="bg-white mx-2 sm:mx-4 mt-2 sm:mt-4 mb-2 sm:mb-4 border border-gray-300 rounded shadow-sm">
      <div class="p-3 sm:p-4 border-b bg-gray-50">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-3">
          <h2 class="text-base sm:text-lg font-semibold">Sales Search & Filter</h2>
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <Button
              variant="secondary"
              @click="showFilters = !showFilters"
              class="flex items-center gap-1 text-xs sm:text-sm"
            >
              <Filter class="w-4 h-4" />
              <span class="hidden sm:inline">Filters</span>
            </Button>
            <Button
              v-if="hasActiveFilters"
              variant="secondary"
              @click="clearFilters"
              class="flex items-center gap-1 text-xs sm:text-sm text-red-600 hover:text-red-700"
            >
              <X class="w-4 h-4" />
              <span class="hidden sm:inline">Clear</span>
            </Button>
          </div>
        </div>
        
        <!-- General Search -->
        <div class="mb-3">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
            <input
              type="text"
              placeholder="Search by invoice number, customer name, or customer code..."
              v-model="searchFilters.search"
              @input="handleSearchChange('search', $event.target.value)"
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            />
          </div>
        </div>
        
        <!-- Advanced Filters -->
        <div v-if="showFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pt-3 border-t border-gray-200">
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Invoice Number</label>
            <input
              type="text"
              placeholder="Enter invoice number"
              v-model="searchFilters.invoiceNumber"
              @input="handleSearchChange('invoiceNumber', $event.target.value)"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            />
          </div>
          
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Customer Name</label>
            <input
              type="text"
              placeholder="Enter customer name"
              v-model="searchFilters.customerName"
              @input="handleSearchChange('customerName', $event.target.value)"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            />
          </div>
          
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Customer Code</label>
            <input
              type="text"
              placeholder="Enter customer code"
              v-model="searchFilters.customerCode"
              @input="handleSearchChange('customerCode', $event.target.value)"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            />
          </div>
          
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Start Date</label>
            <input
              type="date"
              v-model="searchFilters.startDate"
              @input="handleSearchChange('startDate', $event.target.value)"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            />
          </div>
          
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">End Date</label>
            <input
              type="date"
              v-model="searchFilters.endDate"
              @input="handleSearchChange('endDate', $event.target.value)"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            />
          </div>
        </div>
        
        <!-- Results Count -->
        <div class="mt-3 pt-3 border-t border-gray-200">
          <p class="text-xs sm:text-sm text-gray-600">
            <span v-if="loading">Searching...</span>
            <span v-else>
              Found <strong>{{ sales.length }}</strong> sale{{ sales.length !== 1 ? 's' : '' }}
              <span v-if="hasActiveFilters"> matching your filters</span>
            </span>
          </p>
        </div>
      </div>
      
      <!-- Sales Table -->
      <div class="p-3 sm:p-4">
        <div v-if="loading" class="text-center py-8 text-sm">Loading...</div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">Customer Code</th>
                <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Customer Name</th>
                <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="sales.length === 0">
                <td colspan="6" class="px-2 sm:px-4 lg:px-6 py-8 text-center text-gray-500">
                  <ShoppingCart class="w-12 h-12 mx-auto mb-4 text-gray-400" />
                  <p class="text-sm sm:text-base">
                    {{ hasActiveFilters ? 'No sales found matching your filters' : 'No sales recorded yet' }}
                  </p>
                </td>
              </tr>
              <tr v-for="sale in sales" :key="sale.id" class="hover:bg-gray-50">
                <td class="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-gray-900">
                  {{ formatDate(sale.date) }}
                </td>
                <td class="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-gray-900 font-medium">
                  {{ sale.invoiceNumber || '-' }}
                </td>
                <td class="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-gray-900 hidden sm:table-cell">
                  {{ sale.customerCode || '-' }}
                </td>
                <td class="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-gray-900 hidden md:table-cell">
                  {{ sale.customerName || '-' }}
                </td>
                <td class="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 text-gray-900">
                  {{ sale.salesItems.length }} item{{ sale.salesItems.length !== 1 ? 's' : '' }}
                </td>
                <td class="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  ${{ parseFloat(sale.totalAmount).toFixed(2) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { Plus, Trash2, ShoppingCart, Save, FileSpreadsheet, Search, X, Filter } from 'lucide-vue-next'
import api from '../utils/api'
import Button from '../components/Button.vue'
import Alert from '../components/Alert.vue'

// State
const sales = ref([])
const products = ref([])
const taxSetting = ref({ defaultRate: 0 })
const loading = ref(true)
const alert = ref(null)
const autoSaving = ref(false)
const lastSaved = ref(null)
const autoSaveTimeoutRef = ref(null)
const searchTimeoutRef = ref(null)
const gridRef = ref(null)
const savedRowIdsRef = ref(new Set())

const searchFilters = reactive({
  search: '',
  invoiceNumber: '',
  customerName: '',
  customerCode: '',
  startDate: '',
  endDate: '',
})
const showFilters = ref(false)

const rows = ref([
  { id: 1, date: '', customerCode: '', customerName: '', invoiceNumber: '', taxRate: '', productName: '', quantity: '', sellingPrice: '', costPrice: '', amount: '', vat: '', subtotal: '' }
])
const selectedCell = ref(null)
const editingCell = ref(null)
const headerData = reactive({
  discountAmount: '0',
  discountType: 'fixed',
  applyTax: true,
  vatPercentage: '15',
})
const calculations = reactive({
  subtotal: 0,
  discount: 0,
  tax: 0,
  total: 0,
  profit: 0,
})

const columns = [
  { key: 'date', label: 'Date', width: '110px', mobileWidth: '90px', type: 'date' },
  { key: 'customerCode', label: 'Customer Code', width: '120px', mobileWidth: '100px', type: 'text' },
  { key: 'customerName', label: 'Customer Name', width: '180px', mobileWidth: '120px', type: 'text' },
  { key: 'invoiceNumber', label: 'Invoice Number', width: '130px', mobileWidth: '110px', type: 'text' },
  { key: 'productName', label: 'Product Name', width: '220px', mobileWidth: '150px', type: 'text' },
  { key: 'sellingPrice', label: 'Price', width: '100px', mobileWidth: '80px', type: 'number' },
  { key: 'quantity', label: 'Qty', width: '80px', mobileWidth: '60px', type: 'number' },
  { key: 'amount', label: 'Amount', width: '100px', mobileWidth: '80px', type: 'readonly' },
  { key: 'vat', label: 'VAT 15%', width: '100px', mobileWidth: '80px', type: 'readonly' },
  { key: 'subtotal', label: 'Subtotal', width: '100px', mobileWidth: '80px', type: 'readonly' },
]

const hasActiveFilters = computed(() => {
  return Object.values(searchFilters).some(value => value !== '')
})

// Directives
const vFocus = {
  mounted(el) {
    el.focus()
  }
}

// Lifecycle
onMounted(() => {
  fetchSales({})
  fetchProducts()
  fetchTaxSetting()
})

onUnmounted(() => {
  if (searchTimeoutRef.value) {
    clearTimeout(searchTimeoutRef.value)
  }
  if (autoSaveTimeoutRef.value) {
    clearTimeout(autoSaveTimeoutRef.value)
  }
})

// Watch for auto-save
watch(rows, () => {
  const validRows = rows.value.filter(row => {
    const isComplete = row.productName && row.quantity && row.sellingPrice
    const notSaved = !savedRowIdsRef.value.has(row.id)
    return isComplete && notSaved
  })
  
  if (validRows.length > 0) {
    if (autoSaveTimeoutRef.value) {
      clearTimeout(autoSaveTimeoutRef.value)
    }
    
    autoSaveTimeoutRef.value = setTimeout(async () => {
      const rowsToSave = rows.value.filter(row => {
        const isComplete = row.productName && row.quantity && row.sellingPrice
        const notSaved = !savedRowIdsRef.value.has(row.id)
        return isComplete && notSaved
      })
      
      if (rowsToSave.length > 0) {
        await autoSaveSales(rowsToSave)
        rowsToSave.forEach(row => savedRowIdsRef.value.add(row.id))
      }
    }, 2000)
  }
}, { deep: true })

// Watch for calculations
watch([rows, () => headerData.discountAmount, () => headerData.discountType, () => headerData.applyTax, () => headerData.vatPercentage], () => {
  calculateTotals()
}, { deep: true })

// Functions
const formatTime = (date) => {
  return new Date(date).toLocaleTimeString()
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const fetchSales = async (filters = {}) => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    
    if (filters.search) params.append('search', filters.search)
    if (filters.invoiceNumber) params.append('invoiceNumber', filters.invoiceNumber)
    if (filters.customerName) params.append('customerName', filters.customerName)
    if (filters.customerCode) params.append('customerCode', filters.customerCode)
    if (filters.startDate) params.append('startDate', filters.startDate)
    if (filters.endDate) params.append('endDate', filters.endDate)
    
    const queryString = params.toString()
    const url = queryString ? `/sales?${queryString}` : '/sales'
    const response = await api.get(url)
    sales.value = response.data
  } catch (error) {
    alert.value = { type: 'error', message: 'Failed to fetch sales' }
  } finally {
    loading.value = false
  }
}

const handleSearchChange = (field, value) => {
  searchFilters[field] = value
  
  if (searchTimeoutRef.value) {
    clearTimeout(searchTimeoutRef.value)
  }
  
  searchTimeoutRef.value = setTimeout(() => {
    fetchSales(searchFilters)
  }, 500)
}

const clearFilters = () => {
  Object.keys(searchFilters).forEach(key => {
    searchFilters[key] = ''
  })
  fetchSales(searchFilters)
}

const fetchProducts = async () => {
  try {
    const response = await api.get('/products')
    products.value = response.data
  } catch (error) {
    console.error('Failed to fetch products:', error)
  }
}

const fetchTaxSetting = async () => {
  try {
    const response = await api.get('/tax')
    taxSetting.value = response.data
    if (!headerData.vatPercentage || headerData.vatPercentage === '15') {
      const defaultVat = response.data.defaultRate || 15
      headerData.vatPercentage = defaultVat.toString()
    }
    if (rows.value.length > 0 && (!rows.value[0].taxRate || !rows.value[0].date)) {
      const today = new Date().toISOString().split('T')[0]
      const defaultTaxRate = response.data.defaultRate || 15
      rows.value = rows.value.map((row, index) => {
        if (index === 0) {
          return { 
            ...row, 
            taxRate: row.taxRate || defaultTaxRate.toString(), 
            date: row.date || today 
          }
        } else if (!row.date) {
          const baseDate = new Date(today)
          baseDate.setDate(baseDate.getDate() + index)
          return { ...row, date: baseDate.toISOString().split('T')[0], taxRate: row.taxRate || defaultTaxRate.toString() }
        }
        return { ...row, taxRate: row.taxRate || defaultTaxRate.toString() }
      })
    }
  } catch (error) {
    console.error('Failed to fetch tax setting:', error)
  }
}

const calculateTotals = () => {
  let subtotal = 0
  let totalProfit = 0
  let totalVat = 0

  const vatRate = headerData.applyTax ? parseFloat(headerData.vatPercentage || 15) : 0

  const updatedRows = rows.value.map(row => {
    if (row.productName && row.quantity && row.sellingPrice) {
      const qty = parseFloat(row.quantity) || 0
      const price = parseFloat(row.sellingPrice) || 0
      const amount = price * qty
      
      const rowVat = (amount * vatRate) / 100
      const rowSubtotal = amount + rowVat
      
      subtotal += amount
      totalVat += rowVat
      
      if (row.costPrice) {
        const itemCost = parseFloat(row.costPrice) * qty
        totalProfit += (amount - itemCost)
      }
      
      return { 
        ...row, 
        amount: amount.toFixed(2),
        vat: rowVat.toFixed(2),
        subtotal: rowSubtotal.toFixed(2)
      }
    }
    return { ...row, amount: '', vat: '', subtotal: '' }
  })
  
  rows.value = updatedRows

  let discount = 0
  if (headerData.discountType === 'percentage') {
    discount = (subtotal * parseFloat(headerData.discountAmount)) / 100
  } else {
    discount = parseFloat(headerData.discountAmount) || 0
  }

  const subtotalAfterDiscount = subtotal - discount
  const vatAfterDiscount = subtotal > 0 ? (totalVat * (subtotalAfterDiscount / subtotal)) : 0
  const total = subtotalAfterDiscount + vatAfterDiscount

  Object.assign(calculations, {
    subtotal: subtotalAfterDiscount,
    discount,
    tax: vatAfterDiscount,
    total,
    profit: totalProfit,
  })
}

const handleProductNameChange = (rowId, productName) => {
  const existingProduct = products.value.find(p => 
    p.name.toLowerCase() === productName.toLowerCase()
  )
  
  if (existingProduct) {
    rows.value = rows.value.map(row => 
      row.id === rowId 
        ? { 
            ...row, 
            productName,
            costPrice: existingProduct.costPrice,
            sellingPrice: existingProduct.sellingPrice || existingProduct.costPrice,
          }
        : row
    )
  } else {
    rows.value = rows.value.map(row => 
      row.id === rowId 
        ? { ...row, productName }
        : row
    )
  }
}

const handleCellChange = (rowId, field, value) => {
  if (field === 'productName') {
    handleProductNameChange(rowId, value)
  } else if (field === 'date') {
    const currentIndex = rows.value.findIndex(r => r.id === rowId)
    rows.value = rows.value.map((row, index) => {
      if (row.id === rowId) {
        return { ...row, date: value }
      } else if (index > currentIndex && value) {
        const baseDate = new Date(value)
        baseDate.setDate(baseDate.getDate() + (index - currentIndex))
        return { ...row, date: baseDate.toISOString().split('T')[0] }
      }
      return row
    })
  } else if (field === 'invoiceNumber') {
    const currentIndex = rows.value.findIndex(r => r.id === rowId)
    const baseNumber = value ? parseInt(value.toString().match(/(\d+)$/)?.[1] || '0') : 0
    rows.value = rows.value.map((row, index) => {
      if (row.id === rowId) {
        return { ...row, invoiceNumber: value }
      } else if (index > currentIndex && value && baseNumber > 0) {
        const prefix = value.toString().replace(/\d+$/, '')
        const nextNumber = (baseNumber + (index - currentIndex)).toString()
        return { ...row, invoiceNumber: prefix + nextNumber }
      }
      return row
    })
  } else if (field === 'customerCode') {
    const currentIndex = rows.value.findIndex(r => r.id === rowId)
    const baseNumber = value ? parseInt(value.toString().match(/(\d+)$/)?.[1] || '0') : 0
    rows.value = rows.value.map((row, index) => {
      if (row.id === rowId) {
        return { ...row, customerCode: value }
      } else if (index > currentIndex && value && baseNumber > 0) {
        const prefix = value.toString().replace(/\d+$/, '')
        const nextNumber = (baseNumber + (index - currentIndex)).toString()
        return { ...row, customerCode: prefix + nextNumber }
      }
      return row
    })
  } else {
    rows.value = rows.value.map(row => 
      row.id === rowId ? { ...row, [field]: value } : row
    )
  }
}

const getNextInvoiceNumber = () => {
  const invoiceNumbers = rows.value
    .map(row => row.invoiceNumber)
    .filter(inv => inv && inv.trim() !== '')
    .map(inv => {
      const match = inv.toString().match(/(\d+)$/)
      return match ? parseInt(match[1]) : 0
    })
  
  const savedInvoiceNumbers = sales.value
    .map(sale => sale.invoiceNumber)
    .filter(inv => inv && inv.trim() !== '')
    .map(inv => {
      const match = inv.toString().match(/(\d+)$/)
      return match ? parseInt(match[1]) : 0
    })
  
  const allNumbers = [...invoiceNumbers, ...savedInvoiceNumbers]
  const maxNumber = allNumbers.length > 0 ? Math.max(...allNumbers) : 0
  return (maxNumber + 1).toString()
}

const getNextCustomerCode = () => {
  const customerCodes = rows.value
    .map(row => row.customerCode)
    .filter(code => code && code.trim() !== '')
    .map(code => {
      const match = code.toString().match(/(\d+)$/)
      return match ? parseInt(match[1]) : 0
    })
  
  const savedCustomerCodes = sales.value
    .map(sale => sale.customerCode)
    .filter(code => code && code.trim() !== '')
    .map(code => {
      const match = code.toString().match(/(\d+)$/)
      return match ? parseInt(match[1]) : 0
    })
  
  const allNumbers = [...customerCodes, ...savedCustomerCodes]
  const maxNumber = allNumbers.length > 0 ? Math.max(...allNumbers) : 0
  return (maxNumber + 1).toString()
}

const addRow = () => {
  const newId = Math.max(...rows.value.map(r => r.id), 0) + 1
  const lastRow = rows.value[rows.value.length - 1] || {}
  const firstRow = rows.value[0] || {}
  
  let nextDate = new Date().toISOString().split('T')[0]
  if (lastRow.date) {
    const lastDate = new Date(lastRow.date)
    lastDate.setDate(lastDate.getDate() + 1)
    nextDate = lastDate.toISOString().split('T')[0]
  } else if (firstRow.date) {
    const firstDate = new Date(firstRow.date)
    firstDate.setDate(firstDate.getDate() + rows.value.length)
    nextDate = firstDate.toISOString().split('T')[0]
  }
  
  const nextInvoiceNumber = getNextInvoiceNumber()
  const nextCustomerCode = getNextCustomerCode()
  
  rows.value.push({ 
    id: newId, 
    date: nextDate,
    customerCode: nextCustomerCode,
    customerName: '',
    invoiceNumber: nextInvoiceNumber,
    taxRate: firstRow.taxRate || (taxSetting.value.defaultRate || 15).toString(),
    productName: '', 
    quantity: '', 
    sellingPrice: '', 
    costPrice: '',
    amount: '',
    vat: '',
    subtotal: ''
  })
}

const handlePriceBlur = (rowId) => {
  const currentIndex = rows.value.findIndex(r => r.id === rowId)
  const isLastRow = currentIndex === rows.value.length - 1
  const currentRow = rows.value[currentIndex]
  
  if (isLastRow && (currentRow.productName || currentRow.quantity || currentRow.sellingPrice)) {
    const hasEmptyRow = rows.value.some(row => 
      !row.productName && !row.quantity && !row.sellingPrice && row.id !== rowId
    )
    if (!hasEmptyRow) {
      addRow()
    }
  }
}

const deleteRow = (rowId) => {
  if (rows.value.length > 1) {
    rows.value = rows.value.filter(row => row.id !== rowId)
  }
}

const handleCellClick = (rowId, field) => {
  selectedCell.value = { rowId, field }
  editingCell.value = { rowId, field }
}

const handleCellBlur = () => {
  editingCell.value = null
}

const handleKeyDown = async (e, rowId, field) => {
  const currentIndex = rows.value.findIndex(r => r.id === rowId)
  const currentRow = rows.value.find(r => r.id === rowId)
  
  if (e.key === 'Enter') {
    e.preventDefault()
    
    if (currentRow.productName && currentRow.quantity && currentRow.sellingPrice) {
      await handleSave()
    }
    
    if (currentIndex < rows.value.length - 1) {
      const nextRowId = rows.value[currentIndex + 1].id
      selectedCell.value = { rowId: nextRowId, field }
      editingCell.value = { rowId: nextRowId, field }
    } else {
      addRow()
      await nextTick()
      const newRowId = Math.max(...rows.value.map(r => r.id), 0)
      selectedCell.value = { rowId: newRowId, field }
      editingCell.value = { rowId: newRowId, field }
    }
  } else if (e.key === 'Tab') {
    e.preventDefault()
    const fields = ['date', 'customerCode', 'customerName', 'invoiceNumber', 'productName', 'quantity', 'sellingPrice']
    const currentFieldIndex = fields.indexOf(field)
    if (currentFieldIndex < fields.length - 1) {
      const nextField = fields[currentFieldIndex + 1]
      selectedCell.value = { rowId, field: nextField }
      editingCell.value = { rowId, field: nextField }
    } else if (currentIndex < rows.value.length - 1) {
      const nextRowId = rows.value[currentIndex + 1].id
      selectedCell.value = { rowId: nextRowId, field: 'date' }
      editingCell.value = { rowId: nextRowId, field: 'date' }
    } else {
      addRow()
    }
  } else if (e.key === 'ArrowDown') {
    e.preventDefault()
    if (currentIndex < rows.value.length - 1) {
      const nextRowId = rows.value[currentIndex + 1].id
      selectedCell.value = { rowId: nextRowId, field }
      editingCell.value = { rowId: nextRowId, field }
    }
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    if (currentIndex > 0) {
      const prevRowId = rows.value[currentIndex - 1].id
      selectedCell.value = { rowId: prevRowId, field }
      editingCell.value = { rowId: prevRowId, field }
    }
  } else if (e.key === 'Delete' || e.key === 'Backspace') {
    if (['date', 'customerCode', 'customerName', 'invoiceNumber', 'taxRate', 'productName', 'quantity', 'sellingPrice'].includes(field)) {
      handleCellChange(rowId, field, '')
    }
  }
}

const autoSaveSales = async (validRows) => {
  if (validRows.length === 0) return
  
  try {
    autoSaving.value = true
    const firstRow = validRows[0]
    const saleData = {
      date: firstRow.date || new Date().toISOString().split('T')[0],
      customerCode: firstRow.customerCode || null,
      customerName: firstRow.customerName || null,
      invoiceNumber: firstRow.invoiceNumber || null,
      items: validRows.map(row => ({
        productName: row.productName.trim(),
        quantity: parseInt(row.quantity),
        sellingPrice: parseFloat(row.sellingPrice).toString(),
      })),
      taxRate: headerData.applyTax ? headerData.vatPercentage : '0',
      discountAmount: headerData.discountAmount,
      discountType: headerData.discountType,
    }

    await api.post('/sales', saleData)
    
    await fetchProducts()
    fetchSales(searchFilters)
    
    lastSaved.value = new Date()
    
    const incompleteRows = rows.value.filter(row => {
      const isIncomplete = !row.productName || !row.quantity || !row.sellingPrice
      if (!isIncomplete) {
        savedRowIdsRef.value.delete(row.id)
      }
      return isIncomplete
    })
    
    if (incompleteRows.length === 0) {
      const today = new Date().toISOString().split('T')[0]
      const nextInvoiceNumber = getNextInvoiceNumber()
      const nextCustomerCode = getNextCustomerCode()
      const newRow = { 
        id: Math.max(...rows.value.map(r => r.id), 0) + 1, 
        date: today,
        customerCode: nextCustomerCode,
        customerName: '',
        invoiceNumber: nextInvoiceNumber,
        taxRate: (taxSetting.value.defaultRate || 15).toString(),
        productName: '', 
        quantity: '', 
        sellingPrice: '', 
        costPrice: '',
        amount: '',
        vat: '',
        subtotal: ''
      }
      rows.value = [newRow]
    } else {
      rows.value = incompleteRows
    }
  } catch (error) {
    console.error('Auto-save failed:', error)
  } finally {
    autoSaving.value = false
  }
}

const handleSave = async (clearCompletedRows = true) => {
  const validRows = rows.value.filter(row => 
    row.productName && row.quantity && row.sellingPrice
  )

  if (validRows.length === 0) {
    if (!clearCompletedRows) {
      return
    }
    alert.value = { type: 'error', message: 'Please add at least one item' }
    return
  }

  try {
    const firstRow = validRows[0]
    const saleData = {
      date: firstRow.date || new Date().toISOString().split('T')[0],
      customerCode: firstRow.customerCode || null,
      customerName: firstRow.customerName || null,
      invoiceNumber: firstRow.invoiceNumber || null,
      items: validRows.map(row => ({
        productName: row.productName.trim(),
        quantity: parseInt(row.quantity),
        sellingPrice: parseFloat(row.sellingPrice).toString(),
      })),
      taxRate: headerData.applyTax ? headerData.vatPercentage : '0',
      discountAmount: headerData.discountAmount,
      discountType: headerData.discountType,
    }

    await api.post('/sales', saleData)
    alert.value = { type: 'success', message: 'Sale recorded successfully! Products added to database.' }
    
    await fetchProducts()
    
    if (clearCompletedRows) {
      const emptyRows = rows.value.filter(row => 
        !row.productName && !row.quantity && !row.sellingPrice
      )
      
      if (emptyRows.length === 0) {
        const today = new Date().toISOString().split('T')[0]
        rows.value = [{ 
          id: 1, 
          date: today,
          customerCode: '',
          customerName: '',
          invoiceNumber: '',
          taxRate: (taxSetting.value.defaultRate || 15).toString(),
          productName: '', 
          quantity: '', 
          sellingPrice: '', 
          costPrice: '',
          amount: '',
          vat: '',
          subtotal: ''
        }]
      } else {
        rows.value = emptyRows
      }
      
      Object.assign(headerData, {
        discountAmount: '0',
        discountType: 'fixed',
        applyTax: true,
        vatPercentage: '15',
      })
    } else {
      const remainingRows = rows.value.filter(row => 
        !row.productName || !row.quantity || !row.sellingPrice
      )
      if (remainingRows.length === 0) {
        const today = new Date().toISOString().split('T')[0]
        const nextInvoiceNumber = getNextInvoiceNumber()
        const nextCustomerCode = getNextCustomerCode()
        rows.value = [{ 
          id: 1, 
          date: today,
          customerCode: nextCustomerCode,
          customerName: '',
          invoiceNumber: nextInvoiceNumber,
          taxRate: (taxSetting.value.defaultRate || 15).toString(),
          productName: '', 
          quantity: '', 
          sellingPrice: '', 
          costPrice: '',
          amount: '',
          vat: '',
          subtotal: ''
        }]
      } else {
        const firstRow = remainingRows[0]
        if (firstRow.date) {
          const baseDate = new Date(firstRow.date)
          rows.value = remainingRows.map((row, index) => {
            if (index === 0) return row
            const rowDate = new Date(baseDate)
            rowDate.setDate(rowDate.getDate() + index)
            return { ...row, date: rowDate.toISOString().split('T')[0] }
          })
        } else {
          rows.value = remainingRows
        }
      }
    }
    
    fetchSales(searchFilters)
  } catch (error) {
    alert.value = { type: 'error', message: error.response?.data?.error || 'Failed to save sale' }
  }
}
</script>
