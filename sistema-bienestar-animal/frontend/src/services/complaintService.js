/**
 * Complaint Service
 * Servicio para gestionar denuncias y rescates
 */

import api from './api';

export const complaintService = {
  // ============================================
  // DENUNCIAS
  // ============================================

  /**
   * Registrar denuncia (publico)
   */
  async createDenuncia(data) {
    const response = await api.post('/denuncias', data);
    return response.data;
  },

  /**
   * Consultar estado de denuncia por ticket (publico)
   */
  async consultarTicket(ticket) {
    const response = await api.get(`/denuncias/consultar/${ticket}`);
    return response.data;
  },

  /**
   * Obtener lista de denuncias (requiere auth)
   */
  async getDenuncias(params = {}) {
    const response = await api.get('/denuncias', { params });
    return response.data;
  },

  /**
   * Obtener denuncia por ID
   */
  async getDenuncia(id) {
    const response = await api.get(`/denuncias/${id}`);
    return response.data;
  },

  /**
   * Obtener denuncias urgentes sin asignar
   */
  async getDenunciasUrgentes() {
    const response = await api.get('/denuncias/urgentes');
    return response.data;
  },

  /**
   * Obtener mis denuncias asignadas
   */
  async getMisAsignaciones() {
    const response = await api.get('/denuncias/mis-asignaciones');
    return response.data;
  },

  /**
   * Asignar denuncia a funcionario
   */
  async asignarDenuncia(id, funcionarioId) {
    const response = await api.put(`/denuncias/${id}/asignar`, {
      funcionario_id: funcionarioId,
    });
    return response.data;
  },

  /**
   * Actualizar estado de denuncia
   */
  async actualizarEstado(id, data) {
    const response = await api.put(`/denuncias/${id}/estado`, data);
    return response.data;
  },

  /**
   * Obtener estadisticas de denuncias
   */
  async getEstadisticas() {
    const response = await api.get('/denuncias/estadisticas');
    return response.data;
  },

  /**
   * Obtener mapa de calor por comuna
   */
  async getMapaCalor() {
    const response = await api.get('/denuncias/mapa-calor');
    return response.data;
  },

  // ============================================
  // RESCATES
  // ============================================

  /**
   * Obtener lista de rescates
   */
  async getRescates(params = {}) {
    const response = await api.get('/rescates', { params });
    return response.data;
  },

  /**
   * Registrar rescate
   */
  async createRescate(data) {
    const response = await api.post('/rescates', data);
    return response.data;
  },

  /**
   * Obtener rescate por ID
   */
  async getRescate(id) {
    const response = await api.get(`/rescates/${id}`);
    return response.data;
  },

  /**
   * Actualizar rescate
   */
  async updateRescate(id, data) {
    const response = await api.put(`/rescates/${id}`, data);
    return response.data;
  },

  /**
   * Vincular animal rescatado
   */
  async vincularAnimal(rescateId, animalId) {
    const response = await api.put(`/rescates/${rescateId}/vincular-animal`, {
      animal_id: animalId,
    });
    return response.data;
  },

  /**
   * Obtener estadisticas de rescates
   */
  async getRescatesEstadisticas() {
    const response = await api.get('/rescates/estadisticas');
    return response.data;
  },
};

export default complaintService;
