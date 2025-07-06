<template>
  <div class="user-battlepasses-management">
    <!-- Data Table -->
    <DataTable
      title="User Battle Passes Management"
      :data="userBattlePasses"
      :columns="columns"
      :loading="loading"
      add-button-text="Assign Battle Pass"
      empty-message="No user battle passes found. Assign your first battle pass!"
      @add="showCreateForm = true"
      @edit="editUserBattlePass"
      @delete="deleteUserBattlePass"
    >
      <!-- Custom cell for user -->
      <template #cell-user.StrUserID="{ item }">
        <div class="user-info">
          <span v-if="item.user" class="user-name">
            {{ item.user.StrUserID }}
          </span>
          <span v-else class="user-unknown">Unknown User</span>
          <small v-if="item.user" class="user-email">
            {{ item.user.Email }}
          </small>
        </div>
      </template>

      <!-- Custom cell for battle pass -->
      <template #cell-battlePass.name="{ item }">
        <div class="battlepass-info">
          <span v-if="item.battlePass" class="battlepass-name">
            {{ item.battlePass.name }}
          </span>
          <span v-else class="battlepass-unknown">Unknown Battle Pass</span>
          <small v-if="item.battlePass && item.battlePass.season" class="season-subtitle">
            {{ item.battlePass.season.name }}
          </small>
        </div>
      </template>

      <!-- Custom cell for progress -->
      <template #cell-progress="{ item }">
        <div class="progress-container">
          <div class="progress-bar">
            <div
              class="progress-fill"
              :style="{ width: getProgressPercentage(item) + '%' }"
            ></div>
          </div>
          <span class="progress-text">
            {{ item.level }} / {{ item.total_levels }}
          </span>
        </div>
      </template>

      <!-- Custom actions -->
      <template #actions="{ item }">
        <button
          @click="levelUp(item)"
          :disabled="item.is_completed || item.level >= item.total_levels"
          class="btn btn-sm btn-info"
          title="Level Up"
        >
          ⬆️ Level Up
        </button>
        <button
          @click="editUserBattlePass(item)"
          class="btn btn-sm btn-warning"
        >
          Edit
        </button>
        <button
          @click="deleteUserBattlePass(item)"
          class="btn btn-sm btn-danger"
        >
          Delete
        </button>
      </template>
    </DataTable>

    <!-- Create/Edit Form Modal -->
    <div v-if="showCreateForm || editingUserBattlePass" class="form-modal">
      <div class="form-container">
        <h3>{{ editingUserBattlePass ? 'Edit User Battle Pass' : 'Assign New Battle Pass' }}</h3>
        <form @submit.prevent="submitForm">
          <div class="form-row">
            <div class="form-group">
              <label>User:</label>
              <select v-model="formData.user_id" required>
                <option value="">Select User</option>
                <option v-for="user in users" :key="user.JID" :value="user.JID">
                  {{ user.StrUserID }} ({{ user.Email }})
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Battle Pass:</label>
              <select v-model="formData.battle_pass_id" required>
                <option value="">Select Battle Pass</option>
                <option v-for="battlepass in battlepasses" :key="battlepass.id" :value="battlepass.id">
                  {{ battlepass.name }} ({{ battlepass.season?.name || 'Unknown Season' }})
                </option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Current Level:</label>
              <input v-model.number="formData.level" type="number" min="0" :max="formData.total_levels" required>
            </div>
            <div class="form-group">
              <label>Total Levels:</label>
              <input v-model.number="formData.total_levels" type="number" min="1" max="1000" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Experience:</label>
              <input v-model.number="formData.experience" type="number" min="0" required>
            </div>
            <div class="form-group">
              <label>Total Experience:</label>
              <input v-model.number="formData.total_experience" type="number" min="0" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Status:</label>
              <select v-model="formData.status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="completed">Completed</option>
                <option value="expired">Expired</option>
              </select>
            </div>
            <div class="form-group">
              <label>Type:</label>
              <select v-model="formData.type" required>
                <option value="standard">Standard</option>
                <option value="premium">Premium</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Started At:</label>
              <input v-model="formData.started_at" type="datetime-local">
            </div>
            <div class="form-group">
              <label>Ended At:</label>
              <input v-model="formData.ended_at" type="datetime-local">
            </div>
          </div>

          <div class="form-group">
            <label>Custom Name (Optional):</label>
            <input v-model="formData.name" type="text" placeholder="Override default name">
          </div>

          <div class="form-group">
            <label>Description (Optional):</label>
            <textarea v-model="formData.description" rows="3" placeholder="Custom description..."></textarea>
          </div>

          <div class="form-switches">
            <div class="switch-group">
              <label>Active:</label>
              <div class="toggle-switch">
                <input
                  v-model="formData.is_active"
                  type="checkbox"
                  :id="`active-toggle-${editingUserBattlePass?.id || 'new'}`"
                >
                <label
                  :for="`active-toggle-${editingUserBattlePass?.id || 'new'}`"
                  class="toggle-label"
                >
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>

            <div class="switch-group">
              <label>Completed:</label>
              <div class="toggle-switch">
                <input
                  v-model="formData.is_completed"
                  type="checkbox"
                  :id="`completed-toggle-${editingUserBattlePass?.id || 'new'}`"
                >
                <label
                  :for="`completed-toggle-${editingUserBattlePass?.id || 'new'}`"
                  class="toggle-label"
                >
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>

            <div class="switch-group">
              <label>Premium:</label>
              <div class="toggle-switch">
                <input
                  v-model="formData.is_premium"
                  type="checkbox"
                  :id="`premium-toggle-${editingUserBattlePass?.id || 'new'}`"
                >
                <label
                  :for="`premium-toggle-${editingUserBattlePass?.id || 'new'}`"
                  class="toggle-label"
                >
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>

            <div class="switch-group">
              <label>Visible:</label>
              <div class="toggle-switch">
                <input
                  v-model="formData.is_visible"
                  type="checkbox"
                  :id="`visible-toggle-${editingUserBattlePass?.id || 'new'}`"
                >
                <label
                  :for="`visible-toggle-${editingUserBattlePass?.id || 'new'}`"
                  class="toggle-label"
                >
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-success" :disabled="submitting">
              {{ submitting ? 'Saving...' : (editingUserBattlePass ? 'Update' : 'Assign') }}
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
const userBattlePasses = ref([])
const users = ref([])
const battlepasses = ref([])
const loading = ref(false)
const submitting = ref(false)
const showCreateForm = ref(false)
const editingUserBattlePass = ref(null)

const formData = reactive({
  user_id: '',
  battle_pass_id: '',
  level: 0,
  experience: 0,
  is_active: true,
  is_completed: false,
  status: 'active',
  type: 'standard',
  name: '',
  description: '',
  total_levels: 100,
  total_experience: 0,
  is_claimed: false,
  is_visible: true,
  is_premium: false,
  is_active_for_user: true,
  started_at: '',
  ended_at: ''
})

const columns = ref([
  {
    key: 'id',
    label: 'ID',
    type: 'number',
    class: 'id-column'
  },
  {
    key: 'user.StrUserID',
    label: 'User',
    type: 'text'
  },
  {
    key: 'battlePass.name',
    label: 'Battle Pass',
    type: 'text'
  },
  {
    key: 'progress',
    label: 'Progress',
    type: 'custom'
  },
  {
    key: 'experience',
    label: 'XP',
    type: 'number'
  },
  {
    key: 'status',
    label: 'Status',
    type: 'badge',
    badgeMap: {
      'active': 'success',
      'inactive': 'secondary',
      'completed': 'info',
      'expired': 'danger'
    }
  },
  {
    key: 'type',
    label: 'Type',
    type: 'badge',
    badgeMap: {
      'standard': 'secondary',
      'premium': 'warning'
    }
  },
  {
    key: 'is_completed',
    label: 'Completed',
    type: 'boolean',
    trueText: 'Yes',
    falseText: 'No'
  },
  {
    key: 'started_at',
    label: 'Started',
    type: 'datetime'
  }
])

// Methods
const loadUserBattlePasses = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/user-battle-passes')
    userBattlePasses.value = response.data
    console.log('Loaded user battle passes:', userBattlePasses.value)
  } catch (error) {
    console.error('Error loading user battle passes:', error)
    $toast?.error('Failed to load user battle passes')
  } finally {
    loading.value = false
  }
}

const loadUsers = async () => {
  try {
    const response = await axios.get('/api/users')
    users.value = response.data
    console.log('Loaded users:', users.value)
  } catch (error) {
    console.error('Error loading users:', error)
    $toast?.error('Failed to load users')
  }
}

const loadBattlePasses = async () => {
  try {
    const response = await axios.get('/api/battlepasses')
    if (response.data.data) {
      battlepasses.value = response.data.data
    } else {
      battlepasses.value = response.data
    }
    console.log('Loaded battle passes:', battlepasses.value)
  } catch (error) {
    console.error('Error loading battle passes:', error)
    $toast?.error('Failed to load battle passes')
  }
}

const submitForm = async () => {
  if (submitting.value) return

  submitting.value = true
  try {
    const payload = { ...formData }

    // Convert datetime-local to ISO string
    if (payload.started_at) {
      payload.started_at = new Date(payload.started_at).toISOString()
    }
    if (payload.ended_at) {
      payload.ended_at = new Date(payload.ended_at).toISOString()
    }

    console.log('Submitting form data:', payload)

    if (editingUserBattlePass.value) {
      await axios.put(`/api/user-battle-passes/${editingUserBattlePass.value.id}`, payload)
      $toast?.success('User battle pass updated successfully!')
    } else {
      await axios.post('/api/user-battle-passes', payload)
      $toast?.success('Battle pass assigned successfully!')
    }

    await loadUserBattlePasses()
    cancelForm()
  } catch (error) {
    console.error('Error saving user battle pass:', error)
    const message = error.response?.data?.message || 'Failed to save user battle pass'
    $toast?.error(message)
  } finally {
    submitting.value = false
  }
}

const editUserBattlePass = (userBattlePass) => {
  editingUserBattlePass.value = userBattlePass
  Object.assign(formData, {
    user_id: userBattlePass.jid,
    battle_pass_id: userBattlePass.battle_pass_id,
    level: userBattlePass.level,
    experience: userBattlePass.experience,
    is_active: userBattlePass.is_active,
    is_completed: userBattlePass.is_completed,
    status: userBattlePass.status,
    type: userBattlePass.type,
    name: userBattlePass.name || '',
    description: userBattlePass.description || '',
    total_levels: userBattlePass.total_levels,
    total_experience: userBattlePass.total_experience,
    is_claimed: userBattlePass.is_claimed,
    is_visible: userBattlePass.is_visible,
    is_premium: userBattlePass.is_premium,
    is_active_for_user: userBattlePass.is_active_for_user,
    started_at: toDatetimeLocal(userBattlePass.started_at),
    ended_at: toDatetimeLocal(userBattlePass.ended_at)
  })
  showCreateForm.value = false
}

const deleteUserBattlePass = async (userBattlePass) => {
  const userName = userBattlePass.user?.name || 'Unknown User'
  const battlePassName = userBattlePass.battlePass?.name || 'Unknown Battle Pass'

  if (confirm(`Are you sure you want to remove "${battlePassName}" from "${userName}"? This action cannot be undone.`)) {
    try {
      await axios.delete(`/api/user-battle-passes/${userBattlePass.id}`)
      $toast?.success('User battle pass removed successfully!')
      await loadUserBattlePasses()
    } catch (error) {
      console.error('Error deleting user battle pass:', error)
      const message = error.response?.data?.message || 'Failed to delete user battle pass'
      $toast?.error(message)
    }
  }
}

const levelUp = async (userBattlePass) => {
  if (userBattlePass.is_completed || userBattlePass.level >= userBattlePass.total_levels) {
    $toast?.warning('Battle pass is already at max level!')
    return
  }

  try {
    const response = await axios.post(`/api/user-battle-passes/${userBattlePass.id}/level-up`)
    $toast?.success(response.data.message)
    await loadUserBattlePasses()
  } catch (error) {
    console.error('Error leveling up:', error)
    const message = error.response?.data?.message || 'Failed to level up'
    $toast?.error(message)
  }
}

const cancelForm = () => {
  showCreateForm.value = false
  editingUserBattlePass.value = null
  Object.assign(formData, {
    user_id: '',
    battle_pass_id: '',
    level: 0,
    experience: 0,
    is_active: true,
    is_completed: false,
    status: 'active',
    type: 'standard',
    name: '',
    description: '',
    total_levels: 100,
    total_experience: 0,
    is_claimed: false,
    is_visible: true,
    is_premium: false,
    is_active_for_user: true,
    started_at: '',
    ended_at: ''
  })
}

const getProgressPercentage = (item) => {
  if (item.total_levels === 0) return 0
  return Math.min((item.level / item.total_levels) * 100, 100)
}

const toDatetimeLocal = (dateString) => {
  if (!dateString) return ''
  try {
    const date = new Date(dateString)
    const tzOffset = date.getTimezoneOffset() * 60000
    const localISOTime = new Date(date.getTime() - tzOffset).toISOString()
    return localISOTime.slice(0, 16)
  } catch (error) {
    return ''
  }
}

// Lifecycle
onMounted(async () => {
  await loadUsers()
  await loadBattlePasses()
  await loadUserBattlePasses()
})
</script>

<style scoped>
.user-battlepasses-management {
  max-width: 1800px;
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
  max-width: 800px;
  max-height: 95vh;
  overflow-y: auto;
}

.form-container h3 {
  margin: 0 0 1.5rem 0;
  color: #2c3e50;
  text-align: center;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
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

.form-switches {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
  margin: 1.5rem 0;
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 6px;
}

.switch-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.switch-group label {
  font-size: 0.9rem;
  font-weight: 600;
  color: #495057;
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
  margin-right: 0.5rem;
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
.btn-info { background-color: #3498db; color: white; }
.btn-warning { background-color: #f39c12; color: white; }
.btn-danger { background-color: #e74c3c; color: white; }

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.8rem;
}

/* Custom styles */
:deep(.id-column) {
  width: 80px;
  text-align: center;
}

.user-info, .battlepass-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.user-name, .battlepass-name {
  color: #2c3e50;
  font-weight: 500;
}

.user-unknown, .battlepass-unknown {
  color: #e74c3c;
  font-style: italic;
}

.user-email, .season-subtitle {
  color: #6c757d;
  font-size: 0.8rem;
}

.progress-container {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  min-width: 120px;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background-color: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3498db 0%, #2ecc71 100%);
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 0.8rem;
  color: #495057;
  text-align: center;
}

/* Responsive */
@media (max-width: 1200px) {
  .form-row {
    grid-template-columns: 1fr;
  }

  .form-switches {
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  }
}

@media (max-width: 768px) {
  .form-container {
    width: 95%;
    padding: 1.5rem;
    margin: 1rem;
  }

  .form-actions {
    flex-direction: column;
  }

  .form-switches {
    grid-template-columns: repeat(2, 1fr);
  }

  .user-info, .battlepass-info {
    font-size: 0.9rem;
  }

  .user-email, .season-subtitle {
    font-size: 0.75rem;
  }

  .progress-container {
    min-width: 80px;
  }
}
</style>
