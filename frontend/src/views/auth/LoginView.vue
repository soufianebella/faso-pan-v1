<template>
  <div class="min-h-screen flex font-sans">

    <!-- Panneau gauche — bleu royal -->
    <div class="hidden lg:flex w-1/2 flex-col
                items-center justify-center p-12"
         style="background-color: #1B3B8A">

      <div class="text-center">
        <h1 class="text-5xl font-bold text-white mb-2">
          FASO PAN
        </h1>
        <p class="mb-10" style="color: #F97316; font-style: italic">
          Gestion des panneaux publicitaires
        </p>

        <ul class="space-y-4 text-left">
          <li class="flex items-center gap-3 text-white">
            <span class="text-lg" style="color: #F97316">✦</span>
            Gestion optimisée des panneaux
          </li>
          <li class="flex items-center gap-3 text-white">
            <span class="text-lg" style="color: #F97316">✦</span>
            Suivi des campagnes en temps réel
          </li>
          <li class="flex items-center gap-3 text-white">
            <span class="text-lg" style="color: #F97316">✦</span>
            Coordination fluide des agents terrain
          </li>
        </ul>
      </div>

      <p class="mt-16 text-xs" style="color: rgba(255,255,255,0.4)">
        © 2026 FASO PAN — Plateforme sécurisée
      </p>
    </div>

    <!-- Panneau droite — formulaire -->
    <div class="flex-1 flex items-center justify-center p-6"
         style="background-color: #F0F4FF">

      <div class="w-full max-w-md bg-white rounded-xl shadow-sm p-8">

        <div class="mb-8">
          <h2 class="text-2xl font-bold mb-1"
              style="color: #1B3B8A">
            Connexion
          </h2>
          <p style="color: #6B7280">
            Accédez à votre espace de gestion
          </p>
        </div>

        <div v-if="error"
             class="mb-5 p-3 rounded-lg bg-red-50
                    border-l-4 border-red-500 text-red-700 text-sm">
          {{ error }}
        </div>

        <form @submit.prevent="handleLogin" class="space-y-5">

          <div>
            <label class="block text-sm font-medium mb-1.5"
                   style="color: #374151">
              Adresse email
            </label> 
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2
                           text-gray-400">✉</span>
              <input
                v-model="form.email"
                type="email"
                required
                placeholder="votre@email.bf"
                class="w-full pl-9 pr-4 py-2.5 rounded-lg
                       border text-sm outline-none transition"
                style="border-color: #E5E7EB;
                       --tw-ring-color: #F97316"
                @focus="$event.target.style.borderColor='#F97316'"
                @blur="$event.target.style.borderColor='#E5E7EB'"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1.5"
                   style="color: #374151">
              Mot de passe
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2
                           text-gray-400">🔒</span>
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
                   disabled:opacity-60 disabled:cursor-not-allowed"
            style="background-color: #F97316"
            @mouseenter="!loading && ($event.target.style.backgroundColor='#EA6C0A')"
            @mouseleave="!loading && ($event.target.style.backgroundColor='#F97316')"
          >
            {{ loading ? 'Connexion en cours...' : '→ Se connecter' }}
          </button>

        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth.store'
import {useRouter} from "vue-router";

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
    error.value = err.response?.data?.message
                  || "Erreur de connexion."
  } finally {
    loading.value = false
  }
}
</script>