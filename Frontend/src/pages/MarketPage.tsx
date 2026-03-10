import { useEffect, useState } from 'react'
import DashboardLayout from '../components/DashboardLayout'
import FilterBar from '../components/FilterBar'
import type { FilterValues } from '../components/FilterBar'
import MarketBarChart from '../components/charts/MarketBarChart'
import DataTable from '../components/charts/DataTable'
import intelligenceService from '../services/intelligenceService'

const getCompanyId = () => {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  return user.company_id || 1
}

function MarketPage() {
  const [data, setData] = useState<any>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  const fetchData = (dateFrom = '', dateTo = '') => {
    setLoading(true)
    setError('')
    intelligenceService.getMarketIntelligence(getCompanyId(), dateFrom, dateTo)
      .then(setData)
      .catch(() => setError('No se pudieron cargar los datos de mercado'))
      .finally(() => setLoading(false))
  }

  useEffect(() => { fetchData() }, [])

  const handleFilter = ({ dateFrom, dateTo }: FilterValues) => {
    fetchData(dateFrom, dateTo)
  }

  const chartData = data?.market_analysis?.map((item: any) => {
    const row: any = { producto: item.product, 'Mi Precio': item.my_price }
    item.competitors.forEach((c: any) => { row[c.name] = c.price })
    return row
  }) || []

  const tableColumns = [
    { key: 'name' as const, label: 'Competidor' },
    { key: 'price' as const, label: 'Precio', render: (v: number) => `$${v}` },
  ]

  return (
    <DashboardLayout>
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Inteligencia de Mercado</h1>
        <p className="text-gray-500 text-sm mt-1">Comparativa de precios de tus productos vs. la competencia</p>
      </div>

      <FilterBar onFilter={handleFilter} loading={loading} />

      {loading && <div className="flex items-center justify-center h-64 text-gray-400">Cargando datos...</div>}
      {error && <div className="bg-red-50 text-red-600 p-4 rounded-xl">{error}</div>}

      {data && !loading && (
        <div className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {data.market_analysis?.slice(0, 3).map((item: any, i: number) => (
              <div key={i} className="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <p className="text-xs text-gray-400 uppercase tracking-wider">{item.product}</p>
                <p className="text-2xl font-bold text-gray-800 mt-1">${item.my_price}</p>
                <p className="text-sm text-indigo-600 mt-1 font-medium">Cuota: {item.market_share}</p>
              </div>
            ))}
          </div>

          <div className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h2 className="font-semibold text-gray-700 mb-4">Comparativa de Precios por Producto</h2>
            <MarketBarChart data={chartData} />
          </div>

          <div className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h2 className="font-semibold text-gray-700 mb-4">Tabla de Competidores</h2>
            {data.market_analysis?.[0]?.competitors && (
              <DataTable columns={tableColumns} data={data.market_analysis[0].competitors} emptyMessage="Sin competidores registrados" />
            )}
          </div>
        </div>
      )}
    </DashboardLayout>
  )
}

export default MarketPage
