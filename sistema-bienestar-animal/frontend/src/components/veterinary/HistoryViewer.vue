<template>
  <section class="history-viewer">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Historial Clínico</h2>
      <p class="text2-tipografia-govco">Consulte el historial médico completo de un animal</p>
    </div>

    <!-- Búsqueda de animal -->
    <div class="search-section">
      <div class="search-grid">
        <div class="input-wrapper">
          <label for="searchInput">Buscar animal por código o nombre</label>
          <div class="search-input-group">
            <input
              type="text"
              id="searchInput"
              v-model="searchQuery"
              placeholder="AN-2025-00001 o nombre del animal"
              @keyup.enter="buscarAnimal"
              class="input-govco"
            />
            <button 
              type="button" 
              @click="buscarAnimal" 
              class="btn-search"
              :disabled="searching"
            >
              🔍 Buscar
            </button>
          </div>
          <span v-if="searchError" class="error-text">{{ searchError }}</span>
        </div>

        <div v-if="animalesEncontrados.length > 0" class="results-dropdown">
          <p class="results-label">Seleccione un animal:</p>
          <div 
            v-for="animal in animalesEncontrados" 
            :key="animal.id"
            @click="seleccionarAnimal(animal)"
            class="result-item"
          >
            <div class="result-info">
              <strong>{{ animal.nombre || 'Sin nombre' }}</strong>
              <span class="result-code">{{ animal.codigo_unico }}</span>
            </div>
            <div class="result-meta">
              {{ animal.especie }} • {{ animal.raza }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Historial del animal seleccionado -->
    <div v-if="animalSeleccionado && !loading" class="animal-info-card">
      <div class="animal-header">
        <div class="animal-avatar">
          <img 
            :src="animalSeleccionado.foto_url || 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiB2aWV3Qm94PSIwIDAgNDAwIDMwMCI+PHJlY3Qgd2lkdGg9IjQwMCIgaGVpZ2h0PSIzMDAiIGZpbGw9IiNlOWVjZWYiLz48ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyMDAsMTUwKSI+PGVsbGlwc2UgY3g9IjAiIGN5PSIyNSIgcng9IjM1IiByeT0iMzAiIGZpbGw9IiNhZGI1YmQiLz48ZWxsaXBzZSBjeD0iLTQ1IiBjeT0iLTEwIiByeD0iMTgiIHJ5PSIyMiIgZmlsbD0iI2FkYjViZCIvPjxlbGxpcHNlIGN4PSI0NSIgY3k9Ii0xMCIgcng9IjE4IiByeT0iMjIiIGZpbGw9IiNhZGI1YmQiLz48ZWxsaXBzZSBjeD0iLTI1IiBjeT0iLTQ1IiByeD0iMTUiIHJ5PSIxOCIgZmlsbD0iI2FkYjViZCIvPjxlbGxpcHNlIGN4PSIyNSIgY3k9Ii00NSIgcng9IjE1IiByeT0iMTgiIGZpbGw9IiNhZGI1YmQiLz48L2c+PC9zdmc+'" 
            :alt="animalSeleccionado.nombre"
          />
        </div>
        <div class="animal-details">
          <h3>{{ animalSeleccionado.nombre || 'Sin nombre' }}</h3>
          <p class="animal-meta">
            {{ animalSeleccionado.codigo_unico }} • 
            {{ animalSeleccionado.especie }} • 
            {{ animalSeleccionado.raza }}
          </p>
          <p v-if="animalSeleccionado.edad_formateada" class="animal-age">
            {{ animalSeleccionado.edad_formateada }}
          </p>
        </div>
      </div>
    </div>

    <!-- Mostrar historial -->
    <MedicalHistory 
      v-if="animalSeleccionado"
      :animal-id="animalSeleccionado.id"
    />

    <!-- Estado de carga -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Cargando historial...</p>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue';
import MedicalHistory from '../veterinary/MedicalHistory.vue';
import { useAnimalsStore } from '@/stores/animals';

const animalsStore = useAnimalsStore();

const searchQuery = ref('');
const searching = ref(false);
const searchError = ref('');
const animalesEncontrados = ref([]);
const animalSeleccionado = ref(null);
const loading = ref(false);

async function buscarAnimal() {
  if (!searchQuery.value.trim()) {
    searchError.value = 'Ingrese un código o nombre para buscar';
    return;
  }

  searching.value = true;
  searchError.value = '';
  animalesEncontrados.value = [];

  try {
    await animalsStore.fetchAnimals({ 
      search: searchQuery.value,
      per_page: 10 
    });

    const resultados = animalsStore.animales || [];

    if (resultados.length === 0) {
      searchError.value = 'No se encontraron animales con ese criterio';
    } else if (resultados.length === 1) {
      // Si solo hay un resultado, seleccionarlo automáticamente
      seleccionarAnimal(resultados[0]);
    } else {
      animalesEncontrados.value = resultados;
    }
  } catch (error) {
    console.error('Error buscando animal:', error);
    searchError.value = 'Error al buscar el animal';
  } finally {
    searching.value = false;
  }
}

function seleccionarAnimal(animal) {
  animalSeleccionado.value = animal;
  animalesEncontrados.value = [];
  searchQuery.value = animal.codigo_unico;
}
</script>

<style scoped>
.history-viewer {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  background: #f5f7fb;
}

.form-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #3366CC;
}

.search-section {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.search-grid {
  display: grid;
  gap: 1rem;
}

.input-wrapper {
  display: flex;
  flex-direction: column;
}

.input-wrapper label {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.search-input-group {
  display: flex;
  gap: 0.75rem;
}

.input-govco {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  height: 44px;
}

.input-govco:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.1);
}

.btn-search {
  padding: 0 1.5rem;
  background: #3366CC;
  color: white;
  border: none;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.btn-search:hover:not(:disabled) {
  background: #004884;
  transform: translateY(-1px);
}

.btn-search:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.error-text {
  color: #b00020;
  font-size: 0.85rem;
  margin-top: 0.5rem;
}

.results-dropdown {
  margin-top: 1rem;
  border: 1px solid #E0E0E0;
  border-radius: 6px;
  overflow: hidden;
  background: white;
}

.results-label {
  padding: 0.75rem 1rem;
  background: #F5F7FB;
  color: #666;
  font-size: 0.9rem;
  font-weight: 500;
  margin: 0;
  border-bottom: 1px solid #E0E0E0;
}

.result-item {
  padding: 1rem;
  cursor: pointer;
  transition: background 0.2s;
  border-bottom: 1px solid #F0F0F0;
}

.result-item:last-child {
  border-bottom: none;
}

.result-item:hover {
  background: #F5F7FB;
}

.result-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.25rem;
}

.result-code {
  color: #3366CC;
  font-size: 0.9rem;
  font-family: monospace;
}

.result-meta {
  color: #666;
  font-size: 0.85rem;
}

.animal-info-card {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.animal-header {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

.animal-avatar {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  background: linear-gradient(135deg, #3366cc 0%, #004884 100%);
}

.animal-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.animal-details h3 {
  margin: 0 0 0.5rem 0;
  color: #3366CC;
  font-size: 1.5rem;
}

.animal-meta {
  margin: 0;
  color: #666;
  font-size: 0.95rem;
}

.animal-age {
  margin: 0.5rem 0 0 0;
  color: #888;
  font-size: 0.9rem;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3366cc;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .history-viewer {
    padding: 1rem;
  }

  .search-input-group {
    flex-direction: column;
  }

  .btn-search {
    width: 100%;
  }

  .animal-header {
    flex-direction: column;
    text-align: center;
  }
}
</style>