<!-- src/components/complaints/RescueAssignmentModal.vue -->
<!-- Modal para asignar equipo de rescate a una denuncia (HU-016) -->
<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <!-- Header -->
      <div class="modal-header">
        <div class="header-info">
          <h3 class="h4-tipografia-govco">Asignar equipo de rescate</h3>
          <p class="header-subtitle">{{ complaint.numero_ticket || 'Sin ticket' }}</p>
        </div>
        <button @click="$emit('close')" class="modal-close">×</button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <!-- Resumen de la denuncia -->
        <div class="complaint-summary">
          <div class="summary-row">
            <span class="summary-label">Tipo:</span>
            <span class="summary-value">{{ getComplaintTypeLabel(complaint.tipo_denuncia) }}</span>
          </div>
          <div class="summary-row">
            <span class="summary-label">Prioridad:</span>
            <span class="urgency-badge" :class="`urgency-${complaint.prioridad}`">
              {{ getUrgencyLabel(complaint.prioridad) }}
            </span>
          </div>
          <div class="summary-row">
            <span class="summary-label">Ubicación:</span>
            <span class="summary-value">{{ complaint.ubicacion || 'Sin ubicación' }}</span>
          </div>
        </div>

        <!-- Formulario de asignación -->
        <form @submit.prevent="handleSubmit" class="assignment-form">
          <!-- Selección de equipo -->
          <div class="form-group">
            <label class="form-label required">Equipo de rescate</label>
            <select
              v-model="form.equipo_id"
              class="form-control"
              required
            >
              <option value="" disabled>Seleccione un equipo</option>
              <option
                v-for="team in availableTeams"
                :key="team.id"
                :value="team.id"
                :disabled="team.ocupado"
              >
                {{ team.nombre }} {{ team.ocupado ? '(Ocupado)' : '' }}
              </option>
            </select>
            <p v-if="selectedTeam" class="team-info">
              <span class="team-members">{{ selectedTeam.miembros }} miembros</span>
              <span class="team-vehicle">{{ selectedTeam.vehiculo }}</span>
            </p>
          </div>

          <!-- Fecha y hora programada -->
          <div class="form-row">
            <div class="form-group">
              <label class="form-label required">Fecha programada</label>
              <input
                type="date"
                v-model="form.fecha_programada"
                class="form-control"
                :min="todayDate"
                required
              />
            </div>
            <div class="form-group">
              <label class="form-label required">Hora programada</label>
              <input
                type="time"
                v-model="form.hora_programada"
                class="form-control"
                required
              />
            </div>
          </div>

          <!-- Prioridad de atención -->
          <div class="form-group">
            <label class="form-label">Prioridad de atención</label>
            <div class="priority-options">
              <label
                v-for="priority in priorities"
                :key="priority.value"
                class="priority-option"
                :class="{ active: form.prioridad === priority.value }"
              >
                <input
                  type="radio"
                  v-model="form.prioridad"
                  :value="priority.value"
                  class="priority-radio"
                />
                <span class="priority-badge-small" :class="`urgency-${priority.value}`">
                  {{ priority.label }}
                </span>
              </label>
            </div>
          </div>

          <!-- Observaciones -->
          <div class="form-group">
            <label class="form-label">Observaciones</label>
            <textarea
              v-model="form.observaciones"
              class="form-control textarea"
              rows="3"
              placeholder="Indicaciones adicionales para el equipo de rescate..."
              maxlength="500"
            ></textarea>
            <span class="char-count">{{ form.observaciones.length }}/500</span>
          </div>

          <!-- Recursos necesarios (comentado por el momento)
          <div class="form-group">
            <label class="form-label">Recursos necesarios</label>
            <div class="resources-grid">
              <label
                v-for="resource in resources"
                :key="resource.id"
                class="resource-checkbox"
              >
                <input
                  type="checkbox"
                  v-model="form.recursos"
                  :value="resource.id"
                />
                <span class="resource-icon">{{ resource.icon }}</span>
                <span class="resource-name">{{ resource.name }}</span>
              </label>
            </div>
          </div>
          -->

          <!-- Notificaciones (comentado por el momento)
          <div class="form-group">
            <label class="form-label">Notificaciones</label>
            <div class="notification-options">
              <label class="checkbox-option">
                <input type="checkbox" v-model="form.notificar_equipo" />
                <span>Notificar al equipo por SMS/Email</span>
              </label>
              <label v-if="!complaint.es_anonima" class="checkbox-option">
                <input type="checkbox" v-model="form.notificar_denunciante" />
                <span>Notificar al denunciante</span>
              </label>
            </div>
          </div>
          -->
        </form>
      </div>

      <!-- Footer -->
      <div class="modal-footer">
        <button @click="$emit('close')" class="govco-btn govco-bg-concrete">
          Cancelar
        </button>
        <button
          @click="handleSubmit"
          class="govco-btn govco-bg-marine"
          :disabled="!isFormValid || isSubmitting"
        >
          <span v-if="isSubmitting">Asignando...</span>
          <span v-else>Asignar equipo</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import complaintService from '@/services/complaintService';

const props = defineProps({
  complaint: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'assigned']);

// Estado del formulario
const form = ref({
  equipo_id: '',
  fecha_programada: '',
  hora_programada: '',
  prioridad: 'media',
  observaciones: ''
});

const isSubmitting = ref(false);

// Equipos disponibles (mock data - en el futuro vendrá del backend)
const availableTeams = ref([
  { id: 1, nombre: 'Equipo Alfa', miembros: 4, vehiculo: 'Camioneta 4x4', ocupado: false },
  { id: 2, nombre: 'Equipo Beta', miembros: 3, vehiculo: 'Van de rescate', ocupado: true },
  { id: 3, nombre: 'Equipo Gamma', miembros: 5, vehiculo: 'Ambulancia veterinaria', ocupado: false },
  { id: 4, nombre: 'Equipo Delta', miembros: 3, vehiculo: 'Motocicleta', ocupado: false }
]);

// Prioridades (usando valores de la BD)
const priorities = [
  { value: 'urgente', label: 'URGENTE' },
  { value: 'alta', label: 'ALTA' },
  { value: 'media', label: 'MEDIA' },
  { value: 'baja', label: 'BAJA' }
];

// Computed
const todayDate = computed(() => {
  return new Date().toISOString().split('T')[0];
});

const selectedTeam = computed(() => {
  return availableTeams.value.find(t => t.id === form.value.equipo_id);
});

const isFormValid = computed(() => {
  return form.value.equipo_id &&
         form.value.fecha_programada &&
         form.value.hora_programada;
});

// Helpers
function getComplaintTypeLabel(type) {
  const labels = {
    maltrato: 'Maltrato',
    abandono: 'Abandono',
    animal_herido: 'Animal herido',
    animal_peligroso: 'Animal peligroso',
    otro: 'Otro'
  };
  return labels[type] || type || 'Sin tipo';
}

function getUrgencyLabel(urgency) {
  const labels = {
    urgente: 'URGENTE',
    alta: 'ALTA',
    media: 'MEDIA',
    baja: 'BAJA'
  };
  return labels[urgency] || urgency?.toUpperCase() || '';
}

// Submit
async function handleSubmit() {
  if (!isFormValid.value || isSubmitting.value) return;

  isSubmitting.value = true;

  try {
    // Construir fecha_programada completa
    const fechaProgramada = `${form.value.fecha_programada} ${form.value.hora_programada}:00`;

    const rescateData = {
      denuncia_id: props.complaint.id,
      fecha_programada: fechaProgramada,
      equipo_rescate: {
        id: form.value.equipo_id,
        nombre: selectedTeam.value?.nombre,
        miembros: selectedTeam.value?.miembros,
        vehiculo: selectedTeam.value?.vehiculo
      },
      observaciones: form.value.observaciones || null
    };

    // Llamar al servicio para crear el rescate
    const response = await complaintService.createRescate(rescateData);

    // Emitir evento con los datos del rescate creado
    emit('assigned', response.data);
    emit('close');

  } catch (error) {
    console.error('Error al asignar equipo:', error);
    alert(error.response?.data?.message || 'Error al asignar el equipo. Intente nuevamente.');
  } finally {
    isSubmitting.value = false;
  }
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
  max-width: 600px;
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
  background: #004884;
  color: white;
}

.header-info h3 {
  margin: 0;
  color: white;
}

.header-subtitle {
  margin: 0.25rem 0 0 0;
  opacity: 0.9;
  font-size: 0.9rem;
}

.modal-close {
  background: none;
  border: none;
  font-size: 2rem;
  cursor: pointer;
  color: white;
  line-height: 1;
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

/* Resumen de denuncia */
.complaint-summary {
  background: #f9f9f9;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.summary-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.summary-row:last-child {
  margin-bottom: 0;
}

.summary-label {
  font-weight: 500;
  color: #666;
  min-width: 80px;
}

.summary-value {
  color: #333;
}

.urgency-badge {
  padding: 0.2rem 0.6rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

.urgency-urgente { background: #A80521; }
.urgency-alta { background: #FFAB00; color: #333; }
.urgency-media { background: #3366CC; }
.urgency-baja { background: #737373; }

.priority-badge-small {
  padding: 0.3rem 0.6rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

/* Form */
.assignment-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-label {
  font-weight: 500;
  color: #333;
  font-size: 0.9rem;
}

.form-label.required::after {
  content: ' *';
  color: #A80521;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-control {
  padding: 0.75rem;
  border: 1px solid #d9e2f3;
  border-radius: 4px;
  font-size: 1rem;
  width: 100%;
  box-sizing: border-box;
}

.form-control:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 2px rgba(51, 102, 204, 0.2);
}

.textarea {
  resize: vertical;
  min-height: 80px;
}

.char-count {
  font-size: 0.8rem;
  color: #666;
  text-align: right;
}

/* Dropdown GOV.CO */
.desplegable-govco {
  position: relative;
}

.desplegable-govco-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d9e2f3;
  border-radius: 4px;
  font-size: 1rem;
  background: white;
  cursor: pointer;
}

.desplegable-govco-input:focus {
  outline: none;
  border-color: #3366CC;
}

.team-info {
  display: flex;
  gap: 1rem;
  font-size: 0.85rem;
  color: #666;
  margin-top: 0.25rem;
}

/* Priority options */
.priority-options {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.priority-option {
  display: flex;
  align-items: center;
  padding: 0.5rem;
  border: 2px solid transparent;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.priority-option:hover {
  background: #f0f0f0;
}

.priority-option.active {
  border-color: #3366CC;
  background: #E8F0FE;
}

.priority-radio {
  display: none;
}

/* Resources grid */
.resources-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
}

.resource-checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  border: 1px solid #E0E0E0;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.85rem;
}

.resource-checkbox:has(input:checked) {
  background: #E8F5E9;
  border-color: #068460;
}

.resource-icon {
  font-size: 1.1rem;
}

/* Notification options */
.notification-options {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.checkbox-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.checkbox-option input {
  width: 18px;
  height: 18px;
  accent-color: #3366CC;
}

/* Footer */
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
  transition: opacity 0.2s;
}

.govco-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.govco-bg-concrete { background: #737373; }
.govco-bg-marine { background: #3366CC; }

@media (max-width: 576px) {
  .form-row {
    grid-template-columns: 1fr;
  }

  .priority-options {
    grid-template-columns: repeat(2, 1fr);
  }

  .resources-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .modal-footer {
    flex-direction: column;
  }

  .govco-btn {
    width: 100%;
  }
}
</style>
