<!-- AnimalSearch.vue -->
<template>
  <section class="animal-search">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Búsqueda de animales</h2>
      <p class="text2-tipografia-govco">Use los filtros para encontrar animales registrados</p>
    </div>

    <!-- FILTROS -->
    <SearchFilters
      v-model="filters"
      @search="handleSearch"
      @clear="clearFilters"
    />

    <!-- RESULTADOS -->
    <SearchResults
      v-if="hasSearched"
      :results="filteredResults"
      :is-loading="isSearching"
      @select="selectAnimal"
    />

    <!-- MODAL DE DETALLES -->
    <AnimalDetailModal
      v-if="selectedAnimal"
      :animal="selectedAnimal"
      @close="closeModal"
    />
  </section>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { useAnimalsStore } from '@/stores/animals';
import SearchFilters from '../common/SearchFilters.vue';
import SearchResults from '../common/SearchResults.vue';
import AnimalDetailModal from './AnimalDetail.vue';

const animalsStore = useAnimalsStore();

const filters = reactive({
  microchip: '',
  species: '',
  breed: '',
  color: '',
  sex: '',
  status: '',
  location: '',
  dateFrom: '',
  dateTo: '',
  onlyNeutered: false
});

const hasSearched = ref(false);
const selectedAnimal = ref(null);

// Usar estado del store
const isSearching = computed(() => animalsStore.loading);
const filteredResults = computed(() => animalsStore.animales || []);

async function handleSearch() {
  hasSearched.value = true;

  // Construir parametros de busqueda
  const params = {};
  if (filters.microchip) params.chip = filters.microchip;
  if (filters.species) params.especie = filters.species;
  if (filters.breed) params.raza = filters.breed;
  if (filters.color) params.color = filters.color;
  if (filters.sex) params.sexo = filters.sex;
  if (filters.status) params.estado = filters.status;
  if (filters.dateFrom) params.fecha_desde = filters.dateFrom;
  if (filters.dateTo) params.fecha_hasta = filters.dateTo;

  await animalsStore.fetchAnimals(params);
}

function clearFilters() {
  Object.keys(filters).forEach(key => {
    filters[key] = typeof filters[key] === 'boolean' ? false : '';
  });
  hasSearched.value = false;
  animalsStore.clearAnimals();
}

function selectAnimal(animal) {
  selectedAnimal.value = animal;
}

function closeModal() {
  selectedAnimal.value = null;
}
</script>

<style scoped>
.animal-search {
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

@media (max-width: 576px) {
  .animal-search {
    padding: 1rem;
  }
}
</style>




