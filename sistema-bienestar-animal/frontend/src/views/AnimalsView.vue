<template>
  <main class="animals-view">
    <div class="view-header">
      <h1 class="h2-tipografia-govco">Gestión de animales</h1>
      
      <!-- TABS DE NAVEGACIÓN -->
      <div class="tabs-nav">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="currentTab = tab.id"
          :class="['tab-btn', { active: currentTab === tab.id }]"
        >
          <span>{{ tab.icon }}</span>
          {{ tab.label }}
        </button>
      </div>
    </div>

    <!-- CONTENIDO SEGÚN TAB ACTIVO -->
    <component :is="currentComponent" />
  </main>
</template>

<script setup>
import { ref, computed } from 'vue';

import AnimalForm from '../components/animals/AnimalForm.vue';

import AnimalSearch from '../components/animals/AnimalSearch.vue';
import AnimalNeuturingForm from '../components/animals/AnimalNeuturingForm.vue';

const currentTab = ref('register');

const tabs = [
  { id: 'register', label: 'Registrar animal', icon: '➕', component: AnimalForm },
  { id: 'search', label: 'Búsqueda avanzada', icon: '🔍', component: AnimalSearch },
  { id: 'neutering', label: 'Registrar esterilización', icon: '🐾', component: AnimalNeuturingForm }
];

const currentComponent = computed(() => {
  return tabs.find(t => t.id === currentTab.value)?.component;
});
</script>

<style scoped>
.animals-view {
  max-width: 1400px;
  margin: 0 auto;
  padding: 1.5rem;
}

.view-header {
  margin-bottom: 2rem;
}

.tabs-nav {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
  border-bottom: 2px solid #E0E0E0;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 1.5rem;
  background: none;
  border: none;
  border-bottom: 3px solid transparent;
  color: #737373;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.tab-btn:hover {
  color: #3366CC;
  background: #F6F8F9;
}

.tab-btn.active {
  color: #3366CC;
  border-bottom-color: #3366CC;
}

@media (max-width: 768px) {
  .tabs-nav {
    flex-wrap: wrap;
  }
  
  .tab-btn {
    flex: 1 1 45%;
    justify-content: center;
  }
}
</style>