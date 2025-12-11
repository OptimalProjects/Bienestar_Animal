<!-- src/components/admin/ExecutiveDashboard.vue -->
<!-- HU-020: Visualizar Dashboard Ejecutivo con KPIs -->
<template>
  <section class="executive-dashboard">
    <!-- Loading state -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner">Cargando dashboard...</div>
    </div>

    <!-- Error state -->
    <div v-if="error" class="error-banner">
      {{ error }}
      <button @click="loadDashboard" class="retry-btn">Reintentar</button>
    </div>

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
            <div v-for="especie in speciesData" :key="especie.label" class="h-bar-row">
              <span class="h-bar-label">{{ especie.label }}</span>
              <div class="h-bar-container">
                <div class="h-bar-fill" :style="{ width: especie.porcentaje + '%', background: especie.color }"></div>
              </div>
              <span class="h-bar-value">{{ especie.cantidad.toLocaleString() }}</span>
            </div>
            <div v-if="speciesData.length === 0" class="no-data">
              Sin datos de especies
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- DENUNCIAS RECIENTES Y ALERTAS -->
    <div class="bottom-grid">
      <!-- Denuncias Recientes -->
      <div class="recent-section">
        <h3 class="h5-tipografia-govco">Denuncias Recientes</h3>
        <div class="recent-list">
          <div v-if="denunciasRecientes.length === 0" class="no-data">
            No hay denuncias recientes
          </div>
          <div v-for="denuncia in denunciasRecientes" :key="denuncia.id" class="recent-item">
            <div class="recent-icon" :class="`priority-${denuncia.prioridad || 'media'}`">
              {{ getDenunciaIcon(denuncia.tipo_denuncia) }}
            </div>
            <div class="recent-content">
              <span class="recent-title">{{ formatTipoDenuncia(denuncia.tipo_denuncia) }}</span>
              <span class="recent-subtitle">{{ denuncia.direccion || 'Sin dirección' }}</span>
            </div>
            <div class="recent-meta">
              <span class="recent-status" :class="`status-${denuncia.estado}`">
                {{ formatEstadoDenuncia(denuncia.estado) }}
              </span>
              <span class="recent-date">{{ formatDate(denuncia.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Alertas del Sistema -->
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
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import reportService from '@/services/reportService';

const dateRange = ref('month');
const customStart = ref('');
const customEnd = ref('');
const lastUpdate = ref('');
const loading = ref(true);
const error = ref(null);

// Auto-refresh cada 60 segundos
let refreshInterval = null;

// Dashboard data from backend
const dashboardData = ref(null);

// Denuncias recientes
const denunciasRecientes = ref([]);

// KPIs computed from backend data
const kpis = computed(() => {
  if (!dashboardData.value) {
    return {
      totalAnimals: 0,
      animalsTrend: 0,
      adoptionsMonth: 0,
      adoptionsTrend: 0,
      activeComplaints: 0,
      criticalComplaints: 0,
      vaccinationRate: 0,
      vaccinationsMonth: 0,
      rescuesMonth: 0,
      sterilizations: 0,
      pendingAdoptions: 0,
      avgResponseTime: 0
    };
  }

  const { animales, adopciones, denuncias, veterinaria } = dashboardData.value;

  // Calcular tasa de vacunación (vacunas del mes / animales en refugio * 100)
  const vaccinationRate = animales.en_refugio > 0
    ? Math.round((veterinaria.vacunas_mes / animales.en_refugio) * 100)
    : 0;

  return {
    totalAnimals: animales.total || 0,
    animalsTrend: animales.ingresos_mes > 0 ? ((animales.ingresos_mes / animales.total) * 100).toFixed(1) : 0,
    adoptionsMonth: adopciones.aprobadas_mes || 0,
    adoptionsTrend: adopciones.tasa_aprobacion || 0,
    activeComplaints: denuncias.pendientes || 0,
    criticalComplaints: denuncias.urgentes || 0,
    vaccinationRate: Math.min(vaccinationRate, 100),
    vaccinationsMonth: veterinaria.vacunas_mes || 0,
    rescuesMonth: animales.ingresos_mes || 0,
    sterilizations: veterinaria.esterilizaciones_mes || 0,
    pendingAdoptions: adopciones.pendientes || 0,
    avgResponseTime: 4.2 // TODO: Calcular desde backend
  };
});

// Datos para graficos - obtenidos de tendencias
const months = computed(() => {
  if (!dashboardData.value?.tendencias?.adopciones) {
    return ['Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
  }
  return dashboardData.value.tendencias.adopciones.map(t => t.mes.split(' ')[0]);
});

const adoptionsData = computed(() => {
  if (!dashboardData.value?.tendencias?.adopciones) {
    return [0, 0, 0, 0, 0, 0];
  }
  const data = dashboardData.value.tendencias.adopciones.map(t => t.cantidad);
  const max = Math.max(...data, 1);
  return data.map(v => (v / max) * 100); // Normalizar a porcentaje para altura de barras
});

// Datos para gráfico de especies
const speciesData = computed(() => {
  if (!dashboardData.value?.animales?.por_especie) {
    return [];
  }
  const especies = dashboardData.value.animales.por_especie;
  const total = Object.values(especies).reduce((a, b) => a + b, 0);

  const colors = {
    perro: '#3366CC',
    gato: '#068460',
    equino: '#FFAB00',
    otro: '#737373'
  };

  return Object.entries(especies).map(([especie, cantidad]) => ({
    label: especie.charAt(0).toUpperCase() + especie.slice(1),
    cantidad,
    porcentaje: total > 0 ? Math.round((cantidad / total) * 100) : 0,
    color: colors[especie] || '#737373'
  })).sort((a, b) => b.cantidad - a.cantidad);
});

// Datos para gráfico de denuncias
const complaintsData = computed(() => {
  if (!dashboardData.value?.denuncias?.por_tipo) {
    return [];
  }
  const tipos = dashboardData.value.denuncias.por_tipo;
  const total = Object.values(tipos).reduce((a, b) => a + b, 0);

  const colors = {
    maltrato: '#A80521',
    abandono: '#FFAB00',
    negligencia: '#3366CC',
    otros: '#068460'
  };

  return Object.entries(tipos).map(([tipo, cantidad]) => ({
    label: tipo.charAt(0).toUpperCase() + tipo.slice(1),
    cantidad,
    porcentaje: total > 0 ? Math.round((cantidad / total) * 100) : 0,
    color: colors[tipo] || '#737373'
  }));
});

// Alertas dinámicas basadas en datos
const alerts = computed(() => {
  const alertsList = [];
  let alertId = 1;

  if (dashboardData.value) {
    const { denuncias, veterinaria, adopciones, animales } = dashboardData.value;

    // Alertas críticas (prioridad más alta)
    if (denuncias?.urgentes > 0) {
      alertsList.push({
        id: alertId++,
        type: 'critical',
        title: `${denuncias.urgentes} denuncia(s) urgente(s)`,
        description: 'Requieren atención inmediata (SLA < 4 horas)',
        time: 'Ahora'
      });
    }

    if (denuncias?.sin_asignar > 0) {
      alertsList.push({
        id: alertId++,
        type: 'critical',
        title: `${denuncias.sin_asignar} denuncia(s) sin asignar`,
        description: 'Requieren asignación de funcionario',
        time: 'Ahora'
      });
    }

    // Alertas de advertencia
    if (adopciones?.pendientes > 5) {
      alertsList.push({
        id: alertId++,
        type: 'warning',
        title: `${adopciones.pendientes} solicitudes de adopción pendientes`,
        description: 'Esperando evaluación',
        time: 'Ahora'
      });
    }

    if (denuncias?.pendientes > 10) {
      alertsList.push({
        id: alertId++,
        type: 'warning',
        title: `${denuncias.pendientes} denuncias pendientes`,
        description: 'Acumulación de casos por resolver',
        time: 'Ahora'
      });
    }

    // Alertas informativas
    if (veterinaria?.consultas_hoy === 0) {
      alertsList.push({
        id: alertId++,
        type: 'info',
        title: 'Sin consultas veterinarias hoy',
        description: 'Día disponible para programar citas',
        time: 'Hoy'
      });
    }

    if (animales?.ingresos_mes > 0) {
      alertsList.push({
        id: alertId++,
        type: 'info',
        title: `${animales.ingresos_mes} nuevo(s) ingreso(s) este mes`,
        description: 'Animales registrados recientemente',
        time: 'Este mes'
      });
    }

    if (veterinaria?.esterilizaciones_mes > 0) {
      alertsList.push({
        id: alertId++,
        type: 'info',
        title: `${veterinaria.esterilizaciones_mes} esterilización(es) realizadas`,
        description: 'Procedimientos completados este mes',
        time: 'Este mes'
      });
    }
  }

  // Si no hay alertas dinámicas, mostrar mensaje informativo
  if (alertsList.length === 0) {
    alertsList.push({
      id: 0,
      type: 'info',
      title: 'Sistema operando normalmente',
      description: 'No hay alertas pendientes',
      time: 'Ahora'
    });
  }

  // Limitar a las 5 alertas más importantes
  return alertsList.slice(0, 5);
});

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

function getDenunciaIcon(tipo) {
  const icons = {
    maltrato: '🔴',
    abandono: '🟠',
    animal_herido: '🏥',
    animal_peligroso: '⚠️',
    negligencia: '⚡',
    otro: '📋'
  };
  return icons[tipo] || '📋';
}

function formatTipoDenuncia(tipo) {
  const labels = {
    maltrato: 'Maltrato Animal',
    abandono: 'Abandono',
    animal_herido: 'Animal Herido',
    animal_peligroso: 'Animal Peligroso',
    negligencia: 'Negligencia',
    otro: 'Otro'
  };
  return labels[tipo] || tipo || 'Sin tipo';
}

function formatEstadoDenuncia(estado) {
  const labels = {
    recibida: 'Recibida',
    en_proceso: 'En proceso',
    en_revision: 'En revisión',
    asignada: 'Asignada',
    en_atencion: 'En atención',
    resuelta: 'Resuelta',
    cerrada: 'Cerrada',
    desestimada: 'Desestimada'
  };
  return labels[estado] || estado || 'Sin estado';
}

function formatDate(dateString) {
  if (!dateString) return '';
  try {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const hours = Math.floor(diff / (1000 * 60 * 60));

    if (hours < 1) return 'Hace menos de 1 hora';
    if (hours < 24) return `Hace ${hours} hora${hours > 1 ? 's' : ''}`;

    const days = Math.floor(hours / 24);
    if (days < 7) return `Hace ${days} día${days > 1 ? 's' : ''}`;

    return date.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' });
  } catch {
    return dateString;
  }
}

function updateLastUpdate() {
  const now = new Date();
  lastUpdate.value = now.toLocaleTimeString('es-CO', {
    hour: '2-digit',
    minute: '2-digit'
  });
}

function exportChart(chartName) {
  console.log(`Exportando gráfico "${chartName}" como PNG...`);
  // TODO: Implementar exportación real con html2canvas
}

async function loadDashboard() {
  loading.value = true;
  error.value = null;
  try {
    // Cargar datos del dashboard y denuncias recientes en paralelo
    const [dashboardResponse, denunciasResponse] = await Promise.allSettled([
      reportService.getDashboard(),
      reportService.getDenunciasRecientes(5)
    ]);

    // Procesar respuesta del dashboard
    if (dashboardResponse.status === 'fulfilled') {
      dashboardData.value = dashboardResponse.value.data || dashboardResponse.value;
    }

    // Procesar denuncias recientes
    if (denunciasResponse.status === 'fulfilled') {
      const responseData = denunciasResponse.value;
      // La respuesta puede ser: { success, data: { data: [...] } } (paginado) o { success, data: [...] } (array directo)
      let denunciasArray = [];

      if (responseData?.data?.data && Array.isArray(responseData.data.data)) {
        // Formato paginado: { data: { data: [...], current_page, ... } }
        denunciasArray = responseData.data.data;
      } else if (Array.isArray(responseData?.data)) {
        // Formato array directo: { data: [...] }
        denunciasArray = responseData.data;
      } else if (Array.isArray(responseData)) {
        // Array directo sin wrapper
        denunciasArray = responseData;
      }

      // Mapear campos del backend al formato esperado por el template
      denunciasRecientes.value = denunciasArray.map(denuncia => ({
        id: denuncia.id,
        tipo_denuncia: denuncia.tipo_denuncia,
        prioridad: denuncia.prioridad,
        estado: denuncia.estado,
        direccion: denuncia.ubicacion || denuncia.direccion,
        created_at: denuncia.created_at || denuncia.fecha_denuncia,
        numero_ticket: denuncia.numero_ticket
      }));
    }

    updateLastUpdate();
  } catch (err) {
    console.error('Error al cargar dashboard:', err);
    error.value = 'Error al cargar los datos del dashboard';
  } finally {
    loading.value = false;
  }
}

async function refreshData() {
  await loadDashboard();
}

onMounted(async () => {
  await loadDashboard();
  // Auto-refresh cada 60 segundos
  refreshInterval = setInterval(refreshData, 60000);
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

/* Bottom Grid - Denuncias y Alertas */
.bottom-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

/* Recent Section (Denuncias Recientes) */
.recent-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.recent-section h3 {
  margin: 0 0 1rem 0;
  color: #004884;
}

.recent-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.recent-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: 8px;
  background: #f9f9f9;
  border-left: 4px solid #D0D0D0;
  transition: all 0.2s;
}

.recent-item:hover {
  background: #f0f0f0;
}

.recent-icon {
  font-size: 1.5rem;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: #f0f0f0;
}

.recent-icon.priority-urgente,
.recent-icon.priority-alta { background: #FFEBEE; }
.recent-icon.priority-media { background: #FFF8E1; }
.recent-icon.priority-baja { background: #E8F5E9; }

.recent-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.recent-title {
  font-weight: 600;
  color: #333;
}

.recent-subtitle {
  font-size: 0.85rem;
  color: #666;
}

.recent-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.25rem;
}

.recent-status {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-weight: 500;
}

.recent-status.status-recibida,
.recent-status.status-en_proceso { background: #FFF8E1; color: #F57C00; }
.recent-status.status-asignada,
.recent-status.status-en_atencion { background: #E8F0FE; color: #3366CC; }
.recent-status.status-resuelta,
.recent-status.status-cerrada { background: #E8F5E9; color: #2E7D32; }
.recent-status.status-desestimada { background: #f0f0f0; color: #666; }

.recent-date {
  font-size: 0.75rem;
  color: #999;
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

  .bottom-grid {
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

/* Loading and Error states */
.loading-overlay {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 2rem;
  background: rgba(255, 255, 255, 0.8);
  margin-bottom: 1rem;
  border-radius: 8px;
}

.loading-spinner {
  font-size: 1.2rem;
  color: #004884;
}

.error-banner {
  background: #f8d7da;
  color: #842029;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.retry-btn {
  padding: 0.5rem 1rem;
  background: #842029;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.retry-btn:hover {
  background: #6a1a23;
}

.no-data {
  text-align: center;
  color: #666;
  padding: 2rem;
  font-style: italic;
}
</style>
