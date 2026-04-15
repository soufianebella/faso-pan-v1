<template>
  <div class="relative overflow-x-auto">
    <table class="w-full text-sm text-left">
      <thead style="background-color: #1B3B8A">
        <tr>
          <th class="px-6 py-3 text-xs font-semibold uppercase text-white">
            Campagne / Annonceur
          </th>
          <th class="px-6 py-3 text-xs font-semibold uppercase text-white text-center">
            Periode
          </th>
          <th class="px-6 py-3 text-xs font-semibold uppercase text-white text-center">
            Faces
          </th>
          <th class="px-6 py-3 text-xs font-semibold uppercase text-white text-center">
            Statut
          </th>
          <th class="px-6 py-3 text-xs font-semibold uppercase text-white text-right">
            Actions
          </th>
        </tr>
      </thead>

      <tbody class="divide-y" style="border-color: #F3F4F6">

        <tr v-if="campagnes.length === 0">
          <td colspan="5" class="px-6 py-12 text-center" style="color: #9CA3AF">
            <div class="flex flex-col items-center gap-2">
              <i class="fa-solid fa-bullhorn text-3xl"></i>
              <span class="text-sm font-medium">Aucune campagne enregistree</span>
            </div>
          </td>
        </tr>

        <tr
          v-for="campagne in campagnes"
          :key="campagne.id"
          class="bg-white transition-colors hover:bg-slate-50"
        >
          <td class="px-6 py-4">
            <div class="font-bold text-sm" style="color: #1C2833">
              {{ campagne.nom }}
            </div>
            <div class="text-xs mt-0.5" style="color: #6B7280">
              {{ campagne.annonceur }}
            </div>
          </td>

          <td class="px-6 py-4 text-center">
            <div class="text-xs font-medium" style="color: #374151">
              {{ campagne.date_debut }}
            </div>
            <div class="text-xs" style="color: #6B7280">
              {{ campagne.date_fin }}
            </div>
          </td>

          <td class="px-6 py-4 text-center">
            <span
              class="px-2 py-1 rounded-full text-xs font-bold"
              style="background-color: #EBF3FC; color: #1B3B8A"
            >
              {{ campagne.affectations_count || 0 }} face(s)
            </span>
          </td>

          <td class="px-6 py-4 text-center">
            <span
              class="px-2.5 py-1 rounded-full text-xs font-bold uppercase"
              :style="getStatutStyle(campagne.statut)"
            >
              {{ formatStatut(campagne.statut) }}
            </span>
          </td>

          <td class="px-6 py-4 text-right space-x-2">
            <button
              @click="$emit('edit', campagne)"
              class="p-1.5 transition-colors"
              style="color: #9CA3AF"
              title="Modifier"
              @mouseenter="$event.currentTarget.style.color='#1B3B8A'"
              @mouseleave="$event.currentTarget.style.color='#9CA3AF'"
            >
              <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <button
              @click="$emit('archive', campagne.id)"
              class="p-1.5 transition-colors"
              style="color: #9CA3AF"
              title="Cloturer"
              @mouseenter="$event.currentTarget.style.color='#EF4444'"
              @mouseleave="$event.currentTarget.style.color='#9CA3AF'"
            >
              <i class="fa-solid fa-circle-stop"></i>
            </button>
          </td>
        </tr>

      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  campagnes: {
    type:     Array,
    required: true,
    default:  () => [],
  },
})

defineEmits(['edit', 'archive'])

const STATUT_STYLES = {
  preparation: { backgroundColor: '#EBF3FC', color: '#1B3B8A' },
  active:      { backgroundColor: '#D1FAE5', color: '#065F46' },
  expiree:     { backgroundColor: '#F3F4F6', color: '#6B7280' },
}

const STATUT_LABELS = {
  preparation: 'En preparation',
  active:      'Active',
  expiree:     'Expiree',
}

function getStatutStyle(statut) {
  return STATUT_STYLES[statut] ?? { backgroundColor: '#F3F4F6', color: '#6B7280' }
}

function formatStatut(statut) {
  return STATUT_LABELS[statut] ?? statut
}
</script>