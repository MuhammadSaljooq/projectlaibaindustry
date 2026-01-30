import express from 'express'
import { body, query, param } from 'express-validator'
import { PrismaClient } from '@prisma/client'
import { validate } from '../middleware/validation.js'

const router = express.Router()
const prisma = new PrismaClient()

// Helper function to get or create default category
async function getOrCreateDefaultCategory() {
  let category = await prisma.category.findFirst({
    where: { name: 'General' },
  })
  
  if (!category) {
    category = await prisma.category.create({
      data: {
        name: 'General',
        description: 'Default category for auto-created products',
      },
    })
  }
  
  return category
}

// Helper function to generate SKU from product name
function generateSKU(productName) {
  const timestamp = Date.now().toString().slice(-6)
  const initials = productName
    .split(' ')
    .map(word => word.charAt(0).toUpperCase())
    .join('')
    .slice(0, 3)
  return `${initials}-${timestamp}`
}

// Helper function to find or create product by name
async function findOrCreateProduct(productName, sellingPrice) {
  // First, try to find existing product by name (case-insensitive)
  let product = await prisma.product.findFirst({
    where: {
      name: {
        equals: productName,
        mode: 'insensitive',
      },
    },
  })
  
  if (product) {
    return product
  }
  
  // Product doesn't exist, create it
  const category = await getOrCreateDefaultCategory()
  const sku = generateSKU(productName)
  const costPrice = sellingPrice ? parseFloat(sellingPrice) * 0.7 : 0 // Default cost is 70% of selling price
  
  product = await prisma.product.create({
    data: {
      name: productName,
      sku,
      categoryId: category.id,
      costPrice,
      sellingPrice: sellingPrice ? parseFloat(sellingPrice) : null,
      stockQuantity: 0,
      reorderLevel: 10,
    },
    include: { category: true },
  })
  
  return product
}

// Get all products with filters and search
router.get(
  '/',
  [
    query('search').optional().isString(),
    query('categoryId').optional().isInt(),
    query('lowStock').optional().isBoolean(),
  ],
  validate,
  async (req, res, next) => {
    try {
      const { search, categoryId, lowStock } = req.query
      
      const where = {}
      
      if (search) {
        where.OR = [
          { name: { contains: search, mode: 'insensitive' } },
          { sku: { contains: search, mode: 'insensitive' } },
          { description: { contains: search, mode: 'insensitive' } },
        ]
      }
      
      if (categoryId) {
        where.categoryId = parseInt(categoryId)
      }
      
      let products = await prisma.product.findMany({
        where,
        include: {
          category: true,
        },
        orderBy: {
          createdAt: 'desc',
        },
      })
      
      // Filter low stock items if requested
      if (lowStock === 'true') {
        products = products.filter(p => p.stockQuantity <= p.reorderLevel)
      }
      
      res.json(products)
    } catch (error) {
      next(error)
    }
  }
)

// Get single product
router.get(
  '/:id',
  [param('id').isInt()],
  validate,
  async (req, res, next) => {
    try {
      const product = await prisma.product.findUnique({
        where: { id: parseInt(req.params.id) },
        include: { category: true },
      })
      
      if (!product) {
        return res.status(404).json({ error: 'Product not found' })
      }
      
      res.json(product)
    } catch (error) {
      next(error)
    }
  }
)

// Create product
router.post(
  '/',
  [
    body('name').notEmpty().withMessage('Name is required'),
    body('sku').optional().isString(),
    body('categoryId').optional().isInt(),
    body('costPrice').optional().isFloat({ min: 0 }),
    body('sellingPrice').optional().isFloat({ min: 0 }),
    body('description').optional().isString(),
    body('stockQuantity').optional().isInt({ min: 0 }),
    body('reorderLevel').optional().isInt({ min: 0 }),
  ],
  validate,
  async (req, res, next) => {
    try {
      const {
        name,
        sku,
        categoryId,
        costPrice,
        sellingPrice,
        description,
        stockQuantity = 0,
        reorderLevel = 10,
      } = req.body
      
      // Generate SKU if not provided
      let finalSku = sku || generateSKU(name)
      
      // Check if SKU already exists
      let existingProduct = await prisma.product.findUnique({
        where: { sku: finalSku },
      })
      
      // If SKU exists, generate a new one
      if (existingProduct) {
        finalSku = generateSKU(name)
      }
      
      // Get or create category
      let category
      if (categoryId) {
        category = await prisma.category.findUnique({
          where: { id: parseInt(categoryId) },
        })
        if (!category) {
          return res.status(404).json({ error: 'Category not found' })
        }
      } else {
        category = await getOrCreateDefaultCategory()
      }
      
      const product = await prisma.product.create({
        data: {
          name,
          sku: finalSku,
          categoryId: category.id,
          costPrice: costPrice ? parseFloat(costPrice) : (sellingPrice ? parseFloat(sellingPrice) * 0.7 : 0),
          sellingPrice: sellingPrice ? parseFloat(sellingPrice) : null,
          description,
          stockQuantity: parseInt(stockQuantity),
          reorderLevel: parseInt(reorderLevel),
        },
        include: { category: true },
      })
      
      res.status(201).json(product)
    } catch (error) {
      next(error)
    }
  }
)

// Auto-create product from name (used by sales)
router.post(
  '/auto-create',
  [
    body('name').notEmpty().withMessage('Product name is required'),
    body('sellingPrice').optional().isFloat({ min: 0 }),
  ],
  validate,
  async (req, res, next) => {
    try {
      const { name, sellingPrice } = req.body
      const product = await findOrCreateProduct(name, sellingPrice)
      res.json(product)
    } catch (error) {
      next(error)
    }
  }
)

// Update product
router.put(
  '/:id',
  [
    param('id').isInt(),
    body('name').optional().notEmpty(),
    body('sku').optional().notEmpty(),
    body('categoryId').optional().isInt(),
    body('costPrice').optional().isFloat({ min: 0 }),
    body('sellingPrice').optional().isFloat({ min: 0 }),
    body('description').optional().isString(),
    body('stockQuantity').optional().isInt({ min: 0 }),
    body('reorderLevel').optional().isInt({ min: 0 }),
  ],
  validate,
  async (req, res, next) => {
    try {
      const { id } = req.params
      const updateData = {}
      
      if (req.body.name) updateData.name = req.body.name
      if (req.body.sku) {
        // Check if SKU is being changed and if new SKU exists
        const existing = await prisma.product.findUnique({ where: { sku: req.body.sku } })
        if (existing && existing.id !== parseInt(id)) {
          return res.status(409).json({ error: 'SKU already exists' })
        }
        updateData.sku = req.body.sku
      }
      if (req.body.categoryId) {
        const category = await prisma.category.findUnique({
          where: { id: parseInt(req.body.categoryId) },
        })
        if (!category) {
          return res.status(404).json({ error: 'Category not found' })
        }
        updateData.categoryId = parseInt(req.body.categoryId)
      }
      if (req.body.costPrice !== undefined) updateData.costPrice = parseFloat(req.body.costPrice)
      if (req.body.sellingPrice !== undefined) {
        updateData.sellingPrice = req.body.sellingPrice ? parseFloat(req.body.sellingPrice) : null
      }
      if (req.body.description !== undefined) updateData.description = req.body.description
      if (req.body.stockQuantity !== undefined) updateData.stockQuantity = parseInt(req.body.stockQuantity)
      if (req.body.reorderLevel !== undefined) updateData.reorderLevel = parseInt(req.body.reorderLevel)
      
      const product = await prisma.product.update({
        where: { id: parseInt(id) },
        data: updateData,
        include: { category: true },
      })
      
      res.json(product)
    } catch (error) {
      if (error.code === 'P2025') {
        return res.status(404).json({ error: 'Product not found' })
      }
      next(error)
    }
  }
)

// Delete product
router.delete(
  '/:id',
  [param('id').isInt()],
  validate,
  async (req, res, next) => {
    try {
      await prisma.product.delete({
        where: { id: parseInt(req.params.id) },
      })
      
      res.json({ message: 'Product deleted successfully' })
    } catch (error) {
      if (error.code === 'P2025') {
        return res.status(404).json({ error: 'Product not found' })
      }
      next(error)
    }
  }
)

// Get inventory metrics
router.get('/metrics/inventory', async (req, res, next) => {
  try {
    const products = await prisma.product.findMany()
    
    const totalValue = products.reduce((sum, p) => {
      return sum + (parseFloat(p.costPrice) * p.stockQuantity)
    }, 0)
    
    const lowStockCount = products.filter(p => p.stockQuantity <= p.reorderLevel).length
    
    const totalProducts = products.length
    
    res.json({
      totalProducts,
      totalInventoryValue: totalValue,
      lowStockCount,
      totalStockQuantity: products.reduce((sum, p) => sum + p.stockQuantity, 0),
    })
  } catch (error) {
    next(error)
  }
})

// Export helper function for use in sales route
export { findOrCreateProduct }

// Export router as default
export default router
