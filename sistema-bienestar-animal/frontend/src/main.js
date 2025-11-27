// src/main.js
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

// Crear la aplicación
const app = createApp(App);

// Usar el router
app.use(router);

// Montar la aplicación
app.mount('#app');

// Inicializar componentes GOV.CO después del montaje
router.isReady().then(() => {
  // Inicializar GOV.CO si está disponible
  if (typeof window !== 'undefined' && window.GOVCo && window.GOVCo.init) {
    setTimeout(() => {
      window.GOVCo.init();
    }, 100);
  }
});
