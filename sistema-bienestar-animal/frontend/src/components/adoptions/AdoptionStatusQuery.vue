<!-- AdoptionStatusQuery.vue - Consulta pública de estado de adopción con firma electrónica -->
<template>
  <section class="adoption-status-query">
    <!-- Formulario de consulta -->
    <div v-if="!adoption" class="query-form-container">
      <div class="query-card">
        <div class="query-header">
          <h2 class="h3-tipografia-govco govcolor-blue-dark">Consulta tu Solicitud de Adopción</h2>
          <p class="text2-tipografia-govco">
            Ingresa tu número de documento y el código de la solicitud para ver el estado de tu adopción.
          </p>
        </div>

        <form @submit.prevent="searchAdoption" class="query-form">
          <div class="form-row">
            <div class="form-group">
              <label for="tipoDocumento">Tipo de documento</label>
              <select id="tipoDocumento" v-model="queryForm.tipo_documento" required>
                <option value="">Seleccione...</option>
                <option value="CC">Cédula de Ciudadanía</option>
                <option value="CE">Cédula de Extranjería</option>
                <option value="TI">Tarjeta de Identidad</option>
                <option value="PP">Pasaporte</option>
              </select>
            </div>
            <div class="form-group">
              <label for="numeroDocumento">Número de documento</label>
              <input
                type="text"
                id="numeroDocumento"
                v-model="queryForm.numero_documento"
                placeholder="Ej: 1234567890"
                required
              />
            </div>
          </div>

          <div class="form-group">
            <label for="codigoSolicitud">Código de solicitud (opcional)</label>
            <input
              type="text"
              id="codigoSolicitud"
              v-model="queryForm.codigo_solicitud"
              placeholder="Ej: ADOP-2025-001"
            />
            <small class="form-hint">Si no tienes el código, se buscarán todas tus solicitudes.</small>
          </div>

          <button type="submit" class="govco-btn govco-bg-marine query-btn" :disabled="searching">
            <span v-if="searching">Buscando...</span>
            <span v-else>Consultar</span>
          </button>
        </form>

        <div v-if="searchError" class="search-error">
          {{ searchError }}
        </div>
      </div>
    </div>

    <!-- Resultado de la consulta -->
    <div v-else class="result-container">
      <button @click="clearSearch" class="back-btn">
        &larr; Nueva consulta
      </button>

      <!-- Información de la adopción -->
      <div class="adoption-detail-card">
        <div class="adoption-header">
          <div class="status-section">
            <span class="status-badge" :class="`status-${adoption.estado}`">
              {{ getEstadoLabel(adoption.estado) }}
            </span>
            <span v-if="adoption.codigo" class="adoption-code">{{ adoption.codigo }}</span>
          </div>
          <div class="adoption-date">
            <span class="label">Fecha de solicitud:</span>
            <span class="value">{{ formatDate(adoption.fecha_solicitud) }}</span>
          </div>
        </div>

        <!-- Datos del animal -->
        <div class="section animal-section">
          <h3 class="section-title">Animal Solicitado</h3>
          <div class="animal-card">
            <div class="animal-photo-container">
              <img
                :src="adoption.animal?.foto_url || '/images/placeholder-animal.jpg'"
                :alt="adoption.animal?.nombre"
                class="animal-photo"
                @error="handleImageError"
              />
            </div>
            <div class="animal-info">
              <h4 class="animal-name">{{ adoption.animal?.nombre || adoption.animal?.codigo_unico }}</h4>
              <div class="animal-tags">
                <span class="tag">{{ adoption.animal?.especie }}</span>
                <span class="tag">{{ adoption.animal?.raza || 'Sin raza definida' }}</span>
                <span class="tag">{{ getSexoLabel(adoption.animal?.sexo) }}</span>
              </div>
              <p class="animal-description" v-if="adoption.animal?.observaciones">
                {{ adoption.animal.observaciones }}
              </p>
            </div>
          </div>
        </div>

        <!-- Datos del adoptante -->
        <div class="section adoptante-section">
          <h3 class="section-title">Tus Datos</h3>
          <div class="data-grid">
            <div class="data-item">
              <span class="label">Nombre:</span>
              <span class="value">{{ adoption.adoptante?.nombre_completo }}</span>
            </div>
            <div class="data-item">
              <span class="label">Documento:</span>
              <span class="value">{{ adoption.adoptante?.tipo_documento }} {{ adoption.adoptante?.numero_documento }}</span>
            </div>
            <div class="data-item">
              <span class="label">Teléfono:</span>
              <span class="value">{{ adoption.adoptante?.telefono }}</span>
            </div>
            <div class="data-item">
              <span class="label">Email:</span>
              <span class="value">{{ adoption.adoptante?.email }}</span>
            </div>
            <div class="data-item full-width">
              <span class="label">Dirección:</span>
              <span class="value">{{ adoption.adoptante?.direccion }}</span>
            </div>
          </div>
        </div>

        <!-- Timeline de estado -->
        <div class="section timeline-section">
          <h3 class="section-title">Progreso de tu Adopción</h3>
          <div class="timeline">
            <div class="timeline-item" :class="{ completed: isStepCompleted('solicitada'), current: adoption.estado === 'solicitada' }">
              <div class="timeline-marker"></div>
              <div class="timeline-content">
                <h4>Solicitud Recibida</h4>
                <p>Tu solicitud fue recibida y está en revisión.</p>
              </div>
            </div>
            <div class="timeline-item" :class="{ completed: isStepCompleted('en_evaluacion'), current: adoption.estado === 'en_evaluacion' }">
              <div class="timeline-marker"></div>
              <div class="timeline-content">
                <h4>En Evaluación</h4>
                <p>Estamos evaluando tu solicitud y programando visita domiciliaria.</p>
              </div>
            </div>
            <div class="timeline-item" :class="{ completed: isStepCompleted('aprobada'), current: adoption.estado === 'aprobada' }">
              <div class="timeline-marker"></div>
              <div class="timeline-content">
                <h4>Aprobada</h4>
                <p>¡Tu solicitud fue aprobada! Firma el contrato para completar.</p>
              </div>
            </div>
            <div class="timeline-item" :class="{ completed: isStepCompleted('completada'), current: adoption.estado === 'completada' }">
              <div class="timeline-marker"></div>
              <div class="timeline-content">
                <h4>Completada</h4>
                <p>¡Felicitaciones! La adopción ha sido completada.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Sección de Contrato y Firma (solo si está aprobada) -->
        <div v-if="adoption.estado === 'aprobada'" class="section contract-section">
          <h3 class="section-title">Contrato de Adopción</h3>

          <div v-if="!contractStatus.firmado" class="contract-pending">
            <div class="contract-info-box">
              <h4>Tu adopción ha sido aprobada</h4>
              <p>
                Para completar el proceso de adopción, debes firmar electrónicamente el contrato.
                Por favor, revisa el contrato y firma en el área de abajo.
              </p>
            </div>

            <div class="contract-actions">
              <button
                @click="downloadContract"
                class="govco-btn govco-bg-marine"
                :disabled="loadingContract"
              >
                Ver/Descargar Contrato PDF
              </button>
            </div>

            <!-- Firma Electrónica -->
            <div class="signature-section">
              <h4 class="signature-title">Firma Electrónica</h4>
              <p class="signature-instructions">
                Dibuja tu firma en el área de abajo usando el mouse o tu dedo (en dispositivos táctiles).
              </p>

              <div class="signature-container">
                <canvas
                  ref="signatureCanvas"
                  class="signature-canvas"
                  @mousedown="startDrawing"
                  @mousemove="draw"
                  @mouseup="stopDrawing"
                  @mouseleave="stopDrawing"
                  @touchstart="startDrawingTouch"
                  @touchmove="drawTouch"
                  @touchend="stopDrawing"
                ></canvas>
                <button @click="clearSignature" class="clear-signature-btn" type="button">
                  Limpiar
                </button>
              </div>

              <div class="signature-terms">
                <label class="checkbox-container">
                  <input type="checkbox" v-model="acceptTerms" />
                  <span class="checkmark"></span>
                  <span class="terms-text">
                    He leído y acepto los términos y condiciones del contrato de adopción responsable.
                    Me comprometo a cumplir con todas las cláusulas establecidas y a brindar los cuidados
                    necesarios al animal adoptado.
                  </span>
                </label>
              </div>

              <button
                @click="submitSignature"
                class="govco-btn govco-bg-elf-green submit-signature-btn"
                :disabled="!acceptTerms || !hasSignature || signingContract"
              >
                <span v-if="signingContract">Procesando firma...</span>
                <span v-else>Firmar y Completar Adopción</span>
              </button>
            </div>
          </div>

          <div v-else class="contract-signed">
            <div class="success-message">
              <div class="success-icon">✓</div>
              <h4>Contrato Firmado Exitosamente</h4>
              <p>El proceso de adopción ha sido completado. ¡Felicitaciones!</p>
              <p class="firma-date">Firmado el: {{ formatDate(contractStatus.fecha_firma) }}</p>
            </div>
            <button
              @click="downloadContract"
              class="govco-btn govco-bg-marine"
              :disabled="loadingContract"
            >
              Descargar Contrato Firmado
            </button>
          </div>
        </div>

        <!-- Sección de Adopción Completada -->
        <div v-if="adoption.estado === 'completada'" class="section completed-section">
          <div class="completed-message">
            <div class="success-icon large">✓</div>
            <h3>¡Adopción Completada!</h3>
            <p>
              Gracias por darle un hogar a {{ adoption.animal?.nombre || 'tu nuevo compañero' }}.
              Recuerda que realizaremos visitas de seguimiento para asegurar el bienestar del animal.
            </p>
          </div>

          <!-- Seguimientos programados -->
          <div v-if="contractStatus.seguimientos?.length > 0" class="followups-section">
            <h4>Visitas de Seguimiento Programadas</h4>
            <ul class="followups-list">
              <li v-for="seguimiento in contractStatus.seguimientos" :key="seguimiento.id">
                <span class="followup-type">{{ getVisitTypeLabel(seguimiento.tipo_visita) }}</span>
                <span class="followup-date">{{ formatDate(seguimiento.fecha_programada) }}</span>
                <span class="followup-status" :class="seguimiento.realizada ? 'done' : 'pending'">
                  {{ seguimiento.realizada ? 'Realizada' : 'Pendiente' }}
                </span>
              </li>
            </ul>
          </div>

          <button
            @click="downloadContract"
            class="govco-btn govco-bg-marine"
            :disabled="loadingContract"
          >
            Descargar Contrato
          </button>
        </div>

        <!-- Mensaje si fue rechazada -->
        <div v-if="adoption.estado === 'rechazada'" class="section rejected-section">
          <div class="rejected-message">
            <div class="rejected-icon">✕</div>
            <h3>Solicitud No Aprobada</h3>
            <p v-if="adoption.motivo_rechazo">
              <strong>Motivo:</strong> {{ adoption.motivo_rechazo }}
            </p>
            <p>
              Puedes volver a aplicar para otra adopción en cualquier momento.
              Si tienes preguntas, contáctanos.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, reactive, nextTick, watch } from 'vue';
import adoptionService from '@/services/adoptionService';

// Formulario de consulta
const queryForm = reactive({
  tipo_documento: '',
  numero_documento: '',
  codigo_solicitud: ''
});

// Estado de búsqueda
const searching = ref(false);
const searchError = ref(null);

// Resultado de la adopción
const adoption = ref(null);

// Estado del contrato
const contractStatus = ref({
  firmado: false,
  fecha_firma: null,
  contrato_url: null,
  seguimientos: []
});
const loadingContract = ref(false);
const signingContract = ref(false);

// Firma electrónica
const signatureCanvas = ref(null);
const acceptTerms = ref(false);
const hasSignature = ref(false);
let isDrawing = false;
let ctx = null;

// Buscar adopción
async function searchAdoption() {
  if (!queryForm.tipo_documento || !queryForm.numero_documento) {
    searchError.value = 'Por favor ingresa tu tipo y número de documento.';
    return;
  }

  searching.value = true;
  searchError.value = null;

  try {
    // Usar función del servicio para consulta pública
    const response = await adoptionService.consultarAdopcionPublica(
      queryForm.tipo_documento,
      queryForm.numero_documento,
      queryForm.codigo_solicitud || null
    );

    const data = response.data || response;

    if (!data || (Array.isArray(data) && data.length === 0)) {
      searchError.value = 'No se encontraron solicitudes de adopción con los datos ingresados.';
      return;
    }

    // Si es un array, tomar la más reciente
    adoption.value = Array.isArray(data) ? data[0] : data;

    // Si está aprobada o completada, cargar estado del contrato
    if (['aprobada', 'completada'].includes(adoption.value.estado)) {
      await loadContractStatus();
    }

  } catch (err) {
    console.error('Error al buscar adopción:', err);
    if (err.response?.status === 404) {
      searchError.value = 'No se encontraron solicitudes de adopción con los datos ingresados.';
    } else {
      searchError.value = err.response?.data?.message || 'Error al buscar la solicitud. Intenta nuevamente.';
    }
  } finally {
    searching.value = false;
  }
}

// Cargar estado del contrato
async function loadContractStatus() {
  if (!adoption.value?.id) return;

  try {
    const response = await adoptionService.fetchContractStatus(adoption.value.id);
    const data = response.data || response;
    contractStatus.value = {
      firmado: data.contrato_firmado || data.firmado || false,
      fecha_firma: data.fecha_firma || null,
      contrato_url: data.contrato_url || null,
      seguimientos: data.seguimientos || []
    };

    // Si no está firmado, inicializar el canvas
    if (!contractStatus.value.firmado && adoption.value.estado === 'aprobada') {
      await nextTick();
      initSignatureCanvas();
    }
  } catch (err) {
    console.error('Error al cargar estado del contrato:', err);
  }
}

// Descargar contrato
async function downloadContract() {
  if (!adoption.value?.id) return;

  loadingContract.value = true;
  try {
    const blob = await adoptionService.downloadAdoptionContract(adoption.value.id);
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `contrato-adopcion-${adoption.value.codigo || adoption.value.id}.pdf`;
    link.click();
    window.URL.revokeObjectURL(url);
  } catch (err) {
    console.error('Error al descargar contrato:', err);
    alert('Error al descargar el contrato. Intenta nuevamente.');
  } finally {
    loadingContract.value = false;
  }
}

// Limpiar búsqueda
function clearSearch() {
  adoption.value = null;
  contractStatus.value = {
    firmado: false,
    fecha_firma: null,
    contrato_url: null,
    seguimientos: []
  };
  acceptTerms.value = false;
  hasSignature.value = false;
  searchError.value = null;
}

// ============================================
// FIRMA ELECTRÓNICA
// ============================================

function initSignatureCanvas() {
  if (!signatureCanvas.value) return;

  const canvas = signatureCanvas.value;
  canvas.width = canvas.offsetWidth;
  canvas.height = 200;

  ctx = canvas.getContext('2d');
  ctx.strokeStyle = '#000';
  ctx.lineWidth = 2;
  ctx.lineCap = 'round';
  ctx.lineJoin = 'round';

  // Fondo blanco
  ctx.fillStyle = '#fff';
  ctx.fillRect(0, 0, canvas.width, canvas.height);
}

function startDrawing(e) {
  isDrawing = true;
  const rect = signatureCanvas.value.getBoundingClientRect();
  ctx.beginPath();
  ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
}

function draw(e) {
  if (!isDrawing) return;
  const rect = signatureCanvas.value.getBoundingClientRect();
  ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
  ctx.stroke();
  hasSignature.value = true;
}

function stopDrawing() {
  isDrawing = false;
}

function startDrawingTouch(e) {
  e.preventDefault();
  const touch = e.touches[0];
  const rect = signatureCanvas.value.getBoundingClientRect();
  isDrawing = true;
  ctx.beginPath();
  ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
}

function drawTouch(e) {
  e.preventDefault();
  if (!isDrawing) return;
  const touch = e.touches[0];
  const rect = signatureCanvas.value.getBoundingClientRect();
  ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
  ctx.stroke();
  hasSignature.value = true;
}

function clearSignature() {
  if (!signatureCanvas.value || !ctx) return;
  ctx.fillStyle = '#fff';
  ctx.fillRect(0, 0, signatureCanvas.value.width, signatureCanvas.value.height);
  hasSignature.value = false;
}

async function submitSignature() {
  if (!adoption.value?.id || !hasSignature.value || !acceptTerms.value) {
    alert('Debes firmar y aceptar los términos para continuar.');
    return;
  }

  signingContract.value = true;
  try {
    const firmaBase64 = signatureCanvas.value.toDataURL('image/png');

    // Usar endpoint público con validación de documento
    await adoptionService.firmarContratoPublico(
      adoption.value.id,
      queryForm.tipo_documento,
      queryForm.numero_documento,
      firmaBase64
    );

    if (window.$toast) {
      window.$toast.success('Contrato firmado exitosamente', '¡Felicitaciones! Tu adopción ha sido completada.');
    } else {
      alert('¡Contrato firmado exitosamente! Tu adopción ha sido completada.');
    }

    // Actualizar estado
    adoption.value.estado = 'completada';
    adoption.value.contrato_firmado = true;
    await loadContractStatus();

  } catch (err) {
    console.error('Error al firmar contrato:', err);
    const errorMsg = err.response?.data?.message || 'Error al firmar el contrato. Intenta nuevamente.';
    if (window.$toast) {
      window.$toast.error('Error', errorMsg);
    } else {
      alert(errorMsg);
    }
  } finally {
    signingContract.value = false;
  }
}

// ============================================
// HELPERS
// ============================================

function formatDate(dateStr) {
  if (!dateStr) return 'Sin fecha';
  const date = new Date(dateStr);
  return date.toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

function getEstadoLabel(estado) {
  const labels = {
    'solicitada': 'Solicitud Recibida',
    'pendiente': 'Pendiente',
    'en_evaluacion': 'En Evaluación',
    'aprobada': 'Aprobada',
    'rechazada': 'No Aprobada',
    'completada': 'Completada',
    'revocada': 'Revocada'
  };
  return labels[estado] || estado;
}

function getSexoLabel(sexo) {
  return sexo === 'macho' ? 'Macho' : sexo === 'hembra' ? 'Hembra' : sexo || '';
}

function getVisitTypeLabel(tipo) {
  const labels = {
    'pre_adopcion': 'Pre-adopción',
    'seguimiento_1mes': 'Seguimiento 1 mes',
    'seguimiento_3meses': 'Seguimiento 3 meses',
    'seguimiento_6meses': 'Seguimiento 6 meses',
    'extraordinaria': 'Extraordinaria'
  };
  return labels[tipo] || tipo || '';
}

function isStepCompleted(step) {
  const order = ['solicitada', 'en_evaluacion', 'aprobada', 'completada'];
  const currentIndex = order.indexOf(adoption.value?.estado);
  const stepIndex = order.indexOf(step);
  return stepIndex <= currentIndex;
}

function handleImageError(event) {
  event.target.src = '/images/placeholder-animal.jpg';
}

// Watch para inicializar canvas cuando cambie el estado
watch(() => adoption.value?.estado, async (newEstado) => {
  if (newEstado === 'aprobada' && !contractStatus.value.firmado) {
    await nextTick();
    initSignatureCanvas();
  }
});
</script>

<style scoped>
.adoption-status-query {
  max-width: 900px;
  margin: 0 auto;
  padding: 2rem;
}

/* Query Form */
.query-form-container {
  display: flex;
  justify-content: center;
}

.query-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  padding: 2.5rem;
  width: 100%;
  max-width: 550px;
}

.query-header {
  text-align: center;
  margin-bottom: 2rem;
}

.query-header h2 {
  margin-bottom: 0.5rem;
}

.query-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1.5fr;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 600;
  color: #4B4B4B;
  font-size: 0.9rem;
}

.form-group input,
.form-group select {
  padding: 0.75rem 1rem;
  border: 1px solid #E0E0E0;
  border-radius: 6px;
  font-size: 1rem;
  transition: all 0.2s;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.1);
}

.form-hint {
  font-size: 0.8rem;
  color: #737373;
}

.query-btn {
  width: 100%;
  padding: 1rem;
  font-size: 1rem;
  margin-top: 0.5rem;
}

.search-error {
  margin-top: 1rem;
  padding: 1rem;
  background: #FFEBEE;
  border: 1px solid #FFCDD2;
  border-radius: 6px;
  color: #C62828;
  text-align: center;
}

/* Result Container */
.result-container {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.back-btn {
  background: none;
  border: none;
  color: #3366CC;
  font-size: 0.95rem;
  cursor: pointer;
  margin-bottom: 1rem;
  padding: 0.5rem 0;
}

.back-btn:hover {
  text-decoration: underline;
}

/* Adoption Detail Card */
.adoption-detail-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  overflow: hidden;
}

.adoption-header {
  background: linear-gradient(135deg, #004884 0%, #3366CC 100%);
  padding: 1.5rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.status-section {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.status-badge {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.9rem;
}

.status-badge.status-solicitada,
.status-badge.status-pendiente {
  background: #FFF8E1;
  color: #F57F17;
}

.status-badge.status-en_evaluacion {
  background: #E3F2FD;
  color: #1565C0;
}

.status-badge.status-aprobada {
  background: #E8F5E9;
  color: #2E7D32;
}

.status-badge.status-completada {
  background: #068460;
  color: white;
}

.status-badge.status-rechazada,
.status-badge.status-revocada {
  background: #FFEBEE;
  color: #C62828;
}

.adoption-code {
  color: rgba(255,255,255,0.8);
  font-family: monospace;
  font-size: 0.9rem;
}

.adoption-date {
  color: white;
}

.adoption-date .label {
  opacity: 0.8;
  font-size: 0.85rem;
}

.adoption-date .value {
  font-weight: 600;
}

/* Sections */
.section {
  padding: 2rem;
  border-bottom: 1px solid #E0E0E0;
}

.section:last-child {
  border-bottom: none;
}

.section-title {
  color: #004884;
  font-size: 1.1rem;
  margin: 0 0 1.25rem 0;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #E8F0FE;
}

/* Animal Card */
.animal-card {
  display: flex;
  gap: 1.5rem;
  align-items: flex-start;
}

.animal-photo-container {
  flex-shrink: 0;
}

.animal-photo {
  width: 150px;
  height: 150px;
  border-radius: 12px;
  object-fit: cover;
  background: linear-gradient(135deg, #3366cc 0%, #004884 100%);
}

.animal-info {
  flex: 1;
}

.animal-name {
  color: #004884;
  font-size: 1.25rem;
  margin: 0 0 0.75rem 0;
}

.animal-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.animal-tags .tag {
  background: #E8F0FE;
  color: #3366CC;
  padding: 4px 12px;
  border-radius: 16px;
  font-size: 0.85rem;
  font-weight: 500;
}

.animal-description {
  color: #737373;
  font-size: 0.9rem;
  line-height: 1.5;
  margin: 0;
}

/* Data Grid */
.data-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.data-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.data-item.full-width {
  grid-column: 1 / -1;
}

.data-item .label {
  font-size: 0.8rem;
  color: #737373;
  text-transform: uppercase;
  font-weight: 600;
}

.data-item .value {
  color: #4B4B4B;
  font-size: 0.95rem;
}

/* Timeline */
.timeline {
  position: relative;
  padding-left: 2rem;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 7px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #E0E0E0;
}

.timeline-item {
  position: relative;
  padding-bottom: 1.5rem;
}

.timeline-item:last-child {
  padding-bottom: 0;
}

.timeline-marker {
  position: absolute;
  left: -2rem;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: white;
  border: 2px solid #E0E0E0;
}

.timeline-item.completed .timeline-marker {
  background: #068460;
  border-color: #068460;
}

.timeline-item.current .timeline-marker {
  background: #3366CC;
  border-color: #3366CC;
  box-shadow: 0 0 0 4px rgba(51, 102, 204, 0.2);
}

.timeline-content h4 {
  margin: 0 0 0.25rem 0;
  font-size: 1rem;
  color: #4B4B4B;
}

.timeline-item.completed .timeline-content h4 {
  color: #068460;
}

.timeline-item.current .timeline-content h4 {
  color: #3366CC;
  font-weight: 700;
}

.timeline-content p {
  margin: 0;
  font-size: 0.85rem;
  color: #737373;
}

/* Contract Section */
.contract-section {
  background: #F0FFF4;
}

.contract-info-box {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  border-left: 4px solid #068460;
  margin-bottom: 1.5rem;
}

.contract-info-box h4 {
  color: #068460;
  margin: 0 0 0.5rem 0;
}

.contract-info-box p {
  margin: 0;
  color: #4B4B4B;
  line-height: 1.6;
}

.contract-actions {
  margin-bottom: 2rem;
}

/* Signature Section */
.signature-section {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  border: 2px dashed #3366CC;
}

.signature-title {
  color: #004884;
  margin: 0 0 0.5rem 0;
}

.signature-instructions {
  color: #737373;
  font-size: 0.9rem;
  margin-bottom: 1rem;
}

.signature-container {
  position: relative;
  margin-bottom: 1rem;
}

.signature-canvas {
  width: 100%;
  height: 200px;
  border: 2px solid #E0E0E0;
  border-radius: 8px;
  cursor: crosshair;
  touch-action: none;
}

.clear-signature-btn {
  position: absolute;
  top: 8px;
  right: 8px;
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid #E0E0E0;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 0.8rem;
  cursor: pointer;
}

.signature-terms {
  margin-bottom: 1.5rem;
}

.checkbox-container {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  cursor: pointer;
}

.checkbox-container input[type="checkbox"] {
  width: 20px;
  height: 20px;
  margin-top: 2px;
  accent-color: #068460;
}

.terms-text {
  font-size: 0.9rem;
  color: #4B4B4B;
  line-height: 1.5;
}

.submit-signature-btn {
  width: 100%;
  padding: 1rem;
  font-size: 1rem;
}

/* Contract Signed */
.contract-signed {
  text-align: center;
}

.success-message {
  margin-bottom: 1.5rem;
}

.success-message .success-icon,
.completed-message .success-icon {
  width: 60px;
  height: 60px;
  background: #E8F5E9;
  color: #068460;
  font-size: 2rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
}

.success-message .success-icon.large,
.completed-message .success-icon.large {
  width: 80px;
  height: 80px;
  font-size: 2.5rem;
}

.success-message h4,
.completed-message h3 {
  color: #068460;
  margin: 0 0 0.5rem 0;
}

.firma-date {
  font-size: 0.9rem;
  color: #737373;
}

/* Completed Section */
.completed-section {
  text-align: center;
}

.completed-message {
  margin-bottom: 2rem;
}

.completed-message p {
  color: #4B4B4B;
  max-width: 500px;
  margin: 0.5rem auto;
}

/* Followups */
.followups-section {
  background: #F6F8F9;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  text-align: left;
}

.followups-section h4 {
  color: #004884;
  margin: 0 0 1rem 0;
  font-size: 1rem;
}

.followups-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.followups-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #E0E0E0;
}

.followups-list li:last-child {
  border-bottom: none;
}

.followup-type {
  font-weight: 600;
  color: #4B4B4B;
}

.followup-date {
  color: #737373;
  font-size: 0.9rem;
}

.followup-status {
  font-size: 0.8rem;
  padding: 2px 8px;
  border-radius: 10px;
}

.followup-status.done {
  background: #E8F5E9;
  color: #2E7D32;
}

.followup-status.pending {
  background: #FFF8E1;
  color: #F57F17;
}

/* Rejected Section */
.rejected-section {
  text-align: center;
}

.rejected-message {
  padding: 2rem;
}

.rejected-message .rejected-icon {
  width: 60px;
  height: 60px;
  background: #FFEBEE;
  color: #C62828;
  font-size: 2rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
}

.rejected-message h3 {
  color: #C62828;
  margin: 0 0 1rem 0;
}

.rejected-message p {
  color: #4B4B4B;
  margin: 0.5rem 0;
}

/* Buttons */
.govco-btn {
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  color: white;
  transition: all 0.3s;
}

.govco-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  opacity: 0.9;
}

.govco-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.govco-bg-marine {
  background-color: #3366cc;
}

.govco-bg-elf-green {
  background-color: #068460;
}

/* Responsive */
@media (max-width: 768px) {
  .adoption-status-query {
    padding: 1rem;
  }

  .query-card {
    padding: 1.5rem;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .section {
    padding: 1.5rem;
  }

  .animal-card {
    flex-direction: column;
  }

  .animal-photo {
    width: 100%;
    max-width: 200px;
  }

  .data-grid {
    grid-template-columns: 1fr;
  }

  .adoption-header {
    padding: 1rem 1.5rem;
  }

  .followups-list li {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
}
</style>
