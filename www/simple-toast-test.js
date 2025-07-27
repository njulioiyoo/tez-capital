// Test toast langsung tanpa framework
console.log('=== SIMPLE TOAST TEST ===');

// Buat toast container
const container = document.createElement('div');
container.id = 'simple-toast-container';
container.style.cssText = `
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 999999;
  width: 350px;
`;
document.body.appendChild(container);

// Buat toast element
const toast = document.createElement('div');
toast.style.cssText = `
  margin-bottom: 12px;
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  opacity: 0;
  transform: translateX(100%);
  transition: all 0.3s ease;
`;

// Header
const header = document.createElement('div');
header.style.cssText = `
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid #eee;
`;

// Icon
const icon = document.createElement('div');
icon.style.cssText = `
  width: 20px;
  height: 20px;
  background: #28a745;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  margin-right: 8px;
`;
icon.textContent = '✓';

// Title
const title = document.createElement('strong');
title.style.cssText = 'color: #333; flex: 1;';
title.textContent = 'Test Toast';

// Time
const time = document.createElement('small');
time.style.cssText = 'color: #666; margin-right: 8px;';
time.textContent = 'now';

// Close button
const closeBtn = document.createElement('button');
closeBtn.style.cssText = `
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: #666;
  padding: 0;
  width: 20px;
  height: 20px;
`;
closeBtn.innerHTML = '×';
closeBtn.onclick = () => {
  toast.style.opacity = '0';
  toast.style.transform = 'translateX(100%)';
  setTimeout(() => toast.remove(), 300);
};

// Body
const body = document.createElement('div');
body.style.cssText = 'padding: 12px 16px; color: #555;';
body.textContent = 'Ini adalah test toast sederhana untuk memastikan DOM manipulation berfungsi dengan benar.';

// Assemble
header.appendChild(icon);
header.appendChild(title);
header.appendChild(time);
header.appendChild(closeBtn);
toast.appendChild(header);
toast.appendChild(body);
container.appendChild(toast);

console.log('Toast element created and added to DOM');
console.log('Container position:', container.getBoundingClientRect());
console.log('Toast position:', toast.getBoundingClientRect());

// Animate in
setTimeout(() => {
  toast.style.opacity = '1';
  toast.style.transform = 'translateX(0)';
  console.log('Toast animated in');
}, 100);

// Auto remove after 5 seconds
setTimeout(() => {
  closeBtn.click();
  console.log('Toast auto-removed');
}, 5000);

console.log('Test completed. Toast should be visible in top-right corner.');