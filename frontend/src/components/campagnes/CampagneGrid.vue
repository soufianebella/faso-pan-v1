<template>

  <!-- ── État vide ─────────────────────────────────────────────────────── -->
  <div
    v-if="campagnes.length === 0"
    class="flex flex-col items-center justify-center py-24 gap-3"
  >
    <i class="fa-solid fa-bullhorn text-4xl" style="color: #E5E7EB"></i>
    <p class="text-sm font-medium" style="color: #9CA3AF">Aucune campagne trouvée</p>
    <p class="text-xs" style="color: #D1D5DB">Modifiez vos filtres ou créez une nouvelle campagne</p>
  </div>

  <!-- ── Grille ─────────────────────────────────────────────────────────── -->
  <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 p-5">
    <article
      v-for="campagne in campagnes"
      :key="campagne.id"
      class="group bg-white rounded-2xl flex flex-col overflow-hidden cursor-pointer
             transition-all duration-200"
      style="border: 1px solid #E5E7EB; box-shadow: 0 1px 3px rgba(0,0,0,0.06)"
      @click="$emit('detail', campagne)"
      @mouseenter="$event.currentTarget.style.boxShadow = '0 8px 24px rgba(0,0,0,0.10)';
                   $event.currentTarget.style.transform  = 'translateY(-2px)'"
      @mouseleave="$event.currentTarget.style.boxShadow = '0 1px 3px rgba(0,0,0,0.06)';
                   $event.currentTarget.style.transform  = 'translateY(0)'"
    >

      <!-- Accent statut 3px -->
      <div class="h-0.5 w-full flex-shrink-0" style="height: 3px"
           :style="{ backgroundColor: accentColor(campagne) }"></div>

      <div class="flex flex-col flex-1 p-5 gap-3">

        <!-- ── Ligne 1 : Badge statut + Actions (hover-only) ─────────────── -->
        <div class="flex items-start justify-between gap-2">
          <span
            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full
                   text-xs font-bold uppercase tracking-wide"
            :style="badgeStyle(campagne)"
          >
            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                  :style="{ backgroundColor: accentColor(campagne) }"></span>
            {{ badgeLabel(campagne) }}
          </span>

          <!-- Actions secondaires — visibles au hover uniquement -->
          <div class="flex items-center gap-0.5 flex-shrink-0
                      opacity-0 group-hover:opacity-100 transition-opacity duration-150"
               @click.stop>
            <button
              @click.stop="$emit('edit', campagne)"
              class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
              style="color: #9CA3AF"
              title="Modifier"
              @mouseenter="$event.currentTarget.style.backgroundColor = '#F3F4F6';
                           $event.currentTarget.style.color = '#1B3B8A'"
              @mouseleave="$event.currentTarget.style.backgroundColor = '';
                           $event.currentTarget.style.color = '#9CA3AF'"
            >
              <i class="fa-solid fa-pen text-[10px]"></i>
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
              <i class="fa-solid fa-ban text-[10px]"></i>
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
              <i class="fa-solid fa-trash-can text-[10px]"></i>
            </button>
          </div>
        </div>

        <!-- ── Ligne 2 : Nom + Annonceur ──────────────────────────────── -->
        <div>
          <h3 class="text-base font-bold leading-snug line-clamp-2" style="color: #1C2833">
            {{ campagne.nom }}
          </h3>
          <p class="flex items-center gap-1.5 text-xs font-medium mt-1" style="color: #6B7280">
            <i class="fa-solid fa-building text-[10px]" style="color: #9CA3AF"></i>
            {{ campagne.annonceur }}
          </p>
        </div>

        <!-- ── Ligne 3 : Dates + Faces ────────────────────────────────── -->
        <div class="space-y-1.5 text-xs" style="color: #6B7280">
          <div class="flex items-center gap-2">
            <i class="fa-regular fa-calendar w-3 text-center" style="color: #D1D5DB"></i>
            <span>{{ campagne.date_debut }}</span>
            <span style="color: #D1D5DB">→</span>
            <span>{{ campagne.date_fin }}</span>
          </div>
          <div class="flex items-center gap-2">
            <i class="fa-solid fa-layer-group w-3 text-center" style="color: #D1D5DB"></i>
            <span>
              {{ campagne.affectations_count || 0 }}
              face{{ (campagne.affectations_count || 0) !== 1 ? 's' : '' }} réservée{{ (campagne.affectations_count || 0) !== 1 ? 's' : '' }}
            </span>
          </div>
        </div>

        <!-- ── Ligne 4 : Progression ──────────────────────────────────── -->
        <div class="space-y-1.5">
          <div class="flex items-center justify-between">
            <span class="text-xs" style="color: #9CA3AF">Progression</span>
            <span class="text-xs font-bold tabular-nums" style="color: #F97316">
              {{ calcProgress(campagne) }}%
            </span>
          </div>
          <!-- Barre toujours visible (fond gris même à 0%) -->
          <div class="w-full rounded-full overflow-hidden" style="height: 6px; background-color: #F3F4F6">
            <div
              class="h-full rounded-full transition-all duration-700"
              style="background-color: #F97316"
              :style="{ width: calcProgress(campagne) + '%' }"
            ></div>
          </div>
        </div>

        <!-- Spacer -->
        <div class="flex-1"></div>

        <!-- ── CTA pleine largeur ──────────────────────────────────────── -->
        <div class="pt-2 border-t" style="border-color: #F3F4F6">
          <button
            @click.stop="$emit('detail', campagne)"
            class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl
                   text-xs font-semibold border transition-all duration-150"
            style="border-color: #1B3B8A; color: #1B3B8A; background-color: transparent"
            @mouseenter="$event.currentTarget.style.backgroundColor = '#EBF3FC'"
            @mouseleave="$event.currentTarget.style.backgroundColor = 'transparent'"
          >
            <i class="fa-solid fa-eye text-[10px]"></i>
            Voir les détails
          </button>
        </div>

      </div>
    </article>
  </div>

</template>

<script setup>
defineProps({
  campagnes: { type: Array, required: true, default: () => [] },
})

defineEmits(['detail', 'edit', 'archive', 'delete'])

// ── Couleur d'accent par statut ────────────────────────────────────────────
const ACCENT = {
  preparation: '#F97316',
  active:      '#27AE60',
  expiree:     '#EF4444',
}

function accentColor(c) {
  return ACCENT[c.statut] ?? '#9CA3AF'
}

// ── Utilitaire date ────────────────────────────────────────────────────────
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

// ── Badge statut ───────────────────────────────────────────────────────────
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

<style scoped>
/* La barre de progression utilise un width calculé en % réel */
article .prog-fill {
  transition: width 700ms ease;
}
</style>
