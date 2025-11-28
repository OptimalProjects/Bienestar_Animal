<!-- src/views/ComplaintsView.vue -->
<template>
  <div class="complaints-view">
    <header class="complaints-header">
      <h1 class="h2-tipografia-govco">
        Denuncias y Rescate Animal
      </h1>
      <p class="text2-tipografia-govco">
        Reporta casos de maltrato, consulta el estado de tu denuncia y gestiona operativos de rescate.
      </p>
    </header>

    <!-- PESTAÑAS -->
    <nav class="complaints-tabs">
      <button
        v-for="tab in visibleTabs"
        :key="tab.id"
        type="button"
        class="complaints-tab-btn"
        :class="{ active: activeTab === tab.id }"
        @click="activeTab = tab.id"
      >
        <span class="tab-icon" v-html="tab.icon"></span>
        {{ tab.label }}
        <span v-if="tab.badge" class="tab-badge" :class="tab.badgeClass">{{ tab.badge }}</span>
      </button>
    </nav>

    <!-- CONTENIDO SEGÚN PESTAÑA -->
    <section class="complaints-tab-content">
      <!-- HU-015: Reportar denuncia (Público) -->
      <ComplaintForm
        v-if="activeTab === 'report'"
        @submitted="onComplaintSubmitted"
      />

      <!-- HU-018: Consultar estado (Público) -->
      <ComplaintStatusCheck
        v-else-if="activeTab === 'status'"
      />

      <!-- HU-016: Lista de denuncias (Operadores) -->
      <ComplaintList
        v-else-if="activeTab === 'complaints'"
        @select="openComplaintDetail"
        @assign="openAssignment"
      />

      <!-- HU-016/HU-017: Gestión de operativos -->
      <RescueOperations
        v-else-if="activeTab === 'operations'"
        @register-result="openResultForm"
      />

      <!-- HU-019: Reportes para autoridades (Director) -->
      <AuthorityReports
        v-else-if="activeTab === 'reports'"
      />
    </section>

    <!-- MODAL: Detalle de denuncia -->
    <ComplaintDetailModal
      v-if="selectedComplaint"
      :complaint="selectedComplaint"
      @close="selectedComplaint = null"
      @assign="openAssignment"
      @register-result="openResultForm"
    />

    <!-- MODAL: Asignar operativo -->
    <RescueAssignmentModal
      v-if="showAssignmentModal"
      :complaint="complaintToAssign"
      @close="closeAssignmentModal"
      @assigned="onOperationAssigned"
    />

    <!-- MODAL: Registrar resultado -->
    <RescueResultModal
      v-if="showResultModal"
      :complaint="complaintForResult"
      @close="closeResultModal"
      @saved="onResultSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

// Componentes del módulo de denuncias
import ComplaintForm from '../components/complaints/ComplaintForm.vue';
import ComplaintStatusCheck from '../components/complaints/ComplaintStatusCheck.vue';
import ComplaintList from '../components/complaints/ComplaintList.vue';
import RescueOperations from '../components/complaints/RescueOperations.vue';
import AuthorityReports from '../components/complaints/AuthorityReports.vue';
import ComplaintDetailModal from '../components/complaints/ComplaintDetailModal.vue';
import RescueAssignmentModal from '../components/complaints/RescueAssignmentModal.vue';
import RescueResultModal from '../components/complaints/RescueResultModal.vue';

const activeTab = ref('report');

// Estado para modales
const selectedComplaint = ref(null);
const showAssignmentModal = ref(false);
const complaintToAssign = ref(null);
const showResultModal = ref(false);
const complaintForResult = ref(null);

// Simular rol del usuario (conectar con auth real)
const userRole = ref('operator'); // 'citizen', 'operator', 'director', 'admin'

// Contadores para badges (conectar con API real)
const pendingComplaints = ref(12);
const activeOperations = ref(5);

const tabs = [
  {
    id: 'report',
    label: 'Reportar denuncia',
    icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
      <line x1="12" y1="9" x2="12" y2="13"></line>
      <line x1="12" y1="17" x2="12.01" y2="17"></line>
    </svg>`,
    roles: ['citizen', 'operator', 'director', 'admin'],
    badge: null,
    badgeClass: ''
  },
  {
    id: 'status',
    label: 'Consultar estado',
    icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="11" cy="11" r="8"></circle>
      <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
    </svg>`,
    roles: ['citizen', 'operator', 'director', 'admin'],
    badge: null,
    badgeClass: ''
  },
  {
    id: 'complaints',
    label: 'Denuncias',
    icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
      <polyline points="14 2 14 8 20 8"></polyline>
      <line x1="16" y1="13" x2="8" y2="13"></line>
      <line x1="16" y1="17" x2="8" y2="17"></line>
    </svg>`,
    roles: ['operator', 'director', 'admin'],
    badge: pendingComplaints,
    badgeClass: 'badge-critical'
  },
  {
    id: 'operations',
    label: 'Operativos',
    icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="10"></circle>
      <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
    </svg>`,
    roles: ['operator', 'director', 'admin'],
    badge: activeOperations,
    badgeClass: 'badge-warning'
  },
  {
    id: 'reports',
    label: 'Reportes autoridades',
    icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
      <polyline points="14 2 14 8 20 8"></polyline>
      <line x1="12" y1="18" x2="12" y2="12"></line>
      <line x1="9" y1="15" x2="15" y2="15"></line>
    </svg>`,
    roles: ['director', 'admin'],
    badge: null,
    badgeClass: ''
  }
];

// Filtrar tabs según rol del usuario
const visibleTabs = computed(() => {
  return tabs.filter(tab => tab.roles.includes(userRole.value));
});

// Handlers
function onComplaintSubmitted(caseNumber) {
  alert(`Denuncia registrada exitosamente.\n\nNúmero de caso: ${caseNumber}\n\nGuarde este número para consultar el estado de su denuncia.`);
  activeTab.value = 'status';
}

function openComplaintDetail(complaint) {
  selectedComplaint.value = complaint;
}

function openAssignment(complaint) {
  complaintToAssign.value = complaint;
  showAssignmentModal.value = true;
  selectedComplaint.value = null;
}

function closeAssignmentModal() {
  showAssignmentModal.value = false;
  complaintToAssign.value = null;
}

function onOperationAssigned() {
  closeAssignmentModal();
  // Refrescar lista de denuncias
}

function openResultForm(complaint) {
  complaintForResult.value = complaint;
  showResultModal.value = true;
  selectedComplaint.value = null;
}

function closeResultModal() {
  showResultModal.value = false;
  complaintForResult.value = null;
}

function onResultSaved() {
  closeResultModal();
  // Refrescar listas
}
</script>

<style scoped>
.complaints-view {
  padding: 1.5rem 2rem;
  background: #f5f7fb;
  min-height: 100vh;
}

.complaints-header {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #3366cc;
}

.complaints-header h1 {
  color: #004884;
  margin: 0 0 0.5rem 0;
}

.complaints-header p {
  color: #4B4B4B;
  margin: 0;
}

/* Tabs */
.complaints-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  background: white;
  padding: 0.75rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.complaints-tab-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border: none;
  border-radius: 20px;
  padding: 0.6rem 1.2rem;
  background: #e0e0e0;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s;
  color: #4B4B4B;
}

.complaints-tab-btn:hover {
  background: #d0d0d0;
}

.complaints-tab-btn.active {
  background: #3366cc;
  color: #fff;
  font-weight: 600;
}

.tab-icon {
  display: flex;
  align-items: center;
}

.tab-badge {
  border-radius: 10px;
  padding: 2px 8px;
  font-size: 0.75rem;
  font-weight: bold;
  min-width: 20px;
  text-align: center;
}

.badge-critical {
  background: #A80521;
  color: white;
}

.badge-warning {
  background: #FFAB00;
  color: #333;
}

.complaints-tab-btn.active .tab-badge {
  background: #fff;
}

.complaints-tab-btn.active .badge-critical {
  color: #A80521;
}

.complaints-tab-btn.active .badge-warning {
  color: #FFAB00;
}

.complaints-tab-content {
  margin-top: 0.5rem;
}

/* Responsive */
@media (max-width: 768px) {
  .complaints-view {
    padding: 1rem;
  }

  .complaints-tabs {
    gap: 0.25rem;
  }

  .complaints-tab-btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.85rem;
  }

  .tab-icon {
    display: none;
  }
}

@media (max-width: 576px) {
  .complaints-tabs {
    flex-direction: column;
  }

  .complaints-tab-btn {
    width: 100%;
    justify-content: center;
  }

  .tab-icon {
    display: flex;
  }
}
</style>
