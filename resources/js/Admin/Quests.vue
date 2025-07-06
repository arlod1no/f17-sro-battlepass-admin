<template>
  <div class="quests-management">
    <!-- Data Table -->
    <DataTable
      title="Quests Management"
      :data="quests"
      :columns="columns"
      :loading="loading"
      add-button-text="Add New Quest"
      empty-message="No quests found. Create your first quest!"
      @add="showCreateForm = true"
      @edit="editQuest"
      @delete="deleteQuest"
    >
      <!-- Custom cell for battlepass -->
      <template #cell-battlePass.name="{ item }">
        <span v-if="item.battlePass" class="battlepass-name">
          {{ item.battlePass.name }}
        </span>
        <span v-else class="battlepass-unknown">Unknown Battlepass</span>
      </template>

      <!-- Custom cell for description -->
      <template #cell-description="{ value }">
        <span :title="value" class="description-cell">
          {{ value ? (value.length > 40 ? value.substring(0, 40) + '...' : value) : '-' }}
        </span>
      </template>
    </DataTable>

    <!-- Create/Edit Form Modal -->
    <div v-if="showCreateForm || editingQuest" class="form-modal">
      <div class="form-container">
        <h3>{{ editingQuest ? 'Edit Quest' : 'Create New Quest' }}</h3>
        <form @submit.prevent="submitForm">
          <div class="form-group">
            <label>Battlepass:</label>
            <select v-model="formData.battle_pass_id" required>
              <option value="">Select Battlepass</option>
              <option v-for="battlepass in battlepasses" :key="battlepass.id" :value="battlepass.id">
                {{ battlepass.name }} ({{ battlepass.season?.name || 'Unknown Season' }})
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>Name:</label>
            <input v-model="formData.name" type="text" required>
          </div>
          <div class="form-group">
            <label>Description:</label>
            <textarea v-model="formData.description" rows="3" placeholder="Quest description..."></textarea>
          </div>
          <div class="form-group">
            <label>Type:</label>
            <select v-model="formData.type" required>
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="seasonal">Seasonal</option>
              <option value="special">Special</option>
            </select>
          </div>
          <div class="form-group">
            <label>Required Action:</label>
            <input v-model="formData.required_action" type="text" placeholder="e.g., kill_enemies, collect_items">
          </div>
          <div class="form-group">
            <label>Required Count:</label>
            <input v-model.number="formData.required_count" type="number" min="1" required>
          </div>
          <div class="form-group">
            <label>Active:</label>
            <div class="toggle-switch">
              <input
                v-model="formData.is_active"
                type="checkbox"
                :id="`active-toggle-${editingQuest?.id || 'new'}`"
              >
              <label
                :for="`active-toggle-${editingQuest?.id || 'new'}`"
                class="toggle-label"
              >
                <span class="toggle-slider"></span>
              </label>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-success" :disabled="submitting">
              {{ submitting ? 'Saving...' : (editingQuest ? 'Update' : 'Create') }}
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
const quests = ref([])
const battlepasses = ref([])
const loading = ref(false)
const submitting = ref(false)
const showCreateForm = ref(false)
const editingQuest = ref(null)

const formData = reactive({
  battle_pass_id: '',
  name: '',
  description: '',
  type: 'daily',
  required_action: '',
  required_count: 1,
  is_active: true
})

const columns = ref([
  {
    key: 'id',
    label: 'ID',
    type: 'number',
    class: 'id-column'
  },
  {
    key: 'battlePass.name',
    label: 'Battlepass',
    type: 'text'
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
    key: 'type',
    label: 'Type',
    type: 'badge',
    badgeMap: {
      'daily': 'info',
      'weekly': 'primary',
      'seasonal': 'warning',
      'special': 'danger'
    }
  },
  {
    key: 'required_action',
    label: 'Action',
    type: 'text'
  },
  {
    key: 'required_count',
    label: 'Count',
    type: 'number'
  },
  {
    key: 'is_active',
    label: 'Active',
    type: 'boolean',
    trueText: 'Active',
    falseText: 'Inactive'
  },
  {
    key: 'completed_at',
    label: 'Completed',
    type: 'datetime'
  }
])

// Methods
const loadQuests = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/quests')
    quests.value = response.data
    console.log('Loaded quests:', quests.value)
  } catch (error) {
    console.error('Error loading quests:', error)
    $toast?.error('Failed to load quests')
  } finally {
    loading.value = false
  }
}

const loadBattlepasses = async () => {
  try {
    const response = await axios.get('/api/battlepasses')

    // Handle both debug response and normal response
    if (response.data.data) {
      battlepasses.value = response.data.data
    } else {
      battlepasses.value = response.data
    }
    console.log('Loaded battlepasses:', battlepasses.value)
  } catch (error) {
    console.error('Error loading battlepasses:', error)
    $toast?.error('Failed to load battlepasses')
  }
}

const submitForm = async () => {
  if (submitting.value) return

  submitting.value = true
  try {
    if (editingQuest.value) {
      await axios.put(`/api/quests/${editingQuest.value.id}`, formData)
      $toast?.success('Quest updated successfully!')
    } else {
      await axios.post('/api/quests', formData)
      $toast?.success('Quest created successfully!')
    }

    await loadQuests()
    cancelForm()
  } catch (error) {
    console.error('Error saving quest:', error)
    const message = error.response?.data?.message || 'Failed to save quest'
    $toast?.error(message)
  } finally {
    submitting.value = false
  }
}

const editQuest = (quest) => {
  editingQuest.value = quest
  Object.assign(formData, {
    battle_pass_id: quest.battle_pass_id,
    name: quest.name,
    description: quest.description || '',
    type: quest.type,
    required_action: quest.required_action || '',
    required_count: quest.required_count,
    is_active: quest.is_active
  })
  showCreateForm.value = false
}

const deleteQuest = async (quest) => {
  if (confirm(`Are you sure you want to delete "${quest.name}"? This action cannot be undone.`)) {
    try {
      await axios.delete(`/api/quests/${quest.id}`)
      $toast?.success('Quest deleted successfully!')
      await loadQuests()
    } catch (error) {
      console.error('Error deleting quest:', error)
      const message = error.response?.data?.message || 'Failed to delete quest'
      $toast?.error(message)
    }
  }
}

const cancelForm = () => {
  showCreateForm.value = false
  editingQuest.value = null
  Object.assign(formData, {
    battle_pass_id: '',
    name: '',
    description: '',
    type: 'daily',
    required_action: '',
    required_count: 1,
    is_active: true
  })
}

// Lifecycle
onMounted(async () => {
  await loadBattlepasses()
  await loadQuests()
})
</script>

<style scoped>
.quests-management {
  max-width: 1600px;
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
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group textarea:focus,
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

.battlepass-name {
  color: #2c3e50;
  font-weight: 500;
}

.battlepass-unknown {
  color: #e74c3c;
  font-style: italic;
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
