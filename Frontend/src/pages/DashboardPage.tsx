import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import DashboardLayout from '../components/DashboardLayout'
import api from '../services/api'

const getCompanyId = () => {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  return user.company_id || 1
}

// colores y estilos para cada KPI
const kpiConfig: Record<string, { icon: string; color: string; bg: string }> = {
  market_share:         { icon: '📊', color: 'text-indigo-600', bg: 'bg-indigo-50' },
  sentiment:            { icon: '💬', color: 'text-purple-600', bg: 'bg-purple-50' },
  next_prediction:      { icon: '🔮', color: 'text-orange-600', bg: 'bg-orange-50' },
  active_opportunities: { icon: '💡', color: 'text-green-600', bg: 'bg-green-50' },
}

const modules = [
  { to: '/dashboard/market',      icon: '📊', title: 'Mercado',      description: 'Precios y cuota de mercado vs. competidores.',      color: 'bg-indigo-50' },
  { to: '/dashboard/trends',      icon: '📈', title: 'Tendencias',   description: 'Keywords y sentimiento de clientes.',                color: 'bg-purple-50' },
  { to: '/dashboard/predictions', icon: '🔮', title: 'Predicciones', description: 'Proyecciones de ventas con regresión lineal.',       color: 'bg-orange-50' },
  { to: '/dashboard/innovation',  icon: '💡', title: 'Innovación',   description: 'Oportunidades, gaps y tecnologías emergentes.',      color: 'bg-green-50' },
]

function DashboardPage() {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  const [kpiData, setKpiData] = useState<any>(null)
  const [loading, setLoading] = useState(true)
  const [dateFrom, setDateFrom] = useState('')
  const [dateTo, setDateTo] = useState('')

  const fetchKpis = (from = '', to = '') => {
    setLoading(true)
    const params = new URLSearchParams({ company_id: String(getCompanyId()) })
    if (from) params.append('date_from', from)
    if (to) params.append('date_to', to)

    api.get(`/dashboard?${params.toString()}`)
      .then(res => setKpiData(res.data))
      .catch(() => setKpiData(null))
      .finally(() => setLoading(false))
  }

  useEffect(() => { fetchKpis() }, [])

  const handleFilter = (e: React.FormEvent) => {
    e.preventDefault()
    fetchKpis(dateFrom, dateTo)
  }

  const kpis = kpiData?.kpis ? Object.entries(kpiData.kpis) : []

  return (
    <DashboardLayout>
      {/* saludo */}
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-800">
          ¡Bienvenido, {user.name?.split(' ')[0] || 'Usuario'}! 👋
        </h1>
        <p className="text-gray-500 mt-1 text-sm">Resumen de inteligencia para tu empresa.</p>
      </div>

      {/* filtros de fecha */}
      <form onSubmit={handleFilter} className="flex flex-wrap gap-3 items-end mb-6 bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
        <div>
          <label className="block text-xs font-medium text-gray-500 mb-1">Desde</label>
          <input
            type="date"
            value={dateFrom}
            onChange={e => setDateFrom(e.target.value)}
            className="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400"
          />
        </div>
        <div>
          <label className="block text-xs font-medium text-gray-500 mb-1">Hasta</label>
          <input
            type="date"
            value={dateTo}
            onChange={e => setDateTo(e.target.value)}
            className="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400"
          />
        </div>
        <button
          type="submit"
          className="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition"
        >
          Filtrar
        </button>
        <button
          type="button"
          onClick={() => { setDateFrom(''); setDateTo(''); fetchKpis() }}
          className="text-gray-400 hover:text-gray-600 text-sm transition"
        >
          Limpiar
        </button>
      </form>

      {/* grid de KPIs */}
      {loading ? (
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          {[...Array(4)].map((_, i) => (
            <div key={i} className="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 animate-pulse h-28" />
          ))}
        </div>
      ) : (
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          {kpis.map(([key, kpi]: [string, any]) => {
            const cfg = kpiConfig[key] || { icon: '📌', color: 'text-gray-600', bg: 'bg-gray-50' }
            return (
              <div key={key} className="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div className={`w-10 h-10 ${cfg.bg} rounded-xl flex items-center justify-center text-xl mb-3`}>
                  {cfg.icon}
                </div>
                <p className={`text-2xl font-bold ${cfg.color}`}>{kpi.value}</p>
                <p className="text-xs text-gray-400 mt-1 leading-tight">{kpi.label}</p>
              </div>
            )
          })}
        </div>
      )}

      {/* acceso rapido a modulos */}
      <h2 className="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Módulos de Inteligencia</h2>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        {modules.map((m) => (
          <Link
            key={m.to}
            to={m.to}
            className="group bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md hover:-translate-y-0.5 transition-all flex items-start gap-4"
          >
            <div className={`w-11 h-11 ${m.color} rounded-xl flex items-center justify-center text-2xl shrink-0`}>
              {m.icon}
            </div>
            <div>
              <h3 className="font-semibold text-gray-800 group-hover:text-indigo-600 transition-colors text-sm">{m.title}</h3>
              <p className="text-xs text-gray-500 mt-0.5 leading-relaxed">{m.description}</p>
            </div>
          </Link>
        ))}
      </div>

      {/* banner de configuracion */}
      <div className="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 flex items-center justify-between">
        <div>
          <p className="font-semibold text-indigo-800 text-sm">¿Recién empezando?</p>
          <p className="text-indigo-600 text-xs mt-0.5">Configura tu empresa para recibir inteligencia personalizada.</p>
        </div>
        <Link to="/setup-company" className="shrink-0 bg-indigo-600 text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-indigo-700 transition">
          Configurar →
        </Link>
      </div>
    </DashboardLayout>
  )
}

export default DashboardPage
