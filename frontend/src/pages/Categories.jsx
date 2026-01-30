import { useState, useEffect } from 'react'
import { Plus, Edit, Trash2, FolderTree } from 'lucide-react'
import api from '../utils/api'
import Modal from '../components/Modal'
import Button from '../components/Button'
import Input from '../components/Input'
import Alert from '../components/Alert'

export default function Categories() {
  const [categories, setCategories] = useState([])
  const [loading, setLoading] = useState(true)
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [editingCategory, setEditingCategory] = useState(null)
  const [alert, setAlert] = useState(null)
  const [formData, setFormData] = useState({
    name: '',
    description: '',
  })
  const [errors, setErrors] = useState({})

  useEffect(() => {
    fetchCategories()
  }, [])

  const fetchCategories = async () => {
    try {
      setLoading(true)
      const response = await api.get('/categories')
      setCategories(response.data)
    } catch (error) {
      setAlert({ type: 'error', message: 'Failed to fetch categories' })
    } finally {
      setLoading(false)
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setErrors({})

    try {
      if (editingCategory) {
        await api.put(`/categories/${editingCategory.id}`, formData)
        setAlert({ type: 'success', message: 'Category updated successfully' })
      } else {
        await api.post('/categories', formData)
        setAlert({ type: 'success', message: 'Category created successfully' })
      }
      
      setIsModalOpen(false)
      resetForm()
      fetchCategories()
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

  const handleEdit = (category) => {
    setEditingCategory(category)
    setFormData({
      name: category.name,
      description: category.description || '',
    })
    setIsModalOpen(true)
  }

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure you want to delete this category?')) return

    try {
      await api.delete(`/categories/${id}`)
      setAlert({ type: 'success', message: 'Category deleted successfully' })
      fetchCategories()
    } catch (error) {
      const errorMsg = error.response?.data?.error || 'Failed to delete category'
      if (error.response?.data?.productCount) {
        setAlert({
          type: 'error',
          message: `${errorMsg}. This category has ${error.response.data.productCount} product(s).`,
        })
      } else {
        setAlert({ type: 'error', message: errorMsg })
      }
    }
  }

  const resetForm = () => {
    setFormData({
      name: '',
      description: '',
    })
    setEditingCategory(null)
    setErrors({})
  }

  const handleModalClose = () => {
    setIsModalOpen(false)
    resetForm()
  }

  return (
    <div>
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0 mb-4 sm:mb-6">
        <h1 className="text-2xl sm:text-3xl font-bold text-gray-900">Categories</h1>
        <Button onClick={() => setIsModalOpen(true)} className="w-full sm:w-auto text-sm sm:text-base">
          <Plus className="w-4 h-4 sm:w-5 sm:h-5 inline mr-2" />
          <span className="hidden sm:inline">Add Category</span>
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

      <div className="card p-3 sm:p-6">
        {loading ? (
          <div className="text-center py-8">Loading...</div>
        ) : categories.length === 0 ? (
          <div className="text-center py-8 text-gray-500">
            <FolderTree className="w-12 h-12 mx-auto mb-4 text-gray-400" />
            <p className="text-sm sm:text-base">No categories found</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            {categories.map((category) => (
              <div
                key={category.id}
                className="border border-gray-200 rounded-lg p-3 sm:p-4 hover:shadow-md transition-shadow"
              >
                <div className="flex justify-between items-start mb-2">
                  <h3 className="text-base sm:text-lg font-semibold text-gray-900 flex-1 pr-2">{category.name}</h3>
                  <div className="flex space-x-2 flex-shrink-0">
                    <button
                      onClick={() => handleEdit(category)}
                      className="text-primary-600 hover:text-primary-900 touch-manipulation p-1"
                      aria-label="Edit"
                    >
                      <Edit className="w-4 h-4 sm:w-5 sm:h-5" />
                    </button>
                    <button
                      onClick={() => handleDelete(category.id)}
                      className="text-red-600 hover:text-red-900 touch-manipulation p-1"
                      aria-label="Delete"
                    >
                      <Trash2 className="w-4 h-4 sm:w-5 sm:h-5" />
                    </button>
                  </div>
                </div>
                {category.description && (
                  <p className="text-xs sm:text-sm text-gray-600 mb-2 line-clamp-2">{category.description}</p>
                )}
                <p className="text-xs text-gray-500">
                  {category._count?.products || 0} product(s)
                </p>
              </div>
            ))}
          </div>
        )}
      </div>

      <Modal
        isOpen={isModalOpen}
        onClose={handleModalClose}
        title={editingCategory ? 'Edit Category' : 'Add Category'}
      >
        <form onSubmit={handleSubmit}>
          <Input
            label="Category Name"
            name="name"
            value={formData.name}
            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
            required
            error={errors.name}
          />

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
              {editingCategory ? 'Update' : 'Create'}
            </Button>
          </div>
        </form>
      </Modal>
    </div>
  )
}
