import { useState, useEffect } from 'react'
import { BarChart3, TrendingUp, Package, DollarSign } from 'lucide-react'
import api from '../utils/api'
import Select from '../components/Select'
import { BarChart, Bar, LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts'

const COLORS = ['#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899']

export default function Reports() {
  const [salesSummary, setSalesSummary] = useState([])
  const [topProducts, setTopProducts] = useState([])
  const [profitMargins, setProfitMargins] = useState([])
  const [period, setPeriod] = useState('daily')
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchReports()
  }, [period])

  const fetchReports = async () => {
    try {
      setLoading(true)
      const [summaryRes, productsRes, marginsRes] = await Promise.all([
        api.get(`/analytics/sales-summary?period=${period}`),
        api.get('/analytics/top-products?limit=10'),
        api.get('/analytics/profit-margins'),
      ])
      
      setSalesSummary(summaryRes.data)
      setTopProducts(productsRes.data)
      setProfitMargins(marginsRes.data)
    } catch (error) {
      console.error('Failed to fetch reports:', error)
    } finally {
      setLoading(false)
    }
  }

  if (loading) {
    return <div className="text-center py-8">Loading...</div>
  }

  const totalSales = salesSummary.reduce((sum, item) => sum + item.totalSales, 0)
  const totalProfit = salesSummary.reduce((sum, item) => sum + item.totalProfit, 0)
  const totalTransactions = salesSummary.reduce((sum, item) => sum + item.transactionCount, 0)

  const topProductsData = topProducts.map(item => ({
    name: item.product.name,
    quantity: item.totalQuantitySold,
    sales: item.totalQuantitySold * parseFloat(item.product.sellingPrice || item.product.costPrice),
  }))

  const profitMarginsData = profitMargins.map(item => ({
    name: item.category,
    profit: item.totalProfit,
    margin: item.margin,
  }))

  return (
    <div>
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0 mb-4 sm:mb-6">
        <h1 className="text-2xl sm:text-3xl font-bold text-gray-900">Reports & Analytics</h1>
        <div className="w-full sm:w-48">
          <Select
            name="period"
            value={period}
            onChange={(e) => setPeriod(e.target.value)}
            options={[
              { value: 'daily', label: 'Daily' },
              { value: 'weekly', label: 'Weekly' },
              { value: 'monthly', label: 'Monthly' },
            ]}
          />
        </div>
      </div>

      {/* Summary Cards - Mobile Optimized */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-8">
        <div className="card p-4 sm:p-6">
          <div className="flex items-center">
            <div className="bg-blue-100 p-2 sm:p-3 rounded-full mr-3 sm:mr-4">
              <DollarSign className="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" />
            </div>
            <div>
              <p className="text-xs sm:text-sm text-gray-600">Total Sales</p>
              <p className="text-xl sm:text-2xl font-bold">${totalSales.toFixed(2)}</p>
            </div>
          </div>
        </div>
        <div className="card p-4 sm:p-6">
          <div className="flex items-center">
            <div className="bg-green-100 p-2 sm:p-3 rounded-full mr-3 sm:mr-4">
              <TrendingUp className="w-5 h-5 sm:w-6 sm:h-6 text-green-600" />
            </div>
            <div>
              <p className="text-xs sm:text-sm text-gray-600">Total Profit</p>
              <p className="text-xl sm:text-2xl font-bold">${totalProfit.toFixed(2)}</p>
            </div>
          </div>
        </div>
        <div className="card p-4 sm:p-6 sm:col-span-2 lg:col-span-1">
          <div className="flex items-center">
            <div className="bg-purple-100 p-2 sm:p-3 rounded-full mr-3 sm:mr-4">
              <BarChart3 className="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" />
            </div>
            <div>
              <p className="text-xs sm:text-sm text-gray-600">Total Transactions</p>
              <p className="text-xl sm:text-2xl font-bold">{totalTransactions}</p>
            </div>
          </div>
        </div>
      </div>

      {/* Charts - Mobile Optimized */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
        {/* Sales Summary */}
        <div className="card p-3 sm:p-6">
          <h2 className="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Sales Summary ({period})</h2>
          {salesSummary.length > 0 ? (
            <ResponsiveContainer width="100%" height={250}>
              <BarChart data={salesSummary}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="period" tick={{ fontSize: 10 }} />
                <YAxis tick={{ fontSize: 10 }} />
                <Tooltip />
                <Legend wrapperStyle={{ fontSize: '12px' }} />
                <Bar dataKey="totalSales" fill="#0ea5e9" name="Sales ($)" />
                <Bar dataKey="totalProfit" fill="#10b981" name="Profit ($)" />
              </BarChart>
            </ResponsiveContainer>
          ) : (
            <div className="text-center py-8 text-gray-500 text-sm">No data available</div>
          )}
        </div>

        {/* Top Products */}
        <div className="card p-3 sm:p-6">
          <h2 className="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Top Selling Products</h2>
          {topProductsData.length > 0 ? (
            <ResponsiveContainer width="100%" height={250}>
              <BarChart data={topProductsData} layout="vertical">
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis type="number" tick={{ fontSize: 10 }} />
                <YAxis dataKey="name" type="category" width={80} tick={{ fontSize: 9 }} />
                <Tooltip />
                <Legend wrapperStyle={{ fontSize: '12px' }} />
                <Bar dataKey="quantity" fill="#0ea5e9" name="Quantity Sold" />
              </BarChart>
            </ResponsiveContainer>
          ) : (
            <div className="text-center py-8 text-gray-500 text-sm">No data available</div>
          )}
        </div>
      </div>

      {/* Profit Margins */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div className="card">
          <h2 className="text-xl font-semibold mb-4">Profit by Category</h2>
          {profitMarginsData.length > 0 ? (
            <ResponsiveContainer width="100%" height={300}>
              <BarChart data={profitMarginsData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="name" />
                <YAxis />
                <Tooltip />
                <Legend />
                <Bar dataKey="profit" fill="#10b981" name="Profit ($)" />
              </BarChart>
            </ResponsiveContainer>
          ) : (
            <div className="text-center py-8 text-gray-500">No data available</div>
          )}
        </div>

        <div className="card">
          <h2 className="text-xl font-semibold mb-4">Profit Margins by Category</h2>
          {profitMarginsData.length > 0 ? (
            <ResponsiveContainer width="100%" height={300}>
              <BarChart data={profitMarginsData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="name" />
                <YAxis />
                <Tooltip />
                <Legend />
                <Bar dataKey="margin" fill="#f59e0b" name="Margin (%)" />
              </BarChart>
            </ResponsiveContainer>
          ) : (
            <div className="text-center py-8 text-gray-500">No data available</div>
          )}
        </div>
      </div>

      {/* Top Products Table */}
      <div className="card">
        <h2 className="text-xl font-semibold mb-4">Top Products Details</h2>
        {topProducts.length > 0 ? (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Product
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Category
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Quantity Sold
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Times Sold
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {topProducts.map((item, index) => (
                  <tr key={item.product.id}>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {item.product.name}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {item.product.category.name}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {item.totalQuantitySold}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {item.timesSold}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <div className="text-center py-8 text-gray-500">No data available</div>
        )}
      </div>
    </div>
  )
}
