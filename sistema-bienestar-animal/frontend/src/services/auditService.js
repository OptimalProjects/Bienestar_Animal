import api from './api';

export const auditService = {
  async getEventos(params = {}) {
    const response = await api.get('/auditoria', { params });
    return response.data;
  },

  async getAcciones() {
    const response = await api.get('/auditoria/acciones');
    return response.data;
  },

  async getUsuarios() {
    const response = await api.get('/auditoria/usuarios');
    return response.data;
  },
};

export default auditService;
