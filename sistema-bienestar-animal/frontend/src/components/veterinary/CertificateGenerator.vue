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
          <div class="input-like-govco">
            <label for="animal">
              Animal<span aria-required="true">*</span>
            </label>
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
          </div>

          <!-- Tipo certificado -->
          <div class="input-like-govco">
            <label for="type">
              Tipo de certificado<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic">
              <select
                id="type"
                v-model="form.type"
                class="browser-default"
              >
                <option value="" disabled>Seleccione tipo</option>
                <option value="vaccination">Vacunación</option>
                <option value="sterilization">Esterilización</option>
                <option value="health">Salud general</option>
              </select>
            </div>
          </div>

          <!-- Veterinario -->
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
          </div>

          <!-- Observaciones -->
          <div class="entradas-de-texto-govco full-width">
            <label for="notes">Observaciones adicionales</label>
            <textarea
              id="notes"
              v-model="form.notes"
              rows="3"
              placeholder="Información adicional que desea incluir en el certificado."
            />
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button
          type="button"
          class="govco-btn govco-bg-concrete"
          @click="resetForm"
        >
          Limpiar
        </button>
        <button type="submit" class="govco-btn govco-bg-elf-green">
          Generar certificado PDF
        </button>
      </div>
    </form>
  </section>
</template>

<script setup>
import { reactive, ref, onMounted, nextTick, watch } from 'vue';
import { useVeterinaryStore } from '@/stores/veterinary';
import { useAnimalsStore } from '@/stores/animals';
import animalService from '@/services/animalService';

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

// Función para reinicializar componentes GOV.CO
function initGovCoComponents() {
  console.log('🔄 CertificateGenerator: Inicializando componentes GOV.CO...');

  nextTick(() => {
    if (window.GOVCo?.init) {
      const dropdowns = document.querySelectorAll('.certificate-generator .desplegable-govco');
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

// Cargar datos iniciales
async function loadData() {
  loadingData.value = true;
  console.log('🔄 CertificateGenerator: Cargando datos iniciales...');

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
      const vets = vetsData || veterinaryStore.veterinarios || [];
      veterinarians.value = vets.map(vet => ({
        id: vet.id,
        name: `${vet.usuario?.nombres || ''} ${vet.usuario?.apellidos || ''}`.trim() || 'Veterinario',
        license: vet.tarjeta_profesional || 'N/A'
      }));
      console.log('✅ Veterinarios cargados:', veterinarians.value.length);
    } catch (vetsError) {
      console.error('❌ Error cargando veterinarios:', vetsError);
    }

    // Reinicializar GOV.CO después de cargar datos
    await nextTick();
    setTimeout(() => {
      initGovCoComponents();
    }, 200);

  } catch (error) {
    console.error('❌ Error cargando datos:', error);
    alert('Error al cargar datos. Por favor recargue la página.');
  } finally {
    loadingData.value = false;
    console.log('✅ CertificateGenerator: Carga de datos completada');
  }
}

// Watch para reinicializar GOV.CO cuando cargan los datos
watch(() => animals.value.length, async (newLength) => {
  if (newLength > 0) {
    console.log('📦 Animales actualizados, reinicializando GOV.CO...');
    await nextTick();
    setTimeout(() => {
      initGovCoComponents();
    }, 100);
  }
});

function resetForm() {
  Object.assign(form, {
    animalId: '',
    type: '',
    veterinarianId: '',
    notes: ''
  });
}

async function generateCertificate() {
  if (!form.animalId || !form.type || !form.veterinarianId) {
    alert('Debe completar los campos obligatorios');
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
