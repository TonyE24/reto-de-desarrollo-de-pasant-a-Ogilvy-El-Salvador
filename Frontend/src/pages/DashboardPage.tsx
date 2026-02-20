import { useNavigate } from 'react-router-dom'
import api from '../services/api'

function DashboardPage() {
  const navigate = useNavigate()

  // obtengo los datos del usuario del localStorage
  const user = JSON.parse(localStorage.getItem('user') || '{}')

  const handleLogout = async () => {
    try {
      // le aviso al backend que cierre la sesion (borra el token en la BD)
      await api.post('/auth/logout')
    } catch {
      // si falla la peticion igual borro el token del localStorage
    } finally {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      navigate('/login')
    }
  }

  return (
    <div className="min-h-screen bg-gray-50 p-8">
      <div className="max-w-2xl mx-auto">

        <div className="bg-white rounded-2xl shadow-lg p-8">
          <div className="flex items-center justify-between mb-6">
            <h1 className="text-2xl font-bold text-gray-800">Dashboard</h1>
            <button
              onClick={handleLogout}
              className="text-sm text-red-500 hover:text-red-700 font-medium transition"
            >
              Cerrar sesión
            </button>
          </div>

          <div className="bg-blue-50 rounded-xl p-5">
            <p className="text-sm text-blue-600 font-medium mb-1">Bienvenido de vuelta</p>
            <p className="text-xl font-bold text-gray-800">{user.name || 'Usuario'}</p>
            <p className="text-gray-500 text-sm mt-1">{user.email}</p>
            <span className="mt-3 inline-block bg-blue-100 text-blue-700 text-xs font-medium px-3 py-1 rounded-full">
              {user.role}
            </span>
          </div>

          <p className="text-gray-400 text-sm text-center mt-8">
            ✅ Autenticación funcionando correctamente
          </p>
        </div>
      </div>
    </div>
  )
}

export default DashboardPage
