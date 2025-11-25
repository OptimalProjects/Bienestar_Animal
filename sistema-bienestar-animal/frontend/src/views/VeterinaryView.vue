<!-- src/views/VeterinaryView.vue -->
<template>
  <div class="vet-layout">
    <!-- Si ya usas Navbar y Sidebar en otras vistas, los reutilizamos -->
    <Navbar />

    <div class="vet-content">
      <Sidebar />

      <main class="vet-main">
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

          <div v-else-if="activeTab === 'history'">
            <!-- Puedes pasar el animalId real cuando lo tengas -->
            <MedicalHistory :animal-id="1" />
          </div>

          <VeterinaryAlerts v-else-if="activeTab === 'alerts'" />

          <CertificateGenerator v-else-if="activeTab === 'certificates'" />
        </section>
      </main>
    </div>
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
.vet-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.vet-content {
  display: flex;
  flex: 1;
}

/* Sidebar a la izquierda y contenido a la derecha */
.vet-main {
  flex: 1;
  padding: 1.5rem 2rem;
  background: #f5f7fb;
  overflow-x: hidden;
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
@media (max-width: 992px) {
  .vet-content {
    flex-direction: column;
  }
}
</style>
