import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer
} from 'recharts'

interface MarketBarChartProps {
  data: { producto: string; 'Mi Precio': number; [key: string]: any }[]
  height?: number
}

// grafico de barras reutilizable para comparar precios de mercado
function MarketBarChart({ data, height = 320 }: MarketBarChartProps) {
  return (
    <ResponsiveContainer width="100%" height={height}>
      <BarChart data={data} margin={{ top: 5, right: 20, bottom: 5, left: 0 }}>
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
  )
}

export default MarketBarChart
