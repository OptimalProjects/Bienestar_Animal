<template>
  <section class="animal-search">
    <!-- FILTROS -->
    <div class="search-filters">
      <div class="filters-header">
        <h3 class="h5-tipografia-govco">Filtros de búsqueda</h3>
        <button 
          class="toggle-filters-btn"
          @click="filtersExpanded = !filtersExpanded"
        >
          {{ filtersExpanded ? '▼ Ocultar filtros' : '▶ Mostrar filtros' }}
        </button>
      </div>

      <div v-show="filtersExpanded" class="filters-grid">
        <!-- Microchip -->
        <InputGovCo
          id="filter-microchip"
          v-model="localFilters.microchip"
          label="Microchip/Código"
          type="text"
          placeholder="MC123456789"
        />

        <!-- Especie -->
        <DesplegableGovco
          id="filter-species"
          v-model="localFilters.species"
          label="Especie"
          :options="speciesOptions"
          placeholder="Todas"
          width="100%"
        />

        <!-- Raza -->
        <InputGovCo
          id="filter-breed"
          v-model="localFilters.breed"
          label="Raza"
          type="text"
          placeholder="Cualquier raza"
        />

        <!-- Color -->
        <InputGovCo
          id="filter-color"
          v-model="localFilters.color"
          label="Color"
          type="text"
          placeholder="Ej: Negro"
        />

        <!-- Sexo -->
        <DesplegableGovco
          id="filter-sex"
          v-model="localFilters.sex"
          label="Sexo"
          :options="sexOptions"
          placeholder="Todos"
          width="100%"
        />

        <!-- Estado -->
        <DesplegableGovco
          id="filter-status"
          v-model="localFilters.status"
          label="Estado"
          :options="statusOptions"
          placeholder="Todos"
          width="100%"
        />

        <!-- Fecha desde -->
        <CalendarioGovco
          id="filter-date-from"
          input-id="filter-date-from-input"
          v-model="localFilters.dateFrom"
          label="Fecha rescate desde"
          placeholder="Seleccione una fecha"
          width="100%"
        />

        <!-- Fecha hasta -->
        <CalendarioGovco
          id="filter-date-to"
          input-id="filter-date-to-input"
          v-model="localFilters.dateTo"
          label="Fecha rescate hasta"
          placeholder="Seleccione una fecha"
          width="100%"
        />

        <!-- Esterilizado -->
        <div class="checkbox-filter">
          <input
            id="filter-neutered"
            v-model="localFilters.onlyNeutered"
            type="checkbox"
          />
          <label for="filter-neutered">Solo animales esterilizados</label>
        </div>
      </div>

      <div v-show="filtersExpanded" class="filters-actions">
        <ButtonGovCo
          type="button"
          variant="outline"
          label="Limpiar filtros"
          width="auto"
          height="44px"
          @click="handleClearFilters"
        />
        <ButtonGovCo
          type="button"
          variant="fill"
          label="Aplicar filtros"
          width="auto"
          height="44px"
          :disabled="loading"
          @click="handleSearch"
        />
      </div>
    </div>

    <!-- RESULTADOS -->
    <div class="results">
      <div class="results-header">
        <h3 class="h5-tipografia-govco">Animales</h3>
        <div class="results-header-actions">
          <p class="results-meta" v-if="!loading">
            Mostrando {{ animals.length }} de {{ total }} resultados
          </p>
          <ButtonGovCo
            v-if="hasActiveFilters"
            type="button"
            variant="outline"
            label="Ver todos"
            width="auto"
            height="36px"
            @click="handleViewAll"
          />
        </div>
      </div>

      <div v-if="loading" class="results-state">
        <div class="loading-spinner"></div>
        Cargando animales…
      </div>
      <div v-else-if="error" class="results-state error">
        <strong>Error:</strong> {{ error }}
      </div>
      <div v-else-if="animals.length === 0" class="results-state">
        No hay animales para los filtros seleccionados.
      </div>

      <div v-else class="cards-grid">
        <AnimalCard
          v-for="animal in animals"
          :key="animal.id || animal.uuid || animal.codigo_unico || animal.numero_chip"
          :animal="animal"
          @click="openDetail(animal)"
        />
      </div>

      <div v-if="!loading && lastPage > 1" class="pagination">
        <ButtonGovCo
          type="button"
          variant="outline"
          label="Anterior"
          width="auto"
          height="40px"
          :disabled="currentPage <= 1"
          @click="goToPage(currentPage - 1)"
        />
        <span class="pagination-info">
          Página {{ currentPage }} de {{ lastPage }}
        </span>
        <ButtonGovCo
          type="button"
          variant="outline"
          label="Siguiente"
          width="auto"
          height="40px"
          :disabled="currentPage >= lastPage"
          @click="goToPage(currentPage + 1)"
        />
      </div>
    </div>

    <!-- DETALLE -->
    <AnimalDetail
      v-if="selectedAnimal"
      :animal="selectedAnimal"
      @close="selectedAnimal = null"
      @updated="refreshAfterUpdate"
    />
  </section>
</template>

<script setup>
import { reactive, watch, onMounted, computed, ref } from 'vue';
import InputGovCo from '../common/InputGovCo.vue';
import DesplegableGovco from '../common/DesplegableGovco.vue';
import CalendarioGovco from '../common/CalendarioGovco.vue';
import ButtonGovCo from '../common/ButtonGovCo.vue';
import AnimalCard from './AnimalCard.vue';
import AnimalDetail from './AnimalDetail.vue';

import { useAnimalsStore } from '@/stores/animals';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      microchip: '',
      species: '',
      breed: '',
      color: '',
      sex: '',
      status: '',
      dateFrom: '',
      dateTo: '',
      onlyNeutered: false
    })
  }
});

const emit = defineEmits(['update:modelValue', 'search', 'clear']);

const animalsStore = useAnimalsStore();

const selectedAnimal = ref(null);
const filtersExpanded = ref(true);
const isSearching = ref(false);

const localFilters = reactive({ ...props.modelValue });

const speciesOptions = [
  { value: '', text: 'Todas' },
  { value: 'canino', text: 'Canino' },
  { value: 'felino', text: 'Felino' },
  { value: 'equino', text: 'Equino' },
  { value: 'otro', text: 'Otro' }
];

const sexOptions = [
  { value: '', text: 'Todos' },
  { value: 'macho', text: 'Macho' },
  { value: 'hembra', text: 'Hembra' },
  { value: 'desconocido', text: 'Desconocido' }
];

const statusOptions = [
  { value: '', text: 'Todos' },
  { value: 'en_calle', text: 'En calle' },
  { value: 'en_refugio', text: 'En refugio' },
  { value: 'en_adopcion', text: 'En adopción' },
  { value: 'adoptado', text: 'Adoptado' },
  { value: 'en_tratamiento', text: 'En tratamiento' },
  { value: 'fallecido', text: 'Fallecido' }
];

// Store bindings
const animals = computed(() => animalsStore.animales);
const loading = computed(() => animalsStore.loading);
const error = computed(() => animalsStore.error);
const currentPage = computed(() => animalsStore.pagination.currentPage);
const lastPage = computed(() => animalsStore.pagination.lastPage);
const total = computed(() => animalsStore.pagination.total);

// Detectar si hay filtros activos
const hasActiveFilters = computed(() => {
  return !!(
    localFilters.microchip ||
    localFilters.species ||
    localFilters.breed ||
    localFilters.color ||
    localFilters.sex ||
    localFilters.status ||
    localFilters.dateFrom ||
    localFilters.dateTo ||
    localFilters.onlyNeutered
  );
});

// Sync local -> parent v-model
watch(localFilters, (newValue) => {
  emit('update:modelValue', { ...newValue });
}, { deep: true });

watch(() => props.modelValue, (newValue) => {
  Object.assign(localFilters, newValue || {});
}, { deep: true });

onMounted(async () => {
  // Carga inicial: mostrar todos los animales sin filtros
  await loadInitialData();
});

async function loadInitialData() {
  try {
    animalsStore.clearFilters();
    await animalsStore.fetchAnimals();
  } catch (e) {
    console.error('Error al cargar datos iniciales:', e);
  }
}

function buildFiltersForAPI() {
  const filters = {};

  // Solo agregar filtros si tienen valor
  if (localFilters.microchip?.trim()) {
    filters.search = localFilters.microchip.trim();
  }
  
  if (localFilters.species) {
    filters.especie = localFilters.species;
  }
  
  if (localFilters.breed?.trim()) {
    if (filters.search) {
      filters.search += ' ' + localFilters.breed.trim();
    } else {
      filters.search = localFilters.breed.trim();
    }
  }
  
  if (localFilters.color?.trim()) {
    if (filters.search) {
      filters.search += ' ' + localFilters.color.trim();
    } else {
      filters.search = localFilters.color.trim();
    }
  }
  
  if (localFilters.sex) {
    filters.sexo = localFilters.sex;
  }
  
  if (localFilters.status) {
    filters.estado = localFilters.status;
  }
  
  if (localFilters.dateFrom) {
    filters.fecha_desde = localFilters.dateFrom;
  }
  
  if (localFilters.dateTo) {
    filters.fecha_hasta = localFilters.dateTo;
  }
  
  if (localFilters.onlyNeutered) {
    filters.esterilizado = 1;
  }

  return filters;
}

async function handleSearch() {
  if (isSearching.value) return;
  
  isSearching.value = true;
  
  try {
    const filters = buildFiltersForAPI();
    
    console.log('🔍 Aplicando filtros:', filters);
    
    // Aplicar filtros y resetear a página 1
    animalsStore.setFilters(filters);
    animalsStore.setPage(1);
    
    // Realizar búsqueda
    await animalsStore.fetchAnimals();
    
    emit('search');
  } catch (e) {
    console.error('Error al buscar:', e);
  } finally {
    isSearching.value = false;
  }
}

async function handleClearFilters() {
  // Reset UI
  Object.assign(localFilters, {
    microchip: '',
    species: '',
    breed: '',
    color: '',
    sex: '',
    status: '',
    dateFrom: '',
    dateTo: '',
    onlyNeutered: false
  });

  // Reset store y recargar
  animalsStore.clearFilters();
  animalsStore.setPage(1);
  
  try {
    await animalsStore.fetchAnimals();
    emit('clear');
  } catch (e) {
    console.error('Error al limpiar filtros:', e);
  }
}

async function handleViewAll() {
  await handleClearFilters();
}

async function goToPage(page) {
  if (page < 1 || page > lastPage.value) return;
  
  try {
    animalsStore.setPage(page);
    await animalsStore.fetchAnimals();
  } catch (e) {
    console.error('Error al cambiar de página:', e);
  }
}

function openDetail(animal) {
  selectedAnimal.value = animal;
}

async function refreshAfterUpdate(updatedAnimal) {
  selectedAnimal.value = null;
  
  // Recargar la página actual manteniendo los filtros
  try {
    await animalsStore.fetchAnimals();
  } catch (e) {
    console.error('Error al refrescar:', e);
  }
}
</script>

<style scoped>
.animal-search {
  display: grid;
  gap: 1.5rem;
}

/* Filtros */
.search-filters {
  background: #ffffff;
  border: 1px solid #e6e9ef;
  border-radius: 8px;
  padding: 1.5rem;
}

.filters-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.toggle-filters-btn {
  background: #f5f7fb;
  border: 1px solid #e6e9ef;
  border-radius: 6px;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
  font-weight: 600;
  color: #3366cc;
  cursor: pointer;
  transition: all 0.2s;
}

.toggle-filters-btn:hover {
  background: #e8f0fe;
  border-color: #3366cc;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  column-gap: 2rem;
  row-gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.checkbox-filter {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: #f5f7fb;
  border-radius: 6px;
  border: 1px solid #e6e9ef;
}

.checkbox-filter input[type="checkbox"] {
  width: 18px;
  height: 18px;
  accent-color: #3366cc;
  cursor: pointer;
}

.checkbox-filter label {
  cursor: pointer;
  user-select: none;
}

.filters-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

/* Resultados */
.results {
  background: #ffffff;
  border: 1px solid #e6e9ef;
  border-radius: 8px;
  padding: 1.5rem;
}

.results-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
}

.results-header-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.results-meta {
  margin: 0;
  color: #637381;
  font-size: 0.95rem;
  white-space: nowrap;
}

.results-state {
  padding: 1.25rem;
  border: 1px dashed #cfd6e4;
  border-radius: 8px;
  color: #637381;
  background: #fbfcfe;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
}

.results-state.error {
  border-color: #f1c0c0;
  color: #b42318;
  background: #fff7f7;
}

.loading-spinner {
  width: 20px;
  height: 20px;
  border: 3px solid #e6e9ef;
  border-top-color: #3366cc;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1.25rem;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  padding-top: 1.25rem;
  margin-top: 1.25rem;
  border-top: 1px solid #e6e9ef;
}

.pagination-info {
  color: #344054;
  font-size: 0.95rem;
  font-weight: 500;
}

@media (max-width: 992px) {
  .filters-grid,
  .cards-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  
  .results-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .results-header-actions {
    flex-direction: column;
    align-items: flex-start;
    width: 100%;
  }
}

@media (max-width: 576px) {
  .search-filters,
  .results {
    padding: 1rem;
  }

  .filters-grid,
  .cards-grid {
    grid-template-columns: 1fr;
  }

  .filters-actions {
    flex-direction: column;
    align-items: stretch;
  }
  
  .filters-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }
  
  .toggle-filters-btn {
    width: 100%;
  }
}
</style>