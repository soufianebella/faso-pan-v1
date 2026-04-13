import http from './http'

export const usersApi = {
  getAll:   (params = {}) => http.get('/v1/users', { params }),
  getById:  (id)          => http.get(`/v1/users/${id}`),
  create:   (data)        => http.post('/v1/users', data),
  update:   (id, data)    => http.put(`/v1/users/${id}`, data),
  remove:   (id)          => http.delete(`/v1/users/${id}`),
}