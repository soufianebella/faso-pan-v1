<template>
  <div class="relative" style="height: calc(100vh - 64px)">

    <!-- Carte Leaflet pleine hauteur -->
    <div ref="mapContainer" class="w-full h-full" style="z-index: 0"></div>

    <!-- Légende fixe bas-gauche -->
    <div
      class="absolute bottom-6 left-4 z-10 bg-white rounded-lg shadow-lg p-3 space-y-2"
      style="border: 1px solid #e5e7eb; min-width: 160px"
    >
      <p class="text-xs font-bold uppercase tracking-wider" style="color: #1b3b8a">Légende</p>
      <div v-for="item in LEGENDE" :key="item.statut" class="flex items-center gap-2">
        <span
          class="w-3 h-3 rounded-full flex-shrink-0"
          :style="`background-color: ${item.color}`"
        ></span>
        <span class="text-xs" style="color: #374151">{{ item.label }}</span>
      </div>
    </div>

    <!-- Compteur panneaux visibles -->
    <div
      class="absolute top-4 left-4 z-10 bg-white rounded-lg shadow px-3 py-2"
      style="border: 1px solid #e5e7eb"
    >
      <p class="text-xs font-medium" style="color: #6b7280">
        <i class="fa-solid fa-sign-hanging mr-1" style="color: #1b3b8a"></i>
        {{ panneauxAvecCoords.length }} panneau(x) géolocalisés
      </p>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { storeToRefs }   from 'pinia'
import { usePanneauxStore } from '@/stores/panneaux.store'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

// Fix icônes Leaflet sous Vite
import markerIcon   from 'leaflet/dist/images/marker-icon.png'
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'

delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconUrl:       markerIcon,
  iconRetinaUrl: markerIcon2x,
  shadowUrl:     markerShadow,
})

const emit = defineEmits(['open-panneau'])

const store    = usePanneauxStore()
const { panneaux } = storeToRefs(store)

const mapContainer = ref(null)
let   mapInstance  = null
let   markersLayer = null

// ── Constantes ─────────────────────────────────────────────────────────────
const DEFAULT_LAT = 12.3714
const DEFAULT_LNG = -1.5197

// Limites géographiques du Burkina Faso
const BF_BOUNDS = L.latLngBounds(
  L.latLng(9.4009,  -5.5189),   // Sud-Ouest
  L.latLng(15.0849,  2.4061),   // Nord-Est
)

const LEGENDE = [
  { statut: 'actif',        label: 'Actif',        color: '#27AE60' },
  { statut: 'maintenance',  label: 'Maintenance',  color: '#F97316' },
  { statut: 'hors_service', label: 'Hors service', color: '#EF4444' },
]

const STATUT_LABELS = {
  actif:        'Actif',
  maintenance:  'Maintenance',
  hors_service: 'Hors service',
}

const STATUT_COLORS = {
  actif:        '#27AE60',
  maintenance:  '#F97316',
  hors_service: '#EF4444',
}

// ── Panneaux filtrés ────────────────────────────────────────────────────────
// On exclut les panneaux sans coords ET ceux dont les coords sont hors BF
// (protection contre des données de test invalides)
const panneauxAvecCoords = computed(() =>
  panneaux.value.filter(p => {
    if (!p.latitude || !p.longitude) return false
    return (
      p.latitude  >=  9.4009 && p.latitude  <= 15.0849 &&
      p.longitude >= -5.5189 && p.longitude <=  2.4061
    )
  }),
)

// ── Helpers marqueurs ───────────────────────────────────────────────────────
function createIcon(statut) {
  const color = STATUT_COLORS[statut] ?? '#6B7280'
  return L.divIcon({
    html: `<div style="
      width:22px; height:22px; border-radius:50% 50% 50% 0;
      background:${color}; transform:rotate(-45deg);
      border: 2px solid #fff;
      box-shadow: 0 2px 6px rgba(0,0,0,.35);
    "></div>`,
    iconSize:   [22, 22],
    iconAnchor: [11, 22],
    className:  '',
  })
}

function badgeHtml(statut) {
  const color = STATUT_COLORS[statut] ?? '#6B7280'
  return `<span style="
    display:inline-block; padding:1px 8px; border-radius:9999px;
    font-size:11px; font-weight:600;
    background-color:${color}22; color:${color}
  ">${STATUT_LABELS[statut] ?? statut}</span>`
}

function buildPopup(panneau) {
  const libres = panneau.faces_libres_count ?? 0
  const total  = panneau.faces_count        ?? 0

  return `
    <div style="min-width:180px; font-family:sans-serif">
      <p style="font-weight:700; color:#1B3B8A; font-size:14px; margin:0 0 4px">
        ${panneau.reference}
      </p>
      <p style="color:#6B7280; font-size:12px; margin:0 0 6px">
        ${panneau.ville ?? ''}${panneau.quartier ? ' — ' + panneau.quartier : ''}
      </p>
      <p style="margin:0 0 8px">${badgeHtml(panneau.statut)}</p>
      <p style="font-size:12px; color:#374151; margin:0 0 10px">
        Faces libres : <strong>${libres} / ${total}</strong>
      </p>
      <button
        onclick="window._fasopanOpenPanneau(${panneau.id})"
        style="
          display:block; width:100%; text-align:center;
          padding:5px 0; border-radius:6px; border:none;
          background:#1B3B8A; color:#fff; font-size:12px;
          font-weight:600; cursor:pointer
        "
      >
        Voir détails
      </button>
    </div>
  `
}

// ── Initialisation ──────────────────────────────────────────────────────────
onMounted(() => {
  mapInstance = L.map(mapContainer.value, {
    center:             [DEFAULT_LAT, DEFAULT_LNG],
    zoom:               7,                // zoom 7 = tout le BF visible au départ
    maxBounds:          BF_BOUNDS,
    maxBoundsViscosity: 1.0,
    minZoom:            6,                // empêche de dézoomer hors BF
  })

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>',
    maxZoom:     19,
  }).addTo(mapInstance)

  // Rebond immédiat si drag rapide dépasse les limites
  mapInstance.on('drag', () => mapInstance.panInsideBounds(BF_BOUNDS, { animate: false }))

  markersLayer = L.layerGroup().addTo(mapInstance)

  // Callback global pour le bouton "Voir détails" dans le popup Leaflet
  // Leaflet popup HTML ne supporte pas les gestionnaires Vue — on passe par window
  window._fasopanOpenPanneau = (id) => {
    const p = panneaux.value.find(x => x.id === id)
    if (p) emit('open-panneau', p)
  }

  renderMarkers()
})

onUnmounted(() => {
  delete window._fasopanOpenPanneau

  if (mapInstance) {
    mapInstance.remove()
    mapInstance  = null
    markersLayer = null
  }
})

// ── Rendu des marqueurs ─────────────────────────────────────────────────────
function renderMarkers() {
  if (!markersLayer) return

  markersLayer.clearLayers()

  panneauxAvecCoords.value.forEach((panneau) => {
    const marker = L.marker([panneau.latitude, panneau.longitude], {
      icon: createIcon(panneau.statut),
    })

    marker.bindPopup(buildPopup(panneau), {
      maxWidth: 220,
      className: 'fasopan-popup',
    })

    markersLayer.addLayer(marker)
  })
}

// Actualise les marqueurs si la liste panneaux change (ex: filtre statut)
watch(panneauxAvecCoords, renderMarkers, { deep: false })
</script>
