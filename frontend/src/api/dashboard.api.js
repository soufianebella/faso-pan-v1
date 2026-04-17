import http from './http'

export const dashboardApi = {
  getStats:        () => http.get('/v1/stats'),
  getDashboard:    () => http.get('/v1/stats/dashboard'),
  getStatistiques: (params = {}) => http.get('/v1/stats/statistiques', { params }),
}