import { X, CheckCircle, AlertCircle, Info, AlertTriangle } from 'lucide-react'
import { useState } from 'react'

export default function Alert({ type = 'info', message, onClose, className = '' }) {
  const [isVisible, setIsVisible] = useState(true)

  if (!isVisible) return null

  const handleClose = () => {
    setIsVisible(false)
    if (onClose) onClose()
  }

  const types = {
    success: {
      bg: 'bg-green-50',
      border: 'border-green-400',
      text: 'text-green-800',
      icon: CheckCircle,
    },
    error: {
      bg: 'bg-red-50',
      border: 'border-red-400',
      text: 'text-red-800',
      icon: AlertCircle,
    },
    warning: {
      bg: 'bg-yellow-50',
      border: 'border-yellow-400',
      text: 'text-yellow-800',
      icon: AlertTriangle,
    },
    info: {
      bg: 'bg-blue-50',
      border: 'border-blue-400',
      text: 'text-blue-800',
      icon: Info,
    },
  }

  const config = types[type]
  const Icon = config.icon

  return (
    <div
      className={`${config.bg} ${config.border} ${config.text} border-l-4 p-4 mb-4 rounded ${className}`}
    >
      <div className="flex items-center">
        <Icon className="w-5 h-5 mr-3" />
        <p className="flex-1">{message}</p>
        {onClose && (
          <button onClick={handleClose} className="ml-4">
            <X className="w-5 h-5" />
          </button>
        )}
      </div>
    </div>
  )
}
