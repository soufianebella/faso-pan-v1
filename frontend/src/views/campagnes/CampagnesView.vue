<template>
  <div class="p-6 space-y-4 h-screen overflow-hidden flex flex-col">

    <!-- Header -->
    <div class="flex items-center justify-between flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold" style="color: #1B3B8A">
          Campagnes Publicitaires
        </h1>
        <p class="text-sm mt-0.5" style="color: #6B7280">
          {{ pagination.total }} campagnes enregistrees
        </p>
      </div>

      <button
        @click="openCreate"
        class="flex items-center gap-2 px-4 py-2 rounded
               text-white text-sm font-medium shadow-sm transition-colors"
        style="background-color: #F97316"
        @mouseenter="$event.target.style.backgroundColor='#EA6C0A'"
        @mouseleave="$event.target.style.backgroundColor='#F97316'"
      >
        <i class="fa-solid fa-bullhorn"></i>
        Nouvelle campagne
      </button>
    </div>

    <!-- Filtres -->
    <div
      class="flex gap-3 p-3 rounded-lg border flex-shrink-0 bg-white"
      style="border-color: #E5E7EB"
    >
      <input
        v-model="filtres.search"
        @input="debouncedFetch"
        type="text"
        placeholder="Rechercher un annonceur ou une campagne..."
        class="flex-1 border rounded px-3 py-2 text-sm outline-none transition-all"
        style="border-color: #E5E7EB"
        @focus="$event.target.style.borderColor='#F97316'"
        @blur="$event.target.style.borderColor='#E5E7EB'"
      />
      <select
        v-model="filtres.statut"
        @change="store.fetchCampagnes(1)"
        class="border rounded px-3 py-2 text-sm outline-none bg-white"
        style="border-color: #E5E7EB"
      >
        <option value="">Tous les statuts</option>
        <option value="preparation">En preparation</option>
        <option value="active">Active</option>
        <option value="expiree">Expiree</option>
      </select>
    </div>

    <!-- Skeleton loader -->
    <div v-if="isLoading" class="space-y-3 flex-1">
      <div
        v-for="i in 6"
        :key="i"
        class="h-16 w-full rounded animate-pulse"
        style="background-color: #E5E7EB"
      ></div>
    </div>

    <template v-else>
      <div
        class="flex-1 overflow-auto bg-white rounded-lg border"
        style="border-color: #E5E7EB"
      >
        <CampagneTable
          :campagnes="campagnes"
          @edit="openEdit"
          @archive="handleArchive"
        />
      </div>

      <CampagnePagination
        :pagination="pagination"
        @page-change="store.fetchCampagnes"
      />
    </template>

    <CampagneModal
      :show="showModal"
      @close="handleClose"
      @saved="handleSaved"
    />

  </div>
</template>

<script setup>
import { ref, onMounted }       from 'vue'
import { storeToRefs }          from 'pinia'
import { useCampagnesStore }    from '@/stores/campagnes.store'
import CampagneTable            from '@/components/campagnes/CampagneTable.vue'
import CampagnePagination       from '@/components/campagnes/CampagnePagination.vue'
import CampagneModal            from '@/components/campagnes/CampagneModal.vue'

const store = useCampagnesStore()

const {
  campagnes,
  isLoading,
  pagination,
  filtres,
} = storeToRefs(store)

const showModal = ref(false)
let debounceTimer = null

onMounted(() => store.fetchCampagnes())

function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => store.fetchCampagnes(1), 500)
}

function openCreate() {
  store.setCampagneActuelle(null)
  store.resetFacesDisponibles()
  showModal.value = true
}

function openEdit(campagne) {
  store.setCampagneActuelle({ ...campagne })
  showModal.value = true
}

async function handleArchive(id) {
  if (!confirm('Confirmer la cloture de cette campagne ?')) return
  try {
    await store.archiveCampagne(id)
  } catch (err) {
    alert(err.response?.data?.message || 'Erreur lors de la cloture.')
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