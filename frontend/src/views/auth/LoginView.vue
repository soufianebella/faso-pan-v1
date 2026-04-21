<template>
  <div class="min-h-screen flex font-sans">

    <!-- Panneau gauche — bleu royal -->
    <div class="hidden lg:flex w-1/2 flex-col
                items-center justify-center p-12 relative"
         style="background-color: #1B3B8A">

      <div class="text-center">
        <!-- Logo cohérent avec la sidebar : FASO blanc / PAN orange -->
        <h1 class="text-5xl font-bold mb-2">
          <span class="text-white">FASO </span><span style="color: #F97316">PAN</span>
        </h1>
        <p class="mb-10 text-sm" style="color: rgba(255,255,255,0.6); font-style: italic">
          Gestion des panneaux publicitaires
        </p>

        <ul class="space-y-4 text-left">
          <li class="flex items-center gap-3 text-white text-sm">
            <i class="fa-solid fa-signs-post w-5 text-center" style="color: #F97316"></i>
            Gestion optimisée des panneaux
          </li>
          <li class="flex items-center gap-3 text-white text-sm">
            <i class="fa-solid fa-bullhorn w-5 text-center" style="color: #F97316"></i>
            Suivi des campagnes en temps réel
          </li>
          <li class="flex items-center gap-3 text-white text-sm">
            <i class="fa-solid fa-users w-5 text-center" style="color: #F97316"></i>
            Coordination fluide des agents terrain
          </li>
        </ul>
      </div>

      <!-- Footer collé en bas du panneau gauche -->
      <p class="absolute bottom-6 text-xs" style="color: rgba(255,255,255,0.35)">
        © 2026 FASO PAN — Plateforme sécurisée
      </p>
    </div>

    <!-- Panneau droite — formulaire -->
    <div class="flex-1 flex flex-col items-center justify-center p-6"
         style="background-color: #F0F4FF">

      <div class="w-full max-w-md bg-white rounded-xl shadow-sm p-8">

        <!-- Logo mobile (visible uniquement < lg) -->
        <div class="flex justify-center mb-6 lg:hidden">
          <span class="text-2xl font-bold">
            <span style="color: #1B3B8A">FASO </span><span style="color: #F97316">PAN</span>
          </span>
        </div>

        <div class="mb-8">
          <h2 class="text-2xl font-bold mb-1" style="color: #1B3B8A">
            Connexion
          </h2>
          <p class="text-sm" style="color: #6B7280">
            Accédez à votre espace de gestion
          </p>
        </div>

        <div
          v-if="error"
          class="mb-5 p-3 rounded-lg bg-red-50
                 border-l-4 border-red-500 text-red-700 text-sm"
        >
          <i class="fa-solid fa-circle-exclamation mr-2"></i>
          {{ error }}
        </div>

        <form @submit.prevent="handleLogin" class="space-y-5">

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium mb-1.5" style="color: #374151">
              Adresse email
            </label>
            <div class="relative">
              <i class="fa-solid fa-envelope absolute left-3 top-1/2 -translate-y-1/2
                         text-sm" style="color: #9CA3AF"></i>
              <input
                v-model="form.email"
                type="email"
                required
                placeholder="votre@email.bf"
                class="w-full pl-9 pr-4 py-2.5 rounded-lg
                       border text-sm outline-none transition"
                style="border-color: #E5E7EB"
                @focus="$event.target.style.borderColor='#F97316'"
                @blur="$event.target.style.borderColor='#E5E7EB'"
              />
            </div>
          </div>

          <!-- Mot de passe -->
          <div>
            <label class="block text-sm font-medium mb-1.5" style="color: #374151">
              Mot de passe
            </label>
            <div class="relative">
              <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2
                         text-sm" style="color: #9CA3AF"></i>
              <input
                v-model="form.password"
                type="password"
                required
                placeholder="Minimum 8 caractères"
                class="w-full pl-9 pr-4 py-2.5 rounded-lg
                       border text-sm outline-none transition"
                style="border-color: #E5E7EB"
                @focus="$event.target.style.borderColor='#F97316'"
                @blur="$event.target.style.borderColor='#E5E7EB'"
              />
            </div>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full py-3 rounded-lg font-semibold
                   text-white transition-colors duration-200
                   disabled:opacity-60 disabled:cursor-not-allowed
                   flex items-center justify-center gap-2"
            style="background-color: #F97316"
            @mouseenter="!loading && ($event.target.style.backgroundColor='#EA6C0A')"
            @mouseleave="!loading && ($event.target.style.backgroundColor='#F97316')"
          >
            <i
              :class="loading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-right-to-bracket'"
            ></i>
            {{ loading ? 'Connexion en cours...' : 'Se connecter' }}
          </button>

        </form>
      </div>

      <!-- Footer mobile -->
      <p class="mt-6 text-xs lg:hidden" style="color: #9CA3AF">
        © 2026 FASO PAN — Plateforme sécurisée
      </p>
    </div>

  </div>
</template>

<script setup>
import { ref }           from 'vue'
import { useAuthStore }  from '@/stores/auth.store'
import { useRouter }     from 'vue-router'

const auth    = useAuthStore()
const router  = useRouter()
const loading = ref(false)
const error   = ref(null)
const form    = ref({ email: '', password: '' })

async function handleLogin() {
  loading.value = true
  error.value   = null
  try {
    await auth.login(form.value)
    router.push({ name: 'dashboard' })
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur de connexion.'
  } finally {
    loading.value = false
  }
}
</script>
