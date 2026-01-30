import express from 'express'
import productsRouter from './products.js'
import categoriesRouter from './categories.js'
import salesRouter from './sales.js'
import receivablesRouter from './receivables.js'
import taxRouter from './tax.js'
import analyticsRouter from './analytics.js'
import currenciesRouter from './currencies.js'

const router = express.Router()

// API info
router.get('/', (req, res) => {
  res.json({ message: 'Inventory & Sales Management API' })
})

// Route handlers
router.use('/products', productsRouter)
router.use('/categories', categoriesRouter)
router.use('/sales', salesRouter)
router.use('/receivables', receivablesRouter)
router.use('/tax', taxRouter)
router.use('/analytics', analyticsRouter)
router.use('/currencies', currenciesRouter)

export default router
