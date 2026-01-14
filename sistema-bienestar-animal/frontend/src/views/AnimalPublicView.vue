<!-- src/views/AnimalPublicView.vue -->
<!-- Página pública para compartir animales en redes sociales -->
<template>
  <div class="animal-public-view">
    <!-- Loading -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Cargando información del animal...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="error-container">
      <div class="error-icon">🐾</div>
      <h2>Animal no encontrado</h2>
      <p>{{ error }}</p>
      <router-link to="/adopciones" class="btn-govco btn-govco-primary">
        Ver todos los animales disponibles
      </router-link>
    </div>

    <!-- Contenido del animal -->
    <div v-else-if="animal" class="animal-content">
      <!-- Header con imagen principal -->
      <div class="animal-header">
        <div class="image-gallery">
          <div class="main-image">
            <img
              :src="mainPhotoUrl"
              :alt="animal.nombre"
              @error="onImageError"
            />
            <span class="status-badge" :class="statusClass">
              {{ animal.estado_adopcion || 'Disponible' }}
            </span>
          </div>
          <div v-if="galleryPhotos.length > 0" class="thumbnail-strip">
            <button
              v-for="(foto, idx) in allPhotos"
              :key="idx"
              class="thumbnail"
              :class="{ active: currentPhotoIndex === idx }"
              @click="currentPhotoIndex = idx"
            >
              <img :src="foto" :alt="`Foto ${idx + 1}`" @error="onImageError" />
            </button>
          </div>
        </div>

        <div class="animal-info">
          <h1 class="animal-name">{{ animal.nombre || animal.codigo_unico }}</h1>
          <p class="animal-code">{{ animal.codigo_unico }}</p>

          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Especie</span>
              <span class="info-value">{{ formatSpecies(animal.especie) }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Raza</span>
              <span class="info-value">{{ animal.raza || 'Mestizo' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Sexo</span>
              <span class="info-value">{{ capitalize(animal.sexo) }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Tamaño</span>
              <span class="info-value">{{ formatSize(animal.tamanio) }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Edad</span>
              <span class="info-value">{{ animal.edad_formateada || formatAge(animal.edad_aproximada) || 'No especificada' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Color</span>
              <span class="info-value">{{ animal.color || 'Variado' }}</span>
            </div>
          </div>

          <!-- Características -->
          <div v-if="animal.esterilizado || animal.vacunado" class="characteristics">
            <span v-if="animal.esterilizado" class="char-badge char-sterilized">
              ✓ Esterilizado
            </span>
            <span v-if="animal.vacunado" class="char-badge char-vaccinated">
              ✓ Vacunado
            </span>
          </div>

          <!-- Descripción -->
          <div v-if="animal.observaciones" class="description-section">
            <h3>Sobre {{ animal.nombre || 'este amiguito' }}</h3>
            <p>{{ animal.observaciones }}</p>
          </div>

          <!-- Botones de acción -->
          <div class="action-buttons">
            <router-link
              to="/adopciones"
              class="btn-govco btn-govco-primary btn-large"
            >
              ¡Quiero adoptarlo!
            </router-link>
          </div>

          <!-- Compartir -->
          <div class="share-section">
            <span class="share-title">Comparte y ayúdalo a encontrar un hogar:</span>
            <div class="share-buttons">
              <button
                type="button"
                class="share-btn share-facebook"
                @click="shareOnFacebook"
                title="Compartir en Facebook"
              >
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                Facebook
              </button>
              <button
                type="button"
                class="share-btn share-twitter"
                @click="shareOnTwitter"
                title="Compartir en X (Twitter)"
              >
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                X
              </button>
              <button
                type="button"
                class="share-btn share-whatsapp"
                @click="shareOnWhatsApp"
                title="Compartir en WhatsApp"
              >
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
              </button>
              <button
                type="button"
                class="share-btn share-copy"
                @click="copyLink"
                title="Copiar enlace"
              >
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                Copiar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Más animales disponibles -->
      <div class="more-animals-section">
        <h2>¿Buscas más amigos peludos?</h2>
        <p>Tenemos muchos más animales esperando por un hogar lleno de amor.</p>
        <router-link to="/adopciones" class="btn-govco btn-govco-secondary">
          Ver todos los animales disponibles
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import animalService from '@/services/animalService';
import { resolveAnimalImageUrl, handleImageError, getSpeciesPlaceholder } from '@/utils/animalImages';

const route = useRoute();

const animal = ref(null);
const loading = ref(true);
const error = ref(null);
const currentPhotoIndex = ref(0);

// Obtener URL base para compartir
const getBaseUrl = () => {
  return window.location.origin;
};

// Foto principal
const mainPhotoUrl = computed(() => {
  if (!animal.value) return '';
  const photos = allPhotos.value;
  return photos[currentPhotoIndex.value] || getSpeciesPlaceholder(animal.value.especie, animal.value.id);
});

// Todas las fotos
const allPhotos = computed(() => {
  if (!animal.value) return [];
  const photos = [];

  // Foto principal
  const mainUrl = animal.value.foto_url || animal.value.url_foto_principal;
  if (mainUrl) {
    photos.push(resolveAnimalImageUrl(mainUrl, animal.value.especie, animal.value.id));
  }

  // Galería
  const galeria = animal.value.galeria_urls || animal.value.galeria_fotos || [];
  if (Array.isArray(galeria)) {
    galeria.forEach(foto => {
      if (foto) {
        photos.push(resolveGalleryUrl(foto));
      }
    });
  }

  // Si no hay fotos, usar placeholder
  if (photos.length === 0) {
    photos.push(getSpeciesPlaceholder(animal.value.especie, animal.value.id));
  }

  return photos;
});

const galleryPhotos = computed(() => {
  return allPhotos.value.slice(1);
});

const statusClass = computed(() => {
  const estado = animal.value?.estado_adopcion?.toLowerCase() || 'disponible';
  return `status-${estado.replace(/\s+/g, '-')}`;
});

// Resolver URL de galería
function resolveGalleryUrl(url) {
  if (!url) return '';
  const s = String(url);
  if (/^(https?:)?\/\//i.test(s) || s.startsWith('data:') || s.startsWith('blob:')) return s;
  if (s.includes('/storage/')) {
    return s.startsWith('http') ? s : `${window.location.origin}${s.startsWith('/') ? '' : '/'}${s}`;
  }
  return `${window.location.origin}/storage/${s.replace(/^\/+/, '')}`;
}

function onImageError(event) {
  const especie = animal.value?.especie || 'otro';
  const seed = animal.value?.id || 0;
  handleImageError(event, especie, seed);
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

function capitalize(value) {
  if (!value) return '';
  return value.charAt(0).toUpperCase() + value.slice(1);
}

// Compartir en redes sociales
function getShareText() {
  if (!animal.value) return '';
  const nombre = animal.value.nombre || 'Este amiguito';
  const especie = formatSpecies(animal.value.especie);
  const edad = animal.value.edad_formateada || formatAge(animal.value.edad_aproximada) || '';

  let texto = `¡Conoce a ${nombre}! 🐾 ${especie}`;
  if (animal.value.sexo) texto += ` ${animal.value.sexo.toLowerCase()}`;
  if (edad) texto += `, ${edad}`;
  texto += `. Está buscando un hogar lleno de amor. ❤️ #AdoptaNoCompres #BienestarAnimal`;

  return texto;
}

function shareOnFacebook() {
  const url = window.location.href;
  const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
  window.open(shareUrl, '_blank', 'width=600,height=400,scrollbars=yes');
}

function shareOnTwitter() {
  const url = window.location.href;
  const text = getShareText();
  const shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`;
  window.open(shareUrl, '_blank', 'width=600,height=400,scrollbars=yes');
}

function shareOnWhatsApp() {
  const url = window.location.href;
  const text = getShareText();
  const fullText = `${text}\n\n${url}`;
  const shareUrl = `https://wa.me/?text=${encodeURIComponent(fullText)}`;
  window.open(shareUrl, '_blank');
}

async function copyLink() {
  try {
    await navigator.clipboard.writeText(window.location.href);
    if (window.$toast) {
      window.$toast.success('Enlace copiado', 'El enlace ha sido copiado al portapapeles');
    } else {
      alert('Enlace copiado al portapapeles');
    }
  } catch (err) {
    console.error('Error al copiar:', err);
  }
}

// Cargar animal
async function loadAnimal() {
  loading.value = true;
  error.value = null;

  try {
    const animalId = route.params.id;
    const response = await animalService.getAnimalPublic(animalId);

    if (response.data) {
      animal.value = response.data;
    } else {
      error.value = 'No se encontró el animal solicitado.';
    }
  } catch (err) {
    console.error('Error cargando animal:', err);
    if (err.response?.status === 404) {
      error.value = 'Este animal ya no está disponible o no existe.';
    } else {
      error.value = 'Error al cargar la información del animal.';
    }
  } finally {
    loading.value = false;
  }
}

// Meta tags para Open Graph (compartir en redes)
function updateMetaTags() {
  if (!animal.value) return;

  const title = `${animal.value.nombre || 'Animal'} busca hogar - Sistema Bienestar Animal`;
  const description = getShareText();
  const imageUrl = mainPhotoUrl.value;

  // Actualizar titulo
  document.title = title;

  // Funcion helper para crear o actualizar meta tags
  const setMetaTag = (attr, attrValue, content) => {
    let element = document.querySelector(`meta[${attr}="${attrValue}"]`);
    if (!element) {
      element = document.createElement('meta');
      element.setAttribute(attr, attrValue);
      document.head.appendChild(element);
    }
    element.setAttribute('content', content);
  };

  // Meta descripcion
  setMetaTag('name', 'description', description);

  // Open Graph
  setMetaTag('property', 'og:title', title);
  setMetaTag('property', 'og:description', description);
  setMetaTag('property', 'og:image', imageUrl);
  setMetaTag('property', 'og:url', window.location.href);
  setMetaTag('property', 'og:type', 'website');
  setMetaTag('property', 'og:site_name', 'Sistema Bienestar Animal');

  // Twitter Card
  setMetaTag('name', 'twitter:card', 'summary_large_image');
  setMetaTag('name', 'twitter:title', title);
  setMetaTag('name', 'twitter:description', description);
  setMetaTag('name', 'twitter:image', imageUrl);
}

watch(animal, () => {
  updateMetaTags();
}, { immediate: true });

onMounted(() => {
  loadAnimal();
});
</script>

<style scoped>
.animal-public-view {
  min-height: 100vh;
  background: #f5f7fb;
}

/* Loading */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  gap: 16px;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e0e0e0;
  border-top-color: #3366cc;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Error */
.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  padding: 40px;
  text-align: center;
}

.error-icon {
  font-size: 64px;
  margin-bottom: 16px;
}

.error-container h2 {
  color: #004884;
  margin-bottom: 8px;
}

.error-container p {
  color: #666;
  margin-bottom: 24px;
}

/* Contenido */
.animal-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px;
}

.animal-header {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 32px;
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

/* Galería de imágenes */
.image-gallery {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.main-image {
  position: relative;
  width: 100%;
  aspect-ratio: 4 / 3;
  border-radius: 12px;
  overflow: hidden;
  background: #f0f0f0;
}

.main-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.status-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: capitalize;
}

.status-disponible {
  background: #d4edda;
  color: #155724;
}

.status-en-proceso,
.status-reservado {
  background: #fff3cd;
  color: #856404;
}

.status-adoptado {
  background: #cce5ff;
  color: #004085;
}

.thumbnail-strip {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  padding: 4px;
}

.thumbnail {
  flex-shrink: 0;
  width: 64px;
  height: 64px;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid transparent;
  cursor: pointer;
  padding: 0;
  background: none;
  transition: border-color 0.2s;
}

.thumbnail.active {
  border-color: #3366cc;
}

.thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Info del animal */
.animal-info {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.animal-name {
  font-size: 2rem;
  color: #004884;
  margin: 0;
}

.animal-code {
  color: #666;
  font-size: 0.9rem;
  margin: 0;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.info-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #666;
  text-transform: uppercase;
}

.info-value {
  font-size: 1rem;
  color: #333;
}

/* Características */
.characteristics {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.char-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
}

.char-sterilized {
  background: #e8f5e9;
  color: #2e7d32;
}

.char-vaccinated {
  background: #e3f2fd;
  color: #1565c0;
}

/* Descripción */
.description-section {
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
}

.description-section h3 {
  margin: 0 0 8px 0;
  font-size: 1rem;
  color: #004884;
}

.description-section p {
  margin: 0;
  color: #4b4b4b;
  line-height: 1.6;
}

/* Botones de acción */
.action-buttons {
  padding-top: 16px;
}

.btn-large {
  padding: 14px 32px;
  font-size: 1.1rem;
}

/* Compartir */
.share-section {
  padding-top: 16px;
  border-top: 1px solid #eee;
}

.share-title {
  display: block;
  font-size: 0.9rem;
  color: #666;
  margin-bottom: 12px;
}

.share-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.share-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border: none;
  border-radius: 20px;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 500;
  transition: transform 0.2s, opacity 0.2s;
}

.share-btn:hover {
  transform: scale(1.05);
  opacity: 0.9;
}

.share-btn svg {
  width: 18px;
  height: 18px;
}

.share-facebook {
  background: #1877F2;
  color: white;
}

.share-twitter {
  background: #000000;
  color: white;
}

.share-whatsapp {
  background: #25D366;
  color: white;
}

.share-copy {
  background: #6c757d;
  color: white;
}

/* Más animales */
.more-animals-section {
  margin-top: 32px;
  padding: 32px;
  background: white;
  border-radius: 16px;
  text-align: center;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.more-animals-section h2 {
  color: #004884;
  margin: 0 0 8px 0;
}

.more-animals-section p {
  color: #666;
  margin: 0 0 20px 0;
}

/* Responsive */
@media (max-width: 768px) {
  .animal-header {
    grid-template-columns: 1fr;
  }

  .animal-name {
    font-size: 1.5rem;
  }

  .info-grid {
    grid-template-columns: 1fr 1fr;
  }

  .share-buttons {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .animal-content {
    padding: 16px;
  }

  .animal-header {
    padding: 16px;
  }

  .share-btn {
    padding: 8px 12px;
    font-size: 0.8rem;
  }

  .share-btn span {
    display: none;
  }
}
</style>
