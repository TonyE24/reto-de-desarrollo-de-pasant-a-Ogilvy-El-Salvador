import Sidebar from './Sidebar'

// este layout envuelve todas las paginas del dashboard
// de esta forma el sidebar aparece en todas sin repetir codigo
function DashboardLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="flex min-h-screen bg-gray-50">
      <Sidebar />
      {/* el contenido principal tiene margen a la izquierda por el sidebar */}
      <main className="flex-1 ml-64 p-8 overflow-y-auto">
        {children}
      </main>
    </div>
  )
}

export default DashboardLayout
