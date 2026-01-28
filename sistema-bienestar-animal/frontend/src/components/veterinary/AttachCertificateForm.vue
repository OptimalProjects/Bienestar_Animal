<template>
  <section class="certificate-section">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Adjuntar Certificado Veterinario</h2>
      <p class="text2-tipografia-govco">Busque un animal y adjunte certificados de esterilización, cirugía o vacunación</p>
    </div>

    <form ref="formEl" @submit.prevent="onSubmit" novalidate>
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Selección de Animal y Tipo de Certificado</h3>
        
        <div class="form-grid">
          <!-- Búsqueda de Animal -->
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

          <!-- Tipo de Certificado -->
          <div class="entradas-de-texto-govco">
            <label for="tipoCertificado">Tipo de certificado*</label>
            <select
              id="tipoCertificado"
              v-model="form.tipoCertificado"
              @change="onTipoCertificadoChange"
              :disabled="!animalEncontrado"
            >
              <option value="">Seleccione un tipo</option>
              <option value="esterilizacion">Esterilización</option>
              <option value="cirugia">Cirugía</option>
              <option value="vacunacion">Vacunación</option>
            </select>
            <span v-if="errors.tipoCertificado" class="error-text">{{ errors.tipoCertificado }}</span>
          </div>

          <!-- Selector de Registro Específico (Cirugía o Vacuna) -->
          <div v-if="form.tipoCertificado === 'cirugia' || form.tipoCertificado === 'vacunacion'" class="entradas-de-texto-govco full-width">
            <label for="registroId">
              {{ form.tipoCertificado === 'cirugia' ? 'Seleccione la cirugía*' : 'Seleccione la vacuna*' }}
            </label>
            <select
              id="registroId"
              v-model="form.registroId"
              @change="onRegistroChange"
            >
              <option value="">-- Seleccione --</option>
              <option 
                v-for="registro in registrosDisponibles" 
                :key="registro.id" 
                :value="registro.id"
              >
                {{ formatRegistro(registro) }}
              </option>
            </select>
            <span v-if="errors.registroId" class="error-text">{{ errors.registroId }}</span>
          </div>
        </div>
      </div>

      <!-- Información del Registro Seleccionado -->
      <div v-if="registroSeleccionado || (form.tipoCertificado === 'esterilizacion' && animalEncontrado)" class="form-section">
        <h3 class="h5-tipografia-govco section-title">📋 Información del Registro</h3>
        <div class="form-grid">
          <div class="full-width">
            <div class="info-card">
              <h4>{{ getTituloInfo() }}</h4>
              <div class="info-grid">
                <div v-for="(value, key) in getInfoRegistro()" :key="key">
                  <strong>{{ key }}:</strong>
                  <span>{{ value }}</span>
                </div>
              </div>
              
              <!-- Mostrar certificados existentes -->
              <div v-if="certificadosExistentes.length > 0" class="certificados-existentes">
                <h5>📎 Certificados existentes ({{ certificadosExistentes.length }})</h5>
                <div class="certificados-lista">
                  <div v-for="(cert, index) in certificadosExistentes" :key="index" class="certificado-item">
                    <span class="certificado-numero">{{ index + 1 }}.</span>
                    <span class="certificado-nombre">{{ cert.nombre_archivo || 'Certificado ' + (index + 1) }}</span>
                    <span class="certificado-fecha">{{ formatDateShort(cert.fecha_adjuncion) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Adjuntar Certificado -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">📎 Adjuntar Certificado</h3>
        <div class="form-grid">
          <div class="full-width">
            <FileUploader
              v-model="form.certificateFiles"
              accept="application/pdf,image/jpeg,image/jpg,image/png"
              :max-files="1"
              :max-size-m-b="5"
              label="Certificado"
              help-text="PDF o imagen. Peso máximo: 5 MB. Se agregará a los certificados existentes."
              :required="true"
              @update:modelValue="onCertificateChange"
            />
            <span v-if="errors.certificate" class="error-text">{{ errors.certificate }}</span>
          </div>

          <!-- Notas adicionales -->
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
          :disabled="isSubmitting || !canSubmit"
        />
      </div>
    </form>
  </section>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import api from '@/services/api';
import ButtonGovCo from '../common/ButtonGovCo.vue';
import InputGovCo from '../common/InputGovCo.vue';
import FileUploader from '../common/FileUploader.vue';

const emit = defineEmits(['certificateAttached']);

const formEl = ref(null);
const isSubmitting = ref(false);
const animalEncontrado = ref(null);
const buscandoAnimal = ref(false);
const registrosDisponibles = ref([]);
const registroSeleccionado = ref(null);
const certificadosExistentes = ref([]);

const form = reactive({
  animalId: '',
  tipoCertificado: '',
  registroId: '',
  certificateFiles: [],
  notes: ''
});

const errors = reactive({
  animalId: '',
  tipoCertificado: '',
  registroId: '',
  certificate: ''
});

// Computed
const canSubmit = computed(() => {
  return animalEncontrado.value && 
         form.tipoCertificado && 
         form.certificateFiles.length > 0 &&
         (form.tipoCertificado === 'esterilizacion' || form.registroId);
});

// Buscar animal
async function buscarAnimal() {
  if (!form.animalId || form.animalId.trim().length < 3) {
    animalEncontrado.value = null;
    registrosDisponibles.value = [];
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
      buscandoAnimal.value = false;
      return;
    }
  } catch (err) {
    console.log('No encontrado por chip, buscando en animales...');
  }

  // Si no se encuentra por chip, buscar en la lista general
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
      
      // Cargar historial clínico
      try {
        const historialResponse = await api.get(`/animals/${animal.id}/historial-clinico`);
        if (historialResponse.data?.data) {
          animalEncontrado.value = historialResponse.data.data;
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
  registrosDisponibles.value = [];
  errors.animalId = 'Animal no encontrado';
  buscandoAnimal.value = false;
}

// Cambio de tipo de certificado
async function onTipoCertificadoChange() {
  form.registroId = '';
  registroSeleccionado.value = null;
  registrosDisponibles.value = [];
  certificadosExistentes.value = [];
  errors.tipoCertificado = '';

  if (!animalEncontrado.value) return;

  const animalId = animalEncontrado.value.animal?.id;
  if (!animalId) return;

  try {
    if (form.tipoCertificado === 'cirugia') {
      // Cargar cirugías del animal
      const response = await api.get(`/cirugias/animal/${animalId}`);
      registrosDisponibles.value = response.data?.data || [];
    } else if (form.tipoCertificado === 'vacunacion') {
      // Cargar vacunas del animal
      const response = await api.get(`/vacunas/animal/${animalId}`);
      registrosDisponibles.value = response.data?.data || [];
    }
  } catch (error) {
    console.error('Error cargando registros:', error);
    registrosDisponibles.value = [];
  }
}

// Cambio de registro seleccionado
function onRegistroChange() {
  if (!form.registroId) {
    registroSeleccionado.value = null;
    certificadosExistentes.value = [];
    return;
  }

  const registro = registrosDisponibles.value.find(r => r.id === form.registroId);
  registroSeleccionado.value = registro || null;
  errors.registroId = '';

  // Cargar certificados existentes
  if (registro) {
    try {
      const certifs = registro.certificados ? JSON.parse(registro.certificados) : [];
      certificadosExistentes.value = Array.isArray(certifs) ? certifs : [];
    } catch (e) {
      console.warn('Error parseando certificados:', e);
      certificadosExistentes.value = [];
    }
  }
}

// Formatear registro para el select
function formatRegistro(registro) {
  if (form.tipoCertificado === 'cirugia') {
    const fecha = formatDate(registro.fecha_realizacion || registro.fecha_programada);
    const tipo = formatTipoCirugia(registro.tipo_cirugia);
    return `${fecha} - ${tipo} - ${registro.descripcion?.substring(0, 50) || 'Sin descripción'}`;
  } else if (form.tipoCertificado === 'vacunacion') {
    const fecha = formatDate(registro.fecha_aplicacion);
    // FIX: Extraer el nombre de la vacuna correctamente
    const nombreVacuna = extraerNombreVacuna(registro.tipo_vacuna);
    const lote = registro.lote || 'N/A';
    return `${fecha} - ${nombreVacuna} - Lote: ${lote}`;
  }
  return '';
}

// NUEVA FUNCIÓN: Extraer nombre de vacuna del objeto JSON
function extraerNombreVacuna(tipoVacuna) {
  if (!tipoVacuna) return 'Sin especificar';
  
  // Si es un string, retornarlo directamente
  if (typeof tipoVacuna === 'string') {
    try {
      const parsed = JSON.parse(tipoVacuna);
      return parsed.nombre || parsed.descripcion || 'Sin especificar';
    } catch {
      return tipoVacuna;
    }
  }
  
  // Si es un objeto, extraer el nombre
  if (typeof tipoVacuna === 'object' && tipoVacuna !== null) {
    return tipoVacuna.nombre || tipoVacuna.descripcion || 'Sin especificar';
  }
  
  return 'Sin especificar';
}

// Obtener título de la info
function getTituloInfo() {
  if (form.tipoCertificado === 'esterilizacion') {
    return '🏥 Información de Esterilización';
  } else if (form.tipoCertificado === 'cirugia') {
    return '🔧 Información de Cirugía';
  } else if (form.tipoCertificado === 'vacunacion') {
    return '💉 Información de Vacunación';
  }
  return '';
}

// Obtener info del registro
function getInfoRegistro() {
  if (form.tipoCertificado === 'esterilizacion') {
    // Buscar cirugía de esterilización en el historial
    const cirugia = animalEncontrado.value?.cirugias?.find(c => 
      c.tipo_cirugia === 'esterilizacion' || c.tipo_cirugia === 'castracion'
    );

    if (cirugia) {
      return {
        '📅 Fecha': formatDate(cirugia.fecha_realizacion || cirugia.fecha_programada),
        '👨‍⚕️ Veterinario': extraerNombreVeterinario(cirugia.cirujano),
        '🔧 Tipo': formatTipoCirugia(cirugia.tipo_cirugia),
        '✅ Resultado': formatResultado(cirugia.resultado)
      };
    }
    return { 'Información': 'No hay cirugía de esterilización registrada' };
  }

  if (!registroSeleccionado.value) return {};

  if (form.tipoCertificado === 'cirugia') {
    return {
      '📅 Fecha programada': formatDate(registroSeleccionado.value.fecha_programada),
      '📅 Fecha realizada': registroSeleccionado.value.fecha_realizacion ? formatDate(registroSeleccionado.value.fecha_realizacion) : 'Pendiente',
      '🔧 Tipo': formatTipoCirugia(registroSeleccionado.value.tipo_cirugia),
      '👨‍⚕️ Cirujano': extraerNombreVeterinario(registroSeleccionado.value.cirujano),
      '⏱️ Duración': `${registroSeleccionado.value.duracion} min`,
      '✅ Resultado': formatResultado(registroSeleccionado.value.resultado),
      '📝 Descripción': registroSeleccionado.value.descripcion || 'Sin descripción'
    };
  }

  if (form.tipoCertificado === 'vacunacion') {
    return {
      '💉 Tipo de vacuna': extraerNombreVacuna(registroSeleccionado.value.tipo_vacuna),
      '📅 Fecha de aplicación': formatDate(registroSeleccionado.value.fecha_aplicacion),
      '👨‍⚕️ Veterinario': extraerNombreVeterinario(registroSeleccionado.value.veterinario),
      '📦 Lote': registroSeleccionado.value.lote || 'No especificado',
      '🏭 Fabricante': registroSeleccionado.value.fabricante || 'No especificado',
      '📝 Observaciones': registroSeleccionado.value.observaciones || 'Sin observaciones'
    };
  }

  return {};
}

// NUEVA FUNCIÓN: Extraer nombre del veterinario
function extraerNombreVeterinario(veterinario) {
  if (!veterinario) return 'No especificado';
  
  // Si tiene nombre_completo
  if (veterinario.nombre_completo) return veterinario.nombre_completo;
  
  // Si tiene usuario con nombre_completo
  if (veterinario.usuario?.nombre_completo) return veterinario.usuario.nombre_completo;
  
  // Si tiene nombres y apellidos
  if (veterinario.nombres || veterinario.apellidos) {
    return `${veterinario.nombres || ''} ${veterinario.apellidos || ''}`.trim();
  }
  
  // Si el veterinario ES el usuario
  if (veterinario.usuario) {
    const nombres = veterinario.usuario.nombres || '';
    const apellidos = veterinario.usuario.apellidos || '';
    return `${nombres} ${apellidos}`.trim() || 'No especificado';
  }
  
  return 'No especificado';
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
  return resultados[resultado] || resultado || 'Pendiente';
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

// Formatear fecha corta
function formatDateShort(dateString) {
  if (!dateString) return 'N/A';
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-CO', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    });
  } catch {
    return dateString;
  }
}

// Handler para cuando cambia el certificado
function onCertificateChange(files) {
  errors.certificate = '';
  console.log('📎 Certificado seleccionado:', files);
}

// Validación
function validate() {
  let valid = true;
  
  // Reset errors
  Object.keys(errors).forEach(key => errors[key] = '');

  if (!animalEncontrado.value) {
    errors.animalId = 'Debe buscar y seleccionar un animal';
    valid = false;
  }

  if (!form.tipoCertificado) {
    errors.tipoCertificado = 'Debe seleccionar el tipo de certificado';
    valid = false;
  }

  if ((form.tipoCertificado === 'cirugia' || form.tipoCertificado === 'vacunacion') && !form.registroId) {
    errors.registroId = 'Debe seleccionar el registro específico';
    valid = false;
  }

  if (!form.certificateFiles || form.certificateFiles.length === 0) {
    errors.certificate = 'Debe adjuntar el certificado';
    valid = false;
  }

  return valid;
}

// Reset form
function resetForm() {
  form.animalId = '';
  form.tipoCertificado = '';
  form.registroId = '';
  form.certificateFiles = [];
  form.notes = '';
  
  animalEncontrado.value = null;
  registrosDisponibles.value = [];
  registroSeleccionado.value = null;
  certificadosExistentes.value = [];
  
  Object.keys(errors).forEach(key => errors[key] = '');
}

// Submit
async function onSubmit() {
  if (!validate()) {
    console.warn('⚠️ Validación fallida');
    return;
  }

  isSubmitting.value = true;

  try {
    const animalId = animalEncontrado.value.animal?.id;
    
    if (!animalId) {
      throw new Error('ID del animal no disponible');
    }

    // Crear FormData
    const formData = new FormData();
    formData.append('tipo', form.tipoCertificado);
    formData.append('animal_id', animalId);
    
    if (form.registroId) {
      formData.append('registro_id', form.registroId);
    }
    
    if (form.certificateFiles && form.certificateFiles.length > 0) {
      formData.append('certificado', form.certificateFiles[0], form.certificateFiles[0].name);
    }
    
    if (form.notes) {
      formData.append('notas', form.notes);
    }

    console.log('📤 Enviando certificado:', {
      tipo: form.tipoCertificado,
      animal_id: animalId,
      registro_id: form.registroId || 'N/A'
    });

    // Enviar a la API
    const response = await api.post('/certificados-veterinarios', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    console.log('✅ Certificado adjuntado:', response);

    // Mensaje de éxito
    const tipoLabel = form.tipoCertificado === 'esterilizacion' ? 'esterilización' : form.tipoCertificado;
    const totalCerts = response.data?.data?.total_certificados || 1;
    
    if (window.$toast) {
      window.$toast.success(
        '¡Certificado adjuntado!',
        `El certificado de ${tipoLabel} ha sido adjuntado correctamente. Total de certificados: ${totalCerts}`
      );
    } else {
      alert(`✅ Certificado de ${tipoLabel} adjuntado exitosamente (${totalCerts} en total)`);
    }

    emit('certificateAttached', response.data);
    resetForm();

  } catch (error) {
    console.error('❌ Error al adjuntar certificado:', error);
    console.error('❌ Error response:', error.response);

    let errorMessage = 'No se pudo adjuntar el certificado. Intente nuevamente.';

    if (error.response?.status === 404) {
      errorMessage = 'La ruta del servidor no existe. Verifique la configuración del backend.';
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
.certificate-section { 
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

.entradas-de-texto-govco input,
.entradas-de-texto-govco select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  line-height: 1.5;
  box-sizing: border-box;
  height: 44px;
}

.entradas-de-texto-govco input:focus,
.entradas-de-texto-govco select:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.1);
}

.entradas-de-texto-govco select:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
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

.info-card h5 {
  margin: 1.5rem 0 0.75rem 0;
  color: #3366cc;
  font-size: 0.9rem;
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

.certificados-existentes {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e0e0e0;
}

.certificados-lista {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.certificado-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 0.75rem;
  background: #fff;
  border-radius: 4px;
  border: 1px solid #e0e0e0;
}

.certificado-numero {
  font-weight: 600;
  color: #3366cc;
  min-width: 24px;
}

.certificado-nombre {
  flex: 1;
  color: #333;
  font-size: 0.9rem;
}

.certificado-fecha {
  color: #666;
  font-size: 0.85rem;
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
  .certificate-section { 
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