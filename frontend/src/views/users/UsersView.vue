<template>
  <div class="p-6 space-y-4 h-screen overflow-hidden flex flex-col">

    <div class="flex items-center justify-between flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold" style="color: #1B3B8A">
          Utilisateurs
        </h1>
        <p class="text-sm mt-0.5" style="color: #6B7280">
          {{ pagination.total }} utilisateurs au total
        </p>
      </div>

      <button
        @click="openCreate"
        class="flex items-center gap-2 px-4 py-2 rounded
               text-white text-sm font-medium shadow-sm
               transition-colors"
        style="background-color: #F97316"
        @mouseenter="$event.target.style.backgroundColor='#EA6C0A'"
        @mouseleave="$event.target.style.backgroundColor='#F97316'"
      >
        <i class="fa-solid fa-plus"></i>
        Nouvel utilisateur
      </button>
    </div>

    <!-- Skeleton loader -->
    <div v-if="isLoading" class="space-y-3 flex-1">
      <div
        v-for="i in 5"
        :key="i"
        class="h-12 w-full rounded animate-pulse"
        style="background-color: #E5E7EB"
      ></div>
    </div>

    <template v-else>
      <UserTable
        :users="users"
        class="flex-1 overflow-auto"
        @edit="openEdit"
        @delete="handleDelete"
      />

      <UserPagination
        :pagination="pagination"
        @page-change="store.fetchUsers"
      />
    </template>

    <UserModal
      :show="showModal"
      :user="currentUser"
      :errors="errors"
      @close="handleClose"
      @saved="handleSaved"
    />

    <ConfirmModal
      v-model="confirm.show"
      :title="confirm.title"
      :message="confirm.message"
      :variant="confirm.variant"
      :confirm-label="confirm.label"
      @confirm="confirm.action?.()"
    />

  </div>
</template>

<script setup>
import { ref, onMounted }  from 'vue'
import { storeToRefs }     from 'pinia'
import { useUsersStore }   from '@/stores/users.store'
import UserTable           from '@/components/users/UserTable.vue'
import UserPagination      from '@/components/users/UserPagination.vue'
import UserModal           from '@/components/users/UserModal.vue'
import ConfirmModal        from '@/components/ui/ConfirmModal.vue'
import { useToast }        from '@/composables/useToast'

const store = useUsersStore()

const {
  users,
  isLoading,
  pagination,
  currentUser,
  errors,
} = storeToRefs(store)

const showModal = ref(false)
const toast     = useToast()
const confirm   = ref({ show: false, title: '', message: '', variant: 'danger', label: 'Confirmer', action: null })

onMounted(() => store.fetchUsers())

function openCreate() {
  store.setCurrentUser(null)
  showModal.value = true
}

function openEdit(user) {
  // Clone pour eviter la mutation directe du store
  store.setCurrentUser({ ...user })
  showModal.value = true
}

function handleDelete(userId) {
  confirm.value = {
    show:    true,
    title:   'Désactiver l\'utilisateur',
    message: 'Cet utilisateur ne pourra plus se connecter. Confirmer ?',
    variant: 'danger',
    label:   'Désactiver',
    action:  async () => {
      try {
        await store.removeUser(userId)
        toast.success('Utilisateur désactivé.')
      } catch (err) {
        toast.error(err.response?.data?.message || 'Erreur lors de la désactivation.')
      }
    },
  }
}

function handleClose() {
  showModal.value = false
  store.clearErrors()
}

function handleSaved() {
  showModal.value = false
  store.fetchUsers(pagination.value.currentPage)
}
</script>