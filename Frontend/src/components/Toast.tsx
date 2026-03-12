import { useEffect, useState, useCallback } from 'react'
import { createPortal } from 'react-dom'

export type ToastType = 'success' | 'error' | 'warning' | 'info'

export interface ToastMessage {
  id: string
  type: ToastType
  message: string
  duration?: number  // ms hasta que se auto-cierra, default 4000
}

// ---------------------------------------------------------------
// Store de toasts: usamos un patrón de suscriptores simple
// para poder llamar a showToast() desde cualquier parte del código
// sin necesidad de Context ni Redux
// ---------------------------------------------------------------
type Listener = (toast: ToastMessage) => void
const listeners: Listener[] = []

export const toast = {
  success: (message: string, duration?: number) => dispatch('success', message, duration),
  error:   (message: string, duration?: number) => dispatch('error',   message, duration),
  warning: (message: string, duration?: number) => dispatch('warning', message, duration),
  info:    (message: string, duration?: number) => dispatch('info',    message, duration),
}

function dispatch(type: ToastType, message: string, duration = 4000) {
  const toastMsg: ToastMessage = {
    id: `${Date.now()}-${Math.random()}`,
    type,
    message,
    duration,
  }
  listeners.forEach(fn => fn(toastMsg))
}

// ---------------------------------------------------------------
// Estilos por tipo de toast
// ---------------------------------------------------------------
const toastStyles: Record<ToastType, { bg: string; icon: string; iconEl: string }> = {
  success: {
    bg: 'bg-green-50 border-green-200 text-green-800',
    icon: 'text-green-500',
    iconEl: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
  },
  error: {
    bg: 'bg-red-50 border-red-200 text-red-800',
    icon: 'text-red-500',
    iconEl: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
  },
  warning: {
    bg: 'bg-yellow-50 border-yellow-200 text-yellow-800',
    icon: 'text-yellow-500',
    iconEl: 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z',
  },
  info: {
    bg: 'bg-indigo-50 border-indigo-200 text-indigo-800',
    icon: 'text-indigo-500',
    iconEl: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
  },
}

// ---------------------------------------------------------------
// Componente individual de toast
// ---------------------------------------------------------------
function ToastItem({ toast: t, onClose }: { toast: ToastMessage; onClose: () => void }) {
  const [visible, setVisible] = useState(false)
  const style = toastStyles[t.type]

  useEffect(() => {
    // entrada animada
    const show = setTimeout(() => setVisible(true), 10)
    // auto-cierre
    const hide = setTimeout(() => {
      setVisible(false)
      setTimeout(onClose, 300) // esperamos que termine la animacion de salida
    }, t.duration ?? 4000)

    return () => { clearTimeout(show); clearTimeout(hide) }
  }, [t.duration, onClose])

  return (
    <div
      role="alert"
      className={`
        flex items-start gap-3 px-4 py-3 rounded-xl border shadow-md text-sm font-medium
        transition-all duration-300 cursor-pointer max-w-sm w-full
        ${style.bg}
        ${visible ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-2'}
      `}
      onClick={() => { setVisible(false); setTimeout(onClose, 300) }}
    >
      <svg className={`w-5 h-5 mt-0.5 shrink-0 ${style.icon}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={style.iconEl} />
      </svg>
      <span className="flex-1">{t.message}</span>
      <svg className="w-4 h-4 opacity-40 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
      </svg>
    </div>
  )
}

// ---------------------------------------------------------------
// Contenedor global de toasts — montar UNA VEZ en App.tsx
// ---------------------------------------------------------------
function ToastContainer() {
  const [toasts, setToasts] = useState<ToastMessage[]>([])

  const addToast = useCallback((t: ToastMessage) => {
    setToasts(prev => [...prev, t])
  }, [])

  const removeToast = useCallback((id: string) => {
    setToasts(prev => prev.filter(t => t.id !== id))
  }, [])

  useEffect(() => {
    listeners.push(addToast)
    return () => {
      const idx = listeners.indexOf(addToast)
      if (idx >= 0) listeners.splice(idx, 1)
    }
  }, [addToast])

  // usamos portal para renderizar sobre todo el contenido
  return createPortal(
    <div
      aria-live="polite"
      className="fixed top-5 right-5 z-[9999] flex flex-col gap-2"
    >
      {toasts.map(t => (
        <ToastItem key={t.id} toast={t} onClose={() => removeToast(t.id)} />
      ))}
    </div>,
    document.body
  )
}

export default ToastContainer
