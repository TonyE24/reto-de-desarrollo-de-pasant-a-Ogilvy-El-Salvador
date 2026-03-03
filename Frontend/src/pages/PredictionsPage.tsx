import { useEffect, useState } from 'react'
import {
  LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer
} from 'recharts'
import DashboardLayout from '../components/DashboardLayout'
import intelligenceService from '../services/intelligenceService'

const getCompanyId = () => {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  return user.company_id || 1
}

function PredictionsPage() {
  const [data, setData] = useState<any>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    intelligenceService.getPredictionIntelligence(getCompanyId())
      .then(setData)
      .finally(() => setLoading(false))
  }, [])

  const predictions = data?.predictions || []
  const source = data?.source

  return (
    <DashboardLayout>
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Inteligencia de Predicción</h1>
        <p className="text-gray-500 text-sm mt-1">
          Proyecciones de ventas basadas en regresión lineal
          {source === 'algorithm_prediction' && (
            <span className="ml-2 text-xs bg-green-50 text-green-600 px-2 py-0.5 rounded-full font-medium">
              ✓ Datos reales
            </span>
          )}
          {source === 'mock_data' && (
            <span className="ml-2 text-xs bg-yellow-50 text-yellow-600 px-2 py-0.5 rounded-full font-medium">
              Datos simulados
            </span>
          )}
        </p>
      </div>

      {loading && (
        <div className="flex items-center justify-center h-64 text-gray-400">Calculando predicciones...</div>
      )}

      {!loading && (
        <div className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
          <h2 className="font-semibold text-gray-700 mb-1">Historial vs. Predicciones</h2>
          <p className="text-xs text-gray-400 mb-5">Línea azul = datos reales · Línea naranja = proyectado por el algoritmo</p>
          <ResponsiveContainer width="100%" height={350}>
            <LineChart data={predictions} margin={{ top: 5, right: 30, bottom: 5, left: 0 }}>
              <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
              <XAxis dataKey="period" tick={{ fontSize: 12 }} />
              <YAxis tick={{ fontSize: 12 }} />
              <Tooltip />
              <Legend />
              <Line
                type="monotone"
                dataKey="actual"
                stroke="#6366f1"
                strokeWidth={2.5}
                dot={{ r: 5, fill: '#6366f1' }}
                name="Ventas Reales"
                connectNulls={false}
              />
              <Line
                type="monotone"
                dataKey="predicted"
                stroke="#f97316"
                strokeWidth={2.5}
                strokeDasharray="6 3"
                dot={{ r: 5, fill: '#f97316' }}
                name="Predicción"
                connectNulls={false}
              />
            </LineChart>
          </ResponsiveContainer>
        </div>
      )}
    </DashboardLayout>
  )
}

export default PredictionsPage
