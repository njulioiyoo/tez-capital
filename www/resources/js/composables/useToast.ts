import { reactive, nextTick } from 'vue'

interface ToastOptions {
  title: string
  description?: string
  variant?: 'default' | 'destructive'
  duration?: number
}

interface ToastItem extends ToastOptions {
  id: number
  visible: boolean
}

// Global toast state
const toastState = reactive<{
  toasts: ToastItem[]
  idCounter: number
}>({
  toasts: [],
  idCounter: 0
})

export const toast = (options: ToastOptions) => {
  const id = toastState.idCounter++
  
  const toastItem: ToastItem = {
    ...options,
    id,
    visible: true,
    duration: options.duration ?? 5000
  }
  
  toastState.toasts.push(toastItem)
  
  // Auto remove after duration
  if (toastItem.duration > 0) {
    setTimeout(() => {
      removeToast(id)
    }, toastItem.duration)
  }
  
  return id
}

export const removeToast = (id: number) => {
  const index = toastState.toasts.findIndex(t => t.id === id)
  if (index > -1) {
    toastState.toasts[index].visible = false
    // Remove from array after transition
    setTimeout(() => {
      const currentIndex = toastState.toasts.findIndex(t => t.id === id)
      if (currentIndex > -1) {
        toastState.toasts.splice(currentIndex, 1)
      }
    }, 300)
  }
}

export const clearToasts = () => {
  toastState.toasts.forEach(toast => {
    toast.visible = false
  })
  setTimeout(() => {
    toastState.toasts.splice(0)
  }, 300)
}

export const useToastState = () => toastState

export default toast