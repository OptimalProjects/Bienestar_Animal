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
              <div class="animal-cell">
                <span class="animal-code-chip">{{ request.animal.code }}</span>
                <strong>{{ request.animal.name }}</strong>
                <small>{{ request.animal.species }} · {{ request.animal.breed }}</small>
              </div>
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
              <dt>Documento:</dt>
              <dd>{{ selectedRequest.applicant.idType }} {{ selectedRequest.applicant.idNumber }}</dd>
              <dt>Edad:</dt>
              <dd>{{ selectedRequest.applicant.age ? `${selectedRequest.applicant.age} años` : 'No calculada' }}</dd>
              <dt>Teléfono:</dt>
              <dd>{{ selectedRequest.applicant.phone || 'No registrado' }}</dd>
              <dt>Email:</dt>
              <dd>{{ selectedRequest.applicant.email || 'No registrado' }}</dd>
              <dt>Dirección:</dt>
              <dd>{{ selectedRequest.applicant.address || 'No registrada' }}</dd>
            </dl>

            <h5 class="subsection-title">Información de Vivienda</h5>
            <dl class="detail-list">
              <dt>Tipo de vivienda:</dt>
              <dd>{{ selectedRequest.applicant.housingType }}</dd>
              <dt>¿Tiene patio?</dt>
              <dd>{{ selectedRequest.applicant.hasYard ? 'Sí' : 'No' }}</dd>
              <dt>Personas en el hogar:</dt>
              <dd>{{ selectedRequest.applicant.householdSize || 'No especificado' }}</dd>
              <dt>Experiencia con animales:</dt>
              <dd>{{ selectedRequest.applicant.petExperience }}</dd>
            </dl>

            <!-- Documentos adjuntos -->
            <div v-if="selectedRequest.applicant.copiaCedulaUrl || selectedRequest.applicant.comprobanteDomicilioUrl" class="documents-section">
              <h5 class="subsection-title">Documentos Adjuntos</h5>
              <div class="documents-links">
                <a
                  v-if="selectedRequest.applicant.copiaCedulaUrl"
                  :href="selectedRequest.applicant.copiaCedulaUrl"
                  target="_blank"
                  class="document-link"
                >
                  📄 Ver copia de cédula
                </a>
                <a
                  v-if="selectedRequest.applicant.comprobanteDomicilioUrl"
                  :href="selectedRequest.applicant.comprobanteDomicilioUrl"
                  target="_blank"
                  class="document-link"
                >
                  📄 Ver comprobante de domicilio
                </a>
              </div>
            </div>
          </div>

          <!-- Información del Animal -->
          <div class="detail-section">
            <h4 class="h6-tipografia-govco govcolor-marine">Animal Solicitado</h4>
            <div class="animal-detail-card">
              <div class="animal-photo-container">
                <img
                  :src="selectedRequest.animal.photoUrl || '/images/placeholder-animal.jpg'"
                  :alt="selectedRequest.animal.name"
                  class="animal-photo-large"
                  @error="handleImageError"
                />
                <span class="animal-code-badge">{{ selectedRequest.animal.code }}</span>
              </div>
              <div class="animal-info">
                <h5 class="animal-name">{{ selectedRequest.animal.name }}</h5>
                <div class="animal-tags">
                  <span class="tag species">{{ selectedRequest.animal.species }}</span>
                  <span class="tag sex">{{ selectedRequest.animal.sex }}</span>
                  <span class="tag size">{{ selectedRequest.animal.size }}</span>
                </div>
              </div>
            </div>

            <dl class="detail-list">
              <dt>Raza:</dt>
              <dd>{{ selectedRequest.animal.breed }}</dd>
              <dt>Edad:</dt>
              <dd>{{ selectedRequest.animal.age }}</dd>
              <dt>Color:</dt>
              <dd>{{ selectedRequest.animal.color }}</dd>
              <dt>Peso:</dt>
              <dd>{{ selectedRequest.animal.weight }}</dd>
              <dt>Esterilizado:</dt>
              <dd>{{ selectedRequest.animal.sterilized ? 'Sí' : 'No' }}</dd>
              <dt>Estado de salud:</dt>
              <dd>
                <span class="health-badge" :class="`health-${selectedRequest.animal.healthStatus}`">
                  {{ getHealthLabel(selectedRequest.animal.healthStatus) }}
                </span>
              </dd>
              <dt>Señas particulares:</dt>
              <dd>{{ selectedRequest.animal.specialMarks }}</dd>
            </dl>

            <div v-if="selectedRequest.animal.observations" class="animal-observations">
              <dt>Observaciones:</dt>
              <dd>{{ selectedRequest.animal.observations }}</dd>
            </div>
          </div>

          <!-- Observaciones de la solicitud -->
          <div v-if="selectedRequest.observaciones" class="detail-section full-width">
            <h4 class="h6-tipografia-govco govcolor-marine">Observaciones de la Solicitud</h4>
            <p class="request-observations">{{ selectedRequest.observaciones }}</p>
          </div>

          <!-- Visita Domiciliaria Pre-adopción -->
          <div class="detail-section full-width visit-section">
            <div class="section-header">
              <h4 class="h6-tipografia-govco govcolor-marine">Visita Domiciliaria Pre-adopción</h4>
              <span v-if="adoptionVisits.length > 0" class="visit-count-badge">
                {{ adoptionVisits.length }} visita(s)
              </span>
            </div>

            <!-- Lista de visitas existentes -->
            <div v-if="adoptionVisits.length > 0" class="visits-list">
              <div
                v-for="visit in adoptionVisits"
                :key="visit.id"
                class="visit-card"
                :class="{ 'visit-completed': visit.fecha_realizada, 'visit-pending': !visit.fecha_realizada }"
              >
                <div class="visit-card-header">
                  <span class="visit-type-badge">{{ getVisitTypeLabel(visit.tipo_visita) }}</span>
                  <span class="visit-status-badge" :class="visit.fecha_realizada ? 'status-done' : 'status-pending'">
                    {{ visit.fecha_realizada ? 'Realizada' : 'Pendiente' }}
                  </span>
                </div>
                <div class="visit-card-body">
                  <div class="visit-date-info">
                    <span class="visit-label">Programada:</span>
                    <span class="visit-value">{{ formatDate(visit.fecha_programada) }}</span>
                  </div>
                  <div v-if="visit.fecha_realizada" class="visit-date-info">
                    <span class="visit-label">Realizada:</span>
                    <span class="visit-value">{{ formatDate(visit.fecha_realizada) }}</span>
                  </div>
                  <div v-if="visit.resultado" class="visit-result">
                    <span class="result-badge" :class="`result-${visit.resultado}`">
                      {{ getResultLabel(visit.resultado) }}
                    </span>
                  </div>
                  <div v-if="visit.visitador" class="visit-visitador">
                    <span class="visit-label">Visitador:</span>
                    <span class="visit-value">{{ visit.visitador.nombre_completo || visit.visitador.nombres }}</span>
                  </div>
                  <div v-if="visit.observaciones" class="visit-observations">
                    <span class="visit-label">Observaciones:</span>
                    <p class="visit-obs-text">{{ visit.observaciones }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Formulario para programar nueva visita -->
            <div class="schedule-visit-form">
              <h5 class="form-subtitle">Programar Nueva Visita</h5>
              <div class="visit-form-grid">
                <div class="form-group">
                  <label for="visitType">Tipo de visita*</label>
                  <select id="visitType" v-model="newVisit.tipo_visita" class="form-control">
                    <option value="">Seleccione...</option>
                    <option value="pre_adopcion">Pre-adopción</option>
                    <option value="seguimiento_1mes">Seguimiento 1 mes</option>
                    <option value="seguimiento_3meses">Seguimiento 3 meses</option>
                    <option value="seguimiento_6meses">Seguimiento 6 meses</option>
                    <option value="extraordinaria">Extraordinaria</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="visitDate">Fecha*</label>
                  <input
                    type="date"
                    id="visitDate"
                    v-model="newVisit.fecha_programada"
                    class="form-control"
                    :min="minDate"
                  />
                </div>

                <div class="form-group">
                  <label for="visitNotes">Observaciones</label>
                  <input
                    type="text"
                    id="visitNotes"
                    v-model="newVisit.observaciones"
                    class="form-control"
                    placeholder="Notas adicionales..."
                  />
                </div>

                <button
                  @click="scheduleNewVisit"
                  class="govco-btn govco-bg-elf-green schedule-btn"
                  :disabled="!newVisit.tipo_visita || !newVisit.fecha_programada || schedulingVisit"
                >
                  <span v-if="schedulingVisit">Programando...</span>
                  <span v-else>+ Programar Visita</span>
                </button>
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

          <!-- Sección de Contrato (Vista Coordinador - Solo lectura) -->
          <div v-if="selectedRequest.status === 'approved'" class="detail-section full-width contract-section">
            <div class="section-header">
              <h4 class="h6-tipografia-govco govcolor-marine">Contrato de Adopción</h4>
              <span v-if="contractStatus.firmado" class="contract-signed-badge">
                Firmado
              </span>
              <span v-else class="contract-pending-badge">
                Pendiente de firma
              </span>
            </div>

            <!-- Estado del contrato -->
            <div class="contract-status-card">
              <div class="contract-info">
                <div v-if="contractStatus.numero_contrato" class="contract-number">
                  <span class="label">No. Contrato:</span>
                  <span class="value">{{ contractStatus.numero_contrato }}</span>
                </div>
                <div v-if="contractStatus.fecha_contrato" class="contract-date">
                  <span class="label">Fecha generación:</span>
                  <span class="value">{{ formatDate(contractStatus.fecha_contrato) }}</span>
                </div>
                <div v-if="contractStatus.firmado && contractStatus.fecha_firma" class="contract-signed">
                  <span class="label">Firmado el:</span>
                  <span class="value">{{ formatDate(contractStatus.fecha_firma) }}</span>
                </div>
                <div class="contract-status-info">
                  <span class="label">Estado:</span>
                  <span class="value" :class="contractStatus.firmado ? 'status-signed' : 'status-pending'">
                    {{ contractStatus.firmado ? 'Completado' : 'Esperando firma del adoptante' }}
                  </span>
                </div>
              </div>

              <div class="contract-actions">
                <button
                  @click="previewContract"
                  class="govco-btn govco-bg-marine contract-btn"
                  :disabled="loadingContract"
                >
                  <span v-if="loadingContract">Cargando...</span>
                  <span v-else>Ver Contrato</span>
                </button>
                <button
                  @click="downloadContractPdf"
                  class="govco-btn govco-bg-elf-green contract-btn"
                  :disabled="loadingContract"
                >
                  Descargar PDF
                </button>
              </div>
            </div>

            <!-- Información para el coordinador -->
            <div v-if="!contractStatus.firmado" class="coordinator-info-box">
              <p>
                El contrato fue generado automáticamente al aprobar la visita domiciliaria.
                El adoptante debe firmar el contrato desde el portal de consulta de adopciones.
              </p>
            </div>

            <!-- Mensaje de contrato firmado con seguimientos -->
            <div v-if="contractStatus.firmado" class="contract-completed-message">
              <div class="success-icon">✓</div>
              <h5>Adopción Completada</h5>
              <p>El adoptante firmó el contrato y se programaron los seguimientos post-adopción automáticamente.</p>

              <!-- Seguimientos programados -->
              <div v-if="contractStatus.seguimientos && contractStatus.seguimientos.length > 0" class="scheduled-followups">
                <h6>Seguimientos Post-adopción Programados:</h6>
                <ul>
                  <li v-for="seguimiento in contractStatus.seguimientos" :key="seguimiento.id">
                    <span class="followup-type">{{ getVisitTypeLabel(seguimiento.tipo_visita) }}</span>
                    <span class="followup-date">{{ formatDate(seguimiento.fecha_programada) }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button @click="closeModal" class="govco-btn govco-bg-concrete">
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch, nextTick } from 'vue';
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

// Visitas de la adopción seleccionada
const adoptionVisits = ref([]);
const loadingVisits = ref(false);

// Estado del contrato (solo lectura para coordinador)
const contractStatus = ref({
  firmado: false,
  numero_contrato: null,
  fecha_contrato: null,
  fecha_firma: null,
  contrato_url: null,
  seguimientos: []
});
const loadingContract = ref(false);

// Nueva visita a programar
const newVisit = reactive({
  tipo_visita: '',
  fecha_programada: '',
  observaciones: '',
});
const schedulingVisit = ref(false);

// Fecha mínima para programar (hoy)
const minDate = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0];
});

// Datos de visita domiciliaria (legacy)
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
  'solicitada': 'pending',
  'pendiente': 'pending',
  'en_evaluacion': 'in_evaluation',
  'aprobada': 'approved',
  'rechazada': 'rejected',
  'completada': 'approved',
  'revocada': 'rejected'
};

const reverseStatusMap = {
  'pending': 'solicitada',
  'in_evaluation': 'en_evaluacion',
  'approved': 'aprobada',
  'rejected': 'rechazada'
};

// Helper para obtener etiqueta de tipo de vivienda
function getHousingTypeLabel(tipo) {
  const tipos = {
    'casa': 'Casa',
    'apartamento': 'Apartamento',
    'finca': 'Finca',
    'otro': 'Otro'
  };
  return tipos[tipo] || tipo || 'No especificado';
}

// Helper para obtener etiqueta de sexo
function getSexLabel(sexo) {
  const sexos = {
    'macho': 'Macho',
    'hembra': 'Hembra'
  };
  return sexos[sexo] || sexo || 'No especificado';
}

// Helper para obtener etiqueta de tamaño
function getSizeLabel(size) {
  const sizes = {
    'pequeno': 'Pequeño',
    'mediano': 'Mediano',
    'grande': 'Grande'
  };
  return sizes[size] || size || 'No especificado';
}

// Transforma los datos del backend al formato del frontend
function transformRequest(backendRequest) {
  const adoptante = backendRequest.adoptante || {};
  const animal = backendRequest.animal || {};
  const evaluador = backendRequest.evaluador || {};

  return {
    id: backendRequest.id,
    status: statusMap[backendRequest.estado] || backendRequest.estado,
    applicant: {
      // Campos del modelo Adoptante
      name: adoptante.nombre_completo || `${adoptante.nombres || ''} ${adoptante.apellidos || ''}`.trim() || 'Sin nombre',
      idType: adoptante.tipo_documento || '',
      idNumber: adoptante.numero_documento || '',
      birthDate: adoptante.fecha_nacimiento || '',
      age: adoptante.edad || '',
      phone: adoptante.telefono || '',
      email: adoptante.email || '',
      address: adoptante.direccion || '',
      housingType: getHousingTypeLabel(adoptante.tipo_vivienda),
      housingTypeRaw: adoptante.tipo_vivienda || '',
      hasYard: adoptante.tiene_patio,
      petExperience: adoptante.experiencia_animales || 'No especificado',
      householdSize: adoptante.num_personas_hogar || '',
      // URLs de documentos
      copiaCedulaUrl: adoptante.copia_cedula_url || null,
      comprobanteDomicilioUrl: adoptante.comprobante_domicilio_url || null,
    },
    animal: {
      // Campos del modelo Animal
      id: animal.id,
      code: animal.codigo_unico || 'Sin código',
      name: animal.nombre || animal.codigo_unico || 'Sin nombre',
      species: animal.especie || '',
      breed: animal.raza || 'Sin raza definida',
      sex: getSexLabel(animal.sexo),
      sexRaw: animal.sexo || '',
      size: getSizeLabel(animal.tamanio),
      sizeRaw: animal.tamanio || '',
      color: animal.color || 'No especificado',
      weight: animal.peso_actual ? `${animal.peso_actual} kg` : 'No registrado',
      age: animal.edad_formateada || 'Desconocida',
      sterilized: animal.esterilizacion,
      healthStatus: animal.estado_salud || '',
      specialMarks: animal.senias_particulares || 'Ninguna',
      photoUrl: animal.foto_url || null,
      galleryUrls: animal.galeria_urls || [],
      observations: animal.observaciones || '',
      rescueDate: animal.fecha_rescate || '',
      rescueLocation: animal.ubicacion_rescate || '',
    },
    createdAt: backendRequest.fecha_solicitud || backendRequest.created_at,
    observaciones: backendRequest.observaciones || '',
    homeVisit: {
      scheduled: !!backendRequest.fecha_visita,
      date: backendRequest.fecha_visita || '',
      time: backendRequest.hora_visita || '',
      observations: backendRequest.observaciones_visita || '',
      completed: backendRequest.visita_realizada || false,
    },
    decision: {
      evaluator: evaluador.nombre_completo || `${evaluador.nombres || ''} ${evaluador.apellidos || ''}`.trim() || '',
      date: backendRequest.fecha_aprobacion || backendRequest.updated_at || '',
      justification: backendRequest.motivo_rechazo || backendRequest.observaciones || '',
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

async function viewDetails(request) {
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
  // Reset nueva visita
  newVisit.tipo_visita = '';
  newVisit.fecha_programada = '';
  newVisit.observaciones = '';

  // Reset estado del contrato
  contractStatus.value = {
    firmado: false,
    numero_contrato: null,
    fecha_contrato: null,
    fecha_firma: null,
    contrato_url: null,
    seguimientos: []
  };

  showModal.value = true;

  // Cargar visitas de esta adopción
  await loadAdoptionVisits(request.id);

  // Si la solicitud está aprobada, cargar estado del contrato
  if (request.status === 'approved') {
    await loadContractStatus(request.id);
  }
}

async function loadAdoptionVisits(adopcionId) {
  loadingVisits.value = true;
  adoptionVisits.value = [];
  try {
    const response = await adoptionService.fetchVisitsByAdoption(adopcionId);
    const data = response.data || response;
    adoptionVisits.value = Array.isArray(data) ? data : (data.data || []);
  } catch (err) {
    console.error('Error al cargar visitas:', err);
    adoptionVisits.value = [];
  } finally {
    loadingVisits.value = false;
  }
}

function closeModal() {
  showModal.value = false;
  selectedRequest.value = null;
  adoptionVisits.value = [];
  // Limpiar estado del contrato
  contractStatus.value = {
    firmado: false,
    numero_contrato: null,
    fecha_contrato: null,
    fecha_firma: null,
    contrato_url: null,
    seguimientos: []
  };
}

async function scheduleNewVisit() {
  if (!selectedRequest.value || !newVisit.tipo_visita || !newVisit.fecha_programada) {
    if (window.$toast) {
      window.$toast.warning('Atención', 'Debe seleccionar tipo de visita y fecha');
    } else {
      alert('Debe seleccionar tipo de visita y fecha');
    }
    return;
  }

  schedulingVisit.value = true;
  try {
    await adoptionService.scheduleFollowUpVisit({
      adopcion_id: selectedRequest.value.id,
      tipo_visita: newVisit.tipo_visita,
      fecha_programada: newVisit.fecha_programada,
      observaciones: newVisit.observaciones || null,
    });

    if (window.$toast) {
      window.$toast.success('Visita programada exitosamente');
    } else {
      alert('Visita programada exitosamente');
    }

    // Limpiar formulario y recargar visitas
    newVisit.tipo_visita = '';
    newVisit.fecha_programada = '';
    newVisit.observaciones = '';
    await loadAdoptionVisits(selectedRequest.value.id);

  } catch (err) {
    console.error('Error al programar visita:', err);
    const errorMsg = err.response?.data?.message || 'Error al programar la visita';
    if (window.$toast) {
      window.$toast.error('Error', errorMsg);
    } else {
      alert(errorMsg);
    }
  } finally {
    schedulingVisit.value = false;
  }
}

// Legacy function - mantener para compatibilidad
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
      tipo_visita: 'pre_adopcion',
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

// ============================================
// CONTRATO Y FIRMA ELECTRÓNICA
// ============================================

async function loadContractStatus(adopcionId) {
  loadingContract.value = true;
  try {
    const response = await adoptionService.fetchContractStatus(adopcionId);
    const data = response.data || response;
    contractStatus.value = {
      firmado: data.firmado || false,
      numero_contrato: data.numero_contrato || null,
      fecha_contrato: data.fecha_contrato || null,
      fecha_firma: data.fecha_firma || null,
      contrato_url: data.contrato_url || null,
      seguimientos: data.seguimientos || []
    };
  } catch (err) {
    console.error('Error al cargar estado del contrato:', err);
    // Si no existe contrato, dejar valores por defecto
    contractStatus.value = {
      firmado: false,
      numero_contrato: null,
      fecha_contrato: null,
      fecha_firma: null,
      contrato_url: null,
      seguimientos: []
    };
  } finally {
    loadingContract.value = false;
  }
}

async function previewContract() {
  if (!selectedRequest.value) return;

  loadingContract.value = true;
  try {
    const response = await adoptionService.fetchAdoptionContract(selectedRequest.value.id);
    const data = response.data || response;

    if (data.contrato_url) {
      window.open(data.contrato_url, '_blank');
    } else if (window.$toast) {
      window.$toast.info('Info', 'El contrato aún no ha sido generado');
    }
  } catch (err) {
    console.error('Error al cargar contrato:', err);
    const errorMsg = err.response?.data?.message || 'Error al cargar el contrato';
    if (window.$toast) {
      window.$toast.error('Error', errorMsg);
    } else {
      alert(errorMsg);
    }
  } finally {
    loadingContract.value = false;
  }
}

async function downloadContractPdf() {
  if (!selectedRequest.value) return;

  loadingContract.value = true;
  try {
    const blob = await adoptionService.downloadAdoptionContract(selectedRequest.value.id);

    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `contrato-adopcion-${contractStatus.value.numero_contrato || selectedRequest.value.id}.pdf`;
    link.click();
    window.URL.revokeObjectURL(url);

    if (window.$toast) {
      window.$toast.success('Éxito', 'Contrato descargado');
    }
  } catch (err) {
    console.error('Error al descargar contrato:', err);
    const errorMsg = err.response?.data?.message || 'Error al descargar el contrato';
    if (window.$toast) {
      window.$toast.error('Error', errorMsg);
    } else {
      alert(errorMsg);
    }
  } finally {
    loadingContract.value = false;
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

function getHealthLabel(health) {
  const labels = {
    'excelente': 'Excelente',
    'bueno': 'Bueno',
    'estable': 'Estable',
    'en_tratamiento': 'En tratamiento',
    'critico': 'Crítico'
  };
  return labels[health] || health || 'No especificado';
}

function getVisitTypeLabel(tipo) {
  const labels = {
    'pre_adopcion': 'Pre-adopción',
    'seguimiento_1mes': 'Seguimiento 1 mes',
    'seguimiento_3meses': 'Seguimiento 3 meses',
    'seguimiento_6meses': 'Seguimiento 6 meses',
    'extraordinaria': 'Extraordinaria'
  };
  return labels[tipo] || tipo || 'No especificado';
}

function getResultLabel(resultado) {
  const labels = {
    'satisfactoria': 'Satisfactoria',
    'observaciones': 'Con observaciones',
    'critica': 'Crítica'
  };
  return labels[resultado] || resultado || 'No especificado';
}

function handleImageError(event) {
  event.target.src = '/images/placeholder-animal.jpg';
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

/* Animal cell en tabla */
.animal-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.animal-code-chip {
  display: inline-block;
  background: #E8F0FE;
  color: #3366CC;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 12px;
  width: fit-content;
  font-family: monospace;
}

/* Subsection title */
.subsection-title {
  font-size: 0.9rem;
  color: #004884;
  margin: 1.5rem 0 0.5rem 0;
  padding-top: 1rem;
  border-top: 1px solid #E0E0E0;
}

/* Documents section */
.documents-section {
  margin-top: 1rem;
}

.documents-links {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.document-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #E8F0FE;
  color: #3366CC;
  border-radius: 6px;
  text-decoration: none;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.document-link:hover {
  background: #3366CC;
  color: white;
}

/* Animal detail card */
.animal-detail-card {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
  margin-bottom: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 8px;
}

.animal-photo-container {
  position: relative;
  flex-shrink: 0;
}

.animal-photo-large {
  width: 140px;
  height: 140px;
  border-radius: 12px;
  object-fit: cover;
  background: linear-gradient(135deg, #3366cc 0%, #004884 100%);
}

.animal-code-badge {
  position: absolute;
  bottom: -8px;
  left: 50%;
  transform: translateX(-50%);
  background: #004884;
  color: white;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 12px;
  font-family: monospace;
  white-space: nowrap;
}

.animal-info {
  flex: 1;
}

.animal-name {
  font-size: 1.2rem;
  color: #004884;
  margin: 0 0 0.75rem 0;
}

.animal-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.animal-tags .tag {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 16px;
  font-size: 0.8rem;
  font-weight: 600;
}

.animal-tags .tag.species {
  background: #E3F2FD;
  color: #1565C0;
}

.animal-tags .tag.sex {
  background: #F3E5F5;
  color: #7B1FA2;
}

.animal-tags .tag.size {
  background: #E8F5E9;
  color: #2E7D32;
}

/* Health badge */
.health-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
}

.health-excelente,
.health-bueno {
  background: #E8F5E9;
  color: #2E7D32;
}

.health-estable {
  background: #E3F2FD;
  color: #1565C0;
}

.health-en_tratamiento {
  background: #FFF3E0;
  color: #E65100;
}

.health-critico {
  background: #FFEBEE;
  color: #C62828;
}

/* Animal observations */
.animal-observations {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #E0E0E0;
}

/* Request observations */
.request-observations {
  background: white;
  padding: 1rem;
  border-radius: 6px;
  white-space: pre-wrap;
  line-height: 1.6;
  color: #4B4B4B;
}

/* ===== VISITAS SECTION ===== */
.visit-section {
  border: 2px solid #E8F0FE;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.visit-count-badge {
  background: #3366CC;
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 12px;
}

/* Lista de visitas */
.visits-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.visit-card {
  background: white;
  border-radius: 8px;
  border: 1px solid #E0E0E0;
  overflow: hidden;
  transition: all 0.2s;
}

.visit-card:hover {
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.visit-card.visit-completed {
  border-left: 4px solid #068460;
}

.visit-card.visit-pending {
  border-left: 4px solid #FFAB00;
}

.visit-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  background: #F6F8F9;
}

.visit-type-badge {
  background: #E8F0FE;
  color: #3366CC;
  font-size: 0.8rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 12px;
}

.visit-status-badge {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 10px;
}

.visit-status-badge.status-done {
  background: #E8F5E9;
  color: #2E7D32;
}

.visit-status-badge.status-pending {
  background: #FFF8E1;
  color: #F57F17;
}

.visit-card-body {
  padding: 1rem;
}

.visit-date-info {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.visit-label {
  font-weight: 600;
  color: #4B4B4B;
  font-size: 0.85rem;
}

.visit-value {
  color: #737373;
  font-size: 0.85rem;
}

.visit-result {
  margin: 0.75rem 0;
}

.result-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
}

.result-badge.result-satisfactoria {
  background: #E8F5E9;
  color: #2E7D32;
}

.result-badge.result-observaciones {
  background: #FFF3E0;
  color: #E65100;
}

.result-badge.result-critica {
  background: #FFEBEE;
  color: #C62828;
}

.visit-visitador {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.visit-observations {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px dashed #E0E0E0;
}

.visit-obs-text {
  margin: 0.25rem 0 0 0;
  color: #737373;
  font-size: 0.9rem;
  line-height: 1.5;
}

/* Formulario nueva visita */
.schedule-visit-form {
  background: white;
  padding: 1.25rem;
  border-radius: 8px;
  border: 1px dashed #3366CC;
}

.form-subtitle {
  font-size: 1rem;
  color: #004884;
  margin: 0 0 1rem 0;
}

.visit-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1.5fr auto;
  gap: 1rem;
  align-items: end;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.form-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #4B4B4B;
}

.form-control {
  padding: 0.5rem 0.75rem;
  border: 1px solid #E0E0E0;
  border-radius: 6px;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.1);
}

.schedule-btn {
  white-space: nowrap;
  padding: 0.5rem 1.25rem;
  height: fit-content;
}

/* ===== CONTRATO Y FIRMA ELECTRÓNICA ===== */
.contract-section {
  border: 2px solid #068460;
  background: #F0FFF4;
}

.contract-signed-badge {
  background: #068460;
  color: white;
  font-size: 0.8rem;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: 12px;
}

.contract-status-card {
  background: white;
  padding: 1.25rem;
  border-radius: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.contract-info {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.contract-info > div {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.contract-info .label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #737373;
  text-transform: uppercase;
}

.contract-info .value {
  font-size: 1rem;
  font-weight: 600;
  color: #004884;
}

.contract-actions {
  display: flex;
  gap: 0.75rem;
}

.contract-btn {
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
}

/* Firma Electrónica */
.signature-section {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  border: 2px dashed #3366CC;
}

.signature-instructions {
  color: #737373;
  font-size: 0.9rem;
  margin-bottom: 1rem;
}

.signature-container {
  position: relative;
  margin-bottom: 1rem;
}

.signature-canvas {
  width: 100%;
  height: 200px;
  border: 2px solid #E0E0E0;
  border-radius: 8px;
  cursor: crosshair;
  touch-action: none;
}

.clear-signature-btn {
  position: absolute;
  top: 8px;
  right: 8px;
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid #E0E0E0;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s;
}

.clear-signature-btn:hover {
  background: #f5f5f5;
  border-color: #ccc;
}

/* Checkbox de términos */
.signature-terms {
  margin-bottom: 1.5rem;
}

.checkbox-container {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  cursor: pointer;
}

.checkbox-container input[type="checkbox"] {
  width: 20px;
  height: 20px;
  margin-top: 2px;
  cursor: pointer;
  accent-color: #068460;
}

.terms-text {
  font-size: 0.9rem;
  color: #4B4B4B;
  line-height: 1.5;
}

.submit-signature-btn {
  width: 100%;
  padding: 1rem;
  font-size: 1rem;
}

/* Mensaje de contrato firmado */
.contract-completed-message {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  text-align: center;
}

.contract-completed-message .success-icon {
  width: 60px;
  height: 60px;
  background: #E8F5E9;
  color: #068460;
  font-size: 2rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
}

.contract-completed-message h5 {
  color: #068460;
  font-size: 1.25rem;
  margin: 0 0 0.5rem 0;
}

.contract-completed-message p {
  color: #737373;
  margin: 0 0 1.5rem 0;
}

/* Seguimientos programados */
.scheduled-followups {
  background: #F6F8F9;
  padding: 1rem;
  border-radius: 8px;
  text-align: left;
}

.scheduled-followups h6 {
  color: #004884;
  font-size: 0.9rem;
  margin: 0 0 0.75rem 0;
}

.scheduled-followups ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.scheduled-followups li {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #E0E0E0;
}

.scheduled-followups li:last-child {
  border-bottom: none;
}

.followup-type {
  font-weight: 600;
  color: #4B4B4B;
}

.followup-date {
  color: #737373;
  font-size: 0.9rem;
}

/* Badge pendiente de firma */
.contract-pending-badge {
  background: #FFAB00;
  color: white;
  font-size: 0.8rem;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: 12px;
}

/* Estado del contrato valores */
.contract-info .value.status-signed {
  color: #068460;
}

.contract-info .value.status-pending {
  color: #FFAB00;
}

/* Box informativo para coordinador */
.coordinator-info-box {
  background: #FFF8E1;
  border: 1px solid #FFE082;
  border-radius: 8px;
  padding: 1rem 1.25rem;
  margin-bottom: 1rem;
}

.coordinator-info-box p {
  margin: 0;
  color: #5D4037;
  font-size: 0.9rem;
  line-height: 1.5;
}

@media (max-width: 992px) {
  .filters-grid {
    grid-template-columns: 1fr;
  }

  .visit-form-grid {
    grid-template-columns: 1fr;
  }

  .schedule-btn {
    width: 100%;
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

  .contract-status-card {
    flex-direction: column;
    align-items: stretch;
  }

  .contract-actions {
    flex-direction: column;
  }

  .contract-btn {
    width: 100%;
  }
}
</style>