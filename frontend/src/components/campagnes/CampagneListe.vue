<template>
  <table class="w-full text-left border-collapse text-sm">

    <!-- En-tête navy -->
    <thead style="background-color: #1B3B8A">
      <tr>
        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-white">
          Campagne
        </th>
        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-white hidden md:table-cell">
          Période
        </th>
        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-white text-center hidden sm:table-cell">
          Faces
        </th>
        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-white hidden lg:table-cell">
          Progression
        </th>
        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-white">
          Statut
        </th>
        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-white text-right">
          Actions
        </th>
      </tr>
    </thead>

    <tbody class="divide-y" style="border-color: #F3F4F6">

      <!-- État vide -->
      <tr v-if="campagnes.length === 0">
        <td colspan="6" class="px-5 py-20 text-center">
          <i class="fa-solid fa-bullhorn text-4xl block mb-3" style="color: #E5E7EB"></i>
          <p class="text-sm font-medium" style="color: #9CA3AF">Aucune campagne trouvée</p>
          <p class="text-xs mt-1" style="color: #D1D5DB">Modifiez vos filtres ou créez une nouvelle campagne</p>
        </td>
      </tr>

      <!-- Ligne campagne -->
      <tr
        v-for="campagne in campagnes"
        :key="campagne.id"
        class="bg-white group cursor-pointer transition-colors"
        style="border-left: 3px solid transparent"
        :style="{ borderLeftColor: accentColor(campagne) }"
        @click="$emit('detail', campagne)"
        @mouseenter="$event.currentTarget.style.backgroundColor = '#F8FAFF'"
        @mouseleave="$event.currentTarget.style.backgroundColor = '#FFFFFF'"
      >

        <!-- Campagne + Annonceur -->
        <td class="px-5 py-4">
          <p class="font-semibold text-sm leading-snug" style="color: #1C2833">
            {{ campagne.nom }}
          </p>
          <p class="flex items-center gap-1 text-xs mt-0.5" style="color: #6B7280">
            <i class="fa-solid fa-building text-[9px]" style="color: #D1D5DB"></i>
            {{ campagne.annonceur }}
          </p>
        </td>

        <!-- Période -->
        <td class="px-5 py-4 hidden md:table-cell">
          <p class="text-xs" style="color: #374151">{{ campagne.date_debut }}</p>
          <p class="text-xs mt-0.5 flex items-center gap-1" style="color: #9CA3AF">
            <i class="fa-solid fa-arrow-right text-[8px]"></i>
            {{ campagne.date_fin }}
          </p>
        </td>

        <!-- Faces -->
        <td class="px-5 py-4 text-center hidden sm:table-cell">
          <span class="text-sm font-semibold" style="color: #374151">
            {{ campagne.affectations_count || 0 }}
          </span>
        </td>

        <!-- Mini barre de progression -->
        <td class="px-5 py-4 hidden lg:table-cell">
          <div class="flex items-center gap-2">
            <div class="rounded-full overflow-hidden flex-shrink-0"
                 style="width: 80px; height: 6px; background-color: #F3F4F6">
              <div
                class="h-full rounded-full transition-all"
                style="background-color: #F97316"
                :style="{ width: calcProgress(campagne) + '%' }"
              ></div>
            </div>
            <span class="text-xs font-semibold tabular-nums" style="color: #6B7280; min-width: 30px">
              {{ calcProgress(campagne) }}%
            </span>
          </div>
        </td>

        <!-- Statut -->
        <td class="px-5 py-4">
          <span
            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full
                   text-xs font-bold uppercase tracking-wide"
            :style="badgeStyle(campagne)"
          >
            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                  :style="{ backgroundColor: accentColor(campagne) }"></span>
            {{ badgeLabel(campagne) }}
          </span>
        </td>

        <!-- Actions — icônes grises → colorées au hover -->
        <td class="px-5 py-4" @click.stop>
          <div class="flex items-center justify-end gap-1
                      opacity-0 group-hover:opacity-100 transition-opacity duration-150">

            <button
              @click.stop="$emit('edit', campagne)"
              class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
              style="color: #9CA3AF"
              title="Modifier"
              @mouseenter="$event.currentTarget.style.backgroundColor = '#EBF3FC';
                           $event.currentTarget.style.color = '#1B3B8A'"
              @mouseleave="$event.currentTarget.style.backgroundColor = '';
                           $event.currentTarget.style.color = '#9CA3AF'"
            >
              <i class="fa-solid fa-pen text-xs"></i>
            </button>

            <button
              v-if="campagne.statut !== 'expiree'"
              @click.stop="$emit('archive', campagne.id)"
              class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
              style="color: #9CA3AF"
              title="Clôturer"
              @mouseenter="$event.currentTarget.style.backgroundColor = '#FEF2F2';
                           $event.currentTarget.style.color = '#EF4444'"
              @mouseleave="$event.currentTarget.style.backgroundColor = '';
                           $event.currentTarget.style.color = '#9CA3AF'"
            >
              <i class="fa-solid fa-ban text-xs"></i>
            </button>

            <button
              v-else
              @click.stop="$emit('delete', campagne)"
              class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
              style="color: #9CA3AF"
              title="Supprimer"
              @mouseenter="$event.currentTarget.style.backgroundColor = '#FEF2F2';
                           $event.currentTarget.style.color = '#EF4444'"
              @mouseleave="$event.currentTarget.style.backgroundColor = '';
                           $event.currentTarget.style.color = '#9CA3AF'"
            >
              <i class="fa-solid fa-trash-can text-xs"></i>
            </button>

          </div>
        </td>

      </tr>
    </tbody>

  </table>
</template>

<script setup>
defineProps({
  campagnes: { type: Array, required: true, default: () => [] },
})

defineEmits(['detail', 'edit', 'archive', 'delete'])

// ── Accent par statut ─────────────────────────────────────────────────────
const ACCENT = {
  preparation: '#F97316',
  active:      '#27AE60',
  expiree:     '#EF4444',
}

function accentColor(c) {
  return ACCENT[c.statut] ?? '#9CA3AF'
}

// ── Badge ──────────────────────────────────────────────────────────────────
function parseDateFR(str) {
  if (!str) return null
  const parts = str.split('/')
  if (parts.length === 3) return new Date(+parts[2], +parts[1] - 1, +parts[0])
  return new Date(str)
}

function joursRestants(c) {
  if (c.statut !== 'active') return null
  const fin = parseDateFR(c.date_fin)
  if (!fin) return null
  const now = new Date(); now.setHours(0,0,0,0); fin.setHours(0,0,0,0)
  return Math.ceil((fin - now) / 86400000)
}

const BADGE_STYLES = {
  preparation: { backgroundColor: '#FEF3DC', color: '#92400E' },
  active:      { backgroundColor: '#D1FAE5', color: '#065F46' },
  expiree:     { backgroundColor: '#FEE2E2', color: '#991B1B' },
}

function badgeStyle(c) {
  const j = joursRestants(c)
  if (c.statut === 'active' && j !== null && j <= 7) {
    return { backgroundColor: '#FEF3C7', color: '#92400E' }
  }
  return BADGE_STYLES[c.statut] ?? { backgroundColor: '#F3F4F6', color: '#6B7280' }
}

function badgeLabel(c) {
  const j = joursRestants(c)
  if (c.statut === 'active' && j !== null && j <= 7) return `Expire J-${j}`
  return { preparation: 'En préparation', active: 'Active', expiree: 'Expirée' }[c.statut] ?? c.statut
}

// ── Progression ────────────────────────────────────────────────────────────
function calcProgress(c) {
  if (c.statut === 'expiree')     return 100
  if (c.statut === 'preparation') return 0
  const debut = parseDateFR(c.date_debut)
  const fin   = parseDateFR(c.date_fin)
  if (!debut || !fin) return 0
  const total = fin - debut; const elapsed = new Date() - debut
  if (total <= 0) return 100
  return Math.min(100, Math.max(0, Math.round((elapsed / total) * 100)))
}
</script>
