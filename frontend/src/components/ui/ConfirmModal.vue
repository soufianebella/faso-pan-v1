<template>
  <teleport to="body">
    <transition name="fade">
      <div
        v-if="modelValue"
        class="fixed inset-0 z-[9990] flex items-center justify-center p-4"
        style="background-color: rgba(15,23,42,0.65)"
        @click.self="$emit('update:modelValue', false)"
      >
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm">

          <!-- Header -->
          <div class="p-5 flex items-start gap-3">
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
              :style="iconContainerStyle"
            >
              <i :class="iconClass" class="text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="text-base font-bold" style="color: #1C2833">{{ title }}</h3>
              <p class="text-sm mt-1" style="color: #6B7280">{{ message }}</p>
            </div>
          </div>

          <!-- Footer -->
          <div
            class="px-5 pb-5 flex justify-end gap-3"
          >
            <button
              type="button"
              @click="$emit('update:modelValue', false)"
              class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors hover:bg-slate-100"
              style="color: #374151"
            >
              Annuler
            </button>
            <button
              type="button"
              @click="handleConfirm"
              class="px-5 py-2 rounded-lg text-white text-sm font-bold transition-colors"
              :style="confirmBtnStyle"
            >
              {{ confirmLabel }}
            </button>
          </div>

        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue:   { type: Boolean, required: true },
  title:        { type: String,  default: 'Confirmer l\'action' },
  message:      { type: String,  default: 'Cette action est irréversible.' },
  confirmLabel: { type: String,  default: 'Confirmer' },
  variant:      { type: String,  default: 'danger' }, // danger | warning | primary
})

const emit = defineEmits(['update:modelValue', 'confirm'])

const VARIANTS = {
  danger:  { icon: 'fa-solid fa-trash',              bg: '#FEE2E2', color: '#EF4444', btn: '#EF4444' },
  warning: { icon: 'fa-solid fa-triangle-exclamation', bg: '#FEF3DC', color: '#F97316', btn: '#F97316' },
  primary: { icon: 'fa-solid fa-circle-check',       bg: '#EBF3FC', color: '#1B3B8A', btn: '#1B3B8A' },
}

const v = computed(() => VARIANTS[props.variant] ?? VARIANTS.danger)

const iconContainerStyle = computed(() =>
  `background-color: ${v.value.bg}; color: ${v.value.color}`
)
const iconClass          = computed(() => v.value.icon)
const confirmBtnStyle    = computed(() => `background-color: ${v.value.btn}`)

function handleConfirm() {
  emit('confirm')
  emit('update:modelValue', false)
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from,  .fade-leave-to      { opacity: 0; }
</style>
