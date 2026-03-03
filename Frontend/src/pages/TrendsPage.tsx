import { useEffect, useState } from 'react'
import {
  AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer
} from 'recharts'
import DashboardLayout from '../components/DashboardLayout'
import intelligenceService from '../services/intelligenceService'

const getCompanyId = () => {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  return user.company_id || 1
}

function TrendsPage() {
  const [data, setData] = useState<any>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    intelligenceService.getTrendIntelligence(getCompanyId())
      .then(setData)
      .finally(() => setLoading(false))
  }, [])

  const trends = data?.trends || data?.trend_analysis || []

  // colores para los badges de sentimiento
  const sentimentColor = (score: number) => {
    if (score >= 60) return 'text-green-600 bg-green-50'
    if (score >= 40) return 'text-yellow-600 bg-yellow-50'
    return 'text-red-600 bg-red-50'
  }

  return (
    <DashboardLayout>
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Inteligencia de Tendencias</h1>
        <p className="text-gray-500 text-sm mt-1">Keywords que están sonando y análisis de sentimiento</p>
      </div>

      {loading && (
        <div className="flex items-center justify-center h-64 text-gray-400">Cargando tendencias...</div>
      )}

      {!loading && (
        <div className="space-y-6">
          {/* grafico de volumen de menciones */}
          <div className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h2 className="font-semibold text-gray-700 mb-4">Volumen de Menciones por Keyword</h2>
            <ResponsiveContainer width="100%" height={280}>
              <AreaChart data={trends} margin={{ top: 5, right: 20, bottom: 5, left: 0 }}>
                <defs>
                  <linearGradient id="colorVolumen" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="#6366f1" stopOpacity={0.3} />
                    <stop offset="95%" stopColor="#6366f1" stopOpacity={0} />
                  </linearGradient>
                </defs>
                <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                <XAxis dataKey="keyword" tick={{ fontSize: 11 }} />
                <YAxis tick={{ fontSize: 11 }} />
                <Tooltip />
                <Area type="monotone" dataKey="volume" stroke="#6366f1" fill="url(#colorVolumen)" strokeWidth={2} />
              </AreaChart>
            </ResponsiveContainer>
          </div>

          {/* tabla de sentimiento por keyword */}
          <div className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h2 className="font-semibold text-gray-700 mb-4">Análisis de Sentimiento</h2>
            <div className="space-y-3">
              {trends.map((t: any, i: number) => (
                <div key={i} className="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                  <div className="flex items-center gap-3">
                    <span className={`text-xs font-bold px-2 py-1 rounded-full ${t.trend === 'up' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-500'}`}>
                      {t.trend === 'up' ? '↑' : '↓'}
                    </span>
                    <span className="text-sm font-medium text-gray-700">{t.keyword}</span>
                  </div>
                  <div className="flex items-center gap-4 text-xs">
                    <span className={`px-2 py-1 rounded-full font-medium ${sentimentColor(t.sentiment?.positive)}`}>
                      😊 {t.sentiment?.positive}%
                    </span>
                    <span className="text-gray-400">{t.sentiment?.neutral}% neutro</span>
                    <span className="text-red-400">{t.sentiment?.negative}% neg.</span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}
    </DashboardLayout>
  )
}

export default TrendsPage
