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
          <!-- Canal de recepción -->
          <DesplegableGovco
            id="canalRecepcion"
            v-model="form.canalRecepcion"
            label="Canal de recepción"
            :required="true"
            :options="canalRecepcionOptions"
            placeholder="Seleccionar canal"
            :alert-text="errors.canalRecepcion"
            :error="!!errors.canalRecepcion"
            width="100%"
          />

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

          <!-- Prioridad -->
          <DesplegableGovco
            id="prioridad"
            v-model="form.prioridad"
            label="Prioridad"
            :required="true"
            :options="prioridadOptions"
            placeholder="Seleccionar prioridad"
            :alert-text="errors.prioridad"
            :error="!!errors.prioridad"
            width="100%"
          />

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
          <!-- Dirección con botón de búsqueda -->
          <div class="full-width">
            <label class="label-desplegable-govco">
              Dirección o referencia<span aria-required="true">*</span>
            </label>
            <div class="address-input-wrapper">
              <input
                id="address"
                v-model="form.address"
                type="text"
                class="address-input"
                :class="{ 'error': errors.address }"
                placeholder="Ej: Calle 15 #23-45, Barrio El Centro, Cali"
                @keyup.enter="searchAddressOnMap"
              />
              <button
                type="button"
                class="search-address-btn"
                @click="searchAddressOnMap"
                :disabled="isSearchingAddress"
                title="Buscar dirección en el mapa"
              >
                <span v-if="isSearchingAddress">...</span>
                <span v-else>Buscar</span>
              </button>
            </div>
            <span class="info-entradas-de-texto-govco">
              Formato: Calle 1C #68-23, Barrio, Cali. Puede editar la dirección después de seleccionar en el mapa.
            </span>
            <span v-if="errors.address" class="alert-entradas-de-texto-govco">{{ errors.address }}</span>
          </div>

          <!-- Mapa interactivo -->
          <div class="full-width">
            <label class="label-desplegable-govco">
              Ubicación en el mapa<span aria-required="true">*</span>
            </label>
            <div class="map-container">
              <MapSelector
                ref="mapSelectorRef"
                v-model="form.coordinates"
                :initial-center="{ lat: 3.4516, lng: -76.5319 }"
                :zoom="13"
                @address-found="onAddressFound"
              />
              <p class="map-placeholder">
                <span v-if="form.coordinates">
                  Lat: {{ form.coordinates.lat.toFixed(6) }},
                  Lng: {{ form.coordinates.lng.toFixed(6) }}
                </span>
                <span v-else>
                  Haga clic en el mapa para marcar la ubicación. Luego ajuste la dirección arriba si es necesario.
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
            <!-- Nombres -->
            <InputGovCo
              id="reporterFirstName"
              v-model="form.reporterFirstName"
              label="Nombres"
              placeholder="Sus nombres"
            />

            <!-- Apellidos -->
            <InputGovCo
              id="reporterLastName"
              v-model="form.reporterLastName"
              label="Apellidos"
              placeholder="Sus apellidos"
            />

            <!-- Teléfono -->
            <InputGovCo
              id="reporterPhone"
              v-model="form.reporterPhone"
              label="Teléfono de contacto"
              type="tel"
              placeholder="3001234567"
            />

            <!-- Email -->
            <InputGovCo
              id="reporterEmail"
              v-model="form.reporterEmail"
              label="Correo electrónico"
              type="email"
              placeholder="correo@ejemplo.com"
              help-text="Para recibir notificaciones del caso"
            />

            <!-- Dirección del denunciante -->
            <InputGovCo
              id="reporterAddress"
              v-model="form.reporterAddress"
              label="Dirección de residencia"
              placeholder="Su dirección de residencia"
              class="full-width"
            />
          </template>

          <div v-else class="anonymous-notice full-width">
            <div class="notice-box">
              <span class="notice-icon">🔒</span>
              <p>Su denuncia será procesada de forma anónima. No podremos contactarlo para información adicional, pero puede consultar el estado con el número de caso que recibirá.</p>
            </div>
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
import { reactive, ref, nextTick } from 'vue';
import MapSelector from '../common/MapSelector.vue';
import DesplegableGovco from '../common/DesplegableGovco.vue';
import InputGovCo from '../common/InputGovCo.vue';
import FileUploader from '../common/FileUploader.vue';
import { useComplaintsStore } from '@/stores/complaints';

const emit = defineEmits(['submitted']);
const complaintsStore = useComplaintsStore();

const formEl = ref(null);
const mapSelectorRef = ref(null);
const isSubmitting = ref(false);
const isSearchingAddress = ref(false);

// Opciones para los dropdowns (valores deben coincidir con backend)
// canal_recepcion: 'web', 'telefono', 'presencial', 'email', 'whatsapp'
const canalRecepcionOptions = [
  { value: 'web', text: 'Página web' },
  { value: 'telefono', text: 'Teléfono' },
  { value: 'presencial', text: 'Presencial' },
  { value: 'email', text: 'Correo electrónico' },
  { value: 'whatsapp', text: 'WhatsApp' }
];

// tipo_denuncia: 'maltrato', 'abandono', 'animal_herido', 'animal_peligroso', 'otro'
const complaintTypeOptions = [
  { value: 'maltrato', text: 'Maltrato' },
  { value: 'abandono', text: 'Abandono' },
  { value: 'animal_herido', text: 'Animal herido' },
  { value: 'animal_peligroso', text: 'Animal peligroso' },
  { value: 'otro', text: 'Otro' }
];

// prioridad: 'baja', 'media', 'alta', 'urgente'
const prioridadOptions = [
  { value: 'baja', text: 'Baja' },
  { value: 'media', text: 'Media' },
  { value: 'alta', text: 'Alta' },
  { value: 'urgente', text: 'Urgente' }
];

const form = reactive({
  // Información del caso
  canalRecepcion: 'web',
  complaintType: '',
  prioridad: 'media',
  description: '',

  // Ubicación
  address: '',
  coordinates: null,

  // Evidencias
  evidence: [],

  // Denunciante
  isAnonymous: false,
  reporterFirstName: '',
  reporterLastName: '',
  reporterPhone: '',
  reporterEmail: '',
  reporterAddress: '',

  // Terminos
  acceptTerms: false
});

const errors = reactive({
  canalRecepcion: '',
  complaintType: '',
  prioridad: '',
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

  if (!form.canalRecepcion) {
    errors.canalRecepcion = 'Seleccione el canal de recepción';
    isValid = false;
  }

  if (!form.complaintType) {
    errors.complaintType = 'Seleccione el tipo de denuncia';
    isValid = false;
  }

  if (!form.prioridad) {
    errors.prioridad = 'Seleccione la prioridad';
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

// Preparar datos para envio al backend
// Mapeo de campos frontend -> backend segun DenunciaController@store
function prepareComplaintData() {
  // Log de valores del formulario antes de preparar
  console.log('Estado del formulario antes de preparar:', {
    isAnonymous: form.isAnonymous,
    reporterFirstName: form.reporterFirstName,
    reporterLastName: form.reporterLastName,
    reporterPhone: form.reporterPhone,
    reporterEmail: form.reporterEmail,
    reporterAddress: form.reporterAddress
  });

  const data = {
    // Campos requeridos por backend
    canal_recepcion: form.canalRecepcion,
    tipo_denuncia: form.complaintType,
    prioridad: form.prioridad,
    descripcion: form.description,
    ubicacion: form.address,

    // Campos de ubicacion
    latitud: form.coordinates?.lat || null,
    longitud: form.coordinates?.lng || null,

    // Anonimato
    es_anonima: form.isAnonymous,

    // Denunciante (solo si no es anonima)
    denunciante: form.isAnonymous ? null : {
      nombres: form.reporterFirstName || null,
      apellidos: form.reporterLastName || null,
      telefono: form.reporterPhone || null,
      email: form.reporterEmail || null,
      direccion: form.reporterAddress || null,
    },
  };

  // Limpiar denunciante si es nulo
  if (data.es_anonima) {
    delete data.denunciante;
  }

  console.log('Datos preparados para enviar:', data);

  return data;
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
  if (window.$toast) {
    window.$toast.info('Terminos y Condiciones', '1. La informacion sera utilizada solo para atender la denuncia. 2. Las denuncias falsas tienen consecuencias legales. 3. Datos protegidos segun Ley 1581 de 2012.', 15000);
  } else {
    alert('Términos y Condiciones:\n\n1. La información proporcionada será utilizada únicamente para atender la denuncia.\n2. Las denuncias falsas pueden tener consecuencias legales.\n3. Los datos personales serán protegidos según la Ley 1581 de 2012.\n4. El tiempo de respuesta depende de la urgencia y disponibilidad de recursos.');
  }
}

/**
 * Lee valores directamente de los elementos HTML
 * Solucion temporal mientras DesplegableGovco no emite update:modelValue correctamente
 */
function syncFormFromDOM() {
  // Leer valores de los selects HTML nativos
  const canalRecepcionSelect = document.querySelector('#canalRecepcion-select');
  const complaintTypeSelect = document.querySelector('#complaintType-select');
  const prioridadSelect = document.querySelector('#prioridad-select');
  const descriptionTextarea = document.querySelector('#description');
  const addressInput = document.querySelector('#address input');

  // Actualizar form reactive con valores reales del DOM
  if (canalRecepcionSelect && canalRecepcionSelect.value) {
    form.canalRecepcion = canalRecepcionSelect.value;
  }

  if (complaintTypeSelect && complaintTypeSelect.value) {
    form.complaintType = complaintTypeSelect.value;
  }

  if (prioridadSelect && prioridadSelect.value) {
    form.prioridad = prioridadSelect.value;
  }

  if (descriptionTextarea && descriptionTextarea.value) {
    form.description = descriptionTextarea.value;
  }

  if (addressInput && addressInput.value) {
    form.address = addressInput.value;
  }

  // Sincronizar campos del denunciante desde el DOM
  // (por si el v-model no está capturando correctamente los valores)
  if (!form.isAnonymous) {
    const reporterFirstNameInput = document.querySelector('#reporterFirstName');
    const reporterLastNameInput = document.querySelector('#reporterLastName');
    const reporterPhoneInput = document.querySelector('#reporterPhone');
    const reporterEmailInput = document.querySelector('#reporterEmail');
    const reporterAddressInput = document.querySelector('#reporterAddress');

    if (reporterFirstNameInput && reporterFirstNameInput.value) {
      form.reporterFirstName = reporterFirstNameInput.value;
    }
    if (reporterLastNameInput && reporterLastNameInput.value) {
      form.reporterLastName = reporterLastNameInput.value;
    }
    if (reporterPhoneInput && reporterPhoneInput.value) {
      form.reporterPhone = reporterPhoneInput.value;
    }
    if (reporterEmailInput && reporterEmailInput.value) {
      form.reporterEmail = reporterEmailInput.value;
    }
    if (reporterAddressInput && reporterAddressInput.value) {
      form.reporterAddress = reporterAddressInput.value;
    }

    console.log('Datos del denunciante sincronizados:', {
      nombres: form.reporterFirstName,
      apellidos: form.reporterLastName,
      telefono: form.reporterPhone,
      email: form.reporterEmail,
      direccion: form.reporterAddress
    });
  }
}

// Handler para cuando el mapa encuentra una dirección
function onAddressFound(address) {
  if (address) {
    form.address = address;
  }
}

// Submit
async function onSubmit() {
  // Sincronizar valores del DOM primero
  syncFormFromDOM();

  // Esperar a que Vue sincronice todos los valores antes de validar
  await nextTick();

  if (!validate()) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  isSubmitting.value = true;

  try {
    const complaintData = prepareComplaintData();
    console.log('Enviando denuncia al backend:', complaintData);

    // LLAMADA REAL AL BACKEND
    const response = await complaintsStore.crearDenuncia(complaintData);
    console.log('Respuesta del backend:', response);

    // Obtener el ticket del response
    const ticket = response?.ticket || response?.data?.ticket || 'N/A';

    // Emitir al padre (ComplaintsView) que se encarga del toast y cambio de pestaña
    emit('submitted', ticket);
    resetForm();

  } catch (error) {
    console.error('Error al enviar denuncia:', error);

    let errorMessage = 'No se pudo registrar la denuncia. Por favor intente nuevamente.';

    // Extraer mensaje de error del backend
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      const firstError = Object.values(errors)[0];
      errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.message) {
      errorMessage = error.message;
    }

    if (window.$toast) {
      window.$toast.error('Error al registrar', errorMessage);
    } else {
      alert(`Error: ${errorMessage}`);
    }
  } finally {
    isSubmitting.value = false;
  }
}

// Buscar dirección en el mapa
async function searchAddressOnMap() {
  if (!form.address || form.address.trim().length < 5) {
    if (window.$toast) {
      window.$toast.warning('Dirección muy corta', 'Ingrese una dirección más completa para buscar');
    }
    return;
  }

  if (!mapSelectorRef.value) {
    console.error('MapSelector ref no disponible');
    return;
  }

  isSearchingAddress.value = true;

  try {
    const result = await mapSelectorRef.value.searchAddress(form.address);

    if (result) {
      if (window.$toast) {
        window.$toast.success('Ubicación encontrada', 'Se marcó la dirección en el mapa');
      }
    } else {
      if (window.$toast) {
        window.$toast.warning('No encontrada', 'No se encontró la dirección. Intente con más detalles o marque manualmente en el mapa.');
      }
    }
  } catch (error) {
    console.error('Error buscando dirección:', error);
    if (window.$toast) {
      window.$toast.error('Error', 'No se pudo buscar la dirección');
    }
  } finally {
    isSearchingAddress.value = false;
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

/* Búsqueda de dirección */
.address-input-wrapper {
  display: flex;
  gap: 0.5rem;
  align-items: stretch;
}

.address-input {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #737373;
  border-radius: 4px;
  font-size: 1rem;
  height: 44px;
  box-sizing: border-box;
}

.address-input:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 2px rgba(51, 102, 204, 0.2);
}

.address-input.error {
  border-color: #A80521;
  background-color: #FDEAED;
}

.search-address-btn {
  padding: 0 1.25rem;
  height: 44px;
  border: none;
  background: #3366CC;
  color: white;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 600;
  white-space: nowrap;
  transition: all 0.2s;
}

.search-address-btn:hover:not(:disabled) {
  background: #254a99;
}

.search-address-btn:disabled {
  background: #999;
  cursor: not-allowed;
}

.alert-entradas-de-texto-govco {
  display: block;
  color: #A80521;
  font-size: 0.85rem;
  margin-top: 0.25rem;
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
