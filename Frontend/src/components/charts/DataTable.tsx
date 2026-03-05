interface Column<T> {
  key: keyof T
  label: string
  render?: (value: any, row: T) => React.ReactNode
}

interface DataTableProps<T> {
  columns: Column<T>[]
  data: T[]
  emptyMessage?: string
}

// tabla de datos reutilizable y tipada con TypeScript
function DataTable<T extends object>({ columns, data, emptyMessage = 'Sin datos' }: DataTableProps<T>) {
  if (data.length === 0) {
    return (
      <div className="text-center text-gray-400 py-8 text-sm">{emptyMessage}</div>
    )
  }

  return (
    <div className="overflow-x-auto">
      <table className="w-full text-sm">
        <thead>
          <tr className="border-b border-gray-100">
            {columns.map((col) => (
              <th key={String(col.key)} className="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider pb-3 pr-4">
                {col.label}
              </th>
            ))}
          </tr>
        </thead>
        <tbody className="divide-y divide-gray-50">
          {data.map((row, i) => (
            <tr key={i} className="hover:bg-gray-50 transition-colors">
              {columns.map((col) => (
                <td key={String(col.key)} className="py-3 pr-4 text-gray-700">
                  {col.render ? col.render(row[col.key], row) : String(row[col.key] ?? '-')}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}

export default DataTable
