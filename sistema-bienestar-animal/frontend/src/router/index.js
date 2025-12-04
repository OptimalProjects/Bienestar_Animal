// src/router/index.js 
import { createRouter, createWebHistory } from 'vue-router';

// Vistas principales
import HomeView from '../views/HomeView.vue';
import LoginView from '../views/LoginView.vue';
import DashboardView from '../views/DashboardView.vue';
import AnimalsView from '../views/AnimalsView.vue';
import VeterinaryView from '../views/VeterinaryView.vue';
import ComplaintsView from '../views/ComplaintsView.vue';
import AdoptionsView from '../views/AdoptionsView.vue';
import AdminView from '../views/AdminView.vue';
import AdoptionsCoordinatorView from '../views/AdoptionsCoordinatorView.vue';

// Roles
import { getCurrentRole, ROLES } from '../composables/useRol.js';

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView,
    meta: {
      title: 'Inicio - Sistema Bienestar Animal',
      requiresAuth: false,
      layout: 'public'
    }
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: {
      title: 'Iniciar Sesión - Sistema Bienestar Animal',
      requiresAuth: false,
      layout: 'public'
    }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: {
      title: 'Panel Principal - Sistema Bienestar Animal',
      requiresAuth: true,
      layout: 'app',
      roles: [
        ROLES.OPERADOR_RESCATE,
        ROLES.MEDICO_VETERINARIO,
        ROLES.COORDINADOR_ADOPCIONES,
        ROLES.ADMIN_SISTEMA,
        ROLES.DIRECTOR,
      ]
    }
  },
  {
    path: '/animales',
    name: 'animals',
    component: AnimalsView,
    meta: {
      title: 'Gestión de Animales - Sistema Bienestar Animal',
      requiresAuth: true,
      layout: 'app',
      roles: [
        ROLES.OPERADOR_RESCATE,
        ROLES.MEDICO_VETERINARIO,
        ROLES.COORDINADOR_ADOPCIONES,
        ROLES.ADMIN_SISTEMA,
      ]
    }
  },
  {
    path: '/veterinaria',
    name: 'veterinary',
    component: VeterinaryView,
    meta: {
      title: 'Atención Veterinaria - Sistema Bienestar Animal',
      requiresAuth: true,
      layout: 'app',
      roles: [
        ROLES.MEDICO_VETERINARIO,
        ROLES.ADMIN_SISTEMA,
      ]
    }
  },
  {
    path: '/denuncias',
    name: 'complaints',
    component: ComplaintsView,
    meta: {
      title: 'Denuncias y Rescate - Sistema Bienestar Animal',
      requiresAuth: false, // ciudadano puede denunciar
      layout: 'app',
      // sin meta.roles = todos los roles, incluyendo ciudadano
    }
  },
  {
    path: '/adopciones',
    name: 'adoptions',
    component: AdoptionsView,
    meta: {
      title: 'Adopciones - Sistema Bienestar Animal',
      requiresAuth: false,
      layout: 'app'
    }
  },
  {
    path: '/adopciones/coordinador',
    name: 'adoptions-coordinator',
    component: AdoptionsCoordinatorView,
    meta: {
      title: 'Gestión de Adopciones - Sistema Bienestar Animal',
      requiresAuth: true,
      layout: 'app',
      roles: [
        ROLES.COORDINADOR_ADOPCIONES,
        ROLES.ADMIN_SISTEMA,
      ]
    }
  },
  {
    path: '/administracion',
    name: 'admin',
    component: AdminView,
    meta: {
      title: 'Administración - Sistema Bienestar Animal',
      requiresAuth: true,
      layout: 'app',
      roles: [ROLES.ADMIN_SISTEMA]
    }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    redirect: '/'
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0 };
    }
  }
});

// Guard de navegación: títulos + RBAC simple
router.beforeEach((to, from, next) => {
  document.title = to.meta.title || 'Sistema Bienestar Animal';

  const role = getCurrentRole();
  const isInternalRole = role !== ROLES.CIUDADANO;

  // 1) Bloquear rutas internas para ciudadano
  if (to.meta.requiresAuth && !isInternalRole) {
    if (to.name === 'adoptions-coordinator') {
      return next({ name: 'adoptions' });
    }
    return next({ name: 'home' });
  }

  // 2) Validar roles específicos
  if (to.meta.roles && to.meta.roles.length) {
    const allowed = to.meta.roles.includes(role);
    if (!allowed) {
      if (role === ROLES.CIUDADANO) {
        return next({ name: 'home' });
      }
      return next({ name: 'dashboard' });
    }
  }

  next();
});

export default router;
