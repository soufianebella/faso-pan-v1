<template>
  <div class="p-6 space-y-4 h-screen overflow-hidden flex flex-col">

    <!-- Header -->
    <div class="flex items-center justify-between flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold" style="color: #1B3B8A">
          Taches Terrain
        </h1>
        <p class="text-sm mt-0.5" style="color: #6B7280">
          {{ pagination.total }} taches au total
        </p>
      </div>
    </div>

    <!-- Filtre statut -->
    <div
      class="flex gap-3 p-3 rounded-lg border flex-shrink-0 bg-white"
      style="border-color: #E5E7EB"
    >
      <select
        v-model="filtres.statut"
        @change="store.fetchTaches(1)"
        class="border rounded px-3 py-2 text-sm outline-none bg-white"
        style="border-color: #E5E7EB"
      >
        <option value="">Tous les statuts</option>
        <option value="en_attente">En attente</option>
        <option value="en_cours">En cours</option>
        <option value="realisee">Realisee</option>
        <option value="validee">Validee</option>
      </select>
    </div>

    <!-- Skeleton -->
    <div v-if="isLoading" class="space-y-3 flex-1">
      <div
        v-for="i in 5" :key="i"
        class="h-14 w-full rounded animate-pulse"
        style="background-color: #E5E7EB"
      ></div>
    </div>

    <template v-else>
      <div
        class="flex-1 overflow-auto bg-white rounded-lg border"
        style="border-color: #E5E7EB"
      >
        <TacheTable
          :taches="taches"
          @assigner="ouvrirModal"
          @avancer="confirmerAvancement"
        />
      </div>

      <!-- Pagination simple -->
      <div
        class="flex items-center justify-between py-3 px-4
               border-t bg-white flex-shrink-0"
        style="border-color: #E5E7EB"
      >
        <span class="text-sm" style="color: #6B7280">
          Page {{ pagination.currentPage }} sur {{ pagination.lastPage }}
        </span>
        <div class="flex gap-2">
          <button
            :disabled="pagination.currentPage === 1"
            @click="store.fetchTaches(pagination.currentPage - 1)"
            class="px-3 py-1 border rounded text-sm disabled:opacity-40"
            style="border-color: #E5E7EB"
          >
            <i class="fa-solid fa-chevron-left text-xs"></i>
          </button>
          <button
            :disabled="pagination.currentPage === pagination.lastPage"
            @click="store.fetchTaches(pagination.currentPage + 1)"
            class="px-3 py-1 border rounded text-sm disabled:opacity-40"
            style="border-color: #E5E7EB"
          >
            <i class="fa-solid fa-chevron-right text-xs"></i>
          </button>
        </div>
      </div>
    </template>

    <TacheModal
      v-if="showModal"
      @close="handleClose"
      @saved="handleSaved"
    />

  </div>
</template>

<script setup>
import { ref, onMounted }   from 'vue'
import { storeToRefs }      from 'pinia'
import { useTachesStore }   from '@/stores/taches.store'
import TacheTable           from '@/components/taches/TacheTable.vue'
import TacheModal           from '@/components/taches/TacheModal.vue'

const store = useTachesStore()
const { taches, isLoading, filtres, pagination } = storeToRefs(store)

const showModal = ref(false)

onMounted(() => store.fetchTaches())

function ouvrirModal(tache) {
  store.setTacheActuelle(tache)
  store.fetchAgents()
  showModal.value = true
}

async function confirmerAvancement(id) {
  if (!confirm('Confirmer l\'avancement de cette tache ?')) return
  try {
    await store.avancerTache(id)
  } catch (err) {
    alert(err.response?.data?.message || 'Erreur lors de l\'avancement.')
  }
}

function handleClose() {
  showModal.value = false
  store.clearErrors()
}

function handleSaved() {
  showModal.value = false
}
</script>