import axios from 'axios'

// Auto-detect API URL based on environment
// If VITE_API_URL is set, use it. Otherwise, detect if we're on mobile/network
const getApiUrl = () => {
  if (import.meta.env.VITE_API_URL) {
    return import.meta.env.VITE_API_URL
  }
  
  // If accessing from network IP (not localhost), use network IP for API
  const hostname = window.location.hostname
  if (hostname !== 'localhost' && hostname !== '127.0.0.1') {
    return `http://${hostname}:5000/api`
  }
  
  return 'http://localhost:5000/api'
}

const API_URL = getApiUrl()

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
  },
})

// Request interceptor for adding auth tokens (future)
api.interceptors.request.use(
  (config) => {
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

// Response interceptor for error handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Handle unauthorized access (future)
      localStorage.removeItem('token')
    }
    return Promise.reject(error)
  }
)

export default api
