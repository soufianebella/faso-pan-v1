<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold text-[#0D2137]">
      Tableau de bord
    </h1>
    <p class="text-gray-500 mt-2">
      Bienvenue, {{ auth.user?.name }} —
      <span class="font-semibold text-[#1B4F8A]">
        {{ auth.user?.role }}
      </span>
    </p>

    <button
      @click="handleLogout"
      class="mt-6 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
    >
      Se déconnecter
    </button>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth.store'

const auth = useAuthStore()

async function handleLogout() {
  await auth.logout()
}
</script>