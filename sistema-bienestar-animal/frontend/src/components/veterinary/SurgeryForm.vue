<template>
  <section class="surgery-form">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Registro de Cirugía</h2>
      <p class="text2-tipografia-govco">
        Registre la información de la intervención quirúrgica.
      </p>
    </div>

    <!-- Indicador de carga -->
    <div v-if="loadingData" class="loading-overlay">
      <div class="spinner"></div>
      <p>Cargando datos...</p>
    </div>

    <form v-else ref="formEl" @submit.prevent="onSubmit" novalidate>
      <!-- SECCIÓN 1: DATOS GENERALES -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Datos generales</h3>

        <div class="form-grid">
          <!-- Animal -->
          <div class="input-like-govco">
            <label for="animal">Animal<span aria-required="true">*</span></label>
            <div class="desplegable-govco" data-type="basic">
              <select
                id="animal"
                v-model="form.animalId"
                class="browser-default"
              >
                <option value="" disabled>Seleccione un animal</option>
                <option
                  v-for="animal in animals"
                  :key="animal.id"
                  :value="animal.id"
                >
                  {{ animal.name }} ({{ animal.microchip }})
                </option>
              </select>
            </div>
            <span v-if="errors.animalId" class="alert-desplegable-govco">
              {{ errors.animalId }}
            </span>
          </div>

          <!-- Veterinario principal -->
          <div class="input-like-govco">
            <label for="veterinarian">
              Veterinario responsable<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic">
              <select
                id="veterinarian"
                v-model="form.veterinarianId"
                class="browser-default"
              >
                <option value="" disabled>Seleccione veterinario</option>
                <option
                  v-for="vet in veterinarians"
                  :key="vet.id"
                  :value="vet.id"
                >
                  {{ vet.name }} ({{ vet.license }})
                </option>
              </select>
            </div>
            <span v-if="errors.veterinarianId" class="alert-desplegable-govco">
              {{ errors.veterinarianId }}
            </span>
          </div>

          <!-- Tipo cirugía -->
          <div class="entradas-de-texto-govco">
            <label for="surgeryType">
              Tipo de cirugía<span aria-required="true">*</span>
            </label>
            <input
              id="surgeryType"
              v-model="form.surgeryType"
              type="text"
              placeholder="Esterilización, laparotomía exploratoria, etc."
            />
            <span v-if="errors.surgeryType" class="error-text">
              {{ errors.surgeryType }}
            </span>
          </div>

          <!-- Urgencia -->
          <div class="input-like-govco">
            <label for="urgency">Tipo de procedimiento</label>
            <div class="desplegable-govco" data-type="basic">
              <select
                id="urgency"
                v-model="form.urgency"
                class="browser-default"
              >
                <option value="programada">Programada</option>
                <option value="emergencia">Emergencia</option>
              </select>
            </div>
          </div>

          <!-- Fecha y hora -->
          <div class="entradas-de-texto-govco">
            <label for="surgeryDate">
              Fecha y hora<span aria-required="true">*</span>
            </label>
            <input
              id="surgeryDate"
              v-model="form.surgeryDateTime"
              type="datetime-local"
            />
            <span v-if="errors.surgeryDateTime" class="error-text">
              {{ errors.surgeryDateTime }}
            </span>
          </div>

          <!-- Duración -->
          <div class="entradas-de-texto-govco">
            <label for="duration">
              Duración (minutos)<span aria-required="true">*</span>
            </label>
            <input
              id="duration"
              v-model.number="form.durationMinutes"
              type="number"
              min="0"
            />
            <span v-if="errors.durationMinutes" class="error-text">
              {{ errors.durationMinutes }}
            </span>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 2: DETALLES Y ANESTESIA -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">
          Detalles de la cirugía
        </h3>

        <div class="form-grid">
          <div class="entradas-de-texto-govco full-width">
            <label for="anesthesia">
              Anestesia utilizada<span aria-required="true">*</span>
            </label>
            <textarea
              id="anesthesia"
              v-model="form.anesthesia"
              rows="2"
              placeholder="Protocolo anestésico, medicamentos, dosis..."
            />
            <span v-if="errors.anesthesia" class="error-text">
              {{ errors.anesthesia }}
            </span>
          </div>

          <div class="entradas-de-texto-govco full-width">
            <label for="findings">
              Hallazgos quirúrgicos<span aria-required="true">*</span>
            </label>
            <textarea
              id="findings"
              v-model="form.findings"
              rows="4"
              placeholder="Descripción de hallazgos intraoperatorios"
            />
            <span v-if="errors.findings" class="error-text">
              {{ errors.findings }}
            </span>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 3: EQUIPO QUIRÚRGICO -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">
          Equipo quirúrgico
        </h3>

        <div class="form-grid">
          <div class="entradas-de-texto-govco">
            <label for="assistant">Ayudante</label>
            <input
              id="assistant"
              v-model="form.assistantName"
              type="text"
              placeholder="Nombre del ayudante"
            />
          </div>

          <div class="entradas-de-texto-govco">
            <label for="anesthetist">Anestesista</label>
            <input
              id="anesthetist"
              v-model="form.anesthetistName"
              type="text"
              placeholder="Nombre del anestesista"
            />
          </div>
        </div>
      </div>

      <!-- SECCIÓN 4: DOCUMENTACIÓN -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">
          Documentación del procedimiento
        </h3>

        <div class="form-grid">
          <div class="full-width">
            <FileUploader
              v-model="form.photos"
              accept="image/*"
              :max-files="10"
              :max-size-m-b="10"
              label="Fotografías del procedimiento"
              help-text="Opcional. Máximo 10 imágenes, 10MB cada una."
            />
          </div>
        </div>
      </div>

      <!-- SECCIÓN 5: SEGUIMIENTO -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">
          Seguimiento postoperatorio
        </h3>

        <div class="form-grid">
          <div class="checkbox-govco full-width">
            <input
              id="autoFollowups"
              v-model="form.autoFollowups"
              type="checkbox"
            />
            <label for="autoFollowups">
              Programar controles postoperatorios automáticos
            </label>
          </div>

          <div class="entradas-de-texto-govco">
            <label for="followupCount">Número de controles</label>
            <input
              id="followupCount"
              v-model.number="form.followupCount"
              type="number"
              min="1"
              :disabled="!form.autoFollowups"
            />
          </div>

          <div class="entradas-de-texto-govco">
            <label for="followupInterval">
              Intervalo entre controles (días)
            </label>
            <input
              id="followupInterval"
              v-model.number="form.followupIntervalDays"
              type="number"
              min="1"
              :disabled="!form.autoFollowups"
            />
          </div>

          <div class="entradas-de-texto-govco">
            <label for="firstFollowup">
              Primer control postoperatorio
            </label>
            <input
              id="firstFollowup"
              v-model="form.firstFollowupDate"
              type="date"
              :disabled="!form.autoFollowups"
            />
          </div>

          <div class="checkbox-govco full-width">
            <input
              id="notifyAdopter"
              v-model="form.notifyAdopter"
              type="checkbox"
            />
            <label for="notifyAdopter">
              Enviar notificaciones de seguimiento al adoptante
            </label>
          </div>
        </div>
      </div>

      <!-- ACCIONES -->
      <div class="form-actions">
        <button
          type="button"
          class="govco-btn govco-bg-concrete"
          @click="resetForm"
        >
          Cancelar
        </button>
        <button type="submit" class="govco-btn govco-bg-elf-green">
          Guardar cirugía
        </button>
      </div>
    </form>
  </section>
</template>

<script setup>
import { reactive, ref, onMounted, watch, nextTick } from 'vue';
import FileUploader from '../common/FileUploader.vue';
import { useVeterinaryStore } from '@/stores/veterinary';
import { useAnimalsStore } from '@/stores/animals';
import animalService from '@/services/animalService';

const emit = defineEmits(['cirugia-saved', 'cancel']);
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
const loadingData = ref(true);

// Datos desde API
const animals = ref([]);
const veterinarians = ref([]);

// Función para reinicializar componentes GOV.CO
function initGovCoComponents() {
  console.log('🔄 Inicializando componentes GOV.CO...');

  nextTick(() => {
    // Intentar con window.GOVCo
    if (window.GOVCo?.init) {
      const dropdowns = document.querySelectorAll('.surgery-form .desplegable-govco');
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
  console.log('🔄 SurgeryForm: Cargando datos iniciales...');

  try {
    // Cargar animales
    console.log('📦 Cargando animales...');
    let animalsData = [];

    // Intentar primero con el store
    try {
      await animalsStore.fetchAnimals();
      animalsData = animalsStore.animals || [];
      console.log('✅ Animales desde store:', animalsData.length);
    } catch (storeError) {
      console.warn('⚠️ Error con store, intentando servicio directo:', storeError);
      // Fallback al servicio directo
      const animalsResponse = await animalService.getAll();
      animalsData = animalsResponse?.data?.data || animalsResponse?.data || [];
      console.log('✅ Animales desde servicio:', animalsData.length);
    }

    animals.value = animalsData.map(animal => ({
      id: animal.id,
      name: animal.nombre || 'Sin nombre',
      microchip: animal.codigo_unico || animal.codigo_chip || 'Sin chip',
      historialClinicoId: animal.historial_clinico?.id || animal.historial_clinico_id
    }));

    console.log('✅ Animales procesados:', animals.value.length, animals.value);

    // Cargar veterinarios
    console.log('📦 Cargando veterinarios...');
    await veterinaryStore.fetchVeterinarios();
    const vetsData = veterinaryStore.veterinarios || [];
    console.log('✅ Veterinarios desde store:', vetsData.length);

    veterinarians.value = vetsData.map(vet => ({
      id: vet.id,
      name: vet.nombre_completo ||
            `${vet.usuario?.nombres || ''} ${vet.usuario?.apellidos || ''}`.trim() ||
            vet.nombre ||
            'Veterinario',
      license: vet.tarjeta_profesional || vet.licencia || 'N/A'
    }));

    console.log('✅ Veterinarios procesados:', veterinarians.value.length, veterinarians.value);

    // Pre-seleccionar si vienen props
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
    console.log('✅ Carga de datos completada');
  }
}

const form = reactive({
  animalId: '',
  historialClinicoId: '',
  veterinarianId: '',
  surgeryType: '',
  urgency: 'programada',
  surgeryDateTime: '',
  durationMinutes: null,
  anesthesia: '',
  findings: '',
  assistantName: '',
  anesthetistName: '',
  photos: [],
  autoFollowups: true,
  followupCount: 2,
  followupIntervalDays: 7,
  firstFollowupDate: '',
  notifyAdopter: true
});

const errors = reactive({
  animalId: '',
  veterinarianId: '',
  surgeryType: '',
  surgeryDateTime: '',
  durationMinutes: '',
  anesthesia: '',
  findings: ''
});

function resetForm() {
  Object.assign(form, {
    animalId: '',
    historialClinicoId: '',
    veterinarianId: '',
    surgeryType: '',
    urgency: 'programada',
    surgeryDateTime: '',
    durationMinutes: null,
    anesthesia: '',
    findings: '',
    assistantName: '',
    anesthetistName: '',
    photos: [],
    autoFollowups: true,
    followupCount: 2,
    followupIntervalDays: 7,
    firstFollowupDate: '',
    notifyAdopter: true
  });

  Object.keys(errors).forEach(k => (errors[k] = ''));
  emit('cancel');
}

function validate() {
  Object.keys(errors).forEach(k => (errors[k] = ''));
  let isValid = true;

  if (!form.animalId) {
    errors.animalId = 'Debe seleccionar un animal';
    isValid = false;
  }
  if (!form.veterinarianId) {
    errors.veterinarianId = 'Debe seleccionar veterinario responsable';
    isValid = false;
  }
  if (!form.surgeryType.trim()) {
    errors.surgeryType = 'Campo requerido';
    isValid = false;
  }
  if (!form.surgeryDateTime) {
    errors.surgeryDateTime = 'Debe indicar fecha y hora';
    isValid = false;
  }
  if (!form.durationMinutes || form.durationMinutes <= 0) {
    errors.durationMinutes = 'Duración inválida';
    isValid = false;
  }
  if (!form.anesthesia.trim()) {
    errors.anesthesia = 'Campo requerido';
    isValid = false;
  }
  if (!form.findings.trim()) {
    errors.findings = 'Campo requerido';
    isValid = false;
  }

  return isValid;
}

async function onSubmit() {
  if (!validate()) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  isSubmitting.value = true;

  try {
    // Obtener historial_clinico_id del animal seleccionado
    let historialId = form.historialClinicoId;
    if (!historialId) {
      const selectedAnimal = animals.value.find(a => a.id === form.animalId);
      historialId = selectedAnimal?.historialClinicoId;
    }

    // Separar fecha y hora del datetime-local
    const dateTimeParts = form.surgeryDateTime.split('T');
    const fechaProgramada = dateTimeParts[0];
    const horaProgramada = dateTimeParts[1] || '00:00';

    // Preparar datos para el backend
    const cirugiaData = {
      historial_clinico_id: historialId,
      veterinario_id: form.veterinarianId,
      tipo_cirugia: form.surgeryType,
      descripcion: form.findings,
      fecha_programada: fechaProgramada,
      hora_programada: horaProgramada,
      duracion_minutos: form.durationMinutes,
      anestesia_utilizada: form.anesthesia,
      tipo_procedimiento: form.urgency,
      ayudante: form.assistantName || null,
      anestesista: form.anesthetistName || null,
      estado: 'realizada',
      resultado: 'exitoso',
      observaciones: form.findings,
      // Seguimiento postoperatorio
      programar_controles: form.autoFollowups,
      cantidad_controles: form.followupCount,
      intervalo_controles_dias: form.followupIntervalDays,
      fecha_primer_control: form.firstFollowupDate || null,
      notificar_adoptante: form.notifyAdopter
    };

    console.log('Guardando cirugía:', cirugiaData);

    // Guardar cirugía en backend
    await veterinaryStore.crearCirugia(cirugiaData);

    alert('Cirugía registrada exitosamente');
    emit('cirugia-saved', cirugiaData);
    resetForm();
  } catch (error) {
    console.error('Error al registrar cirugía:', error);
    alert(error.response?.data?.message || 'Error al registrar la cirugía');
  } finally {
    isSubmitting.value = false;
  }
}

// Observar cambios en el animalId de props
watch(() => props.animalId, async (newId) => {
  if (newId && animals.value.length > 0) {
    console.log('🔄 Animal ID changed:', newId);
    form.animalId = newId;
    const selectedAnimal = animals.value.find(a => a.id === newId);
    if (selectedAnimal?.historialClinicoId) {
      form.historialClinicoId = selectedAnimal.historialClinicoId;
    }

    await nextTick();
    initGovCoComponents();
  }
});

// Observar cuando se cargan los animales para reinicializar GOV.CO
watch(() => animals.value.length, async (newLength) => {
  if (newLength > 0) {
    console.log('📦 Animales actualizados, reinicializando GOV.CO...');
    await nextTick();
    setTimeout(() => {
      initGovCoComponents();
    }, 100);
  }
});

onMounted(async () => {
  console.log('📍 SurgeryForm mounted');
  console.log('📍 Props:', { animalId: props.animalId, historialClinicoId: props.historialClinicoId });

  // Cargar datos iniciales
  await loadInitialData();
});
</script>

<style scoped>
.surgery-form {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  background: #f5f7fb;
}
.form-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #3366cc;
}
.form-section {
  background: #fff;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  overflow: visible;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}
.section-title {
  margin: 0;
  padding: 1rem 1.5rem;
  background: #e8f0fe;
  color: #3366cc;
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
.entradas-de-texto-govco textarea,
.desplegable-govco select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d0d0d0;
  border-radius: 4px;
  font-size: 1rem;
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
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
}
.govco-btn {
  padding: 0.75rem 2rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  color: #fff;
}
.govco-bg-concrete {
  background-color: #737373;
}
.govco-bg-elf-green {
  background-color: #069169;
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

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  .full-width {
    grid-column: 1 / 2;
  }
}
</style>
