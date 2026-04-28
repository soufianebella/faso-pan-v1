<template>
  <div class="p-6 space-y-6 min-h-screen" style="background-color: #f0f4ff">

    <!-- ── Breadcrumb ──────────────────────────────────────────────────────── -->
    <nav class="flex items-center gap-2 text-xs" style="color: #9ca3af">
      <button
        @click="router.push({ name: 'panneaux' })"
        class="hover:underline transition-colors"
        style="color: #1b3b8a"
      >
        Panneaux
      </button>
      <i class="fa-solid fa-chevron-right text-[10px]"></i>
      <span style="color: #374151">{{ panneau?.reference ?? '…' }}</span>
      <span v-if="panneau?.adresse || panneau?.quartier" style="color: #9ca3af">
        — {{ panneau?.adresse || panneau?.quartier }}
      </span>
    </nav>

    <!-- ── Skeleton global ────────────────────────────────────────────────── -->
    <div v-if="panneauDetailLoading" class="space-y-4">
      <div class="h-20 rounded-xl animate-pulse" style="background-color: #e5e7eb"></div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
          <div class="h-64 rounded-xl animate-pulse" style="background-color: #e5e7eb"></div>
          <div class="h-48 rounded-xl animate-pulse" style="background-color: #e5e7eb"></div>
          <div class="h-48 rounded-xl animate-pulse" style="background-color: #e5e7eb"></div>
        </div>
        <div class="h-80 rounded-xl animate-pulse" style="background-color: #e5e7eb"></div>
      </div>
    </div>

    <template v-else-if="panneau">

      <!-- ── Header — card blanc ──────────────────────────────────────────── -->
      <div
        class="bg-white rounded-xl shadow-sm border p-5 flex items-start justify-between gap-4"
        style="border-color: #e5e7eb"
      >
        <div class="min-w-0">
          <div class="flex items-center gap-3 flex-wrap">
            <h2 class="text-2xl font-bold" style="color: #1b3b8a">
              {{ panneau.reference }}
            </h2>
            <!-- Badge statut à côté de la référence -->
            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
              :style="statutStyle(panneau.statut)"
            >
              <i class="fa-solid" :class="statutIcon(panneau.statut)"></i>
              {{ formatStatut(panneau.statut) }}
            </span>
          </div>
          <p class="text-sm mt-1" style="color: #6b7280">
            <i class="fa-solid fa-map-marker-alt mr-1"></i>
            {{ panneau.ville }}{{ panneau.quartier ? ', ' + panneau.quartier : '' }}
          </p>
        </div>

        <div class="flex items-center gap-2 flex-shrink-0">
          <button
            @click="openEdit"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border transition-colors"
            style="border-color: #1b3b8a; color: #1b3b8a; background-color: #fff"
            @mouseenter="$event.currentTarget.style.backgroundColor='#f0f4ff'"
            @mouseleave="$event.currentTarget.style.backgroundColor='#fff'"
          >
            <i class="fa-solid fa-pen-to-square"></i>
            Modifier
          </button>
          <button
            @click="openStatutModal"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border transition-colors"
            style="border-color: #f97316; color: #f97316; background-color: #fff"
            @mouseenter="$event.currentTarget.style.backgroundColor='#fff7ed'"
            @mouseleave="$event.currentTarget.style.backgroundColor='#fff'"
          >
            <i class="fa-solid fa-circle-half-stroke"></i>
            Changer statut
          </button>
        </div>
      </div>

      <!-- ── Grid principal ───────────────────────────────────────────────── -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- ── Colonne gauche (col-span-2) ───────────────────────────────── -->
        <div class="lg:col-span-2 space-y-6">

          <!-- ── Card infos techniques ──────────────────────────────────── -->
          <div class="bg-white rounded-xl shadow-sm border p-6 space-y-4" style="border-color: #e5e7eb">
            <h3 class="text-sm font-bold uppercase tracking-wider" style="color: #f97316">
              Informations techniques
            </h3>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">

              <!-- Référence -->
              <div class="flex flex-col gap-0.5">
                <dt class="text-xs" style="color: #9ca3af">Référence</dt>
                <dd class="text-sm font-bold" style="color: #1b3b8a">{{ panneau.reference }}</dd>
              </div>

              <!-- Adresse -->
              <div class="flex flex-col gap-0.5">
                <dt class="text-xs flex items-center gap-1" style="color: #9ca3af">
                  <i class="fa-solid fa-location-dot"></i> Adresse
                </dt>
                <dd class="text-sm" style="color: #374151">
                  {{ panneau.adresse || '—' }}
                </dd>
              </div>

              <!-- Ville / Quartier -->
              <div class="flex flex-col gap-0.5">
                <dt class="text-xs flex items-center gap-1" style="color: #9ca3af">
                  <i class="fa-solid fa-map"></i> Ville / Quartier
                </dt>
                <dd class="text-sm" style="color: #374151">
                  {{ panneau.ville || '—' }}{{ panneau.quartier ? ' / ' + panneau.quartier : '' }}
                </dd>
              </div>

              <!-- GPS -->
              <div class="flex flex-col gap-0.5">
                <dt class="text-xs flex items-center gap-1" style="color: #9ca3af">
                  <i class="fa-solid fa-crosshairs"></i> GPS
                </dt>
                <dd class="text-sm">
                  <a
                    v-if="panneau.latitude && panneau.longitude"
                    :href="`https://www.google.com/maps?q=${panneau.latitude},${panneau.longitude}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="hover:underline"
                    style="color: #1b3b8a"
                  >
                    {{ formatCoords(panneau.latitude, panneau.longitude) }}
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs ml-1"></i>
                  </a>
                  <span v-else style="color: #9ca3af">Non renseigné</span>
                </dd>
              </div>

              <!-- Hauteur du mat -->
              <div class="flex flex-col gap-0.5">
                <dt class="text-xs flex items-center gap-1" style="color: #9ca3af">
                  <i class="fa-solid fa-ruler-vertical"></i> Hauteur du mat
                </dt>
                <dd class="text-sm" style="color: #374151">
                  {{ panneau.hauteur_mat != null ? panneau.hauteur_mat + ' m' : '—' }}
                </dd>
              </div>

              <!-- Éclairage -->
              <div class="flex flex-col gap-0.5">
                <dt class="text-xs flex items-center gap-1" style="color: #9ca3af">
                  <i class="fa-solid fa-lightbulb"></i> Éclairage
                </dt>
                <dd class="text-sm font-medium" :style="panneau.eclaire ? 'color: #27AE60' : 'color: #EF4444'">
                  <i class="fa-solid mr-1" :class="panneau.eclaire ? 'fa-check' : 'fa-xmark'"></i>
                  {{ panneau.eclaire ? 'Oui' : 'Non' }}
                </dd>
              </div>

              <!-- État physique -->
              <div class="flex flex-col gap-0.5">
                <dt class="text-xs" style="color: #9ca3af">État physique</dt>
                <dd>
                  <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                    :style="statutStyle(panneau.statut)"
                  >
                    <i class="fa-solid" :class="statutIcon(panneau.statut)"></i>
                    {{ formatStatut(panneau.statut) }}
                  </span>
                </dd>
              </div>

            </dl>

            <!-- Pied de card — affiché uniquement si créateur connu -->
            <p
              v-if="panneau.createur"
              class="text-xs pt-3 border-t"
              style="color: #9ca3af; border-color: #f3f4f6"
            >
              Créé par
              <span class="font-medium" style="color: #6b7280">{{ panneau.createur.name }}</span>
              —
              {{ panneau.created_at_full ?? panneau.created_at ?? '—' }}
            </p>
          </div>

          <!-- ── Mini-carte Leaflet (readonly) — visible si coords existent ── -->
          <div
            v-if="panneau.latitude && panneau.longitude"
            class="bg-white rounded-xl shadow-sm border overflow-hidden"
            style="border-color: #e5e7eb"
          >
            <div class="px-4 py-3 border-b flex items-center gap-2" style="border-color: #f3f4f6">
              <i class="fa-solid fa-map-location-dot" style="color: #1b3b8a"></i>
              <span class="text-xs font-semibold uppercase tracking-wider" style="color: #6b7280">
                Localisation
              </span>
            </div>
            <MapPicker
              :model-value="{ lat: panneau.latitude, lng: panneau.longitude }"
              :readonly="true"
            />
          </div>

          <!-- ── Onglets ─────────────────────────────────────────────────── -->
          <div class="bg-white rounded-xl shadow-sm border overflow-hidden" style="border-color: #e5e7eb">

            <!-- Nav onglets -->
            <div class="flex border-b" style="border-color: #e5e7eb">
              <button
                v-for="tab in TABS"
                :key="tab.key"
                @click="activerOnglet(tab.key)"
                class="flex items-center gap-2 px-5 py-3 text-sm font-medium transition-colors border-b-2"
                :style="activeTab === tab.key
                  ? 'border-color: #1b3b8a; color: #1b3b8a; font-weight: 700'
                  : 'border-color: transparent; color: #6b7280'"
              >
                <i class="fa-solid" :class="tab.icon"></i>
                {{ tab.label }}
              </button>
            </div>

            <!-- ── Onglet 1 : Historique ─────────────────────────────────── -->
            <div v-show="activeTab === 'historique'" class="p-6">

              <!-- Skeleton 3 lignes -->
              <div v-if="historiqueLoading" class="space-y-4">
                <div v-for="i in 3" :key="i" class="flex gap-3">
                  <div class="w-8 h-8 rounded-full animate-pulse flex-shrink-0" style="background-color: #e5e7eb"></div>
                  <div class="flex-1 space-y-2 pt-1">
                    <div class="h-3 w-3/4 rounded animate-pulse" style="background-color: #e5e7eb"></div>
                    <div class="h-3 w-1/2 rounded animate-pulse" style="background-color: #f3f4f6"></div>
                  </div>
                </div>
              </div>

              <!-- Empty state -->
              <div
                v-else-if="!historique.length"
                class="flex flex-col items-center gap-3 py-10"
                style="color: #9ca3af"
              >
                <i class="fa-solid fa-clock-rotate-left text-3xl"></i>
                <span class="text-sm font-medium">Aucun historique disponible</span>
              </div>

              <!-- Timeline groupée par mois -->
              <div v-else class="space-y-6">
                <div v-for="groupe in groupesHistorique" :key="groupe.mois">
                  <p
                    class="text-xs font-bold uppercase tracking-wider mb-3"
                    style="color: #6b7280"
                  >
                    {{ groupe.mois }}
                  </p>
                  <ol class="relative pl-1">
                    <li
                      v-for="(evt, i) in groupe.events"
                      :key="i"
                      class="flex gap-3 pb-4"
                    >
                      <div class="flex flex-col items-center flex-shrink-0">
                        <div
                          class="w-8 h-8 rounded-full flex items-center justify-center shadow-sm flex-shrink-0"
                          :style="`background-color: ${evt.couleur}1A; border: 2px solid ${evt.couleur}`"
                        >
                          <i class="fa-solid text-xs" :class="evt.icone" :style="`color: ${evt.couleur}`"></i>
                        </div>
                        <div
                          v-if="i < groupe.events.length - 1"
                          class="w-px flex-1 mt-1"
                          style="background-color: #e5e7eb; min-height: 20px"
                        ></div>
                      </div>
                      <div class="flex-1 pt-0.5">
                        <p class="text-sm font-semibold" style="color: #1c2833">{{ evt.titre }}</p>
                        <p v-if="evt.detail" class="text-xs italic mt-0.5" style="color: #6b7280">
                          {{ evt.detail }}
                        </p>
                        <p class="text-xs mt-0.5" style="color: #9ca3af">
                          <i class="fa-solid fa-clock mr-1"></i>{{ evt.date }}
                        </p>
                      </div>
                    </li>
                  </ol>
                </div>
              </div>
            </div>

            <!-- ── Onglet 2 : Tâches liées ────────────────────────────────── -->
            <div v-show="activeTab === 'taches'" class="p-6 space-y-4">

              <div v-if="tachesLoading" class="space-y-3">
                <div v-for="i in 3" :key="i" class="h-14 rounded animate-pulse" style="background-color: #e5e7eb"></div>
              </div>

              <div
                v-else-if="!tachesDetail.length"
                class="flex flex-col items-center gap-3 py-10"
                style="color: #9ca3af"
              >
                <i class="fa-solid fa-list-check text-3xl"></i>
                <span class="text-sm font-medium">Aucune tâche liée</span>
              </div>

              <div v-else class="space-y-2">
                <div
                  v-for="tache in tachesDetail"
                  :key="tache.id"
                  class="flex items-center gap-3 p-3 rounded-lg border"
                  style="border-color: #e5e7eb"
                >
                  <i
                    class="fa-solid fa-circle text-sm flex-shrink-0"
                    :style="`color: ${STATUT_TACHE_COLORS[tache.statut] ?? '#9CA3AF'}`"
                  ></i>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold truncate" style="color: #1c2833">
                      {{ tache.campagne_nom || '—' }}
                    </p>
                    <p class="text-xs truncate" style="color: #6b7280">
                      <span v-if="tache.face_numero">Face {{ tache.face_numero }}</span>
                      <span v-if="tache.face_numero" class="mx-1">·</span>
                      <span v-if="tache.agent_name" style="color: #374151">{{ tache.agent_name }}</span>
                      <span v-else style="color: #F97316">Non assigné</span>
                    </p>
                  </div>
                  <div class="flex flex-col items-end gap-1 flex-shrink-0">
                    <span
                      class="px-2 py-0.5 rounded-full text-xs font-medium"
                      :style="statutTacheBadge(tache.statut)"
                    >
                      {{ STATUT_TACHE_LABELS[tache.statut] ?? tache.statut }}
                    </span>
                    <span v-if="tache.realise_at" class="text-xs" style="color: #9ca3af">
                      {{ tache.realise_at }}
                    </span>
                  </div>
                </div>
              </div>

              <button
                @click="router.push({ name: 'taches', query: { panneau_id: panneau.id } })"
                class="w-full py-2 text-sm font-medium rounded-lg border transition-colors mt-2"
                style="border-color: #1b3b8a; color: #1b3b8a"
                @mouseenter="$event.currentTarget.style.backgroundColor='#f0f4ff'"
                @mouseleave="$event.currentTarget.style.backgroundColor=''"
              >
                <i class="fa-solid fa-arrow-right mr-1"></i>
                Voir toutes les tâches
              </button>
            </div>

          </div>
        </div>

        <!-- ── Colonne droite : Faces ──────────────────────────────────────── -->
        <div class="space-y-4">
          <h3 class="text-sm font-bold" style="color: #374151">
            Faces du panneau
            <span class="ml-1 font-normal" style="color: #9ca3af">
              ({{ panneau.faces?.length ?? 0 }}
              face{{ (panneau.faces?.length ?? 0) > 1 ? 's' : '' }})
            </span>
          </h3>

          <!-- Empty state — aucune face -->
          <div
            v-if="!panneau.faces?.length"
            class="bg-white rounded-xl shadow-sm border p-8 flex flex-col items-center gap-3"
            style="border-color: #e5e7eb; color: #9ca3af"
          >
            <i class="fa-solid fa-layer-group text-3xl"></i>
            <span class="text-sm font-medium">Aucune face enregistrée</span>
          </div>

          <!-- Liste des faces -->
          <div v-else class="space-y-3">
            <div
              v-for="face in panneau.faces"
              :key="face.id"
              class="bg-white rounded-xl shadow-sm overflow-hidden"
              :style="`border-left: 3px solid ${faceBorderColor(face)}; border-right: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb`"
            >
              <!-- Header face -->
              <div class="flex items-center justify-between px-4 py-2.5 border-b" style="border-color: #f3f4f6">
                <span class="text-sm font-bold" style="color: #374151">
                  Face {{ face.numero }}
                </span>
                <div class="flex items-center gap-1.5">
                  <!-- Badge J-X si expiration proche -->
                  <span
                    v-if="faceExpireBientot(face)"
                    class="px-2 py-0.5 rounded text-xs font-bold"
                    :style="jxBadgeStyle(face.affectation_active?.jours_restants)"
                  >
                    J-{{ face.affectation_active?.jours_restants }}
                  </span>
                  <!-- Badge statut -->
                  <span
                    class="px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wide"
                    :style="faceStatutBadge(face)"
                  >
                    {{ face.statut === 'libre' ? 'LIBRE' : 'OCCUPÉ' }}
                  </span>
                </div>
              </div>

              <!-- Corps face -->
              <div class="px-4 py-3 space-y-2">
                <!-- Dimensions -->
                <p class="text-sm" style="color: #374151">
                  <i class="fa-solid fa-ruler-combined mr-1.5" style="color: #9ca3af"></i>
                  {{ face.largeur }}m × {{ face.hauteur }}m = {{ face.surface }}m²
                </p>

                <!-- Infos affectation active si occupée -->
                <template v-if="face.statut === 'occupee' && face.affectation_active">
                  <p class="text-sm font-semibold" style="color: #1c2833">
                    <i class="fa-solid fa-building mr-1.5" style="color: #9ca3af"></i>
                    {{ face.affectation_active.campagne_annonceur || face.affectation_active.campagne_nom || '—' }}
                  </p>
                  <p class="text-xs" style="color: #6b7280">
                    <i class="fa-solid fa-calendar mr-1.5"></i>
                    expire le {{ face.affectation_active.date_fin }}
                  </p>
                </template>
              </div>
            </div>
          </div>
        </div>

      </div>
    </template>

    <!-- ── Modals ──────────────────────────────────────────────────────────── -->
    <PanneauModal
      :show="showModal"
      :panneau="panneauActuel"
      :errors="errors"
      @close="showModal = false; store.clearErrors()"
      @saved="onSaved"
    />

    <StatutModal
      :show="showStatutModal"
      :panneau="panneau"
      @close="showStatutModal = false"
      @saved="onStatutSaved"
    />

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter }    from 'vue-router'
import { storeToRefs }            from 'pinia'
import { usePanneauxStore }       from '@/stores/panneaux.store'
import PanneauModal               from '@/components/panneaux/PanneauModal.vue'
import StatutModal                from '@/components/panneaux/StatutModal.vue'
import MapPicker                  from '@/components/panneaux/MapPicker.vue'

const route  = useRoute()
const router = useRouter()
const store  = usePanneauxStore()

const {
  panneauDetail,
  panneauDetailLoading,
  historique,
  historiqueLoading,
  panneauActuel,
  errors,
} = storeToRefs(store)

const panneau = panneauDetail

// ── Onglets ──────────────────────────────────────────────────────────────────
const TABS = [
  { key: 'historique', label: 'Historique',  icon: 'fa-clock-rotate-left' },
  { key: 'taches',     label: 'Tâches liées', icon: 'fa-list-check' },
]

const activeTab        = ref('historique')
const historiqueCharge = ref(false)
const tachesCharge     = ref(false)
const tachesDetail     = ref([])
const tachesLoading    = ref(false)

// ── Modals ───────────────────────────────────────────────────────────────────
const showModal       = ref(false)
const showStatutModal = ref(false)

// ── Chargement initial ────────────────────────────────────────────────────────
onMounted(async () => {
  await store.fetchPanneauDetail(route.params.id)
  await chargerHistorique()
})

onUnmounted(() => {
  // Évite un flash de l'ancien panneau si on navigue vers un autre détail
  panneau.value = null
})

// ── Lazy loading onglets ─────────────────────────────────────────────────────
async function activerOnglet(key) {
  activeTab.value = key
  if (key === 'historique' && !historiqueCharge.value) await chargerHistorique()
  if (key === 'taches'     && !tachesCharge.value)     await chargerTaches()
}

async function chargerHistorique() {
  if (historiqueCharge.value) return
  await store.fetchHistorique(route.params.id)
  historiqueCharge.value = true
}

async function chargerTaches() {
  if (tachesCharge.value) return
  tachesLoading.value = true
  try {
    const evts = historique.value.filter(e => e.type === 'tache')
    tachesDetail.value = evts.map(e => ({
      id:          e.id ?? Math.random(),
      statut:      e.statut_tache ?? 'en_attente',
      campagne_nom: e.campagne_nom ?? e.titre,
      face_numero:  e.face_numero,
      agent_name:   e.auteur,
      realise_at:   e.realise_at_fmt,
    }))
    tachesCharge.value = true
  } finally {
    tachesLoading.value = false
  }
}

// ── Historique groupé par mois ────────────────────────────────────────────────
const groupesHistorique = computed(() => {
  const map = new Map()
  historique.value.forEach(evt => {
    const mois = evtMois(evt.date_iso)
    if (!map.has(mois)) map.set(mois, [])
    map.get(mois).push(evt)
  })
  return Array.from(map.entries()).map(([mois, events]) => ({ mois, events }))
})

function evtMois(isoStr) {
  if (!isoStr) return '—'
  const d = new Date(isoStr)
  return d.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' })
    .replace(/^\w/, c => c.toUpperCase())
}

// ── Handlers modals ───────────────────────────────────────────────────────────
function openEdit() {
  store.setPanneauActuel({
    ...panneau.value,
    faces: panneau.value.faces ? [...panneau.value.faces] : [],
  })
  showModal.value = true
}

function openStatutModal() {
  showStatutModal.value = true
}

async function onSaved() {
  showModal.value = false
  store.clearErrors()
  await store.fetchPanneauDetail(route.params.id)
}

async function onStatutSaved() {
  showStatutModal.value = false
  await store.fetchPanneauDetail(route.params.id)
  historiqueCharge.value = false
  await chargerHistorique()
}

// ── Helpers UI ────────────────────────────────────────────────────────────────
function formatCoords(lat, lng) {
  const latDir = lat >= 0 ? 'N' : 'S'
  const lngDir = lng >= 0 ? 'E' : 'W'
  return `${Math.abs(lat).toFixed(4)} ${latDir}, ${Math.abs(lng).toFixed(4)} ${lngDir}`
}

const STATUT_LABELS = {
  actif:        'Actif',
  maintenance:  'Maintenance',
  hors_service: 'Hors service',
}

function formatStatut(s) {
  const val = typeof s === 'object' && s !== null ? (s.value ?? s) : s
  return STATUT_LABELS[val] ?? val
}

function statutStyle(s) {
  const val = typeof s === 'object' && s !== null ? (s.value ?? s) : s
  if (val === 'actif')        return 'background-color: #D1FAE5; color: #065F46'
  if (val === 'maintenance')  return 'background-color: #FEF3DC; color: #92400E'
  if (val === 'hors_service') return 'background-color: #FEE2E2; color: #991B1B'
  return 'background-color: #F3F4F6; color: #374151'
}

function statutIcon(s) {
  const val = typeof s === 'object' && s !== null ? (s.value ?? s) : s
  if (val === 'actif')        return 'fa-circle-check'
  if (val === 'maintenance')  return 'fa-triangle-exclamation'
  if (val === 'hors_service') return 'fa-circle-xmark'
  return 'fa-circle'
}

function faceBorderColor(face) {
  const aff = face.affectation_active
  if (face.statut !== 'occupee') return '#27AE60'
  if (aff && aff.jours_restants <= 7) return '#F97316'
  return '#EF4444'
}

function faceStatutBadge(face) {
  if (face.statut === 'libre') return 'background-color: #D1FAE5; color: #065F46'
  return 'background-color: #FEE2E2; color: #EF4444'
}

function faceExpireBientot(face) {
  return face.statut === 'occupee'
    && face.affectation_active
    && face.affectation_active.jours_restants >= 0
    && face.affectation_active.jours_restants <= 7
}

function jxBadgeStyle(jours) {
  if (jours <= 2) return 'background-color: #FEE2E2; color: #EF4444'
  if (jours <= 5) return 'background-color: #FEF3DC; color: #F97316'
  return 'background-color: #EBF3FC; color: #1B3B8A'
}

const STATUT_TACHE_COLORS = {
  en_attente: '#9CA3AF',
  en_cours:   '#1B3B8A',
  realisee:   '#F97316',
  validee:    '#27AE60',
}

const STATUT_TACHE_LABELS = {
  en_attente: 'En attente',
  en_cours:   'En cours',
  realisee:   'Réalisée',
  validee:    'Validée',
}

function statutTacheBadge(s) {
  const colors = {
    en_attente: 'background-color: #F3F4F6; color: #6B7280',
    en_cours:   'background-color: #EBF3FC; color: #1B3B8A',
    realisee:   'background-color: #FEF3DC; color: #F97316',
    validee:    'background-color: #D1FAE5; color: #065F46',
  }
  return colors[s] ?? 'background-color: #F3F4F6; color: #6B7280'
}
</script>
