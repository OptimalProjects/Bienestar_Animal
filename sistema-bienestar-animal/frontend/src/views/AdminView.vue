<!-- src/views/AdminView.vue -->
<!-- Epica 5: Administracion y Reportes -->
<template>
  <div class="admin-view">
    <header class="admin-header">
      <h1 class="h2-tipografia-govco">
        Administracion y Reportes
      </h1>
      <p class="text2-tipografia-govco">
        Dashboard ejecutivo, reportes personalizados, gestion de usuarios y auditoria del sistema.
      </p>
    </header>

    <!-- PESTANAS -->
    <nav class="admin-tabs">
      <button
        v-for="tab in visibleTabs"
        :key="tab.id"
        type="button"
        class="admin-tab-btn"
        :class="{ active: activeTab === tab.id }"
        @click="activeTab = tab.id"
      >
        <span class="tab-icon" v-html="tab.icon"></span>
        {{ tab.label }}
        <span v-if="tab.badge" class="tab-badge" :class="tab.badgeClass">{{ tab.badge }}</span>
      </button>
    </nav>

    <!-- CONTENIDO SEGUN PESTANA -->
    <section class="admin-tab-content">
      <!-- HU-020: Dashboard Ejecutivo -->
      <ExecutiveDashboard
        v-if="activeTab === 'dashboard'"
      />

      <!-- HU-021: Reportes Personalizados -->
      <CustomReports
        v-else-if="activeTab === 'reports'"
      />

      <!-- HU-022: Gestion de Usuarios -->
      <UserManagement
        v-else-if="activeTab === 'users'"
      />

      <!-- HU-023: Log de Auditoria -->
      <AuditLog
        v-else-if="activeTab === 'audit'"
      />
    </section>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

// Componentes del modulo de administracion
import ExecutiveDashboard from '../components/admin/ExecutiveDashboard.vue';
import CustomReports from '../components/admin/CustomReports.vue';
import UserManagement from '../components/admin/UserManagement.vue';
import AuditLog from '../components/admin/AuditLog.vue';

const activeTab = ref('dashboard');

// Simular rol del usuario (conectar con auth real)
const userRole = ref('admin'); // 'director', 'admin'

const tabs = [
  {
    id: 'dashboard',
    label: 'Dashboard',
    icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <rect x="3" y="3" width="7" height="7"></rect>
      <rect x="14" y="3" width="7" height="7"></rect>
      <rect x="14" y="14" width="7" height="7"></rect>
      <rect x="3" y="14" width="7" height="7"></rect>
    </svg>`,
    roles: ['director', 'admin'],
    badge: null,
    badgeClass: ''
  },
  {
    id: 'reports',
    label: 'Reportes',
    icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
      <polyline points="14 2 14 8 20 8"></polyline>
      <line x1="16" y1="13" x2="8" y2="13"></line>
      <line x1="16" y1="17" x2="8" y2="17"></line>
    </svg>`,
    roles: ['director', 'admin'],
    badge: null,
    badgeClass: ''
  },
  {
    id: 'users',
    label: 'Usuarios',
    icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
      <circle cx="9" cy="7" r="4"></circle>
      <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
      <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
    </svg>`,
    roles: ['admin'],
    badge: null,
    badgeClass: ''
  },
  {
    id: 'audit',
    label: 'Auditoria',
    icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
      <polyline points="14 2 14 8 20 8"></polyline>
      <line x1="12" y1="18" x2="12" y2="12"></line>
      <line x1="9" y1="15" x2="15" y2="15"></line>
    </svg>`,
    roles: ['admin'],
    badge: null,
    badgeClass: ''
  }
];

// Filtrar tabs segun rol del usuario
const visibleTabs = computed(() => {
  return tabs.filter(tab => tab.roles.includes(userRole.value));
});
</script>

<style scoped>
.admin-view {
  padding: 1.5rem 2rem;
  background: #f5f7fb;
  min-height: 100vh;
}

.admin-header {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #004884;
}

.admin-header h1 {
  color: #004884;
  margin: 0 0 0.5rem 0;
}

.admin-header p {
  color: #4B4B4B;
  margin: 0;
}

/* Tabs */
.admin-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  background: white;
  padding: 0.75rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.admin-tab-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border: none;
  border-radius: 20px;
  padding: 0.6rem 1.2rem;
  background: #e0e0e0;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s;
  color: #4B4B4B;
}

.admin-tab-btn:hover {
  background: #d0d0d0;
}

.admin-tab-btn.active {
  background: #004884;
  color: #fff;
  font-weight: 600;
}

.tab-icon {
  display: flex;
  align-items: center;
}

.tab-badge {
  border-radius: 10px;
  padding: 2px 8px;
  font-size: 0.75rem;
  font-weight: bold;
  min-width: 20px;
  text-align: center;
}

.admin-tab-content {
  margin-top: 0.5rem;
}

/* Responsive */
@media (max-width: 768px) {
  .admin-view {
    padding: 1rem;
  }

  .admin-tabs {
    gap: 0.25rem;
  }

  .admin-tab-btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.85rem;
  }

  .tab-icon {
    display: none;
  }
}

@media (max-width: 576px) {
  .admin-tabs {
    flex-direction: column;
  }

  .admin-tab-btn {
    width: 100%;
    justify-content: center;
  }

  .tab-icon {
    display: flex;
  }
}
</style>
