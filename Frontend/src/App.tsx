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
import ToastContainer from './components/Toast'
import ErrorBoundary from './components/ErrorBoundary'

// componente que protege rutas: si no hay token manda al login
const PrivateRoute = ({ children }: { children: React.ReactNode }) => {
  const token = localStorage.getItem('token')
  return token ? <>{children}</> : <Navigate to="/login" />
}

function App() {
  return (
    <BrowserRouter>
      {/*
        ToastContainer se monta una sola vez aquí y usa createPortal
        para que los toasts aparezcan sobre cualquier contenido
      */}
      <ToastContainer />

      <Routes>
        {/* ruta raiz */}
        <Route path="/" element={<Navigate to="/login" />} />

        {/* rutas publicas */}
        <Route path="/login"           element={<LoginPage />} />
        <Route path="/register"        element={<RegisterPage />} />
        <Route path="/forgot-password" element={<ForgotPasswordPage />} />

        {/* rutas privadas envueltas en ErrorBoundary para capturar errores de renders */}
        <Route path="/dashboard" element={
          <PrivateRoute>
            <ErrorBoundary><DashboardPage /></ErrorBoundary>
          </PrivateRoute>
        } />
        <Route path="/dashboard/market" element={
          <PrivateRoute>
            <ErrorBoundary><MarketPage /></ErrorBoundary>
          </PrivateRoute>
        } />
        <Route path="/dashboard/trends" element={
          <PrivateRoute>
            <ErrorBoundary><TrendsPage /></ErrorBoundary>
          </PrivateRoute>
        } />
        <Route path="/dashboard/predictions" element={
          <PrivateRoute>
            <ErrorBoundary><PredictionsPage /></ErrorBoundary>
          </PrivateRoute>
        } />
        <Route path="/dashboard/innovation" element={
          <PrivateRoute>
            <ErrorBoundary><InnovationPage /></ErrorBoundary>
          </PrivateRoute>
        } />

        {/* configuracion de empresa */}
        <Route path="/setup-company" element={
          <PrivateRoute>
            <ErrorBoundary><CompanySetupPage /></ErrorBoundary>
          </PrivateRoute>
        } />
      </Routes>
    </BrowserRouter>
  )
}

export default App
