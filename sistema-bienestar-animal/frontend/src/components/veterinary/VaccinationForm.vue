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
      
      <!-- SECCIÓN 1: BÚSQUEDA DE ANIMAL -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Datos del animal</h3>
        
        <div class="form-grid">
          <!-- Búsqueda por chip/código -->
          <div class="search-section full-width">
            <label for="searchInput">Buscar animal por número de chip o código</label>
            <div class="search-input-group">
              <input
                type="text"
                id="searchInput"
                v-model="searchQuery"
                placeholder="Ej: 982000123456789 o AN-2025-00001"
                @keyup.enter="buscarAnimal"
                class="input-govco"
              />
              <button 
                type="button" 
                @click="buscarAnimal" 
                class="btn-search"
                :disabled="searching"
              >
                🔍 {{ searching ? 'Buscando...' : 'Buscar' }}
              </button>
            </div>
            <span v-if="searchError" class="error-text">{{ searchError }}</span>
          </div>

          <!-- Resultados de búsqueda -->
          <div v-if="animalesEncontrados.length > 0" class="results-dropdown full-width">
            <p class="results-label">Seleccione un animal:</p>
            <div 
              v-for="animal in animalesEncontrados" 
              :key="animal.id"
              @click="seleccionarAnimal(animal)"
              class="result-item"
            >
              <div class="result-info">
                <strong>{{ animal.nombre || 'Sin nombre' }}</strong>
                <span class="result-code">{{ animal.numero_chip || animal.codigo_unico }}</span>
              </div>
              <div class="result-meta">
                {{ animal.especie }} • {{ animal.raza }} • {{ animal.edad_formateada || 'Edad no especificada' }}
              </div>
            </div>
          </div>

          <!-- Animal seleccionado -->
          <div v-if="animalSeleccionado" class="animal-selected full-width">
            <div class="animal-card">
              <div class="animal-avatar">
                <img 
                  :src="animalSeleccionado.foto_url || 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiB2aWV3Qm94PSIwIDAgNDAwIDMwMCI+PHJlY3Qgd2lkdGg9IjQwMCIgaGVpZ2h0PSIzMDAiIGZpbGw9IiNlOWVjZWYiLz48ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyMDAsMTUwKSI+PGVsbGlwc2UgY3g9IjAiIGN5PSIyNSIgcng9IjM1IiByeT0iMzAiIGZpbGw9IiNhZGI1YmQiLz48ZWxsaXBzZSBjeD0iLTQ1IiBjeT0iLTEwIiByeD0iMTgiIHJ5PSIyMiIgZmlsbD0iI2FkYjViZCIvPjxlbGxpcHNlIGN4PSI0NSIgY3k9Ii0xMCIgcng9IjE4IiByeT0iMjIiIGZpbGw9IiNhZGI1YmQiLz48ZWxsaXBzZSBjeD0iLTI1IiBjeT0iLTQ1IiByeD0iMTUiIHJ5PSIxOCIgZmlsbD0iI2FkYjViZCIvPjxlbGxpcHNlIGN4PSIyNSIgY3k9Ii00NSIgcng9IjE1IiByeT0iMTgiIGZpbGw9IiNhZGI1YmQiLz48L2c+PC9zdmc+'" 
                  :alt="animalSeleccionado.nombre"
                />
              </div>
              <div class="animal-info">
                <h4>{{ animalSeleccionado.nombre || 'Sin nombre' }}</h4>
                <p>
                  <strong>Chip:</strong> {{ animalSeleccionado.numero_chip || 'No registrado' }} • 
                  <strong>Código:</strong> {{ animalSeleccionado.codigo_unico }}
                </p>
                <p>{{ animalSeleccionado.especie }} • {{ animalSeleccionado.raza }}</p>
              </div>
              <button type="button" @click="limpiarSeleccion" class="btn-clear">
                ✕ Cambiar animal
              </button>
            </div>
          </div>

          <!-- Fecha de aplicación -->
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

          <InputGovCo
            id="vaccineName"
            label="Nombre comercial"
            v-model="form.vaccineName"
            placeholder="Ej: Nobivac, Vanguard"
            :required="true"
            :alert-text="errors.vaccineName"
            :error="!!errors.vaccineName"
          />

          <InputGovCo
            id="laboratory"
            label="Laboratorio fabricante"
            v-model="form.laboratory"
            placeholder="Ej: MSD, Zoetis"
            :required="true"
            :alert-text="errors.laboratory"
            :error="!!errors.laboratory"
          />

          <InputGovCo
            id="batchNumber"
            label="Número de lote"
            v-model="form.batchNumber"
            placeholder="LOT123456"
            :required="true"
            :alert-text="errors.batchNumber"
            :error="!!errors.batchNumber"
          />

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

          <InputGovCo
            id="dose"
            label="Dosis (ml)"
            type="number"
            v-model="form.dose"
            placeholder="1.0"
            step="0.1"
            min="0.1"
            max="10"
            :required="true"
            :alert-text="errors.dose"
            :error="!!errors.dose"
          />

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

          <InputGovCo
            id="site"
            label="Sitio de aplicación"
            v-model="form.site"
            placeholder="Ej: Miembro anterior derecho"
            help-text="Opcional: indique la ubicación exacta"
          />
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
              @change="onDoseNumberChange"
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
        </div>
      </div>

      <!-- BOTONES -->
      <div class="form-actions">
        <button type="button" @click="resetForm" class="btn-secondary">
          Cancelar
        </button>
        <button type="submit" class="btn-primary" :disabled="isSubmitting || !animalSeleccionado">
          {{ isSubmitting ? 'Guardando...' : 'Registrar vacunación' }}
        </button>
      </div>
    </form>

  </section>
</template>

<script setup>
import { reactive, ref, computed, onMounted, nextTick, watch } from 'vue';
import DesplegableGovco from '../common/DesplegableGovco.vue';
import CalendarioGovco from '../common/CalendarioGovco.vue';
import InputGovCo from '../common/InputGovCo.vue';
import { useVeterinaryStore } from '@/stores/veterinary';
import { useAnimalsStore } from '@/stores/animals';

const veterinaryStore = useVeterinaryStore();
const animalsStore = useAnimalsStore();

const formEl = ref(null);
const applicationDateRef = ref(null);
const vaccineTypeRef = ref(null);
const expirationDateRef = ref(null);
const routeRef = ref(null);
const doseNumberRef = ref(null);
const nextDoseDateRef = ref(null);
const veterinarianRef = ref(null);
const isSubmitting = ref(false);
const loadingData = ref(true);

// Búsqueda de animales
const searchQuery = ref('');
const searching = ref(false);
const searchError = ref('');
const animalesEncontrados = ref([]);
const animalSeleccionado = ref(null);

// Data from API
const veterinarians = ref([]);
const tiposVacunaApi = ref([]);

function initGovCoComponents() {
  nextTick(() => {
    if (window.GOVCo?.init) {
      const dropdowns = document.querySelectorAll('.vaccination-form .desplegable-govco');
      dropdowns.forEach((dd) => {
        try {
          window.GOVCo.init(dd.parentElement || dd);
        } catch (e) {
          console.warn('Error inicializando dropdown:', e);
        }
      });
    }

    if (window.reinitGovCo) {
      setTimeout(() => {
        window.reinitGovCo();
      }, 100);
    }
  });
}

async function buscarAnimal() {
  if (!searchQuery.value.trim()) {
    searchError.value = 'Ingrese un número de chip o código para buscar';
    return;
  }

  searching.value = true;
  searchError.value = '';
  animalesEncontrados.value = [];

  try {
    await animalsStore.fetchAnimals({ 
      search: searchQuery.value,
      per_page: 10 
    });

    const resultados = animalsStore.animals || [];

    if (resultados.length === 0) {
      searchError.value = 'No se encontraron animales con ese criterio';
    } else if (resultados.length === 1) {
      seleccionarAnimal(resultados[0]);
    } else {
      animalesEncontrados.value = resultados;
    }
  } catch (error) {
    console.error('Error buscando animal:', error);
    searchError.value = 'Error al buscar el animal';
  } finally {
    searching.value = false;
  }
}

function seleccionarAnimal(animal) {
  animalSeleccionado.value = {
    ...animal,
    historial_clinico_id: animal.historial_clinico?.id || animal.historial_clinico_id
  };
  animalesEncontrados.value = [];
  searchQuery.value = animal.numero_chip || animal.codigo_unico;
  searchError.value = '';
  
  // Verificar que tenga historial clínico
  if (!animalSeleccionado.value.historial_clinico_id) {
    searchError.value = 'Este animal no tiene historial clínico. Por favor, cree uno primero.';
    animalSeleccionado.value = null;
  }
}

function limpiarSeleccion() {
  animalSeleccionado.value = null;
  searchQuery.value = '';
  animalesEncontrados.value = [];
  searchError.value = '';
}

async function loadInitialData() {
  loadingData.value = true;

  try {
    // Cargar tipos de vacuna
    try {
      const tiposData = await veterinaryStore.fetchTiposVacuna();
      if (tiposData && tiposData.length > 0) {
        tiposVacunaApi.value = tiposData;
      }
    } catch (error) {
      console.warn('Error cargando tipos de vacuna:', error);
    }

    // Cargar veterinarios
    try {
      const vetsData = await veterinaryStore.fetchVeterinarios();
      veterinarians.value = vetsData || veterinaryStore.veterinarios || [];
      
      if (veterinarians.value.length === 0) {
        alert('No hay veterinarios registrados. Debes crear al menos uno para registrar vacunaciones.');
      }
    } catch (error) {
      console.error('Error cargando veterinarios:', error);
    }

    await nextTick();
    setTimeout(() => {
      initGovCoComponents();
    }, 200);

  } catch (error) {
    console.error('Error cargando datos iniciales:', error);
    alert('Error al cargar datos. Por favor recargue la página.');
  } finally {
    loadingData.value = false;
  }
}

const vaccineTypeOptions = computed(() => {
  if (tiposVacunaApi.value.length > 0) {
    return tiposVacunaApi.value.map(tipo => ({
      value: tipo.id,
      text: tipo.nombre,
      codigo: tipo.codigo,
      intervalo: tipo.intervalo_dosis
    }));
  }
  
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
    text: `${vet.nombres || vet.nombre_completo || 'Dr.'} ${vet.apellidos || ''} - TP ${vet.numero_tarjeta_profesional || 'N/A'}`
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
  observations: ''
});

const errors = reactive({
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

function onDoseNumberChange(value) {
  form.doseNumber = value;
  if (value === 'refuerzo') {
    form.requiresNextDose = false;
    form.nextDoseDate = '';
  } else {
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

  if (!animalSeleccionado.value) {
    searchError.value = 'Debe buscar y seleccionar un animal';
    isValid = false;
  }

  if (!animalSeleccionado.value?.historial_clinico_id) {
    searchError.value = 'El animal seleccionado no tiene historial clínico';
    isValid = false;
  }
  
  const requiredFields = {
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
      form[k] = false;
    } else if (typeof form[k] === 'number') {
      form[k] = 1.0;
    } else {
      form[k] = '';
    }
  });
  Object.keys(errors).forEach(k => errors[k] = '');
  limpiarSeleccion();
}

function convertirFecha(fechaStr) {
  if (!fechaStr) return null;
  const parts = fechaStr.split('/');
  if (parts.length === 3) {
    return `${parts[2]}-${parts[1]}-${parts[0]}`;
  }
  return fechaStr;
}

async function onSubmit() {
  if (!validate()) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  if (isSubmitting.value) return;
  isSubmitting.value = true;

  try {
    const vacunaData = {
      historial_clinico_id: animalSeleccionado.value.historial_clinico_id,
      tipo_vacuna_id: form.vaccineType,
      veterinario_id: form.veterinarianId,
      fecha_aplicacion: convertirFecha(form.applicationDate),
      fecha_proxima_dosis: form.requiresNextDose ? convertirFecha(form.nextDoseDate) : null,
      lote: form.batchNumber,
      fabricante: form.laboratory,
      nombre_vacuna: form.vaccineName,
      dosis: form.dose,
      via_administracion: form.route,
      sitio_aplicacion: form.site || null,
      numero_dosis: form.doseNumber,
      observaciones: form.observations || null
    };

    console.log('📤 Enviando vacunación:', vacunaData);

    await veterinaryStore.crearVacuna(vacunaData);

    alert('✅ Vacunación registrada exitosamente');
    resetForm();
  } catch (error) {
    console.error('❌ Error al registrar vacunación:', error);
    const errorMsg = error.response?.data?.message || 'Error al registrar la vacunación';
    alert(errorMsg);
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
}

onMounted(async () => {
  fixButtonTypes();
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
  gap: 1.5rem 2rem; 
  padding: 1.5rem; 
}

.full-width { 
  grid-column: 1 / 3; 
}

/* Búsqueda de animales */
.search-section label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.search-input-group {
  display: flex;
  gap: 0.75rem;
}

.input-govco {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  height: 44px;
  box-sizing: border-box;
  font-family: inherit;
}

.input-govco:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.1);
}

.btn-search {
  padding: 0 1.5rem;
  background: #3366CC;
  color: white;
  border: none;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
  height: 44px;
}

.btn-search:hover:not(:disabled) {
  background: #004884;
  transform: translateY(-1px);
}

.btn-search:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Resultados de búsqueda */
.results-dropdown {
  margin-top: 1rem;
  border: 1px solid #E0E0E0;
  border-radius: 6px;
  overflow: hidden;
  background: white;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.results-label {
  padding: 0.75rem 1rem;
  background: #F5F7FB;
  color: #666;
  font-size: 0.9rem;
  font-weight: 500;
  margin: 0;
  border-bottom: 1px solid #E0E0E0;
}

.result-item {
  padding: 1rem;
  cursor: pointer;
  transition: background 0.2s;
  border-bottom: 1px solid #F0F0F0;
}

.result-item:last-child {
  border-bottom: none;
}

.result-item:hover {
  background: #E8F0FE;
}

.result-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.25rem;
}

.result-code {
  color: #3366CC;
  font-size: 0.9rem;
  font-family: monospace;
}

.result-meta {
  color: #666;
  font-size: 0.85rem;
}

/* Animal seleccionado */
.animal-selected {
  margin-top: 1rem;
}

.animal-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: linear-gradient(135deg, #E8F0FE 0%, #F5F7FB 100%);
  border: 2px solid #3366CC;
  border-radius: 8px;
}

.animal-avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  background: linear-gradient(135deg, #3366cc 0%, #004884 100%);
}

.animal-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.animal-info {
  flex: 1;
}

.animal-info h4 {
  margin: 0 0 0.25rem 0;
  color: #3366CC;
  font-size: 1.1rem;
}

.animal-info p {
  margin: 0.25rem 0;
  color: #666;
  font-size: 0.9rem;
}

.btn-clear {
  padding: 0.5rem 1rem;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.btn-clear:hover {
  background: #c82333;
}

.input-wrapper,
.input-like-govco {
  display: flex;
  flex-direction: column;
  width: 100%;
}

.input-wrapper label {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
  font-size: 0.95rem;
}

.input-wrapper textarea.input-govco {
  resize: vertical;
  min-height: 80px;
  padding: 0.75rem;
  font-family: inherit;
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

.btn-primary:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.btn-secondary {
  background-color: #737373;
}

.btn-primary:hover:not(:disabled),
.btn-secondary:hover {
  transform: translateY(-2px);
  opacity: 0.9;
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

@media (max-width: 768px) {
  .vaccination-form {
    padding: 1rem;
  }

  .form-grid { 
    grid-template-columns: 1fr; 
  }
  
  .full-width { 
    grid-column: 1 / 2; 
  }

  .search-input-group {
    flex-direction: column;
  }

  .btn-search {
    width: 100%;
  }

  .animal-card {
    flex-direction: column;
    text-align: center;
  }

  .btn-clear {
    width: 100%;
  }
}
</style>