<!-- src/components/Navbar.vue -->
<template>
  <nav class="navbar-govco">
    <div class="navbar-container">
      <!-- Logo y título -->
      <div class="navbar-brand">
        <img src="" alt="Logo" class="navbar-logo" />
        <span class="navbar-title">Sistema Veterinario</span>
      </div>

      <!-- Menú derecho -->
      <div class="navbar-menu">
        <!-- Notificaciones -->
        <button class="navbar-btn" @click="toggleNotifications" title="Notificaciones">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
          </svg>
          <span v-if="notificationCount > 0" class="badge">{{ notificationCount }}</span>
        </button>

        <!-- Usuario -->
        <div class="navbar-user" @click="toggleUserMenu">
          <div class="user-avatar">
            <span>{{ userInitials }}</span>
          </div>
          <span class="user-name">{{ userName }}</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </div>

        <!-- Dropdown usuario -->
        <div v-if="showUserMenu" class="dropdown-menu">
          <div class="dropdown-header">
            <strong>{{ userName }}</strong>
            <small>{{ userRole }}</small>
          </div>
          <hr />
          <a href="#" class="dropdown-item" @click.prevent="goToProfile">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Mi perfil
          </a>
          <a href="#" class="dropdown-item" @click.prevent="goToSettings">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="3"></circle>
              <path d="M12 1v6m0 6v6m5.66-13.66l-4.24 4.24m0 6l4.24 4.24M23 12h-6m-6 0H1m18.66 5.66l-4.24-4.24m0-6l4.24-4.24"></path>
            </svg>
            Configuración
          </a>
          <hr />
          <a href="#" class="dropdown-item logout" @click.prevent="logout">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
              <polyline points="16 17 21 12 16 7"></polyline>
              <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Cerrar sesión
          </a>
        </div>
      </div>
    </div>

    <!-- Panel de notificaciones -->
    <div v-if="showNotifications" class="notifications-panel">
      <div class="notifications-header">
        <h3>Notificaciones</h3>
        <button @click="toggleNotifications" class="close-btn">✕</button>
      </div>
      <div class="notifications-body">
        <div v-if="notifications.length === 0" class="no-notifications">
          No hay notificaciones nuevas
        </div>
        <div v-else v-for="notif in notifications" :key="notif.id" class="notification-item">
          <div class="notif-icon" :class="notif.type">
            <span v-if="notif.type === 'alert'">⚠️</span>
            <span v-else-if="notif.type === 'info'">ℹ️</span>
            <span v-else>✓</span>
          </div>
          <div class="notif-content">
            <strong>{{ notif.title }}</strong>
            <p>{{ notif.message }}</p>
            <small>{{ notif.time }}</small>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed } from 'vue';

// Estado del usuario (esto lo conectarías a tu store/auth real)
const userName = ref('Dr. Juan Pérez');
const userRole = ref('Veterinario');
const showUserMenu = ref(false);
const showNotifications = ref(false);

// Notificaciones de ejemplo
const notifications = ref([
  {
    id: 1,
    type: 'alert',
    title: 'Vacuna pendiente',
    message: 'El perro "Max" necesita vacuna antirrábica',
    time: 'Hace 5 min'
  },
  {
    id: 2,
    type: 'info',
    title: 'Cita programada',
    message: 'Consulta para "Luna" mañana a las 10:00',
    time: 'Hace 1 hora'
  }
]);

const notificationCount = computed(() => notifications.value.length);

const userInitials = computed(() => {
  return userName.value
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase();
});

function toggleUserMenu() {
  showUserMenu.value = !showUserMenu.value;
  if (showUserMenu.value) showNotifications.value = false;
}

function toggleNotifications() {
  showNotifications.value = !showNotifications.value;
  if (showNotifications.value) showUserMenu.value = false;
}

function goToProfile() {
  console.log('Ir a perfil');
  showUserMenu.value = false;
}

function goToSettings() {
  console.log('Ir a configuración');
  showUserMenu.value = false;
}

function logout() {
  console.log('Cerrar sesión');
  // Aquí irían tus acciones de logout
  showUserMenu.value = false;
}
</script>

<style scoped>
.navbar-govco {
  background: #004884;
  color: white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.navbar-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.navbar-brand {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.navbar-logo {
  height: 40px;
  width: auto;
}

.navbar-title {
  font-size: 1.25rem;
  font-weight: 600;
}

.navbar-menu {
  display: flex;
  align-items: center;
  gap: 1rem;
  position: relative;
}

.navbar-btn {
  background: transparent;
  border: none;
  color: white;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 4px;
  position: relative;
  transition: background 0.2s;
}

.navbar-btn:hover {
  background: rgba(255,255,255,0.1);
}

.badge {
  position: absolute;
  top: 0;
  right: 0;
  background: #ff4444;
  color: white;
  border-radius: 10px;
  padding: 2px 6px;
  font-size: 0.7rem;
  font-weight: bold;
}

.navbar-user {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  transition: background 0.2s;
}

.navbar-user:hover {
  background: rgba(255,255,255,0.1);
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #3366cc;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.9rem;
}

.user-name {
  font-size: 0.95rem;
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 0.5rem);
  right: 0;
  background: white;
  color: #333;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  min-width: 220px;
  padding: 0.5rem 0;
  z-index: 1001;
}

.dropdown-header {
  padding: 1rem;
}

.dropdown-header strong {
  display: block;
  color: #004884;
}

.dropdown-header small {
  color: #666;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: #333;
  text-decoration: none;
  transition: background 0.2s;
}

.dropdown-item:hover {
  background: #f5f5f5;
}

.dropdown-item.logout {
  color: #d32f2f;
}

.dropdown-item svg {
  flex-shrink: 0;
}

hr {
  margin: 0.5rem 0;
  border: none;
  border-top: 1px solid #e0e0e0;
}

.notifications-panel {
  position: fixed;
  top: 70px;
  right: 1rem;
  width: 360px;
  max-height: 500px;
  background: white;
  color: #333;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  overflow: hidden;
  z-index: 1001;
}

.notifications-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #e0e0e0;
  background: #f5f5f5;
}

.notifications-header h3 {
  margin: 0;
  font-size: 1rem;
  color: #004884;
}

.close-btn {
  background: transparent;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
  padding: 0;
  width: 30px;
  height: 30px;
}

.notifications-body {
  max-height: 400px;
  overflow-y: auto;
}

.no-notifications {
  padding: 2rem;
  text-align: center;
  color: #999;
}

.notification-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid #f0f0f0;
  transition: background 0.2s;
  cursor: pointer;
}

.notification-item:hover {
  background: #f9f9f9;
}

.notification-item:last-child {
  border-bottom: none;
}

.notif-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  flex-shrink: 0;
}

.notif-icon.alert {
  background: #fff3cd;
}

.notif-icon.info {
  background: #d1ecf1;
}

.notif-content {
  flex: 1;
}

.notif-content strong {
  display: block;
  margin-bottom: 0.25rem;
  color: #004884;
}

.notif-content p {
  margin: 0 0 0.5rem 0;
  font-size: 0.9rem;
  color: #666;
}

.notif-content small {
  color: #999;
  font-size: 0.8rem;
}

@media (max-width: 768px) {
  .navbar-container {
    padding: 0.75rem 1rem;
  }

  .user-name {
    display: none;
  }

  .navbar-title {
    font-size: 1rem;
  }

  .notifications-panel {
    right: 0;
    left: 0;
    width: 100%;
    border-radius: 0;
  }
}
</style>