<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center"
    style="background-color: rgba(15, 23, 42, 0.7)"
    @click.self="$emit('close')"
  >
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">

      <!-- Header -->
      <div class="p-4 border-b flex justify-between items-center flex-shrink-0" style="border-color: #e5e7eb">
        <h2 class="text-xl font-bold" style="color: #1b3b8a">
          {{ isEditMode ? 'Modifier le panneau' : 'Nouveau panneau' }}
        </h2>
        <button @click="$emit('close')" class="p-1 rounded-full hover:bg-slate-100 transition-colors">
          <i class="fa-solid fa-xmark text-xl" style="color: #6b7280"></i>
        </button>
      </div>

      <!-- Navigation onglets -->
      <div class="flex border-b flex-shrink-0" style="border-color: #e5e7eb">
        <button
          v-for="tab in visibleTabs"
          :key="tab.key"
          type="button"
          @click="activeTab = tab.key"
          class="flex items-center gap-2 px-5 py-3 text-sm font-medium transition-colors border-b-2"
          :style="activeTab === tab.key
            ? 'border-color: #1b3b8a; color: #1b3b8a; background-color: #f0f4ff'
            : 'border-color: transparent; color: #6b7280; background-color: transparent'"
        >
          <i class="fa-solid" :class="tab.icon"></i>
          {{ tab.label }}
        </button>
      </div>

      <!-- Contenu onglets -->
      <form @submit.prevent="handleSubmit" class="flex-1 overflow-y-auto">

        <!-- ─── Onglet 1 : Informations ───────────────────────────────── -->
        <div v-show="activeTab === 'infos'" class="p-6 space-y-8">

          <!-- Section : Infos générales -->
          <section>
            <h3 class="text-xs font-bold uppercase tracking-wider mb-4" style="color: #f97316">
              1. Informations générales
            </h3>
            <div class="grid grid-cols-2 gap-4">

              <div class="space-y-1">
                <label class="text-xs font-medium" style="color: #374151">Référence</label>
                <input
                  v-model="form.reference"
                  type="text"
                  :disabled="isEditMode"
                  class="w-full border rounded px-3 py-2 text-sm outline-none transition-all disabled:bg-gray-50"
                  :style="errors?.reference ? 'border-color: #EF4444' : 'border-color: #E5E7EB'"
                  @focus="$event.target.style.borderColor = '#F97316'"
                  @blur="$event.target.style.borderColor = errors?.reference ? '#EF4444' : '#E5E7EB'"
                />
                <p v-if="errors?.reference" class="text-xs" style="color: #ef4444">
                  {{ errors.reference[0] }}
                </p>
              </div>

              <div class="space-y-1">
                <label class="text-xs font-medium" style="color: #374151">Ville</label>
                <input
                  v-model="form.ville"
                  type="text"
                  class="w-full border rounded px-3 py-2 text-sm outline-none transition-all"
                  :style="errors?.ville ? 'border-color: #EF4444' : 'border-color: #E5E7EB'"
                  @focus="$event.target.style.borderColor = '#F97316'"
                  @blur="$event.target.style.borderColor = errors?.ville ? '#EF4444' : '#E5E7EB'"
                />
                <p v-if="errors?.ville" class="text-xs" style="color: #ef4444">
                  {{ errors.ville[0] }}
                </p>
              </div>

              <div class="space-y-1">
                <label class="text-xs font-medium" style="color: #374151">Quartier</label>
                <input
                  v-model="form.quartier"
                  type="text"
                  class="w-full border rounded px-3 py-2 text-sm outline-none"
                  style="border-color: #e5e7eb"
                />
              </div>

              <div class="space-y-1">
                <label class="text-xs font-medium" style="color: #374151">Hauteur mat (m)</label>
                <input
                  v-model="form.hauteur_mat"
                  type="number"
                  step="0.1"
                  min="0"
                  class="w-full border rounded px-3 py-2 text-sm outline-none"
                  style="border-color: #e5e7eb"
                />
              </div>

              <div class="space-y-1">
                <label class="text-xs font-medium" style="color: #374151">Statut</label>
                <select
                  v-model="form.statut"
                  class="w-full border rounded px-3 py-2 text-sm outline-none bg-white"
                  style="border-color: #e5e7eb"
                >
                  <option value="actif">Actif</option>
                  <option value="maintenance">Maintenance</option>
                  <option value="hors_service">Hors Service</option>
                </select>
              </div>

              <div class="flex items-center gap-2 pt-5">
                <input type="checkbox" v-model="form.eclaire" id="eclaire" class="rounded" />
                <label for="eclaire" class="text-sm font-medium" style="color: #374151">
                  Eclairage présent
                </label>
              </div>

            </div>
          </section>

          <!-- Section : Faces -->
          <section>
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-xs font-bold uppercase tracking-wider" style="color: #f97316">
                2. Faces du panneau
              </h3>
              <button
                v-if="!isEditMode"
                type="button"
                @click="ajouterFace"
                class="text-xs font-bold px-3 py-1 rounded border transition-colors hover:bg-orange-50"
                style="border-color: #f97316; color: #f97316"
              >
                <i class="fa-solid fa-plus mr-1"></i>Ajouter une face
              </button>
            </div>

            <div
              v-if="isEditMode"
              class="mb-3 px-3 py-2 rounded text-xs"
              style="background-color: #fef3dc; color: #7a5010"
            >
              <i class="fa-solid fa-triangle-exclamation mr-1"></i>
              Les faces ne sont pas modifiables après création (contrats potentiels en cours).
            </div>

            <div class="space-y-3">
              <div
                v-for="(face, index) in form.faces"
                :key="index"
                class="flex items-center gap-4 p-3 rounded border"
                :style="{
                  backgroundColor: isEditMode ? '#F9FAFB' : '#FAFAFA',
                  borderColor: '#E5E7EB',
                  opacity: isEditMode ? '0.7' : '1',
                }"
              >
                <div
                  class="w-9 h-9 flex items-center justify-center rounded-full border text-sm font-bold flex-shrink-0"
                  style="border-color: #1b3b8a; color: #1b3b8a; background-color: #ebf3fc"
                >
                  {{ face.numero }}
                </div>
                <div class="flex-1 space-y-1">
                  <label class="text-xs uppercase" style="color: #6b7280">Largeur (m)</label>
                  <input
                    v-model="face.largeur"
                    type="number"
                    step="0.1"
                    min="0.5"
                    :disabled="isEditMode"
                    class="w-full border rounded px-2 py-1.5 text-sm outline-none disabled:bg-gray-100"
                    style="border-color: #e5e7eb"
                  />
                  <p v-if="errors?.[`faces.${index}.largeur`]" class="text-xs" style="color: #ef4444">
                    {{ errors[`faces.${index}.largeur`][0] }}
                  </p>
                </div>
                <div class="flex-1 space-y-1">
                  <label class="text-xs uppercase" style="color: #6b7280">Hauteur (m)</label>
                  <input
                    v-model="face.hauteur"
                    type="number"
                    step="0.1"
                    min="0.5"
                    :disabled="isEditMode"
                    class="w-full border rounded px-2 py-1.5 text-sm outline-none disabled:bg-gray-100"
                    style="border-color: #e5e7eb"
                  />
                </div>
                <button
                  v-if="!isEditMode && form.faces.length > 1"
                  type="button"
                  @click="supprimerFace(index)"
                  class="p-2 rounded transition-colors hover:bg-red-50 flex-shrink-0"
                  style="color: #ef4444"
                >
                  <i class="fa-solid fa-trash-can"></i>
                </button>
              </div>
            </div>
          </section>
        </div>

        <!-- ─── Onglet 2 : Localisation ──────────────────────────────── -->
        <div v-show="activeTab === 'localisation'" class="p-6">
          <h3 class="text-xs font-bold uppercase tracking-wider mb-4" style="color: #f97316">
            Localisation GPS
          </h3>
          <MapPicker
            :model-value="{ lat: form.latitude, lng: form.longitude }"
            @update:modelValue="onMapUpdate"
          />
        </div>

        <!-- ─── Onglet 3 : Historique (édition uniquement) ────────────── -->
        <div v-show="activeTab === 'historique'" class="p-6">
          <h3 class="text-xs font-bold uppercase tracking-wider mb-4" style="color: #f97316">
            Historique du panneau
          </h3>
          <HistoriquePanneau
            :events="historique"
            :loading="historiqueLoading"
          />
        </div>

      </form>

      <!-- Footer -->
      <div
        class="p-4 border-t flex justify-end gap-3 flex-shrink-0"
        style="border-color: #e5e7eb; background-color: #f9fafb"
      >
        <button
          type="button"
          @click="$emit('close')"
          class="px-4 py-2 text-sm font-medium rounded-lg transition-colors hover:bg-slate-100"
          style="color: #374151"
        >
          Annuler
        </button>
        <button
          v-if="activeTab !== 'historique'"
          type="button"
          @click="handleSubmit"
          :disabled="isLoading"
          class="px-6 py-2 rounded-lg text-white text-sm font-bold shadow-sm
                 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          style="background-color: #f97316"
        >
          <i v-if="isLoading" class="fa-solid fa-circle-notch animate-spin mr-1"></i>
          {{ isEditMode ? 'Mettre à jour' : 'Enregistrer le panneau' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { storeToRefs }          from 'pinia'
import { usePanneauxStore }     from '@/stores/panneaux.store'
import MapPicker                from '@/components/panneaux/MapPicker.vue'
import HistoriquePanneau        from '@/components/panneaux/HistoriquePanneau.vue'

const props = defineProps({
  show:    { type: Boolean, required: true },
  panneau: { type: Object,  default: null  },
  errors:  { type: Object,  default: null  },
})

const emit = defineEmits(['close', 'saved'])

const store = usePanneauxStore()
const { isLoading, historique, historiqueLoading } = storeToRefs(store)

const isEditMode = computed(() => !!props.panneau?.id)

const TABS = [
  { key: 'infos',        label: 'Informations', icon: 'fa-circle-info' },
  { key: 'localisation', label: 'Localisation', icon: 'fa-map-location-dot' },
  { key: 'historique',   label: 'Historique',   icon: 'fa-clock-rotate-left' },
]

// L'onglet Historique n'est visible qu'en mode édition
const visibleTabs = computed(() =>
  isEditMode.value ? TABS : TABS.filter(t => t.key !== 'historique'),
)

const activeTab = ref('infos')

// ── Formulaire 
const form = ref({
  reference:   '',
  ville:       '',
  quartier:    '',
  latitude:    null,
  longitude:   null,
  eclaire:     false,
  hauteur_mat: null,
  statut:      'actif',
  faces:       [{ numero: 1, largeur: 0, hauteur: 0 }],
})

// Pré-remplit le formulaire quand la prop panneau change
watch(
  () => props.panneau,
  (newPanneau) => {
    activeTab.value = 'infos'

    if (newPanneau) {
      form.value = {
        reference:   newPanneau.reference   ?? '',
        ville:       newPanneau.ville       ?? '',
        quartier:    newPanneau.quartier    ?? '',
        latitude:    newPanneau.latitude    ?? null,
        longitude:   newPanneau.longitude   ?? null,
        eclaire:     newPanneau.eclaire     ?? false,
        hauteur_mat: newPanneau.hauteur_mat ?? null,
        statut:      newPanneau.statut      ?? 'actif',
        faces: newPanneau.faces
          ? newPanneau.faces.map(f => ({ ...f }))
          : [{ numero: 1, largeur: 0, hauteur: 0 }],
      }
    }
  },
  { immediate: true },
)

// Lazy loading historique — chargé uniquement quand l'onglet est activé
watch(activeTab, (tab) => {
  if (tab === 'historique' && props.panneau?.id) {
    store.fetchHistorique(props.panneau.id)
  }
})

// ── Handlers 
function onMapUpdate({ lat, lng }) {
  form.value.latitude  = lat
  form.value.longitude = lng
}

function ajouterFace() {
  form.value.faces.push({
    numero:  form.value.faces.length + 1,
    largeur: 0,
    hauteur: 0,
  })
}

function supprimerFace(index) {
  form.value.faces.splice(index, 1)
  form.value.faces.forEach((f, i) => { f.numero = i + 1 })
}

async function handleSubmit() {
  try {
    if (isEditMode.value) {
      const payload = {
        pays:        form.value.pays,
        ville:       form.value.ville,
        quartier:    form.value.quartier,
        adresse:     form.value.adresse,
        latitude:    form.value.latitude,
        longitude:   form.value.longitude,
        eclaire:     form.value.eclaire,
        hauteur_mat: form.value.hauteur_mat,
        statut:      form.value.statut,
      }
      await store.updatePanneau(props.panneau.id, payload)
    } else {
      await store.createPanneau(form.value)
    }
    emit('saved')
  } catch {
    // Erreurs 422 dans store.errors
  }
}
</script>
