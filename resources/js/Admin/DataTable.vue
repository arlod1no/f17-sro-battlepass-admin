<template>
  <div class="data-table-container">
    <!-- Table Header -->
    <div class="table-header">
      <h2>{{ title }}</h2>
      <button v-if="showAddButton" @click="$emit('add')" class="btn btn-primary">
        {{ addButtonText }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="data.length === 0" class="empty-state">
      <p>{{ emptyMessage }}</p>
    </div>

    <!-- Data Table -->
    <div v-else class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th v-for="column in columns" :key="column.key" :class="column.class">
              {{ column.label }}
            </th>
            <th v-if="showActions" class="actions-column">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in data" :key="getItemKey(item)">
            <td v-for="column in columns" :key="column.key" :class="column.class">
              <slot
                :name="`cell-${column.key}`"
                :item="item"
                :value="getColumnValue(item, column.key)"
                :column="column"
              >
                <span v-if="column.type === 'datetime'">
                  {{ formatDateTime(getColumnValue(item, column.key)) }}
                </span>
                <span v-else-if="column.type === 'date'">
                  {{ formatDate(getColumnValue(item, column.key)) }}
                </span>
                <span v-else-if="column.type === 'time'">
                  {{ formatTime(getColumnValue(item, column.key)) }}
                </span>
                <span v-else-if="column.type === 'boolean'">
                  <span :class="getBooleanClass(getColumnValue(item, column.key))">
                    {{ getColumnValue(item, column.key) ? (column.trueText || 'Yes') : (column.falseText || 'No') }}
                  </span>
                </span>
                <span v-else-if="column.type === 'badge'">
                  <span :class="getBadgeClass(getColumnValue(item, column.key), column.badgeMap)">
                    {{ getColumnValue(item, column.key) }}
                  </span>
                </span>
                <span v-else-if="column.type === 'currency'">
                  {{ formatCurrency(getColumnValue(item, column.key)) }}
                </span>
                <span v-else-if="column.type === 'number'">
                  {{ formatNumber(getColumnValue(item, column.key)) }}
                </span>
                <span v-else>
                  {{ getColumnValue(item, column.key) || '-' }}
                </span>
              </slot>
            </td>
            <td v-if="showActions" class="actions-cell">
              <slot name="actions" :item="item">
                <button
                  v-if="showEditAction"
                  @click="$emit('edit', item)"
                  class="btn btn-sm btn-warning"
                >
                  Edit
                </button>
                <button
                  v-if="showDeleteAction"
                  @click="$emit('delete', item)"
                  class="btn btn-sm btn-danger"
                >
                  Delete
                </button>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination (if needed) -->
    <div v-if="showPagination && totalPages > 1" class="pagination">
      <button
        @click="$emit('page-change', currentPage - 1)"
        :disabled="currentPage <= 1"
        class="btn btn-sm btn-secondary"
      >
        Previous
      </button>

      <span class="page-info">
        Page {{ currentPage }} of {{ totalPages }}
      </span>

      <button
        @click="$emit('page-change', currentPage + 1)"
        :disabled="currentPage >= totalPages"
        class="btn btn-sm btn-secondary"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

// Props
const props = defineProps({
  // Table configuration
  title: {
    type: String,
    default: 'Data Table'
  },
  data: {
    type: Array,
    required: true
  },
  columns: {
    type: Array,
    required: true
  },
  keyField: {
    type: String,
    default: 'id'
  },

  // Loading state
  loading: {
    type: Boolean,
    default: false
  },

  // Empty state
  emptyMessage: {
    type: String,
    default: 'No data available'
  },

  // Add button
  showAddButton: {
    type: Boolean,
    default: true
  },
  addButtonText: {
    type: String,
    default: 'Add New'
  },

  // Actions
  showActions: {
    type: Boolean,
    default: true
  },
  showEditAction: {
    type: Boolean,
    default: true
  },
  showDeleteAction: {
    type: Boolean,
    default: true
  },

  // Pagination
  showPagination: {
    type: Boolean,
    default: false
  },
  currentPage: {
    type: Number,
    default: 1
  },
  totalPages: {
    type: Number,
    default: 1
  }
})

// Emits
const emit = defineEmits(['add', 'edit', 'delete', 'page-change'])

// Methods
const getItemKey = (item) => {
  return item[props.keyField] || Math.random()
}

const getColumnValue = (item, key) => {
  // Support nested keys like 'user.name' or 'battlePass.season.name'
  return key.split('.').reduce((obj, prop) => obj?.[prop], item)
}

const formatDateTime = (value) => {
  if (!value) return '-'
  try {
    const date = new Date(value)
    if (isNaN(date.getTime())) return value

    const time = date.toLocaleTimeString('en-US', {
      hour12: false,
      hour: '2-digit',
      minute: '2-digit'
    })
    const dateStr = date.toLocaleDateString('en-US', {
      month: '2-digit',
      day: '2-digit',
      year: 'numeric'
    })

    return `${time} ${dateStr}`
  } catch (error) {
    return value
  }
}

const formatDate = (value) => {
  if (!value) return '-'
  try {
    const date = new Date(value)
    if (isNaN(date.getTime())) return value

    return date.toLocaleDateString('en-US', {
      month: '2-digit',
      day: '2-digit',
      year: 'numeric'
    })
  } catch (error) {
    return value
  }
}

const formatTime = (value) => {
  if (!value) return '-'
  try {
    const date = new Date(value)
    if (isNaN(date.getTime())) return value

    return date.toLocaleTimeString('en-US', {
      hour12: false,
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (error) {
    return value
  }
}

const formatCurrency = (value) => {
  if (value === null || value === undefined) return '-'
  if (value === 0) return 'Free'
  return `$${Number(value).toFixed(2)}`
}

const formatNumber = (value) => {
  if (value === null || value === undefined) return '-'
  return Number(value).toLocaleString()
}

const getBooleanClass = (value) => {
  return value ? 'status-active' : 'status-inactive'
}

const getBadgeClass = (value, badgeMap) => {
  if (!badgeMap || !value) return 'badge'
  const type = badgeMap[value] || 'default'
  return `badge badge-${type}`
}
</script>

<style scoped>
.data-table-container {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  overflow: hidden;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid #eee;
  background-color: #f8f9fa;
}

.table-header h2 {
  margin: 0;
  color: #2c3e50;
  font-size: 1.5rem;
  font-weight: 600;
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 3rem 2rem;
  color: #6c757d;
}

.spinner {
  border: 3px solid #f3f3f3;
  border-top: 3px solid #3498db;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.table-wrapper {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 600px;
}

.data-table th,
.data-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.data-table th {
  background-color: #f8f9fa;
  font-weight: 600;
  color: #495057;
  white-space: nowrap;
}

.data-table tbody tr:hover {
  background-color: #f8f9fa;
}

.actions-column,
.actions-cell {
  width: 150px;
  white-space: nowrap;
}

/* Button Styles */
.btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.2s;
}

.btn:hover {
  opacity: 0.9;
  transform: translateY(-1px);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.btn-primary { background-color: #3498db; color: white; }
.btn-success { background-color: #2ecc71; color: white; }
.btn-warning { background-color: #f39c12; color: white; }
.btn-danger { background-color: #e74c3c; color: white; }
.btn-secondary { background-color: #95a5a6; color: white; }

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.8rem;
  margin-right: 0.5rem;
}

/* Status Styles */
.status-active {
  color: #2ecc71;
  font-weight: 600;
}

.status-inactive {
  color: #e74c3c;
  font-weight: 600;
}

/* Badge Styles */
.badge {
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.badge-success { background-color: #d4edda; color: #155724; }
.badge-danger { background-color: #f8d7da; color: #721c24; }
.badge-warning { background-color: #fff3cd; color: #856404; }
.badge-info { background-color: #d1ecf1; color: #0c5460; }
.badge-primary { background-color: #d1ecf1; color: #004085; }
.badge-secondary { background-color: #e2e3e5; color: #383d41; }
.badge-default { background-color: #f8f9fa; color: #6c757d; }

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid #eee;
  background-color: #f8f9fa;
}

.page-info {
  color: #6c757d;
  font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
  .table-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .table-wrapper {
    overflow-x: scroll;
  }

  .data-table th,
  .data-table td {
    padding: 0.5rem;
    font-size: 0.9rem;
  }

  .btn-sm {
    padding: 0.2rem 0.4rem;
    font-size: 0.75rem;
  }
}
</style>
