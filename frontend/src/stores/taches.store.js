import { defineStore }   from 'pinia'
import { ref, reactive } from 'vue'
import { tachesApi }     from '@/api/taches.api'

export const useTachesStore = defineStore('taches', () => {

  // ── State 
  const taches       = ref([])
  const tacheActuelle = ref(null)
  const agents       = ref([])
  const isLoading    = ref(false)
  const errors       = ref(null)

  const pagination = reactive({
    currentPage: 1,
    lastPage:    1,
    perPage:     15,
    total:       0,
  })

  const filtres = reactive({
    statut:   '',
    agent_id: '',
  })

  // ── Actions 

  async function fetchTaches(page = 1) {
    isLoading.value = true
    errors.value    = null

    try {
      const response = await tachesApi.getAll({
        page,
        statut:   filtres.statut   || undefined,
        agent_id: filtres.agent_id || undefined,
      })

      taches.value = response.data

      pagination.currentPage = response.meta.current_page
      pagination.lastPage    = response.meta.last_page
      pagination.perPage     = response.meta.per_page
      pagination.total       = response.meta.total

    } finally {
      isLoading.value = false
    }
  }

  async function fetchAgents() {
    try {
      const response = await tachesApi.getAgents()
      // getAgents retourne { data: [...] }
      agents.value = response.data ?? response
    } catch (err) {
      console.error('Erreur chargement agents:', err)
    }
  }

  async function avancerTache(id) {
    isLoading.value = true
    errors.value    = null

    try {
      // Le backend calcule automatiquement le nouveau statut
      const updated = await tachesApi.avancer(id)

      // Mise à jour locale sans refetch
      const index = taches.value.findIndex(t => t.id === id)
      if (index !== -1) taches.value[index] = updated

      if (tacheActuelle.value?.id === id) {
        tacheActuelle.value = updated
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

  async function assignerAgent(tacheId, agentId) {
    isLoading.value = true
    errors.value    = null

    try {
      const updated = await tachesApi.assigner(tacheId, { agent_id: agentId })

      const index = taches.value.findIndex(t => t.id === tacheId)
      if (index !== -1) taches.value[index] = updated

    } catch (err) {
      if (err.response?.status === 422) {
        errors.value = err.response.data.errors
      }
      throw err

    } finally {
      isLoading.value = false
    }
  }

  function setTacheActuelle(tache) {
    tacheActuelle.value = tache
    errors.value        = null
  }

  function clearErrors() {
    errors.value = null
  }

  // ── Return 

  return {
    taches,
    tacheActuelle,
    agents,
    isLoading,
    errors,
    pagination,
    filtres,
    fetchTaches,
    fetchAgents,
    avancerTache,
    assignerAgent,
    setTacheActuelle,
    clearErrors,
  }
})