/**
 * Utilidades para manejo de imagenes de animales
 * Incluye placeholders reales por especie usando picsum.photos/unsplash
 */

// Imagenes placeholder por especie usando URLs de imagenes reales
// Usamos Lorem Picsum con seeds especificos para obtener imagenes consistentes
const SPECIES_PLACEHOLDERS = {
  // Perros - multiples opciones para variedad
  perro: [
    'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400&h=400&fit=crop&auto=format',
    'https://images.unsplash.com/photo-1561037404-61cd46aa615b?w=400&h=400&fit=crop&auto=format',
    'https://images.unsplash.com/photo-1518717758536-85ae29035b6d?w=400&h=400&fit=crop&auto=format',
    'https://images.unsplash.com/photo-1477884213360-7e9d7dcc1e48?w=400&h=400&fit=crop&auto=format',
    'https://images.unsplash.com/photo-1534361960057-19889db9621e?w=400&h=400&fit=crop&auto=format',
    'https://images.unsplash.com/photo-1552053831-71594a27632d?w=400&h=400&fit=crop&auto=format',
  ],
  canino: 'perro', // alias

  // Gatos - multiples opciones para variedad
  gato: [
    'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400&h=400&fit=crop&auto=format',
    'https://images.unsplash.com/photo-1573865526739-10659fec78a5?w=400&h=400&fit=crop&auto=format',
    'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?w=400&h=400&fit=crop&auto=format',
    'https://images.unsplash.com/photo-1526336024174-e58f5cdd8e13?w=400&h=400&fit=crop&auto=format',
    'https://images.unsplash.com/photo-1494256997604-768d1f608cac?w=400&h=400&fit=crop&auto=format',
    'https://images.unsplash.com/photo-1533738363-b7f9aef128ce?w=400&h=400&fit=crop&auto=format',
  ],
  felino: 'gato', // alias

  // Equinos
  equino: [
    'https://images.unsplash.com/photo-1553284965-83fd3e82fa5a?w=400&h=400&fit=crop&auto=format',
    'https://images.unsplash.com/photo-1534773728080-33d4c294f726?w=400&h=400&fit=crop&auto=format',
    'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=400&fit=crop&auto=format',
  ],
  caballo: 'equino', // alias

  // Ave
  ave: [
    'https://images.unsplash.com/photo-1522926193341-e9ffd686c60f?w=400&h=400&fit=crop&auto=format',
    'https://images.unsplash.com/photo-1444464666168-49d633b86797?w=400&h=400&fit=crop&auto=format',
  ],

  // Conejo
  conejo: [
    'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?w=400&h=400&fit=crop&auto=format',
    'https://images.unsplash.com/photo-1452857297128-d9c29adba80b?w=400&h=400&fit=crop&auto=format',
  ],

  // Default/Otro - huella de mascota generica (SVG)
  otro: [
    'https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=400&h=400&fit=crop&auto=format',
  ],
  default: [
    'https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=400&h=400&fit=crop&auto=format',
  ]
};

// SVG placeholder de respaldo (huella de mascota) - se usa si falla la carga de imagen
export const FALLBACK_SVG = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iNDAwIiB2aWV3Qm94PSIwIDAgNDAwIDQwMCI+PHJlY3Qgd2lkdGg9IjQwMCIgaGVpZ2h0PSI0MDAiIGZpbGw9IiNmNWY1ZjUiLz48ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyMDAsMjAwKSI+PGVsbGlwc2UgY3g9IjAiIGN5PSIzMCIgcng9IjQ1IiByeT0iNDAiIGZpbGw9IiNkZGQiLz48ZWxsaXBzZSBjeD0iLTU1IiBjeT0iLTEwIiByeD0iMjIiIHJ5PSIyOCIgZmlsbD0iI2RkZCIvPjxlbGxpcHNlIGN4PSI1NSIgY3k9Ii0xMCIgcng9IjIyIiByeT0iMjgiIGZpbGw9IiNkZGQiLz48ZWxsaXBzZSBjeD0iLTMwIiBjeT0iLTU1IiByeD0iMTgiIHJ5PSIyMiIgZmlsbD0iI2RkZCIvPjxlbGxpcHNlIGN4PSIzMCIgY3k9Ii01NSIgcng9IjE4IiByeT0iMjIiIGZpbGw9IiNkZGQiLz48L2c+PC9zdmc+';

/**
 * Obtiene una imagen placeholder basada en la especie del animal
 * @param {string} especie - La especie del animal (perro, gato, equino, etc.)
 * @param {string|number} seed - Semilla para seleccionar una imagen consistente (puede ser el ID del animal)
 * @returns {string} URL de la imagen placeholder
 */
export function getSpeciesPlaceholder(especie, seed = 0) {
  const specieNormalized = (especie || '').toString().toLowerCase().trim();

  // Resolver alias
  let images = SPECIES_PLACEHOLDERS[specieNormalized];
  if (typeof images === 'string') {
    images = SPECIES_PLACEHOLDERS[images];
  }

  // Si no hay imagenes para la especie, usar default
  if (!images || !Array.isArray(images) || images.length === 0) {
    images = SPECIES_PLACEHOLDERS.default;
  }

  // Usar el seed para seleccionar una imagen consistente
  const numericSeed = typeof seed === 'number' ? seed : hashString(String(seed || ''));
  const index = Math.abs(numericSeed) % images.length;

  return images[index];
}

/**
 * Genera un hash numerico simple de un string
 * @param {string} str - String a hashear
 * @returns {number} Hash numerico
 */
function hashString(str) {
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    const char = str.charCodeAt(i);
    hash = ((hash << 5) - hash) + char;
    hash = hash & hash; // Convertir a 32bit integer
  }
  return hash;
}

/**
 * Resuelve la URL de una imagen de animal, con fallback a placeholder por especie
 * @param {string|null} imageUrl - URL de la imagen original
 * @param {string} especie - Especie del animal para el placeholder
 * @param {string|number} seed - Semilla para placeholder consistente
 * @returns {string} URL resuelta de la imagen
 */
export function resolveAnimalImageUrl(imageUrl, especie = 'otro', seed = 0) {
  if (!imageUrl) {
    return getSpeciesPlaceholder(especie, seed);
  }

  const url = String(imageUrl);

  // Ya es URL absoluta, data URL o blob
  if (/^(https?:)?\/\//i.test(url) || url.startsWith('data:') || url.startsWith('blob:')) {
    return url;
  }

  // Si contiene /storage/, completar URL
  if (url.includes('/storage/')) {
    return url.startsWith('http') ? url : `${window.location.origin}${url.startsWith('/') ? '' : '/'}${url}`;
  }

  // Path relativo tipico de Laravel
  const clean = url.replace(/^\/+/, '');
  return `${window.location.origin}/storage/${clean}`;
}

/**
 * Manejador de error para imagenes - reemplaza con placeholder por especie
 * @param {Event} event - Evento de error de la imagen
 * @param {string} especie - Especie del animal
 * @param {string|number} seed - Semilla para placeholder
 */
export function handleImageError(event, especie = 'otro', seed = 0) {
  const img = event.target;
  const placeholder = getSpeciesPlaceholder(especie, seed);

  // Evitar loop infinito si el placeholder tambien falla
  if (img.src === placeholder || img.dataset.fallbackAttempted) {
    img.src = FALLBACK_SVG;
    return;
  }

  img.dataset.fallbackAttempted = 'true';
  img.src = placeholder;
}

/**
 * Crea un manejador de error personalizado para un animal especifico
 * @param {string} especie - Especie del animal
 * @param {string|number} seed - Semilla para placeholder
 * @returns {Function} Funcion manejadora de errores
 */
export function createImageErrorHandler(especie, seed) {
  return (event) => handleImageError(event, especie, seed);
}

export default {
  getSpeciesPlaceholder,
  resolveAnimalImageUrl,
  handleImageError,
  createImageErrorHandler,
  FALLBACK_SVG
};
