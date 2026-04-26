import http from './http'

export const tachesApi = {
  getAll:                  (params = {}) => http.get('/v1/taches', { params }),
  getById:                 (id)          => http.get(`/v1/taches/${id}`),
  create:                  (data)        => http.post('/v1/taches', data),

  /**
   * Avance le statut d'une tache.
   * Accepte optionnellement un payload { photo, note, latitude_pose, longitude_pose }.
   * Si une photo est fournie, la requete est envoyee en multipart/form-data
   * via POST + _method=PATCH (contrainte Laravel sur PUT/PATCH multipart).
   */
  avancer: (id, payload = null) => {
    if (!payload || !payload.photo) {
      return http.patch(`/v1/taches/${id}/avancer`)
    }

    const fd = new FormData()
    fd.append('photo', payload.photo)
    if (payload.note)            fd.append('note', payload.note)
    if (payload.latitude_pose)   fd.append('latitude_pose', payload.latitude_pose)
    if (payload.longitude_pose)  fd.append('longitude_pose', payload.longitude_pose)

    // Content-Type: undefined → axios supprime le header par défaut (application/json)
    // et laisse le navigateur générer multipart/form-data avec le boundary correct
    return http.post(`/v1/taches/${id}/avancer`, fd, {
      headers: { 'Content-Type': undefined },
    })
  },

  updatePhoto: (id, file) => {
    const fd = new FormData()
    fd.append('photo', file)
    return http.post(`/v1/taches/${id}/photo`, fd, {
      headers: { 'Content-Type': undefined },
    })
  },

  delete:   (id)       => http.delete(`/v1/taches/${id}`),
  assigner: (id, data) => http.patch(`/v1/taches/${id}/assigner`, data),
  getAgents:                  ()         => http.get('/v1/agents'),
  getAffectationsDisponibles: ()         => http.get('/v1/taches/affectations-disponibles'),
}
