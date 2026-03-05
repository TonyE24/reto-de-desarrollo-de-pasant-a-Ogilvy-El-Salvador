import { useEffect, useState } from 'react'
import DashboardLayout from '../components/DashboardLayout'
import PredictionChart from '../components/charts/PredictionChart'
import DataTable from '../components/charts/DataTable'
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

  const tableColumns = [
    { key: 'period' as const, label: 'Período' },
    { key: 'actual' as const, label: 'Ventas Reales', render: (v: any) => v != null ? `$${v}` : '—' },
    { key: 'predicted' as const, label: 'Predicción', render: (v: any) => v != null ? `$${v}` : '—' },
  ]

  return (
    <DashboardLayout>
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Inteligencia de Predicción</h1>
        <p className="text-gray-500 text-sm mt-1 flex items-center gap-2">
          Proyecciones de ventas basadas en regresión lineal
          {source === 'algorithm_prediction' && (
            <span className="text-xs bg-green-50 text-green-600 px-2 py-0.5 rounded-full font-medium">✓ Datos reales</span>
          )}
          {source === 'mock_data' && (
            <span className="text-xs bg-yellow-50 text-yellow-600 px-2 py-0.5 rounded-full font-medium">Datos simulados</span>
          )}
        </p>
      </div>

      {loading && <div className="flex items-center justify-center h-64 text-gray-400">Calculando predicciones...</div>}

      {!loading && (
        <div className="space-y-6">
          <div className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h2 className="font-semibold text-gray-700 mb-1">Historial vs. Predicciones</h2>
            <p className="text-xs text-gray-400 mb-5">Línea azul = datos reales · Línea naranja = proyectado</p>
            <PredictionChart data={predictions} />
          </div>

          <div className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h2 className="font-semibold text-gray-700 mb-4">Tabla de Valores</h2>
            <DataTable columns={tableColumns} data={predictions} emptyMessage="Sin datos históricos aún" />
          </div>
        </div>
      )}
    </DashboardLayout>
  )
}

export default PredictionsPage
