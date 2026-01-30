import express from 'express'
import { body, param } from 'express-validator'
import { PrismaClient } from '@prisma/client'
import { validate } from '../middleware/validation.js'

const router = express.Router()
const prisma = new PrismaClient()

// Get all categories
router.get('/', async (req, res, next) => {
  try {
    const categories = await prisma.category.findMany({
      include: {
        _count: {
          select: { products: true },
        },
      },
      orderBy: {
        name: 'asc',
      },
    })
    
    res.json(categories)
  } catch (error) {
    next(error)
  }
})

// Get single category
router.get(
  '/:id',
  [param('id').isInt()],
  validate,
  async (req, res, next) => {
    try {
      const category = await prisma.category.findUnique({
        where: { id: parseInt(req.params.id) },
        include: {
          products: true,
        },
      })
      
      if (!category) {
        return res.status(404).json({ error: 'Category not found' })
      }
      
      res.json(category)
    } catch (error) {
      next(error)
    }
  }
)

// Create category
router.post(
  '/',
  [
    body('name').notEmpty().withMessage('Name is required'),
    body('description').optional().isString(),
  ],
  validate,
  async (req, res, next) => {
    try {
      const { name, description } = req.body
      
      const category = await prisma.category.create({
        data: {
          name,
          description,
        },
      })
      
      res.status(201).json(category)
    } catch (error) {
      if (error.code === 'P2002') {
        return res.status(409).json({ error: 'Category with this name already exists' })
      }
      next(error)
    }
  }
)

// Update category
router.put(
  '/:id',
  [
    param('id').isInt(),
    body('name').optional().notEmpty(),
    body('description').optional().isString(),
  ],
  validate,
  async (req, res, next) => {
    try {
      const { id } = req.params
      const updateData = {}
      
      if (req.body.name) updateData.name = req.body.name
      if (req.body.description !== undefined) updateData.description = req.body.description
      
      const category = await prisma.category.update({
        where: { id: parseInt(id) },
        data: updateData,
      })
      
      res.json(category)
    } catch (error) {
      if (error.code === 'P2025') {
        return res.status(404).json({ error: 'Category not found' })
      }
      if (error.code === 'P2002') {
        return res.status(409).json({ error: 'Category with this name already exists' })
      }
      next(error)
    }
  }
)

// Delete category
router.delete(
  '/:id',
  [param('id').isInt()],
  validate,
  async (req, res, next) => {
    try {
      const category = await prisma.category.findUnique({
        where: { id: parseInt(req.params.id) },
        include: { _count: { select: { products: true } } },
      })
      
      if (!category) {
        return res.status(404).json({ error: 'Category not found' })
      }
      
      if (category._count.products > 0) {
        return res.status(400).json({
          error: 'Cannot delete category with existing products',
          productCount: category._count.products,
        })
      }
      
      await prisma.category.delete({
        where: { id: parseInt(req.params.id) },
      })
      
      res.json({ message: 'Category deleted successfully' })
    } catch (error) {
      next(error)
    }
  }
)

export default router
