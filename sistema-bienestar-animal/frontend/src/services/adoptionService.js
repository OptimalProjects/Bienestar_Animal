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
 * Enviar solicitud de adopción
 * POST /api/v1/adopciones
 */
export async function submitAdoptionRequest(payload) {
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
 * Obtener contrato de adopción
 * GET /api/v1/adopciones/{id}/contrato
 */
export async function fetchAdoptionContract(requestId) {
  const response = await api.get(`/adopciones/${requestId}/contrato`);
  return response.data;
}

/**
 * Generar/descargar contrato de adopción (PDF)
 * GET /api/v1/adopciones/{id}/contrato
 */
export async function generateAdoptionContract(requestId) {
  const response = await api.get(`/adopciones/${requestId}/contrato`, {
    responseType: 'blob'
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
 * Registrar resultado de visita de seguimiento
 * PUT /api/v1/visitas-seguimiento/{id}/registrar
 */
export async function registerFollowUpResult(visitId, resultData) {
  const response = await api.put(`/visitas-seguimiento/${visitId}/registrar`, resultData);
  return response.data;
}

/**
 * Cancelar visita de seguimiento
 * PUT /api/v1/visitas-seguimiento/{id}/cancelar
 */
export async function cancelFollowUpVisit(visitId, motivo) {
  const response = await api.put(`/visitas-seguimiento/${visitId}/cancelar`, { motivo });
  return response.data;
}

/**
 * Reprogramar visita de seguimiento
 * PUT /api/v1/visitas-seguimiento/{id}/reprogramar
 */
export async function rescheduleFollowUpVisit(visitId, newDate) {
  const response = await api.put(`/visitas-seguimiento/${visitId}/reprogramar`, { nueva_fecha: newDate });
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

/**
 * @deprecated Not implemented in backend
 */
export async function registerReturn(adoptionId, payload) {
  console.warn('registerReturn is not implemented in backend');
  return { success: false, message: 'Not implemented' };
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
  fetchAdoptionRequest,
  evaluateAdoptionRequest,
  approveAdoptionRequest,
  rejectAdoptionRequest,
  fetchAdoptionContract,
  generateAdoptionContract,

  // Follow-up visits
  fetchPendingFollowUps,
  fetchRequireVisit,
  fetchFollowUpVisits,
  scheduleFollowUpVisit,
  fetchFollowUpVisit,
  registerFollowUpResult,
  cancelFollowUpVisit,
  rescheduleFollowUpVisit,

  // Legacy functions (deprecated)
  fetchApprovedRequestsWithoutContract,
  scheduleHomeVisit,
  saveHomeVisitReport,
  saveFollowUpResult,
  fetchActiveAdoptionsBySearch,
  registerReturn,
  fetchAnimalAdoptionHistory,
  registerContractSignature,
};
