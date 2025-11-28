<!-- src/components/complaints/RescueOperations.vue -->
<!-- HU-016/HU-017: Gestión de operativos de rescate -->
<template>
  <section class="rescue-operations">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Operativos de Rescate</h2>
      <p class="text2-tipografia-govco">
        Gestione los operativos activos y registre los resultados de las intervenciones.
      </p>
    </div>

    <!-- ESTADÍSTICAS DE OPERATIVOS -->
    <div class="stats-row">
      <div class="stat-card stat-active">
        <span class="stat-number">{{ stats.active }}</span>
        <span class="stat-label">En curso</span>
      </div>
      <div class="stat-card stat-today">
        <span class="stat-number">{{ stats.today }}</span>
        <span class="stat-label">Programados hoy</span>
      </div>
      <div class="stat-card stat-completed">
        <span class="stat-number">{{ stats.completed }}</span>
        <span class="stat-label">Completados (mes)</span>
      </div>
      <div class="stat-card stat-success">
        <span class="stat-number">{{ stats.successRate }}%</span>
        <span class="stat-label">Tasa de éxito</span>
      </div>
    </div>

    <!-- OPERATIVOS ACTIVOS -->
    <div class="form-section">
      <h3 class="h5-tipografia-govco section-title">Operativos en curso</h3>

      <div v-if="activeOperations.length === 0" class="empty-state">
        <span class="empty-icon">✅</span>
        <p>No hay operativos activos en este momento.</p>
      </div>

      <div v-else class="operations-grid">
        <div
          v-for="operation in activeOperations"
          :key="operation.id"
          class="operation-card active"
        >
          <div class="operation-header">
            <div class="operation-info">
              <span class="operation-case">{{ operation.caso_numero }}</span>
              <span class="operation-time">{{ formatTime(operation.hora_inicio) }}</span>
            </div>
            <span class="operation-status status-active">En curso</span>
          </div>

          <div class="operation-body">
            <div class="operation-type">
              <span class="type-icon">{{ getTypeIcon(operation.tipo_denuncia) }}</span>
              <span>{{ getComplaintTypeLabel(operation.tipo_denuncia) }}</span>
            </div>
            <div class="operation-location">
              <span class="location-icon">📍</span>
              <span>{{ operation.dirección }}</span>
            </div>
            <div class="operation-team">
              <span class="team-icon">👥</span>
              <span>{{ operation.equipo_nombre }}</span>
            </div>
          </div>

          <div class="operation-actions">
            <button
              type="button"
              class="action-btn result-btn"
              @click="$emit('register-result', operation)"
            >
              Registrar resultado
            </button>
            <button type="button" class="action-btn contact-btn">
              Contactar equipo
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- OPERATIVOS PROGRAMADOS -->
    <div class="form-section">
      <h3 class="h5-tipografia-govco section-title">Programados para hoy</h3>

      <div v-if="scheduledOperations.length === 0" class="empty-state">
        <span class="empty-icon">📅</span>
        <p>No hay operativos programados para hoy.</p>
      </div>

      <div v-else class="operations-list">
        <div
          v-for="operation in scheduledOperations"
          :key="operation.id"
          class="operation-item scheduled"
        >
          <div class="item-time">
            <span class="time-hour">{{ formatHour(operation.hora_programada) }}</span>
            <span class="time-label">hrs</span>
          </div>
          <div class="item-info">
            <span class="item-case">{{ operation.caso_numero }}</span>
            <span class="item-type">{{ getComplaintTypeLabel(operation.tipo_denuncia) }}</span>
            <span class="item-location">{{ operation.direccion }}</span>
          </div>
          <div class="item-team">
            <span class="team-badge">{{ operation.equipo_nombre }}</span>
          </div>
          <div class="item-actions">
            <button type="button" class="start-btn" @click="startOperation(operation)">
              Iniciar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- HISTORIAL RECIENTE -->
    <div class="form-section">
      <h3 class="h5-tipografia-govco section-title">Historial reciente</h3>

      <div class="history-table-container">
        <table class="history-table">
          <thead>
            <tr>
              <th>Caso</th>
              <th>Fecha</th>
              <th>Tipo</th>
              <th>Equipo</th>
              <th>Resultado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="operation in completedOperations" :key="operation.id">
              <td class="cell-case">{{ operation.caso_numero }}</td>
              <td>{{ formatDate(operation.fecha_cierre) }}</td>
              <td>{{ getComplaintTypeLabel(operation.tipo_denuncia) }}</td>
              <td>{{ operation.equipo_nombre }}</td>
              <td>
                <span class="result-badge" :class="`result-${operation.resultado}`">
                  {{ getResultLabel(operation.resultado) }}
                </span>
              </td>
              <td>
                <button type="button" class="table-btn" @click="viewDetails(operation)">
                  Ver
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- EQUIPOS DISPONIBLES -->
    <div class="form-section">
      <h3 class="h5-tipografia-govco section-title">Equipos de rescate</h3>

      <div class="teams-grid">
        <div
          v-for="team in rescueTeams"
          :key="team.id"
          class="team-card"
          :class="{ 'team-busy': team.en_operativo }"
        >
          <div class="team-header">
            <span class="team-name">{{ team.nombre }}</span>
            <span class="team-status" :class="team.en_operativo ? 'busy' : 'available'">
              {{ team.en_operativo ? 'En operativo' : 'Disponible' }}
            </span>
          </div>
          <div class="team-members">
            <span class="members-icon">👥</span>
            <span>{{ team.miembros }} miembros</span>
          </div>
          <div class="team-vehicle">
            <span class="vehicle-icon">🚐</span>
            <span>{{ team.vehiculo }}</span>
          </div>
          <div v-if="team.en_operativo" class="team-current">
            <span class="current-label">Atendiendo:</span>
            <span class="current-case">{{ team.caso_actual }}</span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue';

const emit = defineEmits(['register-result']);

// Mock data - Operativos activos
const activeOperations = ref([
  {
    id: 1,
    caso_numero: 'DEN-202411-0003',
    tipo_denuncia: 'animal_herido',
    direccion: 'Avenida 6N con Calle 25, junto a Gasolinera Terpel',
    equipo_nombre: 'Equipo Alpha',
    hora_inicio: '2024-11-15T14:30:00',
    urgencia: 'critico'
  },
  {
    id: 2,
    caso_numero: 'DEN-202411-0004',
    tipo_denuncia: 'negligencia',
    direccion: 'Calle 70 #1-50, Barrio Ciudad Jardín',
    equipo_nombre: 'Equipo Beta',
    hora_inicio: '2024-11-15T15:00:00',
    urgencia: 'medio'
  }
]);

// Operativos programados
const scheduledOperations = ref([
  {
    id: 3,
    caso_numero: 'DEN-202411-0005',
    tipo_denuncia: 'hacinamiento',
    direccion: 'Carrera 24 #5-30, Barrio Obrero',
    equipo_nombre: 'Equipo Gamma',
    hora_programada: '2024-11-15T16:00:00'
  }
]);

// Operativos completados
const completedOperations = ref([
  {
    id: 4,
    caso_numero: 'DEN-202411-0002',
    tipo_denuncia: 'abandono',
    equipo_nombre: 'Equipo Alpha',
    fecha_cierre: '2024-11-14T16:30:00',
    resultado: 'rescatado'
  },
  {
    id: 5,
    caso_numero: 'DEN-202410-0089',
    tipo_denuncia: 'maltrato_fisico',
    equipo_nombre: 'Equipo Beta',
    fecha_cierre: '2024-11-13T11:00:00',
    resultado: 'derivado'
  },
  {
    id: 6,
    caso_numero: 'DEN-202410-0087',
    tipo_denuncia: 'animal_herido',
    equipo_nombre: 'Equipo Alpha',
    fecha_cierre: '2024-11-12T09:45:00',
    resultado: 'rescatado'
  }
]);

// Equipos de rescate
const rescueTeams = ref([
  {
    id: 1,
    nombre: 'Equipo Alpha',
    miembros: 3,
    vehiculo: 'Camioneta BA-001',
    en_operativo: true,
    caso_actual: 'DEN-202411-0003'
  },
  {
    id: 2,
    nombre: 'Equipo Beta',
    miembros: 2,
    vehiculo: 'Moto BA-010',
    en_operativo: true,
    caso_actual: 'DEN-202411-0004'
  },
  {
    id: 3,
    nombre: 'Equipo Gamma',
    miembros: 3,
    vehiculo: 'Camioneta BA-002',
    en_operativo: false,
    caso_actual: null
  },
  {
    id: 4,
    nombre: 'Equipo Delta',
    miembros: 2,
    vehiculo: 'Moto BA-011',
    en_operativo: false,
    caso_actual: null
  }
]);

// Estadísticas
const stats = computed(() => ({
  active: activeOperations.value.length,
  today: scheduledOperations.value.length + activeOperations.value.length,
  completed: 45, // Mock: completados este mes
  successRate: 87 // Mock: tasa de éxito
}));

// Funciones
function startOperation(operation) {
  console.log('Iniciando operativo:', operation.caso_numero);
  // Mover de programados a activos
  const index = scheduledOperations.value.findIndex(o => o.id === operation.id);
  if (index !== -1) {
    const op = scheduledOperations.value.splice(index, 1)[0];
    op.hora_inicio = new Date().toISOString();
    activeOperations.value.push(op);
  }
}

function viewDetails(operation) {
  console.log('Ver detalles:', operation.caso_numero);
  // Abrir modal de detalles
}

// Helpers
function getComplaintTypeLabel(type) {
  const labels = {
    maltrato_fisico: 'Maltrato físico',
    abandono: 'Abandono',
    negligencia: 'Negligencia',
    hacinamiento: 'Hacinamiento',
    animal_herido: 'Animal herido',
    envenenamiento: 'Envenenamiento'
  };
  return labels[type] || type;
}

function getTypeIcon(type) {
  const icons = {
    maltrato_fisico: '🩹',
    abandono: '🏚️',
    negligencia: '⚠️',
    hacinamiento: '🏠',
    animal_herido: '🚑',
    envenenamiento: '☠️'
  };
  return icons[type] || '📋';
}

function getResultLabel(result) {
  const labels = {
    rescatado: 'Rescatado',
    no_encontrado: 'No encontrado',
    derivado: 'Derivado',
    sin_merito: 'Sin mérito'
  };
  return labels[result] || result;
}

function formatTime(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diff = Math.floor((now - date) / 60000); // minutos

  if (diff < 60) return `Hace ${diff} min`;
  if (diff < 1440) return `Hace ${Math.floor(diff / 60)} hrs`;
  return formatDate(dateString);
}

function formatHour(dateString) {
  const date = new Date(dateString);
  return date.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
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
</script>

<style scoped>
.rescue-operations {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
  background: #f5f7fb;
}

.form-header {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #068460;
}

.form-header h2 {
  color: #068460;
}

.form-section {
  background: white;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.section-title {
  margin: 0;
  padding: 1rem 1.5rem;
  background: #E8F0FE;
  color: #3366CC;
  font-weight: 600;
}

/* Estadísticas */
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

.stat-active { border-color: #FFAB00; }
.stat-today { border-color: #3366CC; }
.stat-completed { border-color: #068460; }
.stat-success { border-color: #068460; }

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

/* Empty state */
.empty-state {
  padding: 3rem;
  text-align: center;
  color: #666;
}

.empty-icon {
  font-size: 3rem;
  display: block;
  margin-bottom: 1rem;
}

/* Operativos activos grid */
.operations-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1rem;
  padding: 1.5rem;
}

.operation-card {
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  overflow: hidden;
}

.operation-card.active {
  border-color: #FFAB00;
  border-width: 2px;
}

.operation-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #FFF8E1;
  border-bottom: 1px solid #E0E0E0;
}

.operation-info {
  display: flex;
  flex-direction: column;
}

.operation-case {
  font-weight: 600;
  color: #004884;
}

.operation-time {
  font-size: 0.8rem;
  color: #666;
}

.operation-status {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-active {
  background: #FFAB00;
  color: #333;
}

.operation-body {
  padding: 1rem;
}

.operation-type,
.operation-location,
.operation-team {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.type-icon, .location-icon, .team-icon {
  width: 24px;
  text-align: center;
}

.operation-actions {
  display: flex;
  gap: 0.5rem;
  padding: 1rem;
  background: #f9f9f9;
  border-top: 1px solid #E0E0E0;
}

.action-btn {
  flex: 1;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
}

.result-btn {
  background: #068460;
  color: white;
}

.contact-btn {
  background: #E8F0FE;
  color: #3366CC;
  border: 1px solid #3366CC;
}

/* Operativos programados */
.operations-list {
  padding: 0;
}

.operation-item {
  display: grid;
  grid-template-columns: 80px 1fr 150px 100px;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #E0E0E0;
}

.operation-item:last-child {
  border-bottom: none;
}

.item-time {
  text-align: center;
}

.time-hour {
  display: block;
  font-size: 1.25rem;
  font-weight: bold;
  color: #3366CC;
}

.time-label {
  font-size: 0.75rem;
  color: #666;
}

.item-info {
  display: flex;
  flex-direction: column;
}

.item-case {
  font-weight: 600;
  color: #004884;
}

.item-type {
  font-size: 0.9rem;
  color: #333;
}

.item-location {
  font-size: 0.85rem;
  color: #666;
}

.team-badge {
  background: #E8F0FE;
  color: #3366CC;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.85rem;
}

.start-btn {
  padding: 0.5rem 1rem;
  background: #068460;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
}

/* Tabla historial */
.history-table-container {
  overflow-x: auto;
}

.history-table {
  width: 100%;
  border-collapse: collapse;
}

.history-table th,
.history-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #E0E0E0;
}

.history-table th {
  background: #f9f9f9;
  font-weight: 600;
  color: #333;
  font-size: 0.85rem;
}

.cell-case {
  font-weight: 600;
  color: #004884;
}

.result-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 600;
}

.result-rescatado { background: #E8F5E9; color: #2E7D32; }
.result-no_encontrado { background: #FFEBEE; color: #C62828; }
.result-derivado { background: #FFF8E1; color: #F57C00; }
.result-sin_merito { background: #F5F5F5; color: #666; }

.table-btn {
  padding: 0.25rem 0.75rem;
  background: #E8F0FE;
  color: #3366CC;
  border: 1px solid #3366CC;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8rem;
}

/* Equipos */
.teams-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
  padding: 1.5rem;
}

.team-card {
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  padding: 1rem;
  transition: all 0.2s;
}

.team-card.team-busy {
  background: #FFF8E1;
  border-color: #FFAB00;
}

.team-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.team-name {
  font-weight: 600;
  color: #333;
}

.team-status {
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.team-status.available {
  background: #E8F5E9;
  color: #2E7D32;
}

.team-status.busy {
  background: #FFAB00;
  color: #333;
}

.team-members,
.team-vehicle {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 0.25rem;
}

.team-current {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #E0E0E0;
  font-size: 0.85rem;
}

.current-label {
  color: #666;
}

.current-case {
  font-weight: 600;
  color: #004884;
}

/* Responsive */
@media (max-width: 992px) {
  .stats-row {
    grid-template-columns: repeat(2, 1fr);
  }

  .operation-item {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }

  .item-time {
    text-align: left;
  }

  .time-hour {
    display: inline;
  }
}

@media (max-width: 576px) {
  .rescue-operations {
    padding: 1rem;
  }

  .operations-grid {
    grid-template-columns: 1fr;
  }

  .operation-actions {
    flex-direction: column;
  }
}
</style>
