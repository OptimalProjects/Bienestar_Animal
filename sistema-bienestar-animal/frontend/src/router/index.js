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
      layout: 'app'
    }
  },
  {
    path: '/animales',
    name: 'animals',
    component: AnimalsView,
    meta: {
      title: 'Gestión de Animales - Sistema Bienestar Animal',
      requiresAuth: true,
      layout: 'app'
    }
  },
  {
    path: '/veterinaria',
    name: 'veterinary',
    component: VeterinaryView,
    meta: {
      title: 'Atención Veterinaria - Sistema Bienestar Animal',
      requiresAuth: true,
      layout: 'app'
    }
  },
  {
    path: '/denuncias',
    name: 'complaints',
    component: ComplaintsView,
    meta: {
      title: 'Denuncias y Rescate - Sistema Bienestar Animal',
      requiresAuth: false, // Público puede reportar denuncias
      layout: 'app'
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
    path: '/administracion',
    name: 'admin',
    component: AdminView,
    meta: {
      title: 'Administración - Sistema Bienestar Animal',
      requiresAuth: true,
      roles: ['director', 'admin'],
      layout: 'app'
    }
  },
  // Ruta de captura para 404
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

// Guard de navegación para actualizar el título
router.beforeEach((to, from, next) => {
  // Actualizar título de la página
  document.title = to.meta.title || 'Sistema Bienestar Animal';

  // Aquí se puede agregar lógica de autenticación
  // const isAuthenticated = checkAuth();
  // if (to.meta.requiresAuth && !isAuthenticated) {
  //   next({ name: 'login' });
  // } else {
  //   next();
  // }

  next();
});

export default router;
