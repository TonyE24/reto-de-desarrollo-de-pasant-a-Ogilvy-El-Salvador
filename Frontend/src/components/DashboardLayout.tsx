import { useState, useCallback } from 'react'
import Sidebar from './Sidebar'

// ícono hamburguesa
const IconMenu = () => (
  <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
  </svg>
)

/**
 * DashboardLayout - Issue #37 (Responsive)
 * Maneja el estado open/close del sidebar para mobile.
 * En desktop (≥ lg) el sidebar siempre es visible y el contenido
 * tiene el margen izquierdo fijo (ml-64).
 */
function DashboardLayout({ children }: { children: React.ReactNode }) {
  const [sidebarOpen, setSidebarOpen] = useState(false)

  const openSidebar  = useCallback(() => setSidebarOpen(true),  [])
  const closeSidebar = useCallback(() => setSidebarOpen(false), [])

  return (
    <div className="flex min-h-screen bg-gray-50">
      <Sidebar open={sidebarOpen} onClose={closeSidebar} />

      {/* contenido principal */}
      <main className="flex-1 lg:ml-64 min-w-0">
        {/* topbar en mobile: botón hamburguesa */}
        <div className="sticky top-0 z-30 flex items-center gap-3 px-4 py-3 bg-white border-b border-gray-100 shadow-sm lg:hidden">
          <button
            id="sidebar-toggle-btn"
            onClick={openSidebar}
            className="p-2 rounded-xl text-gray-500 hover:bg-gray-100 transition"
            aria-label="Abrir menú"
          >
            <IconMenu />
          </button>
          <div className="flex items-center gap-2">
            <div className="w-7 h-7 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-xs">
              MI
            </div>
            <span className="font-semibold text-gray-800 text-sm">MarketIntelligence</span>
          </div>
        </div>

        {/* contenido de la página */}
        <div className="p-4 md:p-6 lg:p-8">
          {children}
        </div>
      </main>
    </div>
  )
}

export default DashboardLayout
