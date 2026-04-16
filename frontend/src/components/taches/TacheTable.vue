<template>
  <table class="w-full text-left border-collapse text-sm">

    <thead style="background-color: #1B3B8A">
      <tr>
        <th class="px-6 py-3 text-xs font-semibold uppercase text-white">
          Campagne
        </th>
        <th class="px-6 py-3 text-xs font-semibold uppercase text-white">
          Face / Panneau
        </th>
        <th class="px-6 py-3 text-xs font-semibold uppercase text-white">
          Periode
        </th>
        <th class="px-6 py-3 text-xs font-semibold uppercase text-white">
          Agent
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

      <tr v-if="taches.length === 0">
        <td colspan="6" class="px-6 py-12 text-center" style="color: #9CA3AF">
          <div class="flex flex-col items-center gap-2">
            <i class="fa-solid fa-list-check text-3xl"></i>
            <span class="text-sm font-medium">Aucune tache trouvee</span>
          </div>
        </td>
      </tr>

      <tr
        v-for="tache in taches"
        :key="tache.id"
        class="bg-white transition-colors hover:bg-slate-50"
      >
        <!-- Campagne -->
        <td class="px-6 py-4">
          <div class="font-semibold" style="color: #1C2833">
            {{ tache.affectation?.campagne?.nom ?? 'N/A' }}
          </div>
          <div class="text-xs mt-0.5" style="color: #6B7280">
            {{ tache.affectation?.campagne?.annonceur }}
          </div>
        </td>

        <!-- Face -->
        <td class="px-6 py-4" style="color: #374151">
          {{ tache.affectation?.face?.panneau?.reference ?? 'N/A' }}
          <span class="text-xs ml-1" style="color: #6B7280">
            — Face {{ tache.affectation?.face?.numero }}
          </span>
        </td>

        <!-- Periode -->
        <td class="px-6 py-4">
          <div class="text-xs" style="color: #374151">
            {{ tache.affectation?.date_debut }}
          </div>
          <div class="text-xs" style="color: #6B7280">
            {{ tache.affectation?.date_fin }}
          </div>
        </td>

        <!-- Agent -->
        <td class="px-6 py-4">
          <span
            v-if="tache.agent"
            class="text-sm font-medium"
            style="color: #374151"
          >
            {{ tache.agent.name }}
          </span>
          <span
            v-else
            class="text-xs px-2 py-1 rounded"
            style="background-color: #FEF3DC; color: #F97316"
          >
            Non assigne
          </span>
        </td>

        <!-- Statut -->
        <td class="px-6 py-4 text-center">
          <span
            class="px-2.5 py-1 rounded-full text-xs font-bold uppercase"
            :style="getStatutStyle(tache.statut)"
          >
            {{ formatStatut(tache.statut) }}
          </span>
        </td>

        <!-- Actions -->
        <td class="px-6 py-4 text-right space-x-2">

          <!-- Bouton Assigner — gestionnaire seulement -->
          <button
            v-if="isGestionnaire"
            @click="$emit('assigner', tache)"
            class="p-1.5 transition-colors text-xs font-medium"
            style="color: #1B3B8A"
            title="Assigner un agent"
          >
            <i class="fa-solid fa-user-plus"></i>
          </button>

          <!-- Bouton Avancer — agent sur sa propre tache -->
          <button
            v-if="peutAvancer(tache)"
            @click="$emit('avancer', tache.id)"
            class="p-1.5 transition-colors text-xs font-medium"
            style="color: #F97316"
            title="Avancer le statut"
          >
            <i class="fa-solid fa-circle-arrow-right"></i>
          </button>

        </td>
      </tr>

    </tbody>
  </table>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth.store'

defineProps({
  taches: { type: Array, required: true, default: () => [] },
})

defineEmits(['assigner', 'avancer'])

const auth = useAuthStore()

const isGestionnaire = computed(() =>
  ['super_admin', 'gestionnaire'].includes(auth.user?.role)
)

function peutAvancer(tache) {
  const isOwnTache    = tache.agent?.id === auth.user?.id
  const isProcessable = ['en_attente', 'en_cours'].includes(tache.statut)
  return isOwnTache && isProcessable
}

const STATUT_STYLES = {
  en_attente: { backgroundColor: '#EBF3FC', color: '#1B3B8A' },
  en_cours:   { backgroundColor: '#FEF3DC', color: '#F97316' },
  realisee:   { backgroundColor: '#F3E8FF', color: '#7C3AED' },
  validee:    { backgroundColor: '#D1FAE5', color: '#065F46' },
}

const STATUT_LABELS = {
  en_attente: 'En attente',
  en_cours:   'En cours',
  realisee:   'Realisee',
  validee:    'Validee',
}

function getStatutStyle(statut) {
  return STATUT_STYLES[statut] ?? { backgroundColor: '#F3F4F6', color: '#6B7280' }
}

function formatStatut(statut) {
  return STATUT_LABELS[statut] ?? statut
}
</script>