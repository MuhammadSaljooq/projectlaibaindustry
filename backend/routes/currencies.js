import express from 'express'
import { body, validationResult } from 'express-validator'
import { PrismaClient } from '@prisma/client'

const router = express.Router()
const prisma = new PrismaClient()

// Get all currencies
router.get('/', async (req, res) => {
  try {
    const currencies = await prisma.currency.findMany({
      orderBy: [
        { isDefault: 'desc' },
        { code: 'asc' }
      ]
    })
    res.json(currencies)
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch currencies' })
  }
})

// Get active currencies only
router.get('/active', async (req, res) => {
  try {
    const currencies = await prisma.currency.findMany({
      where: { isActive: true },
      orderBy: [
        { isDefault: 'desc' },
        { code: 'asc' }
      ]
    })
    res.json(currencies)
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch active currencies' })
  }
})

// Get default currency
router.get('/default', async (req, res) => {
  try {
    const currency = await prisma.currency.findFirst({
      where: { isDefault: true, isActive: true }
    })
    if (!currency) {
      return res.status(404).json({ error: 'No default currency found' })
    }
    res.json(currency)
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch default currency' })
  }
})

// Get single currency
router.get('/:id', async (req, res) => {
  try {
    const currency = await prisma.currency.findUnique({
      where: { id: parseInt(req.params.id) },
      include: {
        exchangeRates: {
          include: {
            toCurrency: true
          },
          orderBy: {
            effectiveDate: 'desc'
          },
          take: 1
        }
      }
    })
    if (!currency) {
      return res.status(404).json({ error: 'Currency not found' })
    }
    res.json(currency)
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch currency' })
  }
})

// Create currency
router.post(
  '/',
  [
    body('code').trim().isLength({ min: 3, max: 3 }).withMessage('Currency code must be 3 characters'),
    body('name').trim().notEmpty().withMessage('Currency name is required'),
    body('symbol').trim().notEmpty().withMessage('Currency symbol is required'),
    body('decimalPlaces').optional().isInt({ min: 0, max: 4 }).withMessage('Decimal places must be 0-4'),
  ],
  async (req, res) => {
    try {
      const errors = validationResult(req)
      if (!errors.isEmpty()) {
        return res.status(400).json({ errors: errors.array() })
      }

      const { code, name, symbol, isDefault, isActive, decimalPlaces } = req.body

      // If setting as default, unset other defaults
      if (isDefault) {
        await prisma.currency.updateMany({
          where: { isDefault: true },
          data: { isDefault: false }
        })
      }

      const currency = await prisma.currency.create({
        data: {
          code: code.toUpperCase(),
          name,
          symbol,
          isDefault: isDefault || false,
          isActive: isActive !== undefined ? isActive : true,
          decimalPlaces: decimalPlaces || 2
        }
      })

      res.status(201).json(currency)
    } catch (error) {
      if (error.code === 'P2002') {
        return res.status(400).json({ error: 'Currency code already exists' })
      }
      res.status(500).json({ error: 'Failed to create currency' })
    }
  }
)

// Update currency
router.put(
  '/:id',
  [
    body('code').optional().trim().isLength({ min: 3, max: 3 }).withMessage('Currency code must be 3 characters'),
    body('name').optional().trim().notEmpty().withMessage('Currency name cannot be empty'),
    body('symbol').optional().trim().notEmpty().withMessage('Currency symbol cannot be empty'),
  ],
  async (req, res) => {
    try {
      const errors = validationResult(req)
      if (!errors.isEmpty()) {
        return res.status(400).json({ errors: errors.array() })
      }

      const { code, name, symbol, isDefault, isActive, decimalPlaces } = req.body
      const id = parseInt(req.params.id)

      // If setting as default, unset other defaults
      if (isDefault) {
        await prisma.currency.updateMany({
          where: { 
            isDefault: true,
            id: { not: id }
          },
          data: { isDefault: false }
        })
      }

      const currency = await prisma.currency.update({
        where: { id },
        data: {
          ...(code && { code: code.toUpperCase() }),
          ...(name && { name }),
          ...(symbol && { symbol }),
          ...(isDefault !== undefined && { isDefault }),
          ...(isActive !== undefined && { isActive }),
          ...(decimalPlaces !== undefined && { decimalPlaces })
        }
      })

      res.json(currency)
    } catch (error) {
      if (error.code === 'P2025') {
        return res.status(404).json({ error: 'Currency not found' })
      }
      if (error.code === 'P2002') {
        return res.status(400).json({ error: 'Currency code already exists' })
      }
      res.status(500).json({ error: 'Failed to update currency' })
    }
  }
)

// Delete currency
router.delete('/:id', async (req, res) => {
  try {
    const id = parseInt(req.params.id)
    
    // Check if currency is in use
    const [products, sales] = await Promise.all([
      prisma.product.count({ where: { currencyId: id } }),
      prisma.sale.count({ where: { currencyId: id } })
    ])

    if (products > 0 || sales > 0) {
      return res.status(400).json({ 
        error: 'Cannot delete currency that is in use. Deactivate it instead.' 
      })
    }

    await prisma.currency.delete({
      where: { id }
    })

    res.json({ message: 'Currency deleted successfully' })
  } catch (error) {
    if (error.code === 'P2025') {
      return res.status(404).json({ error: 'Currency not found' })
    }
    res.status(500).json({ error: 'Failed to delete currency' })
  }
})

// Get exchange rate between two currencies
router.get('/exchange-rate/:fromCode/:toCode', async (req, res) => {
  try {
    const { fromCode, toCode } = req.params

    if (fromCode === toCode) {
      return res.json({ rate: 1 })
    }

    const fromCurrency = await prisma.currency.findUnique({
      where: { code: fromCode.toUpperCase() }
    })

    const toCurrency = await prisma.currency.findUnique({
      where: { code: toCode.toUpperCase() }
    })

    if (!fromCurrency || !toCurrency) {
      return res.status(404).json({ error: 'Currency not found' })
    }

    // Get latest exchange rate
    const exchangeRate = await prisma.exchangeRate.findFirst({
      where: {
        fromCurrencyId: fromCurrency.id,
        toCurrencyId: toCurrency.id
      },
      orderBy: {
        effectiveDate: 'desc'
      }
    })

    if (!exchangeRate) {
      return res.status(404).json({ error: 'Exchange rate not found' })
    }

    res.json({ 
      rate: exchangeRate.rate.toString(),
      effectiveDate: exchangeRate.effectiveDate,
      fromCurrency: fromCurrency.code,
      toCurrency: toCurrency.code
    })
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch exchange rate' })
  }
})

// Create/Update exchange rate
router.post(
  '/exchange-rate',
  [
    body('fromCurrencyId').isInt().withMessage('From currency ID is required'),
    body('toCurrencyId').isInt().withMessage('To currency ID is required'),
    body('rate').isFloat({ min: 0 }).withMessage('Exchange rate must be a positive number'),
  ],
  async (req, res) => {
    try {
      const errors = validationResult(req)
      if (!errors.isEmpty()) {
        return res.status(400).json({ errors: errors.array() })
      }

      const { fromCurrencyId, toCurrencyId, rate, effectiveDate } = req.body

      if (fromCurrencyId === toCurrencyId) {
        return res.status(400).json({ error: 'From and to currencies cannot be the same' })
      }

      const exchangeRate = await prisma.exchangeRate.create({
        data: {
          fromCurrencyId: parseInt(fromCurrencyId),
          toCurrencyId: parseInt(toCurrencyId),
          rate: parseFloat(rate),
          effectiveDate: effectiveDate ? new Date(effectiveDate) : new Date()
        },
        include: {
          fromCurrency: true,
          toCurrency: true
        }
      })

      res.status(201).json(exchangeRate)
    } catch (error) {
      if (error.code === 'P2002') {
        return res.status(400).json({ error: 'Exchange rate for this date already exists' })
      }
      res.status(500).json({ error: 'Failed to create exchange rate' })
    }
  }
)

// Get all exchange rates
router.get('/exchange-rates/all', async (req, res) => {
  try {
    const exchangeRates = await prisma.exchangeRate.findMany({
      include: {
        fromCurrency: true,
        toCurrency: true
      },
      orderBy: {
        effectiveDate: 'desc'
      }
    })
    res.json(exchangeRates)
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch exchange rates' })
  }
})

export default router
