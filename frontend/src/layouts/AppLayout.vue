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
      <nav class="flex-1 px-3 py-5 overflow-y-auto">

        <div
          v-for="section in visibleSections"
          :key="section.label"
          class="mb-6 last:mb-0"
        >
          <!-- Label de section -->
          <p
            class="px-3 mb-2 text-[10px] font-bold uppercase tracking-[0.12em]"
            style="color: rgba(255,255,255,0.4)"
          >
            {{ section.label }}
          </p>

          <div class="space-y-0.5">
            <template v-for="item in section.items" :key="item.name">

              <!-- Item route -->
              <RouterLink
                v-if="!item.action"
                :to="item.to"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg
                       text-sm font-medium transition-all duration-150 text-white"
                :class="isActive(item.to) ? '' : 'hover:bg-white/10'"
                :style="isActive(item.to)
                  ? 'background-color: rgba(249,115,22,0.25); border-left: 3px solid #F97316'
                  : 'border-left: 3px solid transparent'"
              >
                <i :class="item.icon" class="w-4 text-center"></i>
                {{ item.label }}
              </RouterLink>

              <!-- Item action (ex: Mon profil → modal) -->
              <button
                v-else
                @click="handleAction(item.action)"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg
                       text-sm font-medium transition-all duration-150 text-white
                       hover:bg-white/10 text-left"
                style="border-left: 3px solid transparent"
              >
                <i :class="item.icon" class="w-4 text-center"></i>
                {{ item.label }}
              </button>

            </template>
          </div>
        </div>

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
        <div class="flex items-center gap-3">
          <NotificationDropdown />

          <!-- Séparateur vertical -->
          <div class="h-5 w-px" style="background-color: #E5E7EB"></div>

          <!-- Dropdown profil -->
          <div class="relative" ref="profileRef">
            <button
              @click="profileOpen = !profileOpen"
              class="flex items-center gap-2 px-2 py-1.5 rounded-lg
                     transition-colors hover:bg-slate-100"
            >
              <div
                class="w-7 h-7 rounded-full flex items-center justify-center
                       text-xs font-bold text-white flex-shrink-0"
                style="background-color: #1B3B8A"
              >
                {{ initiales }}
              </div>
              <span class="text-sm font-medium hidden sm:block" style="color: #374151">
                {{ auth.user?.name }}
              </span>
              <i
                class="fa-solid fa-chevron-down text-xs transition-transform"
                :class="profileOpen ? 'rotate-180' : ''"
                style="color: #9CA3AF"
              ></i>
            </button>

            <!-- Menu -->
            <Transition name="dropdown">
              <div
                v-if="profileOpen"
                class="absolute right-0 top-11 w-56 bg-white rounded-xl
                       shadow-xl border z-50 overflow-hidden"
                style="border-color: #E5E7EB"
              >
                <!-- Info utilisateur -->
                <div class="px-4 py-3 border-b" style="border-color: #F3F4F6">
                  <p class="text-sm font-semibold truncate" style="color: #1C2833">
                    {{ auth.user?.name }}
                  </p>
                  <p class="text-xs mt-0.5 truncate" style="color: #9CA3AF">
                    {{ auth.user?.email }}
                  </p>
                </div>

                <!-- Actions -->
                <div class="py-1">
                  <button
                    @click="openProfile"
                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm
                           transition-colors hover:bg-slate-50 text-left"
                    style="color: #374151"
                  >
                    <i class="fa-solid fa-user-pen w-4 text-center" style="color: #6B7280"></i>
                    Modifier mon profil
                  </button>
                </div>

                <div class="border-t py-1" style="border-color: #F3F4F6">
                  <button
                    @click="handleLogout"
                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm
                           transition-colors hover:bg-red-50 text-left"
                    style="color: #EF4444"
                  >
                    <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                    Se déconnecter
                  </button>
                </div>
              </div>
            </Transition>
          </div>
        </div>
      </header>

      <!-- Zone de contenu — scroll interne -->
      <main class="flex-1 overflow-y-auto" style="background-color: #F0F4FF">
        <RouterView />
      </main>

    </div>
  </div>

  <ToastContainer />

  <ProfileModal
    :show="showProfile"
    @close="showProfile = false"
  />
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore }         from '@/stores/auth.store'
import NotificationDropdown     from '@/components/notifications/NotificationDropdown.vue'
import ToastContainer           from '@/components/ui/ToastContainer.vue'
import ProfileModal             from '@/components/profile/ProfileModal.vue'

const auth   = useAuthStore()
const route  = useRoute()
const router = useRouter()

const profileOpen  = ref(false)
const profileRef   = ref(null)
const showProfile  = ref(false)

function openProfile() {
  profileOpen.value = false
  showProfile.value = true
}

function handleClickOutside(e) {
  if (profileRef.value && !profileRef.value.contains(e.target)) {
    profileOpen.value = false
  }
}

onMounted(()  => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))

// ── Règles de visibilité par rôle ────────────────────────────────────────────
// roles: null = tous les rôles authentifiés
const NAV_SECTIONS = [
  {
    label: 'Principal',
    items: [
      { name: 'dashboard', label: 'Tableau de bord', to: '/', icon: 'fa-solid fa-gauge-high', roles: null },
    ],
  },
  {
    label: 'Gestion',
    items: [
      { name: 'users',     label: 'Utilisateurs', to: '/users',     icon: 'fa-solid fa-users',        roles: ['super_admin', 'gestionnaire'] },
      { name: 'panneaux',  label: 'Panneaux',     to: '/panneaux',  icon: 'fa-solid fa-sign-hanging', roles: ['super_admin', 'gestionnaire', 'agent_terrain'] },
      { name: 'campagnes', label: 'Campagnes',    to: '/campagnes', icon: 'fa-solid fa-bullhorn',     roles: ['super_admin', 'gestionnaire', 'annonceur'] },
      { name: 'taches',    label: 'Taches',       to: '/taches',    icon: 'fa-solid fa-list-check',   roles: ['super_admin', 'gestionnaire', 'agent_terrain'] },
    ],
  },
  {
    label: 'Analyse',
    items: [
      { name: 'statistiques', label: 'Statistiques', to: '/statistiques', icon: 'fa-solid fa-chart-line', roles: ['super_admin', 'gestionnaire'] },
    ],
  },
  {
    label: 'Compte',
    items: [
      { name: 'profile', label: 'Profile', action: 'openProfile', icon: 'fa-solid fa-user-pen', roles: null },
    ],
  },
]

const visibleSections = computed(() => {
  const role = auth.user?.role
  return NAV_SECTIONS
    .map(section => ({
      ...section,
      items: section.items.filter(item =>
        item.roles === null || (role && item.roles.includes(role))
      ),
    }))
    .filter(section => section.items.length > 0)
})

function handleAction(action) {
  if (action === 'openProfile') {
    showProfile.value = true
  }
}

function isActive(path) {
  if (path === '/') return route.path === '/'
  return route.path.startsWith(path)
}

const currentPageLabel = computed(() => {
  for (const section of visibleSections.value) {
    const item = section.items.find(i => i.to && isActive(i.to))
    if (item) return item.label
  }
  return 'Tableau de bord'
})

const initiales = computed(() => {
  const name = auth.user?.name
  if (!name) return '?'
  const parts = name.trim().split(' ')
  return parts.length > 1
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : parts[0].substring(0, 2).toUpperCase()
})

function handleLogout() {
  auth.logout()                        // synchrone — nettoie le state immédiatement
  router.push({ name: 'login' })       // redirection instantanée
}
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active { transition: opacity 0.12s ease, transform 0.12s ease; }
.dropdown-enter-from,
.dropdown-leave-to     { opacity: 0; transform: translateY(-6px); }
</style>