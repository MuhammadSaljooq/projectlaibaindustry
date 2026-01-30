import express from 'express'
import { body, param } from 'express-validator'
import { PrismaClient } from '@prisma/client'
import { validate } from '../middleware/validation.js'

const router = express.Router()
const prisma = new PrismaClient()

// Get tax settings (get or create default)
router.get('/', async (req, res, next) => {
  try {
    let taxSetting = await prisma.taxSetting.findFirst()
    
    if (!taxSetting) {
      // Create default tax setting
      taxSetting = await prisma.taxSetting.create({
        data: {
          defaultRate: 0,
          description: 'Default tax rate',
        },
      })
    }
    
    res.json(taxSetting)
  } catch (error) {
    next(error)
  }
})

// Update tax settings
router.put(
  '/:id',
  [
    param('id').isInt(),
    body('defaultRate').isFloat({ min: 0, max: 100 }).withMessage('Tax rate must be between 0 and 100'),
    body('description').optional().isString(),
  ],
  validate,
  async (req, res, next) => {
    try {
      const { defaultRate, description } = req.body
      
      const taxSetting = await prisma.taxSetting.update({
        where: { id: parseInt(req.params.id) },
        data: {
          defaultRate: parseFloat(defaultRate),
          description,
        },
      })
      
      res.json(taxSetting)
    } catch (error) {
      if (error.code === 'P2025') {
        return res.status(404).json({ error: 'Tax setting not found' })
      }
      next(error)
    }
  }
)

export default router
