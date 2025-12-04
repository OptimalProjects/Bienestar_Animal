// src/services/adoptionsService.js
// Centraliza todas las llamadas relacionadas con adopciones.

export async function fetchAdoptableAnimals(filters = {}) {
  const query = new URLSearchParams(filters).toString();
  const res = await fetch(`/api/adoptions/animals?${query}`);
  if (!res.ok) throw new Error('Error al obtener animales');
  return res.json();
}

export async function submitAdoptionRequest(payload) {
  const res = await fetch('/api/adoptions/requests', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (!res.ok) throw new Error('Error al enviar solicitud');
  return res.json();
}

/* HU-011 - Gestión de solicitudes */
export async function fetchAdoptionRequests(filters = {}) {
  const query = new URLSearchParams(filters).toString();
  const res = await fetch(`/api/adoptions/requests?${query}`);
  if (!res.ok) throw new Error('Error al obtener solicitudes');
  return res.json();
}

export async function scheduleHomeVisit(requestId, visit) {
  const res = await fetch(`/api/adoptions/requests/${requestId}/visit`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(visit),
  });
  if (!res.ok) throw new Error('Error al programar visita');
  return res.json();
}

export async function saveHomeVisitReport(requestId, visitReport) {
  const res = await fetch(
    `/api/adoptions/requests/${requestId}/visit-report`,
    {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(visitReport),
    }
  );
  if (!res.ok) throw new Error('Error al guardar informe');
  return res.json();
}

export async function approveAdoptionRequest(requestId, justification) {
  const res = await fetch(`/api/adoptions/requests/${requestId}/approve`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ justification }),
  });
  if (!res.ok) throw new Error('Error al aprobar solicitud');
  return res.json();
}

export async function rejectAdoptionRequest(requestId, justification) {
  const res = await fetch(`/api/adoptions/requests/${requestId}/reject`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ justification }),
  });
  if (!res.ok) throw new Error('Error al rechazar solicitud');
  return res.json();
}

/* HU-012 - Contrato */
export async function fetchApprovedRequestsWithoutContract() {
  const res = await fetch('/api/adoptions/contracts/pending');
  if (!res.ok) throw new Error('Error al obtener solicitudes aprobadas');
  return res.json();
}

export async function generateAdoptionContract(requestId) {
  const res = await fetch(`/api/adoptions/contracts/${requestId}/generate`, {
    method: 'POST',
  });
  if (!res.ok) throw new Error('Error al generar contrato');
  return res.json(); // { contractUrl, contractId }
}

export async function registerContractSignature(contractId) {
  const res = await fetch(
    `/api/adoptions/contracts/${contractId}/sign`,
    { method: 'POST' }
  );
  if (!res.ok) throw new Error('Error al registrar firma');
  return res.json();
}

/* HU-013 - Seguimientos */
export async function fetchPendingFollowUps() {
  const res = await fetch('/api/adoptions/followups/pending');
  if (!res.ok) throw new Error('Error al obtener seguimientos');
  return res.json();
}

export async function saveFollowUpResult(followupId, payload) {
  const res = await fetch(`/api/adoptions/followups/${followupId}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (!res.ok) throw new Error('Error al guardar seguimiento');
  return res.json();
}

/* HU-014 - Devoluciones */
export async function fetchActiveAdoptionsBySearch(term) {
  const res = await fetch(
    `/api/adoptions/active?search=${encodeURIComponent(term)}`
  );
  if (!res.ok) throw new Error('Error al buscar adopciones activas');
  return res.json();
}

export async function registerReturn(adoptionId, payload) {
  const res = await fetch(`/api/adoptions/${adoptionId}/return`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (!res.ok) throw new Error('Error al registrar devolución');
  return res.json();
}
