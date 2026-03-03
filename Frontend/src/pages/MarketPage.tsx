import { useEffect, useState } from 'react'
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer
} from 'recharts'
import DashboardLayout from '../components/DashboardLayout'
import intelligenceService from '../services/intelligenceService'

// obtenemos el company_id del user guardado en localStorage
const getCompanyId = () => {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  return user.company_id || 1 // usamos 1 como fallback para pruebas
}

function MarketPage() {
  const [data, setData] = useState<any>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    intelligenceService.getMarketIntelligence(getCompanyId())
      .then(setData)
      .catch(() => setError('No se pudieron cargar los datos de mercado'))
      .finally(() => setLoading(false))
  }, [])

  // convertimos los datos de la API al formato que requiere Recharts
  const chartData = data?.market_analysis?.map((item: any) => {
    const row: any = { producto: item.product, 'Mi Precio': item.my_price }
    item.competitors.forEach((c: any) => {
      row[c.name] = c.price
    })
    return row
  }) || []

  return (
    <DashboardLayout>
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Inteligencia de Mercado</h1>
        <p className="text-gray-500 text-sm mt-1">Comparativa de precios de tus productos vs. la competencia</p>
      </div>

      {loading && (
        <div className="flex items-center justify-center h-64 text-gray-400">Cargando datos...</div>
      )}
      {error && (
        <div className="bg-red-50 text-red-600 p-4 rounded-xl">{error}</div>
      )}

      {data && (
        <div className="space-y-6">
          {/* KPI: cuota de mercado */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {data.market_analysis?.slice(0, 3).map((item: any, i: number) => (
              <div key={i} className="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <p className="text-xs text-gray-400 uppercase tracking-wider">{item.product}</p>
                <p className="text-2xl font-bold text-gray-800 mt-1">${item.my_price}</p>
                <p className="text-sm text-indigo-600 mt-1 font-medium">Cuota: {item.market_share}</p>
              </div>
            ))}
          </div>

          {/* grafico de barras */}
          <div className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h2 className="font-semibold text-gray-700 mb-4">Comparativa de Precios por Producto</h2>
            <ResponsiveContainer width="100%" height={320}>
              <BarChart data={chartData} margin={{ top: 5, right: 20, bottom: 5, left: 0 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                <XAxis dataKey="producto" tick={{ fontSize: 12 }} />
                <YAxis tick={{ fontSize: 12 }} />
                <Tooltip />
                <Legend />
                <Bar dataKey="Mi Precio" fill="#6366f1" radius={[4, 4, 0, 0]} />
                <Bar dataKey="Competidor Alpha" fill="#e879f9" radius={[4, 4, 0, 0]} />
                <Bar dataKey="Competidor Beta" fill="#fb923c" radius={[4, 4, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
      )}
    </DashboardLayout>
  )
}

export default MarketPage
