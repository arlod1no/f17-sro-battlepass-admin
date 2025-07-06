<template>
  <div class="battlepasses-management">
    <!-- Data Table -->
    <DataTable
      title="Battlepasses Management"
      :data="battlepasses"
      :columns="columns"
      :loading="loading"
      add-button-text="Add New Battlepass"
      empty-message="No battlepasses found. Create your first battlepass!"
      @add="showCreateForm = true"
      @edit="editBattlepass"
      @delete="deleteBattlepass"
    >
      <!-- Custom cell for season -->
      <template #cell-season.name="{ item }">
        <span v-if="item.season" class="season-name">
          {{ item.season.name }}
        </span>
        <span v-else class="season-unknown">Unknown Season</span>
      </template>
    </DataTable>

    <!-- Create/Edit Form Modal -->
    <div v-if="showCreateForm || editingBattlepass" class="form-modal">
      <div class="form-container">
        <h3>{{ editingBattlepass ? 'Edit Battlepass' : 'Create New Battlepass' }}</h3>
        <form @submit.prevent="submitForm">
          <div class="form-group">
            <label>Season:</label>
            <select v-model="formData.season_id" required>
              <option value="">Select Season</option>
              <option v-for="season in seasons" :key="season.id" :value="season.id">
                {{ season.name }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>Name:</label>
            <input v-model="formData.name" type="text" required>
          </div>
          <div class="form-group">
            <label>Type:</label>
            <select v-model="formData.type" required>
              <option value="free">Free</option>
              <option value="premium">Premium</option>
            </select>
          </div>
          <div class="form-group">
            <label>Active:</label>
            <div class="toggle-switch">
              <input
                v-model="formData.is_active"
                type="checkbox"
                :id="`active-toggle-${editingBattlepass?.id || 'new'}`"
              >
              <label
                :for="`active-toggle-${editingBattlepass?.id || 'new'}`"
                class="toggle-label"
              >
                <span class="toggle-slider"></span>
              </label>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-success" :disabled="submitting">
              {{ submitting ? 'Saving...' : (editingBattlepass ? 'Update' : 'Create') }}
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
const battlepasses = ref([])
const seasons = ref([])
const loading = ref(false)
const submitting = ref(false)
const showCreateForm = ref(false)
const editingBattlepass = ref(null)

const formData = reactive({
  season_id: '',
  name: '',
  type: 'free',
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
    key: 'season.name',
    label: 'Season',
    type: 'text'
  },
  {
    key: 'name',
    label: 'Name',
    type: 'text'
  },
  {
    key: 'type',
    label: 'Type',
    type: 'badge',
    badgeMap: {
      'free': 'success',
      'premium': 'warning'
    }
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
const loadBattlepasses = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/battlepasses')

    // Handle both debug response and normal response
    if (response.data.data) {
      battlepasses.value = response.data.data
      console.log('Debug info:', response.data.debug)
    } else {
      battlepasses.value = response.data
    }
    console.log('Loaded battlepasses:', battlepasses.value)
  } catch (error) {
    console.error('Error loading battlepasses:', error)
    $toast?.error('Failed to load battlepasses')
  } finally {
    loading.value = false
  }
}

const loadSeasons = async () => {
  try {
    const response = await axios.get('/api/seasons')
    seasons.value = response.data
    console.log('Loaded seasons:', seasons.value)
  } catch (error) {
    console.error('Error loading seasons:', error)
    $toast?.error('Failed to load seasons')
  }
}

const submitForm = async () => {
  if (submitting.value) return

  submitting.value = true
  try {
    console.log('Submitting form data:', formData)

    if (editingBattlepass.value) {
      await axios.put(`/api/battlepasses/${editingBattlepass.value.id}`, formData)
      $toast?.success('Battlepass updated successfully!')
    } else {
      await axios.post('/api/battlepasses', formData)
      $toast?.success('Battlepass created successfully!')
    }

    await loadBattlepasses()
    cancelForm()
  } catch (error) {
    console.error('Error saving battlepass:', error)
    const message = error.response?.data?.message || 'Failed to save battlepass'
    $toast?.error(message)
  } finally {
    submitting.value = false
  }
}

const editBattlepass = (battlepass) => {
  editingBattlepass.value = battlepass
  Object.assign(formData, {
    season_id: battlepass.season_id,
    name: battlepass.name,
    type: battlepass.type,
    is_active: battlepass.is_active
  })
  showCreateForm.value = false
}

const deleteBattlepass = async (battlepass) => {
  if (confirm(`Are you sure you want to delete "${battlepass.name}"? This action cannot be undone.`)) {
    try {
      await axios.delete(`/api/battlepasses/${battlepass.id}`)
      $toast?.success('Battlepass deleted successfully!')
      await loadBattlepasses()
    } catch (error) {
      console.error('Error deleting battlepass:', error)
      const message = error.response?.data?.message || 'Failed to delete battlepass'
      $toast?.error(message)
    }
  }
}

const cancelForm = () => {
  showCreateForm.value = false
  editingBattlepass.value = null
  Object.assign(formData, {
    season_id: '',
    name: '',
    type: 'free',
    is_active: false
  })
}

// Lifecycle
onMounted(async () => {
  await loadSeasons()
  await loadBattlepasses()
})
</script>

<style scoped>
.battlepasses-management {
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
.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group select:focus {
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

/* Custom styles */
:deep(.id-column) {
  width: 80px;
  text-align: center;
}

.season-name {
  color: #2c3e50;
  font-weight: 500;
}

.season-unknown {
  color: #e74c3c;
  font-style: italic;
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
