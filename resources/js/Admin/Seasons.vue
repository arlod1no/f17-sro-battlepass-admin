<template>
  <div class="seasons-management">
    <!-- Data Table -->
    <DataTable
      title="Seasons Management"
      :data="seasons"
      :columns="columns"
      :loading="loading"
      add-button-text="Add New Season"
      empty-message="No seasons found. Create your first season!"
      @add="showCreateForm = true"
      @edit="editSeason"
      @delete="deleteSeason"
    >
      <!-- Custom cell for description -->
      <template #cell-description="{ value }">
        <span :title="value" class="description-cell">
          {{ value ? (value.length > 50 ? value.substring(0, 50) + '...' : value) : '-' }}
        </span>
      </template>
    </DataTable>

    <!-- Create/Edit Form Modal -->
    <div v-if="showCreateForm || editingSeason" class="form-modal">
      <div class="form-container">
        <h3>{{ editingSeason ? 'Edit Season' : 'Create New Season' }}</h3>
        <form @submit.prevent="submitForm">
          <div class="form-group">
            <label>Name:</label>
            <input v-model="formData.name" type="text" required>
          </div>
          <div class="form-group">
            <label>Description:</label>
            <textarea v-model="formData.description" rows="3" placeholder="Optional description..."></textarea>
          </div>
          <div class="form-group">
            <label>Start Date:</label>
            <input v-model="formData.start_date" type="datetime-local" required>
          </div>
          <div class="form-group">
            <label>End Date:</label>
            <input v-model="formData.end_date" type="datetime-local" required>
          </div>
          <div class="form-group">
            <label>Active:</label>
            <div class="toggle-switch">
              <input
                v-model="formData.is_active"
                type="checkbox"
                :id="`active-toggle-${editingSeason?.id || 'new'}`"
              >
              <label
                :for="`active-toggle-${editingSeason?.id || 'new'}`"
                class="toggle-label"
              >
                <span class="toggle-slider"></span>
              </label>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-success" :disabled="submitting">
              {{ submitting ? 'Saving...' : (editingSeason ? 'Update' : 'Create') }}
            </button>
            <button type="button" @click="cancelForm" class="btn btn-secondary">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, getCurrentInstance } from 'vue'
import axios from 'axios'

// Get the app instance for accessing global properties
const { appContext } = getCurrentInstance()
const $toast = appContext.config.globalProperties.$toast

// Reactive data
const seasons = ref([])
const loading = ref(false)
const submitting = ref(false)
const showCreateForm = ref(false)
const editingSeason = ref(null)

const formData = reactive({
  name: '',
  description: '',
  start_date: '',
  end_date: '',
  is_active: false
})

const columns = ref([
  {
    key: 'id',
    label: 'ID',
    type: 'number',
    class: 'id-column'
  },
  {
    key: 'name',
    label: 'Name',
    type: 'text'
  },
  {
    key: 'description',
    label: 'Description',
    type: 'text'
  },
  {
    key: 'start_date',
    label: 'Start Date',
    type: 'datetime'
  },
  {
    key: 'end_date',
    label: 'End Date',
    type: 'datetime'
  },
  {
    key: 'is_active',
    label: 'Active',
    type: 'boolean',
    trueText: 'Active',
    falseText: 'Inactive'
  },
  {
    key: 'created_at',
    label: 'Created',
    type: 'datetime'
  }
])

// Methods
const loadSeasons = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/seasons')
    seasons.value = response.data
    console.log('Loaded seasons:', seasons.value)
  } catch (error) {
    console.error('Error loading seasons:', error)
    $toast?.error('Failed to load seasons')
  } finally {
    loading.value = false
  }
}

const submitForm = async () => {
  if (submitting.value) return

  submitting.value = true
  try {
    const payload = { ...formData }

    // Convert datetime-local to ISO string
    if (payload.start_date) {
      payload.start_date = new Date(payload.start_date).toISOString()
    }
    if (payload.end_date) {
      payload.end_date = new Date(payload.end_date).toISOString()
    }

    if (editingSeason.value) {
      await axios.put(`/api/seasons/${editingSeason.value.id}`, payload)
      $toast?.success('Season updated successfully!')
    } else {
      await axios.post('/api/seasons', payload)
      $toast?.success('Season created successfully!')
    }

    await loadSeasons()
    cancelForm()
  } catch (error) {
    console.error('Error saving season:', error)
    const message = error.response?.data?.message || 'Failed to save season'
    $toast?.error(message)
  } finally {
    submitting.value = false
  }
}

const editSeason = (season) => {
  editingSeason.value = season
  Object.assign(formData, {
    name: season.name,
    description: season.description || '',
    start_date: toDatetimeLocal(season.start_date),
    end_date: toDatetimeLocal(season.end_date),
    is_active: season.is_active
  })
  showCreateForm.value = false
}

const deleteSeason = async (season) => {
  if (confirm(`Are you sure you want to delete "${season.name}"? This action cannot be undone.`)) {
    try {
      await axios.delete(`/api/seasons/${season.id}`)
      $toast?.success('Season deleted successfully!')
      await loadSeasons()
    } catch (error) {
      console.error('Error deleting season:', error)
      const message = error.response?.data?.message || 'Failed to delete season'
      $toast?.error(message)
    }
  }
}

const cancelForm = () => {
  showCreateForm.value = false
  editingSeason.value = null
  Object.assign(formData, {
    name: '',
    description: '',
    start_date: '',
    end_date: '',
    is_active: false
  })
}

const toDatetimeLocal = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const tzOffset = date.getTimezoneOffset() * 60000
  const localISOTime = new Date(date.getTime() - tzOffset).toISOString()
  return localISOTime.slice(0, 16) // Remove seconds and timezone
}

// Lifecycle
onMounted(() => {
  loadSeasons()
})
</script>

<style scoped>
.seasons-management {
  max-width: 1400px;
  margin: 0 auto;
}

/* Form Modal Styles */
.form-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.form-container {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.form-container h3 {
  margin: 0 0 1.5rem 0;
  color: #2c3e50;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #495057;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.form-actions .btn {
  flex: 1;
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
}

/* Toggle Switch */
.toggle-switch {
  display: flex;
  align-items: center;
  margin-top: 0.5rem;
}

.toggle-switch input[type="checkbox"] {
  display: none;
}

.toggle-label {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
  background-color: #ccc;
  border-radius: 24px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.toggle-slider {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 20px;
  height: 20px;
  background-color: white;
  border-radius: 50%;
  transition: transform 0.3s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.toggle-switch input[type="checkbox"]:checked + .toggle-label {
  background-color: #2ecc71;
}

.toggle-switch input[type="checkbox"]:checked + .toggle-label .toggle-slider {
  transform: translateX(26px);
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

.btn:hover:not(:disabled) {
  opacity: 0.9;
  transform: translateY(-1px);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.btn-success { background-color: #2ecc71; color: white; }
.btn-secondary { background-color: #95a5a6; color: white; }

/* Custom column styles */
:deep(.id-column) {
  width: 80px;
  text-align: center;
}

.description-cell {
  cursor: help;
}

/* Responsive */
@media (max-width: 768px) {
  .form-container {
    width: 95%;
    padding: 1.5rem;
    margin: 1rem;
  }

  .form-actions {
    flex-direction: column;
  }
}
</style>
