import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import LoginPage from './pages/LoginPage'
import RegisterPage from './pages/RegisterPage'
import ForgotPasswordPage from './pages/ForgotPasswordPage'
import DashboardPage from './pages/DashboardPage'
import CompanySetupPage from './pages/CompanySetupPage'
import MarketPage from './pages/MarketPage'
import TrendsPage from './pages/TrendsPage'
import PredictionsPage from './pages/PredictionsPage'
import InnovationPage from './pages/InnovationPage'

// componente que protege rutas: si no hay token manda al login
const PrivateRoute = ({ children }: { children: React.ReactNode }) => {
  const token = localStorage.getItem('token')
  return token ? <>{children}</> : <Navigate to="/login" />
}

function App() {
  return (
    <BrowserRouter>
      <Routes>
        {/* ruta raiz */}
        <Route path="/" element={<Navigate to="/login" />} />

        {/* rutas publicas */}
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route path="/forgot-password" element={<ForgotPasswordPage />} />

        {/* rutas privadas del dashboard */}
        <Route path="/dashboard" element={<PrivateRoute><DashboardPage /></PrivateRoute>} />
        <Route path="/dashboard/market" element={<PrivateRoute><MarketPage /></PrivateRoute>} />
        <Route path="/dashboard/trends" element={<PrivateRoute><TrendsPage /></PrivateRoute>} />
        <Route path="/dashboard/predictions" element={<PrivateRoute><PredictionsPage /></PrivateRoute>} />
        <Route path="/dashboard/innovation" element={<PrivateRoute><InnovationPage /></PrivateRoute>} />

        {/* configuracion de empresa */}
        <Route path="/setup-company" element={<PrivateRoute><CompanySetupPage /></PrivateRoute>} />
      </Routes>
    </BrowserRouter>
  )
}

export default App
