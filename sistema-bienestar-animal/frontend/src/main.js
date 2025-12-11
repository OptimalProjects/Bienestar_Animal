// src/main.js
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import { useAuthStore } from '@/stores/auth';

// Función para inicializar componentes GOV.CO
function initGovCoComponents() {
  // Reinicializar inputs de texto
  if (typeof window.initInput === 'function') {
    window.initInput();
  }
  // Reinicializar desplegables/selects
  if (typeof window.initDropDown === 'function') {
    window.initDropDown();
  }
  // Reinicializar buscador
  if (typeof window.initSearchBar === 'function') {
    window.initSearchBar();
  }
  // Reinicializar menú
  if (typeof window.initMenu === 'function') {
    window.initMenu();
  }
  console.log('🎨 GOV.CO componentes reinicializados');
}

// Crear la aplicación
const app = createApp(App);

// Crear e instalar Pinia
const pinia = createPinia();
app.use(pinia);

// Usar el router
app.use(router);

// Inicializar auth store
const authStore = useAuthStore();
authStore.initAuth();

// Montar la aplicación
app.mount('#app');

// Inicializar componentes GOV.CO después del montaje inicial
router.isReady().then(() => {
  setTimeout(() => {
    initGovCoComponents();
  }, 200);
});

// Reinicializar GOV.CO después de cada navegación
router.afterEach(() => {
  setTimeout(() => {
    initGovCoComponents();
  }, 100);
});

// Exponer función globalmente para que los componentes puedan llamarla
window.reinitGovCo = initGovCoComponents;
