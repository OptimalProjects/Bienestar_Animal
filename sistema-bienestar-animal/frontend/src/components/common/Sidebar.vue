<!-- src/components/Sidebar.vue -->
<template>
  <aside class="sidebar-govco" :class="{ collapsed: isCollapsed }">
    <!-- Toggle button -->
    <button class="sidebar-toggle" @click="toggleSidebar" title="Expandir/Contraer menú">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
      </svg>
    </button>

    <!-- Menú de navegación -->
    <nav class="sidebar-nav">
      <a
        v-for="item in menuItems"
        :key="item.id"
        :href="item.path"
        class="sidebar-item"
        :class="{ active: isActive(item.id) }"
        @click.prevent="navigateTo(item.id, item.path)"
        :title="isCollapsed ? item.label : ''"
      >
        <span class="sidebar-icon" v-html="item.icon"></span>
        <span v-if="!isCollapsed" class="sidebar-label">{{ item.label }}</span>
        <span v-if="item.badge && !isCollapsed" class="sidebar-badge">{{ item.badge }}</span>
      </a>
    </nav>

    <!-- Footer del sidebar -->
    <div v-if="!isCollapsed" class="sidebar-footer">
      <small>Sistema Veterinario v1.0</small>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue';

const isCollapsed = ref(false);
const activeItem = ref('dashboard');

const menuItems = [
  {
    id: 'dashboard',
    label: 'Dashboard',
    path: '/dashboard',
    icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <rect x="3" y="3" width="7" height="7"></rect>
      <rect x="14" y="3" width="7" height="7"></rect>
      <rect x="14" y="14" width="7" height="7"></rect>
      <rect x="3" y="14" width="7" height="7"></rect>
    </svg>`
  },
  {
    id: 'animals',
    label: 'Animales',
    path: '/animals',
    icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="11" cy="4" r="2"></circle>
      <circle cx="18" cy="8" r="2"></circle>
      <circle cx="20" cy="16" r="2"></circle>
      <path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z"></path>
    </svg>`
  },
  {
    id: 'veterinary',
    label: 'Veterinaria',
    path: '/veterinary',
    icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
    </svg>`,
    badge: '3'
  },
  {
    id: 'vaccinations',
    label: 'Vacunación',
    path: '/vaccinations',
    icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M12 2v20m-4-2l4-4 4 4m0-16l-4 4-4-4"></path>
    </svg>`
  },
  {
    id: 'inventory',
    label: 'Inventario',
    path: '/inventory',
    icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
      <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
      <line x1="12" y1="22.08" x2="12" y2="12"></line>
    </svg>`
  },
  {
    id: 'reports',
    label: 'Reportes',
    path: '/reports',
    icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <line x1="18" y1="20" x2="18" y2="10"></line>
      <line x1="12" y1="20" x2="12" y2="4"></line>
      <line x1="6" y1="20" x2="6" y2="14"></line>
    </svg>`
  },
  {
    id: 'calendar',
    label: 'Calendario',
    path: '/calendar',
    icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
      <line x1="16" y1="2" x2="16" y2="6"></line>
      <line x1="8" y1="2" x2="8" y2="6"></line>
      <line x1="3" y1="10" x2="21" y2="10"></line>
    </svg>`
  },
  {
    id: 'settings',
    label: 'Configuración',
    path: '/settings',
    icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M12 1v6m0 6v6m5.66-13.66l-4.24 4.24m0 6l4.24 4.24M23 12h-6m-6 0H1m18.66 5.66l-4.24-4.24m0-6l4.24-4.24"></path>
    </svg>`
  }
];

function toggleSidebar() {
  isCollapsed.value = !isCollapsed.value;
}

function isActive(itemId) {
  return activeItem.value === itemId;
}

function navigateTo(itemId, path) {
  activeItem.value = itemId;
  console.log('Navegar a:', path);
  // Aquí integrarías con Vue Router:
  // this.$router.push(path);
}
</script>

<style scoped>
.sidebar-govco {
  width: 260px;
  background: #ffffff;
  border-right: 1px solid #e0e0e0;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  position: relative;
  height: 100vh;
  position: sticky;
  top: 0;
}

.sidebar-govco.collapsed {
  width: 70px;
}

.sidebar-toggle {
  position: absolute;
  top: 1rem;
  right: -12px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #004884;
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  transition: transform 0.2s;
}

.sidebar-toggle:hover {
  transform: scale(1.1);
}

.sidebar-nav {
  flex: 1;
  padding: 3rem 0.5rem 1rem;
  overflow-y: auto;
}

.sidebar-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1rem;
  color: #333;
  text-decoration: none;
  border-radius: 8px;
  margin-bottom: 0.25rem;
  transition: all 0.2s;
  position: relative;
}

.collapsed .sidebar-item {
  justify-content: center;
  padding: 0.75rem;
}

.sidebar-item:hover {
  background: #f5f7fb;
  color: #004884;
}

.sidebar-item.active {
  background: #e8f0ff;
  color: #004884;
  font-weight: 600;
}

.sidebar-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #004884;
  border-radius: 0 4px 4px 0;
}

.sidebar-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.sidebar-icon :deep(svg) {
  width: 20px;
  height: 20px;
}

.sidebar-label {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sidebar-badge {
  background: #ff4444;
  color: white;
  border-radius: 10px;
  padding: 2px 8px;
  font-size: 0.75rem;
  font-weight: bold;
  min-width: 20px;
  text-align: center;
}

.sidebar-footer {
  padding: 1rem;
  text-align: center;
  color: #999;
  border-top: 1px solid #e0e0e0;
}

/* Scrollbar personalizado */
.sidebar-nav::-webkit-scrollbar {
  width: 6px;
}

.sidebar-nav::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background: #ccc;
  border-radius: 3px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
  background: #999;
}

@media (max-width: 992px) {
  .sidebar-govco {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    z-index: 999;
    box-shadow: 2px 0 8px rgba(0,0,0,0.1);
  }

  .sidebar-govco.collapsed {
    transform: translateX(-100%);
  }
}
</style>