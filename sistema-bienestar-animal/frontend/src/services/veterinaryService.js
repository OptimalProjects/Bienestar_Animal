/**
 * Veterinary Service
 * Servicio para gestionar consultas, vacunas y cirugias
 */

import api from './api';

// Helper para extraer datos de respuesta envuelta
const extractData = (response, defaultValue = null) => {
  return response.data?.data !== undefined ? response.data.data : defaultValue;
};

export const veterinaryService = {
  // ============================================
  // VETERINARIOS
  // ============================================

  /**
   * Obtener lista de veterinarios
   */
  async getVeterinarios() {
    try {
      const response = await api.get('/vacunas/veterinarios');
      return extractData(response, []);
    } catch (error) {
      console.error('Error al obtener veterinarios:', error);
      throw error;
    }
  },

  // ============================================
  // CONSULTAS
  // ============================================

  /**
   * Obtener lista de consultas
   */
  async getConsultas(params = {}) {
    try {
      const response = await api.get('/consultas', { params });
      return extractData(response, []);
    } catch (error) {
      console.error('Error al obtener consultas:', error);
      throw error;
    }
  },

  /**
   * Obtener consultas del dia
   */
  async getConsultasHoy() {
    try {
      const response = await api.get('/consultas/hoy');
      return extractData(response, []);
    } catch (error) {
      console.error('Error al obtener consultas hoy:', error);
      throw error;
    }
  },

  /**
   * Obtener consultas pendientes
   */
  async getConsultasPendientes() {
    try {
      const response = await api.get('/consultas/pendientes');
      return extractData(response, []);
    } catch (error) {
      console.error('Error al obtener consultas pendientes:', error);
      throw error;
    }
  },

  /**
   * Obtener consulta por ID
   */
  async getConsulta(id) {
    try {
      const response = await api.get(`/consultas/${id}`);
      return extractData(response, {});
    } catch (error) {
      console.error('Error al obtener consulta:', error);
      throw error;
    }
  },

  /**
   * Registrar nueva consulta
   */
  async createConsulta(data) {
    try {
      const response = await api.post('/consultas', data);
      return extractData(response, {});
    } catch (error) {
      console.error('Error al crear consulta:', error);
      throw error;
    }
  },

  /**
   * Obtener estadisticas de consultas
   */
  async getConsultasEstadisticas() {
    try {
      const response = await api.get('/consultas/estadisticas');
      return extractData(response, {});
    } catch (error) {
      console.error('Error al obtener estadísticas de consultas:', error);
      throw error;
    }
  },

  // ============================================
  // VACUNAS
  // ============================================

  /**
   * Obtener tipos de vacunas
   */
  async getTiposVacuna() {
    try {
      const response = await api.get('/vacunas/tipos');
      return extractData(response, []);
    } catch (error) {
      console.error('Error al obtener tipos de vacuna:', error);
      throw error;
    }
  },

  /**
   * Obtener lista de vacunas
   */
  async getVacunas(params = {}) {
    try {
      const response = await api.get('/vacunas', { params });
      return extractData(response, []);
    } catch (error) {
      console.error('Error al obtener vacunas:', error);
      throw error;
    }
  },

  /**
   * Obtener vacunas de un animal
   */
  async getVacunasAnimal(animalId) {
    try {
      const response = await api.get(`/vacunas/animal/${animalId}`);
      return extractData(response, []);
    } catch (error) {
      console.error('Error al obtener vacunas del animal:', error);
      throw error;
    }
  },

  /**
   * Obtener vacunas proximas
   */
  async getVacunasProximas(dias = 30) {
    try {
      const response = await api.get('/vacunas/proximas', { params: { dias } });
      return extractData(response, []);
    } catch (error) {
      console.error('Error al obtener vacunas próximas:', error);
      throw error;
    }
  },

  /**
   * Registrar vacuna
   */
  async createVacuna(data) {
    try {
      const response = await api.post('/vacunas', data);
      return extractData(response, {});
    } catch (error) {
      console.error('Error al crear vacuna:', error);
      throw error;
    }
  },

  /**
   * Obtener vacuna por ID
   */
  async getVacuna(id) {
    try {
      const response = await api.get(`/vacunas/${id}`);
      return extractData(response, {});
    } catch (error) {
      console.error('Error al obtener vacuna:', error);
      throw error;
    }
  },

  // ============================================
  // CIRUGIAS
  // ============================================

  /**
   * Obtener procedimientos disponibles
   */
  async getProcedimientos() {
    try {
      const response = await api.get('/cirugias/procedimientos');
      return extractData(response, []);
    } catch (error) {
      console.error('Error al obtener procedimientos:', error);
      throw error;
    }
  },

  /**
   * Obtener lista de cirugias
   */
  async getCirugias(params = {}) {
    try {
      const response = await api.get('/cirugias', { params });
      return extractData(response, []);
    } catch (error) {
      console.error('Error al obtener cirugías:', error);
      throw error;
    }
  },

  /**
   * Obtener cirugias de un animal
   */
  async getCirugiasAnimal(animalId) {
    try {
      const response = await api.get(`/cirugias/animal/${animalId}`);
      return extractData(response, []);
    } catch (error) {
      console.error('Error al obtener cirugías del animal:', error);
      throw error;
    }
  },

  /**
   * Registrar cirugia
   */
  async createCirugia(data) {
    try {
      const response = await api.post('/cirugias', data);
      return extractData(response, {});
    } catch (error) {
      console.error('Error al crear cirugía:', error);
      throw error;
    }
  },

  /**
   * Obtener cirugia por ID
   */
  async getCirugia(id) {
    try {
      const response = await api.get(`/cirugias/${id}`);
      return extractData(response, {});
    } catch (error) {
      console.error('Error al obtener cirugía:', error);
      throw error;
    }
  },

  /**
   * Actualizar cirugia
   */
  async updateCirugia(id, data) {
    try {
      const response = await api.put(`/cirugias/${id}`, data);
      return extractData(response, {});
    } catch (error) {
      console.error('Error al actualizar cirugía:', error);
      throw error;
    }
  },

  /**
   * Obtener estadisticas de cirugias
   */
  async getCirugiasEstadisticas() {
    try {
      const response = await api.get('/cirugias/estadisticas');
      return extractData(response, {});
    } catch (error) {
      console.error('Error al obtener estadísticas de cirugías:', error);
      throw error;
    }
  },

  // ============================================
  // HISTORIAL CLÍNICO
  // ============================================

  /**
   * Obtener historial clinico completo de un animal
   */
  async getHistorialCompleto(animalId) {
    try {
      const response = await api.get(`/animals/${animalId}/historial-clinico`);
      return extractData(response, {});
    } catch (error) {
      console.error('Error al obtener historial clínico completo:', error);
      throw error;
    }
  },

  /**
   * Obtener historial clinico por ID
   */
  async getHistorialById(historialId) {
    try {
      const response = await api.get(`/historiales-clinicos/${historialId}`);
      return extractData(response, {});
    } catch (error) {
      console.error('Error al obtener historial clínico:', error);
      throw error;
    }
  },

  // ============================================
  // TRATAMIENTOS
  // ============================================

  /**
   * Obtener tratamientos de una consulta
   */
  async getTratamientosConsulta(consultaId) {
    try {
      const response = await api.get(`/consultas/${consultaId}/tratamientos`);
      return extractData(response, []);
    } catch (error) {
      console.error('Error al obtener tratamientos:', error);
      throw error;
    }
  },

  /**
   * Registrar tratamiento
   */
  async createTratamiento(data) {
    try {
      const response = await api.post('/tratamientos', data);
      return extractData(response, {});
    } catch (error) {
      console.error('Error al crear tratamiento:', error);
      throw error;
    }
  },

  /**
   * Actualizar estado de tratamiento
   */
  async updateTratamiento(id, data) {
    try {
      const response = await api.put(`/tratamientos/${id}`, data);
      return extractData(response, {});
    } catch (error) {
      console.error('Error al actualizar tratamiento:', error);
      throw error;
    }
  },

  // ============================================
  // ALERTAS Y RECORDATORIOS
  // ============================================

  /**
   * Obtener alertas veterinarias activas
   */
  async getAlertas() {
    try {
      const response = await api.get('/veterinaria/alertas');
      return extractData(response, []);
    } catch (error) {
      console.error('Error al obtener alertas veterinarias:', error);
      throw error;
    }
  },

  /**
   * Obtener recordatorios de vacunas pendientes
   */
  async getRecordatoriosVacunas(params = {}) {
    try {
      const response = await api.get('/vacunas/recordatorios', { params });
      return extractData(response, []);
    } catch (error) {
      console.error('Error al obtener recordatorios de vacunas:', error);
      throw error;
    }
  },

  /**
   * Marcar recordatorio como enviado
   */
  async marcarRecordatorioEnviado(recordatorioId) {
    try {
      const response = await api.put(`/vacunas/recordatorios/${recordatorioId}/enviado`);
      return extractData(response, {});
    } catch (error) {
      console.error('Error al marcar recordatorio como enviado:', error);
      throw error;
    }
  },

  // ============================================
  // CERTIFICADOS
  // ============================================

  /**
   * Generar certificado de vacunacion
   */
  async generarCertificadoVacunacion(animalId) {
    try {
      const response = await api.get(`/certificados/vacunacion/${animalId}`, {
        responseType: 'blob'
      });
      return response;
    } catch (error) {
      console.error('Error al generar certificado de vacunación:', error);
      throw error;
    }
  },

  /**
   * Generar certificado de salud
   */
  async generarCertificadoSalud(animalId) {
    try {
      const response = await api.get(`/certificados/salud/${animalId}`, {
        responseType: 'blob'
      });
      return response;
    } catch (error) {
      console.error('Error al generar certificado de salud:', error);
      throw error;
    }
  },

  /**
   * Generar carnet de vacunacion
   */
  async generarCarnetVacunacion(animalId) {
    try {
      const response = await api.get(`/certificados/carnet/${animalId}`, {
        responseType: 'blob'
      });
      return response;
    } catch (error) {
      console.error('Error al generar carnet de vacunación:', error);
      throw error;
    }
  },

  // ============================================
  // INVENTARIO / MEDICAMENTOS
  // ============================================

  /**
   * Verificar stock de medicamento
   */
  async verificarStock(medicamentoId) {
    try {
      const response = await api.get(`/inventario/verificar-stock/${medicamentoId}`);
      return extractData(response, { disponible: false });
    } catch (error) {
      console.error('Error al verificar stock:', error);
      throw error;
    }
  },

  /**
   * Obtener medicamentos disponibles
   */
  async getMedicamentos(params = {}) {
    try {
      const response = await api.get('/inventario/insumos', { params });
      const data = extractData(response, []);
      // Si vuelve paginado, extraer el array
      if (data.data && Array.isArray(data.data)) {
        return data.data;
      }
      return Array.isArray(data) ? data : [];
    } catch (error) {
      console.error('Error al obtener medicamentos:', error);
      throw error;
    }
  },

  /**
   * Prescribir medicamento (descuenta del inventario)
   */
  async prescribirMedicamento(data) {
    try {
      // data debe contener: { medicamentoId, cantidad, motivo }
      const response = await api.post(`/inventario/${data.medicamentoId}/salida`, {
        tipo: 'insumo',
        cantidad: data.cantidad,
        motivo: data.motivo || 'Prescripción médica'
      });
      return extractData(response, {});
    } catch (error) {
      console.error('Error al prescribir medicamento:', error);
      throw error;
    }
  },

  /**
   * Obtener alertas de stock bajo
   */
  async getAlertasStockBajo() {
    try {
      const response = await api.get('/inventario/stock-bajo');
      const data = extractData(response, {});
      return {
        inventario: data.inventario || [],
        insumos: data.insumos || [],
        productos_farmaceuticos: data.productos_farmaceuticos || []
      };
    } catch (error) {
      console.error('Error al obtener alertas de stock bajo:', error);
      throw error;
    }
  },

  // ============================================
  // DESPARASITACIONES
  // ============================================

  /**
   * Obtener desparasitaciones de un animal
   */
  async getDesparasitacionesAnimal(animalId) {
    try {
      const response = await api.get(`/desparasitaciones/animal/${animalId}`);
      return extractData(response, []);
    } catch (error) {
      console.error('Error al obtener desparasitaciones:', error);
      throw error;
    }
  },

  /**
   * Registrar desparasitacion
   */
  async createDesparasitacion(data) {
    try {
      const response = await api.post('/desparasitaciones', data);
      return extractData(response, {});
    } catch (error) {
      console.error('Error al registrar desparasitación:', error);
      throw error;
    }
  },

  // ============================================
  // EXÁMENES DE LABORATORIO
  // ============================================

  /**
   * Obtener examenes de un animal
   */
  async getExamenesAnimal(animalId) {
    try {
      const response = await api.get(`/examenes/animal/${animalId}`);
      return extractData(response, []);
    } catch (error) {
      console.error('Error al obtener exámenes:', error);
      throw error;
    }
  },

  /**
   * Registrar examen de laboratorio
   */
  async createExamen(data) {
    try {
      const response = await api.post('/examenes', data);
      return extractData(response, {});
    } catch (error) {
      console.error('Error al crear examen:', error);
      throw error;
    }
  },

  /**
   * Actualizar resultados de examen
   */
  async updateExamenResultados(id, data) {
    try {
      const response = await api.put(`/examenes/${id}/resultados`, data);
      return extractData(response, {});
    } catch (error) {
      console.error('Error al actualizar resultados de examen:', error);
      throw error;
    }
  },

  // ============================================
  // ESTADÍSTICAS GENERALES
  // ============================================

  /**
   * Obtener estadisticas generales de veterinaria
   */
  async getEstadisticasGenerales() {
    try {
      const response = await api.get('/veterinaria/estadisticas');
      return extractData(response, {});
    } catch (error) {
      console.error('Error al obtener estadísticas generales:', error);
      throw error;
    }
  },

  /**
   * Obtener dashboard veterinario
   */
  async getDashboard() {
    try {
      const response = await api.get('/veterinaria/dashboard');
      return extractData(response, {});
    } catch (error) {
      console.error('Error al obtener dashboard veterinario:', error);
      throw error;
    }
  },
};

export default veterinaryService;

