<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="background-color: rgba(15,23,42,0.7)"
    @click.self="$emit('close')"
  >
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">

      <!-- Header -->
      <div
        class="p-4 border-b flex justify-between items-center"
        style="border-color: #E5E7EB"
      >
        <div>
          <h3 class="text-lg font-bold" style="color: #1B3B8A">
            Assigner un agent
          </h3>
          <p class="text-xs mt-0.5" style="color: #6B7280">
            Campagne :
            {{ tacheActuelle?.affectation?.campagne?.nom ?? 'N/A' }}
          </p>
        </div>
        <button
          @click="$emit('close')"
          class="p-1 rounded-full hover:bg-slate-100 transition-colors"
        >
          <i class="fa-solid fa-xmark" style="color: #6B7280"></i>
        </button>
      </div>

      <!-- Formulaire -->
      <div class="p-6">

        <div class="space-y-1">
          <label class="text-xs font-bold uppercase tracking-wider"
                 style="color: #6B7280">
            Agent de terrain
          </label>

          <!-- Skeleton si agents pas encore chargés -->
          <div
            v-if="agents.length === 0"
            class="h-10 w-full rounded animate-pulse"
            style="background-color: #E5E7EB"
          ></div>

          <select
            v-else
            v-model="selectedAgentId"
            class="w-full border rounded-lg px-3 py-2 text-sm
                   outline-none bg-white"
            style="border-color: #E5E7EB"
          >
            <option value="" disabled>Selectionner un agent</option>
            <option
              v-for="agent in agents"
              :key="agent.id"
              :value="agent.id"
            >
              {{ agent.name }}
            </option>
          </select>

          <p v-if="errors?.agent_id"
             class="text-xs" style="color: #EF4444">
            {{ errors.agent_id[0] }}
          </p>
        </div>

      </div>

      <!-- Footer -->
      <div
        class="p-4 border-t flex justify-end gap-3"
        style="border-color: #E5E7EB; background-color: #F9FAFB"
      >
        <button
          type="button"
          @click="$emit('close')"
          class="px-4 py-2 text-sm font-semibold rounded-lg
                 hover:bg-slate-100 transition-colors"
          style="color: #374151"
        >
          Annuler
        </button>
        <button
          type="button"
          @click="handleAssign"
          :disabled="isLoading || !selectedAgentId"
          class="px-6 py-2 text-sm font-bold text-white rounded-lg
                 shadow-sm transition-all
                 disabled:opacity-50 disabled:cursor-not-allowed
                 flex items-center gap-2"
          style="background-color: #F97316"
        >
          <i v-if="isLoading"
             class="fa-solid fa-circle-notch animate-spin"></i>
          Confirmer
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, watch }  from 'vue'
import { storeToRefs } from 'pinia'
import { useTachesStore } from '@/stores/taches.store'

const emit = defineEmits(['close', 'saved'])

const store = useTachesStore()
const { agents, tacheActuelle, isLoading, errors } = storeToRefs(store)

const selectedAgentId = ref('')

// Reset la sélection à chaque ouverture sur une nouvelle tâche
watch(tacheActuelle, () => {
  selectedAgentId.value = tacheActuelle.value?.agent?.id ?? ''
}, { immediate: true })

async function handleAssign() {
  if (!tacheActuelle.value?.id) return
  try {
    await store.assignerAgent(
      tacheActuelle.value.id,
      selectedAgentId.value
    )
    emit('saved')
  } catch {
    // Erreurs 422 affichees via store.errors
  }
}
</script>