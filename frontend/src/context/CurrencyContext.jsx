import { createContext, useContext, useState, useEffect } from 'react'
import api from '../utils/api'

const CurrencyContext = createContext()

export const useCurrency = () => {
  const context = useContext(CurrencyContext)
  if (!context) {
    throw new Error('useCurrency must be used within CurrencyProvider')
  }
  return context
}

export const CurrencyProvider = ({ children }) => {
  const [currencies, setCurrencies] = useState([])
  const [defaultCurrency, setDefaultCurrency] = useState(null)
  const [selectedCurrency, setSelectedCurrency] = useState(null)
  const [exchangeRates, setExchangeRates] = useState({})
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchCurrencies()
  }, [])

  useEffect(() => {
    if (selectedCurrency && defaultCurrency && selectedCurrency.id !== defaultCurrency.id) {
      fetchExchangeRate(defaultCurrency.code, selectedCurrency.code)
    }
  }, [selectedCurrency, defaultCurrency])

  const fetchCurrencies = async () => {
    try {
      const [currenciesRes, defaultRes] = await Promise.all([
        api.get('/currencies/active'),
        api.get('/currencies/default')
      ])
      
      setCurrencies(currenciesRes.data)
      setDefaultCurrency(defaultRes.data)
      
      // Load selected currency from localStorage or use default
      const savedCurrency = localStorage.getItem('selectedCurrency')
      if (savedCurrency) {
        const parsed = JSON.parse(savedCurrency)
        const found = currenciesRes.data.find(c => c.id === parsed.id)
        setSelectedCurrency(found || defaultRes.data)
      } else {
        setSelectedCurrency(defaultRes.data)
      }
    } catch (error) {
      console.error('Failed to fetch currencies:', error)
    } finally {
      setLoading(false)
    }
  }

  const fetchExchangeRate = async (fromCode, toCode) => {
    try {
      const response = await api.get(`/currencies/exchange-rate/${fromCode}/${toCode}`)
      setExchangeRates(prev => ({
        ...prev,
        [`${fromCode}_${toCode}`]: parseFloat(response.data.rate)
      }))
    } catch (error) {
      console.error('Failed to fetch exchange rate:', error)
      // Default to 1 if rate not found
      setExchangeRates(prev => ({
        ...prev,
        [`${fromCode}_${toCode}`]: 1
      }))
    }
  }

  const changeCurrency = (currency) => {
    setSelectedCurrency(currency)
    localStorage.setItem('selectedCurrency', JSON.stringify(currency))
  }

  const convertAmount = (amount, fromCurrency = defaultCurrency, toCurrency = selectedCurrency) => {
    if (!amount || !fromCurrency || !toCurrency) return amount
    if (fromCurrency.id === toCurrency.id) return amount

    const rateKey = `${fromCurrency.code}_${toCurrency.code}`
    const rate = exchangeRates[rateKey] || 1
    
    return parseFloat(amount) * rate
  }

  const formatCurrency = (amount, currency = selectedCurrency) => {
    if (!currency || amount === null || amount === undefined) return amount
    
    const formattedAmount = parseFloat(amount).toFixed(currency.decimalPlaces || 2)
    const symbol = currency.symbol || currency.code
    
    // Format based on currency symbol position (some currencies have symbol after)
    if (['USD', 'EUR', 'GBP'].includes(currency.code)) {
      return `${symbol}${formattedAmount}`
    } else {
      return `${formattedAmount} ${symbol}`
    }
  }

  const value = {
    currencies,
    defaultCurrency,
    selectedCurrency,
    exchangeRates,
    loading,
    changeCurrency,
    convertAmount,
    formatCurrency,
    refreshCurrencies: fetchCurrencies,
  }

  return (
    <CurrencyContext.Provider value={value}>
      {children}
    </CurrencyContext.Provider>
  )
}
