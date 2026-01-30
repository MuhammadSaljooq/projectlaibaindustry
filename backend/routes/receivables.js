import express from 'express'
import { body, query, param } from 'express-validator'
import { PrismaClient } from '@prisma/client'
import { validate } from '../middleware/validation.js'

const router = express.Router()
const prisma = new PrismaClient()

// Get all receivables with filters
router.get(
  '/',
  [
    query('startDate').optional().isISO8601(),
    query('endDate').optional().isISO8601(),
    query('customerName').optional().isString(),
    query('customerCode').optional().isString(),
    query('invoiceNumber').optional().isString(),
    query('search').optional().isString(),
  ],
  validate,
  async (req, res, next) => {
    try {
      const { startDate, endDate, customerName, customerCode, invoiceNumber, search } = req.query
      
      const where = {}
      
      if (startDate || endDate) {
        where.date = {}
        if (startDate) where.date.gte = new Date(startDate)
        if (endDate) {
          const endDateTime = new Date(endDate)
          endDateTime.setHours(23, 59, 59, 999)
          where.date.lte = endDateTime
        }
      }
      
      if (customerName) {
        where.customerName = { contains: customerName, mode: 'insensitive' }
      }
      
      if (customerCode) {
        where.customerCode = { contains: customerCode, mode: 'insensitive' }
      }
      
      if (invoiceNumber) {
        where.invoiceNumber = { contains: invoiceNumber, mode: 'insensitive' }
      }
      
      if (search) {
        where.OR = [
          { customerName: { contains: search, mode: 'insensitive' } },
          { customerCode: { contains: search, mode: 'insensitive' } },
          { invoiceNumber: { contains: search, mode: 'insensitive' } },
        ]
      }
      
      const receivables = await prisma.receivable.findMany({
        where,
        orderBy: {
          date: 'desc',
        },
      })
      
      res.json(receivables)
    } catch (error) {
      next(error)
    }
  }
)

// Create receivables (bulk)
router.post(
  '/',
  [
    body('receivables').isArray().withMessage('Receivables array is required'),
    body('receivables.*.date').optional().isISO8601(),
    body('receivables.*.invoiceNumber').optional().isString(),
    body('receivables.*.customerName').optional().isString(),
    body('receivables.*.customerCode').optional().isString(),
    body('receivables.*.amount').isFloat({ min: 0 }).withMessage('Amount must be a positive number'),
    body('receivables.*.received').optional().isFloat({ min: 0 }).withMessage('Received must be a positive number'),
  ],
  validate,
  async (req, res, next) => {
    try {
      const { receivables } = req.body
      
      const createdReceivables = await prisma.receivable.createMany({
        data: receivables.map(rec => ({
          date: rec.date ? new Date(rec.date) : new Date(),
          invoiceNumber: rec.invoiceNumber || null,
          customerName: rec.customerName || null,
          customerCode: rec.customerCode || null,
          amount: parseFloat(rec.amount),
          received: parseFloat(rec.received || 0),
        })),
      })
      
      res.status(201).json({ 
        message: `Created ${createdReceivables.count} receivable(s)`,
        count: createdReceivables.count 
      })
    } catch (error) {
      next(error)
    }
  }
)

// Get single receivable
router.get(
  '/:id',
  [param('id').isInt()],
  validate,
  async (req, res, next) => {
    try {
      const receivable = await prisma.receivable.findUnique({
        where: { id: parseInt(req.params.id) },
      })
      
      if (!receivable) {
        return res.status(404).json({ error: 'Receivable not found' })
      }
      
      res.json(receivable)
    } catch (error) {
      next(error)
    }
  }
)

// Delete receivable
router.delete(
  '/:id',
  [param('id').isInt()],
  validate,
  async (req, res, next) => {
    try {
      await prisma.receivable.delete({
        where: { id: parseInt(req.params.id) },
      })
      
      res.json({ message: 'Receivable deleted successfully' })
    } catch (error) {
      next(error)
    }
  }
)

export default router
