<!-- src/components/complaints/RescueResultModal.vue -->
<!-- Modal para registrar resultado de operativo de rescate (HU-017) -->
<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content modal-large" @click.stop>
      <!-- Header -->
      <div class="modal-header">
        <div class="header-info">
          <h3 class="h4-tipografia-govco">Registrar resultado del operativo</h3>
          <p class="header-subtitle">{{ complaint.numero_ticket || 'Sin ticket' }}</p>
        </div>
        <button @click="$emit('close')" class="modal-close">×</button>
      </div>

      <!-- Stepper -->
      <div class="stepper">
        <div
          v-for="(step, index) in steps"
          :key="step.id"
          class="step"
          :class="{
            active: currentStep === index,
            completed: currentStep > index,
            clickable: index < currentStep
          }"
          @click="goToStep(index)"
        >
          <div class="step-number">
            <span v-if="currentStep > index">✓</span>
            <span v-else>{{ index + 1 }}</span>
          </div>
          <span class="step-label">{{ step.label }}</span>
        </div>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <!-- Info del operativo -->
        <div class="operation-summary">
          <div class="summary-row">
            <div class="summary-item">
              <span class="summary-icon">👥</span>
              <div class="summary-content">
                <span class="summary-label">Equipo</span>
                <span class="summary-value">{{ equipoNombre }}</span>
              </div>
            </div>
            <div class="summary-item">
              <span class="summary-icon">📅</span>
              <div class="summary-content">
                <span class="summary-label">Programado</span>
                <span class="summary-value">{{ formatDateTime(complaint.rescate?.fecha_programada) }}</span>
              </div>
            </div>
          </div>
          <div class="summary-item full-width">
            <span class="summary-icon">📍</span>
            <div class="summary-content">
              <span class="summary-label">Ubicación</span>
              <span class="summary-value">{{ complaint.ubicacion || 'Sin ubicación' }}</span>
            </div>
          </div>
        </div>

        <!-- PASO 1: Resultado del operativo -->
        <div v-if="currentStep === 0" class="step-content">
          <form @submit.prevent class="result-form">
            <!-- Resultado del operativo -->
            <div class="form-group">
              <label class="form-label required">Resultado del operativo</label>
              <div class="result-options">
                <label
                  class="result-option"
                  :class="{ active: form.exitoso === true }"
                >
                  <input
                    type="radio"
                    v-model="form.exitoso"
                    :value="true"
                    class="result-radio"
                  />
                  <span class="result-icon-big success">✅</span>
                  <span class="result-label">Exitoso</span>
                  <span class="result-desc">Se completó el rescate</span>
                </label>
                <label
                  class="result-option"
                  :class="{ active: form.exitoso === false }"
                >
                  <input
                    type="radio"
                    v-model="form.exitoso"
                    :value="false"
                    class="result-radio"
                  />
                  <span class="result-icon-big error">❌</span>
                  <span class="result-label">No exitoso</span>
                  <span class="result-desc">No se pudo completar</span>
                </label>
              </div>
            </div>

            <!-- Descripción / Observaciones -->
            <div class="form-group">
              <label class="form-label required">Descripción del resultado</label>
              <textarea
                v-model="form.observaciones"
                class="form-control textarea"
                rows="4"
                placeholder="Describa detalladamente lo encontrado en el lugar y las acciones realizadas..."
                required
              ></textarea>
            </div>

            <!-- Motivo de fallo (solo si no exitoso) -->
            <div v-if="form.exitoso === false" class="form-group">
              <label class="form-label required">Motivo del fallo</label>
              <textarea
                v-model="form.motivo_fallo"
                class="form-control textarea"
                rows="3"
                placeholder="Indique por qué no se pudo completar el rescate..."
                required
              ></textarea>
            </div>

            <!-- Fecha y hora de atención -->
            <div class="form-row">
              <div class="form-group">
                <label class="form-label required">Fecha de atención</label>
                <input
                  type="date"
                  v-model="form.fecha_ejecucion"
                  class="form-control"
                  :max="todayDate"
                  required
                />
              </div>
              <div class="form-group">
                <label class="form-label required">Hora de atención</label>
                <input
                  type="time"
                  v-model="form.hora_ejecucion"
                  class="form-control"
                  required
                />
              </div>
            </div>
          </form>
        </div>

        <!-- PASO 2: Registrar Animal (solo si exitoso) -->
        <div v-if="currentStep === 1" class="step-content">
          <div class="step-intro">
            <p>Registre la información completa del animal rescatado para vincularlo al sistema.</p>
          </div>

          <form @submit.prevent class="result-form">
            <!-- ¿Se rescató un animal? -->
            <div class="form-group">
              <label class="form-label required">¿Se rescató un animal?</label>
              <div class="result-options">
                <label
                  class="result-option small"
                  :class="{ active: form.animal_rescatado === true }"
                >
                  <input
                    type="radio"
                    v-model="form.animal_rescatado"
                    :value="true"
                    class="result-radio"
                  />
                  <span class="result-icon-big">🐾</span>
                  <span class="result-label">Sí</span>
                </label>
                <label
                  class="result-option small"
                  :class="{ active: form.animal_rescatado === false }"
                >
                  <input
                    type="radio"
                    v-model="form.animal_rescatado"
                    :value="false"
                    class="result-radio"
                  />
                  <span class="result-icon-big">➖</span>
                  <span class="result-label">No</span>
                </label>
              </div>
            </div>

            <!-- Datos completos del animal (solo si se rescató) -->
            <template v-if="form.animal_rescatado === true">
              <!-- Sección: Identificación básica -->
              <div class="animal-section">
                <h5 class="section-title">Identificación del animal</h5>

                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label">Nombre (opcional)</label>
                    <input
                      type="text"
                      v-model="form.animal.nombre"
                      class="form-control"
                      placeholder="Si se conoce el nombre..."
                    />
                  </div>
                  <div class="form-group">
                    <label class="form-label required">Especie</label>
                    <select v-model="form.animal.especie" class="form-control" required>
                      <option value="">Seleccione...</option>
                      <option value="canino">Canino (Perro)</option>
                      <option value="felino">Felino (Gato)</option>
                      <option value="equino">Equino</option>
                      <option value="ave">Ave</option>
                      <option value="otro">Otro</option>
                    </select>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label">Raza (aproximada)</label>
                    <input
                      type="text"
                      v-model="form.animal.raza"
                      class="form-control"
                      placeholder="Ej: Mestizo, Labrador, Criollo..."
                    />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Sexo</label>
                    <select v-model="form.animal.sexo" class="form-control">
                      <option value="desconocido">Desconocido</option>
                      <option value="macho">Macho</option>
                      <option value="hembra">Hembra</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Sección: Características físicas -->
              <div class="animal-section">
                <h5 class="section-title">Características físicas</h5>

                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label">Color</label>
                    <input
                      type="text"
                      v-model="form.animal.color"
                      class="form-control"
                      placeholder="Ej: Negro, Blanco con manchas café..."
                    />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Tamaño</label>
                    <select v-model="form.animal.tamanio" class="form-control">
                      <option value="">Seleccione...</option>
                      <option value="pequenio">Pequeño</option>
                      <option value="mediano">Mediano</option>
                      <option value="grande">Grande</option>
                      <option value="muy_grande">Muy grande</option>
                    </select>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label">Edad aproximada (meses)</label>
                    <input
                      type="number"
                      v-model.number="form.animal.edad_aproximada"
                      class="form-control"
                      min="0"
                      placeholder="Ej: 24 (para 2 años)"
                    />
                    <span class="form-hint">Ingrese en meses (12 = 1 año)</span>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Peso aproximado (kg)</label>
                    <input
                      type="number"
                      v-model.number="form.animal.peso_actual"
                      class="form-control"
                      min="0"
                      step="0.1"
                      placeholder="Ej: 15.5"
                    />
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label">Señas particulares</label>
                  <textarea
                    v-model="form.animal.senias_particulares"
                    class="form-control textarea"
                    rows="2"
                    placeholder="Cicatrices, marcas, collar, microchip visible, etc..."
                  ></textarea>
                </div>
              </div>

              <!-- Sección: Estado de salud -->
              <div class="animal-section">
                <h5 class="section-title">Estado de salud al rescate</h5>

                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label required">Estado de salud</label>
                    <select v-model="form.animal.estado_salud" class="form-control" required>
                      <option value="">Seleccione...</option>
                      <option value="critico">Crítico - Requiere atención inmediata</option>
                      <option value="grave">Grave - Requiere atención urgente</option>
                      <option value="estable">Estable - Condición controlada</option>
                      <option value="bueno">Bueno - Sin problemas aparentes</option>
                      <option value="excelente">Excelente - Muy buen estado</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="form-label">¿Está esterilizado?</label>
                    <select v-model="form.animal.esterilizacion" class="form-control">
                      <option :value="null">Desconocido</option>
                      <option :value="true">Sí</option>
                      <option :value="false">No</option>
                    </select>
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label">Observaciones médicas</label>
                  <textarea
                    v-model="form.animal.observaciones"
                    class="form-control textarea"
                    rows="2"
                    placeholder="Lesiones, enfermedades visibles, comportamiento, etc..."
                  ></textarea>
                </div>
              </div>

              <!-- Sección: Destino y ubicación -->
              <div class="animal-section">
                <h5 class="section-title">Destino del animal</h5>

                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label required">Estado inicial</label>
                    <select v-model="form.animal.estado" class="form-control" required>
                      <option value="">Seleccione...</option>
                      <option value="en_refugio">En refugio</option>
                      <option value="en_tratamiento">En tratamiento médico</option>
                      <option value="en_adopcion">Disponible para adopción</option>
                      <option value="fallecido">Fallecido</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="form-label required">Destino</label>
                    <select v-model="form.animal.destino" class="form-control" required>
                      <option value="">Seleccione...</option>
                      <option value="refugio">Refugio municipal</option>
                      <option value="clinica_veterinaria">Clínica veterinaria</option>
                      <option value="hogar_paso">Hogar de paso</option>
                      <option value="liberado">Liberado (fauna silvestre)</option>
                      <option value="fallecido">Fallecido en sitio</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Sección: Fotografías -->
              <div class="animal-section">
                <h5 class="section-title">Fotografías del animal</h5>

                <div class="photo-upload-area">
                  <input
                    type="file"
                    ref="fileInput"
                    @change="handleFileSelect"
                    accept="image/jpeg,image/jpg,image/png"
                    multiple
                    class="file-input-hidden"
                  />
                  <button type="button" @click="triggerFileInput" class="upload-btn">
                    <span class="upload-icon">📷</span>
                    <span>Agregar fotografías</span>
                  </button>
                  <p class="upload-hint">JPG o PNG. Máximo 10 MB por archivo.</p>
                </div>

                <!-- Preview de fotos -->
                <div v-if="form.animal.fotos.length > 0" class="photos-preview">
                  <div
                    v-for="(foto, index) in form.animal.fotos"
                    :key="index"
                    class="photo-item"
                  >
                    <img :src="foto.preview" :alt="`Foto ${index + 1}`" />
                    <button
                      type="button"
                      @click="removePhoto(index)"
                      class="photo-remove"
                      title="Eliminar foto"
                    >×</button>
                    <span v-if="index === 0" class="photo-badge">Principal</span>
                  </div>
                </div>
              </div>
            </template>

            <!-- Si no se rescató animal -->
            <div v-if="form.animal_rescatado === false" class="info-box">
              <span class="info-icon">ℹ️</span>
              <p>El operativo fue exitoso pero no se rescató ningún animal. Puede continuar al cierre del caso.</p>
            </div>
          </form>
        </div>

        <!-- PASO 3: Cierre -->
        <div v-if="currentStep === 2" class="step-content">
          <div class="step-intro">
            <p>Revise la información y confirme el cierre del operativo.</p>
          </div>

          <!-- Resumen -->
          <div class="summary-card">
            <h4>Resumen del operativo</h4>

            <div class="summary-section">
              <div class="summary-label">Resultado:</div>
              <div class="summary-value">
                <span :class="form.exitoso ? 'badge-success' : 'badge-error'">
                  {{ form.exitoso ? '✅ Exitoso' : '❌ No exitoso' }}
                </span>
              </div>
            </div>

            <div class="summary-section">
              <div class="summary-label">Fecha de atención:</div>
              <div class="summary-value">{{ form.fecha_ejecucion }} {{ form.hora_ejecucion }}</div>
            </div>

            <div class="summary-section">
              <div class="summary-label">Observaciones:</div>
              <div class="summary-value text-wrap">{{ form.observaciones || 'Sin observaciones' }}</div>
            </div>

            <template v-if="form.exitoso === false">
              <div class="summary-section">
                <div class="summary-label">Motivo del fallo:</div>
                <div class="summary-value text-wrap">{{ form.motivo_fallo }}</div>
              </div>
            </template>

            <template v-if="form.exitoso && form.animal_rescatado">
              <div class="summary-section animal-summary">
                <div class="summary-label">Animal rescatado:</div>
                <div class="summary-value animal-details">
                  <div class="animal-info-grid">
                    <div class="animal-info-item">
                      <span class="info-label">Especie:</span>
                      <span class="info-value">{{ especieLabel(form.animal.especie) }}</span>
                    </div>
                    <div v-if="form.animal.raza" class="animal-info-item">
                      <span class="info-label">Raza:</span>
                      <span class="info-value">{{ form.animal.raza }}</span>
                    </div>
                    <div class="animal-info-item">
                      <span class="info-label">Sexo:</span>
                      <span class="info-value">{{ sexoLabel(form.animal.sexo) }}</span>
                    </div>
                    <div v-if="form.animal.color" class="animal-info-item">
                      <span class="info-label">Color:</span>
                      <span class="info-value">{{ form.animal.color }}</span>
                    </div>
                    <div v-if="form.animal.tamanio" class="animal-info-item">
                      <span class="info-label">Tamaño:</span>
                      <span class="info-value">{{ tamanioLabel(form.animal.tamanio) }}</span>
                    </div>
                    <div v-if="form.animal.edad_aproximada" class="animal-info-item">
                      <span class="info-label">Edad:</span>
                      <span class="info-value">{{ formatEdad(form.animal.edad_aproximada) }}</span>
                    </div>
                    <div class="animal-info-item">
                      <span class="info-label">Estado salud:</span>
                      <span class="info-value" :class="`health-${form.animal.estado_salud}`">
                        {{ estadoSaludLabel(form.animal.estado_salud) }}
                      </span>
                    </div>
                    <div class="animal-info-item">
                      <span class="info-label">Estado:</span>
                      <span class="info-value">{{ estadoAnimalLabel(form.animal.estado) }}</span>
                    </div>
                    <div class="animal-info-item">
                      <span class="info-label">Destino:</span>
                      <span class="info-value">{{ destinoAnimalLabel(form.animal.destino) }}</span>
                    </div>
                  </div>
                  <div v-if="form.animal.fotos.length > 0" class="animal-photos-summary">
                    <span class="info-label">Fotos:</span>
                    <span class="info-value">{{ form.animal.fotos.length }} fotografía(s) adjunta(s)</span>
                  </div>
                </div>
              </div>
            </template>

            <template v-if="form.exitoso && form.animal_rescatado === false">
              <div class="summary-section">
                <div class="summary-label">Animal rescatado:</div>
                <div class="summary-value">No se rescató ningún animal</div>
              </div>
            </template>
          </div>

          <!-- Estado final de la denuncia -->
          <div class="final-status">
            <label class="form-label">Estado final de la denuncia:</label>
            <div class="status-badge" :class="form.exitoso ? 'status-resuelta' : 'status-cerrada'">
              {{ form.exitoso ? 'Resuelta' : 'Cerrada' }}
            </div>
            <p class="status-note">
              {{ form.exitoso
                ? 'La denuncia será marcada como resuelta exitosamente.'
                : 'La denuncia será cerrada debido a que el operativo no fue exitoso.'
              }}
            </p>
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
          v-if="currentStep < totalSteps - 1"
          @click="nextStep"
          class="govco-btn govco-bg-elf-green"
          :disabled="!canProceed"
        >
          Siguiente
        </button>
        <button
          v-else
          @click="handleSubmit"
          class="govco-btn govco-bg-elf-green"
          :disabled="!isFormValid || isSubmitting"
        >
          <span v-if="isSubmitting">Guardando...</span>
          <span v-else>Finalizar y cerrar</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useComplaintsStore } from '@/stores/complaints';
import complaintService from '@/services/complaintService';
import api from '@/services/api';

const props = defineProps({
  complaint: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'saved']);

const complaintsStore = useComplaintsStore();
const isSubmitting = ref(false);
const fileInput = ref(null);

// Stepper
const currentStep = ref(0);
const steps = computed(() => {
  // Si no es exitoso, saltamos el paso 2 (animal)
  if (form.value.exitoso === false) {
    return [
      { id: 'resultado', label: 'Resultado' },
      { id: 'cierre', label: 'Cierre' }
    ];
  }
  return [
    { id: 'resultado', label: 'Resultado' },
    { id: 'animal', label: 'Animal' },
    { id: 'cierre', label: 'Cierre' }
  ];
});

const totalSteps = computed(() => steps.value.length);

// Form state
const form = ref({
  exitoso: null,
  observaciones: '',
  motivo_fallo: '',
  fecha_ejecucion: new Date().toISOString().split('T')[0],
  hora_ejecucion: new Date().toTimeString().slice(0, 5),
  animal_rescatado: null,
  animal: {
    nombre: '',
    especie: '',
    raza: '',
    sexo: 'desconocido',
    color: '',
    tamanio: '',
    edad_aproximada: null,
    peso_actual: null,
    senias_particulares: '',
    estado_salud: '',
    estado: '',
    esterilizacion: null,
    observaciones: '',
    destino: '',
    fotos: [] // Array de { file: File, preview: string }
  }
});

// Funciones para manejo de fotos
function triggerFileInput() {
  fileInput.value?.click();
}

function handleFileSelect(event) {
  const files = event.target.files;
  if (!files || files.length === 0) return;

  const maxSize = 10 * 1024 * 1024; // 10MB
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];

  for (const file of files) {
    if (!allowedTypes.includes(file.type)) {
      if (window.$toast) {
        window.$toast.warning('Archivo no válido', `${file.name} no es un formato permitido (JPG/PNG).`);
      }
      continue;
    }

    if (file.size > maxSize) {
      if (window.$toast) {
        window.$toast.warning('Archivo muy grande', `${file.name} supera el límite de 10 MB.`);
      }
      continue;
    }

    // Crear preview
    const reader = new FileReader();
    reader.onload = (e) => {
      form.value.animal.fotos.push({
        file: file,
        preview: e.target.result
      });
    };
    reader.readAsDataURL(file);
  }

  // Limpiar input para permitir seleccionar el mismo archivo de nuevo
  event.target.value = '';
}

function removePhoto(index) {
  form.value.animal.fotos.splice(index, 1);
}

// Computed
const todayDate = computed(() => {
  return new Date().toISOString().split('T')[0];
});

const equipoNombre = computed(() => {
  const rescate = props.complaint.rescate;
  if (rescate?.equipo_rescate?.nombre) {
    return rescate.equipo_rescate.nombre;
  }
  return 'Equipo asignado';
});

// Validación por paso
const canProceed = computed(() => {
  if (currentStep.value === 0) {
    // Paso 1: Resultado
    const baseValid = form.value.exitoso !== null &&
           form.value.observaciones.trim() !== '' &&
           form.value.fecha_ejecucion &&
           form.value.hora_ejecucion;

    if (form.value.exitoso === false) {
      return baseValid && form.value.motivo_fallo.trim() !== '';
    }
    return baseValid;
  }

  if (currentStep.value === 1) {
    // Paso 2: Animal (solo si exitoso)
    if (form.value.exitoso === false) return true; // Saltamos este paso
    if (form.value.animal_rescatado === null) return false;
    if (form.value.animal_rescatado === false) return true;

    // Si hay animal, validar campos requeridos
    return form.value.animal.especie !== '' &&
           form.value.animal.estado_salud !== '' &&
           form.value.animal.estado !== '' &&
           form.value.animal.destino !== '';
  }

  return true;
});

const isFormValid = computed(() => {
  // Validación final antes de enviar
  const baseValid = form.value.exitoso !== null &&
         form.value.observaciones.trim() !== '' &&
         form.value.fecha_ejecucion &&
         form.value.hora_ejecucion;

  if (form.value.exitoso === false) {
    return baseValid && form.value.motivo_fallo.trim() !== '';
  }

  // Si es exitoso y hay animal
  if (form.value.animal_rescatado === true) {
    return baseValid &&
           form.value.animal.especie !== '' &&
           form.value.animal.estado_salud !== '' &&
           form.value.animal.estado !== '' &&
           form.value.animal.destino !== '';
  }

  return baseValid && form.value.animal_rescatado !== null;
});

// Navigation
function nextStep() {
  if (canProceed.value && currentStep.value < totalSteps.value - 1) {
    currentStep.value++;
  }
}

function prevStep() {
  if (currentStep.value > 0) {
    currentStep.value--;
  }
}

function goToStep(index) {
  if (index < currentStep.value) {
    currentStep.value = index;
  }
}

// Helpers
function formatDateTime(dateString) {
  if (!dateString) return 'Sin fecha';
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return 'Fecha inválida';
  return date.toLocaleDateString('es-CO', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function estadoAnimalLabel(estado) {
  const estados = {
    en_refugio: 'En refugio',
    en_tratamiento: 'En tratamiento',
    en_adopcion: 'En adopción',
    adoptado: 'Adoptado',
    fallecido: 'Fallecido',
    en_calle: 'En calle'
  };
  return estados[estado] || estado || 'No especificado';
}

function estadoSaludLabel(estado) {
  const estados = {
    critico: 'Crítico',
    grave: 'Grave',
    estable: 'Estable',
    bueno: 'Bueno',
    excelente: 'Excelente'
  };
  return estados[estado] || estado || 'No especificado';
}

function destinoAnimalLabel(destino) {
  const destinos = {
    refugio: 'Refugio municipal',
    clinica_veterinaria: 'Clínica veterinaria',
    hogar_paso: 'Hogar de paso',
    liberado: 'Liberado',
    fallecido: 'Fallecido en sitio'
  };
  return destinos[destino] || destino || 'No especificado';
}

function especieLabel(especie) {
  const especies = {
    canino: 'Canino (Perro)',
    felino: 'Felino (Gato)',
    equino: 'Equino',
    ave: 'Ave',
    otro: 'Otro'
  };
  return especies[especie] || especie || 'No especificada';
}

function sexoLabel(sexo) {
  const sexos = {
    macho: 'Macho',
    hembra: 'Hembra',
    desconocido: 'Desconocido'
  };
  return sexos[sexo] || sexo || 'Desconocido';
}

function tamanioLabel(tamanio) {
  const tamanios = {
    pequenio: 'Pequeño',
    mediano: 'Mediano',
    grande: 'Grande',
    muy_grande: 'Muy grande'
  };
  return tamanios[tamanio] || tamanio || 'No especificado';
}

function formatEdad(meses) {
  if (!meses) return 'Desconocida';
  const anios = Math.floor(meses / 12);
  const mesesRestantes = meses % 12;
  const partes = [];
  if (anios > 0) partes.push(`${anios} año${anios > 1 ? 's' : ''}`);
  if (mesesRestantes > 0) partes.push(`${mesesRestantes} mes${mesesRestantes > 1 ? 'es' : ''}`);
  return partes.join(' y ') || 'Desconocida';
}

// Submit
async function handleSubmit() {
  if (!isFormValid.value || isSubmitting.value) return;

  isSubmitting.value = true;

  try {
    const rescateId = props.complaint.rescate_id || props.complaint.rescate?.id;
    const denunciaId = props.complaint.id;

    if (!rescateId) {
      throw new Error('No se encontró el ID del rescate');
    }

    // Construir fecha_ejecucion completa
    const fechaEjecucion = `${form.value.fecha_ejecucion} ${form.value.hora_ejecucion}:00`;

    let animalCreado = null;

    // 1. Si hay animal rescatado, crear el animal en el sistema PRIMERO
    if (form.value.exitoso && form.value.animal_rescatado && form.value.animal.especie) {
      console.log('Registrando animal rescatado...');

      // Preparar datos del animal
      const animalData = {
        nombre: form.value.animal.nombre || null,
        especie: form.value.animal.especie,
        raza: form.value.animal.raza || null,
        sexo: form.value.animal.sexo || 'desconocido',
        color: form.value.animal.color || null,
        tamanio: form.value.animal.tamanio || null,
        edad_aproximada: form.value.animal.edad_aproximada || null,
        peso_actual: form.value.animal.peso_actual || null,
        senias_particulares: form.value.animal.senias_particulares || null,
        estado_salud: form.value.animal.estado_salud,
        estado: form.value.animal.estado,
        esterilizacion: form.value.animal.esterilizacion,
        observaciones: form.value.animal.observaciones || null,
        fecha_rescate: form.value.fecha_ejecucion,
        ubicacion_rescate: props.complaint.ubicacion || props.complaint.direccion || null,
        procedencia: 'rescate'
      };

      // Verificar si hay fotos para usar FormData
      const hasPhotos = form.value.animal.fotos.length > 0;

      if (hasPhotos) {
        // Usar FormData para enviar con fotos
        const fd = new FormData();

        // Agregar campos del animal
        Object.entries(animalData).forEach(([key, value]) => {
          if (value === null || value === undefined || value === '') return;
          if (typeof value === 'boolean') {
            fd.append(key, value ? '1' : '0');
          } else {
            fd.append(key, String(value));
          }
        });

        // Agregar foto principal (primera foto)
        const primeraFoto = form.value.animal.fotos[0]?.file;
        if (primeraFoto) {
          fd.append('foto_principal', primeraFoto);
        }

        // Agregar galería (resto de fotos)
        form.value.animal.fotos.slice(1).forEach((foto) => {
          if (foto.file) {
            fd.append('galeria_fotos[]', foto.file);
          }
        });

        const response = await api.post('/animals', fd);
        animalCreado = response.data?.data ?? response.data;
      } else {
        // Sin fotos, enviar JSON normal
        const response = await api.post('/animals', animalData);
        animalCreado = response.data?.data ?? response.data;
      }

      console.log('Animal registrado:', animalCreado);
    }

    // 2. Actualizar el rescate
    const rescateData = {
      exitoso: form.value.exitoso,
      observaciones: form.value.observaciones,
      fecha_ejecucion: fechaEjecucion,
      motivo_fallo: form.value.exitoso === false ? form.value.motivo_fallo : null,
      estado_animal_rescate: form.value.animal_rescatado ? form.value.animal.estado_salud : null,
      destino: form.value.animal_rescatado ? form.value.animal.destino : null,
      animal_id: animalCreado?.id || null
    };

    await complaintService.updateRescate(rescateId, rescateData);

    // 3. Si se creó animal, vincular al rescate
    if (animalCreado?.id) {
      try {
        await complaintService.vincularAnimal(rescateId, animalCreado.id);
        console.log('Animal vinculado al rescate');
      } catch (vinculaError) {
        console.warn('No se pudo vincular animal al rescate:', vinculaError);
      }
    }

    // 4. Actualizar estado de la denuncia
    const nuevoEstado = form.value.exitoso ? 'resuelta' : 'cerrada';
    const estadoData = {
      estado: nuevoEstado,
      observaciones_resolucion: form.value.observaciones
    };

    await complaintService.actualizarEstado(denunciaId, estadoData);

    // Refrescar datos
    await Promise.all([
      complaintsStore.fetchRescates(),
      complaintsStore.fetchDenuncias()
    ]);

    // Notificar éxito
    if (window.$toast) {
      const mensaje = animalCreado
        ? `El operativo fue exitoso y se registró el animal (${animalCreado.codigo_unico || 'ID: ' + animalCreado.id}).`
        : `El operativo ha sido marcado como ${form.value.exitoso ? 'exitoso' : 'no exitoso'}.`;
      window.$toast.success('Resultado registrado', mensaje);
    }

    emit('saved');
    emit('close');

  } catch (error) {
    console.error('Error al registrar resultado:', error);
    const errorMsg = error.response?.data?.message || 'Error al guardar el resultado. Intente nuevamente.';
    if (window.$toast) {
      window.$toast.error('Error', errorMsg);
    } else {
      alert(errorMsg);
    }
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
  max-width: 650px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-content.modal-large {
  max-width: 800px;
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

/* Stepper */
.stepper {
  display: flex;
  justify-content: center;
  padding: 1rem 1.5rem;
  background: #f5f5f5;
  border-bottom: 1px solid #e0e0e0;
  gap: 0.5rem;
}

.step {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  color: #666;
  transition: all 0.2s;
}

.step.active {
  background: #068460;
  color: white;
}

.step.completed {
  background: #e8f5e9;
  color: #2e7d32;
}

.step.clickable {
  cursor: pointer;
}

.step.clickable:hover {
  background: #d0d0d0;
}

.step-number {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #e0e0e0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.8rem;
}

.step.active .step-number {
  background: white;
  color: #068460;
}

.step.completed .step-number {
  background: #2e7d32;
  color: white;
}

.step-label {
  font-weight: 500;
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

/* Operation summary */
.operation-summary {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 1rem;
  background: #f9f9f9;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

.summary-row {
  display: flex;
  gap: 1.5rem;
}

.summary-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  flex: 1;
}

.summary-item.full-width {
  flex: none;
  width: 100%;
  padding-top: 0.5rem;
  border-top: 1px solid #e0e0e0;
}

.summary-icon {
  font-size: 1.25rem;
  flex-shrink: 0;
}

.summary-content {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.summary-label {
  font-size: 0.75rem;
  color: #666;
}

.summary-value {
  font-weight: 500;
  color: #333;
  font-size: 0.9rem;
  word-break: break-word;
}

/* Step content */
.step-content {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateX(10px); }
  to { opacity: 1; transform: translateX(0); }
}

.step-intro {
  margin-bottom: 1.25rem;
  padding: 0.75rem;
  background: #e3f2fd;
  border-radius: 6px;
  border-left: 4px solid #2196f3;
}

.step-intro p {
  margin: 0;
  color: #1565c0;
  font-size: 0.9rem;
}

/* Form */
.result-form {
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

/* Result options */
.result-options {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.result-option {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1.25rem 1rem;
  border: 2px solid #E0E0E0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}

.result-option.small {
  padding: 1rem 0.75rem;
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

.result-icon-big {
  font-size: 2.5rem;
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

/* Info box */
.info-box {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem;
  background: #fff3e0;
  border-radius: 8px;
  border-left: 4px solid #ff9800;
}

.info-icon {
  font-size: 1.25rem;
  flex-shrink: 0;
}

.info-box p {
  margin: 0;
  color: #e65100;
  font-size: 0.9rem;
}

/* Animal sections */
.animal-section {
  background: #f9f9f9;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 1.25rem;
  margin-bottom: 1rem;
}

.animal-section .section-title {
  margin: 0 0 1rem 0;
  font-size: 0.95rem;
  font-weight: 600;
  color: #3366cc;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #e0e0e0;
}

.form-hint {
  font-size: 0.75rem;
  color: #666;
  margin-top: 0.25rem;
}

/* Photo upload */
.photo-upload-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1.5rem;
  border: 2px dashed #d0d0d0;
  border-radius: 8px;
  background: #fafafa;
  margin-bottom: 1rem;
}

.file-input-hidden {
  display: none;
}

.upload-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #3366cc;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.upload-btn:hover {
  background: #2850a0;
}

.upload-icon {
  font-size: 1.25rem;
}

.upload-hint {
  margin: 0.75rem 0 0 0;
  font-size: 0.8rem;
  color: #666;
}

.photos-preview {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 0.75rem;
}

.photo-item {
  position: relative;
  aspect-ratio: 1;
  border-radius: 8px;
  overflow: hidden;
  background: #f0f0f0;
}

.photo-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.photo-remove {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 24px;
  height: 24px;
  background: rgba(168, 5, 33, 0.9);
  color: white;
  border: none;
  border-radius: 50%;
  font-size: 1rem;
  line-height: 1;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.photo-remove:hover {
  background: #a80521;
}

.photo-badge {
  position: absolute;
  bottom: 4px;
  left: 4px;
  padding: 2px 8px;
  background: #068460;
  color: white;
  font-size: 0.7rem;
  font-weight: 600;
  border-radius: 4px;
}

/* Animal summary in cierre step */
.animal-summary {
  flex-direction: column;
}

.animal-summary .summary-label {
  margin-bottom: 0.75rem;
}

.animal-details {
  background: #f5f5f5;
  border-radius: 6px;
  padding: 1rem;
}

.animal-info-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
}

.animal-info-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.animal-info-item .info-label {
  font-size: 0.75rem;
  color: #666;
}

.animal-info-item .info-value {
  font-weight: 500;
  color: #333;
}

.animal-photos-summary {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #e0e0e0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.animal-photos-summary .info-label {
  font-size: 0.85rem;
  color: #666;
}

.animal-photos-summary .info-value {
  font-weight: 500;
  color: #068460;
}

/* Health status colors */
.health-critico { color: #a80521; font-weight: 600; }
.health-grave { color: #e65100; font-weight: 600; }
.health-estable { color: #ff9800; }
.health-bueno { color: #068460; }
.health-excelente { color: #2e7d32; font-weight: 600; }

/* Summary card (cierre) */
.summary-card {
  background: #f5f5f5;
  border-radius: 8px;
  padding: 1.25rem;
  margin-bottom: 1.5rem;
}

.summary-card h4 {
  margin: 0 0 1rem 0;
  color: #333;
  font-size: 1rem;
  border-bottom: 1px solid #e0e0e0;
  padding-bottom: 0.75rem;
}

.summary-section {
  display: flex;
  margin-bottom: 0.75rem;
  gap: 0.5rem;
}

.summary-section .summary-label {
  font-weight: 600;
  color: #555;
  min-width: 140px;
  flex-shrink: 0;
}

.summary-section .summary-value {
  color: #333;
}

.summary-section .summary-value.text-wrap {
  word-break: break-word;
  white-space: pre-wrap;
}

.badge-success {
  color: #2e7d32;
  font-weight: 600;
}

.badge-error {
  color: #c62828;
  font-weight: 600;
}

/* Final status */
.final-status {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 1.25rem;
  text-align: center;
}

.final-status .form-label {
  margin-bottom: 0.75rem;
}

.status-badge {
  display: inline-block;
  padding: 0.5rem 1.5rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 1.1rem;
  margin-bottom: 0.75rem;
}

.status-badge.status-resuelta {
  background: #e8f5e9;
  color: #2e7d32;
}

.status-badge.status-cerrada {
  background: #ffebee;
  color: #c62828;
}

.status-note {
  margin: 0;
  color: #666;
  font-size: 0.85rem;
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
.govco-bg-elf-green { background: #068460; }

@media (max-width: 768px) {
  .animal-info-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 576px) {
  .stepper {
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .step {
    padding: 0.4rem 0.75rem;
    font-size: 0.8rem;
  }

  .step-label {
    display: none;
  }

  .summary-row {
    flex-direction: column;
    gap: 0.75rem;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .result-options {
    grid-template-columns: 1fr;
  }

  .summary-section {
    flex-direction: column;
    gap: 0.25rem;
  }

  .summary-section .summary-label {
    min-width: auto;
  }

  .modal-footer {
    flex-direction: column;
  }

  .govco-btn {
    width: 100%;
  }

  .animal-info-grid {
    grid-template-columns: 1fr;
  }

  .photos-preview {
    grid-template-columns: repeat(3, 1fr);
  }
}
</style>
