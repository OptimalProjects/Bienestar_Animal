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
              <option value="perro">Perros</option>
              <option value="gato">Gatos</option>
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
              <option value="pequeño">Pequeño</option>
              <option value="mediano">Mediano</option>
              <option value="grande">Grande</option>
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
        <div class="image-wrapper">
          <img
            :src="animal.photoUrl || '/placeholder-animal.jpg'"
            :alt="animal.name"
          />
          <span class="status-pill badge-govco badge-govco-primary">
            {{ animal.statusLabel || 'Disponible' }}
          </span>
        </div>

        <div class="card-govco-body">
          <h3 class="h5-tipografia-govco">{{ animal.name }}</h3>
          <p class="text2-tipografia-govco meta">
            {{ capitalize(animal.species) }} · {{ capitalize(animal.sex) }} ·
            {{ animal.size || 'N/A' }}
          </p>
          <p class="text2-tipografia-govco description">
            {{ animal.shortDescription || 'Animal en adopción responsable.' }}
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
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 16px;
}

.animal-card {
  display: flex;
  flex-direction: column;
}

.image-wrapper {
  position: relative;
  height: 180px;
  overflow: hidden;
}

.image-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.status-pill {
  position: absolute;
  top: 8px;
  left: 8px;
}

.meta {
  color: #606060;
}

.description {
  margin-top: 4px;
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
}

@media (max-width: 576px) {
  .filters-grid {
    grid-template-columns: 1fr;
  }
}
</style>
