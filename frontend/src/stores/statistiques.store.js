import { defineStore }  from 'pinia'
import { ref }          from 'vue'
import { dashboardApi } from '@/api/dashboard.api'

export const useStatistiquesStore = defineStore('statistiques', () => {

  const stats     = ref(null)
  const isLoading = ref(false)
  const errors    = ref(null)

  async function fetchStats(periode = 'ce_mois') {
    isLoading.value = true
    errors.value    = null

    try {
      const response = await dashboardApi.getStatistiques({ periode })
      stats.value    = response.data

    } catch (err) {
      errors.value = err.response?.data?.message
                     || 'Erreur chargement statistiques'
    } finally {
      isLoading.value = false
    }
  }

  return { stats, isLoading, errors, fetchStats }
})