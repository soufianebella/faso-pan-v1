<template>
  <div class="flex flex-col h-screen overflow-hidden" style="background-color: #F0F4FF">
    <div class="flex-1 overflow-y-auto">
      <div class="max-w-screen-xl mx-auto px-6 py-6 space-y-5">

        <!-- ── En-tête ──────────────────────────────────────────────────── -->
        <div class="flex items-start justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold tracking-tight" style="color: #1C2833">
              Campagnes
            </h1>
            <p class="text-sm mt-0.5" style="color: #6B7280">
              Gérez vos campagnes publicitaires et suivez leur performance
            </p>
          </div>
          <button
            @click="openCreate"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl
                   text-white text-sm font-semibold flex-shrink-0 transition-colors"
            style="background-color: #F97316"
            @mouseenter="$event.currentTarget.style.backgroundColor = '#EA6C0A'"
            @mouseleave="$event.currentTarget.style.backgroundColor = '#F97316'"
          >
            <i class="fa-solid fa-plus text-xs"></i>
            Nouvelle campagne
          </button>
        </div>

        <!-- ── KPI row ──────────────────────────────────────────────────── -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <button
            v-for="kpi in KPI_CARDS"
            :key="kpi.statut"
            @click="setStatut(kpi.statut)"
            class="relative text-left bg-white rounded-2xl p-5 overflow-hidden
                   transition-all duration-150 border group"
            :style="kpiCardStyle(kpi)"
          >
            <!-- Barre top colorée (4px) -->
            <div
              class="absolute top-0 left-0 right-0 h-1 transition-all duration-150"
              :style="{ backgroundColor: kpi.color }"
            ></div>

            <div class="flex items-start justify-between gap-3">
              <!-- Chiffre + label -->
              <div>
                <p
                  class="text-3xl font-bold tabular-nums leading-none"
                  :style="{ color: kpi.color }"
                >
                  {{ kpi.count }}
                </p>
                <p class="text-sm font-medium mt-1.5" style="color: #1C2833">
                  {{ kpi.label }}
                </p>
                <p class="text-xs mt-0.5" style="color: #6B7280">
                  {{ kpiPct(kpi.count) }}% du total
                </p>
              </div>

              <!-- Icône -->
              <div
                class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                :style="{ backgroundColor: kpi.bgLight }"
              >
                <i class="fa-solid text-sm" :class="kpi.icon" :style="{ color: kpi.color }"></i>
              </div>
            </div>
          </button>
        </div>

        <!-- ── Barre filtres fusionnée ───────────────────────────────────── -->
        <div
          class="flex items-stretch bg-white rounded-xl overflow-hidden"
          style="border: 1px solid #E5E7EB; box-shadow: 0 1px 3px rgba(0,0,0,0.05)"
        >
          <!-- Recherche -->
          <div class="relative flex-1 border-r" style="border-color: #F3F4F6">
            <i
              class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-xs"
              style="color: #9CA3AF"
            ></i>
            <input
              v-model="filtres.search"
              @input="debouncedFetch"
              type="text"
              placeholder="Rechercher par nom, annonceur…"
              class="w-full h-full pl-10 pr-4 py-3 text-sm outline-none bg-transparent"
              style="color: #1C2833"
              @focus="$event.target.closest('div').style.backgroundColor = '#FFF7F0'"
              @blur="$event.target.closest('div').style.backgroundColor = ''"
            />
          </div>

          <!-- Onglets statut -->
          <div class="flex items-stretch overflow-x-auto">
            <button
              v-for="tab in FILTER_TABS"
              :key="tab.value"
              @click="setStatut(tab.value)"
              class="relative flex items-center gap-2 px-4 py-3 text-sm font-medium
                     whitespace-nowrap transition-all duration-150 border-r"
              style="border-color: #F3F4F6"
              :style="filtres.statut === tab.value
                ? 'background-color: #1B3B8A; color: #FFFFFF'
                : 'color: #6B7280'"
            >
              {{ tab.label }}

              <!-- Badge count coloré par statut -->
              <span
                class="text-xs px-1.5 py-0.5 rounded-md font-semibold tabular-nums"
                :style="tabBadgeStyle(tab)"
              >
                {{ countForStatut(tab.value) }}
              </span>
            </button>
          </div>

          <!-- Toggle grille / liste -->
          <div class="flex items-stretch flex-shrink-0">
            <button
              @click="setViewMode('grille')"
              class="w-11 flex items-center justify-center transition-colors border-r"
              style="border-color: #F3F4F6"
              :style="viewMode === 'grille'
                ? 'background-color: #1B3B8A; color: #FFFFFF'
                : 'background-color: #FFFFFF; color: #9CA3AF'"
              title="Vue grille"
            >
              <i class="fa-solid fa-grip text-sm"></i>
            </button>
            <button
              @click="setViewMode('liste')"
              class="w-11 flex items-center justify-center transition-colors"
              :style="viewMode === 'liste'
                ? 'background-color: #1B3B8A; color: #FFFFFF'
                : 'background-color: #FFFFFF; color: #9CA3AF'"
              title="Vue liste"
            >
              <i class="fa-solid fa-list text-sm"></i>
            </button>
          </div>
        </div>

        <!-- ── Skeleton ─────────────────────────────────────────────────── -->
        <div v-if="isLoading">
          <!-- Skeleton grille -->
          <div v-if="viewMode === 'grille'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div
              v-for="i in 6" :key="i"
              class="rounded-2xl animate-pulse"
              style="background-color: #E5E7EB; height: 270px"
            ></div>
          </div>
          <!-- Skeleton liste -->
          <div v-else class="bg-white rounded-2xl border overflow-hidden" style="border-color: #E5E7EB">
            <div class="h-11" style="background-color: #1B3B8A"></div>
            <div v-for="i in 6" :key="i" class="flex gap-4 px-5 py-4 border-b" style="border-color: #F3F4F6">
              <div class="h-4 rounded animate-pulse flex-1" style="background-color: #F3F4F6"></div>
              <div class="h-4 rounded animate-pulse w-24" style="background-color: #F3F4F6"></div>
              <div class="h-4 rounded animate-pulse w-20" style="background-color: #F3F4F6"></div>
            </div>
          </div>
        </div>

        <!-- ── Vue grille ───────────────────────────────────────────────── -->
        <template v-else>
          <div
            v-if="viewMode === 'grille'"
            class="bg-white rounded-2xl border overflow-hidden"
            style="border-color: #E5E7EB; box-shadow: 0 1px 3px rgba(0,0,0,0.04)"
          >
            <CampagneGrid
              :campagnes="campagnes"
              @detail="openDetail"
              @edit="openEdit"
              @archive="handleArchive"
              @delete="handleDelete"
            />
          </div>

          <!-- ── Vue liste ─────────────────────────────────────────────── -->
          <div
            v-else
            class="bg-white rounded-2xl border overflow-hidden"
            style="border-color: #E5E7EB; box-shadow: 0 1px 3px rgba(0,0,0,0.04)"
          >
            <CampagneListe
              :campagnes="campagnes"
              @detail="openDetail"
              @edit="openEdit"
              @archive="handleArchive"
              @delete="handleDelete"
            />
          </div>

          <!-- Pagination -->
          <CampagnePagination
            :pagination="pagination"
            @page-change="store.fetchCampagnes"
          />
        </template>

      </div>
    </div>

    <!-- ── Modales & Drawer ─────────────────────────────────────────────── -->
    <CampagneModal
      :show="showModal"
      @close="handleClose"
      @saved="handleSaved"
    />

    <CampagneDetailDrawer
      :campagne="detailCampagne"
      @close="detailCampagne = null"
      @edit="openEditFromDrawer"
      @archive="handleArchiveFromDrawer"
      @delete="handleDeleteFromDrawer"
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
import { ref, computed, onMounted } from 'vue'
import { storeToRefs }              from 'pinia'
import { useCampagnesStore }        from '@/stores/campagnes.store'
import CampagneGrid                 from '@/components/campagnes/CampagneGrid.vue'
import CampagneListe                from '@/components/campagnes/CampagneListe.vue'
import CampagnePagination           from '@/components/campagnes/CampagnePagination.vue'
import CampagneModal                from '@/components/campagnes/CampagneModal.vue'
import CampagneDetailDrawer         from '@/components/campagnes/CampagneDetailDrawer.vue'
import ConfirmModal                 from '@/components/ui/ConfirmModal.vue'
import { useToast }                 from '@/composables/useToast'

// ── Constantes ──────────────────────────────────────────────────────────────
const FILTER_TABS = [
  { value: '',            label: 'Toutes',       color: '#1B3B8A', bgLight: '#EBF3FC' },
  { value: 'active',      label: 'Actives',      color: '#27AE60', bgLight: '#D1FAE5' },
  { value: 'preparation', label: 'Préparation',  color: '#F97316', bgLight: '#FEF3DC' },
  { value: 'expiree',     label: 'Expirées',     color: '#EF4444', bgLight: '#FEE2E2' },
]

// ── Store ────────────────────────────────────────────────────────────────────
const store = useCampagnesStore()
const { campagnes, isLoading, pagination, filtres, counts } = storeToRefs(store)

// ── État local ───────────────────────────────────────────────────────────────
const showModal      = ref(false)
const detailCampagne = ref(null)
const toast          = useToast()
const viewMode       = ref(localStorage.getItem('campagnes_vue_mode') ?? 'grille')
const confirm        = ref({
  show: false, title: '', message: '', variant: 'warning', label: 'Confirmer', action: null,
})
let debounceTimer = null

onMounted(() => store.fetchCampagnes())

// ── Toggle vue ───────────────────────────────────────────────────────────────
function setViewMode(mode) {
  viewMode.value = mode
  localStorage.setItem('campagnes_vue_mode', mode)
}

// ── KPI cards ────────────────────────────────────────────────────────────────
const KPI_CARDS = computed(() => [
  {
    statut:  '',
    label:   'Total campagnes',
    count:   counts.value.total,
    color:   '#1B3B8A',
    bgLight: '#EBF3FC',
    icon:    'fa-bullhorn',
  },
  {
    statut:  'active',
    label:   'Actives',
    count:   counts.value.active,
    color:   '#27AE60',
    bgLight: '#D1FAE5',
    icon:    'fa-circle-check',
  },
  {
    statut:  'preparation',
    label:   'En préparation',
    count:   counts.value.preparation,
    color:   '#F97316',
    bgLight: '#FEF3DC',
    icon:    'fa-hourglass-half',
  },
  {
    statut:  'expiree',
    label:   'Expirées',
    count:   counts.value.expiree,
    color:   '#EF4444',
    bgLight: '#FEE2E2',
    icon:    'fa-calendar-xmark',
  },
])

function kpiPct(count) {
  const t = counts.value.total
  return t ? Math.round((count / t) * 100) : 0
}

function kpiCardStyle(kpi) {
  const isActive = filtres.value.statut === kpi.statut
  return isActive
    ? `border-color: ${kpi.color}; box-shadow: 0 0 0 3px ${kpi.color}22`
    : 'border-color: #E5E7EB; box-shadow: 0 1px 2px rgba(0,0,0,0.04)'
}

// ── Filtres ──────────────────────────────────────────────────────────────────
function countForStatut(val) {
  const map = {
    '':           counts.value.total,
    active:       counts.value.active,
    preparation:  counts.value.preparation,
    expiree:      counts.value.expiree,
  }
  return map[val] ?? 0
}

function tabBadgeStyle(tab) {
  const isActive = filtres.value.statut === tab.value
  if (isActive) return { backgroundColor: 'rgba(255,255,255,0.2)', color: '#FFFFFF' }
  return { backgroundColor: tab.bgLight, color: tab.color }
}

function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => store.fetchCampagnes(1), 400)
}

function setStatut(val) {
  filtres.value.statut = val
  store.fetchCampagnes(1)
}

// ── Création / édition ───────────────────────────────────────────────────────
function openCreate() {
  store.setCampagneActuelle(null)
  store.resetFacesDisponibles()
  showModal.value = true
}

function openEdit(campagne) {
  store.setCampagneActuelle({ ...campagne })
  showModal.value = true
}

function openEditFromDrawer(campagne) {
  detailCampagne.value = null
  openEdit(campagne)
}

// ── Détail ───────────────────────────────────────────────────────────────────
function openDetail(campagne) {
  detailCampagne.value = campagne
}

// ── Clôturer ─────────────────────────────────────────────────────────────────
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

function handleArchiveFromDrawer(id) {
  detailCampagne.value = null
  handleArchive(id)
}

// ── Supprimer ─────────────────────────────────────────────────────────────────
function handleDelete(campagne) {
  confirm.value = {
    show:    true,
    title:   'Supprimer définitivement',
    message: `La campagne « ${campagne.nom} » et ses affectations seront supprimées. Cette action est irréversible.`,
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

function handleDeleteFromDrawer(campagne) {
  detailCampagne.value = null
  handleDelete(campagne)
}

// ── Modal ─────────────────────────────────────────────────────────────────────
function handleClose() {
  showModal.value = false
  store.clearErrors()
}

function handleSaved() {
  showModal.value = false
}
</script>
