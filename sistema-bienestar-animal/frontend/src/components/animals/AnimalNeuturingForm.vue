<template>
  <section class="neutering-section">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Registro de esterilización</h2>
      <p class="text2-tipografia-govco">Complete la información de esterilización del animal</p>
    </div>

    <form ref="formEl" @submit.prevent="onSubmit" novalidate>
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Datos de esterilización</h3>
        
        <div class="form-grid">
          <!-- Animal ID (para vincular) -->
          <div class="entradas-de-texto-govco">
            <label for="animalId">ID/Microchip del animal*</label>
            <div class="input-with-button">
              <input
                type="text"
                id="animalId"
                v-model="form.animalId"
                placeholder="MC123456789 o código del animal"
                @blur="buscarAnimal"
                @keyup.enter="buscarAnimal"
              />
              <ButtonGovCo
                type="button"
                variant="fill"
                label="🔍"
                width="50px"
                height="44px"
                :disabled="buscandoAnimal"
                @click="buscarAnimal"
              />
            </div>
            <span v-if="errors.animalId" class="error-text">{{ errors.animalId }}</span>
            <span v-if="animalEncontrado" class="success-text">
              ✅ {{ animalEncontrado.animal?.nombre || 'Animal' }} - {{ animalEncontrado.animal?.especie || '' }}
            </span>
          </div>

          <!-- Fecha de esterilización -->
          <CalendarioGovco
            id="neut-date-calendar"
            input-id="neutDate"
            v-model="form.neuteringDate"
            label="Fecha de esterilización"
            :required="true"
            :error="!!errors.neuteringDate"
            :alert-text="errors.neuteringDate"
            placeholder="DD/MM/AAAA"
            view-days="true"
          />

          <!-- Veterinario responsable -->
          <DesplegableGovco
            id="vet-dropdown"
            v-model="form.veterinario_id"
            label="Veterinario responsable"
            :options="veterinariosOptions"
            placeholder="Seleccione un veterinario"
            :required="true"
            :error="!!errors.veterinario_id"
            :alert-text="errors.veterinario_id"
            width="100%"
          />

          <!-- Certificado usando FileUploader -->
          <div class="full-width">
            <FileUploader
              v-model="form.certificateFiles"
              accept="application/pdf,image/jpeg,image/jpg,image/png"
              :max-files="1"
              :max-size-m-b="2"
              label="Certificado digital"
              help-text="PDF o imagen. Peso máximo: 2 MB"
              :required="true"
              @update:modelValue="onCertificateChange"
            />
            <span v-if="errors.neuteringCertificate" class="error-text">{{ errors.neuteringCertificate }}</span>
          </div>

          <!-- Notas adicionales (opcional) -->
          <InputGovCo
            id="notes"
            v-model="form.notes"
            label="Notas adicionales (opcional)"
            type="text"
            placeholder="Observaciones sobre el procedimiento..."
            class="full-width"
          />
        </div>
      </div>

      <!-- BOTONES -->
      <div class="form-actions">
        <ButtonGovCo
          type="button"
          variant="secondary"
          label="Limpiar formulario"
          width="auto"
          height="44px"
          :disabled="isSubmitting"
          @click="resetForm"
        />
        <ButtonGovCo
          type="submit"
          variant="primary"
          :label="isSubmitting ? 'Registrando...' : 'Registrar esterilización'"
          width="auto"
          height="44px"
          :disabled="isSubmitting"
        />
      </div>
    </form>
  </section>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import { useVeterinaryStore } from '@/stores/veterinary';
import api from '@/services/api';
import ButtonGovCo from '../common/ButtonGovCo.vue';
import CalendarioGovco from '../common/CalendarioGovco.vue';
import DesplegableGovco from '../common/DesplegableGovco.vue';
import InputGovCo from '../common/InputGovCo.vue';
import FileUploader from '../common/FileUploader.vue';

const emit = defineEmits(['sterilizationRegistered']);

const veterinaryStore = useVeterinaryStore();
const formEl = ref(null);
const isSubmitting = ref(false);
const veterinarios = ref([]);
const animalEncontrado = ref(null);
const buscandoAnimal = ref(false);

const form = reactive({
  animalId: '',
  neuteringDate: '',
  veterinario_id: '',
  certificateFiles: [],
  notes: ''
});

const errors = reactive({
  animalId: '',
  neuteringDate: '',
  veterinario_id: '',
  neuteringCertificate: ''
});

// Veterinarios temporales del seeder (hasta que haya data en BD)
const veterinariosTemporales = [
  {
    id: 'temp-1',
    nombres: 'Ana Maria',
    apellidos: 'Garcia Sanchez',
    email: 'ana.garcia@bienestaranimal.gov.co'
  },
  {
    id: 'temp-2',
    nombres: 'Pedro',
    apellidos: 'Ramirez Castillo',
    email: 'pedro.ramirez@bienestaranimal.gov.co'
  }
];

// Computed para opciones de veterinarios
const veterinariosOptions = computed(() => {
  // Si hay veterinarios de la BD, usarlos
  if (veterinarios.value.length > 0) {
    return veterinarios.value.map(vet => ({
      value: vet.id,
      text: `${vet.usuario?.nombres || vet.nombre || 'Veterinario'} ${vet.usuario?.apellidos || ''} ${vet.tarjeta_profesional ? `(TP: ${vet.tarjeta_profesional})` : ''}`.trim()
    }));
  }
  
  // Si no, usar veterinarios temporales
  return veterinariosTemporales.map(vet => ({
    value: vet.id,
    text: `${vet.nombres} ${vet.apellidos}`.trim()
  }));
});

// Cargar veterinarios al montar
async function loadVeterinarios() {
  try {
    await veterinaryStore.fetchVeterinarios();
    veterinarios.value = veterinaryStore.veterinarios;
    
    // Si no hay veterinarios en la BD, usar los temporales
    if (!veterinarios.value || veterinarios.value.length === 0) {
      console.log('ℹ️ Usando veterinarios temporales del seeder');
      veterinarios.value = veterinariosTemporales;
    }
  } catch (err) {
    console.warn('⚠️ No se pudieron cargar veterinarios de la BD, usando temporales:', err);
    veterinarios.value = veterinariosTemporales;
  }
}

// Buscar animal por microchip/código
async function buscarAnimal() {
  if (!form.animalId || form.animalId.trim().length < 3) {
    animalEncontrado.value = null;
    return;
  }

  buscandoAnimal.value = true;
  errors.animalId = '';

  try {
    // Intentar buscar por historial clínico primero
    const responseChip = await api.get(`/historial-clinico/buscar-chip/${form.animalId}`);
    if (responseChip.data?.data) {
      animalEncontrado.value = responseChip.data.data;
      console.log('✅ Animal encontrado por chip:', animalEncontrado.value);
      return;
    }
  } catch (err) {
    console.log('No encontrado por chip, buscando en animales...');
  }

  // Si no se encuentra por chip, buscar en la lista general de animales
  try {
    const responseAnimales = await api.get('/animals', { 
      params: { search: form.animalId } 
    });
    
    const animales = responseAnimales.data?.data?.data || responseAnimales.data?.data || [];
    
    if (animales.length > 0) {
      const animal = animales[0];
      
      // Crear estructura compatible
      animalEncontrado.value = {
        animal: animal,
        historial_clinico_id: animal.historial_clinico?.id || null,
        id: animal.historial_clinico?.id || null
      };
      
      console.log('✅ Animal encontrado:', animalEncontrado.value);
      return;
    }
  } catch (err2) {
    console.error('Error buscando animal:', err2);
  } finally {
    buscandoAnimal.value = false;
  }

  // No se encontró
  animalEncontrado.value = null;
  errors.animalId = 'Animal no encontrado';
  buscandoAnimal.value = false;
}

// Handler para cuando cambia el certificado
function onCertificateChange(files) {
  errors.neuteringCertificate = '';
  console.log('📎 Certificado seleccionado:', files);
}

onMounted(() => {
  loadVeterinarios();
});

function validate() {
  // Limpiar errores
  Object.keys(errors).forEach(k => errors[k] = '');

  let isValid = true;

  // Validar animal
  if (!form.animalId || !form.animalId.trim()) {
    errors.animalId = 'Campo requerido';
    isValid = false;
  } else if (!animalEncontrado.value) {
    errors.animalId = 'Debe buscar y seleccionar un animal válido';
    isValid = false;
  }

  // Validar fecha
  if (!form.neuteringDate || form.neuteringDate.trim() === '') {
    errors.neuteringDate = 'Campo requerido';
    isValid = false;
  }

  // Validar veterinario
  if (!form.veterinario_id) {
    errors.veterinario_id = 'Seleccione un veterinario';
    isValid = false;
  }

  // Validar certificado
  if (!form.certificateFiles || form.certificateFiles.length === 0) {
    errors.neuteringCertificate = 'Debe adjuntar un certificado';
    isValid = false;
  }

  return isValid;
}

function resetForm() {
  // Limpiar formulario
  form.animalId = '';
  form.neuteringDate = '';
  form.veterinario_id = '';
  form.certificateFiles = [];
  form.notes = '';
  
  // Limpiar errores
  Object.keys(errors).forEach(k => errors[k] = '');
  
  // Limpiar animal encontrado
  animalEncontrado.value = null;
}

async function onSubmit() {
  if (!validate()) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  if (!animalEncontrado.value) {
    errors.animalId = 'Debe buscar y seleccionar un animal válido';
    return;
  }

  // Obtener ID del historial clínico
  const historialClinicoId = animalEncontrado.value.historial_clinico_id ||
    animalEncontrado.value.animal?.historial_clinico?.id ||
    animalEncontrado.value.id;

  if (!historialClinicoId) {
    errors.animalId = 'El animal no tiene historial clínico asociado';
    return;
  }

  console.log('📋 Historial clínico ID:', historialClinicoId);

  isSubmitting.value = true;

  try {
    // Convertir fecha si está en formato DD/MM/YYYY
    let fechaCirugia = form.neuteringDate;
    if (fechaCirugia && fechaCirugia.includes('/')) {
      const parts = fechaCirugia.split('/');
      if (parts.length === 3) {
        fechaCirugia = `${parts[2]}-${parts[1]}-${parts[0]}`;
      }
    }

    // Preparar datos de la cirugía
    const cirugiaData = {
      historial_clinico_id: historialClinicoId,
      veterinario_id: form.veterinario_id || null,
      fecha_cirugia: fechaCirugia,
      tipo_cirugia: 'esterilizacion',
      descripcion: `Esterilización realizada. ${form.notes || ''}`.trim(),
      resultado: 'exitosa',
      notas_postoperatorias: form.notes || null
    };

    console.log('📤 Enviando datos de cirugía:', cirugiaData);

    // Registrar la cirugía
    const response = await veterinaryStore.crearCirugia(cirugiaData);
    
    console.log('✅ Cirugía registrada:', response);

    // Ahora actualizar el animal para marcar esterilización = true
    const animalId = animalEncontrado.value.animal?.id;
    
    if (animalId) {
      try {
        await api.put(`/animals/${animalId}`, {
          esterilizacion: true,
          fecha_esterilizacion: fechaCirugia,
          veterinario_esterilizacion: form.veterinario_id
        });
        console.log('✅ Animal actualizado con estado de esterilización');
      } catch (updateError) {
        console.warn('⚠️ No se pudo actualizar el estado del animal:', updateError);
      }
    }

    // Mostrar mensaje de éxito
    if (window.$toast) {
      window.$toast.success(
        '¡Esterilización registrada!',
        'El procedimiento ha sido registrado exitosamente en el historial médico del animal.'
      );
    } else {
      alert('✅ Esterilización registrada exitosamente');
    }

    emit('sterilizationRegistered', response);
    resetForm();

  } catch (error) {
    console.error('❌ Error al registrar esterilización:', error);

    let errorMessage = 'No se pudo registrar la esterilización. Intente nuevamente.';

    if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.response?.data?.errors) {
      const firstError = Object.values(error.response.data.errors)[0];
      errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
    }

    if (window.$toast) {
      window.$toast.error('Error al registrar', errorMessage);
    } else {
      alert(`❌ Error: ${errorMessage}`);
    }
  } finally {
    isSubmitting.value = false;
  }
}
</script>

<style scoped>
.neutering-section { 
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
  grid-template-columns: repeat(3, minmax(0, 1fr)); 
  column-gap: 2rem; 
  row-gap: 1.5rem; 
  padding: 1.5rem; 
}

.form-grid > div { 
  display: flex; 
  flex-direction: column;
}

.full-width { 
  grid-column: 1 / 4; 
}

.entradas-de-texto-govco { 
  width: 100%; 
}

.entradas-de-texto-govco label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
  font-size: 14px;
}

.entradas-de-texto-govco input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  line-height: 1.5;
  box-sizing: border-box;
  height: 44px;
}

.entradas-de-texto-govco input:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.1);
}

.error-text {
  display: block;
  color: #b00020;
  font-size: 0.85rem;
  margin-top: 0.5rem;
  font-weight: 500;
}

.success-text {
  display: block;
  color: #069169;
  font-size: 0.85rem;
  margin-top: 0.5rem;
  font-weight: 500;
}

.input-with-button {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.input-with-button input {
  flex: 1;
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

@media (max-width: 992px) {
  .form-grid { 
    grid-template-columns: repeat(2, minmax(0, 1fr)); 
  }
  .full-width { 
    grid-column: 1 / 3; 
  }
}

@media (max-width: 576px) {
  .neutering-section { 
    padding: 1rem; 
  }
  .form-grid { 
    grid-template-columns: 1fr; 
    padding: 1rem; 
  }
  .full-width { 
    grid-column: 1 / 2; 
  }
  .form-actions { 
    flex-direction: column; 
  }
}
</style>