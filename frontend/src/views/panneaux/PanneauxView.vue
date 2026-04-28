<template>
  <div class="p-6 space-y-4 h-screen overflow-hidden flex flex-col">

    <!-- Header -->
    <div class="flex items-center justify-between flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold" style="color: #1B3B8A">Panneaux</h1>
        <p class="text-sm mt-0.5" style="color: #6B7280">
          {{ pagination.total }} panneau(x) au total
        </p>
      </div>

      <div class="flex items-center gap-3">
        <!-- Toggle Tableau / Carte -->
        <div class="flex rounded overflow-hidden border" style="border-color: #e5e7eb">
          <button
            @click="viewMode = 'tableau'"
            class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium transition-colors"
            :style="viewMode === 'tableau'
              ? 'background-color: #1B3B8A; color: #fff'
              : 'background-color: #fff; color: #6B7280'"
          >
            <i class="fa-solid fa-table-list"></i>
            Tableau
          </button>
          <button
            @click="viewMode = 'carte'"
            class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium transition-colors border-l"
            style="border-color: #e5e7eb"
            :style="viewMode === 'carte'
              ? 'background-color: #1B3B8A; color: #fff'
              : 'background-color: #fff; color: #6B7280'"
          >
            <i class="fa-solid fa-map-location-dot"></i>
            Carte
          </button>
        </div>

        <button
          @click="openCreate"
          class="flex items-center gap-2 px-4 py-2 rounded text-white text-sm font-medium shadow-sm transition-colors"
          style="background-color: #F97316"
          @mouseenter="$event.target.style.backgroundColor='#EA6C0A'"
          @mouseleave="$event.target.style.backgroundColor='#F97316'"
        >
          <i class="fa-solid fa-plus"></i>
          Nouveau panneau
        </button>
      </div>
    </div>

    <!-- ── Vue Tableau ────────────────────────────────────────────────────── -->
    <template v-if="viewMode === 'tableau'">

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
        <PanneauTable
          :panneaux="panneaux"
          class="flex-1 overflow-auto"
          @detail="(id) => router.push({ name: 'panneau-detail', params: { id } })"
          @edit="openEdit"
          @archive="handleArchive"
          @changer-statut="openStatutModal"
        />

        <PanneauPagination
          :pagination="pagination"
          @page-change="store.fetchPanneaux"
        />
      </template>

    </template>

    <!-- ── Vue Carte ─────────────────────────────────────────────────────── -->
    <div v-else class="flex-1 overflow-hidden rounded shadow-sm -mx-0">
      <PanneauxMapView
        @open-panneau="openEdit"
      />
    </div>

    <!-- ── Modals ─────────────────────────────────────────────────────────── -->
    <PanneauModal
      :show="showModal"
      :panneau="panneauActuel"
      :errors="errors"
      @close="handleClose"
      @saved="handleSaved"
    />

    <StatutModal
      :show="showStatutModal"
      :panneau="panneauPourStatut"
      @close="showStatutModal = false"
      @saved="handleStatutSaved"
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
import { ref, onMounted }      from 'vue'
import { storeToRefs }         from 'pinia'
import { useRouter }           from 'vue-router'
import { usePanneauxStore }    from '@/stores/panneaux.store'
import PanneauTable            from '@/components/panneaux/PanneauTable.vue'
import PanneauPagination       from '@/components/panneaux/PanneauPagination.vue'
import PanneauModal            from '@/components/panneaux/PanneauModal.vue'
import StatutModal             from '@/components/panneaux/StatutModal.vue'
import PanneauxMapView         from '@/views/panneaux/PanneauxMapView.vue'
import ConfirmModal            from '@/components/ui/ConfirmModal.vue'
import { useToast }            from '@/composables/useToast'

const store  = usePanneauxStore()
const router = useRouter()

const {
  panneaux,
  isLoading,
  pagination,
  panneauActuel,
  errors,
} = storeToRefs(store)

// ── State local ────────────────────────────────────────────────────────────
const showModal      = ref(false)
const showStatutModal = ref(false)
const panneauPourStatut = ref(null)
const viewMode       = ref('tableau')   // 'tableau' | 'carte'
const toast          = useToast()
const confirm        = ref({
  show: false, title: '', message: '', variant: 'danger', label: 'Confirmer', action: null,
})

onMounted(() => store.fetchPanneaux())

// ── Handlers modal principale ──────────────────────────────────────────────
function openCreate() {
  store.setPanneauActuel({
    reference:   '',
    ville:       '',
    quartier:    '',
    latitude:    null,
    longitude:   null,
    eclaire:     false,
    statut:      'actif',
    faces:       [{ numero: 1, largeur: 0, hauteur: 0 }],
  })
  showModal.value = true
}

function openEdit(panneau) {
  store.setPanneauActuel({
    ...panneau,
    faces: panneau.faces ? [...panneau.faces] : [],
  })
  showModal.value = true
}

function handleClose() {
  showModal.value = false
  store.clearErrors()
}

function handleSaved() {
  showModal.value = false
  store.fetchPanneaux(pagination.value.currentPage)
  toast.success('Panneau enregistré.')
}

// ── Handlers modal statut ──────────────────────────────────────────────────
function openStatutModal(panneau) {
  panneauPourStatut.value = panneau
  showStatutModal.value   = true
}

function handleStatutSaved() {
  showStatutModal.value = false
  // La liste locale a déjà été mise à jour dans le store
}

// ── Archive ────────────────────────────────────────────────────────────────
function handleArchive(panneauId) {
  confirm.value = {
    show:    true,
    title:   'Archiver le panneau',
    message: 'Ce panneau sera marqué hors service. Confirmer ?',
    variant: 'danger',
    label:   'Archiver',
    action:  async () => {
      try {
        await store.archivePanneau(panneauId)
        toast.success('Panneau archivé.')
      } catch (err) {
        toast.error(err.response?.data?.message || "Erreur lors de l'archivage.")
      }
    },
  }
}
</script>
