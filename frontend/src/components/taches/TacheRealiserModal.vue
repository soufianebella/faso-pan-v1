<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="background-color: rgba(15,23,42,0.7)"
    @click.self="$emit('close')"
  >
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg">

      <!-- Header -->
      <div
        class="p-4 border-b flex justify-between items-center rounded-t-xl"
        style="background-color: #F9FAFB; border-color: #E5E7EB"
      >
        <div>
          <h3 class="text-lg font-bold" style="color: #1B3B8A">
            Marquer comme realisee
          </h3>
          <p class="text-xs mt-0.5" style="color: #6B7280">
            Photo de la pose obligatoire (preuve terrain).
          </p>
        </div>
        <button
          @click="$emit('close')"
          class="p-1 rounded-full hover:bg-slate-100 transition-colors"
        >
          <i class="fa-solid fa-xmark text-xl" style="color: #6B7280"></i>
        </button>
      </div>

      <!-- Rappel tache -->
      <div class="px-6 pt-4">
        <div
          class="flex items-start gap-3 p-3 rounded-lg border"
          style="background-color: #F0F4FF; border-color: #C7D7F5"
        >
          <i class="fa-solid fa-billboard text-base mt-0.5" style="color: #1B3B8A"></i>
          <div class="text-xs leading-relaxed" style="color: #374151">
            <p class="font-bold" style="color: #1B3B8A">
              {{ tache.affectation?.campagne?.nom ?? 'Campagne' }}
            </p>
            <p>
              {{ tache.affectation?.face?.panneau?.reference ?? 'N/A' }}
              — Face {{ tache.affectation?.face?.numero ?? '?' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Corps -->
      <div class="p-6 space-y-5">

        <!-- Upload photo -->
        <div class="space-y-1">
          <label class="text-xs font-bold uppercase tracking-wider" style="color: #6B7280">
            Photo de la pose <span style="color: #EF4444">*</span>
          </label>

          <!-- Zone dropzone / preview -->
          <div v-if="!previewUrl"
            class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-colors"
            :style="photoError ? 'border-color: #EF4444' : 'border-color: #E5E7EB'"
            @click="$refs.fileInput.click()"
            @dragover.prevent
            @drop.prevent="handleDrop"
          >
            <i class="fa-solid fa-cloud-arrow-up text-3xl mb-2" style="color: #1B3B8A"></i>
            <p class="text-sm font-semibold" style="color: #1C2833">
              Cliquez ou glissez une image ici
            </p>
            <p class="text-xs mt-1" style="color: #6B7280">
              JPEG, PNG, WEBP — max 5 Mo
            </p>
          </div>

          <div v-else class="relative rounded-lg overflow-hidden border" style="border-color: #E5E7EB">
            <img :src="previewUrl" alt="Preview" class="w-full h-56 object-cover" />
            <button
              type="button"
              @click="resetPhoto"
              class="absolute top-2 right-2 h-8 w-8 rounded-full flex items-center justify-center shadow-md"
              style="background-color: rgba(239,68,68,0.9); color: white"
              title="Retirer la photo"
            >
              <i class="fa-solid fa-trash text-xs"></i>
            </button>
            <div
              class="absolute bottom-0 left-0 right-0 px-3 py-1.5 text-xs flex justify-between"
              style="background-color: rgba(0,0,0,0.6); color: white"
            >
              <span class="truncate">{{ fileName }}</span>
              <span>{{ fileSize }}</span>
            </div>
          </div>

          <input
            ref="fileInput"
            type="file"
            accept="image/jpeg,image/jpg,image/png,image/webp"
            class="hidden"
            @change="handleFileChange"
          />

          <p v-if="photoError" class="text-xs mt-1" style="color: #EF4444">
            {{ photoError }}
          </p>
          <p v-else-if="errors?.photo" class="text-xs mt-1" style="color: #EF4444">
            {{ errors.photo[0] }}
          </p>
        </div>

        <!-- Note -->
        <div class="space-y-1">
          <label class="text-xs font-bold uppercase tracking-wider" style="color: #6B7280">
            Note
            <span class="font-normal normal-case ml-1" style="color: #9CA3AF">(optionnel)</span>
          </label>
          <textarea
            v-model="note"
            rows="2"
            placeholder="Observations terrain, difficultes rencontrees..."
            class="w-full border rounded-lg px-3 py-2 text-sm outline-none resize-none"
            style="border-color: #E5E7EB"
            @focus="$event.target.style.borderColor='#F97316'"
            @blur="$event.target.style.borderColor='#E5E7EB'"
          ></textarea>
        </div>

        <!-- GPS (auto, silencieux) -->
        <div v-if="gps.latitude" class="flex items-center gap-2 text-xs" style="color: #6B7280">
          <i class="fa-solid fa-location-dot" style="color: #27AE60"></i>
          Position GPS capturee : {{ gps.latitude.toFixed(5) }}, {{ gps.longitude.toFixed(5) }}
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
          class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors hover:bg-slate-100"
          style="color: #374151"
        >
          Annuler
        </button>

        <button
          type="button"
          @click="handleSubmit"
          :disabled="isLoading || !photo"
          class="px-6 py-2 rounded-lg text-white text-sm font-bold shadow-sm transition-all
                 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
          style="background-color: #F97316"
        >
          <i v-if="isLoading" class="fa-solid fa-circle-notch animate-spin"></i>
          <i v-else class="fa-solid fa-check text-xs"></i>
          Confirmer la realisation
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { storeToRefs }                      from 'pinia'
import { useTachesStore }                   from '@/stores/taches.store'

const props = defineProps({
  tache: { type: Object, required: true },
})

const emit = defineEmits(['close', 'saved'])

const store = useTachesStore()
const { isLoading, errors } = storeToRefs(store)

const photo       = ref(null)
const previewUrl  = ref(null)
const fileName    = ref('')
const fileSize    = ref('')
const note        = ref('')
const photoError  = ref(null)
const gps         = ref({ latitude: null, longitude: null })

const MAX_SIZE_MB      = 5
const ACCEPTED_MIMES   = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']

// ── GPS capture silencieuse au montage
onMounted(() => {
  if (!navigator.geolocation) return
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      gps.value.latitude  = pos.coords.latitude
      gps.value.longitude = pos.coords.longitude
    },
    () => { /* refus utilisateur — on ignore, c'est optionnel */ },
    { timeout: 5000, maximumAge: 60000 }
  )
})

onBeforeUnmount(() => {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
})

function setPhoto(file) {
  photoError.value = null

  if (!ACCEPTED_MIMES.includes(file.type)) {
    photoError.value = 'Format non supporte. Utilise JPEG, PNG ou WEBP.'
    return
  }

  if (file.size > MAX_SIZE_MB * 1024 * 1024) {
    photoError.value = `Image trop lourde (max ${MAX_SIZE_MB} Mo).`
    return
  }

  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)

  photo.value      = file
  previewUrl.value = URL.createObjectURL(file)
  fileName.value   = file.name
  fileSize.value   = formatSize(file.size)
}

function formatSize(bytes) {
  if (bytes < 1024)        return bytes + ' o'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' Ko'
  return (bytes / 1024 / 1024).toFixed(2) + ' Mo'
}

function handleFileChange(e) {
  const file = e.target.files?.[0]
  if (file) setPhoto(file)
}

function handleDrop(e) {
  const file = e.dataTransfer.files?.[0]
  if (file) setPhoto(file)
}

function resetPhoto() {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
  photo.value      = null
  previewUrl.value = null
  fileName.value   = ''
  fileSize.value   = ''
}

async function handleSubmit() {
  if (!photo.value) {
    photoError.value = 'La photo est obligatoire.'
    return
  }

  try {
    await store.avancerTache(props.tache.id, {
      photo:          photo.value,
      note:           note.value || null,
      latitude_pose:  gps.value.latitude,
      longitude_pose: gps.value.longitude,
    })
    emit('saved')
  } catch (err) {
    if (err.response?.status !== 422) {
      alert(err.response?.data?.message ?? 'Erreur lors de la soumission.')
    }
  }
}
</script>
