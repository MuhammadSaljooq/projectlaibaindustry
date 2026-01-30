import express from 'express'
import { query } from 'express-validator'
import { PrismaClient } from '@prisma/client'
import { validate } from '../middleware/validation.js'

const router = express.Router()
const prisma = new PrismaClient()

// Dashboard summary
router.get('/dashboard', async (req, res, next) => {
  try {
    // Get date range (default to last 30 days)
    const endDate = new Date()
    const startDate = new Date()
    startDate.setDate(startDate.getDate() - 30)
    
    // Total sales
    const sales = await prisma.sale.findMany({
      where: {
        date: {
          gte: startDate,
          lte: endDate,
        },
      },
      include: {
        salesItems: true,
      },
    })
    
    const totalSales = sales.reduce((sum, sale) => sum + parseFloat(sale.totalAmount), 0)
    const totalProfit = sales.reduce((sum, sale) => {
      const saleProfit = sale.salesItems.reduce((itemSum, item) => {
        return itemSum + parseFloat(item.profit)
      }, 0)
      return sum + saleProfit
    }, 0)
    
    // Inventory metrics
    const products = await prisma.product.findMany()
    const totalInventoryValue = products.reduce((sum, p) => {
      return sum + (parseFloat(p.costPrice) * p.stockQuantity)
    }, 0)
    const lowStockCount = products.filter(p => p.stockQuantity <= p.reorderLevel).length
    
    // Recent sales count
    const recentSalesCount = sales.length
    
    res.json({
      totalSales,
      totalProfit,
      totalInventoryValue,
      lowStockCount,
      recentSalesCount,
      totalProducts: products.length,
    })
  } catch (error) {
    next(error)
  }
})

// Sales summary by period
router.get(
  '/sales-summary',
  [
    query('period').optional().isIn(['daily', 'weekly', 'monthly']),
    query('startDate').optional().isISO8601(),
    query('endDate').optional().isISO8601(),
  ],
  validate,
  async (req, res, next) => {
    try {
      const { period = 'daily', startDate, endDate } = req.query
      
      let dateStart = startDate ? new Date(startDate) : new Date()
      let dateEnd = endDate ? new Date(endDate) : new Date()
      
      // If no dates provided, set default based on period
      if (!startDate && !endDate) {
        dateEnd = new Date()
        dateStart = new Date()
        
        switch (period) {
          case 'daily':
            dateStart.setDate(dateStart.getDate() - 7)
            break
          case 'weekly':
            dateStart.setDate(dateStart.getDate() - 28)
            break
          case 'monthly':
            dateStart.setMonth(dateStart.getMonth() - 6)
            break
        }
      }
      
      const sales = await prisma.sale.findMany({
        where: {
          date: {
            gte: dateStart,
            lte: dateEnd,
          },
        },
        include: {
          salesItems: true,
        },
        orderBy: {
          date: 'asc',
        },
      })
      
      // Group by period
      const grouped = {}
      
      sales.forEach(sale => {
        const saleDate = new Date(sale.date)
        let key
        
        switch (period) {
          case 'daily':
            key = saleDate.toISOString().split('T')[0]
            break
          case 'weekly':
            const weekStart = new Date(saleDate)
            weekStart.setDate(saleDate.getDate() - saleDate.getDay())
            key = weekStart.toISOString().split('T')[0]
            break
          case 'monthly':
            key = `${saleDate.getFullYear()}-${String(saleDate.getMonth() + 1).padStart(2, '0')}`
            break
        }
        
        if (!grouped[key]) {
          grouped[key] = {
            period: key,
            totalSales: 0,
            totalProfit: 0,
            transactionCount: 0,
          }
        }
        
        grouped[key].totalSales += parseFloat(sale.totalAmount)
        grouped[key].transactionCount += 1
        const saleProfit = sale.salesItems.reduce((sum, item) => sum + parseFloat(item.profit), 0)
        grouped[key].totalProfit += saleProfit
      })
      
      res.json(Object.values(grouped))
    } catch (error) {
      next(error)
    }
  }
)

// Top selling products
router.get(
  '/top-products',
  [query('limit').optional().isInt({ min: 1, max: 100 })],
  validate,
  async (req, res, next) => {
    try {
      const limit = parseInt(req.query.limit) || 10
      
      const salesItems = await prisma.salesItem.groupBy({
        by: ['productId'],
        _sum: {
          quantity: true,
        },
        _count: {
          id: true,
        },
        orderBy: {
          _sum: {
            quantity: 'desc',
          },
        },
        take: limit,
      })
      
      const productIds = salesItems.map(item => item.productId)
      const products = await prisma.product.findMany({
        where: { id: { in: productIds } },
        include: { category: true },
      })
      
      const topProducts = salesItems.map(item => {
        const product = products.find(p => p.id === item.productId)
        return {
          product,
          totalQuantitySold: item._sum.quantity,
          timesSold: item._count.id,
        }
      })
      
      res.json(topProducts)
    } catch (error) {
      next(error)
    }
  }
)

// Profit margins analysis
router.get('/profit-margins', async (req, res, next) => {
  try {
    const sales = await prisma.sale.findMany({
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
    
    const categoryProfits = {}
    
    sales.forEach(sale => {
      sale.salesItems.forEach(item => {
        const categoryName = item.product.category.name
        if (!categoryProfits[categoryName]) {
          categoryProfits[categoryName] = {
            category: categoryName,
            totalProfit: 0,
            totalSales: 0,
            itemCount: 0,
          }
        }
        
        categoryProfits[categoryName].totalProfit += parseFloat(item.profit)
        categoryProfits[categoryName].totalSales += parseFloat(item.sellingPrice) * item.quantity
        categoryProfits[categoryName].itemCount += item.quantity
      })
    })
    
    // Calculate margins
    const margins = Object.values(categoryProfits).map(cat => ({
      ...cat,
      margin: cat.totalSales > 0 ? (cat.totalProfit / cat.totalSales) * 100 : 0,
    }))
    
    res.json(margins)
  } catch (error) {
    next(error)
  }
})

export default router
