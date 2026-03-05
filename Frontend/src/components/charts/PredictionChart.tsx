import {
  LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer
} from 'recharts'

interface PredictionDataPoint {
  period: string
  actual?: number | null
  predicted?: number | null
}

interface PredictionChartProps {
  data: PredictionDataPoint[]
  height?: number
}

// grafico de lineas reutilizable para predicciones vs datos historicos
function PredictionChart({ data, height = 350 }: PredictionChartProps) {
  return (
    <ResponsiveContainer width="100%" height={height}>
      <LineChart data={data} margin={{ top: 5, right: 30, bottom: 5, left: 0 }}>
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
  )
}

export default PredictionChart
