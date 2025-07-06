<template>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <div class="sidebar-header">
        <div class="logo">
          <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="32" height="32" rx="8" fill="#3498db"/>
            <path d="M8 12L16 8L24 12V20L16 24L8 20V12Z" stroke="white" stroke-width="2" fill="none"/>
            <circle cx="16" cy="16" r="3" fill="white"/>
          </svg>
        </div>
        <h1>SROF17</h1>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/admin/seasons" class="nav-item">
          <span class="nav-icon">📅</span>
          <span class="nav-text">Seasons</span>
        </router-link>
        <router-link to="/admin/battlepasses" class="nav-item">
          <span class="nav-icon">🎯</span>
          <span class="nav-text">Battlepasses</span>
        </router-link>
        <router-link to="/admin/quests" class="nav-item">
          <span class="nav-icon">⚡</span>
          <span class="nav-text">Quests</span>
        </router-link>
        <router-link to="/admin/rewards" class="nav-item">
          <span class="nav-icon">🏆</span>
          <span class="nav-text">Rewards</span>
        </router-link>
        <router-link to="/admin/user-battlepasses" class="nav-item">
          <span class="nav-icon">👥</span>
          <span class="nav-text">User Progress</span>
        </router-link>
      </nav>
    </aside>

    <main class="admin-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
// No logic needed for this layout component
// All reactive data and methods are handled by individual page components
</script>

<style scoped>
.admin-layout {
  min-height: 100vh;
  display: flex;
  background-color: #f5f5f5;
}

.admin-sidebar {
  width: 280px;
  background-color: #2c3e50;
  box-shadow: 2px 0 4px rgba(0,0,0,0.1);
  display: flex;
  flex-direction: column;
  position: fixed;
  height: 100vh;
  left: 0;
  top: 0;
}

.sidebar-header {
  padding: 2rem 1.5rem;
  border-bottom: 1px solid #34495e;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo {
  flex-shrink: 0;
}

.sidebar-header h1 {
  color: white;
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
  letter-spacing: 0.5px;
}

.sidebar-nav {
  flex: 1;
  padding: 1.5rem 0;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  color: #bdc3c7;
  text-decoration: none;
  padding: 1rem 1.5rem;
  transition: all 0.3s ease;
  border-left: 3px solid transparent;
}

.nav-item:hover {
  background-color: #34495e;
  color: white;
  border-left-color: #3498db;
}

.nav-item.router-link-active {
  background-color: #34495e;
  color: white;
  border-left-color: #3498db;
}

.nav-icon {
  font-size: 1.2rem;
  width: 24px;
  text-align: center;
  flex-shrink: 0;
}

.nav-text {
  font-weight: 500;
}

.admin-content {
  flex: 1;
  margin-left: 280px;
  padding: 2rem;
  min-height: 100vh;
}

@media (max-width: 768px) {
  .admin-sidebar {
    width: 100%;
    position: relative;
    height: auto;
  }

  .admin-content {
    margin-left: 0;
  }

  .sidebar-nav {
    padding: 1rem 0;
  }

  .nav-item {
    padding: 0.75rem 1.5rem;
  }
}
</style>
