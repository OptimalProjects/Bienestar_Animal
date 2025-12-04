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

/**
 * 🐾 MASCOTAS DE PRUEBA
 */
const mockAnimals = [
  {
    id: 1,
    name: 'Luna',
    species: 'perro',
    sex: 'hembra',
    size: 'mediano',
    ageInYears: 2,
    photoUrl: 'https://images.pexels.com/photos/4587991/pexels-photo-4587991.jpeg',
    statusLabel: 'Disponible',
    shortDescription: 'Perrita juguetona, sociable con otros perros y niños.'
  },
  {
    id: 2,
    name: 'Rocky',
    species: 'perro',
    sex: 'macho',
    size: 'grande',
    ageInYears: 4,
    photoUrl: 'https://images.pexels.com/photos/7210261/pexels-photo-7210261.jpeg',
    statusLabel: 'Disponible',
    shortDescription: 'Perro guardián muy noble, ideal para casa con patio.'
  },
  {
    id: 3,
    name: 'Michi',
    species: 'gato',
    sex: 'hembra',
    size: 'pequeño',
    ageInYears: 1,
    photoUrl: 'https://images.pexels.com/photos/45201/kitty-cat-kitten-pet-45201.jpeg',
    statusLabel: 'Disponible',
    shortDescription: 'Gatita curiosa y cariñosa, se adapta fácil a espacios pequeños.'
  },
  {
    id: 4,
    name: 'Simón',
    species: 'gato',
    sex: 'macho',
    size: 'pequeño',
    ageInYears: 6,
    photoUrl: 'https://images.pexels.com/photos/617278/pexels-photo-617278.jpeg',
    statusLabel: 'Disponible',
    shortDescription: 'Gato tranquilo, perfecto para compañía en apartamento.'
  }
];

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
    if (filters.species && animal.species !== filters.species) return false;
    if (filters.size && animal.size !== filters.size) return false;
    if (filters.sex && animal.sex !== filters.sex) return false;

    if (filters.ageRange) {
      const age = animal.ageInYears ?? animal.age;
      if (filters.ageRange === 'young' && age >= 2) return false;
      if (filters.ageRange === 'adult' && (age < 2 || age > 8)) return false;
      if (filters.ageRange === 'senior' && age <= 8) return false;
    }

    return true;
  });
});

function loadAnimals() {
  loadingAnimals.value = true;
  setTimeout(() => {
    animals.value = mockAnimals;
    loadingAnimals.value = false;
  }, 300);
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
  alert('✅ Tu solicitud de adopción fue enviada (mock).');
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
