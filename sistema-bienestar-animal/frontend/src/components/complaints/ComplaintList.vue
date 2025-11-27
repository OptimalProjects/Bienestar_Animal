<!-- src/components/complaints/ComplaintList.vue -->
<!-- HU-016: Asignar Operativo de Rescate - Lista de denuncias -->
<template>
  <section class="complaint-list">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Gestion de Denuncias</h2>
      <p class="text2-tipografia-govco">
        Revise, clasifique y asigne equipos de rescate a las denuncias pendientes.
      </p>
    </div>

    <!-- FILTROS -->
    <div class="form-section filters-section">
      <h3 class="h5-tipografia-govco section-title">Filtros de busqueda</h3>
      <div class="filters-grid">
        <!-- Estado -->
        <DesplegableGovco
          id="filterStatus"
          v-model="filters.status"
          label="Estado"
          :options="statusOptions"
          placeholder="Todos"
          width="100%"
        />

        <!-- Urgencia -->
        <DesplegableGovco
          id="filterUrgency"
          v-model="filters.urgency"
          label="Urgencia"
          :options="urgencyOptions"
          placeholder="Todas"
          width="100%"
        />

        <!-- Tipo -->
        <DesplegableGovco
          id="filterType"
          v-model="filters.type"
          label="Tipo"
          :options="typeOptions"
          placeholder="Todos"
          width="100%"
        />

        <!-- Busqueda por numero de caso -->
        <div class="entradas-de-texto-govco">
          <label for="filterCase">Numero de caso</label>
          <input
            type="text"
            id="filterCase"
            v-model="filters.caseNumber"
            placeholder="DEN-XXXX-XXXX"
          />
        </div>

        <!-- Botones -->
        <div class="filter-actions">
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
        <span class="stat-label">En atencion</span>
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
            <option value="date">Fecha (mas reciente)</option>
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
          :class="`urgency-border-${complaint.urgencia}`"
          @click="$emit('select', complaint)"
        >
          <!-- Header de la tarjeta -->
          <div class="card-header">
            <div class="case-info">
              <span class="case-number">{{ complaint.caso_numero }}</span>
              <span class="case-date">{{ formatDate(complaint.fecha_recepcion) }}</span>
            </div>
            <div class="badges">
              <span class="urgency-badge" :class="`urgency-${complaint.urgencia}`">
                {{ getUrgencyLabel(complaint.urgencia) }}
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
            <p class="complaint-description">{{ truncateText(complaint.descripcion, 120) }}</p>
            <div class="complaint-location">
              <span class="location-icon">📍</span>
              <span>{{ complaint.direccion }}</span>
            </div>
          </div>

          <!-- Footer -->
          <div class="card-footer">
            <div class="animal-info">
              <span>{{ getSpeciesLabel(complaint.especie_animal) }}</span>
              <span v-if="complaint.cantidad_animales > 1">
                ({{ complaint.cantidad_animales }} animales)
              </span>
            </div>
            <div class="card-actions">
              <button
                v-if="!complaint.equipo_asignado"
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
import RescueMap from './RescueMap.vue';
import DesplegableGovco from '../common/DesplegableGovco.vue';

const emit = defineEmits(['select', 'assign']);

const isLoading = ref(false);
const sortBy = ref('urgency');

const filters = reactive({
  status: '',
  urgency: '',
  type: '',
  caseNumber: ''
});

// Opciones para los filtros
const statusOptions = [
  { value: 'recibida', text: 'Recibidas' },
  { value: 'en_revision', text: 'En revision' },
  { value: 'asignada', text: 'Asignadas' },
  { value: 'en_atencion', text: 'En atencion' },
  { value: 'cerrada', text: 'Cerradas' }
];

const urgencyOptions = [
  { value: 'critico', text: 'Critico' },
  { value: 'alto', text: 'Alto' },
  { value: 'medio', text: 'Medio' },
  { value: 'bajo', text: 'Bajo' }
];

const typeOptions = [
  { value: 'maltrato_fisico', text: 'Maltrato fisico' },
  { value: 'abandono', text: 'Abandono' },
  { value: 'negligencia', text: 'Negligencia' },
  { value: 'animal_herido', text: 'Animal herido' },
  { value: 'envenenamiento', text: 'Envenenamiento' }
];

// Mock data
const complaints = ref([
  {
    id: 1,
    caso_numero: 'DEN-202411-0001',
    tipo_denuncia: 'maltrato_fisico',
    urgencia: 'critico',
    especie_animal: 'perro',
    cantidad_animales: 1,
    descripcion: 'Perro encadenado sin agua ni comida, visiblemente desnutrido y con heridas en el cuello por la cadena. El animal llora constantemente y los vecinos reportan que lleva semanas en esa situacion.',
    direccion: 'Calle 15 #23-45, Barrio El Centro',
    coordenadas: { lat: 3.4516, lng: -76.5319 },
    fecha_recepcion: '2024-11-15T10:30:00',
    estado: 'recibida',
    equipo_asignado: false
  },
  {
    id: 2,
    caso_numero: 'DEN-202411-0002',
    tipo_denuncia: 'abandono',
    urgencia: 'alto',
    especie_animal: 'gato',
    cantidad_animales: 3,
    descripcion: 'Familia se mudo y dejo 3 gatos encerrados en el apartamento. Los vecinos escuchan maullidos de hambre desde hace 3 dias.',
    direccion: 'Carrera 8 #42-10, Apto 301, Barrio San Fernando',
    coordenadas: { lat: 3.4450, lng: -76.5250 },
    fecha_recepcion: '2024-11-14T08:15:00',
    estado: 'en_revision',
    equipo_asignado: false
  },
  {
    id: 3,
    caso_numero: 'DEN-202411-0003',
    tipo_denuncia: 'animal_herido',
    urgencia: 'critico',
    especie_animal: 'perro',
    cantidad_animales: 1,
    descripcion: 'Perro atropellado en la via, aun con vida pero no puede moverse. Esta en la acera junto a la gasolinera.',
    direccion: 'Avenida 6N con Calle 25, junto a Gasolinera Terpel',
    coordenadas: { lat: 3.4600, lng: -76.5400 },
    fecha_recepcion: '2024-11-15T14:20:00',
    estado: 'asignada',
    equipo_asignado: true
  },
  {
    id: 4,
    caso_numero: 'DEN-202411-0004',
    tipo_denuncia: 'negligencia',
    urgencia: 'medio',
    especie_animal: 'perro',
    cantidad_animales: 2,
    descripcion: 'Dos perros en terraza pequena, expuestos al sol todo el dia sin sombra ni agua suficiente. Se ven muy delgados.',
    direccion: 'Calle 70 #1-50, Casa esquinera, Barrio Ciudad Jardin',
    coordenadas: { lat: 3.4300, lng: -76.5450 },
    fecha_recepcion: '2024-11-13T16:45:00',
    estado: 'en_atencion',
    equipo_asignado: true
  },
  {
    id: 5,
    caso_numero: 'DEN-202411-0005',
    tipo_denuncia: 'hacinamiento',
    urgencia: 'alto',
    especie_animal: 'gato',
    cantidad_animales: 15,
    descripcion: 'Casa con acumulacion de gatos, mal olor y condiciones insalubres. Los vecinos reportan que hay aproximadamente 15 gatos o mas.',
    direccion: 'Carrera 24 #5-30, Barrio Obrero',
    coordenadas: { lat: 3.4380, lng: -76.5180 },
    fecha_recepcion: '2024-11-12T11:00:00',
    estado: 'recibida',
    equipo_asignado: false
  }
]);

// Estadisticas
const stats = computed(() => ({
  critical: complaints.value.filter(c => c.urgencia === 'critico' && c.estado !== 'cerrada').length,
  high: complaints.value.filter(c => c.urgencia === 'alto' && c.estado !== 'cerrada').length,
  pending: complaints.value.filter(c => !c.equipo_asignado && c.estado !== 'cerrada').length,
  inProgress: complaints.value.filter(c => c.estado === 'en_atencion').length
}));

// Filtrado y ordenamiento
const filteredComplaints = computed(() => {
  let result = [...complaints.value];

  // Aplicar filtros
  if (filters.status) {
    result = result.filter(c => c.estado === filters.status);
  }
  if (filters.urgency) {
    result = result.filter(c => c.urgencia === filters.urgency);
  }
  if (filters.type) {
    result = result.filter(c => c.tipo_denuncia === filters.type);
  }
  if (filters.caseNumber) {
    result = result.filter(c =>
      c.caso_numero.toLowerCase().includes(filters.caseNumber.toLowerCase())
    );
  }

  // Ordenar
  const urgencyOrder = { critico: 0, alto: 1, medio: 2, bajo: 3 };
  const statusOrder = { recibida: 0, en_revision: 1, asignada: 2, en_atencion: 3, cerrada: 4 };

  result.sort((a, b) => {
    if (sortBy.value === 'urgency') {
      return urgencyOrder[a.urgencia] - urgencyOrder[b.urgencia];
    }
    if (sortBy.value === 'date') {
      return new Date(b.fecha_recepcion) - new Date(a.fecha_recepcion);
    }
    if (sortBy.value === 'status') {
      return statusOrder[a.estado] - statusOrder[b.estado];
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

function applyFilters() {
  console.log('Filtros aplicados:', filters);
}

// Helpers
function getUrgencyLabel(urgency) {
  const labels = { critico: 'CRITICO', alto: 'ALTO', medio: 'MEDIO', bajo: 'BAJO' };
  return labels[urgency] || urgency;
}

function getStatusLabel(status) {
  const labels = {
    recibida: 'Recibida',
    en_revision: 'En revision',
    asignada: 'Asignada',
    en_atencion: 'En atencion',
    cerrada: 'Cerrada'
  };
  return labels[status] || status;
}

function getComplaintTypeLabel(type) {
  const labels = {
    maltrato_fisico: 'Maltrato fisico',
    abandono: 'Abandono',
    negligencia: 'Negligencia',
    hacinamiento: 'Hacinamiento',
    pelea_animales: 'Pelea de animales',
    animal_herido: 'Animal herido',
    envenenamiento: 'Envenenamiento',
    otro: 'Otro'
  };
  return labels[type] || type;
}

function getTypeIcon(type) {
  const icons = {
    maltrato_fisico: '🩹',
    abandono: '🏚️',
    negligencia: '⚠️',
    hacinamiento: '🏠',
    pelea_animales: '⚔️',
    animal_herido: '🚑',
    envenenamiento: '☠️',
    otro: '📋'
  };
  return icons[type] || '📋';
}

function getSpeciesLabel(species) {
  const labels = {
    perro: 'Perro',
    gato: 'Gato',
    equino: 'Equino',
    bovino: 'Bovino',
    ave: 'Ave',
    otro: 'Otro'
  };
  return labels[species] || species;
}

function formatDate(dateString) {
  const date = new Date(dateString);
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

onMounted(() => {
  isLoading.value = true;
  setTimeout(() => {
    isLoading.value = false;
  }, 500);
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

/* Estilos para DesplegableGovco en filtros */
:deep(.desplegable-govco) {
  position: relative;
  z-index: 10;
}

:deep(.desplegable-govco select) {
  width: 100%;
  padding: 0.6rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 0.9rem;
  height: 40px;
  background: white;
  cursor: pointer;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 10px center;
  padding-right: 30px;
}

:deep(.desplegable-govco select:focus) {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 2px rgba(51, 102, 204, 0.2);
}

:deep(.label-desplegable-govco) {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
  font-size: 0.9rem;
}

.entradas-de-texto-govco {
  display: flex;
  flex-direction: column;
}

.entradas-de-texto-govco label {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
  font-size: 0.9rem;
}

.entradas-de-texto-govco input {
  width: 100%;
  padding: 0.6rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 0.9rem;
  height: 40px;
}

.filter-actions {
  display: flex;
  gap: 0.5rem;
  align-items: flex-end;
  padding-top: 1.5rem;
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

.urgency-border-critico { border-left-color: #A80521; }
.urgency-border-alto { border-left-color: #FFAB00; }
.urgency-border-medio { border-left-color: #3366CC; }
.urgency-border-bajo { border-left-color: #737373; }

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

.urgency-critico { background: #A80521; color: white; }
.urgency-alto { background: #FFAB00; color: #333; }
.urgency-medio { background: #3366CC; color: white; }
.urgency-bajo { background: #737373; color: white; }

.status-recibida { background: #E0E0E0; color: #333; }
.status-en_revision { background: #E8F0FE; color: #3366CC; }
.status-asignada { background: #FFF8E1; color: #856404; }
.status-en_atencion { background: #E8F5E9; color: #2E7D32; }
.status-cerrada { background: #F5F5F5; color: #666; }

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

.animal-info {
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
