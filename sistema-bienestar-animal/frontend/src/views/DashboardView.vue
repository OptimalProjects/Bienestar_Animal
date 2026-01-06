<!-- src/views/DashboardView.vue -->
<!-- Panel principal del sistema con diseño GOV.CO -->
<template>
  <div class="dashboard-view">
    <div class="dashboard-container">
      <!-- Header del dashboard -->
      <header class="dashboard-header">
        <div class="header-content">
          <h1 class="h2-tipografia-govco govcolor-blue-dark">Panel Principal</h1>
          <p class="text2-tipografia-govco">
            Bienvenido, {{ userName }}. Resumen general del sistema de bienestar animal.
          </p>
        </div>
        <div class="header-actions">
          <span class="last-update text3-tipografia-govco">
            Última actualización: {{ lastUpdate }}
          </span>
        </div>
      </header>

      <!-- KPIs principales -->
      <section class="kpi-section">
        <div class="kpi-grid">
          <div class="kpi-card kpi-primary">
            <div class="kpi-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10 5.172C10 3.782 8.423 2.679 6.5 3c-2.823.47-4.113 6.006-4 7 .137 1.217.677 1.146 2 1.5.67.178 1.5.5 2.5.5s1.7-.322 2-1c.247-.558.212-1.828 1-2.5.786-.672 0-2.328 0-3.328z"></path>
                <path d="M14 5.172C14 3.782 15.577 2.679 17.5 3c2.823.47 4.113 6.006 4 7-.137 1.217-.677 1.146-2 1.5-.67.178-1.5.5-2.5.5s-1.7-.322-2-1c-.247-.558-.212-1.828-1-2.5-.786-.672 0-2.328 0-3.328z"></path>
                <path d="M4.42 11.247A13.152 13.152 0 0 0 4 14.556C4 18.728 7.582 21 12 21s8-2.272 8-6.444c0-1.061-.162-2.2-.493-3.309"></path>
              </svg>
            </div>
            <div class="kpi-content">
              <span class="kpi-value">{{ kpis.totalAnimals }}</span>
              <span class="kpi-label">Animales Registrados</span>
            </div>
          </div>

          <div class="kpi-card kpi-success">
            <div class="kpi-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
              </svg>
            </div>
            <div class="kpi-content">
              <span class="kpi-value">{{ kpis.adoptions }}</span>
              <span class="kpi-label">Adopciones del Mes</span>
            </div>
          </div>

          <div class="kpi-card kpi-warning">
            <div class="kpi-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
              </svg>
            </div>
            <div class="kpi-content">
              <span class="kpi-value">{{ kpis.pendingComplaints }}</span>
              <span class="kpi-label">Denuncias Pendientes</span>
            </div>
          </div>

          <div class="kpi-card kpi-info">
            <div class="kpi-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
              </svg>
            </div>
            <div class="kpi-content">
              <span class="kpi-value">{{ kpis.sterilizations }}</span>
              <span class="kpi-label">Esterilizaciones</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Contenido principal -->
      <div class="dashboard-content">
        <!-- Acciones rápidas -->
        <section class="quick-actions-section card-govco">
          <div class="card-govco-header">
            <h3 class="h5-tipografia-govco">Acciones Rápidas</h3>
          </div>
          <div class="card-govco-body">
            <div class="quick-actions-grid">
              <router-link to="/animales" class="quick-action-item">
                <div class="quick-action-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                  </svg>
                </div>
                <span>Registrar Animal</span>
              </router-link>

              <router-link to="/denuncias" class="quick-action-item">
                <div class="quick-action-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                  </svg>
                </div>
                <span>Ver Denuncias</span>
              </router-link>

              <router-link to="/veterinaria" class="quick-action-item">
                <div class="quick-action-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                  </svg>
                </div>
                <span>Atención Veterinaria</span>
              </router-link>

              <router-link to="/adopciones" class="quick-action-item">
                <div class="quick-action-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                  </svg>
                </div>
                <span>Gestionar Adopciones</span>
              </router-link>
            </div>
          </div>
        </section>

        <!-- Denuncias recientes -->
        <section class="recent-complaints-section card-govco">
          <div class="card-govco-header">
            <h3 class="h5-tipografia-govco">Denuncias Recientes</h3>
            <router-link to="/denuncias" class="see-all-link">Ver todas</router-link>
          </div>
          <div class="card-govco-body">
            <div class="complaints-list">
              <div
                v-for="complaint in recentComplaints"
                :key="complaint.id"
                class="complaint-item"
              >
                <div class="complaint-status" :class="getStatusClass(complaint.urgency)"></div>
                <div class="complaint-info">
                  <span class="complaint-number">{{ complaint.caseNumber }}</span>
                  <span class="complaint-type">{{ complaint.type }}</span>
                </div>
                <div class="complaint-date">{{ complaint.date }}</div>
                <span class="badge-govco" :class="getBadgeClass(complaint.status)">
                  {{ complaint.status }}
                </span>
              </div>
            </div>
          </div>
        </section>

        <!-- Alertas del sistema -->
        <section class="alerts-section card-govco">
          <div class="card-govco-header">
            <h3 class="h5-tipografia-govco">Alertas del Sistema</h3>
          </div>
          <div class="card-govco-body">
            <div class="alerts-list">
              <div v-if="alertas.urgentes > 0" class="alert-govco alert-govco-danger">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                  <line x1="12" y1="9" x2="12" y2="13"></line>
                  <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <div class="alert-content">
                  <strong>{{ alertas.urgentes }} denuncias urgentes sin asignar</strong>
                  <span>Requieren atención inmediata</span>
                </div>
              </div>

              <div v-if="alertas.pendientes > 0" class="alert-govco alert-govco-warning">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <line x1="12" y1="8" x2="12" y2="12"></line>
                  <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div class="alert-content">
                  <strong>{{ alertas.pendientes }} denuncias pendientes de atención</strong>
                  <span>Revisar y dar seguimiento</span>
                </div>
              </div>

              <div v-if="alertas.adopcionesPendientes > 0" class="alert-govco alert-govco-info">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <line x1="12" y1="16" x2="12" y2="12"></line>
                  <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <div class="alert-content">
                  <strong>{{ alertas.adopcionesPendientes }} solicitudes de adopción pendientes</strong>
                  <span>Por evaluar y aprobar</span>
                </div>
              </div>

              <div v-if="alertas.urgentes === 0 && alertas.pendientes === 0 && alertas.adopcionesPendientes === 0" class="alert-govco alert-govco-success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                  <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <div class="alert-content">
                  <strong>Sistema al día</strong>
                  <span>No hay alertas pendientes</span>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useAnimalsStore } from '@/stores/animals';
import { useComplaintsStore } from '@/stores/complaints';
import { useVeterinaryStore } from '@/stores/veterinary';
import { useAdoptionsStore } from '@/stores/adoptions';

// Stores
const authStore = useAuthStore();
const animalsStore = useAnimalsStore();
const complaintsStore = useComplaintsStore();
const veterinaryStore = useVeterinaryStore();
const adoptionsStore = useAdoptionsStore();

// Estado de carga
const loading = ref(true);

// Datos del usuario desde auth store
const userName = computed(() => authStore.userName || 'Usuario');

// KPIs - Datos reales de los stores
const kpis = computed(() => ({
  totalAnimals: animalsStore.statistics?.total || animalsStore.totalAnimales || 0,
  adoptions: adoptionsStore.estadisticas?.aprobadas_mes || 0,
  pendingComplaints: complaintsStore.estadisticas?.total_pendientes || complaintsStore.totalPendientes || 0,
  sterilizations: veterinaryStore.estadisticas?.consultas_mes || 0
}));

// Alertas del sistema - Datos reales
const alertas = computed(() => ({
  urgentes: complaintsStore.estadisticas?.urgentes_sin_asignar || 0,
  pendientes: complaintsStore.estadisticas?.total_pendientes || 0,
  adopcionesPendientes: adoptionsStore.estadisticas?.pendientes || 0
}));

// Última actualización
const lastUpdate = computed(() => {
  return new Date().toLocaleString('es-CO', {
    dateStyle: 'medium',
    timeStyle: 'short'
  });
});

// Denuncias recientes - Datos reales del store
const recentComplaints = computed(() => {
  return complaintsStore.denuncias.slice(0, 4).map(d => ({
    id: d.id,
    caseNumber: d.numero_ticket || `DEN-${d.id?.substring(0, 8)}`,
    type: d.tipo_denuncia || 'Sin clasificar',
    date: formatDate(d.fecha_denuncia || d.created_at),
    status: getStatusLabel(d.estado),
    urgency: getPriorityLevel(d.prioridad)
  }));
});

// Cargar datos al montar el componente
onMounted(async () => {
  loading.value = true;
  try {
    // Cargar datos en paralelo
    await Promise.all([
      animalsStore.fetchAnimals({ per_page: 1 }), // Solo para obtener total
      animalsStore.fetchStatistics().catch(() => {}),
      complaintsStore.fetchDenuncias({ per_page: 5 }),
      complaintsStore.fetchEstadisticas().catch(() => {}),
      veterinaryStore.fetchEstadisticas().catch(() => {}),
      adoptionsStore.fetchEstadisticas().catch(() => {}),
    ]);
  } catch (error) {
    console.error('Error cargando datos del dashboard:', error);
  } finally {
    loading.value = false;
  }
});

// Funciones de utilidad
function formatDate(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('es-CO', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
}

function getStatusLabel(estado) {
  const labels = {
    recibida: 'Pendiente',
    en_proceso: 'En proceso',
    resuelta: 'Resuelto',
    cerrada: 'Cerrado',
    archivada: 'Archivado'
  };
  return labels[estado] || estado || 'Pendiente';
}

function getPriorityLevel(prioridad) {
  const levels = {
    urgente: 'critical',
    alta: 'high',
    media: 'medium',
    baja: 'low'
  };
  return levels[prioridad] || 'medium';
}

function getStatusClass(urgency) {
  const classes = {
    critical: 'status-critical',
    high: 'status-high',
    medium: 'status-medium',
    low: 'status-low'
  };
  return classes[urgency] || 'status-medium';
}

function getBadgeClass(status) {
  const classes = {
    'Pendiente': 'badge-govco-danger',
    'En proceso': 'badge-govco-warning',
    'Resuelto': 'badge-govco-success',
    'Cerrado': 'badge-govco-secondary',
    'Archivado': 'badge-govco-secondary'
  };
  return classes[status] || 'badge-govco-primary';
}
</script>

<style scoped>
.dashboard-view {
  padding: 24px;
  background: #f5f7fb;
  min-height: 100%;
}

.dashboard-container {
  max-width: 1400px;
  margin: 0 auto;
}

/* Header */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}

.header-content h1 {
  margin: 0 0 4px 0;
}

.header-content p {
  margin: 0;
}

.last-update {
  color: #666;
}

/* KPI Section */
.kpi-section {
  margin-bottom: 24px;
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.kpi-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border-left: 4px solid;
}

.kpi-primary {
  border-color: #3366CC;
}

.kpi-success {
  border-color: #069169;
}

.kpi-warning {
  border-color: #FFAB00;
}

.kpi-info {
  border-color: #17A2B8;
}

.kpi-icon {
  width: 56px;
  height: 56px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.kpi-primary .kpi-icon {
  background: #E8F0FE;
  color: #3366CC;
}

.kpi-success .kpi-icon {
  background: #E8F5E9;
  color: #069169;
}

.kpi-warning .kpi-icon {
  background: #FFF8E1;
  color: #F57C00;
}

.kpi-info .kpi-icon {
  background: #E3F2FD;
  color: #17A2B8;
}

.kpi-content {
  margin-bottom: 12px;
}

.kpi-value {
  display: block;
  font-size: 2rem;
  font-weight: 700;
  color: #333;
  line-height: 1;
}

.kpi-label {
  display: block;
  font-size: 0.9rem;
  color: #666;
  margin-top: 4px;
}

.kpi-trend {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.85rem;
  font-weight: 500;
}

.kpi-trend-up {
  color: #069169;
}

.kpi-trend-down {
  color: #069169;
}

/* Dashboard Content */
.dashboard-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

/* Quick Actions */
.quick-actions-section {
  grid-column: 1 / -1;
}

.quick-actions-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

.quick-action-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 20px;
  border-radius: 8px;
  background: #f5f7fb;
  text-decoration: none;
  color: #333;
  transition: all 0.2s;
}

.quick-action-item:hover {
  background: #E8F0FE;
  transform: translateY(-2px);
}

.quick-action-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #3366CC;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.quick-action-item span {
  font-size: 0.9rem;
  font-weight: 500;
}

/* Recent Complaints */
.card-govco-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.see-all-link {
  color: #3366CC;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
}

.see-all-link:hover {
  text-decoration: underline;
}

.complaints-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.complaint-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #f5f7fb;
  border-radius: 6px;
}

.complaint-status {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-critical {
  background: #A80521;
}

.status-high {
  background: #FFAB00;
}

.status-medium {
  background: #3366CC;
}

.status-low {
  background: #069169;
}

.complaint-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.complaint-number {
  font-weight: 600;
  color: #333;
  font-size: 0.9rem;
}

.complaint-type {
  font-size: 0.85rem;
  color: #666;
}

.complaint-date {
  font-size: 0.85rem;
  color: #666;
}

/* Alerts */
.alerts-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.alert-govco {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.alert-content {
  display: flex;
  flex-direction: column;
}

.alert-content strong {
  font-size: 0.9rem;
}

.alert-content span {
  font-size: 0.85rem;
  opacity: 0.85;
}

.alert-govco-success {
  background: #E8F5E9;
  border: 1px solid #A5D6A7;
  padding: 12px 16px;
  border-radius: 8px;
  color: #2E7D32;
}

.alert-govco-success svg {
  color: #2E7D32;
}

.alert-govco-danger {
  background: #FFEBEE;
  border: 1px solid #FFCDD2;
  padding: 12px 16px;
  border-radius: 8px;
  color: #C62828;
}

.alert-govco-danger svg {
  color: #C62828;
}

.alert-govco-warning {
  background: #FFF8E1;
  border: 1px solid #FFE082;
  padding: 12px 16px;
  border-radius: 8px;
  color: #F57C00;
}

.alert-govco-warning svg {
  color: #F57C00;
}

.alert-govco-info {
  background: #E3F2FD;
  border: 1px solid #90CAF9;
  padding: 12px 16px;
  border-radius: 8px;
  color: #1565C0;
}

.alert-govco-info svg {
  color: #1565C0;
}

/* Responsive */
@media (max-width: 1199.98px) {
  .kpi-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .quick-actions-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 991.98px) {
  .dashboard-content {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 575.98px) {
  .dashboard-view {
    padding: 16px;
  }

  .kpi-grid {
    grid-template-columns: 1fr;
  }

  .quick-actions-grid {
    grid-template-columns: 1fr;
  }

  .kpi-value {
    font-size: 1.75rem;
  }
}
</style>
