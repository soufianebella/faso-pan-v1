import { defineStore }   from 'pinia'
import { ref, reactive } from 'vue'
import { panneauxApi }   from '@/api/panneaux.api'

export const usePanneauxStore = defineStore('panneaux', () => {

  // ── State ──────────────────────────────────────────────────────
  const panneaux      = ref([])
  const panneauActuel = ref(null)
  const isLoading     = ref(false)
  const errors        = ref(null)

  const pagination = reactive({
    currentPage: 1,
    lastPage:    1,
    perPage:     15,
    total:       0,
  })

  const filtres = reactive({
    search: '',
    ville:  '',
    statut: '',
  })

  // ── Actions

  async function fetchPanneaux(page = 1) {
    isLoading.value = true
    errors.value    = null

    try {
      const response = await panneauxApi.getAll({
        page,
        search: filtres.search || undefined,
        ville:  filtres.ville  || undefined,
        statut: filtres.statut || undefined,
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
      panneauActuel.value = await panneauxApi.getById(id)
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
      const updated = await panneauxApi.update(id, data)

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

  // ── Helpers 

  function setPanneauActuel(panneau) {
    panneauActuel.value = panneau
    errors.value        = null
  }

  function clearErrors() {
    errors.value = null
  }

  // ── Return 

  return {
    panneaux,
    panneauActuel,
    isLoading,
    errors,
    pagination,
    filtres,
    fetchPanneaux,
    fetchPanneau,
    createPanneau,
    updatePanneau,
    archivePanneau,
    setPanneauActuel,
    clearErrors,
  }
})