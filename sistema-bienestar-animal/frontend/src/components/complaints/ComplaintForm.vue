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
          <div class="input-like-govco">
            <label for="complaintType" class="label-desplegable-govco">
              Tipo de denuncia<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic" id="complaintType-dropdown">
              <select id="complaintType" v-model="form.complaintType" aria-invalid="false">
                <option disabled value="">Seleccionar tipo</option>
                <option value="maltrato_fisico">Maltrato físico</option>
                <option value="abandono">Abandono</option>
                <option value="negligencia">Negligencia (falta de alimento/agua/refugio)</option>
                <option value="hacinamiento">Hacinamiento</option>
                <option value="pelea_animales">Pelea de animales</option>
                <option value="animal_herido">Animal herido en vía pública</option>
                <option value="envenenamiento">Posible envenenamiento</option>
                <option value="otro">Otro</option>
              </select>
            </div>
            <span v-if="errors.complaintType" class="alert-desplegable-govco">{{ errors.complaintType }}</span>
          </div>

          <!-- Urgencia sugerida -->
          <div class="input-like-govco">
            <label for="urgency" class="label-desplegable-govco">
              Nivel de urgencia<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic" id="urgency-dropdown">
              <select id="urgency" v-model="form.urgency" aria-invalid="false">
                <option disabled value="">Seleccionar urgencia</option>
                <option value="critico">Crítico - Riesgo de vida inminente</option>
                <option value="alto">Alto - Maltrato activo</option>
                <option value="medio">Medio - Abandono/negligencia</option>
                <option value="bajo">Bajo - Situación no urgente</option>
              </select>
            </div>
            <span v-if="errors.urgency" class="alert-desplegable-govco">{{ errors.urgency }}</span>
          </div>

          <!-- Especie del animal -->
          <div class="input-like-govco">
            <label for="animalSpecies" class="label-desplegable-govco">
              Especie del animal<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic" id="animalSpecies-dropdown">
              <select id="animalSpecies" v-model="form.animalSpecies" aria-invalid="false">
                <option disabled value="">Seleccionar especie</option>
                <option value="perro">Perro</option>
                <option value="gato">Gato</option>
                <option value="equino">Equino (caballo, burro)</option>
                <option value="bovino">Bovino</option>
                <option value="ave">Ave</option>
                <option value="otro">Otro</option>
                <option value="desconocido">No sé / Varios</option>
              </select>
            </div>
            <span v-if="errors.animalSpecies" class="alert-desplegable-govco">{{ errors.animalSpecies }}</span>
          </div>

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
              Ubicación en el mapa<span aria-required="true">*</span>
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
                  Haga clic en el mapa o use "Mi ubicación" para marcar el lugar del incidente
                </span>
              </p>
            </div>
            <span v-if="errors.coordinates" class="alert-desplegable-govco">{{ errors.coordinates }}</span>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 3: EVIDENCIAS -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Evidencias (fotografías y videos)</h3>

        <div class="form-grid">
          <div class="full-width">
            <div class="evidence-info">
              <p class="text2-tipografia-govco">
                Adjunte fotografías o videos que documenten la situación. Las evidencias ayudan a priorizar y atender mejor su denuncia.
              </p>
            </div>

            <!-- Carga de archivos GOV.CO -->
            <div class="container-carga-de-archivo-govco">
              <div class="loader-carga-de-archivo-govco">
                <div class="all-input-carga-de-archivo-govco">
                  <input
                    id="evidence"
                    type="file"
                    class="input-carga-de-archivo-govco"
                    multiple
                    accept="image/*,video/*"
                    @change="onFilesSelected"
                  />
                  <label for="evidence" class="label-carga-de-archivo-govco">
                    Fotografías y videos del caso (opcional)
                  </label>
                  <label for="evidence" class="container-input-carga-de-archivo-govco">
                    <span class="button-file-carga-de-archivo-govco">Seleccionar archivos</span>
                    <span class="file-name-carga-de-archivo-govco">{{ evidenceLabel }}</span>
                  </label>
                  <span class="text-validation-carga-de-archivo-govco">
                    Formatos: JPG, PNG, MP4, MOV. Máximo 10 archivos, 10MB cada uno.
                  </span>
                </div>
              </div>

              <!-- Lista de archivos seleccionados -->
              <div v-if="form.evidence.length > 0" class="evidence-preview">
                <div v-for="(file, index) in form.evidence" :key="index" class="evidence-item">
                  <span class="evidence-icon">{{ getFileIcon(file) }}</span>
                  <span class="evidence-name">{{ file.name }}</span>
                  <span class="evidence-size">({{ formatSize(file.size) }})</span>
                  <button type="button" class="evidence-remove" @click="removeFile(index)">Quitar</button>
                </div>
              </div>

              <span v-if="errors.evidence" class="alert-desplegable-govco">{{ errors.evidence }}</span>
            </div>
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
              <span class="notice-icon">=</span>
              <p>Su denuncia será procesada de forma anónima. No podremos contactarlo para información adicional, pero puede consultar el estado con el número de caso que recibirá.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 5: INFORMACIÓN ADICIONAL -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Información adicional</h3>

        <div class="form-grid">
          <!-- ¿Conoce al responsable? -->
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
import { reactive, ref, computed, onMounted, nextTick } from 'vue';
import MapSelector from '../common/MapSelector.vue';

const API_BASE_URL = 'http://localhost:8000/api/v1';

const emit = defineEmits(['submitted']);

const formEl = ref(null);
const isSubmitting = ref(false);

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

  // Términos
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

// Computed
const evidenceLabel = computed(() => {
  if (form.evidence.length === 0) return 'Sin archivos seleccionados';
  return `${form.evidence.length} archivo${form.evidence.length > 1 ? 's' : ''} seleccionado${form.evidence.length > 1 ? 's' : ''}`;
});

// Funciones de archivos
function onFilesSelected(event) {
  const files = Array.from(event.target.files || []);
  errors.evidence = '';

  const maxSize = 10 * 1024 * 1024; // 10MB
  const maxFiles = 10;
  const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'video/mp4', 'video/quicktime', 'video/x-msvideo'];

  // Validar cantidad
  if (form.evidence.length + files.length > maxFiles) {
    errors.evidence = `Máximo ${maxFiles} archivos permitidos.`;
    return;
  }

  // Validar cada archivo
  for (const file of files) {
    if (!allowedTypes.includes(file.type)) {
      errors.evidence = `Formato no permitido: ${file.name}. Use JPG, PNG, MP4 o MOV.`;
      return;
    }
    if (file.size > maxSize) {
      errors.evidence = `El archivo ${file.name} supera el límite de 10MB.`;
      return;
    }
  }

  form.evidence.push(...files);
}

function removeFile(index) {
  form.evidence.splice(index, 1);
}

function getFileIcon(file) {
  if (file.type.startsWith('image/')) return '=÷';
  if (file.type.startsWith('video/')) return '<¥';
  return '=Î';
}

function formatSize(bytes) {
  if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

// Inicialización GOV.CO
function initializeGovcoComponents() {
  if (typeof window === 'undefined' || !window.GOVCo) return;

  nextTick(() => {
    const dropdowns = document.querySelectorAll('.desplegable-govco[data-type="basic"]');
    dropdowns.forEach(dropdown => {
      if (window.GOVCo?.init) {
        window.GOVCo.init(dropdown.parentElement);
      }
    });

    setTimeout(() => syncDropdownValues(), 200);
  });
}

function setupDropdownListeners() {
  const selects = ['complaintType', 'urgency', 'animalSpecies'];

  selects.forEach(id => {
    const select = document.getElementById(id);
    if (select) {
      select.addEventListener('change', (e) => {
        form[id] = e.target.value;
      });
    }
  });
}

function syncDropdownValues() {
  const complaintType = document.getElementById('complaintType');
  const urgency = document.getElementById('urgency');
  const animalSpecies = document.getElementById('animalSpecies');

  if (complaintType) form.complaintType = complaintType.value;
  if (urgency) form.urgency = urgency.value;
  if (animalSpecies) form.animalSpecies = animalSpecies.value;
}

function preventScrollOnInteractions() {
  const handleDropdownOpen = (e) => {
    const element = e.target.closest('.desplegable-govco');
    if (element) {
      const scrollPos = window.scrollY || document.documentElement.scrollTop;
      setTimeout(() => {
        window.scrollTo(0, scrollPos);
      }, 50);
    }
  };

  if (formEl.value) {
    formEl.value.removeEventListener('click', handleDropdownOpen);
    formEl.value.addEventListener('click', handleDropdownOpen);
  }
}

// Validación
function validate() {
  syncDropdownValues();
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
    errors.description = 'La descripción debe tener al menos 50 caracteres';
    isValid = false;
  }

  if (!form.address || form.address.trim().length < 10) {
    errors.address = 'Ingrese una dirección o referencia válida';
    isValid = false;
  }

  if (!form.coordinates) {
    errors.coordinates = 'Marque la ubicación en el mapa';
    isValid = false;
  }

  if (!form.acceptTerms) {
    errors.acceptTerms = 'Debe aceptar los términos y condiciones';
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

// Clasificar urgencia automáticamente
function classifyUrgency() {
  // La urgencia puede ser reclasificada por el sistema según el tipo
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

// Preparar datos para envío
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

    // TODO: Integrar con API real
    // const response = await fetch(`${API_BASE_URL}/complaints`, {
    //   method: 'POST',
    //   headers: { 'Content-Type': 'application/json' },
    //   body: JSON.stringify(complaintData)
    // });

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

onMounted(() => {
  initializeGovcoComponents();
  setupDropdownListeners();
  preventScrollOnInteractions();

  if (typeof window !== 'undefined') {
    window.addEventListener('load', () => {
      initializeGovcoComponents();
      setupDropdownListeners();
    });
  }
});
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
}

.full-width {
  grid-column: 1 / 3;
}

/* Inputs */
.entradas-de-texto-govco input,
.entradas-de-texto-govco textarea,
.desplegable-govco select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  line-height: 1.5;
  box-sizing: border-box;
}

.entradas-de-texto-govco input,
.desplegable-govco select {
  height: 44px;
}

.entradas-de-texto-govco textarea {
  resize: vertical;
  min-height: 100px;
}

.input-like-govco {
  display: flex;
  flex-direction: column;
  width: 100%;
  margin: 18px 0;
}

.input-like-govco label {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.desplegable-govco {
  position: relative;
  width: 100%;
}

.desplegable-govco select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23333' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 12px 8px;
  padding-right: 2.5rem;
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

.evidence-preview {
  margin-top: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.evidence-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #f5f7fb;
  border-radius: 4px;
}

.evidence-icon {
  font-size: 1.2rem;
}

.evidence-name {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.evidence-size {
  color: #666;
  font-size: 0.85rem;
}

.evidence-remove {
  background: none;
  border: none;
  color: #A80521;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 500;
}

.evidence-remove:hover {
  text-decoration: underline;
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

/* Denuncia anónima */
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

/* Términos */
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

/* Carga de archivos GOV.CO */
.container-carga-de-archivo-govco .button-file-carga-de-archivo-govco {
  background: #3366CC;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}

.container-carga-de-archivo-govco .file-name-carga-de-archivo-govco {
  margin-left: 1rem;
  color: #666;
}

.container-carga-de-archivo-govco .input-carga-de-archivo-govco {
  display: none;
}

.container-carga-de-archivo-govco .container-input-carga-de-archivo-govco {
  display: flex;
  align-items: center;
  padding: 1rem;
  border: 2px dashed #D0D0D0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.container-carga-de-archivo-govco .container-input-carga-de-archivo-govco:hover {
  border-color: #3366CC;
  background: #f5f7fb;
}

.container-carga-de-archivo-govco .text-validation-carga-de-archivo-govco {
  display: block;
  color: #666;
  font-size: 0.85rem;
  margin-top: 0.5rem;
}

/* Z-index para dropdowns */
:deep(.desplegable-govco .desplegable-items) {
  z-index: 1500 !important;
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
