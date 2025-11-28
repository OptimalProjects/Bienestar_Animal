<!-- src/components/complaints/ComplaintForm.vue -->
<!-- HU-015: Reportar Denuncia de Maltrato Animal -->
<template>
  <section class="complaint-form">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Reportar Denuncia de Maltrato Animal</h2>
      <p class="text2-tipografia-govco">
        Complete el formulario para reportar un caso de maltrato, abandono o situación de riesgo animal.
        <strong>No requiere autenticación.</strong>
      </p>
    </div>

    <form ref="formEl" @submit.prevent="onSubmit" novalidate>

      <!-- SECCIÓN 1: INFORMACIÓN DEL CASO -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Información del caso</h3>

        <div class="form-grid">
          <!-- Tipo de denuncia -->
          <DesplegableGovco
            id="complaintType"
            v-model="form.complaintType"
            label="Tipo de denuncia"
            :required="true"
            :options="complaintTypeOptions"
            placeholder="Seleccionar tipo"
            :alert-text="errors.complaintType"
            :error="!!errors.complaintType"
            width="100%"
          />

          <!-- Urgencia sugerida -->
          <DesplegableGovco
            id="urgency"
            v-model="form.urgency"
            label="Nivel de urgencia"
            :required="true"
            :options="urgencyOptions"
            placeholder="Seleccionar urgencia"
            :alert-text="errors.urgency"
            :error="!!errors.urgency"
            width="100%"
          />

          <!-- Especie del animal -->
          <DesplegableGovco
            id="animalSpecies"
            v-model="form.animalSpecies"
            label="Especie del animal"
            :required="true"
            :options="speciesOptions"
            placeholder="Seleccionar especie"
            :alert-text="errors.animalSpecies"
            :error="!!errors.animalSpecies"
            width="100%"
          />

          <!-- Cantidad aproximada -->
          <div class="entradas-de-texto-govco">
            <label for="animalCount">Cantidad aproximada de animales</label>
            <input
              type="number"
              id="animalCount"
              v-model="form.animalCount"
              min="1"
              placeholder="1"
            />
            <span class="info-entradas-de-texto-govco">Si no sabe, deje 1</span>
          </div>

          <!-- Descripción del caso -->
          <div class="entradas-de-texto-govco full-width">
            <label for="description">Descripción detallada del caso<span aria-required="true">*</span></label>
            <textarea
              id="description"
              v-model="form.description"
              rows="5"
              placeholder="Describa la situación observada: qué tipo de maltrato o situación, desde cuándo ocurre (si lo sabe), condiciones del animal, comportamiento del presunto responsable, etc."
            ></textarea>
            <span class="info-entradas-de-texto-govco">Mínimo 50 caracteres. Sea lo más detallado posible.</span>
            <span v-if="errors.description" class="error-text">{{ errors.description }}</span>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 2: UBICACIÓN -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Ubicación del caso</h3>

        <div class="form-grid">
          <!-- Dirección -->
          <div class="entradas-de-texto-govco full-width">
            <label for="address">Dirección o referencia<span aria-required="true">*</span></label>
            <input
              type="text"
              id="address"
              v-model="form.address"
              placeholder="Ej: Calle 15 #23-45, Barrio El Centro, frente a la tienda..."
            />
            <span class="info-entradas-de-texto-govco">Incluya barrio, comuna o puntos de referencia</span>
            <span v-if="errors.address" class="error-text">{{ errors.address }}</span>
          </div>

          <!-- Mapa interactivo -->
          <div class="full-width">
            <label class="label-desplegable-govco">
              Ubicacion en el mapa<span aria-required="true">*</span>
            </label>
            <div class="map-container">
              <MapSelector
                v-model="form.coordinates"
                :initial-center="{ lat: 3.4516, lng: -76.5319 }"
                :zoom="13"
              />
              <p class="map-placeholder">
                <span v-if="form.coordinates">
                  Lat: {{ form.coordinates.lat.toFixed(6) }},
                  Lng: {{ form.coordinates.lng.toFixed(6) }}
                </span>
                <span v-else>
                  Haga clic en el mapa o use "Mi ubicacion" para marcar el lugar del incidente
                </span>
              </p>
            </div>
            <span v-if="errors.coordinates" class="alert-desplegable-govco">{{ errors.coordinates }}</span>
          </div>
        </div>
      </div>

      <!-- SECCION 3: EVIDENCIAS -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Evidencias (fotografias y videos)</h3>

        <div class="form-grid">
          <div class="full-width">
            <div class="evidence-info">
              <p class="text2-tipografia-govco">
                Adjunte fotografías o videos que documenten la situación. Las evidencias ayudan a priorizar y atender mejor su denuncia.
              </p>
            </div>

            <!-- Usar componente FileUploader -->
            <FileUploader
              v-model="form.evidence"
              label="Fotografías y videos del caso (opcional)"
              accept="image/*,video/*"
              :max-files="10"
              :max-size-m-b="10"
              help-text="Formatos: JPG, PNG, MP4, MOV. Máximo 10 archivos, 10MB cada uno."
            />

            <span v-if="errors.evidence" class="alert-desplegable-govco">{{ errors.evidence }}</span>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 4: DATOS DEL DENUNCIANTE (OPCIONAL) -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Datos del denunciante (opcional)</h3>

        <div class="form-grid">
          <div class="checkbox-govco full-width anonymous-checkbox">
            <input
              type="checkbox"
              id="anonymous"
              v-model="form.isAnonymous"
            />
            <label for="anonymous">
              Deseo realizar esta denuncia de forma <strong>anónima</strong>
            </label>
          </div>

          <template v-if="!form.isAnonymous">
            <!-- Nombre -->
            <div class="entradas-de-texto-govco">
              <label for="reporterName">Nombre completo</label>
              <input
                type="text"
                id="reporterName"
                v-model="form.reporterName"
                placeholder="Su nombre completo"
              />
            </div>

            <!-- Cédula -->
            <div class="entradas-de-texto-govco">
              <label for="reporterId">Número de cédula</label>
              <input
                type="text"
                id="reporterId"
                v-model="form.reporterId"
                placeholder="1234567890"
              />
            </div>

            <!-- Teléfono -->
            <div class="entradas-de-texto-govco">
              <label for="reporterPhone">Teléfono de contacto</label>
              <input
                type="tel"
                id="reporterPhone"
                v-model="form.reporterPhone"
                placeholder="3001234567"
              />
            </div>

            <!-- Email -->
            <div class="entradas-de-texto-govco">
              <label for="reporterEmail">Correo electrónico</label>
              <input
                type="email"
                id="reporterEmail"
                v-model="form.reporterEmail"
                placeholder="correo@ejemplo.com"
              />
              <span class="info-entradas-de-texto-govco">Para recibir notificaciones del caso</span>
            </div>
          </template>

          <div v-else class="anonymous-notice full-width">
            <div class="notice-box">
              <span class="notice-icon">🔒</span>
              <p>Su denuncia será procesada de forma anónima. No podremos contactarlo para información adicional, pero puede consultar el estado con el número de caso que recibirá.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 5: INFORMACIÓN ADICIONAL -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Información adicional</h3>

        <div class="form-grid">
          <!-- Conoce al responsable? -->
          <div class="checkbox-govco">
            <input
              type="checkbox"
              id="knowsResponsible"
              v-model="form.knowsResponsible"
            />
            <label for="knowsResponsible">
              Tengo información sobre el presunto responsable
            </label>
          </div>

          <!-- Datos del responsable -->
          <div v-if="form.knowsResponsible" class="entradas-de-texto-govco full-width">
            <label for="responsibleInfo">Información del presunto responsable</label>
            <textarea
              id="responsibleInfo"
              v-model="form.responsibleInfo"
              rows="3"
              placeholder="Nombre, descripción física, dirección, placa de vehículo, o cualquier dato que ayude a identificarlo..."
            ></textarea>
            <span class="info-entradas-de-texto-govco">Esta información es confidencial y solo será usada para la investigación</span>
          </div>

          <!-- Observaciones adicionales -->
          <div class="entradas-de-texto-govco full-width">
            <label for="additionalNotes">Observaciones adicionales</label>
            <textarea
              id="additionalNotes"
              v-model="form.additionalNotes"
              rows="3"
              placeholder="Cualquier información adicional que considere relevante..."
            ></textarea>
          </div>
        </div>
      </div>

      <!-- TÉRMINOS Y CONDICIONES -->
      <div class="form-section terms-section">
        <div class="checkbox-govco">
          <input
            type="checkbox"
            id="acceptTerms"
            v-model="form.acceptTerms"
          />
          <label for="acceptTerms">
            Declaro que la información proporcionada es verdadera y acepto los
            <a href="#" @click.prevent="showTerms">términos y condiciones</a> del servicio.
          </label>
        </div>
        <span v-if="errors.acceptTerms" class="error-text">{{ errors.acceptTerms }}</span>
      </div>

      <!-- BOTONES -->
      <div class="form-actions">
        <button type="button" @click="resetForm" class="govco-btn govco-bg-concrete" :disabled="isSubmitting">
          Limpiar formulario
        </button>
        <button type="submit" class="govco-btn govco-bg-elf-green" :disabled="isSubmitting">
          {{ isSubmitting ? 'Enviando denuncia...' : 'Enviar denuncia' }}
        </button>
      </div>
    </form>
  </section>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import MapSelector from '../common/MapSelector.vue';
import DesplegableGovco from '../common/DesplegableGovco.vue';
import FileUploader from '../common/FileUploader.vue';

const emit = defineEmits(['submitted']);

const formEl = ref(null);
const isSubmitting = ref(false);

// Opciones para los dropdowns
const complaintTypeOptions = [
  { value: 'maltrato_fisico', text: 'Maltrato fisico' },
  { value: 'abandono', text: 'Abandono' },
  { value: 'negligencia', text: 'Negligencia (falta de alimento/agua/refugio)' },
  { value: 'hacinamiento', text: 'Hacinamiento' },
  { value: 'pelea_animales', text: 'Pelea de animales' },
  { value: 'animal_herido', text: 'Animal herido en via publica' },
  { value: 'envenenamiento', text: 'Posible envenenamiento' },
  { value: 'otro', text: 'Otro' }
];

const urgencyOptions = [
  { value: 'critico', text: 'Critico - Riesgo de vida inminente' },
  { value: 'alto', text: 'Alto - Maltrato activo' },
  { value: 'medio', text: 'Medio - Abandono/negligencia' },
  { value: 'bajo', text: 'Bajo - Situación no urgente' }
];

const speciesOptions = [
  { value: 'perro', text: 'Perro' },
  { value: 'gato', text: 'Gato' },
  { value: 'equino', text: 'Equino (caballo, burro)' },
  { value: 'bovino', text: 'Bovino' },
  { value: 'ave', text: 'Ave' },
  { value: 'otro', text: 'Otro' },
  { value: 'desconocido', text: 'No se / Varios' }
];

const form = reactive({
  // Información del caso
  complaintType: '',
  urgency: '',
  animalSpecies: '',
  animalCount: 1,
  description: '',

  // Ubicación
  address: '',
  coordinates: null,

  // Evidencias
  evidence: [],

  // Denunciante
  isAnonymous: false,
  reporterName: '',
  reporterId: '',
  reporterPhone: '',
  reporterEmail: '',

  // Información adicional
  knowsResponsible: false,
  responsibleInfo: '',
  additionalNotes: '',

  // Terminos
  acceptTerms: false
});

const errors = reactive({
  complaintType: '',
  urgency: '',
  animalSpecies: '',
  description: '',
  address: '',
  coordinates: '',
  evidence: '',
  acceptTerms: ''
});

// Validacion
function validate() {
  Object.keys(errors).forEach(k => errors[k] = '');

  let isValid = true;

  if (!form.complaintType) {
    errors.complaintType = 'Seleccione el tipo de denuncia';
    isValid = false;
  }

  if (!form.urgency) {
    errors.urgency = 'Seleccione el nivel de urgencia';
    isValid = false;
  }

  if (!form.animalSpecies) {
    errors.animalSpecies = 'Seleccione la especie del animal';
    isValid = false;
  }

  if (!form.description || form.description.trim().length < 50) {
    errors.description = 'La descripcion debe tener al menos 50 caracteres';
    isValid = false;
  }

  if (!form.address || form.address.trim().length < 10) {
    errors.address = 'Ingrese una direccion o referencia valida';
    isValid = false;
  }

  if (!form.coordinates) {
    errors.coordinates = 'Marque la ubicación en el mapa';
    isValid = false;
  }

  if (!form.acceptTerms) {
    errors.acceptTerms = 'Debe aceptar los terminos y condiciones';
    isValid = false;
  }

  return isValid;
}

// Generar número de caso
function generateCaseNumber() {
  const date = new Date();
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
  return `DEN-${year}${month}-${random}`;
}

// Clasificar urgencia automaticamente
function classifyUrgency() {
  const criticalTypes = ['maltrato_fisico', 'pelea_animales', 'envenenamiento'];
  const highTypes = ['animal_herido'];

  if (criticalTypes.includes(form.complaintType) && form.urgency !== 'critico') {
    return 'critico';
  }
  if (highTypes.includes(form.complaintType) && !['critico', 'alto'].includes(form.urgency)) {
    return 'alto';
  }
  return form.urgency;
}

// Preparar datos para envio
function prepareComplaintData() {
  return {
    caso_numero: generateCaseNumber(),
    tipo_denuncia: form.complaintType,
    urgencia: classifyUrgency(),
    especie_animal: form.animalSpecies,
    cantidad_animales: form.animalCount || 1,
    descripcion: form.description,
    direccion: form.address,
    ubicacion_lat: form.coordinates?.lat,
    ubicacion_lng: form.coordinates?.lng,
    es_anonimo: form.isAnonymous,
    denunciante_nombre: form.isAnonymous ? null : form.reporterName,
    denunciante_cedula: form.isAnonymous ? null : form.reporterId,
    denunciante_telefono: form.isAnonymous ? null : form.reporterPhone,
    denunciante_email: form.isAnonymous ? null : form.reporterEmail,
    conoce_responsable: form.knowsResponsible,
    info_responsable: form.knowsResponsible ? form.responsibleInfo : null,
    observaciones: form.additionalNotes,
    estado: 'recibida',
    fecha_recepcion: new Date().toISOString()
  };
}

// Reset
function resetForm() {
  Object.keys(form).forEach(k => {
    if (Array.isArray(form[k])) {
      form[k] = [];
    } else if (typeof form[k] === 'boolean') {
      form[k] = false;
    } else if (typeof form[k] === 'number') {
      form[k] = 1;
    } else if (k === 'coordinates') {
      form[k] = null;
    } else {
      form[k] = '';
    }
  });
  Object.keys(errors).forEach(k => errors[k] = '');
}

function showTerms() {
  alert('Términos y Condiciones:\n\n1. La información proporcionada será utilizada únicamente para atender la denuncia.\n2. Las denuncias falsas pueden tener consecuencias legales.\n3. Los datos personales serán protegidos según la Ley 1581 de 2012.\n4. El tiempo de respuesta depende de la urgencia y disponibilidad de recursos.');
}

// Submit
async function onSubmit() {
  if (!validate()) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  isSubmitting.value = true;

  try {
    const complaintData = prepareComplaintData();

    console.log('Enviando denuncia:', complaintData);

    // Simular respuesta exitosa
    await new Promise(resolve => setTimeout(resolve, 1500));

    const caseNumber = complaintData.caso_numero;

    emit('submitted', caseNumber);
    resetForm();

  } catch (error) {
    console.error('Error al enviar denuncia:', error);
    alert('Error al enviar la denuncia. Por favor intente nuevamente.');
  } finally {
    isSubmitting.value = false;
  }
}
</script>

<style scoped>
.complaint-form {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  background: #f5f7fb;
}

.form-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #A80521;
}

.form-header h2 {
  color: #A80521;
}

.form-section {
  background: white;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  overflow: visible;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.section-title {
  margin: 0;
  padding: 1rem 1.5rem;
  background: #E8F0FE;
  color: #3366CC;
  font-weight: 600;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  column-gap: 2rem;
  row-gap: 1.5rem;
  padding: 1.5rem;
  overflow: visible;
}

.full-width {
  grid-column: 1 / 3;
}

/* Estilos para DesplegableGovco */
.form-grid > *:nth-child(1) :deep(.desplegable-govco) { z-index: 50; }
.form-grid > *:nth-child(2) :deep(.desplegable-govco) { z-index: 49; }
.form-grid > *:nth-child(3) :deep(.desplegable-govco) { z-index: 48; }
.form-grid > *:nth-child(4) :deep(.desplegable-govco) { z-index: 47; }

:deep(.desplegable-govco) {
  position: relative;
}

:deep(.desplegable-govco select) {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  height: 44px;
  background: white;
  cursor: pointer;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  padding-right: 36px;
}

:deep(.desplegable-govco select:focus) {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 2px rgba(51, 102, 204, 0.2);
}

:deep(.label-desplegable-govco) {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

/* Inputs */
.entradas-de-texto-govco {
  display: flex;
  flex-direction: column;
}

.entradas-de-texto-govco label {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.entradas-de-texto-govco input,
.entradas-de-texto-govco textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  line-height: 1.5;
  box-sizing: border-box;
}

.entradas-de-texto-govco input {
  height: 44px;
}

.entradas-de-texto-govco textarea {
  resize: vertical;
  min-height: 100px;
}

.entradas-de-texto-govco input:focus,
.entradas-de-texto-govco textarea:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 2px rgba(51, 102, 204, 0.2);
}

/* Textos de ayuda y error */
.info-entradas-de-texto-govco {
  display: block;
  color: #666;
  font-size: 0.85rem;
  margin-top: 0.25rem;
}

.error-text,
.alert-desplegable-govco {
  display: block;
  color: #b00020;
  font-size: 0.85rem;
  margin-top: 0.5rem;
  font-weight: 500;
}

/* Mapa */
.map-container {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.label-desplegable-govco {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.map-placeholder {
  color: #666;
  text-align: center;
  margin: 0;
  padding: 0.5rem;
  background: #f5f7fb;
  border-radius: 4px;
  font-size: 0.9rem;
}

/* Evidencias */
.evidence-info {
  margin-bottom: 1rem;
  padding: 1rem;
  background: #FFF8E1;
  border-radius: 4px;
  border-left: 4px solid #FFAB00;
}

.evidence-info p {
  margin: 0;
}

/* Checkbox */
.checkbox-govco {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem;
  background: #f5f7fb;
  border-radius: 6px;
}

.checkbox-govco input {
  width: 20px;
  height: 20px;
  cursor: pointer;
  margin-top: 2px;
  flex-shrink: 0;
  accent-color: #3366CC;
}

.checkbox-govco label {
  cursor: pointer;
  margin: 0;
  line-height: 1.4;
}

.checkbox-govco a {
  color: #3366CC;
  text-decoration: none;
}

.checkbox-govco a:hover {
  text-decoration: underline;
}

/* Denuncia anonima */
.anonymous-checkbox {
  background: #E8F0FE;
  border: 2px solid #3366CC;
}

.anonymous-notice {
  grid-column: 1 / 3;
}

.notice-box {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  background: #E8F0FE;
  border-radius: 8px;
  border: 1px solid #3366CC;
}

.notice-icon {
  font-size: 1.5rem;
}

.notice-box p {
  margin: 0;
  color: #004884;
}

/* Terminos */
.terms-section {
  padding: 1.5rem;
}

.terms-section .checkbox-govco {
  background: transparent;
  padding: 0;
}

/* Botones */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
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
  transform: none;
}

.govco-bg-concrete {
  background-color: #737373;
}

.govco-bg-elf-green {
  background-color: #069169;
}

/* Responsive */
@media (max-width: 992px) {
  .form-grid {
    grid-template-columns: 1fr;
  }

  .full-width,
  .anonymous-notice {
    grid-column: 1;
  }
}

@media (max-width: 576px) {
  .complaint-form {
    padding: 1rem;
  }

  .form-actions {
    flex-direction: column;
  }

  .govco-btn {
    width: 100%;
  }
}
</style>
