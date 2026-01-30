import { useState, useEffect, useRef } from 'react'
import { Plus, Trash2, Save, FileSpreadsheet, Search, X, Filter, Receipt } from 'lucide-react'
import api from '../utils/api'
import Button from '../components/Button'
import Alert from '../components/Alert'

export default function Receivables() {
  const [receivables, setReceivables] = useState([])
  const [loading, setLoading] = useState(true)
  const [alert, setAlert] = useState(null)
  const [autoSaving, setAutoSaving] = useState(false)
  const [lastSaved, setLastSaved] = useState(null)
  const autoSaveTimeoutRef = useRef(null)
  
  // Search and filter state
  const [searchFilters, setSearchFilters] = useState({
    search: '',
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
    { id: 1, date: '', invoiceNumber: '', customerName: '', customerCode: '', amount: '', received: '', subtotal: '0.00' }
  ])
  const [selectedCell, setSelectedCell] = useState(null)
  const [editingCell, setEditingCell] = useState(null)
  const gridRef = useRef(null)

  useEffect(() => {
    fetchReceivables({})
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
  
  // Track which rows have been saved
  const savedRowIdsRef = useRef(new Set())

  const fetchReceivables = async (filters = {}) => {
    try {
      setLoading(true)
      const params = new URLSearchParams()
      
      if (filters.search) params.append('search', filters.search)
      if (filters.invoiceNumber) params.append('invoiceNumber', filters.invoiceNumber)
      if (filters.customerName) params.append('customerName', filters.customerName)
      if (filters.customerCode) params.append('customerCode', filters.customerCode)
      if (filters.startDate) params.append('startDate', filters.startDate)
      if (filters.endDate) params.append('endDate', filters.endDate)
      
      const queryString = params.toString()
      const url = queryString ? `/receivables?${queryString}` : '/receivables'
      const response = await api.get(url)
      setReceivables(response.data)
    } catch (error) {
      setAlert({ type: 'error', message: 'Failed to fetch receivables' })
    } finally {
      setLoading(false)
    }
  }
  
  // Debounced search function
  const handleSearchChange = (field, value) => {
    const newFilters = { ...searchFilters, [field]: value }
    setSearchFilters(newFilters)
    
    if (searchTimeoutRef.current) {
      clearTimeout(searchTimeoutRef.current)
    }
    
    searchTimeoutRef.current = setTimeout(() => {
      fetchReceivables(newFilters)
    }, 500)
  }
  
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
    fetchReceivables(emptyFilters)
  }
  
  const hasActiveFilters = Object.values(searchFilters).some(value => value !== '')

  // Helper function to get next invoice number
  const getNextInvoiceNumber = () => {
    const invoiceNumbers = rows
      .map(row => row.invoiceNumber)
      .filter(inv => inv && inv.trim() !== '')
      .map(inv => {
        const match = inv.toString().match(/(\d+)$/)
        return match ? parseInt(match[1]) : 0
      })
    
    const savedInvoiceNumbers = receivables
      .map(rec => rec.invoiceNumber)
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
    const customerCodes = rows
      .map(row => row.customerCode)
      .filter(code => code && code.trim() !== '')
      .map(code => {
        const match = code.toString().match(/(\d+)$/)
        return match ? parseInt(match[1]) : 0
      })
    
    const savedCustomerCodes = receivables
      .map(rec => rec.customerCode)
      .filter(code => code && code.trim() !== '')
      .map(code => {
        const match = code.toString().match(/(\d+)$/)
        return match ? parseInt(match[1]) : 0
      })
    
    const allNumbers = [...customerCodes, ...savedCustomerCodes]
    const maxNumber = allNumbers.length > 0 ? Math.max(...allNumbers) : 0
    return (maxNumber + 1).toString()
  }

  const handleCellChange = (rowId, field, value) => {
    if (field === 'amount' || field === 'received') {
      // Update the field and recalculate subtotal
      setRows(rows.map(row => {
        if (row.id === rowId) {
          const updatedRow = { ...row, [field]: value }
          const amount = parseFloat(updatedRow.amount) || 0
          const received = parseFloat(updatedRow.received) || 0
          const subtotal = amount - received
          return { ...updatedRow, subtotal: subtotal.toFixed(2) }
        }
        return row
      }))
    } else if (field === 'date') {
      const currentIndex = rows.findIndex(r => r.id === rowId)
      setRows(rows.map((row, index) => {
        if (row.id === rowId) {
          return { ...row, date: value }
        } else if (index > currentIndex && value) {
          const baseDate = new Date(value)
          baseDate.setDate(baseDate.getDate() + (index - currentIndex))
          return { ...row, date: baseDate.toISOString().split('T')[0] }
        }
        return row
      }))
    } else if (field === 'invoiceNumber') {
      const currentIndex = rows.findIndex(r => r.id === rowId)
      const baseNumber = value ? parseInt(value.toString().match(/(\d+)$/)?.[1] || '0') : 0
      setRows(rows.map((row, index) => {
        if (row.id === rowId) {
          return { ...row, invoiceNumber: value }
        } else if (index > currentIndex && value && baseNumber > 0) {
          const prefix = value.toString().replace(/\d+$/, '')
          const nextNumber = (baseNumber + (index - currentIndex)).toString()
          return { ...row, invoiceNumber: prefix + nextNumber }
        }
        return row
      }))
    } else if (field === 'customerCode') {
      const currentIndex = rows.findIndex(r => r.id === rowId)
      const baseNumber = value ? parseInt(value.toString().match(/(\d+)$/)?.[1] || '0') : 0
      setRows(rows.map((row, index) => {
        if (row.id === rowId) {
          return { ...row, customerCode: value }
        } else if (index > currentIndex && value && baseNumber > 0) {
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

  const addRow = () => {
    const newId = Math.max(...rows.map(r => r.id), 0) + 1
    const lastRow = rows[rows.length - 1] || {}
    const firstRow = rows[0] || {}
    
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
    
    const nextInvoiceNumber = getNextInvoiceNumber()
    const nextCustomerCode = getNextCustomerCode()
    
    setRows([...rows, { 
      id: newId, 
      date: nextDate,
      invoiceNumber: nextInvoiceNumber,
      customerName: '',
      customerCode: nextCustomerCode,
      amount: '',
      received: '',
      subtotal: ''
    }])
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
      
      if (currentRow.date && currentRow.invoiceNumber && currentRow.customerName && currentRow.customerCode && currentRow.amount) {
        await handleSave()
      }
      
      if (currentIndex < rows.length - 1) {
        const nextRowId = rows[currentIndex + 1].id
        setSelectedCell({ rowId: nextRowId, field })
        setEditingCell({ rowId: nextRowId, field })
      } else {
        addRow()
        setTimeout(() => {
          const newRowId = Math.max(...rows.map(r => r.id), 0) + 1
          setSelectedCell({ rowId: newRowId, field })
          setEditingCell({ rowId: newRowId, field })
        }, 100)
      }
    } else if (e.key === 'Tab') {
      e.preventDefault()
      const fields = ['date', 'invoiceNumber', 'customerName', 'customerCode', 'amount', 'received']
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
      if (['date', 'invoiceNumber', 'customerName', 'customerCode', 'amount', 'received'].includes(field)) {
        handleCellChange(rowId, field, '')
      }
    }
  }

  // Auto-save function
  const autoSaveReceivables = async (validRows) => {
    if (validRows.length === 0) return
    
    try {
      setAutoSaving(true)
      const receivableData = {
        receivables: validRows.map(row => ({
          date: row.date || new Date().toISOString().split('T')[0],
          invoiceNumber: row.invoiceNumber || null,
          customerName: row.customerName || null,
          customerCode: row.customerCode || null,
          amount: parseFloat(row.amount) || 0,
          received: parseFloat(row.received) || 0,
        }))
      }

      await api.post('/receivables', receivableData)
      await fetchReceivables(searchFilters)
      setLastSaved(new Date())
      
      const incompleteRows = rows.filter(row => 
        !row.date || !row.invoiceNumber || !row.customerName || !row.customerCode || !row.amount
      )
      
      if (incompleteRows.length === 0) {
        const today = new Date().toISOString().split('T')[0]
        const nextInvoiceNumber = getNextInvoiceNumber()
        const nextCustomerCode = getNextCustomerCode()
        setRows([{ 
          id: 1, 
          date: today,
          invoiceNumber: nextInvoiceNumber,
          customerName: '',
          customerCode: nextCustomerCode,
          amount: '',
          received: '',
          subtotal: '0.00'
        }])
      } else {
        setRows(incompleteRows)
      }
    } catch (error) {
      console.error('Auto-save failed:', error)
    } finally {
      setAutoSaving(false)
    }
  }
  
  // Auto-save when row is completed
  useEffect(() => {
    const validRows = rows.filter(row => {
      const isComplete = row.date && row.invoiceNumber && row.customerName && row.customerCode && row.amount
      const notSaved = !savedRowIdsRef.current.has(row.id)
      return isComplete && notSaved
    })
    
    if (validRows.length > 0) {
      if (autoSaveTimeoutRef.current) {
        clearTimeout(autoSaveTimeoutRef.current)
      }
      
      autoSaveTimeoutRef.current = setTimeout(async () => {
        const rowsToSave = rows.filter(row => {
          const isComplete = row.date && row.invoiceNumber && row.customerName && row.customerCode && row.amount
          const notSaved = !savedRowIdsRef.current.has(row.id)
          return isComplete && notSaved
        })
        
        if (rowsToSave.length > 0) {
          await autoSaveReceivables(rowsToSave)
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

  const handleSave = async () => {
    const validRows = rows.filter(row => 
      row.date && row.invoiceNumber && row.customerName && row.customerCode && row.amount
    )

    if (validRows.length === 0) {
      setAlert({ type: 'error', message: 'Please add at least one receivable entry' })
      return
    }

    try {
      const receivableData = {
        receivables: validRows.map(row => ({
          date: row.date,
          invoiceNumber: row.invoiceNumber,
          customerName: row.customerName,
          customerCode: row.customerCode,
          amount: parseFloat(row.amount),
          received: parseFloat(row.received) || 0,
        }))
      }

      await api.post('/receivables', receivableData)
      setAlert({ type: 'success', message: 'Receivables saved successfully!' })
      
      const emptyRows = rows.filter(row => 
        !row.date || !row.invoiceNumber || !row.customerName || !row.customerCode || !row.amount
      )
      
      if (emptyRows.length === 0) {
        const today = new Date().toISOString().split('T')[0]
        const nextInvoiceNumber = getNextInvoiceNumber()
        const nextCustomerCode = getNextCustomerCode()
        setRows([{ 
          id: 1, 
          date: today,
          invoiceNumber: nextInvoiceNumber,
          customerName: '',
          customerCode: nextCustomerCode,
          amount: '',
          received: '',
          subtotal: '0.00'
        }])
      } else {
        setRows(emptyRows)
      }
      
      fetchReceivables(searchFilters)
    } catch (error) {
      setAlert({ type: 'error', message: error.response?.data?.error || 'Failed to save receivables' })
    }
  }

  const columns = [
    { key: 'date', label: 'Date', width: '120px', mobileWidth: '100px', type: 'date' },
    { key: 'invoiceNumber', label: 'Invoice Number', width: '150px', mobileWidth: '120px', type: 'text' },
    { key: 'customerName', label: 'Customer Name', width: '200px', mobileWidth: '150px', type: 'text' },
    { key: 'customerCode', label: 'Customer Code', width: '150px', mobileWidth: '120px', type: 'text' },
    { key: 'amount', label: 'Amount', width: '120px', mobileWidth: '100px', type: 'number' },
    { key: 'received', label: 'Received', width: '120px', mobileWidth: '100px', type: 'number' },
    { key: 'subtotal', label: 'Subtotal', width: '120px', mobileWidth: '100px', type: 'readonly' },
  ]

  const totalAmount = rows.reduce((sum, row) => {
    return sum + (parseFloat(row.amount) || 0)
  }, 0)

  const totalReceived = rows.reduce((sum, row) => {
    return sum + (parseFloat(row.received) || 0)
  }, 0)

  const totalBalance = totalAmount - totalReceived

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
              <Receipt className="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" />
              <h1 className="text-lg sm:text-xl lg:text-2xl font-semibold text-gray-800">
                <span className="hidden sm:inline">Receivables Entry</span>
                <span className="sm:hidden">Receivables</span>
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
              <Button variant="primary" onClick={handleSave} className="flex-1 sm:flex-none text-xs sm:text-sm px-2 sm:px-4">
                <Save className="w-4 h-4 inline mr-1 sm:mr-2" />
                <span className="hidden sm:inline">Save Now</span>
                <span className="sm:hidden">Save</span>
              </Button>
            </div>
          </div>
        </div>
      </div>

      {/* Total Summary */}
      <div className="bg-white mx-2 sm:mx-4 mt-2 sm:mt-4 border border-gray-300 rounded shadow-sm">
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 p-3 sm:p-4 border-b border-gray-300 bg-gray-50">
          <div className="text-center">
            <div className="text-xs text-gray-600 mb-1 font-medium">Total Amount</div>
            <div className="text-base sm:text-lg lg:text-xl font-bold text-gray-900">
              ${totalAmount.toFixed(2)}
            </div>
          </div>
          <div className="text-center">
            <div className="text-xs text-gray-600 mb-1 font-medium">Total Received</div>
            <div className="text-base sm:text-lg lg:text-xl font-bold text-blue-600">
              ${totalReceived.toFixed(2)}
            </div>
          </div>
          <div className="text-center border-l-0 sm:border-l-2 border-gray-400 sm:pl-4">
            <div className="text-xs text-gray-600 mb-1 font-medium">Balance</div>
            <div className="text-xl sm:text-2xl font-bold text-green-600">
              ${totalBalance.toFixed(2)}
            </div>
          </div>
        </div>
      </div>

      {/* Excel-like Grid */}
      <div className="bg-white mx-2 sm:mx-4 my-2 sm:my-4 border border-gray-300 rounded shadow-sm overflow-hidden">
        <div className="overflow-x-auto overflow-y-auto max-h-[70vh] sm:max-h-none" ref={gridRef} style={{ WebkitOverflowScrolling: 'touch' }}>
          <table className="min-w-full border-collapse text-xs sm:text-sm" style={{ fontFamily: 'Calibri, Arial, sans-serif' }}>
            <thead className="sticky top-0 z-10">
              <tr className="bg-gray-50 border-b-2 border-gray-400">
                <th className="border border-gray-300 bg-gray-100 text-center font-semibold text-gray-700 px-1 sm:px-2 py-1.5 sm:py-2 sticky left-0 z-20 bg-gray-100" style={{ width: '40px', minWidth: '40px' }}>
                  #
                </th>
                {columns.map((col) => (
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
                  
                  {/* Amount Column */}
                  <td className="border border-gray-300 px-0.5 sm:px-1 py-0">
                    {editingCell?.rowId === row.id && editingCell?.field === 'amount' ? (
                      <input
                        autoFocus
                        type="number"
                        step="0.01"
                        min="0"
                        value={row.amount || ''}
                        onChange={(e) => handleCellChange(row.id, 'amount', e.target.value)}
                        onBlur={handleCellBlur}
                        onKeyDown={(e) => handleKeyDown(e, row.id, 'amount')}
                        className="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 text-right"
                      />
                    ) : (
                      <div
                        onClick={() => handleCellClick(row.id, 'amount')}
                        className="px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm cursor-pointer min-h-[36px] sm:min-h-[28px] flex items-center justify-end touch-manipulation"
                      >
                        {row.amount ? <span className="text-[10px] sm:text-xs">${parseFloat(row.amount).toFixed(2)}</span> : <span className="text-gray-400 text-[10px]">0.00</span>}
                      </div>
                    )}
                  </td>
                  
                  {/* Received Column */}
                  <td className="border border-gray-300 px-0.5 sm:px-1 py-0">
                    {editingCell?.rowId === row.id && editingCell?.field === 'received' ? (
                      <input
                        autoFocus
                        type="number"
                        step="0.01"
                        min="0"
                        value={row.received || ''}
                        onChange={(e) => handleCellChange(row.id, 'received', e.target.value)}
                        onBlur={handleCellBlur}
                        onKeyDown={(e) => handleKeyDown(e, row.id, 'received')}
                        className="w-full px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 text-right"
                      />
                    ) : (
                      <div
                        onClick={() => handleCellClick(row.id, 'received')}
                        className="px-1 sm:px-2 py-1.5 sm:py-1 text-xs sm:text-sm cursor-pointer min-h-[36px] sm:min-h-[28px] flex items-center justify-end touch-manipulation"
                      >
                        {row.received ? <span className="text-[10px] sm:text-xs">${parseFloat(row.received).toFixed(2)}</span> : <span className="text-gray-400 text-[10px]">0.00</span>}
                      </div>
                    )}
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
        <div className="p-3 sm:p-4 border-b bg-gray-50">
          <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-3">
            <h2 className="text-base sm:text-lg font-semibold">Receivables Search & Filter</h2>
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
          
          {showFilters && (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pt-3 border-t border-gray-200">
              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">Invoice Number</label>
                <input
                  type="text"
                  placeholder="Enter invoice number"
                  value={searchFilters.invoiceNumber}
                  onChange={(e) => handleSearchChange('invoiceNumber', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                />
              </div>
              
              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">Customer Name</label>
                <input
                  type="text"
                  placeholder="Enter customer name"
                  value={searchFilters.customerName}
                  onChange={(e) => handleSearchChange('customerName', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                />
              </div>
              
              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">Customer Code</label>
                <input
                  type="text"
                  placeholder="Enter customer code"
                  value={searchFilters.customerCode}
                  onChange={(e) => handleSearchChange('customerCode', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                />
              </div>
              
              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">Start Date</label>
                <input
                  type="date"
                  value={searchFilters.startDate}
                  onChange={(e) => handleSearchChange('startDate', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                />
              </div>
              
              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">End Date</label>
                <input
                  type="date"
                  value={searchFilters.endDate}
                  onChange={(e) => handleSearchChange('endDate', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                />
              </div>
            </div>
          )}
          
          <div className="mt-3 pt-3 border-t border-gray-200">
            <p className="text-xs sm:text-sm text-gray-600">
              {loading ? (
                <span>Searching...</span>
              ) : (
                <span>
                  Found <strong>{receivables.length}</strong> receivable{receivables.length !== 1 ? 's' : ''}
                  {hasActiveFilters && ' matching your filters'}
                </span>
              )}
            </p>
          </div>
        </div>
        
        <div className="p-3 sm:p-4">
          {loading ? (
            <div className="text-center py-8 text-sm">Loading...</div>
          ) : (
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                    <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">Customer Code</th>
                    <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Customer Name</th>
                    <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">Received</th>
                    <th className="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {receivables.length === 0 ? (
                    <tr>
                      <td colSpan="7" className="px-2 sm:px-4 lg:px-6 py-8 text-center text-gray-500">
                        <Receipt className="w-12 h-12 mx-auto mb-4 text-gray-400" />
                        <p className="text-sm sm:text-base">
                          {hasActiveFilters ? 'No receivables found matching your filters' : 'No receivables recorded yet'}
                        </p>
                      </td>
                    </tr>
                  ) : (
                    receivables.map((rec) => (
                      <tr key={rec.id} className="hover:bg-gray-50">
                        <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-gray-900">
                          {new Date(rec.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                        </td>
                        <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-gray-900 font-medium">
                          {rec.invoiceNumber || '-'}
                        </td>
                        <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-gray-900 hidden sm:table-cell">
                          {rec.customerCode || '-'}
                        </td>
                        <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-gray-900 hidden md:table-cell">
                          {rec.customerName || '-'}
                        </td>
                        <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                          ${parseFloat(rec.amount).toFixed(2)}
                        </td>
                        <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                          ${parseFloat(rec.received || 0).toFixed(2)}
                        </td>
                        <td className="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                          ${(parseFloat(rec.amount) - parseFloat(rec.received || 0)).toFixed(2)}
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
