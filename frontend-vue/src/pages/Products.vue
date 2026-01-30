<template>
  <div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0 mb-4 sm:mb-6">
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Products</h1>
      <Button @click="isModalOpen = true" class="w-full sm:w-auto text-sm sm:text-base">
        <Plus class="w-4 h-4 sm:w-5 sm:h-5 inline mr-2" />
        <span class="hidden sm:inline">Add Product</span>
        <span class="sm:hidden">Add</span>
      </Button>
    </div>

    <Alert
      v-if="alert"
      :type="alert.type"
      :message="alert.message"
      :onClose="() => alert = null"
    />

    <!-- Filters -->
    <div class="card mb-4 sm:mb-6 p-3 sm:p-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        <div class="relative sm:col-span-2 lg:col-span-1">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4 sm:w-5 sm:h-5" />
          <input
            type="text"
            placeholder="Search products..."
            v-model="searchTerm"
            @input="fetchProducts"
            class="input-field pl-9 sm:pl-10 text-sm sm:text-base"
          />
        </div>
        <div class="sm:col-span-1">
          <Select
            label="Category"
            name="categoryFilter"
            :modelValue="categoryFilter"
            @update:modelValue="categoryFilter = $event; fetchProducts()"
            :options="[{ value: '', label: 'All Categories' }, ...categoryOptions]"
          />
        </div>
        <div class="flex items-center sm:col-span-1 lg:col-span-1">
          <input
            type="checkbox"
            id="lowStock"
            v-model="lowStockFilter"
            @change="fetchProducts"
            class="w-4 h-4 text-primary-600 rounded"
          />
          <label for="lowStock" class="ml-2 text-xs sm:text-sm text-gray-700">
            Show low stock only
          </label>
        </div>
      </div>
    </div>

    <!-- Products Table -->
    <div class="card p-0 sm:p-6">
      <div v-if="loading" class="text-center py-8">Loading...</div>
      <div v-else-if="products.length === 0" class="text-center py-8 text-gray-500">
        <Package class="w-12 h-12 mx-auto mb-4 text-gray-400" />
        <p class="text-sm sm:text-base">No products found</p>
      </div>
      <div v-else class="overflow-x-auto -mx-2 sm:mx-0">
        <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">
                SKU
              </th>
              <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Name
              </th>
              <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">
                Category
              </th>
              <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Cost
              </th>
              <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">
                Selling
              </th>
              <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Stock
              </th>
              <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="product in products"
              :key="product.id"
              :class="product.stockQuantity <= product.reorderLevel ? 'bg-red-50' : ''"
            >
              <td class="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">
                {{ product.sku }}
              </td>
              <td class="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-900 max-w-[120px] sm:max-w-none truncate sm:whitespace-nowrap">
                {{ product.name }}
              </td>
              <td class="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden md:table-cell">
                {{ product.category.name }}
              </td>
              <td class="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                ${{ parseFloat(product.costPrice).toFixed(2) }}
              </td>
              <td class="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 hidden sm:table-cell">
                {{ product.sellingPrice ? `$${parseFloat(product.sellingPrice).toFixed(2)}` : '-' }}
              </td>
              <td class="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap">
                <span
                  :class="`text-xs sm:text-sm font-medium ${
                    product.stockQuantity <= product.reorderLevel
                      ? 'text-red-600'
                      : 'text-gray-900'
                  }`"
                >
                  {{ product.stockQuantity }}
                </span>
                <span v-if="product.stockQuantity <= product.reorderLevel" class="ml-1 sm:ml-2 text-[10px] sm:text-xs text-red-600">Low</span>
              </td>
              <td class="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium">
                <div class="flex items-center space-x-2">
                  <button
                    @click="handleEdit(product)"
                    class="text-primary-600 hover:text-primary-900 touch-manipulation p-1"
                    aria-label="Edit"
                  >
                    <Edit class="w-4 h-4 sm:w-5 sm:h-5" />
                  </button>
                  <button
                    @click="handleDelete(product.id)"
                    class="text-red-600 hover:text-red-900 touch-manipulation p-1"
                    aria-label="Delete"
                  >
                    <Trash2 class="w-4 h-4 sm:w-5 sm:h-5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <Modal
      :isOpen="isModalOpen"
      :onClose="handleModalClose"
      :title="editingProduct ? 'Edit Product' : 'Add Product'"
      size="lg"
    >
      <form @submit.prevent="handleSubmit">
        <div class="grid grid-cols-2 gap-4">
          <Input
            label="Product Name"
            name="name"
            v-model="formData.name"
            required
            :error="errors.name"
          />
          <Input
            label="SKU"
            name="sku"
            v-model="formData.sku"
            required
            :error="errors.sku"
          />
        </div>

        <Select
          label="Category"
          name="categoryId"
          v-model="formData.categoryId"
          :options="categoryOptions"
          required
          :error="errors.categoryId"
        />

        <div class="grid grid-cols-2 gap-4">
          <Input
            label="Cost Price"
            name="costPrice"
            type="number"
            step="0.01"
            v-model="formData.costPrice"
            required
            :error="errors.costPrice"
          />
          <Input
            label="Selling Price"
            name="sellingPrice"
            type="number"
            step="0.01"
            v-model="formData.sellingPrice"
            :error="errors.sellingPrice"
          />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <Input
            label="Stock Quantity"
            name="stockQuantity"
            type="number"
            v-model="formData.stockQuantity"
            :error="errors.stockQuantity"
          />
          <Input
            label="Reorder Level"
            name="reorderLevel"
            type="number"
            v-model="formData.reorderLevel"
            :error="errors.reorderLevel"
          />
        </div>

        <Input
          label="Description"
          name="description"
          v-model="formData.description"
          :error="errors.description"
        />

        <div class="flex justify-end space-x-3 mt-6">
          <Button type="button" variant="secondary" @click="handleModalClose">
            Cancel
          </Button>
          <Button type="submit" variant="primary">
            {{ editingProduct ? 'Update' : 'Create' }}
          </Button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { Plus, Search, Edit, Trash2, Package } from 'lucide-vue-next'
import api from '../utils/api'
import Modal from '../components/Modal.vue'
import Button from '../components/Button.vue'
import Input from '../components/Input.vue'
import Select from '../components/Select.vue'
import Alert from '../components/Alert.vue'

const products = ref([])
const categories = ref([])
const loading = ref(true)
const isModalOpen = ref(false)
const editingProduct = ref(null)
const searchTerm = ref('')
const categoryFilter = ref('')
const lowStockFilter = ref(false)
const alert = ref(null)
const formData = reactive({
  name: '',
  sku: '',
  categoryId: '',
  costPrice: '',
  sellingPrice: '',
  description: '',
  stockQuantity: '0',
  reorderLevel: '10',
})
const errors = ref({})

onMounted(() => {
  fetchProducts()
  fetchCategories()
})

watch([searchTerm, categoryFilter, lowStockFilter], () => {
  fetchProducts()
})

const fetchProducts = async () => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    if (searchTerm.value) params.append('search', searchTerm.value)
    if (categoryFilter.value) params.append('categoryId', categoryFilter.value)
    if (lowStockFilter.value) params.append('lowStock', 'true')
    
    const response = await api.get(`/products?${params.toString()}`)
    products.value = response.data
  } catch (error) {
    alert.value = { type: 'error', message: 'Failed to fetch products' }
  } finally {
    loading.value = false
  }
}

const fetchCategories = async () => {
  try {
    const response = await api.get('/categories')
    categories.value = response.data
  } catch (error) {
    console.error('Failed to fetch categories:', error)
  }
}

const categoryOptions = computed(() => {
  return categories.value.map(cat => ({
    value: cat.id.toString(),
    label: cat.name,
  }))
})

const handleSubmit = async () => {
  errors.value = {}

  try {
    if (editingProduct.value) {
      await api.put(`/products/${editingProduct.value.id}`, formData)
      alert.value = { type: 'success', message: 'Product updated successfully' }
    } else {
      await api.post('/products', formData)
      alert.value = { type: 'success', message: 'Product created successfully' }
    }
    
    isModalOpen.value = false
    resetForm()
    fetchProducts()
  } catch (error) {
    if (error.response?.data?.errors) {
      const validationErrors = {}
      error.response.data.errors.forEach(err => {
        validationErrors[err.param] = err.msg
      })
      errors.value = validationErrors
    } else {
      alert.value = { type: 'error', message: error.response?.data?.error || 'Operation failed' }
    }
  }
}

const handleEdit = (product) => {
  editingProduct.value = product
  formData.name = product.name
  formData.sku = product.sku
  formData.categoryId = product.categoryId.toString()
  formData.costPrice = product.costPrice.toString()
  formData.sellingPrice = product.sellingPrice?.toString() || ''
  formData.description = product.description || ''
  formData.stockQuantity = product.stockQuantity.toString()
  formData.reorderLevel = product.reorderLevel.toString()
  isModalOpen.value = true
}

const handleDelete = async (id) => {
  if (!window.confirm('Are you sure you want to delete this product?')) return

  try {
    await api.delete(`/products/${id}`)
    alert.value = { type: 'success', message: 'Product deleted successfully' }
    fetchProducts()
  } catch (error) {
    alert.value = { type: 'error', message: error.response?.data?.error || 'Failed to delete product' }
  }
}

const resetForm = () => {
  formData.name = ''
  formData.sku = ''
  formData.categoryId = ''
  formData.costPrice = ''
  formData.sellingPrice = ''
  formData.description = ''
  formData.stockQuantity = '0'
  formData.reorderLevel = '10'
  editingProduct.value = null
  errors.value = {}
}

const handleModalClose = () => {
  isModalOpen.value = false
  resetForm()
}
</script>

<style scoped>
.card {
  @apply bg-white rounded-lg shadow p-6;
}

.input-field {
  @apply w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500;
}
</style>
