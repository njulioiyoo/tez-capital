import { reactive } from 'vue'

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

// Global reactive state
export const toastState = reactive<{
  items: ToastItem[]
  counter: number
}>({
  items: [],
  counter: 0
})

export const toast = (options: ToastOptions) => {
  console.log('Toast called with:', options) // Debug log
  
  const id = toastState.counter++
  const duration = options.duration ?? 5000
  
  const item: ToastItem = {
    ...options,
    id,
    visible: true
  }
  
  // Add to beginning of array (stack from bottom)
  toastState.items.unshift(item)
  console.log('Toast added, current items:', toastState.items.length) // Debug log
  
  // Auto remove
  if (duration > 0) {
    setTimeout(() => {
      removeToast(id)
    }, duration)
  }
  
  return id
}

export const removeToast = (id: number) => {
  console.log('Removing toast:', id) // Debug log
  const index = toastState.items.findIndex(item => item.id === id)
  if (index !== -1) {
    toastState.items.splice(index, 1)
    console.log('Toast removed, remaining:', toastState.items.length) // Debug log
  }
}