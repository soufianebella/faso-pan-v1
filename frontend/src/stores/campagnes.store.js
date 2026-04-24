import { defineStore } from 'pinia'
import { ref, reactive } from 'vue'
import { campagnesApi } from '@/api/campagnes.api'

export const useCampagnesStore = defineStore('campagnes', () => {

  // ── State 
  const campagnes        = ref([])
  const campagneActuelle = ref(null)
  const facesDisponibles = ref([])
  const isLoading        = ref(false)
  const isLoadingFaces   = ref(false)
  const errors           = ref(null)

  const pagination = reactive({
    currentPage: 1,
    lastPage:    1,
    perPage:     15,
    total:       0,
  })

  const filtres = reactive({
    search:    '',
    statut:    '',
    annonceur: '',
  })

  // ── Actions 

  async function fetchCampagnes(page = 1) {
    isLoading.value = true
    errors.value    = null

    try {
      const response = await campagnesApi.getAll({
        page,
        search:    filtres.search    || undefined,
        statut:    filtres.statut    || undefined,
        annonceur: filtres.annonceur || undefined,
      })

      campagnes.value = response.data

      pagination.currentPage = response.meta.current_page
      pagination.lastPage    = response.meta.last_page
      pagination.perPage     = response.meta.per_page
      pagination.total       = response.meta.total

    } finally {
      isLoading.value = false
    }
  }

  async function fetchCampagne(id) {
    isLoading.value = true
    try {
      // http.js retourne response.data directement
      campagneActuelle.value = await campagnesApi.getById(id)
    } finally {
      isLoading.value = false
    }
  }

  async function fetchAvailableFaces(dateDebut, dateFin) {
    if (!dateDebut || !dateFin) return

    isLoadingFaces.value   = true
    facesDisponibles.value = []
    errors.value           = null

    try {
      const response = await campagnesApi.getAvailableFaces({
        date_debut: dateDebut,
        date_fin:   dateFin,
      })
      facesDisponibles.value = response.data

    } catch (err) {
      facesDisponibles.value = []
      // 422 : dates invalides — on expose les erreurs pour que l'UI les affiche
      if (err.response?.status === 422) {
        errors.value = err.response.data.errors
      } else if (err.response?.status !== 403) {
        // 403 silencieux (permission), autre erreur → propage
        throw err
      }
    } finally {
      isLoadingFaces.value = false
    }
  }

  async function createCampagne(data) {
    isLoading.value = true
    errors.value    = null

    try {
      await campagnesApi.create(data)
      // Revient page 1 pour voir la nouvelle campagne
      await fetchCampagnes(1)

    } catch (err) {
      if (err.response?.status === 422) {
        errors.value = err.response.data.errors
      }
      throw err

    } finally {
      isLoading.value = false
    }
  }

  async function archiveCampagne(id) {
    try {
      await campagnesApi.archive(id)
      campagnes.value  = campagnes.value.filter(c => c.id !== id)
      pagination.total -= 1

    } catch (err) {
      throw err
    }
  }

  async function deleteCampagne(id) {
    await campagnesApi.forceDelete(id)
    campagnes.value  = campagnes.value.filter(c => c.id !== id)
    pagination.total = Math.max(0, pagination.total - 1)
  }

  // ── Helpers 

  function setCampagneActuelle(campagne) {
    campagneActuelle.value = campagne
    errors.value           = null
  }

  function clearErrors() {
    errors.value = null
  }

  function resetFacesDisponibles() {
    facesDisponibles.value = []
  }

  // ── Return 

  return {
    campagnes,
    campagneActuelle,
    facesDisponibles,
    isLoading,
    isLoadingFaces,
    errors,
    pagination,
    filtres,
    fetchCampagnes,
    fetchCampagne,
    fetchAvailableFaces,
    createCampagne,
    archiveCampagne,
    deleteCampagne,
    setCampagneActuelle,
    clearErrors,
    resetFacesDisponibles,
  }
})