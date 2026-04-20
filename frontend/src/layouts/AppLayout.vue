<!-- src/layouts/AppLayout.vue -->
<template>
  <div class="h-screen overflow-hidden flex">

    <!-- ── Sidebar ────────────────────────────────────────── -->
    <aside class="w-60 flex-shrink-0 flex flex-col h-full" style="background-color: #1B3B8A">
      <!-- Logo -->
      <div class="px-6 py-5 flex-shrink-0" style="border-bottom: 1px solid rgba(255,255,255,0.1)">
        <span class="text-xl font-bold text-white">FASO</span>
        <span class="text-xl font-bold" style="color: #F97316"> PAN</span>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

        <RouterLink v-for="item in navItems" :key="item.name" :to="item.to" class="flex items-center gap-3 px-3 py-2.5 rounded-lg
                 text-sm font-medium transition-all duration-150
                 text-white" :class="isActive(item.to)
                  ? 'text-white'
                  : 'hover:bg-white/10'" :style="isActive(item.to)
              ? 'background-color: rgba(249,115,22,0.25);' +
              'border-left: 3px solid #F97316'
              : 'border-left: 3px solid transparent'">
          <i :class="item.icon" class="w-4 text-center"></i>
          {{ item.label }}
        </RouterLink>

      </nav>

      <!-- Utilisateur connecte -->
      <div class="px-4 py-4 flex-shrink-0" style="border-top: 1px solid rgba(255,255,255,0.1)">
        <div class="flex items-center gap-3">
          <div class="h-8 w-8 rounded-full flex items-center justify-center
                   text-xs font-bold text-white flex-shrink-0" style="background-color: #F97316">
            {{ initiales }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-white truncate">
              {{ auth.user?.name }}
            </p>
            <p class="text-xs truncate" style="color: rgba(255,255,255,0.5)">
              {{ auth.user?.role }}
            </p>
          </div>
          <button @click="handleLogout" title="Deconnexion" class="text-white/50 hover:text-white transition-colors">
            <i class="fa-solid fa-right-from-bracket text-sm"></i>
          </button>
        </div>
      </div>

    </aside>

    <!-- ── Contenu principal ──────────────────────────────── -->
    <div class="flex-1 flex flex-col overflow-hidden">

      <!-- Header -->
      <header class="h-14 flex-shrink-0 flex items-center justify-between
               px-6 bg-white" style="border-bottom: 1px solid #E5E7EB">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm">
          <span style="color: #6B7280">FASO PAN</span>
          <i class="fa-solid fa-chevron-right text-xs" style="color: #D1D5DB"></i>
          <span class="font-medium" style="color: #1B3B8A">
            {{ currentPageLabel }}
          </span>
        </div>

        <!-- Actions header -->

        <div class="flex items-center gap-4">
          <NotificationDropdown />
        </div>
      </header>

      <!-- Zone de contenu — scroll interne -->
      <main class="flex-1 overflow-y-auto" style="background-color: #F0F4FF">
        <RouterView />
      </main>

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'
import NotificationDropdown from '@/components/notifications/NotificationDropdown.vue'

const auth  = useAuthStore()
const route = useRoute()

// ── Règles de visibilité par rôle ────────────────────────────────────────────
// roles: null = tous les rôles authentifiés
const ALL_NAV_ITEMS = [
  {
    name:  'dashboard',
    label: 'Tableau de bord',
    to:    '/',
    icon:  'fa-solid fa-gauge-high',
    roles: null,
  },
  {
    name:  'users',
    label: 'Utilisateurs',
    to:    '/users',
    icon:  'fa-solid fa-users',
    roles: ['super_admin', 'gestionnaire'],
  },
  {
    name:  'panneaux',
    label: 'Panneaux',
    to:    '/panneaux',
    icon:  'fa-solid fa-sign-hanging',
    roles: ['super_admin', 'gestionnaire', 'agent_terrain'],
  },
  {
    name:  'campagnes',
    label: 'Campagnes',
    to:    '/campagnes',
    icon:  'fa-solid fa-bullhorn',
    roles: ['super_admin', 'gestionnaire', 'annonceur'],
  },
  {
    name:  'taches',
    label: 'Taches',
    to:    '/taches',
    icon:  'fa-solid fa-list-check',
    roles: ['super_admin', 'gestionnaire', 'agent_terrain'],
  },
  {
    name:  'statistiques',
    label: 'Statistiques',
    to:    '/statistiques',
    icon:  'fa-solid fa-chart-line',
    roles: ['super_admin', 'gestionnaire'],
  },
]

const navItems = computed(() => {
  const role = auth.user?.role
  return ALL_NAV_ITEMS.filter(item =>
    item.roles === null || (role && item.roles.includes(role))
  )
})

function isActive(path) {
  if (path === '/') return route.path === '/'
  return route.path.startsWith(path)
}

const currentPageLabel = computed(() => {
  const item = navItems.value.find(i => isActive(i.to))
  return item?.label ?? 'Tableau de bord'
})

const initiales = computed(() => {
  const name = auth.user?.name
  if (!name) return '?'
  const parts = name.trim().split(' ')
  return parts.length > 1
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : parts[0].substring(0, 2).toUpperCase()
})

async function handleLogout() {
  await auth.logout()
}
</script>