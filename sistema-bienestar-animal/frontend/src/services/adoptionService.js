/**
 * Adoption Service
 * Centraliza todas las llamadas relacionadas con adopciones.
 * Usa el cliente API configurado con autenticación.
 *
 * ENDPOINTS BACKEND:
 * - GET /api/v1/adopciones/estadisticas
 * - GET /api/v1/adopciones/pendientes
 * - GET /api/v1/adopciones
 * - POST /api/v1/adopciones
 * - GET /api/v1/adopciones/{id}
 * - PUT /api/v1/adopciones/{id}/evaluar
 * - GET /api/v1/adopciones/{id}/contrato
 * - GET /api/v1/visitas-seguimiento/pendientes
 * - GET /api/v1/visitas-seguimiento/requieren-visita
 * - GET /api/v1/visitas-seguimiento
 * - POST /api/v1/visitas-seguimiento
 * - GET /api/v1/visitas-seguimiento/{id}
 * - PUT /api/v1/visitas-seguimiento/{id}/registrar
 * - PUT /api/v1/visitas-seguimiento/{id}/cancelar
 * - PUT /api/v1/visitas-seguimiento/{id}/reprogramar
 */

import api from './api';

// ============================================
// ADOPCIONES
// ============================================

/**
 * Obtener animales disponibles para adopción
 * GET /api/v1/animals/catalogo-adopcion
 */
export async function fetchAdoptableAnimals(filters = {}) {
  const response = await api.get('/animals/catalogo-adopcion', { params: filters });
  return response.data;
}

/**
 * Obtener estadísticas de adopciones
 * GET /api/v1/adopciones/estadisticas
 */
export async function fetchAdoptionStats() {
  const response = await api.get('/adopciones/estadisticas');
  return response.data;
}

/**
 * Obtener solicitudes de adopción pendientes
 * GET /api/v1/adopciones/pendientes
 */
export async function fetchPendingAdoptions() {
  const response = await api.get('/adopciones/pendientes');
  return response.data;
}

/**
 * Obtener lista de solicitudes de adopción
 * GET /api/v1/adopciones
 */
export async function fetchAdoptionRequests(filters = {}) {
  const response = await api.get('/adopciones', { params: filters });
  return response.data;
}

/**
 * Enviar solicitud de adopción (con archivos)
 * POST /api/v1/adopciones/solicitud
 *
 * @param {Object} adoptanteData - Datos del adoptante
 * @param {string} animalId - ID del animal
 * @param {Object} solicitudData - Datos adicionales de la solicitud
 * @param {Object} archivos - { copia_cedula, comprobante_domicilio }
 */
export async function submitAdoptionRequest(adoptanteData, animalId, solicitudData = {}, archivos = {}) {
  const formData = new FormData();

  // Animal
  formData.append('animal_id', animalId);

  // Datos del adoptante (prefijo adoptante.)
  Object.keys(adoptanteData).forEach(key => {
    const value = adoptanteData[key];
    if (value !== null && value !== undefined) {
      // Convertir booleanos a "1" o "0" para que Laravel los interprete correctamente
      if (typeof value === 'boolean') {
        formData.append(`adoptante[${key}]`, value ? '1' : '0');
      } else {
        formData.append(`adoptante[${key}]`, value);
      }
    }
  });

  // Datos de la solicitud
  Object.keys(solicitudData).forEach(key => {
    const value = solicitudData[key];
    if (value !== null && value !== undefined) {
      // Convertir booleanos a "1" o "0"
      if (typeof value === 'boolean') {
        formData.append(key, value ? '1' : '0');
      } else {
        formData.append(key, value);
      }
    }
  });

  // Archivos
  if (archivos.copia_cedula) {
    formData.append('copia_cedula', archivos.copia_cedula);
  }
  if (archivos.comprobante_domicilio) {
    formData.append('comprobante_domicilio', archivos.comprobante_domicilio);
  }

  const response = await api.post('/adopciones/solicitud', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  });
  return response.data;
}

/**
 * Enviar solicitud de adopción simple (sin archivos - legacy)
 * POST /api/v1/adopciones
 */
export async function submitAdoptionRequestSimple(payload) {
  const response = await api.post('/adopciones', payload);
  return response.data;
}

/**
 * Obtener una solicitud específica
 * GET /api/v1/adopciones/{id}
 */
export async function fetchAdoptionRequest(requestId) {
  const response = await api.get(`/adopciones/${requestId}`);
  return response.data;
}

/**
 * Evaluar solicitud de adopción (aprobar/rechazar)
 * PUT /api/v1/adopciones/{id}/evaluar
 * @param {string} requestId - ID de la solicitud
 * @param {object} evaluation - { estado: 'aprobada'|'rechazada', observaciones, resultado_evaluacion }
 */
export async function evaluateAdoptionRequest(requestId, evaluation) {
  const response = await api.put(`/adopciones/${requestId}/evaluar`, evaluation);
  return response.data;
}

/**
 * Aprobar solicitud de adopción
 * PUT /api/v1/adopciones/{id}/evaluar
 */
export async function approveAdoptionRequest(requestId, observaciones = '') {
  return evaluateAdoptionRequest(requestId, {
    estado: 'aprobada',
    resultado_evaluacion: 'favorable',
    observaciones
  });
}

/**
 * Rechazar solicitud de adopción
 * PUT /api/v1/adopciones/{id}/evaluar
 */
export async function rejectAdoptionRequest(requestId, observaciones = '') {
  return evaluateAdoptionRequest(requestId, {
    estado: 'rechazada',
    resultado_evaluacion: 'desfavorable',
    observaciones
  });
}

/**
 * Obtener lista de contratos generados
 * GET /api/v1/adopciones/contratos
 * @param {object} filters - Filtros opcionales { firmado, busqueda, per_page }
 */
export async function fetchContracts(filters = {}) {
  const response = await api.get('/adopciones/contratos', { params: filters });
  return response.data;
}

/**
 * Obtener contrato de adopción
 * GET /api/v1/adopciones/{id}/contrato
 */
export async function fetchAdoptionContract(requestId) {
  const response = await api.get(`/adopciones/${requestId}/contrato`);
  return response.data;
}

/**
 * Descargar contrato de adopción como PDF
 * GET /api/v1/adopciones/{id}/contrato/descargar
 */
export async function downloadAdoptionContract(requestId) {
  const response = await api.get(`/adopciones/${requestId}/contrato/descargar`, {
    responseType: 'blob'
  });
  return response.data;
}

/**
 * Firmar contrato de adopción electrónicamente
 * POST /api/v1/adopciones/{id}/contrato/firmar
 * @param {string} requestId - ID de la adopción
 * @param {string} firmaBase64 - Imagen de la firma en Base64
 */
export async function signAdoptionContract(requestId, firmaBase64) {
  const response = await api.post(`/adopciones/${requestId}/contrato/firmar`, {
    firma: firmaBase64,
    acepta_terminos: true,
  });
  return response.data;
}

/**
 * Obtener estado del contrato y adopción
 * GET /api/v1/adopciones/{id}/estado-contrato
 */
export async function fetchContractStatus(requestId) {
  const response = await api.get(`/adopciones/${requestId}/estado-contrato`);
  return response.data;
}

/**
 * @deprecated Use downloadAdoptionContract instead
 * Generar/descargar contrato de adopción (PDF)
 * GET /api/v1/adopciones/{id}/contrato/descargar
 */
export async function generateAdoptionContract(requestId) {
  return downloadAdoptionContract(requestId);
}

// ============================================
// CONSULTA PÚBLICA DE ADOPCIONES (SIN AUTENTICACIÓN)
// ============================================

/**
 * Consultar estado de adopción públicamente (sin autenticación)
 * GET /api/v1/adopciones/consulta-publica
 * @param {string} tipoDocumento - Tipo de documento (CC, CE, TI, PA, PEP)
 * @param {string} numeroDocumento - Número de documento
 * @param {string} codigoAdopcion - Código de adopción (opcional)
 */
export async function consultarAdopcionPublica(tipoDocumento, numeroDocumento, codigoAdopcion = null) {
  const params = {
    tipo_documento: tipoDocumento,
    numero_documento: numeroDocumento,
  };
  if (codigoAdopcion) {
    params.codigo_adopcion = codigoAdopcion;
  }
  const response = await api.get('/adopciones/consulta-publica', { params });
  return response.data;
}

/**
 * Firmar contrato de adopción públicamente (sin autenticación)
 * POST /api/v1/adopciones/{id}/contrato/firmar-publico
 * @param {string} adopcionId - ID de la adopción
 * @param {string} tipoDocumento - Tipo de documento del adoptante
 * @param {string} numeroDocumento - Número de documento del adoptante
 * @param {string} firmaBase64 - Imagen de la firma en Base64
 */
export async function firmarContratoPublico(adopcionId, tipoDocumento, numeroDocumento, firmaBase64) {
  const response = await api.post(`/adopciones/${adopcionId}/contrato/firmar-publico`, {
    tipo_documento: tipoDocumento,
    numero_documento: numeroDocumento,
    firma: firmaBase64,
    acepta_terminos: true,
  });
  return response.data;
}

// ============================================
// VISITAS DE SEGUIMIENTO
// ============================================

/**
 * Obtener visitas de seguimiento pendientes
 * GET /api/v1/visitas-seguimiento/pendientes
 */
export async function fetchPendingFollowUps() {
  const response = await api.get('/visitas-seguimiento/pendientes');
  return response.data;
}

/**
 * Obtener adopciones que requieren visita
 * GET /api/v1/visitas-seguimiento/requieren-visita
 */
export async function fetchRequireVisit() {
  const response = await api.get('/visitas-seguimiento/requieren-visita');
  return response.data;
}

/**
 * Obtener todas las visitas de seguimiento
 * GET /api/v1/visitas-seguimiento
 */
export async function fetchFollowUpVisits(filters = {}) {
  const response = await api.get('/visitas-seguimiento', { params: filters });
  return response.data;
}

/**
 * Programar visita de seguimiento
 * POST /api/v1/visitas-seguimiento
 */
export async function scheduleFollowUpVisit(visitData) {
  const response = await api.post('/visitas-seguimiento', visitData);
  return response.data;
}

/**
 * Obtener detalle de una visita
 * GET /api/v1/visitas-seguimiento/{id}
 */
export async function fetchFollowUpVisit(visitId) {
  const response = await api.get(`/visitas-seguimiento/${visitId}`);
  return response.data;
}

/**
 * Registrar resultado de visita de seguimiento (con fotos de respaldo)
 * POST /api/v1/visitas-seguimiento/{id}/registrar
 *
 * @param {string} visitId - ID de la visita
 * @param {Object} resultData - Datos del resultado
 * @param {File[]} fotos - Array de archivos de fotos (opcional)
 */
export async function registerFollowUpResult(visitId, resultData, fotos = []) {
  const formData = new FormData();

  // Agregar datos básicos
  if (resultData.resultado) {
    formData.append('resultado', resultData.resultado);
  }
  if (resultData.fecha_realizada) {
    formData.append('fecha_realizada', resultData.fecha_realizada);
  }
  if (resultData.observaciones) {
    formData.append('observaciones', resultData.observaciones);
  }
  if (resultData.recomendaciones) {
    formData.append('recomendaciones', resultData.recomendaciones);
  }

  // Agregar condiciones_hogar como array asociativo
  if (resultData.condiciones_hogar) {
    Object.keys(resultData.condiciones_hogar).forEach(key => {
      if (resultData.condiciones_hogar[key]) {
        formData.append(`condiciones_hogar[${key}]`, resultData.condiciones_hogar[key]);
      }
    });
  }

  // Agregar estado_animal como array asociativo
  if (resultData.estado_animal) {
    Object.keys(resultData.estado_animal).forEach(key => {
      if (resultData.estado_animal[key]) {
        formData.append(`estado_animal[${key}]`, resultData.estado_animal[key]);
      }
    });
  }

  // Agregar fotos de respaldo
  if (fotos && fotos.length > 0) {
    fotos.forEach((foto) => {
      formData.append('fotos_respaldo[]', foto);
    });
  }

  const response = await api.post(`/visitas-seguimiento/${visitId}/registrar`, formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  });
  return response.data;
}

/**
 * Eliminar visita programada (no realizada)
 * DELETE /api/v1/visitas-seguimiento/{id}
 */
export async function deleteFollowUpVisit(visitId) {
  const response = await api.delete(`/visitas-seguimiento/${visitId}`);
  return response.data;
}

/**
 * Reprogramar visita de seguimiento
 * PUT /api/v1/visitas-seguimiento/{id}/reprogramar
 */
export async function rescheduleFollowUpVisit(visitId, fecha_programada, observaciones = '') {
  const response = await api.put(`/visitas-seguimiento/${visitId}/reprogramar`, {
    fecha_programada,
    observaciones
  });
  return response.data;
}

/**
 * Obtener visitas de una adopción específica
 * GET /api/v1/visitas-seguimiento/adopcion/{adopcionId}
 */
export async function fetchVisitsByAdoption(adopcionId) {
  const response = await api.get(`/visitas-seguimiento/adopcion/${adopcionId}`);
  return response.data;
}

/**
 * Descargar PDF de resumen de visita de seguimiento
 * GET /api/v1/visitas-seguimiento/{id}/pdf
 */
export async function downloadFollowUpVisitPdf(visitId) {
  const response = await api.get(`/visitas-seguimiento/${visitId}/pdf`, {
    responseType: 'blob'
  });
  return response.data;
}

// ============================================
// FUNCIONES LEGACY (para compatibilidad)
// ============================================

/**
 * @deprecated Use fetchPendingAdoptions instead
 */
export async function fetchApprovedRequestsWithoutContract() {
  return fetchPendingAdoptions();
}

/**
 * @deprecated Use scheduleFollowUpVisit instead
 */
export async function scheduleHomeVisit(requestId, visit) {
  return scheduleFollowUpVisit({
    adopcion_id: requestId,
    ...visit
  });
}

/**
 * @deprecated Use registerFollowUpResult instead
 */
export async function saveHomeVisitReport(requestId, visitReport) {
  console.warn('saveHomeVisitReport is deprecated, use registerFollowUpResult instead');
  return visitReport;
}

/**
 * @deprecated Use registerFollowUpResult instead
 */
export async function saveFollowUpResult(followupId, payload) {
  return registerFollowUpResult(followupId, payload);
}

/**
 * @deprecated Not implemented in backend
 */
export async function fetchActiveAdoptionsBySearch(term) {
  // Search through all adoptions with estado='completada'
  const response = await api.get('/adopciones', { params: { estado: 'completada', busqueda: term } });
  return response.data;
}

// ============================================
// DEVOLUCIONES DE ANIMALES ADOPTADOS
// ============================================

/**
 * Obtener motivos de devolución disponibles
 * GET /api/v1/adopciones/devoluciones/motivos
 */
export async function fetchReturnReasons() {
  const response = await api.get('/adopciones/devoluciones/motivos');
  return response.data;
}

/**
 * Listar devoluciones
 * GET /api/v1/adopciones/devoluciones
 */
export async function fetchReturns(params = {}) {
  const response = await api.get('/adopciones/devoluciones', { params });
  return response.data;
}

/**
 * Obtener estadísticas de devoluciones
 * GET /api/v1/adopciones/devoluciones/estadisticas
 */
export async function fetchReturnStats() {
  const response = await api.get('/adopciones/devoluciones/estadisticas');
  return response.data;
}

/**
 * Obtener detalle de una devolución
 * GET /api/v1/adopciones/devoluciones/{id}
 */
export async function fetchReturn(returnId) {
  const response = await api.get(`/adopciones/devoluciones/${returnId}`);
  return response.data;
}

/**
 * Registrar devolución de animal adoptado
 * POST /api/v1/adopciones/{id}/devolucion
 * @param {string} adoptionId - ID de la adopción
 * @param {object} payload - Datos de la devolución
 * @param {string} payload.motivo - Motivo de devolución
 * @param {string} payload.descripcion_motivo - Descripción detallada
 * @param {string} payload.estado_animal_devolucion - Estado del animal (bueno, regular, malo, critico)
 * @param {string} payload.observaciones_estado - Observaciones adicionales
 * @param {string} payload.fecha_devolucion - Fecha de devolución (opcional)
 */
export async function registerReturn(adoptionId, payload) {
  const response = await api.post(`/adopciones/${adoptionId}/devolucion`, payload);
  return response.data;
}

/**
 * Completar revisión veterinaria de devolución
 * PUT /api/v1/adopciones/devoluciones/{id}/revision
 * @param {string} returnId - ID de la devolución
 * @param {object} payload - Datos de la revisión
 * @param {string} payload.diagnostico - Diagnóstico veterinario
 * @param {string} payload.observaciones_veterinario - Observaciones
 * @param {string} payload.recomendaciones - Recomendaciones
 * @param {boolean} payload.apto_adopcion - Si está apto para re-adopción
 * @param {string} payload.estado_salud - Estado de salud actual
 */
export async function completeReturnReview(returnId, payload) {
  const response = await api.put(`/adopciones/devoluciones/${returnId}/revision`, payload);
  return response.data;
}

/**
 * Descargar PDF de resumen de devolución
 * GET /api/v1/adopciones/devoluciones/{id}/pdf
 */
export async function downloadReturnPdf(returnId) {
  const response = await api.get(`/adopciones/devoluciones/${returnId}/pdf`, {
    responseType: 'blob'
  });
  return response.data;
}

/**
 * @deprecated Use fetchAnimal from animalService
 */
export async function fetchAnimalAdoptionHistory(animalId) {
  const response = await api.get(`/animals/${animalId}`);
  return response.data;
}

/**
 * @deprecated Use registerContractSignature - Not implemented
 */
export async function registerContractSignature(contractId) {
  console.warn('registerContractSignature is not implemented in backend');
  return { success: false, message: 'Not implemented' };
}

export default {
  // Main adoption functions
  fetchAdoptableAnimals,
  fetchAdoptionStats,
  fetchPendingAdoptions,
  fetchAdoptionRequests,
  submitAdoptionRequest,
  submitAdoptionRequestSimple,
  fetchAdoptionRequest,
  evaluateAdoptionRequest,
  approveAdoptionRequest,
  rejectAdoptionRequest,

  // Contract functions
  fetchContracts,
  fetchAdoptionContract,
  downloadAdoptionContract,
  signAdoptionContract,
  fetchContractStatus,
  generateAdoptionContract,

  // Public consultation (no auth required)
  consultarAdopcionPublica,
  firmarContratoPublico,

  // Follow-up visits
  fetchPendingFollowUps,
  fetchRequireVisit,
  fetchFollowUpVisits,
  scheduleFollowUpVisit,
  fetchFollowUpVisit,
  registerFollowUpResult,
  deleteFollowUpVisit,
  rescheduleFollowUpVisit,
  fetchVisitsByAdoption,
  downloadFollowUpVisitPdf,

  // Returns (Devoluciones)
  fetchReturnReasons,
  fetchReturns,
  fetchReturnStats,
  fetchReturn,
  registerReturn,
  completeReturnReview,
  downloadReturnPdf,

  // Legacy functions (deprecated)
  fetchApprovedRequestsWithoutContract,
  scheduleHomeVisit,
  saveHomeVisitReport,
  saveFollowUpResult,
  fetchActiveAdoptionsBySearch,
  fetchAnimalAdoptionHistory,
  registerContractSignature,
};
