<!-- src/components/adoptions/PostAdoptionFollowUps.vue -->
<template>
  <section class="followups-manager">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Visitas Domiciliarias</h2>
      <p class="text2-tipografia-govco">
        Gestiona las visitas programadas, registra resultados y da seguimiento a las adopciones.
      </p>
    </div>

    <!-- Filtros -->
    <div class="filters-section">
      <div class="filters-grid">
        <div class="form-group">
          <label for="status-filter">Estado</label>
          <select id="status-filter" v-model="filters.estado" class="form-control">
            <option value="">Todas</option>
            <option value="pendiente">Pendientes</option>
            <option value="realizada">Realizadas</option>
          </select>
        </div>

        <div class="form-group">
          <label for="type-filter">Tipo de visita</label>
          <select id="type-filter" v-model="filters.tipo_visita" class="form-control">
            <option value="">Todos</option>
            <option value="pre_adopcion">Pre-adopcion</option>
            <option value="seguimiento_1mes">Seguimiento 1 mes</option>
            <option value="seguimiento_3meses">Seguimiento 3 meses</option>
            <option value="seguimiento_6meses">Seguimiento 6 meses</option>
            <option value="extraordinaria">Extraordinaria</option>
          </select>
        </div>

        <div class="form-group">
          <label for="date-from">Desde</label>
          <input type="date" id="date-from" v-model="filters.fecha_desde" class="form-control" />
        </div>

        <div class="form-group">
          <label for="date-to">Hasta</label>
          <input type="date" id="date-to" v-model="filters.fecha_hasta" class="form-control" />
        </div>

        <button @click="loadVisits" class="govco-btn govco-bg-marine filter-btn">
          Filtrar
        </button>
      </div>
    </div>

    <!-- Tabla de Visitas -->
    <div class="visits-table-container">
      <div v-if="loading" class="loading-state">
        Cargando visitas...
      </div>

      <div v-else-if="visits.length === 0" class="empty-state">
        <p>No se encontraron visitas con los filtros seleccionados.</p>
      </div>

      <table v-else class="visits-table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Animal</th>
            <th>Adoptante</th>
            <th>Estado</th>
            <th>Resultado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="visit in visits"
            :key="visit.id"
            :class="{ 'overdue-row': isOverdue(visit) && !visit.fecha_realizada }"
          >
            <td>
              <div class="date-cell">
                <span class="date-main">{{ formatDate(visit.fecha_programada) }}</span>
                <span v-if="isOverdue(visit) && !visit.fecha_realizada" class="overdue-badge">Vencida</span>
              </div>
            </td>
            <td>
              <span class="type-badge">{{ getVisitTypeLabel(visit.tipo_visita) }}</span>
            </td>
            <td>
              <div class="animal-cell">
                <span class="animal-code">{{ visit.adopcion?.animal?.codigo_unico || 'N/A' }}</span>
                <strong>{{ visit.adopcion?.animal?.nombre || 'Sin nombre' }}</strong>
                <small>{{ visit.adopcion?.animal?.especie }}</small>
              </div>
            </td>
            <td>
              <div class="adopter-cell">
                <strong>{{ visit.adopcion?.adoptante?.nombre_completo || 'Sin adoptante' }}</strong>
                <small>{{ visit.adopcion?.adoptante?.telefono }}</small>
              </div>
            </td>
            <td>
              <span class="status-badge" :class="visit.fecha_realizada ? 'status-done' : 'status-pending'">
                {{ visit.fecha_realizada ? 'Realizada' : 'Pendiente' }}
              </span>
            </td>
            <td>
              <span v-if="visit.resultado" class="result-badge" :class="`result-${visit.resultado}`">
                {{ getResultLabel(visit.resultado) }}
              </span>
              <span v-else class="result-badge result-none">Sin resultado</span>
            </td>
            <td>
              <div class="action-buttons">
                <button
                  v-if="!visit.fecha_realizada"
                  @click="openRegisterModal(visit)"
                  class="action-btn govco-bg-elf-green"
                  title="Registrar visita"
                >
                  Registrar
                </button>
                <button
                  @click="openDetailModal(visit)"
                  class="action-btn govco-bg-marine"
                  title="Ver detalles"
                >
                  Ver
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal de Registro de Visita -->
    <div v-if="showRegisterModal" class="modal-overlay" @click="closeModals">
      <div class="modal-content" @click.stop>
        <div class="modal-header govco-bg-blue-light">
          <h3 class="h4-tipografia-govco govcolor-blue-dark">Registrar Visita Domiciliaria</h3>
          <button @click="closeModals" class="modal-close">&times;</button>
        </div>

        <div class="modal-body">
          <!-- Info de la visita -->
          <div class="visit-info-summary">
            <div class="info-item">
              <span class="info-label">Animal:</span>
              <span class="info-value">{{ selectedVisit?.adopcion?.animal?.nombre }} ({{ selectedVisit?.adopcion?.animal?.codigo_unico }})</span>
            </div>
            <div class="info-item">
              <span class="info-label">Adoptante:</span>
              <span class="info-value">{{ selectedVisit?.adopcion?.adoptante?.nombre_completo }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Direccion:</span>
              <span class="info-value">{{ selectedVisit?.adopcion?.adoptante?.direccion }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Tipo:</span>
              <span class="info-value">{{ getVisitTypeLabel(selectedVisit?.tipo_visita) }}</span>
            </div>
          </div>

          <hr class="divider" />

          <!-- Formulario de registro -->
          <form @submit.prevent="submitVisitResult" class="register-form">
            <div class="form-row">
              <div class="form-group">
                <label for="resultSelect">Resultado de la visita*</label>
                <select id="resultSelect" v-model="registerForm.resultado" class="form-control" required>
                  <option value="">Seleccione...</option>
                  <option value="satisfactoria">Satisfactoria</option>
                  <option value="observaciones">Con observaciones</option>
                  <option value="critica">Critica</option>
                </select>
              </div>

              <div class="form-group">
                <label for="visitDate">Fecha de visita</label>
                <input type="date" id="visitDate" v-model="registerForm.fecha_realizada" class="form-control" />
              </div>
            </div>

            <h5 class="form-section-title">Condiciones del Hogar</h5>
            <div class="form-row three-cols">
              <div class="form-group">
                <label for="limpieza">Limpieza</label>
                <select id="limpieza" v-model="registerForm.condiciones_hogar.limpieza" class="form-control">
                  <option value="">Seleccione...</option>
                  <option value="excelente">Excelente</option>
                  <option value="buena">Buena</option>
                  <option value="regular">Regular</option>
                  <option value="deficiente">Deficiente</option>
                </select>
              </div>
              <div class="form-group">
                <label for="espacio">Espacio</label>
                <select id="espacio" v-model="registerForm.condiciones_hogar.espacio" class="form-control">
                  <option value="">Seleccione...</option>
                  <option value="amplio">Amplio</option>
                  <option value="adecuado">Adecuado</option>
                  <option value="reducido">Reducido</option>
                  <option value="inadecuado">Inadecuado</option>
                </select>
              </div>
              <div class="form-group">
                <label for="seguridad">Seguridad</label>
                <select id="seguridad" v-model="registerForm.condiciones_hogar.seguridad" class="form-control">
                  <option value="">Seleccione...</option>
                  <option value="alta">Alta</option>
                  <option value="media">Media</option>
                  <option value="baja">Baja</option>
                </select>
              </div>
            </div>

            <h5 class="form-section-title">Estado del Animal</h5>
            <div class="form-row three-cols">
              <div class="form-group">
                <label for="salud">Salud</label>
                <select id="salud" v-model="registerForm.estado_animal.salud" class="form-control">
                  <option value="">Seleccione...</option>
                  <option value="excelente">Excelente</option>
                  <option value="bueno">Bueno</option>
                  <option value="regular">Regular</option>
                  <option value="malo">Malo</option>
                </select>
              </div>
              <div class="form-group">
                <label for="comportamiento">Comportamiento</label>
                <select id="comportamiento" v-model="registerForm.estado_animal.comportamiento" class="form-control">
                  <option value="">Seleccione...</option>
                  <option value="excelente">Excelente</option>
                  <option value="bueno">Bueno</option>
                  <option value="regular">Regular</option>
                  <option value="malo">Malo</option>
                </select>
              </div>
              <div class="form-group">
                <label for="alimentacion">Alimentacion</label>
                <select id="alimentacion" v-model="registerForm.estado_animal.alimentacion" class="form-control">
                  <option value="">Seleccione...</option>
                  <option value="adecuada">Adecuada</option>
                  <option value="regular">Regular</option>
                  <option value="deficiente">Deficiente</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="observaciones">Observaciones</label>
              <textarea
                id="observaciones"
                v-model="registerForm.observaciones"
                rows="3"
                class="form-control"
                placeholder="Describa lo observado durante la visita..."
              ></textarea>
            </div>

            <div class="form-group">
              <label for="recomendaciones">Recomendaciones</label>
              <textarea
                id="recomendaciones"
                v-model="registerForm.recomendaciones"
                rows="2"
                class="form-control"
                placeholder="Recomendaciones para el adoptante..."
              ></textarea>
            </div>

            <h5 class="form-section-title">Fotos de Respaldo</h5>
            <div class="photos-upload-section">
              <div class="upload-area" @click="triggerFileInput" @dragover.prevent @drop.prevent="handleDrop">
                <input
                  type="file"
                  ref="fileInput"
                  @change="handleFileSelect"
                  accept="image/jpeg,image/png,image/jpg,image/webp"
                  multiple
                  class="hidden-input"
                />
                <div class="upload-placeholder">
                  <span class="upload-icon">📷</span>
                  <p>Haz clic o arrastra hasta 5 fotos aquí</p>
                  <small>JPG, PNG o WebP - Máximo 5MB por foto</small>
                </div>
              </div>

              <div v-if="selectedPhotos.length > 0" class="photos-preview">
                <div
                  v-for="(photo, index) in selectedPhotos"
                  :key="index"
                  class="photo-preview-item"
                >
                  <img :src="photo.preview" :alt="`Foto ${index + 1}`" />
                  <button type="button" @click="removePhoto(index)" class="remove-photo-btn">×</button>
                  <span class="photo-name">{{ photo.file.name }}</span>
                </div>
              </div>
            </div>

            <div class="form-actions">
              <button type="button" @click="closeModals" class="govco-btn govco-bg-concrete">
                Cancelar
              </button>
              <button type="submit" class="govco-btn govco-bg-elf-green" :disabled="submitting || !registerForm.resultado">
                {{ submitting ? 'Guardando...' : 'Guardar Registro' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal de Detalle -->
    <div v-if="showDetailModal" class="modal-overlay" @click="closeModals">
      <div class="modal-content" @click.stop>
        <div class="modal-header govco-bg-blue-light">
          <h3 class="h4-tipografia-govco govcolor-blue-dark">Detalle de Visita</h3>
          <button @click="closeModals" class="modal-close">&times;</button>
        </div>

        <div class="modal-body">
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">Tipo de visita</span>
              <span class="detail-value">{{ getVisitTypeLabel(selectedVisit?.tipo_visita) }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Fecha programada</span>
              <span class="detail-value">{{ formatDate(selectedVisit?.fecha_programada) }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Fecha realizada</span>
              <span class="detail-value">{{ selectedVisit?.fecha_realizada ? formatDate(selectedVisit.fecha_realizada) : 'No realizada' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Resultado</span>
              <span v-if="selectedVisit?.resultado" class="result-badge" :class="`result-${selectedVisit.resultado}`">
                {{ getResultLabel(selectedVisit.resultado) }}
              </span>
              <span v-else>Sin resultado</span>
            </div>
          </div>

          <div v-if="selectedVisit?.condiciones_hogar" class="detail-section">
            <h5>Condiciones del Hogar</h5>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="detail-label">Limpieza</span>
                <span class="detail-value">{{ selectedVisit.condiciones_hogar.limpieza || 'N/A' }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Espacio</span>
                <span class="detail-value">{{ selectedVisit.condiciones_hogar.espacio || 'N/A' }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Seguridad</span>
                <span class="detail-value">{{ selectedVisit.condiciones_hogar.seguridad || 'N/A' }}</span>
              </div>
            </div>
          </div>

          <div v-if="selectedVisit?.estado_animal" class="detail-section">
            <h5>Estado del Animal</h5>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="detail-label">Salud</span>
                <span class="detail-value">{{ selectedVisit.estado_animal.salud || 'N/A' }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Comportamiento</span>
                <span class="detail-value">{{ selectedVisit.estado_animal.comportamiento || 'N/A' }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Alimentacion</span>
                <span class="detail-value">{{ selectedVisit.estado_animal.alimentacion || 'N/A' }}</span>
              </div>
            </div>
          </div>

          <div v-if="selectedVisit?.observaciones" class="detail-section">
            <h5>Observaciones</h5>
            <p class="observation-text">{{ selectedVisit.observaciones }}</p>
          </div>

          <div v-if="selectedVisit?.recomendaciones" class="detail-section">
            <h5>Recomendaciones</h5>
            <p class="observation-text">{{ selectedVisit.recomendaciones }}</p>
          </div>

          <div v-if="selectedVisit?.fotos_respaldo && selectedVisit.fotos_respaldo.length > 0" class="detail-section">
            <h5>Fotos de Respaldo ({{ selectedVisit.fotos_respaldo.length }})</h5>
            <div class="photos-gallery">
              <div
                v-for="(foto, index) in selectedVisit.fotos_respaldo"
                :key="index"
                class="gallery-item"
                @click="openPhotoViewer(foto)"
              >
                <img :src="foto.url" :alt="foto.nombre_original || `Foto ${index + 1}`" />
                <span class="gallery-item-name">{{ foto.nombre_original || `Foto ${index + 1}` }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button @click="closeModals" class="govco-btn govco-bg-concrete">
            Cerrar
          </button>
        </div>
      </div>
    </div>

    <!-- Modal visor de fotos -->
    <div v-if="showPhotoViewer" class="photo-viewer-overlay" @click="closePhotoViewer">
      <div class="photo-viewer-content" @click.stop>
        <button @click="closePhotoViewer" class="photo-viewer-close">&times;</button>
        <img :src="currentPhoto?.url" :alt="currentPhoto?.nombre_original" />
        <p class="photo-viewer-name">{{ currentPhoto?.nombre_original }}</p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import adoptionService from '@/services/adoptionService';

// Estado
const visits = ref([]);
const loading = ref(false);
const submitting = ref(false);

const filters = reactive({
  estado: '',
  tipo_visita: '',
  fecha_desde: '',
  fecha_hasta: '',
});

// Modals
const showRegisterModal = ref(false);
const showDetailModal = ref(false);
const selectedVisit = ref(null);

// Visor de fotos
const showPhotoViewer = ref(false);
const currentPhoto = ref(null);

// Fotos seleccionadas para subir
const selectedPhotos = ref([]);
const fileInput = ref(null);

// Formulario de registro
const registerForm = reactive({
  resultado: '',
  fecha_realizada: '',
  condiciones_hogar: {
    limpieza: '',
    espacio: '',
    seguridad: '',
  },
  estado_animal: {
    salud: '',
    comportamiento: '',
    alimentacion: '',
  },
  observaciones: '',
  recomendaciones: '',
});

// Cargar visitas
async function loadVisits() {
  loading.value = true;
  try {
    const params = {};
    if (filters.estado) params.estado = filters.estado;
    if (filters.tipo_visita) params.tipo_visita = filters.tipo_visita;
    if (filters.fecha_desde) params.fecha_desde = filters.fecha_desde;
    if (filters.fecha_hasta) params.fecha_hasta = filters.fecha_hasta;

    const response = await adoptionService.fetchFollowUpVisits(params);
    const data = response.data || response;
    visits.value = data.data || data || [];
  } catch (err) {
    console.error('Error al cargar visitas:', err);
    visits.value = [];
  } finally {
    loading.value = false;
  }
}

// Verificar si esta vencida
function isOverdue(visit) {
  if (!visit.fecha_programada) return false;
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const scheduled = new Date(visit.fecha_programada);
  return scheduled < today;
}

// Formatear fecha
function formatDate(dateStr) {
  if (!dateStr) return 'Sin fecha';
  const date = new Date(dateStr);
  return date.toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
}

// Labels
function getVisitTypeLabel(tipo) {
  const labels = {
    'pre_adopcion': 'Pre-adopcion',
    'seguimiento_1mes': 'Seguimiento 1 mes',
    'seguimiento_3meses': 'Seguimiento 3 meses',
    'seguimiento_6meses': 'Seguimiento 6 meses',
    'extraordinaria': 'Extraordinaria'
  };
  return labels[tipo] || tipo || 'No especificado';
}

function getResultLabel(resultado) {
  const labels = {
    'satisfactoria': 'Satisfactoria',
    'observaciones': 'Con observaciones',
    'critica': 'Critica'
  };
  return labels[resultado] || resultado || 'No especificado';
}

// Abrir modales
function openRegisterModal(visit) {
  selectedVisit.value = visit;
  // Reset form
  registerForm.resultado = '';
  registerForm.fecha_realizada = new Date().toISOString().split('T')[0];
  registerForm.condiciones_hogar = { limpieza: '', espacio: '', seguridad: '' };
  registerForm.estado_animal = { salud: '', comportamiento: '', alimentacion: '' };
  registerForm.observaciones = '';
  registerForm.recomendaciones = '';
  // Reset fotos
  selectedPhotos.value = [];
  showRegisterModal.value = true;
}

function openDetailModal(visit) {
  selectedVisit.value = visit;
  showDetailModal.value = true;
}

function closeModals() {
  showRegisterModal.value = false;
  showDetailModal.value = false;
  selectedVisit.value = null;
  selectedPhotos.value = [];
}

// Visor de fotos
function openPhotoViewer(foto) {
  currentPhoto.value = foto;
  showPhotoViewer.value = true;
}

function closePhotoViewer() {
  showPhotoViewer.value = false;
  currentPhoto.value = null;
}

// Manejo de fotos para subir
function triggerFileInput() {
  fileInput.value?.click();
}

function handleFileSelect(event) {
  const files = Array.from(event.target.files || []);
  processFiles(files);
  // Reset input
  if (fileInput.value) {
    fileInput.value.value = '';
  }
}

function handleDrop(event) {
  const files = Array.from(event.dataTransfer?.files || []);
  processFiles(files);
}

function processFiles(files) {
  const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
  const maxSize = 5 * 1024 * 1024; // 5MB
  const maxPhotos = 5;

  const currentCount = selectedPhotos.value.length;
  const available = maxPhotos - currentCount;

  if (available <= 0) {
    if (window.$toast) {
      window.$toast.warning('Máximo 5 fotos permitidas');
    } else {
      alert('Máximo 5 fotos permitidas');
    }
    return;
  }

  const filesToAdd = files.slice(0, available);

  filesToAdd.forEach(file => {
    if (!validTypes.includes(file.type)) {
      if (window.$toast) {
        window.$toast.warning(`${file.name}: Formato no válido`);
      }
      return;
    }
    if (file.size > maxSize) {
      if (window.$toast) {
        window.$toast.warning(`${file.name}: Excede 5MB`);
      }
      return;
    }

    // Crear preview
    const reader = new FileReader();
    reader.onload = (e) => {
      selectedPhotos.value.push({
        file: file,
        preview: e.target.result,
      });
    };
    reader.readAsDataURL(file);
  });
}

function removePhoto(index) {
  selectedPhotos.value.splice(index, 1);
}

// Guardar registro
async function submitVisitResult() {
  if (!selectedVisit.value || !registerForm.resultado) return;

  submitting.value = true;
  try {
    // Limpiar objetos vacios
    const condiciones = Object.fromEntries(
      Object.entries(registerForm.condiciones_hogar).filter(([_, v]) => v)
    );
    const estado = Object.fromEntries(
      Object.entries(registerForm.estado_animal).filter(([_, v]) => v)
    );

    // Extraer archivos de las fotos seleccionadas
    const fotosArchivos = selectedPhotos.value.map(p => p.file);

    await adoptionService.registerFollowUpResult(
      selectedVisit.value.id,
      {
        resultado: registerForm.resultado,
        fecha_realizada: registerForm.fecha_realizada || null,
        condiciones_hogar: Object.keys(condiciones).length > 0 ? condiciones : null,
        estado_animal: Object.keys(estado).length > 0 ? estado : null,
        observaciones: registerForm.observaciones || null,
        recomendaciones: registerForm.recomendaciones || null,
      },
      fotosArchivos
    );

    if (window.$toast) {
      window.$toast.success('Visita registrada exitosamente');
    } else {
      alert('Visita registrada exitosamente');
    }

    closeModals();
    await loadVisits();

  } catch (err) {
    console.error('Error al registrar visita:', err);
    const errorMsg = err.response?.data?.message || 'Error al registrar la visita';
    if (window.$toast) {
      window.$toast.error('Error', errorMsg);
    } else {
      alert(errorMsg);
    }
  } finally {
    submitting.value = false;
  }
}

onMounted(loadVisits);
</script>

<style scoped>
.followups-manager {
  max-width: 1400px;
  margin: 0 auto;
  padding: 1.5rem;
  background: #f5f7fb;
}

.form-header {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #3366CC;
}

/* Filtros */
.filters-section {
  background: white;
  border-radius: 8px;
  padding: 1.25rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr) auto;
  gap: 1rem;
  align-items: end;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.form-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #4B4B4B;
}

.form-control {
  padding: 0.5rem 0.75rem;
  border: 1px solid #E0E0E0;
  border-radius: 6px;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.1);
}

.filter-btn {
  height: fit-content;
  padding: 0.5rem 1.5rem;
}

/* Tabla */
.visits-table-container {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.loading-state,
.empty-state {
  padding: 3rem;
  text-align: center;
  color: #737373;
}

.visits-table {
  width: 100%;
  border-collapse: collapse;
}

.visits-table thead {
  background: #E8F0FE;
  color: #3366CC;
}

.visits-table th,
.visits-table td {
  padding: 0.875rem 1rem;
  text-align: left;
  border-bottom: 1px solid #E0E0E0;
}

.visits-table th {
  font-weight: 600;
  font-size: 0.9rem;
}

.visits-table tbody tr:hover {
  background: #F6F8F9;
}

.overdue-row {
  background: #FFF8F8;
}

/* Celdas */
.date-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.date-main {
  font-weight: 500;
}

.overdue-badge {
  background: #FFEBEE;
  color: #C62828;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 8px;
  width: fit-content;
}

.type-badge {
  background: #E8F0FE;
  color: #3366CC;
  font-size: 0.8rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 12px;
}

.animal-cell,
.adopter-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.animal-code {
  font-family: monospace;
  font-size: 0.75rem;
  color: #3366CC;
  background: #E8F0FE;
  padding: 1px 6px;
  border-radius: 4px;
  width: fit-content;
}

.animal-cell small,
.adopter-cell small {
  color: #737373;
  font-size: 0.8rem;
}

.status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 600;
}

.status-done {
  background: #E8F5E9;
  color: #2E7D32;
}

.status-pending {
  background: #FFF8E1;
  color: #F57F17;
}

.result-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 600;
}

.result-satisfactoria {
  background: #E8F5E9;
  color: #2E7D32;
}

.result-observaciones {
  background: #FFF3E0;
  color: #E65100;
}

.result-critica {
  background: #FFEBEE;
  color: #C62828;
}

.result-none {
  background: #F5F5F5;
  color: #9E9E9E;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  padding: 0.4rem 0.75rem;
  border: none;
  border-radius: 6px;
  color: white;
  font-weight: 600;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn:hover {
  opacity: 0.9;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 100%;
  max-width: 700px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 2px solid #3366cc;
}

.modal-header h3 {
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.75rem;
  cursor: pointer;
  color: #737373;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  transition: all 0.2s;
}

.modal-close:hover {
  background: rgba(0,0,0,0.1);
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  border-top: 1px solid #E0E0E0;
}

/* Info summary */
.visit-info-summary {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
  padding: 1rem;
  background: #F6F8F9;
  border-radius: 8px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.info-label {
  font-size: 0.75rem;
  color: #737373;
  text-transform: uppercase;
}

.info-value {
  font-weight: 500;
  color: #4B4B4B;
}

.divider {
  border: none;
  border-top: 1px solid #E0E0E0;
  margin: 1.5rem 0;
}

/* Form */
.register-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.form-row.three-cols {
  grid-template-columns: repeat(3, 1fr);
}

.form-section-title {
  font-size: 0.95rem;
  color: #004884;
  margin: 0.75rem 0 0.25rem 0;
  padding-top: 0.75rem;
  border-top: 1px solid #E0E0E0;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #E0E0E0;
}

/* Detail modal */
.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-label {
  font-size: 0.75rem;
  color: #737373;
  text-transform: uppercase;
}

.detail-value {
  font-weight: 500;
  color: #4B4B4B;
}

.detail-section {
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid #E0E0E0;
}

.detail-section h5 {
  margin: 0 0 0.75rem 0;
  color: #004884;
  font-size: 0.95rem;
}

.observation-text {
  background: #F6F8F9;
  padding: 0.75rem;
  border-radius: 6px;
  margin: 0;
  color: #4B4B4B;
  line-height: 1.5;
}

/* Buttons */
.govco-btn {
  padding: 0.6rem 1.5rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  color: white;
  transition: all 0.2s;
}

.govco-btn:hover:not(:disabled) {
  opacity: 0.9;
}

.govco-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.govco-bg-marine { background-color: #3366cc; }
.govco-bg-elf-green { background-color: #068460; }
.govco-bg-concrete { background-color: #737373; }
.govco-bg-blue-light { background-color: #c9e2ff; }
.govcolor-blue-dark { color: #004884; }

/* ===== FOTOS UPLOAD ===== */
.photos-upload-section {
  margin-top: 0.5rem;
}

.upload-area {
  border: 2px dashed #3366CC;
  border-radius: 8px;
  padding: 1.5rem;
  text-align: center;
  cursor: pointer;
  background: #F8FAFF;
  transition: all 0.2s;
}

.upload-area:hover {
  background: #E8F0FE;
  border-color: #004884;
}

.hidden-input {
  display: none;
}

.upload-placeholder {
  color: #4B4B4B;
}

.upload-icon {
  font-size: 2rem;
  display: block;
  margin-bottom: 0.5rem;
}

.upload-placeholder p {
  margin: 0;
  font-weight: 500;
}

.upload-placeholder small {
  color: #737373;
}

.photos-preview {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.photo-preview-item {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  background: #F6F8F9;
  border: 1px solid #E0E0E0;
}

.photo-preview-item img {
  width: 100%;
  height: 100px;
  object-fit: cover;
}

.remove-photo-btn {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #C62828;
  color: white;
  border: none;
  font-size: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
}

.remove-photo-btn:hover {
  background: #A80521;
}

.photo-name {
  display: block;
  padding: 0.25rem 0.5rem;
  font-size: 0.7rem;
  color: #737373;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* ===== FOTOS GALLERY (Detail modal) ===== */
.photos-gallery {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 1rem;
}

.gallery-item {
  border-radius: 8px;
  overflow: hidden;
  background: white;
  border: 1px solid #E0E0E0;
  cursor: pointer;
  transition: all 0.2s;
}

.gallery-item:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  transform: translateY(-2px);
}

.gallery-item img {
  width: 100%;
  height: 100px;
  object-fit: cover;
}

.gallery-item-name {
  display: block;
  padding: 0.5rem;
  font-size: 0.75rem;
  color: #4B4B4B;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  background: #F6F8F9;
}

/* ===== PHOTO VIEWER ===== */
.photo-viewer-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 3000;
  padding: 2rem;
}

.photo-viewer-content {
  position: relative;
  max-width: 90vw;
  max-height: 90vh;
  text-align: center;
}

.photo-viewer-content img {
  max-width: 100%;
  max-height: 80vh;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.5);
}

.photo-viewer-close {
  position: absolute;
  top: -40px;
  right: 0;
  background: none;
  border: none;
  color: white;
  font-size: 2.5rem;
  cursor: pointer;
  width: 40px;
  height: 40px;
  line-height: 1;
}

.photo-viewer-close:hover {
  color: #E0E0E0;
}

.photo-viewer-name {
  color: white;
  margin-top: 1rem;
  font-size: 0.9rem;
}

@media (max-width: 992px) {
  .filters-grid {
    grid-template-columns: 1fr 1fr;
  }

  .filter-btn {
    grid-column: span 2;
  }

  .visits-table {
    font-size: 0.85rem;
  }

  .form-row,
  .form-row.three-cols {
    grid-template-columns: 1fr;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }

  .visit-info-summary {
    grid-template-columns: 1fr;
  }
}
</style>
