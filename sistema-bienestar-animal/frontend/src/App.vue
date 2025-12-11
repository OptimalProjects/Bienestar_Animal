<!-- src/App.vue -->
<template>
  <div id="app-root" class="app-wrapper">
    <!-- Header GOV.CO (siempre visible) -->
    <GovcoHeader />

    <!-- Contenido principal -->
    <div class="app-body" :class="{ 'with-sidebar': showSidebar }">
      <!-- Sidebar (solo para rutas autenticadas) -->
      <AppSidebar v-if="showSidebar" />

      <!-- Contenido de la vista -->
      <main class="app-main">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>

    <!-- Footer GOV.CO (siempre visible) -->
    <GovcoFooter />

    <!-- Componentes globales de notificacion -->
    <Toast ref="toastRef" />
    <ConfirmDialog ref="confirmRef" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, getCurrentInstance } from 'vue';
import { useRoute } from 'vue-router';

// Componentes de layout
import GovcoHeader from './components/layout/GovcoHeader.vue';
import GovcoFooter from './components/layout/GovcoFooter.vue';
import AppSidebar from './components/layout/AppSidebar.vue';

// Componentes globales de notificacion
import Toast from './components/common/Toast.vue';
import ConfirmDialog from './components/common/ConfirmDialog.vue';

const route = useRoute();

// Referencias a los componentes de notificacion
const toastRef = ref(null);
const confirmRef = ref(null);

// Mostrar sidebar solo en rutas con layout 'app' y que requieren autenticación
const showSidebar = computed(() => {
  const appRoutes = ['/dashboard', '/animales', '/veterinaria', '/denuncias', '/adopciones', '/administración'];
  return appRoutes.some(r => route.path.startsWith(r));
});

// Registrar componentes globalmente al montar
onMounted(() => {
  const instance = getCurrentInstance();
  if (instance) {
    // Registrar Toast
    if (toastRef.value) {
      instance.appContext.config.globalProperties.$toast = toastRef.value;
      // Tambien exponer globalmente para uso fuera de componentes Vue
      window.$toast = toastRef.value;
    }
    // Registrar ConfirmDialog
    if (confirmRef.value) {
      instance.appContext.config.globalProperties.$confirm = confirmRef.value;
      window.$confirm = confirmRef.value;
    }
  }
});
</script>

<style>
/* ========================================
   ESTILOS GLOBALES GOV.CO
   ======================================== */

/* Reset y base */
*,
*::before,
*::after {
  box-sizing: border-box;
}

html {
  font-size: 16px;
  scroll-behavior: smooth;
}

body {
  margin: 0;
  padding: 0;
  font-family: 'Work Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-size: 1rem;
  line-height: 1.5;
  color: #333333;
  background-color: #f5f5f5;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Wrapper de la aplicación */
.app-wrapper {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Body de la aplicación */
.app-body {
  flex: 1;
  display: flex;
  background-color: #f5f7fb;
}



/* Main content */
.app-main {
  flex: 1;
  min-height: calc(100vh - 200px);
  overflow-x: hidden;
}

/* ========================================
   TIPOGRAFÍA GOV.CO
   ======================================== */

/* Títulos */
.h1-tipografia-govco {
  font-size: 2.5rem;
  font-weight: 700;
  line-height: 1.2;
  margin: 0 0 1rem 0;
}

.h2-tipografia-govco {
  font-size: 2rem;
  font-weight: 700;
  line-height: 1.25;
  margin: 0 0 0.75rem 0;
}

.h3-tipografia-govco {
  font-size: 1.75rem;
  font-weight: 600;
  line-height: 1.3;
  margin: 0 0 0.75rem 0;
}

.h4-tipografia-govco {
  font-size: 1.5rem;
  font-weight: 600;
  line-height: 1.35;
  margin: 0 0 0.5rem 0;
}

.h5-tipografia-govco {
  font-size: 1.25rem;
  font-weight: 600;
  line-height: 1.4;
  margin: 0 0 0.5rem 0;
}

.h6-tipografia-govco {
  font-size: 1rem;
  font-weight: 600;
  line-height: 1.5;
  margin: 0 0 0.5rem 0;
}

/* Texto */
.text1-tipografia-govco {
  font-size: 1rem;
  line-height: 1.6;
}

.text2-tipografia-govco {
  font-size: 0.9rem;
  line-height: 1.5;
  color: #4B4B4B;
}

.text3-tipografia-govco {
  font-size: 0.85rem;
  line-height: 1.5;
  color: #666666;
}

/* ========================================
   COLORES GOV.CO
   ======================================== */

/* Colores de texto */
.govcolor-blue-dark {
  color: #004884;
}

.govcolor-blue {
  color: #3366CC;
}

.govcolor-green {
  color: #069169;
}

.govcolor-red {
  color: #A80521;
}

.govcolor-yellow {
  color: #FFAB00;
}

.govcolor-gray-dark {
  color: #4B4B4B;
}

.govcolor-gray {
  color: #737373;
}

/* Colores de fondo */
.govbg-blue-dark {
  background-color: #004884;
}

.govbg-blue {
  background-color: #3366CC;
}

.govbg-green {
  background-color: #069169;
}

.govbg-red {
  background-color: #A80521;
}

.govbg-yellow {
  background-color: #FFAB00;
}

.govbg-gray-light {
  background-color: #f5f5f5;
}

/* ========================================
   BOTONES GOV.CO
   ======================================== */

.btn-govco {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 24px;
  font-size: 1rem;
  font-weight: 600;
  line-height: 1.5;
  text-decoration: none;
  border: 2px solid transparent;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-govco:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-govco-primary {
  background-color: #3366CC;
  border-color: #3366CC;
  color: white;
}

.btn-govco-primary:hover:not(:disabled) {
  background-color: #004884;
  border-color: #004884;
}

.btn-govco-secondary {
  background-color: transparent;
  border-color: #3366CC;
  color: #3366CC;
}

.btn-govco-secondary:hover:not(:disabled) {
  background-color: #3366CC;
  color: white;
}

.btn-govco-success {
  background-color: #069169;
  border-color: #069169;
  color: white;
}

.btn-govco-success:hover:not(:disabled) {
  background-color: #057a58;
  border-color: #057a58;
}

.btn-govco-danger {
  background-color: #A80521;
  border-color: #A80521;
  color: white;
}

.btn-govco-danger:hover:not(:disabled) {
  background-color: #8a041b;
  border-color: #8a041b;
}

/* ========================================
   CARDS GOV.CO
   ======================================== */

.card-govco {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.card-govco-header {
  padding: 1rem 1.5rem;
  background-color: #E8F0FE;
  border-bottom: 2px solid #3366CC;
}

.card-govco-header h3,
.card-govco-header h4 {
  margin: 0;
  color: #3366CC;
}

.card-govco-body {
  padding: 1.5rem;
}

.card-govco-footer {
  padding: 1rem 1.5rem;
  background-color: #f5f7fb;
  border-top: 1px solid #e0e0e0;
}

/* ========================================
   ALERTAS GOV.CO
   ======================================== */

.alert-govco {
  padding: 1rem 1.5rem;
  border-radius: 4px;
  border-left: 4px solid;
  margin-bottom: 1rem;
}

.alert-govco-info {
  background-color: #E8F0FE;
  border-color: #3366CC;
  color: #004884;
}

.alert-govco-success {
  background-color: #E8F5E9;
  border-color: #069169;
  color: #057a58;
}

.alert-govco-warning {
  background-color: #FFF8E1;
  border-color: #FFAB00;
  color: #856404;
}

.alert-govco-danger {
  background-color: #FFEBEE;
  border-color: #A80521;
  color: #8a041b;
}

/* ========================================
   FORMULARIOS GOV.CO
   ======================================== */

.form-group-govco {
  margin-bottom: 1.5rem;
}

.label-govco {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.label-govco span[aria-required="true"]::after {
  content: " *";
  color: #A80521;
}

.input-govco {
  width: 100%;
  padding: 0.75rem 1rem;
  font-size: 1rem;
  line-height: 1.5;
  color: #333;
  background-color: white;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.input-govco:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.15);
}

.input-govco:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

.input-govco.is-invalid {
  border-color: #A80521;
}

.input-govco.is-valid {
  border-color: #069169;
}

.error-message-govco {
  display: block;
  margin-top: 0.5rem;
  font-size: 0.85rem;
  color: #A80521;
}

.help-text-govco {
  display: block;
  margin-top: 0.5rem;
  font-size: 0.85rem;
  color: #666;
}

/* ========================================
   TABLAS GOV.CO
   ======================================== */

.table-govco {
  width: 100%;
  border-collapse: collapse;
  background: white;
}

.table-govco th,
.table-govco td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #e0e0e0;
}

.table-govco th {
  background-color: #E8F0FE;
  color: #004884;
  font-weight: 600;
}

.table-govco tbody tr:hover {
  background-color: #f5f7fb;
}

/* ========================================
   BADGES GOV.CO
   ======================================== */

.badge-govco {
  display: inline-flex;
  align-items: center;
  padding: 4px 12px;
  font-size: 0.75rem;
  font-weight: 600;
  line-height: 1.5;
  border-radius: 20px;
}

.badge-govco-primary {
  background-color: #E8F0FE;
  color: #3366CC;
}

.badge-govco-success {
  background-color: #E8F5E9;
  color: #069169;
}

.badge-govco-warning {
  background-color: #FFF8E1;
  color: #856404;
}

.badge-govco-danger {
  background-color: #FFEBEE;
  color: #A80521;
}

/* ========================================
   TRANSICIONES
   ======================================== */

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* ========================================
   ALTO CONTRASTE
   ======================================== */

body.high-contrast {
  background-color: #000;
  color: #fff;
}

body.high-contrast .app-body {
  background-color: #000;
}

body.high-contrast .card-govco {
  background-color: #000;
  border: 2px solid #fff;
}

body.high-contrast .input-govco {
  background-color: #000;
  border-color: #fff;
  color: #fff;
}

body.high-contrast a {
  color: #FFFF00;
}

/* ========================================
   RESPONSIVE
   ======================================== */

@media (max-width: 991.98px) {
  .app-body.with-sidebar {
    flex-direction: column;
  }

  .h1-tipografia-govco {
    font-size: 2rem;
  }

  .h2-tipografia-govco {
    font-size: 1.75rem;
  }

  .h3-tipografia-govco {
    font-size: 1.5rem;
  }
}

@media (max-width: 575.98px) {
  html {
    font-size: 14px;
  }

  .btn-govco {
    padding: 10px 20px;
    font-size: 0.9rem;
  }
}

/* ========================================
   UTILIDADES
   ======================================== */

.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.container-govco {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.text-center {
  text-align: center;
}

.mt-1 { margin-top: 0.5rem; }
.mt-2 { margin-top: 1rem; }
.mt-3 { margin-top: 1.5rem; }
.mt-4 { margin-top: 2rem; }

.mb-1 { margin-bottom: 0.5rem; }
.mb-2 { margin-bottom: 1rem; }
.mb-3 { margin-bottom: 1.5rem; }
.mb-4 { margin-bottom: 2rem; }

.p-1 { padding: 0.5rem; }
.p-2 { padding: 1rem; }
.p-3 { padding: 1.5rem; }
.p-4 { padding: 2rem; }
</style>
