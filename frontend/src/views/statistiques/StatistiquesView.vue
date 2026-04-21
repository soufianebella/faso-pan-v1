<template>
  <div class="p-6 space-y-6" style="min-height: 100vh; background-color: #F0F4FF">

    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between
                items-start md:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold" style="color: #1B3B8A">
          Rapports & Statistiques
        </h1>
        <p class="text-sm mt-0.5" style="color: #6B7280">
          Analyse detaillee du parc publicitaire
        </p>
      </div>

      <div class="flex items-center gap-3">
        <!-- Filtres période -->
        <div class="bg-white border rounded-lg p-1 flex shadow-sm" style="border-color: #E5E7EB">
          <button v-for="f in filtreOptions" :key="f.value" @click="changerPeriode(f.value)"
            class="px-4 py-1.5 text-sm rounded-md transition-colors" :style="periode === f.value
              ? 'background-color: #EBF3FC; color: #1B3B8A; font-weight: 700'
              : 'color: #6B7280'">
            {{ f.label }}
          </button>
        </div>

        <!-- Export CSV -->
        <button @click="exporterCSV" class="px-4 py-2 text-sm font-medium text-white rounded-lg
                 shadow-sm flex items-center gap-2" style="background-color: #27AE60">
          <i class="fa-solid fa-file-arrow-down"></i>
          Exporter CSV
        </button>
      </div>
    </div>

    <!-- Skeleton -->
    <div v-if="isLoading" class="space-y-6">
      <div class="grid grid-cols-4 gap-6">
        <div v-for="i in 4" :key="i" class="h-28 rounded-xl animate-pulse" style="background-color: #E5E7EB"></div>
      </div>
      <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 h-80 rounded-xl animate-pulse" style="background-color: #E5E7EB"></div>
        <div class="h-80 rounded-xl animate-pulse" style="background-color: #E5E7EB"></div>
      </div>
    </div>

    <template v-else-if="stats">

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <div class="bg-white p-6 rounded-xl shadow-sm border flex items-center justify-between"
          style="border-color: #E5E7EB; border-left: 4px solid #1B3B8A">
          <div>
            <p class="text-sm font-medium" style="color: #6B7280">Taux d'occupation</p>
            <h3 class="text-3xl font-bold mt-1" style="color: #1B3B8A">
              {{ stats.kpi.taux_occupation }}%
            </h3>
          </div>
          <i class="fa-solid fa-chart-pie text-5xl" style="color: #E5E7EB"></i>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border flex items-center justify-between"
          style="border-color: #E5E7EB; border-left: 4px solid #27AE60">
          <div>
            <p class="text-sm font-medium" style="color: #6B7280">Campagnes actives</p>
            <h3 class="text-3xl font-bold mt-1" style="color: #27AE60">
              {{ stats.kpi.campagnes_actives }}
            </h3>
          </div>
          <i class="fa-solid fa-bullhorn text-5xl" style="color: #E5E7EB"></i>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border flex items-center justify-between"
          style="border-color: #E5E7EB; border-left: 4px solid #F97316">
          <div>
            <p class="text-sm font-medium" style="color: #6B7280">Taches en attente</p>
            <h3 class="text-3xl font-bold mt-1" style="color: #F97316">
              {{ stats.kpi.taches_attente }}
            </h3>
          </div>
          <i class="fa-regular fa-clock text-5xl" style="color: #E5E7EB"></i>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border flex items-center justify-between"
          style="border-color: #E5E7EB; border-left: 4px solid #7C3AED">
          <div>
            <p class="text-sm font-medium" style="color: #6B7280">Revenu estime (FCFA)</p>
            <h3 class="text-3xl font-bold mt-1" style="color: #7C3AED">
              {{ formaterPrix(stats.kpi.revenu_estime) }}
            </h3>
          </div>
          <i class="fa-solid fa-wallet text-5xl" style="color: #E5E7EB"></i>
        </div>

      </div>

      <!-- Graphiques ligne 1 -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border" style="border-color: #E5E7EB">
          <h3 class="text-sm font-bold uppercase tracking-wider mb-6" style="color: #374151">
            Evolution taux occupation — 12 mois
          </h3>
          <apexchart type="area" height="280" :options="evolutionOptions" :series="evolutionSeries" />
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border" style="border-color: #E5E7EB">
          <h3 class="text-sm font-bold uppercase tracking-wider mb-6" style="color: #374151">
            Top 5 Annonceurs
          </h3>
          <apexchart type="bar" height="280" :options="annonceursOptions" :series="annonceursSeries" />
        </div>

      </div>

      <!-- Graphiques ligne 2 -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white p-6 rounded-xl shadow-sm border text-center" style="border-color: #E5E7EB">
          <h3 class="text-sm font-bold uppercase tracking-wider mb-4 text-left" style="color: #374151">
            Statut des faces
          </h3>
          <apexchart type="donut" height="280" :options="donutOptions" :series="donutSeries" />
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border" style="border-color: #E5E7EB">
          <h3 class="text-sm font-bold uppercase tracking-wider mb-6" style="color: #374151">
            Taches par statut ce mois
          </h3>
          <apexchart type="bar" height="280" :options="tachesOptions" :series="tachesSeries" />
        </div>

      </div>

      <!-- Tableau par ville -->
      <div class="bg-white rounded-xl shadow-sm border overflow-hidden" style="border-color: #E5E7EB">
        <div class="px-6 py-4 border-b flex justify-between items-center" style="border-color: #F3F4F6">
          <h3 class="text-sm font-bold uppercase tracking-wider" style="color: #374151">
            Detail par ville
          </h3>
        </div>

        <table class="w-full text-left border-collapse text-sm">
          <thead style="background-color: #F9FAFB">
            <tr>
              <th class="px-6 py-3 text-xs font-bold uppercase" style="color: #6B7280">Ville</th>
              <th class="px-6 py-3 text-xs font-bold uppercase text-center" style="color: #6B7280">Panneaux</th>
              <th class="px-6 py-3 text-xs font-bold uppercase" style="color: #6B7280">Taux occupation</th>
              <th class="px-6 py-3 text-xs font-bold uppercase text-right" style="color: #6B7280">CA Estime</th>
            </tr>
          </thead>
          <tbody class="divide-y" style="border-color: #F3F4F6">
            <tr v-if="!stats.tableau_villes?.length">
              <td colspan="4" class="px-6 py-8 text-center text-sm" style="color: #9CA3AF">
                Aucune donnee disponible
              </td>
            </tr>
            <tr v-for="ville in stats.tableau_villes" :key="ville.nom" class="hover:bg-slate-50 transition-colors">
              <td class="px-6 py-4 font-semibold" style="color: #1B3B8A">
                {{ ville.nom }}
              </td>
              <td class="px-6 py-4 text-center" style="color: #374151">
                {{ ville.total_panneaux }}
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <span class="w-10 text-xs font-bold" style="color: #374151">
                    {{ ville.taux }}%
                  </span>
                  <div class="flex-1 h-2 rounded-full" style="background-color: #F3F4F6">
                    <div class="h-2 rounded-full transition-all" :style="{
                      width: ville.taux + '%',
                      backgroundColor: couleurOccupation(ville.taux),
                    }"></div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-right font-mono font-semibold" style="color: #374151">
                {{ formaterPrix(ville.ca) }} F CFA
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </template>

    <ExportModal
      :show="showExportModal"
      @close="showExportModal = false"
    />

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useStatistiquesStore } from '@/stores/statistiques.store'
import ExportModal from '@/components/exports/ExportModal.vue'

const store = useStatistiquesStore()
const { stats, isLoading } = storeToRefs(store)

// Traduction mois anglais → français
const MOIS_FR = {
  Jan: 'Jan', Feb: 'Fév', Mar: 'Mar', Apr: 'Avr',
  May: 'Mai', Jun: 'Juin', Jul: 'Juil', Aug: 'Aoû',
  Sep: 'Sep', Oct: 'Oct', Nov: 'Nov', Dec: 'Déc',
}
function traduireMois(mois) {
  return MOIS_FR[mois] ?? mois
}

const periode = ref('ce_mois')

const filtreOptions = [
  { label: 'Ce mois', value: 'ce_mois' },
  { label: 'Trimestre', value: 'trimestre' },
]

function changerPeriode(valeur) {
  periode.value = valeur
}

watch(periode, (val) => {
  store.fetchStats(val)
})

// ── ApexCharts 

const evolutionSeries = computed(() =>
  stats.value?.evolution?.series ?? []
)

const evolutionOptions = computed(() => ({
  chart: { toolbar: { show: false }, fontFamily: 'inherit' },
  colors: ['#1B3B8A', '#F97316', '#27AE60', '#7C3AED'],
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.3,
      opacityTo: 0.02,
    },
  },
  xaxis: {
    categories: stats.value?.evolution?.categories?.map(traduireMois) ?? [],
    labels: { style: { colors: '#6B7280', fontSize: '11px' } },
  },
  yaxis: {
    max: 100,
    labels: { style: { colors: '#6B7280', fontSize: '11px' } },
  },
  grid: { borderColor: '#F3F4F6', strokeDashArray: 4 },
}))

const annonceursSeries = computed(() => [{
  name: 'Campagnes',
  data: stats.value?.top_annonceurs?.map(a => a.total) ?? [],
}])

const annonceursOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false } },
  plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '50%' } },
  colors: ['#F97316'],
  dataLabels: { enabled: false },
  xaxis: {
    categories: stats.value?.top_annonceurs?.map(a => a.nom) ?? [],
    labels: { style: { colors: '#6B7280', fontSize: '11px' } },
  },
  grid: { borderColor: '#F3F4F6' },
}))

const donutSeries = computed(() =>
  stats.value?.statut_faces?.map(s => s.total) ?? []
)

const donutOptions = computed(() => ({
  labels: stats.value?.statut_faces?.map(s => s.statut) ?? [],
  colors: ['#27AE60', '#F97316'],
  legend: { position: 'bottom', fontSize: '12px' },
  dataLabels: { enabled: false },
  plotOptions: {
    pie: { donut: { size: '65%' } },
  },
}))

const tachesSeries = computed(() => [{
  name: 'Taches',
  data: stats.value?.repartition_taches?.map(t => t.total) ?? [],
}])

const tachesOptions = computed(() => ({
  chart: { toolbar: { show: false } },
  plotOptions: {
    bar: { borderRadius: 4, columnWidth: '45%', distributed: true },
  },
  colors: ['#1B3B8A', '#F97316', '#7C3AED', '#27AE60'],
  dataLabels: { enabled: false },
  xaxis: {
    categories: stats.value?.repartition_taches?.map(t => t.statut) ?? [],
    labels: { style: { colors: '#6B7280', fontSize: '11px' } },
  },
  legend: { show: false },
  grid: { borderColor: '#F3F4F6' },
}))

// ── Helpers 

function formaterPrix(montant) {
  if (!montant) return '0'
  return new Intl.NumberFormat('fr-FR').format(montant)
}

function couleurOccupation(taux) {
  if (taux >= 80) return '#27AE60'
  if (taux >= 50) return '#F97316'
  return '#EF4444'
}

const showExportModal = ref(false)

function exporterCSV() {
  showExportModal.value = true
}

onMounted(() => store.fetchStats(periode.value))
</script>