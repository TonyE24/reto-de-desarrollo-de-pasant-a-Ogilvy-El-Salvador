import { useEffect, useState } from 'react'
import DashboardLayout from '../components/DashboardLayout'
import TrendLineChart from '../components/charts/TrendLineChart'
import SentimentPieChart from '../components/charts/SentimentPieChart'
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

  // calculamos el sentimiento promedio de todos los keywords
  const avgSentiment = trends.length > 0 ? {
    positive: Math.round(trends.reduce((s: number, t: any) => s + t.sentiment?.positive, 0) / trends.length),
    neutral: Math.round(trends.reduce((s: number, t: any) => s + t.sentiment?.neutral, 0) / trends.length),
    negative: Math.round(trends.reduce((s: number, t: any) => s + t.sentiment?.negative, 0) / trends.length),
  } : { positive: 0, neutral: 0, negative: 0 }

  return (
    <DashboardLayout>
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Inteligencia de Tendencias</h1>
        <p className="text-gray-500 text-sm mt-1">Keywords que están sonando y análisis de sentimiento</p>
      </div>

      {loading && <div className="flex items-center justify-center h-64 text-gray-400">Cargando tendencias...</div>}

      {!loading && (
        <div className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
              <h2 className="font-semibold text-gray-700 mb-4">Volumen de Menciones</h2>
              <TrendLineChart data={trends} />
            </div>

            <div className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
              <h2 className="font-semibold text-gray-700 mb-2">Sentimiento General</h2>
              <p className="text-xs text-gray-400 mb-3">Promedio de todos los keywords</p>
              <SentimentPieChart
                positive={avgSentiment.positive}
                neutral={avgSentiment.neutral}
                negative={avgSentiment.negative}
              />
            </div>
          </div>

          <div className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h2 className="font-semibold text-gray-700 mb-4">Detalle por Keyword</h2>
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
                    <span className="text-green-600 font-medium">😊 {t.sentiment?.positive}%</span>
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
