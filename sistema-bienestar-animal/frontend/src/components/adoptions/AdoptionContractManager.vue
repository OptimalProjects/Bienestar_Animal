<!-- src/components/adoptions/AdoptionContractManager.vue -->
<template>
  <section class="contracts-container">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Contratos de Adopción</h2>
      <p class="text2-tipografia-govco">
        Gestiona los contratos digitales de adopción generados automáticamente tras visita satisfactoria.
      </p>
    </div>

    <!-- Filtros -->
    <div class="filters-section">
      <div class="filters-grid">
        <DesplegableGovco
          id="contract-status-filter"
          label="Estado del contrato"
          v-model="filters.contractStatus"
          :options="contractStatusOptions"
          placeholder="Todos"
        />

        <InputGovCo
          id="contract-search"
          label="Buscar"
          v-model="filters.search"
          placeholder="Nombre del adoptante o animal"
        />

        <div class="filter-button-container">
          <button @click="loadContracts" class="govco-btn govco-bg-marine">
            Filtrar
          </button>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Cargando contratos...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="error-card">
      <span class="error-icon">⚠</span>
      {{ error }}
    </div>

    <!-- Sin contratos -->
    <div v-else-if="!filteredContracts.length" class="empty-state">
      <div class="empty-icon">📋</div>
      <h3>No hay contratos</h3>
      <p>No se encontraron contratos con los filtros seleccionados.</p>
    </div>

    <!-- Tabla de contratos -->
    <div v-else class="contracts-table-container">
      <table class="contracts-table">
        <thead>
          <tr>
            <th>No. Contrato</th>
            <th>Adoptante</th>
            <th>Animal</th>
            <th>Fecha Generación</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="contract in paginatedContracts" :key="contract.id">
            <td>
              <span class="contract-number">{{ contract.numeroContrato || 'Sin número' }}</span>
            </td>
            <td>
              <div class="person-cell">
                <strong>{{ contract.adoptante.nombre }}</strong>
                <small>{{ contract.adoptante.documento }}</small>
              </div>
            </td>
            <td>
              <div class="animal-cell">
                <span class="animal-code">{{ contract.animal.codigo }}</span>
                <strong>{{ contract.animal.nombre }}</strong>
                <small>{{ contract.animal.especie }}</small>
              </div>
            </td>
            <td>{{ formatDate(contract.fechaGeneracion) }}</td>
            <td>
              <span class="status-badge" :class="getStatusClass(contract)">
                {{ getStatusLabel(contract) }}
              </span>
              <small v-if="contract.fechaFirma" class="firma-date">
                Firmado: {{ formatDate(contract.fechaFirma) }}
              </small>
            </td>
            <td>
              <div class="actions-wrapper">
                <button
                  @click="downloadContract(contract)"
                  class="action-btn govco-bg-marine"
                  title="Descargar PDF"
                >
                  <span class="btn-icon">📄</span> Descargar
                </button>
                <button
                  v-if="!contract.firmado"
                  @click="viewContractDetails(contract)"
                  class="action-btn govco-bg-elf-green"
                  title="Ver detalles"
                >
                  <span class="btn-icon">👁</span> Ver
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Información de resultados y paginación -->
      <div v-if="filteredContracts.length > 0" class="pagination-container">
        <div class="pagination-info">
          Mostrando {{ ((currentPage - 1) * itemsPerPage) + 1 }}–{{ Math.min(currentPage * itemsPerPage, filteredContracts.length) }}
          de {{ filteredContracts.length }} contratos
        </div>

        <div v-if="totalPages > 1" class="pagination-controls">
          <button
            class="pagination-btn"
            :disabled="currentPage === 1"
            @click="goToPage(1)"
            title="Primera página"
          >
            «
          </button>
          <button
            class="pagination-btn"
            :disabled="currentPage === 1"
            @click="goToPage(currentPage - 1)"
            title="Página anterior"
          >
            ‹
          </button>

          <template v-for="page in totalPages" :key="page">
            <button
              v-if="page === 1 || page === totalPages || (page >= currentPage - 2 && page <= currentPage + 2)"
              class="pagination-btn"
              :class="{ active: page === currentPage }"
              @click="goToPage(page)"
            >
              {{ page }}
            </button>
            <span
              v-else-if="page === currentPage - 3 || page === currentPage + 3"
              class="pagination-ellipsis"
            >
              ...
            </span>
          </template>

          <button
            class="pagination-btn"
            :disabled="currentPage === totalPages"
            @click="goToPage(currentPage + 1)"
            title="Página siguiente"
          >
            ›
          </button>
          <button
            class="pagination-btn"
            :disabled="currentPage === totalPages"
            @click="goToPage(totalPages)"
            title="Última página"
          >
            »
          </button>
        </div>

        <div class="pagination-per-page">
          <label for="contractsPerPage">Por página:</label>
          <select id="contractsPerPage" v-model.number="itemsPerPage" @change="currentPage = 1">
            <option :value="5">5</option>
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Modal de detalles del contrato -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header govco-bg-blue-light">
          <h3 class="h4-tipografia-govco govcolor-blue-dark">Detalles del Contrato</h3>
          <button @click="closeModal" class="modal-close">&times;</button>
        </div>

        <div v-if="selectedContract" class="modal-body">
          <!-- Info del contrato -->
          <div class="contract-info-card">
            <div class="info-row">
              <span class="info-label">No. Contrato:</span>
              <span class="info-value">{{ selectedContract.numeroContrato }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Fecha generación:</span>
              <span class="info-value">{{ formatDate(selectedContract.fechaGeneracion) }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Estado:</span>
              <span class="status-badge" :class="getStatusClass(selectedContract)">
                {{ getStatusLabel(selectedContract) }}
              </span>
            </div>
          </div>

          <!-- Datos del adoptante -->
          <div class="detail-section">
            <h4 class="section-title">Datos del Adoptante</h4>
            <dl class="detail-list">
              <dt>Nombre:</dt>
              <dd>{{ selectedContract.adoptante.nombre }}</dd>
              <dt>Documento:</dt>
              <dd>{{ selectedContract.adoptante.documento }}</dd>
              <dt>Teléfono:</dt>
              <dd>{{ selectedContract.adoptante.telefono || 'No registrado' }}</dd>
              <dt>Email:</dt>
              <dd>{{ selectedContract.adoptante.email || 'No registrado' }}</dd>
              <dt>Dirección:</dt>
              <dd>{{ selectedContract.adoptante.direccion || 'No registrada' }}</dd>
            </dl>
          </div>

          <!-- Datos del animal -->
          <div class="detail-section">
            <h4 class="section-title">Datos del Animal</h4>
            <dl class="detail-list">
              <dt>Código:</dt>
              <dd><span class="animal-code">{{ selectedContract.animal.codigo }}</span></dd>
              <dt>Nombre:</dt>
              <dd>{{ selectedContract.animal.nombre }}</dd>
              <dt>Especie:</dt>
              <dd>{{ selectedContract.animal.especie }}</dd>
              <dt>Raza:</dt>
              <dd>{{ selectedContract.animal.raza || 'Mestizo' }}</dd>
            </dl>
          </div>

          <!-- Estado de firma -->
          <div v-if="selectedContract.firmado" class="firma-info success">
            <span class="firma-icon">✓</span>
            <div>
              <strong>Contrato firmado</strong>
              <p>Firmado el {{ formatDate(selectedContract.fechaFirma) }}</p>
            </div>
          </div>
          <div v-else class="firma-info pending">
            <span class="firma-icon">⏳</span>
            <div>
              <strong>Pendiente de firma</strong>
              <p>El adoptante debe firmar el contrato desde el portal ciudadano.</p>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button @click="downloadContract(selectedContract)" class="govco-btn govco-bg-marine">
            Descargar PDF
          </button>
          <button @click="closeModal" class="govco-btn govco-bg-concrete">
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import adoptionService from '@/services/adoptionService';
import DesplegableGovco from '@/components/common/DesplegableGovco.vue';
import InputGovCo from '@/components/common/InputGovCo.vue';

// Estado
const contracts = ref([]);
const loading = ref(false);
const error = ref(null);
const showModal = ref(false);
const selectedContract = ref(null);

// Filtros
const filters = ref({
  contractStatus: '',
  search: '',
});

const contractStatusOptions = [
  { value: '', text: 'Todos los estados' },
  { value: 'pending', text: 'Pendiente de firma' },
  { value: 'signed', text: 'Firmado' },
];

// Contratos filtrados
const filteredContracts = computed(() => {
  let result = contracts.value;

  // Filtrar por estado del contrato
  if (filters.value.contractStatus === 'pending') {
    result = result.filter(c => !c.firmado);
  } else if (filters.value.contractStatus === 'signed') {
    result = result.filter(c => c.firmado);
  }

  // Filtrar por búsqueda
  if (filters.value.search) {
    const term = filters.value.search.toLowerCase();
    result = result.filter(c =>
      c.adoptante.nombre.toLowerCase().includes(term) ||
      c.animal.nombre.toLowerCase().includes(term) ||
      c.animal.codigo.toLowerCase().includes(term) ||
      (c.numeroContrato && c.numeroContrato.toLowerCase().includes(term))
    );
  }

  return result;
});

// Paginación del lado del cliente
const currentPage = ref(1);
const itemsPerPage = ref(10);

const totalPages = computed(() => {
  return Math.max(1, Math.ceil(filteredContracts.value.length / itemsPerPage.value));
});

const paginatedContracts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredContracts.value.slice(start, end);
});

function goToPage(page) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
}

// Reset a página 1 cuando cambian los filtros
watch([() => filters.value.contractStatus, () => filters.value.search], () => {
  currentPage.value = 1;
});

/**
 * Transformar contrato del backend al formato del componente
 */
function transformAdoption(contrato) {
  const adoptante = contrato.adoptante || {};
  const animal = contrato.animal || {};

  return {
    id: contrato.id,
    numeroContrato: contrato.numero_contrato || generateContractNumber(contrato),
    fechaGeneracion: contrato.fecha_generacion || contrato.fecha_aprobacion,
    fechaFirma: contrato.fecha_firma,
    firmado: contrato.contrato_firmado || false,
    estadoAdopcion: contrato.estado_adopcion || contrato.estado,
    adoptante: {
      id: adoptante.id,
      nombre: adoptante.nombre_completo || `${adoptante.nombres || ''} ${adoptante.apellidos || ''}`.trim() || 'Sin nombre',
      documento: `${adoptante.tipo_documento || ''} ${adoptante.numero_documento || ''}`.trim(),
      telefono: adoptante.telefono,
      email: adoptante.email,
      direccion: adoptante.direccion,
    },
    animal: {
      id: animal.id,
      codigo: animal.codigo_unico || 'Sin código',
      nombre: animal.nombre || 'Sin nombre',
      especie: animal.especie || '',
      raza: animal.raza,
    },
  };
}

/**
 * Generar número de contrato si no existe
 */
function generateContractNumber(contrato) {
  const fecha = contrato.fecha_generacion || contrato.fecha_aprobacion || contrato.created_at;
  const year = fecha ? new Date(fecha).getFullYear() : new Date().getFullYear();
  const id = (contrato.id || '').substring(0, 8).toUpperCase();
  return `CONT-${year}-${id}`;
}

/**
 * Cargar contratos desde el endpoint dedicado
 */
async function loadContracts() {
  loading.value = true;
  error.value = null;
  contracts.value = [];

  try {
    // Usar el endpoint dedicado - traer todos los contratos
    const response = await adoptionService.fetchContracts({ all: 'true' });

    const data = response.data || response;
    const contratosData = Array.isArray(data) ? data : (data.data || []);

    // Transformar al formato del componente
    contracts.value = contratosData.map(transformAdoption);

    // Reset a la primera página
    currentPage.value = 1;

    console.log(`📋 Contratos: ${contracts.value.length} cargados`);

  } catch (err) {
    console.error('Error al cargar contratos:', err);
    error.value = 'Error al cargar los contratos. Por favor, intente nuevamente.';
  } finally {
    loading.value = false;
  }
}

/**
 * Formatear fecha
 */
function formatDate(dateString) {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

/**
 * Obtener clase CSS para el estado
 */
function getStatusClass(contract) {
  if (contract.firmado) return 'status-signed';
  return 'status-pending';
}

/**
 * Obtener etiqueta del estado
 */
function getStatusLabel(contract) {
  if (contract.firmado) return 'Firmado';
  return 'Pendiente de firma';
}

/**
 * Ver detalles del contrato
 */
function viewContractDetails(contract) {
  selectedContract.value = contract;
  showModal.value = true;
}

/**
 * Descargar contrato PDF
 */
async function downloadContract(contract) {
  try {
    const blob = await adoptionService.downloadAdoptionContract(contract.id);
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `contrato-${contract.numeroContrato || contract.id}.pdf`;
    link.click();
    window.URL.revokeObjectURL(url);

    if (window.$toast) {
      window.$toast.success('Contrato descargado correctamente');
    }
  } catch (err) {
    console.error('Error al descargar contrato:', err);
    const message = err.response?.data?.message || 'Error al descargar el contrato';
    if (window.$toast) {
      window.$toast.error('Error', message);
    } else {
      alert(message);
    }
  }
}

/**
 * Cerrar modal
 */
function closeModal() {
  showModal.value = false;
  selectedContract.value = null;
}

onMounted(loadContracts);
</script>

<style scoped>
.contracts-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
  background: #f5f7fb;
}

.form-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #3366CC;
}

/* Filtros */
.filters-section {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.filters-grid {
  display: grid;
  grid-template-columns: 220px 1fr auto;
  gap: 1.5rem;
  align-items: flex-end;
}

.filter-button-container {
  display: flex;
  align-items: flex-end;
  padding-bottom: 2px;
}

.filter-button-container .govco-btn {
  height: 40px;
  padding: 0 1.5rem;
}

/* Loading */
.loading-container {
  background: white;
  border-radius: 8px;
  padding: 3rem;
  text-align: center;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #E0E0E0;
  border-top-color: #3366CC;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Error */
.error-card {
  background: #FFEBEE;
  border: 1px solid #FFCDD2;
  color: #C62828;
  padding: 1.5rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.error-icon {
  font-size: 1.5rem;
}

/* Empty state */
.empty-state {
  background: white;
  border-radius: 8px;
  padding: 3rem;
  text-align: center;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  color: #4B4B4B;
  margin: 0 0 0.5rem;
}

.empty-state p {
  color: #737373;
  margin: 0;
}

/* Tabla */
.contracts-table-container {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.contracts-table {
  width: 100%;
  border-collapse: collapse;
}

.contracts-table thead {
  background: #E8F0FE;
}

.contracts-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #3366CC;
  border-bottom: 2px solid #3366CC;
}

.contracts-table td {
  padding: 1rem;
  border-bottom: 1px solid #E0E0E0;
  vertical-align: middle;
}

.contracts-table tbody tr:hover {
  background: #F6F8F9;
}

.contract-number {
  font-family: monospace;
  font-weight: 600;
  color: #004884;
  background: #E8F0FE;
  padding: 4px 8px;
  border-radius: 4px;
}

.person-cell,
.animal-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.animal-code {
  display: inline-block;
  font-family: monospace;
  font-size: 0.75rem;
  font-weight: 700;
  background: #E8F0FE;
  color: #3366CC;
  padding: 2px 6px;
  border-radius: 4px;
  width: fit-content;
}

.person-cell small,
.animal-cell small {
  color: #737373;
  font-size: 0.8rem;
}

/* Status badges */
.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 600;
}

.status-signed {
  background: #E8F5E9;
  color: #2E7D32;
}

.status-pending {
  background: #FFF3E0;
  color: #E65100;
}

.firma-date {
  display: block;
  font-size: 0.75rem;
  color: #737373;
  margin-top: 4px;
}

/* Acciones */
.actions-wrapper {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  border: none;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  color: white;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn:hover {
  opacity: 0.9;
  transform: translateY(-1px);
}

.btn-icon {
  font-size: 1rem;
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
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 2px solid #3366CC;
}

.modal-header h3 {
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 2rem;
  cursor: pointer;
  color: #737373;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  transition: all 0.2s;
}

.modal-close:hover {
  background: rgba(0,0,0,0.1);
}

.modal-body {
  padding: 1.5rem;
}

.contract-info-card {
  background: #E8F0FE;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
}

.info-row:not(:last-child) {
  border-bottom: 1px solid rgba(51, 102, 204, 0.2);
}

.info-label {
  font-weight: 600;
  color: #004884;
}

.info-value {
  color: #4B4B4B;
}

.detail-section {
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1rem;
  color: #3366CC;
  margin: 0 0 0.75rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #E8F0FE;
}

.detail-list {
  margin: 0;
}

.detail-list dt {
  font-weight: 600;
  color: #4B4B4B;
  margin-top: 0.5rem;
}

.detail-list dd {
  margin: 0.25rem 0 0 0;
  color: #737373;
}

/* Firma info */
.firma-info {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  border-radius: 8px;
  margin-top: 1rem;
}

.firma-info.success {
  background: #E8F5E9;
  border: 1px solid #A5D6A7;
}

.firma-info.pending {
  background: #FFF3E0;
  border: 1px solid #FFE0B2;
}

.firma-icon {
  font-size: 1.5rem;
}

.firma-info strong {
  display: block;
  margin-bottom: 0.25rem;
}

.firma-info p {
  margin: 0;
  font-size: 0.9rem;
  color: #737373;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 2px solid #E0E0E0;
}

/* GOV.CO buttons */
.govco-btn {
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  color: white;
  transition: all 0.3s;
}

.govco-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  opacity: 0.9;
}

.govco-bg-marine {
  background-color: #3366CC;
}

.govco-bg-elf-green {
  background-color: #068460;
}

.govco-bg-concrete {
  background-color: #737373;
}

.govco-bg-blue-light {
  background-color: #E8F0FE;
}

.govcolor-blue-dark {
  color: #004884;
}

/* Responsive */
@media (max-width: 992px) {
  .filters-grid {
    grid-template-columns: 1fr 1fr;
  }

  .filter-button-container {
    grid-column: 1 / 3;
    justify-content: flex-end;
  }

  .stats-row {
    grid-template-columns: 1fr;
  }

  .actions-wrapper {
    flex-direction: column;
  }
}

@media (max-width: 576px) {
  .contracts-container {
    padding: 1rem;
  }

  .filters-grid {
    grid-template-columns: 1fr;
  }

  .filter-button-container {
    grid-column: 1;
  }

  .filter-button-container .govco-btn {
    width: 100%;
  }

  .contracts-table-container {
    overflow-x: auto;
  }

  .contracts-table {
    min-width: 700px;
  }
}

/* Paginación */
.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: #f8f9fa;
  border-top: 1px solid #dee2e6;
  border-radius: 0 0 8px 8px;
  flex-wrap: wrap;
  gap: 12px;
}

.pagination-info {
  font-size: 0.85rem;
  color: #6c757d;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 4px;
}

.pagination-btn {
  min-width: 32px;
  height: 32px;
  padding: 0 8px;
  border: 1px solid #dee2e6;
  background: white;
  color: #3366cc;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 500;
  transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled):not(.active) {
  background: #e9ecef;
  border-color: #3366cc;
}

.pagination-btn.active {
  background: #3366cc;
  color: white;
  border-color: #3366cc;
}

.pagination-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.pagination-ellipsis {
  padding: 0 4px;
  color: #6c757d;
}

.pagination-per-page {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.85rem;
  color: #6c757d;
}

.pagination-per-page select {
  padding: 4px 8px;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  font-size: 0.85rem;
  background: white;
}

@media (max-width: 768px) {
  .pagination-container {
    flex-direction: column;
    align-items: center;
  }
}
</style>
