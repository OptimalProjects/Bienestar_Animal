<!-- src/components/layout/AppSidebar.vue -->
<!-- Sidebar de navegación interna para el panel de administración -->
<template>
  <aside class="app-sidebar" :class="{ collapsed: isCollapsed }">
    <div class="sidebar-header">
      <button
        type="button"
        class="sidebar-toggle"
        @click="toggleSidebar"
        :aria-label="isCollapsed ? 'Expandir menú' : 'Colapsar menú'"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="3" y1="12" x2="21" y2="12"></line>
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
      </button>
    </div>

    <nav class="sidebar-nav">
      <ul class="sidebar-menu">
        <li
          v-for="item in menuItems"
          :key="item.path"
          class="sidebar-item"
          :class="{ active: isActive(item.path) }"
        >
          <router-link :to="item.path" class="sidebar-link" :title="item.label">
            <span class="sidebar-icon" v-html="item.icon"></span>
            <span class="sidebar-label">{{ item.label }}</span>
            <span v-if="item.badge" class="sidebar-badge" :class="item.badgeClass">
              {{ item.badge }}
            </span>
          </router-link>
        </li>
      </ul>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="sidebar-user-avatar">
          {{ userInitials }}
        </div>
        <div class="sidebar-user-info">
          <span class="sidebar-user-name">{{ userName }}</span>
          <span class="sidebar-user-role">{{ userRoleLabel }}</span>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useRole, ROLES } from '../../composables/useRol.js';

const route = useRoute();
const { role } = useRole();

// Estado del sidebar
const isCollapsed = ref(false);

// Nombre y rol que se muestran en el footer del sidebar
const userName = computed(() => {
  switch (role.value) {
    case ROLES.OPERADOR_RESCATE:
      return 'Operador de Rescate';
    case ROLES.MEDICO_VETERINARIO:
      return 'Médico Veterinario';
    case ROLES.COORDINADOR_ADOPCIONES:
      return 'Coordinador de Adopciones';
    case ROLES.ADMIN_SISTEMA:
      return 'Administrador del Sistema';
    case ROLES.DIRECTOR:
      return 'Director';
    case ROLES.CIUDADANO:
    default:
      return 'Ciudadano';
  }
});

const userInitials = computed(() => {
  return userName.value
    .split(' ')
    .map(n => n[0])
    .slice(0, 2)
    .join('')
    .toUpperCase();
});

const userRoleLabel = computed(() => {
  const labels = {
    [ROLES.CIUDADANO]: 'Ciudadano',
    [ROLES.OPERADOR_RESCATE]: 'Operador de Rescate',
    [ROLES.MEDICO_VETERINARIO]: 'Médico Veterinario',
    [ROLES.COORDINADOR_ADOPCIONES]: 'Coord. Adopciones',
    [ROLES.ADMIN_SISTEMA]: 'Admin. Sistema',
    [ROLES.DIRECTOR]: 'Director',
  };
  return labels[role.value] || 'Usuario';
});

// Items del menú (mantenemos tus íconos, cambiamos solo roles y añadimos gestión de adopciones)
const menuItems = computed(() => {
  const items = [
    {
      path: '/dashboard',
      label: 'Panel Principal',
      icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="7" height="7"></rect>
        <rect x="14" y="3" width="7" height="7"></rect>
        <rect x="14" y="14" width="7" height="7"></rect>
        <rect x="3" y="14" width="7" height="7"></rect>
      </svg>`,
      roles: [
        ROLES.OPERADOR_RESCATE,
        ROLES.MEDICO_VETERINARIO,
        ROLES.COORDINADOR_ADOPCIONES,
        ROLES.ADMIN_SISTEMA,
        ROLES.DIRECTOR,
      ]
    },
    {
      path: '/animales',
      label: 'Animales',
      icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M10 5.172C10 3.782 8.423 2.679 6.5 3c-2.823.47-3.5 3.005-3.5 5 0 0-.04 1.622.5 3.5C4 13 5 15 5 16.5 5 17.5 4 19 4 19h4l1-2 1 2h4s-1-1.5-1-2.5C13 15 14 13 14.5 11.5c.54-1.878.5-3.5.5-3.5 0-1.995-.677-4.53-3.5-5-1.923-.321-3.5.782-3.5 2.172Z"></path>
        <path d="M8 14v.5"></path>
        <path d="M16 14v.5"></path>
        <path d="M11.25 16.25h1.5L12 17l-.75-.75z"></path>
      </svg>`,
      roles: [
        ROLES.OPERADOR_RESCATE,
        ROLES.MEDICO_VETERINARIO,
        ROLES.COORDINADOR_ADOPCIONES,
        ROLES.ADMIN_SISTEMA,
      ]
    },
    {
      path: '/veterinaria',
      label: 'Veterinaria',
      icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3 4.6 4.6 0 0 0 12 5.09 4.6 4.6 0 0 0 7.5 3 5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z"></path>
      </svg>`,
      roles: [
        ROLES.MEDICO_VETERINARIO,
        ROLES.ADMIN_SISTEMA,
      ]
    },
    {
      path: '/denuncias',
      label: 'Denuncias',
      icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
        <line x1="12" y1="9" x2="12" y2="13"></line>
        <line x1="12" y1="17" x2="12.01" y2="17"></line>
      </svg>`,
      // badge solo ilustrativo, puedes quitarlo
      badge: 3,
      badgeClass: 'badge-danger',
      roles: [
        ROLES.CIUDADANO,
        ROLES.OPERADOR_RESCATE,
        ROLES.MEDICO_VETERINARIO,
        ROLES.COORDINADOR_ADOPCIONES,
        ROLES.ADMIN_SISTEMA,
        ROLES.DIRECTOR,
      ]
    },
    {
      path: '/adopciones',
      label: 'Adopciones',
      icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
        <polyline points="9 22 9 12 15 12 15 22"></polyline>
      </svg>`,
      // todos los roles, porque el coordinador también puede ir a catálogo
      roles: [
        ROLES.CIUDADANO,
        ROLES.OPERADOR_RESCATE,
        ROLES.MEDICO_VETERINARIO,
        ROLES.COORDINADOR_ADOPCIONES,
        ROLES.ADMIN_SISTEMA,
        ROLES.DIRECTOR,
      ]
    },
    {
      path: '/adopciones/coordinador',
      label: 'Gestión de adopciones',
      icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="3"></circle>
        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-1.42 3.42h-.09a1.65 1.65 0 0 0-1.51 1l-.2.5a2 2 0 0 1-3.74 0l-.2-.5a1.65 1.65 0 0 0-1.51-1h-.09A2 2 0 0 1 4.21 17.1l.06-.06A1.65 1.65 0 0 0 4.6 15"></path>
        <path d="M19.4 9a1.65 1.65 0 0 0 .33-1.82l-.06-.06A2 2 0 0 0 18.25 5"></path>
      </svg>`,
      roles: [
        ROLES.COORDINADOR_ADOPCIONES,
        ROLES.ADMIN_SISTEMA,
      ]
    },
    {
      path: '/administracion',
      label: 'Administración',
      icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="3"></circle>
        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-1.42 3.42h-.09a1.65 1.65 0 0 0-1.51 1l-.2.5a2 2 0 0 1-3.74 0l-.2-.5a1.65 1.65 0 0 0-1.51-1h-.09A2 2 0 0 1 4.21 17.1l.06-.06A1.65 1.65 0 0 0 4.6 15"></path>
      </svg>`,
      roles: [ROLES.ADMIN_SISTEMA]
    }
  ];

  return items.filter(item => !item.roles || item.roles.includes(role.value));
});

function toggleSidebar() {
  isCollapsed.value = !isCollapsed.value;
}

function isActive(path) {
  return route.path === path || route.path.startsWith(path + '/');
}
</script>


<style scoped>
.app-sidebar {
  width: 260px;
  background-color: #fff;
  border-right: 1px solid #e0e0e0;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  overflow: hidden;
}

.app-sidebar.collapsed {
  width: 70px;
}

/* Header del sidebar */
.sidebar-header {
  padding: 16px;
  border-bottom: 1px solid #e0e0e0;
}

.sidebar-toggle {
  background: #f5f7fb;
  border: none;
  padding: 8px;
  border-radius: 6px;
  cursor: pointer;
  color: #666;
  transition: all 0.2s;
}

.sidebar-toggle:hover {
  background: #e8f0fe;
  color: #3366CC;
}

/* Navegación */
.sidebar-nav {
  flex: 1;
  overflow-y: auto;
  padding: 16px 0;
}

.sidebar-menu {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar-item {
  margin: 4px 12px;
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  color: #4B4B4B;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.2s;
  font-size: 0.9rem;
}

.sidebar-link:hover {
  background-color: #f5f7fb;
  color: #3366CC;
}

.sidebar-item.active .sidebar-link {
  background-color: #e8f0fe;
  color: #3366CC;
  font-weight: 600;
}

.sidebar-item.active .sidebar-link::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 24px;
  background-color: #3366CC;
  border-radius: 0 4px 4px 0;
}

.sidebar-item {
  position: relative;
}

.sidebar-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.sidebar-label {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sidebar-badge {
  padding: 2px 8px;
  border-radius: 10px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-danger {
  background-color: #A80521;
  color: white;
}

.badge-warning {
  background-color: #FFAB00;
  color: #333;
}

/* Footer del sidebar */
.sidebar-footer {
  padding: 16px;
  border-top: 1px solid #e0e0e0;
}

.sidebar-user {
  display: flex;
  align-items: center;
  gap: 12px;
}

.sidebar-user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #3366CC;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.sidebar-user-info {
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.sidebar-user-name {
  font-weight: 600;
  font-size: 0.9rem;
  color: #333;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sidebar-user-role {
  font-size: 0.8rem;
  color: #666;
}

/* Estado colapsado */
.app-sidebar.collapsed .sidebar-label,
.app-sidebar.collapsed .sidebar-badge,
.app-sidebar.collapsed .sidebar-user-info {
  display: none;
}

.app-sidebar.collapsed .sidebar-link {
  justify-content: center;
  padding: 12px;
}

.app-sidebar.collapsed .sidebar-user {
  justify-content: center;
}

/* Responsive */
@media (max-width: 991.98px) {
  .app-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    z-index: 1050;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }

  .app-sidebar.show {
    transform: translateX(0);
  }

  .app-sidebar.collapsed {
    width: 260px;
  }
}
</style>
