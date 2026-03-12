import { Component, type ReactNode } from 'react'

interface ErrorBoundaryProps {
  children: ReactNode
  fallback?: ReactNode  // UI personalizada si no se pasa, usa el default
}

interface ErrorBoundaryState {
  hasError: boolean
  error: Error | null
}

/**
 * ErrorBoundary - Issue #35
 * Captura errores de rendering de React y muestra una UI de recuperación
 * en lugar de romper toda la app. Úsalo alrededor de secciones críticas.
 *
 * Uso: <ErrorBoundary><ComponentePeligroso /></ErrorBoundary>
 */
class ErrorBoundary extends Component<ErrorBoundaryProps, ErrorBoundaryState> {
  constructor(props: ErrorBoundaryProps) {
    super(props)
    this.state = { hasError: false, error: null }
  }

  static getDerivedStateFromError(error: Error): ErrorBoundaryState {
    return { hasError: true, error }
  }

  componentDidCatch(error: Error, info: React.ErrorInfo) {
    // en produccion aquí podríamos enviar el error a un servicio como Sentry
    console.error('[ErrorBoundary] Error capturado:', error, info)
  }

  handleReset = () => {
    this.setState({ hasError: false, error: null })
  }

  render() {
    if (this.state.hasError) {
      // si el padre pasa un fallback personalizado lo usamos
      if (this.props.fallback) {
        return this.props.fallback
      }

      // UI de error por defecto
      return (
        <div className="flex flex-col items-center justify-center min-h-[300px] text-center px-6">
          <div className="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mb-4">
            <svg className="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5}
                d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
            </svg>
          </div>
          <h2 className="text-lg font-semibold text-gray-700 mb-1">Algo salió mal</h2>
          <p className="text-sm text-gray-400 mb-5 max-w-xs">
            Ocurrió un error inesperado en esta sección. Puedes intentar recargarla.
          </p>
          <button
            onClick={this.handleReset}
            className="bg-indigo-600 text-white text-sm font-semibold px-5 py-2 rounded-lg hover:bg-indigo-700 transition"
          >
            Intentar de nuevo
          </button>
        </div>
      )
    }

    return this.props.children
  }
}

export default ErrorBoundary
