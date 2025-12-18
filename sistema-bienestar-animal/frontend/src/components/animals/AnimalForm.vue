<template>
  <section class="animal-form">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Registro de animal</h2>
      <p class="text2-tipografia-govco">Complete los campos obligatorios (*)</p>
    </div>

    <form ref="formEl" @submit.prevent="onSubmit" novalidate>
      
      <!-- SECCIÓN 1: IDENTIFICACIÓN -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Identificación y características básicas</h3>
        
        <div class="form-grid">
          <!-- Nombre -->
          <InputGovCo
            id="name"
            v-model="form.name"
            label="Nombre"
            type="text"
            placeholder="Nombre del animal"
          />

          <!-- Especie -->
          <DesplegableGovco
            id="species-dropdown"
            v-model="form.species"
            label="Especie"
            :options="speciesOptions"
            placeholder="Seleccione una especie"
            :required="true"
            :error="!!errors.species"
            :alert-text="errors.species"
          />

          <!-- Raza -->
          <InputGovCo
            id="breed"
            v-model="form.breed"
            label="Raza"
            type="text"
            placeholder="Criollo, Mestizo"
            :required="true"
            :error="!!errors.breed"
            :alert-text="errors.breed"
          />

          <!-- Color -->
          <InputGovCo
            id="color"
            v-model="form.color"
            label="Color"
            type="text"
            placeholder="Café, Blanco"
            :required="true"
            :error="!!errors.color"
            :alert-text="errors.color"
          />

          <!-- Sexo -->
          <DesplegableGovco
            id="sex-dropdown"
            v-model="form.sex"
            label="Sexo"
            :options="sexOptions"
            placeholder="Seleccione el sexo"
            :required="true"
            :error="!!errors.sex"
            :alert-text="errors.sex"
          />

          <!-- Tamaño -->
          <DesplegableGovco
            id="size-dropdown"
            v-model="form.size"
            label="Tamaño"
            :options="sizeOptions"
            placeholder="Seleccione un tamaño"
            :required="true"
            :error="!!errors.size"
            :alert-text="errors.size"
          />

          

          <!-- Esterilización -->
          <DesplegableGovco
            id="sterilizacion-dropdown"
            v-model="form.sterilizacion"
            label="Esterilización"
            :options="sterilizacionOptions"
            placeholder="Seleccione una opción"
            :required="true"
            :error="!!errors.sterilizacion"
            :alert-text="errors.sterilizacion"
          />
          <!-- Edad estimada -->
          <InputGovCo
            id="age"
            v-model="form.estimatedAge"
            label="Edad estimada"
            type="text"
            placeholder="2 años, 6 meses"
            help-text="Años o meses aproximados"
            :required="true"
            :error="!!errors.estimatedAge"
            :alert-text="errors.estimatedAge"
          />
        </div>
      </div>

      <!-- SECCIÓN 2: RESCATE CON MAPA -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Información de rescate</h3>
        
        <div class="form-grid">
          <!-- Estado -->
          <DesplegableGovco
            id="status-dropdown"
            v-model="form.status"
            label="Estado"
            :options="statusOptions"
            placeholder="Seleccione un estado"
            :required="true"
            :error="!!errors.status"
            :alert-text="errors.status"
          />

          <!-- Condición -->
          <DesplegableGovco
            id="condition-dropdown"
            v-model="form.condition"
            label="Condición"
            :options="conditionOptions"
            placeholder="Seleccione una condición"
            :required="true"
            :error="!!errors.condition"
            :alert-text="errors.condition"
          />

          <!-- Fecha de rescate -->
          <CalendarioGovco
            id="entry_date"
            input-id="entry_date_input"
            v-model="form.entry_date"
            label="Fecha de ingreso"
            placeholder="Seleccione una fecha"
            :required="true"
            :error="!!errors.entry_date"
            :alert-text="errors.entry_date"
          />

          <DesplegableGovco
            id="origin-dropdown"
            v-model="form.origin"
            label="Procedencia"
            :options="originOptions"
            placeholder="Seleccione una procedencia"
            :required="true"
            :error="!!errors.origin"
            :alert-text="errors.origin"
          />

          <!-- Ubicación con mapa -->
          <div class="full-width">
            <label class="label-desplegable-govco">
              Ubicación de rescate (GPS)<span aria-required="true">*</span>
            </label>

            <div class="map-container">
              <MapSelector
                v-model="form.coordinates"
                :initial-center="{ lat: 3.4516, lng: -76.5319 }"
                :zoom="15"
              />

              <p class="map-placeholder">
                <span v-if="form.coordinates">
                  📍 Lat: {{ form.coordinates.lat.toFixed(6) }},
                  Lng: {{ form.coordinates.lng.toFixed(6) }}
                </span>
                <span v-else>
                  🗺️ Haga clic en el mapa o presione "Usar mi ubicación actual"
                </span>
              </p>
            </div>

            <span v-if="errors.coordinates" class="alert-desplegable-govco">
              {{ errors.coordinates }}
            </span>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 3: OBSERVACIONES -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Observaciones</h3>

        <div class="form-grid">
          <InputGovCo
            id="description"
            v-model="form.description"
            label="Descripción"
            type="text"
            placeholder="Descripción general del animal"
            :required="false"
            :error="!!errors.description"
            :alert-text="errors.description"
          />

          <InputGovCo
            id="notes"
            v-model="form.notes"
            label="Notas adicionales"
            type="text"
            placeholder="Notas clínicas o contexto relevante"
            :required="false"
            :error="!!errors.notes"
            :alert-text="errors.notes"
          />
        </div>
      </div>

      <!-- SECCIÓN 4: FOTOS -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Documentación fotográfica</h3>
        
        <div class="form-grid">
          <div class="full-width">
            <FileUploader
              v-model="form.photos"
              accept="image/jpeg,image/jpg,image/png"
              :max-files="10"
              :max-size-m-b="10"
              label="Fotografías del animal"
              help-text="Opcional. JPG/PNG. Peso máximo: 10 MB por archivo"
              :required="false"
              :multiple="true"
            />
            <span v-if="errors.photos" class="alert-desplegable-govco">
              {{ errors.photos }}
            </span>
          </div>
        </div>
      </div>

      <!-- BOTONES -->
      <div class="form-actions">
        <ButtonGovCo
          type="button"
          variant="secondary"
          label="Limpiar formulario"
          :disabled="isSubmitting"
          @click="resetForm"
        />
        <ButtonGovCo
          type="submit"
          variant="primary"
          :label="isSubmitting ? 'Registrando...' : 'Registrar animal'"
          :loading="isSubmitting"
          :disabled="isSubmitting"
        />
      </div>
    </form>

    <!-- RESULTADO: QR Y DESCARGA DE DETALLES -->
    <div v-if="createdAnimal" class="result-card">
      <h3 class="h5-tipografia-govco section-title">Registro completado</h3>
      <div class="result-content">
        <div class="result-info">
          <p><strong>Código:</strong> {{ createdAnimal.codigo_unico || createdAnimal.id }}</p>
          <p><strong>Nombre:</strong> {{ createdAnimal.nombre || 'Sin nombre' }}</p>
          <p><strong>Especie:</strong> {{ createdAnimal.especie }}</p>

          <div class="result-actions">
            <ButtonGovCo
              type="button"
              variant="secondary"
              label="Descargar ficha PDF"
              @click="downloadAnimalPDF"
            />
            <!--
            <a v-if="detailsUrl" class="details-link" :href="detailsUrl" target="_blank" rel="noopener">
              Abrir endpoint de detalles
            </a>
            -->
          </div>
           
        </div>
       

        <div class="result-qr">
          <p class="qr-title"><strong>QR para acceder/descargar detalles</strong></p>
          <img v-if="qrUrl" :src="qrUrl" alt="QR del animal" />
          <p class="qr-help">Escanéalo para abrir los detalles del animal.</p>
        </div>
      </div>
    </div>

  </section>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { jsPDF } from 'jspdf';
import MapSelector from '../common/MapSelector.vue';
import InputGovCo from '../common/InputGovCo.vue';
import CalendarioGovco from '../common/CalendarioGovco.vue';
import DesplegableGovco from '../common/DesplegableGovco.vue';
import ButtonGovCo from '../common/ButtonGovCo.vue';
import FileUploader from '../common/FileUploader.vue';
import api from '@/services/api';

const formEl = ref(null);
const isSubmitting = ref(false);
const createdAnimal = ref(null);
const detailsUrl = ref('');
const qrUrl = ref('');

const speciesOptions = [
  { value: 'canino', text: 'Canino' },
  { value: 'felino', text: 'Felino' },
  { value: 'equino', text: 'Equino' },
  { value: 'otro', text: 'Otro' }
];

const sexOptions = [
  { value: 'macho', text: 'Macho' },
  { value: 'hembra', text: 'Hembra' }
];

const sizeOptions = [
  { value: 'pequenio', text: 'Pequeño' },
  { value: 'mediano', text: 'Mediano' },
  { value: 'grande', text: 'Grande' }
];

const statusOptions = [
  { value: 'en_calle', text: 'En calle' },
  { value: 'en_refugio', text: 'En refugio' },
  { value: 'en_adopcion', text: 'En adopción' },
  { value: 'adoptado', text: 'Adoptado' },
  { value: 'en_tratamiento', text: 'En tratamiento' },
  { value: 'fallecido', text: 'Fallecido' }
];

const conditionOptions = [
  // Importante: el backend valida: critico, grave, estable, bueno, excelente
  { value: 'excelente', text: 'Excelente' },
  { value: 'bueno', text: 'Bueno' },
  { value: 'estable', text: 'Estable' },
  { value: 'grave', text: 'Grave' },
  { value: 'critico', text: 'Crítico' }
];

const sterilizacionOptions = [
  { value: 1, text: 'Sí' },
  { value: 0, text: 'No' }
];

const originOptions = [
  { value: 'rescate', text: 'Rescate' },
  { value: 'entrega_voluntaria', text: 'Entrega voluntaria' },
  { value: 'otra', text: 'Otra' }
];

const form = reactive({
  name: '',
  species: '',
  breed: '',
  color: '',
  sex: '',
  size: '',
  estimatedAge: '',
  status: '',
  condition: '',
  sterilizacion: null,
  entry_date: '',
  origin: '',
  coordinates: null,
  description: '',
  notes: '',
  photos: []
});

const errors = reactive({
  name: '',
  species: '',
  breed: '',
  color: '',
  sex: '',
  size: '',
  estimatedAge: '',
  status: '',
  condition: '',
  sterilizacion: '',
  entry_date: '',
  origin: '',
  coordinates: '',
  description: '',
  notes: '',
  photos: ''
});

function resetErrors() {
  Object.keys(errors).forEach((k) => (errors[k] = ''));
}

function validate() {
  resetErrors();
  if (!form.species) errors.species = 'La especie es obligatoria';
  if (!form.breed) errors.breed = 'La raza es obligatoria';
  if (!form.color) errors.color = 'El color es obligatorio';
  if (!form.sex) errors.sex = 'El sexo es obligatorio';
  if (!form.size) errors.size = 'El tamaño es obligatorio';
  if (!form.estimatedAge) errors.estimatedAge = 'La edad estimada es obligatoria';
  if (!form.status) errors.status = 'El estado es obligatorio';
  if (!form.condition) errors.condition = 'La condición es obligatoria';
  if (form.sterilizacion === null || form.sterilizacion === '' || form.sterilizacion === undefined) {
    errors.sterilizacion = 'La esterilización es obligatoria';
  }
  if (!form.entry_date) errors.entry_date = 'La fecha de ingreso es obligatoria';
  if (!form.origin) errors.origin = 'La procedencia es obligatoria';
  if (!form.coordinates) errors.coordinates = 'Debe seleccionar una ubicación en el mapa';

  return !Object.values(errors).some(Boolean);
}

function parseEdadToMonths(raw) {
  if (raw == null) return null;
  const s = String(raw).trim().toLowerCase();
  if (!s) return null;

  // Si ya es número puro, lo tratamos como meses
  if (/^\d+$/.test(s)) return Number(s);

  const yearsMatch = s.match(/(\d+)\s*a\s*ñ?o/);
  const monthsMatch = s.match(/(\d+)\s*mes/);

  const years = yearsMatch ? Number(yearsMatch[1]) : 0;
  const months = monthsMatch ? Number(monthsMatch[1]) : 0;
  const total = years * 12 + months;
  return Number.isFinite(total) ? total : null;
}

function buildUbicacionRescate() {
  const parts = [];
  if (form.origin) parts.push(String(form.origin));
  if (form.coordinates?.lat && form.coordinates?.lng) {
    parts.push(`(${form.coordinates.lat}, ${form.coordinates.lng})`);
  }
  return parts.join(' ');
}

function setQrForAnimal(animal) {
  if (!animal) return;
  createdAnimal.value = animal;

  // URL de detalles: por simplicidad, el endpoint del backend
  detailsUrl.value = `${window.location.origin}/api/v1/animals/${animal.id}`;
  qrUrl.value = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(detailsUrl.value)}`;
}

// Helper para cargar imagen como base64 para el PDF
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

async function downloadAnimalPDF() {
  if (!createdAnimal.value) return;

  const animal = createdAnimal.value;
  const doc = new jsPDF();
  const pageWidth = doc.internal.pageSize.getWidth();

  // Colores institucionales
  const primaryColor = [51, 102, 204]; // #3366CC - Azul institucional
  const secondaryColor = [0, 72, 132]; // #004884 - Azul oscuro
  const grayColor = [100, 100, 100];

  let y = 20;

  // === HEADER ===
  doc.setFillColor(...primaryColor);
  doc.rect(0, 0, pageWidth, 35, 'F');

  doc.setTextColor(255, 255, 255);
  doc.setFontSize(20);
  doc.setFont('helvetica', 'bold');
  doc.text('FICHA DE REGISTRO ANIMAL', pageWidth / 2, 15, { align: 'center' });

  doc.setFontSize(12);
  doc.setFont('helvetica', 'normal');
  doc.text('Sistema de Bienestar Animal', pageWidth / 2, 24, { align: 'center' });

  doc.setFontSize(10);
  doc.text(`Fecha de generación: ${new Date().toLocaleDateString('es-CO')}`, pageWidth / 2, 31, { align: 'center' });

  y = 45;

  // === CÓDIGO ÚNICO ===
  doc.setFillColor(232, 240, 254); // #E8F0FE
  doc.rect(15, y, pageWidth - 30, 12, 'F');
  doc.setTextColor(...secondaryColor);
  doc.setFontSize(14);
  doc.setFont('helvetica', 'bold');
  doc.text(`Código: ${animal.codigo_unico || animal.id || 'N/A'}`, pageWidth / 2, y + 8, { align: 'center' });

  y += 20;

  // Función helper para agregar sección
  function addSection(title, yPos) {
    doc.setFillColor(...primaryColor);
    doc.rect(15, yPos, pageWidth - 30, 8, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(11);
    doc.setFont('helvetica', 'bold');
    doc.text(title, 20, yPos + 6);
    return yPos + 12;
  }

  // Función helper para agregar campo
  function addField(label, value, xPos, yPos) {
    doc.setTextColor(...grayColor);
    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    doc.text(label, xPos, yPos);

    doc.setTextColor(0, 0, 0);
    doc.setFontSize(10);
    doc.setFont('helvetica', 'bold');
    doc.text(String(value || 'No especificado'), xPos, yPos + 5);
    return yPos + 14;
  }

  // === SECCIÓN: IDENTIFICACIÓN ===
  y = addSection('IDENTIFICACIÓN Y CARACTERÍSTICAS', y);

  const col1 = 20;
  const col2 = 110;

  addField('Nombre', animal.nombre || 'Sin nombre', col1, y);
  addField('Especie', formatLabel(animal.especie), col2, y);
  y += 14;

  addField('Raza', animal.raza, col1, y);
  addField('Color', animal.color, col2, y);
  y += 14;

  addField('Sexo', formatLabel(animal.sexo), col1, y);
  addField('Tamaño', formatLabel(animal.tamanio), col2, y);
  y += 14;

  addField('Edad aproximada', formatEdad(animal.edad_aproximada), col1, y);
  addField('Esterilizado', animal.esterilizacion ? 'Sí' : 'No', col2, y);
  y += 18;

  // === SECCIÓN: ESTADO Y CONDICIÓN ===
  y = addSection('ESTADO Y CONDICIÓN', y);

  addField('Estado actual', formatLabel(animal.estado), col1, y);
  addField('Condición de salud', formatLabel(animal.estado_salud), col2, y);
  y += 18;

  // === SECCIÓN: INFORMACIÓN DE RESCATE ===
  y = addSection('INFORMACIÓN DE RESCATE', y);

  addField('Fecha de ingreso', formatFecha(animal.fecha_rescate), col1, y);
  addField('Ubicación', animal.ubicacion_rescate || 'No especificada', col2, y);
  y += 18;

  // === SECCIÓN: OBSERVACIONES ===
  if (animal.observaciones) {
    y = addSection('OBSERVACIONES', y);

    doc.setTextColor(0, 0, 0);
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');

    const splitText = doc.splitTextToSize(animal.observaciones, pageWidth - 40);
    doc.text(splitText, 20, y + 4);
    y += splitText.length * 5 + 10;
  }

  // === QR CODE ===
  // Centrar el QR en la página
  const qrSize = 30;
  const qrX = (pageWidth - qrSize) / 2;

  y += 5;
  doc.setTextColor(...grayColor);
  doc.setFontSize(9);
  doc.text('Escanea el código QR para ver detalles en línea:', pageWidth / 2, y, { align: 'center' });

  // Agregar el QR como imagen (usamos la URL del servicio QR)
  if (qrUrl.value) {
    try {
      // Cargar imagen del QR y agregarla al PDF
      const qrImage = await loadImageAsBase64(qrUrl.value);
      if (qrImage) {
        doc.addImage(qrImage, 'PNG', qrX, y + 3, qrSize, qrSize);
        y += qrSize + 8;
      }
    } catch (e) {
      console.warn('No se pudo cargar el QR en el PDF:', e);
      y += 10;
    }
  }

  // === FOOTER ===
  const footerY = doc.internal.pageSize.getHeight() - 15;
  doc.setDrawColor(...primaryColor);
  doc.setLineWidth(0.5);
  doc.line(15, footerY - 5, pageWidth - 15, footerY - 5);

  doc.setTextColor(...grayColor);
  doc.setFontSize(8);
  doc.setFont('helvetica', 'normal');
  doc.text('Sistema de Bienestar Animal - Documento generado automáticamente', pageWidth / 2, footerY, { align: 'center' });
  doc.text(`ID: ${animal.id || 'N/A'} | Código: ${animal.codigo_unico || 'N/A'}`, pageWidth / 2, footerY + 4, { align: 'center' });

  // Guardar PDF
  const fileName = `ficha_animal_${animal.codigo_unico || animal.id || 'registro'}.pdf`;
  doc.save(fileName);
}

// Helpers para formatear datos en el PDF
function formatLabel(value) {
  if (!value) return 'No especificado';
  const labels = {
    // Especies
    canino: 'Canino',
    felino: 'Felino',
    equino: 'Equino',
    otro: 'Otro',
    // Sexo
    macho: 'Macho',
    hembra: 'Hembra',
    // Tamaño
    pequenio: 'Pequeño',
    mediano: 'Mediano',
    grande: 'Grande',
    // Estado
    en_calle: 'En calle',
    en_refugio: 'En refugio',
    en_adopcion: 'En adopción',
    adoptado: 'Adoptado',
    en_tratamiento: 'En tratamiento',
    fallecido: 'Fallecido',
    // Condición
    excelente: 'Excelente',
    bueno: 'Bueno',
    estable: 'Estable',
    grave: 'Grave',
    critico: 'Crítico'
  };
  return labels[value] || value;
}

function formatEdad(meses) {
  if (!meses && meses !== 0) return 'No especificada';
  if (meses < 12) return `${meses} ${meses === 1 ? 'mes' : 'meses'}`;
  const anios = Math.floor(meses / 12);
  const mesesRestantes = meses % 12;
  if (mesesRestantes === 0) return `${anios} ${anios === 1 ? 'año' : 'años'}`;
  return `${anios} ${anios === 1 ? 'año' : 'años'} y ${mesesRestantes} ${mesesRestantes === 1 ? 'mes' : 'meses'}`;
}

function formatFecha(fecha) {
  if (!fecha) return 'No especificada';
  try {
    return new Date(fecha).toLocaleDateString('es-CO', {
      day: '2-digit',
      month: 'long',
      year: 'numeric'
    });
  } catch {
    return fecha;
  }
}

async function onSubmit() {
  if (isSubmitting.value) return;

  const ok = validate();
  if (!ok) return;

  console.log('Valor de sterilizacion antes de enviar:', form.sterilizacion, typeof form.sterilizacion);

  isSubmitting.value = true;
  try {
    // Reset resultado previo
    createdAnimal.value = null;
    detailsUrl.value = '';
    qrUrl.value = '';

    const observaciones = [form.description, form.notes].filter(Boolean).join('\n\n');

    const payload = {
      nombre: form.name || null,
      especie: form.species || null,
      raza: form.breed || null,
      sexo: form.sex || null,
      edad_aproximada: parseEdadToMonths(form.estimatedAge),
      color: form.color || null,
      tamanio: form.size || null,
      estado: form.status || null,
      estado_salud: form.condition || null,
      fecha_rescate: form.entry_date || null,
      ubicacion_rescate: buildUbicacionRescate() || null,
      observaciones: observaciones || null,
      esterilizacion: form.sterilizacion === 1 || form.sterilizacion === '1' || form.sterilizacion === true
    };

    const hasPhotos = Array.isArray(form.photos) && form.photos.length > 0;
    let res;

    if (hasPhotos) {
      const fd = new FormData();
      Object.entries(payload).forEach(([k, v]) => {
        if (v === null || v === undefined || v === '') return;
        if (typeof v === 'number' && !Number.isFinite(v)) return;
        // Para booleanos, enviar 1 o 0 (Laravel los interpreta correctamente)
        if (typeof v === 'boolean') {
          fd.append(k, v ? '1' : '0');
        } else {
          fd.append(k, String(v));
        }
      });

      // 1ra foto como foto_principal, el resto como galeria_fotos[]
      const first = form.photos[0]?.file ?? form.photos[0];
      if (first) fd.append('foto_principal', first);

      form.photos.slice(1).forEach((p) => {
        const f = p?.file ?? p;
        if (f) fd.append('galeria_fotos[]', f);
      });

      res = await api.post('/animals', fd);
    } else {
      res = await api.post('/animals', payload);
    }

    // BaseController suele envolver en { success, message, data }
    const animal = res?.data?.data ?? res?.data;
    console.log('Animal registrado', animal);
    setQrForAnimal(animal);
  } catch (e) {
    console.error(e);
    // Aquí puedes agregar manejo de errores
  } finally {
    isSubmitting.value = false;
  }
}

function resetForm() {
  Object.keys(form).forEach((k) => {
    if (Array.isArray(form[k])) {
      form[k] = [];
    } else {
      form[k] = '';
    }
  });
  form.coordinates = null;
  resetErrors();
}

onMounted(() => {
  // Inicialización si es necesaria
});
</script>

<style scoped>
.animal-form { max-width: 1200px; margin: 0 auto; padding: 2rem; background: #f5f7fb; }
.form-header { margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 3px solid #3366CC; }
.form-section { background: white; border-radius: 8px; margin-bottom: 1.5rem; overflow: visible; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.section-title { margin: 0; padding: 1rem 1.5rem; background: #E8F0FE; color: #3366CC; font-weight: 600; }
.form-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); column-gap: 2rem; row-gap: 1.5rem; padding: 1.5rem; }
.form-grid > div { display: flex; flex-direction: column; }
.full-width { grid-column: 1 / 4; }
.map-container { display: flex; flex-direction: column; gap: 1rem; }
.map-placeholder { color: #666; text-align: center; margin: 0; padding: 0.5rem; background: #f9f9f9; border-radius: 4px; }
.form-actions { display: flex; justify-content: flex-end; gap: 1rem; padding: 1.5rem; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.alert-desplegable-govco { display: block; color: #b00020; font-size: 0.85rem; margin-top: 0.5rem; font-weight: 500; }
.label-desplegable-govco { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333; font-size: 14px; }
.label-desplegable-govco span[aria-required="true"] { color: #b00020; margin-left: 4px; }

.result-card { background: white; border-radius: 8px; margin-top: 1.5rem; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.result-content { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; padding: 1.5rem; align-items: start; }
.result-actions { display: flex; gap: 1rem; align-items: center; margin-top: 1rem; flex-wrap: wrap; }
.details-link { color: #004884; text-decoration: underline; font-weight: 600; }
.result-qr { text-align: center; border: 1px solid #c9e2ff; border-radius: 8px; padding: 1rem; }
.result-qr img { width: 220px; height: 220px; object-fit: contain; border-radius: 6px; }
.qr-title { margin: 0 0 0.75rem 0; }
.qr-help { margin: 0.75rem 0 0 0; color: #666; font-size: 0.9rem; }

@media (max-width: 992px) {
  .form-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .full-width { grid-column: 1 / 3; }
  .result-content { grid-template-columns: 1fr; }
}

@media (max-width: 576px) {
  .animal-form { padding: 1rem; }
  .form-grid { grid-template-columns: 1fr; padding: 1rem; }
  .full-width { grid-column: 1 / 2; }
  .form-actions { flex-direction: column; }
}
</style>