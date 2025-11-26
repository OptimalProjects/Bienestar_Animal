<template>
  <section class="medical-form">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Consulta Veterinaria</h2>
      <p class="text2-tipografia-govco">Registro de atención médica</p>
    </div>

    <form ref="formEl" @submit.prevent="onSubmit" novalidate>
      
      <!-- SECCIÓN 1: DATOS DE LA CONSULTA -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Información general</h3>
        
        <div class="form-grid">
          <!-- Animal -->
          <div class="input-like-govco">
            <label for="animal" class="label-desplegable-govco">
              Animal<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic">
              <select id="animal" v-model="form.animalId">
                <option disabled value="">Seleccionar animal</option>
                <option v-for="animal in animals" :key="animal.id" :value="animal.id">
                  {{ animal.name }} - {{ animal.microchip }}
                </option>
              </select>
            </div>
            <span v-if="errors.animalId" class="alert-desplegable-govco">{{ errors.animalId }}</span>
          </div>

          <!-- Fecha y hora -->
          <div class="input-like-govco">
            <div class="date-field-container neut-date-container">
              <label for="consultDate" class="label-desplegable-govco">
                Fecha y hora<span aria-required="true">*</span>
              </label>
              <div class="desplegable-govco desplegable-calendar-govco" data-type="calendar">
                <div class="date desplegable-selected-option">
                  <input
                    class="browser-default"
                    type="text"
                    id="consultDate"
                    v-model="form.consultDate"
                    @change="() => {}"
                    @blur="() => {}"
                    aria-autocomplete="off"
                    days="true"
                    placeholder="DD/MM/AAAA"
                  />
                </div>
              </div>
              <span v-if="errors.consultDate" class="alert-desplegable-govco">{{ errors.consultDate }}</span>
            </div>
          </div>

          <!-- Tipo de consulta -->
          <div class="input-like-govco">
            <label for="consultType" class="label-desplegable-govco" >
              Tipo de consulta<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic" id="consultType-search-dropdown">
              <select id="consultType" v-model="form.consultType" @change="onDropdownChange">
                <option disabled value="">Escoger</option>
                <option value="primera_vez">Primera vez</option>
                <option value="control">Control</option>
                <option value="emergencia">Emergencia</option>
                <option value="vacunacion">Vacunación</option>
                <option value="cirugia">Pre-quirúrgica</option>
              </select>
            </div>
            <span v-if="errors.consultType" class="alert-desplegable-govco">{{ errors.consultType }}</span>
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
            <label for="prognosis" class="label-desplegable-govco">
              Pronóstico
            </label>
            <div class="desplegable-govco" data-type="basic" id="prognosis-search-dropdown">
              <select id="prognosis" v-model="form.prognosis" @change="onDropdownChange">
                <option value="">No especificado</option>
                <option value="excelente">Excelente</option>
                <option value="bueno">Bueno</option>
                <option value="reservado">Reservado</option>
                <option value="grave">Grave</option>
              </select>
            </div>
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
          <div v-if="form.requiresFollowup" class="input-like-govco">
            <div class="date-field-container neut-date-container">
              <label for="followupDate" class="label-desplegable-govco">
                Fecha de próximo control<span aria-required="true">*</span>
              </label>
              <div class="desplegable-govco desplegable-calendar-govco" data-type="calendar">
                <div class="date desplegable-selected-option">
                  <input
                    class="browser-default"
                    type="text"
                    id="followupDate"
                    v-model="form.followupDate"
                    @change="() => {}"
                    @blur="() => {}"
                    aria-autocomplete="off"
                    days="true"
                    placeholder="DD/MM/AAAA"
                  />
                </div>
              </div>
              <span v-if="errors.followupDate" class="alert-desplegable-govco">{{ errors.followupDate }}</span>
            </div>
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
            <label for="veterinarian" class="label-desplegable-govco">
              Veterinario responsable<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic">
              <select id="veterinarian" v-model="form.veterinarianId">
                <option disabled value="">Seleccionar veterinario</option>
                <option v-for="vet in veterinarians" :key="vet.id" :value="vet.id">
                  {{ vet.name }} - Tarjeta Prof. {{ vet.license }}
                </option>
              </select>
            </div>
            <span v-if="errors.veterinarianId" class="alert-desplegable-govco">{{ errors.veterinarianId }}</span>
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
import { reactive, ref, onMounted } from 'vue';
import VitalSignsInput from './VitalSignsInput.vue';
import MedicationPrescription from './MedicationPrescription.vue';
import FileUploader from '../common/FileUploader.vue';

const formEl = ref(null);

// Mock data - reemplazar con llamadas a API
const animals = ref([
  { id: 1, name: 'Firulais', microchip: 'MC123456789' },
  { id: 2, name: 'Michi', microchip: 'MC987654321' }
]);

const veterinarians = ref([
  { id: 1, name: 'Dr. Juan Pérez', license: '12345' },
  { id: 2, name: 'Dra. María López', license: '67890' }
]);

const medicationInventory = ref([
  { id: 1, name: 'Amoxicilina 500mg', stock: 50, unit: 'tabletas' },
  { id: 2, name: 'Ivermectina 1%', stock: 20, unit: 'ml' }
]);

const form = reactive({
  animalId: '',
  consultDate: '',
  consultType: '',
  reason: '',
  vitalSigns: {
    temperature: '',
    heartRate: '',
    respiratoryRate: '',
    weight: '',
    bodyCondition: ''
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

function validate() {
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
  
  if (!form.veterinarianId) {
    errors.veterinarianId = 'Debe seleccionar veterinario responsable';
    isValid = false;
  }
  
  return isValid;
}

function updateInventoryStock(medicationId, quantity) {
  // Lógica para descontar del inventario
  const medication = medicationInventory.value.find(m => m.id === medicationId);
  if (medication) {
    medication.stock -= quantity;
    // TODO: Enviar actualización al backend
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

async function onSubmit() {
  if (!validate()) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }
  
  try {
    // TODO: Integración con backend
    console.log('Guardando consulta:', form);
    
    // Descontar medicamentos del inventario
    form.medications.forEach(med => {
      updateInventoryStock(med.medicationId, med.totalQuantity);
    });
    
    // TODO: Enviar evento a sciaudit
    // await sendAuditEvent('medical_record_created', form);
    
    alert('Consulta veterinaria guardada exitosamente');
    resetForm();
  } catch (error) {
    console.error('Error al guardar consulta:', error);
    alert('Error al guardar la consulta');
  }
}

onMounted(() => {
  // Inicializar componentes GOV.CO
  if (window.GOVCo?.init) {
    const dropdowns = document.querySelectorAll('.desplegable-govco');
    dropdowns.forEach(dropdown => {
      window.GOVCo.init(dropdown.parentElement);
    });
  }
});
</script>

<style scoped>
.medical-form { max-width: 1200px; margin: 0 auto; padding: 2rem; background: #f5f7fb; }
.form-header { margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 3px solid #3366CC; }
.form-section { background: white; border-radius: 8px; margin-bottom: 1.5rem; overflow: visible; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.section-title { margin: 0; padding: 1rem 1.5rem; background: #E8F0FE; color: #3366CC; font-weight: 600; }
.form-grid { display: grid; grid-template-columns: repeat(2, 1fr); column-gap: 2rem; row-gap: 1.5rem; padding: 1.5rem; }
.full-width { grid-column: 1 / 3; }
.entradas-de-texto-govco input,
.entradas-de-texto-govco textarea,
.entradas-de-texto-govco, 
.desplegable-govco, 
.container-carga-de-archivo-govco { 
  width: 100%; 
}
.desplegable-govco select { width: 100%; padding: 0.75rem; border: 1px solid #D0D0D0; border-radius: 4px; font-size: 1rem; }
.error-text, .alert-desplegable-govco { display: block; color: #b00020; font-size: 0.85rem; margin-top: 0.5rem; }
.checkbox-govco { display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: #f5f7fb; border-radius: 6px; }
.form-actions { display: flex; justify-content: flex-end; gap: 1rem; padding: 1.5rem; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.govco-btn { padding: 0.75rem 2rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; color: white; }
.govco-bg-concrete { background-color: #737373; }
.govco-bg-elf-green { background-color: #069169; }
.input-like-govco {
  display: flex;
  flex-direction: column;
  width: 100%;
  margin: 18px 0;
}

/* Igual que en SearchFilters.vue */
.input-like-govco label {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.date-field-container {
  width: 100%;
}

.date-field-container :deep(.date.desplegable-selected-option) {
  padding: 7px 40px 7px 16px !important;
  box-sizing: border-box !important;
}

.date-field-container :deep(.date.desplegable-selected-option input) {
  width: 100% !important;
  padding-right: 30px !important;
  box-sizing: border-box !important;
}

.neut-date-container .desplegable-govco {
  margin-top: 0.7rem;
}

@media (max-width: 768px) {
  .form-grid { grid-template-columns: 1fr; }
  .full-width { grid-column: 1 / 2; }
}
</style>