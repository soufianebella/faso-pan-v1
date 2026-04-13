<template>
  <div class="bg-white rounded-lg shadow-sm border overflow-hidden"
       style="border-color: #E5E7EB">

    <table class="w-full text-left border-collapse">
      <thead class="text-white uppercase text-xs font-semibold"
             style="background-color: #1B3B8A">
        <tr>
          <th class="px-4 py-3">Utilisateur</th>
          <th class="px-4 py-3">Role</th>
          <th class="px-4 py-3">Statut</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>

      <tbody style="color: #374151">

        <tr v-if="users.length === 0">
          <td colspan="4" class="py-12 text-center" style="color: #9CA3AF">
            <div class="flex flex-col items-center gap-2">
              <i class="fa-solid fa-users-slash text-3xl"></i>
              <span class="text-sm font-medium">Aucun utilisateur trouve</span>
            </div>
          </td>
        </tr>

        <tr
          v-for="user in users"
          :key="user.id"
          class="border-b transition-colors hover:bg-slate-50"
          style="border-color: #F3F4F6"
        >
          <td class="px-4 py-3">
            <div class="flex items-center gap-3">
              <div
                class="h-8 w-8 rounded-full flex items-center justify-center
                       text-xs font-bold text-white flex-shrink-0"
                :style="{ backgroundColor: getRoleColor(user.role) }"
              >
                {{ getInitiales(user.name) }}
              </div>
              <div class="flex flex-col min-w-0">
                <span class="font-semibold text-sm truncate">{{ user.name }}</span>
                <span class="text-xs truncate" style="color: #6B7280">
                  {{ user.email }}
                </span>
              </div>
            </div>
          </td>

          <td class="px-4 py-3">
            <span
              class="px-2 py-1 rounded-full text-xs font-bold uppercase
                     tracking-wider border"
              :style="getRoleBadgeStyle(user.role)"
            >
              {{ formatRole(user.role) }}
            </span>
          </td>

          <td class="px-4 py-3">
            <div class="flex items-center gap-1.5">
              <span
                class="h-2 w-2 rounded-full"
                :style="{
                  backgroundColor: user.actif ? '#10B981' : '#EF4444'
                }"
              ></span>
              <span class="text-xs font-medium">
                {{ user.actif ? 'Actif' : 'Inactif' }}
              </span>
            </div>
          </td>

          <td class="px-4 py-3 text-right">
            <div class="flex justify-end gap-2">
              <button
                @click="$emit('edit', user)"
                class="p-1.5 transition-colors"
                style="color: #9CA3AF"
                title="Modifier"
                @mouseenter="$event.currentTarget.style.color='#1B3B8A'"
                @mouseleave="$event.currentTarget.style.color='#9CA3AF'"
              >
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
              <button
                @click="$emit('delete', user.id)"
                class="p-1.5 transition-colors"
                style="color: #9CA3AF"
                title="Desactiver"
                @mouseenter="$event.currentTarget.style.color='#EF4444'"
                @mouseleave="$event.currentTarget.style.color='#9CA3AF'"
              >
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </div>
          </td>
        </tr>

      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  users: {
    type:     Array,
    required: true,
    default:  () => [],
  },
})

defineEmits(['edit', 'delete'])

// Noms de roles correspondant exactement au seeder
const ROLE_COLORS = {
  'super_admin':   '#1e293b',
  'gestionnaire':  '#1B3B8A',
  'agent_terrain': '#F97316',
  'annonceur':     '#059669'
}

function getRoleColor(role) {
  return ROLE_COLORS[role] ?? '#6B7280'
}

function getRoleBadgeStyle(role) {
  const color = getRoleColor(role)
  return {
    backgroundColor: `${color}15`,
    color,
    borderColor: `${color}30`,
  }
}

function formatRole(role) {
  const labels = {
    'super_admin':   'Super Admin',
    'gestionnaire':  'Gestionnaire',
    'agent_terrain': 'Agent Terrain',
    'annonceur':     'Annonceur'
  }
  return labels[role] ?? role
}

function getInitiales(name) {
  if (!name) return '??'
  const parts = name.trim().split(' ')
  return parts.length > 1
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : parts[0].substring(0, 2).toUpperCase()
}
</script>