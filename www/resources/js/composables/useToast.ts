import { ref, createApp, type App } from 'vue'
import Toast from '@/components/ui/toast/Toast.vue'

interface ToastOptions {
  title: string
  description?: string
  variant?: 'default' | 'destructive'
  duration?: number
}

class ToastManager {
  private toasts: Map<number, App> = new Map()
  private idCounter = 0

  show(options: ToastOptions) {
    const id = this.idCounter++
    
    // Create container div
    const container = document.createElement('div')
    document.body.appendChild(container)
    
    // Create Vue app instance
    const app = createApp(Toast, {
      ...options,
      onClose: () => {
        this.remove(id)
      }
    })
    
    // Mount and store
    app.mount(container)
    this.toasts.set(id, app)
    
    return id
  }
  
  private remove(id: number) {
    const app = this.toasts.get(id)
    if (app) {
      const container = app._container as HTMLElement
      if (container?.parentNode) {
        container.parentNode.removeChild(container)
      }
      app.unmount()
      this.toasts.delete(id)
    }
  }
  
  clear() {
    this.toasts.forEach((_, id) => this.remove(id))
  }
}

const toastManager = new ToastManager()

export const toast = (options: ToastOptions) => {
  return toastManager.show(options)
}

export const clearToasts = () => {
  toastManager.clear()
}

export default toast