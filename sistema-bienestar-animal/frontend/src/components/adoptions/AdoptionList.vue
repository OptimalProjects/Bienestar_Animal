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

          <!-- Botones de compartir en redes sociales -->
          <div class="share-section">
            <span class="share-label">Compartir</span>
            <div class="share-icons">
              <button
                type="button"
                class="share-icon share-facebook"
                @click.stop="shareOnFacebook(animal)"
                title="Facebook"
              >
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
              </button>
              <button
                type="button"
                class="share-icon share-twitter"
                @click.stop="shareOnTwitter(animal)"
                title="X (Twitter)"
              >
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
              </button>
              <button
                type="button"
                class="share-icon share-whatsapp"
                @click.stop="shareOnWhatsApp(animal)"
                title="WhatsApp"
              >
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
              </button>
              <button
                type="button"
                class="share-icon share-copy"
                @click.stop="copyShareLink(animal)"
                title="Copiar enlace"
              >
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
              </button>
            </div>
          </div>

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

// ====== FUNCIONES DE COMPARTIR EN REDES SOCIALES ======

// URL base configurable para produccion
const getBaseUrl = () => {
  // En produccion, cambiar por el dominio real
  // Ejemplo: return 'https://bienestaranimal.gov.co';
  return window.location.origin;
};

// Generar URL publica del animal para compartir
function getAnimalShareUrl(animal) {
  const baseUrl = getBaseUrl();
  const animalId = animal.id || animal.codigo_unico;
  return `${baseUrl}/adopciones/animal/${animalId}`;
}

// Generar texto para compartir
function getShareText(animal) {
  const nombre = animal.name || animal.nombre || 'Este amiguito';
  const especie = formatSpecies(animal.species || animal.especie);
  const edad = animal.edad_formateada || formatAge(animal.edad_aproximada) || '';
  const sexo = capitalize(animal.sex || animal.sexo);

  let texto = `¡Conoce a ${nombre}! 🐾 ${especie}`;
  if (sexo) texto += ` ${sexo.toLowerCase()}`;
  if (edad) texto += `, ${edad}`;
  texto += `. Está buscando un hogar lleno de amor. ❤️ #AdoptaNoCompres #BienestarAnimal`;

  return texto;
}

// Compartir en Facebook
function shareOnFacebook(animal) {
  const url = getAnimalShareUrl(animal);
  const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
  window.open(shareUrl, '_blank', 'width=600,height=400,scrollbars=yes');
}

// Compartir en Twitter/X
function shareOnTwitter(animal) {
  const url = getAnimalShareUrl(animal);
  const text = getShareText(animal);
  const shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`;
  window.open(shareUrl, '_blank', 'width=600,height=400,scrollbars=yes');
}

// Compartir en WhatsApp
function shareOnWhatsApp(animal) {
  const url = getAnimalShareUrl(animal);
  const text = getShareText(animal);
  const fullText = `${text}\n\n${url}`;
  const shareUrl = `https://wa.me/?text=${encodeURIComponent(fullText)}`;
  window.open(shareUrl, '_blank');
}

// Copiar enlace al portapapeles
async function copyShareLink(animal) {
  const url = getAnimalShareUrl(animal);
  try {
    await navigator.clipboard.writeText(url);
    if (window.$toast) {
      window.$toast.success('Enlace copiado', 'El enlace ha sido copiado al portapapeles');
    } else {
      alert('Enlace copiado al portapapeles');
    }
  } catch (err) {
    console.error('Error al copiar:', err);
    // Fallback para navegadores antiguos
    const textArea = document.createElement('textarea');
    textArea.value = url;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);
    if (window.$toast) {
      window.$toast.success('Enlace copiado', 'El enlace ha sido copiado al portapapeles');
    }
  }
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

/* Sección de compartir */
.share-section {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 12px;
  padding-top: 10px;
  border-top: 1px solid #f0f0f0;
}

.share-label {
  font-size: 0.7rem;
  color: #999;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 500;
}

.share-icons {
  display: flex;
  align-items: center;
  gap: 6px;
}

.share-icon {
  width: 26px;
  height: 26px;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  padding: 0;
  opacity: 0.85;
}

.share-icon:hover {
  transform: translateY(-2px);
  opacity: 1;
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
}

.share-icon svg {
  width: 13px;
  height: 13px;
}

.share-facebook {
  background: #1877F2;
  color: white;
}

.share-twitter {
  background: #14171A;
  color: white;
}

.share-whatsapp {
  background: #25D366;
  color: white;
}

.share-copy {
  background: #868e96;
  color: white;
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
