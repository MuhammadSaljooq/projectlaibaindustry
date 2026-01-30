import { useState } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { LayoutDashboard, Package, FolderTree, ShoppingCart, Receipt, BarChart3, Settings, Menu, X } from 'lucide-react'

export default function Layout({ children }) {
  const location = useLocation()
  const { t } = useTranslation()
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false)
  
  const navItems = [
    { path: '/', label: t('nav.dashboard'), icon: LayoutDashboard, key: 'dashboard' },
    { path: '/products', label: t('nav.products'), icon: Package, key: 'products' },
    { path: '/categories', label: t('nav.categories'), icon: FolderTree, key: 'categories' },
    { path: '/sales', label: t('nav.sales'), icon: ShoppingCart, key: 'sales' },
    { path: '/receivables', label: t('nav.receivables') || 'Receivables', icon: Receipt, key: 'receivables' },
    { path: '/reports', label: t('nav.reports'), icon: BarChart3, key: 'reports' },
    { path: '/settings', label: t('nav.settings'), icon: Settings, key: 'settings' },
  ]

  return (
    <div className="min-h-screen bg-gray-50">
      <nav className="bg-white shadow-sm border-b sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8">
          <div className="flex justify-between h-14 sm:h-16">
            <div className="flex items-center">
              <Link to="/" className="text-lg sm:text-xl font-bold text-primary-600">
                <span className="hidden sm:inline">Inventory & Sales</span>
                <span className="sm:hidden">I&S</span>
              </Link>
            </div>
            
            {/* Desktop Navigation */}
            <div className="hidden lg:flex items-center space-x-1">
              {navItems.map((item) => {
                const Icon = item.icon
                const isActive = location.pathname === item.path
                return (
                  <Link
                    key={item.path}
                    to={item.path}
                    className={`flex items-center px-2 xl:px-3 py-2 rounded-md text-xs xl:text-sm font-medium transition-colors ${
                      isActive
                        ? 'bg-primary-100 text-primary-700'
                        : 'text-gray-700 hover:bg-gray-100 hover:text-primary-600'
                    }`}
                  >
                    <Icon className="w-4 h-4 xl:w-5 xl:h-5 mr-1 xl:mr-2" />
                    <span className="hidden xl:inline">{item.label}</span>
                  </Link>
                )
              })}
            </div>

            {/* Mobile Menu Button */}
            <div className="lg:hidden flex items-center">
              <button
                onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                className="p-2 text-gray-700 hover:text-primary-600"
              >
                {mobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
              </button>
            </div>
          </div>

          {/* Mobile Navigation */}
          {mobileMenuOpen && (
            <div className="lg:hidden border-t border-gray-200 py-2">
              {navItems.map((item) => {
                const Icon = item.icon
                const isActive = location.pathname === item.path
                return (
                  <Link
                    key={item.path}
                    to={item.path}
                    onClick={() => setMobileMenuOpen(false)}
                    className={`flex items-center px-4 py-3 rounded-md text-base font-medium transition-colors ${
                      isActive
                        ? 'bg-primary-100 text-primary-700'
                        : 'text-gray-700 hover:bg-gray-100 hover:text-primary-600'
                    }`}
                  >
                    <Icon className="w-5 h-5 mr-3" />
                    {item.label}
                  </Link>
                )
              })}
            </div>
          )}
        </div>
      </nav>
      <main className="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
        {children}
      </main>
    </div>
  )
}
