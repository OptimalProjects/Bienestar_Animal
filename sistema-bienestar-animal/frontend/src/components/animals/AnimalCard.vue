<!-- AnimalCard.vue - Con colores Gov.co oficiales -->
<template>
  <div class="animal-card" @click="$emit('click')">
    <div class="card-image">
      <img
        :src="photoUrl"
        :alt="`Foto de ${displayId}`"
        @error="onImageError"
      />
      <span class="status-badge" :class="`status-${animalStatus}`">
        {{ getStatusLabel(animalStatus) }}
      </span>
    </div>

    <div class="card-content">
      <h4 class="h6-tipografia-govco">{{ displayId }}</h4>

      <div class="card-details">
        <p><strong>Especie:</strong> {{ displayEspecie }}</p>
        <p><strong>Raza:</strong> {{ displayRaza }}</p>
        <p><strong>Color:</strong> {{ displayColor }}</p>
        <p><strong>Sexo:</strong> {{ displaySexo }}</p>
        <p><strong>Edad:</strong> {{ displayEdad }}</p>
        <p><strong>Rescate:</strong> {{ displayFechaRescate }}</p>
        <p v-if="isNeutered" class="neutered-badge"><strong>✓ Esterilizado</strong></p>
      </div>

      <button class="view-details-btn govco-bg-blue-light">Ver detalles completos</button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { resolveAnimalImageUrl, handleImageError } from '@/utils/animalImages';

const props = defineProps({
  animal: Object
});

defineEmits(['click']);

// Obtener la especie normalizada para el placeholder
const animalEspecie = computed(() =>
  (props.animal?.especie || props.animal?.species || 'otro').toString().toLowerCase()
);

// Semilla para placeholder consistente (usa el ID del animal)
const animalSeed = computed(() =>
  props.animal?.id || props.animal?.codigo_unico || 0
);

// Mapeo de campos - soporta tanto nombres en español (backend) como ingles
const photoUrl = computed(() => {
  const raw = props.animal?.foto_url || props.animal?.url_foto_principal || props.animal?.foto_principal || props.animal?.photoUrl;
  return resolveAnimalImageUrl(raw, animalEspecie.value, animalSeed.value);
});

// Manejador de error de imagen - usa placeholder por especie
function onImageError(event) {
  handleImageError(event, animalEspecie.value, animalSeed.value);
}

const displayId = computed(() =>
  props.animal?.codigo_unico || props.animal?.numero_chip || props.animal?.microchip || 'Sin ID'
);

const animalStatus = computed(() =>
  props.animal?.estado || props.animal?.status || ''
);

const displayEspecie = computed(() => {
  const especie = (props.animal?.especie || props.animal?.species || '').toString().toLowerCase();
  const labels = {
    perro: 'Perro',
    canino: 'Perro',
    gato: 'Gato',
    felino: 'Gato',
    equino: 'Equino',
    otro: 'Otro'
  };
  return labels[especie] || (props.animal?.especie || props.animal?.species) || 'No especificada';
});

const displayRaza = computed(() =>
  props.animal?.raza || props.animal?.breed || 'No especificada'
);

const displayColor = computed(() =>
  props.animal?.color || 'No especificado'
);

const displaySexo = computed(() => {
  const sexo = props.animal?.sexo || props.animal?.sex || '';
  const labels = { macho: 'Macho', hembra: 'Hembra', desconocido: 'Desconocido' };
  return labels[sexo?.toLowerCase()] || sexo || 'No especificado';
});

const displayEdad = computed(() => {
  // Usar edad_formato si esta disponible (viene del backend como accessor)
  if (props.animal?.edad_formato) {
    return props.animal.edad_formato;
  }
  // Fallback: calcular desde edad_aproximada (en meses)
  const edadMeses = props.animal?.edad_aproximada || props.animal?.estimatedAge;
  if (!edadMeses) return 'Desconocida';

  const anios = Math.floor(edadMeses / 12);
  const meses = edadMeses % 12;
  const partes = [];
  if (anios > 0) partes.push(`${anios} año${anios > 1 ? 's' : ''}`);
  if (meses > 0) partes.push(`${meses} mes${meses > 1 ? 'es' : ''}`);
  return partes.join(' y ') || 'Desconocida';
});

const displayFechaRescate = computed(() => {
  const fecha = props.animal?.fecha_rescate || props.animal?.rescueDate;
  if (!fecha) return 'No registrada';
  // Formatear fecha si viene en formato ISO
  try {
    const date = new Date(fecha);
    if (isNaN(date.getTime())) return fecha;
    return date.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' });
  } catch {
    return fecha;
  }
});

const isNeutered = computed(() =>
  props.animal?.esterilizacion ?? props.animal?.esterilizado ?? props.animal?.neutered ?? false
);

function getStatusLabel(status) {
  const labels = {
    en_calle: 'En calle',
    en_refugio: 'En refugio',
    refugio: 'En refugio',
    en_adopcion: 'En adopcion',
    adoptado: 'Adoptado',
    fallecido: 'Fallecido',
    en_tratamiento: 'En tratamiento'
  };
  return labels[status] || status || 'Sin estado';
}
</script>

<style scoped>
.animal-card {
  background: white;
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.animal-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.card-image {
  position: relative;
  width: 100%;
  aspect-ratio: 1 / 1;
  background: #f0f0f0;
  overflow: hidden;
}

.card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
}

.status-badge {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  color: white;
}

/* Colores Gov.co para estados */
.status-en_calle {
  background-color: #A80521; /* govco-bg-shiraz - Rojo para urgencia */
}

.status-refugio {
  background-color: #FFAB00; /* govco-bg-gold - Amarillo para en proceso */
}

.status-en_refugio {
  background-color: #FFAB00;
}

.status-en_adopcion {
  background-color: #3366cc; /* Azul govco */
}

.status-en_tratamiento {
  background-color: #FFAB00; /* Amarillo (en proceso) */
}

.status-adoptado {
  background-color: #068460; /* govco-bg-elf-green - Verde para exito */
}

.status-fallecido {
  background-color: #4B4B4B; /* govco-bg-tundora - Gris oscuro */
}

.card-content {
  padding: 1rem;
  background: #FFFFFF; /* govco-bg-white */
}

.card-content h4 {
  margin: 0 0 0.75rem 0;
  color: #3366cc; /* govcolor-marine */
}

.card-details p {
  margin: 0.5rem 0;
  font-size: 0.9rem;
  color: #4B4B4B; /* govcolor-tundora */
}

.neutered-badge {
  color: #068460; /* govcolor-elf-green */
  font-weight: 600;
}

.view-details-btn {
  width: 100%;
  margin-top: 1rem;
  padding: 0.5rem;
  background: #c9e2ff; /* govco-bg-blue-light */
  color: #004884; /* govcolor-blue-dark */
  border: 2px solid #3366cc; /* govcolor-marine */
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.view-details-btn:hover {
  background: #3366cc; /* govco-bg-marine */
  color: white;
  border-color: #004884;
}
</style>
