<!-- src/components/complaints/RescueMap.vue -->
<!-- Mapa interactivo de denuncias activas -->
<template>
  <div class="rescue-map">
    <div ref="mapEl" class="map"></div>

    <!-- Leyenda -->
    <div class="map-legend">
      <h4>Urgencia</h4>
      <div class="legend-item">
        <span class="legend-marker critical"></span>
        <span>Crítico</span>
      </div>
      <div class="legend-item">
        <span class="legend-marker high"></span>
        <span>Alto</span>
      </div>
      <div class="legend-item">
        <span class="legend-marker medium"></span>
        <span>Medio</span>
      </div>
      <div class="legend-item">
        <span class="legend-marker low"></span>
        <span>Bajo</span>
      </div>
    </div>

    <!-- Contador -->
    <div class="map-counter">
      <span class="counter-number">{{ complaints.length }}</span>
      <span class="counter-label">denuncias activas</span>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
  complaints: {
    type: Array,
    default: () => []
  },
  center: {
    type: Object,
    default: () => ({ lat: 3.4516, lng: -76.5319 })
  },
  zoom: {
    type: Number,
    default: 12
  }
});

const emit = defineEmits(['select']);

const mapEl = ref(null);
let map = null;
let markers = [];

// Colores por urgencia
const urgencyColors = {
  critico: '#A80521',
  alto: '#FFAB00',
  medio: '#3366CC',
  bajo: '#737373'
};

// Crear icono personalizado
function createCustomIcon(urgency) {
  const color = urgencyColors[urgency] || '#737373';

  return L.divIcon({
    className: 'custom-marker',
    html: `
      <div class="marker-pin" style="background-color: ${color}">
        <span class="marker-icon">=></span>
      </div>
      <div class="marker-pulse" style="background-color: ${color}"></div>
    `,
    iconSize: [30, 42],
    iconAnchor: [15, 42],
    popupAnchor: [0, -42]
  });
}

// Crear contenido del popup
function createPopupContent(complaint) {
  const typeLabels = {
    maltrato_fisico: 'Maltrato físico',
    abandono: 'Abandono',
    negligencia: 'Negligencia',
    hacinamiento: 'Hacinamiento',
    animal_herido: 'Animal herido',
    envenenamiento: 'Envenenamiento'
  };

  const urgencyLabels = {
    critico: 'CRÍTICO',
    alto: 'ALTO',
    medio: 'MEDIO',
    bajo: 'BAJO'
  };

  return `
    <div class="complaint-popup">
      <div class="popup-header">
        <strong>${complaint.caso_numero}</strong>
        <span class="popup-urgency urgency-${complaint.urgencia}">
          ${urgencyLabels[complaint.urgencia] || complaint.urgencia}
        </span>
      </div>
      <div class="popup-type">${typeLabels[complaint.tipo_denuncia] || complaint.tipo_denuncia}</div>
      <div class="popup-address">${complaint.direccion}</div>
      <button class="popup-btn" onclick="window.selectComplaint(${complaint.id})">
        Ver detalle
      </button>
    </div>
  `;
}

// Limpiar marcadores
function clearMarkers() {
  markers.forEach(marker => {
    if (map) map.removeLayer(marker);
  });
  markers = [];
}

// Agregar marcadores
function addMarkers() {
  if (!map) return;

  clearMarkers();

  props.complaints.forEach(complaint => {
    if (complaint.coordenadas) {
      const marker = L.marker(
        [complaint.coordenadas.lat, complaint.coordenadas.lng],
        { icon: createCustomIcon(complaint.urgencia) }
      );

      marker.bindPopup(createPopupContent(complaint), {
        maxWidth: 300,
        className: 'custom-popup'
      });

      marker.on('click', () => {
        // También emitir evento
      });

      marker.addTo(map);
      markers.push(marker);
    }
  });

  // Ajustar vista si hay marcadores
  if (markers.length > 0) {
    const group = L.featureGroup(markers);
    map.fitBounds(group.getBounds().pad(0.1));
  }
}

// Watch para actualizar marcadores
watch(() => props.complaints, () => {
  addMarkers();
}, { deep: true });

onMounted(() => {
  if (!mapEl.value) return;

  // Configurar iconos de Leaflet
  const iconRetinaUrl = 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png';
  const iconUrl = 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png';
  const shadowUrl = 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png';

  L.Marker.prototype.options.icon = L.icon({
    iconRetinaUrl,
    iconUrl,
    shadowUrl,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
  });

  // Crear mapa
  map = L.map(mapEl.value).setView([props.center.lat, props.center.lng], props.zoom);

  // Agregar capa de tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  // Agregar marcadores iniciales
  addMarkers();

  // Función global para seleccionar denuncia desde popup
  window.selectComplaint = (id) => {
    const complaint = props.complaints.find(c => c.id === id);
    if (complaint) {
      emit('select', complaint);
    }
  };
});

onBeforeUnmount(() => {
  if (map) {
    map.off();
    map.remove();
    map = null;
  }
  delete window.selectComplaint;
});
</script>

<style scoped>
.rescue-map {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 400px;
}

.map {
  width: 100%;
  height: 100%;
  min-height: 400px;
  border-radius: 0 0 8px 8px;
}

/* Leyenda */
.map-legend {
  position: absolute;
  bottom: 20px;
  left: 20px;
  background: white;
  padding: 1rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  z-index: 1000;
}

.map-legend h4 {
  margin: 0 0 0.5rem 0;
  font-size: 0.85rem;
  color: #333;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.25rem;
  font-size: 0.8rem;
}

.legend-marker {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.legend-marker.critical { background: #A80521; }
.legend-marker.high { background: #FFAB00; }
.legend-marker.medium { background: #3366CC; }
.legend-marker.low { background: #737373; }

/* Contador */
.map-counter {
  position: absolute;
  top: 20px;
  right: 20px;
  background: white;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  z-index: 1000;
  text-align: center;
}

.counter-number {
  display: block;
  font-size: 1.5rem;
  font-weight: bold;
  color: #A80521;
}

.counter-label {
  font-size: 0.75rem;
  color: #666;
}

/* Estilos globales para marcadores y popups */
:global(.custom-marker) {
  background: transparent;
  border: none;
}

:global(.marker-pin) {
  width: 30px;
  height: 30px;
  border-radius: 50% 50% 50% 0;
  transform: rotate(-45deg);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

:global(.marker-icon) {
  transform: rotate(45deg);
  font-size: 14px;
}

:global(.marker-pulse) {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  position: absolute;
  bottom: -7px;
  left: 8px;
  opacity: 0.4;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); opacity: 0.4; }
  50% { transform: scale(1.5); opacity: 0.2; }
  100% { transform: scale(1); opacity: 0.4; }
}

:global(.custom-popup .leaflet-popup-content-wrapper) {
  border-radius: 8px;
  padding: 0;
}

:global(.custom-popup .leaflet-popup-content) {
  margin: 0;
}

:global(.complaint-popup) {
  padding: 1rem;
  min-width: 200px;
}

:global(.popup-header) {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

:global(.popup-urgency) {
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: bold;
  color: white;
}

:global(.popup-urgency.urgency-critico) { background: #A80521; }
:global(.popup-urgency.urgency-alto) { background: #FFAB00; color: #333; }
:global(.popup-urgency.urgency-medio) { background: #3366CC; }
:global(.popup-urgency.urgency-bajo) { background: #737373; }

:global(.popup-type) {
  font-weight: 600;
  color: #333;
  margin-bottom: 0.25rem;
}

:global(.popup-address) {
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 0.75rem;
}

:global(.popup-btn) {
  width: 100%;
  padding: 0.5rem;
  background: #3366CC;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.85rem;
}

:global(.popup-btn:hover) {
  background: #004884;
}
</style>
