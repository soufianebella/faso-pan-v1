import { defineStore }   from 'pinia'
import { ref, reactive } from 'vue'
import { panneauxApi }   from '@/api/panneaux.api'

export const usePanneauxStore = defineStore('panneaux', () => {

  // ── State ──────────────────────────────────────────────────────────────────
  const panneaux      = ref([])
  const panneauActuel = ref(null)
  const isLoading     = ref(false)
  const errors        = ref(null)

  // Historique lazy — chargé seulement quand l'onglet est activé
  const historique        = ref([])
  const historiqueLoading = ref(false)

  // Page de détail panneau
  const panneauDetail        = ref(null)
  const panneauDetailLoading = ref(false)
  const photosDetail         = ref([])
  const photosLoading        = ref(false)

  const pagination = reactive({
    currentPage: 1,
    lastPage:    1,
    perPage:     15,
    total:       0,
  })

  const filtres = reactive({
    search:  '',
    ville:   '',
    statut:  '',
    eclaire: '',
  })

  // ── Actions ────────────────────────────────────────────────────────────────

  async function fetchPanneaux(page = 1) {
    isLoading.value = true
    errors.value    = null

    try {
      const response = await panneauxApi.getAll({
        page,
        search:  filtres.search   || undefined,
        ville:   filtres.ville    || undefined,
        statut:  filtres.statut   || undefined,
        eclaire: filtres.eclaire  !== '' ? filtres.eclaire : undefined,
      })

      panneaux.value = response.data

      pagination.currentPage = response.meta.current_page
      pagination.lastPage    = response.meta.last_page
      pagination.perPage     = response.meta.per_page
      pagination.total       = response.meta.total

    } finally {
      isLoading.value = false
    }
  }

  async function fetchPanneau(id) {
    isLoading.value = true
    try {
      const res = await panneauxApi.getById(id)
      // Laravel Resource single → { data: {...} } — on unwrap
      panneauActuel.value = res.data ?? res
    } finally {
      isLoading.value = false
    }
  }

  async function createPanneau(data) {
    isLoading.value = true
    errors.value    = null

    try {
      await panneauxApi.create(data)
      await fetchPanneaux(pagination.currentPage)

    } catch (err) {
      if (err.response?.status === 422) {
        errors.value = err.response.data.errors
      }
      throw err

    } finally {
      isLoading.value = false
    }
  }

  async function updatePanneau(id, data) {
    isLoading.value = true
    errors.value    = null

    try {
      const res     = await panneauxApi.update(id, data)
      const updated = res.data ?? res

      const index = panneaux.value.findIndex(p => p.id === id)
      if (index !== -1) panneaux.value[index] = updated

    } catch (err) {
      if (err.response?.status === 422) {
        errors.value = err.response.data.errors
      }
      throw err

    } finally {
      isLoading.value = false
    }
  }

  async function archivePanneau(id) {
    try {
      await panneauxApi.archive(id)
      panneaux.value   = panneaux.value.filter(p => p.id !== id)
      pagination.total -= 1

    } catch (err) {
      throw err
    }
  }

  /**
   * Changement de statut tracé — met à jour la liste locale après succès.
   */
  async function changerStatut(id, data) {
    isLoading.value = true
    errors.value    = null

    try {
      const res     = await panneauxApi.changerStatut(id, data)
      const updated = res.data ?? res

      // Mise à jour locale immédiate sans recharger toute la liste
      const index = panneaux.value.findIndex(p => p.id === id)
      if (index !== -1) panneaux.value[index] = updated

      // Met à jour aussi panneauActuel si c'est le même
      if (panneauActuel.value?.id === id) {
        panneauActuel.value = { ...panneauActuel.value, statut: updated.statut }
      }

    } catch (err) {
      if (err.response?.status === 422) {
        errors.value = err.response.data.errors
      }
      throw err

    } finally {
      isLoading.value = false
    }
  }

  /**
   * Charge le détail complet d'un panneau (faces + affectation_active + createur).
   */
  async function fetchPanneauDetail(id) {
    panneauDetailLoading.value = true
    panneauDetail.value        = null

    try {
      const res = await panneauxApi.getById(id)
      // Laravel Resource single → { data: {...} } — on unwrap
      panneauDetail.value = res.data ?? res
    } finally {
      panneauDetailLoading.value = false
    }
  }

  /**
   * Chargement lazy des photos — appelé au clic sur l'onglet Photos.
   */
  async function fetchPhotosDetail(id) {
    photosLoading.value = true
    photosDetail.value  = []

    try {
      const response    = await panneauxApi.photos(id)
      photosDetail.value = response.data ?? []
    } catch {
      photosDetail.value = []
    } finally {
      photosLoading.value = false
    }
  }

  /**
   * Chargement lazy de l'historique — appelé seulement à l'activation de l'onglet.
   */
  async function fetchHistorique(id) {
    historiqueLoading.value = true
    historique.value        = []

    try {
      const response = await panneauxApi.historique(id)
      historique.value = response.data ?? []
    } catch {
      historique.value = []
    } finally {
      historiqueLoading.value = false
    }
  }

  // ── Helpers ────────────────────────────────────────────────────────────────

  function setPanneauActuel(panneau) {
    panneauActuel.value = panneau
    errors.value        = null
    historique.value    = []
  }

  function clearErrors() {
    errors.value = null
  }

  // ── Return ─────────────────────────────────────────────────────────────────

  return {
    panneaux,
    panneauActuel,
    isLoading,
    errors,
    pagination,
    filtres,
    historique,
    historiqueLoading,
    panneauDetail,
    panneauDetailLoading,
    photosDetail,
    photosLoading,
    fetchPanneaux,
    fetchPanneau,
    fetchPanneauDetail,
    fetchPhotosDetail,
    createPanneau,
    updatePanneau,
    archivePanneau,
    changerStatut,
    fetchHistorique,
    setPanneauActuel,
    clearErrors,
  }
})
