/**
 * LoadingSpinner - Issue #35
 * Spinner reutilizable con diferentes tamaños y colores.
 * Úsalo dentro de botones, páginas o secciones que están cargando.
 */

interface LoadingSpinnerProps {
  size?: 'sm' | 'md' | 'lg'
  color?: 'indigo' | 'white' | 'gray'
  label?: string     // texto opcional debajo del spinner
  fullPage?: boolean // centra el spinner en toda la pantalla
}

const sizeClass = {
  sm: 'w-4 h-4 border-2',
  md: 'w-8 h-8 border-2',
  lg: 'w-14 h-14 border-4',
}

const colorClass = {
  indigo: 'border-indigo-200 border-t-indigo-600',
  white:  'border-white/30 border-t-white',
  gray:   'border-gray-200 border-t-gray-500',
}

function LoadingSpinner({
  size = 'md',
  color = 'indigo',
  label,
  fullPage = false,
}: LoadingSpinnerProps) {
  const spinner = (
    <div className="flex flex-col items-center gap-3">
      <div
        role="status"
        aria-label={label ?? 'Cargando...'}
        className={`rounded-full animate-spin ${sizeClass[size]} ${colorClass[color]}`}
      />
      {label && (
        <p className={`text-sm font-medium ${color === 'white' ? 'text-white/80' : 'text-gray-400'}`}>
          {label}
        </p>
      )}
    </div>
  )

  if (fullPage) {
    return (
      <div className="fixed inset-0 flex items-center justify-center bg-white/70 backdrop-blur-sm z-50">
        {spinner}
      </div>
    )
  }

  return (
    <div className="flex items-center justify-center py-12">
      {spinner}
    </div>
  )
}

export default LoadingSpinner
