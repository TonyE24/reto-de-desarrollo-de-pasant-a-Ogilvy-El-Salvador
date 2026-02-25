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

        <div className="bg-blue-100 rounded-2xl shadow-lg shadow-blue-950 p-8">
          <div className="flex items-center justify-between mb-6">
            <h1 className="text-2xl font-bold text-gray-800">Dashboard</h1>
            <button
              onClick={handleLogout}
              className="text-lg text-red-500 hover:text-red-700 font-medium transition"
            >
              Cerrar sesión
            </button>
          </div>

          <div className="bg-blue-50 rounded-xl p-5 shadow-md shadow-blue-300">
            <p className="text-center text-xl text-pink-600 font-medium mb-1">Bienvenido!</p>
            <p className="text-center text-xl font-bold text-gray-800">{user.name || 'Usuario'}</p>
            <p className="text-center text-black text-lg mt-1">{user.email}</p>
            <span className="mt-3 inline-block bg-fuchsia-400 text-purple-700 text-2xl font-medium px-3 py-1 rounded-full">
              {user.role}
            </span>
          </div>

          <p className="text-black text-md text-center mt-8 font-medium">
            Pruba de Inicio de Sesion Exitosa!!
          </p>
        </div>
      </div>
    </div>
  )
}

export default DashboardPage
