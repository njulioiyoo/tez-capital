// Simple test script to debug toast implementation in browser console
console.log('Testing toast implementation...');

// Test 1: Basic DOM manipulation
console.log('Test 1: Basic DOM manipulation');
const testDiv = document.createElement('div');
testDiv.style.cssText = `
  position: fixed;
  top: 50px;
  right: 50px;
  background: red;
  color: white;
  padding: 20px;
  z-index: 9999;
`;
testDiv.textContent = 'Test Element';
document.body.appendChild(testDiv);
console.log('Test element created and added to body:', testDiv);

// Remove test element after 2 seconds
setTimeout(() => {
  testDiv.remove();
  console.log('Test element removed');
}, 2000);

// Test 2: Toast function simulation
console.log('Test 2: Toast function simulation');

const testToast = (options) => {
  console.log('Toast function called with:', options);
  
  const {
    title,
    description = '',
    variant = 'success',
    duration = 5000
  } = options;
  
  try {
    // Create container if not exists
    let container = document.getElementById('toast-container');
    if (!container) {
      console.log('Creating toast container');
      container = document.createElement('div');
      container.id = 'toast-container';
      container.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        pointer-events: none;
      `;
      document.body.appendChild(container);
      console.log('Container created and added to body:', container);
    } else {
      console.log('Container already exists:', container);
    }
    
    // Create toast
    const toastId = `toast-${Date.now()}`;
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.style.cssText = `
      pointer-events: auto;
      max-width: 350px;
      margin-bottom: 10px;
      padding: 16px;
      background: ${variant === 'error' ? '#fef2f2' : '#f0fdf4'};
      border: 1px solid ${variant === 'error' ? '#fecaca' : '#bbf7d0'};
      border-radius: 8px;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      transform: translateX(400px);
      transition: transform 0.3s ease-in-out;
      font-family: system-ui, -apple-system, sans-serif;
    `;
    
    // Create content
    const content = document.createElement('div');
    content.style.cssText = 'display: flex; align-items: flex-start;';
    
    // Icon
    const icon = document.createElement('div');
    icon.style.cssText = 'margin-right: 12px; margin-top: 2px;';
    icon.innerHTML = variant === 'error' ? '❌' : '✅';
    
    // Text content
    const textDiv = document.createElement('div');
    textDiv.style.cssText = 'flex: 1;';
    
    const titleEl = document.createElement('div');
    titleEl.style.cssText = `
      font-weight: 600;
      font-size: 14px;
      color: ${variant === 'error' ? '#991b1b' : '#166534'};
      margin-bottom: ${description ? '4px' : '0'};
    `;
    titleEl.textContent = title;
    
    textDiv.appendChild(titleEl);
    
    if (description) {
      const descEl = document.createElement('div');
      descEl.style.cssText = `
        font-size: 13px;
        color: ${variant === 'error' ? '#dc2626' : '#16a34a'};
      `;
      descEl.textContent = description;
      textDiv.appendChild(descEl);
    }
    
    // Close button
    const closeBtn = document.createElement('button');
    closeBtn.style.cssText = `
      background: none;
      border: none;
      font-size: 18px;
      cursor: pointer;
      color: ${variant === 'error' ? '#dc2626' : '#16a34a'};
      padding: 0;
      margin-left: 12px;
    `;
    closeBtn.innerHTML = '×';
    closeBtn.onclick = () => {
      console.log('Close button clicked');
      toast.style.transform = 'translateX(400px)';
      toast.style.opacity = '0';
      setTimeout(() => {
        toast.remove();
        console.log('Toast removed');
      }, 300);
    };
    
    // Assemble
    content.appendChild(icon);
    content.appendChild(textDiv);
    content.appendChild(closeBtn);
    toast.appendChild(content);
    
    // Add to container
    container.appendChild(toast);
    console.log('Toast element added to DOM:', toastId);
    console.log('Toast element:', toast);
    console.log('Container children count:', container.children.length);
    
    // Check if elements are actually in DOM
    const domContainer = document.getElementById('toast-container');
    const domToast = document.getElementById(toastId);
    console.log('Container in DOM:', domContainer);
    console.log('Toast in DOM:', domToast);
    
    // Animate in
    setTimeout(() => {
      console.log('Animating toast in...');
      toast.style.transform = 'translateX(0px)';
      console.log('Toast animated in, current transform:', toast.style.transform);
    }, 50);
    
    // Auto remove
    if (duration > 0) {
      setTimeout(() => {
        console.log('Auto removing toast:', toastId);
        toast.style.transform = 'translateX(400px)';
        toast.style.opacity = '0';
        setTimeout(() => {
          toast.remove();
          console.log('Toast auto-removed');
        }, 300);
      }, duration);
    }
    
    return toastId;
    
  } catch (error) {
    console.error('Error creating toast:', error);
    alert(`${variant === 'error' ? 'Error' : 'Success'}: ${title}${description ? ' - ' + description : ''}`);
  }
};

// Test the function
console.log('Testing success toast...');
testToast({
  title: 'Test Success',
  description: 'This is a test success toast',
  variant: 'success'
});

setTimeout(() => {
  console.log('Testing error toast...');
  testToast({
    title: 'Test Error',
    description: 'This is a test error toast',
    variant: 'error'
  });
}, 1000);