<!-- src/views/AdoptionsView.vue -->
<template>
  <section class="adoptions-view">
    <div class="adoptions-container">

      <!-- Header -->
      <header class="adoptions-header">
        <div>
          <h1 class="h2-tipografia-govco govcolor-blue-dark">
            Adopciones Responsables
          </h1>
          <p class="text2-tipografia-govco">
            Encuentra tu compañero ideal. Todos nuestros animales están vacunados,
            esterilizados y listos para formar parte de tu familia.
          </p>
        </div>

        <div class="header-actions">
          <!-- Botón para consultar estado de adopción -->
          <RouterLink
            to="/adopciones/consultar"
            class="btn-govco btn-govco-outline"
          >
            Consultar mi solicitud
          </RouterLink>
        </div>
      </header>

      <!-- 🚨 IMPORTANTE: le pasamos filteredAnimals, NO animals -->
      <AdoptionList
        :animals="filteredAnimals"
        :filters="filters"
        :loading="loadingAnimals"
        @update:filters="updateFilters"
        @request-adoption="openAdoptionForm"
      />

      <!-- Formulario de solicitud (modal) -->
      <AdoptionForm
        v-if="showAdoptionForm"
        :animal="selectedAnimal"
        :loading="submitting"
        @close="closeAdoptionForm"
        @submitted="handleAdoptionSubmitted"
      />
    </div>
  </section>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import AdoptionList from '../components/adoptions/AdoptionList.vue';
import AdoptionForm from '../components/adoptions/AdoptionForm.vue';
import animalService from '@/services/animalService';

// Filtros de catálogo (estado global de filtros)
const filters = reactive({
  species: '',
  size: '',
  ageRange: '',
  sex: '',
});

// Estado de animales
const animals = ref([]);
const loadingAnimals = ref(false);

// Modal de solicitud
const showAdoptionForm = ref(false);
const selectedAnimal = ref(null);
const submitting = ref(false);

// Lista filtrada (filtrado en el front)
const filteredAnimals = computed(() => {
  return animals.value.filter((animal) => {
    // Mapear campos del backend a los esperados por el filtro
    const especie = animal.especie || animal.species;
    const tamanio = animal.tamanio || animal.size;
    const sexo = animal.sexo || animal.sex;
    const edad = animal.edad_aproximada ? Math.floor(animal.edad_aproximada / 12) : (animal.ageInYears ?? animal.age);

    if (filters.species && especie !== filters.species) return false;
    if (filters.size && tamanio !== filters.size) return false;
    if (filters.sex && sexo !== filters.sex) return false;

    if (filters.ageRange) {
      if (filters.ageRange === 'young' && edad >= 2) return false;
      if (filters.ageRange === 'adult' && (edad < 2 || edad > 8)) return false;
      if (filters.ageRange === 'senior' && edad <= 8) return false;
    }

    return true;
  });
});

async function loadAnimals() {
  loadingAnimals.value = true;
  try {
    // Solicitar todos los animales disponibles (sin límite de paginación)
    const response = await animalService.getCatalogoAdopcion({ all: 'true' });
    // El backend devuelve solo animales en_adopcion, saludables y sin adopción activa
    const data = response.data || response;
    const rawAnimals = Array.isArray(data) ? data : data.data || [];

    // Inicializar _currentSlide para el carrusel de cada animal
    animals.value = rawAnimals.map(animal => ({
      ...animal,
      _currentSlide: 0,
      // Labels de compatibilidad para el template
      name: animal.nombre || animal.codigo_unico,
      species: animal.especie,
      sex: animal.sexo,
      size: animal.tamanio,
      statusLabel: 'Disponible',
      photoUrl: animal.foto_url
    }));

    console.log(`📋 Catálogo de adopción: ${animals.value.length} animales cargados`);
  } catch (error) {
    console.error('Error al cargar animales:', error);
    animals.value = [];
  } finally {
    loadingAnimals.value = false;
  }
}

// 👉 Esta función se llama cuando cambias un filtro en AdoptionList
function updateFilters(newFilters) {
  Object.assign(filters, newFilters);
}

function openAdoptionForm(animal) {
  selectedAnimal.value = animal;
  showAdoptionForm.value = true;
}

function closeAdoptionForm() {
  showAdoptionForm.value = false;
  selectedAnimal.value = null;
}

async function handleAdoptionSubmitted(response) {
  console.log('Solicitud de adopcion enviada:', response);

  // Mostrar mensaje de exito
  if (window.$toast) {
    window.$toast.success(
      'Solicitud enviada exitosamente',
      'Tu solicitud de adopcion fue enviada correctamente. Nos pondremos en contacto contigo pronto para continuar con el proceso.'
    );
  } else {
    alert('Tu solicitud de adopcion fue enviada exitosamente. Te contactaremos pronto.');
  }

  // Cerrar el formulario
  closeAdoptionForm();

  // Recargar la lista de animales (el animal adoptado podria ya no estar disponible)
  await loadAnimals();
}

onMounted(loadAnimals);
</script>

<style scoped>
.adoptions-view {
  background: #f5f7fb;
  padding: 24px;
}

.adoptions-container {
  max-width: 1200px;
  margin: 0 auto;
}

.adoptions-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 16px;
}

.header-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.btn-govco-outline {
  background: transparent;
  border: 2px solid #3366cc;
  color: #3366cc;
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s;
}

.btn-govco-outline:hover {
  background: #3366cc;
  color: white;
}

@media (max-width: 768px) {
  .adoptions-header {
    flex-direction: column;
    align-items: stretch;
  }

  .header-actions {
    justify-content: center;
  }
}
</style>
