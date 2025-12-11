/**
 * Animals Store
 * Estado de animales con Pinia
 */

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import animalService from '@/services/animalService';

export const useAnimalsStore = defineStore('animals', () => {
  // State
  const animals = ref([]);
  const currentAnimal = ref(null);
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  });
  const filters = ref({
    especie: null,
    estado: null,
    estado_salud: null,
    sexo: null,
    busqueda: '',
  });
  const statistics = ref(null);
  const loading = ref(false);
  const error = ref(null);

  // Getters
  const animales = computed(() => animals.value);

  const animalesEnRefugio = computed(() =>
    animals.value.filter(a => a.estado === 'en_refugio')
  );

  const animalesEnAdopcion = computed(() =>
    animals.value.filter(a => a.estado === 'en_adopcion')
  );

  const totalAnimales = computed(() => pagination.value.total);

  // Actions
  async function fetchAnimals(params = {}) {
    console.log('📡 animalsStore.fetchAnimals: Starting...', params);
    loading.value = true;
    error.value = null;

    try {
      const queryParams = {
        page: pagination.value.currentPage,
        per_page: pagination.value.perPage,
        ...filters.value,
        ...params,
      };

      // Limpiar parametros vacios
      Object.keys(queryParams).forEach(key => {
        if (queryParams[key] === null || queryParams[key] === '') {
          delete queryParams[key];
        }
      });

      console.log('📡 animalsStore.fetchAnimals: Query params:', queryParams);
      const response = await animalService.getAnimals(queryParams);
      console.log('✅ animalsStore.fetchAnimals: Response:', response);

      // Manejar diferentes estructuras de respuesta
      const data = response.data || response;
      const animalsList = data.data || data || [];

      animals.value = Array.isArray(animalsList) ? animalsList : [];
      console.log('✅ animalsStore.animals set to:', animals.value.length, 'items');

      if (data.current_page !== undefined) {
        pagination.value = {
          currentPage: data.current_page,
          lastPage: data.last_page,
          perPage: data.per_page,
          total: data.total,
        };
      }

      return animals.value;
    } catch (err) {
      console.error('❌ animalsStore.fetchAnimals error:', err);
      error.value = err.response?.data?.message || 'Error al cargar animales';
      animals.value = [];
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function fetchAnimal(id) {
    loading.value = true;
    error.value = null;

    try {
      const response = await animalService.getAnimal(id);
      currentAnimal.value = response.data;
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar animal';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function createAnimal(data) {
    loading.value = true;
    error.value = null;

    try {
      const response = await animalService.createAnimal(data);
      // Agregar a la lista
      animals.value.unshift(response.data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear animal';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function updateAnimal(id, data) {
    loading.value = true;
    error.value = null;

    try {
      const response = await animalService.updateAnimal(id, data);
      // Actualizar en la lista
      const index = animals.value.findIndex(a => a.id === id);
      if (index !== -1) {
        animals.value[index] = response.data;
      }
      if (currentAnimal.value?.id === id) {
        currentAnimal.value = response.data;
      }
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar animal';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function deleteAnimal(id) {
    loading.value = true;
    error.value = null;

    try {
      await animalService.deleteAnimal(id);
      // Remover de la lista
      animals.value = animals.value.filter(a => a.id !== id);
      if (currentAnimal.value?.id === id) {
        currentAnimal.value = null;
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar animal';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function fetchStatistics() {
    try {
      const response = await animalService.getStatistics();
      statistics.value = response.data;
      return response.data;
    } catch (err) {
      console.error('Error al cargar estadisticas:', err);
    }
  }

  async function fetchHistorialClinico(animalId) {
    try {
      const response = await animalService.getHistorialClinico(animalId);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar historial';
      throw err;
    }
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters };
    pagination.value.currentPage = 1;
  }

  function clearFilters() {
    filters.value = {
      especie: null,
      estado: null,
      estado_salud: null,
      sexo: null,
      busqueda: '',
    };
    pagination.value.currentPage = 1;
  }

  function setPage(page) {
    pagination.value.currentPage = page;
  }

  function clearCurrentAnimal() {
    currentAnimal.value = null;
  }

  function clearAnimals() {
    animals.value = [];
    pagination.value = {
      currentPage: 1,
      lastPage: 1,
      perPage: 15,
      total: 0,
    };
  }

  return {
    // State
    animals,
    currentAnimal,
    pagination,
    filters,
    statistics,
    loading,
    error,
    // Getters
    animales,
    animalesEnRefugio,
    animalesEnAdopcion,
    totalAnimales,
    // Actions
    fetchAnimals,
    fetchAnimal,
    createAnimal,
    updateAnimal,
    deleteAnimal,
    fetchStatistics,
    fetchHistorialClinico,
    setFilters,
    clearFilters,
    setPage,
    clearCurrentAnimal,
    clearAnimals,
  };
});

export default useAnimalsStore;
