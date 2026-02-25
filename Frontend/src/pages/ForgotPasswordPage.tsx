import { useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../services/api'

function ForgotPasswordPage() {
  const [email, setEmail] = useState('')
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    setMessage('')
    setLoading(true)

    try {
      // mandamos el email al backend para que envie el link
      const res = await api.post('/auth/forgot-password', { email })
      // si funciono le decimos al user que revise su email
      setMessage(res.data.message)
    } catch (err: any) {
      setError(err.response?.data?.message || 'Algo salio mal')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center p-4">
      <div className="bg-blue-200 rounded-2xl shadow-xl/30 p-8 w-full max-w-md">

        <h1 className="text-2xl font-bold text-gray-800 mb-2 text-center">Recuperar contraseña</h1>
        <p className="text-gray-800 text-lg text-center mb-6">
          Te enviaremos un link a tu email para que puedas restablecer tu contraseña
        </p>

        {message && (
          <div className="bg-green-50 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">
            {message}
          </div>
        )}

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
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="tu@email.com"
              required
              className="w-full border bg-mist-100 border-gray-200 rounded-lg px-4 py-2.5 text-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-mist-800 hover:bg-mist-500 text-white font-medium py-2.5 rounded-lg text-lg transition disabled:opacity-50"
          >
            {loading ? 'Enviando...' : 'Enviar link'}
          </button>
        </form>

        <p className="text-center text-sm text-gray-500 mt-6">
          <Link to="/login" className="text-rose-500 hover:underline font-bold text-lg">
            ← Volver al login
          </Link>
        </p>
      </div>
    </div>
  )
}

export default ForgotPasswordPage
