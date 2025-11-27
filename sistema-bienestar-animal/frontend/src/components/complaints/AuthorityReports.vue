<!-- src/components/complaints/AuthorityReports.vue -->
<!-- HU-019: Generar Reporte para Autoridades -->
<template>
  <section class="authority-reports">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Reportes para Autoridades</h2>
      <p class="text2-tipografia-govco">
        Genere reportes formales de casos de maltrato para envío a Fiscalía, Policía u otras autoridades competentes.
      </p>
    </div>

    <!-- TABS: Generar nuevo / Historial -->
    <div class="report-tabs">
      <button
        type="button"
        class="report-tab"
        :class="{ active: activeTab === 'generate' }"
        @click="activeTab = 'generate'"
      >
        Generar nuevo reporte
      </button>
      <button
        type="button"
        class="report-tab"
        :class="{ active: activeTab === 'history' }"
        @click="activeTab = 'history'"
      >
        Historial de envíos
      </button>
    </div>

    <!-- GENERAR NUEVO REPORTE -->
    <div v-if="activeTab === 'generate'" class="report-content">
      <!-- Selección de caso -->
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Seleccionar caso</h3>

        <div class="case-selector">
          <div class="entradas-de-texto-govco search-field">
            <label for="searchCase">Buscar por número de caso</label>
            <div class="search-input-group">
              <input
                type="text"
                id="searchCase"
                v-model="searchQuery"
                placeholder="DEN-XXXX-XXXX"
                @keyup.enter="searchCase"
              />
              <button type="button" class="search-btn" @click="searchCase">
                Buscar
              </button>
            </div>
          </div>

          <!-- Casos elegibles -->
          <div v-if="eligibleCases.length > 0" class="eligible-cases">
            <p class="cases-label">Casos cerrados disponibles para reporte:</p>
            <div class="cases-list">
              <div
                v-for="caso in eligibleCases"
                :key="caso.id"
                class="case-item"
                :class="{ selected: selectedCase?.id === caso.id }"
                @click="selectCase(caso)"
              >
                <div class="case-main">
                  <span class="case-number">{{ caso.caso_numero }}</span>
                  <span class="case-type">{{ getComplaintTypeLabel(caso.tipo_denuncia) }}</span>
                </div>
                <div class="case-meta">
                  <span>{{ formatDate(caso.fecha_cierre) }}</span>
                  <span class="result-badge" :class="`result-${caso.resultado}`">
                    {{ getResultLabel(caso.resultado) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Formulario de reporte -->
      <div v-if="selectedCase" class="form-section">
        <h3 class="h5-tipografia-govco section-title">Información del reporte</h3>

        <div class="form-grid">
          <!-- Autoridad destino -->
          <div class="input-like-govco">
            <label for="authority" class="label-desplegable-govco">
              Autoridad destino<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic">
              <select id="authority" v-model="reportForm.authority">
                <option disabled value="">Seleccionar autoridad</option>
                <option value="fiscalia">Fiscalía General de la Nación</option>
                <option value="policia">Policía Nacional - CAIA</option>
                <option value="procuraduria">Procuraduría General</option>
                <option value="personeria">Personería Municipal</option>
                <option value="otra">Otra autoridad</option>
              </select>
            </div>
            <span v-if="errors.authority" class="alert-desplegable-govco">{{ errors.authority }}</span>
          </div>

          <!-- Nombre de autoridad (si es otra) -->
          <div v-if="reportForm.authority === 'otra'" class="entradas-de-texto-govco">
            <label for="otherAuthority">Especifique la autoridad<span aria-required="true">*</span></label>
            <input
              type="text"
              id="otherAuthority"
              v-model="reportForm.otherAuthority"
              placeholder="Nombre de la autoridad"
            />
          </div>

          <!-- Tipo de reporte -->
          <div class="input-like-govco">
            <label for="reportType" class="label-desplegable-govco">
              Tipo de reporte<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic">
              <select id="reportType" v-model="reportForm.reportType">
                <option disabled value="">Seleccionar tipo</option>
                <option value="denuncia_formal">Denuncia formal</option>
                <option value="informe_tecnico">Informe técnico</option>
                <option value="solicitud_investigacion">Solicitud de investigación</option>
                <option value="remision_competencia">Remisión por competencia</option>
              </select>
            </div>
          </div>

          <!-- Funcionario responsable -->
          <div class="input-like-govco">
            <label for="responsible" class="label-desplegable-govco">
              Funcionario responsable<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic">
              <select id="responsible" v-model="reportForm.responsible">
                <option disabled value="">Seleccionar funcionario</option>
                <option v-for="func in officials" :key="func.id" :value="func.id">
                  {{ func.nombre }} - {{ func.cargo }}
                </option>
              </select>
            </div>
          </div>

          <!-- Resumen ejecutivo -->
          <div class="entradas-de-texto-govco full-width">
            <label for="summary">Resumen ejecutivo<span aria-required="true">*</span></label>
            <textarea
              id="summary"
              v-model="reportForm.summary"
              rows="4"
              placeholder="Resumen de los hechos relevantes del caso..."
            ></textarea>
            <span class="info-entradas-de-texto-govco">Este resumen aparecerá en la primera página del reporte.</span>
          </div>

          <!-- Solicitud a la autoridad -->
          <div class="entradas-de-texto-govco full-width">
            <label for="request">Solicitud a la autoridad<span aria-required="true">*</span></label>
            <textarea
              id="request"
              v-model="reportForm.request"
              rows="3"
              placeholder="Describa la acción que se solicita a la autoridad competente..."
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Contenido del reporte -->
      <div v-if="selectedCase" class="form-section">
        <h3 class="h5-tipografia-govco section-title">Contenido a incluir</h3>

        <div class="content-options">
          <div class="checkbox-govco">
            <input type="checkbox" id="includeTimeline" v-model="reportForm.includeTimeline" />
            <label for="includeTimeline">Línea de tiempo del caso</label>
          </div>
          <div class="checkbox-govco">
            <input type="checkbox" id="includeEvidence" v-model="reportForm.includeEvidence" />
            <label for="includeEvidence">Fotografías y videos evidencia ({{ selectedCase.evidencias?.length || 0 }} archivos)</label>
          </div>
          <div class="checkbox-govco">
            <input type="checkbox" id="includeVetReport" v-model="reportForm.includeVetReport" />
            <label for="includeVetReport">Informe veterinario (si existe)</label>
          </div>
          <div class="checkbox-govco">
            <input type="checkbox" id="includeWitness" v-model="reportForm.includeWitness" />
            <label for="includeWitness">Declaraciones de testigos</label>
          </div>
          <div class="checkbox-govco">
            <input type="checkbox" id="includeResponsible" v-model="reportForm.includeResponsible" />
            <label for="includeResponsible">Información del presunto responsable</label>
          </div>
        </div>
      </div>

      <!-- Vista previa del reporte -->
      <div v-if="selectedCase" class="form-section preview-section">
        <h3 class="h5-tipografia-govco section-title">Vista previa del reporte</h3>

        <div class="report-preview">
          <div class="preview-header">
            <div class="preview-logo">
              <span class="gov-logo">🏛️</span>
              <div class="gov-text">
                <span class="gov-title">ALCALDÍA MUNICIPAL</span>
                <span class="gov-subtitle">Secretaría de Bienestar Animal</span>
              </div>
            </div>
            <div class="preview-doc-info">
              <span class="doc-type">{{ getReportTypeLabel(reportForm.reportType) }}</span>
              <span class="doc-date">Fecha: {{ formatDate(new Date()) }}</span>
            </div>
          </div>

          <div class="preview-body">
            <div class="preview-section">
              <h4>DATOS DEL CASO</h4>
              <table class="preview-table">
                <tr>
                  <td><strong>Número de caso:</strong></td>
                  <td>{{ selectedCase.caso_numero }}</td>
                </tr>
                <tr>
                  <td><strong>Tipo de denuncia:</strong></td>
                  <td>{{ getComplaintTypeLabel(selectedCase.tipo_denuncia) }}</td>
                </tr>
                <tr>
                  <td><strong>Fecha de recepción:</strong></td>
                  <td>{{ formatDate(selectedCase.fecha_recepcion) }}</td>
                </tr>
                <tr>
                  <td><strong>Fecha de cierre:</strong></td>
                  <td>{{ formatDate(selectedCase.fecha_cierre) }}</td>
                </tr>
                <tr>
                  <td><strong>Resultado:</strong></td>
                  <td>{{ getResultLabel(selectedCase.resultado) }}</td>
                </tr>
              </table>
            </div>

            <div class="preview-section">
              <h4>RESUMEN EJECUTIVO</h4>
              <p>{{ reportForm.summary || 'No especificado' }}</p>
            </div>

            <div class="preview-section">
              <h4>SOLICITUD</h4>
              <p>{{ reportForm.request || 'No especificada' }}</p>
            </div>

            <div v-if="reportForm.includeTimeline" class="preview-section">
              <h4>LÍNEA DE TIEMPO</h4>
              <p class="preview-placeholder">[Se incluirá la línea de tiempo completa del caso]</p>
            </div>

            <div v-if="reportForm.includeEvidence" class="preview-section">
              <h4>EVIDENCIAS ADJUNTAS</h4>
              <p class="preview-placeholder">[Se incluirán {{ selectedCase.evidencias?.length || 0 }} archivos de evidencia]</p>
            </div>
          </div>

          <div class="preview-footer">
            <div class="signature-block">
              <div class="signature-line"></div>
              <span class="signature-name">{{ getOfficialName(reportForm.responsible) }}</span>
              <span class="signature-title">{{ getOfficialTitle(reportForm.responsible) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Botones de acción -->
      <div v-if="selectedCase" class="form-actions">
        <button type="button" class="govco-btn govco-bg-concrete" @click="resetForm">
          Cancelar
        </button>
        <button type="button" class="govco-btn govco-bg-marine" @click="previewPDF">
          Vista previa PDF
        </button>
        <button
          type="button"
          class="govco-btn govco-bg-elf-green"
          :disabled="isGenerating"
          @click="generateReport"
        >
          {{ isGenerating ? 'Generando...' : 'Generar y enviar' }}
        </button>
      </div>
    </div>

    <!-- HISTORIAL DE ENVÍOS -->
    <div v-if="activeTab === 'history'" class="report-content">
      <div class="form-section">
        <div class="history-filters">
          <div class="entradas-de-texto-govco">
            <label for="filterDate">Fecha</label>
            <input type="month" id="filterDate" v-model="historyFilter.date" />
          </div>
          <div class="input-like-govco">
            <label for="filterAuthority" class="label-desplegable-govco">Autoridad</label>
            <div class="desplegable-govco" data-type="basic">
              <select id="filterAuthority" v-model="historyFilter.authority">
                <option value="">Todas</option>
                <option value="fiscalia">Fiscalía</option>
                <option value="policia">Policía</option>
                <option value="procuraduria">Procuraduría</option>
              </select>
            </div>
          </div>
        </div>

        <div class="history-table-container">
          <table class="history-table">
            <thead>
              <tr>
                <th>Fecha envío</th>
                <th>Caso</th>
                <th>Autoridad</th>
                <th>Tipo</th>
                <th>Responsable</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="report in sentReports" :key="report.id">
                <td>{{ formatDate(report.fecha_envio) }}</td>
                <td class="cell-case">{{ report.caso_numero }}</td>
                <td>{{ getAuthorityLabel(report.autoridad) }}</td>
                <td>{{ getReportTypeLabel(report.tipo) }}</td>
                <td>{{ report.responsable_nombre }}</td>
                <td>
                  <span class="send-status" :class="`status-${report.estado}`">
                    {{ getSendStatusLabel(report.estado) }}
                  </span>
                </td>
                <td class="actions-cell">
                  <button type="button" class="table-btn" @click="downloadReport(report)">
                    Descargar
                  </button>
                  <button type="button" class="table-btn" @click="viewAudit(report)">
                    Auditoría
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, reactive } from 'vue';

const activeTab = ref('generate');
const searchQuery = ref('');
const selectedCase = ref(null);
const isGenerating = ref(false);

const reportForm = reactive({
  authority: '',
  otherAuthority: '',
  reportType: '',
  responsible: '',
  summary: '',
  request: '',
  includeTimeline: true,
  includeEvidence: true,
  includeVetReport: false,
  includeWitness: false,
  includeResponsible: false
});

const errors = reactive({
  authority: ''
});

const historyFilter = reactive({
  date: '',
  authority: ''
});

// Mock data
const eligibleCases = ref([
  {
    id: 1,
    caso_numero: 'DEN-202411-0002',
    tipo_denuncia: 'abandono',
    fecha_recepcion: '2024-11-10T08:15:00',
    fecha_cierre: '2024-11-11T15:00:00',
    resultado: 'rescatado',
    evidencias: ['foto1.jpg', 'foto2.jpg', 'video1.mp4']
  },
  {
    id: 2,
    caso_numero: 'DEN-202410-0089',
    tipo_denuncia: 'maltrato_fisico',
    fecha_recepcion: '2024-10-28T10:00:00',
    fecha_cierre: '2024-10-30T16:00:00',
    resultado: 'derivado',
    evidencias: ['foto1.jpg', 'foto2.jpg']
  }
]);

const officials = ref([
  { id: 1, nombre: 'Dr. Carlos Martínez', cargo: 'Director de Bienestar Animal' },
  { id: 2, nombre: 'Dra. Ana García', cargo: 'Coordinadora de Rescate' },
  { id: 3, nombre: 'Ing. Pedro López', cargo: 'Jefe de Operaciones' }
]);

const sentReports = ref([
  {
    id: 1,
    fecha_envio: '2024-11-10T14:00:00',
    caso_numero: 'DEN-202410-0078',
    autoridad: 'fiscalia',
    tipo: 'denuncia_formal',
    responsable_nombre: 'Dr. Carlos Martínez',
    estado: 'enviado'
  },
  {
    id: 2,
    fecha_envio: '2024-11-05T09:30:00',
    caso_numero: 'DEN-202410-0065',
    autoridad: 'policia',
    tipo: 'informe_tecnico',
    responsable_nombre: 'Dra. Ana García',
    estado: 'recibido'
  }
]);

// Funciones
function searchCase() {
  console.log('Buscando caso:', searchQuery.value);
  // Filtrar casos elegibles
}

function selectCase(caso) {
  selectedCase.value = caso;
  // Pre-llenar resumen con datos del caso
  reportForm.summary = `El presente caso corresponde a una denuncia por ${getComplaintTypeLabel(caso.tipo_denuncia).toLowerCase()} recibida el ${formatDate(caso.fecha_recepcion)}, la cual fue atendida y cerrada con resultado: ${getResultLabel(caso.resultado).toLowerCase()}.`;
}

function previewPDF() {
  console.log('Generando vista previa PDF');
  alert('Se abrirá una vista previa del PDF en una nueva ventana.');
}

async function generateReport() {
  // Validar
  if (!reportForm.authority) {
    errors.authority = 'Seleccione una autoridad destino';
    return;
  }

  isGenerating.value = true;

  try {
    // Simular generación
    await new Promise(resolve => setTimeout(resolve, 2000));

    alert(`Reporte generado exitosamente.\n\nSe ha registrado el envío a ${getAuthorityLabel(reportForm.authority)} y se ha creado el evento de auditoría correspondiente.`);

    resetForm();
    activeTab.value = 'history';

  } catch (error) {
    console.error('Error al generar reporte:', error);
    alert('Error al generar el reporte. Intente nuevamente.');
  } finally {
    isGenerating.value = false;
  }
}

function resetForm() {
  selectedCase.value = null;
  Object.keys(reportForm).forEach(key => {
    if (typeof reportForm[key] === 'boolean') {
      reportForm[key] = key === 'includeTimeline' || key === 'includeEvidence';
    } else {
      reportForm[key] = '';
    }
  });
}

function downloadReport(report) {
  console.log('Descargando reporte:', report.id);
  alert('Se descargará el archivo PDF del reporte.');
}

function viewAudit(report) {
  console.log('Ver auditoría:', report.id);
  alert('Se mostrará el registro de auditoría del envío.');
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

function getResultLabel(result) {
  const labels = {
    rescatado: 'Rescatado',
    no_encontrado: 'No encontrado',
    derivado: 'Derivado a autoridades',
    sin_merito: 'Sin mérito'
  };
  return labels[result] || result;
}

function getAuthorityLabel(auth) {
  const labels = {
    fiscalia: 'Fiscalía General',
    policia: 'Policía Nacional',
    procuraduria: 'Procuraduría',
    personeria: 'Personería',
    otra: 'Otra'
  };
  return labels[auth] || auth;
}

function getReportTypeLabel(type) {
  const labels = {
    denuncia_formal: 'Denuncia formal',
    informe_tecnico: 'Informe técnico',
    solicitud_investigacion: 'Solicitud de investigación',
    remision_competencia: 'Remisión por competencia'
  };
  return labels[type] || type || 'REPORTE OFICIAL';
}

function getSendStatusLabel(status) {
  const labels = {
    enviado: 'Enviado',
    recibido: 'Recibido',
    en_proceso: 'En proceso'
  };
  return labels[status] || status;
}

function getOfficialName(id) {
  const official = officials.value.find(o => o.id === id);
  return official?.nombre || '_________________';
}

function getOfficialTitle(id) {
  const official = officials.value.find(o => o.id === id);
  return official?.cargo || 'Funcionario responsable';
}

function formatDate(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}
</script>

<style scoped>
.authority-reports {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  background: #f5f7fb;
}

.form-header {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #004884;
}

.form-header h2 {
  color: #004884;
}

/* Tabs */
.report-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.report-tab {
  flex: 1;
  padding: 1rem;
  background: white;
  border: 2px solid #E0E0E0;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  color: #666;
  transition: all 0.2s;
}

.report-tab:hover {
  border-color: #3366CC;
}

.report-tab.active {
  background: #3366CC;
  border-color: #3366CC;
  color: white;
}

/* Secciones */
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

/* Selector de caso */
.case-selector {
  padding: 1.5rem;
}

.search-field {
  max-width: 400px;
}

.search-input-group {
  display: flex;
  gap: 0.5rem;
}

.search-input-group input {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
}

.search-btn {
  padding: 0.75rem 1.5rem;
  background: #3366CC;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
}

.eligible-cases {
  margin-top: 1.5rem;
}

.cases-label {
  font-size: 0.9rem;
  color: #666;
  margin-bottom: 0.75rem;
}

.cases-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.case-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.case-item:hover {
  border-color: #3366CC;
  background: #f9f9f9;
}

.case-item.selected {
  border-color: #3366CC;
  background: #E8F0FE;
}

.case-main {
  display: flex;
  flex-direction: column;
}

.case-number {
  font-weight: 600;
  color: #004884;
}

.case-type {
  font-size: 0.9rem;
  color: #666;
}

.case-meta {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 0.85rem;
  color: #666;
}

.result-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.result-rescatado { background: #E8F5E9; color: #2E7D32; }
.result-derivado { background: #FFF8E1; color: #F57C00; }
.result-no_encontrado { background: #FFEBEE; color: #C62828; }

/* Form grid */
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
  padding: 1.5rem;
}

.full-width {
  grid-column: 1 / 3;
}

.input-like-govco {
  display: flex;
  flex-direction: column;
}

.input-like-govco label,
.entradas-de-texto-govco label {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.desplegable-govco select,
.entradas-de-texto-govco input,
.entradas-de-texto-govco textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
}

.info-entradas-de-texto-govco {
  font-size: 0.85rem;
  color: #666;
  margin-top: 0.25rem;
}

/* Opciones de contenido */
.content-options {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  padding: 1.5rem;
}

.checkbox-govco {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: #f9f9f9;
  border-radius: 4px;
}

.checkbox-govco input {
  width: 18px;
  height: 18px;
}

/* Vista previa */
.preview-section {
  padding: 0;
}

.report-preview {
  margin: 1.5rem;
  border: 2px solid #E0E0E0;
  border-radius: 8px;
  overflow: hidden;
  background: white;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.5rem;
  background: #f9f9f9;
  border-bottom: 2px solid #004884;
}

.preview-logo {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.gov-logo {
  font-size: 2.5rem;
}

.gov-text {
  display: flex;
  flex-direction: column;
}

.gov-title {
  font-weight: bold;
  color: #004884;
}

.gov-subtitle {
  font-size: 0.9rem;
  color: #666;
}

.preview-doc-info {
  text-align: right;
}

.doc-type {
  display: block;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.25rem;
}

.doc-date {
  font-size: 0.85rem;
  color: #666;
}

.preview-body {
  padding: 1.5rem;
}

.preview-section {
  margin-bottom: 1.5rem;
}

.preview-section h4 {
  margin: 0 0 0.75rem 0;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #E0E0E0;
  color: #004884;
  font-size: 0.9rem;
}

.preview-table {
  width: 100%;
}

.preview-table td {
  padding: 0.5rem 0;
  font-size: 0.9rem;
}

.preview-table td:first-child {
  width: 180px;
  color: #666;
}

.preview-placeholder {
  font-style: italic;
  color: #999;
}

.preview-footer {
  padding: 2rem 1.5rem;
  border-top: 1px solid #E0E0E0;
  display: flex;
  justify-content: flex-end;
}

.signature-block {
  text-align: center;
  min-width: 250px;
}

.signature-line {
  border-bottom: 1px solid #333;
  margin-bottom: 0.5rem;
  height: 40px;
}

.signature-name {
  display: block;
  font-weight: 600;
}

.signature-title {
  display: block;
  font-size: 0.85rem;
  color: #666;
}

/* Botones */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.govco-btn {
  padding: 0.75rem 2rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  color: white;
}

.govco-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.govco-bg-concrete { background: #737373; }
.govco-bg-marine { background: #3366CC; }
.govco-bg-elf-green { background: #068460; }

/* Historial */
.history-filters {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  border-bottom: 1px solid #E0E0E0;
}

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
  font-size: 0.85rem;
}

.cell-case {
  font-weight: 600;
  color: #004884;
}

.send-status {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 600;
}

.status-enviado { background: #FFF8E1; color: #F57C00; }
.status-recibido { background: #E8F5E9; color: #2E7D32; }
.status-en_proceso { background: #E8F0FE; color: #3366CC; }

.actions-cell {
  display: flex;
  gap: 0.5rem;
}

.table-btn {
  padding: 0.25rem 0.75rem;
  background: #E8F0FE;
  color: #3366CC;
  border: 1px solid #3366CC;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8rem;
}

/* Responsive */
@media (max-width: 992px) {
  .form-grid,
  .content-options {
    grid-template-columns: 1fr;
  }

  .full-width {
    grid-column: 1;
  }
}

@media (max-width: 768px) {
  .report-tabs {
    flex-direction: column;
  }

  .preview-header {
    flex-direction: column;
    gap: 1rem;
  }

  .preview-doc-info {
    text-align: left;
  }
}

@media (max-width: 576px) {
  .authority-reports {
    padding: 1rem;
  }

  .form-actions {
    flex-direction: column;
  }

  .govco-btn {
    width: 100%;
  }
}
</style>
