<template>
  <section class="neutering-section">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Adjuntar Certificado de Esterilización</h2>
      <p class="text2-tipografia-govco">Busque un animal esterilizado y adjunte su certificado digital</p>
    </div>

    <form ref="formEl" @submit.prevent="onSubmit" novalidate>
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Búsqueda y Certificado</h3>
        
        <div class="form-grid">
          <!-- Animal ID (para buscar) -->
          <div class="entradas-de-texto-govco">
            <label for="animalId">ID/Microchip del animal esterilizado*</label>
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
              <br><small v-if="animalEncontrado.animal?.esterilizacion">(Ya marcado como esterilizado: ✓)</small>
            </span>
          </div>

          <!-- Información del animal encontrado -->
          <div v-if="animalEncontrado" class="full-width">
            <div class="info-card">
              <h4>📋 Información de Cirugía de Esterilización</h4>
              <div class="info-grid">
                <div v-if="cirugiasInfo.fecha">
                  <strong>📅 Fecha de cirugía:</strong>
                  <span>{{ formatDate(cirugiasInfo.fecha) }}</span>
                </div>
                <div v-if="cirugiasInfo.cirujano">
                  <strong>👨‍⚕️ Veterinario responsable:</strong>
                  <span>{{ cirugiasInfo.cirujano }}</span>
                </div>
                <div v-if="cirugiasInfo.tipo">
                  <strong>🔧 Tipo de cirugía:</strong>
                  <span>{{ formatTipoCirugia(cirugiasInfo.tipo) }}</span>
                </div>
                <div v-if="cirugiasInfo.resultado">
                  <strong>✅ Resultado:</strong>
                  <span :class="'resultado-' + cirugiasInfo.resultado">{{ formatResultado(cirugiasInfo.resultado) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Certificado usando FileUploader -->
          <div class="full-width">
            <FileUploader
              v-model="form.certificateFiles"
              accept="application/pdf,image/jpeg,image/jpg,image/png"
              :max-files="1"
              :max-size-m-b="5"
              label="Certificado de Esterilización"
              help-text="PDF o imagen. Peso máximo: 5 MB. Puede descargar el certificado desde el generador de certificados."
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
            placeholder="Observaciones sobre el certificado..."
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
          :label="isSubmitting ? 'Adjuntando...' : 'Adjuntar Certificado'"
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
import api from '@/services/api';
import ButtonGovCo from '../common/ButtonGovCo.vue';
import InputGovCo from '../common/InputGovCo.vue';
import FileUploader from '../common/FileUploader.vue';

const emit = defineEmits(['certificateAttached']);

const formEl = ref(null);
const isSubmitting = ref(false);
const animalEncontrado = ref(null);
const buscandoAnimal = ref(false);
const cirugiasInfo = ref({
  tipo: null,
  resultado: null,
  fecha: null,
  cirujano: null
});

const form = reactive({
  animalId: '',
  certificateFiles: [],
  notes: ''
});

const errors = reactive({
  animalId: '',
  neuteringCertificate: ''
});

// Buscar animal por microchip/código
async function buscarAnimal() {
  if (!form.animalId || form.animalId.trim().length < 3) {
    animalEncontrado.value = null;
    cirugiasInfo.value = { tipo: null, resultado: null, fecha: null, cirujano: null };
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
      
      // Cargar datos de la cirugía
      await cargarDatosCirugia(responseChip.data.data);
      buscandoAnimal.value = false;
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
      
      animalEncontrado.value = {
        animal: animal,
        historial_clinico_id: animal.historial_clinico?.id || null,
        id: animal.historial_clinico?.id || null
      };
      
      console.log('✅ Animal encontrado:', animalEncontrado.value);
      
      // Cargar datos del historial clínico para obtener datos de cirugía
      try {
        const historialResponse = await api.get(`/animals/${animal.id}/historial-clinico`);
        if (historialResponse.data?.data) {
          await cargarDatosCirugia(historialResponse.data.data);
        }
      } catch (historialErr) {
        console.warn('No se pudo cargar historial clínico:', historialErr);
      }
      
      buscandoAnimal.value = false;
      return;
    }
  } catch (err2) {
    console.error('Error buscando animal:', err2);
  }

  // No se encontró
  animalEncontrado.value = null;
  cirugiasInfo.value = { tipo: null, resultado: null, fecha: null, cirujano: null };
  errors.animalId = 'Animal no encontrado';
  buscandoAnimal.value = false;
}

// Cargar datos de la cirugía de esterilización
async function cargarDatosCirugia(historial) {
  try {
    if (historial.cirugias && historial.cirugias.length > 0) {
      // Buscar la cirugía de esterilización más reciente
      const cirugia = historial.cirugias.find(c => 
        c.tipo_cirugia === 'esterilizacion' || c.tipo_cirugia === 'castracion'
      ) || historial.cirugias[0];
      
      if (cirugia) {
        cirugiasInfo.value = {
          tipo: cirugia.tipo_cirugia,
          resultado: cirugia.resultado,
          fecha: cirugia.fecha_realizacion || cirugia.fecha_programada,
          cirujano: cirugia.cirujano?.nombre_completo || 
                   `${cirugia.cirujano?.nombres || ''} ${cirugia.cirujano?.apellidos || ''}`.trim() ||
                   'No especificado'
        };
        console.log('✅ Datos de cirugía cargados:', cirugiasInfo.value);
      }
    }
  } catch (err) {
    console.warn('No se pudo cargar datos de cirugía:', err);
  }
}

// Formatear tipo de cirugía
function formatTipoCirugia(tipo) {
  const tipos = {
    'esterilizacion': 'Esterilización',
    'castracion': 'Castración',
    'ortopedica': 'Ortopédica',
    'abdominal': 'Abdominal',
    'oftalmologica': 'Oftalmológica',
    'dental': 'Dental',
    'oncologica': 'Oncológica',
    'emergencia': 'Emergencia'
  };
  return tipos[tipo] || tipo;
}

// Formatear resultado
function formatResultado(resultado) {
  const resultados = {
    'exitosa': '✅ Exitosa',
    'con_complicaciones': '⚠️ Con complicaciones',
    'fallida': '❌ Fallida',
    'ok': '✅ Exitosa'
  };
  return resultados[resultado] || resultado;
}

// Formatear fecha
function formatDate(dateString) {
  if (!dateString) return 'No especificada';
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-CO', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  } catch {
    return dateString;
  }
}

// Handler para cuando cambia el certificado
function onCertificateChange(files) {
  errors.neuteringCertificate = '';
  console.log('📎 Certificado seleccionado:', files);
}

onMounted(() => {
  console.log('🚀 AnimalNeuturingForm: Montado (Adjunción de certificados)');
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

  // Validar certificado
  if (!form.certificateFiles || form.certificateFiles.length === 0) {
    errors.neuteringCertificate = 'Debe adjuntar un certificado de esterilización';
    isValid = false;
  }

  return isValid;
}

function resetForm() {
  // Limpiar formulario
  form.animalId = '';
  form.certificateFiles = [];
  form.notes = '';
  
  // Limpiar errores
  Object.keys(errors).forEach(k => errors[k] = '');
  
  // Limpiar animal encontrado y datos de cirugía
  animalEncontrado.value = null;
  cirugiasInfo.value = { tipo: null, resultado: null, fecha: null, cirujano: null };
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

  const animalId = animalEncontrado.value.animal?.id;

  if (!animalId) {
    errors.animalId = 'No se pudo obtener el ID del animal';
    return;
  }

  console.log('📋 Adjuntando certificado al animal:', {
    animalId,
    historialClinicoId,
    tieneArchivo: form.certificateFiles.length > 0
  });

  isSubmitting.value = true;

  try {
    // Preparar FormData
    const formData = new FormData();
    
    if (form.certificateFiles && form.certificateFiles.length > 0) {
      const file = form.certificateFiles[0];
      formData.append('certificado', file);
      console.log('📎 Archivo adjunto:', {
        name: file.name,
        type: file.type,
        size: file.size
      });
    }
    
    if (form.notes) {
      formData.append('notas', form.notes);
    }

    // 🔍 DEPURACIÓN: Ver la URL completa
    const url = `/animals/${animalId}/certificado-esterilizacion`;
    console.log('🌐 URL de la petición:', url);
    console.log('🌐 URL completa:', api.defaults.baseURL + url);
    console.log('🔑 Token:', localStorage.getItem('token') ? 'Presente' : 'Ausente');

    // Adjuntar certificado al animal
    const response = await api.post(url, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    console.log('✅ Certificado adjuntado:', response);

    // Marcar el animal como esterilizado
    try {
      console.log('🔄 Marcando animal como esterilizado...');
      
      const updateData = {
        esterilizacion: true,
        fecha_esterilizacion: cirugiasInfo.value.fecha || new Date().toISOString().split('T')[0]
      };

      await api.put(`/animals/${animalId}`, updateData);
      console.log('✅ Animal marcado como esterilizado');
    } catch (updateError) {
      console.warn('⚠️ Advertencia: No se pudo actualizar el estado del animal:', updateError);
    }

    // Mostrar mensaje de éxito
    if (window.$toast) {
      window.$toast.success(
        '¡Certificado adjuntado!',
        'El certificado de esterilización ha sido adjuntado correctamente y el animal ha sido marcado como esterilizado.'
      );
    } else {
      alert('✅ Certificado adjuntado exitosamente y animal marcado como esterilizado');
    }

    emit('certificateAttached', response.data);
    resetForm();

  } catch (error) {
    console.error('❌ Error al adjuntar certificado:', error);
    console.error('❌ Error response:', error.response);
    console.error('❌ Error config:', error.config);

    let errorMessage = 'No se pudo adjuntar el certificado. Intente nuevamente.';

    if (error.response?.status === 404) {
      errorMessage = 'La ruta del servidor no existe. Verifique la configuración del backend.';
      console.error('❌ RUTA NO ENCONTRADA:', error.config?.url);
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.response?.data?.errors) {
      const firstError = Object.values(error.response.data.errors)[0];
      errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
    }

    if (window.$toast) {
      window.$toast.error('Error al adjuntar certificado', errorMessage);
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
  grid-template-columns: repeat(2, minmax(0, 1fr)); 
  column-gap: 2rem; 
  row-gap: 1.5rem; 
  padding: 1.5rem; 
}

.form-grid > div { 
  display: flex; 
  flex-direction: column;
}

.full-width { 
  grid-column: 1 / 3; 
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

.info-card {
  background: #f8fafe;
  border-left: 4px solid #3366cc;
  padding: 1.5rem;
  border-radius: 8px;
  margin-top: 1rem;
}

.info-card h4 {
  margin: 0 0 1rem 0;
  color: #004884;
  font-size: 1rem;
  font-weight: 600;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.info-grid > div {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.info-grid strong {
  color: #666;
  font-size: 0.85rem;
  font-weight: 600;
}

.info-grid span {
  color: #333;
  font-weight: 500;
  font-size: 0.95rem;
}

.resultado-exitosa {
  color: #069169;
  font-weight: 600;
}

.resultado-con_complicaciones {
  color: #f59e0b;
  font-weight: 600;
}

.resultado-fallida {
  color: #dc2626;
  font-weight: 600;
}

.resultado-ok {
  color: #069169;
  font-weight: 600;
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
    grid-template-columns: 1fr; 
  }
  .full-width { 
    grid-column: 1 / 2; 
  }
  .info-grid {
    grid-template-columns: 1fr;
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