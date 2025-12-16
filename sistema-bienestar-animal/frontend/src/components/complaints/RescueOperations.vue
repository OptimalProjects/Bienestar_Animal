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

    <!-- OPERATIVOS EN CURSO (rescates programados sin ejecutar) -->
    <div class="form-section">
      <h3 class="h5-tipografia-govco section-title">Operativos en curso</h3>

      <div v-if="isLoading" class="loading-state">
        <span>Cargando operativos...</span>
      </div>

      <div v-else-if="activeOperations.length === 0" class="empty-state">
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
              <span class="operation-case">{{ operation.denuncia?.numero_ticket || 'Sin ticket' }}</span>
              <span class="operation-time">{{ formatDateTime(operation.fecha_programada) }}</span>
            </div>
            <span class="operation-status status-active">Programado</span>
          </div>

          <div class="operation-body">
            <div class="operation-type">
              <span class="type-icon">{{ getTypeIcon(operation.denuncia?.tipo_denuncia) }}</span>
              <span>{{ getComplaintTypeLabel(operation.denuncia?.tipo_denuncia) }}</span>
            </div>
            <div class="operation-location">
              <span class="location-icon">📍</span>
              <span>{{ operation.denuncia?.ubicacion || 'Sin ubicación' }}</span>
            </div>
            <div class="operation-team">
              <span class="team-icon">🚐</span>
              <span>{{ operation.equipo_rescate?.nombre || 'Equipo asignado' }}</span>
            </div>
          </div>

          <div class="operation-actions">
            <button
              type="button"
              class="action-btn result-btn"
              @click="openRegisterResult(operation)"
            >
              Registrar resultado
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- OPERATIVOS PROGRAMADOS PARA HOY -->
    <div class="form-section">
      <h3 class="h5-tipografia-govco section-title">Programados para hoy</h3>

      <div v-if="scheduledToday.length === 0" class="empty-state">
        <span class="empty-icon">📅</span>
        <p>No hay operativos programados para hoy.</p>
      </div>

      <div v-else class="operations-list">
        <div
          v-for="operation in scheduledToday"
          :key="operation.id"
          class="operation-item scheduled"
        >
          <div class="item-time">
            <span class="time-hour">{{ formatHour(operation.fecha_programada) }}</span>
            <span class="time-label">hrs</span>
          </div>
          <div class="item-info">
            <span class="item-case">{{ operation.denuncia?.numero_ticket || 'Sin ticket' }}</span>
            <span class="item-type">{{ getComplaintTypeLabel(operation.denuncia?.tipo_denuncia) }}</span>
            <span class="item-location">{{ operation.denuncia?.ubicacion || 'Sin ubicación' }}</span>
          </div>
          <div class="item-team">
            <span class="team-badge">{{ operation.equipo_rescate?.nombre || 'Equipo' }}</span>
          </div>
          <div class="item-actions">
            <button
              type="button"
              class="action-btn result-btn-small"
              @click="openRegisterResult(operation)"
            >
              Registrar resultado
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- HISTORIAL RECIENTE (completados) -->
    <div class="form-section">
      <h3 class="h5-tipografia-govco section-title">Historial reciente</h3>

      <div v-if="completedOperations.length === 0" class="empty-state">
        <span class="empty-icon">📋</span>
        <p>No hay operativos completados recientemente.</p>
      </div>

      <div v-else class="history-table-container">
        <table class="history-table">
          <thead>
            <tr>
              <th>Caso</th>
              <th>Fecha ejecución</th>
              <th>Tipo</th>
              <th>Equipo</th>
              <th>Resultado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="operation in completedOperations" :key="operation.id">
              <td class="cell-case">{{ operation.denuncia?.numero_ticket || 'Sin ticket' }}</td>
              <td>{{ formatDate(operation.fecha_ejecucion) }}</td>
              <td>{{ getComplaintTypeLabel(operation.denuncia?.tipo_denuncia) }}</td>
              <td>{{ operation.equipo_rescate?.nombre || 'Equipo' }}</td>
              <td>
                <span class="result-badge" :class="operation.exitoso ? 'result-exitoso' : 'result-fallido'">
                  {{ operation.exitoso ? 'Exitoso' : 'No exitoso' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useComplaintsStore } from '@/stores/complaints';

const emit = defineEmits(['register-result']);
const complaintsStore = useComplaintsStore();

const isLoading = ref(false);

// Cargar datos al montar
onMounted(async () => {
  isLoading.value = true;
  try {
    await complaintsStore.fetchRescates();
  } catch (error) {
    console.error('Error al cargar rescates:', error);
  } finally {
    isLoading.value = false;
  }
});

// Obtener rescates del store
const allRescates = computed(() => complaintsStore.rescates || []);

// Operativos activos: rescates sin fecha de ejecución (pendientes)
const activeOperations = computed(() => {
  return allRescates.value.filter(r => !r.fecha_ejecucion);
});

// Programados para hoy
const scheduledToday = computed(() => {
  const today = new Date().toISOString().split('T')[0];
  return allRescates.value.filter(r => {
    if (r.fecha_ejecucion) return false; // Ya ejecutado
    if (!r.fecha_programada) return false;
    const programada = r.fecha_programada.split('T')[0];
    return programada === today;
  });
});

// Operativos completados (con fecha de ejecución)
const completedOperations = computed(() => {
  return allRescates.value
    .filter(r => r.fecha_ejecucion)
    .sort((a, b) => new Date(b.fecha_ejecucion) - new Date(a.fecha_ejecucion))
    .slice(0, 10); // Últimos 10
});

// Estadísticas calculadas
const stats = computed(() => {
  const all = allRescates.value;
  const completed = all.filter(r => r.fecha_ejecucion);
  const successful = completed.filter(r => r.exitoso);

  // Completados este mes
  const now = new Date();
  const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
  const completedThisMonth = completed.filter(r =>
    new Date(r.fecha_ejecucion) >= startOfMonth
  );

  // Tasa de éxito
  const successRate = completed.length > 0
    ? Math.round((successful.length / completed.length) * 100)
    : 0;

  return {
    active: activeOperations.value.length,
    today: scheduledToday.value.length,
    completed: completedThisMonth.length,
    successRate
  };
});

// Abrir modal de registrar resultado
function openRegisterResult(operation) {
  // Necesitamos pasar la denuncia asociada al modal
  const complaintData = {
    ...operation.denuncia,
    rescate_id: operation.id,
    rescate: operation
  };
  emit('register-result', complaintData);
}

// Helpers
function getComplaintTypeLabel(type) {
  const labels = {
    maltrato: 'Maltrato',
    abandono: 'Abandono',
    animal_herido: 'Animal herido',
    animal_peligroso: 'Animal peligroso',
    otro: 'Otro'
  };
  return labels[type] || type || 'Sin tipo';
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

function formatDateTime(dateString) {
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

function formatHour(dateString) {
  if (!dateString) return '--:--';
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return '--:--';
  return date.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
}

function formatDate(dateString) {
  if (!dateString) return 'Sin fecha';
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return 'Fecha inválida';
  return date.toLocaleDateString('es-CO', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
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
  margin: 0 0 0.5rem 0;
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

/* Loading state */
.loading-state {
  padding: 3rem;
  text-align: center;
  color: #666;
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

.result-btn:hover {
  background: #056b4e;
}

.result-btn-small {
  padding: 0.4rem 0.75rem;
  background: #068460;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.8rem;
}

.result-btn-small:hover {
  background: #056b4e;
}

/* Operativos programados */
.operations-list {
  padding: 0;
}

.operation-item {
  display: grid;
  grid-template-columns: 80px 1fr 150px 150px;
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

.result-exitoso { background: #E8F5E9; color: #2E7D32; }
.result-fallido { background: #FFEBEE; color: #C62828; }

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
