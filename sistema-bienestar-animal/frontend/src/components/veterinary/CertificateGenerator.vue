<template>
  <section class="certificate-generator">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Generacion de certificados</h2>
      <p class="text2-tipografia-govco">
        Genere certificados de vacunacion, esterilizacion o salud general en formato PDF.
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

        <!-- Informacion del animal seleccionado -->
        <div v-if="selectedAnimalInfo" class="animal-info-card">
          <h4>📋 Informacion del animal</h4>
          <div class="info-grid">
            <div class="info-item">
              <strong>Nombre:</strong>
              <span>{{ selectedAnimalInfo.nombre || 'Sin nombre' }}</span>
            </div>
            <div class="info-item">
              <strong>Codigo unico:</strong>
              <span>{{ selectedAnimalInfo.codigo_unico }}</span>
            </div>
            <div class="info-item">
              <strong>Especie:</strong>
              <span>{{ formatLabel(selectedAnimalInfo.especie) }}</span>
            </div>
            <div class="info-item">
              <strong>Esterilizado:</strong>
              <span :class="selectedAnimalInfo.esterilizacion ? 'status-yes' : 'status-no'">
                {{ selectedAnimalInfo.esterilizacion ? 'Si' : 'No' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Alerta de validacion segun tipo -->
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
  { value: 'vaccination', text: 'Vacunacion' },
  { value: 'sterilization', text: 'Esterilizacion' },
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
          message: 'Este animal no esta registrado como esterilizado. No se puede generar el certificado.'
        };
      }
      return {
        type: 'success',
        icon: '✅',
        title: 'Animal esterilizado',
        message: 'El animal esta registrado como esterilizado. Puede generar el certificado.'
      };

    case 'vaccination':
      return {
        type: 'info',
        icon: '💉',
        title: 'Certificado de vacunacion',
        message: 'Se generara un certificado con todas las vacunas registradas del animal.'
      };

    case 'health':
      return {
        type: 'info',
        icon: '🩺',
        title: 'Certificado de salud',
        message: 'Se generara un certificado con el resumen del estado de salud actual del animal.'
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
  console.log('🔄 CertificateGenerator: Cargando datos iniciales...');

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
      codigo_unico: animal.codigo_unico || animal.codigo_chip || 'Sin codigo',
      especie: animal.especie,
      sexo: animal.sexo,
      esterilizacion: animal.esterilizacion,
      estado_salud: animal.estado_salud
    }));
    
    console.log('✅ Animales procesados:', animals.value.length);

  } catch (error) {
    console.error('❌ Error cargando datos:', error);
    alert('Error al cargar datos. Por favor recargue la pagina.');
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
    errors.animalId = 'El animal seleccionado no esta esterilizado';
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

// Funciones de formateo
function formatLabel(value) {
  if (!value) return 'No especificado';
  const labels = {
    canino: 'Canino',
    felino: 'Felino',
    equino: 'Equino',
    otro: 'Otro',
    macho: 'Macho',
    hembra: 'Hembra',
    desconocido: 'Desconocido',
    esterilizacion: 'Esterilizacion',
    castracion: 'Castracion',
    critico: 'Critico',
    grave: 'Grave',
    estable: 'Estable',
    bueno: 'Bueno',
    excelente: 'Excelente'
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

// Generar certificado segun tipo
async function generateCertificate() {
  if (!validateForm()) {
    return;
  }

  loading.value = true;

  try {
    console.log('🔄 Generando certificado:', form);

    const animal = selectedAnimalInfo.value;
    
    // Cargar datos necesarios
    let historial = null;
    try {
      historial = await veterinaryStore.fetchHistorialClinico(form.animalId);
      console.log('✅ Historial cargado:', historial);
    } catch (e) {
      console.error('Error al cargar historial:', e);
      alert('Error al cargar el historial clinico del animal');
      return;
    }

    // Generar el PDF segun el tipo
    let success = false;
    let message = '';

    switch (form.type) {
      case 'vaccination':
        success = await generateVaccinationCertificate(animal, historial);
        message = 'Certificado de vacunacion generado exitosamente';
        break;
        
      case 'sterilization':
        success = await generateSterilizationCertificate(animal, historial);
        message = 'Certificado de esterilizacion generado exitosamente';
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

// Certificado de Vacunacion
async function generateVaccinationCertificate(animal, historial) {
  const doc = new jsPDF();
  const pageWidth = doc.internal.pageSize.getWidth();
  
  const primaryColor = [51, 102, 204]; // Azul
  const secondaryColor = [0, 72, 132];
  const grayColor = [100, 100, 100];

  let y = 20;

  // Header
  doc.setFillColor(...primaryColor);
  doc.rect(0, 0, pageWidth, 35, 'F');
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(20);
  doc.setFont('helvetica', 'bold');
  doc.text('CERTIFICADO DE VACUNACION', pageWidth / 2, 15, { align: 'center' });
  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.text(`Fecha de emision: ${new Date().toLocaleDateString('es-CO')}`, pageWidth / 2, 28, { align: 'center' });

  y = 50;

  // Informacion del animal
  doc.setFontSize(12);
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(...secondaryColor);
  doc.text('INFORMACION DEL ANIMAL', 20, y);
  y += 8;

  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.setTextColor(0, 0, 0);
  doc.text(`Nombre: ${animal.nombre || 'Sin nombre'}`, 20, y);
  doc.text(`Codigo: ${animal.codigo_unico}`, 110, y);
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
    doc.text('Fecha', 18, y);
    doc.text('Vacuna', 60, y);
    doc.text('Dosis', 120, y);
    doc.text('Proxima', 140, y);
    y += 8;

    // Datos de vacunas
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    
    vacunas.forEach((vacuna, index) => {
      if (y > 270) {
        doc.addPage();
        y = 20;
      }

      const fecha = formatDate(vacuna.fecha_aplicacion);
      const nombre = vacuna.nombre_vacuna || vacuna.tipo_vacuna?.nombre || 'Vacuna';
      const dosis = vacuna.numero_dosis || '-';
      const proxima = vacuna.fecha_proxima_dosis || '-';

      if (index % 2 === 0) {
        doc.setFillColor(248, 249, 250);
        doc.rect(15, y - 4, pageWidth - 30, 6, 'F');
      }

      doc.text(fecha, 18, y);
      doc.text(nombre.substring(0, 25), 60, y);
      doc.text(String(dosis), 120, y);
      doc.text(proxima === '-' ? 'No aplica' : formatDate(proxima), 140, y);
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

  doc.save(`certificado_vacunacion_${animal.codigo_unico}.pdf`);
  return true;
}

// Certificado de Esterilizacion
async function generateSterilizationCertificate(animal, historial) {
  const doc = new jsPDF();
  const pageWidth = doc.internal.pageSize.getWidth();
  
  const primaryColor = [51, 102, 204]; // Azul
  const secondaryColor = [0, 72, 132];
  const grayColor = [100, 100, 100];

  let y = 20;

  // Header
  doc.setFillColor(...primaryColor);
  doc.rect(0, 0, pageWidth, 35, 'F');
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(20);
  doc.setFont('helvetica', 'bold');
  doc.text('CERTIFICADO DE ESTERILIZACION', pageWidth / 2, 15, { align: 'center' });
  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.text(`Fecha de emision: ${new Date().toLocaleDateString('es-CO')}`, pageWidth / 2, 28, { align: 'center' });

  y = 50;

  // Informacion del animal
  doc.setFontSize(12);
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(...secondaryColor);
  doc.text('INFORMACION DEL ANIMAL', 20, y);
  y += 8;

  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.setTextColor(0, 0, 0);
  doc.text(`Nombre: ${animal.nombre || 'Sin nombre'}`, 20, y);
  doc.text(`Codigo: ${animal.codigo_unico}`, 110, y);
  y += 6;
  doc.text(`Especie: ${formatLabel(animal.especie)}`, 20, y);
  // ✅ CORREGIDO: Usar animal.sexo del modelo
  doc.text(`Sexo: ${formatLabel(animal.sexo || 'No especificado')}`, 110, y);
  y += 10;

  // Buscar cirugia de esterilizacion
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
    doc.text('INFORMACION DEL PROCEDIMIENTO', 20, y);
    y += 10;

    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(0, 0, 0);
    doc.text(`Fecha de realizacion: ${formatDate(cirugia.fecha_realizacion)}`, 20, y);
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
      doc.text('Descripcion:', 20, y);
      y += 6;
      const splitDesc = doc.splitTextToSize(cirugia.descripcion, pageWidth - 40);
      doc.text(splitDesc, 20, y);
      y += splitDesc.length * 5;
    }
  } else {
    // ✅ NUEVO: Manejar animales esterilizados sin cirugia registrada
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(...secondaryColor);
    doc.text('INFORMACION DEL PROCEDIMIENTO', 20, y);
    y += 10;

    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(0, 0, 0);
    const message = 'El animal ingreso al sistema ya esterilizado.';
    const splitMsg = doc.splitTextToSize(message, pageWidth - 40);
    doc.text(splitMsg, 20, y);
    y += splitMsg.length * 6;
    doc.text('No se encuentra registro de cirugia en el sistema.', 20, y);
  }

  // Certificacion
  y += 20;
  doc.setFillColor(232, 240, 254);
  doc.rect(15, y - 5, pageWidth - 30, 25, 'F');
  doc.setFontSize(11);
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(...secondaryColor);
  doc.text('CERTIFICACION', pageWidth / 2, y, { align: 'center' });
  y += 8;
  doc.setFontSize(9);
  doc.setFont('helvetica', 'normal');
  const certText = `Se certifica que el animal identificado con codigo ${animal.codigo_unico} esta registrado como esterilizado en el sistema.`;
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
  
  const primaryColor = [51, 102, 204]; // Azul
  const secondaryColor = [0, 72, 132];
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
  doc.text(`Fecha de emision: ${new Date().toLocaleDateString('es-CO')}`, pageWidth / 2, 28, { align: 'center' });

  y = 50;

  // Informacion del animal
  doc.setFontSize(12);
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(...secondaryColor);
  doc.text('INFORMACION DEL ANIMAL', 20, y);
  y += 8;

  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.setTextColor(0, 0, 0);
  doc.text(`Nombre: ${animal.nombre || 'Sin nombre'}`, 20, y);
  doc.text(`Codigo: ${animal.codigo_unico}`, 110, y);
  y += 6;
  doc.text(`Especie: ${formatLabel(animal.especie)}`, 20, y);
  // ✅ CORREGIDO: Usar animal.sexo del modelo
  doc.text(`Sexo: ${formatLabel(animal.sexo || 'No especificado')}`, 110, y);
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
  doc.text(`Estado general: ${formatLabel(animal.estado_salud || 'No especificado')}`, 20, y);
  y += 6;
  doc.text(`Esterilizado: ${animal.esterilizacion ? 'Si' : 'No'}`, 20, y);
  y += 10;

  const consultas = historial?.consultas || [];
  if (consultas.length > 0) {
    const ultimaConsulta = consultas[0];
    
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(...secondaryColor);
    doc.text('ULTIMA CONSULTA', 20, y);
    y += 8;

    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(0, 0, 0);
    
    doc.text(`Fecha: ${formatDate(ultimaConsulta.fecha_consulta)}`, 20, y);
    y += 6;
    
    doc.text(`Tipo: ${formatLabel(ultimaConsulta.tipo_consulta || 'General')}`, 20, y);
    y += 6;
    
    if (ultimaConsulta.motivo_consulta) {
      doc.text('Motivo:', 20, y);
      y += 5;
      const splitMotivo = doc.splitTextToSize(ultimaConsulta.motivo_consulta, pageWidth - 40);
      doc.text(splitMotivo, 25, y);
      y += splitMotivo.length * 5 + 3;
    }
    
    if (ultimaConsulta.diagnostico) {
      doc.text('Diagnostico:', 20, y);
      y += 5;
      const splitDiag = doc.splitTextToSize(ultimaConsulta.diagnostico, pageWidth - 40);
      doc.text(splitDiag, 25, y);
      y += splitDiag.length * 5 + 3;
    }

    // Signos vitales si existen
    if (ultimaConsulta.peso || ultimaConsulta.temperatura || 
        ultimaConsulta.frecuencia_cardiaca || ultimaConsulta.frecuencia_respiratoria) {
      y += 3;
      doc.setFont('helvetica', 'bold');
      doc.text('Signos vitales:', 20, y);
      y += 5;
      doc.setFont('helvetica', 'normal');
      
      if (ultimaConsulta.peso) {
        doc.text(`Peso: ${ultimaConsulta.peso} kg`, 25, y);
        y += 5;
      }
      if (ultimaConsulta.temperatura) {
        doc.text(`Temperatura: ${ultimaConsulta.temperatura} C`, 25, y);
        y += 5;
      }
      if (ultimaConsulta.frecuencia_cardiaca) {
        doc.text(`Frecuencia cardiaca: ${ultimaConsulta.frecuencia_cardiaca} lpm`, 25, y);
        y += 5;
      }
      if (ultimaConsulta.frecuencia_respiratoria) {
        doc.text(`Frecuencia respiratoria: ${ultimaConsulta.frecuencia_respiratoria} rpm`, 25, y);
        y += 5;
      }
      y += 3;
    }

    if (ultimaConsulta.observaciones) {
      doc.text('Observaciones:', 20, y);
      y += 5;
      const splitObs = doc.splitTextToSize(ultimaConsulta.observaciones, pageWidth - 40);
      doc.text(splitObs, 25, y);
      y += splitObs.length * 5;
    }
    
    y += 5;
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
      const fecha = formatDate(vacuna.fecha_aplicacion);
      doc.text(`- ${nombre} - ${fecha}`, 20, y);
      y += 5;
    });
    y += 5;
  }

  // Certificacion
  y += 10;
  doc.setFillColor(232, 240, 254);
  doc.rect(15, y - 5, pageWidth - 30, 25, 'F');
  doc.setFontSize(11);
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(...secondaryColor);
  doc.text('CERTIFICACION', pageWidth / 2, y, { align: 'center' });
  y += 8;
  doc.setFontSize(9);
  doc.setFont('helvetica', 'normal');
  const certText = `Se certifica que el animal identificado con codigo ${animal.codigo_unico} se encuentra registrado en el sistema con el estado de salud indicado.`;
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