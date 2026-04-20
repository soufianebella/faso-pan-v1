<template>
  <div class="flex flex-col h-screen overflow-hidden p-6" style="background-color: #F0F4FF">

    <!-- ── Header : grille 2x2 ── -->
    <div class="mb-5 flex-shrink-0">

      <!-- Rangée 1 : titre à gauche, KPI à droite -->
      <div class="flex items-start justify-between mb-4">

        <div>
          <h1 class="text-2xl font-bold" style="color: #1B3B8A">Taches</h1>
          <p class="text-sm mt-0.5" style="color: #6B7280">
            Suivez l'avancement des installations et validations terrain.
          </p>
        </div>

        <!-- KPI card -->
        <div
          class="flex items-center gap-8 bg-white rounded-xl px-6 py-3 border shadow-sm"
          style="border-color: #E5E7EB"
        >
          <div>
            <p class="text-[10px] uppercase font-bold tracking-widest mb-1" style="color: #6B7280">TOTAL</p>
            <p class="text-2xl font-bold leading-none" style="color: #1C2833">{{ kpi.total }}</p>
          </div>
          <div>
            <p class="text-[10px] uppercase font-bold tracking-widest mb-1" style="color: #6B7280">EN COURS</p>
            <p class="text-2xl font-bold leading-none" style="color: #F97316">{{ kpi.enCours }}</p>
          </div>
          <div>
            <p class="text-[10px] uppercase font-bold tracking-widest mb-1" style="color: #6B7280">COMPLETION</p>
            <p class="text-2xl font-bold leading-none" style="color: #27AE60">{{ kpi.completion }}%</p>
          </div>
        </div>

      </div>

      <!-- Rangée 2 : recherche + filtres à gauche, toggle + bouton à droite -->
      <div class="flex items-center justify-between gap-4">

        <!-- Gauche : recherche + filtres -->
        <div class="flex gap-3 flex-1 max-w-xl">
          <div class="relative flex-1">
            <i
              class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs"
              style="color: #9CA3AF"
            ></i>
            <input
              v-model="search"
              placeholder="Rechercher une tache, un panneau, un agent."
              class="w-full pl-9 pr-4 py-2.5 border rounded-lg text-sm outline-none bg-white transition-all"
              style="border-color: #E5E7EB"
              @focus="$event.target.style.borderColor='#F97316'"
              @blur="$event.target.style.borderColor='#E5E7EB'"
            />
          </div>
          <button
            class="flex items-center gap-2 px-4 py-2.5 border rounded-lg text-sm font-medium bg-white transition-colors hover:bg-slate-50 flex-shrink-0"
            style="border-color: #E5E7EB; color: #374151"
          >
            <i class="fa-solid fa-sliders"></i> Filtres
          </button>
        </div>

        <!-- Droite : toggle + bouton nouvelle tache -->
        <div class="flex items-center gap-3 flex-shrink-0">

          <!-- Toggle Tableau / Liste -->
          <div class="flex rounded-lg overflow-hidden border bg-white" style="border-color: #E5E7EB">
            <button
              @click="viewMode = 'tableau'"
              class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold transition-colors"
              :style="viewMode === 'tableau'
                ? 'background-color: #1B3B8A; color: #fff'
                : 'background-color: #fff; color: #6B7280'"
            >
              <i class="fa-solid fa-table-cells-large text-xs"></i> Tableau
            </button>
            <button
              @click="viewMode = 'liste'"
              class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold transition-colors"
              :style="viewMode === 'liste'
                ? 'background-color: #1B3B8A; color: #fff'
                : 'background-color: #fff; color: #6B7280'"
            >
              <i class="fa-solid fa-list text-xs"></i> Liste
            </button>
          </div>

          <!-- Bouton Nouvelle tache — gestionnaire uniquement -->
          <button
            v-if="isGestionnaire"
            @click="ouvrirCreation"
            class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-white text-sm font-bold shadow-sm transition-colors"
            style="background-color: #F97316"
            @mouseover="$event.currentTarget.style.backgroundColor='#EA6C0A'"
            @mouseout="$event.currentTarget.style.backgroundColor='#F97316'"
          >
            <i class="fa-solid fa-plus"></i> Nouvelle tache
          </button>

        </div>
      </div>
    </div>

    <!-- ── Skeleton ── -->
    <div v-if="isLoading" class="flex gap-4 flex-1 overflow-hidden">
      <div v-for="i in 4" :key="i" class="flex flex-col gap-3 w-72 flex-shrink-0">
        <div class="h-5 w-28 rounded-full animate-pulse" style="background-color: #E5E7EB"></div>
        <div
          v-for="j in 3" :key="j"
          class="rounded-xl animate-pulse"
          :style="{ height: j === 2 ? '120px' : '96px', backgroundColor: '#E5E7EB' }"
        ></div>
      </div>
    </div>

    <!-- ── Vue Kanban ── -->
    <div
      v-else-if="viewMode === 'tableau'"
      class="flex gap-4 flex-1 overflow-x-auto pb-2 min-h-0"
    >
      <div
        v-for="col in colonnes"
        :key="col.statut"
        class="flex flex-col w-72 flex-shrink-0 min-h-0"
      >

        <!-- En-tête colonne -->
        <div class="flex items-center gap-2 mb-3 px-1 flex-shrink-0">
          <span
            class="w-2.5 h-2.5 rounded-full flex-shrink-0"
            :style="{ backgroundColor: col.color }"
          ></span>
          <span class="text-sm font-bold" style="color: #1C2833">{{ col.label }}</span>
          <span
            class="text-xs font-bold px-2 py-0.5 rounded-full ml-auto"
            style="background-color: #F3F4F6; color: #6B7280"
          >
            {{ col.taches.length }}
          </span>
          <button class="ml-1 transition-opacity hover:opacity-60" style="color: #9CA3AF">
            <i class="fa-solid fa-plus text-sm"></i>
          </button>
        </div>

        <!-- Liste des cartes -->
        <div class="flex flex-col gap-3 overflow-y-auto flex-1 pr-1">

          <div
            v-for="tache in col.taches"
            :key="tache.id"
            class="bg-white rounded-xl p-4 border shadow-sm"
            style="border-color: #E5E7EB"
          >
            <!-- Référence panneau / face -->
            <p class="text-[11px] font-semibold mb-1" style="color: #9CA3AF">
              {{ tache.affectation?.face?.panneau?.reference ?? 'N/A' }}
              / Face {{ tache.affectation?.face?.numero ?? '?' }}
            </p>

            <!-- Nom campagne -->
            <p class="text-base font-bold mb-2" style="color: #1C2833">
              {{ tache.affectation?.campagne?.nom ?? 'N/A' }}
            </p>

            <!-- ── En attente : badge échéance proche ── -->
            <template v-if="col.statut === 'en_attente'">
              <div
                v-if="joursRestants(tache) !== null && joursRestants(tache) >= 0 && joursRestants(tache) <= 7"
                class="flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg mb-2"
                style="background-color: #FEF3DC; color: #F97316"
              >
                <i class="fa-solid fa-circle-exclamation"></i>
                Echeance dans {{ joursRestants(tache) }} jours
              </div>
            </template>

            <!-- ── En cours : statut + barre de progression ── -->
            <template v-else-if="col.statut === 'en_cours'">
              <div class="flex items-center justify-between mb-1.5">
                <span class="text-xs font-semibold" style="color: #F97316">En cours</span>
                <span class="text-xs" style="color: #F97316">
                  {{ relativeTime(tache.updated_at) }}
                </span>
              </div>
              <div class="h-1.5 rounded-full overflow-hidden mb-3" style="background-color: #F3F4F6">
                <div
                  class="h-full rounded-full"
                  style="background-color: #F97316; transition: width 0.3s"
                  :style="{ width: progressPct(tache) + '%' }"
                ></div>
              </div>
            </template>

            <!-- ── Réalisées : placeholder photo ── -->
            <template v-else-if="col.statut === 'realisee'">
              <div
                class="flex items-center justify-center rounded-lg mb-3"
                style="height: 52px; background-color: #F9FAFB; border: 1.5px dashed #E5E7EB"
              >
                <i
                  v-if="!tache.photo_path"
                  class="fa-solid fa-camera text-xl"
                  style="color: #D1D5DB"
                ></i>
                <img
                  v-else
                  :src="tache.photo_path"
                  class="h-full w-full object-cover rounded-lg"
                  alt="Photo pose"
                />
              </div>
            </template>

            <!-- ── Footer commun : avatar agent + date/action ── -->
            <div class="flex items-center justify-between">

              <!-- Avatar + nom agent -->
              <div class="flex items-center gap-2">
                <span
                  v-if="tache.agent"
                  class="w-6 h-6 rounded-full flex items-center justify-center
                         text-[10px] font-bold text-white flex-shrink-0"
                  :style="{ backgroundColor: avatarColor(tache.agent.name) }"
                  :title="tache.agent.name"
                >
                  {{ initiales(tache.agent.name) }}
                </span>
                <span class="text-xs font-medium" style="color: #374151">
                  {{ tache.agent?.name ?? '' }}
                </span>
              </div>

              <!-- Droite : selon colonne -->
              <div class="flex items-center gap-2">

                <!-- En attente → Assigner (gestionnaire) + Commencer (agent) + date -->
                <template v-if="col.statut === 'en_attente'">
                  <button
                    v-if="isGestionnaire"
                    @click="ouvrirModal(tache)"
                    class="flex items-center gap-1 text-xs font-semibold transition-opacity hover:opacity-70"
                    style="color: #1B3B8A"
                  >
                    <i class="fa-solid fa-user-plus text-[10px]"></i> Assigner
                  </button>
                  <button
                    v-if="peutAvancer(tache)"
                    @click="confirmerAvancement(tache.id)"
                    class="flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-md transition-colors"
                    style="background-color: #FEF3DC; color: #F97316"
                  >
                    <i class="fa-solid fa-play text-[9px]"></i> Commencer
                  </button>
                  <span class="text-xs" style="color: #9CA3AF">
                    <i class="fa-regular fa-calendar text-[10px] mr-0.5"></i>
                    {{ formatDateCourte(tache.affectation?.date_fin) }}
                  </span>
                </template>

                <!-- En cours → En activite + bouton Marquer réalisée (agent) -->
                <template v-else-if="col.statut === 'en_cours'">
                  <button
                    v-if="peutAvancer(tache)"
                    @click="confirmerAvancement(tache.id)"
                    class="flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-md transition-colors"
                    style="background-color: #F3E8FF; color: #7C3AED"
                  >
                    <i class="fa-solid fa-check text-[9px]"></i> Realisee
                  </button>
                  <span
                    class="flex items-center gap-1 text-xs font-semibold"
                    style="color: #27AE60"
                  >
                    <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background-color: #27AE60"></span>
                    En activite
                  </span>
                </template>

                <!-- Réalisées → date réalisation -->
                <template v-else-if="col.statut === 'realisee'">
                  <span
                    v-if="tache.realise_at"
                    class="flex items-center gap-1 text-xs font-semibold"
                    style="color: #27AE60"
                  >
                    <i class="fa-solid fa-check text-[10px]"></i>
                    {{ tache.realise_at }}
                  </span>
                </template>

                <!-- Validées → date validation -->
                <template v-else-if="col.statut === 'validee'">
                  <span class="text-xs" style="color: #9CA3AF">
                    {{ tache.valide_at?.split(' ')[0] }}
                  </span>
                </template>

              </div>
            </div>

            <!-- Validées → "Valide par X" -->
            <p
              v-if="col.statut === 'validee' && tache.valide_par"
              class="text-xs mt-2"
              style="color: #9CA3AF"
            >
              Valide par {{ tache.valide_par.name }}
            </p>

            <!-- Réalisées → bouton Valider la tache -->
            <button
              v-if="col.statut === 'realisee' && isGestionnaire"
              @click="confirmerValidation(tache.id)"
              :disabled="isLoading"
              class="mt-3 w-full flex items-center justify-center gap-2 py-2
                     rounded-lg text-white text-sm font-bold transition-colors
                     disabled:opacity-50"
              style="background-color: #1B3B8A"
              @mouseover="$event.target.style.backgroundColor='#16306E'"
              @mouseout="$event.target.style.backgroundColor='#1B3B8A'"
            >
              Valider la tache
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>

          </div>
        </div>
      </div>
    </div>

    <!-- ── Vue Liste ── -->
    <template v-else>
      <div
        class="flex-1 overflow-auto bg-white rounded-lg border min-h-0"
        style="border-color: #E5E7EB"
      >
        <TacheTable
          :taches="filteredTaches"
          @assigner="ouvrirModal"
          @avancer="confirmerAvancement"
          @valider="confirmerValidation"
        />
      </div>

      <!-- Pagination -->
      <div
        class="flex items-center justify-between py-3 px-4 border-t bg-white flex-shrink-0"
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

    <!-- Modal Assigner -->
    <TacheModal
      v-if="showModal"
      :tache="tacheActuelle"
      @close="handleClose"
      @saved="handleSaved"
    />

    <!-- Modal Nouvelle tache -->
    <TacheCreationModal
      v-if="showCreationModal"
      @close="showCreationModal = false"
      @saved="handleCreationSaved"
    />

    <!-- Modal Realiser (upload photo preuve) -->
    <TacheRealiserModal
      v-if="showRealiserModal && tacheARealiser"
      :tache="tacheARealiser"
      @close="fermerRealiserModal"
      @saved="handleRealiserSaved"
    />

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { storeToRefs }              from 'pinia'
import { useTachesStore }           from '@/stores/taches.store'
import { useAuthStore }             from '@/stores/auth.store'
import TacheTable           from '@/components/taches/TacheTable.vue'
import TacheModal           from '@/components/taches/TacheModal.vue'
import TacheCreationModal   from '@/components/taches/TacheCreationModal.vue'
import TacheRealiserModal   from '@/components/taches/TacheRealiserModal.vue'

const store = useTachesStore()
const auth  = useAuthStore()

const { taches, isLoading, filtres, pagination, tacheActuelle } = storeToRefs(store)

const viewMode  = ref('tableau')
const showModal         = ref(false)
const showCreationModal = ref(false)
const showRealiserModal = ref(false)
const tacheARealiser    = ref(null)
const search            = ref('')

onMounted(() => store.fetchTaches())

// ── Permissions ──────────────────────────────────────────────────────────────
const isGestionnaire = computed(() =>
  ['super_admin', 'gestionnaire'].includes(auth.user?.role)
)

// ── Recherche frontend (filtre sur données chargées) ─────────────────────────
const filteredTaches = computed(() => {
  if (!search.value.trim()) return taches.value
  const q = search.value.toLowerCase()
  return taches.value.filter(t =>
    t.affectation?.campagne?.nom?.toLowerCase().includes(q) ||
    t.affectation?.face?.panneau?.reference?.toLowerCase().includes(q) ||
    t.agent?.name?.toLowerCase().includes(q)
  )
})

// ── KPI ──────────────────────────────────────────────────────────────────────
const kpi = computed(() => {
  const all       = taches.value
  const total     = all.length
  const enCours   = all.filter(t => t.statut === 'en_cours').length
  const terminees = all.filter(t => ['realisee', 'validee'].includes(t.statut)).length
  const completion = total > 0 ? Math.round((terminees / total) * 100) : 0
  return { total, enCours, completion }
})

// ── Colonnes Kanban ───────────────────────────────────────────────────────────
const COLONNES_CONFIG = [
  { statut: 'en_attente', label: 'En attente', color: '#6B7280' },
  { statut: 'en_cours',   label: 'En cours',   color: '#F97316' },
  { statut: 'realisee',   label: 'Realisees',  color: '#27AE60' },
  { statut: 'validee',    label: 'Validees',   color: '#1B3B8A' },
]

const colonnes = computed(() =>
  COLONNES_CONFIG.map(col => ({
    ...col,
    taches: filteredTaches.value.filter(t => t.statut === col.statut),
  }))
)

// ── Permissions ──────────────────────────────────────────────────────────────
/** Agent connecté peut avancer sa propre tache si elle n'est pas terminée */
function peutAvancer(tache) {
  return tache.agent?.id === auth.user?.id
    && ['en_attente', 'en_cours'].includes(tache.statut)
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const AVATAR_COLORS = ['#1B3B8A', '#F97316', '#7C3AED', '#0891B2', '#065F46', '#B45309']

function initiales(name) {
  if (!name) return '?'
  return name.split(' ').slice(0, 2).map(w => w[0]?.toUpperCase() ?? '').join('')
}

function avatarColor(name) {
  if (!name) return '#6B7280'
  let hash = 0
  for (const c of name) hash = (hash + c.charCodeAt(0)) % AVATAR_COLORS.length
  return AVATAR_COLORS[hash]
}

/** Parse "dd/mm/yyyy" ou "dd/mm/yyyy HH:mm" → Date */
function parseDate(str) {
  if (!str) return null
  const [datePart] = str.split(' ')
  const [d, m, y]  = datePart.split('/')
  if (!d || !m || !y) return null
  return new Date(+y, +m - 1, +d)
}

/** Nombre de jours entre aujourd'hui et date_fin affectation */
function joursRestants(tache) {
  const fin = parseDate(tache.affectation?.date_fin)
  if (!fin) return null
  return Math.ceil((fin - new Date()) / 86_400_000)
}

/** "15/04/2026" → "15 avr." */
function formatDateCourte(str) {
  const d = parseDate(str)
  if (!d) return ''
  return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }).replace('.', '')
}

/** "20/04/2026 10:05" → "il y a 2h" */
function relativeTime(str) {
  if (!str) return ''
  const [datePart, timePart] = str.split(' ')
  const [day, month, year]   = (datePart ?? '').split('/')
  const [h, min]             = (timePart ?? '00:00').split(':')
  if (!day) return ''

  const date    = new Date(+year, +month - 1, +day, +h, +min)
  const diffMs  = Date.now() - date.getTime()
  const minutes = Math.floor(diffMs / 60_000)
  const hours   = Math.floor(diffMs / 3_600_000)
  const days    = Math.floor(diffMs / 86_400_000)

  if (minutes < 1)  return "à l'instant"
  if (minutes < 60) return `il y a ${minutes}min`
  if (hours < 24)   return `il y a ${hours}h`
  if (days === 1)   return 'hier'
  return `il y a ${days}j`
}

/** Pourcentage d'avancement temporel de la campagne */
function progressPct(tache) {
  const debut = parseDate(tache.affectation?.date_debut)
  const fin   = parseDate(tache.affectation?.date_fin)
  if (!debut || !fin || fin <= debut) return 40
  const total   = fin - debut
  const elapsed = Date.now() - debut.getTime()
  return Math.min(95, Math.max(5, Math.round((elapsed / total) * 100)))
}

// ── Actions ───────────────────────────────────────────────────────────────────
function ouvrirCreation() {
  store.fetchAffectationsDisponibles()
  store.fetchAgents()
  showCreationModal.value = true
}

function handleCreationSaved() {
  showCreationModal.value = false
}

function ouvrirModal(tache) {
  store.setTacheActuelle(tache)
  store.fetchAgents()
  showModal.value = true
}

async function confirmerAvancement(id) {
  const tache = taches.value.find(t => t.id === id)
  if (!tache) return

  // Transition en_cours → realisee : on passe par la modal upload photo
  if (tache.statut === 'en_cours') {
    tacheARealiser.value    = tache
    showRealiserModal.value = true
    return
  }

  // en_attente → en_cours : simple confirmation
  if (!confirm("Confirmer le demarrage de cette tache ?")) return
  try {
    await store.avancerTache(id)
  } catch (err) {
    alert(err.response?.data?.message ?? "Erreur lors de l'avancement.")
  }
}

function fermerRealiserModal() {
  showRealiserModal.value = false
  tacheARealiser.value    = null
  store.clearErrors()
}

async function handleRealiserSaved() {
  fermerRealiserModal()
  await store.fetchTaches(pagination.value.currentPage)
}

async function confirmerValidation(id) {
  if (!confirm('Valider cette tache comme terminee ?')) return
  try {
    await store.validerTache(id)
  } catch (err) {
    alert(err.response?.data?.message ?? 'Erreur lors de la validation.')
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
