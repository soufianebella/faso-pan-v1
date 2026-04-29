<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="background-color: rgba(15,23,42,0.7)"
    @click.self="$emit('close')"
  >
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">

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

      <!-- Zone 1 — Upload photo -->
      <div class="p-6 pb-4">

        <!-- Dropzone vide -->
        <div
          v-if="!previewUrl"
          class="border-2 border-dashed rounded-xl flex flex-col items-center justify-center cursor-pointer transition-colors"
          style="height: 192px; border-color: #E5E7EB"
          :style="photoError ? 'border-color: #EF4444' : ''"
          @click="$refs.fileInput.click()"
          @dragover.prevent
          @drop.prevent="handleDrop"
          @mouseenter="$event.currentTarget.style.borderColor = photoError ? '#EF4444' : '#F97316'"
          @mouseleave="$event.currentTarget.style.borderColor = photoError ? '#EF4444' : '#E5E7EB'"
        >
          <i class="fa-solid fa-camera text-4xl mb-3" style="color: #6B7280"></i>
          <p class="text-sm font-semibold" style="color: #1C2833">Cliquez pour prendre une photo</p>
          <p class="text-xs mt-1" style="color: #9CA3AF">JPEG, PNG, WEBP — max 5 Mo</p>
        </div>

        <!-- Preview photo sélectionnée -->
        <div
          v-else
          class="relative rounded-xl overflow-hidden"
          style="height: 192px"
        >
          <img :src="previewUrl" alt="Preview" class="w-full h-full object-cover" />
          <button
            type="button"
            @click="resetPhoto"
            class="absolute top-2 right-2 h-8 w-8 rounded-full flex items-center justify-center shadow-md"
            style="background-color: rgba(239,68,68,0.9); color: white"
            title="Changer la photo"
          >
            <i class="fa-solid fa-xmark text-xs"></i>
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

        <p v-if="photoError" class="text-xs mt-2" style="color: #EF4444">{{ photoError }}</p>
        <p v-else-if="errors?.photo" class="text-xs mt-2" style="color: #EF4444">{{ errors.photo[0] }}</p>

      </div>

      <!-- Zone 2 — Bouton soumettre pleine largeur -->
      <div class="px-6 pb-6">
        <button
          type="button"
          @click="handleSubmit"
          :disabled="isLoading || !photo"
          class="w-full flex items-center justify-center gap-2 py-3 rounded-lg text-white
                 text-sm font-bold shadow-sm transition-colors
                 disabled:opacity-50 disabled:cursor-not-allowed"
          style="background-color: #F97316"
          @mouseenter="$event.currentTarget.style.backgroundColor='#EA6C0A'"
          @mouseleave="$event.currentTarget.style.backgroundColor='#F97316'"
        >
          <i v-if="isLoading" class="fa-solid fa-circle-notch animate-spin"></i>
          <i v-else class="fa-solid fa-check"></i>
          Marquer comme realisee
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
const photoError  = ref(null)
const gps         = ref({ latitude: null, longitude: null })

const MAX_SIZE_MB    = 5
const ACCEPTED_MIMES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']

// ── GPS capturé silencieusement — envoyé avec la photo si disponible
onMounted(() => {
  if (!navigator.geolocation) return
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      gps.value.latitude  = pos.coords.latitude
      gps.value.longitude = pos.coords.longitude
    },
    () => { /* refus utilisateur — optionnel, on ignore */ },
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
      note:           null,
      latitude_pose:  gps.value.latitude,
      longitude_pose: gps.value.longitude,
    })
    emit('saved')
  } catch (err) {
    if (err.response?.status !== 422) {
      photoError.value = err.response?.data?.message ?? 'Erreur lors de la soumission.'
    }
  }
}
</script>
