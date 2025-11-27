<!-- src/components/admin/AuditLog.vue -->
<!-- HU-023: Consultar Log de Auditoria del Sistema -->
<template>
  <section class="audit-log">
    <div class="audit-header">
      <div class="header-left">
        <h2 class="h3-tipografia-govco">Log de Auditoria</h2>
        <p class="text2-tipografia-govco">
          Registro completo de actividades del sistema - Integracion sci-audit
        </p>
      </div>
      <div class="header-right">
        <button type="button" class="btn-export" @click="exportLogs">
          =å Exportar CSV
        </button>
        <button type="button" class="btn-refresh" @click="refreshLogs">
          = Actualizar
        </button>
      </div>
    </div>

    <!-- Filtros Avanzados -->
    <div class="filters-panel">
      <div class="filters-row">
        <div class="filter-group">
          <label>Fecha Desde</label>
          <input type="date" v-model="filters.startDate" class="filter-input" />
        </div>
        <div class="filter-group">
          <label>Fecha Hasta</label>
          <input type="date" v-model="filters.endDate" class="filter-input" />
        </div>
        <div class="filter-group">
          <label>Usuario</label>
          <select v-model="filters.userId" class="filter-select">
            <option value="">Todos</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
        </div>
        <div class="filter-group">
          <label>Tipo de Accion</label>
          <select v-model="filters.actionType" class="filter-select">
            <option value="">Todas</option>
            <option value="CREATE">Crear</option>
            <option value="UPDATE">Actualizar</option>
            <option value="DELETE">Eliminar</option>
            <option value="LOGIN">Inicio Sesion</option>
            <option value="LOGOUT">Cierre Sesion</option>
            <option value="VIEW">Visualizacion</option>
            <option value="EXPORT">Exportacion</option>
          </select>
        </div>
      </div>
      <div class="filters-row">
        <div class="filter-group">
          <label>Modulo</label>
          <select v-model="filters.module" class="filter-select">
            <option value="">Todos</option>
            <option value="animales">Animales</option>
            <option value="adopciones">Adopciones</option>
            <option value="denuncias">Denuncias</option>
            <option value="vacunacion">Vacunacion</option>
            <option value="usuarios">Usuarios</option>
            <option value="reportes">Reportes</option>
            <option value="configuracion">Configuracion</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Resultado</label>
          <select v-model="filters.result" class="filter-select">
            <option value="">Todos</option>
            <option value="success">Exitoso</option>
            <option value="error">Error</option>
            <option value="warning">Advertencia</option>
          </select>
        </div>
        <div class="filter-group search-group">
          <label>Buscar</label>
          <input
            type="text"
            v-model="filters.search"
            placeholder="Buscar en descripcion, IP, ID..."
            class="filter-input"
          />
        </div>
        <div class="filter-actions">
          <button type="button" class="btn-filter" @click="applyFilters">
            Aplicar Filtros
          </button>
          <button type="button" class="btn-clear" @click="clearFilters">
            Limpiar
          </button>
        </div>
      </div>
    </div>

    <!-- Stats Resumen -->
    <div class="stats-bar">
      <div class="stat-item">
        <span class="stat-value">{{ stats.total.toLocaleString() }}</span>
        <span class="stat-label">Total Registros</span>
      </div>
      <div class="stat-item success">
        <span class="stat-value">{{ stats.success.toLocaleString() }}</span>
        <span class="stat-label">Exitosos</span>
      </div>
      <div class="stat-item error">
        <span class="stat-value">{{ stats.errors }}</span>
        <span class="stat-label">Errores</span>
      </div>
      <div class="stat-item">
        <span class="stat-value">{{ stats.uniqueUsers }}</span>
        <span class="stat-label">Usuarios Activos</span>
      </div>
    </div>

    <!-- Tabla de Logs -->
    <div class="logs-table-container">
      <table class="logs-table">
        <thead>
          <tr>
            <th class="col-time">Fecha/Hora</th>
            <th class="col-user">Usuario</th>
            <th class="col-action">Accion</th>
            <th class="col-module">Modulo</th>
            <th class="col-description">Descripcion</th>
            <th class="col-result">Resultado</th>
            <th class="col-ip">IP</th>
            <th class="col-details">Detalles</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in paginatedLogs" :key="log.id" :class="getRowClass(log)">
            <td class="col-time">
              <div class="time-display">
                <span class="date">{{ formatDate(log.timestamp) }}</span>
                <span class="time">{{ formatTime(log.timestamp) }}</span>
              </div>
            </td>
            <td class="col-user">
              <div class="user-display">
                <span class="user-name">{{ log.userName }}</span>
                <span class="user-role">{{ log.userRole }}</span>
              </div>
            </td>
            <td class="col-action">
              <span class="action-badge" :class="log.actionType.toLowerCase()">
                {{ getActionLabel(log.actionType) }}
              </span>
            </td>
            <td class="col-module">
              <span class="module-badge">{{ log.module }}</span>
            </td>
            <td class="col-description">{{ log.description }}</td>
            <td class="col-result">
              <span class="result-badge" :class="log.result">
                {{ getResultIcon(log.result) }} {{ capitalize(log.result) }}
              </span>
            </td>
            <td class="col-ip">{{ log.ipAddress }}</td>
            <td class="col-details">
              <button
                type="button"
                class="details-btn"
                @click="showDetails(log)"
              >
                =A
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="filteredLogs.length === 0" class="no-results">
        <p>No se encontraron registros con los filtros aplicados</p>
      </div>
    </div>

    <!-- Paginacion -->
    <div class="pagination">
      <div class="pagination-info">
        Mostrando {{ paginationStart }}-{{ paginationEnd }} de {{ filteredLogs.length }} registros
      </div>
      <div class="pagination-controls">
        <button
          type="button"
          @click="currentPage = 1"
          :disabled="currentPage === 1"
          class="page-btn"
        >
          « Primera
        </button>
        <button
          type="button"
          @click="currentPage--"
          :disabled="currentPage === 1"
          class="page-btn"
        >
          9 Anterior
        </button>
        <span class="page-numbers">
          <button
            v-for="page in visiblePages"
            :key="page"
            type="button"
            @click="currentPage = page"
            class="page-btn"
            :class="{ active: currentPage === page }"
          >
            {{ page }}
          </button>
        </span>
        <button
          type="button"
          @click="currentPage++"
          :disabled="currentPage === totalPages"
          class="page-btn"
        >
          Siguiente :
        </button>
        <button
          type="button"
          @click="currentPage = totalPages"
          :disabled="currentPage === totalPages"
          class="page-btn"
        >
          Ultima »
        </button>
      </div>
      <div class="page-size-selector">
        <label>Mostrar:</label>
        <select v-model="pageSize" class="page-size-select">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
      </div>
    </div>

    <!-- Modal de Detalles -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Detalle del Registro de Auditoria</h3>
          <button type="button" class="close-btn" @click="closeModal">×</button>
        </div>
        <div class="modal-body">
          <div class="detail-section">
            <h4>Informacion General</h4>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="detail-label">ID Registro</span>
                <span class="detail-value code">{{ selectedLog?.id }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Fecha y Hora</span>
                <span class="detail-value">{{ formatFullDate(selectedLog?.timestamp) }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Usuario</span>
                <span class="detail-value">{{ selectedLog?.userName }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Rol</span>
                <span class="detail-value">{{ selectedLog?.userRole }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Accion</span>
                <span class="detail-value">
                  <span class="action-badge" :class="selectedLog?.actionType?.toLowerCase()">
                    {{ getActionLabel(selectedLog?.actionType) }}
                  </span>
                </span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Modulo</span>
                <span class="detail-value">{{ selectedLog?.module }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Resultado</span>
                <span class="detail-value">
                  <span class="result-badge" :class="selectedLog?.result">
                    {{ getResultIcon(selectedLog?.result) }} {{ capitalize(selectedLog?.result) }}
                  </span>
                </span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Direccion IP</span>
                <span class="detail-value code">{{ selectedLog?.ipAddress }}</span>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <h4>Descripcion</h4>
            <p class="description-text">{{ selectedLog?.description }}</p>
          </div>

          <div class="detail-section">
            <h4>Informacion Tecnica</h4>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="detail-label">Navegador</span>
                <span class="detail-value">{{ selectedLog?.userAgent }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Endpoint</span>
                <span class="detail-value code">{{ selectedLog?.endpoint }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Metodo HTTP</span>
                <span class="detail-value code">{{ selectedLog?.httpMethod }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Tiempo Respuesta</span>
                <span class="detail-value">{{ selectedLog?.responseTime }}ms</span>
              </div>
            </div>
          </div>

          <div v-if="selectedLog?.entityId" class="detail-section">
            <h4>Entidad Afectada</h4>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="detail-label">Tipo de Entidad</span>
                <span class="detail-value">{{ selectedLog?.entityType }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">ID Entidad</span>
                <span class="detail-value code">{{ selectedLog?.entityId }}</span>
              </div>
            </div>
          </div>

          <div v-if="selectedLog?.changes" class="detail-section">
            <h4>Cambios Realizados</h4>
            <div class="changes-container">
              <pre>{{ JSON.stringify(selectedLog?.changes, null, 2) }}</pre>
            </div>
          </div>

          <div v-if="selectedLog?.errorDetails" class="detail-section error-section">
            <h4>Detalles del Error</h4>
            <div class="error-container">
              <p class="error-message">{{ selectedLog?.errorDetails?.message }}</p>
              <pre v-if="selectedLog?.errorDetails?.stack">{{ selectedLog?.errorDetails?.stack }}</pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const currentPage = ref(1);
const pageSize = ref(25);
const showModal = ref(false);
const selectedLog = ref(null);

// Filtros
const filters = ref({
  startDate: '',
  endDate: '',
  userId: '',
  actionType: '',
  module: '',
  result: '',
  search: ''
});

// Stats
const stats = ref({
  total: 15234,
  success: 14892,
  errors: 287,
  uniqueUsers: 42
});

// Usuarios para filtro
const users = ref([
  { id: 1, name: 'Carlos Ramirez' },
  { id: 2, name: 'Maria Lopez' },
  { id: 3, name: 'Juan Martinez' },
  { id: 4, name: 'Ana Gonzalez' },
  { id: 5, name: 'Roberto Vega' }
]);

// Logs de auditoria (mock data)
const auditLogs = ref([
  {
    id: 'AUD-20241127-001',
    timestamp: '2024-11-27T10:45:23',
    userId: 1,
    userName: 'Carlos Ramirez',
    userRole: 'Administrador',
    actionType: 'UPDATE',
    module: 'usuarios',
    description: 'Actualizo permisos del usuario Maria Lopez',
    result: 'success',
    ipAddress: '192.168.1.50',
    userAgent: 'Chrome 120.0 / Windows 10',
    endpoint: '/api/users/2/permissions',
    httpMethod: 'PUT',
    responseTime: 245,
    entityType: 'Usuario',
    entityId: 2,
    changes: {
      before: { permissions: ['read'] },
      after: { permissions: ['read', 'write'] }
    }
  },
  {
    id: 'AUD-20241127-002',
    timestamp: '2024-11-27T10:30:15',
    userId: 3,
    userName: 'Juan Martinez',
    userRole: 'Veterinario',
    actionType: 'CREATE',
    module: 'animales',
    description: 'Registro nuevo animal: Luna (Perro)',
    result: 'success',
    ipAddress: '192.168.1.55',
    userAgent: 'Firefox 121.0 / Windows 11',
    endpoint: '/api/animals',
    httpMethod: 'POST',
    responseTime: 312,
    entityType: 'Animal',
    entityId: 'AN-1234'
  },
  {
    id: 'AUD-20241127-003',
    timestamp: '2024-11-27T10:15:42',
    userId: 4,
    userName: 'Ana Gonzalez',
    userRole: 'Inspector',
    actionType: 'UPDATE',
    module: 'denuncias',
    description: 'Actualizo estado de denuncia DN-5678 a "En proceso"',
    result: 'success',
    ipAddress: '192.168.1.60',
    userAgent: 'Chrome 120.0 / Android',
    endpoint: '/api/complaints/DN-5678/status',
    httpMethod: 'PATCH',
    responseTime: 189,
    entityType: 'Denuncia',
    entityId: 'DN-5678',
    changes: {
      before: { status: 'pendiente' },
      after: { status: 'en_proceso' }
    }
  },
  {
    id: 'AUD-20241127-004',
    timestamp: '2024-11-27T09:58:11',
    userId: 2,
    userName: 'Maria Lopez',
    userRole: 'Director',
    actionType: 'EXPORT',
    module: 'reportes',
    description: 'Exporto reporte de adopciones Q4 2024 en formato PDF',
    result: 'success',
    ipAddress: '192.168.1.45',
    userAgent: 'Safari 17.0 / macOS',
    endpoint: '/api/reports/adoptions/export',
    httpMethod: 'POST',
    responseTime: 1523,
    entityType: 'Reporte',
    entityId: 'RPT-ADO-Q4-2024'
  },
  {
    id: 'AUD-20241127-005',
    timestamp: '2024-11-27T09:45:33',
    userId: 1,
    userName: 'Carlos Ramirez',
    userRole: 'Administrador',
    actionType: 'DELETE',
    module: 'usuarios',
    description: 'Desactivo cuenta de usuario: Roberto Vega',
    result: 'success',
    ipAddress: '192.168.1.50',
    userAgent: 'Chrome 120.0 / Windows 10',
    endpoint: '/api/users/5/deactivate',
    httpMethod: 'DELETE',
    responseTime: 178,
    entityType: 'Usuario',
    entityId: 5,
    changes: {
      before: { status: 'activo' },
      after: { status: 'inactivo' }
    }
  },
  {
    id: 'AUD-20241127-006',
    timestamp: '2024-11-27T09:30:00',
    userId: 3,
    userName: 'Juan Martinez',
    userRole: 'Veterinario',
    actionType: 'UPDATE',
    module: 'vacunacion',
    description: 'Registro vacuna antirrabica para animal AN-1100',
    result: 'success',
    ipAddress: '192.168.1.55',
    userAgent: 'Firefox 121.0 / Windows 11',
    endpoint: '/api/vaccinations',
    httpMethod: 'POST',
    responseTime: 267,
    entityType: 'Vacunacion',
    entityId: 'VAC-8901'
  },
  {
    id: 'AUD-20241127-007',
    timestamp: '2024-11-27T09:15:22',
    userId: 5,
    userName: 'Roberto Vega',
    userRole: 'Gestor Adopciones',
    actionType: 'UPDATE',
    module: 'adopciones',
    description: 'Actualizo estado de adopcion AD-3456 a "Completada"',
    result: 'success',
    ipAddress: '192.168.1.70',
    userAgent: 'Edge 120.0 / Windows 10',
    endpoint: '/api/adoptions/AD-3456/status',
    httpMethod: 'PATCH',
    responseTime: 234,
    entityType: 'Adopcion',
    entityId: 'AD-3456'
  },
  {
    id: 'AUD-20241127-008',
    timestamp: '2024-11-27T09:00:05',
    userId: 2,
    userName: 'Maria Lopez',
    userRole: 'Director',
    actionType: 'LOGIN',
    module: 'autenticacion',
    description: 'Inicio de sesion exitoso',
    result: 'success',
    ipAddress: '192.168.1.45',
    userAgent: 'Safari 17.0 / macOS',
    endpoint: '/api/auth/login',
    httpMethod: 'POST',
    responseTime: 523
  },
  {
    id: 'AUD-20241127-009',
    timestamp: '2024-11-27T08:45:18',
    userId: 4,
    userName: 'Ana Gonzalez',
    userRole: 'Inspector',
    actionType: 'CREATE',
    module: 'denuncias',
    description: 'Registro nueva denuncia por maltrato animal',
    result: 'error',
    ipAddress: '192.168.1.60',
    userAgent: 'Chrome 120.0 / Android',
    endpoint: '/api/complaints',
    httpMethod: 'POST',
    responseTime: 5023,
    errorDetails: {
      message: 'Error de conexion con base de datos',
      code: 'DB_CONNECTION_TIMEOUT',
      stack: 'Error: Connection timeout after 5000ms\n  at Database.connect (/app/db/index.js:45:11)'
    }
  },
  {
    id: 'AUD-20241127-010',
    timestamp: '2024-11-27T08:30:45',
    userId: 1,
    userName: 'Carlos Ramirez',
    userRole: 'Administrador',
    actionType: 'VIEW',
    module: 'auditoria',
    description: 'Consulto logs de auditoria del mes de octubre',
    result: 'success',
    ipAddress: '192.168.1.50',
    userAgent: 'Chrome 120.0 / Windows 10',
    endpoint: '/api/audit/logs',
    httpMethod: 'GET',
    responseTime: 892
  },
  {
    id: 'AUD-20241127-011',
    timestamp: '2024-11-27T08:15:30',
    userId: 3,
    userName: 'Juan Martinez',
    userRole: 'Veterinario',
    actionType: 'LOGIN',
    module: 'autenticacion',
    description: 'Inicio de sesion exitoso con MFA',
    result: 'success',
    ipAddress: '192.168.1.55',
    userAgent: 'Firefox 121.0 / Windows 11',
    endpoint: '/api/auth/login/mfa',
    httpMethod: 'POST',
    responseTime: 723
  },
  {
    id: 'AUD-20241127-012',
    timestamp: '2024-11-27T08:00:12',
    userId: null,
    userName: 'Sistema',
    userRole: 'Automatico',
    actionType: 'UPDATE',
    module: 'configuracion',
    description: 'Respaldo automatico de base de datos completado',
    result: 'success',
    ipAddress: '127.0.0.1',
    userAgent: 'Cron Job / Server',
    endpoint: '/api/system/backup',
    httpMethod: 'POST',
    responseTime: 45230
  }
]);

// Computed
const filteredLogs = computed(() => {
  let result = auditLogs.value;

  if (filters.value.startDate) {
    const start = new Date(filters.value.startDate);
    result = result.filter(log => new Date(log.timestamp) >= start);
  }

  if (filters.value.endDate) {
    const end = new Date(filters.value.endDate);
    end.setHours(23, 59, 59);
    result = result.filter(log => new Date(log.timestamp) <= end);
  }

  if (filters.value.userId) {
    result = result.filter(log => log.userId === filters.value.userId);
  }

  if (filters.value.actionType) {
    result = result.filter(log => log.actionType === filters.value.actionType);
  }

  if (filters.value.module) {
    result = result.filter(log => log.module === filters.value.module);
  }

  if (filters.value.result) {
    result = result.filter(log => log.result === filters.value.result);
  }

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase();
    result = result.filter(log =>
      log.description.toLowerCase().includes(search) ||
      log.ipAddress.includes(search) ||
      log.id.toLowerCase().includes(search) ||
      log.userName.toLowerCase().includes(search)
    );
  }

  return result;
});

const totalPages = computed(() => {
  return Math.max(1, Math.ceil(filteredLogs.value.length / pageSize.value));
});

const paginatedLogs = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredLogs.value.slice(start, end);
});

const paginationStart = computed(() => {
  if (filteredLogs.value.length === 0) return 0;
  return (currentPage.value - 1) * pageSize.value + 1;
});

const paginationEnd = computed(() => {
  return Math.min(currentPage.value * pageSize.value, filteredLogs.value.length);
});

const visiblePages = computed(() => {
  const pages = [];
  const total = totalPages.value;
  const current = currentPage.value;

  let start = Math.max(1, current - 2);
  let end = Math.min(total, current + 2);

  if (end - start < 4) {
    if (start === 1) {
      end = Math.min(total, 5);
    } else if (end === total) {
      start = Math.max(1, total - 4);
    }
  }

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  return pages;
});

// Watch
watch(pageSize, () => {
  currentPage.value = 1;
});

// Methods
function formatDate(timestamp) {
  if (!timestamp) return '-';
  const date = new Date(timestamp);
  return date.toLocaleDateString('es-CO', {
    day: '2-digit',
    month: 'short'
  });
}

function formatTime(timestamp) {
  if (!timestamp) return '-';
  const date = new Date(timestamp);
  return date.toLocaleTimeString('es-CO', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
}

function formatFullDate(timestamp) {
  if (!timestamp) return '-';
  const date = new Date(timestamp);
  return date.toLocaleString('es-CO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
}

function capitalize(str) {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function getActionLabel(action) {
  const labels = {
    CREATE: 'Crear',
    UPDATE: 'Actualizar',
    DELETE: 'Eliminar',
    LOGIN: 'Login',
    LOGOUT: 'Logout',
    VIEW: 'Ver',
    EXPORT: 'Exportar'
  };
  return labels[action] || action;
}

function getResultIcon(result) {
  const icons = {
    success: '',
    error: '',
    warning: ' '
  };
  return icons[result] || '';
}

function getRowClass(log) {
  return {
    'row-error': log.result === 'error',
    'row-warning': log.result === 'warning'
  };
}

function applyFilters() {
  currentPage.value = 1;
}

function clearFilters() {
  filters.value = {
    startDate: '',
    endDate: '',
    userId: '',
    actionType: '',
    module: '',
    result: '',
    search: ''
  };
  currentPage.value = 1;
}

function showDetails(log) {
  selectedLog.value = log;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  selectedLog.value = null;
}

function exportLogs() {
  const csvContent = generateCSV();
  downloadFile(csvContent, 'audit_log_export.csv', 'text/csv');
}

function generateCSV() {
  const headers = ['ID', 'Fecha/Hora', 'Usuario', 'Rol', 'Accion', 'Modulo', 'Descripcion', 'Resultado', 'IP'];
  const rows = filteredLogs.value.map(log => [
    log.id,
    log.timestamp,
    log.userName,
    log.userRole,
    log.actionType,
    log.module,
    `"${log.description.replace(/"/g, '""')}"`,
    log.result,
    log.ipAddress
  ]);

  return [headers.join(','), ...rows.map(row => row.join(','))].join('\n');
}

function downloadFile(content, filename, type) {
  const blob = new Blob([content], { type });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  link.click();
  URL.revokeObjectURL(url);
}

function refreshLogs() {
  // Simular actualizacion
  alert('Logs actualizados desde sci-audit');
}
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

.header-right {
  display: flex;
  gap: 0.75rem;
}

.btn-export,
.btn-refresh {
  padding: 0.65rem 1rem;
  border-radius: 4px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-export {
  background: #E8F5E9;
  color: #2E7D32;
  border: 1px solid #2E7D32;
}

.btn-export:hover {
  background: #2E7D32;
  color: white;
}

.btn-refresh {
  background: #E8F0FE;
  color: #3366CC;
  border: 1px solid #3366CC;
}

.btn-refresh:hover {
  background: #3366CC;
  color: white;
}

/* Filters Panel */
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
  margin-bottom: 1rem;
}

.filters-row:last-child {
  margin-bottom: 0;
}

.filter-group {
  display: flex;
  flex-direction: column;
  min-width: 150px;
}

.filter-group.search-group {
  flex: 1;
  min-width: 250px;
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
  align-items: flex-end;
  gap: 0.5rem;
}

.btn-filter {
  padding: 0.6rem 1rem;
  background: #004884;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
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
}

.btn-clear:hover {
  background: #f5f5f5;
}

/* Stats Bar */
.stats-bar {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.stat-item {
  background: white;
  border-radius: 8px;
  padding: 0.75rem 1.25rem;
  display: flex;
  flex-direction: column;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  border-left: 3px solid #004884;
}

.stat-item.success { border-color: #068460; }
.stat-item.error { border-color: #A80521; }

.stat-value {
  font-size: 1.25rem;
  font-weight: bold;
  color: #004884;
}

.stat-label {
  font-size: 0.8rem;
  color: #666;
}

/* Logs Table */
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
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #E0E0E0;
}

.logs-table th {
  background: #f5f7fb;
  font-weight: 600;
  color: #004884;
  white-space: nowrap;
  position: sticky;
  top: 0;
}

.logs-table tbody tr:hover {
  background: #f9f9f9;
}

.logs-table tbody tr.row-error {
  background: #FFF5F5;
}

.logs-table tbody tr.row-warning {
  background: #FFFBF0;
}

.col-time { width: 100px; }
.col-user { width: 140px; }
.col-action { width: 100px; }
.col-module { width: 100px; }
.col-result { width: 100px; }
.col-ip { width: 110px; }
.col-details { width: 60px; text-align: center; }

.time-display,
.user-display {
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

.user-display .user-name {
  font-weight: 500;
}

.user-display .user-role {
  font-size: 0.8rem;
  color: #666;
}

/* Badges */
.action-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 500;
}

.action-badge.create { background: #E8F5E9; color: #2E7D32; }
.action-badge.update { background: #E8F0FE; color: #1565C0; }
.action-badge.delete { background: #FFEBEE; color: #C62828; }
.action-badge.login { background: #F3E5F5; color: #7B1FA2; }
.action-badge.logout { background: #ECEFF1; color: #546E7A; }
.action-badge.view { background: #E0F7FA; color: #00838F; }
.action-badge.export { background: #FFF3E0; color: #EF6C00; }

.module-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  background: #f5f5f5;
  color: #333;
  text-transform: capitalize;
}

.result-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 500;
}

.result-badge.success { background: #E8F5E9; color: #2E7D32; }
.result-badge.error { background: #FFEBEE; color: #C62828; }
.result-badge.warning { background: #FFF8E1; color: #F57F17; }

.details-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: #E8F0FE;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.details-btn:hover {
  background: #3366CC;
  transform: scale(1.1);
}

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
  align-items: center;
  gap: 0.25rem;
}

.page-numbers {
  display: flex;
  gap: 0.25rem;
}

.page-btn {
  padding: 0.5rem 0.75rem;
  background: #f5f5f5;
  color: #333;
  border: 1px solid #E0E0E0;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.85rem;
  transition: all 0.2s;
}

.page-btn:hover:not(:disabled) {
  background: #E8F0FE;
  border-color: #3366CC;
  color: #3366CC;
}

.page-btn.active {
  background: #004884;
  color: white;
  border-color: #004884;
}

.page-btn:disabled {
  background: #f5f5f5;
  color: #ccc;
  cursor: not-allowed;
}

.page-size-selector {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
}

.page-size-select {
  padding: 0.4rem 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #E0E0E0;
  position: sticky;
  top: 0;
  background: white;
  z-index: 1;
}

.modal-header h3 {
  margin: 0;
  color: #004884;
}

.close-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: #f5f5f5;
  border-radius: 50%;
  font-size: 1.25rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  background: #e0e0e0;
}

.modal-body {
  padding: 1.5rem;
}

.detail-section {
  margin-bottom: 1.5rem;
}

.detail-section:last-child {
  margin-bottom: 0;
}

.detail-section h4 {
  margin: 0 0 1rem 0;
  color: #004884;
  font-size: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #E0E0E0;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
}

.detail-label {
  font-size: 0.8rem;
  color: #666;
  margin-bottom: 0.25rem;
}

.detail-value {
  font-weight: 500;
  color: #333;
}

.detail-value.code {
  font-family: monospace;
  background: #f5f5f5;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.9rem;
}

.description-text {
  background: #f9f9f9;
  padding: 1rem;
  border-radius: 8px;
  margin: 0;
  color: #333;
}

.changes-container,
.error-container {
  background: #f5f5f5;
  border-radius: 8px;
  padding: 1rem;
  overflow-x: auto;
}

.changes-container pre,
.error-container pre {
  margin: 0;
  font-size: 0.85rem;
  white-space: pre-wrap;
}

.error-section {
  background: #FFF5F5;
  border-radius: 8px;
  padding: 1rem;
}

.error-section h4 {
  color: #C62828;
}

.error-message {
  color: #C62828;
  font-weight: 500;
  margin: 0 0 1rem 0;
}

.error-container {
  background: #FFEBEE;
}

/* Responsive */
@media (max-width: 1200px) {
  .detail-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 992px) {
  .filters-row {
    flex-direction: column;
  }

  .filter-group {
    width: 100%;
  }

  .filter-actions {
    width: 100%;
    justify-content: flex-start;
  }

  .stats-bar {
    flex-wrap: wrap;
  }

  .stat-item {
    flex: 1;
    min-width: 120px;
  }
}

@media (max-width: 768px) {
  .audit-log {
    padding: 1rem;
  }

  .audit-header {
    flex-direction: column;
  }

  .header-right {
    width: 100%;
    justify-content: flex-start;
  }

  .pagination {
    flex-direction: column;
    align-items: stretch;
    text-align: center;
  }

  .pagination-controls {
    justify-content: center;
    flex-wrap: wrap;
  }

  .modal-content {
    width: 95%;
  }
}
</style>
