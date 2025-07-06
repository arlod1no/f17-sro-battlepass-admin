<template>
  <div class="rewards-management">
    <!-- Data Table -->
    <DataTable
      title="Rewards Management"
      :data="rewards"
      :columns="columns"
      :loading="loading"
      add-button-text="Add New Reward"
      empty-message="No rewards found. Create your first reward!"
      @add="showCreateForm = true"
      @edit="editReward"
      @delete="deleteReward"
    >
      <!-- Custom cell for quest -->
      <template #cell-quest.name="{ item }">
        <div class="quest-info">
          <span v-if="item.quest" class="quest-name">
            {{ item.quest.name }}
          </span>
          <span v-else class="quest-unknown">Unknown Quest</span>
          <small v-if="item.quest && item.quest.battlePass" class="battlepass-subtitle">
            {{ item.quest.battlePass.name }}
          </small>
        </div>
      </template>

      <!-- Custom cell for reward item -->
      <template #cell-reward_item="{ value }">
        <span v-if="value" class="reward-item">{{ value }}</span>
        <span v-else class="no-item">-</span>
      </template>
    </DataTable>

    <!-- Create/Edit Form Modal -->
    <div v-if="showCreateForm || editingReward" class="form-modal">
      <div class="form-container">
        <h3>{{ editingReward ? 'Edit Reward' : 'Create New Reward' }}</h3>
        <form @submit.prevent="submitForm">
          <div class="form-group">
            <label>Quest:</label>
            <select v-model="formData.quest_id" required>
              <option value="">Select Quest</option>
              <option v-for="quest in quests" :key="quest.id" :value="quest.id">
                {{ quest.name }} ({{ quest.battlePass?.name || 'Unknown BP' }})
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>Name:</label>
            <input v-model="formData.name" type="text" required>
          </div>
          <div class="form-group">
            <label>Description:</label>
            <textarea v-model="formData.description" rows="3" placeholder="Reward description..."></textarea>
          </div>
          <div class="form-group">
            <label>Type:</label>
            <select v-model="formData.type" required>
              <option value="points">Points</option>
              <option value="item">Item</option>
              <option value="currency">Currency</option>
              <option value="cosmetic">Cosmetic</option>
              <option value="experience">Experience</option>
            </select>
          </div>
          <div class="form-group">
            <label>Reward Points:</label>
            <input v-model.number="formData.reward_points" type="number" min="0" required>
          </div>
          <div class="form-group">
            <label>Reward Item:</label>
            <input v-model="formData.reward_item" type="text" placeholder="Optional - item name or code">
          </div>
          <div class="form-group">
            <label>Active:</label>
            <div class="toggle-switch">
              <input
                v-model="formData.is_active"
                type="checkbox"
                :id="`active-toggle-${editingReward?.id || 'new'}`"
              >
              <label
                :for="`active-toggle-${editingReward?.id || 'new'}`"
                class="toggle-label"
              >
                <span class="toggle-slider"></span>
              </label>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-success" :disabled="submitting">
              {{ submitting ? 'Saving...' : (editingReward ? 'Update' : 'Create') }}
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
const rewards = ref([])
const quests = ref([])
const loading = ref(false)
const submitting = ref(false)
const showCreateForm = ref(false)
const editingReward = ref(null)

const formData = reactive({
  quest_id: '',
  name: '',
  description: '',
  type: 'points',
  reward_points: 0,
  reward_item: '',
  is_active: true,
  is_claimed: false
})

const columns = ref([
  {
    key: 'id',
    label: 'ID',
    type: 'number',
    class: 'id-column'
  },
  {
    key: 'quest.name',
    label: 'Quest',
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
      'points': 'info',
      'item': 'secondary',
      'currency': 'warning',
      'cosmetic': 'danger',
      'experience': 'success'
    }
  },
  {
    key: 'reward_points',
    label: 'Points',
    type: 'number'
  },
  {
    key: 'reward_item',
    label: 'Item',
    type: 'text'
  },
  {
    key: 'is_active',
    label: 'Active',
    type: 'boolean',
    trueText: 'Active',
    falseText: 'Inactive'
  },
  {
    key: 'is_claimed',
    label: 'Claimed',
    type: 'boolean',
    trueText: 'Claimed',
    falseText: 'Unclaimed'
  },
  {
    key: 'claimed_at',
    label: 'Claimed At',
    type: 'datetime'
  }
])

// Methods
const loadRewards = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/rewards')
    rewards.value = response.data
    console.log('Loaded rewards:', rewards.value)
  } catch (error) {
    console.error('Error loading rewards:', error)
    $toast?.error('Failed to load rewards')
  } finally {
    loading.value = false
  }
}

const loadQuests = async () => {
  try {
    const response = await axios.get('/api/quests')
    quests.value = response.data
    console.log('Loaded quests:', quests.value)
  } catch (error) {
    console.error('Error loading quests:', error)
    $toast?.error('Failed to load quests')
  }
}

const submitForm = async () => {
  if (submitting.value) return

  submitting.value = true
  try {
    if (editingReward.value) {
      await axios.put(`/api/rewards/${editingReward.value.id}`, formData)
      $toast?.success('Reward updated successfully!')
    } else {
      await axios.post('/api/rewards', formData)
      $toast?.success('Reward created successfully!')
    }

    await loadRewards()
    cancelForm()
  } catch (error) {
    console.error('Error saving reward:', error)
    const message = error.response?.data?.message || 'Failed to save reward'
    $toast?.error(message)
  } finally {
    submitting.value = false
  }
}

const editReward = (reward) => {
  editingReward.value = reward
  Object.assign(formData, {
    quest_id: reward.quest_id,
    name: reward.name,
    description: reward.description || '',
    type: reward.type,
    reward_points: reward.reward_points,
    reward_item: reward.reward_item || '',
    is_active: reward.is_active,
    is_claimed: reward.is_claimed
  })
  showCreateForm.value = false
}

const deleteReward = async (reward) => {
  if (confirm(`Are you sure you want to delete "${reward.name}"? This action cannot be undone.`)) {
    try {
      await axios.delete(`/api/rewards/${reward.id}`)
      $toast?.success('Reward deleted successfully!')
      await loadRewards()
    } catch (error) {
      console.error('Error deleting reward:', error)
      const message = error.response?.data?.message || 'Failed to delete reward'
      $toast?.error(message)
    }
  }
}

const cancelForm = () => {
  showCreateForm.value = false
  editingReward.value = null
  Object.assign(formData, {
    quest_id: '',
    name: '',
    description: '',
    type: 'points',
    reward_points: 0,
    reward_item: '',
    is_active: true,
    is_claimed: false
  })
}

// Lifecycle
onMounted(async () => {
  await loadQuests()
  await loadRewards()
})
</script>

<style scoped>
.rewards-management {
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

.quest-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.quest-name {
  color: #2c3e50;
  font-weight: 500;
}

.quest-unknown {
  color: #e74c3c;
  font-style: italic;
}

.battlepass-subtitle {
  color: #6c757d;
  font-size: 0.8rem;
}

.reward-item {
  color: #2c3e50;
  font-family: 'Courier New', monospace;
  background-color: #f8f9fa;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.9rem;
}

.no-item {
  color: #6c757d;
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

  .quest-info {
    font-size: 0.9rem;
  }

  .battlepass-subtitle {
    font-size: 0.75rem;
  }
}
</style>
