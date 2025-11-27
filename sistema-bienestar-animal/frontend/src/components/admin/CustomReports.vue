<!-- src/components/admin/CustomReports.vue -->
<!-- HU-021: Generar Reportes Personalizados -->
<template>
  <section class="custom-reports">
    <div class="reports-header">
      <div class="header-left">
        <h2 class="h3-tipografia-govco">Reportes Personalizados</h2>
        <p class="text2-tipografia-govco">
          Configure y genere reportes segun sus necesidades
        </p>
      </div>
    </div>

    <div class="reports-layout">
      <!-- Panel de Configuracion -->
      <div class="config-panel">
        <h3 class="h5-tipografia-govco">Configurar Reporte</h3>

        <!-- Tipo de Reporte -->
        <div class="form-group">
          <label class="form-label">Tipo de Reporte *</label>
          <select v-model="reportConfig.type" class="form-select">
            <option value="">Seleccione un tipo</option>
            <option value="animals">Animales Registrados</option>
            <option value="adoptions">Adopciones</option>
            <option value="complaints">Denuncias</option>
            <option value="vaccinations">Vacunaciones</option>
            <option value="rescues">Operativos de Rescate</option>
            <option value="users">Usuarios del Sistema</option>
          </select>
        </div>

        <!-- Rango de Fechas -->
        <div class="form-group">
          <label class="form-label">Rango de Fechas *</label>
          <div class="date-range">
            <input
              type="date"
              v-model="reportConfig.startDate"
              class="form-input"
            />
            <span>a</span>
            <input
              type="date"
              v-model="reportConfig.endDate"
              class="form-input"
            />
          </div>
        </div>

        <!-- Filtros Dinamicos segun tipo -->
        <div v-if="reportConfig.type === 'animals'" class="dynamic-filters">
          <div class="form-group">
            <label class="form-label">Especie</label>
            <select v-model="reportConfig.filters.species" class="form-select">
              <option value="">Todas</option>
              <option value="perro">Perros</option>
              <option value="gato">Gatos</option>
              <option value="equino">Equinos</option>
              <option value="otro">Otros</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Estado</label>
            <select v-model="reportConfig.filters.status" class="form-select">
              <option value="">Todos</option>
              <option value="disponible">Disponible</option>
              <option value="adoptado">Adoptado</option>
              <option value="en_tratamiento">En tratamiento</option>
            </select>
          </div>
        </div>

        <div v-if="reportConfig.type === 'complaints'" class="dynamic-filters">
          <div class="form-group">
            <label class="form-label">Tipo de Denuncia</label>
            <select v-model="reportConfig.filters.complaintType" class="form-select">
              <option value="">Todas</option>
              <option value="maltrato">Maltrato</option>
              <option value="abandono">Abandono</option>
              <option value="negligencia">Negligencia</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Estado</label>
            <select v-model="reportConfig.filters.status" class="form-select">
              <option value="">Todos</option>
              <option value="pendiente">Pendiente</option>
              <option value="en_proceso">En proceso</option>
              <option value="resuelto">Resuelto</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Prioridad</label>
            <select v-model="reportConfig.filters.priority" class="form-select">
              <option value="">Todas</option>
              <option value="critica">Critica</option>
              <option value="alta">Alta</option>
              <option value="media">Media</option>
              <option value="baja">Baja</option>
            </select>
          </div>
        </div>

        <div v-if="reportConfig.type === 'adoptions'" class="dynamic-filters">
          <div class="form-group">
            <label class="form-label">Estado de Adopcion</label>
            <select v-model="reportConfig.filters.adoptionStatus" class="form-select">
              <option value="">Todos</option>
              <option value="completada">Completada</option>
              <option value="en_proceso">En proceso</option>
              <option value="cancelada">Cancelada</option>
            </select>
          </div>
        </div>

        <div v-if="reportConfig.type === 'vaccinations'" class="dynamic-filters">
          <div class="form-group">
            <label class="form-label">Tipo de Vacuna</label>
            <select v-model="reportConfig.filters.vaccineType" class="form-select">
              <option value="">Todas</option>
              <option value="rabia">Antirrabica</option>
              <option value="triple">Triple Felina/Canina</option>
              <option value="parvovirus">Parvovirus</option>
              <option value="otra">Otra</option>
            </select>
          </div>
        </div>

        <!-- Columnas a incluir -->
        <div class="form-group">
          <label class="form-label">Columnas a Incluir</label>
          <div class="columns-selection">
            <label
              v-for="col in availableColumns"
              :key="col.id"
              class="checkbox-label"
            >
              <input
                type="checkbox"
                :value="col.id"
                v-model="reportConfig.columns"
              />
              {{ col.label }}
            </label>
          </div>
        </div>

        <!-- Agrupacion -->
        <div class="form-group">
          <label class="form-label">Agrupar por</label>
          <select v-model="reportConfig.groupBy" class="form-select">
            <option value="">Sin agrupacion</option>
            <option value="day">Dia</option>
            <option value="week">Semana</option>
            <option value="month">Mes</option>
            <option value="species">Especie</option>
            <option value="status">Estado</option>
          </select>
        </div>

        <!-- Botones de Accion -->
        <div class="action-buttons">
          <button
            type="button"
            class="btn-preview"
            @click="generatePreview"
            :disabled="!isConfigValid"
          >
            Vista Previa
          </button>
          <button
            type="button"
            class="btn-reset"
            @click="resetConfig"
          >
            Limpiar
          </button>
        </div>
      </div>

      <!-- Panel de Vista Previa y Resultados -->
      <div class="preview-panel">
        <!-- Estado vacio -->
        <div v-if="!showPreview && !loading" class="empty-state">
          <div class="empty-icon">📊</div>
          <h4>Configure su reporte</h4>
          <p>Seleccione el tipo de reporte y los filtros deseados, luego presione "Vista Previa"</p>
        </div>

        <!-- Cargando -->
        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p>Generando reporte...</p>
        </div>

        <!-- Vista Previa -->
        <div v-if="showPreview && !loading" class="report-preview">
          <div class="preview-header">
            <div class="preview-info">
              <h4>{{ getReportTitle() }}</h4>
              <p>Periodo: {{ formatDate(reportConfig.startDate) }} - {{ formatDate(reportConfig.endDate) }}</p>
              <p class="record-count">{{ previewData.length }} registros encontrados</p>
            </div>
            <div class="export-buttons">
              <button type="button" class="export-btn pdf" @click="exportReport('pdf')">
                📄 PDF
              </button>
              <button type="button" class="export-btn excel" @click="exportReport('xlsx')">
                📗 Excel
              </button>
              <button type="button" class="export-btn csv" @click="exportReport('csv')">
                📋 CSV
              </button>
            </div>
          </div>

          <!-- Tabla de Resultados -->
          <div class="preview-table-container">
            <table class="preview-table">
              <thead>
                <tr>
                  <th v-for="col in selectedColumnLabels" :key="col">{{ col }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, index) in paginatedData" :key="index">
                  <td v-for="col in reportConfig.columns" :key="col">
                    {{ row[col] || '-' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginacion -->
          <div class="pagination" v-if="previewData.length > pageSize">
            <button
              type="button"
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="page-btn"
            >
              Anterior
            </button>
            <span class="page-info">
              Pagina {{ currentPage }} de {{ totalPages }}
            </span>
            <button
              type="button"
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="page-btn"
            >
              Siguiente
            </button>
          </div>

          <!-- Resumen Estadistico -->
          <div class="stats-summary" v-if="showStats">
            <h5>Resumen Estadistico</h5>
            <div class="stats-grid">
              <div class="stat-item">
                <span class="stat-value">{{ previewData.length }}</span>
                <span class="stat-label">Total Registros</span>
              </div>
              <div v-if="reportConfig.type === 'complaints'" class="stat-item">
                <span class="stat-value">{{ getStatValue('criticas') }}</span>
                <span class="stat-label">Casos Criticos</span>
              </div>
              <div v-if="reportConfig.type === 'adoptions'" class="stat-item">
                <span class="stat-value">{{ getStatValue('completadas') }}</span>
                <span class="stat-label">Completadas</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reportes Guardados -->
    <div class="saved-reports">
      <h3 class="h5-tipografia-govco">Reportes Guardados</h3>
      <div class="saved-reports-list">
        <div
          v-for="saved in savedReports"
          :key="saved.id"
          class="saved-report-item"
        >
          <div class="saved-info">
            <span class="saved-name">{{ saved.name }}</span>
            <span class="saved-date">{{ saved.createdAt }}</span>
          </div>
          <div class="saved-actions">
            <button type="button" class="action-btn" @click="loadSavedReport(saved)">
              Cargar
            </button>
            <button type="button" class="action-btn delete" @click="deleteSavedReport(saved.id)">
              Eliminar
            </button>
          </div>
        </div>
        <p v-if="savedReports.length === 0" class="no-saved">
          No hay reportes guardados
        </p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const loading = ref(false);
const showPreview = ref(false);
const currentPage = ref(1);
const pageSize = 10;
const showStats = ref(true);

// Configuracion del reporte
const reportConfig = ref({
  type: '',
  startDate: '',
  endDate: '',
  filters: {
    species: '',
    status: '',
    complaintType: '',
    priority: '',
    adoptionStatus: '',
    vaccineType: ''
  },
  columns: [],
  groupBy: ''
});

// Columnas disponibles segun tipo
const columnsByType = {
  animals: [
    { id: 'id', label: 'ID' },
    { id: 'nombre', label: 'Nombre' },
    { id: 'especie', label: 'Especie' },
    { id: 'raza', label: 'Raza' },
    { id: 'edad', label: 'Edad' },
    { id: 'estado', label: 'Estado' },
    { id: 'fecha_registro', label: 'Fecha Registro' },
    { id: 'ubicacion', label: 'Ubicacion' }
  ],
  adoptions: [
    { id: 'id', label: 'ID' },
    { id: 'animal', label: 'Animal' },
    { id: 'adoptante', label: 'Adoptante' },
    { id: 'fecha_solicitud', label: 'Fecha Solicitud' },
    { id: 'fecha_adopcion', label: 'Fecha Adopcion' },
    { id: 'estado', label: 'Estado' },
    { id: 'seguimiento', label: 'Seguimiento' }
  ],
  complaints: [
    { id: 'id', label: 'ID' },
    { id: 'tipo', label: 'Tipo' },
    { id: 'fecha', label: 'Fecha' },
    { id: 'ubicacion', label: 'Ubicacion' },
    { id: 'estado', label: 'Estado' },
    { id: 'prioridad', label: 'Prioridad' },
    { id: 'asignado', label: 'Asignado a' }
  ],
  vaccinations: [
    { id: 'id', label: 'ID' },
    { id: 'animal', label: 'Animal' },
    { id: 'vacuna', label: 'Vacuna' },
    { id: 'fecha', label: 'Fecha' },
    { id: 'veterinario', label: 'Veterinario' },
    { id: 'proxima', label: 'Proxima Dosis' }
  ],
  rescues: [
    { id: 'id', label: 'ID' },
    { id: 'fecha', label: 'Fecha' },
    { id: 'ubicacion', label: 'Ubicacion' },
    { id: 'animales', label: 'Animales' },
    { id: 'estado', label: 'Estado' },
    { id: 'responsable', label: 'Responsable' }
  ],
  users: [
    { id: 'id', label: 'ID' },
    { id: 'nombre', label: 'Nombre' },
    { id: 'email', label: 'Email' },
    { id: 'rol', label: 'Rol' },
    { id: 'estado', label: 'Estado' },
    { id: 'ultimo_acceso', label: 'Ultimo Acceso' }
  ]
};

// Datos de vista previa (simulados)
const previewData = ref([]);

// Reportes guardados
const savedReports = ref([
  {
    id: 1,
    name: 'Adopciones Q4 2024',
    type: 'adoptions',
    createdAt: '2024-11-15'
  },
  {
    id: 2,
    name: 'Denuncias Criticas',
    type: 'complaints',
    createdAt: '2024-11-20'
  }
]);

// Computed
const availableColumns = computed(() => {
  return columnsByType[reportConfig.value.type] || [];
});

const selectedColumnLabels = computed(() => {
  const cols = columnsByType[reportConfig.value.type] || [];
  return reportConfig.value.columns.map(colId => {
    const col = cols.find(c => c.id === colId);
    return col ? col.label : colId;
  });
});

const isConfigValid = computed(() => {
  return reportConfig.value.type &&
         reportConfig.value.startDate &&
         reportConfig.value.endDate &&
         reportConfig.value.columns.length > 0;
});

const totalPages = computed(() => {
  return Math.ceil(previewData.value.length / pageSize);
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  const end = start + pageSize;
  return previewData.value.slice(start, end);
});

// Watch para seleccionar columnas por defecto
watch(() => reportConfig.value.type, (newType) => {
  if (newType && columnsByType[newType]) {
    // Seleccionar primeras 5 columnas por defecto
    reportConfig.value.columns = columnsByType[newType].slice(0, 5).map(c => c.id);
  }
  // Reset filtros
  reportConfig.value.filters = {
    species: '',
    status: '',
    complaintType: '',
    priority: '',
    adoptionStatus: '',
    vaccineType: ''
  };
  showPreview.value = false;
});

// Methods
function getReportTitle() {
  const titles = {
    animals: 'Reporte de Animales Registrados',
    adoptions: 'Reporte de Adopciones',
    complaints: 'Reporte de Denuncias',
    vaccinations: 'Reporte de Vacunaciones',
    rescues: 'Reporte de Operativos de Rescate',
    users: 'Reporte de Usuarios'
  };
  return titles[reportConfig.value.type] || 'Reporte';
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('es-CO');
}

function generatePreview() {
  loading.value = true;
  showPreview.value = false;

  // Simular carga
  setTimeout(() => {
    previewData.value = generateMockData();
    loading.value = false;
    showPreview.value = true;
    currentPage.value = 1;
  }, 1500);
}

function generateMockData() {
  // Generar datos de ejemplo segun tipo
  const mockGenerators = {
    animals: () => Array.from({ length: 25 }, (_, i) => ({
      id: `AN-${1000 + i}`,
      nombre: ['Luna', 'Max', 'Bella', 'Rocky', 'Milo'][i % 5],
      especie: ['Perro', 'Gato', 'Perro', 'Gato', 'Equino'][i % 5],
      raza: ['Mestizo', 'Criollo', 'Labrador', 'Siames', 'Criollo'][i % 5],
      edad: `${(i % 10) + 1} anos`,
      estado: ['Disponible', 'Adoptado', 'En tratamiento'][i % 3],
      fecha_registro: '2024-11-' + String((i % 28) + 1).padStart(2, '0'),
      ubicacion: ['Sede Norte', 'Sede Sur', 'Sede Centro'][i % 3]
    })),
    adoptions: () => Array.from({ length: 18 }, (_, i) => ({
      id: `AD-${2000 + i}`,
      animal: ['Luna', 'Max', 'Bella'][i % 3],
      adoptante: ['Juan Perez', 'Maria Lopez', 'Carlos Ruiz'][i % 3],
      fecha_solicitud: '2024-11-' + String((i % 20) + 1).padStart(2, '0'),
      fecha_adopcion: i % 2 === 0 ? '2024-11-' + String((i % 20) + 5).padStart(2, '0') : '-',
      estado: ['Completada', 'En proceso', 'Cancelada'][i % 3],
      seguimiento: i % 2 === 0 ? 'Realizado' : 'Pendiente'
    })),
    complaints: () => Array.from({ length: 30 }, (_, i) => ({
      id: `DN-${3000 + i}`,
      tipo: ['Maltrato', 'Abandono', 'Negligencia'][i % 3],
      fecha: '2024-11-' + String((i % 28) + 1).padStart(2, '0'),
      ubicacion: ['Calle 10 #5-20', 'Av. Principal', 'Barrio Centro'][i % 3],
      estado: ['Pendiente', 'En proceso', 'Resuelto'][i % 3],
      prioridad: ['Critica', 'Alta', 'Media', 'Baja'][i % 4],
      asignado: ['Inspector A', 'Inspector B', 'Sin asignar'][i % 3]
    })),
    vaccinations: () => Array.from({ length: 20 }, (_, i) => ({
      id: `VC-${4000 + i}`,
      animal: ['Luna', 'Max', 'Bella', 'Rocky'][i % 4],
      vacuna: ['Rabia', 'Triple', 'Parvovirus'][i % 3],
      fecha: '2024-11-' + String((i % 28) + 1).padStart(2, '0'),
      veterinario: ['Dr. Garcia', 'Dra. Martinez'][i % 2],
      proxima: '2025-05-' + String((i % 28) + 1).padStart(2, '0')
    })),
    rescues: () => Array.from({ length: 12 }, (_, i) => ({
      id: `RS-${5000 + i}`,
      fecha: '2024-11-' + String((i % 28) + 1).padStart(2, '0'),
      ubicacion: ['Zona Rural', 'Centro Urbano', 'Periferia'][i % 3],
      animales: `${(i % 5) + 1}`,
      estado: ['Completado', 'En curso'][i % 2],
      responsable: ['Equipo A', 'Equipo B'][i % 2]
    })),
    users: () => Array.from({ length: 15 }, (_, i) => ({
      id: `US-${6000 + i}`,
      nombre: ['Admin Sistema', 'Juan Inspector', 'Maria Veterinaria'][i % 3],
      email: ['admin@gov.co', 'jinspector@gov.co', 'mvet@gov.co'][i % 3],
      rol: ['Administrador', 'Inspector', 'Veterinario'][i % 3],
      estado: ['Activo', 'Activo', 'Inactivo'][i % 3],
      ultimo_acceso: '2024-11-' + String((i % 10) + 15).padStart(2, '0')
    }))
  };

  return mockGenerators[reportConfig.value.type]?.() || [];
}

function getStatValue(stat) {
  if (stat === 'criticas') {
    return previewData.value.filter(r => r.prioridad === 'Critica').length;
  }
  if (stat === 'completadas') {
    return previewData.value.filter(r => r.estado === 'Completada').length;
  }
  return 0;
}

function exportReport(format) {
  // Simular exportacion
  const formatNames = {
    pdf: 'PDF',
    xlsx: 'Excel',
    csv: 'CSV'
  };
  alert(`Exportando reporte en formato ${formatNames[format]}...\nArchivo: ${getReportTitle().replace(/ /g, '_')}.${format}`);
}

function resetConfig() {
  reportConfig.value = {
    type: '',
    startDate: '',
    endDate: '',
    filters: {
      species: '',
      status: '',
      complaintType: '',
      priority: '',
      adoptionStatus: '',
      vaccineType: ''
    },
    columns: [],
    groupBy: ''
  };
  showPreview.value = false;
  previewData.value = [];
}

function loadSavedReport(saved) {
  reportConfig.value.type = saved.type;
  // Simular carga de configuracion guardada
  alert(`Cargando reporte: ${saved.name}`);
}

function deleteSavedReport(id) {
  if (confirm('Eliminar este reporte guardado?')) {
    savedReports.value = savedReports.value.filter(r => r.id !== id);
  }
}
</script>

<style scoped>
.custom-reports {
  max-width: 1400px;
  margin: 0 auto;
  padding: 1.5rem;
}

.reports-header {
  margin-bottom: 1.5rem;
}

.reports-header h2 {
  margin: 0 0 0.25rem 0;
  color: #004884;
}

.reports-header p {
  margin: 0;
  color: #666;
}

/* Layout */
.reports-layout {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

/* Config Panel */
.config-panel {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  height: fit-content;
}

.config-panel h3 {
  margin: 0 0 1.5rem 0;
  color: #004884;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #E0E0E0;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
  font-size: 0.9rem;
}

.form-select,
.form-input {
  width: 100%;
  padding: 0.65rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 0.9rem;
  box-sizing: border-box;
}

.form-select:focus,
.form-input:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 2px rgba(51, 102, 204, 0.1);
}

.date-range {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.date-range .form-input {
  flex: 1;
}

.date-range span {
  color: #666;
}

.dynamic-filters {
  background: #f9f9f9;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.columns-selection {
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #E0E0E0;
  border-radius: 4px;
  padding: 0.75rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.35rem 0;
  cursor: pointer;
  font-size: 0.9rem;
}

.checkbox-label:hover {
  background: #f5f5f5;
}

.action-buttons {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid #E0E0E0;
}

.btn-preview {
  flex: 1;
  padding: 0.75rem 1rem;
  background: #004884;
  color: white;
  border: none;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-preview:hover:not(:disabled) {
  background: #003366;
}

.btn-preview:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.btn-reset {
  padding: 0.75rem 1rem;
  background: white;
  color: #666;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-reset:hover {
  background: #f5f5f5;
  border-color: #999;
}

/* Preview Panel */
.preview-panel {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  min-height: 400px;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 400px;
  text-align: center;
  color: #666;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.empty-state h4 {
  margin: 0 0 0.5rem 0;
  color: #004884;
}

.empty-state p {
  max-width: 300px;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 400px;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #E0E0E0;
  border-top-color: #004884;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.report-preview {
  padding: 1.5rem;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #E0E0E0;
  flex-wrap: wrap;
  gap: 1rem;
}

.preview-info h4 {
  margin: 0 0 0.25rem 0;
  color: #004884;
}

.preview-info p {
  margin: 0;
  font-size: 0.9rem;
  color: #666;
}

.record-count {
  font-weight: 600;
  color: #068460 !important;
}

.export-buttons {
  display: flex;
  gap: 0.5rem;
}

.export-btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
}

.export-btn.pdf {
  background: #FFEBEE;
  color: #A80521;
}

.export-btn.pdf:hover {
  background: #A80521;
  color: white;
}

.export-btn.excel {
  background: #E8F5E9;
  color: #2E7D32;
}

.export-btn.excel:hover {
  background: #2E7D32;
  color: white;
}

.export-btn.csv {
  background: #E8F0FE;
  color: #3366CC;
}

.export-btn.csv:hover {
  background: #3366CC;
  color: white;
}

/* Table */
.preview-table-container {
  overflow-x: auto;
  margin-bottom: 1rem;
}

.preview-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.preview-table th,
.preview-table td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #E0E0E0;
}

.preview-table th {
  background: #f5f7fb;
  font-weight: 600;
  color: #004884;
  white-space: nowrap;
}

.preview-table tbody tr:hover {
  background: #f9f9f9;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-top: 1px solid #E0E0E0;
}

.page-btn {
  padding: 0.5rem 1rem;
  background: #E8F0FE;
  color: #3366CC;
  border: 1px solid #3366CC;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.page-btn:hover:not(:disabled) {
  background: #3366CC;
  color: white;
}

.page-btn:disabled {
  background: #f0f0f0;
  color: #999;
  border-color: #ddd;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.9rem;
  color: #666;
}

/* Stats Summary */
.stats-summary {
  background: #f5f7fb;
  border-radius: 8px;
  padding: 1rem;
  margin-top: 1rem;
}

.stats-summary h5 {
  margin: 0 0 1rem 0;
  color: #004884;
}

.stats-grid {
  display: flex;
  gap: 2rem;
}

.stat-item {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: bold;
  color: #004884;
}

.stat-label {
  font-size: 0.8rem;
  color: #666;
}

/* Saved Reports */
.saved-reports {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.saved-reports h3 {
  margin: 0 0 1rem 0;
  color: #004884;
}

.saved-reports-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.saved-report-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f9f9f9;
  border-radius: 8px;
}

.saved-info {
  display: flex;
  flex-direction: column;
}

.saved-name {
  font-weight: 600;
  color: #333;
}

.saved-date {
  font-size: 0.85rem;
  color: #666;
}

.saved-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  padding: 0.4rem 0.8rem;
  background: #E8F0FE;
  color: #3366CC;
  border: 1px solid #3366CC;
  border-radius: 4px;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn:hover {
  background: #3366CC;
  color: white;
}

.action-btn.delete {
  background: #FFEBEE;
  color: #A80521;
  border-color: #A80521;
}

.action-btn.delete:hover {
  background: #A80521;
  color: white;
}

.no-saved {
  color: #666;
  text-align: center;
  padding: 1rem;
}

/* Responsive */
@media (max-width: 992px) {
  .reports-layout {
    grid-template-columns: 1fr;
  }

  .config-panel {
    order: 1;
  }

  .preview-panel {
    order: 2;
  }
}

@media (max-width: 768px) {
  .custom-reports {
    padding: 1rem;
  }

  .preview-header {
    flex-direction: column;
  }

  .export-buttons {
    width: 100%;
    justify-content: flex-start;
  }

  .stats-grid {
    flex-direction: column;
    gap: 1rem;
  }
}
</style>
