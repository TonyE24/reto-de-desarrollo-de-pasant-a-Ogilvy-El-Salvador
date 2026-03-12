import { lazy, Suspense, type ReactNode } from 'react'
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import ToastContainer from './components/Toast'
import ErrorBoundary from './components/ErrorBoundary'
import LoadingSpinner from './components/LoadingSpinner'

// ---------------------------------------------------------------
// Issue #36 - Optimización de Performance: Lazy Loading + Code Splitting
// Cada página se carga solo cuando el usuario la visita por primera vez.
// Esto reduce el bundle inicial y mejora el tiempo de primera carga.
// ---------------------------------------------------------------
const LoginPage          = lazy(() => import('./pages/LoginPage'))
const RegisterPage       = lazy(() => import('./pages/RegisterPage'))
const ForgotPasswordPage = lazy(() => import('./pages/ForgotPasswordPage'))
const DashboardPage      = lazy(() => import('./pages/DashboardPage'))
const CompanySetupPage   = lazy(() => import('./pages/CompanySetupPage'))
const MarketPage         = lazy(() => import('./pages/MarketPage'))
const TrendsPage         = lazy(() => import('./pages/TrendsPage'))
const PredictionsPage    = lazy(() => import('./pages/PredictionsPage'))
const InnovationPage     = lazy(() => import('./pages/InnovationPage'))

// componente que protege rutas: si no hay token manda al login
const PrivateRoute = ({ children }: { children: ReactNode }) => {
  const token = localStorage.getItem('token')
  return token ? <>{children}</> : <Navigate to="/login" />
}

// fallback del Suspense: se muestra mientras la página lazy carga
const PageLoader = () => (
  <div className="min-h-screen flex items-center justify-center bg-gray-50">
    <LoadingSpinner size="lg" color="indigo" label="Cargando..." />
  </div>
)

function App() {
  return (
    <BrowserRouter>
      {/* ToastContainer montado una sola vez globalmente (Issue #35) */}
      <ToastContainer />

      {/*
        Suspense envuelve todas las rutas para manejar el lazy loading.
        Mientras una página carga, muestra el PageLoader.
      */}
      <Suspense fallback={<PageLoader />}>
        <Routes>
          {/* ruta raiz */}
          <Route path="/" element={<Navigate to="/login" />} />

          {/* rutas públicas */}
          <Route path="/login"           element={<LoginPage />} />
          <Route path="/register"        element={<RegisterPage />} />
          <Route path="/forgot-password" element={<ForgotPasswordPage />} />

          {/* rutas privadas con ErrorBoundary (Issue #35) */}
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

          {/* configuración de empresa */}
          <Route path="/setup-company" element={
            <PrivateRoute>
              <ErrorBoundary><CompanySetupPage /></ErrorBoundary>
            </PrivateRoute>
          } />
        </Routes>
      </Suspense>
    </BrowserRouter>
  )
}

export default App
