import { defineStore } from 'pinia'
import { ref, reactive } from 'vue'
import { usersApi } from '@/api/users.api'

export const useUsersStore = defineStore('users', () => {

  // State 
  const users       = ref([])
  const currentUser = ref(null)
  const isLoading   = ref(false)
  const errors      = ref(null)

  const pagination = reactive({
    currentPage: 1,
    lastPage:    1,
    perPage:     15,
    total:       0,
  })

  const filters = reactive({
    search: '',
    role:   '',
  })

  // Actions 

  async function fetchUsers(page = 1) {
    isLoading.value = true
    errors.value    = null

    try {
      const response = await usersApi.getAll({
        page,
        search: filters.search || undefined,
        role:   filters.role   || undefined,
      })

      users.value = response.data

      // Hydrate la pagination depuis le meta Laravel
      pagination.currentPage = response.meta.current_page
      pagination.lastPage    = response.meta.last_page
      pagination.perPage     = response.meta.per_page
      pagination.total       = response.meta.total

    } finally {
      isLoading.value = false
    }
  }

  async function createUser(data) {
    isLoading.value = true
    errors.value    = null

    try {
      const newUser = await usersApi.create(data)
      // Rafraichit la liste plutot qu'un push local
      // garantit la coherence avec la pagination serveur
      await fetchUsers(pagination.currentPage)
      return newUser

    } catch (err) {
      // Stocke les erreurs 422 pour les afficher dans UserModal
      if (err.response?.status === 422) {
        errors.value = err.response.data.errors
      }
      throw err

    } finally {
      isLoading.value = false
    }
  }

  async function updateUser(id, data) {
    isLoading.value = true
    errors.value    = null

    try {
      const updated = await usersApi.update(id, data)

      // Mise a jour locale immediate (Optimistic UI)
      // L'utilisateur voit le changement sans attendre un refetch
      const index = users.value.findIndex(u => u.id === id)
      if (index !== -1) {
        users.value[index] = updated
      }

      currentUser.value = null
      return updated

    } catch (err) {
      if (err.response?.status === 422) {
        errors.value = err.response.data.errors
      }
      throw err

    } finally {
      isLoading.value = false
    }
  }

  async function removeUser(id) {
    try {
      await usersApi.remove(id)

      // Retrait immediat de la liste locale
      users.value     = users.value.filter(u => u.id !== id)
      pagination.total -= 1

    } catch (err) {
      throw err
    }
  }

  function setCurrentUser(user) {
    currentUser.value = user
    errors.value      = null
  }

  function clearErrors() {
    errors.value = null
  }

  return {
    users,
    currentUser,
    isLoading,
    errors,
    pagination,
    filters,
    fetchUsers,
    createUser,
    updateUser,
    removeUser,
    setCurrentUser,
    clearErrors,
  }
})