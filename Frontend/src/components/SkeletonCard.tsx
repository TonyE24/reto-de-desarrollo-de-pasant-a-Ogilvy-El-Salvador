/**
 * SkeletonCard - Issue #35
 * Skeleton screens para mostrar mientras los datos cargan.
 * Simula la forma del contenido real para evitar el layout shift.
 */

interface SkeletonCardProps {
  lines?: number   // cuantas lineas de texto simular
  showChart?: boolean // mostrar un bloque de gráfico simulado
  className?: string
}

// bloque animado reutilizable
function SkeletonBlock({ className = '' }: { className?: string }) {
  return (
    <div className={`bg-gray-200 rounded animate-pulse ${className}`} />
  )
}

function SkeletonCard({ lines = 3, showChart = false, className = '' }: SkeletonCardProps) {
  return (
    <div className={`bg-white rounded-2xl p-6 shadow-sm border border-gray-100 ${className}`}>
      {/* header simulado */}
      <div className="flex items-center justify-between mb-4">
        <SkeletonBlock className="h-4 w-32" />
        <SkeletonBlock className="h-4 w-16 rounded-full" />
      </div>

      {/* grafico simulado */}
      {showChart && (
        <SkeletonBlock className="h-40 w-full mb-4 rounded-xl" />
      )}

      {/* lineas de texto simuladas */}
      <div className="space-y-2.5">
        {Array.from({ length: lines }).map((_, i) => (
          <SkeletonBlock
            key={i}
            // la ultima linea es mas corta para que parezca un parrafo real
            className={`h-3 ${i === lines - 1 ? 'w-2/3' : 'w-full'}`}
          />
        ))}
      </div>
    </div>
  )
}

// version grid de skeletons para las páginas del dashboard
export function SkeletonGrid({ count = 3 }: { count?: number }) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      {Array.from({ length: count }).map((_, i) => (
        <SkeletonCard key={i} lines={2} showChart />
      ))}
    </div>
  )
}

export default SkeletonCard
