<!-- src/components/adoptions/PostAdoptionFollowUps.vue -->
<template>
  <section class="followups-container">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Seguimientos post-adopción</h2>
      <p class="text2-tipografia-govco">
        Registra el resultado de los seguimientos y activa procesos de rescate
        cuando sea necesario.
      </p>
    </div>

    <div v-if="loading" class="govco-card">
      Cargando seguimientos pendientes...
    </div>

    <div v-else-if="!followups.length" class="govco-card">
      No hay seguimientos pendientes.
    </div>

    <table v-else class="govco-table">
      <thead>
        <tr>
          <th>Animal</th>
          <th>Adoptante</th>
          <th>Fecha programada</th>
          <th>Estado</th>
          <th>Resultado</th>
          <th>Observaciones</th>
          <th>Adjuntos</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="f in followups"
          :key="f.id"
          :class="{ overdue: isOverdue(f.scheduledDate) }"
        >
          <td>
            {{ f.animal.name }}<br />
            <span class="small">{{ f.animal.microchip }}</span>
          </td>
          <td>{{ f.adopter.name }}</td>
          <td>{{ formatDate(f.scheduledDate) }}</td>
          <td>
            <span v-if="isOverdue(f.scheduledDate)" class="badge badge-danger">
              Vencido
            </span>
            <span v-else class="badge badge-info">
              Pendiente
            </span>
          </td>
          <td>
            <select v-model="f.result">
              <option value="">Sin registrar</option>
              <option value="successful">Exitoso</option>
              <option value="with_observations">Con observaciones</option>
              <option value="critical">Crítico</option>
            </select>
          </td>
          <td>
            <textarea
              v-model="f.observations"
              rows="2"
              class="small-textarea"
            />
          </td>
          <td>
            <input
              type="file"
              multiple
              @change="onFilesSelected($event, f)"
            />
          </td>
          <td>
            <button
              type="button"
              class="govco-btn govco-btn-small govco-btn-primary"
              @click="save(f)"
            >
              Guardar
            </button>
            <button
              v-if="f.result === 'critical'"
              type="button"
              class="govco-btn govco-btn-small govco-btn-danger"
              @click="startRescue(f)"
            >
              Iniciar rescate
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import {
  fetchPendingFollowUps,
  saveFollowUpResult,
} from '../../services/adoptionService.js';

const followups = ref([]);
const loading = ref(false);

async function loadData() {
  loading.value = true;
  try {
    followups.value = await fetchPendingFollowUps();
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
}

function isOverdue(dateStr) {
  const today = new Date();
  const date = new Date(dateStr);
  return date < today;
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('es-CO');
}

function onFilesSelected(event, f) {
  const files = Array.from(event.target.files || []);
  f.photos = files;
}

async function save(f) {
  try {
    // Si necesitas enviar fotos por multipart, aquí las subes vía FormData
    await saveFollowUpResult(f.id, {
      result: f.result,
      observations: f.observations,
      // aquí podrías mandar ids de archivos si el backend ya los guarda aparte
    });
    alert('Seguimiento guardado.');
  } catch (err) {
    console.error(err);
    alert('Error al guardar seguimiento');
  }
}

function startRescue(f) {
  // Aquí simplemente navegas al módulo de denuncias/rescate
  // Por ejemplo:
  window.location.href = `/denuncias/nueva?animalId=${f.animal.id}&followupId=${f.id}`;
}

onMounted(loadData);
</script>

<style scoped>
.followups-container {
  background: #f5f7fb;
  padding: 16px 20px;
  border-radius: 8px;
}

.govco-card {
  background: #ffffff;
  padding: 16px;
  border-radius: 8px;
}

.small {
  font-size: 0.8rem;
}

.small-textarea {
  width: 160px;
  resize: vertical;
}

.badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 0.75rem;
}

.badge-danger {
  background: #dc3545;
  color: #fff;
}

.badge-info {
  background: #0d6efd;
  color: #fff;
}

.overdue {
  background: #fff3f3;
}
</style>
