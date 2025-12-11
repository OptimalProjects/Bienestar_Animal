/**
 * Veterinary Store
 * Estado de veterinaria con Pinia
 */

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import veterinaryService from '@/services/veterinaryService';

export const useVeterinaryStore = defineStore('veterinary', () => {
  // State
  const consultas = ref([]);
  const currentConsulta = ref(null);
  const consultasHoy = ref([]);
  const consultasPendientes = ref([]);
  const vacunas = ref([]);
  const tiposVacuna = ref([]);
  const veterinarios = ref([]);
  const cirugias = ref([]);
  const procedimientos = ref([]);
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  });
  const filters = ref({
    veterinario_id: null,
    tipo_consulta: null,
    fecha_desde: null,
    fecha_hasta: null,
  });
  const estadisticas = ref(null);
  const loading = ref(false);
  const error = ref(null);

  // Getters
  const totalConsultasHoy = computed(() => consultasHoy.value.length);
  const totalPendientes = computed(() => consultasPendientes.value.length);

  // Actions - Consultas
  async function fetchConsultas(params = {}) {
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

      const response = await veterinaryService.getConsultas(queryParams);
      const data = response.data;

      consultas.value = data.data || [];
      pagination.value = {
        currentPage: data.current_page,
        lastPage: data.last_page,
        perPage: data.per_page,
        total: data.total,
      };

      return data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar consultas';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function fetchConsultasHoy() {
    try {
      const response = await veterinaryService.getConsultasHoy();
      consultasHoy.value = response.data || [];
      return consultasHoy.value;
    } catch (err) {
      console.error('Error al cargar consultas del dia:', err);
    }
  }

  async function fetchConsultasPendientes() {
    try {
      const response = await veterinaryService.getConsultasPendientes();
      consultasPendientes.value = response.data || [];
      return consultasPendientes.value;
    } catch (err) {
      console.error('Error al cargar consultas pendientes:', err);
    }
  }

  async function fetchConsulta(id) {
    loading.value = true;

    try {
      const response = await veterinaryService.getConsulta(id);
      currentConsulta.value = response.data;
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar consulta';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function crearConsulta(data) {
    loading.value = true;

    try {
      const response = await veterinaryService.createConsulta(data);
      consultas.value.unshift(response.data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear consulta';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Actions - Veterinarios
  async function fetchVeterinarios() {
    try {
      const response = await veterinaryService.getVeterinarios();
      veterinarios.value = response.data || [];
      return veterinarios.value;
    } catch (err) {
      console.error('Error al cargar veterinarios:', err);
    }
  }

  // Actions - Vacunas
  async function fetchTiposVacuna() {
    try {
      const response = await veterinaryService.getTiposVacuna();
      tiposVacuna.value = response.data || [];
      return tiposVacuna.value;
    } catch (err) {
      console.error('Error al cargar tipos de vacuna:', err);
    }
  }

  async function fetchVacunasAnimal(animalId) {
    try {
      const response = await veterinaryService.getVacunasAnimal(animalId);
      return response.data || [];
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar vacunas';
      throw err;
    }
  }

  async function crearVacuna(data) {
    loading.value = true;

    try {
      const response = await veterinaryService.createVacuna(data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al registrar vacuna';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Actions - Cirugias
  async function fetchProcedimientos() {
    try {
      const response = await veterinaryService.getProcedimientos();
      procedimientos.value = response.data || [];
      return procedimientos.value;
    } catch (err) {
      console.error('Error al cargar procedimientos:', err);
    }
  }

  async function fetchCirugiasAnimal(animalId) {
    try {
      const response = await veterinaryService.getCirugiasAnimal(animalId);
      return response.data || [];
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar cirugias';
      throw err;
    }
  }

  async function crearCirugia(data) {
    loading.value = true;

    try {
      const response = await veterinaryService.createCirugia(data);
      cirugias.value.unshift(response.data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al registrar cirugia';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Estadisticas
  async function fetchEstadisticas() {
    try {
      const response = await veterinaryService.getConsultasEstadisticas();
      estadisticas.value = response.data;
      return response.data;
    } catch (err) {
      console.error('Error al cargar estadisticas:', err);
    }
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters };
    pagination.value.currentPage = 1;
  }

  function clearFilters() {
    filters.value = {
      veterinario_id: null,
      tipo_consulta: null,
      fecha_desde: null,
      fecha_hasta: null,
    };
    pagination.value.currentPage = 1;
  }

  function setPage(page) {
    pagination.value.currentPage = page;
  }

  return {
    // State
    consultas,
    currentConsulta,
    consultasHoy,
    consultasPendientes,
    vacunas,
    tiposVacuna,
    veterinarios,
    cirugias,
    procedimientos,
    pagination,
    filters,
    estadisticas,
    loading,
    error,
    // Getters
    totalConsultasHoy,
    totalPendientes,
    // Actions
    fetchConsultas,
    fetchConsultasHoy,
    fetchConsultasPendientes,
    fetchConsulta,
    crearConsulta,
    fetchVeterinarios,
    fetchTiposVacuna,
    fetchVacunasAnimal,
    crearVacuna,
    fetchProcedimientos,
    fetchCirugiasAnimal,
    crearCirugia,
    fetchEstadisticas,
    setFilters,
    clearFilters,
    setPage,
  };
});

export default useVeterinaryStore;
