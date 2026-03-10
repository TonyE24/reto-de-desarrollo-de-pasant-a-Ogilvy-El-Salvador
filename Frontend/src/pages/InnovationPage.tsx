import { useEffect, useState } from 'react'
import DashboardLayout from '../components/DashboardLayout'
import FilterBar from '../components/FilterBar'
import type { FilterValues } from '../components/FilterBar'
import intelligenceService from '../services/intelligenceService'

const getCompanyId = () => {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  return user.company_id || 1
}

// colores y estilos segun el impacto
const impactStyle: Record<string, string> = {
  'High': 'bg-red-50 text-red-600 border-red-100',
  'Alto': 'bg-red-50 text-red-600 border-red-100',
  'Medium': 'bg-yellow-50 text-yellow-600 border-yellow-100',
  'Medio': 'bg-yellow-50 text-yellow-600 border-yellow-100',
  'Low': 'bg-green-50 text-green-600 border-green-100',
  'Bajo': 'bg-green-50 text-green-600 border-green-100',
}

// icono segun el tipo de oportunidad
const typeIcon: Record<string, string> = {
  opportunity: '🎯',
  gap: '🕳️',
  technology: '🚀',
}

const typeLabel: Record<string, string> = {
  opportunity: 'Oportunidad',
  gap: 'Gap de Mercado',
  technology: 'Tecnología Emergente',
}

function InnovationPage() {
  const [data, setData] = useState<any>(null)
  const [loading, setLoading] = useState(true)

  const fetchData = (dateFrom = '', dateTo = '') => {
    setLoading(true)
    intelligenceService.getInnovationIntelligence(getCompanyId(), dateFrom, dateTo)
      .then(setData)
      .finally(() => setLoading(false))
  }

  useEffect(() => { fetchData() }, [])

  const handleFilter = ({ dateFrom, dateTo }: FilterValues) => {
    fetchData(dateFrom, dateTo)
  }

  const opportunities = data?.innovation_opportunities || []

  // agrupamos por tipo para mostrar cada seccion por separado
  const groups: Record<string, any[]> = {}
  opportunities.forEach((op: any) => {
    const key = op.type || 'opportunity'
    if (!groups[key]) groups[key] = []
    groups[key].push(op)
  })

  return (
    <DashboardLayout>
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Inteligencia de Innovación</h1>
        <p className="text-gray-500 text-sm mt-1">Oportunidades detectadas, gaps de mercado y tecnologías emergentes</p>
      </div>

      <FilterBar onFilter={handleFilter} loading={loading} />

      {loading && (
        <div className="flex items-center justify-center h-64 text-gray-400">Detectando oportunidades...</div>
      )}

      {!loading && opportunities.length === 0 && (
        <div className="bg-yellow-50 text-yellow-700 p-5 rounded-xl">
          Sin oportunidades detectadas aún. A medida que el sistema aprenda sobre tu empresa, aparecerán aquí.
        </div>
      )}

      {!loading && (
        <div className="space-y-6">
          {/* si no tiene tipos definidos (mock data) los mostramos como grid flat */}
          {Object.keys(groups).length === 0 && opportunities.length > 0
            ? (
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {opportunities.map((op: any, i: number) => (
                  <OpportunityCard key={i} op={op} />
                ))}
              </div>
            )
            : Object.entries(groups).map(([type, items]) => (
              <div key={type}>
                <h2 className="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                  {typeIcon[type] || '✨'} {typeLabel[type] || type}
                </h2>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {items.map((op, i) => (
                    <OpportunityCard key={i} op={op} />
                  ))}
                </div>
              </div>
            ))
          }
        </div>
      )}
    </DashboardLayout>
  )
}

// componente de tarjeta reutilizable
function OpportunityCard({ op }: { op: any }) {
  const impact = op.impact || 'Medium'
  return (
    <div className="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
      <div className="flex items-start justify-between gap-3 mb-2">
        <h3 className="font-semibold text-gray-800 text-sm leading-snug">{op.title}</h3>
        <span className={`text-xs font-bold px-2 py-1 rounded-full border shrink-0 ${impactStyle[impact] || impactStyle['Medium']}`}>
          {impact}
        </span>
      </div>
      <p className="text-sm text-gray-500 leading-relaxed">{op.description}</p>
    </div>
  )
}

export default InnovationPage
