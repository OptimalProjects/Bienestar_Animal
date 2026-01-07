<!-- src/components/adoptions/AdoptionList.vue -->
<template>
  <section class="adoptions-catalog">
    <!-- Filtros -->
    <div class="filters-card card-govco">
      <div class="card-govco-header">
        <h2 class="h4-tipografia-govco govcolor-blue-dark">Filtra tu búsqueda</h2>
      </div>
      <div class="card-govco-body">
        <div class="filters-grid">
          <!-- Especie -->
          <div>
            <label for="f-species" class="label-govco">Especie</label>
            <select
              id="f-species"
              class="input-govco"
              v-model="localFilters.species"
              @change="emitFilters"
            >
              <option value="">Todas</option>
              <option value="canino">Perros</option>
              <option value="felino">Gatos</option>
            </select>
          </div>

          <!-- Tamaño -->
          <div>
            <label for="f-size" class="label-govco">Tamaño</label>
            <select
              id="f-size"
              class="input-govco"
              v-model="localFilters.size"
              @change="emitFilters"
            >
              <option value="">Todos</option>
              <option value="pequenio">Pequeño</option>
              <option value="mediano">Mediano</option>
              <option value="grande">Grande</option>
              <option value="muy_grande">Muy grande</option>
            </select>
          </div>

          <!-- Edad -->
          <div>
            <label for="f-age" class="label-govco">Edad</label>
            <select
              id="f-age"
              class="input-govco"
              v-model="localFilters.ageRange"
              @change="emitFilters"
            >
              <option value="">Todas</option>
              <option value="young">Cachorro/Joven (&lt; 2 años)</option>
              <option value="adult">Adulto (2–8 años)</option>
              <option value="senior">Senior (&gt; 8 años)</option>
            </select>
          </div>

          <!-- Sexo -->
          <div>
            <label for="f-sex" class="label-govco">Sexo</label>
            <select
              id="f-sex"
              class="input-govco"
              v-model="localFilters.sex"
              @change="emitFilters"
            >
              <option value="">Ambos</option>
              <option value="macho">Macho</option>
              <option value="hembra">Hembra</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Listado -->
    <div class="list-header">
      <h2 class="h4-tipografia-govco govcolor-blue-dark">
        Animales disponibles para adopción
      </h2>
      <span class="text2-tipografia-govco">
        {{ animals.length }} resultado(s)
      </span>
    </div>

    <div v-if="loading" class="state-card">
      Cargando animales...
    </div>

    <div v-else-if="!animals.length" class="state-card">
      No hay animales disponibles con los filtros seleccionados.
    </div>

    <div v-else class="cards-grid">
      <article
        v-for="animal in animals"
        :key="animal.id"
        class="animal-card card-govco"
      >
        <!-- Carrusel de imagenes -->
        <div class="image-carousel">
          <div class="carousel-container" :style="{ transform: `translateX(-${(animal._currentSlide || 0) * 100}%)` }">
            <!-- Foto principal -->
            <div class="carousel-slide">
              <img
                :src="getPhotoUrl(animal)"
                :alt="animal.name || animal.nombre"
                @error="(e) => onImageError(e, animal)"
              />
            </div>
            <!-- Fotos de galeria -->
            <div
              v-for="(foto, idx) in getGalleryPhotos(animal)"
              :key="idx"
              class="carousel-slide"
            >
              <img
                :src="foto"
                :alt="`${animal.name || animal.nombre} - Foto ${idx + 2}`"
                @error="(e) => onImageError(e, animal)"
              />
            </div>
          </div>
          <!-- Indicadores de carrusel -->
          <div v-if="getTotalPhotos(animal) > 1" class="carousel-indicators">
            <button
              v-for="(_, idx) in getTotalPhotos(animal)"
              :key="idx"
              class="indicator"
              :class="{ active: (animal._currentSlide || 0) === idx }"
              @click="setSlide(animal, idx)"
            ></button>
          </div>
          <!-- Flechas de navegación -->
          <template v-if="getTotalPhotos(animal) > 1">
            <button class="carousel-arrow prev" @click.stop="prevSlide(animal)">‹</button>
            <button class="carousel-arrow next" @click.stop="nextSlide(animal)">›</button>
          </template>
          <!-- Badge de estado -->
          <span class="status-pill badge-govco badge-govco-primary">
            {{ animal.statusLabel || 'Disponible' }}
          </span>
          <!-- Contador de fotos -->
          <span v-if="getTotalPhotos(animal) > 1" class="photo-counter">
            {{ (animal._currentSlide || 0) + 1 }}/{{ getTotalPhotos(animal) }}
          </span>
        </div>

        <div class="card-govco-body">
          <h3 class="h5-tipografia-govco">{{ animal.name || animal.nombre || animal.codigo_unico }}</h3>
          <p class="text2-tipografia-govco meta">
            {{ formatSpecies(animal.species || animal.especie) }} ·
            {{ capitalize(animal.sex || animal.sexo) }} ·
            {{ formatSize(animal.size || animal.tamanio) }}
          </p>
          <p v-if="animal.edad_formateada || animal.edad_aproximada" class="text2-tipografia-govco age-info">
            {{ animal.edad_formateada || formatAge(animal.edad_aproximada) }}
          </p>
          <p class="text2-tipografia-govco description">
            {{ animal.shortDescription || animal.observaciones || `${animal.raza || 'Mestizo'} de ${animal.color || 'color variado'}.` }}
          </p>

          <button
            type="button"
            class="btn-govco btn-govco-primary"
            @click="$emit('request-adoption', animal)"
          >
            Quiero adoptar
          </button>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
import { reactive, watch } from 'vue';
import { resolveAnimalImageUrl, handleImageError as handleImgError, getSpeciesPlaceholder } from '@/utils/animalImages';

const props = defineProps({
  animals: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:filters', 'request-adoption']);

// Copia local de filtros para trabajar el v-model del select
const localFilters = reactive({
  species: props.filters.species || '',
  size: props.filters.size || '',
  ageRange: props.filters.ageRange || '',
  sex: props.filters.sex || '',
});

// Si cambian los filtros desde el padre, sincronizamos
watch(
  () => props.filters,
  (newVal) => {
    localFilters.species = newVal.species || '';
    localFilters.size = newVal.size || '';
    localFilters.ageRange = newVal.ageRange || '';
    localFilters.sex = newVal.sex || '';
  },
  { deep: true }
);

function emitFilters() {
  emit('update:filters', { ...localFilters });
}

function capitalize(value) {
  if (!value) return '';
  return value.charAt(0).toUpperCase() + value.slice(1);
}

// Obtener especie del animal
function getAnimalEspecie(animal) {
  return (animal?.especie || animal?.species || 'otro').toString().toLowerCase();
}

// Obtener semilla para placeholder consistente
function getAnimalSeed(animal) {
  return animal?.id || animal?.codigo_unico || 0;
}

// Funciones para el carrusel de imagenes
function getPhotoUrl(animal) {
  const url = animal?.foto_url || animal?.photoUrl || animal?.url_foto_principal;
  return resolveAnimalImageUrl(url, getAnimalEspecie(animal), getAnimalSeed(animal));
}

// Resolver URL de galeria
function resolveGalleryUrl(url, animal) {
  if (!url) return getSpeciesPlaceholder(getAnimalEspecie(animal), getAnimalSeed(animal));
  const s = String(url);
  if (/^(https?:)?\/\//i.test(s) || s.startsWith('data:') || s.startsWith('blob:')) return s;
  if (s.includes('/storage/')) {
    return s.startsWith('http') ? s : `${window.location.origin}${s.startsWith('/') ? '' : '/'}${s}`;
  }
  return `${window.location.origin}/storage/${s.replace(/^\/+/, '')}`;
}

function getGalleryPhotos(animal) {
  const galeria = animal.galeria_urls || animal.galeria_fotos || [];
  if (!Array.isArray(galeria)) return [];
  return galeria.map(foto => resolveGalleryUrl(foto, animal));
}

// Manejador de error de imagen
function onImageError(event, animal) {
  handleImgError(event, getAnimalEspecie(animal), getAnimalSeed(animal));
}

function getTotalPhotos(animal) {
  const galeria = getGalleryPhotos(animal);
  return 1 + galeria.length; // foto principal + galería
}

function setSlide(animal, index) {
  animal._currentSlide = index;
}

function nextSlide(animal) {
  const total = getTotalPhotos(animal);
  const current = animal._currentSlide || 0;
  animal._currentSlide = (current + 1) % total;
}

function prevSlide(animal) {
  const total = getTotalPhotos(animal);
  const current = animal._currentSlide || 0;
  animal._currentSlide = current === 0 ? total - 1 : current - 1;
}

// Formateadores
function formatSpecies(species) {
  const labels = {
    canino: 'Perro',
    felino: 'Gato',
    equino: 'Equino',
    ave: 'Ave',
    otro: 'Otro'
  };
  return labels[species?.toLowerCase()] || capitalize(species) || 'No especificada';
}

function formatSize(size) {
  const labels = {
    pequenio: 'Pequeño',
    mediano: 'Mediano',
    grande: 'Grande',
    muy_grande: 'Muy grande'
  };
  return labels[size] || size || 'N/A';
}

function formatAge(meses) {
  if (!meses) return '';
  const anios = Math.floor(meses / 12);
  const mesesRestantes = meses % 12;
  const partes = [];
  if (anios > 0) partes.push(`${anios} año${anios > 1 ? 's' : ''}`);
  if (mesesRestantes > 0) partes.push(`${mesesRestantes} mes${mesesRestantes > 1 ? 'es' : ''}`);
  return partes.join(' y ') || '';
}
</script>

<style scoped>
.filters-card {
  margin-bottom: 16px;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 16px 0;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.animal-card {
  display: flex;
  flex-direction: column;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.animal-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

/* Carrusel de imágenes */
.image-carousel {
  position: relative;
  width: 100%;
  aspect-ratio: 4 / 3;
  overflow: hidden;
  background: #f0f0f0;
}

.carousel-container {
  display: flex;
  height: 100%;
  transition: transform 0.3s ease-in-out;
}

.carousel-slide {
  min-width: 100%;
  height: 100%;
}

.carousel-slide img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
}

/* Flechas de navegación */
.carousel-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 32px;
  height: 32px;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  border-radius: 50%;
  font-size: 1.25rem;
  font-weight: bold;
  color: #333;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
  z-index: 10;
}

.image-carousel:hover .carousel-arrow {
  opacity: 1;
}

.carousel-arrow.prev {
  left: 8px;
}

.carousel-arrow.next {
  right: 8px;
}

.carousel-arrow:hover {
  background: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

/* Indicadores */
.carousel-indicators {
  position: absolute;
  bottom: 8px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 6px;
  z-index: 10;
}

.indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.5);
  border: none;
  cursor: pointer;
  padding: 0;
  transition: background 0.2s;
}

.indicator.active {
  background: white;
}

.indicator:hover {
  background: rgba(255, 255, 255, 0.8);
}

/* Badges sobre la imagen */
.status-pill {
  position: absolute;
  top: 8px;
  left: 8px;
  z-index: 10;
}

.photo-counter {
  position: absolute;
  top: 8px;
  right: 8px;
  background: rgba(0, 0, 0, 0.6);
  color: white;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
  z-index: 10;
}

/* Contenido de la tarjeta */
.card-govco-body {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.card-govco-body h3 {
  margin: 0;
  color: #004884;
}

.meta {
  color: #606060;
  margin: 0;
}

.age-info {
  color: #068460;
  font-weight: 500;
  margin: 0;
}

.description {
  margin: 0;
  color: #4B4B4B;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.btn-govco-primary {
  margin-top: auto;
}

.state-card {
  background: #ffffff;
  border-radius: 8px;
  padding: 24px;
  text-align: center;
}

@media (max-width: 768px) {
  .filters-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .cards-grid {
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  }
}

@media (max-width: 576px) {
  .filters-grid {
    grid-template-columns: 1fr;
  }

  .cards-grid {
    grid-template-columns: 1fr;
  }
}
</style>
