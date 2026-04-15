<template>
  <div class="space-y-3">

    <!-- En-tete -->
    <div class="flex justify-between items-center">
      <label class="text-xs font-bold uppercase tracking-wider"
             style="color: #6B7280">
        Selection des faces
      </label>
      <span
        v-if="modelValue.length"
        class="text-xs font-bold"
        style="color: #F97316"
      >
        {{ modelValue.length }} selectionnee(s)
      </span>
    </div>

    <!-- Message si dates manquantes -->
    <div
      v-if="datesMissing"
      class="py-8 text-center rounded-lg border-2 border-dashed"
      style="border-color: #E5E7EB; color: #9CA3AF"
    >
      <i class="fa-solid fa-calendar-days text-2xl mb-2 block"></i>
      <p class="text-xs font-medium">
        Selectionnez une periode pour voir les faces disponibles
      </p>
    </div>

    <!-- Message si aucune face disponible -->
    <div
      v-else-if="faces.length === 0"
      class="py-8 text-center rounded-lg border-2 border-dashed"
      style="border-color: #E5E7EB; color: #9CA3AF"
    >
      <i class="fa-solid fa-circle-xmark text-2xl mb-2 block"></i>
      <p class="text-xs font-medium">
        Aucune face disponible sur cette periode
      </p>
    </div>

    <!-- Grille des faces -->
    <div
      v-else
      class="grid grid-cols-3 gap-3 max-h-64 overflow-y-auto p-1"
    >
      <div
        v-for="face in faces"
        :key="face.id"
        @click="toggleFace(face)"
        class="p-3 rounded-lg border-2 transition-all flex flex-col gap-1 relative"
        :style="getCardStyle(face)"
        :class="face.statut === 'occupee' ? 'cursor-not-allowed' : 'cursor-pointer'"
      >
        <!-- Icone verrou si occupee -->
        <i
          v-if="face.statut === 'occupee'"
          class="fa-solid fa-lock absolute top-2 right-2 text-xs"
          style="color: #9CA3AF"
        ></i>

        <!-- Icone check si selectionnee -->
        <i
          v-if="isSelected(face.id)"
          class="fa-solid fa-circle-check absolute top-2 right-2 text-sm"
          style="color: #F97316"
        ></i>

        <span class="text-xs font-bold pr-4" style="color: #1C2833">
          {{ face.panneau?.reference }} — Face {{ face.numero }}
        </span>

        <span class="text-xs" style="color: #6B7280">
          <i class="fa-solid fa-location-dot mr-1"></i>
          {{ face.panneau?.ville }}
          <span v-if="face.panneau?.quartier"> — {{ face.panneau.quartier }}</span>
        </span>

        <span
          class="text-xs px-1.5 py-0.5 rounded self-start mt-1 font-semibold"
          style="background-color: #F3F4F6; color: #374151"
        >
          {{ face.largeur }}m x {{ face.hauteur }}m = {{ face.surface }}m²
        </span>
      </div>
    </div>

  </div>
</template>
<script setup>
const props = defineProps({
  faces:        { type: Array,   default: () => [] },
  modelValue:   { type: Array,   default: () => [] },
  datesMissing: { type: Boolean, default: false     },
})

const emit = defineEmits(['update:modelValue'])

function isSelected(id) {
  return props.modelValue.includes(id)
}

function getCardStyle(face) {
  if (face.statut === 'occupee') {
    return {
      backgroundColor: '#F9FAFB',
      borderColor:     '#E5E7EB',
      opacity:         '0.5',
    }
  }
  if (isSelected(face.id)) {
    return {
      backgroundColor: '#FEF3DC',
      borderColor:     '#F97316',
    }
  }
  return {
    backgroundColor: '#FFFFFF',
    borderColor:     '#E5E7EB',
  }
}

function toggleFace(face) {
  if (face.statut === 'occupee') return

  const selected = [...props.modelValue]
  const index    = selected.indexOf(face.id)

  if (index > -1) {
    selected.splice(index, 1)
  } else {
    selected.push(face.id)
  }

  emit('update:modelValue', selected)
}
</script>