/**
 * Complaints Store
 * Estado de denuncias y rescates con Pinia
 */

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import complaintService from '@/services/complaintService';

export const useComplaintsStore = defineStore('complaints', () => {
  // State
  const denuncias = ref([]);
  const currentDenuncia = ref(null);
  const urgentes = ref([]);
  const misAsignaciones = ref([]);
  const rescates = ref([]);
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  });
  const filters = ref({
    estado: null,
    prioridad: null,
    tipo_denuncia: null,
    busqueda: '',
  });
  const estadisticas = ref(null);
  const mapaCalor = ref(null);
  const loading = ref(false);
  const error = ref(null);

  // Getters
  const denunciasUrgentes = computed(() =>
    denuncias.value.filter(d => d.prioridad === 'urgente')
  );

  const totalUrgentes = computed(() => urgentes.value.length);

  const totalPendientes = computed(() =>
    denuncias.value.filter(d => ['recibida', 'en_proceso'].includes(d.estado)).length
  );

  // Actions
  async function fetchDenuncias(params = {}) {
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

      const response = await complaintService.getDenuncias(queryParams);
      const data = response.data;

      denuncias.value = data.data || [];
      pagination.value = {
        currentPage: data.current_page,
        lastPage: data.last_page,
        perPage: data.per_page,
        total: data.total,
      };

      return data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar denuncias';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function fetchDenuncia(id) {
    loading.value = true;
    error.value = null;

    try {
      const response = await complaintService.getDenuncia(id);
      currentDenuncia.value = response.data;
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar denuncia';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function fetchUrgentes() {
    try {
      const response = await complaintService.getDenunciasUrgentes();
      urgentes.value = response.data || [];
      return urgentes.value;
    } catch (err) {
      console.error('Error al cargar urgentes:', err);
    }
  }

  async function fetchMisAsignaciones() {
    try {
      const response = await complaintService.getMisAsignaciones();
      misAsignaciones.value = response.data || [];
      return misAsignaciones.value;
    } catch (err) {
      console.error('Error al cargar asignaciones:', err);
    }
  }

  async function crearDenuncia(data) {
    loading.value = true;
    error.value = null;

    try {
      const response = await complaintService.createDenuncia(data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear denuncia';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function consultarTicket(ticket) {
    loading.value = true;
    error.value = null;

    try {
      const response = await complaintService.consultarTicket(ticket);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'No se encontro la denuncia';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function asignarDenuncia(id, funcionarioId) {
    loading.value = true;

    try {
      const response = await complaintService.asignarDenuncia(id, funcionarioId);
      // Actualizar en la lista
      const index = denuncias.value.findIndex(d => d.id === id);
      if (index !== -1) {
        denuncias.value[index] = response.data;
      }
      // Remover de urgentes
      urgentes.value = urgentes.value.filter(d => d.id !== id);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al asignar denuncia';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function actualizarEstado(id, data) {
    loading.value = true;

    try {
      const response = await complaintService.actualizarEstado(id, data);
      // Actualizar en la lista
      const index = denuncias.value.findIndex(d => d.id === id);
      if (index !== -1) {
        denuncias.value[index] = response.data;
      }
      if (currentDenuncia.value?.id === id) {
        currentDenuncia.value = response.data;
      }
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar estado';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function fetchEstadisticas() {
    try {
      const response = await complaintService.getEstadisticas();
      estadisticas.value = response.data;
      return response.data;
    } catch (err) {
      console.error('Error al cargar estadisticas:', err);
    }
  }

  async function fetchMapaCalor() {
    try {
      const response = await complaintService.getMapaCalor();
      mapaCalor.value = response.data;
      return response.data;
    } catch (err) {
      console.error('Error al cargar mapa de calor:', err);
    }
  }

  // Rescates
  async function fetchRescates(params = {}) {
    try {
      const response = await complaintService.getRescates(params);
      rescates.value = response.data.data || [];
      return response.data;
    } catch (err) {
      console.error('Error al cargar rescates:', err);
    }
  }

  async function crearRescate(data) {
    loading.value = true;

    try {
      const response = await complaintService.createRescate(data);
      // Agregar el nuevo rescate a la lista
      const nuevoRescate = response.data?.data || response.data;
      rescates.value.unshift(nuevoRescate);
      return nuevoRescate;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear rescate';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function actualizarRescate(id, data) {
    loading.value = true;

    try {
      const response = await complaintService.updateRescate(id, data);
      const rescateActualizado = response.data?.data || response.data;
      // Actualizar en la lista
      const index = rescates.value.findIndex(r => r.id === id);
      if (index !== -1) {
        rescates.value[index] = rescateActualizado;
      }
      return rescateActualizado;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar rescate';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters };
    pagination.value.currentPage = 1;
  }

  function clearFilters() {
    filters.value = {
      estado: null,
      prioridad: null,
      tipo_denuncia: null,
      busqueda: '',
    };
    pagination.value.currentPage = 1;
  }

  function setPage(page) {
    pagination.value.currentPage = page;
  }

  return {
    // State
    denuncias,
    currentDenuncia,
    urgentes,
    misAsignaciones,
    rescates,
    pagination,
    filters,
    estadisticas,
    mapaCalor,
    loading,
    error,
    // Getters
    denunciasUrgentes,
    totalUrgentes,
    totalPendientes,
    // Actions
    fetchDenuncias,
    fetchDenuncia,
    fetchUrgentes,
    fetchMisAsignaciones,
    crearDenuncia,
    consultarTicket,
    asignarDenuncia,
    actualizarEstado,
    fetchEstadisticas,
    fetchMapaCalor,
    fetchRescates,
    crearRescate,
    actualizarRescate,
    setFilters,
    clearFilters,
    setPage,
  };
});

export default useComplaintsStore;
