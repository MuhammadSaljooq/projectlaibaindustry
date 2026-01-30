import { useState, useEffect, useRef } from 'react'
import { Plus, Trash2, Save, X } from 'lucide-react'
import api from '../utils/api'
import Button from './Button'
import Input from './Input'
import Alert from './Alert'

export default function ExcelSalesEntry({ onSave, onCancel }) {
  const [products, setProducts] = useState([])
  const [rows, setRows] = useState([
    { id: 1, productId: '', quantity: 1, sellingPrice: '', productName: '', costPrice: '' }
  ])
  const [formData, setFormData] = useState({
    date: new Date().toISOString().split('T')[0],
    customerName: '',
    taxRate: '0',
    discountAmount: '0',
    discountType: 'fixed',
    applyTax: true,
  })
  const [taxSetting, setTaxSetting] = useState({ defaultRate: 0 })
  const [alert, setAlert] = useState(null)
  const [calculations, setCalculations] = useState({
    subtotal: 0,
    discount: 0,
    tax: 0,
    total: 0,
    profit: 0,
  })
  const tableRef = useRef(null)

  useEffect(() => {
    fetchProducts()
    fetchTaxSetting()
  }, [])

  useEffect(() => {
    calculateTotals()
  }, [rows, formData.taxRate, formData.discountAmount, formData.discountType, formData.applyTax])

  const fetchProducts = async () => {
    try {
      const response = await api.get('/products')
      setProducts(response.data)
    } catch (error) {
      setAlert({ type: 'error', message: 'Failed to fetch products' })
    }
  }

  const fetchTaxSetting = async () => {
    try {
      const response = await api.get('/tax')
      setTaxSetting(response.data)
      setFormData(prev => ({ ...prev, taxRate: response.data.defaultRate.toString() }))
    } catch (error) {
      console.error('Failed to fetch tax setting:', error)
    }
  }

  const calculateTotals = () => {
    let subtotal = 0
    let totalProfit = 0

    rows.forEach(row => {
      if (row.productId && row.quantity && row.sellingPrice) {
        const itemTotal = parseFloat(row.sellingPrice) * parseInt(row.quantity)
        subtotal += itemTotal
        if (row.costPrice) {
          const itemCost = parseFloat(row.costPrice) * parseInt(row.quantity)
          totalProfit += (itemTotal - itemCost)
        }
      }
    })

    let discount = 0
    if (formData.discountType === 'percentage') {
      discount = (subtotal * parseFloat(formData.discountAmount)) / 100
    } else {
      discount = parseFloat(formData.discountAmount) || 0
    }

    const subtotalAfterDiscount = subtotal - discount
    const taxRate = formData.applyTax ? parseFloat(formData.taxRate) : 0
    const tax = (subtotalAfterDiscount * taxRate) / 100
    const total = subtotalAfterDiscount + tax

    setCalculations({
      subtotal,
      discount,
      tax,
      total,
      profit: totalProfit,
    })
  }

  const handleProductChange = (rowId, productId) => {
    const product = products.find(p => p.id === parseInt(productId))
    if (product) {
      setRows(rows.map(row => 
        row.id === rowId 
          ? { 
              ...row, 
              productId, 
              productName: product.name,
              costPrice: product.costPrice,
              sellingPrice: product.sellingPrice || product.costPrice,
            }
          : row
      ))
    }
  }

  const handleCellChange = (rowId, field, value) => {
    setRows(rows.map(row => 
      row.id === rowId ? { ...row, [field]: value } : row
    ))
  }

  const addRow = () => {
    const newId = Math.max(...rows.map(r => r.id), 0) + 1
    setRows([...rows, { 
      id: newId, 
      productId: '', 
      quantity: 1, 
      sellingPrice: '', 
      productName: '', 
      costPrice: '' 
    }])
  }

  const removeRow = (rowId) => {
    if (rows.length > 1) {
      setRows(rows.filter(row => row.id !== rowId))
    }
  }

  const handleKeyDown = (e, rowId, field) => {
    if (e.key === 'Enter') {
      e.preventDefault()
      const currentIndex = rows.findIndex(r => r.id === rowId)
      if (currentIndex < rows.length - 1) {
        // Focus next row same field
        const nextRowId = rows[currentIndex + 1].id
        const input = document.querySelector(`input[data-row="${nextRowId}"][data-field="${field}"]`)
        input?.focus()
      } else {
        // Add new row if last row
        addRow()
        setTimeout(() => {
          const newRowId = Math.max(...rows.map(r => r.id), 0) + 1
          const input = document.querySelector(`input[data-row="${newRowId}"][data-field="${field}"]`)
          input?.focus()
        }, 100)
      }
    } else if (e.key === 'Tab' && e.shiftKey === false) {
      // Handle tab navigation
      e.preventDefault()
      const fields = ['productId', 'quantity', 'sellingPrice']
      const currentFieldIndex = fields.indexOf(field)
      if (currentFieldIndex < fields.length - 1) {
        const nextField = fields[currentFieldIndex + 1]
        const input = document.querySelector(`input[data-row="${rowId}"][data-field="${nextField}"]`)
        input?.focus()
      } else {
        // Move to next row
        const currentIndex = rows.findIndex(r => r.id === rowId)
        if (currentIndex < rows.length - 1) {
          const nextRowId = rows[currentIndex + 1].id
          const input = document.querySelector(`input[data-row="${nextRowId}"][data-field="productId"]`)
          input?.focus()
        } else {
          addRow()
        }
      }
    }
  }

  const handleSave = async () => {
    const validRows = rows.filter(row => 
      row.productId && row.quantity && row.sellingPrice
    )

    if (validRows.length === 0) {
      setAlert({ type: 'error', message: 'Please add at least one item' })
      return
    }

    try {
      const saleData = {
        date: formData.date,
        customerName: formData.customerName || null,
        items: validRows.map(row => ({
          productId: parseInt(row.productId),
          quantity: parseInt(row.quantity),
          sellingPrice: parseFloat(row.sellingPrice).toString(),
        })),
        taxRate: formData.applyTax ? formData.taxRate : '0',
        discountAmount: formData.discountAmount,
        discountType: formData.discountType,
      }

      await api.post('/sales', saleData)
      setAlert({ type: 'success', message: 'Sale created successfully' })
      if (onSave) {
        setTimeout(() => {
          onSave()
          if (onCancel) onCancel()
        }, 1000)
      }
    } catch (error) {
      setAlert({ type: 'error', message: error.response?.data?.error || 'Failed to create sale' })
    }
  }

  const productOptions = products
    .filter(p => p.stockQuantity > 0)
    .map(p => ({
      value: p.id.toString(),
      label: `${p.name} (Stock: ${p.stockQuantity})`,
    }))

  return (
    <div className="space-y-4">
      {alert && (
        <Alert
          type={alert.type}
          message={alert.message}
          onClose={() => setAlert(null)}
        />
      )}

      {/* Header Info */}
      <div className="grid grid-cols-3 gap-4">
        <Input
          label="Date"
          name="date"
          type="date"
          value={formData.date}
          onChange={(e) => setFormData({ ...formData, date: e.target.value })}
        />
        <Input
          label="Customer Name"
          name="customerName"
          value={formData.customerName}
          onChange={(e) => setFormData({ ...formData, customerName: e.target.value })}
        />
        <div className="flex items-end">
          <Button onClick={addRow} variant="secondary" className="w-full">
            <Plus className="w-4 h-4 inline mr-2" />
            Add Row
          </Button>
        </div>
      </div>

      {/* Excel-like Table */}
      <div className="border rounded-lg overflow-hidden bg-white">
        <div className="overflow-x-auto">
          <table ref={tableRef} className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-12">
                  #
                </th>
                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase min-w-[300px]">
                  Product
                </th>
                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">
                  Quantity
                </th>
                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">
                  Price
                </th>
                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">
                  Total
                </th>
                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-16">
                  Action
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {rows.map((row, index) => (
                <tr key={row.id} className="hover:bg-gray-50">
                  <td className="px-3 py-2 text-sm text-gray-500">
                    {index + 1}
                  </td>
                  <td className="px-3 py-2">
                    <select
                      data-row={row.id}
                      data-field="productId"
                      value={row.productId}
                      onChange={(e) => handleProductChange(row.id, e.target.value)}
                      onKeyDown={(e) => handleKeyDown(e, row.id, 'productId')}
                      className="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm"
                    >
                      <option value="">Select product...</option>
                      {productOptions.map(opt => (
                        <option key={opt.value} value={opt.value}>
                          {opt.label}
                        </option>
                      ))}
                    </select>
                  </td>
                  <td className="px-3 py-2">
                    <input
                      data-row={row.id}
                      data-field="quantity"
                      type="number"
                      min="1"
                      value={row.quantity}
                      onChange={(e) => handleCellChange(row.id, 'quantity', e.target.value)}
                      onKeyDown={(e) => handleKeyDown(e, row.id, 'quantity')}
                      className="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm"
                    />
                  </td>
                  <td className="px-3 py-2">
                    <input
                      data-row={row.id}
                      data-field="sellingPrice"
                      type="number"
                      step="0.01"
                      min="0"
                      value={row.sellingPrice}
                      onChange={(e) => handleCellChange(row.id, 'sellingPrice', e.target.value)}
                      onKeyDown={(e) => handleKeyDown(e, row.id, 'sellingPrice')}
                      className="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm"
                    />
                  </td>
                  <td className="px-3 py-2 text-sm font-medium">
                    {row.quantity && row.sellingPrice
                      ? `$${(parseFloat(row.sellingPrice) * parseInt(row.quantity)).toFixed(2)}`
                      : '$0.00'}
                  </td>
                  <td className="px-3 py-2">
                    {rows.length > 1 && (
                      <button
                        onClick={() => removeRow(row.id)}
                        className="text-red-600 hover:text-red-900"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Discount and Tax */}
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Discount Type
          </label>
          <select
            value={formData.discountType}
            onChange={(e) => setFormData({ ...formData, discountType: e.target.value })}
            className="input-field"
          >
            <option value="fixed">Fixed Amount</option>
            <option value="percentage">Percentage</option>
          </select>
        </div>
        <Input
          label={`Discount ${formData.discountType === 'percentage' ? '(%)' : '($)'}`}
          name="discountAmount"
          type="number"
          step="0.01"
          value={formData.discountAmount}
          onChange={(e) => setFormData({ ...formData, discountAmount: e.target.value })}
        />
      </div>

      <div className="grid grid-cols-2 gap-4">
        <div className="flex items-center">
          <input
            type="checkbox"
            id="applyTax"
            checked={formData.applyTax}
            onChange={(e) => setFormData({ ...formData, applyTax: e.target.checked })}
            className="w-4 h-4 text-primary-600 rounded"
          />
          <label htmlFor="applyTax" className="ml-2 text-sm text-gray-700">
            Apply Tax
          </label>
        </div>
        {formData.applyTax && (
          <Input
            label="Tax Rate (%)"
            name="taxRate"
            type="number"
            step="0.01"
            value={formData.taxRate}
            onChange={(e) => setFormData({ ...formData, taxRate: e.target.value })}
          />
        )}
      </div>

      {/* Totals Summary */}
      <div className="border-t-2 pt-4 bg-gray-50 rounded-lg p-4">
        <div className="grid grid-cols-2 gap-4 text-sm">
          <div className="space-y-2">
            <div className="flex justify-between">
              <span className="text-gray-600">Subtotal:</span>
              <span className="font-medium">${calculations.subtotal.toFixed(2)}</span>
            </div>
            {calculations.discount > 0 && (
              <div className="flex justify-between text-red-600">
                <span>Discount:</span>
                <span>-${calculations.discount.toFixed(2)}</span>
              </div>
            )}
            {calculations.tax > 0 && (
              <div className="flex justify-between">
                <span>Tax:</span>
                <span>${calculations.tax.toFixed(2)}</span>
              </div>
            )}
          </div>
          <div className="space-y-2">
            <div className="flex justify-between text-lg font-bold border-t pt-2">
              <span>Total:</span>
              <span>${calculations.total.toFixed(2)}</span>
            </div>
            <div className="flex justify-between text-sm text-green-600">
              <span>Profit:</span>
              <span>${calculations.profit.toFixed(2)}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Action Buttons */}
      <div className="flex justify-end space-x-3">
        {onCancel && (
          <Button variant="secondary" onClick={onCancel}>
            <X className="w-4 h-4 inline mr-2" />
            Cancel
          </Button>
        )}
        <Button variant="primary" onClick={handleSave}>
          <Save className="w-4 h-4 inline mr-2" />
          Save Sale
        </Button>
      </div>
    </div>
  )
}
