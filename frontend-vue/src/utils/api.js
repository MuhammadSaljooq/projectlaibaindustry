import axios from 'axios'

// Auto-detect API URL based on environment
const getApiUrl = () => {
  // Use explicit environment variable if set
  if (import.meta.env.VITE_API_URL) {
    return import.meta.env.VITE_API_URL
  }
  
  // Auto-detect from current domain (works for free hosting)
  if (typeof window !== 'undefined') {
    const origin = window.location.origin
    
    // For production/free hosting, use relative API path
    if (origin !== 'http://localhost:5173' && origin !== 'http://127.0.0.1:5173') {
      return `${origin}/api`
    }
    
    // For local development with separate backend
    const hostname = window.location.hostname
    if (hostname !== 'localhost' && hostname !== '127.0.0.1') {
      return `http://${hostname}:8000/api`
    }
  }
  
  // Default to localhost for development
  return 'http://localhost:8000/api'
}

const api = axios.create({
  baseURL: getApiUrl(),
  headers: {
    'Content-Type': 'application/json',
  },
})

// Request interceptor
api.interceptors.request.use(
  (config) => {
    // Add auth token if available
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor: unwrap PHP backend { data } so both Node and PHP backends work
api.interceptors.response.use(
  (response) => {
    const body = response.data
    if (body && typeof body === 'object' && !Array.isArray(body) && 'data' in body && !('error' in body)) {
      response.data = body.data
    }
    return response
  },
  (error) => {
    if (error.response?.status === 401) {
      // Handle unauthorized
      localStorage.removeItem('token')
      // Redirect to login if needed
    }
    return Promise.reject(error)
  }
)

export default api
