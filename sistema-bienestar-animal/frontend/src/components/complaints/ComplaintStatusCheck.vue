<!-- src/components/complaints/ComplaintStatusCheck.vue -->
<!-- HU-018: Consultar Estado de Denuncia -->
<template>
  <section class="status-check">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Consultar Estado de Denuncia</h2>
      <p class="text2-tipografia-govco">
        Ingrese el número de caso para conocer el estado actual de su denuncia.
        <strong>No requiere autenticación.</strong>
      </p>
    </div>

    <!-- FORMULARIO DE BÚSQUEDA -->
    <div class="form-section search-section">
      <div class="search-container">
        <div class="entradas-de-texto-govco search-input">
          <label for="caseNumber">Número de caso</label>
          <input
            type="text"
            id="caseNumber"
            v-model="caseNumber"
            placeholder="Ej: DEN-202411-0001"
            @keyup.enter="searchCase"
          />
          <span class="info-entradas-de-texto-govco">
            El número de caso fue enviado a su correo al registrar la denuncia
          </span>
        </div>
        <button
          type="button"
          class="govco-btn govco-bg-marine"
          :disabled="isSearching || !caseNumber.trim()"
          @click="searchCase"
        >
          {{ isSearching ? 'Buscando...' : 'Consultar' }}
        </button>
      </div>

      <span v-if="searchError" class="error-text">{{ searchError }}</span>
    </div>

    <!-- RESULTADO DE LA BÚSQUEDA -->
    <div v-if="complaint" class="form-section result-section">
      <!-- Header con estado -->
      <div class="result-header" :class="`status-${complaint.estado}`">
        <div class="result-header-info">
          <h3 class="h4-tipografia-govco">Caso: {{ complaint.caso_numero }}</h3>
          <span class="status-badge" :class="`badge-${complaint.estado}`">
            {{ getStatusLabel(complaint.estado) }}
          </span>
        </div>
        <div class="urgency-indicator" :class="`urgency-${complaint.urgencia}`">
          {{ getUrgencyLabel(complaint.urgencia) }}
        </div>
      </div>

      <!-- Información general -->
      <div class="result-body">
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Tipo de denuncia</span>
            <span class="info-value">{{ getComplaintTypeLabel(complaint.tipo_denuncia) }}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Fecha de recepción</span>
            <span class="info-value">{{ formatDate(complaint.fecha_recepcion) }}</span>
          </div>
          <div class="info-item full-width">
            <span class="info-label">Dirección</span>
            <span class="info-value">{{ complaint.ubicacion || 'No especificada' }}</span>
          </div>
          <div v-if="complaint.observaciones" class="info-item full-width">
            <span class="info-label">Observaciones</span>
            <span class="info-value">{{ complaint.observaciones }}</span>
          </div>
        </div>

        <!-- Línea de tiempo -->
        <div class="timeline-section">
          <h4 class="h6-tipografia-govco">Seguimiento del caso</h4>
          <div class="timeline">
            <div
              v-for="(event, index) in complaint.timeline"
              :key="index"
              class="timeline-item"
              :class="{ 'active': index === complaint.timeline.length - 1 }"
            >
              <div class="timeline-marker">
                <span class="marker-icon">{{ getTimelineIcon(event.tipo) }}</span>
              </div>
              <div class="timeline-content">
                <span class="timeline-date">{{ formatDateTime(event.fecha) }}</span>
                <span class="timeline-title">{{ event.titulo }}</span>
                <p class="timeline-description">{{ event.descripcion }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Resultado final (si está cerrado) -->
        <div v-if="complaint.estado === 'cerrada'" class="result-final">
          <h4 class="h6-tipografia-govco">Resultado del caso</h4>
          <div class="result-box" :class="`result-${complaint.resultado}`">
            <span class="result-icon">{{ getResultIcon(complaint.resultado) }}</span>
            <div class="result-info">
              <span class="result-title">{{ getResultLabel(complaint.resultado) }}</span>
              <p class="result-description">{{ complaint.resultado_descripcion }}</p>
            </div>
          </div>
        </div>

        <!-- Información de contacto (si fue asignado) -->
        <div v-if="complaint.equipo_asignado && !complaint.es_anonimo" class="contact-section">
          <h4 class="h6-tipografia-govco">Equipo asignado</h4>
          <p class="text2-tipografia-govco">
            Su caso está siendo atendido. Si necesita comunicarse, puede hacerlo a través de los canales oficiales de la Secretaría de Bienestar Animal.
          </p>
        </div>
      </div>
    </div>

    <!-- ESTADO: NO ENCONTRADO -->
    <div v-if="notFound" class="form-section not-found-section">
      <div class="not-found-content">
        <span class="not-found-icon">🔍</span>
        <h3 class="h5-tipografia-govco">Caso no encontrado</h3>
        <p class="text2-tipografia-govco">
          No se encontró ninguna denuncia con el número de caso <strong>{{ searchedCase }}</strong>.
        </p>
        <ul class="suggestions-list">
          <li>Verifique que el número de caso esté escrito correctamente</li>
          <li>El formato debe ser: DEN-AAAAMM-XXXX (ej: DEN-202411-0001)</li>
          <li>Si acaba de registrar la denuncia, espere unos minutos e intente nuevamente</li>
        </ul>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue';
import { useComplaintsStore } from '@/stores/complaints';

const complaintsStore = useComplaintsStore();

const caseNumber = ref('');
const isSearching = ref(false);
const searchError = ref('');
const complaint = ref(null);
const notFound = ref(false);
const searchedCase = ref('');

async function searchCase() {
  if (!caseNumber.value.trim()) {
    searchError.value = 'Ingrese un numero de caso';
    return;
  }

  searchError.value = '';
  complaint.value = null;
  notFound.value = false;
  isSearching.value = true;
  searchedCase.value = caseNumber.value.trim().toUpperCase();

  try {
    console.log('Buscando denuncia con ticket:', searchedCase.value);

    const data = await complaintsStore.consultarTicket(searchedCase.value);

    console.log('Respuesta del backend:', data);

    if (data) {
      // Mapear datos del backend al formato del componente
      complaint.value = {
        caso_numero: data.ticket,
        tipo_denuncia: data.tipo,
        urgencia: data.prioridad,
        fecha_recepcion: data.fecha_registro,
        estado: data.estado,
        ubicacion: data.ubicacion,
        observaciones: data.observaciones,
        fecha_resolucion: data.fecha_resolucion,
        // Generar timeline basico segun el estado
        timeline: generarTimeline(data)
      };
    } else {
      notFound.value = true;
    }

  } catch (error) {
    console.error('Error al buscar caso:', error);
    notFound.value = true;
    searchError.value = '';
  } finally {
    isSearching.value = false;
  }
}

// Generar timeline basico segun el estado de la denuncia
function generarTimeline(data) {
  const timeline = [
    {
      tipo: 'recepcion',
      fecha: data.fecha_registro,
      titulo: 'Denuncia recibida',
      descripcion: 'Su denuncia ha sido registrada en el sistema.'
    }
  ];

  if (data.estado === 'en_proceso' || data.estado === 'resuelta') {
    timeline.push({
      tipo: 'revision',
      fecha: data.fecha_registro,
      titulo: 'En proceso',
      descripcion: 'El caso esta siendo atendido por nuestro equipo.'
    });
  }

  if (data.estado === 'resuelta') {
    timeline.push({
      tipo: 'cierre',
      fecha: data.fecha_resolucion || data.fecha_registro,
      titulo: 'Caso resuelto',
      descripcion: data.resolucion || 'El caso ha sido resuelto exitosamente.'
    });
  }

  return timeline;
}

// Helpers para labels
function getStatusLabel(status) {
  const labels = {
    'recibida': 'Recibida',
    'en_revision': 'En revisión',
    'asignada': 'Asignada',
    'en_atencion': 'En atención',
    'cerrada': 'Cerrada',
    'derivada': 'Derivada a autoridades'
  };
  return labels[status] || status;
}

function getUrgencyLabel(urgency) {
  const labels = {
    'urgente': 'URGENTE',
    'alta': 'ALTA',
    'media': 'MEDIA',
    'baja': 'BAJA'
  };
  return labels[urgency] || urgency?.toUpperCase() || '';
}

function getComplaintTypeLabel(type) {
  const labels = {
    'maltrato': 'Maltrato',
    'abandono': 'Abandono',
    'animal_herido': 'Animal herido',
    'animal_peligroso': 'Animal peligroso',
    'otro': 'Otro'
  };
  return labels[type] || type;
}

function getSpeciesLabel(species) {
  const labels = {
    'perro': 'Perro',
    'gato': 'Gato',
    'equino': 'Equino',
    'bovino': 'Bovino',
    'ave': 'Ave',
    'otro': 'Otro',
    'desconocido': 'Desconocido'
  };
  return labels[species] || species;
}

function getResultLabel(result) {
  const labels = {
    'rescatado': 'Animal(es) rescatado(s)',
    'no_encontrado': 'No se encontró el animal',
    'derivado': 'Derivado a autoridades competentes',
    'sin_merito': 'Caso cerrado sin mérito'
  };
  return labels[result] || result;
}

function getResultIcon(result) {
  const icons = {
    'rescatado': '✅',
    'no_encontrado': '❌',
    'derivado': '📋',
    'sin_merito': '⚠️'
  };
  return icons[result] || '📋';
}

function getTimelineIcon(type) {
  const icons = {
    'recepcion': '📥',
    'revision': '🔍',
    'asignacion': '👥',
    'en_camino': '🚗',
    'operativo': '🦺',
    'cierre': '✅'
  };
  return icons[type] || '📌';
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

function formatDateTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}
</script>

<style scoped>
.status-check {
  max-width: 900px;
  margin: 0 auto;
  padding: 2rem;
  background: #f5f7fb;
}

.form-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #3366CC;
}

.form-section {
  background: white;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

/* Búsqueda */
.search-section {
  padding: 2rem;
}

.search-container {
  display: flex;
  gap: 1rem;
  align-items: flex-end;
}

.search-input {
  flex: 1;
}

.search-input input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  height: 44px;
}

.info-entradas-de-texto-govco {
  display: block;
  color: #666;
  font-size: 0.85rem;
  margin-top: 0.25rem;
}

.error-text {
  display: block;
  color: #b00020;
  font-size: 0.85rem;
  margin-top: 0.5rem;
}

.govco-btn {
  padding: 0.75rem 2rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  color: white;
  height: 44px;
  white-space: nowrap;
}

.govco-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.govco-bg-marine {
  background-color: #3366CC;
}

/* Resultado */
.result-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 3px solid;
}

.result-header.status-recibida { border-color: #737373; background: #f5f5f5; }
.result-header.status-en_revision { border-color: #3366CC; background: #E8F0FE; }
.result-header.status-asignada { border-color: #FFAB00; background: #FFF8E1; }
.result-header.status-en_atencion { border-color: #FFAB00; background: #FFF8E1; }
.result-header.status-cerrada { border-color: #068460; background: #E8F5E9; }
.result-header.status-derivada { border-color: #A80521; background: #FFEBEE; }

.result-header-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.result-header-info h3 {
  margin: 0;
  color: #004884;
}

.status-badge {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  color: white;
}

.badge-recibida { background: #737373; }
.badge-en_revision { background: #3366CC; }
.badge-asignada { background: #FFAB00; color: #333; }
.badge-en_atencion { background: #FFAB00; color: #333; }
.badge-cerrada { background: #068460; }
.badge-derivada { background: #A80521; }

.urgency-indicator {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: bold;
  letter-spacing: 1px;
}

.urgency-urgente { background: #A80521; color: white; }
.urgency-alta { background: #FFAB00; color: #333; }
.urgency-media { background: #3366CC; color: white; }
.urgency-baja { background: #737373; color: white; }

.result-body {
  padding: 1.5rem;
}

/* Info grid */
.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #E0E0E0;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.info-item.full-width {
  grid-column: 1 / -1;
}

.info-label {
  font-size: 0.85rem;
  color: #666;
  font-weight: 500;
}

.info-value {
  font-size: 1rem;
  color: #333;
  font-weight: 600;
}

/* Timeline */
.timeline-section h4 {
  margin: 0 0 1rem 0;
  color: #004884;
}

.timeline {
  position: relative;
  padding-left: 2rem;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 0.75rem;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #E0E0E0;
}

.timeline-item {
  position: relative;
  padding-bottom: 1.5rem;
}

.timeline-item:last-child {
  padding-bottom: 0;
}

.timeline-item.active .timeline-marker {
  background: #068460;
  border-color: #068460;
}

.timeline-item.active .timeline-marker .marker-icon {
  filter: brightness(0) invert(1);
}

.timeline-marker {
  position: absolute;
  left: -2rem;
  width: 1.5rem;
  height: 1.5rem;
  background: white;
  border: 2px solid #3366CC;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.marker-icon {
  font-size: 0.75rem;
}

.timeline-content {
  padding-left: 0.5rem;
}

.timeline-date {
  display: block;
  font-size: 0.8rem;
  color: #666;
  margin-bottom: 0.25rem;
}

.timeline-title {
  display: block;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.25rem;
}

.timeline-description {
  margin: 0;
  font-size: 0.9rem;
  color: #666;
  line-height: 1.4;
}

/* Resultado final */
.result-final {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #E0E0E0;
}

.result-final h4 {
  margin: 0 0 1rem 0;
  color: #004884;
}

.result-box {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border-radius: 8px;
  align-items: flex-start;
}

.result-box.result-rescatado {
  background: #E8F5E9;
  border: 1px solid #068460;
}

.result-box.result-no_encontrado {
  background: #FFEBEE;
  border: 1px solid #A80521;
}

.result-box.result-derivado {
  background: #FFF8E1;
  border: 1px solid #FFAB00;
}

.result-icon {
  font-size: 1.5rem;
}

.result-info {
  flex: 1;
}

.result-title {
  display: block;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.25rem;
}

.result-description {
  margin: 0;
  font-size: 0.9rem;
  color: #666;
}

/* Contacto */
.contact-section {
  margin-top: 1.5rem;
  padding: 1rem;
  background: #E8F0FE;
  border-radius: 8px;
}

.contact-section h4 {
  margin: 0 0 0.5rem 0;
  color: #004884;
}

.contact-section p {
  margin: 0;
}

/* No encontrado */
.not-found-section {
  padding: 3rem;
}

.not-found-content {
  text-align: center;
}

.not-found-icon {
  font-size: 4rem;
  display: block;
  margin-bottom: 1rem;
}

.not-found-content h3 {
  color: #A80521;
  margin: 0 0 1rem 0;
}

.suggestions-list {
  text-align: left;
  max-width: 500px;
  margin: 1rem auto 0;
  padding-left: 1.5rem;
}

.suggestions-list li {
  margin-bottom: 0.5rem;
  color: #666;
}

/* Responsive */
@media (max-width: 768px) {
  .search-container {
    flex-direction: column;
    align-items: stretch;
  }

  .govco-btn {
    width: 100%;
  }

  .result-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 576px) {
  .status-check {
    padding: 1rem;
  }
}
</style>
