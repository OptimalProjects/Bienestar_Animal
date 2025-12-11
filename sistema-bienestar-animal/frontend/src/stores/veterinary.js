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
  const historialClinico = ref(null);
  const alertas = ref([]);
  const recordatoriosVacunas = ref([]);
  const medicamentos = ref([]);
  const alertasStockBajo = ref([]);
  const desparasitaciones = ref([]);
  const examenes = ref([]);
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
  const totalAlertas = computed(() => alertas.value.length);
  const totalRecordatorios = computed(() => recordatoriosVacunas.value.length);
  const tieneAlertasStock = computed(() => alertasStockBajo.value.length > 0);

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
    console.log('📡 veterinaryStore.fetchVeterinarios: Starting...');
    try {
      const response = await veterinaryService.getVeterinarios();
      console.log('✅ veterinaryStore.fetchVeterinarios response:', response);
      // La respuesta puede venir en response.data o directamente en response
      veterinarios.value = response.data || response || [];
      console.log('✅ veterinaryStore.veterinarios set to:', veterinarios.value);
      return veterinarios.value;
    } catch (err) {
      console.error('❌ Error al cargar veterinarios:', err);
      veterinarios.value = [];
      return [];
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

  // Actions - Historial Clinico
  async function fetchHistorialClinico(animalId) {
    loading.value = true;
    error.value = null;
    console.log('📡 veterinaryStore.fetchHistorialClinico: Starting...', animalId);

    try {
      const response = await veterinaryService.getHistorialClinico(animalId);
      console.log('✅ veterinaryStore.fetchHistorialClinico response:', response);

      // Manejar diferentes estructuras de respuesta
      // Puede venir como: response.data.data, response.data, o response
      const data = response?.data?.data || response?.data || response;
      console.log('✅ Historial clínico data:', data);

      historialClinico.value = data;
      return data;
    } catch (err) {
      console.error('❌ Error al cargar historial clinico:', err);
      error.value = err.response?.data?.message || 'Error al cargar historial clinico';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Actions - Alertas y Recordatorios
  async function fetchAlertas() {
    try {
      const response = await veterinaryService.getAlertas();
      alertas.value = response.data || [];
      return alertas.value;
    } catch (err) {
      console.error('Error al cargar alertas:', err);
    }
  }

  async function fetchRecordatoriosVacunas(params = {}) {
    try {
      const response = await veterinaryService.getRecordatoriosVacunas(params);
      recordatoriosVacunas.value = response.data || [];
      return recordatoriosVacunas.value;
    } catch (err) {
      console.error('Error al cargar recordatorios:', err);
    }
  }

  async function marcarRecordatorioEnviado(recordatorioId) {
    try {
      await veterinaryService.marcarRecordatorioEnviado(recordatorioId);
      // Actualizar lista local
      const index = recordatoriosVacunas.value.findIndex(r => r.id === recordatorioId);
      if (index !== -1) {
        recordatoriosVacunas.value[index].enviado = true;
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al marcar recordatorio';
      throw err;
    }
  }

  // Actions - Certificados
  async function generarCertificadoVacunacion(animalId) {
    loading.value = true;

    try {
      const response = await veterinaryService.generarCertificadoVacunacion(animalId);
      // Crear blob y descargar
      const blob = new Blob([response.data], { type: 'application/pdf' });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `certificado_vacunacion_${animalId}.pdf`;
      link.click();
      window.URL.revokeObjectURL(url);
      return true;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al generar certificado';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function generarCertificadoSalud(animalId) {
    loading.value = true;

    try {
      const response = await veterinaryService.generarCertificadoSalud(animalId);
      const blob = new Blob([response.data], { type: 'application/pdf' });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `certificado_salud_${animalId}.pdf`;
      link.click();
      window.URL.revokeObjectURL(url);
      return true;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al generar certificado';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function generarCarnetVacunacion(animalId) {
    loading.value = true;

    try {
      const response = await veterinaryService.generarCarnetVacunacion(animalId);
      const blob = new Blob([response.data], { type: 'application/pdf' });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `carnet_vacunacion_${animalId}.pdf`;
      link.click();
      window.URL.revokeObjectURL(url);
      return true;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al generar carnet';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Actions - Inventario / Medicamentos
  async function fetchMedicamentos(params = {}) {
    try {
      const response = await veterinaryService.getMedicamentos(params);
      medicamentos.value = response.data || [];
      return medicamentos.value;
    } catch (err) {
      console.error('Error al cargar medicamentos:', err);
    }
  }

  async function verificarStock(medicamentoId) {
    try {
      const response = await veterinaryService.verificarStock(medicamentoId);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al verificar stock';
      throw err;
    }
  }

  async function prescribirMedicamento(data) {
    loading.value = true;

    try {
      const response = await veterinaryService.prescribirMedicamento(data);
      // Actualizar stock local si es necesario
      await fetchAlertasStockBajo();
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al prescribir medicamento';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function fetchAlertasStockBajo() {
    try {
      const response = await veterinaryService.getAlertasStockBajo();
      alertasStockBajo.value = response.data || [];
      return alertasStockBajo.value;
    } catch (err) {
      console.error('Error al cargar alertas de stock:', err);
    }
  }

  // Actions - Desparasitaciones
  async function fetchDesparasitacionesAnimal(animalId) {
    try {
      const response = await veterinaryService.getDesparasitacionesAnimal(animalId);
      desparasitaciones.value = response.data || [];
      return desparasitaciones.value;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar desparasitaciones';
      throw err;
    }
  }

  async function crearDesparasitacion(data) {
    loading.value = true;

    try {
      const response = await veterinaryService.createDesparasitacion(data);
      desparasitaciones.value.unshift(response.data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al registrar desparasitacion';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Actions - Examenes de Laboratorio
  async function fetchExamenesAnimal(animalId) {
    try {
      const response = await veterinaryService.getExamenesAnimal(animalId);
      examenes.value = response.data || [];
      return examenes.value;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar examenes';
      throw err;
    }
  }

  async function crearExamen(data) {
    loading.value = true;

    try {
      const response = await veterinaryService.createExamen(data);
      examenes.value.unshift(response.data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al registrar examen';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function actualizarResultadosExamen(id, data) {
    loading.value = true;

    try {
      const response = await veterinaryService.updateExamenResultados(id, data);
      const index = examenes.value.findIndex(e => e.id === id);
      if (index !== -1) {
        examenes.value[index] = response.data;
      }
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar resultados';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Actions - Dashboard
  async function fetchDashboard() {
    loading.value = true;

    try {
      const response = await veterinaryService.getDashboard();
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar dashboard';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Utilidades
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

  function clearError() {
    error.value = null;
  }

  function resetState() {
    consultas.value = [];
    currentConsulta.value = null;
    historialClinico.value = null;
    alertas.value = [];
    recordatoriosVacunas.value = [];
    error.value = null;
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
    historialClinico,
    alertas,
    recordatoriosVacunas,
    medicamentos,
    alertasStockBajo,
    desparasitaciones,
    examenes,
    pagination,
    filters,
    estadisticas,
    loading,
    error,
    // Getters
    totalConsultasHoy,
    totalPendientes,
    totalAlertas,
    totalRecordatorios,
    tieneAlertasStock,
    // Actions - Consultas
    fetchConsultas,
    fetchConsultasHoy,
    fetchConsultasPendientes,
    fetchConsulta,
    crearConsulta,
    // Actions - Veterinarios
    fetchVeterinarios,
    // Actions - Vacunas
    fetchTiposVacuna,
    fetchVacunasAnimal,
    crearVacuna,
    // Actions - Cirugias
    fetchProcedimientos,
    fetchCirugiasAnimal,
    crearCirugia,
    // Actions - Historial Clinico
    fetchHistorialClinico,
    // Actions - Alertas y Recordatorios
    fetchAlertas,
    fetchRecordatoriosVacunas,
    marcarRecordatorioEnviado,
    // Actions - Certificados
    generarCertificadoVacunacion,
    generarCertificadoSalud,
    generarCarnetVacunacion,
    // Actions - Inventario / Medicamentos
    fetchMedicamentos,
    verificarStock,
    prescribirMedicamento,
    fetchAlertasStockBajo,
    // Actions - Desparasitaciones
    fetchDesparasitacionesAnimal,
    crearDesparasitacion,
    // Actions - Examenes
    fetchExamenesAnimal,
    crearExamen,
    actualizarResultadosExamen,
    // Actions - Dashboard / Estadisticas
    fetchEstadisticas,
    fetchDashboard,
    // Utilidades
    setFilters,
    clearFilters,
    setPage,
    clearError,
    resetState,
  };
});

export default useVeterinaryStore;
