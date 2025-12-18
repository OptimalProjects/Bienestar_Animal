<template>
  <div class="map-selector">
    <div ref="mapEl" class="map"></div>

    <!-- Botón flotante GPS -->
    <button
      v-if="hasGeolocation"
      class="gps-button"
      @click="locateUser"
      title="Usar mi ubicación actual"
    >
    <img src="../../assets/icons/compass.png" alt="GPS" class="gps-icon" />
    </button>
  </div>
</template>


<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
  modelValue: {
    type: Object,
    default: null, // { lat, lng } o null
  },
  initialCenter: {
    type: Object,
    default: () => ({
      // Centro por defecto (Cali)
      lat: 3.4516,
      lng: -76.5319,
    }),
  },
  zoom: {
    type: Number,
    default: 15,
  },
});

const emit = defineEmits(['update:modelValue', 'address-found']);

const mapEl = ref(null);
let map = null;
let marker = null;

const currentCoords = ref(props.modelValue);
const hasGeolocation = ref(false);

watch(
  () => props.modelValue,
  (val) => {
    currentCoords.value = val;

    if (val && map) {
      if (!marker) {
        marker = L.marker(val).addTo(map);
      } else {
        marker.setLatLng(val);
      }
      map.setView(val);
    }
  },
  { immediate: true }
);

/**
 * Geocodificación inversa usando Nominatim (OpenStreetMap)
 * Obtiene la dirección a partir de coordenadas
 * Nota: Para direcciones colombianas específicas (Calle X #Y-Z),
 * el usuario debe ingresar manualmente ya que OSM no tiene ese nivel de detalle
 */
async function reverseGeocode(lat, lng) {
  try {
    const response = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`,
      {
        headers: {
          'Accept-Language': 'es',
        },
      }
    );

    if (!response.ok) {
      throw new Error('Error en geocodificación');
    }

    const data = await response.json();

    if (data && data.display_name) {
      const address = data.address || {};
      let parts = [];

      // Calle/Carrera
      if (address.road) {
        let roadPart = address.road;
        if (address.house_number) {
          roadPart += ' #' + address.house_number;
        }
        parts.push(roadPart);
      }

      // Barrio
      if (address.neighbourhood) {
        parts.push(address.neighbourhood);
      } else if (address.suburb) {
        parts.push(address.suburb);
      }

      // Comuna (si existe)
      if (address.city_district) {
        parts.push(address.city_district);
      }

      // Ciudad
      const city = address.city || address.town || address.village || address.municipality;
      if (city) {
        parts.push(city);
      }

      // Si tenemos partes, unirlas
      if (parts.length > 0) {
        return parts.join(', ');
      }

      // Fallback: usar display_name pero limpiado
      let displayName = data.display_name;
      // Remover código postal y país si están
      displayName = displayName.replace(/,\s*\d{6}/, ''); // Código postal Colombia
      displayName = displayName.replace(/,\s*Colombia$/, '');
      return displayName;
    }

    return null;
  } catch (error) {
    console.error('Error en geocodificación inversa:', error);
    return null;
  }
}

function setLocation(latlng, skipReverseGeocode = false) {
  currentCoords.value = latlng;
  emit('update:modelValue', latlng);

  if (!marker) {
    marker = L.marker(latlng).addTo(map);
  } else {
    marker.setLatLng(latlng);
  }

  // Obtener dirección y emitir evento (solo si no viene de una búsqueda de dirección)
  if (!skipReverseGeocode) {
    reverseGeocode(latlng.lat, latlng.lng).then((address) => {
      if (address) {
        emit('address-found', address);
      }
    });
  }
}

/**
 * Geocodificación directa usando Nominatim (OpenStreetMap)
 * Busca coordenadas a partir de una dirección
 * Usa viewbox para limitar búsqueda a Cali y alrededores
 */
async function searchAddress(address) {
  if (!address || address.trim().length < 5) {
    return null;
  }

  try {
    // Limpiar y preparar la dirección
    let searchQuery = address.trim();

    // Si no incluye ciudad, agregar Cali
    if (!searchQuery.toLowerCase().includes('cali')) {
      searchQuery = `${searchQuery}, Cali`;
    }

    // Si no incluye país, agregar Colombia
    if (!searchQuery.toLowerCase().includes('colombia')) {
      searchQuery = `${searchQuery}, Colombia`;
    }

    // Bounding box para Cali y área metropolitana
    // viewbox=lon_min,lat_max,lon_max,lat_min (oeste,norte,este,sur)
    const viewbox = '-76.62,3.52,-76.45,3.35';

    const response = await fetch(
      `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}&limit=5&addressdetails=1&viewbox=${viewbox}&bounded=0`,
      {
        headers: {
          'Accept-Language': 'es',
        },
      }
    );

    if (!response.ok) {
      throw new Error('Error en geocodificación');
    }

    const data = await response.json();

    if (data && data.length > 0) {
      // Buscar el resultado más relevante (preferir los que están en Cali)
      let bestResult = data[0];

      for (const result of data) {
        const displayName = (result.display_name || '').toLowerCase();
        if (displayName.includes('cali') && displayName.includes('valle del cauca')) {
          bestResult = result;
          break;
        }
      }

      const latlng = {
        lat: parseFloat(bestResult.lat),
        lng: parseFloat(bestResult.lon),
      };

      // Mover el mapa y poner el marcador
      if (map) {
        map.setView(latlng, 17);
      }
      setLocation(latlng, true); // true = no hacer geocodificación inversa

      return latlng;
    }

    return null;
  } catch (error) {
    console.error('Error en geocodificación directa:', error);
    return null;
  }
}

// Exponer métodos para el componente padre
defineExpose({
  searchAddress,
});

function locateUser() {
  if (!hasGeolocation.value) {
    if (window.$toast) {
      window.$toast.warning('Geolocalizacion', 'Tu navegador no soporta geolocalizacion.');
    } else {
      alert('Tu navegador no soporta geolocalización.');
    }
    return;
  }

  const btn = document.querySelector('.gps-button');
  if (btn) {
    btn.disabled = true;
    btn.style.opacity = '0.5';
  }

  navigator.geolocation.getCurrentPosition(
    (pos) => {
      const latlng = {
        lat: pos.coords.latitude,
        lng: pos.coords.longitude,
      };
      
      if (map) {
        map.setView(latlng, props.zoom);
      }
      setLocation(latlng);
      
      if (btn) {
        btn.disabled = false;
        btn.style.opacity = '1';
      }
    },
    (err) => {

      let errorMsg = 'No fue posible obtener tu ubicación.';
      
      if (err.code === 1) {
        errorMsg = 'Permiso denegado. Por favor:\n1. Verifica los permisos de ubicación del navegador\n2. Recarga la página\n3. Intenta nuevamente';
      } else if (err.code === 2) {
        errorMsg = 'Ubicación no disponible en tu dispositivo. Intenta en otro lugar o dispositivo.';
      } else if (err.code === 3) {
        errorMsg = 'La solicitud tardó demasiado (>10s). Verifica tu conexión de internet.';
      }
      
      if (window.$toast) {
        window.$toast.error('Error de ubicacion', errorMsg, 8000);
      } else {
        alert(errorMsg);
      }

      if (btn) {
        btn.disabled = false;
        btn.style.opacity = '1';
      }
    },
    {
      enableHighAccuracy: true,
      timeout: 200,               
      maximumAge: 0                 
    }
  );
}

onMounted(() => {
  
  if (!mapEl.value) return;

  // Detectar si el navegador soporta geolocalización
  if (typeof navigator !== 'undefined' && 'geolocation' in navigator) {
    hasGeolocation.value = true;
  } 

  const iconRetinaUrl =
    'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png';
  const iconUrl =
    'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png';
  const shadowUrl =
    'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png';

  L.Marker.prototype.options.icon = L.icon({
    iconRetinaUrl,
    iconUrl,
    shadowUrl,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41],
  });

  let initialView = props.initialCenter;

  if (currentCoords.value) {
    initialView = currentCoords.value;
  }
  
  map = L.map(mapEl.value).setView(initialView, props.zoom);

  if (currentCoords.value) {
    marker = L.marker(currentCoords.value).addTo(map);
  }

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors',
  }).addTo(map);

  map.on('click', (e) => {
    setLocation(e.latlng);
  });
});

onBeforeUnmount(() => {
  if (map) {
    map.off();
    map.remove();
    map = null;
  }
});
</script>

<style scoped>
.map-selector {
  position: relative;
  width: 100%;
}

.map {
  width: 100%;
  height: 320px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #d9e2f3;
}

/* Botón flotante */
.gps-button {
  position: absolute;
  top: 12px;
  right: 12px;
  z-index: 500;
  background: white;
  border: 2px solid #3366CC;
  border-radius: 50%;
  padding: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0px 2px 8px rgba(0,0,0,0.2);
  transition: all 0.2s ease;
}

.gps-button:hover:not(:disabled) {
  background: #E8F0FE;
  transform: scale(1.15);
  box-shadow: 0px 4px 12px rgba(51, 102, 204, 0.3);
}

.gps-button:active:not(:disabled) {
  transform: scale(0.95);
}

.gps-button:disabled {
  cursor: not-allowed;
  opacity: 0.5;
}

/* Tamaño REAL del ícono */
.gps-icon {
  width: 24px;
  height: 24px;
  object-fit: contain;
}

</style>
