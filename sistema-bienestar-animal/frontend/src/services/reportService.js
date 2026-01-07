/**
 * Report Service
 * Servicio para gestionar reportes y dashboard
 */

import api from './api';

export const reportService = {
  /**
   * Obtener datos del dashboard principal
   * @param {Object} params - Parámetros opcionales
   * @param {string} params.fechaInicio - Fecha de inicio (YYYY-MM-DD)
   * @param {string} params.fechaFin - Fecha de fin (YYYY-MM-DD)
   */
  async getDashboard(params = {}) {
    const queryParams = {};
    if (params.fechaInicio) queryParams.fecha_inicio = params.fechaInicio;
    if (params.fechaFin) queryParams.fecha_fin = params.fechaFin;

    const response = await api.get('/reportes/dashboard', { params: queryParams });
    return response.data;
  },

  /**
   * Obtener indicadores KPI
   */
  async getIndicadores() {
    const response = await api.get('/reportes/indicadores');
    return response.data;
  },

  /**
   * Obtener estadisticas de adopciones
   */
  async getAdopcionesStats() {
    const response = await api.get('/adopciones/estadisticas');
    return response.data;
  },

  /**
   * Obtener denuncias recientes
   */
  async getDenunciasRecientes(limit = 5) {
    const response = await api.get('/denuncias', {
      params: {
        per_page: limit,
        order_by: 'created_at',
        order: 'desc'
      }
    });
    return response.data;
  },

  /**
   * Obtener alertas del sistema
   */
  async getAlertas() {
    const response = await api.get('/reportes/alertas');
    return response.data;
  },

  /**
   * Obtener estadisticas generales
   */
  async getEstadisticasGenerales() {
    const response = await api.get('/reportes/estadisticas');
    return response.data;
  },

  /**
   * Exportar reporte
   */
  async exportarReporte(tipo, params = {}) {
    const response = await api.get(`/reportes/exportar/${tipo}`, {
      params,
      responseType: 'blob',
    });
    return response.data;
  },
};

export default reportService;
