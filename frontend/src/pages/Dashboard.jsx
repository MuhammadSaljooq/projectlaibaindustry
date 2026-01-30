import { useState, useEffect } from 'react'
import { DollarSign, TrendingUp, Package, AlertTriangle } from 'lucide-react'
import api from '../utils/api'
import { LineChart, Line, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts'

export default function Dashboard() {
  const [dashboardData, setDashboardData] = useState(null)
  const [salesSummary, setSalesSummary] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchDashboardData()
    fetchSalesSummary()
  }, [])

  const fetchDashboardData = async () => {
    try {
      const response = await api.get('/analytics/dashboard')
      setDashboardData(response.data)
    } catch (error) {
      console.error('Failed to fetch dashboard data:', error)
    } finally {
      setLoading(false)
    }
  }

  const fetchSalesSummary = async () => {
    try {
      const response = await api.get('/analytics/sales-summary?period=daily')
      setSalesSummary(response.data)
    } catch (error) {
      console.error('Failed to fetch sales summary:', error)
    }
  }

  if (loading || !dashboardData) {
    return <div className="text-center py-8">Loading...</div>
  }

  const stats = [
    {
      name: 'Total Sales',
      value: `$${dashboardData.totalSales.toFixed(2)}`,
      icon: DollarSign,
      color: 'text-green-600',
      bgColor: 'bg-green-100',
    },
    {
      name: 'Total Profit',
      value: `$${dashboardData.totalProfit.toFixed(2)}`,
      icon: TrendingUp,
      color: 'text-blue-600',
      bgColor: 'bg-blue-100',
    },
    {
      name: 'Inventory Value',
      value: `$${dashboardData.totalInventoryValue.toFixed(2)}`,
      icon: Package,
      color: 'text-purple-600',
      bgColor: 'bg-purple-100',
    },
    {
      name: 'Low Stock Items',
      value: dashboardData.lowStockCount,
      icon: AlertTriangle,
      color: 'text-red-600',
      bgColor: 'bg-red-100',
    },
  ]

  return (
    <div>
      <h1 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-4 sm:mb-6">Dashboard</h1>

      {/* Stats Grid - Mobile Optimized */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-8">
        {stats.map((stat) => {
          const Icon = stat.icon
          return (
            <div key={stat.name} className="card p-3 sm:p-6">
              <div className="flex items-center justify-between">
                <div className="flex-1 min-w-0">
                  <p className="text-xs sm:text-sm font-medium text-gray-600 truncate">{stat.name}</p>
                  <p className="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mt-1 truncate">{stat.value}</p>
                </div>
                <div className={`${stat.bgColor} p-2 sm:p-3 rounded-full flex-shrink-0 ml-2`}>
                  <Icon className={`w-5 h-5 sm:w-6 sm:h-6 ${stat.color}`} />
                </div>
              </div>
            </div>
          )
        })}
      </div>

      {/* Charts - Mobile Optimized */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        {/* Sales Trend */}
        <div className="card p-3 sm:p-6">
          <h2 className="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Sales Trend (Last 7 Days)</h2>
          {salesSummary.length > 0 ? (
            <ResponsiveContainer width="100%" height={250}>
              <LineChart data={salesSummary}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="period" tick={{ fontSize: 10 }} />
                <YAxis tick={{ fontSize: 10 }} />
                <Tooltip />
                <Legend wrapperStyle={{ fontSize: '12px' }} />
                <Line type="monotone" dataKey="totalSales" stroke="#0ea5e9" name="Sales ($)" strokeWidth={2} />
                <Line type="monotone" dataKey="totalProfit" stroke="#10b981" name="Profit ($)" strokeWidth={2} />
              </LineChart>
            </ResponsiveContainer>
          ) : (
            <div className="text-center py-8 text-gray-500 text-sm">No sales data available</div>
          )}
        </div>

        {/* Sales by Day */}
        <div className="card p-3 sm:p-6">
          <h2 className="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Daily Sales</h2>
          {salesSummary.length > 0 ? (
            <ResponsiveContainer width="100%" height={250}>
              <BarChart data={salesSummary}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="period" tick={{ fontSize: 10 }} />
                <YAxis tick={{ fontSize: 10 }} />
                <Tooltip />
                <Legend wrapperStyle={{ fontSize: '12px' }} />
                <Bar dataKey="totalSales" fill="#0ea5e9" name="Sales ($)" />
              </BarChart>
            </ResponsiveContainer>
          ) : (
            <div className="text-center py-8 text-gray-500 text-sm">No sales data available</div>
          )}
        </div>
      </div>

      {/* Additional Info - Mobile Optimized */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mt-4 sm:mt-6">
        <div className="card p-4 sm:p-6">
          <h2 className="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Quick Stats</h2>
          <div className="space-y-2 sm:space-y-3">
            <div className="flex justify-between text-sm sm:text-base">
              <span className="text-gray-600">Total Products:</span>
              <span className="font-medium">{dashboardData.totalProducts}</span>
            </div>
            <div className="flex justify-between text-sm sm:text-base">
              <span className="text-gray-600">Recent Sales (30 days):</span>
              <span className="font-medium">{dashboardData.recentSalesCount}</span>
            </div>
            <div className="flex justify-between text-sm sm:text-base">
              <span className="text-gray-600">Average Sale:</span>
              <span className="font-medium">
                ${dashboardData.recentSalesCount > 0
                  ? (dashboardData.totalSales / dashboardData.recentSalesCount).toFixed(2)
                  : '0.00'}
              </span>
            </div>
          </div>
        </div>

        <div className="card p-4 sm:p-6">
          <h2 className="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Inventory Status</h2>
          <div className="space-y-2 sm:space-y-3">
            <div className="flex justify-between text-sm sm:text-base">
              <span className="text-gray-600">Total Stock Quantity:</span>
              <span className="font-medium">{dashboardData.totalStockQuantity || 0}</span>
            </div>
            <div className="flex justify-between text-sm sm:text-base">
              <span className="text-gray-600">Low Stock Items:</span>
              <span className={`font-medium ${dashboardData.lowStockCount > 0 ? 'text-red-600' : ''}`}>
                {dashboardData.lowStockCount}
              </span>
            </div>
            <div className="flex justify-between text-sm sm:text-base">
              <span className="text-gray-600">Inventory Value:</span>
              <span className="font-medium">${dashboardData.totalInventoryValue.toFixed(2)}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
