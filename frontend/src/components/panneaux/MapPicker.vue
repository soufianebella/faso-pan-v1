<template>
  <div :class="readonly ? '' : 'space-y-3'">

    <!-- ── Mode édition uniquement ─────────────────────────────────────── -->
    <template v-if="!readonly">
      <!-- Sélecteur ville + bouton Ma position -->
      <div class="flex items-center gap-3">
        <div class="flex-1 relative">
          <select
            @change="onVilleSelect($event.target.value)"
            class="w-full border rounded px-3 py-1.5 text-sm outline-none bg-white appearance-none pr-8"
            style="border-color: #e5e7eb; color: #374151"
          >
            <option value="">-- Centrer sur une ville --</option>
            <option
              v-for="ville in VILLES_BF"
              :key="ville.nom"
              :value="ville.nom"
            >{{ ville.nom }}</option>
          </select>
          <i
            class="fa-solid fa-chevron-down text-xs absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"
            style="color: #9ca3af"
          ></i>
        </div>

        <button
          type="button"
          @click="maPosition"
          :disabled="geoLoading"
          class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-medium border transition-colors disabled:opacity-50"
          style="border-color: #1b3b8a; color: #1b3b8a"
          @mouseenter="!geoLoading && ($event.currentTarget.style.backgroundColor = '#EBF3FC')"
          @mouseleave="$event.currentTarget.style.backgroundColor = ''"
        >
          <i class="fa-solid" :class="geoLoading ? 'fa-circle-notch animate-spin' : 'fa-location-dot'"></i>
          {{ geoLoading ? 'Localisation…' : 'Ma position' }}
        </button>
      </div>

      <!-- Indication -->
      <p class="text-xs" style="color: #9ca3af">
        <i class="fa-solid fa-circle-info mr-1"></i>
        Cliquez sur la carte ou déplacez le marqueur pour positionner le panneau.
      </p>
    </template>

    <!-- ── Carte Leaflet (mode édition : 320px | mode readonly : 192px) ── -->
    <div
      ref="mapContainer"
      class="w-full overflow-hidden"
      :class="readonly ? 'rounded-xl' : 'rounded border'"
      :style="`height: ${readonly ? '192px' : '320px'}; border-color: #e5e7eb; z-index: 0`"
    ></div>

    <!-- ── Champs lat/lng — mode édition uniquement ─────────────────── -->
    <template v-if="!readonly">
      <div class="grid grid-cols-2 gap-4">
        <div class="space-y-1">
          <label class="text-xs font-medium" style="color: #374151">Latitude</label>
          <input
            :value="modelValue.lat ?? ''"
            @input="onLatInput($event.target.value)"
            type="number"
            step="any"
            placeholder="Ex: 12.3647"
            class="w-full border rounded px-3 py-2 text-sm outline-none transition-all"
            style="border-color: #e5e7eb"
            @focus="$event.target.style.borderColor = '#F97316'"
            @blur="$event.target.style.borderColor = '#E5E7EB'"
          />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-medium" style="color: #374151">Longitude</label>
          <input
            :value="modelValue.lng ?? ''"
            @input="onLngInput($event.target.value)"
            type="number"
            step="any"
            placeholder="Ex: -1.5337"
            class="w-full border rounded px-3 py-2 text-sm outline-none transition-all"
            style="border-color: #e5e7eb"
            @focus="$event.target.style.borderColor = '#F97316'"
            @blur="$event.target.style.borderColor = '#E5E7EB'"
          />
        </div>
      </div>
    </template>

  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

// ── Fix icônes Leaflet sous Vite 
import markerIcon   from 'leaflet/dist/images/marker-icon.png'
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'

delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconUrl:       markerIcon,
  iconRetinaUrl: markerIcon2x,
  shadowUrl:     markerShadow,
})

// ── Props / Emits
const props = defineProps({
  modelValue: {
    type:    Object,
    default: () => ({ lat: null, lng: null }),
  },
  // Mode lecture seule : pas de clic, pas de drag, pas de contrôles
  readonly: {
    type:    Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue'])

// ── Refs
const mapContainer = ref(null)
const geoLoading   = ref(false)

// Centre par défaut : Ouagadougou (coordonnées réelles)
const DEFAULT_LAT  = 12.3714
const DEFAULT_LNG  = -1.5197
const DEFAULT_ZOOM = 12

// Villes principales du Burkina Faso
const VILLES_BF = [
  { nom: 'Ouagadougou',    lat: 12.3714, lng: -1.5197 },
  { nom: 'Bobo-Dioulasso', lat: 11.1771, lng: -4.2979 },
  { nom: 'Koudougou',      lat: 12.2525, lng: -2.3628 },
  { nom: 'Ouahigouya',     lat: 13.5731, lng: -2.4268 },
  { nom: 'Banfora',        lat: 10.6333, lng: -4.7667 },
  { nom: 'Dédougou',       lat: 12.4612, lng: -3.4592 },
  { nom: 'Kaya',           lat: 13.0993, lng: -1.0924 },
  { nom: 'Tenkodogo',      lat: 11.7833, lng: -0.3667 },
]

// Limites géographiques du Burkina Faso
const BF_BOUNDS = L.latLngBounds(
  L.latLng(9.4009,  -5.5189),   // Sud-Ouest
  L.latLng(15.0849,  2.4061),   // Nord-Est
)

let mapInstance = null
let marker      = null

// ── Icône marqueur orange 
const orangeIcon = L.divIcon({
  html: `<div style="
    width:24px; height:24px; border-radius:50% 50% 50% 0;
    background:#F97316; transform:rotate(-45deg);
    border: 2px solid #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,.35);
  "></div>`,
  iconSize:   [24, 24],
  iconAnchor: [12, 24],
  className:  '',
})

// ── Cycle de vie 
onMounted(() => {
  // Sécurité : jamais deux instances Leaflet sur le même div
  if (mapInstance) return

  const initialLat = props.modelValue?.lat ?? DEFAULT_LAT
  const initialLng = props.modelValue?.lng ?? DEFAULT_LNG

  mapInstance = L.map(mapContainer.value, {
    center:      [initialLat, initialLng],
    zoom:        props.readonly ? 15 : DEFAULT_ZOOM,
    zoomControl: !props.readonly,
    // En readonly : désactive toutes les interactions
    dragging:       !props.readonly,
    touchZoom:      !props.readonly,
    doubleClickZoom:!props.readonly,
    scrollWheelZoom:!props.readonly,
    boxZoom:        !props.readonly,
    keyboard:       !props.readonly,
    maxBounds:      props.readonly ? undefined : BF_BOUNDS,
    maxBoundsViscosity: props.readonly ? 0 : 1.0,
  })

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>',
    maxZoom:     19,
  }).addTo(mapInstance)

  if (!props.readonly) {
    // Maintient la carte dans les bounds même en drag rapide
    mapInstance.on('drag', () => mapInstance.panInsideBounds(BF_BOUNDS, { animate: false }))
  }

  // Marqueur initial si coordonnées existantes
  if (props.modelValue?.lat && props.modelValue?.lng) {
    placeMarker(props.modelValue.lat, props.modelValue.lng)
  }

  // Clic sur la carte → place / déplace le marqueur (mode édition uniquement)
  if (!props.readonly) {
    mapInstance.on('click', (e) => {
      placeMarker(e.latlng.lat, e.latlng.lng)
      emit('update:modelValue', { lat: e.latlng.lat, lng: e.latlng.lng })
    })
  }
})

onUnmounted(() => {
  if (mapInstance) {
    mapInstance.remove()
    mapInstance = null
    marker      = null
  }
})

// ── Helpers 
function placeMarker(lat, lng) {
  if (marker) {
    marker.setLatLng([lat, lng])
  } else {
    marker = L.marker([lat, lng], {
      icon:      orangeIcon,
      draggable: !props.readonly,
    }).addTo(mapInstance)

    if (!props.readonly) {
      marker.on('dragend', () => {
        const pos = marker.getLatLng()
        emit('update:modelValue', {
          lat: parseFloat(pos.lat.toFixed(6)),
          lng: parseFloat(pos.lng.toFixed(6)),
        })
      })
    }
  }
}

function onVilleSelect(nomVille) {
  if (!nomVille) return
  const ville = VILLES_BF.find(v => v.nom === nomVille)
  if (!ville || !mapInstance) return
  mapInstance.setView([ville.lat, ville.lng], 14)
}

function onLatInput(val) {
  const lat = parseFloat(val)
  if (!isNaN(lat) && props.modelValue?.lng) {
    placeMarker(lat, props.modelValue.lng)
    mapInstance?.setView([lat, props.modelValue.lng])
  }
  emit('update:modelValue', { lat: isNaN(lat) ? null : lat, lng: props.modelValue?.lng ?? null })
}

function onLngInput(val) {
  const lng = parseFloat(val)
  if (!isNaN(lng) && props.modelValue?.lat) {
    placeMarker(props.modelValue.lat, lng)
    mapInstance?.setView([props.modelValue.lat, lng])
  }
  emit('update:modelValue', { lat: props.modelValue?.lat ?? null, lng: isNaN(lng) ? null : lng })
}

async function maPosition() {
  if (!navigator.geolocation) return

  geoLoading.value = true
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      const lat = parseFloat(pos.coords.latitude.toFixed(6))
      const lng = parseFloat(pos.coords.longitude.toFixed(6))
      placeMarker(lat, lng)
      mapInstance?.setView([lat, lng], 15)
      emit('update:modelValue', { lat, lng })
      geoLoading.value = false
    },
    () => {
      geoLoading.value = false
    },
    { enableHighAccuracy: true, timeout: 8000 },
  )
}

// Sync externe : si la prop change depuis le parent (ex: réinitialisation)
watch(
  () => props.modelValue,
  (val) => {
    if (!mapInstance) return
    if (val?.lat && val?.lng) {
      placeMarker(val.lat, val.lng)
    }
  },
  { deep: true },
)
</script>
