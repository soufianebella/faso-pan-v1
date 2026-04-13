<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="background-color: rgba(15, 23, 42, 0.7)"
    @click.self="$emit('close')"
  >
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">

      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold" style="color: #1B3B8A">
          {{ isEditMode ? "Modifier l'utilisateur" : 'Nouvel utilisateur' }}
        </h2>
        <button
          @click="$emit('close')"
          class="p-1 rounded-full transition-colors hover:bg-slate-100"
        >
          <i class="fa-solid fa-xmark" style="color: #6B7280"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">

        <!-- Nom -->
        <div class="space-y-1">
          <label class="text-xs font-bold uppercase" style="color: #6B7280">
            Nom complet
          </label>
          <input
            v-model="form.name"
            type="text"
            placeholder="Ex: Kone Ibrahim"
            class="w-full border rounded-lg px-3 py-2 text-sm
                   outline-none transition-all"
            :style="errors?.name
              ? 'border-color: #EF4444'
              : 'border-color: #E5E7EB'"
            @focus="$event.target.style.borderColor='#F97316'"
            @blur="$event.target.style.borderColor=
              errors?.name ? '#EF4444' : '#E5E7EB'"
          />
          <p v-if="errors?.name"
             class="text-xs font-semibold"
             style="color: #EF4444">
            {{ errors.name[0] }}
          </p>
        </div>

        <!-- Email -->
        <div class="space-y-1">
          <label class="text-xs font-bold uppercase" style="color: #6B7280">
            Email professionnel
          </label>
          <input
            v-model="form.email"
            type="email"
            placeholder="utilisateur@fasopan.bf"
            class="w-full border rounded-lg px-3 py-2 text-sm
                   outline-none transition-all"
            :style="errors?.email
              ? 'border-color: #EF4444'
              : 'border-color: #E5E7EB'"
            @focus="$event.target.style.borderColor='#F97316'"
            @blur="$event.target.style.borderColor=
              errors?.email ? '#EF4444' : '#E5E7EB'"
          />
          <p v-if="errors?.email"
             class="text-xs font-semibold"
             style="color: #EF4444">
            {{ errors.email[0] }}
          </p>
        </div>

        <!-- Role -->
        <div class="space-y-1">
          <label class="text-xs font-bold uppercase" style="color: #6B7280">
            Role systeme
          </label>
          <select
            v-model="form.role"
            class="w-full border rounded-lg px-3 py-2 text-sm
                   outline-none bg-white transition-all"
            :style="errors?.role
              ? 'border-color: #EF4444'
              : 'border-color: #E5E7EB'"
          >
            <option value="agent_terrain">Agent Terrain</option>
            <option value="gestionnaire">Gestionnaire</option>
            <option value="annonceur">Annonceur</option>
            <option value="super_admin">Super Administrateur</option>
          </select>
          <p v-if="errors?.role"
             class="text-xs font-semibold"
             style="color: #EF4444">
            {{ errors.role[0] }}
          </p>
        </div>

        <!-- Mot de passe -->
        <div class="space-y-1">
          <label class="text-xs font-bold uppercase" style="color: #6B7280">
            {{ isEditMode ? 'Nouveau mot de passe (optionnel)' : 'Mot de passe' }}
          </label>
          <input
            v-model="form.password"
            type="password"
            class="w-full border rounded-lg px-3 py-2 text-sm
                   outline-none transition-all"
            :style="errors?.password
              ? 'border-color: #EF4444'
              : 'border-color: #E5E7EB'"
            @focus="$event.target.style.borderColor='#F97316'"
            @blur="$event.target.style.borderColor=
              errors?.password ? '#EF4444' : '#E5E7EB'"
          />
          <p v-if="errors?.password"
             class="text-xs font-semibold"
             style="color: #EF4444">
            {{ errors.password[0] }}
          </p>
        </div>

        <!-- Confirmation mot de passe — DIV SEPARÉ, pas imbriqué -->
        <div class="space-y-1">
          <label class="text-xs font-bold uppercase" style="color: #6B7280">
            {{ isEditMode
              ? 'Confirmer le nouveau mot de passe'
              : 'Confirmer le mot de passe' }}
          </label>
          <input
            v-model="form.password_confirmation"
            type="password"
            class="w-full border rounded-lg px-3 py-2 text-sm
                   outline-none transition-all"
            :style="errors?.password_confirmation
              ? 'border-color: #EF4444'
              : 'border-color: #E5E7EB'"
            @focus="$event.target.style.borderColor='#F97316'"
            @blur="$event.target.style.borderColor=
              errors?.password_confirmation ? '#EF4444' : '#E5E7EB'"
          />
          <p v-if="errors?.password_confirmation"
             class="text-xs font-semibold"
             style="color: #EF4444">
            {{ errors.password_confirmation[0] }}
          </p>
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-sm font-semibold rounded-lg
                   transition-colors hover:bg-slate-50"
            style="color: #374151"
          >
            Annuler
          </button>
          <button
            type="submit"
            :disabled="isLoading"
            class="px-6 py-2 text-sm font-bold text-white rounded-lg
                   shadow-sm transition-all flex items-center gap-2
                   disabled:opacity-50 disabled:cursor-not-allowed"
            style="background-color: #1B3B8A"
          >
            <i v-if="isLoading" class="fa-solid fa-circle-notch animate-spin"></i>
            {{ isEditMode ? 'Enregistrer' : 'Creer' }}
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { storeToRefs }          from 'pinia'
import { useUsersStore }        from '@/stores/users.store'

const props = defineProps({
  show: { type: Boolean, required: true },
  user: { type: Object,  default: null  },
})

const emit = defineEmits(['close', 'saved'])

const store = useUsersStore()
const { errors, isLoading } = storeToRefs(store)

const isEditMode = computed(() => !!props.user)

const form = ref({
  name:                  '',
  email:                 '',
  role:                  'agent_terrain',
  password:              '',
  password_confirmation: '',
})

watch(
  () => props.user,
  (newUser) => {
    if (newUser) {
      form.value = {
        name:                  newUser.name,
        email:                 newUser.email,
        role:                  newUser.role,
        password:              '',
        password_confirmation: '',
      }
    } else {
      resetForm()
    }
  },
  { immediate: true }
)

function resetForm() {
  form.value = {
    name:                  '',
    email:                 '',
    role:                  'agent_terrain',
    password:              '',
    password_confirmation: '',
  }
  store.clearErrors()
}

async function handleSubmit() {
  try {
    if (isEditMode.value) {
      await store.updateUser(props.user.id, form.value)
    } else {
      await store.createUser(form.value)
    }
    emit('saved')
    resetForm()
  } catch {
    // Erreurs 422 dans store.errors — affichees par champ
  }
}
</script>