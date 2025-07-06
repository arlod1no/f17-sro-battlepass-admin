/**
 * Utility functions for formatting data in the application
 */

/**
 * Format datetime to HH:MM MM/DD/YYYY format
 * @param {string|Date} value - The datetime value to format
 * @returns {string} Formatted datetime string
 */
export function formatDateTime(value) {
  if (!value) return '-';

  try {
    const date = new Date(value);
    if (isNaN(date.getTime())) return value;

    const time = date.toLocaleTimeString('en-US', {
      hour12: false,
      hour: '2-digit',
      minute: '2-digit'
    });

    const dateStr = date.toLocaleDateString('en-US', {
      month: '2-digit',
      day: '2-digit',
      year: 'numeric'
    });

    return `${time} ${dateStr}`;
  } catch (error) {
    return value;
  }
}

/**
 * Format date to MM/DD/YYYY format
 * @param {string|Date} value - The date value to format
 * @returns {string} Formatted date string
 */
export function formatDate(value) {
  if (!value) return '-';

  try {
    const date = new Date(value);
    if (isNaN(date.getTime())) return value;

    return date.toLocaleDateString('en-US', {
      month: '2-digit',
      day: '2-digit',
      year: 'numeric'
    });
  } catch (error) {
    return value;
  }
}

/**
 * Format time to HH:MM format
 * @param {string|Date} value - The time value to format
 * @returns {string} Formatted time string
 */
export function formatTime(value) {
  if (!value) return '-';

  try {
    const date = new Date(value);
    if (isNaN(date.getTime())) return value;

    return date.toLocaleTimeString('en-US', {
      hour12: false,
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch (error) {
    return value;
  }
}

/**
 * Format currency value
 * @param {number} value - The currency value to format
 * @returns {string} Formatted currency string
 */
export function formatCurrency(value) {
  if (value === null || value === undefined) return '-';
  if (value === 0) return 'Free';
  return `$${Number(value).toFixed(2)}`;
}

/**
 * Format number with thousands separator
 * @param {number} value - The number to format
 * @returns {string} Formatted number string
 */
export function formatNumber(value) {
  if (value === null || value === undefined) return '-';
  return Number(value).toLocaleString();
}

/**
 * Convert datetime string to datetime-local input format
 * @param {string} dateString - ISO datetime string
 * @returns {string} Formatted datetime-local string
 */
export function toDatetimeLocal(dateString) {
  if (!dateString) return '';

  try {
    const date = new Date(dateString);
    const tzOffset = date.getTimezoneOffset() * 60000;
    const localISOTime = new Date(date.getTime() - tzOffset).toISOString();
    return localISOTime.slice(0, 16); // Remove seconds and timezone
  } catch (error) {
    return '';
  }
}

/**
 * Convert datetime-local input to ISO string
 * @param {string} datetimeLocal - datetime-local input value
 * @returns {string} ISO datetime string
 */
export function fromDatetimeLocal(datetimeLocal) {
  if (!datetimeLocal) return '';

  try {
    return new Date(datetimeLocal).toISOString();
  } catch (error) {
    return '';
  }
}

/**
 * Get nested object value using dot notation
 * @param {Object} obj - The object to get value from
 * @param {string} path - The dot notation path (e.g., 'user.profile.name')
 * @returns {any} The value at the path
 */
export function getNestedValue(obj, path) {
  return path.split('.').reduce((current, prop) => current?.[prop], obj);
}

/**
 * Truncate text with ellipsis
 * @param {string} text - The text to truncate
 * @param {number} maxLength - Maximum length before truncation
 * @returns {string} Truncated text
 */
export function truncateText(text, maxLength = 50) {
  if (!text) return '-';
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + '...';
}

/**
 * Get CSS class for boolean values
 * @param {boolean} value - The boolean value
 * @returns {string} CSS class name
 */
export function getBooleanClass(value) {
  return value ? 'status-active' : 'status-inactive';
}

/**
 * Get badge CSS class based on value and mapping
 * @param {string} value - The value to map
 * @param {Object} badgeMap - Mapping of values to badge types
 * @returns {string} CSS class name
 */
export function getBadgeClass(value, badgeMap = {}) {
  if (!value) return 'badge';
  const type = badgeMap[value] || 'default';
  return `badge badge-${type}`;
}

/**
 * Validate email format
 * @param {string} email - Email to validate
 * @returns {boolean} True if valid email
 */
export function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

/**
 * Debounce function for search inputs
 * @param {Function} func - Function to debounce
 * @param {number} delay - Delay in milliseconds
 * @returns {Function} Debounced function
 */
export function debounce(func, delay = 300) {
  let timeoutId;
  return function (...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => func.apply(this, args), delay);
  };
}

/**
 * Generate random ID
 * @returns {string} Random ID
 */
export function generateId() {
  return Math.random().toString(36).substr(2, 9);
}

/**
 * Copy text to clipboard
 * @param {string} text - Text to copy
 * @returns {Promise<boolean>} Success status
 */
export async function copyToClipboard(text) {
  try {
    if (navigator.clipboard) {
      await navigator.clipboard.writeText(text);
      return true;
    } else {
      // Fallback for older browsers
      const textArea = document.createElement('textarea');
      textArea.value = text;
      document.body.appendChild(textArea);
      textArea.focus();
      textArea.select();
      const successful = document.execCommand('copy');
      document.body.removeChild(textArea);
      return successful;
    }
  } catch (error) {
    console.error('Failed to copy text:', error);
    return false;
  }
}
