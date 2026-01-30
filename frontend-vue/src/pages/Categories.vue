<template>
  <div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0 mb-4 sm:mb-6">
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Categories</h1>
      <Button @click="isModalOpen = true" class="w-full sm:w-auto text-sm sm:text-base">
        <Plus class="w-4 h-4 sm:w-5 sm:h-5 inline mr-2" />
        <span class="hidden sm:inline">Add Category</span>
        <span class="sm:hidden">Add</span>
      </Button>
    </div>

    <Alert
      v-if="alert"
      :type="alert.type"
      :message="alert.message"
      :onClose="() => alert = null"
    />

    <div class="card p-3 sm:p-6">
      <div v-if="loading" class="text-center py-8">Loading...</div>
      <div v-else-if="categories.length === 0" class="text-center py-8 text-gray-500">
        <FolderTree class="w-12 h-12 mx-auto mb-4 text-gray-400" />
        <p class="text-sm sm:text-base">No categories found</p>
      </div>
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        <div
          v-for="category in categories"
          :key="category.id"
          class="border border-gray-200 rounded-lg p-3 sm:p-4 hover:shadow-md transition-shadow"
        >
          <div class="flex justify-between items-start mb-2">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex-1 pr-2">{{ category.name }}</h3>
            <div class="flex space-x-2 flex-shrink-0">
              <button
                @click="handleEdit(category)"
                class="text-primary-600 hover:text-primary-900 touch-manipulation p-1"
                aria-label="Edit"
              >
                <Edit class="w-4 h-4 sm:w-5 sm:h-5" />
              </button>
              <button
                @click="handleDelete(category.id)"
                class="text-red-600 hover:text-red-900 touch-manipulation p-1"
                aria-label="Delete"
              >
                <Trash2 class="w-4 h-4 sm:w-5 sm:h-5" />
              </button>
            </div>
          </div>
          <p v-if="category.description" class="text-xs sm:text-sm text-gray-600 mb-2 line-clamp-2">{{ category.description }}</p>
          <p class="text-xs text-gray-500">
            {{ category._count?.products || 0 }} product(s)
          </p>
        </div>
      </div>
    </div>

    <Modal
      :isOpen="isModalOpen"
      :onClose="handleModalClose"
      :title="editingCategory ? 'Edit Category' : 'Add Category'"
    >
      <form @submit.prevent="handleSubmit">
        <Input
          label="Category Name"
          name="name"
          v-model="formData.name"
          required
          :error="errors.name"
        />

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
            {{ editingCategory ? 'Update' : 'Create' }}
          </Button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Plus, Edit, Trash2, FolderTree } from 'lucide-vue-next'
import api from '../utils/api'
import Modal from '../components/Modal.vue'
import Button from '../components/Button.vue'
import Input from '../components/Input.vue'
import Alert from '../components/Alert.vue'

const categories = ref([])
const loading = ref(true)
const isModalOpen = ref(false)
const editingCategory = ref(null)
const alert = ref(null)
const formData = reactive({
  name: '',
  description: '',
})
const errors = ref({})

onMounted(() => {
  fetchCategories()
})

const fetchCategories = async () => {
  try {
    loading.value = true
    const response = await api.get('/categories')
    categories.value = response.data
  } catch (error) {
    alert.value = { type: 'error', message: 'Failed to fetch categories' }
  } finally {
    loading.value = false
  }
}

const handleSubmit = async () => {
  errors.value = {}

  try {
    if (editingCategory.value) {
      await api.put(`/categories/${editingCategory.value.id}`, formData)
      alert.value = { type: 'success', message: 'Category updated successfully' }
    } else {
      await api.post('/categories', formData)
      alert.value = { type: 'success', message: 'Category created successfully' }
    }
    
    isModalOpen.value = false
    resetForm()
    fetchCategories()
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

const handleEdit = (category) => {
  editingCategory.value = category
  formData.name = category.name
  formData.description = category.description || ''
  isModalOpen.value = true
}

const handleDelete = async (id) => {
  if (!window.confirm('Are you sure you want to delete this category?')) return

  try {
    await api.delete(`/categories/${id}`)
    alert.value = { type: 'success', message: 'Category deleted successfully' }
    fetchCategories()
  } catch (error) {
    const errorMsg = error.response?.data?.error || 'Failed to delete category'
    if (error.response?.data?.productCount) {
      alert.value = {
        type: 'error',
        message: `${errorMsg}. This category has ${error.response.data.productCount} product(s).`,
      }
    } else {
      alert.value = { type: 'error', message: errorMsg }
    }
  }
}

const resetForm = () => {
  formData.name = ''
  formData.description = ''
  editingCategory.value = null
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
</style>
