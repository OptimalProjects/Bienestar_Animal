<!-- src/components/adoptions/AdoptionContractManager.vue -->
<template>
  <section class="contracts-container">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Contratos de adopción</h2>
      <p class="text2-tipografia-govco">
        Gestiona los contratos digitales de adopción ya aprobados.
      </p>
    </div>

    <div v-if="loading" class="govco-card">
      Cargando solicitudes aprobadas...
    </div>

    <div v-else-if="error" class="govco-card error-card">
      {{ error }}
    </div>

    <div v-else-if="!requests.length" class="govco-card">
      No hay solicitudes aprobadas pendientes de contrato.
    </div>

    <table v-else class="govco-table">
      <thead>
        <tr>
          <th>Adoptante</th>
          <th>Animal</th>
          <th>Fecha Aprobación</th>
          <th>Estado Contrato</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="req in requests" :key="req.id">
          <td>
            <strong>{{ req.applicant.name }}</strong><br />
            <span class="small">{{ req.applicant.idNumber }}</span>
          </td>
          <td>
            {{ req.animal.name }}<br />
            <span class="small">{{ req.animal.species }}</span>
          </td>
          <td>
            {{ formatDate(req.approvedAt) }}
          </td>
          <td>
            <span v-if="req.contractSigned" class="badge badge-success">
              Firmado
            </span>
            <span v-else-if="req.contractData" class="badge badge-warning">
              Generado pendiente de firma
            </span>
            <span v-else class="badge badge-secondary">
              Sin contrato
            </span>
          </td>
          <td class="actions-col">
            <button
              v-if="!req.contractData"
              type="button"
              class="govco-btn govco-btn-small govco-btn-primary"
              :disabled="generatingContract === req.id"
              @click="onGenerate(req)"
            >
              {{ generatingContract === req.id ? 'Generando...' : 'Generar contrato' }}
            </button>

            <button
              v-if="req.contractData"
              type="button"
              class="govco-btn govco-btn-small govco-btn-secondary"
              @click="onViewContract(req)"
            >
              Ver contrato
            </button>

            <button
              v-if="req.contractData && !req.contractSigned"
              type="button"
              class="govco-btn govco-btn-small govco-btn-success"
              @click="onSign(req)"
            >
              Registrar firma
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal para ver contrato -->
    <div v-if="showContractModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Contrato de Adopción</h3>
          <button type="button" class="close-btn" @click="closeModal">&times;</button>
        </div>
        <div v-if="selectedContract" class="modal-body">
          <div class="contract-info">
            <p><strong>Número de contrato:</strong> {{ selectedContract.numero_contrato }}</p>
            <p><strong>Fecha de generación:</strong> {{ selectedContract.fecha_generacion }}</p>
          </div>
          <div class="contract-section">
            <h4>Datos del Adoptante</h4>
            <p><strong>Nombre:</strong> {{ selectedContract.adoptante?.nombre_completo }}</p>
            <p><strong>Documento:</strong> {{ selectedContract.adoptante?.tipo_documento }} {{ selectedContract.adoptante?.documento_identidad }}</p>
            <p><strong>Dirección:</strong> {{ selectedContract.adoptante?.direccion }}</p>
            <p><strong>Teléfono:</strong> {{ selectedContract.adoptante?.telefono }}</p>
          </div>
          <div class="contract-section">
            <h4>Datos del Animal</h4>
            <p><strong>Nombre:</strong> {{ selectedContract.animal?.nombre }}</p>
            <p><strong>Especie:</strong> {{ selectedContract.animal?.especie }}</p>
            <p><strong>Raza:</strong> {{ selectedContract.animal?.raza || 'No especificada' }}</p>
          </div>
          <div v-if="selectedContract.compromisos?.length" class="contract-section">
            <h4>Compromisos del Adoptante</h4>
            <ul>
              <li v-for="(compromiso, idx) in selectedContract.compromisos" :key="idx">
                {{ compromiso }}
              </li>
            </ul>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="govco-btn govco-btn-secondary" @click="closeModal">
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import adoptionService from '@/services/adoptionService';

const requests = ref([]);
const loading = ref(false);
const error = ref(null);
const generatingContract = ref(null);
const showContractModal = ref(false);
const selectedContract = ref(null);

/**
 * Transformar adopción del backend al formato del componente
 */
function transformAdoption(adopcion) {
  return {
    id: adopcion.id,
    applicant: {
      name: adopcion.adoptante?.nombre_completo || 'Sin nombre',
      idNumber: adopcion.adoptante?.documento_identidad || '',
    },
    animal: {
      name: adopcion.animal?.nombre || 'Sin nombre',
      species: adopcion.animal?.especie || '',
    },
    approvedAt: adopcion.fecha_aprobacion || adopcion.updated_at,
    contractData: adopcion.contrato || null,
    contractSigned: adopcion.contrato_firmado || false,
    rawData: adopcion,
  };
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
 * Cargar adopciones aprobadas
 */
async function loadData() {
  loading.value = true;
  error.value = null;
  try {
    const response = await adoptionService.fetchAdoptionRequests({ estado: 'aprobada' });
    const data = response.data || response || [];
    const rawAdoptions = Array.isArray(data) ? data : (data.data || []);
    requests.value = rawAdoptions.map(transformAdoption);
  } catch (err) {
    console.error('Error al cargar adopciones aprobadas:', err);
    error.value = 'Error al cargar las solicitudes aprobadas. Por favor, intente nuevamente.';
  } finally {
    loading.value = false;
  }
}

/**
 * Generar contrato para una adopción
 */
async function onGenerate(req) {
  generatingContract.value = req.id;
  try {
    const response = await adoptionService.fetchAdoptionContract(req.id);
    const contractData = response.data || response;

    // Actualizar el request con los datos del contrato
    req.contractData = contractData;

    // Mostrar el contrato generado
    selectedContract.value = contractData;
    showContractModal.value = true;
  } catch (err) {
    console.error('Error al generar contrato:', err);
    const message = err.response?.data?.message || 'Error al generar el contrato';
    error.value = message;
  } finally {
    generatingContract.value = null;
  }
}

/**
 * Ver contrato existente
 */
function onViewContract(req) {
  selectedContract.value = req.contractData;
  showContractModal.value = true;
}

/**
 * Registrar firma del contrato
 * TODO: Implementar endpoint en backend para registrar firma
 */
async function onSign(req) {
  // Por ahora solo marcamos localmente ya que el backend no tiene este endpoint
  req.contractSigned = true;
  console.log('Firma registrada para adopción:', req.id);
  // TODO: Llamar al endpoint cuando esté disponible
  // await adoptionService.registerContractSignature(req.id);
}

/**
 * Cerrar modal
 */
function closeModal() {
  showContractModal.value = false;
  selectedContract.value = null;
}

onMounted(loadData);
</script>


<style scoped>
.contracts-container {
  background: #f5f7fb;
  padding: 16px 20px;
  border-radius: 8px;
}

.govco-card {
  background: #ffffff;
  padding: 16px;
  border-radius: 8px;
}

.error-card {
  background: #f8d7da;
  color: #842029;
  border: 1px solid #f5c2c7;
}

.small {
  font-size: 0.8rem;
  color: #555;
}

.badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 0.75rem;
}

.badge-success {
  background: #198754;
  color: #fff;
}

.badge-warning {
  background: #ffc107;
  color: #000;
}

.badge-secondary {
  background: #6c757d;
  color: #fff;
}

.actions-col {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: #fff;
  border-radius: 8px;
  max-width: 600px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #dee2e6;
}

.modal-header h3 {
  margin: 0;
  color: #3772ff;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
}

.close-btn:hover {
  color: #000;
}

.modal-body {
  padding: 20px;
}

.contract-info {
  background: #e7f1ff;
  padding: 12px 16px;
  border-radius: 6px;
  margin-bottom: 16px;
}

.contract-info p {
  margin: 4px 0;
}

.contract-section {
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #eee;
}

.contract-section:last-child {
  border-bottom: none;
}

.contract-section h4 {
  color: #3772ff;
  margin: 0 0 8px 0;
  font-size: 1rem;
}

.contract-section p {
  margin: 4px 0;
}

.contract-section ul {
  margin: 8px 0;
  padding-left: 20px;
}

.contract-section li {
  margin: 4px 0;
}

.modal-footer {
  padding: 16px 20px;
  border-top: 1px solid #dee2e6;
  display: flex;
  justify-content: flex-end;
}
</style>
