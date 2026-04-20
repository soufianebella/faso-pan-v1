import http from './http'

export const notificationsApi = {
  getAll:         ()   => http.get('/v1/notifications'),
  compter:        ()   => http.get('/v1/notifications/count'),
  marquerLue:     (id) => http.patch(`/v1/notifications/${id}/lue`),
  marquerToutesLues: () => http.patch('/v1/notifications/toutes-lues'),
}