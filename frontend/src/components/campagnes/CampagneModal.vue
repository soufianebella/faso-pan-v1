<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="background-color: rgba(15,23,42,0.7)"
    @click.self="$emit('close')"
  >
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl
                flex flex-col max-h-[90vh]">

      <!-- Header -->
      <div
        class="p-4 border-b flex justify-between items-center rounded-t-xl"
        style="background-color: #F9FAFB; border-color: #E5E7EB"
      >
        <h2 class="font-bold text-lg" style="color: #1B3B8A">
          Nouvelle campagne
        </h2>
        <button
          @click="$emit('close')"
          class="p-1 rounded-full hover:bg-slate-100 transition-colors"
        >
          <i class="fa-solid fa-xmark text-xl" style="color: #6B7280"></i>
        </button>
      </div>

      <!-- Corps -->
      <div class="p-6 overflow-y-auto space-y-5 flex-1">

        <!-- Nom -->
        <div class="space-y-1">
          <label class="text-xs font-bold uppercase tracking-wider"
                 style="color: #6B7280">
            Nom de la campagne
          </label>
          <input
            v-model="form.nom"
            type="text"
            placeholder="Ex: Promo Tabaski 2026"
            class="w-full border rounded-lg px-3 py-2 text-sm outline-none transition-all"
            :style="errors?.nom ? 'border-color: #EF4444' : 'border-color: #E5E7EB'"
            @focus="$event.target.style.borderColor='#F97316'"
            @blur="$event.target.style.borderColor=errors?.nom ? '#EF4444' : '#E5E7EB'"
          />
          <p v-if="errors?.nom" class="text-xs" style="color: #EF4444">
            {{ errors.nom[0] }}
          </p>
        </div>

        <!-- Annonceur -->
        <div class="space-y-1">
          <label class="text-xs font-bold uppercase tracking-wider"
                 style="color: #6B7280">
            Annonceur
          </label>
          <input
            v-model="form.annonceur"
            type="text"
            placeholder="Nom du client"
            class="w-full border rounded-lg px-3 py-2 text-sm outline-none transition-all"
            style="border-color: #E5E7EB"
            @focus="$event.target.style.borderColor='#F97316'"
            @blur="$event.target.style.borderColor='#E5E7EB'"
          />
        </div>

        <!-- Description -->
        <div class="space-y-1">
          <label class="text-xs font-bold uppercase tracking-wider"
                 style="color: #6B7280">
            Description (optionnel)
          </label>
          <textarea
            v-model="form.description"
            rows="2"
            class="w-full border rounded-lg px-3 py-2 text-sm outline-none"
            style="border-color: #E5E7EB"
          ></textarea>
        </div>

        <!-- Dates -->
        <div
          class="grid grid-cols-2 gap-4 p-4 rounded-lg border"
          style="background-color: #F0F4FF; border-color: #C7D7F5"
        >
          <div class="space-y-1">
            <label class="text-xs font-bold uppercase tracking-wider"
                   style="color: #1B3B8A">
              Date debut
            </label>
            <input
              v-model="form.date_debut"
              type="date"
              class="w-full border rounded-lg px-3 py-2 text-sm outline-none bg-white"
              style="border-color: #C7D7F5"
            />
            <p v-if="errors?.date_debut" class="text-xs" style="color: #EF4444">
              {{ errors.date_debut[0] }}
            </p>
          </div>

          <div class="space-y-1">
            <label class="text-xs font-bold uppercase tracking-wider"
                   style="color: #1B3B8A">
              Date fin
            </label>
            <input
              v-model="form.date_fin"
              type="date"
              class="w-full border rounded-lg px-3 py-2 text-sm outline-none bg-white"
              style="border-color: #C7D7F5"
            />
            <p v-if="errors?.date_fin" class="text-xs" style="color: #EF4444">
              {{ errors.date_fin[0] }}
            </p>
          </div>
        </div>

        <!-- Selection des faces -->
        <div class="space-y-1">

          <!-- Skeleton pendant le chargement -->
          <div
            v-if="isLoadingFaces"
            class="flex flex-col items-center justify-center py-10 gap-3"
          >
            <div
              class="w-8 h-8 rounded-full border-4 animate-spin"
              style="border-color: #E5E7EB; border-top-color: #1B3B8A"
            ></div>
            <span class="text-xs font-medium" style="color: #6B7280">
              Analyse des disponibilites...
            </span>
          </div>

          <FaceSelector
            v-else
            v-model="form.face_ids"
            :faces="facesDisponibles"
            :dates-missing="!form.date_debut || !form.date_fin"
          />

          <!-- Erreur conflit faces -->
          <div
            v-if="conflitErrors.length"
            class="mt-2 p-3 rounded-lg text-xs font-semibold"
            style="background-color: #FEF2F2; color: #EF4444"
          >
            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
            {{ conflitErrors.length }} face(s) en conflit sur cette periode.
          </div>

        </div>
      </div>

      <!-- Footer -->
      <div
        class="p-4 border-t rounded-b-xl flex justify-end items-center gap-3"
        style="background-color: #F9FAFB; border-color: #E5E7EB"
      >
        <button
          type="button"
          @click="$emit('close')"
          class="px-4 py-2 text-sm font-semibold rounded-lg
                 transition-colors hover:bg-slate-100"
          style="color: #374151"
        >
          Annuler
        </button>

        <button
          type="button"
          @click="handleSubmit"
          :disabled="isLoading || form.face_ids.length === 0"
          class="px-6 py-2 rounded-lg text-white text-sm font-bold
                 shadow-sm transition-all flex items-center gap-2
                 disabled:opacity-50 disabled:cursor-not-allowed"
          style="background-color: #F97316"
        >
          <i v-if="isLoading" class="fa-solid fa-circle-notch animate-spin"></i>
          Confirmer la campagne
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { storeToRefs }          from 'pinia'
import { useCampagnesStore }    from '@/stores/campagnes.store'
import FaceSelector             from './FaceSelector.vue'

const props = defineProps({
  show: { type: Boolean, required: true },
})

const emit = defineEmits(['close', 'saved'])

const store = useCampagnesStore()
const { facesDisponibles, isLoading, isLoadingFaces, errors } = storeToRefs(store)

const form = ref({
  nom:         '',
  annonceur:   '',
  description: '',
  date_debut:  '',
  date_fin:    '',
  face_ids:    [],
})

// Erreurs de conflit extraites de errors (face_ids.X)
const conflitErrors = computed(() => {
  if (!errors.value) return []
  return Object.keys(errors.value)
    .filter(k => k.startsWith('face_ids.'))
})

// Watch critique sur les dates
// Surveille campagneActuelle du store
// pour pré-remplir le form en mode édition
watch(
  () => store.campagneActuelle,
  (campagne) => {
    if (campagne && campagne.id) {
      // Mode édition — pré-remplir
      form.value = {
        nom:         campagne.nom         ?? '',
        annonceur:   campagne.annonceur   ?? '',
        description: campagne.description ?? '',
        date_debut:  campagne.date_debut  ?? '',
        date_fin:    campagne.date_fin    ?? '',
        face_ids:    [],
      }
    } else {
      // Mode création — formulaire vide
      form.value = {
        nom:         '',
        annonceur:   '',
        description: '',
        date_debut:  '',
        date_fin:    '',
        face_ids:    [],
      }
    }
  },
  { immediate: true }
)

// Watch sur les dates → charge les faces disponibles
watch(
  [() => form.value.date_debut, () => form.value.date_fin],
  ([debut, fin]) => {
    form.value.face_ids = []
    store.clearErrors()

    if (debut && fin && fin >= debut) {
      store.fetchAvailableFaces(debut, fin)
    } else {
      store.resetFacesDisponibles()
    }
  }
)

function resetForm() {
  form.value = {
    nom:         '',
    annonceur:   '',
    description: '',
    date_debut:  '',
    date_fin:    '',
    face_ids:    [],
  }
  store.resetFacesDisponibles()
  store.clearErrors()
}

async function handleSubmit() {
  try {
    await store.createCampagne(form.value)
    emit('saved')
    resetForm()
  } catch {
    // Erreurs 422 affichees via store.errors dans le template
  }
}
</script>