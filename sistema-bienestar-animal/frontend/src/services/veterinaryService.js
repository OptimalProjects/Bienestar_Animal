/**
 * Veterinary Service
 * Servicio para gestionar consultas, vacunas y cirugias
 */

import api from './api';

export const veterinaryService = {
  // ============================================
  // VETERINARIOS
  // ============================================

  /**
   * Obtener lista de veterinarios
   */
  async getVeterinarios() {
    console.log('📡 veterinaryService.getVeterinarios: Fetching...');
    const response = await api.get('/vacunas/veterinarios');
    console.log('✅ veterinaryService.getVeterinarios response:', response.data);
    return response.data;
  },

  // ============================================
  // CONSULTAS
  // ============================================

  /**
   * Obtener lista de consultas
   */
  async getConsultas(params = {}) {
    const response = await api.get('/consultas', { params });
    return response.data;
  },

  /**
   * Obtener consultas del dia
   */
  async getConsultasHoy() {
    const response = await api.get('/consultas/hoy');
    return response.data;
  },

  /**
   * Obtener consultas pendientes
   */
  async getConsultasPendientes() {
    const response = await api.get('/consultas/pendientes');
    return response.data;
  },

  /**
   * Obtener consulta por ID
   */
  async getConsulta(id) {
    const response = await api.get(`/consultas/${id}`);
    return response.data;
  },

  /**
   * Registrar nueva consulta
   */
  async createConsulta(data) {
    const response = await api.post('/consultas', data);
    return response.data;
  },

  /**
   * Obtener estadisticas de consultas
   */
  async getConsultasEstadisticas() {
    const response = await api.get('/consultas/estadisticas');
    return response.data;
  },

  // ============================================
  // VACUNAS
  // ============================================

  /**
   * Obtener tipos de vacunas
   */
  async getTiposVacuna() {
    const response = await api.get('/vacunas/tipos');
    return response.data;
  },

  /**
   * Obtener lista de vacunas
   */
  async getVacunas(params = {}) {
    const response = await api.get('/vacunas', { params });
    return response.data;
  },

  /**
   * Obtener vacunas de un animal
   */
  async getVacunasAnimal(animalId) {
    const response = await api.get(`/vacunas/animal/${animalId}`);
    return response.data;
  },

  /**
   * Obtener vacunas proximas
   */
  async getVacunasProximas(dias = 30) {
    const response = await api.get('/vacunas/proximas', { params: { dias } });
    return response.data;
  },

  /**
   * Registrar vacuna
   */
  async createVacuna(data) {
    const response = await api.post('/vacunas', data);
    return response.data;
  },

  /**
   * Obtener vacuna por ID
   */
  async getVacuna(id) {
    const response = await api.get(`/vacunas/${id}`);
    return response.data;
  },

  // ============================================
  // CIRUGIAS
  // ============================================

  /**
   * Obtener procedimientos disponibles
   */
  async getProcedimientos() {
    const response = await api.get('/cirugias/procedimientos');
    return response.data;
  },

  /**
   * Obtener lista de cirugias
   */
  async getCirugias(params = {}) {
    const response = await api.get('/cirugias', { params });
    return response.data;
  },

  /**
   * Obtener cirugias de un animal
   */
  async getCirugiasAnimal(animalId) {
    const response = await api.get(`/cirugias/animal/${animalId}`);
    return response.data;
  },

  /**
   * Registrar cirugia
   */
  async createCirugia(data) {
    const response = await api.post('/cirugias', data);
    return response.data;
  },

  /**
   * Obtener cirugia por ID
   */
  async getCirugia(id) {
    const response = await api.get(`/cirugias/${id}`);
    return response.data;
  },

  /**
   * Actualizar cirugia
   */
  async updateCirugia(id, data) {
    const response = await api.put(`/cirugias/${id}`, data);
    return response.data;
  },

  /**
   * Obtener estadisticas de cirugias
   */
  async getCirugiasEstadisticas() {
    const response = await api.get('/cirugias/estadisticas');
    return response.data;
  },

  // ============================================
  // HISTORIAL CLÍNICO
  // ============================================

  /**
   * Obtener historial clinico completo de un animal
   */
  async getHistorialCompleto(animalId) {
    const response = await api.get(`/animals/${animalId}/historial-clinico`);
    return response.data;
  },

  /**
   * Obtener historial clinico por ID
   */
  async getHistorialById(historialId) {
    const response = await api.get(`/historiales-clinicos/${historialId}`);
    return response.data;
  },

  // ============================================
  // TRATAMIENTOS
  // ============================================

  /**
   * Obtener tratamientos de una consulta
   */
  async getTratamientosConsulta(consultaId) {
    const response = await api.get(`/consultas/${consultaId}/tratamientos`);
    return response.data;
  },

  /**
   * Registrar tratamiento
   */
  async createTratamiento(data) {
    const response = await api.post('/tratamientos', data);
    return response.data;
  },

  /**
   * Actualizar estado de tratamiento
   */
  async updateTratamiento(id, data) {
    const response = await api.put(`/tratamientos/${id}`, data);
    return response.data;
  },

  // ============================================
  // ALERTAS Y RECORDATORIOS
  // ============================================

  /**
   * Obtener alertas veterinarias activas
   */
  async getAlertas() {
    const response = await api.get('/veterinaria/alertas');
    return response.data;
  },

  /**
   * Obtener recordatorios de vacunas pendientes
   */
  async getRecordatoriosVacunas(params = {}) {
    const response = await api.get('/vacunas/recordatorios', { params });
    return response.data;
  },

  /**
   * Marcar recordatorio como enviado
   */
  async marcarRecordatorioEnviado(recordatorioId) {
    const response = await api.put(`/vacunas/recordatorios/${recordatorioId}/enviado`);
    return response.data;
  },

  // ============================================
  // CERTIFICADOS
  // ============================================

  /**
   * Generar certificado de vacunacion
   */
  async generarCertificadoVacunacion(animalId) {
    const response = await api.get(`/certificados/vacunacion/${animalId}`, {
      responseType: 'blob'
    });
    return response;
  },

  /**
   * Generar certificado de salud
   */
  async generarCertificadoSalud(animalId) {
    const response = await api.get(`/certificados/salud/${animalId}`, {
      responseType: 'blob'
    });
    return response;
  },

  /**
   * Generar carnet de vacunacion
   */
  async generarCarnetVacunacion(animalId) {
    const response = await api.get(`/certificados/carnet/${animalId}`, {
      responseType: 'blob'
    });
    return response;
  },

  // ============================================
  // INVENTARIO / MEDICAMENTOS
  // ============================================

  /**
   * Verificar stock de medicamento
   */
  async verificarStock(medicamentoId) {
    const response = await api.get(`/inventario/verificar-stock/${medicamentoId}`);
    return response.data;
  },

  /**
   * Obtener medicamentos disponibles
   */
  async getMedicamentos(params = {}) {
    const response = await api.get('/medicamentos', { params });
    return response.data;
  },

  /**
   * Prescribir medicamento (descuenta del inventario)
   */
  async prescribirMedicamento(data) {
    const response = await api.post('/medicamentos/prescribir', data);
    return response.data;
  },

  /**
   * Obtener alertas de stock bajo
   */
  async getAlertasStockBajo() {
    const response = await api.get('/inventario/alertas-stock');
    return response.data;
  },

  // ============================================
  // DESPARASITACIONES
  // ============================================

  /**
   * Obtener desparasitaciones de un animal
   */
  async getDesparasitacionesAnimal(animalId) {
    const response = await api.get(`/desparasitaciones/animal/${animalId}`);
    return response.data;
  },

  /**
   * Registrar desparasitacion
   */
  async createDesparasitacion(data) {
    const response = await api.post('/desparasitaciones', data);
    return response.data;
  },

  // ============================================
  // EXÁMENES DE LABORATORIO
  // ============================================

  /**
   * Obtener examenes de un animal
   */
  async getExamenesAnimal(animalId) {
    const response = await api.get(`/examenes/animal/${animalId}`);
    return response.data;
  },

  /**
   * Registrar examen de laboratorio
   */
  async createExamen(data) {
    const response = await api.post('/examenes', data);
    return response.data;
  },

  /**
   * Actualizar resultados de examen
   */
  async updateExamenResultados(id, data) {
    const response = await api.put(`/examenes/${id}/resultados`, data);
    return response.data;
  },

  // ============================================
  // ESTADÍSTICAS GENERALES
  // ============================================

  /**
   * Obtener estadisticas generales de veterinaria
   */
  async getEstadisticasGenerales() {
    const response = await api.get('/veterinaria/estadisticas');
    return response.data;
  },

  /**
   * Obtener dashboard veterinario
   */
  async getDashboard() {
    const response = await api.get('/veterinaria/dashboard');
    return response.data;
  },
};

export default veterinaryService;
