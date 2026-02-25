import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import api from '../services/api'

function LoginPage() {
  const navigate = useNavigate()

  // guardo los valores del formulario en el estado
  const [form, setForm] = useState({ email: '', password: '' })
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  // cuando el user escribe actualizamos el estado
  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setForm({ ...form, [e.target.name]: e.target.value })
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    setLoading(true)

    try {
      // mandamos las credenciales al backend
      const res = await api.post('/auth/login', form)

      // guardamos el token en localStorage para usarlo en peticiones futuras
      localStorage.setItem('token', res.data.token)
      localStorage.setItem('user', JSON.stringify(res.data.user))

      // mandamos al dashboard
      navigate('/dashboard')
    } catch (err: any) {
      // si las credenciales son malas mostramos el error
      setError(err.response?.data?.message || 'Algo salio mal, intenta de nuevo')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-olive-50 flex items-center justify-center p-4">
      <div className="bg-blue-200 rounded-2xl shadow-xl/30 p-8 w-full max-w-md">

        <h1 className="text-2xl font-bold text-gray-800 mb-2 text-center">Iniciar sesión</h1>
        <p className="text-gray-500 text-lg mb-6 text-center">Ingresa tus credenciales para continuar</p>

        {error && (
          <div className="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-lg mb-4">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-xl font-medium text-gray-700 mb-1">Email</label>
            <input
              type="email"
              name="email"
              value={form.email}
              onChange={handleChange}
              placeholder="tu@email.com"
              required
              className="w-full border bg-mist-100 border-gray-200 rounded-lg px-4 py-2.5 text-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label className="block text-xl font-medium text-gray-700 mb-1">Contraseña</label>
            <input
              type="password"
              name="password"
              value={form.password}
              onChange={handleChange}
              placeholder="••••••••"
              required
              className="w-full border bg-mist-100 border-gray-200 rounded-lg px-4 py-2.5 text-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div className="text-right">
            <Link to="/forgot-password" className="text-lg text-rose-500 hover:underline font-bold">
              ¿Olvidaste tu contraseña?
            </Link>
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-mist-800 hover:bg-mist-500 text-white py-2.5 rounded-lg text-lg transition disabled:opacity-50 font-semibold"
          >
            {loading ? 'Ingresando...' : 'Iniciar sesión'}
          </button>
        </form>

        <p className="text-center text-lg text-gray-800 mt-6">
          ¿No tienes cuenta?{' '}
          <Link to="/register" className="text-lg text-rose-500 hover:underline font-bold">
            Regístrate
          </Link>
        </p>
      </div>
    </div>
  )
}

export default LoginPage
