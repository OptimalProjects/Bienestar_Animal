/**
 * Inventory Service
 * Servicio para gestionar inventario de medicamentos e insumos
 */

import api from './api';

export const inventoryService = {
  // ============================================
  // INVENTARIO GENERAL
  // ============================================

  /**
   * Obtener lista de inventario con filtros
   */
  async getInventario(params = {}) {
    try {
      const response = await api.get('/inventario', { params });
      return response.data?.data || [];
    } catch (error) {
      console.error('Error al obtener inventario:', error);
      throw error;
    }
  },

  /**
   * Crear nuevo item de inventario
   */
  async crearInventario(data) {
    try {
      const response = await api.post('/inventario', data);
      return response.data?.data || null;
    } catch (error) {
      console.error('Error al crear inventario:', error);
      throw error;
    }
  },

  /**
   * Actualizar item de inventario
   */
  async actualizarInventario(id, data) {
    try {
      const response = await api.put(`/inventario/${id}`, data);
      return response.data?.data || null;
    } catch (error) {
      console.error('Error al actualizar inventario:', error);
      throw error;
    }
  },

  /**
   * Eliminar item de inventario
   */
  async eliminarInventario(id) {
    try {
      const response = await api.delete(`/inventario/${id}`);
      return response.data?.data || null;
    } catch (error) {
      console.error('Error al eliminar inventario:', error);
      throw error;
    }
  },

  // ============================================
  // MEDICAMENTOS E INSUMOS
  // ============================================

  /**
   * Obtener lista de insumos
   */
  async getInsumos(params = {}) {
    try {
      const response = await api.get('/inventario/insumos', { params });
      // Puede retornar con paginación o array directo
      const data = response.data?.data;
      
      if (Array.isArray(data)) {
        return data;
      }
      
      // Si es paginado, extraer el array de datos
      if (data && data.data && Array.isArray(data.data)) {
        return data.data;
      }
      
      return [];
    } catch (error) {
      console.error('Error al obtener insumos:', error);
      throw error;
    }
  },

  // ============================================
  // MOVIMIENTOS DE INVENTARIO
  // ============================================

  /**
   * Registrar entrada de inventario
   */
  async registrarEntrada(id, data) {
    try {
      const response = await api.post(`/inventario/${id}/entrada`, data);
      return response.data?.data || null;
    } catch (error) {
      console.error('Error al registrar entrada:', error);
      throw error;
    }
  },

  /**
   * Registrar salida de inventario
   */
  async registrarSalida(id, data) {
    try {
      const response = await api.post(`/inventario/${id}/salida`, data);
      return response.data?.data || null;
    } catch (error) {
      console.error('Error al registrar salida:', error);
      throw error;
    }
  },

  /**
   * Verificar disponibilidad de stock
   */
  async verificarStock(params = {}) {
    try {
      const response = await api.get('/inventario/verificar-stock', { params });
      return response.data?.data || { disponible: false };
    } catch (error) {
      console.error('Error al verificar stock:', error);
      throw error;
    }
  },

  // ============================================
  // ALERTAS E INFORMES
  // ============================================

  /**
   * Obtener items con stock bajo
   */
  async getStockBajo() {
    try {
      const response = await api.get('/inventario/stock-bajo');
      const data = response.data?.data || response.data || {};
      return {
        inventario: data.inventario || [],
        insumos: data.insumos || [],
        productos_farmaceuticos: data.productos_farmaceuticos || []
      };
    } catch (error) {
      console.error('Error al obtener items con stock bajo:', error);
      throw error;
    }
  },

  /**
   * Obtener items próximos a vencer
   */
  async getProximosVencer(dias = 30) {
    try {
      const response = await api.get('/inventario/proximos-vencer', {
        params: { dias }
      });
      const data = response.data?.data || response.data || {};
      return {
        insumos: data.insumos || [],
        productos_farmaceuticos: data.productos_farmaceuticos || []
      };
    } catch (error) {
      console.error('Error al obtener próximos a vencer:', error);
      throw error;
    }
  },

  /**
   * Obtener items vencidos
   */
  async getVencidos() {
    try {
      const response = await api.get('/inventario/vencidos');
      const data = response.data?.data || response.data || {};
      return {
        insumos: data.insumos || [],
        productos_farmaceuticos: data.productos_farmaceuticos || []
      };
    } catch (error) {
      console.error('Error al obtener vencidos:', error);
      throw error;
    }
  },

  /**
   * Obtener estadísticas de inventario
   */
  async getEstadisticas() {
    try {
      const response = await api.get('/inventario/estadisticas');
      return response.data?.data || {
        inventario: {},
        insumos: {},
        medicamentos: {}
      };
    } catch (error) {
      console.error('Error al obtener estadísticas:', error);
      throw error;
    }
  }
};

export default inventoryService;

