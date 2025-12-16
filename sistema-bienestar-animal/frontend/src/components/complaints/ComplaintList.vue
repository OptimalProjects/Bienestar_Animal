<!-- src/components/complaints/ComplaintList.vue -->
<!-- HU-016: Asignar Operativo de Rescate - Lista de denuncias -->
<template>
  <section class="complaint-list">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Gestión de Denuncias</h2>
      <p class="text2-tipografia-govco">
        Revise, clasifique y asigne equipos de rescate a las denuncias pendientes.
      </p>
    </div>

    <!-- FILTROS -->
    <div class="form-section filters-section">
      <h3 class="h5-tipografia-govco section-title">Filtros de búsqueda</h3>
      <div class="filters-grid">
        <!-- Estado -->
        <div class="filter-field">
          <label for="filterStatus">Estado</label>
          <select
            id="filterStatus"
            v-model="filters.status"
            class="filter-select"
          >
            <option value="">Todos</option>
            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
              {{ opt.text }}
            </option>
          </select>
        </div>

        <!-- Urgencia -->
        <div class="filter-field">
          <label for="filterUrgency">Urgencia</label>
          <select
            id="filterUrgency"
            v-model="filters.urgency"
            class="filter-select"
          >
            <option value="">Todas</option>
            <option v-for="opt in urgencyOptions" :key="opt.value" :value="opt.value">
              {{ opt.text }}
            </option>
          </select>
        </div>

        <!-- Tipo -->
        <div class="filter-field">
          <label for="filterType">Tipo</label>
          <select
            id="filterType"
            v-model="filters.type"
            class="filter-select"
          >
            <option value="">Todos</option>
            <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">
              {{ opt.text }}
            </option>
          </select>
        </div>

        <!-- Búsqueda por número de caso -->
        <div class="filter-field">
          <label for="filterCase">Número de caso</label>
          <input
            type="text"
            id="filterCase"
            v-model="filters.caseNumber"
            placeholder="DN-2025-XXXXX"
            class="filter-input"
          />
        </div>

        <!-- Botones -->
        <div class="filter-field filter-actions">
          <button type="button" class="govco-btn govco-bg-concrete" @click="clearFilters">
            Limpiar
          </button>
          <button type="button" class="govco-btn govco-bg-marine" @click="applyFilters">
            Filtrar
          </button>
        </div>
      </div>
    </div>

    <!-- ESTADISTICAS RAPIDAS -->
    <div class="stats-row">
      <div class="stat-card stat-critical">
        <span class="stat-number">{{ stats.critical }}</span>
        <span class="stat-label">Criticas</span>
      </div>
      <div class="stat-card stat-high">
        <span class="stat-number">{{ stats.high }}</span>
        <span class="stat-label">Urgencia Alta</span>
      </div>
      <div class="stat-card stat-pending">
        <span class="stat-number">{{ stats.pending }}</span>
        <span class="stat-label">Sin asignar</span>
      </div>
      <div class="stat-card stat-inprogress">
        <span class="stat-number">{{ stats.inProgress }}</span>
        <span class="stat-label">En atención</span>
      </div>
    </div>

    <!-- LISTA DE DENUNCIAS -->
    <div class="form-section">
      <div class="list-header">
        <h3 class="h5-tipografia-govco">
          Denuncias ({{ filteredComplaints.length }})
        </h3>
        <div class="sort-options">
          <label>Ordenar por:</label>
          <select v-model="sortBy" class="sort-select">
            <option value="urgency">Urgencia</option>
            <option value="date">Fecha (más reciente)</option>
            <option value="status">Estado</option>
          </select>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="isLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Cargando denuncias...</p>
      </div>

      <!-- Empty state -->
      <div v-else-if="filteredComplaints.length === 0" class="empty-state">
        <span class="empty-icon">📋</span>
        <p>No hay denuncias que coincidan con los filtros seleccionados.</p>
      </div>

      <!-- Lista -->
      <div v-else class="complaints-grid">
        <div
          v-for="complaint in filteredComplaints"
          :key="complaint.id"
          class="complaint-card"
          :class="`urgency-border-${complaint.prioridad}`"
          @click="$emit('select', complaint)"
        >
          <!-- Header de la tarjeta -->
          <div class="card-header">
            <div class="case-info">
              <span class="case-number">{{ complaint.numero_ticket }}</span>
              <span class="case-date">{{ formatDate(complaint.fecha_denuncia || complaint.created_at) }}</span>
            </div>
            <div class="badges">
              <span class="urgency-badge" :class="`urgency-${complaint.prioridad}`">
                {{ getUrgencyLabel(complaint.prioridad) }}
              </span>
              <span class="status-badge" :class="`status-${complaint.estado}`">
                {{ getStatusLabel(complaint.estado) }}
              </span>
            </div>
          </div>

          <!-- Contenido -->
          <div class="card-content">
            <div class="complaint-type">
              <span class="type-icon">{{ getTypeIcon(complaint.tipo_denuncia) }}</span>
              <span class="type-label">{{ getComplaintTypeLabel(complaint.tipo_denuncia) }}</span>
            </div>
            <p class="complaint-description">{{ truncateText(complaint.descripcion || '', 120) }}</p>
            <div class="complaint-location">
              <span class="location-icon">📍</span>
              <span>{{ complaint.ubicacion || 'Sin ubicación' }}</span>
            </div>
          </div>

          <!-- Footer -->
          <div class="card-footer">
            <div class="denunciante-info">
              <span v-if="complaint.es_anonima">🔒 Anónima</span>
              <span v-else-if="complaint.denunciante">
                {{ complaint.denunciante.nombres }} {{ complaint.denunciante.apellidos }}
              </span>
              <span v-else>Sin denunciante</span>
            </div>
            <div class="card-actions">
              <button
                v-if="!complaint.responsable_id"
                type="button"
                class="action-btn assign-btn"
                @click.stop="$emit('assign', complaint)"
              >
                Asignar equipo
              </button>
              <button
                type="button"
                class="action-btn view-btn"
                @click.stop="$emit('select', complaint)"
              >
                Ver detalle
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MAPA DE DENUNCIAS -->
    <div class="form-section map-section">
      <h3 class="h5-tipografia-govco section-title">Mapa de denuncias activas</h3>
      <div class="map-container">
        <RescueMap
          :complaints="filteredComplaints.filter(c => c.estado !== 'cerrada')"
          @select="(c) => $emit('select', c)"
        />
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useComplaintsStore } from '@/stores/complaints';
import RescueMap from './RescueMap.vue';

const emit = defineEmits(['select', 'assign']);
const complaintsStore = useComplaintsStore();

const sortBy = ref('urgency');

const filters = reactive({
  status: '',
  urgency: '',
  type: '',
  caseNumber: ''
});

// Usar loading del store
const isLoading = computed(() => complaintsStore.loading);

// Opciones para los filtros (valores que coinciden con la BD)
const statusOptions = [
  { value: 'recibida', text: 'Recibida' },
  { value: 'en_proceso', text: 'En proceso' },
  { value: 'resuelta', text: 'Resuelta' }
];

const urgencyOptions = [
  { value: 'urgente', text: 'Urgente' },
  { value: 'alta', text: 'Alta' },
  { value: 'media', text: 'Media' },
  { value: 'baja', text: 'Baja' }
];

const typeOptions = [
  { value: 'maltrato', text: 'Maltrato' },
  { value: 'abandono', text: 'Abandono' },
  { value: 'animal_herido', text: 'Animal herido' },
  { value: 'animal_peligroso', text: 'Animal peligroso' },
  { value: 'otro', text: 'Otro' }
];

// Usar datos del store
const complaints = computed(() => complaintsStore.denuncias || []);

// Estadisticas
const stats = computed(() => ({
  critical: complaints.value.filter(c => c.prioridad === 'urgente' && c.estado !== 'resuelta').length,
  high: complaints.value.filter(c => c.prioridad === 'alta' && c.estado !== 'resuelta').length,
  pending: complaints.value.filter(c => !c.responsable_id && c.estado !== 'resuelta').length,
  inProgress: complaints.value.filter(c => c.estado === 'en_proceso').length
}));

// Filtrado y ordenamiento
const filteredComplaints = computed(() => {
  let result = [...complaints.value];

  // Aplicar filtros
  if (filters.status) {
    result = result.filter(c => c.estado === filters.status);
  }
  if (filters.urgency) {
    result = result.filter(c => c.prioridad === filters.urgency);
  }
  if (filters.type) {
    result = result.filter(c => c.tipo_denuncia === filters.type);
  }
  if (filters.caseNumber) {
    result = result.filter(c =>
      c.numero_ticket?.toLowerCase().includes(filters.caseNumber.toLowerCase())
    );
  }

  // Ordenar
  const urgencyOrder = { urgente: 0, alta: 1, media: 2, baja: 3 };
  const statusOrder = { recibida: 0, en_proceso: 1, resuelta: 2 };

  result.sort((a, b) => {
    if (sortBy.value === 'urgency') {
      return (urgencyOrder[a.prioridad] ?? 4) - (urgencyOrder[b.prioridad] ?? 4);
    }
    if (sortBy.value === 'date') {
      return new Date(b.fecha_denuncia || b.created_at) - new Date(a.fecha_denuncia || a.created_at);
    }
    if (sortBy.value === 'status') {
      return (statusOrder[a.estado] ?? 3) - (statusOrder[b.estado] ?? 3);
    }
    return 0;
  });

  return result;
});

function clearFilters() {
  filters.status = '';
  filters.urgency = '';
  filters.type = '';
  filters.caseNumber = '';
}

async function applyFilters() {
  const params = {};
  if (filters.status) params.estado = filters.status;
  if (filters.urgency) params.prioridad = filters.urgency;
  if (filters.type) params.tipo_denuncia = filters.type;
  if (filters.caseNumber) params.busqueda = filters.caseNumber;

  await complaintsStore.fetchDenuncias(params);
}

// Helpers
function getUrgencyLabel(urgency) {
  const labels = { urgente: 'URGENTE', alta: 'ALTA', media: 'MEDIA', baja: 'BAJA' };
  return labels[urgency] || urgency?.toUpperCase() || '';
}

function getStatusLabel(status) {
  const labels = {
    recibida: 'Recibida',
    en_proceso: 'En proceso',
    resuelta: 'Resuelta'
  };
  return labels[status] || status;
}

function getComplaintTypeLabel(type) {
  const labels = {
    maltrato: 'Maltrato',
    abandono: 'Abandono',
    animal_herido: 'Animal herido',
    animal_peligroso: 'Animal peligroso',
    otro: 'Otro'
  };
  return labels[type] || type;
}

function getTypeIcon(type) {
  const icons = {
    maltrato: '🩹',
    abandono: '🏚️',
    animal_herido: '🚑',
    animal_peligroso: '⚠️',
    otro: '📋'
  };
  return icons[type] || '📋';
}

function formatDate(dateString) {
  if (!dateString) return 'Sin fecha';
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return 'Fecha inválida';
  return date.toLocaleDateString('es-CO', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function truncateText(text, maxLength) {
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + '...';
}

onMounted(async () => {
  await complaintsStore.fetchDenuncias();
});
</script>

<style scoped>
.complaint-list {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
  background: #f5f7fb;
}

.form-header {
  margin-bottom: 1.5rem;
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

/* Filtros */
.filters-section {
  padding-bottom: 1.5rem;
  overflow: visible;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 1rem;
  padding: 1.5rem;
  align-items: end;
  overflow: visible;
}

/* Contenedor de cada campo de filtro */
.filter-field {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
}

.filter-field label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
  font-size: 0.9rem;
}

.filter-input {
  width: 100%;
  padding: 0.6rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 0.9rem;
  height: 40px;
  box-sizing: border-box;
}

.filter-input:focus,
.filter-select:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 2px rgba(51, 102, 204, 0.2);
}

.filter-select {
  width: 100%;
  padding: 0.6rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 0.9rem;
  height: 40px;
  background: white;
  cursor: pointer;
  box-sizing: border-box;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 10px center;
  padding-right: 30px;
}

/* Botones de filtros */
.filter-actions {
  flex-direction: row;
  gap: 0.5rem;
}

.filter-actions .govco-btn {
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.govco-btn {
  padding: 0.6rem 1rem;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  color: white;
  font-size: 0.9rem;
}

.govco-bg-concrete { background: #737373; }
.govco-bg-marine { background: #3366CC; }

/* Estadisticas */
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  border-top: 4px solid;
}

.stat-critical { border-color: #A80521; }
.stat-high { border-color: #FFAB00; }
.stat-pending { border-color: #3366CC; }
.stat-inprogress { border-color: #068460; }

.stat-number {
  display: block;
  font-size: 2rem;
  font-weight: bold;
  color: #004884;
}

.stat-label {
  font-size: 0.85rem;
  color: #666;
}

/* Lista header */
.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: #E8F0FE;
  border-bottom: 1px solid #D0E0F0;
}

.list-header h3 {
  margin: 0;
  color: #3366CC;
}

.sort-options {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.sort-options label {
  font-size: 0.85rem;
  color: #666;
}

.sort-select {
  padding: 0.4rem 0.8rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 0.85rem;
}

/* Loading y empty state */
.loading-state,
.empty-state {
  padding: 3rem;
  text-align: center;
  color: #666;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3366CC;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-icon {
  font-size: 3rem;
  display: block;
  margin-bottom: 1rem;
}

/* Grid de tarjetas */
.complaints-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 1rem;
  padding: 1.5rem;
}

.complaint-card {
  background: white;
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s;
  border-left: 4px solid;
}

.complaint-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  transform: translateY(-2px);
}

.urgency-border-urgente { border-left-color: #A80521; }
.urgency-border-alta { border-left-color: #FFAB00; }
.urgency-border-media { border-left-color: #3366CC; }
.urgency-border-baja { border-left-color: #737373; }

/* Card header */
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1rem;
  background: #f9f9f9;
  border-bottom: 1px solid #E0E0E0;
}

.case-info {
  display: flex;
  flex-direction: column;
}

.case-number {
  font-weight: 600;
  color: #004884;
}

.case-date {
  font-size: 0.8rem;
  color: #666;
}

.badges {
  display: flex;
  gap: 0.5rem;
}

.urgency-badge,
.status-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 600;
}

.urgency-urgente { background: #A80521; color: white; }
.urgency-alta { background: #FFAB00; color: #333; }
.urgency-media { background: #3366CC; color: white; }
.urgency-baja { background: #737373; color: white; }

.status-recibida { background: #E0E0E0; color: #333; }
.status-en_proceso { background: #E8F0FE; color: #3366CC; }
.status-resuelta { background: #E8F5E9; color: #2E7D32; }

/* Card content */
.card-content {
  padding: 1rem;
}

.complaint-type {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.type-icon {
  font-size: 1.2rem;
}

.type-label {
  font-weight: 600;
  color: #333;
}

.complaint-description {
  margin: 0 0 0.75rem 0;
  font-size: 0.9rem;
  color: #666;
  line-height: 1.4;
}

.complaint-location {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: #666;
}

.location-icon {
  flex-shrink: 0;
}

/* Card footer */
.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  background: #f9f9f9;
  border-top: 1px solid #E0E0E0;
}

.denunciante-info {
  font-size: 0.85rem;
  color: #666;
}

.card-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  padding: 0.4rem 0.8rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.assign-btn {
  background: #068460;
  color: white;
}

.assign-btn:hover {
  background: #056B4D;
}

.view-btn {
  background: #E8F0FE;
  color: #3366CC;
  border: 1px solid #3366CC;
}

.view-btn:hover {
  background: #3366CC;
  color: white;
}

/* Mapa */
.map-section {
  padding: 0;
}

.map-container {
  height: 400px;
}

/* Responsive */
@media (max-width: 1200px) {
  .filters-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 992px) {
  .filters-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .stats-row {
    grid-template-columns: repeat(2, 1fr);
  }

  .complaints-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 576px) {
  .complaint-list {
    padding: 1rem;
  }

  .filters-grid {
    grid-template-columns: 1fr;
  }

  .filter-actions {
    flex-direction: column;
  }

  .list-header {
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-start;
  }

  .card-footer {
    flex-direction: column;
    gap: 0.5rem;
    align-items: stretch;
  }

  .card-actions {
    justify-content: stretch;
  }

  .action-btn {
    flex: 1;
    text-align: center;
  }
}
</style>
