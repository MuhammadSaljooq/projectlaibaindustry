import { createContext, useContext, useReducer } from 'react'

const AppContext = createContext()

const initialState = {
  products: [],
  sales: [],
  categories: [],
  taxSettings: {},
  loading: false,
  error: null,
}

function appReducer(state, action) {
  switch (action.type) {
    case 'SET_LOADING':
      return { ...state, loading: action.payload }
    case 'SET_ERROR':
      return { ...state, error: action.payload, loading: false }
    case 'SET_PRODUCTS':
      return { ...state, products: action.payload, loading: false }
    case 'SET_SALES':
      return { ...state, sales: action.payload, loading: false }
    case 'SET_CATEGORIES':
      return { ...state, categories: action.payload, loading: false }
    case 'SET_TAX_SETTINGS':
      return { ...state, taxSettings: action.payload, loading: false }
    default:
      return state
  }
}

export function AppProvider({ children }) {
  const [state, dispatch] = useReducer(appReducer, initialState)

  return (
    <AppContext.Provider value={{ state, dispatch }}>
      {children}
    </AppContext.Provider>
  )
}

export function useApp() {
  const context = useContext(AppContext)
  if (!context) {
    throw new Error('useApp must be used within AppProvider')
  }
  return context
}
