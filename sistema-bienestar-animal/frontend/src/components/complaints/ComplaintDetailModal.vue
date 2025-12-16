<!-- src/components/complaints/ComplaintDetailModal.vue -->
<!-- Modal de detalle de denuncia -->
<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <!-- Header -->
      <div class="modal-header" :class="`urgency-bg-${complaint.prioridad}`">
        <div class="header-info">
          <h3 class="h4-tipografia-govco">{{ complaint.numero_ticket }}</h3>
          <div class="header-badges">
            <span class="urgency-badge" :class="`urgency-${complaint.prioridad}`">
              {{ getUrgencyLabel(complaint.prioridad) }}
            </span>
            <span class="status-badge" :class="`status-${complaint.estado}`">
              {{ getStatusLabel(complaint.estado) }}
            </span>
          </div>
        </div>
        <button @click="$emit('close')" class="modal-close">×</button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <!-- Tipo y fecha -->
        <div class="info-row highlight">
          <div class="info-item">
            <span class="info-icon">{{ getTypeIcon(complaint.tipo_denuncia) }}</span>
            <div class="info-content">
              <span class="info-label">Tipo de denuncia</span>
              <span class="info-value">{{ getComplaintTypeLabel(complaint.tipo_denuncia) }}</span>
            </div>
          </div>
          <div class="info-item">
            <span class="info-icon">📅</span>
            <div class="info-content">
              <span class="info-label">Fecha de recepción</span>
              <span class="info-value">{{ formatDateTime(complaint.fecha_denuncia || complaint.created_at) }}</span>
            </div>
          </div>
        </div>

        <!-- Descripción -->
        <div class="section">
          <h4 class="section-title">Descripción del caso</h4>
          <p class="description-text">{{ complaint.descripcion }}</p>
        </div>

        <!-- Ubicación -->
        <div class="section">
          <h4 class="section-title">Ubicación</h4>
          <div class="location-info">
            <span class="location-icon">📍</span>
            <div class="location-details">
              <span class="location-text">{{ complaint.ubicacion || 'Sin ubicación especificada' }}</span>
              <span v-if="complaint.latitud && complaint.longitud" class="coordinates">
                {{ Number(complaint.latitud).toFixed(5) }}, {{ Number(complaint.longitud).toFixed(5) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Denunciante -->
        <div class="section">
          <h4 class="section-title">Denunciante</h4>
          <div v-if="complaint.es_anonima" class="anonymous-notice">
            <span class="notice-icon">🔒</span>
            <span>Denuncia anónima</span>
          </div>
          <div v-else-if="complaint.denunciante" class="reporter-info">
            <div class="reporter-item">
              <span class="reporter-label">Nombre:</span>
              <span class="reporter-value">{{ complaint.denunciante.nombres }} {{ complaint.denunciante.apellidos }}</span>
            </div>
            <div v-if="complaint.denunciante.telefono" class="reporter-item">
              <span class="reporter-label">Teléfono:</span>
              <span class="reporter-value">{{ complaint.denunciante.telefono }}</span>
            </div>
            <div v-if="complaint.denunciante.email" class="reporter-item">
              <span class="reporter-label">Email:</span>
              <span class="reporter-value">{{ complaint.denunciante.email }}</span>
            </div>
          </div>
          <div v-else class="anonymous-notice">
            <span class="notice-icon">❓</span>
            <span>Sin información de denunciante</span>
          </div>
        </div>

        <!-- Responsable asignado -->
        <div v-if="complaint.responsable_id || complaint.responsable" class="section">
          <h4 class="section-title">Responsable asignado</h4>
          <div class="team-info">
            <span class="team-name" v-if="complaint.responsable">
              {{ complaint.responsable.nombres }} {{ complaint.responsable.apellidos }}
            </span>
            <span class="team-name" v-else>Operador asignado</span>
            <span v-if="complaint.fecha_asignacion" class="team-date">
              Asignado: {{ formatDateTime(complaint.fecha_asignacion) }}
            </span>
          </div>
        </div>

        <!-- Evidencias -->
        <div v-if="complaint.evidencias && complaint.evidencias.length > 0" class="section">
          <h4 class="section-title">Evidencias ({{ complaint.evidencias.length }})</h4>
          <div class="evidence-grid">
            <div v-for="(file, index) in complaint.evidencias" :key="index" class="evidence-thumb">
              <span class="evidence-icon">{{ file.includes('video') ? '🎥' : '📷' }}</span>
              <span class="evidence-name">{{ file }}</span>
            </div>
          </div>
        </div>

        <!-- Línea de tiempo -->
        <div v-if="complaint.timeline && complaint.timeline.length > 0" class="section">
          <h4 class="section-title">Línea de tiempo</h4>
          <div class="timeline">
            <div
              v-for="(event, index) in complaint.timeline"
              :key="index"
              class="timeline-item"
            >
              <div class="timeline-marker"></div>
              <div class="timeline-content">
                <span class="timeline-date">{{ formatDateTime(event.fecha) }}</span>
                <span class="timeline-title">{{ event.titulo }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer">
        <button @click="$emit('close')" class="govco-btn govco-bg-concrete">
          Cerrar
        </button>
        <button
          v-if="!complaint.responsable_id && complaint.estado !== 'resuelta'"
          @click="$emit('assign', complaint)"
          class="govco-btn govco-bg-marine"
        >
          Asignar operador
        </button>
        <button
          v-if="complaint.responsable_id && complaint.estado === 'en_proceso'"
          @click="$emit('register-result', complaint)"
          class="govco-btn govco-bg-elf-green"
        >
          Registrar resultado
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  complaint: {
    type: Object,
    required: true
  }
});

defineEmits(['close', 'assign', 'register-result']);

// Helpers
function getUrgencyLabel(urgency) {
  const labels = { urgente: 'URGENTE', alta: 'ALTA', media: 'MEDIA', baja: 'BAJA' };
  return labels[urgency] || urgency?.toUpperCase() || '';
}

function getStatusLabel(status) {
  const labels = {
    recibida: 'Recibida',
    en_revision: 'En revisión',
    asignada: 'Asignada',
    en_atencion: 'En atención',
    resuelta: 'Resuelta',
    cerrada: 'Cerrada',
    desestimada: 'Desestimada'
  };
  return labels[status] || status;
}

function getComplaintTypeLabel(type) {
  const labels = {
    maltrato: 'Maltrato',
    abandono: 'Abandono',
    animal_herido: 'Animal herido',
    animal_peligroso: 'Animal peligroso',
    otro: 'Otro'
  };
  return labels[type] || type;
}

function getTypeIcon(type) {
  const icons = {
    maltrato: '🩹',
    abandono: '🏚️',
    animal_herido: '🚑',
    animal_peligroso: '⚠️',
    otro: '📋'
  };
  return icons[type] || '📋';
}

function formatDateTime(dateString) {
  if (!dateString) return 'Sin fecha';
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return 'Fecha inválida';
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
  max-width: 700px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.5rem;
  border-bottom: 3px solid;
}

.urgency-bg-urgente { background: #FFEBEE; border-color: #A80521; }
.urgency-bg-alta { background: #FFF8E1; border-color: #FFAB00; }
.urgency-bg-media { background: #E8F0FE; border-color: #3366CC; }
.urgency-bg-baja { background: #F5F5F5; border-color: #737373; }

.header-info h3 {
  margin: 0 0 0.5rem 0;
  color: #004884;
}

.header-badges {
  display: flex;
  gap: 0.5rem;
}

.urgency-badge,
.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

.urgency-urgente { background: #A80521; }
.urgency-alta { background: #FFAB00; color: #333; }
.urgency-media { background: #3366CC; }
.urgency-baja { background: #737373; }

.status-recibida { background: #E0E0E0; color: #333; }
.status-en_revision { background: #FFF3E0; color: #E65100; }
.status-asignada { background: #E3F2FD; color: #1565C0; }
.status-en_atencion { background: #E8F0FE; color: #3366CC; }
.status-resuelta { background: #E8F5E9; color: #2E7D32; }
.status-cerrada { background: #F3E5F5; color: #7B1FA2; }
.status-desestimada { background: #FFEBEE; color: #C62828; }

.modal-close {
  background: none;
  border: none;
  font-size: 2rem;
  cursor: pointer;
  color: #666;
  line-height: 1;
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.info-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.info-row.highlight {
  background: #f9f9f9;
  padding: 1rem;
  border-radius: 8px;
}

.info-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.info-icon {
  font-size: 1.5rem;
}

.info-content {
  display: flex;
  flex-direction: column;
}

.info-label {
  font-size: 0.8rem;
  color: #666;
}

.info-value {
  font-weight: 600;
  color: #333;
}

.section {
  margin-bottom: 1.5rem;
}

.section-title {
  margin: 0 0 0.75rem 0;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #E0E0E0;
  color: #004884;
  font-size: 0.9rem;
}

.description-text {
  margin: 0;
  line-height: 1.6;
  color: #333;
}

.location-info {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.location-icon {
  flex-shrink: 0;
}

.location-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.location-text {
  color: #333;
}

.coordinates {
  font-size: 0.8rem;
  color: #666;
  font-family: monospace;
}

.mini-map {
  height: 100px;
  background: #E8F0FE;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.map-placeholder {
  display: flex;
  flex-direction: column;
  font-size: 0.85rem;
  color: #666;
}

.animal-info,
.reporter-info {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.animal-item,
.reporter-item {
  display: flex;
  gap: 0.5rem;
}

.animal-label,
.reporter-label {
  font-weight: 500;
  color: #666;
}

.anonymous-notice {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  background: #E8F0FE;
  border-radius: 4px;
  color: #004884;
}

.team-info {
  display: flex;
  flex-direction: column;
}

.team-name {
  font-weight: 600;
  color: #333;
}

.team-date {
  font-size: 0.85rem;
  color: #666;
}

.evidence-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 0.5rem;
}

.evidence-thumb {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.75rem;
  background: #f9f9f9;
  border-radius: 4px;
  font-size: 0.8rem;
}

.evidence-icon {
  font-size: 1.5rem;
  margin-bottom: 0.25rem;
}

.evidence-name {
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
  max-width: 100%;
}

.timeline {
  padding-left: 1rem;
  border-left: 2px solid #E0E0E0;
}

.timeline-item {
  position: relative;
  padding-left: 1rem;
  padding-bottom: 1rem;
}

.timeline-item:last-child {
  padding-bottom: 0;
}

.timeline-marker {
  position: absolute;
  left: -1.35rem;
  width: 10px;
  height: 10px;
  background: #3366CC;
  border-radius: 50%;
}

.timeline-content {
  display: flex;
  flex-direction: column;
}

.timeline-date {
  font-size: 0.8rem;
  color: #666;
}

.timeline-title {
  font-weight: 500;
  color: #333;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid #E0E0E0;
  background: #f9f9f9;
}

.govco-btn {
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  color: white;
}

.govco-bg-concrete { background: #737373; }
.govco-bg-marine { background: #3366CC; }
.govco-bg-elf-green { background: #068460; }

@media (max-width: 576px) {
  .info-row {
    grid-template-columns: 1fr;
  }

  .modal-footer {
    flex-direction: column;
  }

  .govco-btn {
    width: 100%;
  }
}
</style>
