import { defineStore }    from 'pinia'
import { ref }            from 'vue'
import { notificationsApi } from '@/api/notifications.api'

export const useNotificationsStore = defineStore('notifications', () => {

  const notifications = ref([])
  const count         = ref(0)
  const isLoading     = ref(false)

  async function fetchNotifications() {
    isLoading.value = true
    try {
      const response      = await notificationsApi.getAll()
      notifications.value = response.data
      count.value         = response.total
    } finally {
      isLoading.value = false
    }
  }

  async function fetchCount() {
    try {
      const response = await notificationsApi.compter()
      count.value    = response.count
    } catch {
      count.value = 0
    }
  }

  async function marquerLue(id) {
    await notificationsApi.marquerLue(id)
    notifications.value = notifications.value.filter(n => n.id !== id)
    count.value         = Math.max(0, count.value - 1)
  }

  async function marquerToutesLues() {
    await notificationsApi.marquerToutesLues()
    notifications.value = []
    count.value         = 0
  }

  return {
    notifications,
    count,
    isLoading,
    fetchNotifications,
    fetchCount,
    marquerLue,
    marquerToutesLues,
  }
})