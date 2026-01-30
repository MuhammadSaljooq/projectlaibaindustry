import { useCurrency } from '../context/CurrencyContext'

/**
 * Hook to format currency amounts
 * @param {number|string} amount - The amount to format
 * @param {object} currency - Optional currency object (defaults to selected currency)
 * @returns {string} Formatted currency string
 */
export const useFormatCurrency = () => {
  const { formatCurrency, selectedCurrency } = useCurrency()
  
  return (amount, currency = selectedCurrency) => {
    return formatCurrency(amount, currency)
  }
}

/**
 * Hook to convert amounts between currencies
 * @returns {function} Conversion function
 */
export const useConvertCurrency = () => {
  const { convertAmount, defaultCurrency, selectedCurrency } = useCurrency()
  
  return (amount, fromCurrency = defaultCurrency, toCurrency = selectedCurrency) => {
    return convertAmount(amount, fromCurrency, toCurrency)
  }
}
