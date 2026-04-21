import axios from 'axios'

/**
 * Telecharge un CSV via axios en mode blob.
 * N'utilise PAS l'instance http partagée car :
 *  1. L'interceptor http.js retourne response.data → on perd les headers
 *  2. On a besoin du Content-Disposition pour le nom de fichier
 *
 * On re-injecte le Bearer token manuellement.
 */
async function downloadCsv(url, params = {}, fallbackFilename = 'export.csv') {
  const baseURL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'
  const token   = localStorage.getItem('token')

  const response = await axios.get(`${baseURL}${url}`, {
    params,
    responseType: 'blob',
    headers: {
      // X-Requested-With : indicateur AJAX — Laravel retourne 401/403 JSON
      // au lieu de rediriger vers la page login (ce qui causait le CORS error)
      'X-Requested-With': 'XMLHttpRequest',
      Accept:             'text/csv, application/json',
      Authorization:      token ? `Bearer ${token}` : undefined,
    },
  })

  // Extrait le filename du header Content-Disposition si present
  const disposition = response.headers['content-disposition'] || ''
  const match = disposition.match(/filename\s*=\s*"?([^";]+)"?/i)
  const filename = match ? match[1] : fallbackFilename

  // Blob → URL objet → <a download> → click → revoke
  const blobUrl = window.URL.createObjectURL(new Blob([response.data], { type: 'text/csv' }))
  const link = document.createElement('a')
  link.href = blobUrl
  link.download = filename
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  window.URL.revokeObjectURL(blobUrl)
}

export const exportsApi = {
  inventaire:        (filtres = {}) => downloadCsv('/v1/exports/inventaire', filtres, 'inventaire.csv'),
  campagnesActives:  (filtres = {}) => downloadCsv('/v1/exports/campagnes-actives', filtres, 'campagnes.csv'),
}
