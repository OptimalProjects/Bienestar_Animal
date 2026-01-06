/**
 * Adoptions Store
 * Estado de adopciones con Pinia
 */

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/services/api';

export const useAdoptionsStore = defineStore('adoptions', () => {
  // State
  const adopciones = ref([]);
  const currentAdopcion = ref(null);
  const pendientes = ref([]);
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  });
  const filters = ref({
    estado: null,
    busqueda: '',
  });
  const estadisticas = ref(null);
  const loading = ref(false);
  const error = ref(null);

  // Getters
  const adopcionesPendientes = computed(() =>
    adopciones.value.filter(a => a.estado === 'pendiente')
  );

  const adopcionesAprobadas = computed(() =>
    adopciones.value.filter(a => a.estado === 'aprobada')
  );

  const totalPendientes = computed(() => pendientes.value.length);

  // Actions
  async function fetchAdopciones(params = {}) {
    loading.value = true;
    error.value = null;

    try {
      const queryParams = {
        page: pagination.value.currentPage,
        per_page: pagination.value.perPage,
        ...filters.value,
        ...params,
      };

      Object.keys(queryParams).forEach(key => {
        if (queryParams[key] === null || queryParams[key] === '') {
          delete queryParams[key];
        }
      });

      const response = await api.get('/adopciones', { params: queryParams });
      const data = response.data.data;

      adopciones.value = data.data || [];
      pagination.value = {
        currentPage: data.current_page,
        lastPage: data.last_page,
        perPage: data.per_page,
        total: data.total,
      };

      return data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar adopciones';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function fetchPendientes() {
    loading.value = true;

    try {
      const response = await api.get('/adopciones/pendientes');
      pendientes.value = response.data.data || [];
      return pendientes.value;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar pendientes';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function fetchAdopcion(id) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.get(`/adopciones/${id}`);
      currentAdopcion.value = response.data.data;
      return response.data.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar adopcion';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function crearSolicitud(data) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.post('/adopciones', data);
      return response.data.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear solicitud';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function evaluarSolicitud(id, data) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.put(`/adopciones/${id}/evaluar`, data);
      // Actualizar en la lista
      const index = adopciones.value.findIndex(a => a.id === id);
      if (index !== -1) {
        adopciones.value[index] = response.data.data;
      }
      // Remover de pendientes
      pendientes.value = pendientes.value.filter(a => a.id !== id);
      return response.data.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al evaluar solicitud';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function generarContrato(id) {
    try {
      const response = await api.get(`/adopciones/${id}/contrato`);
      return response.data.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al generar contrato';
      throw err;
    }
  }

  async function fetchEstadisticas() {
    try {
      const response = await api.get('/adopciones/estadisticas');
      // La API devuelve { success: true, data: {...} }
      estadisticas.value = response.data?.data || response.data;
      console.log('✅ adoptionsStore.estadisticas:', estadisticas.value);
      return estadisticas.value;
    } catch (err) {
      console.error('Error al cargar estadisticas de adopciones:', err);
    }
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters };
    pagination.value.currentPage = 1;
  }

  function clearFilters() {
    filters.value = {
      estado: null,
      busqueda: '',
    };
    pagination.value.currentPage = 1;
  }

  function setPage(page) {
    pagination.value.currentPage = page;
  }

  return {
    // State
    adopciones,
    currentAdopcion,
    pendientes,
    pagination,
    filters,
    estadisticas,
    loading,
    error,
    // Getters
    adopcionesPendientes,
    adopcionesAprobadas,
    totalPendientes,
    // Actions
    fetchAdopciones,
    fetchPendientes,
    fetchAdopcion,
    crearSolicitud,
    evaluarSolicitud,
    generarContrato,
    fetchEstadisticas,
    setFilters,
    clearFilters,
    setPage,
  };
});

export default useAdoptionsStore;
