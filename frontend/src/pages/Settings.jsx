import { useState, useEffect } from 'react'
import { Settings as SettingsIcon } from 'lucide-react'
import { useTranslation } from 'react-i18next'
import api from '../utils/api'
import Button from '../components/Button'
import Input from '../components/Input'
import Alert from '../components/Alert'
import CurrencySelector from '../components/CurrencySelector'
import LanguageSelector from '../components/LanguageSelector'
import { useCurrency } from '../context/CurrencyContext'

export default function Settings() {
  const { t } = useTranslation()
  const { selectedCurrency, defaultCurrency, currencies, loading: currencyLoading } = useCurrency()
  const [taxSetting, setTaxSetting] = useState(null)
  const [loading, setLoading] = useState(true)
  const [alert, setAlert] = useState(null)
  const [formData, setFormData] = useState({
    defaultRate: '0',
    description: '',
  })
  const [errors, setErrors] = useState({})

  useEffect(() => {
    fetchTaxSetting()
  }, [])

  const fetchTaxSetting = async () => {
    try {
      setLoading(true)
      const response = await api.get('/tax')
      setTaxSetting(response.data)
      setFormData({
        defaultRate: response.data.defaultRate.toString(),
        description: response.data.description || '',
      })
      setAlert(null) // Clear any previous errors
    } catch (error) {
      console.error('Failed to fetch tax settings:', error)
      setAlert({ 
        type: 'error', 
        message: error.response?.data?.error || 'Failed to fetch tax settings. Please check if the backend server is running.' 
      })
      // Set default values if API fails
      setFormData({
        defaultRate: '0',
        description: '',
      })
    } finally {
      setLoading(false)
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setErrors({})

    if (!taxSetting || !taxSetting.id) {
      setAlert({ type: 'error', message: 'Tax settings not loaded. Please refresh the page.' })
      return
    }

    try {
      const response = await api.put(`/tax/${taxSetting.id}`, formData)
      setTaxSetting(response.data)
      setAlert({ type: 'success', message: 'Tax settings updated successfully' })
    } catch (error) {
      if (error.response?.data?.errors) {
        const validationErrors = {}
        error.response.data.errors.forEach(err => {
          validationErrors[err.param] = err.msg
        })
        setErrors(validationErrors)
      } else {
        setAlert({ type: 'error', message: error.response?.data?.error || 'Failed to update settings. Please check if the backend server is running.' })
      }
    }
  }

  if (loading || currencyLoading) {
    return (
      <div className="text-center py-8">
        <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        <p className="mt-4 text-gray-600">Loading settings...</p>
      </div>
    )
  }

  return (
    <div>
      <div className="flex items-center mb-6">
        <SettingsIcon className="w-8 h-8 text-primary-600 mr-3" />
        <h1 className="text-2xl sm:text-3xl font-bold text-gray-900">{t('settings.title')}</h1>
      </div>

      {alert && (
        <Alert
          type={alert.type}
          message={alert.message}
          onClose={() => setAlert(null)}
        />
      )}

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        {/* Tax Settings */}
        <div className="card">
          <h2 className="text-lg sm:text-xl font-semibold mb-4">{t('settings.taxSettings')}</h2>
          <form onSubmit={handleSubmit}>
            <Input
              label={t('settings.defaultTaxRate')}
              name="defaultRate"
              type="number"
              step="0.01"
              min="0"
              max="100"
              value={formData.defaultRate}
              onChange={(e) => setFormData({ ...formData, defaultRate: e.target.value })}
              required
              error={errors.defaultRate}
            />

            <Input
              label={t('common.description')}
              name="description"
              value={formData.description}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              error={errors.description}
            />

            <div className="flex justify-end mt-6">
              <Button type="submit" variant="primary">
                {t('settings.updateTaxRate')}
              </Button>
            </div>
          </form>
        </div>

        {/* Currency Settings */}
        <div className="card">
          <h2 className="text-lg sm:text-xl font-semibold mb-4">{t('settings.currencySettings')}</h2>
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                {t('settings.defaultCurrency')}
              </label>
              {currencyLoading ? (
                <div className="text-sm text-gray-500">Loading...</div>
              ) : defaultCurrency ? (
                <div className="p-3 bg-gray-50 rounded-lg border border-gray-200">
                  <div className="flex items-center justify-between">
                    <div>
                      <div className="font-medium text-gray-900">{defaultCurrency.name}</div>
                      <div className="text-sm text-gray-500">
                        {defaultCurrency.code} - {defaultCurrency.symbol}
                      </div>
                    </div>
                    <span className="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">
                      {t('currency.isDefault')}
                    </span>
                  </div>
                </div>
              ) : (
                <div className="text-sm text-red-500">Default currency not available</div>
              )}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                {t('settings.selectCurrency')}
              </label>
              {currencyLoading ? (
                <div className="text-sm text-gray-500">Loading currencies...</div>
              ) : selectedCurrency ? (
                <>
                  <CurrencySelector />
                  <div className="mt-2 text-sm text-gray-600">
                    {t('common.current')}: {selectedCurrency.name} ({selectedCurrency.code})
                  </div>
                </>
              ) : (
                <div className="text-sm text-red-500">Failed to load currencies</div>
              )}
            </div>
          </div>
        </div>

        {/* Language Settings */}
        <div className="card">
          <h2 className="text-lg sm:text-xl font-semibold mb-4">{t('settings.languageSettings')}</h2>
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                {t('settings.selectLanguage')}
              </label>
              <div className="flex items-center space-x-2">
                <LanguageSelector />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
