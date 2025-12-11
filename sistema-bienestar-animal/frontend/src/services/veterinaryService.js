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
    const response = await api.get('/vacunas/veterinarios');
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
};

export default veterinaryService;
