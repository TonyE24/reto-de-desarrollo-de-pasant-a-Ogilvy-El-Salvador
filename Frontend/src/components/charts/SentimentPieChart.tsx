import { PieChart, Pie, Cell, Tooltip, Legend, ResponsiveContainer } from 'recharts'

interface SentimentPieChartProps {
  positive: number
  neutral: number
  negative: number
  height?: number
}

const COLORS = ['#22c55e', '#94a3b8', '#ef4444']

// grafico de pastel reutilizable para analisis de sentimiento
function SentimentPieChart({ positive, neutral, negative, height = 260 }: SentimentPieChartProps) {
  const data = [
    { name: 'Positivo', value: positive },
    { name: 'Neutral', value: neutral },
    { name: 'Negativo', value: negative },
  ]

  return (
    <ResponsiveContainer width="100%" height={height}>
      <PieChart>
        <Pie
          data={data}
          cx="50%"
          cy="50%"
          innerRadius={60}
          outerRadius={95}
          paddingAngle={3}
          dataKey="value"
        >
          {data.map((_, index) => (
            <Cell key={`cell-${index}`} fill={COLORS[index]} />
          ))}
        </Pie>
        <Tooltip formatter={(v) => `${v}%`} />
        <Legend />
      </PieChart>
    </ResponsiveContainer>
  )
}

export default SentimentPieChart
