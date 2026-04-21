<template>
  <div>

    <!-- État vide -->
    <div
      v-if="campagnes.length === 0"
      class="flex flex-col items-center justify-center py-20 gap-3"
    >
      <i class="fa-solid fa-bullhorn text-4xl" style="color: #E5E7EB"></i>
      <p class="text-sm font-medium" style="color: #9CA3AF">Aucune campagne trouvée</p>
    </div>

    <!-- Liste de cartes -->
    <div v-else class="divide-y" style="border-color: #F3F4F6">
      <div
        v-for="campagne in campagnes"
        :key="campagne.id"
        class="flex items-center gap-4 px-5 py-4 bg-white
               transition-colors hover:bg-slate-50 group"
        style="border-left: 3px solid transparent"
        :style="{ borderLeftColor: statutColor(campagne.statut) }"
      >

        <!-- Avatar annonceur -->
        <div
          class="flex-shrink-0 w-9 h-9 rounded-full flex items-center
                 justify-center text-xs font-bold text-white"
          :style="{ backgroundColor: statutColor(campagne.statut) }"
        >
          {{ initiales(campagne.annonceur) }}
        </div>

        <!-- Nom + annonceur -->
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold truncate" style="color: #1C2833">
            {{ campagne.nom }}
          </p>
          <p class="text-xs mt-0.5 truncate" style="color: #6B7280">
            {{ campagne.annonceur }}
          </p>
        </div>

        <!-- Période -->
        <div class="hidden md:flex flex-col items-center text-xs gap-0.5 flex-shrink-0 w-36">
          <div class="flex items-center gap-1.5 w-full" style="color: #374151">
            <i class="fa-regular fa-calendar text-xs" style="color: #9CA3AF"></i>
            <span>{{ formatDate(campagne.date_debut) }}</span>
          </div>
          <div class="flex items-center gap-1.5 w-full" style="color: #374151">
            <i class="fa-solid fa-arrow-right text-xs" style="color: #9CA3AF"></i>
            <span>{{ formatDate(campagne.date_fin) }}</span>
          </div>
        </div>

        <!-- Faces -->
        <div class="hidden sm:flex items-center gap-1.5 flex-shrink-0 w-20">
          <i class="fa-solid fa-layer-group text-xs" style="color: #9CA3AF"></i>
          <span class="text-xs font-medium" style="color: #374151">
            {{ campagne.affectations_count || 0 }} face{{ campagne.affectations_count > 1 ? 's' : '' }}
          </span>
        </div>

        <!-- Statut avec dot -->
        <div class="flex items-center gap-1.5 flex-shrink-0 w-32">
          <span
            class="w-2 h-2 rounded-full flex-shrink-0"
            :style="{ backgroundColor: statutColor(campagne.statut) }"
          ></span>
          <span class="text-xs font-medium" :style="{ color: statutColor(campagne.statut) }">
            {{ STATUT_LABELS[campagne.statut] ?? campagne.statut }}
          </span>
        </div>

        <!-- Actions — visibles au hover -->
        <div class="flex items-center gap-1 flex-shrink-0
                    opacity-0 group-hover:opacity-100 transition-opacity">
          <button
            @click="$emit('edit', campagne)"
            title="Modifier"
            class="p-1.5 rounded-md transition-colors hover:bg-slate-100"
            style="color: #9CA3AF"
          >
            <i class="fa-solid fa-pen text-xs"></i>
          </button>
          <button
            v-if="campagne.statut !== 'expiree'"
            @click="$emit('archive', campagne.id)"
            title="Clôturer"
            class="p-1.5 rounded-md transition-colors hover:bg-red-50"
            style="color: #9CA3AF"
            @mouseenter="$event.currentTarget.style.color='#EF4444'"
            @mouseleave="$event.currentTarget.style.color='#9CA3AF'"
          >
            <i class="fa-solid fa-ban text-xs"></i>
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  campagnes: { type: Array, required: true, default: () => [] },
})

defineEmits(['edit', 'archive'])

const STATUT_COLORS = {
  preparation: '#1B3B8A',
  active:      '#27AE60',
  expiree:     '#EF4444',
}

const STATUT_LABELS = {
  preparation: 'En préparation',
  active:      'Active',
  expiree:     'Expirée',
}

function statutColor(statut) {
  return STATUT_COLORS[statut] ?? '#9CA3AF'
}

function initiales(nom = '') {
  const parts = nom.trim().split(' ')
  return parts.length > 1
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : nom.substring(0, 2).toUpperCase()
}

const MOIS = ['janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.']

function formatDate(dateStr) {
  if (!dateStr) return '—'
  // Prend uniquement la partie YYYY-MM-DD (ignore l'heure si présente)
  const partie = String(dateStr).substring(0, 10)
  const [y, m, d] = partie.split('-')
  if (!y || !m || !d) return dateStr
  return `${d} ${MOIS[parseInt(m, 10) - 1]} ${y}`
}
</script>
