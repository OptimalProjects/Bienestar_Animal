<template>
  <section class="medical-history">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Historial clínico</h2>
      <p class="text2-tipografia-govco">
        Línea de tiempo de atenciones veterinarias.
      </p>
    </div>

    <div v-if="loading" class="loading-state">
      Cargando historial...
    </div>

    <div v-else-if="!entries.length" class="empty-state">
      No hay registros clínicos para este animal.
    </div>

    <ol v-else class="timeline">
      <li v-for="item in sortedEntries" :key="item.id" class="timeline-item">
        <div class="timeline-dot" :class="`type-${item.type}`"></div>
        <div class="timeline-content">
          <div class="timeline-header">
            <span class="timeline-date">{{ item.date }}</span>
            <span class="timeline-type">{{ typeLabel(item.type) }}</span>
          </div>
          <h3 class="timeline-title">{{ item.title }}</h3>
          <p class="timeline-desc">{{ item.description }}</p>
          <div v-if="item.tags?.length" class="timeline-tags">
            <span v-for="tag in item.tags" :key="tag" class="tag">
              {{ tag }}
            </span>
          </div>
        </div>
      </li>
    </ol>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useVeterinaryStore } from '@/stores/veterinary';

const props = defineProps({
  animalId: {
    type: [String, Number],
    required: true
  }
});

const veterinaryStore = useVeterinaryStore();
const loading = ref(true);
const entries = ref([]);
const historialData = ref(null);

/**
 * type: 'consultation' | 'vaccination' | 'surgery' | 'hospitalization' | 'lab' | 'deworming'
 */

const typeLabel = (type) => {
  switch (type) {
    case 'consultation':
      return 'Consulta médica';
    case 'vaccination':
      return 'Vacunación';
    case 'surgery':
      return 'Cirugía';
    case 'hospitalization':
      return 'Hospitalización';
    case 'lab':
      return 'Resultado de laboratorio';
    case 'deworming':
      return 'Desparasitación';
    default:
      return 'Registro';
  }
};

const sortedEntries = computed(() =>
  [...entries.value].sort((a, b) => (a.date < b.date ? 1 : -1))
);

// Función para formatear fecha
function formatDate(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toISOString().split('T')[0];
}

// Función para transformar datos del backend al formato del timeline
function transformHistorialToEntries(historial) {
  const allEntries = [];

  // Consultas
  if (historial.consultas && historial.consultas.length > 0) {
    historial.consultas.forEach(consulta => {
      allEntries.push({
        id: `consulta-${consulta.id}`,
        type: 'consultation',
        date: formatDate(consulta.fecha_consulta),
        title: `Consulta ${consulta.tipo_consulta || 'general'}`,
        description: consulta.diagnostico || consulta.motivo_consulta || 'Sin diagnóstico registrado',
        tags: [
          consulta.estado || 'realizada',
          ...(consulta.tratamientos?.length > 0 ? ['con tratamiento'] : [])
        ].filter(Boolean)
      });
    });
  }

  // Vacunas
  if (historial.vacunas && historial.vacunas.length > 0) {
    historial.vacunas.forEach(vacuna => {
      allEntries.push({
        id: `vacuna-${vacuna.id}`,
        type: 'vaccination',
        date: formatDate(vacuna.fecha_aplicacion),
        title: vacuna.tipo_vacuna?.nombre || 'Vacunación',
        description: `${vacuna.fabricante || ''} - Lote: ${vacuna.lote || 'N/A'}`.trim(),
        tags: [
          vacuna.fecha_proxima ? `próxima: ${formatDate(vacuna.fecha_proxima)}` : null
        ].filter(Boolean)
      });
    });
  }

  // Cirugías
  if (historial.cirugias && historial.cirugias.length > 0) {
    historial.cirugias.forEach(cirugia => {
      allEntries.push({
        id: `cirugia-${cirugia.id}`,
        type: 'surgery',
        date: formatDate(cirugia.fecha_realizacion || cirugia.fecha_programada),
        title: `${cirugia.tipo_cirugia || 'Cirugía'}`,
        description: cirugia.descripcion || `Resultado: ${cirugia.resultado || 'pendiente'}`,
        tags: [
          cirugia.resultado,
          cirugia.estado
        ].filter(Boolean)
      });
    });
  }

  // Exámenes de laboratorio
  if (historial.examenes && historial.examenes.length > 0) {
    historial.examenes.forEach(examen => {
      allEntries.push({
        id: `examen-${examen.id}`,
        type: 'lab',
        date: formatDate(examen.fecha_examen),
        title: examen.tipo_examen || 'Examen de laboratorio',
        description: examen.resultados || 'Resultados pendientes',
        tags: [examen.estado].filter(Boolean)
      });
    });
  }

  return allEntries;
}

async function loadHistory() {
  if (!props.animalId) return;

  loading.value = true;
  try {
    console.log('Cargando historial para animal', props.animalId);

    // Llamar al store para obtener el historial completo
    const historial = await veterinaryStore.fetchHistorialClinico(props.animalId);

    if (historial) {
      historialData.value = historial;
      entries.value = transformHistorialToEntries(historial);
    } else {
      entries.value = [];
    }
  } catch (err) {
    console.error('Error cargando historial clínico:', err);
    entries.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(loadHistory);
watch(() => props.animalId, loadHistory);
</script>

<style scoped>
.medical-history {
  max-width: 900px;
  margin: 0 auto;
  padding: 2rem;
  background: #f5f7fb;
}
.form-header {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #3366cc;
}
.loading-state,
.empty-state {
  padding: 1.5rem;
}
.timeline {
  list-style: none;
  margin: 0;
  padding: 0;
  position: relative;
}
.timeline-item {
  display: flex;
  gap: 1rem;
  position: relative;
  padding-bottom: 1.5rem;
}
.timeline-item::before {
  content: '';
  position: absolute;
  left: 8px;
  top: 0;
  bottom: -0.5rem;
  width: 2px;
  background: #d0d0d0;
}
.timeline-item:last-child::before {
  bottom: 0;
}
.timeline-dot {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  margin-top: 4px;
  background: #3366cc;
  flex-shrink: 0;
}
.timeline-dot.type-vaccination {
  background: #069169;
}
.timeline-dot.type-surgery {
  background: #cc3366;
}
.timeline-dot.type-hospitalization {
  background: #ff9900;
}
.timeline-dot.type-lab {
  background: #6633cc;
}
.timeline-content {
  background: #fff;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
  flex: 1;
}
.timeline-header {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: #555;
}
.timeline-title {
  margin: 0.25rem 0;
  font-size: 1rem;
  font-weight: 600;
}
.timeline-desc {
  margin: 0;
  font-size: 0.9rem;
}
.timeline-tags {
  margin-top: 0.5rem;
}
.tag {
  display: inline-block;
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
  background: #e8f0fe;
  color: #3366cc;
  font-size: 0.75rem;
  margin-right: 0.25rem;
}
</style>
