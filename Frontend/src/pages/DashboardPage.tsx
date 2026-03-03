import DashboardLayout from '../components/DashboardLayout'
import { Link } from 'react-router-dom'

// tarjeta rapida para los modulos de inteligencia
const ModuleCard = ({ to, icon, title, description, color }: {
  to: string, icon: string, title: string, description: string, color: string
}) => (
  <Link to={to} className="group block bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md hover:-translate-y-0.5 transition-all">
    <div className={`w-12 h-12 ${color} rounded-xl flex items-center justify-center text-2xl mb-4`}>
      {icon}
    </div>
    <h3 className="font-semibold text-gray-800 mb-1 group-hover:text-indigo-600 transition-colors">{title}</h3>
    <p className="text-sm text-gray-500 leading-relaxed">{description}</p>
  </Link>
)

function DashboardPage() {
  const user = JSON.parse(localStorage.getItem('user') || '{}')

  const modules = [
    {
      to: '/dashboard/market',
      icon: '📊',
      title: 'Inteligencia de Mercado',
      description: 'Compara tus precios con la competencia y monitorea tu cuota de mercado.',
      color: 'bg-indigo-50',
    },
    {
      to: '/dashboard/trends',
      icon: '📈',
      title: 'Inteligencia de Tendencias',
      description: 'Analiza keywords que están sonando y el sentimiento de tus clientes.',
      color: 'bg-purple-50',
    },
    {
      to: '/dashboard/predictions',
      icon: '🔮',
      title: 'Inteligencia de Predicción',
      description: 'Proyecta tus ventas futuras con nuestro algoritmo de regresión lineal.',
      color: 'bg-orange-50',
    },
    {
      to: '/dashboard/innovation',
      icon: '💡',
      title: 'Inteligencia de Innovación',
      description: 'Detecta oportunidades de negocio, gaps de mercado y tecnologías emergentes.',
      color: 'bg-green-50',
    },
  ]

  return (
    <DashboardLayout>
      {/* saludo del usuario */}
      <div className="mb-8">
        <h1 className="text-2xl font-bold text-gray-800">
          ¡Bienvenido, {user.name?.split(' ')[0] || 'Usuario'}! 👋
        </h1>
        <p className="text-gray-500 mt-1">
          Aquí tienes un resumen de la inteligencia disponible para tu empresa.
        </p>
      </div>

      {/* grid de los 4 modulos */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
        {modules.map((m) => (
          <ModuleCard key={m.to} {...m} />
        ))}
      </div>

      {/* banner de configuracion si la empresa no esta lista */}
      <div className="mt-6 bg-indigo-50 border border-indigo-100 rounded-2xl p-5 flex items-center justify-between">
        <div>
          <p className="font-semibold text-indigo-800 text-sm">¿Recién empezando?</p>
          <p className="text-indigo-600 text-sm mt-0.5">Configura tu empresa para recibir inteligencia personalizada.</p>
        </div>
        <Link
          to="/setup-company"
          className="shrink-0 bg-indigo-600 text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-indigo-700 transition"
        >
          Configurar →
        </Link>
      </div>
    </DashboardLayout>
  )
}

export default DashboardPage
