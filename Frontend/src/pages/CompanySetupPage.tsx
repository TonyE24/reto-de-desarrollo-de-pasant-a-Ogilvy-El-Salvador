import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import axios from 'axios'
import api from '../services/api'

function CompanySetupPage() {
  const navigate = useNavigate()

  // controles para el formulario multi-pasos
  const [step, setStep] = useState(1)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  // aqui guardamos todos los datos que el usuario va metiendo
  const [form, setForm] = useState({
    name: '',
    industry: '',
    country: 'El Salvador',
    region: '',
    keywords: [] as string[],
  })

  // auxiliar para manejar las keywords (las guardamos como array)
  const [currentKeyword, setCurrentKeyword] = useState('')

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    setForm({ ...form, [e.target.name]: e.target.value })
  }

  const addKeyword = () => {
    if (currentKeyword && !form.keywords.includes(currentKeyword)) {
      setForm({ ...form, keywords: [...form.keywords, currentKeyword] })
      setCurrentKeyword('')
    }
  }

  const removeKeyword = (word: string) => {
    setForm({ ...form, keywords: form.keywords.filter(k => k !== word) })
  }

  const nextStep = () => setStep(step + 1)
  const prevStep = () => setStep(step - 1)

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)
    setError('')

    try {
      // mandamos los datos al endpoint que creamos en el backend
      await api.post('/companies', form)
      // si todo sale bien, lo mandamos al dashboard
      navigate('/dashboard')
    } catch (err) {
      if (axios.isAxiosError(err)) {
        setError(err.response?.data?.message || 'Error al guardar la empresa')
      } else {
        setError('Error al guardar la empresa')
      }
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center p-6">
      <div className="bg-white rounded-2xl shadow-xl p-8 w-full max-w-lg">

        {/* Indicador de pasos arriba */}
        <div className="flex justify-between mb-8">
          {[1, 2, 3].map((s) => (
            <div key={s} className="flex flex-col items-center flex-1">
              <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold transition ${step >= s ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500'}`}>
                {s}
              </div>
              <span className={`text-xs mt-2 ${step >= s ? 'text-blue-600 font-medium' : 'text-gray-400'}`}>
                {s === 1 ? 'Básico' : s === 2 ? 'Lugar' : 'Intereses'}
              </span>
            </div>
          ))}
        </div>

        <h1 className="text-2xl font-bold text-gray-800 mb-1">Configura tu Empresa</h1>
        <p className="text-gray-500 text-sm mb-6">Necesitamos estos datos para generar tu inteligencia personalizada.</p>

        {error && (
          <div className="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-lg mb-4">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit}>
          {/* PASO 1: Datos Basicos */}
          {step === 1 && (
            <div className="space-y-4 animate-in fade-in duration-300">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Nombre de la Empresa</label>
                <input
                  type="text"
                  name="name"
                  value={form.name}
                  onChange={handleChange}
                  placeholder="Ej: Mi Pyme Global"
                  required
                  className="w-full border border-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Industria</label>
                <select
                  name="industry"
                  value={form.industry}
                  onChange={handleChange}
                  required
                  className="w-full border border-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none"
                >
                  <option value="">Selecciona una industria</option>
                  <option value="Tecnología">Tecnología</option>
                  <option value="Alimentos">Alimentos</option>
                  <option value="Comercio">Comercio</option>
                  <option value="Construcción">Construcción</option>
                  <option value="Servicios">Servicios</option>
                  <option value="Salud">Salud</option>
                  <option value="Educación">Educación</option>
                  <option value="Finanzas">Finanzas</option>
                  <option value="Turismo">Turismo</option>
                  <option value="Manufactura">Manufactura</option>
                  <option value="Transporte">Transporte</option>
                  <option value="Energía">Energía</option>
                  <option value="Agricultura">Agricultura</option>
                  <option value="Moda">Moda</option>
                  <option value="Medios">Medios</option>
                  <option value="Deportes">Deportes</option>
                  <option value="Entretenimiento">Entretenimiento</option>
                  <option value="Otro">Otro</option>
                </select>
              </div>
              <button
                type="button"
                onClick={nextStep}
                disabled={!form.name || !form.industry}
                className="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition disabled:opacity-50"
              >
                Siguiente
              </button>
            </div>
          )}

          {/* PASO 2: Ubicacion */}
          {step === 2 && (
            <div className="space-y-4 animate-in fade-in duration-300">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">País</label>
                <input
                  type="text"
                  name="country"
                  value={form.country}
                  onChange={handleChange}
                  className="w-full border border-gray-200 rounded-lg px-4 py-3 bg-gray-50"
                  readOnly
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Región / Departamento</label>
                <input
                  type="text"
                  name="region"
                  value={form.region}
                  onChange={handleChange}
                  placeholder="Ej: San Salvador"
                  required
                  className="w-full border border-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none"
                />
              </div>
              <div className="flex gap-4">
                <button type="button" onClick={prevStep} className="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl font-bold">Atrás</button>
                <button type="button" onClick={nextStep} disabled={!form.region} className="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700">Siguiente</button>
              </div>
            </div>
          )}

          {/* PASO 3: Keywords e Intereses */}
          {step === 3 && (
            <div className="space-y-4 animate-in fade-in duration-300">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">¿Qué temas te interesan monitorear?</label>
                <div className="flex gap-2">
                  <input
                    type="text"
                    value={currentKeyword}
                    onChange={(e) => setCurrentKeyword(e.target.value)}
                    placeholder="Ej: Ecommerce"
                    className="flex-1 border border-gray-200 rounded-lg px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500"
                  />
                  <button type="button" onClick={addKeyword} className="bg-blue-100 text-blue-700 px-4 rounded-lg font-bold">+</button>
                </div>
              </div>

              <div className="flex flex-wrap gap-2 mt-2">
                {form.keywords.map(word => (
                  <span key={word} className="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm flex items-center gap-1">
                    {word}
                    <button type="button" onClick={() => removeKeyword(word)} className="text-gray-400 hover:text-red-500">×</button>
                  </span>
                ))}
              </div>

              <div className="flex gap-4 mt-8">
                <button type="button" onClick={prevStep} className="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl font-bold">Atrás</button>
                <button
                  type="submit"
                  disabled={loading}
                  className="flex-1 bg-green-600 text-white py-3 rounded-xl font-bold hover:bg-green-700 transition disabled:opacity-50"
                >
                  {loading ? 'Guardando...' : 'Finalizar'}
                </button>
              </div>
            </div>
          )}
        </form>
      </div>
    </div>
  )
}

export default CompanySetupPage
