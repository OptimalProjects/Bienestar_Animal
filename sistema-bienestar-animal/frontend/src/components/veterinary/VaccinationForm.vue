<template>
  <section class="vaccination-form">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Registro de Vacunación</h2>
      <p class="text2-tipografia-govco">Complete los datos de la vacuna aplicada</p>
    </div>

    <form ref="formEl" @submit.prevent="onSubmit" novalidate>
      
      <!-- SECCIÓN 1: IDENTIFICACIÓN -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Datos del animal</h3>
        
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

          <!-- Fecha de aplicación -->
          <div class="input-like-govco">
            <label for="applicationDate" class="label-desplegable-govco">
              Fecha de aplicación<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco desplegable-calendar-govco" data-type="calendar">
              <div class="date desplegable-selected-option">
                <input
                  type="date"
                  id="applicationDate"
                  v-model="form.applicationDate"
                  class="browser-default"
                  @change="calculateNextDose"
                />
              </div>
            </div>
            <span v-if="errors.applicationDate" class="alert-desplegable-govco">{{ errors.applicationDate }}</span>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 2: DATOS DE LA VACUNA -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Información de la vacuna</h3>
        
        <div class="form-grid">
          <!-- Tipo de vacuna -->
          <div class="input-like-govco">
            <label for="vaccineType" class="label-desplegable-govco">
              Tipo de vacuna<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic">
              <select 
                id="vaccineType" 
                v-model="form.vaccineType"
                @change="onVaccineTypeChange"
              >
                <option disabled value="">Seleccionar tipo</option>
                <option value="rabia">Rabia</option>
                <option value="quintuple">Quíntuple (DHPPL)</option>
                <option value="sextuple">Séxtuple (DHPPL + Corona)</option>
                <option value="triple_felina">Triple Felina</option>
                <option value="leucemia_felina">Leucemia Felina</option>
                <option value="parvovirus">Parvovirus</option>
                <option value="bordetella">Bordetella (Tos de las perreras)</option>
                <option value="otra">Otra</option>
              </select>
            </div>
            <span v-if="errors.vaccineType" class="alert-desplegable-govco">{{ errors.vaccineType }}</span>
          </div>

          <!-- Nombre comercial -->
          <div class="entradas-de-texto-govco">
            <label for="vaccineName">
              Nombre comercial<span aria-required="true">*</span>
            </label>
            <input
              type="text"
              id="vaccineName"
              v-model="form.vaccineName"
              placeholder="Ej: Nobivac, Vanguard"
            />
            <span v-if="errors.vaccineName" class="error-text">{{ errors.vaccineName }}</span>
          </div>

          <!-- Laboratorio -->
          <div class="entradas-de-texto-govco">
            <label for="laboratory">
              Laboratorio fabricante<span aria-required="true">*</span>
            </label>
            <input
              type="text"
              id="laboratory"
              v-model="form.laboratory"
              placeholder="Ej: MSD, Zoetis"
            />
            <span v-if="errors.laboratory" class="error-text">{{ errors.laboratory }}</span>
          </div>

          <!-- Número de lote -->
          <div class="entradas-de-texto-govco">
            <label for="batchNumber">
              Número de lote<span aria-required="true">*</span>
            </label>
            <input
              type="text"
              id="batchNumber"
              v-model="form.batchNumber"
              placeholder="LOT123456"
            />
            <span v-if="errors.batchNumber" class="error-text">{{ errors.batchNumber }}</span>
          </div>

          <!-- Fecha de vencimiento -->
          <div class="input-like-govco">
            <label for="expirationDate" class="label-desplegable-govco">
              Fecha de vencimiento<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco desplegable-calendar-govco" data-type="calendar">
              <div class="date desplegable-selected-option">
                <input
                  type="date"
                  id="expirationDate"
                  v-model="form.expirationDate"
                  class="browser-default"
                />
              </div>
            </div>
            <span v-if="errors.expirationDate" class="alert-desplegable-govco">{{ errors.expirationDate }}</span>
          </div>

          <!-- Dosis -->
          <div class="entradas-de-texto-govco">
            <label for="dose">
              Dosis (ml)<span aria-required="true">*</span>
            </label>
            <input
              type="number"
              id="dose"
              v-model.number="form.dose"
              step="0.1"
              min="0.1"
              max="10"
              placeholder="1.0"
            />
            <span v-if="errors.dose" class="error-text">{{ errors.dose }}</span>
          </div>

          <!-- Vía de aplicación -->
          <div class="input-like-govco">
            <label for="route" class="label-desplegable-govco">
              Vía de aplicación<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic">
              <select id="route" v-model="form.route">
                <option disabled value="">Seleccionar vía</option>
                <option value="subcutanea">Subcutánea</option>
                <option value="intramuscular">Intramuscular</option>
                <option value="intranasal">Intranasal</option>
              </select>
            </div>
            <span v-if="errors.route" class="alert-desplegable-govco">{{ errors.route }}</span>
          </div>

          <!-- Sitio de aplicación -->
          <div class="entradas-de-texto-govco">
            <label for="site">
              Sitio de aplicación
            </label>
            <input
              type="text"
              id="site"
              v-model="form.site"
              placeholder="Ej: Miembro anterior derecho"
            />
          </div>
        </div>
      </div>

      <!-- SECCIÓN 3: ESQUEMA DE VACUNACIÓN -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Esquema de vacunación</h3>
        
        <div class="form-grid">
          <!-- Dosis número -->
          <div class="input-like-govco">
            <label for="doseNumber" class="label-desplegable-govco">
              Dosis número<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic">
              <select id="doseNumber" v-model="form.doseNumber">
                <option disabled value="">Seleccionar</option>
                <option value="1">Primera dosis</option>
                <option value="2">Segunda dosis</option>
                <option value="3">Tercera dosis</option>
                <option value="refuerzo">Refuerzo anual</option>
              </select>
            </div>
            <span v-if="errors.doseNumber" class="alert-desplegable-govco">{{ errors.doseNumber }}</span>
          </div>

          <!-- ¿Requiere próxima dosis? -->
          <div class="checkbox-govco full-width">
            <input 
              type="checkbox" 
              id="requiresNext" 
              v-model="form.requiresNextDose"
              @change="calculateNextDose"
            />
            <label for="requiresNext">
              Requiere próxima dosis
            </label>
          </div>

          <!-- Fecha de próxima dosis -->
          <div v-if="form.requiresNextDose" class="input-like-govco">
            <label for="nextDoseDate" class="label-desplegable-govco">
              Fecha de próxima dosis<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco desplegable-calendar-govco" data-type="calendar">
              <div class="date desplegable-selected-option">
                <input
                  type="date"
                  id="nextDoseDate"
                  v-model="form.nextDoseDate"
                  class="browser-default"
                />
              </div>
            </div>
            <span class="info-entradas-de-texto-govco">
              {{ nextDoseInfo }}
            </span>
            <span v-if="errors.nextDoseDate" class="alert-desplegable-govco">{{ errors.nextDoseDate }}</span>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 4: VETERINARIO Y OBSERVACIONES -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Responsable y observaciones</h3>
        
        <div class="form-grid">
          <!-- Veterinario -->
          <div class="input-like-govco">
            <label for="veterinarian" class="label-desplegable-govco">
              Veterinario aplicador<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic">
              <select id="veterinarian" v-model="form.veterinarianId">
                <option disabled value="">Seleccionar veterinario</option>
                <option v-for="vet in veterinarians" :key="vet.id" :value="vet.id">
                  {{ vet.name }} - TP {{ vet.license }}
                </option>
              </select>
            </div>
            <span v-if="errors.veterinarianId" class="alert-desplegable-govco">{{ errors.veterinarianId }}</span>
          </div>

          <!-- Observaciones -->
          <div class="entradas-de-texto-govco full-width">
            <label for="observations">
              Observaciones
            </label>
            <textarea
              id="observations"
              v-model="form.observations"
              rows="3"
              placeholder="Reacciones adversas, estado del animal post-vacunación..."
            ></textarea>
          </div>

          <!-- Generar certificado -->
          <div class="checkbox-govco full-width">
            <input 
              type="checkbox" 
              id="generateCertificate" 
              v-model="form.generateCertificate"
            />
            <label for="generateCertificate">
              Generar certificado de vacunación en PDF
            </label>
          </div>
        </div>
      </div>

      <!-- BOTONES -->
      <div class="form-actions">
        <button type="button" @click="resetForm" class="govco-btn govco-bg-concrete">
          Cancelar
        </button>
        <button type="submit" class="govco-btn govco-bg-elf-green">
          Registrar vacunación
        </button>
      </div>
    </form>

  </section>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';

const formEl = ref(null);

// Mock data
const animals = ref([
  { id: 1, name: 'Firulais', microchip: 'MC123456789' },
  { id: 2, name: 'Michi', microchip: 'MC987654321' }
]);

const veterinarians = ref([
  { id: 1, name: 'Dr. Juan Pérez', license: '12345' },
  { id: 2, name: 'Dra. María López', license: '67890' }
]);

// Esquemas de vacunación por tipo
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

function onVaccineTypeChange() {
  // Calcular fecha de próxima dosis automáticamente
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

  const appDate = new Date(form.applicationDate);
  const nextDate = new Date(appDate);
  nextDate.setDate(nextDate.getDate() + scheme.interval);
  
  form.nextDoseDate = nextDate.toISOString().split('T')[0];
}

function validate() {
  Object.keys(errors).forEach(k => errors[k] = '');
  
  let isValid = true;
  
  if (!form.animalId) {
    errors.animalId = 'Debe seleccionar un animal';
    isValid = false;
  }
  
  if (!form.applicationDate) {
    errors.applicationDate = 'Campo requerido';
    isValid = false;
  }
  
  if (!form.vaccineType) {
    errors.vaccineType = 'Campo requerido';
    isValid = false;
  }
  
  if (!form.vaccineName?.trim()) {
    errors.vaccineName = 'Campo requerido';
    isValid = false;
  }
  
  if (!form.laboratory?.trim()) {
    errors.laboratory = 'Campo requerido';
    isValid = false;
  }
  
  if (!form.batchNumber?.trim()) {
    errors.batchNumber = 'Campo requerido';
    isValid = false;
  }
  
  if (!form.expirationDate) {
    errors.expirationDate = 'Campo requerido';
    isValid = false;
  }
  
  if (!form.dose || form.dose <= 0) {
    errors.dose = 'Debe especificar una dosis válida';
    isValid = false;
  }
  
  if (!form.route) {
    errors.route = 'Campo requerido';
    isValid = false;
  }
  
  if (!form.doseNumber) {
    errors.doseNumber = 'Campo requerido';
    isValid = false;
  }
  
  if (form.requiresNextDose && !form.nextDoseDate) {
    errors.nextDoseDate = 'Debe especificar fecha de próxima dosis';
    isValid = false;
  }
  
  if (!form.veterinarianId) {
    errors.veterinarianId = 'Debe seleccionar veterinario responsable';
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
  
  try {
    console.log('Guardando vacunación:', form);
    
    // TODO: Guardar en backend
    // await saveVaccination(form);
    
    // TODO: Si requiresNextDose, crear alerta automática 7 días antes
    if (form.requiresNextDose) {
      const alertDate = new Date(form.nextDoseDate);
      alertDate.setDate(alertDate.getDate() - 7);
      console.log('Crear alerta para:', alertDate);
      // await createVaccinationAlert(form.animalId, form.nextDoseDate, alertDate);
    }
    
    // TODO: Si generateCertificate, generar PDF
    if (form.generateCertificate) {
      console.log('Generar certificado de vacunación');
      // await generateVaccinationCertificate(form);
    }
    
    // TODO: Enviar indicador a sci-indicators
    // await sendIndicator('vaccination_registered', form);
    
    alert('Vacunación registrada exitosamente');
    resetForm();
  } catch (error) {
    console.error('Error al registrar vacunación:', error);
    alert('Error al registrar la vacunación');
  }
}

onMounted(() => {
  // Inicializar componentes GOV.CO
  if (window.GOVCo?.init) {
    const elements = document.querySelectorAll('.desplegable-govco');
    elements.forEach(el => {
      window.GOVCo.init(el.parentElement);
    });
  }
});
</script>

<style scoped>
.vaccination-form { max-width: 1200px; margin: 0 auto; padding: 2rem; background: #f5f7fb; }
.form-header { margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 3px solid #3366CC; }
.form-section { background: white; border-radius: 8px; margin-bottom: 1.5rem; overflow: visible; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.section-title { margin: 0; padding: 1rem 1.5rem; background: #E8F0FE; color: #3366CC; font-weight: 600; }
.form-grid { display: grid; grid-template-columns: repeat(2, 1fr); column-gap: 2rem; row-gap: 1.5rem; padding: 1.5rem; }
.full-width { grid-column: 1 / 3; }
.entradas-de-texto-govco input,
.entradas-de-texto-govco textarea,
.desplegable-govco select { width: 100%; padding: 0.75rem; border: 1px solid #D0D0D0; border-radius: 4px; font-size: 1rem; }
.error-text, .alert-desplegable-govco { display: block; color: #b00020; font-size: 0.85rem; margin-top: 0.5rem; }
.info-entradas-de-texto-govco { display: block; color: #666; font-size: 0.85rem; margin-top: 0.25rem; }
.checkbox-govco { display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: #f5f7fb; border-radius: 6px; }
.form-actions { display: flex; justify-content: flex-end; gap: 1rem; padding: 1.5rem; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.govco-btn { padding: 0.75rem 2rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; color: white; }
.govco-bg-concrete { background-color: #737373; }
.govco-bg-elf-green { background-color: #069169; }
.input-like-govco { display: flex; flex-direction: column; width: 100%; }

@media (max-width: 768px) {
  .form-grid { grid-template-columns: 1fr; }
  .full-width { grid-column: 1 / 2; }
}
</style>6