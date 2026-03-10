import api from './api'

// Interfaces para que el resto del equipo sepa que devuelve la API
export interface Competitor {
  name: string
  price: number
}

export interface MarketProduct {
  product: string
  my_price: number
  competitors: Competitor[]
  market_share: string
}

export interface MarketResponse {
  company_name: string
  industry: string
  market_analysis: MarketProduct[]
}

export interface Trend {
  keyword: string
  volume: number
  sentiment: {
    positive: number
    neutral: number
    negative: number
  }
  trend: 'up' | 'down'
}

export interface TrendResponse {
  company_name: string
  trends: Trend[]
}

// helper para construir la query string con filtros opcionales de fecha
const buildParams = (companyId: number, dateFrom?: string, dateTo?: string) => {
  const params = new URLSearchParams({ company_id: String(companyId) })
  if (dateFrom) params.append('date_from', dateFrom)
  if (dateTo)   params.append('date_to', dateTo)
  return params.toString()
}

const intelligenceService = {
  // obtiene inteligencia de mercado (precios, etc)
  getMarketIntelligence: async (companyId: number, dateFrom = '', dateTo = ''): Promise<MarketResponse> => {
    const res = await api.get(`/intelligence/market?${buildParams(companyId, dateFrom, dateTo)}`)
    return res.data
  },

  // obtiene tendencias y sentimiento de redes
  getTrendIntelligence: async (companyId: number, dateFrom = '', dateTo = ''): Promise<TrendResponse> => {
    const res = await api.get(`/intelligence/trends?${buildParams(companyId, dateFrom, dateTo)}`)
    return res.data
  },

  // obtiene predicciones historicas
  getPredictionIntelligence: async (companyId: number, dateFrom = '', dateTo = '') => {
    const res = await api.get(`/intelligence/predictions?${buildParams(companyId, dateFrom, dateTo)}`)
    return res.data
  },

  // obtiene oportunidades de innovacion
  getInnovationIntelligence: async (companyId: number, dateFrom = '', dateTo = '') => {
    const res = await api.get(`/intelligence/innovation?${buildParams(companyId, dateFrom, dateTo)}`)
    return res.data
  }
}

export default intelligenceService
