/**
 * Animal Service
 * Servicio para gestionar animales
 */

import api from './api';

export const animalService = {
  /**
   * Obtener lista de animales con filtros y paginacion
   */
  async getAnimals(params = {}) {
    const response = await api.get('/animals', { params });
    return response.data;
  },

  /**
   * Obtener catalogo de adopcion (publico)
   */
  async getCatalogoAdopcion(params = {}) {
    const response = await api.get('/animals/catalogo-adopcion', { params });
    return response.data;
  },

  /**
   * Obtener estadisticas de animales
   */
  async getStatistics() {
    const response = await api.get('/animals/statistics');
    return response.data;
  },

  /**
   * Obtener animal por ID
   */
  async getAnimal(id) {
    const response = await api.get(`/animals/${id}`);
    return response.data;
  },

  /**
   * Crear nuevo animal
   */
  async createAnimal(data) {
    const response = await api.post('/animals', data);
    return response.data;
  },

  /**
   * Actualizar animal
   */
  async updateAnimal(id, data) {
    const response = await api.put(`/animals/${id}`, data);
    return response.data;
  },

  /**
   * Eliminar animal
   */
  async deleteAnimal(id) {
    const response = await api.delete(`/animals/${id}`);
    return response.data;
  },

  /**
   * Obtener historial clinico de un animal
   */
  async getHistorialClinico(animalId) {
    const response = await api.get(`/animals/${animalId}/historial-clinico`);
    return response.data;
  },

  /**
   * Actualizar historial clinico
   */
  async updateHistorialClinico(animalId, data) {
    const response = await api.put(`/animals/${animalId}/historial-clinico`, data);
    return response.data;
  },

  /**
   * Registrar chip
   */
  async registrarChip(animalId, data) {
    const response = await api.post(`/animals/${animalId}/chip`, data);
    return response.data;
  },

  /**
   * Buscar animal por chip
   */
  async buscarPorChip(chip) {
    const response = await api.get(`/historial-clinico/buscar-chip/${chip}`);
    return response.data;
  },

  /**
   * Subir foto de animal
   */
  async uploadPhoto(animalId, file) {
    const formData = new FormData();
    formData.append('foto', file);

    const response = await api.post(`/animals/${animalId}/foto`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },
};

export default animalService;
