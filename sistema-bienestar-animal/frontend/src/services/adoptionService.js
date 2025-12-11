/**
 * Adoption Service
 * Centraliza todas las llamadas relacionadas con adopciones.
 * Usa el cliente API configurado con autenticación.
 */

import api from './api';

/**
 * Obtener animales disponibles para adopción
 * GET /api/v1/adopciones/animales-disponibles
 */
export async function fetchAdoptableAnimals(filters = {}) {
  const response = await api.get('/adopciones/animales-disponibles', { params: filters });
  return response.data;
}

/**
 * Enviar solicitud de adopción
 * POST /api/v1/adopciones/solicitudes
 */
export async function submitAdoptionRequest(payload) {
  const response = await api.post('/adopciones/solicitudes', payload);
  return response.data;
}

/**
 * HU-011 - Gestión de solicitudes
 * Obtener lista de solicitudes de adopción
 * GET /api/v1/adopciones/solicitudes
 */
export async function fetchAdoptionRequests(filters = {}) {
  const response = await api.get('/adopciones/solicitudes', { params: filters });
  return response.data;
}

/**
 * Obtener una solicitud específica
 * GET /api/v1/adopciones/solicitudes/:id
 */
export async function fetchAdoptionRequest(requestId) {
  const response = await api.get(`/adopciones/solicitudes/${requestId}`);
  return response.data;
}

/**
 * Programar visita domiciliaria
 * POST /api/v1/adopciones/solicitudes/:id/visita
 */
export async function scheduleHomeVisit(requestId, visit) {
  const response = await api.post(`/adopciones/solicitudes/${requestId}/visita`, visit);
  return response.data;
}

/**
 * Guardar informe de visita domiciliaria
 * POST /api/v1/adopciones/solicitudes/:id/informe-visita
 */
export async function saveHomeVisitReport(requestId, visitReport) {
  const response = await api.post(`/adopciones/solicitudes/${requestId}/informe-visita`, visitReport);
  return response.data;
}

/**
 * Aprobar solicitud de adopción
 * POST /api/v1/adopciones/solicitudes/:id/aprobar
 */
export async function approveAdoptionRequest(requestId, justification) {
  const response = await api.post(`/adopciones/solicitudes/${requestId}/aprobar`, { justification });
  return response.data;
}

/**
 * Rechazar solicitud de adopción
 * POST /api/v1/adopciones/solicitudes/:id/rechazar
 */
export async function rejectAdoptionRequest(requestId, justification) {
  const response = await api.post(`/adopciones/solicitudes/${requestId}/rechazar`, { justification });
  return response.data;
}

/**
 * HU-012 - Contrato
 * Obtener solicitudes aprobadas sin contrato
 * GET /api/v1/adopciones/contratos/pendientes
 */
export async function fetchApprovedRequestsWithoutContract() {
  const response = await api.get('/adopciones/contratos/pendientes');
  return response.data;
}

/**
 * Generar contrato de adopción
 * POST /api/v1/adopciones/contratos/:id/generar
 */
export async function generateAdoptionContract(requestId) {
  const response = await api.post(`/adopciones/contratos/${requestId}/generar`);
  return response.data;
}

/**
 * Registrar firma de contrato
 * POST /api/v1/adopciones/contratos/:id/firmar
 */
export async function registerContractSignature(contractId) {
  const response = await api.post(`/adopciones/contratos/${contractId}/firmar`);
  return response.data;
}

/**
 * HU-013 - Seguimientos
 * Obtener seguimientos pendientes
 * GET /api/v1/adopciones/seguimientos/pendientes
 */
export async function fetchPendingFollowUps() {
  const response = await api.get('/adopciones/seguimientos/pendientes');
  return response.data;
}

/**
 * Guardar resultado de seguimiento
 * POST /api/v1/adopciones/seguimientos/:id
 */
export async function saveFollowUpResult(followupId, payload) {
  const response = await api.post(`/adopciones/seguimientos/${followupId}`, payload);
  return response.data;
}

/**
 * HU-014 - Devoluciones
 * Buscar adopciones activas
 * GET /api/v1/adopciones/activas
 */
export async function fetchActiveAdoptionsBySearch(term) {
  const response = await api.get('/adopciones/activas', { params: { search: term } });
  return response.data;
}

/**
 * Registrar devolución de animal
 * POST /api/v1/adopciones/:id/devolucion
 */
export async function registerReturn(adoptionId, payload) {
  const response = await api.post(`/adopciones/${adoptionId}/devolucion`, payload);
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
 * Obtener historial de adopciones de un animal
 * GET /api/v1/animales/:id/adopciones
 */
export async function fetchAnimalAdoptionHistory(animalId) {
  const response = await api.get(`/animales/${animalId}/adopciones`);
  return response.data;
}

export default {
  fetchAdoptableAnimals,
  submitAdoptionRequest,
  fetchAdoptionRequests,
  fetchAdoptionRequest,
  scheduleHomeVisit,
  saveHomeVisitReport,
  approveAdoptionRequest,
  rejectAdoptionRequest,
  fetchApprovedRequestsWithoutContract,
  generateAdoptionContract,
  registerContractSignature,
  fetchPendingFollowUps,
  saveFollowUpResult,
  fetchActiveAdoptionsBySearch,
  registerReturn,
  fetchAdoptionStats,
  fetchAnimalAdoptionHistory,
};
