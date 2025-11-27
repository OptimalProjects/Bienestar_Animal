<!-- src/components/complaints/RescueResultModal.vue -->
<!-- Modal para registrar resultado de operativo de rescate (HU-017) -->
<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <!-- Header -->
      <div class="modal-header">
        <div class="header-info">
          <h3 class="h4-tipografia-govco">Registrar resultado del operativo</h3>
          <p class="header-subtitle">{{ complaint.caso_numero }}</p>
        </div>
        <button @click="$emit('close')" class="modal-close">×</button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <!-- Info del operativo -->
        <div class="operation-summary">
          <div class="summary-item">
            <span class="summary-icon">👥</span>
            <div class="summary-content">
              <span class="summary-label">Equipo</span>
              <span class="summary-value">{{ complaint.equipo_nombre || 'Equipo asignado' }}</span>
            </div>
          </div>
          <div class="summary-item">
            <span class="summary-icon">📍</span>
            <div class="summary-content">
              <span class="summary-label">Ubicación</span>
              <span class="summary-value">{{ complaint.direccion }}</span>
            </div>
          </div>
        </div>

        <!-- Stepper -->
        <div class="stepper">
          <div
            v-for="(step, index) in steps"
            :key="index"
            class="step"
            :class="{ active: currentStep === index, completed: currentStep > index }"
          >
            <div class="step-marker">{{ currentStep > index ? '✓' : index + 1 }}</div>
            <span class="step-label">{{ step }}</span>
          </div>
        </div>

        <!-- Step 1: Resultado general -->
        <div v-show="currentStep === 0" class="step-content">
          <div class="form-group">
            <label class="form-label required">Resultado del operativo</label>
            <div class="result-options">
              <label
                v-for="result in resultOptions"
                :key="result.value"
                class="result-option"
                :class="{ active: form.resultado === result.value }"
              >
                <input
                  type="radio"
                  v-model="form.resultado"
                  :value="result.value"
                  class="result-radio"
                />
                <span class="result-icon" :class="result.class">{{ result.icon }}</span>
                <span class="result-label">{{ result.label }}</span>
                <span class="result-desc">{{ result.description }}</span>
              </label>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label required">Descripción del resultado</label>
            <textarea
              v-model="form.descripcion_resultado"
              class="form-control textarea"
              rows="4"
              placeholder="Describa detalladamente lo encontrado en el lugar y las acciones realizadas..."
              required
            ></textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label required">Fecha de atención</label>
              <input
                type="date"
                v-model="form.fecha_atencion"
                class="form-control"
                required
              />
            </div>
            <div class="form-group">
              <label class="form-label required">Hora de atención</label>
              <input
                type="time"
                v-model="form.hora_atencion"
                class="form-control"
                required
              />
            </div>
          </div>
        </div>

        <!-- Step 2: Animales rescatados -->
        <div v-show="currentStep === 1" class="step-content">
          <div class="form-group">
            <label class="form-label">Animales rescatados</label>
            <div class="animals-list">
              <div
                v-for="(animal, index) in form.animales_rescatados"
                :key="index"
                class="animal-card"
              >
                <div class="animal-header">
                  <span class="animal-number">Animal #{{ index + 1 }}</span>
                  <button
                    @click="removeAnimal(index)"
                    class="remove-animal"
                    type="button"
                  >×</button>
                </div>
                <div class="animal-fields">
                  <div class="field-row">
                    <select v-model="animal.especie" class="form-control small">
                      <option value="">Especie</option>
                      <option value="perro">Perro</option>
                      <option value="gato">Gato</option>
                      <option value="equino">Equino</option>
                      <option value="ave">Ave</option>
                      <option value="otro">Otro</option>
                    </select>
                    <select v-model="animal.sexo" class="form-control small">
                      <option value="">Sexo</option>
                      <option value="macho">Macho</option>
                      <option value="hembra">Hembra</option>
                      <option value="desconocido">Desconocido</option>
                    </select>
                  </div>
                  <div class="field-row">
                    <select v-model="animal.estado_salud" class="form-control small">
                      <option value="">Estado de salud</option>
                      <option value="critico">Crítico</option>
                      <option value="malo">Malo</option>
                      <option value="regular">Regular</option>
                      <option value="bueno">Bueno</option>
                    </select>
                    <input
                      type="text"
                      v-model="animal.identificacion"
                      class="form-control small"
                      placeholder="ID temporal"
                    />
                  </div>
                  <textarea
                    v-model="animal.observaciones"
                    class="form-control small"
                    placeholder="Observaciones del animal..."
                    rows="2"
                  ></textarea>
                </div>
              </div>

              <button @click="addAnimal" class="add-animal-btn" type="button">
                <span class="add-icon">+</span>
                Agregar animal rescatado
              </button>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Destino de los animales</label>
            <select v-model="form.destino_animales" class="form-control">
              <option value="">Seleccione destino</option>
              <option value="albergue_municipal">Albergue Municipal</option>
              <option value="clinica_veterinaria">Clínica Veterinaria</option>
              <option value="fundacion">Fundación protectora</option>
              <option value="hogar_paso">Hogar de paso</option>
              <option value="devuelto_propietario">Devuelto al propietario</option>
            </select>
          </div>
        </div>

        <!-- Step 3: Evidencia y cierre -->
        <div v-show="currentStep === 2" class="step-content">
          <!-- Evidencia fotográfica -->
          <div class="form-group">
            <label class="form-label">Evidencia fotográfica</label>
            <div class="evidence-upload">
              <div class="upload-area" @click="triggerFileInput">
                <span class="upload-icon">📷</span>
                <span class="upload-text">Clic para agregar fotos</span>
                <span class="upload-hint">JPG, PNG (máx. 5MB c/u)</span>
              </div>
              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                multiple
                @change="handleFileSelect"
                style="display: none"
              />
            </div>
            <div v-if="form.evidencias.length > 0" class="evidence-preview">
              <div
                v-for="(file, index) in form.evidencias"
                :key="index"
                class="evidence-item"
              >
                <span class="evidence-name">{{ file.name }}</span>
                <button @click="removeEvidence(index)" class="remove-evidence">×</button>
              </div>
            </div>
          </div>

          <!-- Acciones realizadas -->
          <div class="form-group">
            <label class="form-label">Acciones realizadas</label>
            <div class="actions-grid">
              <label
                v-for="action in availableActions"
                :key="action.id"
                class="action-checkbox"
              >
                <input
                  type="checkbox"
                  v-model="form.acciones_realizadas"
                  :value="action.id"
                />
                <span>{{ action.label }}</span>
              </label>
            </div>
          </div>

          <!-- Requiere seguimiento -->
          <div class="form-group">
            <label class="form-label">Seguimiento posterior</label>
            <div class="follow-up-options">
              <label class="radio-option">
                <input type="radio" v-model="form.requiere_seguimiento" :value="false" />
                <span>No requiere seguimiento</span>
              </label>
              <label class="radio-option">
                <input type="radio" v-model="form.requiere_seguimiento" :value="true" />
                <span>Requiere visita de seguimiento</span>
              </label>
            </div>
            <div v-if="form.requiere_seguimiento" class="follow-up-date">
              <label class="form-label">Fecha sugerida de seguimiento</label>
              <input
                type="date"
                v-model="form.fecha_seguimiento"
                class="form-control"
                :min="todayDate"
              />
            </div>
          </div>

          <!-- Derivar a autoridad -->
          <div class="form-group">
            <label class="form-label">Derivación a autoridades</label>
            <label class="checkbox-option">
              <input type="checkbox" v-model="form.derivar_autoridad" />
              <span>Derivar caso a Fiscalía/Policía (delito penal)</span>
            </label>
            <textarea
              v-if="form.derivar_autoridad"
              v-model="form.motivo_derivacion"
              class="form-control textarea"
              rows="2"
              placeholder="Motivo de la derivación..."
            ></textarea>
          </div>

          <!-- Observaciones finales -->
          <div class="form-group">
            <label class="form-label">Observaciones finales</label>
            <textarea
              v-model="form.observaciones_finales"
              class="form-control textarea"
              rows="3"
              placeholder="Comentarios adicionales, recomendaciones, etc..."
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer">
        <button
          v-if="currentStep > 0"
          @click="prevStep"
          class="govco-btn govco-bg-concrete"
        >
          Anterior
        </button>
        <button
          v-else
          @click="$emit('close')"
          class="govco-btn govco-bg-concrete"
        >
          Cancelar
        </button>

        <button
          v-if="currentStep < steps.length - 1"
          @click="nextStep"
          class="govco-btn govco-bg-marine"
          :disabled="!canProceed"
        >
          Siguiente
        </button>
        <button
          v-else
          @click="handleSubmit"
          class="govco-btn govco-bg-elf-green"
          :disabled="!canSubmit || isSubmitting"
        >
          <span v-if="isSubmitting">Guardando...</span>
          <span v-else>Finalizar operativo</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  complaint: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'result-registered']);

// Steps
const steps = ['Resultado', 'Animales', 'Cierre'];
const currentStep = ref(0);

// Form state
const form = ref({
  resultado: '',
  descripcion_resultado: '',
  fecha_atencion: new Date().toISOString().split('T')[0],
  hora_atencion: new Date().toTimeString().slice(0, 5),
  animales_rescatados: [],
  destino_animales: '',
  evidencias: [],
  acciones_realizadas: [],
  requiere_seguimiento: false,
  fecha_seguimiento: '',
  derivar_autoridad: false,
  motivo_derivacion: '',
  observaciones_finales: ''
});

const isSubmitting = ref(false);
const fileInput = ref(null);

// Result options
const resultOptions = [
  {
    value: 'exitoso',
    icon: '✅',
    label: 'Rescate exitoso',
    description: 'Se rescataron animales',
    class: 'success'
  },
  {
    value: 'parcial',
    icon: '⚠️',
    label: 'Parcialmente exitoso',
    description: 'Rescate parcial o con dificultades',
    class: 'warning'
  },
  {
    value: 'fallido',
    icon: '❌',
    label: 'No exitoso',
    description: 'No se pudo realizar el rescate',
    class: 'error'
  },
  {
    value: 'infundada',
    icon: '📋',
    label: 'Denuncia infundada',
    description: 'No se encontró situación reportada',
    class: 'neutral'
  }
];

// Available actions
const availableActions = [
  { id: 'rescate_animal', label: 'Rescate de animales' },
  { id: 'atencion_veterinaria', label: 'Atención veterinaria in situ' },
  { id: 'decomiso', label: 'Decomiso de animales' },
  { id: 'educacion', label: 'Educación al tenedor' },
  { id: 'compromiso', label: 'Acta de compromiso' },
  { id: 'notificacion', label: 'Notificación de sanción' },
  { id: 'mejoras', label: 'Verificación de mejoras' },
  { id: 'denuncia_penal', label: 'Recopilación para denuncia penal' }
];

// Computed
const todayDate = computed(() => {
  return new Date().toISOString().split('T')[0];
});

const canProceed = computed(() => {
  if (currentStep.value === 0) {
    return form.value.resultado &&
           form.value.descripcion_resultado &&
           form.value.fecha_atencion &&
           form.value.hora_atencion;
  }
  return true;
});

const canSubmit = computed(() => {
  return form.value.resultado &&
         form.value.descripcion_resultado;
});

// Methods
function nextStep() {
  if (currentStep.value < steps.length - 1) {
    currentStep.value++;
  }
}

function prevStep() {
  if (currentStep.value > 0) {
    currentStep.value--;
  }
}

function addAnimal() {
  form.value.animales_rescatados.push({
    especie: '',
    sexo: '',
    estado_salud: '',
    identificacion: `TMP-${Date.now().toString().slice(-6)}`,
    observaciones: ''
  });
}

function removeAnimal(index) {
  form.value.animales_rescatados.splice(index, 1);
}

function triggerFileInput() {
  fileInput.value?.click();
}

function handleFileSelect(event) {
  const files = Array.from(event.target.files);
  files.forEach(file => {
    if (file.size <= 5 * 1024 * 1024) {
      form.value.evidencias.push(file);
    }
  });
}

function removeEvidence(index) {
  form.value.evidencias.splice(index, 1);
}

async function handleSubmit() {
  if (!canSubmit.value || isSubmitting.value) return;

  isSubmitting.value = true;

  try {
    await new Promise(resolve => setTimeout(resolve, 1500));

    const resultData = {
      denuncia_id: props.complaint.id,
      resultado: form.value.resultado,
      descripcion_resultado: form.value.descripcion_resultado,
      fecha_atencion: `${form.value.fecha_atencion}T${form.value.hora_atencion}`,
      animales_rescatados: form.value.animales_rescatados,
      destino_animales: form.value.destino_animales,
      acciones_realizadas: form.value.acciones_realizadas,
      requiere_seguimiento: form.value.requiere_seguimiento,
      fecha_seguimiento: form.value.fecha_seguimiento,
      derivar_autoridad: form.value.derivar_autoridad,
      motivo_derivacion: form.value.motivo_derivacion,
      observaciones_finales: form.value.observaciones_finales,
      evidencias_count: form.value.evidencias.length
    };

    emit('result-registered', resultData);
    emit('close');

  } catch (error) {
    console.error('Error al registrar resultado:', error);
    alert('Error al guardar el resultado. Intente nuevamente.');
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
  background: #068460;
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

/* Operation summary */
.operation-summary {
  display: flex;
  gap: 1.5rem;
  padding: 1rem;
  background: #f9f9f9;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

.summary-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.summary-icon {
  font-size: 1.5rem;
}

.summary-content {
  display: flex;
  flex-direction: column;
}

.summary-label {
  font-size: 0.8rem;
  color: #666;
}

.summary-value {
  font-weight: 500;
  color: #333;
}

/* Stepper */
.stepper {
  display: flex;
  justify-content: center;
  gap: 2rem;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #E0E0E0;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  opacity: 0.5;
}

.step.active,
.step.completed {
  opacity: 1;
}

.step-marker {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #E0E0E0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  color: #666;
}

.step.active .step-marker {
  background: #3366CC;
  color: white;
}

.step.completed .step-marker {
  background: #068460;
  color: white;
}

.step-label {
  font-size: 0.85rem;
  color: #666;
}

.step.active .step-label {
  color: #3366CC;
  font-weight: 500;
}

/* Step content */
.step-content {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

/* Form elements */
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

.form-control.small {
  padding: 0.5rem;
  font-size: 0.9rem;
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

/* Result options */
.result-options {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.result-option {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1rem;
  border: 2px solid #E0E0E0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}

.result-option:hover {
  border-color: #3366CC;
}

.result-option.active {
  border-color: #3366CC;
  background: #E8F0FE;
}

.result-radio {
  display: none;
}

.result-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.result-label {
  font-weight: 600;
  color: #333;
  margin-bottom: 0.25rem;
}

.result-desc {
  font-size: 0.8rem;
  color: #666;
}

/* Animals list */
.animals-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.animal-card {
  border: 1px solid #d9e2f3;
  border-radius: 8px;
  padding: 1rem;
  background: #fafafa;
}

.animal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.animal-number {
  font-weight: 600;
  color: #004884;
}

.remove-animal {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #A80521;
  cursor: pointer;
  line-height: 1;
}

.animal-fields {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.field-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem;
}

.add-animal-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem;
  border: 2px dashed #3366CC;
  border-radius: 8px;
  background: transparent;
  color: #3366CC;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.add-animal-btn:hover {
  background: #E8F0FE;
}

.add-icon {
  font-size: 1.25rem;
  font-weight: bold;
}

/* Evidence upload */
.evidence-upload {
  margin-bottom: 0.75rem;
}

.upload-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 2rem;
  border: 2px dashed #d9e2f3;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.upload-area:hover {
  border-color: #3366CC;
  background: #fafafa;
}

.upload-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.upload-text {
  font-weight: 500;
  color: #333;
}

.upload-hint {
  font-size: 0.8rem;
  color: #666;
}

.evidence-preview {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.evidence-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: #E8F0FE;
  border-radius: 4px;
  font-size: 0.85rem;
}

.remove-evidence {
  background: none;
  border: none;
  color: #A80521;
  cursor: pointer;
  font-size: 1.1rem;
}

/* Actions grid */
.actions-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
}

.action-checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  border: 1px solid #E0E0E0;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
}

.action-checkbox:has(input:checked) {
  background: #E8F5E9;
  border-color: #068460;
}

.action-checkbox input {
  accent-color: #068460;
}

/* Follow up options */
.follow-up-options {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.radio-option,
.checkbox-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.radio-option input,
.checkbox-option input {
  width: 18px;
  height: 18px;
  accent-color: #3366CC;
}

.follow-up-date {
  padding-left: 1.5rem;
  margin-top: 0.5rem;
}

/* Footer */
.modal-footer {
  display: flex;
  justify-content: space-between;
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
.govco-bg-elf-green { background: #068460; }

@media (max-width: 576px) {
  .operation-summary {
    flex-direction: column;
    gap: 0.75rem;
  }

  .stepper {
    gap: 1rem;
  }

  .step-label {
    font-size: 0.75rem;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .result-options {
    grid-template-columns: 1fr;
  }

  .field-row {
    grid-template-columns: 1fr;
  }

  .actions-grid {
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
