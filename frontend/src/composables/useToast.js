import { reactive } from 'vue'

// État partagé entre tous les composants qui importent useToast
const toasts = reactive([])
let nextId = 1

const DURATION = {
  success: 4000,
  error:   6000,
  warning: 5000,
  info:    4000,
}

function push(type, message, title = null) {
  const id      = nextId++
  const toast   = { id, type, message, title, visible: true }

  toasts.push(toast)

  setTimeout(() => dismiss(id), DURATION[type] ?? 4000)

  return id
}

function dismiss(id) {
  const index = toasts.findIndex(t => t.id === id)
  if (index !== -1) toasts.splice(index, 1)
}

export function useToast() {
  return {
    toasts,
    success: (message, title = 'Succès')         => push('success', message, title),
    error:   (message, title = 'Erreur')          => push('error',   message, title),
    warning: (message, title = 'Attention')       => push('warning', message, title),
    info:    (message, title = null)              => push('info',    message, title),
    dismiss,
  }
}
