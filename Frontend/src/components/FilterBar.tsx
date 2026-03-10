import { useState } from 'react'

export interface FilterValues {
  dateFrom: string
  dateTo: string
}

interface FilterBarProps {
  onFilter: (filters: FilterValues) => void
  loading?: boolean
}

/**
 * Barra de filtros reutilizable para todos los módulos del dashboard.
 * Issue #27: Filtros Globales de Dashboard
 */
function FilterBar({ onFilter, loading = false }: FilterBarProps) {
  const [dateFrom, setDateFrom] = useState('')
  const [dateTo, setDateTo] = useState('')

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    onFilter({ dateFrom, dateTo })
  }

  const handleClear = () => {
    setDateFrom('')
    setDateTo('')
    onFilter({ dateFrom: '', dateTo: '' })
  }

  return (
    <form
      onSubmit={handleSubmit}
      className="flex flex-wrap gap-3 items-end bg-white border border-gray-100 rounded-2xl px-5 py-4 shadow-sm mb-6"
    >
      {/* icono de filtro */}
      <div className="flex items-center gap-2 mr-1 text-gray-400 self-center">
        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
        </svg>
        <span className="text-xs font-semibold text-gray-500 uppercase tracking-wide">Filtros</span>
      </div>

      {/* desde */}
      <div className="flex flex-col gap-1">
        <label className="text-xs font-medium text-gray-400">Desde</label>
        <input
          id="filter-date-from"
          type="date"
          value={dateFrom}
          onChange={e => setDateFrom(e.target.value)}
          className="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-indigo-400 transition"
        />
      </div>

      {/* hasta */}
      <div className="flex flex-col gap-1">
        <label className="text-xs font-medium text-gray-400">Hasta</label>
        <input
          id="filter-date-to"
          type="date"
          value={dateTo}
          onChange={e => setDateTo(e.target.value)}
          className="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-indigo-400 transition"
        />
      </div>

      {/* botones */}
      <div className="flex gap-2 self-end">
        <button
          id="filter-apply-btn"
          type="submit"
          disabled={loading}
          className="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-1.5"
        >
          {loading ? (
            <>
              <svg className="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
              </svg>
              Cargando
            </>
          ) : (
            'Filtrar'
          )}
        </button>

        {(dateFrom || dateTo) && (
          <button
            id="filter-clear-btn"
            type="button"
            onClick={handleClear}
            className="text-gray-400 hover:text-gray-600 text-sm transition px-2 py-2 rounded-lg hover:bg-gray-50 flex items-center gap-1"
          >
            <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
            Limpiar
          </button>
        )}
      </div>

      {/* indicador de filtro activo */}
      {(dateFrom || dateTo) && (
        <div className="self-center ml-auto">
          <span className="bg-indigo-50 text-indigo-600 text-xs font-medium px-2.5 py-1 rounded-full border border-indigo-100">
            Filtro activo
          </span>
        </div>
      )}
    </form>
  )
}

export default FilterBar
