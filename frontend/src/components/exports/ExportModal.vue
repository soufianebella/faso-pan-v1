<template>
  <teleport to="body">
    <transition name="fade">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="$emit('close')"
      >
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg">

          <!-- Header -->
          <div class="px-6 py-4 border-b flex items-center justify-between"
               style="border-color: #E5E7EB">
            <div>
              <h2 class="text-lg font-bold" style="color: #1B3B8A">
                <i class="fa-solid fa-file-arrow-down mr-2"></i>
                Exporter en CSV
              </h2>
              <p class="text-xs mt-0.5" style="color: #6B7280">
                Choisissez le type d'export et les filtres
              </p>
            </div>
            <button
              @click="$emit('close')"
              class="text-gray-400 hover:text-gray-600 text-xl leading-none"
            >&times;</button>
          </div>

          <!-- Body -->
          <div class="p-6 space-y-5">

            <!-- Choix du type -->
            <div class="grid grid-cols-2 gap-3">
              <button
                type="button"
                @click="typeExport = 'inventaire'"
                class="p-4 rounded-lg border-2 text-left transition-all"
                :style="typeExport === 'inventaire'
                  ? 'border-color: #F97316; background-color: #FFF7ED'
                  : 'border-color: #E5E7EB; background-color: white'"
              >
                <i class="fa-solid fa-signs-post text-xl mb-2"
                   :style="typeExport === 'inventaire' ? 'color: #F97316' : 'color: #6B7280'"></i>
                <div class="font-medium text-sm">Inventaire parc</div>
                <div class="text-xs mt-1" style="color: #6B7280">
                  Panneaux + faces + statuts
                </div>
              </button>

              <button
                type="button"
                @click="typeExport = 'campagnes'"
                class="p-4 rounded-lg border-2 text-left transition-all"
                :style="typeExport === 'campagnes'
                  ? 'border-color: #F97316; background-color: #FFF7ED'
                  : 'border-color: #E5E7EB; background-color: white'"
              >
                <i class="fa-solid fa-bullhorn text-xl mb-2"
                   :style="typeExport === 'campagnes' ? 'color: #F97316' : 'color: #6B7280'"></i>
                <div class="font-medium text-sm">Campagnes</div>
                <div class="text-xs mt-1" style="color: #6B7280">
                  Liste avec filtre annonceur
                </div>
              </button>
            </div>

            <!-- Filtres Inventaire -->
            <div v-if="typeExport === 'inventaire'" class="space-y-3 pt-2">
              <div>
                <label class="block text-xs font-medium mb-1" style="color: #6B7280">Ville</label>
                <input
                  v-model="filtresInventaire.ville"
                  type="text"
                  placeholder="Toutes les villes"
                  class="w-full border rounded px-3 py-2 text-sm outline-none"
                  style="border-color: #E5E7EB"
                />
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium mb-1" style="color: #6B7280">Statut</label>
                  <select
                    v-model="filtresInventaire.statut"
                    class="w-full border rounded px-3 py-2 text-sm outline-none bg-white"
                    style="border-color: #E5E7EB"
                  >
                    <option value="">Tous</option>
                    <option value="actif">Actif</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="hors_service">Hors service</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-medium mb-1" style="color: #6B7280">Eclairage</label>
                  <select
                    v-model="filtresInventaire.eclaire"
                    class="w-full border rounded px-3 py-2 text-sm outline-none bg-white"
                    style="border-color: #E5E7EB"
                  >
                    <option value="">Tous</option>
                    <option :value="true">Eclaire</option>
                    <option :value="false">Non eclaire</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Filtres Campagnes -->
            <div v-if="typeExport === 'campagnes'" class="space-y-3 pt-2">
              <div>
                <label class="block text-xs font-medium mb-1" style="color: #6B7280">Annonceur</label>
                <input
                  v-model="filtresCampagnes.annonceur"
                  type="text"
                  placeholder="Tous les annonceurs"
                  class="w-full border rounded px-3 py-2 text-sm outline-none"
                  style="border-color: #E5E7EB"
                />
              </div>
              <div>
                <label class="block text-xs font-medium mb-1" style="color: #6B7280">Statut</label>
                <select
                  v-model="filtresCampagnes.statut"
                  class="w-full border rounded px-3 py-2 text-sm outline-none bg-white"
                  style="border-color: #E5E7EB"
                >
                  <option value="active">Actives uniquement</option>
                  <option value="preparation">En preparation</option>
                  <option value="expiree">Expirees</option>
                  <option value="tous">Tous les statuts</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="px-6 py-4 border-t flex justify-end gap-2"
               style="border-color: #E5E7EB">
            <button
              @click="$emit('close')"
              type="button"
              class="px-4 py-2 text-sm font-medium rounded border"
              style="border-color: #E5E7EB; color: #6B7280"
            >
              Annuler
            </button>
            <button
              @click="lancerExport"
              :disabled="isExporting"
              type="button"
              class="px-4 py-2 text-sm font-medium text-white rounded
                     flex items-center gap-2 disabled:opacity-60"
              style="background-color: #27AE60"
            >
              <i
                :class="isExporting ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-download'"
              ></i>
              {{ isExporting ? 'Export en cours...' : 'Telecharger' }}
            </button>
          </div>

        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { exportsApi }    from '@/api/exports.api'
import { useToast }      from '@/composables/useToast'

defineProps({ show: Boolean })
const emit = defineEmits(['close'])

const toast      = useToast()
const typeExport = ref('inventaire')
const isExporting = ref(false)

const filtresInventaire = reactive({ ville: '', statut: '', eclaire: '' })
const filtresCampagnes  = reactive({ annonceur: '', statut: 'active' })

function cleanFiltres(obj) {
  // Retire les valeurs vides avant envoi (evite params=&statut=)
  return Object.fromEntries(
    Object.entries(obj).filter(([, v]) => v !== '' && v !== null && v !== undefined)
  )
}

async function lancerExport() {
  isExporting.value = true
  try {
    if (typeExport.value === 'inventaire') {
      await exportsApi.inventaire(cleanFiltres(filtresInventaire))
    } else {
      await exportsApi.campagnesActives(cleanFiltres(filtresCampagnes))
    }
    toast.success('Export genere avec succes.')
    emit('close')
  } catch (err) {
    toast.error(err.response?.status === 403
      ? 'Vous n\'avez pas les droits pour cet export.'
      : 'Erreur lors de la generation du CSV.')
  } finally {
    isExporting.value = false
  }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
