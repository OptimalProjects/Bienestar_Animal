<template>
  <div class="medical-history">
    <!-- Pestañas de navegación -->
    <div class="history-tabs">
      <button 
        v-for="tab in tabs" 
        :key="tab.id"
        @click="activeTab = tab.id"
        :class="['tab-button', { active: activeTab === tab.id }]"
      >
        {{ tab.icon }} {{ tab.label }}
        <span v-if="tab.count !== undefined" class="count-badge">{{ tab.count }}</span>
      </button>
    </div>

    <!-- Estado de carga -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Cargando historial...</p>
    </div>

    <!-- Mensaje de error -->
    <div v-else-if="error" class="error-container">
      <p class="error-message">❌ {{ error }}</p>
      <button @click="loadHistory" class="retry-button">Reintentar</button>
    </div>

    <!-- Contenido de las pestañas -->
    <div v-else class="tab-content">
      
      <!-- RESUMEN -->
      <div v-show="activeTab === 'resumen'" class="tab-panel">
        <div class="summary-grid">
          <div class="summary-card">
            <div class="summary-icon">🩺</div>
            <div class="summary-info">
              <h4>{{ historial.consultas?.length || 0 }}</h4>
              <p>Consultas registradas</p>
            </div>
          </div>
          
          <div class="summary-card">
            <div class="summary-icon">💉</div>
            <div class="summary-info">
              <h4>{{ historial.vacunas?.length || 0 }}</h4>
              <p>Vacunas aplicadas</p>
            </div>
          </div>
          
          <div class="summary-card">
            <div class="summary-icon">🔬</div>
            <div class="summary-info">
              <h4>{{ historial.cirugias?.length || 0 }}</h4>
              <p>Cirugías realizadas</p>
            </div>
          </div>
          
          <div class="summary-card">
            <div class="summary-icon">🧪</div>
            <div class="summary-info">
              <h4>{{ historial.examenes?.length || 0 }}</h4>
              <p>Exámenes de laboratorio</p>
            </div>
          </div>
        </div>

        <!-- Última consulta -->
        <div v-if="lastConsulta" class="last-consult">
          <h4>Última consulta</h4>
          <div class="consult-card clickable" @click="openConsultaModal(lastConsulta)">
            <div class="consult-header">
              <span class="consult-date">📅 {{ formatDate(lastConsulta.fecha_consulta) }}</span>
              <span :class="['consult-type', lastConsulta.tipo_consulta]">
                {{ formatConsultType(lastConsulta.tipo_consulta) }}
              </span>
            </div>
            <p><strong>Motivo:</strong> {{ lastConsulta.motivo_consulta }}</p>
            <p><strong>Diagnóstico:</strong> {{ lastConsulta.diagnostico }}</p>
            <div class="view-more">👁️ Click para ver detalles completos</div>
          </div>
        </div>
      </div>

      <!-- CONSULTAS -->
      <div v-show="activeTab === 'consultas'" class="tab-panel">
        <div v-if="!historial.consultas || historial.consultas.length === 0" class="empty-state">
          <p>📋 No hay consultas registradas</p>
        </div>
        <div v-else class="timeline">
          <div 
            v-for="consulta in historial.consultas" 
            :key="consulta.id" 
            class="timeline-item clickable"
            @click="openConsultaModal(consulta)"
          >
            <div class="timeline-marker"></div>
            <div class="timeline-content">
              <div class="timeline-header">
                <h4>{{ formatDate(consulta.fecha_consulta) }}</h4>
                <span :class="['badge', consulta.tipo_consulta]">
                  {{ formatConsultType(consulta.tipo_consulta) }}
                </span>
              </div>
              
              <div class="timeline-body">
                <p><strong>Motivo:</strong> {{ consulta.motivo_consulta }}</p>
                <p v-if="consulta.diagnostico"><strong>Diagnóstico:</strong> {{ truncate(consulta.diagnostico, 100) }}</p>
                <div class="view-more">👁️ Click para ver detalles completos</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- VACUNAS -->
      <div v-show="activeTab === 'vacunas'" class="tab-panel">
        <div v-if="!historial.vacunas || historial.vacunas.length === 0" class="empty-state">
          <p>💉 No hay vacunas registradas</p>
        </div>

        <div v-else class="card-grid">
          <div
            v-for="vacuna in historial.vacunas"
            :key="vacuna.id"
            class="info-card clickable"
            @click="openVacunaModal(vacuna)"
          >
            <div class="card-header">
              <h4>{{ getVacunaTitle(vacuna) }}</h4>
              <span class="card-date">{{ formatDate(getVacunaFechaAplicacion(vacuna)) }}</span>
            </div>

            <div class="card-body">
              <p v-if="getVacunaCodigo(vacuna)">
                <strong>Código:</strong> {{ getVacunaCodigo(vacuna) }}
              </p>

              <p v-if="getVacunaField(vacuna, 'nombre_vacuna')">
                <strong>Nombre:</strong> {{ getVacunaField(vacuna, 'nombre_vacuna') }}
              </p>

              <div v-if="getVacunaField(vacuna, 'dosis') || getVacunaField(vacuna, 'via_administracion')" class="vaccine-details">
                <span v-if="getVacunaField(vacuna, 'dosis')" class="detail-badge">
                  💉 {{ getVacunaField(vacuna, 'dosis') }} ml
                </span>
                <span v-if="getVacunaField(vacuna, 'via_administracion')" class="detail-badge">
                  📍 {{ formatVia(getVacunaField(vacuna, 'via_administracion')) }}
                </span>
              </div>

              <p v-if="getVacunaField(vacuna, 'numero_dosis')" class="dose-number">
                <strong>{{ formatNumeroDosis(getVacunaField(vacuna, 'numero_dosis')) }}</strong>
              </p>

              <p v-if="getVacunaFechaProxima(vacuna)" class="next-dose">
                <strong>Próxima dosis:</strong> {{ formatDate(getVacunaFechaProxima(vacuna)) }}
              </p>

              <div class="view-more">👁️ Ver detalles completos</div>
            </div>
          </div>
        </div>
      </div>


      <!-- CIRUGÍAS -->
      <div v-show="activeTab === 'cirugias'" class="tab-panel">
        <div v-if="!historial.cirugias || historial.cirugias.length === 0" class="empty-state">
          <p>🔬 No hay cirugías registradas</p>
        </div>
        <div v-else class="card-grid">
          <div 
            v-for="cirugia in historial.cirugias" 
            :key="cirugia.id" 
            class="info-card surgery-card clickable"
            @click="openCirugiaModal(cirugia)"
          >
            <div class="card-header">
              <h4>{{ cirugia.tipo_procedimiento || cirugia.nombre_cirugia }}</h4>
              <span class="card-date">{{ formatDate(cirugia.fecha_cirugia) }}</span>
            </div>
            <div class="card-body">
              <p v-if="cirugia.descripcion">{{ truncate(cirugia.descripcion, 80) }}</p>
              <p v-if="cirugia.estado" :class="['status', cirugia.estado]">
                <strong>Estado:</strong> {{ formatStatus(cirugia.estado) }}
              </p>
              <div class="view-more">👁️ Ver detalles</div>
            </div>
          </div>
        </div>
      </div>

      <!-- DESPARASITACIONES -->
      <div v-show="activeTab === 'desparasitaciones'" class="tab-panel">
        <div v-if="!historial.desparasitaciones || historial.desparasitaciones.length === 0" class="empty-state">
          <p>🪱 No hay desparasitaciones registradas</p>
        </div>
        <div v-else class="card-grid">
          <div 
            v-for="desp in historial.desparasitaciones" 
            :key="desp.id" 
            class="info-card clickable"
            @click="openDesparasitacionModal(desp)"
          >
            <div class="card-header">
              <h4>{{ desp.producto || 'Desparasitación' }}</h4>
              <span class="card-date">{{ formatDate(desp.fecha_aplicacion) }}</span>
            </div>
            <div class="card-body">
              <p v-if="desp.tipo"><strong>Tipo:</strong> {{ desp.tipo }}</p>
              <p v-if="desp.dosis"><strong>Dosis:</strong> {{ desp.dosis }}</p>
              <div class="view-more">👁️ Ver detalles</div>
            </div>
          </div>
        </div>
      </div>

      <!-- EXÁMENES -->
      <div v-show="activeTab === 'examenes'" class="tab-panel">
        <div v-if="!historial.examenes || historial.examenes.length === 0" class="empty-state">
          <p>🧪 No hay exámenes registrados</p>
        </div>
        <div v-else class="card-grid">
          <div 
            v-for="examen in historial.examenes" 
            :key="examen.id" 
            class="info-card exam-card clickable"
            @click="openExamenModal(examen)"
          >
            <div class="card-header">
              <h4>{{ examen.tipo_examen }}</h4>
              <span class="card-date">{{ formatDate(examen.fecha_examen) }}</span>
            </div>
            <div class="card-body">
              <p v-if="examen.descripcion">{{ truncate(examen.descripcion, 80) }}</p>
              <p v-if="examen.estado" :class="['status', examen.estado]">
                {{ formatStatus(examen.estado) }}
              </p>
              <div class="view-more">👁️ Ver resultados</div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- MODALES -->
    
    <!-- Modal Consulta -->
    <div v-if="selectedConsulta" class="modal-overlay" @click="closeModals">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>📋 Detalle de Consulta</h3>
          <button class="close-btn" @click="closeModals">✕</button>
        </div>
        <div class="modal-body">
          <div class="detail-section">
            <h4>Información General</h4>
            <div class="detail-grid">
              <div class="detail-item">
                <strong>Fecha:</strong>
                <span>{{ formatDate(selectedConsulta.fecha_consulta) }}</span>
              </div>
              <div class="detail-item">
                <strong>Tipo:</strong>
                <span :class="['badge', selectedConsulta.tipo_consulta]">
                  {{ formatConsultType(selectedConsulta.tipo_consulta) }}
                </span>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <h4>Motivo de Consulta</h4>
            <p>{{ selectedConsulta.motivo_consulta }}</p>
          </div>

          <div v-if="selectedConsulta.sintomas" class="detail-section">
            <h4>Síntomas</h4>
            <p>{{ selectedConsulta.sintomas }}</p>
          </div>

          <div v-if="hasVitalSigns(selectedConsulta)" class="detail-section">
            <h4>Signos Vitales</h4>
            <div class="vital-signs-grid">
              <div v-if="selectedConsulta.peso" class="vital-item">
                <strong>Peso:</strong> {{ selectedConsulta.peso }} kg
              </div>
              <div v-if="selectedConsulta.temperatura" class="vital-item">
                <strong>Temperatura:</strong> {{ selectedConsulta.temperatura }}°C
              </div>
              <div v-if="selectedConsulta.frecuencia_cardiaca" class="vital-item">
                <strong>Frecuencia Cardíaca:</strong> {{ selectedConsulta.frecuencia_cardiaca }} lpm
              </div>
              <div v-if="selectedConsulta.frecuencia_respiratoria" class="vital-item">
                <strong>Frecuencia Respiratoria:</strong> {{ selectedConsulta.frecuencia_respiratoria }} rpm
              </div>
            </div>
          </div>

          <div v-if="selectedConsulta.diagnostico" class="detail-section">
            <h4>Diagnóstico</h4>
            <p>{{ selectedConsulta.diagnostico }}</p>
          </div>

          <div v-if="selectedConsulta.tratamientos && selectedConsulta.tratamientos.length > 0" class="detail-section">
            <h4>Tratamientos Prescritos</h4>
            <div class="treatment-list">
              <div v-for="tratamiento in selectedConsulta.tratamientos" :key="tratamiento.id" class="treatment-item">
                <p><strong>{{ tratamiento.descripcion }}</strong></p>
                <p v-if="tratamiento.dosis">Dosis: {{ tratamiento.dosis }}</p>
                <p v-if="tratamiento.frecuencia">Frecuencia: {{ tratamiento.frecuencia }}</p>
                <p v-if="tratamiento.duracion_dias">Duración: {{ tratamiento.duracion_dias }} días</p>
              </div>
            </div>
          </div>

          <div v-if="selectedConsulta.observaciones" class="detail-section">
            <h4>Observaciones</h4>
            <p>{{ selectedConsulta.observaciones }}</p>
          </div>

          <div v-if="selectedConsulta.veterinario" class="detail-section veterinarian-info">
            <h4>Veterinario Responsable</h4>
            <p>👨‍⚕️ {{ selectedConsulta.veterinario.nombre_completo || `${selectedConsulta.veterinario.nombres} ${selectedConsulta.veterinario.apellidos}` }}</p>
            <p v-if="selectedConsulta.veterinario.numero_tarjeta_profesional">
              Tarjeta Profesional: {{ selectedConsulta.veterinario.numero_tarjeta_profesional }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Vacuna -->
    <div v-if="selectedVacuna" class="modal-overlay" @click="closeModals">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>💉 Detalle de Vacuna</h3>
          <button class="close-btn" @click="closeModals">✕</button>
        </div>

        <div class="modal-body">
          <div class="detail-section">
            <h4>{{ getVacunaTitle(selectedVacuna) }}</h4>
            <p class="modal-date">📅 {{ formatDate(getVacunaFechaAplicacion(selectedVacuna)) }}</p>

            <p v-if="getVacunaCodigo(selectedVacuna)">
              <strong>Código:</strong> {{ getVacunaCodigo(selectedVacuna) }}
            </p>
          </div>

          <div v-if="getVacunaField(selectedVacuna, 'nombre_vacuna')" class="detail-section">
            <h4>Producto Comercial</h4>
            <div class="detail-grid">
              <div class="detail-item">
                <strong>Nombre comercial:</strong>
                <span>{{ getVacunaField(selectedVacuna, 'nombre_vacuna') }}</span>
              </div>

              <div v-if="getVacunaField(selectedVacuna, 'fabricante')" class="detail-item">
                <strong>Laboratorio:</strong>
                <span>{{ getVacunaField(selectedVacuna, 'fabricante') }}</span>
              </div>

              <div v-if="getVacunaLote(selectedVacuna)" class="detail-item">
                <strong>Lote:</strong>
                <span>{{ getVacunaLote(selectedVacuna) }}</span>
              </div>

              <div v-if="getVacunaField(selectedVacuna, 'fecha_vencimiento')" class="detail-item">
                <strong>Fecha de vencimiento:</strong>
                <span>{{ formatDate(getVacunaField(selectedVacuna, 'fecha_vencimiento')) }}</span>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <h4>Información de Aplicación</h4>

            <div class="detail-grid">
              <div v-if="getVacunaField(selectedVacuna, 'dosis')" class="detail-item">
                <strong>Dosis:</strong>
                <span>{{ getVacunaField(selectedVacuna, 'dosis') }} ml</span>
              </div>

              <div v-if="getVacunaField(selectedVacuna, 'via_administracion')" class="detail-item">
                <strong>Vía de aplicación:</strong>
                <span>{{ formatVia(getVacunaField(selectedVacuna, 'via_administracion')) }}</span>
              </div>

              <div v-if="getVacunaField(selectedVacuna, 'sitio_aplicacion')" class="detail-item">
                <strong>Sitio de aplicación:</strong>
                <span>{{ getVacunaField(selectedVacuna, 'sitio_aplicacion') }}</span>
              </div>

              <div v-if="getVacunaField(selectedVacuna, 'numero_dosis')" class="detail-item">
                <strong>Número de dosis:</strong>
                <span>{{ formatNumeroDosis(getVacunaField(selectedVacuna, 'numero_dosis')) }}</span>
              </div>
            </div>
          </div>

          <div v-if="getVacunaFechaProxima(selectedVacuna)" class="detail-section alert-section">
            <h4>⏰ Próxima dosis</h4>
            <p>{{ formatDate(getVacunaFechaProxima(selectedVacuna)) }}</p>
          </div>

          <div v-if="getVacunaField(selectedVacuna, 'observaciones')" class="detail-section">
            <h4>Observaciones</h4>
            <p>{{ getVacunaField(selectedVacuna, 'observaciones') }}</p>
          </div>

          <div v-if="getVacunaVeterinarioText(selectedVacuna)" class="detail-section veterinarian-info">
            <h4>Veterinario Aplicador</h4>
            <p>👨‍⚕️ {{ getVacunaVeterinarioText(selectedVacuna) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Cirugía -->
    <div v-if="selectedCirugia" class="modal-overlay" @click="closeModals">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>🔬 Detalle de Cirugía</h3>
          <button class="close-btn" @click="closeModals">✕</button>
        </div>
        <div class="modal-body">
          <div class="detail-section">
            <h4>{{ selectedCirugia.tipo_procedimiento || selectedCirugia.nombre_cirugia }}</h4>
            <p class="modal-date">📅 {{ formatDate(selectedCirugia.fecha_cirugia) }}</p>
            <span v-if="selectedCirugia.estado" :class="['badge', selectedCirugia.estado]">
              {{ formatStatus(selectedCirugia.estado) }}
            </span>
          </div>

          <div v-if="selectedCirugia.descripcion" class="detail-section">
            <h4>Descripción del Procedimiento</h4>
            <p>{{ selectedCirugia.descripcion }}</p>
          </div>

          <div v-if="selectedCirugia.anestesia" class="detail-section">
            <h4>Anestesia</h4>
            <p>{{ selectedCirugia.anestesia }}</p>
          </div>

          <div v-if="selectedCirugia.duracion_minutos" class="detail-section">
            <h4>Duración</h4>
            <p>{{ selectedCirugia.duracion_minutos }} minutos</p>
          </div>

          <div v-if="selectedCirugia.complicaciones" class="detail-section alert-section">
            <h4>⚠️ Complicaciones</h4>
            <p>{{ selectedCirugia.complicaciones }}</p>
          </div>

          <div v-if="selectedCirugia.observaciones_postoperatorias" class="detail-section">
            <h4>Observaciones Post-Operatorias</h4>
            <p>{{ selectedCirugia.observaciones_postoperatorias }}</p>
          </div>

          <div v-if="selectedCirugia.veterinario" class="detail-section veterinarian-info">
            <h4>Cirujano</h4>
            <p>👨‍⚕️ {{ selectedCirugia.veterinario.nombre_completo || `${selectedCirugia.veterinario.nombres} ${selectedCirugia.veterinario.apellidos}` }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Desparasitación -->
    <div v-if="selectedDesparasitacion" class="modal-overlay" @click="closeModals">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>🪱 Detalle de Desparasitación</h3>
          <button class="close-btn" @click="closeModals">✕</button>
        </div>
        <div class="modal-body">
          <div class="detail-section">
            <h4>{{ selectedDesparasitacion.producto || 'Desparasitación' }}</h4>
            <p class="modal-date">📅 {{ formatDate(selectedDesparasitacion.fecha_aplicacion) }}</p>
          </div>

          <div class="detail-section">
            <div class="detail-grid">
              <div v-if="selectedDesparasitacion.tipo" class="detail-item">
                <strong>Tipo:</strong> {{ selectedDesparasitacion.tipo }}
              </div>
              <div v-if="selectedDesparasitacion.dosis" class="detail-item">
                <strong>Dosis:</strong> {{ selectedDesparasitacion.dosis }}
              </div>
              <div v-if="selectedDesparasitacion.peso_animal" class="detail-item">
                <strong>Peso del Animal:</strong> {{ selectedDesparasitacion.peso_animal }} kg
              </div>
              <div v-if="selectedDesparasitacion.via_administracion" class="detail-item">
                <strong>Vía:</strong> {{ selectedDesparasitacion.via_administracion }}
              </div>
            </div>
          </div>

          <div v-if="selectedDesparasitacion.proxima_aplicacion" class="detail-section alert-section">
            <h4>⏰ Próxima Desparasitación</h4>
            <p>{{ formatDate(selectedDesparasitacion.proxima_aplicacion) }}</p>
          </div>

          <div v-if="selectedDesparasitacion.observaciones" class="detail-section">
            <h4>Observaciones</h4>
            <p>{{ selectedDesparasitacion.observaciones }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Examen -->
    <div v-if="selectedExamen" class="modal-overlay" @click="closeModals">
      <div class="modal-content large-modal" @click.stop>
        <div class="modal-header">
          <h3>🧪 Resultados de Examen</h3>
          <button class="close-btn" @click="closeModals">✕</button>
        </div>
        <div class="modal-body">
          <div class="detail-section">
            <h4>{{ selectedExamen.tipo_examen }}</h4>
            <p class="modal-date">📅 {{ formatDate(selectedExamen.fecha_examen) }}</p>
            <span v-if="selectedExamen.estado" :class="['badge', selectedExamen.estado]">
              {{ formatStatus(selectedExamen.estado) }}
            </span>
          </div>

          <div v-if="selectedExamen.descripcion" class="detail-section">
            <h4>Descripción</h4>
            <p>{{ selectedExamen.descripcion }}</p>
          </div>

          <div v-if="selectedExamen.resultados" class="detail-section results-section">
            <h4>Resultados</h4>
            <pre>{{ selectedExamen.resultados }}</pre>
          </div>

          <div v-if="selectedExamen.interpretacion" class="detail-section">
            <h4>Interpretación</h4>
            <p>{{ selectedExamen.interpretacion }}</p>
          </div>

          <div v-if="selectedExamen.valores_referencia" class="detail-section">
            <h4>Valores de Referencia</h4>
            <p>{{ selectedExamen.valores_referencia }}</p>
          </div>

          <div v-if="selectedExamen.observaciones" class="detail-section">
            <h4>Observaciones</h4>
            <p>{{ selectedExamen.observaciones }}</p>
          </div>
        </div>
      </div>
    </div>

  </div>
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

const activeTab = ref('resumen');
const loading = ref(false);
const error = ref(null);
const historial = ref({
  consultas: [],
  vacunas: [],
  cirugias: [],
  desparasitaciones: [],
  examenes: []
});

// Estados de modales
const selectedConsulta = ref(null);
const selectedVacuna = ref(null);
const selectedCirugia = ref(null);
const selectedDesparasitacion = ref(null);
const selectedExamen = ref(null);

const tabs = computed(() => [
  { id: 'resumen', label: 'Resumen', icon: '📊' },
  { id: 'consultas', label: 'Consultas', icon: '🩺', count: historial.value.consultas?.length || 0 },
  { id: 'vacunas', label: 'Vacunas', icon: '💉', count: historial.value.vacunas?.length || 0 },
  { id: 'cirugias', label: 'Cirugías', icon: '🔬', count: historial.value.cirugias?.length || 0 },
  { id: 'desparasitaciones', label: 'Desparasitación', icon: '🪱', count: historial.value.desparasitaciones?.length || 0 },
  { id: 'examenes', label: 'Exámenes', icon: '🧪', count: historial.value.examenes?.length || 0 }
]);

const lastConsulta = computed(() => {
  if (!historial.value.consultas || historial.value.consultas.length === 0) return null;
  return historial.value.consultas[0];
});

// Funciones de modales
function openConsultaModal(consulta) {
  selectedConsulta.value = consulta;
}

function openVacunaModal(vacuna) {
  selectedVacuna.value = vacuna;
}

function openCirugiaModal(cirugia) {
  selectedCirugia.value = cirugia;
}

function openDesparasitacionModal(desp) {
  selectedDesparasitacion.value = desp;
}

function openExamenModal(examen) {
  selectedExamen.value = examen;
}

function closeModals() {
  selectedConsulta.value = null;
  selectedVacuna.value = null;
  selectedCirugia.value = null;
  selectedDesparasitacion.value = null;
  selectedExamen.value = null;
}

async function loadHistory() {
  if (!props.animalId) {
    error.value = 'No se ha seleccionado un animal';
    return;
  }

  loading.value = true;
  error.value = null;

  try {
    console.log('🔄 Cargando historial para animal:', props.animalId);
    const data = await veterinaryStore.fetchHistorialClinico(props.animalId);
    
    console.log('✅ Historial recibido:', data);
    
    if (data.data) {
      historial.value = data.data;
    } else {
      historial.value = data;
    }

    historial.value.consultas = historial.value.consultas || [];
    historial.value.vacunas = historial.value.vacunas || [];
    historial.value.cirugias = historial.value.cirugias || [];
    historial.value.desparasitaciones = historial.value.desparasitaciones || [];
    historial.value.examenes = historial.value.examenes || [];

    if (historial.value.consultas.length > 0) {
      historial.value.consultas.sort((a, b) => 
        new Date(b.fecha_consulta) - new Date(a.fecha_consulta)
      );
    }

    console.log('📦 Historial procesado:', {
      consultas: historial.value.consultas.length,
      vacunas: historial.value.vacunas.length,
      cirugias: historial.value.cirugias.length,
      desparasitaciones: historial.value.desparasitaciones.length,
      examenes: historial.value.examenes.length
    });

  } catch (err) {
    console.error('❌ Error cargando historial:', err);
    error.value = err.response?.data?.message || 'Error al cargar el historial clínico';
  } finally {
    loading.value = false;
  }
}

// ====== FUNCIONES PARA FORMATEAR DATOS DE VACUNAS ======

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('es-CO', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
}

function getVacunaTitle(v) {
  if (v?.nombre_vacuna) return v.nombre_vacuna;
  if (v?.tipo_vacuna?.nombre) return v.tipo_vacuna.nombre;
  if (typeof v?.tipo_vacuna === 'string') return v.tipo_vacuna;
  if (v?.nombre) return v.nombre;
  return 'Vacuna';
}

function getVacunaCodigo(v) {
  return v?.tipo_vacuna?.codigo || v?.codigo || null;
}

function getVacunaFechaAplicacion(v) {
  if (v?.fecha_aplicacion) return v.fecha_aplicacion;
  if (v?.pivot?.fecha_aplicacion) return v.pivot.fecha_aplicacion;
  if (v?.created_at) return v.created_at;
  return null;
}

function getVacunaFechaProxima(v) {
  // Campo real en BD (vacunas): fecha_proxima_dosis
  return (
    v?.fecha_proxima_dosis ||
    v?.proxima_aplicacion ||
    v?.fecha_proxima ||
    v?.pivot?.fecha_proxima_dosis ||
    v?.pivot?.proxima_aplicacion ||
    v?.pivot?.fecha_proxima ||
    null
  );
}

function getVacunaLote(v) {
  return v?.lote || v?.pivot?.lote || null;
}

function getVacunaField(v, fieldName) {
  if (v?.[fieldName]) return v[fieldName];
  if (v?.pivot?.[fieldName]) return v.pivot[fieldName];
  return null;
}

function getVacunaVeterinarioText(v) {
  if (v?.veterinario) {
    const vet = v.veterinario;
    return vet.nombre_completo || 
           `${vet.nombres || ''} ${vet.apellidos || ''}`.trim() ||
           'Veterinario no especificado';
  }

  if (v?.pivot?.veterinario_nombre) {
    return v.pivot.veterinario_nombre;
  }

  return null;
}

function formatVia(via) {
  const vias = {
    subcutanea: 'Subcutánea',
    intramuscular: 'Intramuscular',
    intranasal: 'Intranasal',
    oral: 'Oral',
    intravenosa: 'Intravenosa'
  };
  return vias[via] || via;
}

function formatNumeroDosis(numero) {
  const numeros = {
    '1': 'Primera dosis',
    '2': 'Segunda dosis',
    '3': 'Tercera dosis',
    'refuerzo': 'Refuerzo anual'
  };
  return numeros[numero] || `Dosis ${numero}`;
}

// ====== OTRAS FUNCIONES DE FORMATO ======

function formatConsultType(type) {
  const types = {
    general: 'General',
    emergencia: 'Emergencia',
    seguimiento: 'Seguimiento',
    especializada: 'Especializada',
    control: 'Control'
  };
  return types[type] || type;
}

function formatStatus(status) {
  const statuses = {
    pendiente: 'Pendiente',
    completado: 'Completado',
    cancelado: 'Cancelado',
    en_proceso: 'En Proceso'
  };
  return statuses[status] || status;
}

function hasVitalSigns(consulta) {
  return consulta.peso || consulta.temperatura || 
         consulta.frecuencia_cardiaca || consulta.frecuencia_respiratoria;
}

function truncate(text, length) {
  if (!text) return '';
  return text.length > length ? text.substring(0, length) + '...' : text;
}

watch(() => props.animalId, (newId) => {
  if (newId) {
    loadHistory();
  }
}, { immediate: true });

onMounted(() => {
  if (props.animalId) {
    loadHistory();
  }
});
</script>

<style scoped>
.medical-history {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  overflow: hidden;
}

.history-tabs {
  display: flex;
  gap: 0.5rem;
  padding: 1rem;
  background: #F5F7FB;
  border-bottom: 2px solid #E0E0E0;
  overflow-x: auto;
}

.tab-button {
  padding: 0.75rem 1.5rem;
  background: white;
  border: 2px solid transparent;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 500;
  white-space: nowrap;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.tab-button:hover {
  background: #E8F0FE;
  border-color: #3366CC;
}

.tab-button.active {
  background: #3366CC;
  color: white;
  border-color: #3366CC;
}

.count-badge {
  background: rgba(255,255,255,0.3);
  padding: 0.2rem 0.5rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
}

.loading-container,
.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3366cc;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-message {
  color: #b00020;
  margin-bottom: 1rem;
}

.retry-button {
  padding: 0.5rem 1.5rem;
  background: #3366CC;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.tab-content {
  padding: 1.5rem;
}

.tab-panel {
  animation: fadeIn 0.3s;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.clickable {
  cursor: pointer;
  transition: all 0.2s;
}

.clickable:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.view-more {
  margin-top: 0.75rem;
  padding: 0.5rem;
  background: #E8F0FE;
  color: #3366CC;
  text-align: center;
  border-radius: 4px;
  font-size: 0.85rem;
  font-weight: 600;
}

/* Resumen */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.summary-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  color: white;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.summary-card:nth-child(2) {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.summary-card:nth-child(3) {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.summary-card:nth-child(4) {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.summary-icon {
  font-size: 2.5rem;
}

.summary-info h4 {
  margin: 0;
  font-size: 2rem;
  font-weight: 700;
}

.summary-info p {
  margin: 0.25rem 0 0 0;
  opacity: 0.9;
  font-size: 0.9rem;
}

.last-consult {
  margin-top: 2rem;
}

.last-consult h4 {
  margin-bottom: 1rem;
  color: #333;
}

.consult-card {
  background: #F5F7FB;
  padding: 1.5rem;
  border-radius: 8px;
  border-left: 4px solid #3366CC;
}

.consult-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.consult-date {
  font-weight: 600;
  color: #666;
}

.consult-type {
  padding: 0.25rem 0.75rem;
  border-radius: 4px;
  font-size: 0.85rem;
  font-weight: 600;
}

.consult-type.general { background: #E3F2FD; color: #1976D2; }
.consult-type.emergencia { background: #FFEBEE; color: #C62828; }
.consult-type.seguimiento { background: #F3E5F5; color: #7B1FA2; }
.consult-type.especializada { background: #E8F5E9; color: #388E3C; }

.consult-card p {
  margin: 0.5rem 0;
  color: #333;
}

/* Timeline */
.timeline {
  position: relative;
  padding-left: 2rem;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #E0E0E0;
}

.timeline-item {
  position: relative;
  margin-bottom: 2rem;
}

.timeline-marker {
  position: absolute;
  left: -2.5rem;
  top: 0;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #3366CC;
  border: 3px solid white;
  box-shadow: 0 0 0 2px #3366CC;
}

.timeline-content {
  background: #F5F7FB;
  padding: 1.5rem;
  border-radius: 8px;
  border-left: 4px solid #3366CC;
}

.timeline-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.timeline-header h4 {
  margin: 0;
  color: #333;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 4px;
  font-size: 0.85rem;
  font-weight: 600;
}

.badge.general { background: #E3F2FD; color: #1976D2; }
.badge.emergencia { background: #FFEBEE; color: #C62828; }
.badge.seguimiento { background: #F3E5F5; color: #7B1FA2; }
.badge.especializada { background: #E8F5E9; color: #388E3C; }

.timeline-body p {
  margin: 0.5rem 0;
  color: #333;
}

/* Cards Grid */
.card-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.info-card {
  background: #F5F7FB;
  border-radius: 8px;
  overflow: hidden;
  border-left: 4px solid #3366CC;
}

.surgery-card {
  border-left-color: #F5576C;
}

.exam-card {
  border-left-color: #43E97B;
}

.card-header {
  padding: 1rem 1.5rem;
  background: white;
  border-bottom: 1px solid #E0E0E0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h4 {
  margin: 0;
  color: #333;
  font-size: 1.1rem;
}

.card-date {
  color: #666;
  font-size: 0.85rem;
}

.card-body {
  padding: 1.5rem;
}

.card-body p {
  margin: 0.5rem 0;
  color: #333;
}

/* Estilos específicos para vacunas */
.next-dose {
  color: #F5576C;
  font-weight: 600;
}

.dose-number {
  color: #3366CC;
  font-weight: 600;
  font-size: 0.9rem;
  margin: 0.5rem 0;
}

.vaccine-details {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin: 0.75rem 0;
}

.detail-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.75rem;
  background: #E8F0FE;
  color: #3366CC;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
}

.status {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 4px;
  font-size: 0.85rem;
  font-weight: 600;
}

.status.pendiente { background: #FFF3E0; color: #E65100; }
.status.completado { background: #E8F5E9; color: #2E7D32; }
.status.cancelado { background: #FFEBEE; color: #C62828; }
.status.en_proceso { background: #E3F2FD; color: #1976D2; }

.empty-state {
  text-align: center;
  padding: 3rem;
  color: #666;
  font-size: 1.1rem;
}

/* MODALES */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
  animation: fadeIn 0.2s;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 700px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 40px rgba(0,0,0,0.3);
  animation: slideUp 0.3s;
}

.large-modal {
  max-width: 900px;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 2px solid #E0E0E0;
  background: #F5F7FB;
}

.modal-header h3 {
  margin: 0;
  color: #3366CC;
  font-size: 1.5rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s;
}

.close-btn:hover {
  background: #E0E0E0;
  color: #333;
}

.modal-body {
  padding: 1.5rem;
}

.detail-section {
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #E0E0E0;
}

.detail-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.detail-section h4 {
  margin: 0 0 1rem 0;
  color: #333;
  font-size: 1.1rem;
}

.modal-date {
  color: #666;
  margin: 0.5rem 0;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-item strong {
  color: #666;
  font-size: 0.9rem;
}

.detail-item span {
  color: #333;
  font-size: 1rem;
}

.vital-signs-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}

.vital-item {
  padding: 1rem;
  background: #E8F0FE;
  border-radius: 6px;
  text-align: center;
}

.vital-item strong {
  display: block;
  color: #3366CC;
  font-size: 0.85rem;
  margin-bottom: 0.25rem;
}

.treatment-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.treatment-item {
  padding: 1rem;
  background: #F5F7FB;
  border-radius: 6px;
  border-left: 4px solid #3366CC;
}

.treatment-item p {
  margin: 0.25rem 0;
  color: #333;
}

.alert-section {
  background: #FFF3E0;
  padding: 1rem;
  border-radius: 6px;
  border-left: 4px solid #FF9800;
}

.veterinarian-info {
  background: #E8F0FE;
  padding: 1rem;
  border-radius: 6px;
}

.veterinarian-info p {
  margin: 0.25rem 0;
  color: #333;
}

.results-section pre {
  background: #F5F7FB;
  padding: 1rem;
  border-radius: 6px;
  overflow-x: auto;
  white-space: pre-wrap;
  word-wrap: break-word;
  font-family: 'Courier New', monospace;
  font-size: 0.9rem;
  color: #333;
  margin: 0;
}

@media (max-width: 768px) {
  .history-tabs {
    flex-wrap: wrap;
  }
  
  .summary-grid {
    grid-template-columns: 1fr;
  }
  
  .card-grid {
    grid-template-columns: 1fr;
  }
  
  .timeline {
    padding-left: 1.5rem;
  }
  
  .timeline-marker {
    left: -2rem;
  }

  .modal-content {
    max-width: 100%;
    max-height: 95vh;
    margin: 0;
    border-radius: 12px 12px 0 0;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }

  .vital-signs-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>