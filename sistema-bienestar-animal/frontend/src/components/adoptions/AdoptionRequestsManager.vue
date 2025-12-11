<!-- AdoptionRequestsManager.vue - HU-011 -->
<template>
  <section class="requests-manager">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Gestión de Solicitudes de Adopción</h2>
      <p class="text2-tipografia-govco">Evalúa y procesa solicitudes de adopción</p>
    </div>

    <!-- Filtros -->
    <div class="filters-section">
      <div class="filters-grid">
        <div class="input-like-govco">
          <label for="status-filter" class="label-desplegable-govco">Estado</label>
          <div class="desplegable-govco" data-type="basic">
            <select id="status-filter" v-model="filters.status">
              <option value="">Todos</option>
              <option value="pending">Pendiente</option>
              <option value="in_evaluation">En evaluación</option>
              <option value="approved">Aprobada</option>
              <option value="rejected">Rechazada</option>
            </select>
          </div>
        </div>

        <div class="entradas-de-texto-govco">
          <label for="search">Buscar por nombre</label>
          <input
            type="text"
            id="search"
            v-model="filters.search"
            placeholder="Nombre del solicitante o animal"
          />
        </div>

        <button @click="applyFilters" class="govco-btn govco-bg-marine">
          Filtrar
        </button>
      </div>
    </div>

    <!-- Tabla de Solicitudes -->
    <div class="requests-table-container">
      <table class="requests-table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Solicitante</th>
            <th>Animal</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredRequests.length === 0">
            <td colspan="5" class="empty-row">
              No se encontraron solicitudes
            </td>
          </tr>
          <tr
            v-for="request in filteredRequests"
            :key="request.id"
            class="request-row"
          >
            <td>{{ formatDate(request.createdAt) }}</td>
            <td>
              <strong>{{ request.applicant.name }}</strong><br />
              <small>{{ request.applicant.phone }}</small>
            </td>
            <td>
              <strong>{{ request.animal.name }}</strong><br />
              <small>{{ request.animal.species }} - {{ request.animal.microchip }}</small>
            </td>
            <td>
              <span class="status-badge" :class="`status-${request.status}`">
                {{ getStatusLabel(request.status) }}
              </span>
            </td>
            <td>
              <button
                @click="viewDetails(request)"
                class="action-btn-small govco-bg-marine"
              >
                Ver Detalles
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal de Detalles -->
    <div v-if="selectedRequest" class="modal-overlay" @click="closeModal">
      <div class="modal-content modal-large" @click.stop>
        <div class="modal-header govco-bg-blue-light">
          <h3 class="h4-tipografia-govco govcolor-blue-dark">
            Evaluación de Solicitud
          </h3>
          <button @click="closeModal" class="modal-close">×</button>
        </div>

        <div class="modal-body">
          <!-- Información del Solicitante -->
          <div class="detail-section">
            <h4 class="h6-tipografia-govco govcolor-marine">Información del Solicitante</h4>
            <dl class="detail-list">
              <dt>Nombre completo:</dt>
              <dd>{{ selectedRequest.applicant.name }}</dd>
              <dt>Cédula:</dt>
              <dd>{{ selectedRequest.applicant.idNumber }}</dd>
              <dt>Teléfono:</dt>
              <dd>{{ selectedRequest.applicant.phone }}</dd>
              <dt>Email:</dt>
              <dd>{{ selectedRequest.applicant.email }}</dd>
              <dt>Dirección:</dt>
              <dd>{{ selectedRequest.applicant.address }}</dd>
              <dt>Tipo de vivienda:</dt>
              <dd>{{ selectedRequest.applicant.housingType }}</dd>
              <dt>Experiencia con mascotas:</dt>
              <dd>{{ selectedRequest.applicant.petExperience }}</dd>
            </dl>
          </div>

          <!-- Información del Animal -->
          <div class="detail-section">
            <h4 class="h6-tipografia-govco govcolor-marine">Animal Solicitado</h4>
            <div class="animal-preview">
              <img
                :src="selectedRequest.animal.photoUrl || '/placeholder-animal.jpg'"
                :alt="selectedRequest.animal.name"
              />
              <div>
                <h5>{{ selectedRequest.animal.name }}</h5>
                <p>{{ selectedRequest.animal.species }} - {{ selectedRequest.animal.breed }}</p>
                <p>Microchip: {{ selectedRequest.animal.microchip }}</p>
                <p>Edad: {{ selectedRequest.animal.age }}</p>
              </div>
            </div>
          </div>

          <!-- Visita Domiciliaria -->
          <div class="detail-section full-width">
            <h4 class="h6-tipografia-govco govcolor-marine">Visita Domiciliaria</h4>
            
            <div v-if="!selectedRequest.homeVisit.scheduled" class="form-grid">
              <div class="input-like-govco">
                <label for="visitDate" class="label-desplegable-govco">
                  Fecha de visita<span aria-required="true">*</span>
                </label>
                <div class="desplegable-govco desplegable-calendar-govco" data-type="calendar">
                  <div class="date desplegable-selected-option">
                    <input
                      type="text"
                      id="visitDate"
                      v-model="homeVisit.date"
                      placeholder="DD/MM/AAAA"
                    />
                  </div>
                </div>
              </div>

              <div class="entradas-de-texto-govco">
                <label for="visitTime">Hora de visita*</label>
                <input
                  type="time"
                  id="visitTime"
                  v-model="homeVisit.time"
                />
              </div>

              <button
                @click="scheduleVisit"
                class="govco-btn govco-bg-elf-green"
              >
                Programar Visita
              </button>
            </div>

            <div v-else class="visit-info">
              <p><strong>Visita programada:</strong></p>
              <p>{{ formatDate(selectedRequest.homeVisit.date) }} a las {{ selectedRequest.homeVisit.time }}</p>
              
              <div v-if="!selectedRequest.homeVisit.completed" class="form-grid">
                <div class="entradas-de-texto-govco full-width">
                  <label for="observations">Observaciones de la visita*</label>
                  <textarea
                    id="observations"
                    v-model="homeVisit.observations"
                    rows="4"
                    placeholder="Describe las condiciones de la vivienda, interacción con el solicitante, etc."
                  ></textarea>
                </div>

                <button
                  @click="completeVisit"
                  class="govco-btn govco-bg-marine"
                >
                  Registrar Visita Completada
                </button>
              </div>

              <div v-else class="visit-completed">
                <p><strong>✓ Visita completada</strong></p>
                <p>{{ selectedRequest.homeVisit.observations }}</p>
              </div>
            </div>
          </div>

          <!-- Decisión -->
          <div v-if="selectedRequest.homeVisit.completed && selectedRequest.status === 'in_evaluation'" class="detail-section full-width decision-section">
            <h4 class="h6-tipografia-govco govcolor-marine">Decisión Final</h4>
            
            <div class="form-grid">
              <div class="entradas-de-texto-govco full-width">
                <label for="justification">Justificación de la decisión*</label>
                <textarea
                  id="justification"
                  v-model="decision.justification"
                  rows="3"
                  placeholder="Explica el motivo de la decisión..."
                ></textarea>
              </div>

              <div class="decision-buttons">
                <button
                  @click="approveRequest"
                  class="govco-btn govco-bg-elf-green"
                  :disabled="!decision.justification"
                >
                  ✓ Aprobar Adopción
                </button>
                <button
                  @click="rejectRequest"
                  class="govco-btn govco-bg-shiraz"
                  :disabled="!decision.justification"
                >
                  ✕ Rechazar Solicitud
                </button>
              </div>
            </div>
          </div>

          <!-- Estado Final -->
          <div v-if="selectedRequest.status === 'approved' || selectedRequest.status === 'rejected'" class="detail-section full-width">
            <div class="final-decision" :class="selectedRequest.status">
              <h4>
                {{ selectedRequest.status === 'approved' ? '✓ Solicitud Aprobada' : '✕ Solicitud Rechazada' }}
              </h4>
              <p><strong>Decisión tomada por:</strong> {{ selectedRequest.decision.evaluator }}</p>
              <p><strong>Fecha:</strong> {{ formatDate(selectedRequest.decision.date) }}</p>
              <p><strong>Justificación:</strong> {{ selectedRequest.decision.justification }}</p>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button @click="closeModal" class="govco-btn govco-bg-concrete">
            Cerrar
          </button>
          <button
            v-if="selectedRequest.status === 'approved'"
            @click="generateContract"
            class="govco-btn govco-bg-elf-green"
          >
            Generar Contrato
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import adoptionService from '@/services/adoptionService';

const filters = ref({
  status: '',
  search: '',
});

const requests = ref([]);
const loading = ref(false);
const error = ref(null);

const selectedRequest = ref(null);
const showModal = ref(false);

// Datos de visita domiciliaria
const homeVisit = ref({
  date: '',
  time: '',
  observations: '',
});

// Decisión (aprobar / rechazar)
const decision = ref({
  action: '',
  justification: '',
});

// Mapeo de estados del backend al frontend
const statusMap = {
  'pendiente': 'pending',
  'en_evaluacion': 'in_evaluation',
  'aprobada': 'approved',
  'rechazada': 'rejected',
  'completada': 'approved'
};

const reverseStatusMap = {
  'pending': 'pendiente',
  'in_evaluation': 'en_evaluacion',
  'approved': 'aprobada',
  'rejected': 'rechazada'
};

// Transforma los datos del backend al formato del frontend
function transformRequest(backendRequest) {
  return {
    id: backendRequest.id,
    status: statusMap[backendRequest.estado] || backendRequest.estado,
    applicant: {
      name: backendRequest.adoptante?.nombre_completo || backendRequest.nombre_adoptante || 'Sin nombre',
      idNumber: backendRequest.adoptante?.documento_identidad || '',
      phone: backendRequest.adoptante?.telefono || '',
      email: backendRequest.adoptante?.email || '',
      address: backendRequest.adoptante?.direccion || '',
      housingType: backendRequest.adoptante?.tipo_vivienda || 'No especificado',
      petExperience: backendRequest.adoptante?.experiencia_mascotas || 'No especificado',
    },
    animal: {
      id: backendRequest.animal?.id,
      name: backendRequest.animal?.nombre || 'Sin nombre',
      species: backendRequest.animal?.especie || '',
      breed: backendRequest.animal?.raza || '',
      microchip: backendRequest.animal?.numero_chip || 'Sin chip',
      age: backendRequest.animal?.edad_formato || 'Desconocida',
      photoUrl: backendRequest.animal?.url_foto_principal,
    },
    createdAt: backendRequest.created_at || backendRequest.fecha_solicitud,
    homeVisit: {
      scheduled: !!backendRequest.fecha_visita,
      date: backendRequest.fecha_visita || '',
      time: backendRequest.hora_visita || '',
      observations: backendRequest.observaciones_visita || '',
      completed: backendRequest.visita_realizada || false,
    },
    decision: {
      evaluator: backendRequest.evaluador?.nombre_completo || '',
      date: backendRequest.fecha_evaluacion || '',
      justification: backendRequest.observaciones || '',
    }
  };
}

const filteredRequests = computed(() => {
  return requests.value.filter((r) => {
    if (filters.value.status && r.status !== filters.value.status) {
      return false;
    }
    if (filters.value.search) {
      const term = filters.value.search.toLowerCase();
      const haystack = `${r.applicant.name} ${r.animal.name} ${r.applicant.idNumber}`.toLowerCase();
      return haystack.includes(term);
    }
    return true;
  });
});

async function loadRequests() {
  loading.value = true;
  error.value = null;
  try {
    const params = {};
    if (filters.value.status) {
      params.estado = reverseStatusMap[filters.value.status] || filters.value.status;
    }
    if (filters.value.search) {
      params.busqueda = filters.value.search;
    }

    const response = await adoptionService.fetchAdoptionRequests(params);
    const data = response.data || response;

    // Manejar paginación si existe
    const rawRequests = data.data || data || [];
    requests.value = rawRequests.map(transformRequest);
  } catch (err) {
    console.error('Error al cargar solicitudes:', err);
    error.value = err.response?.data?.message || 'Error al cargar solicitudes de adopción';
    requests.value = [];
  } finally {
    loading.value = false;
  }
}

function applyFilters() {
  loadRequests();
}

function viewDetails(request) {
  selectedRequest.value = JSON.parse(JSON.stringify(request));
  homeVisit.value = {
    date: request.homeVisit?.date || '',
    time: request.homeVisit?.time || '',
    observations: request.homeVisit?.observations || '',
  };
  decision.value = {
    action: '',
    justification: '',
  };
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  selectedRequest.value = null;
}

async function scheduleVisit() {
  if (!selectedRequest.value || !homeVisit.value.date || !homeVisit.value.time) {
    if (window.$toast) {
      window.$toast.warning('Atención', 'Debe completar la fecha y hora de visita');
    } else {
      alert('Debe completar la fecha y hora de visita');
    }
    return;
  }

  try {
    await adoptionService.scheduleFollowUpVisit({
      adopcion_id: selectedRequest.value.id,
      fecha_programada: homeVisit.value.date,
      hora_programada: homeVisit.value.time,
      tipo: 'previa',
      observaciones: homeVisit.value.observations
    });

    selectedRequest.value.homeVisit = {
      scheduled: true,
      date: homeVisit.value.date,
      time: homeVisit.value.time,
      observations: homeVisit.value.observations,
      completed: false
    };

    if (window.$toast) {
      window.$toast.success('Éxito', 'Visita domiciliaria programada exitosamente');
    } else {
      alert('Visita domiciliaria programada exitosamente');
    }

    await loadRequests();
  } catch (err) {
    console.error('Error al programar visita:', err);
    const errorMsg = err.response?.data?.message || 'Error al programar la visita';
    if (window.$toast) {
      window.$toast.error('Error', errorMsg);
    } else {
      alert(errorMsg);
    }
  }
}

async function completeVisit() {
  if (!selectedRequest.value || !homeVisit.value.observations) {
    if (window.$toast) {
      window.$toast.warning('Atención', 'Debe completar las observaciones de la visita');
    } else {
      alert('Debe completar las observaciones de la visita');
    }
    return;
  }

  // Marcar visita como completada - esto dependerá de si hay una visita_id
  // Por ahora actualizamos localmente
  selectedRequest.value.homeVisit.completed = true;
  selectedRequest.value.homeVisit.observations = homeVisit.value.observations;

  if (window.$toast) {
    window.$toast.success('Éxito', 'Visita registrada como completada');
  } else {
    alert('Visita registrada como completada');
  }
}

async function approveRequest() {
  if (!selectedRequest.value || !decision.value.justification) {
    if (window.$toast) {
      window.$toast.warning('Atención', 'Debe proporcionar una justificación');
    } else {
      alert('Debe proporcionar una justificación');
    }
    return;
  }

  try {
    await adoptionService.approveAdoptionRequest(
      selectedRequest.value.id,
      decision.value.justification
    );

    if (window.$toast) {
      window.$toast.success('Éxito', 'Solicitud de adopción aprobada exitosamente');
    } else {
      alert('Solicitud de adopción aprobada exitosamente');
    }

    closeModal();
    await loadRequests();
  } catch (err) {
    console.error('Error al aprobar solicitud:', err);
    const errorMsg = err.response?.data?.message || 'Error al aprobar la solicitud';
    if (window.$toast) {
      window.$toast.error('Error', errorMsg);
    } else {
      alert(errorMsg);
    }
  }
}

async function rejectRequest() {
  if (!selectedRequest.value || !decision.value.justification) {
    if (window.$toast) {
      window.$toast.warning('Atención', 'Debe proporcionar una justificación');
    } else {
      alert('Debe proporcionar una justificación');
    }
    return;
  }

  try {
    await adoptionService.rejectAdoptionRequest(
      selectedRequest.value.id,
      decision.value.justification
    );

    if (window.$toast) {
      window.$toast.success('Éxito', 'Solicitud de adopción rechazada');
    } else {
      alert('Solicitud de adopción rechazada');
    }

    closeModal();
    await loadRequests();
  } catch (err) {
    console.error('Error al rechazar solicitud:', err);
    const errorMsg = err.response?.data?.message || 'Error al rechazar la solicitud';
    if (window.$toast) {
      window.$toast.error('Error', errorMsg);
    } else {
      alert(errorMsg);
    }
  }
}

async function generateContract() {
  if (!selectedRequest.value) return;

  try {
    const blob = await adoptionService.generateAdoptionContract(selectedRequest.value.id);

    // Descargar el PDF
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `contrato-adopcion-${selectedRequest.value.id}.pdf`;
    link.click();
    window.URL.revokeObjectURL(url);

    if (window.$toast) {
      window.$toast.success('Éxito', 'Contrato generado y descargado');
    }
  } catch (err) {
    console.error('Error al generar contrato:', err);
    const errorMsg = err.response?.data?.message || 'Error al generar el contrato';
    if (window.$toast) {
      window.$toast.error('Error', errorMsg);
    } else {
      alert(errorMsg);
    }
  }
}

function formatDate(dateStr) {
  if (!dateStr) return 'Sin fecha';
  const date = new Date(dateStr);
  return date.toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

function getStatusLabel(status) {
  const labels = {
    'pending': 'Pendiente',
    'in_evaluation': 'En evaluación',
    'approved': 'Aprobada',
    'rejected': 'Rechazada'
  };
  return labels[status] || status;
}

onMounted(loadRequests);
</script>



<style scoped>
.requests-manager {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
  background: #f5f7fb;
}

.form-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #3366CC;
}

.filters-section {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.filters-grid {
  display: grid;
  grid-template-columns: 1fr 2fr auto;
  gap: 1rem;
  align-items: end;
}

.requests-table-container {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.requests-table {
  width: 100%;
  border-collapse: collapse;
}

.requests-table thead {
  background: #E8F0FE;
  color: #3366CC;
}

.requests-table th,
.requests-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #E0E0E0;
}

.requests-table th {
  font-weight: 600;
}

.request-row {
  transition: background 0.2s;
}

.request-row:hover {
  background: #F6F8F9;
}

.empty-row {
  text-align: center;
  padding: 3rem;
  color: #737373;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
  color: white;
}

.status-pending {
  background: #FFAB00;
}

.status-in_evaluation {
  background: #3366CC;
}

.status-approved {
  background: #068460;
}

.status-rejected {
  background: #A80521;
}

.action-btn-small {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn-small:hover {
  opacity: 0.9;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.modal-large {
  max-width: 1200px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 2px solid #3366cc;
}

.modal-header h3 {
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 2rem;
  cursor: pointer;
  color: #737373;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  transition: all 0.2s;
}

.modal-close:hover {
  background: rgba(0,0,0,0.1);
}

.modal-body {
  padding: 1.5rem;
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 2rem;
}

.detail-section {
  padding: 1.5rem;
  background: #F6F8F9;
  border-radius: 8px;
}

.full-width {
  grid-column: 1 / 3;
}

.detail-list {
  margin: 0;
}

.detail-list dt {
  font-weight: 600;
  color: #4B4B4B;
  margin-top: 0.75rem;
}

.detail-list dd {
  margin: 0.25rem 0 0 0;
  color: #737373;
}

.animal-preview {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.animal-preview img {
  width: 80px;
  height: 80px;
  border-radius: 8px;
  object-fit: cover;
  background: linear-gradient(135deg, #3366cc 0%, #004884 100%);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-top: 1rem;
}

.visit-info,
.visit-completed {
  margin-top: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 6px;
}

.decision-section {
  border: 2px solid #3366CC;
}

.decision-buttons {
  grid-column: 1 / 4;
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.final-decision {
  padding: 1.5rem;
  border-radius: 8px;
  text-align: center;
}

.final-decision.approved {
  background: #E8F5E9;
  border: 2px solid #068460;
  color: #068460;
}

.final-decision.rejected {
  background: #FFEBEE;
  border: 2px solid #A80521;
  color: #A80521;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 2px solid #E0E0E0;
}

.govco-btn {
  padding: 0.75rem 2rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  color: white;
  transition: all 0.3s;
}

.govco-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  opacity: 0.9;
}

.govco-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.govco-bg-marine {
  background-color: #3366cc;
}

.govco-bg-elf-green {
  background-color: #068460;
}

.govco-bg-shiraz {
  background-color: #A80521;
}

.govco-bg-concrete {
  background-color: #737373;
}

.govco-bg-blue-light {
  background-color: #c9e2ff;
}

.govcolor-blue-dark {
  color: #004884;
}

.govcolor-marine {
  color: #3366cc;
}

@media (max-width: 992px) {
  .filters-grid {
    grid-template-columns: 1fr;
  }

  .modal-body {
    grid-template-columns: 1fr;
  }

  .full-width {
    grid-column: 1;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .decision-buttons {
    grid-column: 1;
    flex-direction: column;
  }
}
</style>