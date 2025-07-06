import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import router from "./router";

// Import DataTable component globally
import DataTable from './Admin/DataTable.vue';

const app = createApp(App);

// Register DataTable globally so all components can use it
app.component('DataTable', DataTable);

// Simple toast notification system (optional)
app.config.globalProperties.$toast = {
  success: (message) => {
    console.log('✅ Success:', message);
    // You can replace this with a proper toast library like vue-toastification
    // For now, using a simple styled alert
    showToast(message, 'success');
  },
  error: (message) => {
    console.error('❌ Error:', message);
    showToast(message, 'error');
  },
  info: (message) => {
    console.log('ℹ️ Info:', message);
    showToast(message, 'info');
  }
};

// Simple toast implementation
function showToast(message, type = 'info') {
  // Create toast element
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.textContent = message;

  // Add styles
  const styles = {
    position: 'fixed',
    top: '20px',
    right: '20px',
    padding: '12px 20px',
    borderRadius: '6px',
    color: 'white',
    fontWeight: '500',
    fontSize: '14px',
    zIndex: '9999',
    boxShadow: '0 4px 12px rgba(0,0,0,0.2)',
    transform: 'translateX(100%)',
    transition: 'transform 0.3s ease',
    maxWidth: '400px',
    wordWrap: 'break-word'
  };

  Object.assign(toast.style, styles);

  // Set background color based on type
  const colors = {
    success: '#2ecc71',
    error: '#e74c3c',
    info: '#3498db',
    warning: '#f39c12'
  };

  toast.style.backgroundColor = colors[type] || colors.info;

  // Add to DOM
  document.body.appendChild(toast);

  // Animate in
  setTimeout(() => {
    toast.style.transform = 'translateX(0)';
  }, 100);

  // Remove after 3 seconds
  setTimeout(() => {
    toast.style.transform = 'translateX(100%)';
    setTimeout(() => {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 300);
  }, 3000);
}

app.use(router).mount('#app');
