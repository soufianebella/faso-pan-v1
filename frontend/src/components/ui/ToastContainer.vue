<template>
  <teleport to="body">
    <div class="fixed bottom-5 right-5 z-[9999] flex flex-col gap-2 pointer-events-none">
      <transition-group name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-xl shadow-lg
                 min-w-[280px] max-w-sm"
          :style="styleFor(toast.type)"
        >
          <i :class="iconFor(toast.type)" class="text-base flex-shrink-0 mt-0.5"></i>

          <div class="flex-1 min-w-0">
            <p v-if="toast.title" class="text-xs font-bold uppercase tracking-wide mb-0.5">
              {{ toast.title }}
            </p>
            <p class="text-sm leading-snug">{{ toast.message }}</p>
          </div>

          <button
            @click="dismiss(toast.id)"
            class="flex-shrink-0 opacity-60 hover:opacity-100 transition-opacity"
          >
            <i class="fa-solid fa-xmark text-sm"></i>
          </button>
        </div>
      </transition-group>
    </div>
  </teleport>
</template>

<script setup>
import { useToast } from '@/composables/useToast'

const { toasts, dismiss } = useToast()

const STYLES = {
  success: 'background-color: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0',
  error:   'background-color: #FEE2E2; color: #991B1B; border: 1px solid #FECACA',
  warning: 'background-color: #FEF3DC; color: #92400E; border: 1px solid #FDE68A',
  info:    'background-color: #EBF3FC; color: #1B3B8A; border: 1px solid #C7D7F5',
}

const ICONS = {
  success: 'fa-solid fa-circle-check',
  error:   'fa-solid fa-circle-xmark',
  warning: 'fa-solid fa-triangle-exclamation',
  info:    'fa-solid fa-circle-info',
}

function styleFor(type) { return STYLES[type] ?? STYLES.info }
function iconFor(type)  { return ICONS[type]  ?? ICONS.info  }
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active { transition: all 0.25s ease; }

.toast-enter-from { opacity: 0; transform: translateX(100%); }
.toast-leave-to   { opacity: 0; transform: translateX(100%); }
</style>
