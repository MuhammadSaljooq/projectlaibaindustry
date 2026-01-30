import { useState, useEffect, useRef } from 'react'
import { Plus, Trash2, ShoppingCart, Save, FileSpreadsheet, Search, X, Filter } from 'lucide-react'
import api from '../utils/api'
import Button from '../components/Button'
import Input from '../components/Input'
import Alert from '../components/Alert'

export default function Sales() {
  const [sales, setSales] = useState([])
  const [products, setProducts] = useState([])
  const [taxSetting, setTaxSetting] = useState({ defaultRate: 0 })
  const [loading, setLoading] = useState(true)
  const [alert, setAlert] = useState(null)
  const [showSalesList, setShowSalesList] = useState(true) // Always show search by default
  const [autoSaving, setAutoSaving] = useState(false)
  const [lastSaved, setLastSaved] = useState(null)
  const autoSaveTimeoutRef = useRef(null)
  
  // Search and filter state
  const [searchFilters, setSearchFilters] = useState({
    search: '', // General search
    invoiceNumber: '',
    customerName: '',
    customerCode: '',
    startDate: '',
    endDate: '',
  })
  const [showFilters, setShowFilters] = useState(false)
  const searchTimeoutRef = useRef(null)
  
  // Excel-like grid state
  const [rows, setRows] = useState([
    { id: 1, date: '', customerCode: '', customerName: '', invoiceNumber: '', taxRate: '', productName: '', quantity: '', sellingPrice: '', costPrice: '', amount: '', vat: '', subtotal: '' }
  ])
  const [selectedCell, setSelectedCell] = useState(null)
  const [editingCell, setEditingCell] = useState(null)
  const [headerData, setHeaderData] = useState({
    discountAmount: '0',
    discountType: 'fixed',
    applyTax: true,
    vatPercentage: '15', // Default VAT percentage
  })
  const [calculations, setCalculations] = useState({
    subtotal: 0,
    discount: 0,
    tax: 0,
    total: 0,
    profit: 0,
  })
  const gridRef = useRef(null)

  useEffect(() => {
    fetchSales({}) // Initial load with no filters
    fetchProducts()
    fetchTaxSetting()
  }, [])
  
  // Cleanup timeouts on unmount
  useEffect(() => {
    return () => {
      if (searchTimeoutRef.current) {
        clearTimeout(searchTimeoutRef.current)
      }
      if (autoSaveTimeoutRef.current) {
        clearTimeout(autoSaveTimeoutRef.current)
      }
    }
  }, [])
  
  // Track which rows have been saved to avoid duplicate saves
  const savedRowIdsRef = useRef(new Set())
  
  // Auto-save function (silent, no clearing rows)
  const autoSaveSales = async (validRows) => {
    if (validRows.length === 0) return
    
    try {
      setAutoSaving(true)
      const firstRow = validRows[0]
      const currentHeaderData = headerData // Capture current headerData
      const saleData = {
        date: firstRow.date || new Date().toISOString().split('T')[0],
        customerCode: firstRow.customerCode || null,
        customerName: firstRow.customerName || null,
        invoiceNumber: firstRow.invoiceNumber || null,
        items: validRows.map(row => ({
          productName: row.productName.trim(),
          quantity: parseInt(row.quantity),
          sellingPrice: parseFloat(row.sellingPrice).toString(),
        })),
        taxRate: currentHeaderData.applyTax ? currentHeaderData.vatPercentage : '0',
        discountAmount: currentHeaderData.discountAmount,
        discountType: currentHeaderData.discountType,
      }

      await api.post('/sales', saleData)
      
      // Refresh products and sales list
      await fetchProducts()
      fetchSales(searchFilters)
      
      // Update last saved time
      setLastSaved(new Date())
      
      // Remove saved rows from the grid (keep only incomplete ones)
      const incompleteRows = rows.filter(row => {
        const isIncomplete = !row.productName || !row.quantity || !row.sellingPrice
        if (!isIncomplete) {
          // Remove from saved tracking when row is removed
          savedRowIdsRef.current.delete(row.id)
        }
        return isIncomplete
      })
      
      if (incompleteRows.length === 0) {
        // Add one empty row if none exist
        const today = new Date().toISOString().split('T')[0]
        const nextInvoiceNumber = getNextInvoiceNumber()
        const nextCustomerCode = getNextCustomerCode()
        const newRow = { 
          id: Math.max(...rows.map(r => r.id), 0) + 1, 
          date: today,
          customerCode: nextCustomerCode,
          customerName: '',
          invoiceNumber: nextInvoiceNumber,
          taxRate: (taxSetting.defaultRate || 15).toString(),
          productName: '', 
          quantity: '', 
          sellingPrice: '', 
          costPrice: '',
          amount: '',
          vat: '',
          subtotal: ''
        }
        setRows([newRow])
      } else {
        setRows(incompleteRows)
      }
    } catch (error) {
      console.error('Auto-save failed:', error)
      // Don't show error for auto-save failures, just log them
    } finally {
      setAutoSaving(false)
    }
  }
  
  // Auto-save when row is completed (only once per row)
  useEffect(() => {
    const validRows = rows.filter(row => {
      const isComplete = row.productName && row.quantity && row.sellingPrice
      const notSaved = !savedRowIdsRef.current.has(row.id)
      return isComplete && notSaved
    })
    
    if (validRows.length > 0) {
      // Clear existing timeout
      if (autoSaveTimeoutRef.current) {
        clearTimeout(autoSaveTimeoutRef.current)
      }
      
      // Debounce auto-save by 2 seconds after user stops typing
      autoSaveTimeoutRef.current = setTimeout(async () => {
        const rowsToSave = rows.filter(row => {
          const isComplete = row.productName && row.quantity && row.sellingPrice
          const notSaved = !savedRowIdsRef.current.has(row.id)
          return isComplete && notSaved
        })
        
        if (rowsToSave.length > 0) {
          await autoSaveSales(rowsToSave)
          // Mark rows as saved
          rowsToSave.forEach(row => savedRowIdsRef.current.add(row.id))
        }
      }, 2000)
    }
    
    return () => {
      if (autoSaveTimeoutRef.current) {
        clearTimeout(autoSaveTimeoutRef.current)
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rows])

  useEffect(() => {
    calculateTotals()
  }, [rows, headerData.discountAmount, headerData.discountType, headerData.applyTax, headerData.vatPercentage])

  const fetchSales = async (filters = {}) => {
    try {
      setLoading(true)
      const params = new URLSearchParams()
      
      // Add filters to query params
      if (filters.search) params.append('search', filters.search)
      if (filters.invoiceNumber) params.append('invoiceNumber', filters.invoiceNumber)
      if (filters.customerName) params.append('customerName', filters.customerName)
      if (filters.customerCode) params.append('customerCode', filters.customerCode)
      if (filters.startDate) params.append('startDate', filters.startDate)
      if (filters.endDate) params.append('endDate', filters.endDate)
      
      const queryString = params.toString()
      const url = queryString ? `/sales?${queryString}` : '/sales'
      const response = await api.get(url)
      setSales(response.data)
    } catch (error) {
      setAlert({ type: 'error', message: 'Failed to fetch sales' })
    } finally {
      setLoading(false)
    }
  }
  
  // Debounced search function
  const handleSearchChange = (field, value) => {
    const newFilters = { ...searchFilters, [field]: value }
    setSearchFilters(newFilters)
    
    // Clear existing timeout
    if (searchTimeoutRef.current) {
      clearTimeout(searchTimeoutRef.current)
    }
    
    // Debounce search by 500ms
    searchTimeoutRef.current = setTimeout(() => {
      fetchSales(newFilters)
    }, 500)
  }
  
  // Clear all filters
  const clearFilters = () => {
    const emptyFilters = {
      search: '',
      invoiceNumber: '',
      customerName: '',
      customerCode: '',
      startDate: '',
      endDate: '',
    }
    setSearchFilters(emptyFilters)
    fetchSales(emptyFilters)
  }
  
  // Check if any filters are active
  const hasActiveFilters = Object.values(searchFilters).some(value => value !== '')

  const fetchProducts = async () => {
    try {
      const response = await api.get('/products')
      setProducts(response.data)
    } catch (error) {
      console.error('Failed to fetch products:', error)
    }
  }

  const fetchTaxSetting = async () => {
    try {
      const response = await api.get('/tax')
      setTaxSetting(response.data)
      // Set default VAT percentage if not already set
      if (!headerData.vatPercentage || headerData.vatPercentage === '15') {
        const defaultVat = response.data.defaultRate || 15
        setHeaderData(prev => ({ ...prev, vatPercentage: defaultVat.toString() }))
      }
      // Set default tax rate and date in first row
      if (rows.length > 0 && (!rows[0].taxRate || !rows[0].date)) {
        const today = new Date().toISOString().split('T')[0]
        const defaultTaxRate = response.data.defaultRate || 15 // Default to 15% if not set
        setRows(rows.map((row, index) => {
          if (index === 0) {
            const baseDate = new Date(today)
            return { 
              ...row, 
              taxRate: row.taxRate || defaultTaxRate.toString(), 
              date: row.date || today 
            }
          } else if (!row.date) {
            // Auto-increment date for subsequent rows
            const baseDate = new Date(today)
            baseDate.setDate(baseDate.getDate() + index)
            return { ...row, date: baseDate.toISOString().split('T')[0], taxRate: row.taxRate || defaultTaxRate.toString() }
          }
          return { ...row, taxRate: row.taxRate || defaultTaxRate.toString() }
        }))
      }
    } catch (error) {
      console.error('Failed to fetch tax setting:', error)
    }
  }

  const calculateTotals = () => {
    let subtotal = 0
    let totalProfit = 0
    let totalVat = 0

    // Get VAT rate from header
    const vatRate = headerData.applyTax ? parseFloat(headerData.vatPercentage || 15) : 0

    // Update row calculations (amount, VAT, and subtotal per row)
    const updatedRows = rows.map(row => {
      if (row.productName && row.quantity && row.sellingPrice) {
        const qty = parseFloat(row.quantity) || 0
        const price = parseFloat(row.sellingPrice) || 0
        const amount = price * qty
        
        // Calculate VAT for this row
        const rowVat = (amount * vatRate) / 100
        const rowSubtotal = amount + rowVat
        
        subtotal += amount
        totalVat += rowVat
        
        if (row.costPrice) {
          const itemCost = parseFloat(row.costPrice) * qty
          totalProfit += (amount - itemCost)
        }
        
        return { 
          ...row, 
          amount: amount.toFixed(2),
          vat: rowVat.toFixed(2),
          subtotal: rowSubtotal.toFixed(2)
        }
      }
      return { ...row, amount: '', vat: '', subtotal: '' }
    })
    
    setRows(updatedRows)

    // Calculate discount
    let discount = 0
    if (headerData.discountType === 'percentage') {
      discount = (subtotal * parseFloat(headerData.discountAmount)) / 100
    } else {
      discount = parseFloat(headerData.discountAmount) || 0
    }

    const subtotalAfterDiscount = subtotal - discount
    
    // Calculate total VAT after discount (proportional adjustment)
    // If there's a discount, adjust VAT proportionally
    const vatAfterDiscount = subtotal > 0 ? (totalVat * (subtotalAfterDiscount / subtotal)) : 0
    
    // Total = Subtotal (after discount) + VAT (after discount)
    const total = subtotalAfterDiscount + vatAfterDiscount

    setCalculations({
      subtotal: subtotalAfterDiscount, // Subtotal after discount
      discount,
      tax: vatAfterDiscount,
      total,
      profit: totalProfit,
    })
  }

  const handleProductNameChange = (rowId, productName) => {
    // Check if product exists in our list
    const existingProduct = products.find(p => 
      p.name.toLowerCase() === productName.toLowerCase()
    )
    
    if (existingProduct) {
      // Product exists, auto-fill price
      setRows(rows.map(row => 
        row.id === rowId 
          ? { 
              ...row, 
              productName,
              costPrice: existingProduct.costPrice,
              sellingPrice: existingProduct.sellingPrice || existingProduct.costPrice,
            }
          : row
      ))
    } else {
      // New product, just update the name
      setRows(rows.map(row => 
        row.id === rowId 
          ? { ...row, productName }
          : row
      ))
    }
  }

  const handleCellChange = (rowId, field, value) => {
    if (field === 'productName') {
      handleProductNameChange(rowId, value)
    } else if (field === 'date') {
      // When date changes, update subsequent rows to maintain increment
      const currentIndex = rows.findIndex(r => r.id === rowId)
      setRows(rows.map((row, index) => {
        if (row.id === rowId) {
          return { ...row, date: value }
        } else if (index > currentIndex && value) {
          // Update subsequent rows to increment by their position
          const baseDate = new Date(value)
          baseDate.setDate(baseDate.getDate() + (index - currentIndex))
          return { ...row, date: baseDate.toISOString().split('T')[0] }
        }
        return row
      }))
    } else if (field === 'invoiceNumber') {
      // When invoice number changes, auto-increment for subsequent rows
      const currentIndex = rows.findIndex(r => r.id === rowId)
      const baseNumber = value ? parseInt(value.toString().match(/(\d+)$/)?.[1] || '0') : 0
      setRows(rows.map((row, index) => {
        if (row.id === rowId) {
          return { ...row, invoiceNumber: value }
        } else if (index > currentIndex && value && baseNumber > 0) {
          // Auto-increment invoice number for subsequent rows
          const prefix = value.toString().replace(/\d+$/, '')
          const nextNumber = (baseNumber + (index - currentIndex)).toString()
          return { ...row, invoiceNumber: prefix + nextNumber }
        }
        return row
      }))
    } else if (field === 'customerCode') {
      // When customer code changes, auto-increment for subsequent rows
      const currentIndex = rows.findIndex(r => r.id === rowId)
      const baseNumber = value ? parseInt(value.toString().match(/(\d+)$/)?.[1] || '0') : 0
      setRows(rows.map((row, index) => {
        if (row.id === rowId) {
          return { ...row, customerCode: value }
        } else if (index > currentIndex && value && baseNumber > 0) {
          // Auto-increment customer code for subsequent rows
          const prefix = value.toString().replace(/\d+$/, '')
          const nextNumber = (baseNumber + (index - currentIndex)).toString()
          return { ...row, customerCode: prefix + nextNumber }
        }
        return row
      }))
    } else {
      setRows(rows.map(row => 
        row.id === rowId ? { ...row, [field]: value } : row
      ))
    }
  }

  // Helper function to get next invoice number
  const getNextInvoiceNumber = () => {
    // Get all invoice numbers from current rows
    const invoiceNumbers = rows
      .map(row => row.invoiceNumber)
      .filter(inv => inv && inv.trim() !== '')
      .map(inv => {
        // Extract numeric part if invoice number contains numbers
        const match = inv.toString().match(/(\d+)$/)
        return match ? parseInt(match[1]) : 0
      })
    
    // Also check saved sales for the highest invoice number
    const savedInvoiceNumbers = sales
      .map(sale => sale.invoiceNumber)
      .filter(inv => inv && inv.trim() !== '')
      .map(inv => {
        const match = inv.toString().match(/(\d+)$/)
        return match ? parseInt(match[1]) : 0
      })
    
    const allNumbers = [...invoiceNumbers, ...savedInvoiceNumbers]
    const maxNumber = allNumbers.length > 0 ? Math.max(...allNumbers) : 0
    return (maxNumber + 1).toString()
  }

  // Helper function to get next customer code
  const getNextCustomerCode = () => {
    // Get all customer codes from current rows
    const customerCodes = rows
      .map(row => row.customerCode)
      .filter(code => code && code.trim() !== '')
      .map(code => {
        // Extract numeric part if customer code contains numbers
        const match = code.toString().match(/(\d+)$/)
        return match ? parseInt(match[1]) : 0
      })
    
    // Also check saved sales for the highest customer code
    const savedCustomerCodes = sales
      .map(sale => sale.customerCode)
      .filter(code => code && code.trim() !== '')
      .map(code => {
        const match = code.toString().match(/(\d+)$/)
        return match ? parseInt(match[1]) : 0
      })
    
    const allNumbers = [...customerCodes, ...savedCustomerCodes]
    const maxNumber = allNumbers.length > 0 ? Math.max(...allNumbers) : 0
    return (maxNumber + 1).toString()
  }

  const addRow = () => {
    const newId = Math.max(...rows.map(r => r.id), 0) + 1
    const lastRow = rows[rows.length - 1] || {}
    const firstRow = rows[0] || {}
    
    // Calculate next date (increment by 1 day from last row)
    let nextDate = new Date().toISOString().split('T')[0]
    if (lastRow.date) {
      const lastDate = new Date(lastRow.date)
      lastDate.setDate(lastDate.getDate() + 1)
      nextDate = lastDate.toISOString().split('T')[0]
    } else if (firstRow.date) {
      const firstDate = new Date(firstRow.date)
      firstDate.setDate(firstDate.getDate() + rows.length)
      nextDate = firstDate.toISOString().split('T')[0]
    }
    
    // Get next invoice number and customer code
    const nextInvoiceNumber = getNextInvoiceNumber()
    const nextCustomerCode = getNextCustomerCode()
    
    setRows([...rows, { 
      id: newId, 
      date: nextDate,
      customerCode: nextCustomerCode, // Auto-increment customer code
      customerName: '', // Empty customer name for new rows
      invoiceNumber: nextInvoiceNumber, // Auto-increment invoice number
      taxRate: firstRow.taxRate || (taxSetting.defaultRate || 15).toString(),
      productName: '', 
      quantity: '', 
      sellingPrice: '', 
      costPrice: '',
      amount: '',
      vat: '',
      subtotal: ''
    }])
  }

  // Auto-add row when user finishes editing last row's price field (infinite rows like Excel)
  const handlePriceBlur = (rowId) => {
    const currentIndex = rows.findIndex(r => r.id === rowId)
    const isLastRow = currentIndex === rows.length - 1
    const currentRow = rows[currentIndex]
    
    // If this is the last row and it has data, add a new empty row with incremented date
    if (isLastRow && (currentRow.productName || currentRow.quantity || currentRow.sellingPrice)) {
      const hasEmptyRow = rows.some(row => 
        !row.productName && !row.quantity && !row.sellingPrice && row.id !== rowId
      )
      if (!hasEmptyRow) {
        addRow() // This will automatically increment the date
      }
    }
  }

  const deleteRow = (rowId) => {
    if (rows.length > 1) {
      setRows(rows.filter(row => row.id !== rowId))
    }
  }

  const handleCellClick = (rowId, field) => {
    setSelectedCell({ rowId, field })
    setEditingCell({ rowId, field })
  }

  const handleCellBlur = () => {
    setEditingCell(null)
  }

  const handleKeyDown = async (e, rowId, field) => {
    const currentIndex = rows.findIndex(r => r.id === rowId)
    const currentRow = rows.find(r => r.id === rowId)
    
    if (e.key === 'Enter') {
      e.preventDefault()
      
      // Check if current row has all required data
      if (currentRow.productName && currentRow.quantity && currentRow.sellingPrice) {
        // Auto-save the sale
        await handleSave()
      }
      
      // Always move to next row (or create new one)
      if (currentIndex < rows.length - 1) {
        const nextRowId = rows[currentIndex + 1].id
        setSelectedCell({ rowId: nextRowId, field })
        setEditingCell({ rowId: nextRowId, field })
      } else {
        // Add new row automatically (infinite rows like Excel)
        addRow()
        setTimeout(() => {
          const newRowId = Math.max(...rows.map(r => r.id), 0) + 1
          setSelectedCell({ rowId: newRowId, field })
          setEditingCell({ rowId: newRowId, field })
        }, 100)
      }
    } else if (e.key === 'Tab') {
      e.preventDefault()
      const fields = ['date', 'customerCode', 'customerName', 'invoiceNumber', 'productName', 'quantity', 'sellingPrice']
      const currentFieldIndex = fields.indexOf(field)
      if (currentFieldIndex < fields.length - 1) {
        const nextField = fields[currentFieldIndex + 1]
        setSelectedCell({ rowId, field: nextField })
        setEditingCell({ rowId, field: nextField })
      } else if (currentIndex < rows.length - 1) {
        const nextRowId = rows[currentIndex + 1].id
        setSelectedCell({ rowId: nextRowId, field: 'date' })
        setEditingCell({ rowId: nextRowId, field: 'date' })
      } else {
        addRow()
      }
    } else if (e.key === 'ArrowDown') {
      e.preventDefault()
      if (currentIndex < rows.length - 1) {
        const nextRowId = rows[currentIndex + 1].id
        setSelectedCell({ rowId: nextRowId, field })
        setEditingCell({ rowId: nextRowId, field })
      }
    } else if (e.key === 'ArrowUp') {
      e.preventDefault()
      if (currentIndex > 0) {
        const prevRowId = rows[currentIndex - 1].id
        setSelectedCell({ rowId: prevRowId, field })
        setEditingCell({ rowId: prevRowId, field })
      }
    } else if (e.key === 'Delete' || e.key === 'Backspace') {
      if (['date', 'customerCode', 'customerName', 'invoiceNumber', 'taxRate', 'productName', 'quantity', 'sellingPrice'].includes(field)) {
        handleCellChange(rowId, field, '')
      }
    }
  }

  const handleSave = async (clearCompletedRows = true) => {
    const validRows = rows.filter(row => 
      row.productName && row.quantity && row.sellingPrice
    )

    if (validRows.length === 0) {
      if (!clearCompletedRows) {
        // Don't show error if auto-saving (Enter key)
        return
      }
      setAlert({ type: 'error', message: 'Please add at least one item' })
      return
    }

    try {
      // Get date, customer, and tax from first row
      const firstRow = validRows[0]
      const saleData = {
        date: firstRow.date || new Date().toISOString().split('T')[0],
        customerCode: firstRow.customerCode || null,
        customerName: firstRow.customerName || null,
        invoiceNumber: firstRow.invoiceNumber || null,
        items: validRows.map(row => ({
          productName: row.productName.trim(),
          quantity: parseInt(row.quantity),
          sellingPrice: parseFloat(row.sellingPrice).toString(),
        })),
        taxRate: headerData.applyTax ? headerData.vatPercentage : '0',
        discountAmount: headerData.discountAmount,
        discountType: headerData.discountType,
      }

      await api.post('/sales', saleData)
      setAlert({ type: 'success', message: 'Sale recorded successfully! Products added to database.' })
      
      // Refresh products list to include newly created ones
      await fetchProducts()
      
      if (clearCompletedRows) {
        // Remove completed rows and keep empty ones
        const emptyRows = rows.filter(row => 
          !row.productName && !row.quantity && !row.sellingPrice
        )
        
        if (emptyRows.length === 0) {
          // Add one empty row if none exist
          const today = new Date().toISOString().split('T')[0]
          setRows([{ 
            id: 1, 
            date: today,
            customerCode: '',
            customerName: '',
            invoiceNumber: '',
            taxRate: (taxSetting.defaultRate || 15).toString(),
            productName: '', 
            quantity: '', 
            sellingPrice: '', 
            costPrice: '',
            amount: '',
            vat: '',
            subtotal: ''
          }])
        } else {
          setRows(emptyRows)
        }
        
        // Reset header data
        setHeaderData({
          discountAmount: '0',
          discountType: 'fixed',
          applyTax: true,
          vatPercentage: '15',
        })
      } else {
        // Auto-save: just clear the completed rows, keep current row
        const remainingRows = rows.filter(row => 
          !row.productName || !row.quantity || !row.sellingPrice
        )
        if (remainingRows.length === 0) {
          const today = new Date().toISOString().split('T')[0]
          const nextInvoiceNumber = getNextInvoiceNumber()
          const nextCustomerCode = getNextCustomerCode()
          setRows([{ 
            id: 1, 
            date: today,
            customerCode: nextCustomerCode,
            customerName: '',
            invoiceNumber: nextInvoiceNumber,
            taxRate: (taxSetting.defaultRate || 15).toString(),
            productName: '', 
            quantity: '', 
            sellingPrice: '', 
            costPrice: '',
            amount: '',
            vat: '',
            subtotal: ''
          }])
        } else {
          // Ensure dates are incremented properly for remaining rows
          const firstRow = remainingRows[0]
          if (firstRow.date) {
            const baseDate = new Date(firstRow.date)
            setRows(remainingRows.map((row, index) => {
              if (index === 0) return row
              const rowDate = new Date(baseDate)
              rowDate.setDate(rowDate.getDate() + index)
              return { ...row, date: rowDate.toISOString().split('T')[0] }
            }))
          } else {
            setRows(remainingRows)
          }
        }
      }
      
      fetchSales(searchFilters)
    } catch (error) {
      setAlert({ type: 'error', message: error.response?.data?.error || 'Failed to save sale' })
    }
  }

  const columns = [
    { key: 'date', label: 'Date', width: '110px', mobileWidth: '90px', type: 'date' },
    { key: 'customerCode', label: 'Customer Code', width: '120px', mobileWidth: '100px', type: 'text' },
    { key: 'customerName', label: 'Customer Name', width: '180px', mobileWidth: '120px', type: 'text' },
    { key: 'invoiceNumber', label: 'Invoice Number', width: '130px', mobileWidth: '110px', type: 'text' },
    { key: 'productName', label: 'Product Name', width: '220px', mobileWidth: '150px', type: 'text' },
    { key: 'sellingPrice', label: 'Price', width: '100px', mobileWidth: '80px', type: 'number' },
    { key: 'quantity', label: 'Qty', width: '80px', mobileWidth: '60px', type: 'number' },
    { key: 'amount', label: 'Amount', width: '100px', mobileWidth: '80px', type: 'readonly' },
    { key: 'vat', label: 'VAT 15%', width: '100px', mobileWidth: '80px', type: 'readonly' },
    { key: 'subtotal', label: 'Subtotal', width: '100px', mobileWidth: '80px', type: 'readonly' },
  ]

  return (
    <div className="min-h-screen bg-gray-100">
      {alert && (
        <div className="fixed top-2 sm:top-4 right-2 sm:right-4 z-50 max-w-[calc(100%-1rem)] sm:max-w-md">
          <Alert
            type={alert.type}
            message={alert.message}
            onClose={() => setAlert(null)}
          />
        </div>
      )}

      <div className="bg-white border-b shadow-sm sticky top-0 z-40">
        <div className="max-w-full mx-auto px-2 sm:px-4 py-2 sm:py-3">
          <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 sm:gap-0">
            <div className="flex items-center space-x-2 sm:space-x-4">
              <FileSpreadsheet className="w-5 h-5 sm:w-6 sm:h-6 text-green-600" />
              <h1 className="text-lg sm:text-xl lg:text-2xl font-semibold text-gray-800">
                <span className="hidden sm:inline">Sales Entry - Excel Mode</span>
                <span className="sm:hidden">Sales</span>
              </h1>
            </div>
            <div className="flex items-center space-x-2 w-full sm:w-auto">
              {autoSaving && (
                <span className="text-xs text-blue-600 flex items-center">
                  <span className="animate-spin mr-1">⏳</span>
                  Auto-saving...
                </span>
              )}
              {lastSaved && !autoSaving && (
                <span className="text-xs text-green-600 flex items-center">
                  ✓ Saved {lastSaved.toLocaleTimeString()}
                </span>
              )}
              <Button variant="primary" onClick={() => handleSave(true)} className="flex-1 sm:flex-none text-xs sm:text-sm px-2 sm:px-4">
                <Save className="w-4 h-4 inline mr-1 sm:mr-2" />
                <span className="hidden sm:inline">Save Now</span>
                <span className="sm:hidden">Save</span>
              </Button>
            </div>
          </div>
        </div>
      </div>

      {/* Totals Summary at Top (Excel-like) - Responsive */}
      <div className="bg-white mx-2 sm:mx-4 mt-2 sm:mt-4 border border-gray-300 rounded shadow-sm">
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-4 p-2 sm:p-4 border-b border-gray-300 bg-gray-50">
          <div className="text-center">
            <div className="text-xs text-gray-600 mb-1 font-medium">Subtotal (After Discount)</div>
            <div className="text-base sm:text-lg lg:text-xl font-bold text-gray-900">${calculations.subtotal.toFixed(2)}</div>
          </div>
          <div className="text-center">
            <div className="text-xs text-gray-600 mb-1 font-medium">Discount</div>
            <div className="text-base sm:text-lg lg:text-xl font-bold text-red-600">
              -${calculations.discount.toFixed(2)}
            </div>
          </div>
          <div className="text-center">
            <div className="text-xs text-gray-600 mb-1 font-medium">VAT ({headerData.applyTax ? `${headerData.vatPercentage}%` : '0%'})</div>
            <div className="text-base sm:text-lg lg:text-xl font-bold text-gray-900">${calculations.tax.toFixed(2)}</div>
          </div>
          <div className="text-center border-l-0 sm:border-l-2 border-gray-400 sm:pl-4 col-span-2 sm:col-span-1">
            <div className="text-xs text-gray-600 mb-1 font-medium">Total</div>
            <div className="text-xl sm:text-2xl font-bold text-green-600">
              ${calculations.total.toFixed(2)}
            </div>
          </div>
          <div className="text-center col-span-2 sm:col-span-1 lg:col-span-1">
            <div className="text-xs text-gray-600 mb-1 font-medium">Profit</div>
            <div className="text-base sm:text-lg lg:text-xl font-bold text-blue-600">
              ${calculations.profit.toFixed(2)}
            </div>
          </div>
        </div>
        {/* Discount Settings - Responsive */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 p-2 sm:p-4">
          <div className="flex items-center">
            <label className="flex items-center text-sm">
              <input
                type="checkbox"
                checked={headerData.applyTax}
                onChange={(e) => setHeaderData({ ...headerData, applyTax: e.target.checked })}
                className="mr-2 w-4 h-4"
              />
              Apply Tax
            </label>
          </div>
          <div>
            <label className="text-xs text-gray-600 mb-1 block">VAT Adjustment (%)</label>
            <input
              type="number"
              step="0.01"
              min="0"
              max="100"
              value={headerData.vatPercentage}
              onChange={(e) => setHeaderData({ ...headerData, vatPercentage: e.target.value })}
              className="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
              disabled={!headerData.applyTax}
            />
          </div>
          <div>
            <label className="text-xs text-gray-600 mb-1 block">Discount Type</label>
            <select
              value={headerData.discountType}
              onChange={(e) => setHeaderData({ ...headerData, discountType: e.target.value })}
              className="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
            >
              <option value="fixed">Fixed Amount</option>
              <option value="percentage">Percentage</option>
            </select>
          </div>
          <div>
            <label className="text-xs text-gray-600 mb-1 block">
              Discount {headerData.discountType === 'percentage' ? '(%)' : '($)'}
            </label>
            <input
              type="number"
              step="0.01"
              value={headerData.discountAmount}
              onChange={(e) => setHeaderData({ ...headerData, discountAmount: e.target.value })}
              className="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
            />
          </div>
        </div>
      </div>

      {/* Excel-like Grid - Mobile Optimized */}
      <div className="bg-white mx-2 sm:mx-4 my-2 sm:my-4 border border-gray-300 rounded shadow-sm overflow-hidden">
        <div className="overflow-x-auto overflow-y-auto max-h-[70vh] sm:max-h-none" ref={gridRef} style={{ WebkitOverflowScrolling: 'touch' }}>
          <table className="min-w-full border-collapse text-xs sm:text-sm" style={{ fontFamily: 'Calibri, Arial, sans-serif' }}>
            {/* Column Headers */}
            <thead className="sticky top-0 z-10">
              <tr className="bg-gray-50 border-b-2 border-gray-400">
                <th className="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2 sticky left-0 z-20 bg-gray-100" style={{ width: '40px', minWidth: '40px' }}>
                  #
                </th>
                {columns.map((col, idx) => (
                  <th
                    key={col.key}
                    className="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2"
                    style={{ width: col.width, minWidth: col.width }}
                  >
                    <span className="hidden sm:inline">{col.label}</span>
                    <span className="sm:hidden text-[10px]">{col.label.split(' ')[0]}</span>
                  </th>
                ))}
                <th className="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2" style={{ width: '40px', minWidth: '40px' }}>
                  <span className="hidden sm:inline">Action</span>
                  <span className="sm:hidden">✕</span>
                </th>
              </tr>
            </thead>
            <tbody>
              {rows.map((row, rowIndex) => (
                <tr
                  key={row.id}
                  className={`hover:bg-blue-50 ${
                    selectedCell?.rowId === row.id ? 'bg-blue-100' : ''
                  }`}
                >
                  {/* Row Number - Sticky */}
                  <td className="border border-gray-300 bg-gray-50 text-center text-gray-600 px-1 sm:px-2 py-1 sticky left-0 z-10 bg-gray-50">
                    {rowIndex + 1}
                  </td>
                  
                  {/* Date Column */}
                  <td className="border border-gray-300 px-0.5 sm:px-1 py-0">
                    {editingCell?.rowId === row.id && editingCell?.field === 'date' ? (
                      <input
                        autoFocus
                        type="date"
                        value={row.date || ''}
                        onChange={(e) => handleCellChange(row.id, 'date', e.target.value)}
                        onBlur={handleCellBlur}
                        onKeyDown={(e) => handleKeyDown(e, row.id, 'date')}
                        className="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                    ) : (
                      <div
                        onClick={() => handleCellClick(row.id, 'date')}
                        className="px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm cursor-pointer min-h-[36px] sm:min-h-[28px] flex items-center touch-manipulation"
                      >
                        {row.date ? <span className="text-[10px] sm:text-xs">{new Date(row.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}</span> : <span className="text-gray-400 text-[10px]">Date</span>}
                      </div>
                    )}
                  </td>
                  
                  {/* Customer Code Column */}
                  <td className="border border-gray-300 px-0.5 sm:px-1 py-0">
                    {editingCell?.rowId === row.id && editingCell?.field === 'customerCode' ? (
                      <input
                        autoFocus
                        type="text"
                        value={row.customerCode || ''}
                        onChange={(e) => handleCellChange(row.id, 'customerCode', e.target.value)}
                        onBlur={handleCellBlur}
                        onKeyDown={(e) => handleKeyDown(e, row.id, 'customerCode')}
                        placeholder="Code..."
                        className="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                    ) : (
                      <div
                        onClick={() => handleCellClick(row.id, 'customerCode')}
                        className="px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm cursor-pointer min-h-[36px] sm:min-h-[28px] flex items-center touch-manipulation truncate"
                      >
                        {row.customerCode || <span className="text-gray-400 text-[10px]">Code</span>}
                      </div>
                    )}
                  </td>
                  
                  {/* Customer Name Column */}
                  <td className="border border-gray-300 px-0.5 sm:px-1 py-0">
                    {editingCell?.rowId === row.id && editingCell?.field === 'customerName' ? (
                      <input
                        autoFocus
                        type="text"
                        value={row.customerName || ''}
                        onChange={(e) => handleCellChange(row.id, 'customerName', e.target.value)}
                        onBlur={handleCellBlur}
                        onKeyDown={(e) => handleKeyDown(e, row.id, 'customerName')}
                        placeholder="Customer..."
                        className="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                    ) : (
                      <div
                        onClick={() => handleCellClick(row.id, 'customerName')}
                        className="px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm cursor-pointer min-h-[36px] sm:min-h-[28px] flex items-center touch-manipulation truncate"
                      >
                        {row.customerName || <span className="text-gray-400 text-[10px]">Customer</span>}
                      </div>
                    )}
                  </td>
                  
                  {/* Invoice Number Column */}
                  <td className="border border-gray-300 px-0.5 sm:px-1 py-0">
                    {editingCell?.rowId === row.id && editingCell?.field === 'invoiceNumber' ? (
                      <input
                        autoFocus
                        type="text"
                        value={row.invoiceNumber || ''}
                        onChange={(e) => handleCellChange(row.id, 'invoiceNumber', e.target.value)}
                        onBlur={handleCellBlur}
                        onKeyDown={(e) => handleKeyDown(e, row.id, 'invoiceNumber')}
                        placeholder="Invoice..."
                        className="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                    ) : (
                      <div
                        onClick={() => handleCellClick(row.id, 'invoiceNumber')}
                        className="px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm cursor-pointer min-h-[36px] sm:min-h-[28px] flex items-center touch-manipulation truncate"
                      >
                        {row.invoiceNumber || <span className="text-gray-400 text-[10px]">Invoice</span>}
                      </div>
                    )}
                  </td>
                  
                  {/* Product Name Column */}
                  <td className="border border-gray-300 px-0.5 sm:px-1 py-0">
                    {editingCell?.rowId === row.id && editingCell?.field === 'productName' ? (
                      <input
                        autoFocus
                        type="text"
                        value={row.productName}
                        onChange={(e) => handleCellChange(row.id, 'productName', e.target.value)}
                        onBlur={handleCellBlur}
                        onKeyDown={(e) => handleKeyDown(e, row.id, 'productName')}
                        placeholder="Product..."
                        className="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        list={`product-suggestions-${row.id}`}
                      />
                    ) : (
                      <div
                        onClick={() => handleCellClick(row.id, 'productName')}
                        className="px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm cursor-pointer min-h-[36px] sm:min-h-[28px] flex items-center touch-manipulation truncate"
                      >
                        {row.productName || <span className="text-gray-400 text-[10px]">Product</span>}
                      </div>
                    )}
                  </td>
                  
                  {/* Price Column */}
                  <td className="border border-gray-300 px-0.5 sm:px-1 py-0">
                    {editingCell?.rowId === row.id && editingCell?.field === 'sellingPrice' ? (
                      <input
                        autoFocus
                        type="number"
                        step="0.01"
                        min="0"
                        value={row.sellingPrice}
                        onChange={(e) => handleCellChange(row.id, 'sellingPrice', e.target.value)}
                        onBlur={() => {
                          handleCellBlur()
                          handlePriceBlur(row.id)
                        }}
                        onKeyDown={(e) => handleKeyDown(e, row.id, 'sellingPrice')}
                        className="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 text-right"
                      />
                    ) : (
                      <div
                        onClick={() => handleCellClick(row.id, 'sellingPrice')}
                        className="px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm cursor-pointer min-h-[36px] sm:min-h-[28px] flex items-center justify-end touch-manipulation"
                      >
                        {row.sellingPrice ? <span className="text-[10px] sm:text-xs">${parseFloat(row.sellingPrice).toFixed(2)}</span> : <span className="text-gray-400 text-[10px]">0.00</span>}
                      </div>
                    )}
                  </td>
                  
                  {/* Quantity Column */}
                  <td className="border border-gray-300 px-0.5 sm:px-1 py-0">
                    {editingCell?.rowId === row.id && editingCell?.field === 'quantity' ? (
                      <input
                        autoFocus
                        type="number"
                        min="1"
                        value={row.quantity}
                        onChange={(e) => handleCellChange(row.id, 'quantity', e.target.value)}
                        onBlur={handleCellBlur}
                        onKeyDown={(e) => handleKeyDown(e, row.id, 'quantity')}
                        className="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 text-right"
                      />
                    ) : (
                      <div
                        onClick={() => handleCellClick(row.id, 'quantity')}
                        className="px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm cursor-pointer min-h-[36px] sm:min-h-[28px] flex items-center justify-end touch-manipulation"
                      >
                        {row.quantity || <span className="text-gray-400 text-[10px]">0</span>}
                      </div>
                    )}
                  </td>
                  
                  {/* Amount Column (Read-only) */}
                  <td className="border border-gray-300 px-1 sm:px-2 py-1 text-xs sm:text-sm text-right font-medium bg-gray-50">
                    {row.amount ? <span className="text-[10px] sm:text-xs">${row.amount}</span> : <span className="text-[10px] text-gray-400">$0.00</span>}
                  </td>
                  
                  {/* VAT Column (Read-only) */}
                  <td className="border border-gray-300 px-1 sm:px-2 py-1 text-xs sm:text-sm text-right font-medium bg-gray-50">
                    {row.vat ? <span className="text-[10px] sm:text-xs">${row.vat}</span> : <span className="text-[10px] text-gray-400">$0.00</span>}
                  </td>
                  
                  {/* Subtotal Column (Read-only) */}
                  <td className="border border-gray-300 px-1 sm:px-2 py-1 text-xs sm:text-sm text-right font-medium bg-gray-50">
                    {row.subtotal ? <span className="text-[10px] sm:text-xs">${row.subtotal}</span> : <span className="text-[10px] text-gray-400">$0.00</span>}
                  </td>
                  
                  {/* Delete Button */}
                  <td className="border border-gray-300 px-1 sm:px-2 py-1 text-center">
                    {rows.length > 1 && (
                      <button
                        onClick={() => deleteRow(row.id)}
                        className="text-red-600 hover:text-red-800 touch-manipulation p-1"
                        aria-label="Delete row"
                      >
                        <Trash2 className="w-4 h-4 sm:w-5 sm:h-5" />
                      </button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        
        {/* Add Row Button */}
        <div className="border-t border-gray-300 bg-gray-50 px-4 py-2">
          <button
            onClick={addRow}
            className="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center"
          >
            <Plus className="w-4 h-4 mr-1" />
            Add Row
          </button>
        </div>
      </div>


      {/* Search Bar - Always Visible */}
      <div className="bg-white mx-2 sm:mx-4 mt-2 sm:mt-4 mb-2 sm:mb-4 border border-gray-300 rounded shadow-sm">
        {/* Search and Filter Section */}
          <div className="p-3 sm:p-4 border-b bg-gray-50">
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-3">
              <h2 className="text-base sm:text-lg font-semibold">Sales Search & Filter</h2>
              <div className="flex items-center gap-2 w-full sm:w-auto">
                <Button
                  variant="secondary"
                  onClick={() => setShowFilters(!showFilters)}
                  className="flex items-center gap-1 text-xs sm:text-sm"
                >
                  <Filter className="w-4 h-4" />
                  <span className="hidden sm:inline">Filters</span>
                </Button>
                {hasActiveFilters && (
                  <Button
                    variant="secondary"
                    onClick={clearFilters}
                    className="flex items-center gap-1 text-xs sm:text-sm text-red-600 hover:text-red-700"
                  >
                    <X className="w-4 h-4" />
                    <span className="hidden sm:inline">Clear</span>
                  </Button>
                )}
              </div>
            </div>
            
            {/* General Search */}
            <div className="mb-3">
              <div className="relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                <input
                  type="text"
                  placeholder="Search by invoice number, customer name, or customer code..."
                  value={searchFilters.search}
                  onChange={(e) => handleSearchChange('search', e.target.value)}
                  className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                />
              </div>
            </div>
            
            {/* Advanced Filters (Collapsible) */}
            {showFilters && (
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pt-3 border-t border-gray-200">
                {/* Invoice Number */}
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">
                    Invoice Number
                  </label>
                  <input
                    type="text"
                    placeholder="Enter invoice number"
                    value={searchFilters.invoiceNumber}
                    onChange={(e) => handleSearchChange('invoiceNumber', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                  />
                </div>
                
                {/* Customer Name */}
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">
                    Customer Name
                  </label>
                  <input
                    type="text"
                    placeholder="Enter customer name"
                    value={searchFilters.customerName}
                    onChange={(e) => handleSearchChange('customerName', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                  />
                </div>
                
                {/* Customer Code */}
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">
                    Customer Code
                  </label>
                  <input
                    type="text"
                    placeholder="Enter customer code"
                    value={searchFilters.customerCode}
                    onChange={(e) => handleSearchChange('customerCode', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                  />
                </div>
                
                {/* Start Date */}
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">
                    Start Date
                  </label>
                  <input
                    type="date"
                    value={searchFilters.startDate}
                    onChange={(e) => handleSearchChange('startDate', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                  />
                </div>
                
                {/* End Date */}
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">
                    End Date
                  </label>
                  <input
                    type="date"
                    value={searchFilters.endDate}
                    onChange={(e) => handleSearchChange('endDate', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                  />
                </div>
              </div>
            )}
            
            {/* Results Count */}
            <div className="mt-3 pt-3 border-t border-gray-200">
              <p className="text-xs sm:text-sm text-gray-600">
                {loading ? (
                  <span>Searching...</span>
                ) : (
                  <span>
                    Found <strong>{sales.length}</strong> sale{sales.length !== 1 ? 's' : ''}
                    {hasActiveFilters && ' matching your filters'}
                  </span>
                )}
              </p>
            </div>
          </div>
          
          {/* Sales Table */}
          <div className="p-3 sm:p-4">
            {loading ? (
              <div className="text-center py-8 text-sm">Loading...</div>
            ) : (
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        Date
                      </th>
                      <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        Invoice #
                      </th>
                      <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">
                        Customer Code
                      </th>
                      <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">
                        Customer Name
                      </th>
                      <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        Items
                      </th>
                      <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        Total
                      </th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {sales.length === 0 ? (
                      <tr>
                        <td colSpan="6" className="px-2 sm:px-4 lg:px-6 py-8 text-center text-gray-500">
                          <ShoppingCart className="w-12 h-12 mx-auto mb-4 text-gray-400" />
                          <p className="text-sm sm:text-base">
                            {hasActiveFilters ? 'No sales found matching your filters' : 'No sales recorded yet'}
                          </p>
                        </td>
                      </tr>
                    ) : (
                      sales.map((sale) => (
                        <tr key={sale.id} className="hover:bg-gray-50">
                          <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-gray-900">
                            {new Date(sale.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                          </td>
                          <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-gray-900 font-medium">
                            {sale.invoiceNumber || '-'}
                          </td>
                          <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-gray-900 hidden sm:table-cell">
                            {sale.customerCode || '-'}
                          </td>
                          <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-gray-900 hidden md:table-cell">
                            {sale.customerName || '-'}
                          </td>
                          <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 text-gray-900">
                            {sale.salesItems.length} item{sale.salesItems.length !== 1 ? 's' : ''}
                          </td>
                          <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            ${parseFloat(sale.totalAmount).toFixed(2)}
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
    </div>
  )
}
