// src/main.js
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import { useAuthStore } from '@/stores/auth';

/**
 * Función para inicializar componentes GOV.CO
 */
function initGovCoComponents() {
  if (typeof window === 'undefined') return;

  try {
    // Inicializar inputs
    if (typeof window.initInput === 'function') {
      window.initInput();
    }

    // Inicializar dropdowns
    if (typeof window.initDropDown === 'function') {
      window.initDropDown();
    }

    // Inicializar otros componentes GOV.CO
    if (typeof window.initSearchBar === 'function') {
      window.initSearchBar();
    }

    if (typeof window.initMenu === 'function') {
      window.initMenu();
    }

    // Inicializar listas de desplegables
    const dropdowns = document.querySelectorAll('[data-type="basic"]');
    dropdowns.forEach((dropdown) => {
      if (window.createList && dropdown.id) {
        try {
          window.createList(dropdown.id);
        } catch (e) {
          // Ignorar errores de dropdowns ya inicializados
        }
      }
    });

    console.log('✅ GOV.CO components initialized');
  } catch (error) {
    console.error('❌ Error initializing GOV.CO:', error);
  }
}

/**
 * Esperar a que GOV.CO esté disponible
 */
function waitForGovCo(callback, maxAttempts = 50) {
  let attempts = 0;

  const checkGovCo = setInterval(() => {
    attempts++;

    if (
      typeof window.govco !== 'undefined' ||
      typeof window.initInput !== 'undefined' ||
      typeof window.createList !== 'undefined'
    ) {
      clearInterval(checkGovCo);
      console.log('✅ GOV.CO CDN loaded');
      callback();
    } else if (attempts >= maxAttempts) {
      clearInterval(checkGovCo);
      console.warn('⚠️ GOV.CO CDN timeout, continuing anyway');
      callback();
    }
  }, 100); // Check every 100ms
}

/**
 * Inicializar aplicación Vue
 */
function initVueApp() {
  const app = createApp(App);
  const pinia = createPinia();

  app.use(pinia);
  app.use(router);

  // Inicializar auth store
  const authStore = useAuthStore();
  authStore.initAuth();

  // Exponer función de inicialización globalmente
  window.reinitGovCo = initGovCoComponents;

  // Montar aplicación
  app.mount('#app');

  // Inicializar GOV.CO después de montar
  router.isReady().then(() => {
    setTimeout(() => {
      initGovCoComponents();
    }, 200);
  });

  // Reinicializar GOV.CO después de cada cambio de ruta
  router.afterEach(() => {
    setTimeout(() => {
      initGovCoComponents();
    }, 300); // Esperar a que Vue renderice
  });
}

// Esperar a DOMContentLoaded y GOV.CO
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    waitForGovCo(initVueApp);
  });
} else {
  waitForGovCo(initVueApp);
}
