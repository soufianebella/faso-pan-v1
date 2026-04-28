<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center"
    style="background-color: rgba(15, 23, 42, 0.7)"
    @click.self="$emit('close')"
  >
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden">
      <!-- Header -->
      <div class="p-4 border-b flex justify-between items-center" style="border-color: #e5e7eb">
        <div>
          <h2 class="text-lg font-bold" style="color: #1b3b8a">
            Changer le statut
          </h2>
          <p class="text-xs mt-0.5" style="color: #6b7280">
            {{ panneau?.reference }} — {{ panneau?.ville }}
          </p>
        </div>
        <button
          @click="$emit('close')"
          class="p-1 rounded-full hover:bg-slate-100 transition-colors"
        >
          <i class="fa-solid fa-xmark text-lg" style="color: #6b7280"></i>
        </button>
      </div>

      <!-- Corps -->
      <div class="p-5 space-y-5">
        <!-- Statut actuel -->
        <div class="flex items-center gap-3 p-3 rounded" style="background-color: #f0f4ff">
          <span class="text-xs font-medium" style="color: #6b7280">Statut actuel :</span>
          <span
            :class="getStatusClass(panneau?.statut)"
            class="px-2.5 py-0.5 rounded-full text-xs font-medium"
          >
            <i class="fa-solid mr-1" :class="getStatusIcon(panneau?.statut)"></i>
            {{ formatStatut(panneau?.statut) }}
          </span>
        </div>

        <!-- Sélection nouveau statut -->
        <div class="space-y-2">
          <label class="text-xs font-medium" style="color: #374151">
            Nouveau statut <span style="color: #ef4444">*</span>
          </label>
          <div class="grid grid-cols-3 gap-2">
            <button
              v-for="opt in STATUT_OPTIONS"
              :key="opt.value"
              type="button"
              @click="form.statut = opt.value"
              :disabled="opt.value === panneau?.statut"
              class="flex flex-col items-center gap-1.5 p-3 rounded border text-xs font-medium
                     transition-all disabled:opacity-40 disabled:cursor-not-allowed"
              :style="form.statut === opt.value
                ? `border-color: ${opt.color}; background-color: ${opt.bg}; color: ${opt.color}`
                : 'border-color: #E5E7EB; color: #6B7280'"
            >
              <i class="fa-solid text-base" :class="opt.icon"></i>
              {{ opt.label }}
            </button>
          </div>
          <p v-if="errors?.statut" class="text-xs" style="color: #ef4444">
            {{ errors.statut[0] }}
          </p>
        </div>

        <!-- Motif -->
        <div class="space-y-1">
          <label class="text-xs font-medium" style="color: #374151">
            Justification <span style="color: #ef4444">*</span>
            <span class="font-normal ml-1" style="color: #9ca3af">(min. 10 caractères)</span>
          </label>
          <textarea
            v-model="form.motif"
            rows="3"
            placeholder="Décrivez la raison de ce changement de statut…"
            class="w-full border rounded px-3 py-2 text-sm outline-none resize-none transition-all"
            :style="errors?.motif ? 'border-color: #EF4444' : 'border-color: #E5E7EB'"
            @focus="$event.target.style.borderColor = '#F97316'"
            @blur="$event.target.style.borderColor = errors?.motif ? '#EF4444' : '#E5E7EB'"
          ></textarea>
          <div class="flex justify-between">
            <p v-if="errors?.motif" class="text-xs" style="color: #ef4444">
              {{ errors.motif[0] }}
            </p>
            <p
              class="text-xs ml-auto"
              :style="form.motif.length < 10 ? 'color: #EF4444' : 'color: #9CA3AF'"
            >
              {{ form.motif.length }} / 500
            </p>
          </div>
        </div>
      </div>

      <!-- Hint — visible si statut choisi mais motif trop court -->
      <div
        v-if="form.statut && form.motif.length < 10"
        class="mx-5 mb-1 flex items-center gap-2 text-xs px-3 py-2 rounded"
        style="background-color: #FEF3DC; color: #92400E"
      >
        <i class="fa-solid fa-triangle-exclamation flex-shrink-0"></i>
        Ajoutez une justification ({{ 10 - form.motif.length }} caractère{{ 10 - form.motif.length > 1 ? 's' : '' }} manquant{{ 10 - form.motif.length > 1 ? 's' : '' }})
      </div>

      <!-- Footer -->
      <div
        class="p-4 border-t flex justify-end gap-3"
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

        <!-- Bouton désactivé : gris clair explicite → pas d'ambiguïté visuelle -->
        <button
          type="button"
          @click="handleSubmit"
          :disabled="isLoading || !canSubmit"
          class="px-5 py-2 rounded-lg text-sm font-bold shadow-sm transition-colors flex items-center gap-2"
          :style="(isLoading || !canSubmit)
            ? 'background-color: #D1D5DB; color: #9CA3AF; cursor: not-allowed'
            : 'background-color: #1b3b8a; color: #ffffff; cursor: pointer'"
          @mouseenter="!isLoading && canSubmit && ($event.currentTarget.style.backgroundColor = '#152e6e')"
          @mouseleave="!isLoading && canSubmit && ($event.currentTarget.style.backgroundColor = '#1b3b8a')"
        >
          <i v-if="isLoading" class="fa-solid fa-circle-notch animate-spin"></i>
          <i v-else class="fa-solid fa-check"></i>
          Confirmer le changement
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { storeToRefs }          from 'pinia'
import { usePanneauxStore }     from '@/stores/panneaux.store'
import { useToast }             from '@/composables/useToast'

const props = defineProps({
  show:    { type: Boolean, required: true },
  panneau: { type: Object,  default: null  },
})

const emit = defineEmits(['close', 'saved'])

const store             = usePanneauxStore()
const { isLoading, errors } = storeToRefs(store)
const toast             = useToast()

const STATUT_OPTIONS = [
  {
    value: 'actif',
    label: 'Actif',
    icon:  'fa-circle-check',
    color: '#27AE60',
    bg:    '#F0FBF4',
  },
  {
    value: 'maintenance',
    label: 'Maintenance',
    icon:  'fa-triangle-exclamation',
    color: '#F97316',
    bg:    '#FFF7ED',
  },
  {
    value: 'hors_service',
    label: 'Hors service',
    icon:  'fa-circle-xmark',
    color: '#EF4444',
    bg:    '#FEF2F2',
  },
]

const form = ref({ statut: '', motif: '' })

// Réinitialiser à l'ouverture
watch(
  () => props.show,
  (val) => {
    if (val) {
      form.value = { statut: '', motif: '' }
      store.clearErrors()
    }
  },
)

const canSubmit = computed(
  () => form.value.statut !== '' && form.value.motif.length >= 10,
)

async function handleSubmit() {
  try {
    await store.changerStatut(props.panneau.id, form.value)
    toast.success(`Statut mis à jour : ${formatStatut(form.value.statut)}`)
    emit('saved')
  } catch (err) {
    // Les erreurs 422 sont déjà dans store.errors (affichées dans le formulaire)
    // Pour toute autre erreur (500, réseau...) → toast explicite
    if (!err.response || err.response.status !== 422) {
      toast.error('Erreur lors du changement de statut. Veuillez réessayer.')
    }
  }
}

// ── Helpers ────────────────────────────────────────────────────────────────
const STATUT_LABELS = {
  actif:        'Actif',
  maintenance:  'Maintenance',
  hors_service: 'Hors service',
}

function formatStatut(s) {
  return STATUT_LABELS[s] ?? s
}

function getStatusClass(s) {
  if (s === 'actif')        return 'bg-green-100 text-green-700'
  if (s === 'maintenance')  return 'bg-orange-100 text-orange-700'
  if (s === 'hors_service') return 'bg-red-100 text-red-700'
  return 'bg-gray-100 text-gray-600'
}

function getStatusIcon(s) {
  if (s === 'actif')        return 'fa-circle-check'
  if (s === 'maintenance')  return 'fa-triangle-exclamation'
  if (s === 'hors_service') return 'fa-circle-xmark'
  return 'fa-circle'
}
</script>
