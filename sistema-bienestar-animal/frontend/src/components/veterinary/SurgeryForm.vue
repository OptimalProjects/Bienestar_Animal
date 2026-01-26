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

    <form v-else @submit.prevent="onSubmit" novalidate>
      <!-- SECCIÓN 1: DATOS GENERALES -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Datos generales</h3>

        <!-- ALERTA DE ESTERILIZACIÓN/CASTRACIÓN -->
        <div v-if="form.tipoCirugia === 'esterilizacion' || form.tipoCirugia === 'castracion'" class="alert-section">
          <div class="alert-warning">
            <span class="alert-icon">⚠️</span>
            <div class="alert-content">
              <strong>Recordatorio importante:</strong>
              <p>Después de registrar esta cirugía, deberá descargar el certificado de {{ form.tipoCirugia === 'esterilizacion' ? 'esterilización' : 'castración' }} en el <strong>Generador de Certificados</strong> y adjuntarlo en el formulario de <strong>Adjuntar Certificado</strong>.</p>
            </div>
          </div>
        </div>

        <div class="form-grid">
          <!-- Animal -->
          <DesplegableGovco
            id="animal-select"
            v-model="form.animalId"
            label="Animal"
            placeholder="Seleccione un animal"
            :options="animalOptions"
            :required="true"
            :error="!!errors.animalId"
            :alert-text="errors.animalId"
          />

          <!-- Veterinario cirujano -->
          <DesplegableGovco
            id="cirujano-select"
            v-model="form.cirujanoId"
            label="Veterinario cirujano"
            placeholder="Seleccione veterinario cirujano"
            :options="veterinarianOptions"
            :required="true"
            :error="!!errors.cirujanoId"
            :alert-text="errors.cirujanoId"
          />

          <!-- Tipo cirugía -->
          <DesplegableGovco
            id="tipo-cirugia-select"
            v-model="form.tipoCirugia"
            label="Tipo de cirugía"
            placeholder="Seleccione tipo de cirugía"
            :options="tiposCirugia"
            :required="true"
            :error="!!errors.tipoCirugia"
            :alert-text="errors.tipoCirugia"
          />

          <!-- Estado -->
          <DesplegableGovco
            id="estado-select"
            v-model="form.estado"
            label="Estado de la cirugía"
            :options="estadosOptions"
            :required="true"
          />

          <!-- Fecha programada -->
          <CalendarioGovco
            id="fecha-programada"
            input-id="fecha-programada-input"
            v-model="form.fechaProgramada"
            label="Fecha programada"
            placeholder="Seleccione fecha"
            :required="true"
            :error="!!errors.fechaProgramada"
            :alert-text="errors.fechaProgramada"
          />

          <!-- Fecha realización (si ya se realizó) -->
          <CalendarioGovco
            v-if="form.estado === 'realizada'"
            id="fecha-realizacion"
            input-id="fecha-realizacion-input"
            v-model="form.fechaRealizacion"
            label="Fecha de realización"
            placeholder="Seleccione fecha"
            :max="new Date().toISOString().split('T')[0]"
          />

          <!-- Duración -->
          <InputGovCo
            id="duracion-input"
            v-model="form.duracionMinutos"
            label="Duración (minutos)"
            type="number"
            placeholder="90"
            :min="0"
            :required="true"
            :error="!!errors.duracionMinutos"
            :alert-text="errors.duracionMinutos"
          />

          <!-- Resultado (si ya se realizó) -->
          <DesplegableGovco
            v-if="form.estado === 'realizada'"
            id="resultado-select"
            v-model="form.resultado"
            label="Resultado de la cirugía"
            :options="resultadosOptions"
          />
        </div>
      </div>

      <!-- SECCIÓN 2: DESCRIPCIÓN Y ANESTESIA -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">
          Descripción del procedimiento
        </h3>

        <div class="form-grid">
          <div class="full-width">
            <InputGovCo
              id="descripcion-input"
              v-model="form.descripcion"
              label="Descripción del procedimiento"
              type="textarea"
              placeholder="Describa el procedimiento quirúrgico realizado..."
              :required="true"
              :error="!!errors.descripcion"
              :alert-text="errors.descripcion"
            />
          </div>

          <div class="full-width">
            <InputGovCo
              id="anestesia-input"
              v-model="form.tipoAnestesia"
              label="Tipo de anestesia utilizada"
              type="textarea"
              placeholder="Protocolo anestésico, medicamentos, dosis..."
              :required="form.estado === 'realizada'"
              :error="!!errors.tipoAnestesia"
              :alert-text="errors.tipoAnestesia"
            />
          </div>
        </div>
      </div>

      <!-- SECCIÓN 3: EQUIPO QUIRÚRGICO -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">
          Equipo quirúrgico
        </h3>

        <div class="form-grid">
          <!-- Anestesiólogo -->
          <DesplegableGovco
            id="anestesiologo-select"
            v-model="form.anestesiologoId"
            label="Anestesiólogo"
            placeholder="Seleccione anestesiólogo"
            :options="veterinarianOptions"
          />

          <!-- Asistentes (campo de texto por ahora, luego se puede mejorar a multi-select) -->
          <InputGovCo
            id="asistentes-input"
            v-model="form.asistentesTexto"
            label="Asistentes"
            placeholder="Nombres de los asistentes (separados por coma)"
            help-text="Ejemplo: Dr. Juan Pérez, Aux. María López"
          />
        </div>
      </div>

      <!-- SECCIÓN 4: RESULTADO Y OBSERVACIONES (si ya se realizó) -->
      <div v-if="form.estado === 'realizada'" class="form-section">
        <h3 class="h5-tipografia-govco section-title">
          Resultado y observaciones
        </h3>

        <div class="form-grid">
          <div class="full-width">
            <InputGovCo
              id="complicaciones-input"
              v-model="form.complicaciones"
              label="Complicaciones"
              type="textarea"
              placeholder="Describa cualquier complicación presentada durante el procedimiento..."
            />
          </div>

          <div class="full-width">
            <InputGovCo
              id="postoperatorio-input"
              v-model="form.postoperatorio"
              label="Indicaciones postoperatorias"
              type="textarea"
              placeholder="Cuidados postoperatorios, medicación, restricciones..."
            />
          </div>

          <!-- Estado del animal -->
          <DesplegableGovco
            id="estado-animal-select"
            v-model="form.estadoAnimal"
            label="Estado del animal"
            :options="estadosAnimalOptions"
          />

          <!-- Seguimiento requerido -->
          <div class="checkbox-wrapper full-width">
            <input
              id="seguimiento-checkbox"
              v-model="form.seguimientoRequerido"
              type="checkbox"
            />
            <label for="seguimiento-checkbox">
              Requiere seguimiento postoperatorio
            </label>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 5: DOCUMENTACIÓN -->
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
              :multiple="true"
              label="Fotografías del procedimiento"
              help-text="Opcional. Máximo 10 imágenes, 10MB cada una."
            />
          </div>
        </div>
      </div>

      <!-- ACCIONES -->
      <div class="form-actions">
        <ButtonGovCo
          label="Cancelar"
          variant="secondary"
          @click="resetForm"
        />
        <ButtonGovCo
          type="submit"
          label="Guardar cirugía"
          variant="primary"
          :loading="isSubmitting"
          :disabled="isSubmitting"
        />
      </div>
    </form>
  </section>
</template>

<script setup>
import { reactive, ref, onMounted, computed, watch } from 'vue';
import ButtonGovCo from '../common/ButtonGovCo.vue';
import CalendarioGovco from '../common/CalendarioGovco.vue';
import DesplegableGovco from '../common/DesplegableGovco.vue';
import FileUploader from '../common/FileUploader.vue';
import InputGovCo from '../common/InputGovCo.vue';
import { useVeterinaryStore } from '@/stores/veterinary';
import { useAnimalsStore } from '@/stores/animals';
import animalService from '@/services/animalService';
import api from '@/services/api';

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
const isSubmitting = ref(false);
const loadingData = ref(true);

// Datos desde API
const animals = ref([]);
const veterinarians = ref([]);

// Opciones para desplegables
const tiposCirugia = [
  { value: 'esterilizacion', text: 'Esterilización' },
  { value: 'castracion', text: 'Castración' },
  { value: 'ortopedica', text: 'Ortopédica' },
  { value: 'abdominal', text: 'Abdominal' },
  { value: 'oftalmologica', text: 'Oftalmológica' },
  { value: 'dental', text: 'Dental' },
  { value: 'oncologica', text: 'Oncológica' },
  { value: 'emergencia', text: 'Emergencia' },
  { value: 'otra', text: 'Otra' }
];

const estadosOptions = [
  { value: 'programada', text: 'Programada' },
  { value: 'realizada', text: 'Realizada' },
  { value: 'cancelada', text: 'Cancelada' }
];

const resultadosOptions = [
  { value: 'exitosa', text: 'Exitosa' },
  { value: 'con_complicaciones', text: 'Con complicaciones' },
  { value: 'fallida', text: 'Fallida' }
];

const estadosAnimalOptions = [
  { value: 'en_tratamiento', text: 'En tratamiento' },
  { value: 'en_recuperacion', text: 'En recuperación' },
  { value: 'estable', text: 'Estable' }
];

const animalOptions = computed(() =>
  animals.value.map(animal => ({
    value: animal.id,
    text: `${animal.name} (${animal.microchip})`
  }))
);

const veterinarianOptions = computed(() =>
  veterinarians.value.map(vet => ({
    value: vet.id,
    text: `${vet.name} (${vet.license})`
  }))
);

// Formulario alineado con backend
const form = reactive({
  // Campos básicos
  animalId: '',
  historialClinicoId: '',
  cirujanoId: '',
  anestesiologoId: '',
  tipoCirugia: '',
  descripcion: '',
  
  // Fechas
  fechaProgramada: '',
  fechaRealizacion: '',
  
  // Detalles quirúrgicos
  duracionMinutos: null,
  tipoAnestesia: '',
  asistentesTexto: '', // Se convertirá a array antes de enviar
  
  // Resultado
  estado: 'programada',
  resultado: 'exitosa',
  complicaciones: '',
  postoperatorio: '',
  estadoAnimal: '',
  seguimientoRequerido: false,
  
  // Documentación
  photos: []
});

const errors = reactive({
  animalId: '',
  cirujanoId: '',
  tipoCirugia: '',
  descripcion: '',
  fechaProgramada: '',
  duracionMinutos: '',
  tipoAnestesia: ''
});

// Cargar datos iniciales
async function loadInitialData() {
  loadingData.value = true;
  console.log('📄 SurgeryForm: Cargando datos iniciales...');

  try {
    // Cargar animales
    console.log('📦 Cargando animales...');
    let animalsData = [];

    try {
      await animalsStore.fetchAnimals();
      animalsData = animalsStore.animals || [];
      console.log('✅ Animales desde store:', animalsData.length);
    } catch (storeError) {
      console.warn('⚠️ Error con store, intentando servicio directo:', storeError);
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

    console.log('✅ Animales procesados:', animals.value.length);

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

    console.log('✅ Veterinarios procesados:', veterinarians.value.length);

    // Pre-seleccionar si vienen props
    if (props.animalId) {
      form.animalId = props.animalId;
      console.log('🎯 Animal pre-seleccionado:', props.animalId);
    }
    if (props.historialClinicoId) {
      form.historialClinicoId = props.historialClinicoId;
    }

  } catch (error) {
    console.error('❌ Error cargando datos iniciales:', error);
    alert('Error al cargar datos. Por favor recargue la página.');
  } finally {
    loadingData.value = false;
    console.log('✅ Carga de datos completada');
  }
}

function resetForm() {
  Object.assign(form, {
    animalId: '',
    historialClinicoId: '',
    cirujanoId: '',
    anestesiologoId: '',
    tipoCirugia: '',
    descripcion: '',
    fechaProgramada: '',
    fechaRealizacion: '',
    duracionMinutos: null,
    tipoAnestesia: '',
    asistentesTexto: '',
    estado: 'programada',
    resultado: 'exitosa',
    complicaciones: '',
    postoperatorio: '',
    estadoAnimal: '',
    seguimientoRequerido: false,
    photos: []
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
  if (!form.cirujanoId) {
    errors.cirujanoId = 'Debe seleccionar veterinario cirujano';
    isValid = false;
  }
  if (!form.tipoCirugia) {
    errors.tipoCirugia = 'Debe seleccionar tipo de cirugía';
    isValid = false;
  }
  if (!form.descripcion.trim()) {
    errors.descripcion = 'La descripción es requerida';
    isValid = false;
  }
  if (!form.fechaProgramada) {
    errors.fechaProgramada = 'Debe indicar fecha programada';
    isValid = false;
  }
  if (!form.duracionMinutos || form.duracionMinutos <= 0) {
    errors.duracionMinutos = 'Duración inválida';
    isValid = false;
  }
  if (form.estado === 'realizada' && !form.tipoAnestesia.trim()) {
    errors.tipoAnestesia = 'Debe especificar el tipo de anestesia utilizada';
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

    // Convertir asistentes de texto a array
    const asistentesArray = form.asistentesTexto
      ? form.asistentesTexto.split(',').map(a => a.trim()).filter(Boolean)
      : [];

    // Preparar datos alineados con backend y modelo
    const cirugiaData = {
      // Relaciones
      historial_clinico_id: historialId,
      cirujano_id: form.cirujanoId,
      anestesiologo_id: form.anestesiologoId || null,
      
      // Tipo y descripción
      tipo_cirugia: form.tipoCirugia,
      descripcion: form.descripcion,
      
      // Fechas
      fecha_programada: form.fechaProgramada,
      fecha_realizacion: form.fechaRealizacion || null,
      
      // Detalles quirúrgicos
      duracion: form.duracionMinutos,
      tipo_anestesia: form.tipoAnestesia || null,
      asistentes: asistentesArray,
      
      // Estado y resultado
      estado: form.estado,
      resultado: form.estado === 'realizada' ? form.resultado : null,
      complicaciones: form.complicaciones || null,
      postoperatorio: form.postoperatorio || null,
      seguimiento_requerido: form.seguimientoRequerido,
      
      // Estado del animal (solo si está realizada)
      estado_animal: form.estado === 'realizada' ? form.estadoAnimal : null
    };

    console.log('Guardando cirugía:', cirugiaData);

    // Guardar cirugía en backend
    await veterinaryStore.crearCirugia(cirugiaData);

    console.log('✅ Cirugía registrada exitosamente');

    // Si es esterilización o castración y está realizada, actualizar el animal
    if ((form.tipoCirugia === 'esterilizacion' || form.tipoCirugia === 'castracion') && 
        form.estado === 'realizada') {
      
      const animalId = form.animalId;
      const updateData = {
        esterilizacion: true,
        fecha_esterilizacion: form.fechaRealizacion || form.fechaProgramada
      };

      try {
        console.log('📝 Actualizando estado de esterilización del animal:', animalId);
        await api.put(`/animals/${animalId}`, updateData);
        console.log('✅ Animal actualizado: esterilizado');
      } catch (updateError) {
        console.warn('⚠️ Advertencia: No se pudo actualizar el estado del animal, pero la cirugía fue registrada:', updateError);
      }
    }

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
watch(() => props.animalId, (newId) => {
  if (newId && animals.value.length > 0) {
    console.log('🔄 Animal ID changed:', newId);
    form.animalId = newId;
    const selectedAnimal = animals.value.find(a => a.id === newId);
    if (selectedAnimal?.historialClinicoId) {
      form.historialClinicoId = selectedAnimal.historialClinicoId;
    }
  }
});

onMounted(async () => {
  console.log('🔍 SurgeryForm mounted');
  console.log('🔍 Props:', { animalId: props.animalId, historialClinicoId: props.historialClinicoId });

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

.checkbox-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: #f5f7fb;
  border-radius: 6px;
}

.checkbox-wrapper input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.checkbox-wrapper label {
  font-size: 0.95rem;
  font-weight: 500;
  color: #344054;
  cursor: pointer;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
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

.alert-section {
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.alert-warning {
  background: #fffbeb;
  border-left: 4px solid #f59e0b;
  padding: 1rem 1.5rem;
  border-radius: 6px;
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}

.alert-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
  min-width: 24px;
}

.alert-content {
  flex: 1;
}

.alert-content strong {
  display: block;
  color: #92400e;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.alert-content p {
  margin: 0;
  color: #78350f;
  font-size: 0.9rem;
  line-height: 1.5;
}

.alert-content strong strong {
  display: inline;
  color: #b45309;
  font-weight: 700;
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