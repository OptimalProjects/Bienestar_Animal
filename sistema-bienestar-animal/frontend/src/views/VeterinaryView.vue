<!-- src/views/VeterinaryView.vue -->
<template>
  <div class="vet-view">
    <header class="vet-header">
      <h1 class="h2-tipografia-govco">
        Atención Veterinaria y Bienestar Animal
      </h1>
      <p class="text2-tipografia-govco">
        Gestión de consultas, vacunaciones, cirugías e inventario de medicamentos.
      </p>
    </header>

    <!-- PESTAÑAS -->
    <nav class="vet-tabs">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        type="button"
        class="vet-tab-btn"
        :class="{ active: activeTab === tab.id }"
        @click="activeTab = tab.id"
      >
        {{ tab.label }}
      </button>
    </nav>

    <!-- CONTENIDO SEGÚN PESTAÑA -->
    <section class="vet-tab-content">
      <MedicalRecordForm v-if="activeTab === 'consultation'" />

      <VaccinationForm v-else-if="activeTab === 'vaccination'" />

      <SurgeryForm v-else-if="activeTab === 'surgery'" />

      <MedicationInventory v-else-if="activeTab === 'inventory'" />

      <HistoryViewer v-else-if="activeTab === 'history'" />

      <VeterinaryAlerts v-else-if="activeTab === 'alerts'" />

      <CertificateGenerator v-else-if="activeTab === 'certificates'" />
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// Componentes del módulo veterinario
import MedicalRecordForm from '../components/veterinary/MedicalRecordForm.vue';
import VaccinationForm from '../components/veterinary/VaccinationForm.vue';
import SurgeryForm from '../components/veterinary/SurgeryForm.vue';
import MedicationInventory from '../components/veterinary/MedicationInventory.vue';
import MedicalHistory from '../components/veterinary/MedicalHistory.vue';
import VeterinaryAlerts from '../components/veterinary/VeterinaryAlerts.vue';
import CertificateGenerator from '../components/veterinary/CertificateGenerator.vue';
import HistoryViewer from '../components/veterinary/HistoryViewer.vue';

const activeTab = ref('consultation');

const tabs = [
  { id: 'consultation', label: 'Consulta veterinaria' },
  { id: 'vaccination', label: 'Vacunación' },
  { id: 'surgery', label: 'Cirugías' },
  { id: 'inventory', label: 'Inventario' },
  { id: 'history', label: 'Historial clínico' },
  { id: 'alerts', label: 'Alertas' },
  { id: 'certificates', label: 'Certificados' }
];
</script>

<style scoped>
.vet-view {
  padding: 1.5rem 2rem;
  background: #f5f7fb;
  min-height: 100vh;
}

.vet-header {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #3366cc;
}

/* Tabs */
.vet-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.vet-tab-btn {
  border: none;
  border-radius: 20px;
  padding: 0.5rem 1rem;
  background: #e0e0e0;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.vet-tab-btn:hover {
  background: #d0d0d0;
}

.vet-tab-btn.active {
  background: #3366cc;
  color: #fff;
  font-weight: 600;
}

.vet-tab-content {
  margin-top: 0.5rem;
}

/* Responsive */
@media (max-width: 768px) {
  .vet-view {
    padding: 1rem;
  }
}
</style>