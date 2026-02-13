<template>
  <section class="medical-form">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Consulta Veterinaria</h2>
      <p class="text2-tipografia-govco">Registro de atención médica</p>
    </div>

    <!-- Indicador de carga -->
    <div v-if="loadingData" class="loading-overlay">
      <div class="spinner"></div>
      <p>Cargando datos...</p>
    </div>

    <form v-else ref="formEl" @submit.prevent="onSubmit" novalidate>
      
      <!-- SECCIÓN 1: DATOS DE LA CONSULTA -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Información general</h3>
        
        <div class="form-grid">
          <!-- Animal -->
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
              @change="onAnimalChange"
            />
          </div>

          <!-- Fecha y hora -->
          <div class="input-like-govco calendar-wrapper">
            <CalendarioGovco
              ref="consultDateCalendarRef"
              id="consultDateCalendar"
              input-id="consultDate"
              label="Fecha y hora"
              v-model="form.consultDate"
              view-days="true"
              :required="true"
              width="100%"
              height="44px"
              :alert-text="errors.consultDate"
              :error="!!errors.consultDate"
              @change="onConsultDateChange"
            />
          </div>

          <!-- Tipo de consulta -->
          <div class="input-like-govco">
            <DesplegableGovco
              ref="consultTypeDropdownRef"
              id="consultType"
              label="Tipo de consulta"
              :options="consultTypeOptions"
              v-model="form.consultType"
              placeholder="Escoger"
              :required="true"
              :alert-text="errors.consultType"
              :error="!!errors.consultType"
              width="100%"
              height="44px"
              @change="onConsultTypeChange"
            />
          </div>

          <!-- Motivo de consulta -->
          <div class="entradas-de-texto-govco full-width">
            <label for="reason">Motivo de consulta<span aria-required="true">*</span></label>
            <textarea
              id="reason"
              v-model="form.reason"
              rows="3"
              placeholder="Describa el motivo de la consulta..."
            ></textarea>
            <span v-if="errors.reason" class="error-text">{{ errors.reason }}</span>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 2: SIGNOS VITALES -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Signos vitales</h3>
        <VitalSignsInput v-model="form.vitalSigns" :errors="errors.vitalSigns" />
      </div>

      <!-- SECCIÓN 3: EXAMEN FÍSICO Y DIAGNÓSTICO -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Evaluación clínica</h3>
        
        <div class="form-grid">
          <!-- Examen físico -->
          <div class="entradas-de-texto-govco full-width">
            <label for="physicalExam">Examen físico<span aria-required="true">*</span></label>
            <textarea
              id="physicalExam"
              v-model="form.physicalExam"
              rows="4"
              placeholder="Hallazgos del examen físico general..."
            ></textarea>
            <span v-if="errors.physicalExam" class="error-text">{{ errors.physicalExam }}</span>
          </div>

          <!-- Diagnóstico -->
          <div class="entradas-de-texto-govco full-width">
            <label for="diagnosis">Diagnóstico<span aria-required="true">*</span></label>
            <textarea
              id="diagnosis"
              v-model="form.diagnosis"
              rows="3"
              placeholder="Diagnóstico clínico..."
            ></textarea>
            <span v-if="errors.diagnosis" class="error-text">{{ errors.diagnosis }}</span>
          </div>

          <!-- Pronóstico -->
          <div class="input-like-govco">
            <DesplegableGovco
              ref="prognosisDropdownRef"
              id="prognosis"
              label="Pronóstico"
              :options="prognosisOptions"
              v-model="form.prognosis"
              placeholder="No especificado"
              width="100%"
              height="44px"
              @change="onPrognosisChange"
            />
          </div>
        </div>
      </div>

      <!-- SECCIÓN 4: TRATAMIENTO -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Plan de tratamiento</h3>
        
        <div class="form-grid">
          <!-- Prescripción de medicamentos -->
          <div class="full-width">
            <MedicationPrescription 
              v-model="form.medications" 
              :inventory="medicationInventory"
              @update:stock="updateInventoryStock"
            />
            <span v-if="errors.medications" class="error-text">{{ errors.medications }}</span>
          </div>

          <!-- Indicaciones generales -->
          <div class="entradas-de-texto-govco full-width">
            <label for="treatment">Indicaciones y recomendaciones<span aria-required="true">*</span></label>
            <textarea
              id="treatment"
              v-model="form.treatment"
              rows="4"
              placeholder="Cuidados en casa, alimentación, restricciones..."
            ></textarea>
            <span v-if="errors.treatment" class="error-text">{{ errors.treatment }}</span>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 5: RESULTADOS DE LABORATORIO -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Resultados de laboratorio</h3>
        
        <div class="form-grid">
          <div class="full-width">
            <FileUploader
              v-model="form.labResults"
              accept=".pdf"
              :max-files="5"
              label="Adjuntar resultados (PDF)"
              help-text="Máximo 5 archivos PDF, 5MB cada uno"
            />
          </div>
        </div>
      </div>

      <!-- SECCIÓN 6: SEGUIMIENTO -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Seguimiento y control</h3>
        
        <div class="form-grid">
          <!-- ¿Requiere control? -->
          <div class="checkbox-govco full-width">
            <input 
              type="checkbox" 
              id="requiresFollowup" 
              v-model="form.requiresFollowup"
            />
            <label for="requiresFollowup">
              Programar consulta de control
            </label>
          </div>

          <!-- Fecha de próximo control -->
          <div v-if="form.requiresFollowup" class="input-like-govco calendar-wrapper">
            <CalendarioGovco
              ref="followupDateCalendarRef"
              id="followupDateCalendar"
              input-id="followupDate"
              label="Fecha de próximo control"
              v-model="form.followupDate"
              view-days="true"
              :required="true"
              width="100%"
              height="44px"
              :alert-text="errors.followupDate"
              :error="!!errors.followupDate"
              @change="onFollowupDateChange"
            />
          </div>

          <!-- Notas de seguimiento -->
          <div v-if="form.requiresFollowup" class="entradas-de-texto-govco full-width">
            <label for="followupNotes">Notas para el control</label>
            <textarea
              id="followupNotes"
              v-model="form.followupNotes"
              rows="2"
              placeholder="Aspectos a evaluar en el próximo control..."
            ></textarea>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 7: VETERINARIO -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Firma digital</h3>
        
        <div class="form-grid">
          <div class="input-like-govco">
            <DesplegableGovco
              ref="veterinarianDropdownRef"
              id="veterinarian"
              label="Veterinario responsable"
              :options="veterinarianOptions"
              v-model="form.veterinarianId"
              placeholder="Seleccionar veterinario"
              :required="true"
              :alert-text="errors.veterinarianId"
              :error="!!errors.veterinarianId"
              width="100%"
              height="44px"
              @change="onVeterinarianChange"
            />
          </div>

          <!-- Observaciones del veterinario -->
          <div class="entradas-de-texto-govco full-width">
            <label for="vetNotes">Observaciones adicionales</label>
            <textarea
              id="vetNotes"
              v-model="form.veterinarianNotes"
              rows="2"
              placeholder="Comentarios adicionales del veterinario..."
            ></textarea>
          </div>
        </div>
      </div>

      <!-- BOTONES -->
      <div class="form-actions">
        <button type="button" @click="resetForm" class="govco-btn govco-bg-concrete">
          Cancelar
        </button>
        <button type="submit" class="govco-btn govco-bg-elf-green">
          Guardar consulta
        </button>
      </div>
    </form>

  </section>
</template>

<script setup>
import { reactive, ref, computed, onMounted, nextTick, watch } from 'vue';
import VitalSignsInput from './VitalSignsInput.vue';
import MedicationPrescription from './MedicationPrescription.vue';
import FileUploader from '../common/FileUploader.vue';
import DesplegableGovco from '../common/DesplegableGovco.vue';
import CalendarioGovco from '../common/CalendarioGovco.vue';
import { useVeterinaryStore } from '@/stores/veterinary';
import { useAnimalsStore } from '@/stores/animals';
import animalService from '@/services/animalService';

const emit = defineEmits(['consulta-saved', 'cancel']);
const props = defineProps({
  animalId: {
    type: String,
    default: null
  },
  historialClinicoId: {
    type: String,
    default: null
  }
});

const veterinaryStore = useVeterinaryStore();
const animalsStore = useAnimalsStore();

const formEl = ref(null);
const isSubmitting = ref(false);

// Datos reactivos desde APIs
const animals = ref([]);
const veterinarians = ref([]);
const medicationInventory = ref([]);
const loadingData = ref(true);

// Función para reinicializar componentes GOV.CO
function initGovCoComponents() {
  console.log('🔄 MedicalRecordForm: Inicializando componentes GOV.CO...');

  nextTick(() => {
    if (window.GOVCo?.init) {
      const dropdowns = document.querySelectorAll('.medical-form .desplegable-govco');
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

    if (window.reinitGovCo) {
      setTimeout(() => {
        window.reinitGovCo();
        console.log('✅ reinitGovCo ejecutado');
      }, 100);
    }
  });
}

// Cargar datos al montar
async function loadInitialData() {
  loadingData.value = true;
  console.log('🔄 MedicalRecordForm: Cargando datos iniciales...');

  try {
    // Cargar animales
    console.log('📦 Cargando animales...');
    let animalsData = [];

    try {
      animalsStore.clearFilters(); 
      await animalsStore.fetchAnimals({ per_page: 100 });
      animalsData = animalsStore.animals || [];
      console.log('✅ Animales desde store:', animalsData.length);
    } catch (storeError) {
      console.warn('⚠️ Error con store, intentando servicio directo:', storeError);
      const animalsResponse = await animalService.getAll();
      animalsData = animalsResponse?.data?.data || animalsResponse?.data || [];
      console.log('✅ Animales desde servicio:', animalsData.length);
    }

    animals.value = animalsData;
    console.log('✅ Animales procesados:', animals.value.length);

    // Cargar veterinarios
    // Cargar veterinarios
console.log(' Cargando veterinarios...');
try {
  const vetsData = await veterinaryStore.fetchVeterinarios();

  veterinarians.value = Array.isArray(vetsData) ? vetsData : [];

  if (veterinarians.value.length === 0) {
    console.warn('No hay veterinarios registrados en BD');
    alert('No hay veterinarios registrados. Debes crear al menos uno para registrar consultas.');
  }

} catch (vetsError) {
  console.warn(' Error cargando veterinarios', vetsError);
}

    // Cargar medicamentos del inventario
    console.log(' Cargando medicamentos...');
    try {
      await veterinaryStore.fetchMedicamentos();
      medicationInventory.value = (veterinaryStore.medicamentos || []).map(m => ({
        id: m.id,
        name: m.nombre || m.name,
        stock: m.cantidad_actual || m.stock_actual || m.stock || 0, // Fix: leer cantidad_actual primero
        unit: m.unidad_medida || 'unidades'
      }));
      console.log('✅ Medicamentos cargados:', medicationInventory.value.length);
      if (medicationInventory.value.length > 0) {
        console.log('📦 Ejemplo de medicamento:', medicationInventory.value[0]); // Debug para verificar estructura
      }
    } catch (medsError) {
      console.warn('⚠️ Error cargando medicamentos:', medsError);
    }

    // Si se pasó un animalId, pre-seleccionar
    if (props.animalId) {
      form.animalId = props.animalId;
      console.log('🎯 Animal pre-seleccionado:', props.animalId);
    }

    if (props.historialClinicoId) {
      form.historialClinicoId = props.historialClinicoId;
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
    console.log('✅ MedicalRecordForm: Carga de datos completada');
  }
}

// Observar cuando se cargan los datos para reinicializar GOV.CO
watch(() => animals.value.length, async (newLength) => {
  if (newLength > 0) {
    console.log('📦 Animales actualizados, reinicializando GOV.CO...');
    await nextTick();
    setTimeout(() => {
      initGovCoComponents();
    }, 100);
  }
});

// Opciones computadas para los dropdowns
const animalOptions = computed(() =>
  animals.value.map(animal => ({
    value: animal.id,
    text: `${animal.nombre || animal.name} - ${animal.codigo_unico || animal.microchip || 'Sin chip'}`
  }))
);

// Valores aceptados por el backend: general, emergencia, seguimiento, especializada
const consultTypeOptions = [
  { value: 'general', text: 'Consulta general' },
  { value: 'emergencia', text: 'Emergencia' },
  { value: 'seguimiento', text: 'Seguimiento / Control' },
  { value: 'especializada', text: 'Especializada' }
];

const prognosisOptions = [
  { value: '', text: 'No especificado' },
  { value: 'excelente', text: 'Excelente' },
  { value: 'bueno', text: 'Bueno' },
  { value: 'reservado', text: 'Reservado' },
  { value: 'grave', text: 'Grave' }
];

const veterinarianOptions = computed(() =>
  veterinarians.value.map(vet => ({
    value: vet.id,
    text: `${vet.nombre_completo ?? `${vet.nombres} ${vet.apellidos}`} - Tarjeta Prof. ${vet.numero_tarjeta_profesional ?? 'N/A'}`
  }))
);



const form = reactive({
  animalId: '',
  historialClinicoId: '',
  consultDate: '',
  consultType: '',
  reason: '',
  vitalSigns: {
    temperature: '',
    heartRate: '',
    respiratoryRate: '',
    weight: '',
    bodyCondition: '',
    mucosa: '',
    hydration: '',
    tllc: ''
  },
  physicalExam: '',
  diagnosis: '',
  prognosis: '',
  medications: [],
  treatment: '',
  labResults: [],
  requiresFollowup: false,
  followupDate: '',
  followupNotes: '',
  veterinarianId: '',
  veterinarianNotes: ''
});

const errors = reactive({
  animalId: '',
  consultDate: '',
  consultType: '',
  reason: '',
  vitalSigns: {},
  physicalExam: '',
  diagnosis: '',
  treatment: '',
  medications: '',
  followupDate: '',
  veterinarianId: ''
});

// Handlers para los eventos de cambio de los dropdowns
async function onAnimalChange(value) {
  console.log('🔍 Animal seleccionado:', value);
  form.animalId = value;

  if (value) {
    // Buscar el animal en la lista
    const selectedAnimal = animals.value.find(a => a.id === value);
    console.log('🐾 Datos del animal:', selectedAnimal);

    if (selectedAnimal) {
      // Si el animal tiene historial_clinico_id en sus datos, usarlo
      if (selectedAnimal.historial_clinico_id) {
        form.historialClinicoId = selectedAnimal.historial_clinico_id;
        console.log('✅ historial_clinico_id encontrado en animal:', form.historialClinicoId);
      } else if (selectedAnimal.historial_clinico?.id) {
        form.historialClinicoId = selectedAnimal.historial_clinico.id;
        console.log('✅ historial_clinico_id encontrado en relación:', form.historialClinicoId);
      } else {
        // Si no tiene, intentar obtenerlo del backend
        try {
          console.log('📡 Obteniendo historial clínico del backend...');
          const historial = await veterinaryStore.fetchHistorialClinico(value);

          if (historial && historial.id) {
            form.historialClinicoId = historial.id;
            console.log('✅ historial_clinico_id obtenido del backend:', historial.id);
          } else {
            console.warn('⚠️ Animal no tiene historial clínico');
            form.historialClinicoId = '';
            alert('Este animal no tiene historial clínico. Por favor, cree uno primero.');
          }
        } catch (error) {
          console.error('❌ Error al obtener historial:', error);
          form.historialClinicoId = '';
          alert('Error al obtener el historial clínico del animal.');
        }
      }
    }
  } else {
    form.historialClinicoId = '';
  }
}

function onConsultDateChange(value) {
  console.log('Fecha de consulta:', value);
  form.consultDate = value;
}

function onConsultTypeChange(value) {
  console.log('Tipo de consulta:', value);
  form.consultType = value;
}

function onPrognosisChange(value) {
  console.log('Pronóstico:', value);
  form.prognosis = value;
}

function onFollowupDateChange(value) {
  console.log('Fecha de seguimiento:', value);
  form.followupDate = value;
}

function onVeterinarianChange(value) {
  console.log('Veterinario:', value);
  form.veterinarianId = value;
}

// Referencias a los componentes
const animalDropdownRef = ref(null);
const consultTypeDropdownRef = ref(null);
const prognosisDropdownRef = ref(null);
const veterinarianDropdownRef = ref(null);
const consultDateCalendarRef = ref(null);
const followupDateCalendarRef = ref(null);

// Función para sincronizar valores desde los selects nativos
function syncAllDropdownValues() {
  console.log('=== INICIANDO SINCRONIZACIÓN ===');
  
  // Método 1: Intentar obtener valores de las refs de los componentes
  if (animalDropdownRef.value?.getValue) {
    const value = animalDropdownRef.value.getValue();
    if (value) {
      form.animalId = value;
      console.log('Animal desde ref:', value);
    }
  }
  
  if (consultTypeDropdownRef.value?.getValue) {
    const value = consultTypeDropdownRef.value.getValue();
    if (value) {
      form.consultType = value;
      console.log('ConsultType desde ref:', value);
    }
  }
  
  if (prognosisDropdownRef.value?.getValue) {
    const value = prognosisDropdownRef.value.getValue();
    if (value) {
      form.prognosis = value;
      console.log('Prognosis desde ref:', value);
    }
  }
  
  if (veterinarianDropdownRef.value?.getValue) {
    const value = veterinarianDropdownRef.value.getValue();
    if (value) {
      form.veterinarianId = value;
      console.log('Veterinarian desde ref:', value);
    }
  }
  
  // Sincronizar calendarios
  if (consultDateCalendarRef.value?.getValue) {
    const value = consultDateCalendarRef.value.getValue();
    if (value) {
      form.consultDate = value;
      console.log('ConsultDate desde ref:', value);
    }
  }
  
  if (followupDateCalendarRef.value?.getValue) {
    const value = followupDateCalendarRef.value.getValue();
    if (value) {
      form.followupDate = value;
      console.log('FollowupDate desde ref:', value);
    }
  }
  
  // Método 2: Leer directamente desde el DOM como fallback
  const animalSelect = document.querySelector('#animal-select');
  const consultTypeSelect = document.querySelector('#consultType-select');
  const prognosisSelect = document.querySelector('#prognosis-select');
  const veterinarianSelect = document.querySelector('#veterinarian-select');
  const consultDateInput = document.getElementById('consultDate');
  const followupDateInput = document.getElementById('followupDate');
  
  if (animalSelect?.value && !form.animalId) {
    form.animalId = parseInt(animalSelect.value) || animalSelect.value;
    console.log('Animal desde DOM:', animalSelect.value);
  }
  
  if (consultTypeSelect?.value && !form.consultType) {
    form.consultType = consultTypeSelect.value;
    console.log('ConsultType desde DOM:', consultTypeSelect.value);
  }
  
  if (prognosisSelect?.value && !form.prognosis) {
    form.prognosis = prognosisSelect.value;
    console.log('Prognosis desde DOM:', prognosisSelect.value);
  }
  
  if (veterinarianSelect?.value && !form.veterinarianId) {
    form.veterinarianId = parseInt(veterinarianSelect.value) || veterinarianSelect.value;
    console.log('Veterinarian desde DOM:', veterinarianSelect.value);
  }
  
  if (consultDateInput?.value && !form.consultDate) {
    form.consultDate = consultDateInput.value;
    console.log('ConsultDate desde DOM:', consultDateInput.value);
  }
  
  if (followupDateInput?.value && !form.followupDate) {
    form.followupDate = followupDateInput.value;
    console.log('FollowupDate desde DOM:', followupDateInput.value);
  }
  
  console.log('Valores finales sincronizados:', {
    animalId: form.animalId,
    consultDate: form.consultDate,
    consultType: form.consultType,
    prognosis: form.prognosis,
    veterinarianId: form.veterinarianId,
    followupDate: form.followupDate
  });
  console.log('=== FIN SINCRONIZACIÓN ===');
}

// Función para asegurar que los botones tengan el type correcto
function fixNonSubmitButtons() {
  if (!formEl.value) return;

  const buttons = formEl.value.querySelectorAll('button');

  buttons.forEach((btn) => {
    const isSubmitButton = btn.textContent?.includes('Guardar consulta');
    
    if (isSubmitButton) {
      btn.setAttribute('type', 'submit');
    } else {
      btn.setAttribute('type', 'button');
    }
  });
  
  if (formEl.value && !formEl.value.dataset.listenerAdded) {
    formEl.value.addEventListener('submit', (e) => {
      const submitter = e.submitter;
      if (!submitter || !submitter.textContent?.includes('Guardar consulta')) {
        e.preventDefault();
        e.stopPropagation();
        return false;
      }
    }, true);
    
    formEl.value.dataset.listenerAdded = 'true';
  }
}

// Función para prevenir scroll automático de GOV.CO
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

function validate() {
  // IMPORTANTE: Sincronizar valores antes de validar
  syncAllDropdownValues();
  
  Object.keys(errors).forEach(k => {
    if (typeof errors[k] === 'object') {
      Object.keys(errors[k]).forEach(subKey => errors[k][subKey] = '');
    } else {
      errors[k] = '';
    }
  });
  
  let isValid = true;
  
  if (!form.animalId) {
    errors.animalId = 'Debe seleccionar un animal';
    isValid = false;
  }

  // Verificar que el animal tenga historial clínico
  if (!form.historialClinicoId) {
    errors.animalId = 'El animal seleccionado no tiene historial clínico';
    isValid = false;
  }

  if (!form.consultDate) {
    errors.consultDate = 'Campo requerido';
    isValid = false;
  }
  
  if (!form.consultType) {
    errors.consultType = 'Campo requerido';
    isValid = false;
  }
  
  if (!form.reason?.trim()) {
    errors.reason = 'Campo requerido';
    isValid = false;
  }
  
  if (!form.physicalExam?.trim()) {
    errors.physicalExam = 'Campo requerido';
    isValid = false;
  }
  
  if (!form.diagnosis?.trim()) {
    errors.diagnosis = 'Campo requerido';
    isValid = false;
  }
  
  if (!form.treatment?.trim()) {
    errors.treatment = 'Campo requerido';
    isValid = false;
  }
  
  if (form.requiresFollowup && !form.followupDate) {
    errors.followupDate = 'Debe especificar fecha de control';
    isValid = false;
  }
  
  // Veterinario es opcional si no hay veterinarios reales en BD
if (!form.veterinarianId) {
  errors.veterinarianId = 'Debe seleccionar un veterinario';
  isValid = false;
} 
  console.log('Validación completada:', { isValid, form, errors });
  
  return isValid;
}

function updateInventoryStock(medicationId, quantity) {
  const medication = medicationInventory.value.find(m => m.id === medicationId);
  if (medication) {
    medication.stock -= quantity;
    console.log(`Stock actualizado: ${medication.name} - Quedan ${medication.stock}`);
  }
}

function resetForm() {
  Object.keys(form).forEach(k => {
    if (Array.isArray(form[k])) {
      form[k] = [];
    } else if (typeof form[k] === 'object' && form[k] !== null) {
      Object.keys(form[k]).forEach(subKey => form[k][subKey] = '');
    } else if (typeof form[k] === 'boolean') {
      form[k] = false;
    } else {
      form[k] = '';
    }
  });
  
  Object.keys(errors).forEach(k => {
    if (typeof errors[k] === 'object') {
      Object.keys(errors[k]).forEach(subKey => errors[k][subKey] = '');
    } else {
      errors[k] = '';
    }
  });
}

/**
 * Formatear fecha para backend (YYYY-MM-DD)
 */
function formatDateForBackend(dateString) {
  if (!dateString) return new Date().toISOString().split('T')[0];

  // Si viene en formato DD/MM/YYYY
  if (dateString.includes('/')) {
    const [day, month, year] = dateString.split('/');
    return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
  }

  // Si ya está en formato correcto YYYY-MM-DD
  return dateString;
}

async function onSubmit() {
  if (!validate()) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  // Verificar que tengamos historial_clinico_id
  if (!form.historialClinicoId) {
    console.error('❌ historial_clinico_id está vacío');
    alert('Error: No se ha seleccionado un animal con historial clínico válido.');
    return;
  }

  isSubmitting.value = true;

  try {
    const consultaData = {
      historial_clinico_id: form.historialClinicoId,
      veterinario_id: String(form.veterinarianId),
      fecha_consulta: formatDateForBackend(form.consultDate),
      tipo_consulta: form.consultType, // Ya está con valores correctos: general, emergencia, seguimiento, especializada
      motivo_consulta: form.reason,
      sintomas: form.physicalExam || null,
      diagnostico: form.diagnosis || null,
      observaciones: form.veterinarianNotes || null,
      peso: form.vitalSigns.weight ? parseFloat(form.vitalSigns.weight) : null,
      temperatura: form.vitalSigns.temperature ? parseFloat(form.vitalSigns.temperature) : null,
      frecuencia_cardiaca: form.vitalSigns.heartRate ? parseInt(form.vitalSigns.heartRate) : null,
      frecuencia_respiratoria: form.vitalSigns.respiratoryRate ? parseInt(form.vitalSigns.respiratoryRate) : null,
      estado_salud: form.prognosis || null,
      tratamientos: form.medications.length > 0 ? form.medications.map(med => ({
        medicamento_id: med.medicationId,
        tipo_tratamiento: 'medicamento', // Fix: agregar tipo_tratamiento requerido
        descripcion: med.instructions || form.treatment,
        dosis: med.dose,
        frecuencia: med.frequency,
        cantidad_total: med.totalQuantity || null, // Enviar cantidad calculada en frontend
        duracion_dias: med.duration ? parseInt(med.duration) : null
      })) : []
    };

    // Si hay fecha de seguimiento, agregar
    if (form.requiresFollowup && form.followupDate) {
      consultaData.proxima_cita = formatDateForBackend(form.followupDate);
      consultaData.notas_seguimiento = form.followupNotes;
    }

    console.log('📤 Enviando consulta:', consultaData);

    // Verificar datos antes de enviar
    if (!consultaData.historial_clinico_id) {
      throw new Error('historial_clinico_id es requerido');
    }
    if (!consultaData.veterinario_id) {
      throw new Error('veterinario_id es requerido');
    }

    // Guardar consulta usando el store
    const result = await veterinaryStore.crearConsulta(consultaData);

    // Actualizar inventario localmente para cada medicamento prescrito
    form.medications.forEach(med => {
      updateInventoryStock(med.medicationId, med.totalQuantity);
    });

    alert('Consulta veterinaria guardada exitosamente');
    emit('consulta-saved', result);
    resetForm();
  } catch (error) {
    console.error('❌ Error al guardar consulta:', error);

    // Mostrar error más detallado
    let errorMessage = 'Error al guardar la consulta';
    if (error.response?.data?.errors) {
      const errores = error.response.data.errors;
      const primerError = Object.values(errores)[0];
      errorMessage = Array.isArray(primerError) ? primerError[0] : primerError;
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.message) {
      errorMessage = error.message;
    }

    alert(errorMessage);
  } finally {
    isSubmitting.value = false;
  }
}

onMounted(async () => {
  // Cargar datos iniciales
  await loadInitialData();

  fixNonSubmitButtons();
  preventScrollOnInteractions();

  if (typeof window !== 'undefined') {
    window.addEventListener('load', () => {
      fixNonSubmitButtons();
      preventScrollOnInteractions();
    });
  }
});

// Watch para cuando cambie el animalId desde props
watch(() => props.animalId, async (newAnimalId) => {
  if (newAnimalId) {
    form.animalId = newAnimalId;
    // Obtener historial clínico del animal
    try {
      const historial = await veterinaryStore.fetchHistorialClinico(newAnimalId);
      if (historial && historial.id) {
        form.historialClinicoId = historial.id;
      }
    } catch (error) {
      console.error('Error al obtener historial clínico:', error);
    }
  }
});

// Watch para cuando se seleccione un animal desde el dropdown
watch(() => form.animalId, async (newAnimalId) => {
  if (newAnimalId && !form.historialClinicoId) {
    try {
      const historial = await veterinaryStore.fetchHistorialClinico(newAnimalId);
      if (historial && historial.id) {
        form.historialClinicoId = historial.id;
      }
    } catch (error) {
      console.error('Error al obtener historial clínico:', error);
    }
  }
});
</script>

<style scoped>
.medical-form {
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

.entradas-de-texto-govco input,
.entradas-de-texto-govco textarea { 
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  box-sizing: border-box;
}

.entradas-de-texto-govco input {
  height: 44px;
}

.entradas-de-texto-govco textarea {
  font-family: inherit;
  resize: vertical;
}

.input-like-govco {
  display: flex;
  flex-direction: column;
  width: 100%;
}

.input-like-govco label {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

/* Estilos específicos para los calendarios */
.calendar-wrapper {
  margin: 0;
}

.calendar-wrapper :deep(.label-desplegable-govco) {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.calendar-wrapper :deep(.desplegable-govco) {
  width: 100%;
}

.calendar-wrapper :deep(.desplegable-govco .date.desplegable-selected-option) {
  width: 100%;
  box-sizing: border-box;
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

.error-text, 
.alert-desplegable-govco { 
  display: block; 
  color: #b00020; 
  font-size: 0.85rem; 
  margin-top: 0.5rem; 
}

.checkbox-govco { 
  display: flex; 
  align-items: center; 
  gap: 0.75rem; 
  padding: 1rem; 
  background: #f5f7fb; 
  border-radius: 6px; 
}

.checkbox-govco input {
  width: 20px;
  height: 20px;
  cursor: pointer;
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

.govco-btn { 
  padding: 0.75rem 2rem; 
  border-radius: 6px; 
  font-weight: 600; 
  cursor: pointer; 
  border: none; 
  color: white;
  transition: all 0.3s;
}

.govco-btn:hover {
  transform: translateY(-2px);
  opacity: 0.9;
}

.govco-bg-concrete { 
  background-color: #737373; 
}

.govco-bg-elf-green { 
  background-color: #069169; 
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