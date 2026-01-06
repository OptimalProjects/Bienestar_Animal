<!-- AdoptionCoordinatorDashboard.vue -->
<template>
  <section class="dashboard-container">
    <div class="dashboard-header">
      <h1 class="h2-tipografia-govco">Panel de Adopciones</h1>
      <p class="text2-tipografia-govco">Gestión integral del ciclo de adopción</p>
    </div>

    <!-- KPIs Principales -->
    <div class="kpi-grid">
      <div class="kpi-card govco-bg-hawkes-blue">
        <div class="kpi-icon">📋</div>
        <div class="kpi-content">
          <h3 class="kpi-number">{{ stats.pendingRequests }}</h3>
          <p class="kpi-label">Solicitudes Pendientes</p>
        </div>
      </div>

      <div class="kpi-card govco-bg-hawkes-blue">
        <div class="kpi-icon">🏠</div>
        <div class="kpi-content">
          <h3 class="kpi-number">{{ stats.monthlyAdoptions }}</h3>
          <p class="kpi-label">Adopciones del Mes</p>
        </div>
      </div>

      <div class="kpi-card govco-bg-hawkes-blue">
        <div class="kpi-icon">📅</div>
        <div class="kpi-content">
          <h3 class="kpi-number">{{ stats.pendingFollowups }}</h3>
          <p class="kpi-label">Seguimientos Pendientes</p>
        </div>
      </div>

      <div class="kpi-card govco-bg-hawkes-blue">
        <div class="kpi-icon">🐕</div>
        <div class="kpi-content">
          <h3 class="kpi-number">{{ stats.availableAnimals }}</h3>
          <p class="kpi-label">Animales Disponibles</p>
        </div>
      </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="quick-actions">
      <h3 class="h5-tipografia-govco section-title">Acciones Rápidas</h3>
      <div class="action-buttons">
        <button @click="goToRequests" class="action-btn govco-bg-marine">
          <span class="action-icon">📝</span>
          <span>Gestionar Solicitudes</span>
          <span v-if="stats.pendingRequests > 0" class="badge">{{ stats.pendingRequests }}</span>
        </button>

        <button @click="goToFollowups" class="action-btn govco-bg-gold">
          <span class="action-icon">📞</span>
          <span>Seguimientos</span>
          <span v-if="stats.overdueFollowups > 0" class="badge badge-warning">{{ stats.overdueFollowups }}</span>
        </button>

        <button @click="goToContracts" class="action-btn govco-bg-elf-green">
          <span class="action-icon">📄</span>
          <span>Generar Contrato</span>
        </button>

        <button @click="goToReturns" class="action-btn govco-bg-shiraz">
          <span class="action-icon">↩️</span>
          <span>Devoluciones</span>
        </button>

        <button @click="goToCatalog" class="action-btn govco-bg-blue-light">
          <span class="action-icon">🔍</span>
          <span>Ver Catálogo</span>
        </button>
      </div>
    </div>

    <!-- Solicitudes Recientes -->
    <div class="recent-requests">
      <div class="section-header">
        <h3 class="h5-tipografia-govco section-title">Solicitudes Recientes</h3>
        <button @click="goToRequests" class="link-btn">Ver todas →</button>
      </div>

      <div v-if="recentRequests.length === 0" class="empty-state">
        <p>No hay solicitudes recientes</p>
      </div>

      <div v-else class="requests-list">
        <div
          v-for="request in recentRequests"
          :key="request.id"
          class="request-item"
          @click="viewRequest(request)"
        >
          <div class="request-animal">
            <img :src="request.animal.photoUrl || 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiB2aWV3Qm94PSIwIDAgNDAwIDMwMCI+PHJlY3Qgd2lkdGg9IjQwMCIgaGVpZ2h0PSIzMDAiIGZpbGw9IiNlOWVjZWYiLz48ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyMDAsMTUwKSI+PGVsbGlwc2UgY3g9IjAiIGN5PSIyNSIgcng9IjM1IiByeT0iMzAiIGZpbGw9IiNhZGI1YmQiLz48ZWxsaXBzZSBjeD0iLTQ1IiBjeT0iLTEwIiByeD0iMTgiIHJ5PSIyMiIgZmlsbD0iI2FkYjViZCIvPjxlbGxpcHNlIGN4PSI0NSIgY3k9Ii0xMCIgcng9IjE4IiByeT0iMjIiIGZpbGw9IiNhZGI1YmQiLz48ZWxsaXBzZSBjeD0iLTI1IiBjeT0iLTQ1IiByeD0iMTUiIHJ5PSIxOCIgZmlsbD0iI2FkYjViZCIvPjxlbGxpcHNlIGN4PSIyNSIgY3k9Ii00NSIgcng9IjE1IiByeT0iMTgiIGZpbGw9IiNhZGI1YmQiLz48L2c+PC9zdmc+'" :alt="request.animal.name" />
            <div>
              <h4 class="h6-tipografia-govco">{{ request.animal.name }}</h4>
              <p class="request-meta">{{ request.animal.species }} - {{ request.animal.breed }}</p>
            </div>
          </div>

          <div class="request-applicant">
            <p><strong>{{ request.applicant.name }}</strong></p>
            <p class="request-meta">{{ request.applicant.phone }}</p>
          </div>

          <div class="request-status">
            <span class="status-badge" :class="`status-${request.status}`">
              {{ getStatusLabel(request.status) }}
            </span>
            <p class="request-meta">{{ formatDate(request.createdAt) }}</p>
          </div>

          <button class="view-btn" @click.stop="viewRequest(request)">Ver</button>
        </div>
      </div>
    </div>

    <!-- Seguimientos Próximos -->
    <div class="upcoming-followups">
      <div class="section-header">
        <h3 class="h5-tipografia-govco section-title">Seguimientos Próximos</h3>
        <button @click="goToFollowups" class="link-btn">Ver todos →</button>
      </div>

      <div v-if="upcomingFollowups.length === 0" class="empty-state">
        <p>No hay seguimientos programados</p>
      </div>

      <div v-else class="followups-list">
        <div
          v-for="followup in upcomingFollowups"
          :key="followup.id"
          class="followup-item"
          :class="{ 'overdue': isOverdue(followup.scheduledDate) }"
        >
          <div class="followup-date">
            <div class="date-icon" :class="{ 'overdue-icon': isOverdue(followup.scheduledDate) }">
              📅
            </div>
            <div>
              <p class="followup-day">{{ formatDay(followup.scheduledDate) }}</p>
              <p class="followup-time">{{ formatTime(followup.scheduledDate) }}</p>
            </div>
          </div>

          <div class="followup-animal">
            <p><strong>{{ followup.animal.name }}</strong></p>
            <p class="followup-meta">{{ followup.animal.species }} - {{ followup.animal.microchip }}</p>
          </div>

          <div class="followup-adopter">
            <p>{{ followup.adopter.name }}</p>
            <p class="followup-meta">{{ followup.adopter.phone }}</p>
          </div>

          <button class="perform-btn" @click="performFollowup(followup)">
            Realizar
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const stats = ref({
  pendingRequests: 8,
  monthlyAdoptions: 12,
  pendingFollowups: 5,
  overdueFollowups: 2,
  availableAnimals: 23
});

const recentRequests = ref([
  {
    id: 1,
    animal: {
      name: 'Max',
      species: 'Perro',
      breed: 'Criollo',
      photoUrl: null,
      microchip: 'MC123456'
    },
    applicant: {
      name: 'María González',
      phone: '318-555-0123',
      email: 'maria@example.com'
    },
    status: 'pending',
    createdAt: '2024-12-01T10:30:00'
  },
  {
    id: 2,
    animal: {
      name: 'Luna',
      species: 'Gato',
      breed: 'Mestizo',
      photoUrl: null,
      microchip: 'MC789012'
    },
    applicant: {
      name: 'Carlos Ramírez',
      phone: '315-555-0456',
      email: 'carlos@example.com'
    },
    status: 'in_evaluation',
    createdAt: '2024-11-30T14:20:00'
  }
]);

const upcomingFollowups = ref([
  {
    id: 1,
    animal: {
      name: 'Rocky',
      species: 'Perro',
      microchip: 'MC456789'
    },
    adopter: {
      name: 'Ana Martínez',
      phone: '317-555-0789'
    },
    scheduledDate: '2024-12-03T10:00:00',
    type: 'first_followup'
  },
  {
    id: 2,
    animal: {
      name: 'Mishi',
      species: 'Gato',
      microchip: 'MC321654'
    },
    adopter: {
      name: 'Pedro López',
      phone: '319-555-0321'
    },
    scheduledDate: '2024-12-02T15:00:00',
    type: 'second_followup'
  }
]);

function getStatusLabel(status) {
  const labels = {
    pending: 'Pendiente',
    in_evaluation: 'En evaluación',
    approved: 'Aprobada',
    rejected: 'Rechazada'
  };
  return labels[status] || status;
}

function formatDate(dateString) {
  const date = new Date(dateString);
  const day = date.getDate().toString().padStart(2, '0');
  const month = (date.getMonth() + 1).toString().padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}

function formatDay(dateString) {
  const date = new Date(dateString);
  const today = new Date();
  const tomorrow = new Date(today);
  tomorrow.setDate(tomorrow.getDate() + 1);

  if (date.toDateString() === today.toDateString()) return 'Hoy';
  if (date.toDateString() === tomorrow.toDateString()) return 'Mañana';
  
  return formatDate(dateString);
}

function formatTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
}

function isOverdue(dateString) {
  return new Date(dateString) < new Date();
}

function goToRequests() {
  router.push('/adopciones/solicitudes');
}

function goToFollowups() {
  router.push('/adopciones/seguimientos');
}

function goToContracts() {
  router.push('/adopciones/contratos');
}

function goToReturns() {
  router.push('/adopciones/devoluciones');
}

function goToCatalog() {
  router.push('/adopciones/catalogo');
}

function viewRequest(request) {
  router.push(`/adopciones/solicitudes/${request.id}`);
}

function performFollowup(followup) {
  router.push(`/adopciones/seguimientos/${followup.id}`);
}

onMounted(() => {
  // Aquí cargarías datos reales desde la API
  console.log('Dashboard cargado');
});
</script>

<style scoped>
.dashboard-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
  background: #f5f7fb;
}

.dashboard-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #3366CC;
}

/* KPIs Grid */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.kpi-card {
  background: #F6F8F9;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  transition: transform 0.2s;
}

.kpi-card:hover {
  transform: translateY(-4px);
}

.kpi-icon {
  font-size: 2.5rem;
}

.kpi-content {
  flex: 1;
}

.kpi-number {
  font-size: 2rem;
  font-weight: 700;
  color: #3366CC;
  margin: 0;
}

.kpi-label {
  margin: 0.25rem 0 0 0;
  color: #4B4B4B;
  font-size: 0.9rem;
}

/* Acciones Rápidas */
.quick-actions {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.section-title {
  margin: 0 0 1rem 0;
  color: #3366CC;
}

.action-buttons {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  border: none;
  border-radius: 8px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  position: relative;
}

.action-btn:hover {
  transform: translateY(-2px);
  opacity: 0.9;
}

.action-icon {
  font-size: 1.5rem;
}

.badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #A80521;
  color: white;
  border-radius: 12px;
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  font-weight: 700;
}

.badge-warning {
  background: #FFAB00;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

/* Solicitudes Recientes */
.recent-requests, .upcoming-followups {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.link-btn {
  background: none;
  border: none;
  color: #3366CC;
  font-weight: 600;
  cursor: pointer;
  transition: color 0.2s;
}

.link-btn:hover {
  color: #004884;
  text-decoration: underline;
}

.requests-list, .followups-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.request-item, .followup-item {
  display: grid;
  grid-template-columns: 2fr 2fr 1.5fr auto;
  gap: 1rem;
  align-items: center;
  padding: 1rem;
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.request-item:hover, .followup-item:hover {
  background: #F6F8F9;
  border-color: #3366CC;
}

.request-animal {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.request-animal img {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  background: linear-gradient(135deg, #3366cc 0%, #004884 100%);
}

.request-meta, .followup-meta {
  margin: 0.25rem 0 0 0;
  color: #737373;
  font-size: 0.85rem;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
  color: white;
}

.status-pending {
  background: #FFAB00;
}

.status-in_evaluation {
  background: #3366CC;
}

.status-approved {
  background: #068460;
}

.status-rejected {
  background: #A80521;
}

.view-btn, .perform-btn {
  padding: 0.5rem 1.5rem;
  background: #3366CC;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.view-btn:hover, .perform-btn:hover {
  background: #004884;
}

/* Seguimientos */
.followup-item {
  grid-template-columns: auto 2fr 2fr auto;
}

.followup-item.overdue {
  border-color: #A80521;
  background: #FFF5F5;
}

.followup-date {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.date-icon {
  font-size: 2rem;
}

.overdue-icon {
  animation: shake 0.5s infinite;
}

@keyframes shake {
  0%, 100% { transform: rotate(0deg); }
  25% { transform: rotate(-10deg); }
  75% { transform: rotate(10deg); }
}

.followup-day {
  margin: 0;
  font-weight: 700;
  color: #3366CC;
}

.followup-time {
  margin: 0.25rem 0 0 0;
  font-size: 0.85rem;
  color: #737373;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: #737373;
}

/* Gov.co Colors */
.govco-bg-marine {
  background-color: #3366cc;
}

.govco-bg-gold {
  background-color: #FFAB00;
}

.govco-bg-elf-green {
  background-color: #068460;
}

.govco-bg-shiraz {
  background-color: #A80521;
}

.govco-bg-blue-light {
  background-color: #c9e2ff;
  color: #004884;
}

.govco-bg-hawkes-blue {
  background-color: #F6F8F9;
}

@media (max-width: 992px) {
  .request-item, .followup-item {
    grid-template-columns: 1fr;
  }

  .action-buttons {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 576px) {
  .dashboard-container {
    padding: 1rem;
  }

  .kpi-grid {
    grid-template-columns: 1fr;
  }
}
</style>