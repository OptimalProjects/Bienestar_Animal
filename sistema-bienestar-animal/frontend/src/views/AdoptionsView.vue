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

        <!-- Botón temporal para ver gestión -->
        <RouterLink
          to="/adopciones/coordinador"
          class="btn-govco btn-govco-secondary"
        >
          Ver gestión de adopciones
        </RouterLink>
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
    const response = await animalService.getCatalogoAdopcion();
    // Mapear datos del backend al formato esperado por el componente
    const data = response.data || response;
    animals.value = (Array.isArray(data) ? data : data.data || []).map(animal => ({
      ...animal,
      // Mapear campos para compatibilidad con el template
      name: animal.nombre || animal.name,
      species: animal.especie || animal.species,
      sex: animal.sexo || animal.sex,
      size: animal.tamanio || animal.size,
      ageInYears: animal.edad_aproximada ? Math.floor(animal.edad_aproximada / 12) : animal.ageInYears,
      photoUrl: animal.url_foto_principal || animal.foto_principal || animal.photoUrl,
      statusLabel: animal.estado === 'en_adopcion' ? 'Disponible' : (animal.estado || 'Disponible'),
      shortDescription: animal.observaciones || animal.shortDescription || `${animal.raza || 'Mestizo'} de ${animal.color || 'varios colores'}.`
    }));
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

async function handleAdoptionSubmitted(formData) {
  console.log('Solicitud enviada (mock):', {
    ...formData,
    animalId: selectedAnimal.value?.id,
  });
  if (window.$toast) {
    window.$toast.success('Solicitud enviada', 'Tu solicitud de adopcion fue enviada correctamente. Te contactaremos pronto.');
  } else {
    alert('Tu solicitud de adopción fue enviada (mock).');
  }
  closeAdoptionForm();
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
</style>
