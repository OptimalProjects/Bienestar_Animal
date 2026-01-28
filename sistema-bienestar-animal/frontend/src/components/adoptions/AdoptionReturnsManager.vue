<template>
  <section class="returns-container">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Gestion de Devoluciones</h2>
      <p class="text2-tipografia-govco">
        Registra devoluciones de animales adoptados y programa revision
        veterinaria obligatoria.
      </p>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
      <button
        :class="['tab-btn', { active: activeTab === 'nueva' }]"
        @click="activeTab = 'nueva'"
      >
        Nueva Devolucion
      </button>
      <button
        :class="['tab-btn', { active: activeTab === 'listado' }]"
        @click="activeTab = 'listado'; loadReturns()"
      >
        Listado de Devoluciones
      </button>
      <button
        :class="['tab-btn', { active: activeTab === 'pendientes' }]"
        @click="activeTab = 'pendientes'; loadPendingReviews()"
      >
        Pendientes Revision
        <span v-if="pendingCount > 0" class="badge">{{ pendingCount }}</span>
      </button>
    </div>

    <!-- Tab: Nueva Devolucion -->
    <div v-if="activeTab === 'nueva'" class="tab-content">
      <!-- Busqueda -->
      <div class="govco-card search-card">
        <label for="search" class="entradas-de-texto-govco">
          <span>Buscar adopcion por nombre del animal, adoptante o documento</span>
          <input
            id="search"
            v-model="search"
            type="text"
            placeholder="Ej: Luna, 1234567890, Juan Perez"
            @keyup.enter="searchAdoptions"
          />
        </label>
        <button
          type="button"
          class="btn-govco btn-govco-primary"
          :disabled="searchLoading"
          @click="searchAdoptions"
        >
          {{ searchLoading ? 'Buscando...' : 'Buscar' }}
        </button>
      </div>

      <!-- Resultados -->
      <div v-if="searchLoading" class="govco-card loading-card">
        <div class="spinner"></div>
        <span>Buscando adopciones completadas...</span>
      </div>

      <div v-else-if="searched && !adoptions.length" class="govco-card empty-card">
        <p>No se encontraron adopciones completadas con ese criterio.</p>
      </div>

      <table v-else-if="adoptions.length" class="govco-table">
        <thead>
          <tr>
            <th>Animal</th>
            <th>Adoptante</th>
            <th>Fecha Adopcion</th>
            <th>Estado</th>
            <th>Accion</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in adoptions" :key="a.id">
            <td>
              <strong>{{ a.animal?.nombre || 'Sin nombre' }}</strong><br />
              <span class="small text-muted">{{ a.animal?.codigo_unico }}</span>
            </td>
            <td>
              {{ a.adoptante?.nombre_completo || 'N/A' }}<br />
              <span class="small text-muted">{{ a.adoptante?.numero_documento }}</span>
            </td>
            <td>{{ formatDate(a.fecha_entrega || a.created_at) }}</td>
            <td>
              <span :class="['estado-badge', `estado-${a.estado}`]">
                {{ a.estado }}
              </span>
            </td>
            <td>
              <button
                v-if="a.estado === 'completada'"
                type="button"
                class="btn-govco btn-govco-danger btn-small"
                @click="selectAdoption(a)"
              >
                Registrar Devolucion
              </button>
              <span v-else class="text-muted">No disponible</span>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Formulario de devolucion -->
      <div v-if="selectedAdoption" class="govco-card return-form">
        <div class="form-header-small">
          <h3 class="h4-tipografia-govco">
            Devolucion de {{ selectedAdoption.animal?.nombre }}
          </h3>
          <button type="button" class="btn-close" @click="selectedAdoption = null">×</button>
        </div>

        <div class="animal-info">
          <p><strong>Codigo:</strong> {{ selectedAdoption.animal?.codigo_unico }}</p>
          <p><strong>Adoptante:</strong> {{ selectedAdoption.adoptante?.nombre_completo }}</p>
          <p><strong>Fecha adopcion:</strong> {{ formatDate(selectedAdoption.fecha_entrega) }}</p>
        </div>

        <form @submit.prevent="submitReturn">
          <div class="form-row">
            <div class="entradas-de-texto-govco">
              <label for="motivo">Motivo de devolucion<span class="required">*</span></label>
              <select
                id="motivo"
                v-model="returnForm.motivo"
                required
              >
                <option value="">Selecciona un motivo</option>
                <option v-for="(label, key) in motivos" :key="key" :value="key">
                  {{ label }}
                </option>
              </select>
            </div>

            <div class="entradas-de-texto-govco">
              <label for="estado_animal">Estado del animal<span class="required">*</span></label>
              <select
                id="estado_animal"
                v-model="returnForm.estado_animal_devolucion"
                required
              >
                <option value="">Selecciona estado</option>
                <option value="bueno">Bueno</option>
                <option value="regular">Regular</option>
                <option value="malo">Malo</option>
                <option value="critico">Critico</option>
              </select>
            </div>
          </div>

          <div class="entradas-de-texto-govco">
            <label for="descripcion">Descripcion detallada del motivo<span class="required">*</span></label>
            <textarea
              id="descripcion"
              v-model="returnForm.descripcion_motivo"
              rows="4"
              required
              minlength="10"
              placeholder="Describe detalladamente la razon de la devolucion..."
            />
          </div>

          <div class="entradas-de-texto-govco">
            <label for="observaciones">Observaciones sobre el estado del animal</label>
            <textarea
              id="observaciones"
              v-model="returnForm.observaciones_estado"
              rows="2"
              placeholder="Observaciones adicionales sobre el estado fisico o comportamental..."
            />
          </div>

          <div class="entradas-de-texto-govco">
            <label for="fecha_devolucion">Fecha de devolucion</label>
            <input
              id="fecha_devolucion"
              v-model="returnForm.fecha_devolucion"
              type="date"
              :max="today"
            />
          </div>

          <div class="form-actions">
            <button
              type="button"
              class="btn-govco btn-govco-secondary"
              @click="selectedAdoption = null"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="btn-govco btn-govco-primary"
              :disabled="submitting"
            >
              {{ submitting ? 'Registrando...' : 'Registrar Devolucion' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Tab: Listado -->
    <div v-if="activeTab === 'listado'" class="tab-content">
      <div v-if="returnsLoading" class="govco-card loading-card">
        <div class="spinner"></div>
        <span>Cargando devoluciones...</span>
      </div>

      <div v-else-if="!returns.length" class="govco-card empty-card">
        <p>No hay devoluciones registradas.</p>
      </div>

      <table v-else class="govco-table">
        <thead>
          <tr>
            <th>Animal</th>
            <th>Motivo</th>
            <th>Fecha</th>
            <th>Estado Animal</th>
            <th>Estado Proceso</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in returns" :key="r.id">
            <td>
              <strong>{{ r.animal?.nombre }}</strong><br />
              <span class="small text-muted">{{ r.animal?.codigo_unico }}</span>
            </td>
            <td>{{ motivos[r.motivo] || r.motivo }}</td>
            <td>{{ formatDate(r.fecha_devolucion) }}</td>
            <td>
              <span :class="['estado-badge', `estado-animal-${r.estado_animal_devolucion}`]">
                {{ r.estado_animal_devolucion }}
              </span>
            </td>
            <td>
              <span :class="['estado-badge', `proceso-${r.estado_proceso}`]">
                {{ estadosProceso[r.estado_proceso] || r.estado_proceso }}
              </span>
            </td>
            <td>
              <button
                type="button"
                class="btn-govco btn-govco-outline btn-small"
                @click="viewReturnDetail(r)"
              >
                Ver detalle
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Tab: Pendientes Revision -->
    <div v-if="activeTab === 'pendientes'" class="tab-content">
      <div v-if="pendingLoading" class="govco-card loading-card">
        <div class="spinner"></div>
        <span>Cargando pendientes...</span>
      </div>

      <div v-else-if="!pendingReviews.length" class="govco-card empty-card">
        <p>No hay devoluciones pendientes de revision veterinaria.</p>
      </div>

      <div v-else class="pending-cards">
        <div v-for="r in pendingReviews" :key="r.id" class="govco-card pending-card">
          <div class="pending-header">
            <div>
              <h4>{{ r.animal?.nombre }}</h4>
              <span class="text-muted">{{ r.animal?.codigo_unico }}</span>
            </div>
            <span :class="['estado-badge', `estado-animal-${r.estado_animal_devolucion}`]">
              {{ r.estado_animal_devolucion }}
            </span>
          </div>

          <div class="pending-info">
            <p><strong>Motivo:</strong> {{ motivos[r.motivo] }}</p>
            <p><strong>Fecha devolucion:</strong> {{ formatDate(r.fecha_devolucion) }}</p>
            <p><strong>Revision programada:</strong> {{ formatDate(r.fecha_revision_programada) }}</p>
            <p v-if="r.observaciones_estado"><strong>Observaciones:</strong> {{ r.observaciones_estado }}</p>
          </div>

          <button
            type="button"
            class="btn-govco btn-govco-primary"
            @click="openReviewModal(r)"
          >
            Completar Revision
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Detalle de Devolucion -->
    <div v-if="showDetailModal" class="modal-overlay" @click.self="showDetailModal = false">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Detalle de Devolucion</h3>
          <button type="button" class="btn-close" @click="showDetailModal = false">×</button>
        </div>

        <div class="modal-body" v-if="selectedReturnDetail">
          <div class="detail-section">
            <h4>Informacion del Animal</h4>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="detail-label">Nombre:</span>
                <span class="detail-value">{{ selectedReturnDetail.animal?.nombre || 'N/A' }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Codigo:</span>
                <span class="detail-value">{{ selectedReturnDetail.animal?.codigo_unico || 'N/A' }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Especie:</span>
                <span class="detail-value">{{ selectedReturnDetail.animal?.especie || 'N/A' }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Raza:</span>
                <span class="detail-value">{{ selectedReturnDetail.animal?.raza || 'N/A' }}</span>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <h4>Informacion de la Devolucion</h4>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="detail-label">Motivo:</span>
                <span class="detail-value">{{ motivos[selectedReturnDetail.motivo] || selectedReturnDetail.motivo }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Fecha devolucion:</span>
                <span class="detail-value">{{ formatDate(selectedReturnDetail.fecha_devolucion) }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Estado del animal:</span>
                <span :class="['estado-badge', `estado-animal-${selectedReturnDetail.estado_animal_devolucion}`]">
                  {{ selectedReturnDetail.estado_animal_devolucion }}
                </span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Estado del proceso:</span>
                <span :class="['estado-badge', `proceso-${selectedReturnDetail.estado_proceso}`]">
                  {{ estadosProceso[selectedReturnDetail.estado_proceso] || selectedReturnDetail.estado_proceso }}
                </span>
              </div>
            </div>
            <div class="detail-item full-width" v-if="selectedReturnDetail.descripcion_motivo">
              <span class="detail-label">Descripcion del motivo:</span>
              <p class="detail-text">{{ selectedReturnDetail.descripcion_motivo }}</p>
            </div>
            <div class="detail-item full-width" v-if="selectedReturnDetail.observaciones_estado">
              <span class="detail-label">Observaciones del estado:</span>
              <p class="detail-text">{{ selectedReturnDetail.observaciones_estado }}</p>
            </div>
          </div>

          <div class="detail-section" v-if="selectedReturnDetail.fecha_revision_programada || selectedReturnDetail.diagnostico">
            <h4>Revision Veterinaria</h4>
            <div class="detail-grid">
              <div class="detail-item" v-if="selectedReturnDetail.fecha_revision_programada">
                <span class="detail-label">Fecha revision programada:</span>
                <span class="detail-value">{{ formatDate(selectedReturnDetail.fecha_revision_programada) }}</span>
              </div>
              <div class="detail-item" v-if="selectedReturnDetail.fecha_revision_completada">
                <span class="detail-label">Fecha revision completada:</span>
                <span class="detail-value">{{ formatDate(selectedReturnDetail.fecha_revision_completada) }}</span>
              </div>
              <div class="detail-item" v-if="selectedReturnDetail.estado_salud">
                <span class="detail-label">Estado de salud:</span>
                <span class="detail-value">{{ selectedReturnDetail.estado_salud }}</span>
              </div>
              <div class="detail-item" v-if="selectedReturnDetail.apto_adopcion !== undefined">
                <span class="detail-label">Apto para re-adopcion:</span>
                <span :class="['estado-badge', selectedReturnDetail.apto_adopcion ? 'estado-animal-bueno' : 'estado-animal-malo']">
                  {{ selectedReturnDetail.apto_adopcion ? 'Si' : 'No' }}
                </span>
              </div>
            </div>
            <div class="detail-item full-width" v-if="selectedReturnDetail.diagnostico">
              <span class="detail-label">Diagnostico:</span>
              <p class="detail-text">{{ selectedReturnDetail.diagnostico }}</p>
            </div>
            <div class="detail-item full-width" v-if="selectedReturnDetail.observaciones_veterinario">
              <span class="detail-label">Observaciones del veterinario:</span>
              <p class="detail-text">{{ selectedReturnDetail.observaciones_veterinario }}</p>
            </div>
            <div class="detail-item full-width" v-if="selectedReturnDetail.recomendaciones">
              <span class="detail-label">Recomendaciones:</span>
              <p class="detail-text">{{ selectedReturnDetail.recomendaciones }}</p>
            </div>
          </div>

          <div class="detail-section" v-if="selectedReturnDetail.adoptante">
            <h4>Informacion del Adoptante</h4>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="detail-label">Nombre:</span>
                <span class="detail-value">{{ selectedReturnDetail.adoptante?.nombre_completo || 'N/A' }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Documento:</span>
                <span class="detail-value">{{ selectedReturnDetail.adoptante?.numero_documento || 'N/A' }}</span>
              </div>
              <div class="detail-item" v-if="selectedReturnDetail.adoptante?.telefono">
                <span class="detail-label">Telefono:</span>
                <span class="detail-value">{{ selectedReturnDetail.adoptante.telefono }}</span>
              </div>
              <div class="detail-item" v-if="selectedReturnDetail.adoptante?.email">
                <span class="detail-label">Email:</span>
                <span class="detail-value">{{ selectedReturnDetail.adoptante.email }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button
            type="button"
            class="btn-govco btn-govco-primary"
            :disabled="downloadingPdf"
            @click="downloadReturnPdf(selectedReturnDetail)"
          >
            {{ downloadingPdf ? 'Descargando...' : 'Descargar PDF' }}
          </button>
          <button
            type="button"
            class="btn-govco btn-govco-secondary"
            @click="showDetailModal = false"
          >
            Cerrar
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Revision -->
    <div v-if="showReviewModal" class="modal-overlay" @click.self="showReviewModal = false">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Revision Veterinaria</h3>
          <button type="button" class="btn-close" @click="showReviewModal = false">×</button>
        </div>

        <div class="modal-body">
          <div class="animal-info">
            <p><strong>Animal:</strong> {{ selectedReturn?.animal?.nombre }} ({{ selectedReturn?.animal?.codigo_unico }})</p>
            <p><strong>Estado reportado:</strong> {{ selectedReturn?.estado_animal_devolucion }}</p>
          </div>

          <form @submit.prevent="submitReview">
            <div class="entradas-de-texto-govco">
              <label for="diagnostico">Diagnostico veterinario<span class="required">*</span></label>
              <textarea
                id="diagnostico"
                v-model="reviewForm.diagnostico"
                rows="3"
                required
                placeholder="Diagnostico del examen veterinario..."
              />
            </div>

            <div class="entradas-de-texto-govco">
              <label for="estado_salud">Estado de salud actual<span class="required">*</span></label>
              <select
                id="estado_salud"
                v-model="reviewForm.estado_salud"
                required
              >
                <option value="">Selecciona estado</option>
                <option value="critico">Critico</option>
                <option value="grave">Grave</option>
                <option value="estable">Estable</option>
                <option value="bueno">Bueno</option>
                <option value="excelente">Excelente</option>
              </select>
            </div>

            <div class="entradas-de-texto-govco">
              <label for="observaciones_vet">Observaciones</label>
              <textarea
                id="observaciones_vet"
                v-model="reviewForm.observaciones_veterinario"
                rows="2"
                placeholder="Observaciones adicionales..."
              />
            </div>

            <div class="entradas-de-texto-govco">
              <label for="recomendaciones">Recomendaciones</label>
              <textarea
                id="recomendaciones"
                v-model="reviewForm.recomendaciones"
                rows="2"
                placeholder="Recomendaciones de tratamiento o cuidados..."
              />
            </div>

            <div class="checkbox-container">
              <label class="checkbox-label">
                <input
                  type="checkbox"
                  v-model="reviewForm.apto_adopcion"
                />
                <span>Apto para re-adopcion</span>
              </label>
              <p class="help-text">
                Si no esta apto, el animal permanecera en tratamiento hasta nueva evaluacion.
              </p>
            </div>

            <div class="form-actions">
              <button
                type="button"
                class="btn-govco btn-govco-secondary"
                @click="showReviewModal = false"
              >
                Cancelar
              </button>
              <button
                type="submit"
                class="btn-govco btn-govco-primary"
                :disabled="reviewSubmitting"
              >
                {{ reviewSubmitting ? 'Guardando...' : 'Guardar Revision' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import adoptionService from '@/services/adoptionService';

// State
const activeTab = ref('nueva');
const search = ref('');
const searched = ref(false);
const searchLoading = ref(false);
const adoptions = ref([]);
const selectedAdoption = ref(null);
const submitting = ref(false);

const returns = ref([]);
const returnsLoading = ref(false);

const pendingReviews = ref([]);
const pendingLoading = ref(false);
const pendingCount = ref(0);

const showReviewModal = ref(false);
const selectedReturn = ref(null);
const reviewSubmitting = ref(false);

const showDetailModal = ref(false);
const selectedReturnDetail = ref(null);
const downloadingPdf = ref(false);

const today = new Date().toISOString().slice(0, 10);

// Motivos de devolucion
const motivos = {
  incompatibilidad: 'Incompatibilidad con el hogar',
  cambio_situacion: 'Cambio de situacion personal/familiar',
  problemas_comportamiento: 'Problemas de comportamiento del animal',
  enfermedad_animal: 'Enfermedad del animal',
  enfermedad_adoptante: 'Enfermedad del adoptante',
  mudanza: 'Mudanza a lugar no apto',
  economico: 'Problemas economicos',
  fallecimiento_adoptante: 'Fallecimiento del adoptante',
  alergias: 'Alergias en el hogar',
  otro: 'Otro motivo',
};

const estadosProceso = {
  recibido: 'Recibido',
  en_revision: 'En revision',
  aprobado_readopcion: 'Aprobado re-adopcion',
  en_tratamiento: 'En tratamiento',
  finalizado: 'Finalizado',
};

// Form
const returnForm = reactive({
  motivo: '',
  descripcion_motivo: '',
  estado_animal_devolucion: '',
  observaciones_estado: '',
  fecha_devolucion: today,
});

const reviewForm = reactive({
  diagnostico: '',
  observaciones_veterinario: '',
  recomendaciones: '',
  apto_adopcion: false,
  estado_salud: '',
});

// Methods
function formatDate(dateStr) {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleDateString('es-CO');
}

async function loadCompletedAdoptions() {
  searchLoading.value = true;
  searched.value = true;

  try {
    const response = await adoptionService.fetchActiveAdoptionsBySearch(search.value.trim() || '');
    // La respuesta viene paginada: { data: { data: [...], current_page, total, ... } }
    // O puede venir como { data: [...] } dependiendo del wrapper
    let data = response;
    if (response.data) {
      data = response.data;
    }
    // Si es paginado, los items están en data.data
    if (data.data && Array.isArray(data.data)) {
      adoptions.value = data.data;
    } else if (Array.isArray(data)) {
      adoptions.value = data;
    } else {
      adoptions.value = [];
    }
    console.log('Adopciones cargadas:', adoptions.value.length, adoptions.value);
  } catch (error) {
    console.error('Error cargando adopciones:', error);
    if (window.$toast) {
      window.$toast.error('Error', 'No se pudieron cargar las adopciones');
    }
    adoptions.value = [];
  } finally {
    searchLoading.value = false;
  }
}

async function searchAdoptions() {
  await loadCompletedAdoptions();
}

function selectAdoption(adoption) {
  selectedAdoption.value = adoption;
  Object.assign(returnForm, {
    motivo: '',
    descripcion_motivo: '',
    estado_animal_devolucion: '',
    observaciones_estado: '',
    fecha_devolucion: today,
  });
}

async function submitReturn() {
  if (!selectedAdoption.value) return;

  submitting.value = true;

  try {
    const response = await adoptionService.registerReturn(selectedAdoption.value.id, {
      motivo: returnForm.motivo,
      descripcion_motivo: returnForm.descripcion_motivo,
      estado_animal_devolucion: returnForm.estado_animal_devolucion,
      observaciones_estado: returnForm.observaciones_estado || null,
      fecha_devolucion: returnForm.fecha_devolucion || null,
    });

    if (window.$toast) {
      window.$toast.success('Devolucion registrada', response.message || 'La devolucion ha sido registrada exitosamente.');
    }

    // Remover de la lista
    adoptions.value = adoptions.value.filter(a => a.id !== selectedAdoption.value.id);
    selectedAdoption.value = null;

    // Actualizar contador de pendientes
    loadPendingCount();

  } catch (error) {
    console.error('Error registrando devolucion:', error);
    const msg = error.response?.data?.message || 'Error al registrar la devolucion';
    if (window.$toast) {
      window.$toast.error('Error', msg);
    }
  } finally {
    submitting.value = false;
  }
}

async function loadReturns() {
  returnsLoading.value = true;
  try {
    const response = await adoptionService.fetchReturns();
    returns.value = response.data?.data || response.data || [];
  } catch (error) {
    console.error('Error cargando devoluciones:', error);
    returns.value = [];
  } finally {
    returnsLoading.value = false;
  }
}

async function loadPendingReviews() {
  pendingLoading.value = true;
  try {
    const response = await adoptionService.fetchReturns({ pendientes_revision: true });
    pendingReviews.value = response.data?.data || response.data || [];
    pendingCount.value = pendingReviews.value.length;
  } catch (error) {
    console.error('Error cargando pendientes:', error);
    pendingReviews.value = [];
  } finally {
    pendingLoading.value = false;
  }
}

async function loadPendingCount() {
  try {
    const response = await adoptionService.fetchReturnStats();
    pendingCount.value = response.data?.pendientes_revision || 0;
  } catch (error) {
    console.error('Error cargando estadisticas:', error);
  }
}

function viewReturnDetail(returnItem) {
  selectedReturnDetail.value = returnItem;
  showDetailModal.value = true;
}

async function downloadReturnPdf(returnItem) {
  if (!returnItem?.id) return;

  downloadingPdf.value = true;
  try {
    const blob = await adoptionService.downloadReturnPdf(returnItem.id);
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;

    // Nombre del archivo
    const animalNombre = returnItem.animal?.nombre || 'animal';
    const fecha = returnItem.fecha_devolucion?.split('T')[0] || new Date().toISOString().split('T')[0];
    link.download = `resumen_devolucion_${animalNombre}_${fecha}.pdf`;

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);

    if (window.$toast) {
      window.$toast.success('PDF descargado correctamente');
    }
  } catch (err) {
    console.error('Error al descargar PDF:', err);
    const message = err.response?.data?.message || 'Error al descargar el PDF';
    if (window.$toast) {
      window.$toast.error('Error', message);
    } else {
      alert(message);
    }
  } finally {
    downloadingPdf.value = false;
  }
}

function openReviewModal(returnItem) {
  selectedReturn.value = returnItem;
  Object.assign(reviewForm, {
    diagnostico: '',
    observaciones_veterinario: '',
    recomendaciones: '',
    apto_adopcion: false,
    estado_salud: '',
  });
  showReviewModal.value = true;
}

async function submitReview() {
  if (!selectedReturn.value) return;

  reviewSubmitting.value = true;

  try {
    const response = await adoptionService.completeReturnReview(selectedReturn.value.id, {
      diagnostico: reviewForm.diagnostico,
      observaciones_veterinario: reviewForm.observaciones_veterinario || null,
      recomendaciones: reviewForm.recomendaciones || null,
      apto_adopcion: reviewForm.apto_adopcion,
      estado_salud: reviewForm.estado_salud,
    });

    if (window.$toast) {
      window.$toast.success('Revision completada', response.message || 'La revision veterinaria ha sido registrada.');
    }

    showReviewModal.value = false;
    selectedReturn.value = null;

    // Recargar pendientes
    loadPendingReviews();

  } catch (error) {
    console.error('Error completando revision:', error);
    const msg = error.response?.data?.message || 'Error al completar la revision';
    if (window.$toast) {
      window.$toast.error('Error', msg);
    }
  } finally {
    reviewSubmitting.value = false;
  }
}

onMounted(() => {
  loadPendingCount();
  loadCompletedAdoptions();
});
</script>

<style scoped>
.returns-container {
  background: #f5f7fb;
  padding: 20px;
  border-radius: 8px;
}

.form-header {
  margin-bottom: 20px;
}

.tabs-container {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
  border-bottom: 2px solid #e0e0e0;
  padding-bottom: 10px;
}

.tab-btn {
  padding: 10px 20px;
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  color: #666;
  border-bottom: 2px solid transparent;
  margin-bottom: -12px;
  transition: all 0.2s;
  position: relative;
}

.tab-btn:hover {
  color: #3366cc;
}

.tab-btn.active {
  color: #3366cc;
  border-bottom-color: #3366cc;
}

.badge {
  background: #dc3545;
  color: white;
  font-size: 11px;
  padding: 2px 6px;
  border-radius: 10px;
  margin-left: 6px;
}

.tab-content {
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.govco-card {
  background: #ffffff;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.search-card {
  display: flex;
  gap: 12px;
  align-items: flex-end;
}

.search-card .entradas-de-texto-govco {
  flex: 1;
}

.loading-card, .empty-card {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 40px;
  color: #666;
}

.spinner {
  width: 24px;
  height: 24px;
  border: 3px solid #e0e0e0;
  border-top-color: #3366cc;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.govco-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.govco-table th,
.govco-table td {
  padding: 12px 16px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.govco-table th {
  background: #f8f9fa;
  font-weight: 600;
  color: #333;
}

.govco-table tbody tr:hover {
  background: #f8f9fa;
}

.small {
  font-size: 0.85rem;
}

.text-muted {
  color: #666;
}

.estado-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.estado-completada { background: #d4edda; color: #155724; }
.estado-devuelta { background: #f8d7da; color: #721c24; }

.estado-animal-bueno { background: #d4edda; color: #155724; }
.estado-animal-regular { background: #fff3cd; color: #856404; }
.estado-animal-malo { background: #f8d7da; color: #721c24; }
.estado-animal-critico { background: #721c24; color: white; }

.proceso-recibido { background: #e2e3e5; color: #383d41; }
.proceso-en_revision { background: #cce5ff; color: #004085; }
.proceso-aprobado_readopcion { background: #d4edda; color: #155724; }
.proceso-en_tratamiento { background: #fff3cd; color: #856404; }
.proceso-finalizado { background: #d1ecf1; color: #0c5460; }

.return-form {
  margin-top: 20px;
  border: 2px solid #dc3545;
}

.form-header-small {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.btn-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
}

.btn-close:hover {
  color: #333;
}

.animal-info {
  background: #f8f9fa;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 20px;
}

.animal-info p {
  margin: 4px 0;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.entradas-de-texto-govco {
  margin-bottom: 16px;
}

.entradas-de-texto-govco label {
  display: block;
  margin-bottom: 6px;
  font-weight: 500;
}

.required {
  color: #dc3545;
  margin-left: 2px;
}

.entradas-de-texto-govco input,
.entradas-de-texto-govco select,
.entradas-de-texto-govco textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ced4da;
  border-radius: 6px;
  font-size: 14px;
}

.entradas-de-texto-govco textarea {
  resize: vertical;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 20px;
}

.btn-govco {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-govco-primary {
  background: #3366cc;
  color: white;
}

.btn-govco-primary:hover:not(:disabled) {
  background: #254e9c;
}

.btn-govco-secondary {
  background: #6c757d;
  color: white;
}

.btn-govco-danger {
  background: #dc3545;
  color: white;
}

.btn-govco-outline {
  background: transparent;
  border: 1px solid #3366cc;
  color: #3366cc;
}

.btn-small {
  padding: 6px 12px;
  font-size: 13px;
}

.btn-govco:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Pendientes cards */
.pending-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}

.pending-card {
  border-left: 4px solid #ffc107;
}

.pending-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.pending-header h4 {
  margin: 0;
}

.pending-info {
  margin-bottom: 16px;
}

.pending-info p {
  margin: 4px 0;
  font-size: 14px;
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
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #eee;
}

.modal-header h3 {
  margin: 0;
}

.modal-body {
  padding: 20px;
}

.checkbox-container {
  margin: 16px 0;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.checkbox-label input {
  width: 18px;
  height: 18px;
}

.help-text {
  font-size: 13px;
  color: #666;
  margin-top: 6px;
}

/* Modal de detalle */
.modal-footer {
  display: flex;
  justify-content: flex-end;
  padding: 16px 20px;
  border-top: 1px solid #eee;
}

.detail-section {
  margin-bottom: 24px;
}

.detail-section h4 {
  margin: 0 0 12px 0;
  padding-bottom: 8px;
  border-bottom: 2px solid #3366cc;
  color: #004884;
  font-size: 16px;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-item.full-width {
  grid-column: 1 / -1;
  margin-top: 8px;
}

.detail-label {
  font-size: 12px;
  font-weight: 600;
  color: #666;
  text-transform: uppercase;
}

.detail-value {
  font-size: 14px;
  color: #333;
}

.detail-text {
  margin: 4px 0 0 0;
  padding: 10px;
  background: #f8f9fa;
  border-radius: 6px;
  font-size: 14px;
  color: #333;
  line-height: 1.5;
}

@media (max-width: 768px) {
  .search-card {
    flex-direction: column;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .tabs-container {
    flex-wrap: wrap;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>
