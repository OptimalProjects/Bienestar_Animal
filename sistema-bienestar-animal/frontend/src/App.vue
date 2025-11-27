<!-- src/App.vue -->
<template>
  
    
    
      
      <main class="app-main">
        <!-- Aquí irían tus vistas/rutas -->
        <!-- Por ahora muestras las vistas directamente -->
        <AnimalsView />
        <VeterinaryView />
        <ComplaintsView />
        <AdminView />
      </main>
    
  
</template>

<script setup>
import Navbar from './components/common/Navbar.vue';
import Sidebar from './components/common/Sidebar.vue';
import AnimalsView from './views/AnimalsView.vue';
import VeterinaryView from './views/VeterinaryView.vue';
import ComplaintsView from './views/ComplaintsView.vue';
import AdminView from './views/AdminView.vue';
</script>

<style>
/* Estilos globales */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Times New Roman', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background: #f5f7fb;
}

.app-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.app-content {
  display: flex;
  flex: 1;
}

.app-main {
  flex: 1;
  overflow-x: hidden;
}

/* Responsive */
@media (max-width: 992px) {
  .app-content {
    flex-direction: column;
  }
}
</style>