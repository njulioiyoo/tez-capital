interface ToastOptions {
  title: string
  description?: string
  variant?: 'success' | 'error'
  duration?: number
}

let toastCounter = 0

export const toast = (options: ToastOptions) => {
  console.log('=== TOAST FUNCTION CALLED ===')
  console.log('Options:', options)
  console.log('Document ready state:', document.readyState)
  console.log('Body exists:', !!document.body)
  
  const {
    title,
    description = '',
    variant = 'success',
    duration = 5000
  } = options
  
  console.log('Parsed options:', { title, description, variant, duration })
  
  try {
    // Create container if not exists
    let container = document.getElementById('toast-container')
    if (!container) {
      console.log('Creating toast container')
      container = document.createElement('div')
      container.id = 'toast-container'
      container.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1055;
        pointer-events: none;
        width: auto;
        max-width: 350px;
      `
      document.body.appendChild(container)
      console.log('Container created with styles:', container.style.cssText)
      console.log('Container position in DOM:', container.getBoundingClientRect())
    }
    
    // Create toast
    const toastId = `toast-${toastCounter++}`
    const toast = document.createElement('div')
    toast.id = toastId
    toast.className = 'toast'
    toast.setAttribute('role', 'alert')
    toast.setAttribute('aria-live', 'assertive')
    toast.setAttribute('aria-atomic', 'true')
    toast.style.cssText = `
      pointer-events: auto;
      max-width: 350px;
      width: 350px;
      margin-bottom: 12px;
      background-color: rgba(255, 255, 255, 0.95);
      background-clip: padding-box;
      border: 1px solid rgba(0, 0, 0, 0.1);
      border-radius: 0.375rem;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
      opacity: 0;
      transform: translateX(100%);
      transition: all 0.15s linear;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      font-size: 14px;
      display: block;
    `
    console.log('Toast created with styles:', toast.style.cssText)
    
    // Create toast header
    const toastHeader = document.createElement('div')
    toastHeader.className = 'toast-header'
    toastHeader.style.cssText = `
      display: flex;
      align-items: center;
      padding: 0.5rem 0.75rem;
      color: #6c757d;
      background-color: rgba(255, 255, 255, 0.85);
      background-clip: padding-box;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      border-top-left-radius: calc(0.375rem - 1px);
      border-top-right-radius: calc(0.375rem - 1px);
    `
    
    // Icon
    const icon = document.createElement('div')
    icon.style.cssText = `
      width: 20px;
      height: 20px;
      margin-right: 0.5rem;
      background-color: ${variant === 'error' ? '#dc3545' : '#198754'};
      border-radius: 0.25rem;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      color: white;
    `
    icon.innerHTML = variant === 'error' ? '×' : '✓'
    
    // Title
    const titleEl = document.createElement('strong')
    titleEl.className = 'me-auto'
    titleEl.style.cssText = `
      font-weight: 600;
      color: #212529;
      margin-right: auto;
    `
    titleEl.textContent = title
    
    // Time
    const timeEl = document.createElement('small')
    timeEl.style.cssText = 'color: #6c757d; margin-right: 0.5rem;'
    timeEl.textContent = 'now'
    
    // Close button
    const closeBtn = document.createElement('button')
    closeBtn.type = 'button'
    closeBtn.className = 'btn-close'
    closeBtn.setAttribute('aria-label', 'Close')
    closeBtn.style.cssText = `
      box-sizing: content-box;
      width: 1em;
      height: 1em;
      padding: 0.25em 0.25em;
      color: #000;
      background: transparent url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'><path d='m.235 1.027 1.027-.235 6.738 6.738 6.738-6.738 1.027.235-6.738 6.738 6.738 6.738-1.027.235-6.738-6.738-6.738 6.738-.235-1.027 6.738-6.738z'/></svg>") center/1em auto no-repeat;
      border: 0;
      border-radius: 0.375rem;
      opacity: 0.5;
      cursor: pointer;
    `
    closeBtn.onclick = () => removeToast(toastId)
    
    // Assemble header
    toastHeader.appendChild(icon)
    toastHeader.appendChild(titleEl)
    toastHeader.appendChild(timeEl)
    toastHeader.appendChild(closeBtn)
    
    // Create toast body if description exists
    if (description) {
      const toastBody = document.createElement('div')
      toastBody.className = 'toast-body'
      toastBody.style.cssText = `
        padding: 0.75rem;
        color: #212529;
      `
      toastBody.textContent = description
      toast.appendChild(toastHeader)
      toast.appendChild(toastBody)
    } else {
      toast.appendChild(toastHeader)
    }
    
    // Add to container
    container.appendChild(toast)
    console.log('Toast element added to DOM:', toastId)
    
    // Animate in
    setTimeout(() => {
      toast.style.opacity = '1'
      toast.style.transform = 'translateX(0)'
      console.log('Toast animated in - opacity:', toast.style.opacity, 'transform:', toast.style.transform)
      console.log('Toast position in DOM:', toast.getBoundingClientRect())
    }, 50)
    
    // Auto remove
    if (duration > 0) {
      setTimeout(() => {
        console.log('Auto removing toast:', toastId)
        removeToast(toastId)
      }, duration)
    }
    
    return toastId
    
  } catch (error) {
    console.error('ERROR creating toast:', error)
    console.error('Error stack:', error.stack)
    console.error('Options passed:', options)
    
    // Create a simple fallback toast without complex styling
    try {
      const fallbackContainer = document.createElement('div')
      fallbackContainer.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 999999;
        background: ${variant === 'error' ? '#ff4444' : '#44ff44'};
        color: white;
        padding: 16px;
        border-radius: 8px;
        max-width: 300px;
        font-family: system-ui;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      `
      fallbackContainer.innerHTML = `<strong>${title}</strong>${description ? `<div style="margin-top:4px;">${description}</div>` : ''}`
      document.body.appendChild(fallbackContainer)
      
      setTimeout(() => {
        fallbackContainer.remove()
      }, 5000)
      
      console.log('Fallback toast created')
    } catch (fallbackError) {
      console.error('Even fallback toast failed:', fallbackError)
    }
  }
}

export const removeToast = (toastId: string) => {
  console.log('Removing toast:', toastId)
  const toast = document.getElementById(toastId)
  if (toast) {
    toast.style.opacity = '0'
    toast.style.transform = 'translateX(100%)'
    
    setTimeout(() => {
      toast.remove()
      console.log('Toast removed from DOM')
      
      // Clean up container
      const container = document.getElementById('toast-container')
      if (container && container.children.length === 0) {
        container.remove()
        console.log('Container cleaned up')
      }
    }, 150)
  }
}