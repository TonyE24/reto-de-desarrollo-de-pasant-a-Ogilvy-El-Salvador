import { useState, useEffect, useCallback } from 'react'

/**
 * useIntelligenceData - Issue #36 (Memoización y reutilización)
 *
 * Hook genérico que abstrae el patrón de fetch + loading + error
 * que se repite en los 4 módulos de inteligencia.
 * Usa useCallback para estabilizar fetchData y evitar re-renders innecesarios.
 *
 * @param fetcher - función que recibe (dateFrom, dateTo) y retorna los datos
 *
 * Uso:
 *   const { data, loading, error, refetch } = useIntelligenceData(
 *     (from, to) => intelligenceService.getMarketIntelligence(companyId, from, to)
 *   )
 */
export function useIntelligenceData<T>(
  fetcher: (dateFrom: string, dateTo: string) => Promise<T>
) {
  const [data, setData]       = useState<T | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError]     = useState<string | null>(null)

  // useCallback evita que fetchData cambie de referencia en cada render
  // sin esto, el useEffect se ejecutaría en bucle infinito
  const fetchData = useCallback(async (dateFrom = '', dateTo = '') => {
    setLoading(true)
    setError(null)
    try {
      const result = await fetcher(dateFrom, dateTo)
      setData(result)
    } catch (err: any) {
      const message = err?.response?.data?.message ?? 'Error al cargar los datos. Intenta de nuevo.'
      setError(message)
    } finally {
      setLoading(false)
    }
  }, [fetcher])

  // carga inicial
  useEffect(() => {
    fetchData()
  }, [fetchData])

  return { data, loading, error, refetch: fetchData }
}

/**
 * getCompanyId - helper memoizable para leer el company_id del localStorage
 * Exportado para usarlo en los módulos sin duplicar lógica
 */
export function getCompanyId(): number {
  try {
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    return user.company_id || 1
  } catch {
    return 1
  }
}
