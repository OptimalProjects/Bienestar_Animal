/**
 * User Service
 * Servicio para gestionar usuarios y roles del sistema
 */

import api from './api';

export const userService = {
  /**
   * Obtener lista de usuarios con filtros y paginacion
   * @param {Object} params - { busqueda, rol_id, activo, page, per_page }
   */
  async getUsers(params = {}) {
    const response = await api.get('/usuarios', { params });
    return response.data;
  },

  /**
   * Obtener usuario por ID con roles y permisos
   */
  async getUser(id) {
    const response = await api.get(`/usuarios/${id}`);
    return response.data;
  },

  /**
   * Crear nuevo usuario
   */
  async createUser(data) {
    const response = await api.post('/usuarios', data);
    return response.data;
  },

  /**
   * Actualizar usuario
   */
  async updateUser(id, data) {
    const response = await api.put(`/usuarios/${id}`, data);
    return response.data;
  },

  /**
   * Cambiar contrasena de usuario
   */
  async changePassword(id, data) {
    const response = await api.put(`/usuarios/${id}/password`, data);
    return response.data;
  },

  /**
   * Activar/Desactivar usuario
   */
  async toggleActive(id) {
    const response = await api.put(`/usuarios/${id}/toggle-activo`);
    return response.data;
  },

  /**
   * Eliminar usuario (soft delete)
   */
  async deleteUser(id) {
    const response = await api.delete(`/usuarios/${id}`);
    return response.data;
  },

  /**
   * Obtener roles disponibles con permisos
   */
  async getRoles() {
    const response = await api.get('/usuarios/roles');
    return response.data;
  },
};

export default userService;
