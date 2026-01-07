<template>
  <section class="certificate-generator">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Generación de certificados</h2>
      <p class="text2-tipografia-govco">
        Genere certificados de vacunación, esterilización o salud general.
      </p>
    </div>

    <!-- Indicador de carga -->
    <div v-if="loadingData" class="loading-overlay">
      <div class="spinner"></div>
      <p>Cargando datos...</p>
    </div>

    <form v-else @submit.prevent="generateCertificate">
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Datos del certificado</h3>

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

          <!-- Tipo certificado -->
          <DesplegableGovco
            id="type-select"
            v-model="form.type"
            label="Tipo de certificado"
            placeholder="Seleccione tipo"
            :options="certificateTypes"
            :required="true"
            :error="!!errors.type"
            :alert-text="errors.type"
          />

          <!-- Veterinario -->
          <DesplegableGovco
            id="veterinarian-select"
            v-model="form.veterinarianId"
            label="Veterinario responsable"
            placeholder="Seleccione veterinario"
            :options="veterinarianOptions"
            :required="true"
            :error="!!errors.veterinarianId"
            :alert-text="errors.veterinarianId"
          />

          <!-- Observaciones -->
          <div class="full-width">
            <InputGovCo
              id="notes-input"
              v-model="form.notes"
              label="Observaciones adicionales"
              placeholder="Información adicional que desea incluir en el certificado."
              width="100%"
              help-text="Opcional: agregue detalles relevantes para el certificado"
            />
          </div>
        </div>
      </div>

      <div class="form-actions">
        <ButtonGovCo
          type="button"
          variant="secondary"
          @click="resetForm"
        >
          Limpiar
        </ButtonGovCo>
        
        <ButtonGovCo
          type="submit"
          variant="primary"
          :loading="loading"
          :disabled="loading"
        >
          Generar certificado PDF
        </ButtonGovCo>
      </div>
    </form>
  </section>
</template>

<script setup>
import { reactive, ref, onMounted, computed } from 'vue';
import { useVeterinaryStore } from '@/stores/veterinary';
import { useAnimalsStore } from '@/stores/animals';
import animalService from '@/services/animalService';
import DesplegableGovco from '../common/DesplegableGovco.vue';
import InputGovCo from '../common/InputGovCo.vue';
import ButtonGovCo from '../common/ButtonGovCo.vue';

const veterinaryStore = useVeterinaryStore();
const animalsStore = useAnimalsStore();

const loading = ref(false);
const loadingData = ref(true);
const animals = ref([]);
const veterinarians = ref([]);

const form = reactive({
  animalId: '',
  type: '',
  veterinarianId: '',
  notes: ''
});

const errors = reactive({
  animalId: '',
  type: '',
  veterinarianId: ''
});

// Opciones para los desplegables
const certificateTypes = [
  { value: 'vaccination', text: 'Vacunación' },
  { value: 'sterilization', text: 'Esterilización' },
  { value: 'health', text: 'Salud general' }
];

const animalOptions = computed(() => {
  return animals.value.map(animal => ({
    value: animal.id,
    text: `${animal.name} (${animal.microchip})`
  }));
});

const veterinarianOptions = computed(() => {
  return veterinarians.value.map(vet => ({
    value: vet.id,
    text: `${vet.nombre_completo ?? `${vet.nombres} ${vet.apellidos}`} - Tarjeta Prof. ${vet.numero_tarjeta_profesional ?? 'N/A'}`
  }));
});

// Cargar datos iniciales
async function loadData() {
  loadingData.value = true;
  console.log('📄 CertificateGenerator: Cargando datos iniciales...');

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
      animalsData = animalsResponse?.data?.data || animalsResponse?.data || animalsResponse || [];
      console.log('✅ Animales desde servicio:', animalsData.length);
    }

    animals.value = (Array.isArray(animalsData) ? animalsData : []).map(animal => ({
      id: animal.id,
      name: animal.nombre || animal.name,
      microchip: animal.codigo_unico || animal.codigo_chip || 'Sin chip'
    }));
    console.log('✅ Animales procesados:', animals.value.length);

    // Cargar veterinarios
    console.log('📦 Cargando veterinarios...');
    try {
      const vetsData = await veterinaryStore.fetchVeterinarios();
      veterinarians.value = Array.isArray(vetsData) ? vetsData : [];

      if (veterinarians.value.length === 0) {
        console.warn('⚠️ No hay veterinarios registrados en BD');
        alert('No hay veterinarios registrados. Debes crear al menos uno para generar certificados.');
      }

      console.log('✅ Veterinarios cargados:', veterinarians.value.length);
    } catch (vetsError) {
      console.error('❌ Error cargando veterinarios:', vetsError);
      veterinarians.value = [];
    }

  } catch (error) {
    console.error('❌ Error cargando datos:', error);
    alert('Error al cargar datos. Por favor recargue la página.');
  } finally {
    loadingData.value = false;
    console.log('✅ CertificateGenerator: Carga de datos completada');
  }
}

function validateForm() {
  // Limpiar errores previos
  Object.keys(errors).forEach(key => errors[key] = '');
  
  let isValid = true;

  if (!form.animalId) {
    errors.animalId = 'Debe seleccionar un animal';
    isValid = false;
  }

  if (!form.type) {
    errors.type = 'Debe seleccionar un tipo de certificado';
    isValid = false;
  }

  if (!form.veterinarianId) {
    errors.veterinarianId = 'Debe seleccionar un veterinario';
    isValid = false;
  }

  return isValid;
}

function resetForm() {
  Object.assign(form, {
    animalId: '',
    type: '',
    veterinarianId: '',
    notes: ''
  });
  
  // Limpiar errores
  Object.keys(errors).forEach(key => errors[key] = '');
}

async function generateCertificate() {
  if (!validateForm()) {
    return;
  }

  loading.value = true;

  try {
    console.log('Generando certificado:', form);

    let success = false;

    // Llamar al método correspondiente según el tipo de certificado
    switch (form.type) {
      case 'vaccination':
        success = await veterinaryStore.generarCertificadoVacunacion(form.animalId);
        break;
      case 'sterilization':
      case 'health':
        success = await veterinaryStore.generarCertificadoSalud(form.animalId);
        break;
      default:
        throw new Error('Tipo de certificado no soportado');
    }

    if (success) {
      alert('Certificado generado y descargado exitosamente');
      resetForm();
    }
  } catch (e) {
    console.error('Error generando certificado:', e);
    alert(e.response?.data?.message || 'Error al generar el certificado. El endpoint podría no estar disponible aún.');
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  console.log('🚀 CertificateGenerator: onMounted');
  await loadData();
});
</script>

<style scoped>
.certificate-generator {
  max-width: 900px;
  margin: 0 auto;
  padding: 2rem;
  background: #f5f7fb;
}

.form-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #3366cc;
}

.h2-tipografia-govco {
  font-size: 1.75rem;
  font-weight: 700;
  color: #004884;
  margin: 0 0 0.5rem 0;
}

.text2-tipografia-govco {
  font-size: 1rem;
  color: #4B4B4B;
  margin: 0;
}

.loading-overlay {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  min-height: 200px;
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
  font-size: 1.1rem;
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

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
}

@media (max-width: 768px) {
  .certificate-generator {
    padding: 1rem;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .full-width {
    grid-column: 1 / 2;
  }

  .form-actions {
    flex-direction: column-reverse;
  }

  .form-actions button {
    width: 100%;
  }
}
</style>