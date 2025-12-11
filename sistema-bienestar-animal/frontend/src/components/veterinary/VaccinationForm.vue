<template>
  <section class="vaccination-form">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Registro de Vacunación</h2>
      <p class="text2-tipografia-govco">Complete los datos de la vacuna aplicada</p>
    </div>

    <!-- Indicador de carga -->
    <div v-if="loadingData" class="loading-overlay">
      <div class="spinner"></div>
      <p>Cargando datos...</p>
    </div>

    <form v-else ref="formEl" @submit.prevent="onSubmit" novalidate>
      
      <!-- SECCIÓN 1: IDENTIFICACIÓN -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Datos del animal</h3>
        
        <div class="form-grid">
          <div class="input-like-govco">
            <DesplegableGovco
              ref="animalDropdownRef"
              id="animal"
              label="Animal"
              :options="animalOptions"
              v-model="form.animalId"
              placeholder="Seleccionar animal"
              :required="true"
              :alert-text="errors.animalId"
              :error="!!errors.animalId"
              width="100%"
              height="44px"
              @change="form.animalId = $event"
            />
          </div>

          <div class="input-like-govco calendar-wrapper">
            <CalendarioGovco
              ref="applicationDateRef"
              id="applicationDateCalendar"
              input-id="applicationDate"
              label="Fecha de aplicación"
              v-model="form.applicationDate"
              view-days="true"
              :required="true"
              width="100%"
              height="44px"
              :alert-text="errors.applicationDate"
              :error="!!errors.applicationDate"
              @change="onDateChange"
            />
          </div>
        </div>
      </div>

      <!-- SECCIÓN 2: DATOS DE LA VACUNA -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Información de la vacuna</h3>
        
        <div class="form-grid">
          <div class="input-like-govco">
            <DesplegableGovco
              ref="vaccineTypeRef"
              id="vaccineType"
              label="Tipo de vacuna"
              :options="vaccineTypeOptions"
              v-model="form.vaccineType"
              placeholder="Seleccionar tipo"
              :required="true"
              :alert-text="errors.vaccineType"
              :error="!!errors.vaccineType"
              width="100%"
              height="44px"
              @change="onVaccineTypeChange"
            />
          </div>

          <div class="input-wrapper">
            <label for="vaccineName">Nombre comercial<span class="required">*</span></label>
            <input
              type="text"
              id="vaccineName"
              v-model="form.vaccineName"
              placeholder="Ej: Nobivac, Vanguard"
              class="input-govco"
            />
            <span v-if="errors.vaccineName" class="error-text">{{ errors.vaccineName }}</span>
          </div>

          <div class="input-wrapper">
            <label for="laboratory">Laboratorio fabricante<span class="required">*</span></label>
            <input
              type="text"
              id="laboratory"
              v-model="form.laboratory"
              placeholder="Ej: MSD, Zoetis"
              class="input-govco"
            />
            <span v-if="errors.laboratory" class="error-text">{{ errors.laboratory }}</span>
          </div>

          <div class="input-wrapper">
            <label for="batchNumber">Número de lote<span class="required">*</span></label>
            <input
              type="text"
              id="batchNumber"
              v-model="form.batchNumber"
              placeholder="LOT123456"
              class="input-govco"
            />
            <span v-if="errors.batchNumber" class="error-text">{{ errors.batchNumber }}</span>
          </div>

          <div class="input-like-govco calendar-wrapper">
            <CalendarioGovco
              ref="expirationDateRef"
              id="expirationDateCalendar"
              input-id="expirationDate"
              label="Fecha de vencimiento"
              v-model="form.expirationDate"
              view-days="true"
              :required="true"
              width="100%"
              height="44px"
              :alert-text="errors.expirationDate"
              :error="!!errors.expirationDate"
              @change="form.expirationDate = $event"
            />
          </div>

          <div class="input-wrapper">
            <label for="dose">Dosis (ml)<span class="required">*</span></label>
            <input
              type="number"
              id="dose"
              v-model.number="form.dose"
              step="0.1"
              min="0.1"
              max="10"
              placeholder="1.0"
              class="input-govco"
            />
            <span v-if="errors.dose" class="error-text">{{ errors.dose }}</span>
          </div>

          <div class="input-like-govco">
            <DesplegableGovco
              ref="routeRef"
              id="route"
              label="Vía de aplicación"
              :options="routeOptions"
              v-model="form.route"
              placeholder="Seleccionar vía"
              :required="true"
              :alert-text="errors.route"
              :error="!!errors.route"
              width="100%"
              height="44px"
              @change="form.route = $event"
            />
          </div>

          <div class="input-wrapper">
            <label for="site">Sitio de aplicación</label>
            <input
              type="text"
              id="site"
              v-model="form.site"
              placeholder="Ej: Miembro anterior derecho"
              class="input-govco"
            />
          </div>
        </div>
      </div>

      <!-- SECCIÓN 3: ESQUEMA DE VACUNACIÓN -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Esquema de vacunación</h3>
        
        <div class="form-grid">
          <div class="input-like-govco">
            <DesplegableGovco
              ref="doseNumberRef"
              id="doseNumber"
              label="Dosis número"
              :options="doseNumberOptions"
              v-model="form.doseNumber"
              placeholder="Seleccionar"
              :required="true"
              :alert-text="errors.doseNumber"
              :error="!!errors.doseNumber"
              width="100%"
              height="44px"
              @change="form.doseNumber = $event"
            />
          </div>

          <div class="checkbox-wrapper full-width">
            <input 
              type="checkbox" 
              id="requiresNext" 
              v-model="form.requiresNextDose"
              @change="calculateNextDose"
            />
            <label for="requiresNext">Requiere próxima dosis</label>
          </div>

          <div v-if="form.requiresNextDose" class="input-like-govco calendar-wrapper full-width">
            <CalendarioGovco
              ref="nextDoseDateRef"
              id="nextDoseDateCalendar"
              input-id="nextDoseDate"
              label="Fecha de próxima dosis"
              v-model="form.nextDoseDate"
              view-days="true"
              :required="true"
              width="100%"
              height="44px"
              :alert-text="errors.nextDoseDate"
              :error="!!errors.nextDoseDate"
              @change="form.nextDoseDate = $event"
            />
            <span class="info-text">{{ nextDoseInfo }}</span>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 4: VETERINARIO Y OBSERVACIONES -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Responsable y observaciones</h3>
        
        <div class="form-grid">
          <div class="input-like-govco">
            <DesplegableGovco
              ref="veterinarianRef"
              id="veterinarian"
              label="Veterinario aplicador"
              :options="veterinarianOptions"
              v-model="form.veterinarianId"
              placeholder="Seleccionar veterinario"
              :required="true"
              :alert-text="errors.veterinarianId"
              :error="!!errors.veterinarianId"
              width="100%"
              height="44px"
              @change="form.veterinarianId = $event"
            />
          </div>

          <div class="input-wrapper full-width">
            <label for="observations">Observaciones</label>
            <textarea
              id="observations"
              v-model="form.observations"
              rows="3"
              placeholder="Reacciones adversas, estado del animal post-vacunación..."
              class="input-govco"
            ></textarea>
          </div>

          <div class="checkbox-wrapper full-width">
            <input 
              type="checkbox" 
              id="generateCertificate" 
              v-model="form.generateCertificate"
            />
            <label for="generateCertificate">Generar certificado de vacunación en PDF</label>
          </div>
        </div>
      </div>

      <!-- BOTONES -->
      <div class="form-actions">
        <button type="button" @click="resetForm" class="btn-secondary">
          Cancelar
        </button>
        <button type="submit" class="btn-primary">
          Registrar vacunación
        </button>
      </div>
    </form>

  </section>
</template>

<script setup>
import { reactive, ref, computed, onMounted, nextTick, watch } from 'vue';
import DesplegableGovco from '../common/DesplegableGovco.vue';
import CalendarioGovco from '../common/CalendarioGovco.vue';
import { useVeterinaryStore } from '@/stores/veterinary';
import { useAnimalsStore } from '@/stores/animals';
import animalService from '@/services/animalService';

const veterinaryStore = useVeterinaryStore();
const animalsStore = useAnimalsStore();

const formEl = ref(null);
const animalDropdownRef = ref(null);
const applicationDateRef = ref(null);
const vaccineTypeRef = ref(null);
const expirationDateRef = ref(null);
const routeRef = ref(null);
const doseNumberRef = ref(null);
const nextDoseDateRef = ref(null);
const veterinarianRef = ref(null);
const isSubmitting = ref(false);
const loadingData = ref(true);

// Data from API
const animals = ref([]);
const veterinarians = ref([]);
const tiposVacunaApi = ref([]);

// Función para reinicializar componentes GOV.CO
function initGovCoComponents() {
  console.log('🔄 VaccinationForm: Inicializando componentes GOV.CO...');

  nextTick(() => {
    // Intentar con window.GOVCo
    if (window.GOVCo?.init) {
      const dropdowns = document.querySelectorAll('.vaccination-form .desplegable-govco');
      console.log(`📦 Encontrados ${dropdowns.length} dropdowns`);
      dropdowns.forEach((dd, index) => {
        try {
          window.GOVCo.init(dd.parentElement || dd);
          console.log(`✅ Dropdown ${index + 1} inicializado`);
        } catch (e) {
          console.warn(`⚠️ Error en dropdown ${index + 1}:`, e);
        }
      });
    }

    // Intentar con reinitGovCo global
    if (window.reinitGovCo) {
      setTimeout(() => {
        window.reinitGovCo();
        console.log('✅ reinitGovCo ejecutado');
      }, 100);
    }
  });
}

// Cargar datos iniciales
async function loadInitialData() {
  loadingData.value = true;
  console.log('🔄 VaccinationForm: Cargando datos iniciales...');

  try {
    // Cargar animales
    console.log('📦 Cargando animales...');
    let animalsData = [];

    try {
      await animalsStore.fetchAnimals({ per_page: 100 });
      animalsData = animalsStore.animals || [];
      console.log('✅ Animales desde store:', animalsData.length);
    } catch (storeError) {
      console.warn('⚠️ Error con store, intentando servicio directo:', storeError);
      const animalsResponse = await animalService.getAll();
      animalsData = animalsResponse?.data?.data || animalsResponse?.data || [];
      console.log('✅ Animales desde servicio:', animalsData.length);
    }

    animals.value = animalsData.map(animal => ({
      ...animal,
      historial_clinico_id: animal.historial_clinico?.id || animal.historial_clinico_id
    }));

    console.log('✅ Animales procesados:', animals.value.length);

    // Cargar tipos de vacuna
    console.log('📦 Cargando tipos de vacuna...');
    try {
      const tiposData = await veterinaryStore.fetchTiposVacuna();
      if (tiposData && tiposData.length > 0) {
        tiposVacunaApi.value = tiposData;
        console.log('✅ Tipos de vacuna cargados:', tiposData.length);
      } else {
        console.log('⚠️ Usando tipos de vacuna por defecto');
      }
    } catch (tiposError) {
      console.warn('⚠️ Error cargando tipos de vacuna:', tiposError);
    }

    // Cargar veterinarios
    console.log('📦 Cargando veterinarios...');
    try {
      const vetsData = await veterinaryStore.fetchVeterinarios();
      veterinarians.value = vetsData || veterinaryStore.veterinarios || [];
      console.log('✅ Veterinarios cargados:', veterinarians.value.length, veterinarians.value);
    } catch (vetsError) {
      console.error('❌ Error cargando veterinarios:', vetsError);
    }

    // Reinicializar GOV.CO después de cargar datos
    await nextTick();
    setTimeout(() => {
      initGovCoComponents();
    }, 200);

  } catch (error) {
    console.error('❌ Error cargando datos iniciales:', error);
    alert('Error al cargar datos. Por favor recargue la página.');
  } finally {
    loadingData.value = false;
    console.log('✅ VaccinationForm: Carga de datos completada');
  }
}

// Observar cuando se cargan los datos para reinicializar GOV.CO
watch(() => veterinarians.value.length, async (newLength) => {
  if (newLength > 0) {
    console.log('📦 Veterinarios actualizados, reinicializando GOV.CO...');
    await nextTick();
    setTimeout(() => {
      initGovCoComponents();
    }, 100);
  }
});

const animalOptions = computed(() =>
  animals.value.map(animal => ({
    value: animal.id,
    text: `${animal.nombre} - ${animal.numero_chip || 'Sin chip'}`,
    historialClinicoId: animal.historial_clinico?.id || animal.historial_clinico_id
  }))
);

// Tipos de vacuna desde API (fallback a lista fija si la API no devuelve datos)
const vaccineTypeOptions = computed(() => {
  if (tiposVacunaApi.value.length > 0) {
    return tiposVacunaApi.value.map(tipo => ({
      value: tipo.id,
      text: tipo.nombre,
      codigo: tipo.codigo,
      intervalo: tipo.intervalo_dosis
    }));
  }
  // Fallback si no hay tipos desde API
  return [
    { value: 'rabia', text: 'Rabia' },
    { value: 'quintuple', text: 'Quíntuple (DHPPL)' },
    { value: 'sextuple', text: 'Séxtuple (DHPPL + Corona)' },
    { value: 'triple_felina', text: 'Triple Felina' },
    { value: 'leucemia_felina', text: 'Leucemia Felina' },
    { value: 'parvovirus', text: 'Parvovirus' },
    { value: 'bordetella', text: 'Bordetella (Tos de las perreras)' },
    { value: 'otra', text: 'Otra' }
  ];
});

const routeOptions = [
  { value: 'subcutanea', text: 'Subcutánea' },
  { value: 'intramuscular', text: 'Intramuscular' },
  { value: 'intranasal', text: 'Intranasal' }
];

const doseNumberOptions = [
  { value: '1', text: 'Primera dosis' },
  { value: '2', text: 'Segunda dosis' },
  { value: '3', text: 'Tercera dosis' },
  { value: 'refuerzo', text: 'Refuerzo anual' }
];

const veterinarianOptions = computed(() =>
  veterinarians.value.map(vet => ({
    value: vet.id,
    text: `${vet.usuario?.nombres || vet.nombre_completo || vet.nombres || 'Dr.'} ${vet.usuario?.apellidos || vet.apellidos || ''} - TP ${vet.tarjeta_profesional || vet.numero_tarjeta_profesional || 'N/A'}`
  }))
);

const vaccinationSchemes = {
  rabia: { interval: 365, nextDoseText: 'Refuerzo anual' },
  quintuple: { interval: 21, nextDoseText: 'Cada 21 días hasta completar esquema' },
  sextuple: { interval: 21, nextDoseText: 'Cada 21 días hasta completar esquema' },
  triple_felina: { interval: 21, nextDoseText: 'Cada 21 días hasta completar esquema' },
  leucemia_felina: { interval: 21, nextDoseText: 'Segunda dosis a los 21 días' },
  parvovirus: { interval: 21, nextDoseText: 'Cada 21 días hasta completar esquema' },
  bordetella: { interval: 365, nextDoseText: 'Refuerzo anual' }
};

const form = reactive({
  animalId: '',
  applicationDate: '',
  vaccineType: '',
  vaccineName: '',
  laboratory: '',
  batchNumber: '',
  expirationDate: '',
  dose: 1.0,
  route: '',
  site: '',
  doseNumber: '',
  requiresNextDose: false,
  nextDoseDate: '',
  veterinarianId: '',
  observations: '',
  generateCertificate: true
});

const errors = reactive({
  animalId: '',
  applicationDate: '',
  vaccineType: '',
  vaccineName: '',
  laboratory: '',
  batchNumber: '',
  expirationDate: '',
  dose: '',
  route: '',
  doseNumber: '',
  nextDoseDate: '',
  veterinarianId: ''
});

const nextDoseInfo = computed(() => {
  if (!form.vaccineType || !vaccinationSchemes[form.vaccineType]) {
    return 'Seleccione el tipo de vacuna';
  }
  return `ℹ️ ${vaccinationSchemes[form.vaccineType].nextDoseText}`;
});

function onDateChange(value) {
  form.applicationDate = value;
  calculateNextDose();
}

function onVaccineTypeChange(value) {
  form.vaccineType = value;
  if (form.doseNumber !== 'refuerzo') {
    form.requiresNextDose = true;
    calculateNextDose();
  }
}

function calculateNextDose() {
  if (!form.applicationDate || !form.vaccineType || !form.requiresNextDose) {
    return;
  }

  const scheme = vaccinationSchemes[form.vaccineType];
  if (!scheme) return;

  const dateParts = form.applicationDate.split('/');
  if (dateParts.length === 3) {
    const appDate = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);
    const nextDate = new Date(appDate);
    nextDate.setDate(nextDate.getDate() + scheme.interval);
    
    const day = String(nextDate.getDate()).padStart(2, '0');
    const month = String(nextDate.getMonth() + 1).padStart(2, '0');
    const year = nextDate.getFullYear();
    form.nextDoseDate = `${day}/${month}/${year}`;
  }
}

function syncAllValues() {
  const refs = [
    { ref: animalDropdownRef, field: 'animalId', selector: '#animal-select' },
    { ref: vaccineTypeRef, field: 'vaccineType', selector: '#vaccineType-select' },
    { ref: routeRef, field: 'route', selector: '#route-select' },
    { ref: doseNumberRef, field: 'doseNumber', selector: '#doseNumber-select' },
    { ref: veterinarianRef, field: 'veterinarianId', selector: '#veterinarian-select' },
    { ref: applicationDateRef, field: 'applicationDate', selector: '#applicationDate' },
    { ref: expirationDateRef, field: 'expirationDate', selector: '#expirationDate' },
    { ref: nextDoseDateRef, field: 'nextDoseDate', selector: '#nextDoseDate' }
  ];

  refs.forEach(({ ref, field, selector }) => {
    if (ref.value?.getValue) {
      const value = ref.value.getValue();
      if (value) form[field] = value;
    }
    
    if (!form[field]) {
      const element = document.querySelector(selector);
      if (element?.value) {
        form[field] = isNaN(element.value) ? element.value : parseInt(element.value);
      }
    }
  });
}

function validate() {
  syncAllValues();
  Object.keys(errors).forEach(k => errors[k] = '');
  
  let isValid = true;
  const requiredFields = {
    animalId: 'Debe seleccionar un animal',
    applicationDate: 'Campo requerido',
    vaccineType: 'Campo requerido',
    vaccineName: 'Campo requerido',
    laboratory: 'Campo requerido',
    batchNumber: 'Campo requerido',
    expirationDate: 'Campo requerido',
    route: 'Campo requerido',
    doseNumber: 'Campo requerido',
    veterinarianId: 'Debe seleccionar veterinario responsable'
  };

  for (const [field, message] of Object.entries(requiredFields)) {
    if (!form[field] || (typeof form[field] === 'string' && !form[field].trim())) {
      errors[field] = message;
      isValid = false;
    }
  }

  if (!form.dose || form.dose <= 0) {
    errors.dose = 'Debe especificar una dosis válida';
    isValid = false;
  }

  if (form.requiresNextDose && !form.nextDoseDate) {
    errors.nextDoseDate = 'Debe especificar fecha de próxima dosis';
    isValid = false;
  }

  return isValid;
}

function resetForm() {
  Object.keys(form).forEach(k => {
    if (typeof form[k] === 'boolean') {
      form[k] = k === 'generateCertificate';
    } else if (typeof form[k] === 'number') {
      form[k] = 1.0;
    } else {
      form[k] = '';
    }
  });
  Object.keys(errors).forEach(k => errors[k] = '');
}

async function onSubmit() {
  if (!validate()) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  if (isSubmitting.value) return;
  isSubmitting.value = true;

  try {
    // Obtener el historial clinico del animal seleccionado
    const selectedAnimal = animalOptions.value.find(a => a.value === form.animalId);

    // Convertir fecha DD/MM/YYYY a YYYY-MM-DD para el backend
    const convertirFecha = (fechaStr) => {
      if (!fechaStr) return null;
      const parts = fechaStr.split('/');
      if (parts.length === 3) {
        return `${parts[2]}-${parts[1]}-${parts[0]}`;
      }
      return fechaStr;
    };

    // Preparar datos para el backend
    const vacunaData = {
      historial_clinico_id: selectedAnimal?.historialClinicoId,
      tipo_vacuna_id: form.vaccineType,
      veterinario_id: form.veterinarianId,
      fecha_aplicacion: convertirFecha(form.applicationDate),
      fecha_proxima: form.requiresNextDose ? convertirFecha(form.nextDoseDate) : null,
      lote: form.batchNumber,
      fabricante: form.laboratory,
      observaciones: form.observations || null
    };

    console.log('Enviando vacunación:', vacunaData);

    await veterinaryStore.crearVacuna(vacunaData);

    if (window.$toast) {
      window.$toast.success('Éxito', 'Vacunación registrada exitosamente');
    } else {
      alert('Vacunación registrada exitosamente');
    }

    resetForm();
  } catch (error) {
    console.error('Error al registrar vacunación:', error);
    const errorMsg = error.response?.data?.message || 'Error al registrar la vacunación';
    if (window.$toast) {
      window.$toast.error('Error', errorMsg);
    } else {
      alert(errorMsg);
    }
  } finally {
    isSubmitting.value = false;
  }
}

function fixButtonTypes() {
  if (!formEl.value) return;

  const buttons = formEl.value.querySelectorAll('button');
  buttons.forEach((btn) => {
    const isSubmitButton = btn.textContent?.includes('Registrar vacunación');
    btn.setAttribute('type', isSubmitButton ? 'submit' : 'button');
  });
  
  // Prevenir envío del formulario desde botones internos de los componentes
  if (formEl.value && !formEl.value.dataset.listenerAdded) {
    formEl.value.addEventListener('submit', (e) => {
      const submitter = e.submitter;
      if (!submitter || !submitter.textContent?.includes('Registrar vacunación')) {
        e.preventDefault();
        e.stopPropagation();
        return false;
      }
    }, true);
    
    formEl.value.dataset.listenerAdded = 'true';
  }
}

function preventScrollOnInteractions() {
  const handleDropdownOpen = (e) => {
    const element = e.target.closest('.desplegable-govco, [data-type="calendar"]');
    if (element) {
      const scrollPos = window.scrollY || document.documentElement.scrollTop;
      setTimeout(() => {
        window.scrollTo(0, scrollPos);
      }, 50);
    }
  };

  const handleCalendarFocus = (e) => {
    if (e.target.closest('[data-type="calendar"] input')) {
      const scrollPos = window.scrollY || document.documentElement.scrollTop;
      e.preventDefault();
      setTimeout(() => {
        window.scrollTo(0, scrollPos);
      }, 10);
    }
  };

  if (formEl.value) {
    formEl.value.removeEventListener('click', handleDropdownOpen);
    formEl.value.addEventListener('click', handleDropdownOpen);
    
    formEl.value.removeEventListener('focus', handleCalendarFocus, true);
    formEl.value.addEventListener('focus', handleCalendarFocus, true);
  }
}

onMounted(async () => {
  console.log('📍 VaccinationForm mounted');

  fixButtonTypes();
  preventScrollOnInteractions();

  if (typeof window !== 'undefined') {
    window.addEventListener('load', () => {
      fixButtonTypes();
      preventScrollOnInteractions();
    });
  }

  // Cargar datos iniciales
  await loadInitialData();
});
</script>

<style scoped>
.vaccination-form { 
  max-width: 1200px; 
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
  gap: 1.5rem 2rem; 
  padding: 1.5rem; 
}

.full-width { 
  grid-column: 1 / 3; 
}

.input-wrapper,
.input-like-govco {
  display: flex;
  flex-direction: column;
  width: 100%;
}

.input-wrapper label,
.input-like-govco label {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
  font-size: 0.95rem;
}

.required {
  color: #d32f2f;
  margin-left: 0.25rem;
}

.input-govco {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  box-sizing: border-box;
  font-family: inherit;
}

input.input-govco {
  height: 44px;
}

textarea.input-govco {
  resize: vertical;
  min-height: 80px;
}

.input-govco:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.1);
}

.calendar-wrapper {
  margin: 0;
}

.calendar-wrapper :deep(.label-desplegable-govco) {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
  font-size: 0.95rem;
}

.calendar-wrapper :deep(.desplegable-govco) {
  width: 100%;
}

.calendar-wrapper :deep(.desplegable-govco input) {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  height: 44px;
  box-sizing: border-box;
}

.error-text { 
  display: block; 
  color: #b00020; 
  font-size: 0.85rem; 
  margin-top: 0.5rem; 
}

.info-text { 
  display: block; 
  color: #666; 
  font-size: 0.85rem; 
  margin-top: 0.5rem; 
}

.checkbox-wrapper { 
  display: flex; 
  align-items: center; 
  gap: 0.75rem; 
  padding: 1rem; 
  background: #f5f7fb; 
  border-radius: 6px; 
}

.checkbox-wrapper input[type="checkbox"] {
  width: 20px;
  height: 20px;
  cursor: pointer;
}

.checkbox-wrapper label {
  margin: 0;
  cursor: pointer;
  user-select: none;
}

.form-actions { 
  display: flex; 
  justify-content: flex-end; 
  gap: 1rem; 
  padding: 1.5rem; 
  background: white; 
  border-radius: 8px; 
  box-shadow: 0 2px 8px rgba(0,0,0,0.08); 
}

.btn-primary,
.btn-secondary { 
  padding: 0.75rem 2rem; 
  border-radius: 6px; 
  font-weight: 600; 
  cursor: pointer; 
  border: none; 
  color: white;
  transition: all 0.3s;
  font-size: 1rem;
}

.btn-primary {
  background-color: #069169;
}

.btn-secondary {
  background-color: #737373;
}

.btn-primary:hover,
.btn-secondary:hover {
  transform: translateY(-2px);
  opacity: 0.9;
}

.loading-overlay {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  min-height: 300px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3366cc;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

:deep(.desplegable-govco .desplegable-items) {
  z-index: 1500 !important;
}

:deep(.desplegable-govco.desplegable-calendar-govco .desplegable-calendar-control) {
  z-index: 1500 !important;
  width: 100% !important;
  max-width: 100% !important;
  max-height: 668.8px !important;
  overflow-y: auto !important;
  box-sizing: border-box !important;
  padding: 0 !important;
}

:deep(.desplegable-calendar-govco .desplegable-calendar-control .header) { 
  width: 100% !important; 
  box-sizing: border-box !important;
}

:deep(.desplegable-calendar-govco .desplegable-calendar-control table#miCalendarioGrid.dates) {
  width: 100% !important;
  table-layout: fixed !important;
  box-sizing: border-box !important;
  padding: 0 !important;
  margin: 0 !important;  
  margin-left: -4.8px !important;
}

:deep(.desplegable-calendar-govco .desplegable-calendar-control table td) { 
  box-sizing: border-box !important; 
  width: calc(100% / 7) !important;
}

@media (max-width: 768px) {
  .form-grid { 
    grid-template-columns: 1fr; 
  }
  
  .full-width { 
    grid-column: 1 / 2; 
  }
}
</style>