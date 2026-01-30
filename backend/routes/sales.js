import express from 'express'
import { body, param, query } from 'express-validator'
import { PrismaClient } from '@prisma/client'
import { validate } from '../middleware/validation.js'
import { findOrCreateProduct } from './products.js'

const router = express.Router()
const prisma = new PrismaClient()

// Helper function to calculate sale totals
function calculateSaleTotals(items, taxRate, discountAmount = 0, discountType = 'fixed') {
  let subtotal = 0
  let totalProfit = 0
  
  items.forEach(item => {
    const itemTotal = parseFloat(item.sellingPrice) * item.quantity
    subtotal += itemTotal
    const itemCost = parseFloat(item.costPrice) * item.quantity
    totalProfit += (itemTotal - itemCost)
  })
  
  // Apply discount
  let finalDiscount = 0
  if (discountType === 'percentage') {
    finalDiscount = (subtotal * parseFloat(discountAmount)) / 100
  } else {
    finalDiscount = parseFloat(discountAmount)
  }
  
  const subtotalAfterDiscount = subtotal - finalDiscount
  const taxAmount = (subtotalAfterDiscount * parseFloat(taxRate)) / 100
  const totalAmount = subtotalAfterDiscount + taxAmount
  
  return {
    subtotal,
    discountAmount: finalDiscount,
    taxAmount,
    totalAmount,
    totalProfit,
  }
}

// Get all sales with filters
router.get(
  '/',
  [
    query('startDate').optional().isISO8601(),
    query('endDate').optional().isISO8601(),
    query('customerName').optional().isString(),
    query('customerCode').optional().isString(),
    query('invoiceNumber').optional().isString(),
    query('search').optional().isString(), // General search across multiple fields
  ],
  validate,
  async (req, res, next) => {
    try {
      const { startDate, endDate, customerName, customerCode, invoiceNumber, search } = req.query
      
      const where = {}
      
      // Date range filter
      if (startDate || endDate) {
        where.date = {}
        if (startDate) where.date.gte = new Date(startDate)
        if (endDate) {
          // Include the entire end date (set to end of day)
          const endDateTime = new Date(endDate)
          endDateTime.setHours(23, 59, 59, 999)
          where.date.lte = endDateTime
        }
      }
      
      // Specific field filters
      if (customerName) {
        where.customerName = { contains: customerName, mode: 'insensitive' }
      }
      
      if (customerCode) {
        where.customerCode = { contains: customerCode, mode: 'insensitive' }
      }
      
      if (invoiceNumber) {
        where.invoiceNumber = { contains: invoiceNumber, mode: 'insensitive' }
      }
      
      // General search across multiple fields (industry standard)
      if (search) {
        where.OR = [
          { customerName: { contains: search, mode: 'insensitive' } },
          { customerCode: { contains: search, mode: 'insensitive' } },
          { invoiceNumber: { contains: search, mode: 'insensitive' } },
        ]
      }
      
      const sales = await prisma.sale.findMany({
        where,
        include: {
          salesItems: {
            include: {
              product: {
                include: {
                  category: true,
                },
              },
            },
          },
        },
        orderBy: {
          date: 'desc',
        },
      })
      
      res.json(sales)
    } catch (error) {
      next(error)
    }
  }
)

// Get single sale
router.get(
  '/:id',
  [param('id').isInt()],
  validate,
  async (req, res, next) => {
    try {
      const sale = await prisma.sale.findUnique({
        where: { id: parseInt(req.params.id) },
        include: {
          salesItems: {
            include: {
              product: {
                include: {
                  category: true,
                },
              },
            },
          },
        },
      })
      
      if (!sale) {
        return res.status(404).json({ error: 'Sale not found' })
      }
      
      res.json(sale)
    } catch (error) {
      next(error)
    }
  }
)

// Create sale
router.post(
  '/',
  [
    body('date').optional().isISO8601(),
    body('customerCode').optional().isString(),
    body('customerName').optional().isString(),
    body('invoiceNumber').optional().isString(),
    body('items').isArray().withMessage('Items array is required'),
    body('items.*.productId').isInt().withMessage('Product ID must be an integer'),
    body('items.*.quantity').isInt({ min: 1 }).withMessage('Quantity must be at least 1'),
    body('items.*.sellingPrice').isFloat({ min: 0 }).withMessage('Selling price must be a positive number'),
    body('taxRate').isFloat({ min: 0, max: 100 }).withMessage('Tax rate must be between 0 and 100'),
    body('discountAmount').optional().isFloat({ min: 0 }),
    body('discountType').optional().isIn(['fixed', 'percentage']),
  ],
  validate,
  async (req, res, next) => {
    try {
      const {
        date,
        customerCode,
        customerName,
        invoiceNumber,
        items,
        taxRate,
        discountAmount = 0,
        discountType = 'fixed',
      } = req.body
      
      // Validate all products exist and have sufficient stock
      const productIds = items.map(item => item.productId)
      const products = await prisma.product.findMany({
        where: { id: { in: productIds } },
      })
      
      if (products.length !== productIds.length) {
        return res.status(404).json({ error: 'One or more products not found' })
      }
      
      // Check stock availability and prepare sales items
      const salesItemsData = []
      for (const item of items) {
        const product = products.find(p => p.id === item.productId)
        
        if (product.stockQuantity < item.quantity) {
          return res.status(400).json({
            error: `Insufficient stock for ${product.name}. Available: ${product.stockQuantity}, Requested: ${item.quantity}`,
          })
        }
        
        const costPrice = parseFloat(product.costPrice)
        const sellingPrice = parseFloat(item.sellingPrice)
        const profit = (sellingPrice - costPrice) * item.quantity
        const itemTax = (sellingPrice * item.quantity * parseFloat(taxRate)) / 100
        
        salesItemsData.push({
          productId: item.productId,
          quantity: item.quantity,
          costPrice,
          sellingPrice,
          profit,
          taxApplied: itemTax,
        })
      }
      
      // Calculate totals
      const totals = calculateSaleTotals(
        salesItemsData.map(item => ({
          costPrice: item.costPrice,
          sellingPrice: item.sellingPrice,
          quantity: item.quantity,
        })),
        taxRate,
        discountAmount,
        discountType
      )
      
      // Create sale transaction
      const sale = await prisma.$transaction(async (tx) => {
        // Create sale
        const newSale = await tx.sale.create({
          data: {
            date: date ? new Date(date) : new Date(),
            customerCode,
            customerName,
            invoiceNumber,
            subtotal: totals.subtotal,
            discountAmount: totals.discountAmount,
            taxAmount: totals.taxAmount,
            totalAmount: totals.totalAmount,
            taxRate: parseFloat(taxRate),
            salesItems: {
              create: salesItemsData,
            },
          },
          include: {
            salesItems: {
              include: {
                product: {
                  include: {
                    category: true,
                  },
                },
              },
            },
          },
        })
        
        // Update product stock quantities (only if product has stock tracking)
        for (const item of salesItemsData) {
          const product = products.find(p => p.id === item.productId)
          if (product && product.stockQuantity !== undefined) {
            await tx.product.update({
              where: { id: item.productId },
              data: {
                stockQuantity: {
                  decrement: item.quantity,
                },
              },
            })
          }
        }
        
        return newSale
      })
      
      res.status(201).json(sale)
    } catch (error) {
      next(error)
    }
  }
)

// Delete sale (with stock restoration)
router.delete(
  '/:id',
  [param('id').isInt()],
  validate,
  async (req, res, next) => {
    try {
      const sale = await prisma.sale.findUnique({
        where: { id: parseInt(req.params.id) },
        include: {
          salesItems: true,
        },
      })
      
      if (!sale) {
        return res.status(404).json({ error: 'Sale not found' })
      }
      
      await prisma.$transaction(async (tx) => {
        // Restore stock
        for (const item of sale.salesItems) {
          await tx.product.update({
            where: { id: item.productId },
            data: {
              stockQuantity: {
                increment: item.quantity,
              },
            },
          })
        }
        
        // Delete sale (cascade will delete sales items)
        await tx.sale.delete({
          where: { id: parseInt(req.params.id) },
        })
      })
      
      res.json({ message: 'Sale deleted successfully' })
    } catch (error) {
      next(error)
    }
  }
)

export default router
