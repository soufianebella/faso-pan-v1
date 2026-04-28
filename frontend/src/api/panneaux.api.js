import http from './http'

export const panneauxApi = {
  getAll:        (params = {}) => http.get('/v1/panneaux', { params }),
  getById:       (id)          => http.get(`/v1/panneaux/${id}`),
  create:        (data)        => http.post('/v1/panneaux', data),
  update:        (id, data)    => http.put(`/v1/panneaux/${id}`, data),
  archive:       (id)          => http.delete(`/v1/panneaux/${id}`),

  // Changement de statut tracé
  changerStatut: (id, data)    => http.patch(`/v1/panneaux/${id}/statut`, data),

  // Timeline historique
  historique:    (id)          => http.get(`/v1/panneaux/${id}/historique`),

  // Photos de terrain
  photos:        (id)          => http.get(`/v1/panneaux/${id}/photos`),
}
