<!-- src/components/admin/AuditLog.vue -->
<template>
  <section class="audit-log">
    <div class="audit-header">
      <div>
        <h2 class="h3-tipografia-govco">Log de Auditoría</h2>
        <p class="text2-tipografia-govco">Registro de actividades del sistema</p>
      </div>
      <button type="button" class="btn-refresh" @click="fetchEventos">
        Actualizar
      </button>
    </div>

    <!-- Filtros -->
    <div class="filters-panel">
      <div class="filters-row">
        <div class="filter-group">
          <label>Fecha Desde</label>
          <input type="date" v-model="filters.fecha_inicio" class="filter-input" />
        </div>
        <div class="filter-group">
          <label>Fecha Hasta</label>
          <input type="date" v-model="filters.fecha_fin" class="filter-input" />
        </div>
        <div class="filter-group">
          <label>Usuario</label>
          <select v-model="filters.usuario_id" class="filter-select">
            <option value="">Todos</option>
            <option v-for="u in usuariosFiltro" :key="u.id" :value="u.id">
              {{ u.nombres }} {{ u.apellidos }}
            </option>
          </select>
        </div>
        <div class="filter-group">
          <label>Acción</label>
          <select v-model="filters.accion" class="filter-select">
            <option value="">Todas</option>
            <option v-for="a in accionesFiltro" :key="a" :value="a">
              {{ a }}
            </option>
          </select>
        </div>
        <div class="filter-group">
          <label>Resultado</label>
          <select v-model="filters.resultado" class="filter-select">
            <option value="">Todos</option>
            <option value="exitoso">Exitoso</option>
            <option value="fallido">Fallido</option>
            <option value="denegado">Denegado</option>
          </select>
        </div>
        <div class="filter-actions">
          <button type="button" class="btn-filter" @click="applyFilters">Filtrar</button>
          <button type="button" class="btn-clear" @click="clearFilters">Limpiar</button>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <p>Cargando registros...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
      <button type="button" class="btn-refresh" @click="fetchEventos">Reintentar</button>
    </div>

    <!-- Tabla -->
    <template v-else>
      <div class="logs-table-container">
        <table class="logs-table">
          <thead>
            <tr>
              <th>Fecha / Hora</th>
              <th>Usuario</th>
              <th>Acción</th>
              <th>Recurso</th>
              <th>Resultado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="ev in eventos" :key="ev.id" :class="getRowClass(ev)">
              <td>
                <div class="time-display">
                  <span class="date">{{ formatDate(ev.timestamp) }}</span>
                  <span class="time">{{ formatTime(ev.timestamp) }}</span>
                </div>
              </td>
              <td>
                {{ ev.usuario ? `${ev.usuario.nombres} ${ev.usuario.apellidos}` : 'Sistema' }}
              </td>
              <td>
                <span class="action-badge" :class="getActionClass(ev.accion)">
                  {{ ev.accion }}
                </span>
              </td>
              <td>{{ ev.recurso || '-' }}</td>
              <td>
                <span class="result-badge" :class="ev.resultado">
                  {{ capitalize(ev.resultado) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="eventos.length === 0" class="no-results">
          <p>No se encontraron registros</p>
        </div>
      </div>

      <!-- Paginación -->
      <div v-if="pagination.lastPage > 1" class="pagination">
        <span class="pagination-info">
          Página {{ pagination.currentPage }} de {{ pagination.lastPage }}
          ({{ pagination.total }} registros)
        </span>
        <div class="pagination-controls">
          <button
            type="button"
            class="page-btn"
            :disabled="pagination.currentPage === 1"
            @click="goToPage(pagination.currentPage - 1)"
          >
            Anterior
          </button>
          <button
            type="button"
            class="page-btn"
            :disabled="pagination.currentPage === pagination.lastPage"
            @click="goToPage(pagination.currentPage + 1)"
          >
            Siguiente
          </button>
        </div>
      </div>
    </template>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { auditService } from '../../services/auditService';

const eventos = ref([]);
const loading = ref(false);
const error = ref(null);

const usuariosFiltro = ref([]);
const accionesFiltro = ref([]);

const filters = ref({
  fecha_inicio: '',
  fecha_fin: '',
  usuario_id: '',
  accion: '',
  resultado: '',
});

const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  total: 0,
});

async function fetchEventos(page = 1) {
  loading.value = true;
  error.value = null;
  try {
    const params = { page, per_page: 20 };

    if (filters.value.fecha_inicio) params.fecha_inicio = filters.value.fecha_inicio;
    if (filters.value.fecha_fin) params.fecha_fin = filters.value.fecha_fin;
    if (filters.value.usuario_id) params.usuario_id = filters.value.usuario_id;
    if (filters.value.accion) params.accion = filters.value.accion;
    if (filters.value.resultado) params.resultado = filters.value.resultado;

    const res = await auditService.getEventos(params);
    const data = res.data;

    eventos.value = data.data || [];
    pagination.value = {
      currentPage: data.current_page,
      lastPage: data.last_page,
      total: data.total,
    };
  } catch (e) {
    error.value = 'Error al cargar los registros de auditoría';
    console.error(e);
  } finally {
    loading.value = false;
  }
}

async function fetchFiltros() {
  try {
    const [accionesRes, usuariosRes] = await Promise.all([
      auditService.getAcciones(),
      auditService.getUsuarios(),
    ]);
    accionesFiltro.value = accionesRes.data || [];
    usuariosFiltro.value = usuariosRes.data || [];
  } catch (e) {
    console.error('Error cargando filtros:', e);
  }
}

function applyFilters() {
  fetchEventos(1);
}

function clearFilters() {
  filters.value = {
    fecha_inicio: '',
    fecha_fin: '',
    usuario_id: '',
    accion: '',
    resultado: '',
  };
  fetchEventos(1);
}

function goToPage(page) {
  fetchEventos(page);
}

function formatDate(timestamp) {
  if (!timestamp) return '-';
  return new Date(timestamp).toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  });
}

function formatTime(timestamp) {
  if (!timestamp) return '';
  return new Date(timestamp).toLocaleTimeString('es-CO', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  });
}

function capitalize(str) {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function getActionClass(accion) {
  if (!accion) return '';
  const a = accion.toUpperCase();
  if (a.includes('CREAR') || a === 'CREATE') return 'crear';
  if (a.includes('ACTUALIZAR') || a === 'UPDATE') return 'actualizar';
  if (a.includes('ELIMINAR') || a === 'DELETE') return 'eliminar';
  if (a.includes('LOGIN')) return 'login';
  if (a.includes('LOGOUT')) return 'logout';
  if (a.includes('CONSULTAR') || a === 'VIEW') return 'consultar';
  return '';
}

function getRowClass(ev) {
  return {
    'row-error': ev.resultado === 'fallido',
    'row-denied': ev.resultado === 'denegado',
  };
}

onMounted(() => {
  fetchEventos();
  fetchFiltros();
});
</script>

<style scoped>
.audit-log {
  max-width: 1400px;
  margin: 0 auto;
  padding: 1.5rem;
}

.audit-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.audit-header h2 {
  margin: 0 0 0.25rem 0;
  color: #004884;
}

.audit-header p {
  margin: 0;
  color: #666;
}

.btn-refresh {
  padding: 0.6rem 1.2rem;
  background: #E8F0FE;
  color: #3366CC;
  border: 1px solid #3366CC;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.btn-refresh:hover {
  background: #3366CC;
  color: white;
}

/* Filters */
.filters-panel {
  background: white;
  border-radius: 12px;
  padding: 1.25rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  margin-bottom: 1.5rem;
}

.filters-row {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  align-items: flex-end;
}

.filter-group {
  display: flex;
  flex-direction: column;
  min-width: 140px;
}

.filter-group label {
  font-size: 0.8rem;
  color: #666;
  margin-bottom: 0.35rem;
}

.filter-input,
.filter-select {
  padding: 0.6rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 0.9rem;
}

.filter-input:focus,
.filter-select:focus {
  outline: none;
  border-color: #3366CC;
}

.filter-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-filter {
  padding: 0.6rem 1rem;
  background: #004884;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-filter:hover {
  background: #003366;
}

.btn-clear {
  padding: 0.6rem 1rem;
  background: white;
  color: #666;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-clear:hover {
  background: #f5f5f5;
}

/* Loading / Error */
.loading-state,
.error-state {
  background: white;
  border-radius: 12px;
  padding: 3rem;
  text-align: center;
  color: #666;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.error-state {
  color: #C62828;
}

.error-state .btn-refresh {
  margin-top: 1rem;
}

/* Table */
.logs-table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  overflow-x: auto;
  margin-bottom: 1rem;
}

.logs-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.logs-table th,
.logs-table td {
  padding: 0.85rem 1rem;
  text-align: left;
  border-bottom: 1px solid #E0E0E0;
}

.logs-table th {
  background: #f5f7fb;
  font-weight: 600;
  color: #004884;
  white-space: nowrap;
}

.logs-table tbody tr:hover {
  background: #f9f9f9;
}

.logs-table tbody tr.row-error {
  background: #FFF5F5;
}

.logs-table tbody tr.row-denied {
  background: #FFF8E1;
}

.time-display {
  display: flex;
  flex-direction: column;
}

.time-display .date {
  font-weight: 500;
}

.time-display .time {
  font-size: 0.8rem;
  color: #666;
}

/* Badges */
.action-badge {
  padding: 0.25rem 0.6rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 500;
  background: #f5f5f5;
  color: #333;
}

.action-badge.crear { background: #E8F5E9; color: #2E7D32; }
.action-badge.actualizar { background: #E8F0FE; color: #1565C0; }
.action-badge.eliminar { background: #FFEBEE; color: #C62828; }
.action-badge.login { background: #F3E5F5; color: #7B1FA2; }
.action-badge.logout { background: #ECEFF1; color: #546E7A; }
.action-badge.consultar { background: #E0F7FA; color: #00838F; }

.result-badge {
  padding: 0.25rem 0.6rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 500;
}

.result-badge.exitoso { background: #E8F5E9; color: #2E7D32; }
.result-badge.fallido { background: #FFEBEE; color: #C62828; }
.result-badge.denegado { background: #FFF8E1; color: #F57F17; }

.no-results {
  padding: 2rem;
  text-align: center;
  color: #666;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  flex-wrap: wrap;
  gap: 1rem;
}

.pagination-info {
  font-size: 0.9rem;
  color: #666;
}

.pagination-controls {
  display: flex;
  gap: 0.5rem;
}

.page-btn {
  padding: 0.5rem 1rem;
  background: #E8F0FE;
  color: #3366CC;
  border: 1px solid #3366CC;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.85rem;
  transition: all 0.2s;
}

.page-btn:hover:not(:disabled) {
  background: #3366CC;
  color: white;
}

.page-btn:disabled {
  background: #f5f5f5;
  color: #ccc;
  border-color: #D0D0D0;
  cursor: not-allowed;
}

/* Responsive */
@media (max-width: 992px) {
  .filters-row {
    flex-direction: column;
  }

  .filter-group {
    width: 100%;
  }

  .filter-actions {
    width: 100%;
  }
}

@media (max-width: 768px) {
  .audit-log {
    padding: 1rem;
  }

  .audit-header {
    flex-direction: column;
  }

  .pagination {
    flex-direction: column;
    text-align: center;
  }

  .pagination-controls {
    justify-content: center;
  }
}
</style>
