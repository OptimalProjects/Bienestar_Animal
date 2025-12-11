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
              <button type="button" class="btn-search" @click="buscarAnimal" :disabled="buscandoAnimal">
                {{ buscandoAnimal ? '...' : '🔍' }}
              </button>
            </div>
            <span v-if="errors.animalId" class="error-text">{{ errors.animalId }}</span>
            <span v-if="animalEncontrado" class="success-text">
              ✅ {{ animalEncontrado.animal?.nombre || 'Animal' }} - {{ animalEncontrado.animal?.especie || '' }}
            </span>
          </div>

          <!-- Fecha de esterilización -->
          <div class="input-like-govco">
            <div class="date-field-container neut-date-container">
              <label for="neutDate" class="label-desplegable-govco">
                Fecha de esterilización<span aria-required="true">*</span>
              </label>
              <div class="desplegable-govco desplegable-calendar-govco" data-type="calendar">
                <div class="date desplegable-selected-option">
                  <input
                    class="browser-default"
                    type="text"
                    id="neutDate"
                    v-model="form.neuteringDate"
                    aria-autocomplete="off"
                    days="true"
                    placeholder="DD/MM/AAAA"
                  />
                </div>
              </div>
              <span v-if="errors.neuteringDate" class="alert-desplegable-govco">{{ errors.neuteringDate }}</span>
            </div>
          </div>

          <!-- Veterinario responsable -->
          <div class="entradas-de-texto-govco">
            <label for="neutVet">Veterinario responsable*</label>
            <select
              id="neutVet"
              v-model="form.veterinario_id"
              class="select-govco"
            >
              <option value="">Seleccione un veterinario</option>
              <option v-for="vet in veterinarios" :key="vet.id" :value="vet.id">
                {{ vet.usuario?.nombres || vet.nombre || 'Veterinario' }} {{ vet.usuario?.apellidos || '' }}
                {{ vet.tarjeta_profesional ? `(${vet.tarjeta_profesional})` : '' }}
              </option>
            </select>
            <span v-if="errors.neuteringVet" class="error-text">
              {{ errors.neuteringVet }}
            </span>
          </div>

          <!-- Certificado -->
          <div class="container-carga-de-archivo-govco full-width">
            <div class="loader-carga-de-archivo-govco">
              <div class="all-input-carga-de-archivo-govco">
                <input
                  type="file"
                  id="neutCert"
                  class="input-carga-de-archivo-govco active"
                  accept="application/pdf,image/*"
                  data-action="uploadFile"
                  data-action-delete="deleteFile"
                />

                <label for="neutCert" class="label-carga-de-archivo-govco">
                  Certificado digital*
                </label>

                <label for="neutCert" class="container-input-carga-de-archivo-govco">
                  <span class="button-file-carga-de-archivo-govco">Seleccionar archivo</span>
                  <span class="file-name-carga-de-archivo-govco">
                    {{ certificateLabel }}
                  </span>
                </label>

                <span class="text-validation-carga-de-archivo-govco">
                  PDF o imagen. Peso máximo: 2 MB
                </span>
              </div>

              <div class="load-button-carga-de-archivo-govco">
                <div class="load-carga-de-archivo-govco">
                  <div class="spinner-indicador-de-carga-govco" style="width: 32px; height: 32px; border-width: 6px;" role="status">
                    <span class="visually-hidden">Cargando...</span>
                  </div>
                </div>
                <button class="button-loader-carga-de-archivo-govco" type="button">
                  Cargar archivo
                </button>
              </div>
            </div>

            <div class="container-detail-carga-de-archivo-govco">
              <span
                class="alert-carga-de-archivo-govco"
                :class="{ 'visually-hidden': !errors.neuteringCertificate }"
              >
                {{ errors.neuteringCertificate }}
              </span>
              
              <div class="attached-files-carga-de-archivo-govco"></div>
            </div>
          </div>

          <!-- Notas adicionales (opcional) -->
          <div class="entradas-de-texto-govco full-width">
            <label for="notes">Notas adicionales (opcional)</label>
            <textarea
              id="notes"
              v-model="form.notes"
              rows="3"
              placeholder="Observaciones sobre el procedimiento..."
            ></textarea>
          </div>
        </div>
      </div>

      <!-- BOTONES -->
      <div class="form-actions">
        <button type="button" @click="resetForm" class="govco-btn govco-bg-concrete" :disabled="isSubmitting">
          Limpiar formulario
        </button>
        <button type="submit" class="govco-btn govco-bg-elf-green" :disabled="isSubmitting">
          {{ isSubmitting ? 'Registrando...' : 'Registrar esterilización' }}
        </button>
      </div>
    </form>
  </section>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import { useVeterinaryStore } from '@/stores/veterinary';
import api from '@/services/api';

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
  neuteringVet: '',
  veterinario_id: '',
  neuteringCertificate: null,
  notes: ''
});

const errors = reactive({
  animalId: '',
  neuteringDate: '',
  neuteringVet: '',
  neuteringCertificate: ''
});

// Cargar veterinarios al montar
async function loadVeterinarios() {
  try {
    await veterinaryStore.fetchVeterinarios();
    veterinarios.value = veterinaryStore.veterinarios;
  } catch (err) {
    console.error('Error al cargar veterinarios:', err);
  }
}

// Buscar animal por microchip/código
async function buscarAnimal() {
  if (!form.animalId || form.animalId.trim().length < 3) {
    animalEncontrado.value = null;
    return;
  }

  buscandoAnimal.value = true;
  try {
    // Primero intentar buscar por chip
    const responseChip = await api.get(`/historial-clinico/buscar-chip/${form.animalId}`);
    if (responseChip.data?.data) {
      animalEncontrado.value = responseChip.data.data;
      errors.animalId = '';
      return;
    }
  } catch (err) {
    // Si no encuentra por chip, buscar en animales por código
    try {
      const responseAnimales = await api.get('/animals', { params: { search: form.animalId } });
      const animales = responseAnimales.data?.data?.data || responseAnimales.data?.data || [];
      if (animales.length > 0) {
        const animal = animales[0];
        animalEncontrado.value = {
          animal: animal,
          historial_clinico_id: animal.historial_clinico?.id || null
        };
        errors.animalId = '';
        return;
      }
    } catch (err2) {
      console.error('Error buscando animal:', err2);
    }
  } finally {
    buscandoAnimal.value = false;
  }

  animalEncontrado.value = null;
  errors.animalId = 'Animal no encontrado';
}

// Funciones para el componente de carga de archivos de GOV.CO
if (typeof window !== 'undefined') {
  // Función para procesar archivo cuando se hace clic en "Cargar archivo"
  window.uploadFile = function(files) {
    return new Promise((resolve, reject) => {
      try {
        console.log('uploadFile: Procesando archivo');
        
        const file = files[0]; // Solo un archivo
        
        if (!file) {
          reject('No se seleccionó ningún archivo.');
          return;
        }

        // Validar tipo
        const validTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        if (!validTypes.includes(file.type)) {
          reject('Solo se permiten archivos PDF, JPG o PNG.');
          return;
        }

        // Validar tamaño (2MB)
        if (file.size > 2 * 1024 * 1024) {
          reject('El archivo debe ser menor a 2 MB.');
          return;
        }

        form.neuteringCertificate = file;
        errors.neuteringCertificate = '';
        
        console.log('uploadFile: Archivo cargado correctamente');
        resolve([file]); // Devolver como array
      } catch (error) {
        console.error('Error en uploadFile:', error);
        reject('Error al procesar el archivo.');
      }
    });
  };

  // Función para eliminar el archivo
  window.deleteFile = function(file) {
    return new Promise((resolve, reject) => {
      try {
        if (form.neuteringCertificate && form.neuteringCertificate.name === file.name) {
          form.neuteringCertificate = null;
          console.log('deleteFile: Archivo eliminado');
          resolve(true);
        } else {
          reject('Archivo no encontrado.');
        }
      } catch (error) {
        reject('Error al eliminar el archivo.');
      }
    });
  };

  // Configurar validación - se ejecutará cuando la página cargue
  window.addEventListener('load', function() {
    // Permitir 1 archivo, con extensiones pdf/jpg/jpeg/png, máximo 2MB
    if (window.setValidationParameters) {
      window.setValidationParameters('neutCert', ['pdf', 'jpg', 'jpeg', 'png'], 2 * 1024 * 1024, 1);
    }
  });
}

function fixNonSubmitButtons() {
  if (!formEl.value) return;

  const buttons = formEl.value.querySelectorAll('button');

  buttons.forEach((btn) => {
    const isRegisterButton = btn.textContent?.includes('Registrar esterilización');
    
    if (isRegisterButton) {
      btn.setAttribute('type', 'submit');
    } else {
      btn.setAttribute('type', 'button');
    }
  });
  
  if (formEl.value && !formEl.value.dataset.listenerAdded) {
    formEl.value.addEventListener('submit', (e) => {
      const submitter = e.submitter;
      if (!submitter || !submitter.textContent?.includes('Registrar esterilización')) {
        e.preventDefault();
        e.stopPropagation();
        return false;
      }
    }, true);
    
    formEl.value.dataset.listenerAdded = 'true';
  }
}

function initializeCalendars() {
  if (typeof window !== 'undefined' && window.GOVCo) {
    try {
      const calendars = formEl.value?.querySelectorAll('[data-type="calendar"]');
      if (calendars) {
        calendars.forEach((cal) => {
          if (window.GOVCo.init) {
            window.GOVCo.init(cal.parentElement);
          }
        });
      }
    } catch (error) {
      console.warn('Error inicializando calendarios:', error);
    }
  }
}

onMounted(() => {
  fixNonSubmitButtons();
  initializeCalendars();

  // Cargar veterinarios al iniciar
  loadVeterinarios();

  // Configurar validación del componente de carga de archivo
  if (window.setValidationParameters) {
    window.setValidationParameters(
      'neutCert',
      ['pdf', 'jpg', 'jpeg', 'png'],
      2 * 1024 * 1024,
      1  // Máximo 1 archivo
    );
  }

  // Asegurar que el input de certificado no esté bloqueado
  const certInput = document.getElementById('neutCert');
  if (certInput) {
    certInput.disabled = false;
    certInput.classList.remove('disabled');
    certInput.classList.add('active');
  }

  // Agregar listener para sincronizar cambios en el input de fecha
  const dateInput = document.getElementById('neutDate');
  if (dateInput) {
    // Escuchar eventos de cambio, input y blur
    ['change', 'input', 'blur'].forEach(event => {
      dateInput.addEventListener(event, () => {
        if (dateInput.value) {
          form.neuteringDate = dateInput.value;
          errors.neuteringDate = ''; // Limpiar error si hay valor
        }
      });
    });

    // Usar MutationObserver para detectar cambios hechos por el calendario GOV.CO
    const observer = new MutationObserver(() => {
      if (dateInput.value && dateInput.value !== form.neuteringDate) {
        form.neuteringDate = dateInput.value;
        errors.neuteringDate = '';
      }
    });
    observer.observe(dateInput, { attributes: true, attributeFilter: ['value'] });
  }

  if (typeof window !== 'undefined') {
    window.addEventListener('load', () => {
      fixNonSubmitButtons();
      initializeCalendars();

      // Agregar listener al botón de carga
      const uploadButton = document.querySelector('.button-loader-carga-de-archivo-govco');
      if (uploadButton) {
        uploadButton.addEventListener('click', () => {
          setTimeout(() => {
            const certInput = document.getElementById('neutCert');
            if (certInput) {
              certInput.disabled = false;
              certInput.classList.add('active');
            }
          }, 200);
        });
      }
    });
  }
});

const certificateLabel = computed(() => 
  form.neuteringCertificate ? form.neuteringCertificate.name : 'Sin archivo seleccionado'
);

function syncDateFromInput() {
  // Sincronizar valor del input del calendario con form.neuteringDate
  const dateInput = document.getElementById('neutDate');
  if (dateInput && dateInput.value) {
    form.neuteringDate = dateInput.value;
  }
}

function validate() {
  // Sincronizar fecha antes de validar
  syncDateFromInput();

  Object.keys(errors).forEach(k => errors[k] = '');

  if (!form.animalId || !form.animalId.trim()) {
    errors.animalId = 'Campo requerido';
  } else if (!animalEncontrado.value) {
    errors.animalId = 'Debe buscar y seleccionar un animal válido';
  }

  // Validar fecha - verificar tanto el form como el input directamente
  const dateInput = document.getElementById('neutDate');
  const dateValue = form.neuteringDate || (dateInput ? dateInput.value : '');

  if (!dateValue || dateValue.trim() === '') {
    errors.neuteringDate = 'Campo requerido';
  }

  // Validar veterinario (ahora es un select con ID)
  if (!form.veterinario_id) {
    errors.neuteringVet = 'Seleccione un veterinario';
  }

  // El certificado es opcional para el backend
  // if (!form.neuteringCertificate) {
  //   errors.neuteringCertificate = 'Certificado requerido';
  // }

  return !Object.values(errors).some(e => e);
}

function resetForm() {
  Object.keys(form).forEach(k => {
    form[k] = k === 'neuteringCertificate' ? null : '';
  });
  Object.keys(errors).forEach(k => errors[k] = '');
  
  // Limpiar input de archivo
  const fileInput = document.getElementById('neutCert');
  if (fileInput) {
    fileInput.value = '';
    fileInput.disabled = false;
    fileInput.classList.add('active');
  }
}

async function onSubmit() {
  if (!validate()) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  // Verificar que tenemos el animal
  if (!animalEncontrado.value) {
    errors.animalId = 'Debe buscar y seleccionar un animal válido';
    return;
  }

  // Obtener historial_clinico_id
  const historialClinicoId = animalEncontrado.value.historial_clinico_id ||
    animalEncontrado.value.animal?.historial_clinico?.id ||
    animalEncontrado.value.id;

  if (!historialClinicoId) {
    errors.animalId = 'El animal no tiene historial clínico asociado';
    return;
  }

  isSubmitting.value = true;

  try {
    // Sincronizar fecha antes de enviar
    syncDateFromInput();

    // Convertir fecha de DD/MM/YYYY a YYYY-MM-DD
    let fechaCirugia = form.neuteringDate;
    if (fechaCirugia && fechaCirugia.includes('/')) {
      const parts = fechaCirugia.split('/');
      if (parts.length === 3) {
        fechaCirugia = `${parts[2]}-${parts[1]}-${parts[0]}`;
      }
    }

    const cirugiaData = {
      historial_clinico_id: historialClinicoId,
      veterinario_id: form.veterinario_id || null,
      fecha_cirugia: fechaCirugia,
      tipo_cirugia: 'esterilizacion',
      descripcion: `Esterilización realizada. ${form.notes || ''}`.trim(),
      resultado: 'exitosa',
      notas_postoperatorias: form.notes || null
    };

    console.log('📝 Enviando esterilización:', cirugiaData);

    const response = await veterinaryStore.crearCirugia(cirugiaData);

    console.log('✅ Esterilización registrada:', response);

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

.entradas-de-texto-govco, 
.desplegable-govco, 
.container-carga-de-archivo-govco { 
  width: 100%; 
}

.entradas-de-texto-govco input,
.entradas-de-texto-govco textarea,
.desplegable-govco select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  line-height: 1.5;
  box-sizing: border-box;
}

.entradas-de-texto-govco input {
  height: 44px;
}

.error-text,
.alert-desplegable-govco {
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
}

.input-with-button input {
  flex: 1;
}

.btn-search {
  padding: 0.5rem 1rem;
  background: #3366CC;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
}

.btn-search:hover {
  background: #2952A3;
}

.btn-search:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.select-govco {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  line-height: 1.5;
  box-sizing: border-box;
  background: white;
  height: 44px;
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

.govco-btn:disabled { 
  opacity: 0.5; 
  cursor: not-allowed; 
  transform: none; 
}

.govco-bg-concrete {
  background-color: #737373;
}

.govco-bg-elf-green {
  background-color: #069169;
}
.neut-date-container .desplegable-govco{margin-top: 0.7rem;}

.input-like-govco {
  display: flex;
  flex-direction: column;
  width: 100%;
  margin: 18px 0  ;
}

.input-like-govco label {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

:deep(.desplegable-calendar-govco .desplegable-calendar-control table td) { box-sizing: border-box !important; width: calc(100% / 7) !important;}
.date-field-container {  width: 100%;}
.date-field-container :deep(.date.desplegable-selected-option) {padding: 7px 40px 7px 16px !important; box-sizing: border-box !important;}

.date-field-container :deep(.date.desplegable-selected-option input) {
  width: 100% !important;
  padding-right: 30px !important; /* Espacio para el ícono */
  box-sizing: border-box !important;
}

:deep(.desplegable-govco .desplegable-items),
:deep(.desplegable-govco.desplegable-calendar-govco .desplegable-calendar-control) { 
  z-index: 1500 !important;
}

:deep(.desplegable-govco.desplegable-calendar-govco .desplegable-calendar-control) {
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

.container-detail-carga-de-archivo-govco {
  display: block !important;
}

.container-carga-de-archivo-govco :deep(.attached-files-carga-de-archivo-govco) {
  padding-top: 0.8rem;
}

.container-carga-de-archivo-govco :deep(.attached-file-carga-de-archivo-govco) { 
  display: flex !important;
}

.visually-hidden {
  display: none !important;
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
  .govco-btn { 
    width: 100%; 
  }
}
</style>