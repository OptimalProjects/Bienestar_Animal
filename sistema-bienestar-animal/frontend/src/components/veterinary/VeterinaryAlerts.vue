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
import { ref, onMounted, computed } from 'vue';
import { useVeterinaryStore } from '@/stores/veterinary';
import veterinaryService from '@/services/veterinaryService';

const veterinaryStore = useVeterinaryStore();

const loading = ref(true);
const vaccinationAlerts = ref([]);
const postSurgeryAlerts = ref([]);
const chronicTreatmentAlerts = ref([]);
const inventoryAlerts = ref([]);

// Función para formatear fecha
function formatDate(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('es-CO', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  });
}

async function loadAlerts() {
  loading.value = true;
  try {
    // Cargar vacunas próximas (próximos 30 días)
    const vacunasProximasResponse = await veterinaryService.getVacunasProximas(30);
    if (vacunasProximasResponse?.data) {
      vaccinationAlerts.value = vacunasProximasResponse.data.map(v => ({
        id: v.id,
        animalName: v.historial_clinico?.animal?.nombre || 'Animal',
        vaccine: v.tipo_vacuna?.nombre || 'Vacuna',
        nextDoseDate: formatDate(v.fecha_proxima)
      }));
    }

    // Cargar alertas de stock bajo
    await veterinaryStore.fetchAlertasStockBajo();
    inventoryAlerts.value = veterinaryStore.alertasStockBajo.map(item => ({
      id: item.id,
      name: item.nombre || item.producto?.nombre,
      message: item.mensaje || `Stock bajo (quedan ${item.stock_actual || item.cantidad} unidades)`
    }));

    // Cargar alertas generales (si existe endpoint)
    try {
      await veterinaryStore.fetchAlertas();
      const alertasGenerales = veterinaryStore.alertas;

      // Separar alertas por tipo
      postSurgeryAlerts.value = alertasGenerales
        .filter(a => a.tipo === 'postoperatorio' || a.tipo === 'cirugia')
        .map(a => ({
          id: a.id,
          animalName: a.animal?.nombre || 'Animal',
          surgeryType: a.cirugia?.tipo_cirugia || a.descripcion || 'Cirugía',
          controlDate: formatDate(a.fecha_control || a.fecha)
        }));

      chronicTreatmentAlerts.value = alertasGenerales
        .filter(a => a.tipo === 'tratamiento_cronico' || a.tipo === 'tratamiento')
        .map(a => ({
          id: a.id,
          animalName: a.animal?.nombre || 'Animal',
          condition: a.condicion || a.descripcion || 'Tratamiento',
          reviewDate: formatDate(a.fecha_revision || a.fecha)
        }));
    } catch (e) {
      // Si no hay endpoint de alertas generales, dejar vacío
      console.log('Alertas generales no disponibles:', e.message);
    }

    // Cargar recordatorios de vacunas
    try {
      await veterinaryStore.fetchRecordatoriosVacunas();
      const recordatorios = veterinaryStore.recordatoriosVacunas;

      // Agregar recordatorios a alertas de vacunación si no están duplicados
      recordatorios.forEach(r => {
        const existe = vaccinationAlerts.value.some(v => v.id === r.id);
        if (!existe) {
          vaccinationAlerts.value.push({
            id: `rec-${r.id}`,
            animalName: r.animal?.nombre || 'Animal',
            vaccine: r.tipo_vacuna?.nombre || r.vacuna || 'Vacuna',
            nextDoseDate: formatDate(r.fecha_programada)
          });
        }
      });
    } catch (e) {
      console.log('Recordatorios no disponibles:', e.message);
    }

  } catch (e) {
    console.error('Error cargando alertas veterinarias:', e);
  } finally {
    loading.value = false;
  }
}

// Computed para mostrar totales
const totalAlertas = computed(() => {
  return vaccinationAlerts.value.length +
    postSurgeryAlerts.value.length +
    chronicTreatmentAlerts.value.length +
    inventoryAlerts.value.length;
});

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
