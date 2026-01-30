import { BrowserRouter as Router, Routes, Route } from 'react-router-dom'
import { CurrencyProvider } from './context/CurrencyContext'
import Layout from './components/Layout'
import Dashboard from './pages/Dashboard'
import Products from './pages/Products'
import Categories from './pages/Categories'
import Sales from './pages/Sales'
import Receivables from './pages/Receivables'
import Reports from './pages/Reports'
import Settings from './pages/Settings'
import { AppProvider } from './context/AppContext'

function App() {
  return (
    <CurrencyProvider>
      <AppProvider>
        <Router>
          <Layout>
            <Routes>
              <Route path="/" element={<Dashboard />} />
              <Route path="/products" element={<Products />} />
              <Route path="/categories" element={<Categories />} />
              <Route path="/sales" element={<Sales />} />
              <Route path="/receivables" element={<Receivables />} />
              <Route path="/reports" element={<Reports />} />
              <Route path="/settings" element={<Settings />} />
            </Routes>
          </Layout>
        </Router>
      </AppProvider>
    </CurrencyProvider>
  )
}

export default App
