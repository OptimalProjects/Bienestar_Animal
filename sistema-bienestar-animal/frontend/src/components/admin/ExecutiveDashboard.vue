<!-- src/components/admin/ExecutiveDashboard.vue -->
<!-- HU-020: Visualizar Dashboard Ejecutivo con KPIs -->
<template>
  <section class="executive-dashboard">
    <div class="dashboard-header">
      <div class="header-left">
        <h2 class="h3-tipografia-govco">Dashboard Ejecutivo</h2>
        <p class="text2-tipografia-govco">
          Indicadores clave en tiempo real del Sistema de Bienestar Animal
        </p>
      </div>
      <div class="header-right">
        <!-- Filtro de fechas -->
        <div class="date-filter">
          <label>Periodo:</label>
          <select v-model="dateRange" class="date-select">
            <option value="today">Hoy</option>
            <option value="week">Esta semana</option>
            <option value="month">Este mes</option>
            <option value="quarter">Este trimestre</option>
            <option value="year">Este año</option>
            <option value="custom">Personalizado</option>
          </select>
        </div>
        <div v-if="dateRange === 'custom'" class="custom-dates">
          <input type="date" v-model="customStart" />
          <span>a</span>
          <input type="date" v-model="customEnd" />
        </div>
        <!-- Ultima actualizacion -->
        <div class="last-update">
          <span class="update-icon">🔄</span>
          <span>Actualizado: {{ lastUpdate }}</span>
        </div>
      </div>
    </div>

    <!-- KPIs PRINCIPALES -->
    <div class="kpis-grid">
      <div class="kpi-card kpi-animals">
        <div class="kpi-icon">🐕</div>
        <div class="kpi-content">
          <span class="kpi-value">{{ kpis.totalAnimals.toLocaleString() }}</span>
          <span class="kpi-label">Animales Registrados</span>
          <span class="kpi-trend" :class="getTrendClass(kpis.animalsTrend)">
            {{ formatTrend(kpis.animalsTrend) }} vs mes anterior
          </span>
        </div>
      </div>

      <div class="kpi-card kpi-adoptions">
        <div class="kpi-icon">🏠</div>
        <div class="kpi-content">
          <span class="kpi-value">{{ kpis.adoptionsMonth }}</span>
          <span class="kpi-label">Adopciones del Mes</span>
          <span class="kpi-trend" :class="getTrendClass(kpis.adoptionsTrend)">
            {{ formatTrend(kpis.adoptionsTrend) }} vs mes anterior
          </span>
        </div>
      </div>

      <div class="kpi-card kpi-complaints">
        <div class="kpi-icon">⚠️</div>
        <div class="kpi-content">
          <span class="kpi-value">{{ kpis.activeComplaints }}</span>
          <span class="kpi-label">Denuncias Activas</span>
          <span class="kpi-subtitle">{{ kpis.criticalComplaints }} criticas</span>
        </div>
      </div>

      <div class="kpi-card kpi-vaccinations">
        <div class="kpi-icon">💉</div>
        <div class="kpi-content">
          <span class="kpi-value">{{ kpis.vaccinationRate }}%</span>
          <span class="kpi-label">Cobertura Vacunación</span>
          <span class="kpi-subtitle">{{ kpis.vaccinationsMonth }} este mes</span>
        </div>
      </div>
    </div>

    <!-- KPIs SECUNDARIOS -->
    <div class="secondary-kpis">
      <div class="secondary-kpi">
        <span class="secondary-value">{{ kpis.rescuesMonth }}</span>
        <span class="secondary-label">Rescates del mes</span>
      </div>
      <div class="secondary-kpi">
        <span class="secondary-value">{{ kpis.sterilizations }}</span>
        <span class="secondary-label">Esterilizaciones</span>
      </div>
      <div class="secondary-kpi">
        <span class="secondary-value">{{ kpis.pendingAdoptions }}</span>
        <span class="secondary-label">Adopciones pendientes</span>
      </div>
      <div class="secondary-kpi">
        <span class="secondary-value">{{ kpis.avgResponseTime }}h</span>
        <span class="secondary-label">Tiempo resp. promedio</span>
      </div>
    </div>

    <!-- GRAFICOS -->
    <div class="charts-grid">
      <!-- Grafico: Tendencia de Adopciones -->
      <div class="chart-card">
        <div class="chart-header">
          <h3 class="h5-tipografia-govco">Tendencia de Adopciones</h3>
          <button type="button" class="export-btn" @click="exportChart('adoptions')">
            📥 PNG
          </button>
        </div>
        <div class="chart-container" ref="adoptionsChart">
          <div class="mock-chart line-chart">
            <div class="chart-bars">
              <div v-for="(value, index) in adoptionsData" :key="index" class="chart-bar-container">
                <div class="chart-bar" :style="{ height: `${value}%` }"></div>
                <span class="chart-label">{{ months[index] }}</span>
              </div>
            </div>
            <div class="chart-legend">
              <span class="legend-item"><span class="legend-dot adoptions"></span> Adopciones</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Grafico: Casos por Tipo -->
      <div class="chart-card">
        <div class="chart-header">
          <h3 class="h5-tipografia-govco">Denuncias por Tipo</h3>
          <button type="button" class="export-btn" @click="exportChart('complaints')">
            📥 PNG
          </button>
        </div>
        <div class="chart-container" ref="complaintsChart">
          <div class="mock-chart pie-chart">
            <div class="pie-container">
              <div class="pie-slice" style="--percentage: 35; --color: #A80521; --offset: 0"></div>
              <div class="pie-slice" style="--percentage: 25; --color: #FFAB00; --offset: 35"></div>
              <div class="pie-slice" style="--percentage: 20; --color: #3366CC; --offset: 60"></div>
              <div class="pie-slice" style="--percentage: 20; --color: #068460; --offset: 80"></div>
            </div>
            <div class="pie-legend">
              <div class="legend-row"><span class="dot" style="background:#A80521"></span> Maltrato (35%)</div>
              <div class="legend-row"><span class="dot" style="background:#FFAB00"></span> Abandono (25%)</div>
              <div class="legend-row"><span class="dot" style="background:#3366CC"></span> Negligencia (20%)</div>
              <div class="legend-row"><span class="dot" style="background:#068460"></span> Otros (20%)</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Grafico: Cobertura de Vacunacion -->
      <div class="chart-card">
        <div class="chart-header">
          <h3 class="h5-tipografia-govco">Cobertura de Vacunacion</h3>
          <button type="button" class="export-btn" @click="exportChart('vaccination')">
            📥 PNG
          </button>
        </div>
        <div class="chart-container" ref="vaccinationChart">
          <div class="mock-chart gauge-chart">
            <div class="gauge-container">
              <div class="gauge-bg"></div>
              <div class="gauge-fill" :style="{ '--fill': kpis.vaccinationRate }"></div>
              <div class="gauge-center">
                <span class="gauge-value">{{ kpis.vaccinationRate }}%</span>
                <span class="gauge-label">Cobertura</span>
              </div>
            </div>
            <div class="gauge-info">
              <div class="info-row">
                <span>Rabia:</span>
                <div class="progress-bar"><div class="progress-fill" style="width: 92%"></div></div>
                <span>92%</span>
              </div>
              <div class="info-row">
                <span>Triple:</span>
                <div class="progress-bar"><div class="progress-fill" style="width: 78%"></div></div>
                <span>78%</span>
              </div>
              <div class="info-row">
                <span>Parvovirus:</span>
                <div class="progress-bar"><div class="progress-fill" style="width: 85%"></div></div>
                <span>85%</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Grafico: Animales por Especie -->
      <div class="chart-card">
        <div class="chart-header">
          <h3 class="h5-tipografia-govco">Animales por Especie</h3>
          <button type="button" class="export-btn" @click="exportChart('species')">
            📥 PNG
          </button>
        </div>
        <div class="chart-container" ref="speciesChart">
          <div class="mock-chart horizontal-bar">
            <div class="h-bar-row">
              <span class="h-bar-label">Perros</span>
              <div class="h-bar-container">
                <div class="h-bar-fill" style="width: 65%; background: #3366CC"></div>
              </div>
              <span class="h-bar-value">1,250</span>
            </div>
            <div class="h-bar-row">
              <span class="h-bar-label">Gatos</span>
              <div class="h-bar-container">
                <div class="h-bar-fill" style="width: 45%; background: #068460"></div>
              </div>
              <span class="h-bar-value">867</span>
            </div>
            <div class="h-bar-row">
              <span class="h-bar-label">Equinos</span>
              <div class="h-bar-container">
                <div class="h-bar-fill" style="width: 15%; background: #FFAB00"></div>
              </div>
              <span class="h-bar-value">145</span>
            </div>
            <div class="h-bar-row">
              <span class="h-bar-label">Otros</span>
              <div class="h-bar-container">
                <div class="h-bar-fill" style="width: 8%; background: #737373"></div>
              </div>
              <span class="h-bar-value">62</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ALERTAS Y NOTIFICACIONES -->
    <div class="alerts-section">
      <h3 class="h5-tipografia-govco">Alertas del Sistema</h3>
      <div class="alerts-list">
        <div v-for="alert in alerts" :key="alert.id" class="alert-item" :class="`alert-${alert.type}`">
          <span class="alert-icon">{{ getAlertIcon(alert.type) }}</span>
          <div class="alert-content">
            <span class="alert-title">{{ alert.title }}</span>
            <span class="alert-description">{{ alert.description }}</span>
          </div>
          <span class="alert-time">{{ alert.time }}</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const dateRange = ref('month');
const customStart = ref('');
const customEnd = ref('');
const lastUpdate = ref('');

// Auto-refresh cada 30 segundos
let refreshInterval = null;

// KPIs
const kpis = ref({
  totalAnimals: 2324,
  animalsTrend: 8.5,
  adoptionsMonth: 47,
  adoptionsTrend: 12.3,
  activeComplaints: 23,
  criticalComplaints: 5,
  vaccinationRate: 85,
  vaccinationsMonth: 312,
  rescuesMonth: 18,
  sterilizations: 89,
  pendingAdoptions: 156,
  avgResponseTime: 4.2
});

// Datos para graficos
const months = ['Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
const adoptionsData = ref([45, 52, 38, 61, 55, 70]);

// Alertas
const alerts = ref([
  {
    id: 1,
    type: 'critical',
    title: '5 denuncias criticas sin asignar',
    description: 'Requieren atención inmediata',
    time: 'Hace 15 min'
  },
  {
    id: 2,
    type: 'warning',
    title: 'Stock bajo de vacuna antirrabica',
    description: 'Quedan 45 dosis disponibles',
    time: 'Hace 2 horas'
  },
  {
    id: 3,
    type: 'info',
    title: 'Campaña de vacunación programada',
    description: 'Barrio El Centro - Sabado 30 Nov',
    time: 'Hace 1 dia'
  }
]);

// Helpers
function getTrendClass(trend) {
  return trend >= 0 ? 'trend-up' : 'trend-down';
}

function formatTrend(trend) {
  const sign = trend >= 0 ? '+' : '';
  return `${sign}${trend}%`;
}

function getAlertIcon(type) {
  const icons = {
    critical: '🚨',
    warning: '⚠️',
    info: 'ℹ️'
  };
  return icons[type] || 'ℹ️';
}

function updateLastUpdate() {
  const now = new Date();
  lastUpdate.value = now.toLocaleTimeString('es-CO', {
    hour: '2-digit',
    minute: '2-digit'
  });
}

function exportChart(chartName) {
  // Simular exportacion
  alert(`Exportando grafico "${chartName}" como PNG...`);
}

function refreshData() {
  // Simular actualizacion de datos
  kpis.value.activeComplaints = Math.floor(Math.random() * 10) + 20;
  kpis.value.adoptionsMonth = Math.floor(Math.random() * 20) + 40;
  updateLastUpdate();
}

onMounted(() => {
  updateLastUpdate();
  // Auto-refresh cada 30 segundos
  refreshInterval = setInterval(refreshData, 30000);
});

onBeforeUnmount(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
});
</script>

<style scoped>
.executive-dashboard {
  max-width: 1400px;
  margin: 0 auto;
  padding: 1.5rem;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.header-left h2 {
  margin: 0 0 0.25rem 0;
  color: #004884;
}

.header-left p {
  margin: 0;
  color: #666;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.date-filter {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.date-filter label {
  font-weight: 500;
  color: #333;
}

.date-select {
  padding: 0.5rem 1rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 0.9rem;
}

.custom-dates {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.custom-dates input {
  padding: 0.5rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
}

.last-update {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #E8F5E9;
  border-radius: 20px;
  font-size: 0.85rem;
  color: #2E7D32;
}

/* KPIs Grid */
.kpis-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1rem;
}

.kpi-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  gap: 1rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  border-left: 4px solid;
}

.kpi-animals { border-color: #3366CC; }
.kpi-adoptions { border-color: #068460; }
.kpi-complaints { border-color: #A80521; }
.kpi-vaccinations { border-color: #FFAB00; }

.kpi-icon {
  font-size: 2.5rem;
}

.kpi-content {
  display: flex;
  flex-direction: column;
}

.kpi-value {
  font-size: 2rem;
  font-weight: bold;
  color: #004884;
  line-height: 1;
}

.kpi-label {
  font-size: 0.9rem;
  color: #666;
  margin-top: 0.25rem;
}

.kpi-trend {
  font-size: 0.8rem;
  margin-top: 0.5rem;
  font-weight: 500;
}

.trend-up { color: #2E7D32; }
.trend-down { color: #A80521; }

.kpi-subtitle {
  font-size: 0.8rem;
  color: #666;
  margin-top: 0.25rem;
}

/* Secondary KPIs */
.secondary-kpis {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.secondary-kpi {
  background: white;
  border-radius: 8px;
  padding: 1rem;
  text-align: center;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}

.secondary-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 600;
  color: #004884;
}

.secondary-label {
  font-size: 0.8rem;
  color: #666;
}

/* Charts Grid */
.charts-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.chart-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: #f9f9f9;
  border-bottom: 1px solid #E0E0E0;
}

.chart-header h3 {
  margin: 0;
  color: #004884;
}

.export-btn {
  padding: 0.4rem 0.8rem;
  background: #E8F0FE;
  border: 1px solid #3366CC;
  border-radius: 4px;
  color: #3366CC;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s;
}

.export-btn:hover {
  background: #3366CC;
  color: white;
}

.chart-container {
  padding: 1.5rem;
  min-height: 250px;
}

/* Mock Charts */
.mock-chart {
  height: 100%;
}

/* Bar Chart */
.chart-bars {
  display: flex;
  justify-content: space-around;
  align-items: flex-end;
  height: 180px;
  padding-bottom: 1rem;
  border-bottom: 1px solid #E0E0E0;
}

.chart-bar-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.chart-bar {
  width: 40px;
  background: linear-gradient(to top, #068460, #0AA67A);
  border-radius: 4px 4px 0 0;
  transition: height 0.3s;
}

.chart-label {
  font-size: 0.8rem;
  color: #666;
}

.chart-legend {
  display: flex;
  justify-content: center;
  margin-top: 1rem;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
}

.legend-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.legend-dot.adoptions { background: #068460; }

/* Pie Chart */
.pie-chart {
  display: flex;
  align-items: center;
  justify-content: space-around;
}

.pie-container {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  background: conic-gradient(
    #A80521 0% 35%,
    #FFAB00 35% 60%,
    #3366CC 60% 80%,
    #068460 80% 100%
  );
  position: relative;
}

.pie-legend {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.legend-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
}

.dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

/* Gauge Chart */
.gauge-chart {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

.gauge-container {
  position: relative;
  width: 150px;
  height: 75px;
  overflow: hidden;
}

.gauge-bg {
  position: absolute;
  width: 150px;
  height: 150px;
  border-radius: 50%;
  background: #E0E0E0;
  clip-path: polygon(0 0, 100% 0, 100% 50%, 0 50%);
}

.gauge-fill {
  position: absolute;
  width: 150px;
  height: 150px;
  border-radius: 50%;
  background: conic-gradient(
    #068460 0%,
    #068460 calc(var(--fill) * 0.5%),
    transparent calc(var(--fill) * 0.5%)
  );
  clip-path: polygon(0 0, 100% 0, 100% 50%, 0 50%);
}

.gauge-center {
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  text-align: center;
}

.gauge-value {
  display: block;
  font-size: 1.5rem;
  font-weight: bold;
  color: #004884;
}

.gauge-label {
  font-size: 0.8rem;
  color: #666;
}

.gauge-info {
  width: 100%;
}

.info-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
  font-size: 0.85rem;
}

.info-row span:first-child {
  width: 70px;
  color: #666;
}

.progress-bar {
  flex: 1;
  height: 8px;
  background: #E0E0E0;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #068460;
  border-radius: 4px;
}

/* Horizontal Bar Chart */
.horizontal-bar {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.h-bar-row {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.h-bar-label {
  width: 70px;
  font-size: 0.9rem;
  color: #333;
}

.h-bar-container {
  flex: 1;
  height: 24px;
  background: #f0f0f0;
  border-radius: 4px;
  overflow: hidden;
}

.h-bar-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.3s;
}

.h-bar-value {
  width: 50px;
  text-align: right;
  font-weight: 600;
  color: #004884;
}

/* Alerts Section */
.alerts-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.alerts-section h3 {
  margin: 0 0 1rem 0;
  color: #004884;
}

.alerts-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.alert-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid;
}

.alert-critical {
  background: #FFEBEE;
  border-color: #A80521;
}

.alert-warning {
  background: #FFF8E1;
  border-color: #FFAB00;
}

.alert-info {
  background: #E8F0FE;
  border-color: #3366CC;
}

.alert-icon {
  font-size: 1.5rem;
}

.alert-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.alert-title {
  font-weight: 600;
  color: #333;
}

.alert-description {
  font-size: 0.85rem;
  color: #666;
}

.alert-time {
  font-size: 0.8rem;
  color: #999;
}

/* Responsive */
@media (max-width: 1200px) {
  .kpis-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .secondary-kpis {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 992px) {
  .charts-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
  }

  .kpis-grid {
    grid-template-columns: 1fr;
  }

  .secondary-kpis {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 576px) {
  .executive-dashboard {
    padding: 1rem;
  }

  .secondary-kpis {
    grid-template-columns: 1fr;
  }

  .pie-chart {
    flex-direction: column;
    gap: 1rem;
  }
}
</style>
