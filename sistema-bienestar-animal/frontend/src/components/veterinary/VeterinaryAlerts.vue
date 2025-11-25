<template>
  <section class="vet-alerts">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Alertas veterinarias</h2>
      <p class="text2-tipografia-govco">
        Próximas vacunaciones, controles postquirúrgicos, tratamientos crónicos y stock de medicamentos.
      </p>
    </div>

    <div class="alerts-grid">
      <!-- Próximas vacunaciones -->
      <div class="alert-card">
        <h3>Próximas vacunaciones</h3>
        <ul v-if="vaccinationAlerts.length">
          <li v-for="a in vaccinationAlerts" :key="a.id">
            {{ a.animalName }} – {{ a.vaccine }}
            <br />
            <small>Fecha próxima dosis: {{ a.nextDoseDate }}</small>
          </li>
        </ul>
        <p v-else>No hay vacunaciones próximas.</p>
      </div>

      <!-- Controles postquirúrgicos -->
      <div class="alert-card">
        <h3>Controles postquirúrgicos</h3>
        <ul v-if="postSurgeryAlerts.length">
          <li v-for="a in postSurgeryAlerts" :key="a.id">
            {{ a.animalName }} – {{ a.surgeryType }}
            <br />
            <small>Control el {{ a.controlDate }}</small>
          </li>
        </ul>
        <p v-else>No hay controles postoperatorios pendientes.</p>
      </div>

      <!-- Tratamientos crónicos -->
      <div class="alert-card">
        <h3>Tratamientos crónicos</h3>
        <ul v-if="chronicTreatmentAlerts.length">
          <li v-for="a in chronicTreatmentAlerts" :key="a.id">
            {{ a.animalName }} – {{ a.condition }}
            <br />
            <small>Revisión de control: {{ a.reviewDate }}</small>
          </li>
        </ul>
        <p v-else>No hay revisiones de tratamientos crónicos pendientes.</p>
      </div>

      <!-- Inventario -->
      <div class="alert-card">
        <h3>Inventario de medicamentos</h3>
        <ul v-if="inventoryAlerts.length">
          <li v-for="a in inventoryAlerts" :key="a.id">
            {{ a.name }} – {{ a.message }}
          </li>
        </ul>
        <p v-else>No hay alertas de inventario.</p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const vaccinationAlerts = ref([]);
const postSurgeryAlerts = ref([]);
const chronicTreatmentAlerts = ref([]);
const inventoryAlerts = ref([]);

async function loadAlerts() {
  try {
    // TODO: Reemplazar mocks por llamadas a backend / colas de alertas
    vaccinationAlerts.value = [
      {
        id: 1,
        animalName: 'Firulais',
        vaccine: 'Rabia',
        nextDoseDate: '2025-12-01'
      }
    ];

    postSurgeryAlerts.value = [
      {
        id: 2,
        animalName: 'Michi',
        surgeryType: 'Esterilización',
        controlDate: '2025-11-28'
      }
    ];

    chronicTreatmentAlerts.value = [
      {
        id: 3,
        animalName: 'Rocky',
        condition: 'Cardiopatía crónica',
        reviewDate: '2025-12-05'
      }
    ];

    inventoryAlerts.value = [
      {
        id: 4,
        name: 'Meloxicam 5mg',
        message: 'Stock bajo (quedan 5 unidades)'
      },
      {
        id: 5,
        name: 'Amoxicilina 500mg',
        message: 'Próximo a vencer (15 días)'
      }
    ];
  } catch (e) {
    console.error('Error cargando alertas veterinarias:', e);
  }
}

onMounted(loadAlerts);
</script>

<style scoped>
.vet-alerts {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  background: #f5f7fb;
}
.form-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #3366cc;
}
.alerts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1rem;
}
.alert-card {
  background: #fff;
  border-radius: 8px;
  padding: 1rem 1.25rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}
.alert-card h3 {
  margin-top: 0;
}
.alert-card ul {
  padding-left: 1.25rem;
}
.alert-card li {
  margin-bottom: 0.75rem;
  font-size: 0.9rem;
}
.alert-card small {
  color: #555;
}
</style>
