<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="background-color: rgba(0,0,0,0.4)"
        @click.self="$emit('close')"
      >
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">

          <!-- Header -->
          <div
            class="flex items-center justify-between px-6 py-4 border-b"
            style="border-color: #F3F4F6"
          >
            <div class="flex items-center gap-3">
              <div
                class="w-9 h-9 rounded-full flex items-center justify-center
                       text-sm font-bold text-white flex-shrink-0"
                style="background-color: #F97316"
              >
                {{ initiales }}
              </div>
              <div>
                <h2 class="text-sm font-bold" style="color: #1B3B8A">
                  Mon profile
                </h2>
                <p class="text-xs" style="color: #9CA3AF">
                  {{ auth.user?.role }}
                </p>
              </div>
            </div>
            <button
              @click="$emit('close')"
              class="p-1.5 rounded-lg transition-colors hover:bg-slate-100"
              style="color: #9CA3AF"
            >
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <!-- Formulaire -->
          <form @submit.prevent="handleSubmit" class="px-6 py-5 space-y-4">

            <!-- Nom -->
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color: #374151">
                Nom complet
              </label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-all"
                :style="errors.name ? 'border-color:#EF4444' : 'border-color:#E5E7EB'"
                @focus="$event.target.style.borderColor='#1B3B8A'"
                @blur="$event.target.style.borderColor = errors.name ? '#EF4444' : '#E5E7EB'"
              />
              <p v-if="errors.name" class="text-xs mt-1" style="color: #EF4444">
                {{ errors.name[0] }}
              </p>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color: #374151">
                Adresse email
              </label>
              <input
                v-model="form.email"
                type="email"
                class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-all"
                :style="errors.email ? 'border-color:#EF4444' : 'border-color:#E5E7EB'"
                @focus="$event.target.style.borderColor='#1B3B8A'"
                @blur="$event.target.style.borderColor = errors.email ? '#EF4444' : '#E5E7EB'"
              />
              <p v-if="errors.email" class="text-xs mt-1" style="color: #EF4444">
                {{ errors.email[0] }}
              </p>
            </div>

            <!-- Séparateur -->
            <div class="pt-1 pb-1 border-t" style="border-color: #F3F4F6">
              <p class="text-xs font-semibold" style="color: #9CA3AF">
                Changer le mot de passe <span class="font-normal">(optionnel)</span>
              </p>
            </div>

            <!-- Nouveau mot de passe -->
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color: #374151">
                Nouveau mot de passe
              </label>
              <div class="relative">
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  class="w-full px-3 py-2 pr-10 border rounded-lg text-sm outline-none transition-all"
                  :style="errors.password ? 'border-color:#EF4444' : 'border-color:#E5E7EB'"
                  placeholder="Laisser vide pour ne pas changer"
                  @focus="$event.target.style.borderColor='#1B3B8A'"
                  @blur="$event.target.style.borderColor = errors.password ? '#EF4444' : '#E5E7EB'"
                />
                <button
                  type="button"
                  class="absolute right-3 top-1/2 -translate-y-1/2 transition-colors"
                  style="color: #9CA3AF"
                  @click="showPassword = !showPassword"
                >
                  <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-xs"></i>
                </button>
              </div>
              <p v-if="errors.password" class="text-xs mt-1" style="color: #EF4444">
                {{ errors.password[0] }}
              </p>
            </div>

            <!-- Confirmation mot de passe -->
            <div v-if="form.password">
              <label class="block text-xs font-semibold mb-1.5" style="color: #374151">
                Confirmer le mot de passe
              </label>
              <input
                v-model="form.password_confirmation"
                :type="showPassword ? 'text' : 'password'"
                class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-all"
                style="border-color: #E5E7EB"
                @focus="$event.target.style.borderColor='#1B3B8A'"
                @blur="$event.target.style.borderColor='#E5E7EB'"
              />
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-2">
              <button
                type="button"
                @click="$emit('close')"
                class="px-4 py-2 text-sm rounded-lg border transition-colors hover:bg-slate-50"
                style="border-color: #E5E7EB; color: #6B7280"
              >
                Annuler
              </button>
              <button
                type="submit"
                :disabled="isSaving"
                class="px-4 py-2 text-sm font-medium text-white rounded-lg
                       transition-colors flex items-center gap-2 disabled:opacity-60"
                style="background-color: #F97316"
                @mouseenter="!isSaving && ($event.target.style.backgroundColor='#EA6C0A')"
                @mouseleave="$event.target.style.backgroundColor='#F97316'"
              >
                <i v-if="isSaving" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                {{ isSaving ? 'Enregistrement...' : 'Enregistrer' }}
              </button>
            </div>

          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useAuthStore } from '@/stores/auth.store'
import { useToast }     from '@/composables/useToast'

const props = defineProps({
  show: { type: Boolean, required: true },
})

const emit = defineEmits(['close'])

const auth    = useAuthStore()
const toast   = useToast()
const isSaving     = ref(false)
const showPassword = ref(false)
const errors       = ref({})

const form = ref({
  name:                  '',
  email:                 '',
  password:              '',
  password_confirmation: '',
})

// Pré-remplit le formulaire à l'ouverture
watch(() => props.show, (val) => {
  if (val) {
    form.value = {
      name:                  auth.user?.name  ?? '',
      email:                 auth.user?.email ?? '',
      password:              '',
      password_confirmation: '',
    }
    errors.value  = {}
    showPassword.value = false
  }
})

const initiales = computed(() => {
  const name = auth.user?.name ?? ''
  if (!name) return '?'
  const parts = name.trim().split(' ')
  return parts.length > 1
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : name.substring(0, 2).toUpperCase()
})

async function handleSubmit() {
  isSaving.value = true
  errors.value   = {}

  // N'envoie le password que s'il est renseigné
  const payload = {
    name:  form.value.name,
    email: form.value.email,
  }
  if (form.value.password) {
    payload.password              = form.value.password
    payload.password_confirmation = form.value.password_confirmation
  }

  try {
    await auth.updateProfile(payload)
    toast.success('Profil mis à jour.')
    emit('close')
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors ?? {}
    } else {
      toast.error('Erreur lors de la mise à jour.')
    }
  } finally {
    isSaving.value = false
  }
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.modal-enter-from,
.modal-leave-to     { opacity: 0; transform: scale(0.97); }
</style>
