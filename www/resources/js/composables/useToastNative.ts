interface ToastOptions {
  title: string
  description?: string
  variant?: 'success' | 'error'
  duration?: number
}

let toastCounter = 0

export const toast = (options: ToastOptions) => {
  const {
    title,
    description,
    variant = 'success',
    duration = 5000
  } = options
  
  console.log('Creating toast:', options)
  
  // Create toast container if it doesn't exist
  let container = document.getElementById('toast-container')
  if (!container) {
    container = document.createElement('div')
    container.id = 'toast-container'
    container.className = 'fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none'
    document.body.appendChild(container)
  }
  
  // Create toast element
  const toastId = `toast-${toastCounter++}`
  const toastEl = document.createElement('div')
  toastEl.id = toastId
  toastEl.className = `
    pointer-events-auto max-w-sm w-full bg-white shadow-lg rounded-lg border transform transition-all duration-300 ease-in-out
    ${variant === 'error' ? 'border-red-200 bg-red-50' : 'border-green-200 bg-green-50'}
    translate-x-full opacity-0
  `.trim().replace(/\s+/g, ' ')
  
  // Create toast content
  toastEl.innerHTML = `
    <div class="flex items-start p-4">
      <div class="flex-shrink-0">
        ${variant === 'error' 
          ? `<svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
               <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
             </svg>`
          : `<svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
               <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
             </svg>`
        }
      </div>
      <div class="ml-3 w-0 flex-1">
        <p class="text-sm font-medium ${variant === 'error' ? 'text-red-800' : 'text-green-800'}">
          ${title}
        </p>
        ${description ? `<p class="mt-1 text-sm ${variant === 'error' ? 'text-red-600' : 'text-green-600'}">${description}</p>` : ''}
      </div>
      <div class="ml-4 flex-shrink-0 flex">
        <button 
          class="inline-flex ${variant === 'error' ? 'text-red-400 hover:text-red-600' : 'text-green-400 hover:text-green-600'} focus:outline-none focus:ring-2 focus:ring-offset-2 ${variant === 'error' ? 'focus:ring-red-500' : 'focus:ring-green-500'} rounded-md p-1"
          onclick="document.getElementById('${toastId}').remove()"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </button>
      </div>
    </div>
  `
  
  // Add to container
  container.appendChild(toastEl)
  
  // Trigger animation
  setTimeout(() => {
    toastEl.className = toastEl.className.replace('translate-x-full opacity-0', 'translate-x-0 opacity-100')
  }, 10)
  
  // Auto remove
  if (duration > 0) {
    setTimeout(() => {
      removeToast(toastId)
    }, duration)
  }
  
  console.log('Toast created with ID:', toastId)
  return toastId
}

export const removeToast = (toastId: string) => {
  const toastEl = document.getElementById(toastId)
  if (toastEl) {
    // Animate out
    toastEl.className = toastEl.className.replace('translate-x-0 opacity-100', 'translate-x-full opacity-0')
    
    // Remove after animation
    setTimeout(() => {
      toastEl.remove()
      
      // Clean up container if empty
      const container = document.getElementById('toast-container')
      if (container && container.children.length === 0) {
        container.remove()
      }
    }, 300)
  }
}