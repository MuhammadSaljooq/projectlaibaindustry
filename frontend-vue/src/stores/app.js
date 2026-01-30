import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAppStore = defineStore('app', () => {
  const products = ref([])
  const sales = ref([])
  const categories = ref([])
  const taxSettings = ref({})
  const loading = ref(false)
  const error = ref(null)

  const setLoading = (value) => {
    loading.value = value
  }

  const setError = (err) => {
    error.value = err
    loading.value = false
  }

  const setProducts = (data) => {
    products.value = data
    loading.value = false
  }

  const setSales = (data) => {
    sales.value = data
    loading.value = false
  }

  const setCategories = (data) => {
    categories.value = data
    loading.value = false
  }

  const setTaxSettings = (data) => {
    taxSettings.value = data
    loading.value = false
  }

  return {
    products,
    sales,
    categories,
    taxSettings,
    loading,
    error,
    setLoading,
    setError,
    setProducts,
    setSales,
    setCategories,
    setTaxSettings
  }
})
