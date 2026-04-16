import http from './http'

export const tachesApi = {
  getAll: (params = {}) => http.get('/v1/taches', { params }),
  getById: (id) => http.get(`/v1/taches/${id}`),
  avancer: (id, data) => http.patch(`/v1/taches/${id}/avancer`, data),
  assigner: (id, data) => http.patch(`/v1/taches/${id}/assigner`, data),
  getAgents: () => http.get('/v1/agents'),
}