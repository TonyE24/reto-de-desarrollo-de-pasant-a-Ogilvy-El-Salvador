// este archivo configura axios para todas las peticiones al backend
// asi no tenemos que escribir la URL completa en cada peticion
import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// interceptor: antes de cada peticion agrego el token si existe
// el token lo guardamos en localStorage cuando el user hace login
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export default api
