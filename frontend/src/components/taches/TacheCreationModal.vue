<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="background-color: rgba(15,23,42,0.7)"
    @click.self="$emit('close')"
  >
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg">

      <!-- Header -->
      <div
        class="p-4 border-b flex justify-between items-center rounded-t-xl"
        style="background-color: #F9FAFB; border-color: #E5E7EB"
      >
        <div>
          <h3 class="text-lg font-bold" style="color: #1B3B8A">
            Nouvelle tache terrain
          </h3>
          <p class="text-xs mt-0.5" style="color: #6B7280">
            Assigne une affectation à un agent de terrain.
          </p>
        </div>
        <button
          @click="$emit('close')"
          class="p-1 rounded-full hover:bg-slate-100 transition-colors"
        >
          <i class="fa-solid fa-xmark text-xl" style="color: #6B7280"></i>
        </button>
      </div>

      <!-- Corps -->
      <div class="p-6 space-y-5">

        <!-- Affectation -->
        <div class="space-y-1">
          <label class="text-xs font-bold uppercase tracking-wider" style="color: #6B7280">
            Affectation <span style="color: #EF4444">*</span>
          </label>

          <!-- Skeleton -->
          <div
            v-if="isLoadingAffectations"
            class="h-10 w-full rounded-lg animate-pulse"
            style="background-color: #E5E7EB"
          ></div>

          <!-- Aucune affectation disponible -->
          <div
            v-else-if="affectationsDisponibles.length === 0"
            class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm"
            style="background-color: #FEF3DC; color: #F97316"
          >
            <i class="fa-solid fa-triangle-exclamation text-xs"></i>
            Toutes les affectations ont deja une tache.
          </div>

          <select
            v-else
            v-model="form.affectation_id"
            class="w-full border rounded-lg px-3 py-2 text-sm outline-none bg-white transition-all"
            :style="errors?.affectation_id ? 'border-color: #EF4444' : 'border-color: #E5E7EB'"
            @focus="$event.target.style.borderColor='#F97316'"
            @blur="$event.target.style.borderColor = errors?.affectation_id ? '#EF4444' : '#E5E7EB'"
          >
            <option value="" disabled>Selectionner une affectation</option>
            <option
              v-for="a in affectationsDisponibles"
              :key="a.id"
              :value="a.id"
            >
              {{ a.label }}
            </option>
          </select>

          <p v-if="errors?.affectation_id" class="text-xs" style="color: #EF4444">
            {{ errors.affectation_id[0] }}
          </p>
        </div>

        <!-- Aperçu affectation sélectionnée -->
        <div
          v-if="affectationSelectionnee"
          class="flex items-start gap-3 p-3 rounded-lg border"
          style="background-color: #F0F4FF; border-color: #C7D7F5"
        >
          <i class="fa-solid fa-billboard text-base mt-0.5" style="color: #1B3B8A"></i>
          <div class="text-xs leading-relaxed" style="color: #374151">
            <p class="font-bold" style="color: #1B3B8A">
              {{ affectationSelectionnee.campagne?.nom }}
            </p>
            <p>
              {{ affectationSelectionnee.face?.panneau?.reference }} —
              Face {{ affectationSelectionnee.face?.numero }} ·
              {{ affectationSelectionnee.face?.panneau?.ville }}
            </p>
            <p class="mt-0.5" style="color: #6B7280">
              Du {{ affectationSelectionnee.date_debut }}
              au {{ affectationSelectionnee.date_fin }}
            </p>
          </div>
        </div>

        <!-- Agent (optionnel) -->
        <div class="space-y-1">
          <label class="text-xs font-bold uppercase tracking-wider" style="color: #6B7280">
            Agent de terrain
            <span class="font-normal normal-case ml-1" style="color: #9CA3AF">(optionnel)</span>
          </label>

          <div
            v-if="agents.length === 0"
            class="h-10 w-full rounded-lg animate-pulse"
            style="background-color: #E5E7EB"
          ></div>

          <select
            v-else
            v-model="form.agent_id"
            class="w-full border rounded-lg px-3 py-2 text-sm outline-none bg-white"
            style="border-color: #E5E7EB"
            @focus="$event.target.style.borderColor='#F97316'"
            @blur="$event.target.style.borderColor='#E5E7EB'"
          >
            <option value="">Non assigne pour l'instant</option>
            <option
              v-for="agent in agents"
              :key="agent.id"
              :value="agent.id"
            >
              {{ agent.name }}
            </option>
          </select>
        </div>

        <!-- Note (optionnelle) -->
        <div class="space-y-1">
          <label class="text-xs font-bold uppercase tracking-wider" style="color: #6B7280">
            Note
            <span class="font-normal normal-case ml-1" style="color: #9CA3AF">(optionnel)</span>
          </label>
          <textarea
            v-model="form.note"
            rows="2"
            placeholder="Instructions specifiques, materiaux, acces..."
            class="w-full border rounded-lg px-3 py-2 text-sm outline-none resize-none"
            style="border-color: #E5E7EB"
            @focus="$event.target.style.borderColor='#F97316'"
            @blur="$event.target.style.borderColor='#E5E7EB'"
          ></textarea>
        </div>

      </div>

      <!-- Footer -->
      <div
        class="p-4 border-t rounded-b-xl flex justify-end items-center gap-3"
        style="background-color: #F9FAFB; border-color: #E5E7EB"
      >
        <button
          type="button"
          @click="$emit('close')"
          class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors hover:bg-slate-100"
          style="color: #374151"
        >
          Annuler
        </button>

        <button
          type="button"
          @click="handleSubmit"
          :disabled="isLoading || !form.affectation_id"
          class="px-6 py-2 rounded-lg text-white text-sm font-bold shadow-sm transition-all
                 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
          style="background-color: #F97316"
        >
          <i v-if="isLoading" class="fa-solid fa-circle-notch animate-spin"></i>
          <i v-else class="fa-solid fa-plus text-xs"></i>
          Creer la tache
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { storeToRefs }   from 'pinia'
import { useTachesStore } from '@/stores/taches.store'

const emit = defineEmits(['close', 'saved'])

const store = useTachesStore()
const {
  agents,
  affectationsDisponibles,
  isLoading,
  isLoadingAffectations,
  errors,
} = storeToRefs(store)

const form = ref({
  affectation_id: '',
  agent_id:       '',
  note:           '',
})

// Aperçu de l'affectation sélectionnée
const affectationSelectionnee = computed(() =>
  affectationsDisponibles.value.find(a => a.id === form.value.affectation_id) ?? null
)

async function handleSubmit() {
  try {
    await store.creerTache({
      affectation_id: form.value.affectation_id,
      agent_id:       form.value.agent_id   || null,
      note:           form.value.note       || null,
    })
    emit('saved')
  } catch {
    // Erreurs 422 affichées via store.errors
  }
}
</script>
