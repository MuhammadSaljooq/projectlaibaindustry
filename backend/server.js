import express from 'express'
import cors from 'cors'
import dotenv from 'dotenv'
import { PrismaClient } from '@prisma/client'
import { errorHandler } from './middleware/errorHandler.js'
import routes from './routes/index.js'

dotenv.config()

const app = express()
const prisma = new PrismaClient()
const PORT = process.env.PORT || 5000

// Middleware
// CORS configuration - allow specific origins in production
const corsOptions = {
  origin: process.env.CORS_ORIGIN 
    ? process.env.CORS_ORIGIN.split(',')
    : '*', // Allow all in development
  credentials: true
}
app.use(cors(corsOptions))
app.use(express.json())
app.use(express.urlencoded({ extended: true }))

// Health check endpoint
app.get('/api/health', (req, res) => {
  res.json({ status: 'ok', message: 'Server is running' })
})

// API Routes
app.use('/api', routes)

// 404 handler
app.use((req, res) => {
  res.status(404).json({ error: 'Route not found' })
})

// Error handling middleware (must be last)
app.use(errorHandler)

// Start server - listen on all network interfaces (0.0.0.0) to allow public access
const HOST = process.env.HOST || '0.0.0.0'
app.listen(PORT, HOST, () => {
  console.log(`Server running on http://localhost:${PORT}`)
  console.log(`Server accessible on local network`)
  console.log(`Server accessible on public IP (if port forwarded)`)
  console.log(`\n⚠️  SECURITY WARNING: Exposing to public internet requires proper security measures!`)
  console.log(`   - Add authentication/authorization`)
  console.log(`   - Enable HTTPS/SSL`)
  console.log(`   - Configure firewall rules`)
  console.log(`   - Use environment variables for secrets\n`)
})

// Graceful shutdown
process.on('SIGTERM', async () => {
  await prisma.$disconnect()
  process.exit(0)
})
