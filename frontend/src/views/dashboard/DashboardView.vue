<template>
  <div class="p-6 space-y-6" style="min-height: 100vh; background-color: #F0F4FF">

    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold" style="color: #1B3B8A">
          Tableau de bord
        </h1>
        <p class="text-sm mt-0.5" style="color: #6B7280">
          {{ dateAujourdhui }}
        </p>
      </div>
      <button
        @click="store.fetchStats()"
        class="p-2 rounded-lg transition-colors hover:bg-white"
        style="color: #6B7280"
      >
        <i class="fa-solid fa-rotate" :class="{ 'fa-spin': isLoading }"></i>
      </button>
    </div>

    <!-- Skeleton -->
    <div v-if="isLoading" class="space-y-6">
      <div class="grid grid-cols-4 gap-6">
        <div
          v-for="i in 4" :key="i"
          class="h-32 rounded-xl animate-pulse"
          style="background-color: #E5E7EB"
        ></div>
      </div>
      <div class="grid grid-cols-3 gap-6">
        <div
          v-for="i in 3" :key="i"
          class="h-80 rounded-xl animate-pulse"
          :class="i === 1 ? 'col-span-2' : ''"
          style="background-color: #E5E7EB"
        ></div>
      </div>
    </div>

    <template v-else-if="stats">

      <!-- Ligne 1 — KPI Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Total Panneaux -->
        <div class="bg-white rounded-xl p-6 shadow-sm border relative overflow-hidden"
             style="border-color: #E5E7EB; border-top: 4px solid #1B3B8A">
          <p class="text-sm font-medium" style="color: #6B7280">Total Panneaux</p>
          <h3 class="text-3xl font-bold mt-1" style="color: #1B3B8A">
            {{ stats.kpi.total_panneaux }}
          </h3>
          <i class="fa-solid fa-sign-hanging absolute -right-2 -bottom-2 text-5xl"
             style="opacity: 0.05; color: #1B3B8A"></i>
        </div>

        <!-- Faces Libres -->
        <div class="bg-white rounded-xl p-6 shadow-sm border relative overflow-hidden"
             style="border-color: #E5E7EB; border-top: 4px solid #27AE60">
          <p class="text-sm font-medium" style="color: #6B7280">Faces Libres</p>
          <h3 class="text-3xl font-bold mt-1" style="color: #27AE60">
            {{ stats.kpi.faces_libres }}
          </h3>
          <p class="text-xs mt-1" style="color: #27AE60">
            disponibles a la vente
          </p>
          <i class="fa-solid fa-circle-check absolute -right-2 -bottom-2 text-5xl"
             style="opacity: 0.05; color: #27AE60"></i>
        </div>

        <!-- Faces Occupées -->
        <div class="bg-white rounded-xl p-6 shadow-sm border relative overflow-hidden"
             style="border-color: #E5E7EB; border-top: 4px solid #F97316">
          <p class="text-sm font-medium" style="color: #6B7280">Faces Occupees</p>
          <h3 class="text-3xl font-bold mt-1" style="color: #F97316">
            {{ stats.kpi.faces_occupees }}
          </h3>
          <i class="fa-solid fa-layer-group absolute -right-2 -bottom-2 text-5xl"
             style="opacity: 0.05; color: #F97316"></i>
        </div>

        <!-- Expirent 7j -->
        <div class="bg-white rounded-xl p-6 shadow-sm border relative overflow-hidden"
             style="border-color: #E5E7EB; border-top: 4px solid #EF4444">
          <p class="text-sm font-medium" style="color: #6B7280">Expirent dans 7j</p>
          <h3 class="text-3xl font-bold mt-1" style="color: #EF4444">
            {{ stats.kpi.expirent_7j }}
          </h3>
          <i class="fa-solid fa-triangle-exclamation absolute -right-2 -bottom-2 text-5xl"
             style="opacity: 0.05; color: #EF4444"></i>
        </div>

      </div>

      <!-- Ligne 2 — Graphique barres + Expirent bientôt -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Graphique occupation mensuelle -->
        <div class="lg:col-span-2 bg-white rounded-xl p-6 shadow-sm border"
             style="border-color: #E5E7EB">
          <h3 class="text-sm font-bold uppercase tracking-wider mb-6"
              style="color: #374151">
            Taux d'occupation mensuel
          </h3>
          <apexchart
            type="bar"
            height="280"
            :options="barOptions"
            :series="barSeries"
          />
        </div>

        <!-- Expirent bientôt -->
        <div class="bg-white rounded-xl p-6 shadow-sm border"
             style="border-color: #E5E7EB">
          <h3 class="text-sm font-bold uppercase tracking-wider mb-6"
              style="color: #374151">
            Expirent bientot
          </h3>
          <div class="space-y-3 overflow-y-auto max-h-72">
            <div
              v-if="!stats.expirent_bientot?.length"
              class="text-sm text-center py-8"
              style="color: #9CA3AF"
            >
              Aucune expiration imminente
            </div>
            <div
              v-for="item in stats.expirent_bientot"
              :key="item.panneau"
              class="flex justify-between items-center p-2 rounded-lg
                     hover:bg-slate-50 transition-colors"
            >
              <div>
                <p class="text-sm font-semibold" style="color: #1C2833">
                  {{ item.campagne }}
                </p>
                <p class="text-xs" style="color: #6B7280">
                  {{ item.panneau }}
                </p>
              </div>
              <span
                class="text-xs px-2 py-1 rounded-full font-bold flex-shrink-0"
                :style="badgeUrgenceStyle(item.jours)"
              >
                J-{{ item.jours }}
              </span>
            </div>
          </div>
        </div>

      </div>

      <!-- Ligne 3 — Donut + Campagnes actives + Dernières tâches -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Donut répartition par ville -->
        <div class="bg-white rounded-xl p-6 shadow-sm border text-center"
             style="border-color: #E5E7EB">
          <h3 class="text-sm font-bold uppercase tracking-wider mb-4"
              style="color: #374151">
            Repartition par ville
          </h3>
          <apexchart
            type="donut"
            height="250"
            :options="donutOptions"
            :series="donutSeries"
          />
        </div>

        <!-- Campagnes actives -->
        <div class="bg-white rounded-xl p-6 shadow-sm border"
             style="border-color: #E5E7EB">
          <h3 class="text-sm font-bold uppercase tracking-wider mb-6"
              style="color: #374151">
            Campagnes actives
          </h3>
          <div class="space-y-4">
            <div
              v-if="!stats.campagnes_actives?.length"
              class="text-sm text-center py-8"
              style="color: #9CA3AF"
            >
              Aucune campagne active
            </div>
            <div
              v-for="c in stats.campagnes_actives"
              :key="c.id"
            >
              <div class="flex justify-between text-xs mb-1.5">
                <div>
                  <span class="font-semibold" style="color: #1C2833">
                    {{ c.nom }}
                  </span>
                  <span class="ml-1" style="color: #6B7280">
                    — {{ c.annonceur }}
                  </span>
                </div>
                <span class="font-bold" style="color: #F97316">
                  {{ c.avancement }}%
                </span>
              </div>
              <div class="w-full h-1.5 rounded-full"
                   style="background-color: #F3F4F6">
                <div
                  class="h-1.5 rounded-full transition-all duration-700"
                  style="background-color: #F97316"
                  :style="{ width: c.avancement + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Dernières tâches -->
        <div class="bg-white rounded-xl p-6 shadow-sm border"
             style="border-color: #E5E7EB">
          <h3 class="text-sm font-bold uppercase tracking-wider mb-6"
              style="color: #374151">
            Dernieres taches
          </h3>
          <div class="space-y-4">
            <div
              v-if="!stats.dernieres_taches?.length"
              class="text-sm text-center py-8"
              style="color: #9CA3AF"
            >
              Aucune activite recente
            </div>
            <div
              v-for="t in stats.dernieres_taches"
              :key="t.agent + t.panneau"
              class="flex gap-3 items-start border-l-2 pl-3"
              :style="{ borderColor: tacheColor(t.statut) }"
            >
              <div>
                <p class="text-xs font-semibold" style="color: #1C2833">
                  {{ t.agent }} — {{ t.panneau }} Face {{ t.face }}
                </p>
                <p class="text-xs mt-0.5" style="color: #9CA3AF">
                  {{ t.date }}
                </p>
              </div>
            </div>
          </div>
        </div>

      </div>

    </template>

    <!-- Erreur -->
    <div v-else class="text-center py-20" style="color: #6B7280">
      <i class="fa-solid fa-circle-exclamation text-4xl mb-3 block"
         style="color: #EF4444"></i>
      <p class="text-sm mb-3">Impossible de charger les statistiques.</p>
      <button
        @click="store.fetchStats()"
        class="text-sm font-medium px-4 py-2 rounded-lg text-white"
        style="background-color: #F97316"
      >
        Reessayer
      </button>
    </div>

  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { storeToRefs }         from 'pinia'
import { useDashboardStore }   from '@/stores/dashboard.store'

const store = useDashboardStore()
const { stats, isLoading } = storeToRefs(store)

const dateAujourdhui = new Date().toLocaleDateString('fr-FR', {
  weekday: 'long',
  day:     '2-digit',
  month:   'long',
  year:    'numeric',
})

// ── ApexCharts — Barres occupation mensuelle ───────────────────────
const barSeries = computed(() => [{
  name: "Occupation %",
  data: stats.value?.occupation_mensuelle?.map(m => m.taux) ?? [],
}])

const barOptions = computed(() => ({
  chart:  { toolbar: { show: false }, fontFamily: 'inherit' },
  colors: ['#1B3B8A'],
  plotOptions: {
    bar: { borderRadius: 4, columnWidth: '55%' },
  },
  dataLabels: { enabled: false },
  xaxis: {
    categories: stats.value?.occupation_mensuelle?.map(m => m.mois) ?? [],
    labels: { style: { colors: '#6B7280', fontSize: '11px' } },
  },
  yaxis: {
    max: 100,
    labels: { style: { colors: '#6B7280', fontSize: '11px' } },
  },
  annotations: {
    yaxis: [{
      y:           75,
      borderColor: '#F97316',
      borderWidth: 2,
      strokeDashArray: 4,
      label: {
        text:  'Objectif 75%',
        style: { color: '#fff', background: '#F97316', fontSize: '10px' },
      },
    }],
  },
  grid: { borderColor: '#F3F4F6' },
}))

// ── ApexCharts — Donut répartition villes ─────────────────────────
const donutSeries = computed(() =>
  stats.value?.par_ville?.map(v => v.total) ?? []
)

const donutOptions = computed(() => ({
  labels:  stats.value?.par_ville?.map(v => v.ville) ?? [],
  colors:  ['#1B3B8A', '#F97316', '#27AE60', '#7C3AED', '#6B7280'],
  legend:  { position: 'bottom', fontSize: '11px' },
  dataLabels: { enabled: false },
  plotOptions: {
    pie: {
      donut: {
        size: '65%',
        labels: {
          show:  true,
          total: {
            show:  true,
            label: 'Panneaux',
            style: { fontSize: '12px', color: '#6B7280' },
          },
        },
      },
    },
  },
}))

// ── Helpers ────────────────────────────────────────────────────────
function badgeUrgenceStyle(jours) {
  if (jours <= 2) return { backgroundColor: '#FEE2E2', color: '#EF4444' }
  if (jours <= 5) return { backgroundColor: '#FEF3DC', color: '#F97316' }
  return { backgroundColor: '#EBF3FC', color: '#1B3B8A' }
}

const TACHE_COLORS = {
  en_attente: '#1B3B8A',
  en_cours:   '#F97316',
  realisee:   '#7C3AED',
  validee:    '#27AE60',
}

function tacheColor(statut) {
  return TACHE_COLORS[statut] ?? '#E5E7EB'
}

onMounted(() => store.fetchStats())
</script>