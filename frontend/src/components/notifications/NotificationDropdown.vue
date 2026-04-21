<template>
  <div class="relative" ref="dropdownRef">

    <!-- Cloche -->
    <button
      @click="toggleDropdown"
      class="relative p-2 rounded-lg transition-colors hover:bg-slate-100"
      style="color: #6B7280"
    >
      <i class="fa-solid fa-bell text-lg"></i>
      <span
        v-if="count > 0"
        class="absolute -top-1 -right-1 h-5 w-5 rounded-full
               text-white text-xs flex items-center justify-center
               font-bold"
        style="background-color: #F97316; font-size: 9px"
      >
        {{ count > 9 ? '9+' : count }}
      </span>
    </button>

    <!-- Dropdown -->
    <div
      v-if="open"
      class="absolute right-0 top-10 w-80 bg-white rounded-xl
             shadow-xl border z-50"
      style="border-color: #E5E7EB"
    >
      <!-- Header dropdown -->
      <div
        class="flex items-center justify-between px-4 py-3 border-b"
        style="border-color: #F3F4F6"
      >
        <h3 class="text-sm font-bold" style="color: #1B3B8A">
          Notifications
          <span
            v-if="count > 0"
            class="ml-2 px-1.5 py-0.5 rounded-full text-xs font-bold text-white"
            style="background-color: #F97316"
          >
            {{ count }}
          </span>
        </h3>
        <button
          v-if="count > 0"
          @click="store.marquerToutesLues()"
          class="text-xs font-medium transition-colors"
          style="color: #F97316"
        >
          Tout lire
        </button>
      </div>

      <!-- Liste notifications -->
      <div class="max-h-80 overflow-y-auto">

        <div
          v-if="isLoading"
          class="p-4 space-y-3"
        >
          <div
            v-for="i in 3" :key="i"
            class="h-12 rounded animate-pulse"
            style="background-color: #F3F4F6"
          ></div>
        </div>

        <div
          v-else-if="notifications.length === 0"
          class="py-10 text-center"
          style="color: #9CA3AF"
        >
          <i class="fa-solid fa-bell-slash text-2xl mb-2 block"></i>
          <p class="text-xs">Aucune notification</p>
        </div>

        <div
          v-for="notif in notifications"
          :key="notif.id"
          class="flex gap-3 px-4 py-3 border-b cursor-pointer
                 transition-colors hover:bg-slate-50"
          style="border-color: #F9FAFB"
          @click="handleClick(notif)"
        >
          <!-- Icone selon type -->
          <div
            class="w-8 h-8 rounded-full flex items-center justify-center
                   flex-shrink-0 mt-0.5"
            :style="getIconStyle(notif.type)"
          >
            <i :class="getIconClass(notif.type)" class="text-sm"></i>
          </div>

          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold" style="color: #1C2833">
              {{ notif.titre }}
            </p>
            <p class="text-xs mt-0.5 truncate" style="color: #6B7280">
              {{ notif.message }}
            </p>
          </div>

          <!-- Point non lu -->
          <div
            v-if="!notif.lu_at"
            class="w-2 h-2 rounded-full flex-shrink-0 mt-1.5"
            style="background-color: #F97316"
          ></div>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { storeToRefs }                  from 'pinia'
import { useNotificationsStore }        from '@/stores/notifications.store'
import { useRouter }                    from 'vue-router'

const store  = useNotificationsStore()
const router = useRouter()
const { notifications, count, isLoading } = storeToRefs(store)

const open        = ref(false)
const dropdownRef = ref(null)
let   pollInterval = null

function toggleDropdown() {
  open.value = !open.value
  if (open.value) store.fetchNotifications()
}

async function handleClick(notif) {
  await store.marquerLue(notif.id)
  if (notif.lien) {
    router.push(notif.lien)
    open.value = false
  }
}

// Ferme si clic extérieur
function handleClickOutside(e) {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    open.value = false
  }
}

const TYPE_ICONS = {
  nouvelle_tache:  { icon: 'fa-solid fa-list-check', bg: '#EBF3FC', color: '#1B3B8A' },
  campagne_creee:  { icon: 'fa-solid fa-bullhorn',   bg: '#FEF3DC', color: '#F97316' },
  expiration_j7:   { icon: 'fa-solid fa-triangle-exclamation', bg: '#FEE2E2', color: '#EF4444' },
}

function getIconStyle(type) {
  const t = TYPE_ICONS[type]
  return t
    ? { backgroundColor: t.bg, color: t.color }
    : { backgroundColor: '#F3F4F6', color: '#6B7280' }
}

function getIconClass(type) {
  return TYPE_ICONS[type]?.icon ?? 'fa-solid fa-bell'
}

onMounted(() => {
  store.fetchCount()
  document.addEventListener('click', handleClickOutside)
  pollInterval = setInterval(() => store.fetchCount(), 30000)
})

onUnmounted(() => {
  clearInterval(pollInterval)
  document.removeEventListener('click', handleClickOutside)
})
</script>