<template>
  <Teleport to="body">

    <!-- ── Overlay ──────────────────────────────────────────────────────── -->
    <Transition name="backdrop">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-40"
        style="background-color: rgba(0,0,0,0.30)"
        @click="emit('close')"
      ></div>
    </Transition>

    <!-- ── Drawer w-96 ───────────────────────────────────────────────────── -->
    <Transition name="drawer">
      <div
        v-if="isOpen"
        class="fixed top-0 right-0 h-screen z-50 flex flex-col bg-white overflow-hidden"
        style="width: 24rem; box-shadow: -8px 0 32px rgba(0,0,0,0.15)"
      >

        <!-- ════════════════════════════════════════════════════════════════
             HEADER FIXE — fond #1B3B8A
             ════════════════════════════════════════════════════════════════ -->
        <header class="flex-shrink-0 px-5 pt-4 pb-5" style="background-color: #1B3B8A">

          <!-- Label "CAMPAGNE" + bouton fermer -->
          <div class="flex items-center justify-between mb-3">
            <span
              class="text-xs font-bold uppercase tracking-widest"
              style="color: #F97316"
            >
              Campagne
            </span>
            <button
              @click="emit('close')"
              class="w-8 h-8 rounded-full flex items-center justify-center
                     text-xl transition-colors"
              style="color: rgba(255,255,255,0.65)"
              @mouseenter="$event.currentTarget.style.color = '#FFFFFF';
                           $event.currentTarget.style.backgroundColor = 'rgba(255,255,255,0.15)'"
              @mouseleave="$event.currentTarget.style.color = 'rgba(255,255,255,0.65)';
                           $event.currentTarget.style.backgroundColor = ''"
            >
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <!-- Titre (truncate 1 ligne) -->
          <h2 class="text-lg font-bold text-white leading-snug truncate">
            {{ campagne?.nom }}
          </h2>

          <!-- Badge statut sous le titre -->
          <div class="mt-2.5">
            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full
                     text-xs font-bold uppercase tracking-wide"
              :style="badgeStyle"
            >
              <span
                class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                :style="{ backgroundColor: accentColor }"
              ></span>
              {{ badgeLabel }}
            </span>
          </div>
        </header>

        <!-- ════════════════════════════════════════════════════════════════
             CORPS SCROLLABLE
             ════════════════════════════════════════════════════════════════ -->
        <div class="flex-1 overflow-y-auto bg-white">

          <!-- ── Section 1 : Infos générales ──────────────────────────── -->
          <section class="px-5 pt-5 pb-4 space-y-3 border-b" style="border-color: #F3F4F6">

            <!-- Annonceur -->
            <div class="flex items-center gap-3">
              <div
                class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                style="background-color: #EBF3FC"
              >
                <i class="fa-solid fa-building text-xs" style="color: #1B3B8A"></i>
              </div>
              <div class="min-w-0">
                <p class="text-xs" style="color: #9CA3AF">Annonceur</p>
                <p class="text-sm font-semibold truncate mt-0.5" style="color: #1C2833">
                  {{ campagne?.annonceur }}
                </p>
              </div>
            </div>

            <!-- Dates + badge durée -->
            <div class="flex items-center gap-3">
              <div
                class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                style="background-color: #EBF3FC"
              >
                <i class="fa-solid fa-calendar-days text-xs" style="color: #1B3B8A"></i>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs" style="color: #9CA3AF">Période</p>
                <p class="text-sm font-semibold mt-0.5" style="color: #1C2833">
                  {{ campagne?.date_debut }} → {{ campagne?.date_fin }}
                </p>
              </div>
              <span
                v-if="duree"
                class="flex-shrink-0 px-2 py-1 rounded-lg text-xs font-semibold"
                style="background-color: #F3F4F6; color: #6B7280"
              >
                {{ duree }} jours
              </span>
            </div>

            <!-- Description (si présente) -->
            <p
              v-if="campagne?.description"
              class="text-xs rounded-xl p-3 leading-relaxed"
              style="background-color: #F9FAFB; color: #374151; border: 1px solid #E5E7EB"
            >
              {{ campagne.description }}
            </p>

          </section>

          <!-- ── Section 2 : KPIs ──────────────────────────────────────── -->
          <section class="px-5 py-4 border-b space-y-3" style="border-color: #F3F4F6">

            <!-- Grille 2 colonnes : Faces + Panneaux -->
            <div class="grid grid-cols-2 gap-3">

              <!-- Faces réservées -->
              <div class="rounded-xl border p-4" style="border-color: #E5E7EB">
                <i class="fa-solid fa-layer-group text-xs" style="color: #1B3B8A"></i>
                <p class="text-2xl font-bold mt-2 tabular-nums" style="color: #1B3B8A">
                  {{ campagne?.affectations_count || 0 }}
                </p>
                <p class="text-xs mt-0.5" style="color: #6B7280">
                  Face{{ (campagne?.affectations_count || 0) !== 1 ? 's' : '' }} réservée{{ (campagne?.affectations_count || 0) !== 1 ? 's' : '' }}
                </p>
              </div>

              <!-- Panneaux concernés -->
              <div class="rounded-xl border p-4" style="border-color: #E5E7EB">
                <i class="fa-solid fa-sign-hanging text-xs" style="color: #1B3B8A"></i>
                <p class="text-2xl font-bold mt-2 tabular-nums" style="color: #1B3B8A">
                  {{ panneauxCount }}
                </p>
                <p class="text-xs mt-0.5" style="color: #6B7280">
                  Panneau{{ panneauxCount !== 1 ? 'x' : '' }} concerné{{ panneauxCount !== 1 ? 's' : '' }}
                </p>
              </div>

            </div>

            <!-- Progression pleine largeur -->
            <div class="rounded-xl border p-4" style="border-color: #E5E7EB">
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium" style="color: #6B7280">Progression</span>
                <span class="text-sm font-bold tabular-nums" style="color: #F97316">
                  {{ progress }}%
                </span>
              </div>
              <div
                class="rounded-full overflow-hidden"
                style="height: 8px; background-color: #F3F4F6"
              >
                <div
                  class="h-full rounded-full transition-all duration-700"
                  style="background-color: #F97316"
                  :style="{ width: progress + '%' }"
                ></div>
              </div>
              <p
                v-if="joursRestants !== null"
                class="text-xs mt-2 flex items-center gap-1"
                style="color: #6B7280"
              >
                <template v-if="joursRestants > 0">
                  <i class="fa-solid fa-clock" style="color: #F97316"></i>
                  J-{{ joursRestants }} avant expiration
                </template>
                <template v-else>
                  <i class="fa-solid fa-circle-check" style="color: #27AE60"></i>
                  Campagne terminée
                </template>
              </p>
            </div>

          </section>

          <!-- ── Section 3 : Affectations ──────────────────────────────── -->
          <section class="px-5 py-4">

            <!-- Titre section -->
            <div class="flex items-center gap-2 mb-4">
              <span
                class="text-xs font-bold uppercase tracking-wider"
                style="color: #6B7280"
              >
                Affectations
              </span>
              <div class="flex-1 border-t" style="border-color: #F3F4F6"></div>
              <span
                class="text-xs font-semibold px-1.5 py-0.5 rounded-md"
                style="background-color: #F3F4F6; color: #9CA3AF"
              >
                {{ detail?.affectations?.length ?? '—' }}
              </span>
            </div>

            <!-- Skeleton chargement -->
            <div v-if="isLoading" class="space-y-2">
              <div
                v-for="i in 3" :key="i"
                class="h-16 rounded-xl animate-pulse"
                style="background-color: #F3F4F6"
              ></div>
            </div>

            <!-- État vide -->
            <div
              v-else-if="!detail?.affectations?.length"
              class="py-10 text-center"
            >
              <i
                class="fa-solid fa-layer-group text-3xl block mb-2"
                style="color: #E5E7EB"
              ></i>
              <p class="text-sm" style="color: #9CA3AF">Aucune face affectée</p>
            </div>

            <!-- Liste des affectations -->
            <div v-else class="space-y-2 pb-4">
              <div
                v-for="aff in detail.affectations"
                :key="aff.id"
                class="rounded-xl border p-3"
                style="border-color: #E5E7EB"
              >
                <div class="flex items-start gap-3">

                  <!-- Avatar F1/F2 orange -->
                  <div
                    class="w-9 h-9 rounded-xl flex items-center justify-center
                           flex-shrink-0 text-white text-xs font-bold"
                    style="background-color: #F97316"
                  >
                    F{{ aff.face?.numero ?? '?' }}
                  </div>

                  <!-- Infos panneau -->
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold truncate" style="color: #1C2833">
                      {{ aff.face?.panneau?.reference ?? 'Panneau inconnu' }}
                    </p>
                    <p class="text-xs mt-0.5" style="color: #6B7280">
                      {{ aff.face?.panneau?.ville }}{{ aff.face?.panneau?.quartier ? ` · ${aff.face.panneau.quartier}` : '' }}
                    </p>
                    <p class="text-xs mt-0.5" style="color: #9CA3AF">
                      {{ aff.date_debut }} → {{ aff.date_fin }}
                    </p>
                  </div>

                  <!-- Badge statut tâche -->
                  <span
                    v-if="aff.tache"
                    class="flex-shrink-0 px-2 py-0.5 rounded-full text-xs font-bold uppercase"
                    :style="tacheStatutStyle(aff.tache.statut)"
                  >
                    {{ TACHE_LABELS[aff.tache.statut] ?? aff.tache.statut }}
                  </span>
                  <span
                    v-else
                    class="flex-shrink-0 text-xs px-2 py-0.5 rounded-full"
                    style="background-color: #F3F4F6; color: #9CA3AF"
                  >
                    Non assignée
                  </span>

                </div>
              </div>
            </div>

          </section>
        </div>

        <!-- ════════════════════════════════════════════════════════════════
             FOOTER FIXE — 2 lignes hiérarchisées
             ════════════════════════════════════════════════════════════════ -->
        <footer
          class="flex-shrink-0 px-5 py-4 border-t bg-white space-y-2"
          style="border-color: #E5E7EB"
        >

          <!-- Ligne 1 — Modifier (pleine largeur, orange) -->
          <button
            @click="emit('edit', campagne)"
            class="w-full flex items-center justify-center gap-2 py-3 rounded-xl
                   text-sm font-semibold text-white transition-colors"
            style="background-color: #F97316"
            @mouseenter="$event.currentTarget.style.backgroundColor = '#EA6C0A'"
            @mouseleave="$event.currentTarget.style.backgroundColor = '#F97316'"
          >
            <i class="fa-solid fa-pen-to-square text-xs"></i>
            Modifier
          </button>

          <!-- Ligne 2 — Voir tâches + Clôturer/Supprimer -->
          <div class="flex items-center gap-2">

            <!-- Voir les tâches — outline navy -->
            <button
              @click="voirTaches"
              class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-xl
                     text-xs font-semibold border transition-colors"
              style="border-color: #1B3B8A; color: #1B3B8A; background-color: transparent"
              @mouseenter="$event.currentTarget.style.backgroundColor = '#EBF3FC'"
              @mouseleave="$event.currentTarget.style.backgroundColor = 'transparent'"
            >
              <i class="fa-solid fa-list-check text-[10px]"></i>
              Voir les tâches
            </button>

            <!-- Clôturer — outline rouge (non expirées) -->
            <button
              v-if="campagne?.statut !== 'expiree'"
              @click="emit('archive', campagne.id)"
              class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-xl
                     text-xs font-semibold border transition-colors"
              style="border-color: #EF4444; color: #EF4444; background-color: transparent"
              @mouseenter="$event.currentTarget.style.backgroundColor = '#FEF2F2'"
              @mouseleave="$event.currentTarget.style.backgroundColor = 'transparent'"
            >
              <i class="fa-solid fa-circle-stop text-[10px]"></i>
              Clôturer
            </button>

            <!-- Supprimer — outline rouge (expirées) -->
            <button
              v-else
              @click="emit('delete', campagne)"
              class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-xl
                     text-xs font-semibold border transition-colors"
              style="border-color: #EF4444; color: #EF4444; background-color: transparent"
              @mouseenter="$event.currentTarget.style.backgroundColor = '#FEF2F2'"
              @mouseleave="$event.currentTarget.style.backgroundColor = 'transparent'"
            >
              <i class="fa-solid fa-trash-can text-[10px]"></i>
              Supprimer
            </button>

          </div>
        </footer>

      </div>
    </Transition>

  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRouter }      from 'vue-router'
import { campagnesApi }   from '@/api/campagnes.api'
import { useTachesStore } from '@/stores/taches.store'

// ── Props & emits ─────────────────────────────────────────────────────────────
const props = defineProps({
  campagne: { type: Object,  default: null },
  show:     { type: Boolean, default: null },   // null → dérivé de campagne
})

const emit = defineEmits(['close', 'edit', 'archive', 'delete'])

// Visibilité : si `show` est explicitement false → fermé ; sinon → !!campagne
const isOpen = computed(() =>
  props.show === false ? false : !!props.campagne
)

// ── Fermeture Échap ────────────────────────────────────────────────────────────
function handleKeydown(e) {
  if (e.key === 'Escape' && isOpen.value) emit('close')
}
onMounted(()  => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))

// ── Navigation vers les tâches filtrées ───────────────────────────────────────
const router      = useRouter()
const tachesStore = useTachesStore()

function voirTaches() {
  if (!props.campagne) return
  tachesStore.filtres.campagne_id = String(props.campagne.id)
  emit('close')
  router.push('/taches')
}

// ── Chargement du détail (affectations) ───────────────────────────────────────
const detail    = ref(null)
const isLoading = ref(false)

watch(
  () => props.campagne?.id,
  async (id) => {
    detail.value = null
    if (!id) return
    isLoading.value = true
    try {
      const res    = await campagnesApi.getById(id)
      detail.value = res.data ?? res
    } catch {
      // Echec silencieux — infos de base restent visibles
    } finally {
      isLoading.value = false
    }
  },
  { immediate: true }
)

// ── Utilitaires dates ─────────────────────────────────────────────────────────
function parseDateFR(str) {
  if (!str) return null
  const parts = str.split('/')
  if (parts.length === 3) return new Date(+parts[2], +parts[1] - 1, +parts[0])
  return new Date(str)
}

// Durée totale de la campagne en jours
const duree = computed(() => {
  const debut = parseDateFR(props.campagne?.date_debut)
  const fin   = parseDateFR(props.campagne?.date_fin)
  if (!debut || !fin) return null
  return Math.ceil((fin - debut) / 86400000)
})

// ── Progression ───────────────────────────────────────────────────────────────
const progress = computed(() => {
  const c = props.campagne
  if (!c) return 0
  if (c.statut === 'expiree')     return 100
  if (c.statut === 'preparation') return 0
  const debut = parseDateFR(c.date_debut)
  const fin   = parseDateFR(c.date_fin)
  if (!debut || !fin) return 0
  const total = fin - debut; const elapsed = new Date() - debut
  if (total <= 0) return 100
  return Math.min(100, Math.max(0, Math.round((elapsed / total) * 100)))
})

const joursRestants = computed(() => {
  const c = props.campagne
  if (!c || c.statut !== 'active') return null
  const fin = parseDateFR(c.date_fin)
  if (!fin) return null
  const now = new Date(); now.setHours(0,0,0,0); fin.setHours(0,0,0,0)
  return Math.ceil((fin - now) / 86400000)
})

// ── Couleur accent par statut ─────────────────────────────────────────────────
const ACCENT_COLORS = {
  preparation: '#F97316',
  active:      '#27AE60',
  expiree:     '#EF4444',
}

const accentColor = computed(() =>
  ACCENT_COLORS[props.campagne?.statut] ?? '#9CA3AF'
)

// ── Badge statut ──────────────────────────────────────────────────────────────
const badgeLabel = computed(() => {
  const c = props.campagne
  const j = joursRestants.value
  if (!c) return ''
  if (c.statut === 'active' && j !== null && j <= 7) return `Expire J-${j}`
  return {
    preparation: 'En préparation',
    active:      'Active',
    expiree:     'Expirée',
  }[c.statut] ?? c.statut
})

const badgeStyle = computed(() => {
  const c = props.campagne
  const j = joursRestants.value
  if (!c) return {}
  if (c.statut === 'active' && j !== null && j <= 7) {
    return { backgroundColor: 'rgba(254,243,220,0.9)', color: '#F97316' }
  }
  return {
    preparation: { backgroundColor: 'rgba(235,243,252,0.9)', color: '#93C5FD' },
    active:      { backgroundColor: 'rgba(209,250,229,0.9)', color: '#6EE7B7' },
    expiree:     { backgroundColor: 'rgba(255,255,255,0.15)', color: 'rgba(255,255,255,0.7)' },
  }[c.statut] ?? { backgroundColor: 'rgba(255,255,255,0.15)', color: 'rgba(255,255,255,0.7)' }
})

// ── Comptage panneaux uniques ─────────────────────────────────────────────────
const panneauxCount = computed(() => {
  const affs = detail.value?.affectations
  if (!affs?.length) return 0
  return new Set(affs.map(a => a.face?.panneau?.id).filter(Boolean)).size
})

// ── Styles tâches ─────────────────────────────────────────────────────────────
const TACHE_LABELS = {
  en_attente: 'En attente',
  en_cours:   'En cours',
  realisee:   'Réalisée',
  validee:    'Validée',
}

const TACHE_STYLES = {
  en_attente: { backgroundColor: '#EBF3FC', color: '#1B3B8A' },
  en_cours:   { backgroundColor: '#FEF3DC', color: '#F97316' },
  realisee:   { backgroundColor: '#F3E8FF', color: '#7C3AED' },
  validee:    { backgroundColor: '#D1FAE5', color: '#065F46' },
}

function tacheStatutStyle(statut) {
  return TACHE_STYLES[statut] ?? { backgroundColor: '#F3F4F6', color: '#6B7280' }
}
</script>

<style scoped>
/* Transition overlay */
.backdrop-enter-active,
.backdrop-leave-active { transition: opacity 0.25s ease }
.backdrop-enter-from,
.backdrop-leave-to     { opacity: 0 }

/* Transition drawer : glisse depuis la droite */
.drawer-enter-active,
.drawer-leave-active { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) }
.drawer-enter-from,
.drawer-leave-to     { transform: translateX(100%) }
</style>
