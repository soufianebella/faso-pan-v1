<template>
  <div class="flex items-center justify-between py-3 px-4
              border-t bg-white"
       style="border-color: #E5E7EB">

    <span class="text-sm" style="color: #6B7280">
      Affichage
      <span class="font-medium" style="color: #374151">{{ from }}</span>
      -
      <span class="font-medium" style="color: #374151">{{ to }}</span>
      sur
      <span class="font-medium" style="color: #374151">{{ pagination.total }}</span>
    </span>

    <div class="flex items-center gap-1">

      <button
        @click="changePage(pagination.currentPage - 1)"
        :disabled="pagination.currentPage === 1"
        class="p-2 rounded transition-colors
               disabled:opacity-30 disabled:cursor-not-allowed
               hover:bg-slate-100"
      >
        <i class="fa-solid fa-chevron-left text-xs"></i>
      </button>

      <button
        v-for="page in visiblePages"
        :key="page"
        @click="changePage(page)"
        class="w-8 h-8 text-xs font-bold rounded transition-all"
        :style="pagination.currentPage === page
          ? 'background-color: #1B3B8A; color: #FFFFFF'
          : 'color: #374151'"
        :class="pagination.currentPage !== page && 'hover:bg-slate-100'"
      >
        {{ page }}
      </button>

      <button
        @click="changePage(pagination.currentPage + 1)"
        :disabled="pagination.currentPage === pagination.lastPage"
        class="p-2 rounded transition-colors
               disabled:opacity-30 disabled:cursor-not-allowed
               hover:bg-slate-100"
      >
        <i class="fa-solid fa-chevron-right text-xs"></i>
      </button>

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  pagination: {
    type:     Object,
    required: true,
  },
})

const emit = defineEmits(['page-change'])

// Plage d'affichage : "16 - 30"
const from = computed(() =>
  ((props.pagination.currentPage - 1) * props.pagination.perPage) + 1
)

const to = computed(() =>
  Math.min(
    props.pagination.currentPage * props.pagination.perPage,
    props.pagination.total
  )
)

// Maximum 5 numeros de page centres sur la page active
const visiblePages = computed(() => {
  const current = props.pagination.currentPage
  const last    = props.pagination.lastPage
  const delta   = 2

  let start = Math.max(1, current - delta)
  let end   = Math.min(last, current + delta)

  if (current <= delta)        end   = Math.min(5, last)
  if (current > last - delta)  start = Math.max(1, last - 4)

  const pages = []
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

function changePage(page) {
  if (
    page >= 1 &&
    page <= props.pagination.lastPage &&
    page !== props.pagination.currentPage
  ) {
    emit('page-change', page)
  }
}
</script>