<template>
  <div class="py-2">

    <!-- Skeleton loader -->
    <div v-if="loading" class="space-y-4 px-1">
      <div
        v-for="i in 4"
        :key="i"
        class="flex gap-3"
      >
        <div class="flex flex-col items-center flex-shrink-0">
          <div class="w-8 h-8 rounded-full animate-pulse" style="background-color: #e5e7eb"></div>
          <div class="w-px flex-1 mt-1" style="background-color: #e5e7eb; min-height: 32px"></div>
        </div>
        <div class="flex-1 pb-4 space-y-1.5">
          <div class="h-3.5 w-2/3 rounded animate-pulse" style="background-color: #e5e7eb"></div>
          <div class="h-3 w-1/2 rounded animate-pulse" style="background-color: #f3f4f6"></div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div
      v-else-if="events.length === 0"
      class="flex flex-col items-center gap-3 py-12"
      style="color: #9ca3af"
    >
      <i class="fa-solid fa-clock-rotate-left text-3xl"></i>
      <span class="text-sm font-medium">Aucun historique disponible</span>
      <span class="text-xs">Les événements apparaîtront ici au fur et à mesure.</span>
    </div>

    <!-- Timeline -->
    <ol v-else class="relative px-1">
      <li
        v-for="(evt, i) in events"
        :key="i"
        class="flex gap-3 pb-5"
      >
        <!-- Point + ligne verticale -->
        <div class="flex flex-col items-center flex-shrink-0">
          <div
            class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm"
            :style="`background-color: ${evt.couleur}1A; border: 2px solid ${evt.couleur}`"
          >
            <i
              class="fa-solid text-xs"
              :class="evt.icone"
              :style="`color: ${evt.couleur}`"
            ></i>
          </div>
          <!-- Ligne verticale (sauf dernier élément) -->
          <div
            v-if="i < events.length - 1"
            class="w-px flex-1 mt-1"
            style="background-color: #e5e7eb; min-height: 24px"
          ></div>
        </div>

        <!-- Contenu -->
        <div class="flex-1 pt-0.5">
          <div class="flex items-start justify-between gap-2">
            <p class="text-sm font-semibold leading-tight" style="color: #1c2833">
              {{ evt.titre }}
            </p>
            <span
              class="text-xs px-2 py-0.5 rounded-full flex-shrink-0 font-medium"
              :style="`background-color: ${evt.couleur}1A; color: ${evt.couleur}`"
            >
              {{ TYPE_LABELS[evt.type] ?? evt.type }}
            </span>
          </div>

          <!-- Détail (motif ou note) -->
          <p
            v-if="evt.detail"
            class="text-xs mt-1 italic leading-relaxed"
            style="color: #6b7280"
          >
            "{{ evt.detail }}"
          </p>

          <!-- Date + auteur -->
          <p class="text-xs mt-1" style="color: #9ca3af">
            <i class="fa-solid fa-clock mr-1"></i>{{ evt.date }}
            <span v-if="evt.auteur" class="ml-2">
              <i class="fa-solid fa-user mr-1"></i>{{ evt.auteur }}
            </span>
          </p>
        </div>
      </li>
    </ol>

  </div>
</template>

<script setup>
const props = defineProps({
  events:  { type: Array,   required: true },
  loading: { type: Boolean, default: false },
})

const TYPE_LABELS = {
  statut:   'Statut',
  campagne: 'Campagne',
  tache:    'Tâche',
}
</script>
