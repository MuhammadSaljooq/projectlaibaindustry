import { useState, useEffect } from 'react'
import { Plus, Search, Edit, Trash2, Package } from 'lucide-react'
import api from '../utils/api'
import Modal from '../components/Modal'
import Button from '../components/Button'
import Input from '../components/Input'
import Select from '../components/Select'
import Alert from '../components/Alert'

export default function Products() {
  const [products, setProducts] = useState([])
  const [categories, setCategories] = useState([])
  const [loading, setLoading] = useState(true)
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [editingProduct, setEditingProduct] = useState(null)
  const [searchTerm, setSearchTerm] = useState('')
  const [categoryFilter, setCategoryFilter] = useState('')
  const [lowStockFilter, setLowStockFilter] = useState(false)
  const [alert, setAlert] = useState(null)
  const [formData, setFormData] = useState({
    name: '',
    sku: '',
    categoryId: '',
    costPrice: '',
    sellingPrice: '',
    description: '',
    stockQuantity: '0',
    reorderLevel: '10',
  })
  const [errors, setErrors] = useState({})

  useEffect(() => {
    fetchProducts()
    fetchCategories()
  }, [searchTerm, categoryFilter, lowStockFilter])

  const fetchProducts = async () => {
    try {
      setLoading(true)
      const params = new URLSearchParams()
      if (searchTerm) params.append('search', searchTerm)
      if (categoryFilter) params.append('categoryId', categoryFilter)
      if (lowStockFilter) params.append('lowStock', 'true')
      
      const response = await api.get(`/products?${params.toString()}`)
      setProducts(response.data)
    } catch (error) {
      setAlert({ type: 'error', message: 'Failed to fetch products' })
    } finally {
      setLoading(false)
    }
  }

  const fetchCategories = async () => {
    try {
      const response = await api.get('/categories')
      setCategories(response.data)
    } catch (error) {
      console.error('Failed to fetch categories:', error)
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setErrors({})

    try {
      if (editingProduct) {
        await api.put(`/products/${editingProduct.id}`, formData)
        setAlert({ type: 'success', message: 'Product updated successfully' })
      } else {
        await api.post('/products', formData)
        setAlert({ type: 'success', message: 'Product created successfully' })
      }
      
      setIsModalOpen(false)
      resetForm()
      fetchProducts()
    } catch (error) {
      if (error.response?.data?.errors) {
        const validationErrors = {}
        error.response.data.errors.forEach(err => {
          validationErrors[err.param] = err.msg
        })
        setErrors(validationErrors)
      } else {
        setAlert({ type: 'error', message: error.response?.data?.error || 'Operation failed' })
      }
    }
  }

  const handleEdit = (product) => {
    setEditingProduct(product)
    setFormData({
      name: product.name,
      sku: product.sku,
      categoryId: product.categoryId.toString(),
      costPrice: product.costPrice.toString(),
      sellingPrice: product.sellingPrice?.toString() || '',
      description: product.description || '',
      stockQuantity: product.stockQuantity.toString(),
      reorderLevel: product.reorderLevel.toString(),
    })
    setIsModalOpen(true)
  }

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure you want to delete this product?')) return

    try {
      await api.delete(`/products/${id}`)
      setAlert({ type: 'success', message: 'Product deleted successfully' })
      fetchProducts()
    } catch (error) {
      setAlert({ type: 'error', message: error.response?.data?.error || 'Failed to delete product' })
    }
  }

  const resetForm = () => {
    setFormData({
      name: '',
      sku: '',
      categoryId: '',
      costPrice: '',
      sellingPrice: '',
      description: '',
      stockQuantity: '0',
      reorderLevel: '10',
    })
    setEditingProduct(null)
    setErrors({})
  }

  const handleModalClose = () => {
    setIsModalOpen(false)
    resetForm()
  }

  const categoryOptions = categories.map(cat => ({
    value: cat.id.toString(),
    label: cat.name,
  }))

  return (
    <div>
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0 mb-4 sm:mb-6">
        <h1 className="text-2xl sm:text-3xl font-bold text-gray-900">Products</h1>
        <Button onClick={() => setIsModalOpen(true)} className="w-full sm:w-auto text-sm sm:text-base">
          <Plus className="w-4 h-4 sm:w-5 sm:h-5 inline mr-2" />
          <span className="hidden sm:inline">Add Product</span>
          <span className="sm:hidden">Add</span>
        </Button>
      </div>

      {alert && (
        <Alert
          type={alert.type}
          message={alert.message}
          onClose={() => setAlert(null)}
        />
      )}

      {/* Filters - Mobile Optimized */}
      <div className="card mb-4 sm:mb-6 p-3 sm:p-6">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
          <div className="relative sm:col-span-2 lg:col-span-1">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4 sm:w-5 sm:h-5" />
            <input
              type="text"
              placeholder="Search products..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="input-field pl-9 sm:pl-10 text-sm sm:text-base"
            />
          </div>
          <div className="sm:col-span-1">
            <Select
              label="Category"
              name="categoryFilter"
              value={categoryFilter}
              onChange={(e) => setCategoryFilter(e.target.value)}
              options={[{ value: '', label: 'All Categories' }, ...categoryOptions]}
            />
          </div>
          <div className="flex items-center sm:col-span-1 lg:col-span-1">
            <input
              type="checkbox"
              id="lowStock"
              checked={lowStockFilter}
              onChange={(e) => setLowStockFilter(e.target.checked)}
              className="w-4 h-4 text-primary-600 rounded"
            />
            <label htmlFor="lowStock" className="ml-2 text-xs sm:text-sm text-gray-700">
              Show low stock only
            </label>
          </div>
        </div>
      </div>

      {/* Products Table - Mobile Optimized */}
      <div className="card p-0 sm:p-6">
        {loading ? (
          <div className="text-center py-8">Loading...</div>
        ) : products.length === 0 ? (
          <div className="text-center py-8 text-gray-500">
            <Package className="w-12 h-12 mx-auto mb-4 text-gray-400" />
            <p className="text-sm sm:text-base">No products found</p>
          </div>
        ) : (
          <div className="overflow-x-auto -mx-2 sm:mx-0">
            <table className="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    <span className="hidden sm:inline">SKU</span>
                    <span className="sm:hidden">SKU</span>
                  </th>
                  <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Name
                  </th>
                  <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">
                    Category
                  </th>
                  <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    <span className="hidden sm:inline">Cost</span>
                    <span className="sm:hidden">Cost</span>
                  </th>
                  <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">
                    Selling
                  </th>
                  <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Stock
                  </th>
                  <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {products.map((product) => (
                  <tr
                    key={product.id}
                    className={product.stockQuantity <= product.reorderLevel ? 'bg-red-50' : ''}
                  >
                    <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">
                      {product.sku}
                    </td>
                    <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-900 max-w-[120px] sm:max-w-none truncate sm:whitespace-nowrap">
                      {product.name}
                    </td>
                    <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden md:table-cell">
                      {product.category.name}
                    </td>
                    <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                      ${parseFloat(product.costPrice).toFixed(2)}
                    </td>
                    <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 hidden sm:table-cell">
                      {product.sellingPrice ? `$${parseFloat(product.sellingPrice).toFixed(2)}` : '-'}
                    </td>
                    <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap">
                      <span
                        className={`text-xs sm:text-sm font-medium ${
                          product.stockQuantity <= product.reorderLevel
                            ? 'text-red-600'
                            : 'text-gray-900'
                        }`}
                      >
                        {product.stockQuantity}
                      </span>
                      {product.stockQuantity <= product.reorderLevel && (
                        <span className="ml-1 sm:ml-2 text-[10px] sm:text-xs text-red-600">Low</span>
                      )}
                    </td>
                    <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium">
                      <div className="flex items-center space-x-2">
                        <button
                          onClick={() => handleEdit(product)}
                          className="text-primary-600 hover:text-primary-900 touch-manipulation p-1"
                          aria-label="Edit"
                        >
                          <Edit className="w-4 h-4 sm:w-5 sm:h-5" />
                        </button>
                        <button
                          onClick={() => handleDelete(product.id)}
                          className="text-red-600 hover:text-red-900 touch-manipulation p-1"
                          aria-label="Delete"
                        >
                          <Trash2 className="w-4 h-4 sm:w-5 sm:h-5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Add/Edit Modal */}
      <Modal
        isOpen={isModalOpen}
        onClose={handleModalClose}
        title={editingProduct ? 'Edit Product' : 'Add Product'}
        size="lg"
      >
        <form onSubmit={handleSubmit}>
          <div className="grid grid-cols-2 gap-4">
            <Input
              label="Product Name"
              name="name"
              value={formData.name}
              onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              required
              error={errors.name}
            />
            <Input
              label="SKU"
              name="sku"
              value={formData.sku}
              onChange={(e) => setFormData({ ...formData, sku: e.target.value })}
              required
              error={errors.sku}
            />
          </div>

          <Select
            label="Category"
            name="categoryId"
            value={formData.categoryId}
            onChange={(e) => setFormData({ ...formData, categoryId: e.target.value })}
            options={categoryOptions}
            required
            error={errors.categoryId}
          />

          <div className="grid grid-cols-2 gap-4">
            <Input
              label="Cost Price"
              name="costPrice"
              type="number"
              step="0.01"
              value={formData.costPrice}
              onChange={(e) => setFormData({ ...formData, costPrice: e.target.value })}
              required
              error={errors.costPrice}
            />
            <Input
              label="Selling Price"
              name="sellingPrice"
              type="number"
              step="0.01"
              value={formData.sellingPrice}
              onChange={(e) => setFormData({ ...formData, sellingPrice: e.target.value })}
              error={errors.sellingPrice}
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <Input
              label="Stock Quantity"
              name="stockQuantity"
              type="number"
              value={formData.stockQuantity}
              onChange={(e) => setFormData({ ...formData, stockQuantity: e.target.value })}
              error={errors.stockQuantity}
            />
            <Input
              label="Reorder Level"
              name="reorderLevel"
              type="number"
              value={formData.reorderLevel}
              onChange={(e) => setFormData({ ...formData, reorderLevel: e.target.value })}
              error={errors.reorderLevel}
            />
          </div>

          <Input
            label="Description"
            name="description"
            value={formData.description}
            onChange={(e) => setFormData({ ...formData, description: e.target.value })}
            error={errors.description}
          />

          <div className="flex justify-end space-x-3 mt-6">
            <Button type="button" variant="secondary" onClick={handleModalClose}>
              Cancel
            </Button>
            <Button type="submit" variant="primary">
              {editingProduct ? 'Update' : 'Create'}
            </Button>
          </div>
        </form>
      </Modal>
    </div>
  )
}
