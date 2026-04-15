import http from './http'

export const campagnesApi = {
  getAll:            (params = {}) => http.get('/v1/campagnes', { params }),
  getById:           (id)          => http.get(`/v1/campagnes/${id}`),
  create:            (data)        => http.post('/v1/campagnes', data),
  archive:           (id)          => http.delete(`/v1/campagnes/${id}`),
  getAvailableFaces: (params = {}) => http.get('/v1/faces/disponibles', { params }),
}