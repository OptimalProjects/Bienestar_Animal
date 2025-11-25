<template>
  <section class="surgery-form">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Registro de Cirugía</h2>
      <p class="text2-tipografia-govco">
        Registre la información de la intervención quirúrgica.
      </p>
    </div>

    <form ref="formEl" @submit.prevent="onSubmit" novalidate>
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
import { reactive, ref, onMounted } from 'vue';
import FileUploader from '../common/FileUploader.vue';

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

const form = reactive({
  animalId: '',
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

  try {
    const payload = { ...form };

    console.log('Guardando cirugía:', payload);

    // TODO: Guardar cirugía en backend
    // await saveSurgery(payload);

    // TODO: Programar controles postoperatorios automáticos
    if (form.autoFollowups && form.firstFollowupDate) {
      console.log('Programar controles postoperatorios automáticos');
      // await schedulePostOpControls(payload);
    }

    // TODO: Enviar notificaciones de seguimiento al adoptante
    if (form.notifyAdopter) {
      console.log('Enviar notificaciones al adoptante');
      // await notifyAdopter(payload);
    }

    // TODO: Actualizar estado del animal a "en recuperación"
    // await updateAnimalStatus(form.animalId, 'EN_RECUPERACION');

    // TODO: Enviar evento de auditoría a sciaudit
    // await sendAuditEvent('surgery_registered', payload);

    alert('Cirugía registrada exitosamente');
    resetForm();
  } catch (error) {
    console.error('Error al registrar cirugía:', error);
    alert('Error al registrar la cirugía');
  }
}

onMounted(() => {
  // Inicializar componentes GOV.CO
  if (window.GOVCo?.init) {
    const dropdowns = document.querySelectorAll('.desplegable-govco');
    dropdowns.forEach(dd => {
      window.GOVCo.init(dd.parentElement);
    });
  }
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

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  .full-width {
    grid-column: 1 / 2;
  }
}
</style>
