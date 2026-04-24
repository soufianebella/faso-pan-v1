<template>
  <div class="p-6 space-y-4 h-screen overflow-hidden flex flex-col">

    <!-- Header -->
    <div class="flex items-center justify-between flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold" style="color: #1B3B8A">
          Campagnes
        </h1>
        <p class="text-sm mt-0.5" style="color: #6B7280">
          {{ pagination.total }} campagne{{ pagination.total > 1 ? 's' : '' }} enregistrée{{ pagination.total > 1 ? 's' : '' }}
        </p>
      </div>

      <button
        @click="openCreate"
        class="flex items-center gap-2 px-4 py-2 rounded-lg
               text-white text-sm font-medium shadow-sm transition-colors"
        style="background-color: #F97316"
        @mouseenter="$event.target.style.backgroundColor='#EA6C0A'"
        @mouseleave="$event.target.style.backgroundColor='#F97316'"
      >
        <i class="fa-solid fa-plus"></i>
        Nouvelle campagne
      </button>
    </div>

    <!-- Recherche + filtres pills -->
    <div class="flex flex-col sm:flex-row gap-3 flex-shrink-0">

      <div class="relative flex-1">
        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs"
           style="color: #9CA3AF"></i>
        <input
          v-model="filtres.search"
          @input="debouncedFetch"
          type="text"
          placeholder="Rechercher une campagne ou un annonceur..."
          class="w-full pl-9 pr-4 py-2 border rounded-lg text-sm outline-none transition-all bg-white"
          style="border-color: #E5E7EB"
          @focus="$event.target.style.borderColor='#1B3B8A'"
          @blur="$event.target.style.borderColor='#E5E7EB'"
        />
      </div>

      <div class="flex items-center gap-1 bg-white border rounded-lg p-1 flex-shrink-0"
           style="border-color: #E5E7EB">
        <button
          v-for="f in FILTRES_STATUT"
          :key="f.value"
          @click="setStatut(f.value)"
          class="px-3 py-1.5 rounded-md text-xs font-medium transition-all"
          :style="filtres.statut === f.value
            ? 'background-color: #1B3B8A; color: #fff'
            : 'color: #6B7280'"
        >
          {{ f.label }}
        </button>
      </div>
    </div>

    <!-- Skeleton -->
    <div v-if="isLoading" class="space-y-2 flex-1">
      <div
        v-for="i in 6" :key="i"
        class="h-16 w-full rounded-lg animate-pulse"
        style="background-color: #F3F4F6"
      ></div>
    </div>

    <template v-else>
      <div
        class="flex-1 overflow-auto bg-white rounded-xl border"
        style="border-color: #E5E7EB"
      >
        <CampagneTable
          :campagnes="campagnes"
          @edit="openEdit"
          @archive="handleArchive"
          @delete="handleDelete"
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
import { ref, onMounted }       from 'vue'
import { storeToRefs }          from 'pinia'
import { useCampagnesStore }    from '@/stores/campagnes.store'
import CampagneTable            from '@/components/campagnes/CampagneTable.vue'
import CampagnePagination       from '@/components/campagnes/CampagnePagination.vue'
import CampagneModal            from '@/components/campagnes/CampagneModal.vue'
import ConfirmModal             from '@/components/ui/ConfirmModal.vue'
import { useToast }             from '@/composables/useToast'

const FILTRES_STATUT = [
  { label: 'Toutes',       value: '' },
  { label: 'Actives',      value: 'active' },
  { label: 'Préparation',  value: 'preparation' },
  { label: 'Expirées',     value: 'expiree' },
]

const store = useCampagnesStore()
const { campagnes, isLoading, pagination, filtres } = storeToRefs(store)

const showModal = ref(false)
const toast     = useToast()
const confirm   = ref({ show: false, title: '', message: '', variant: 'warning', label: 'Confirmer', action: null })
let debounceTimer = null

onMounted(() => store.fetchCampagnes())

function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => store.fetchCampagnes(1), 400)
}

function setStatut(val) {
  filtres.value.statut = val
  store.fetchCampagnes(1)
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

function handleArchive(id) {
  confirm.value = {
    show:    true,
    title:   'Clôturer la campagne',
    message: 'Cette campagne sera marquée comme expirée. Cette action est irréversible.',
    variant: 'warning',
    label:   'Clôturer',
    action:  async () => {
      try {
        await store.archiveCampagne(id)
        toast.success('Campagne clôturée.')
      } catch (err) {
        toast.error(err.response?.data?.message || 'Erreur lors de la clôture.')
      }
    },
  }
}

function handleDelete(campagne) {
  confirm.value = {
    show:    true,
    title:   'Supprimer définitivement',
    message: `La campagne « ${campagne.nom} » et son historique d'affectations seront supprimés. Cette action est irréversible.`,
    variant: 'danger',
    label:   'Supprimer',
    action:  async () => {
      try {
        await store.deleteCampagne(campagne.id)
        toast.success('Campagne supprimée.')
      } catch (err) {
        toast.error(err.response?.data?.message || 'Erreur lors de la suppression.')
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
}
</script>
