import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../utils/api'

export const useCurrencyStore = defineStore('currency', () => {
  const currencies = ref([])
  const defaultCurrency = ref(null)
  const selectedCurrency = ref(null)
  const exchangeRates = ref({})
  const loading = ref(true)

  const fetchCurrencies = async () => {
    try {
      loading.value = true
      const [currenciesRes, defaultRes] = await Promise.all([
        api.get('/currencies/active'),
        api.get('/currencies/default')
      ])
      
      currencies.value = currenciesRes.data
      defaultCurrency.value = defaultRes.data
      
      // Load selected currency from localStorage or use default
      const savedCurrency = localStorage.getItem('selectedCurrency')
      if (savedCurrency) {
        const parsed = JSON.parse(savedCurrency)
        const found = currenciesRes.data.find(c => c.id === parsed.id)
        selectedCurrency.value = found || defaultRes.data
      } else {
        selectedCurrency.value = defaultRes.data
      }
    } catch (error) {
      console.error('Failed to fetch currencies:', error)
    } finally {
      loading.value = false
    }
  }

  const fetchExchangeRate = async (fromCode, toCode) => {
    try {
      const response = await api.get(`/currencies/exchange-rate/${fromCode}/${toCode}`)
      exchangeRates.value[`${fromCode}_${toCode}`] = parseFloat(response.data.rate)
    } catch (error) {
      console.error('Failed to fetch exchange rate:', error)
      exchangeRates.value[`${fromCode}_${toCode}`] = 1
    }
  }

  const changeCurrency = (currency) => {
    selectedCurrency.value = currency
    localStorage.setItem('selectedCurrency', JSON.stringify(currency))
    
    if (currency && defaultCurrency.value && currency.id !== defaultCurrency.value.id) {
      fetchExchangeRate(defaultCurrency.value.code, currency.code)
    }
  }

  const convertAmount = (amount, fromCurrency = null, toCurrency = null) => {
    const from = fromCurrency || defaultCurrency.value
    const to = toCurrency || selectedCurrency.value
    
    if (!amount || !from || !to) return amount
    if (from.id === to.id) return amount

    const rateKey = `${from.code}_${to.code}`
    const rate = exchangeRates.value[rateKey] || 1
    
    return parseFloat(amount) * rate
  }

  const formatCurrency = (amount, currency = null) => {
    const curr = currency || selectedCurrency.value
    if (!curr || amount === null || amount === undefined) return amount
    
    const formattedAmount = parseFloat(amount).toFixed(curr.decimalPlaces || 2)
    const symbol = curr.symbol || curr.code
    
    if (['USD', 'EUR', 'GBP'].includes(curr.code)) {
      return `${symbol}${formattedAmount}`
    } else {
      return `${formattedAmount} ${symbol}`
    }
  }

  // Initialize on store creation
  fetchCurrencies()

  return {
    currencies,
    defaultCurrency,
    selectedCurrency,
    exchangeRates,
    loading,
    changeCurrency,
    convertAmount,
    formatCurrency,
    fetchCurrencies,
    fetchExchangeRate
  }
})
