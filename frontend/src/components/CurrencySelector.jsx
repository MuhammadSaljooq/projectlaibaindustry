import { useCurrency } from '../context/CurrencyContext'
import { DollarSign } from 'lucide-react'

export default function CurrencySelector() {
  const { currencies, selectedCurrency, changeCurrency, loading } = useCurrency()

  if (loading || !selectedCurrency || !currencies.length) {
    return (
      <div className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-500 bg-gray-50">
        Loading currencies...
      </div>
    )
  }

  const handleChange = (e) => {
    const currencyId = parseInt(e.target.value)
    const currency = currencies.find(c => c.id === currencyId)
    if (currency) {
      changeCurrency(currency)
    }
  }

  return (
    <div className="relative w-full">
      <div className="relative">
        <DollarSign className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
        <select
          value={selectedCurrency.id}
          onChange={handleChange}
          className="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none cursor-pointer"
        >
          {currencies.map((currency) => (
            <option key={currency.id} value={currency.id}>
              {currency.code} - {currency.name} ({currency.symbol})
              {currency.isDefault ? ' [Default]' : ''}
            </option>
          ))}
        </select>
        <div className="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
          <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
          </svg>
        </div>
      </div>
      
      {selectedCurrency && (
        <div className="mt-2 text-xs text-gray-500">
          Selected: <span className="font-medium">{selectedCurrency.name}</span> ({selectedCurrency.code})
        </div>
      )}
    </div>
  )
}
