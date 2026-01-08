
<template>
  <section class="certificate-generator">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Generación de certificados</h2>
      <p class="text2-tipografia-govco">
        Genere certificados de vacunación, esterilización o salud general en formato PDF.
      </p>
    </div>

    <!-- Indicador de carga inicial -->
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
            @update:modelValue="onAnimalChange"
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
        </div>

        <!-- Información del animal seleccionado -->
        <div v-if="selectedAnimalInfo" class="animal-info-card">
          <h4>📋 Información del animal</h4>
          <div class="info-grid">
            <div class="info-item">
              <strong>Nombre:</strong>
              <span>{{ selectedAnimalInfo.nombre || 'Sin nombre' }}</span>
            </div>
            <div class="info-item">
              <strong>Código único:</strong>
              <span>{{ selectedAnimalInfo.codigo_unico }}</span>
            </div>
            <div class="info-item">
              <strong>Especie:</strong>
              <span>{{ formatLabel(selectedAnimalInfo.especie) }}</span>
            </div>
            <div class="info-item">
              <strong>Esterilizado:</strong>
              <span :class="selectedAnimalInfo.esterilizacion ? 'status-yes' : 'status-no'">
                {{ selectedAnimalInfo.esterilizacion ? 'Sí' : 'No' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Alerta de validación según tipo -->
        <div v-if="form.type && form.animalId && validationMessage" class="validation-alert" :class="validationMessage.type">
          <div class="alert-icon">{{ validationMessage.icon }}</div>
          <div class="alert-content">
            <strong>{{ validationMessage.title }}</strong>
            <p>{{ validationMessage.message }}</p>
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
          :disabled="loading || !canGenerate"
        >
          {{ loading ? 'Generando...' : 'Generar certificado PDF' }}
        </ButtonGovCo>
      </div>
    </form>

    <!-- Resultado exitoso -->
    <div v-if="lastGenerated" class="success-card">
      <div class="success-icon">✅</div>
      <div class="success-content">
        <h4>Certificado generado exitosamente</h4>
        <p>{{ lastGenerated.message }}</p>
        <button @click="lastGenerated = null" class="close-success">Cerrar</button>
      </div>
    </div>
  </section>
</template>

<script setup>
import { reactive, ref, onMounted, computed } from 'vue';
import { useVeterinaryStore } from '@/stores/veterinary';
import { useAnimalsStore } from '@/stores/animals';
import animalService from '@/services/animalService';
import { jsPDF } from 'jspdf';
import DesplegableGovco from '../common/DesplegableGovco.vue';
import ButtonGovCo from '../common/ButtonGovCo.vue';

const veterinaryStore = useVeterinaryStore();
const animalsStore = useAnimalsStore();

const loading = ref(false);
const loadingData = ref(true);
const animals = ref([]);
const lastGenerated = ref(null);

const form = reactive({
  animalId: '',
  type: ''
});

const errors = reactive({
  animalId: '',
  type: ''
});

const certificateTypes = [
  { value: 'vaccination', text: 'Vacunación' },
  { value: 'sterilization', text: 'Esterilización' },
  { value: 'health', text: 'Salud general' }
];

const animalOptions = computed(() => {
  return animals.value.map(animal => ({
    value: animal.id,
    text: `${animal.nombre || 'Sin nombre'} - ${animal.codigo_unico}`
  }));
});

const selectedAnimalInfo = computed(() => {
  if (!form.animalId) return null;
  return animals.value.find(a => a.id === form.animalId);
});

const validationMessage = computed(() => {
  if (!form.type || !selectedAnimalInfo.value) return null;

  const animal = selectedAnimalInfo.value;

  switch (form.type) {
    case 'sterilization':
      if (!animal.esterilizacion) {
        return {
          type: 'error',
          icon: '⚠️',
          title: 'Animal no esterilizado',
          message: 'Este animal no está registrado como esterilizado. No se puede generar el certificado.'
        };
      }
      return {
        type: 'success',
        icon: '✅',
        title: 'Animal esterilizado',
        message: 'El animal está registrado como esterilizado. Puede generar el certificado.'
      };

    case 'vaccination':
      return {
        type: 'info',
        icon: '💉',
        title: 'Certificado de vacunación',
        message: 'Se generará un certificado con todas las vacunas registradas del animal.'
      };

    case 'health':
      return {
        type: 'info',
        icon: '🩺',
        title: 'Certificado de salud',
        message: 'Se generará un certificado con el resumen del estado de salud actual del animal.'
      };

    default:
      return null;
  }
});

const canGenerate = computed(() => {
  if (!form.animalId || !form.type) return false;
  
  if (form.type === 'sterilization' && selectedAnimalInfo.value) {
    return selectedAnimalInfo.value.esterilizacion === true;
  }
  
  return true;
});

async function loadData() {
  loadingData.value = true;
  console.log('📄 CertificateGenerator: Cargando datos iniciales...');

  try {
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
      nombre: animal.nombre || animal.name,
      codigo_unico: animal.codigo_unico || animal.codigo_chip || 'Sin código',
      especie: animal.especie,
      esterilizacion: animal.esterilizacion
    }));
    
    console.log('✅ Animales procesados:', animals.value.length);

  } catch (error) {
    console.error('❌ Error cargando datos:', error);
    alert('Error al cargar datos. Por favor recargue la página.');
  } finally {
    loadingData.value = false;
    console.log('✅ CertificateGenerator: Carga de datos completada');
  }
}

function onAnimalChange() {
  errors.animalId = '';
}

function validateForm() {
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

  if (form.type === 'sterilization' && selectedAnimalInfo.value && !selectedAnimalInfo.value.esterilizacion) {
    errors.animalId = 'El animal seleccionado no está esterilizado';
    isValid = false;
  }

  return isValid;
}

function resetForm() {
  Object.assign(form, {
    animalId: '',
    type: ''
  });
  
  Object.keys(errors).forEach(key => errors[key] = '');
  lastGenerated.value = null;
}

// Función helper para cargar imágenes como base64
function loadImageAsBase64(url) {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.crossOrigin = 'Anonymous';
    img.onload = () => {
      const canvas = document.createElement('canvas');
      canvas.width = img.width;
      canvas.height = img.height;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(img, 0, 0);
      try {
        const dataURL = canvas.toDataURL('image/png');
        resolve(dataURL);
      } catch (e) {
        reject(e);
      }
    };
    img.onerror = () => reject(new Error('No se pudo cargar la imagen'));
    img.src = url;
  });
}

// Funciones de formateo
function formatLabel(value) {
  if (!value) return 'No especificado';
  const labels = {
    canino: 'Canino',
    felino: 'Felino',
    equino: 'Equino',
    otro: 'Otro'
  };
  return labels[value] || value;
}

function formatDate(dateString) {
  if (!dateString) return 'No especificada';
  try {
    return new Date(dateString).toLocaleDateString('es-CO', {
      day: '2-digit',
      month: 'long',
      year: 'numeric'
    });
  } catch {
    return dateString;
  }
}

// Generar certificado según tipo
async function generateCertificate() {
  if (!validateForm()) {
    return;
  }

  loading.value = true;

  try {
    console.log('📄 Generando certificado:', form);

    const animal = selectedAnimalInfo.value;
    
    // Cargar datos necesarios
    let historial = null;
    try {
      historial = await veterinaryStore.fetchHistorialClinico(form.animalId);
    } catch (e) {
      console.error('Error al cargar historial:', e);
      alert('Error al cargar el historial clínico del animal');
      return;
    }

    // Generar el PDF según el tipo
    let success = false;
    let message = '';

    switch (form.type) {
      case 'vaccination':
        success = await generateVaccinationCertificate(animal, historial);
        message = 'Certificado de vacunación generado exitosamente';
        break;
        
      case 'sterilization':
        success = await generateSterilizationCertificate(animal, historial);
        message = 'Certificado de esterilización generado exitosamente';
        break;
        
      case 'health':
        success = await generateHealthCertificate(animal, historial);
        message = 'Certificado de salud generado exitosamente';
        break;
        
      default:
        throw new Error('Tipo de certificado no soportado');
    }

    if (success) {
      lastGenerated.value = {
        type: form.type,
        animalId: form.animalId,
        message: message,
        timestamp: new Date().toLocaleString('es-CO')
      };
      
      setTimeout(() => {
        resetForm();
      }, 3000);
    }
  } catch (e) {
    console.error('❌ Error generando certificado:', e);
    alert(`Error al generar el certificado: ${e.message || 'Error desconocido'}`);
  } finally {
    loading.value = false;
  }
}

// Certificado de Vacunación
async function generateVaccinationCertificate(animal, historial) {
  const doc = new jsPDF();
  const pageWidth = doc.internal.pageSize.getWidth();
  
  const primaryColor = [51, 102, 204];
  const secondaryColor = [0, 72, 132];
  const grayColor = [100, 100, 100];

  let y = 20;

  // Header
  doc.setFillColor(...primaryColor);
  doc.rect(0, 0, pageWidth, 35, 'F');
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(20);
  doc.setFont('helvetica', 'bold');
  doc.text('CERTIFICADO DE VACUNACIÓN', pageWidth / 2, 15, { align: 'center' });
  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.text(`Fecha de emisión: ${new Date().toLocaleDateString('es-CO')}`, pageWidth / 2, 28, { align: 'center' });

  y = 50;

  // Información del animal
  doc.setFontSize(12);
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(...secondaryColor);
  doc.text('INFORMACIÓN DEL ANIMAL', 20, y);
  y += 8;

  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.setTextColor(0, 0, 0);
  doc.text(`Nombre: ${animal.nombre || 'Sin nombre'}`, 20, y);
  doc.text(`Código: ${animal.codigo_unico}`, 110, y);
  y += 6;
  doc.text(`Especie: ${formatLabel(animal.especie)}`, 20, y);
  y += 10;

  // Tabla de vacunas
  const vacunas = historial?.vacunas || [];
  
  if (vacunas.length === 0) {
    doc.setTextColor(255, 0, 0);
    doc.text('No hay vacunas registradas para este animal', 20, y);
  } else {
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(...secondaryColor);
    doc.text('REGISTRO DE VACUNAS', 20, y);
    y += 10;

    // Headers de la tabla
    doc.setFillColor(232, 240, 254);
    doc.rect(15, y - 5, pageWidth - 30, 8, 'F');
    doc.setFontSize(9);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(0, 0, 0);
    doc.text('Fecha', 20, y);
    doc.text('Vacuna', 50, y);
    doc.text('Dosis', 120, y);
    doc.text('Próxima', 150, y);
    y += 8;

    // Datos de vacunas
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    
    vacunas.forEach((vacuna, index) => {
      if (y > 270) {
        doc.addPage();
        y = 20;
      }

      const fecha = formatDate(vacuna.fecha_aplicacion || vacuna.pivot?.fecha_aplicacion);
      const nombre = vacuna.nombre_vacuna || vacuna.tipo_vacuna?.nombre || 'Vacuna';
      const dosis = vacuna.numero_dosis || vacuna.pivot?.numero_dosis || '-';
      const proxima = vacuna.fecha_proxima || vacuna.pivot?.fecha_proxima || '-';

      if (index % 2 === 0) {
        doc.setFillColor(248, 249, 250);
        doc.rect(15, y - 4, pageWidth - 30, 6, 'F');
      }

      doc.text(fecha.substring(0, 15), 20, y);
      doc.text(nombre.substring(0, 30), 50, y);
      doc.text(String(dosis), 120, y);
      doc.text(proxima === '-' ? '-' : formatDate(proxima).substring(0, 15), 150, y);
      y += 6;
    });
  }

  // Footer
  const footerY = doc.internal.pageSize.getHeight() - 15;
  doc.setDrawColor(...primaryColor);
  doc.setLineWidth(0.5);
  doc.line(15, footerY - 5, pageWidth - 15, footerY - 5);
  doc.setTextColor(...grayColor);
  doc.setFontSize(8);
  doc.text('Sistema de Bienestar Animal', pageWidth / 2, footerY, { align: 'center' });

  doc.save(`certificado_salud_${animal.codigo_unico}.pdf`);
  return true;
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

.animal-info-card {
  margin: 1.5rem 1.5rem 0 1.5rem;
  padding: 1.5rem;
  background: #f8fafe;
  border-radius: 8px;
  border-left: 4px solid #3366cc;
}

.animal-info-card h4 {
  margin: 0 0 1rem 0;
  color: #004884;
  font-size: 1rem;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.info-item strong {
  color: #666;
  font-size: 0.85rem;
}

.info-item span {
  color: #333;
  font-weight: 500;
}

.status-yes {
  color: #059669;
  font-weight: 600;
}

.status-no {
  color: #DC2626;
  font-weight: 600;
}

.validation-alert {
  margin: 1.5rem;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}

.validation-alert.success {
  background: #D1FAE5;
  border-left: 4px solid #059669;
}

.validation-alert.error {
  background: #FEE2E2;
  border-left: 4px solid #DC2626;
}

.validation-alert.info {
  background: #DBEAFE;
  border-left: 4px solid #2563EB;
}

.alert-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
}

.alert-content {
  flex: 1;
}

.alert-content strong {
  display: block;
  margin-bottom: 0.25rem;
  font-size: 0.95rem;
}

.validation-alert.success .alert-content strong {
  color: #065F46;
}

.validation-alert.error .alert-content strong {
  color: #991B1B;
}

.validation-alert.info .alert-content strong {
  color: #1E40AF;
}

.alert-content p {
  margin: 0;
  font-size: 0.9rem;
  color: #4B5563;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
}

.success-card {
  background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
  border-radius: 12px;
  padding: 2rem;
  margin-top: 1.5rem;
  display: flex;
  gap: 1.5rem;
  align-items: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  animation: slideDown 0.4s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.success-icon {
  font-size: 3rem;
  flex-shrink: 0;
}

.success-content {
  flex: 1;
}

.success-content h4 {
  margin: 0 0 0.5rem 0;
  color: #065F46;
  font-size: 1.25rem;
}

.success-content p {
  margin: 0 0 1rem 0;
  color: #047857;
}

.close-success {
  padding: 0.5rem 1.5rem;
  background: #059669;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.2s;
}

.close-success:hover {
  background: #047857;
}

@media (max-width: 768px) {
  .certificate-generator {
    padding: 1rem;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column-reverse;
  }

  .form-actions button {
    width: 100%;
  }

  .success-card {
    flex-direction: column;
    text-align: center;
  }
}
</style>
  doc.setTextColor(...grayColor);
  doc.setFontSize(8);
  doc.text('Sistema de Bienestar Animal', pageWidth / 2, footerY, { align: 'center' });

  // Guardar
  doc.save(`certificado_vacunacion_${animal.codigo_unico}.pdf`);
  return true;
}

// Certificado de Esterilización
async function generateSterilizationCertificate(animal, historial) {
  const doc = new jsPDF();
  const pageWidth = doc.internal.pageSize.getWidth();
  
  const primaryColor = [124, 58, 237]; // Purple
  const secondaryColor = [107, 33, 168];
  const grayColor = [100, 100, 100];

  let y = 20;

  // Header
  doc.setFillColor(...primaryColor);
  doc.rect(0, 0, pageWidth, 35, 'F');
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(20);
  doc.setFont('helvetica', 'bold');
  doc.text('CERTIFICADO DE ESTERILIZACIÓN', pageWidth / 2, 15, { align: 'center' });
  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.text(`Fecha de emisión: ${new Date().toLocaleDateString('es-CO')}`, pageWidth / 2, 28, { align: 'center' });

  y = 50;

  // Información del animal
  doc.setFontSize(12);
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(...secondaryColor);
  doc.text('INFORMACIÓN DEL ANIMAL', 20, y);
  y += 8;

  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.setTextColor(0, 0, 0);
  doc.text(`Nombre: ${animal.nombre || 'Sin nombre'}`, 20, y);
  doc.text(`Código: ${animal.codigo_unico}`, 110, y);
  y += 6;
  doc.text(`Especie: ${formatLabel(animal.especie)}`, 20, y);
  doc.text(`Sexo: ${formatLabel(animal.sexo)}`, 110, y);
  y += 10;

  // Buscar cirugía de esterilización
  const cirugias = historial?.cirugias || [];
  const cirugia = cirugias.find(c => 
    (c.tipo_cirugia === 'esterilizacion' || c.tipo_cirugia === 'castracion') &&
    c.estado === 'realizada' &&
    c.resultado === 'exitosa'
  );

  if (cirugia) {
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(...secondaryColor);
    doc.text('INFORMACIÓN DEL PROCEDIMIENTO', 20, y);
    y += 10;

    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(0, 0, 0);
    doc.text(`Fecha de realización: ${formatDate(cirugia.fecha_realizacion)}`, 20, y);
    y += 6;
    doc.text(`Tipo: ${formatLabel(cirugia.tipo_cirugia)}`, 20, y);
    y += 6;
    
    const cirujano = cirugia.cirujano?.nombre_completo || 
                     `${cirugia.cirujano?.nombres || ''} ${cirugia.cirujano?.apellidos || ''}`.trim() ||
                     'No especificado';
    doc.text(`Veterinario: ${cirujano}`, 20, y);
    y += 6;

    if (cirugia.cirujano?.numero_tarjeta_profesional) {
      doc.text(`Tarjeta profesional: ${cirugia.cirujano.numero_tarjeta_profesional}`, 20, y);
      y += 6;
    }

    if (cirugia.descripcion) {
      y += 4;
      doc.text('Descripción:', 20, y);
      y += 6;
      const splitDesc = doc.splitTextToSize(cirugia.descripcion, pageWidth - 40);
      doc.text(splitDesc, 20, y);
      y += splitDesc.length * 5;
    }
  } else {
    doc.setTextColor(255, 0, 0);
    doc.text('⚠️ No se encontró registro de cirugía de esterilización', 20, y);
  }

  // Certificación
  y += 20;
  doc.setFillColor(243, 232, 255);
  doc.rect(15, y - 5, pageWidth - 30, 25, 'F');
  doc.setFontSize(11);
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(...secondaryColor);
  doc.text('CERTIFICACIÓN', pageWidth / 2, y, { align: 'center' });
  y += 8;
  doc.setFontSize(9);
  doc.setFont('helvetica', 'normal');
  const certText = `Se certifica que el animal identificado con código ${animal.codigo_unico} ha sido esterilizado satisfactoriamente.`;
  const splitCert = doc.splitTextToSize(certText, pageWidth - 50);
  doc.text(splitCert, pageWidth / 2, y, { align: 'center' });

  // Footer
  const footerY = doc.internal.pageSize.getHeight() - 15;
  doc.setDrawColor(...primaryColor);
  doc.setLineWidth(0.5);
  doc.line(15, footerY - 5, pageWidth - 15, footerY - 5);
  doc.setTextColor(...grayColor);
  doc.setFontSize(8);
  doc.text('Sistema de Bienestar Animal', pageWidth / 2, footerY, { align: 'center' });

  doc.save(`certificado_esterilizacion_${animal.codigo_unico}.pdf`);
  return true;
}

// Certificado de Salud
async function generateHealthCertificate(animal, historial) {
  const doc = new jsPDF();
  const pageWidth = doc.internal.pageSize.getWidth();
  
  const primaryColor = [5, 150, 105]; // Green
  const secondaryColor = [6, 95, 70];
  const grayColor = [100, 100, 100];

  let y = 20;

  // Header
  doc.setFillColor(...primaryColor);
  doc.rect(0, 0, pageWidth, 35, 'F');
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(20);
  doc.setFont('helvetica', 'bold');
  doc.text('CERTIFICADO DE SALUD', pageWidth / 2, 15, { align: 'center' });
  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.text(`Fecha de emisión: ${new Date().toLocaleDateString('es-CO')}`, pageWidth / 2, 28, { align: 'center' });

  y = 50;

  // Información del animal
  doc.setFontSize(12);
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(...secondaryColor);
  doc.text('INFORMACIÓN DEL ANIMAL', 20, y);
  y += 8;

  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.setTextColor(0, 0, 0);
  doc.text(`Nombre: ${animal.nombre || 'Sin nombre'}`, 20, y);
  doc.text(`Código: ${animal.codigo_unico}`, 110, y);
  y += 6;
  doc.text(`Especie: ${formatLabel(animal.especie)}`, 20, y);
  doc.text(`Sexo: ${formatLabel(animal.sexo)}`, 110, y);
  y += 10;

  // Estado de salud general
  doc.setFontSize(12);
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(...secondaryColor);
  doc.text('ESTADO DE SALUD', 20, y);
  y += 8;

  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.setTextColor(0, 0, 0);
  doc.text(`Estado general: ${historial?.estado_general || 'No especificado'}`, 20, y);
  y += 6;
  doc.text(`Esterilizado: ${animal.esterilizacion ? 'Sí' : 'No'}`, 20, y);
  y += 10;

  // Última consulta
  const consultas = historial?.consultas || [];
  if (consultas.length > 0) {
    const ultimaConsulta = consultas[0];
    
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(...secondaryColor);
    doc.text('ÚLTIMA CONSULTA', 20, y);
    y += 8;

    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(0, 0, 0);
    doc.text(`Fecha: ${formatDate(ultimaConsulta.fecha_consulta)}`, 20, y);
    y += 6;
    doc.text(`Diagnóstico: ${ultimaConsulta.diagnostico || 'No especificado'}`, 20, y);
    y += 10;
  }

  // Resumen de vacunas
  const vacunas = historial?.vacunas || [];
  if (vacunas.length > 0) {
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(...secondaryColor);
    doc.text('VACUNAS RECIENTES', 20, y);
    y += 8;

    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    vacunas.slice(0, 5).forEach(vacuna => {
      const nombre = vacuna.nombre_vacuna || vacuna.tipo_vacuna?.nombre || 'Vacuna';
      const fecha = formatDate(vacuna.fecha_aplicacion || vacuna.pivot?.fecha_aplicacion);
      doc.text(`• ${nombre} - ${fecha}`, 20, y);
      y += 5;
    });
    y += 5;
  }

  // Certificación
  y += 10;
  doc.setFillColor(209, 250, 229);
  doc.rect(15, y - 5, pageWidth - 30, 25, 'F');
  doc.setFontSize(11);
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(...secondaryColor);
  doc.text('CERTIFICACIÓN', pageWidth / 2, y, { align: 'center' });
  y += 8;
  doc.setFontSize(9);
  doc.setFont('helvetica', 'normal');
  const certText = `Se certifica que el animal identificado con código ${animal.codigo_unico} se encuentra en buen estado de salud según los registros del sistema.`;
  const splitCert = doc.splitTextToSize(certText, pageWidth - 50);
  doc.text(splitCert, pageWidth / 2, y, { align: 'center' });

  // Footer
  const footerY = doc.internal.pageSize.getHeight() - 15;
  doc.setDrawColor(...primaryColor);
  doc.setLineWidth(0.5);
  doc.line(15, footerY - 5, pageWidth - 15, footerY - 5);