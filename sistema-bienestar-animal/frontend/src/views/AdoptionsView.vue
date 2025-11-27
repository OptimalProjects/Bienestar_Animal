<!-- src/views/AdoptionsView.vue -->
<!-- Vista de adopciones con diseño GOV.CO -->
<template>
  <div class="adoptions-view">
    <div class="adoptions-container">
      <!-- Header -->
      <header class="adoptions-header">
        <h1 class="h2-tipografia-govco govcolor-blue-dark">Adopciones Responsables</h1>
        <p class="text2-tipografia-govco">
          Encuentra tu compañero ideal. Todos nuestros animales están vacunados,
          esterilizados y listos para formar parte de tu familia.
        </p>
      </header>

      <!-- Filtros -->
      <section class="filters-section card-govco">
        <div class="card-govco-body">
          <div class="filters-grid">
            <div class="filter-group">
              <label class="label-govco">Especie</label>
              <select v-model="filters.species" class="input-govco">
                <option value="">Todas</option>
                <option value="perro">Perros</option>
                <option value="gato">Gatos</option>
                <option value="otro">Otros</option>
              </select>
            </div>

            <div class="filter-group">
              <label class="label-govco">Tamaño</label>
              <select v-model="filters.size" class="input-govco">
                <option value="">Todos</option>
                <option value="pequeno">Pequeño</option>
                <option value="mediano">Mediano</option>
                <option value="grande">Grande</option>
              </select>
            </div>

            <div class="filter-group">
              <label class="label-govco">Edad</label>
              <select v-model="filters.age" class="input-govco">
                <option value="">Todas</option>
                <option value="cachorro">Cachorro (0-1 año)</option>
                <option value="joven">Joven (1-3 años)</option>
                <option value="adulto">Adulto (3-7 años)</option>
                <option value="senior">Senior (+7 años)</option>
              </select>
            </div>

            <div class="filter-group">
              <label class="label-govco">Sexo</label>
              <select v-model="filters.sex" class="input-govco">
                <option value="">Todos</option>
                <option value="macho">Macho</option>
                <option value="hembra">Hembra</option>
              </select>
            </div>

            <div class="filter-actions">
              <button type="button" class="btn-govco btn-govco-secondary" @click="clearFilters">
                Limpiar filtros
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Resultados -->
      <section class="results-section">
        <div class="results-header">
          <span class="results-count text2-tipografia-govco">
            Mostrando {{ filteredAnimals.length }} animales disponibles
          </span>
        </div>

        <div class="animals-grid">
          <div
            v-for="animal in filteredAnimals"
            :key="animal.id"
            class="animal-card card-govco"
          >
            <div class="animal-image">
              <img :src="animal.image" :alt="animal.name" />
              <span class="animal-badge" :class="getBadgeClass(animal.species)">
                {{ animal.species }}
              </span>
            </div>
            <div class="animal-info">
              <h3 class="animal-name h5-tipografia-govco">{{ animal.name }}</h3>
              <div class="animal-details">
                <span class="detail-item">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                  </svg>
                  {{ animal.age }}
                </span>
                <span class="detail-item">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                  </svg>
                  {{ animal.sex }}
                </span>
                <span class="detail-item">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                    <line x1="4" y1="22" x2="4" y2="15"></line>
                  </svg>
                  {{ animal.size }}
                </span>
              </div>
              <p class="animal-description text3-tipografia-govco">
                {{ animal.description }}
              </p>
              <div class="animal-tags">
                <span v-if="animal.vaccinated" class="tag tag-success">Vacunado</span>
                <span v-if="animal.sterilized" class="tag tag-success">Esterilizado</span>
                <span v-if="animal.microchip" class="tag tag-info">Con microchip</span>
              </div>
            </div>
            <div class="animal-actions">
              <button
                type="button"
                class="btn-govco btn-govco-primary btn-block"
                @click="openAdoptionModal(animal)"
              >
                Quiero Adoptar
              </button>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-if="filteredAnimals.length === 0" class="empty-state">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <h3 class="h5-tipografia-govco">No se encontraron resultados</h3>
          <p class="text2-tipografia-govco">
            Intenta ajustar los filtros de búsqueda
          </p>
          <button type="button" class="btn-govco btn-govco-secondary" @click="clearFilters">
            Limpiar filtros
          </button>
        </div>
      </section>
    </div>

    <!-- Modal de adopción -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content card-govco">
        <div class="modal-header">
          <h3 class="h4-tipografia-govco govcolor-blue-dark">Solicitud de Adopción</h3>
          <button type="button" class="modal-close" @click="closeModal">&times;</button>
        </div>
        <div class="modal-body">
          <div v-if="selectedAnimal" class="adoption-summary">
            <img :src="selectedAnimal.image" :alt="selectedAnimal.name" class="summary-image" />
            <div class="summary-info">
              <h4 class="h5-tipografia-govco">{{ selectedAnimal.name }}</h4>
              <p class="text2-tipografia-govco">
                {{ selectedAnimal.species }} - {{ selectedAnimal.age }} - {{ selectedAnimal.size }}
              </p>
            </div>
          </div>

          <form @submit.prevent="submitAdoption" class="adoption-form">
            <div class="form-group-govco">
              <label class="label-govco">
                Nombre completo <span aria-required="true">*</span>
              </label>
              <input
                v-model="adoptionForm.name"
                type="text"
                class="input-govco"
                required
              />
            </div>

            <div class="form-row">
              <div class="form-group-govco">
                <label class="label-govco">
                  Documento de identidad <span aria-required="true">*</span>
                </label>
                <input
                  v-model="adoptionForm.document"
                  type="text"
                  class="input-govco"
                  required
                />
              </div>
              <div class="form-group-govco">
                <label class="label-govco">
                  Teléfono <span aria-required="true">*</span>
                </label>
                <input
                  v-model="adoptionForm.phone"
                  type="tel"
                  class="input-govco"
                  required
                />
              </div>
            </div>

            <div class="form-group-govco">
              <label class="label-govco">
                Correo electrónico <span aria-required="true">*</span>
              </label>
              <input
                v-model="adoptionForm.email"
                type="email"
                class="input-govco"
                required
              />
            </div>

            <div class="form-group-govco">
              <label class="label-govco">
                Dirección de residencia <span aria-required="true">*</span>
              </label>
              <input
                v-model="adoptionForm.address"
                type="text"
                class="input-govco"
                required
              />
            </div>

            <div class="form-group-govco">
              <label class="label-govco">
                ¿Por qué deseas adoptar? <span aria-required="true">*</span>
              </label>
              <textarea
                v-model="adoptionForm.reason"
                class="input-govco"
                rows="3"
                required
              ></textarea>
            </div>

            <div class="form-check">
              <input
                type="checkbox"
                id="terms"
                v-model="adoptionForm.acceptTerms"
                required
              />
              <label for="terms">
                Acepto los términos y condiciones de adopción responsable
              </label>
            </div>

            <div class="modal-actions">
              <button type="button" class="btn-govco btn-govco-secondary" @click="closeModal">
                Cancelar
              </button>
              <button type="submit" class="btn-govco btn-govco-success">
                Enviar Solicitud
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';

// Filtros
const filters = reactive({
  species: '',
  size: '',
  age: '',
  sex: ''
});

// Modal
const showModal = ref(false);
const selectedAnimal = ref(null);

// Formulario de adopción
const adoptionForm = reactive({
  name: '',
  document: '',
  phone: '',
  email: '',
  address: '',
  reason: '',
  acceptTerms: false
});

// Datos de ejemplo
const animals = ref([
  {
    id: 1,
    name: 'Luna',
    species: 'Perro',
    sex: 'Hembra',
    age: '2 años',
    size: 'Mediano',
    description: 'Luna es una perrita muy cariñosa y juguetona. Le encanta correr y jugar con pelotas.',
    image: 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400&h=300&fit=crop',
    vaccinated: true,
    sterilized: true,
    microchip: true
  },
  {
    id: 2,
    name: 'Max',
    species: 'Perro',
    sex: 'Macho',
    age: '3 años',
    size: 'Grande',
    description: 'Max es un perro muy noble y protector. Ideal para familias con espacio.',
    image: 'https://images.unsplash.com/photo-1552053831-71594a27632d?w=400&h=300&fit=crop',
    vaccinated: true,
    sterilized: true,
    microchip: true
  },
  {
    id: 3,
    name: 'Michi',
    species: 'Gato',
    sex: 'Macho',
    age: '1 año',
    size: 'Pequeño',
    description: 'Michi es un gato independiente pero muy cariñoso cuando quiere.',
    image: 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400&h=300&fit=crop',
    vaccinated: true,
    sterilized: true,
    microchip: false
  },
  {
    id: 4,
    name: 'Canela',
    species: 'Perro',
    sex: 'Hembra',
    age: '6 meses',
    size: 'Pequeño',
    description: 'Canela es una cachorrita muy activa y curiosa. Necesita mucho amor.',
    image: 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=400&h=300&fit=crop',
    vaccinated: true,
    sterilized: false,
    microchip: true
  },
  {
    id: 5,
    name: 'Nala',
    species: 'Gato',
    sex: 'Hembra',
    age: '4 años',
    size: 'Pequeño',
    description: 'Nala es una gata tranquila y cariñosa. Perfecta para apartamentos.',
    image: 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?w=400&h=300&fit=crop',
    vaccinated: true,
    sterilized: true,
    microchip: true
  },
  {
    id: 6,
    name: 'Rocky',
    species: 'Perro',
    sex: 'Macho',
    age: '5 años',
    size: 'Grande',
    description: 'Rocky es un perro muy tranquilo y obediente. Ya está entrenado.',
    image: 'https://images.unsplash.com/photo-1561037404-61cd46aa615b?w=400&h=300&fit=crop',
    vaccinated: true,
    sterilized: true,
    microchip: true
  }
]);

// Animales filtrados
const filteredAnimals = computed(() => {
  return animals.value.filter(animal => {
    if (filters.species && animal.species.toLowerCase() !== filters.species) return false;
    if (filters.size && animal.size.toLowerCase() !== filters.size) return false;
    if (filters.sex && animal.sex.toLowerCase() !== filters.sex) return false;
    // Filtro de edad simplificado
    if (filters.age) {
      const ageNum = parseInt(animal.age);
      if (filters.age === 'cachorro' && ageNum > 1) return false;
      if (filters.age === 'joven' && (ageNum < 1 || ageNum > 3)) return false;
      if (filters.age === 'adulto' && (ageNum < 3 || ageNum > 7)) return false;
      if (filters.age === 'senior' && ageNum < 7) return false;
    }
    return true;
  });
});

function clearFilters() {
  filters.species = '';
  filters.size = '';
  filters.age = '';
  filters.sex = '';
}

function getBadgeClass(species) {
  return species.toLowerCase() === 'perro' ? 'badge-dog' : 'badge-cat';
}

function openAdoptionModal(animal) {
  selectedAnimal.value = animal;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  selectedAnimal.value = null;
  // Reset form
  Object.keys(adoptionForm).forEach(key => {
    adoptionForm[key] = key === 'acceptTerms' ? false : '';
  });
}

function submitAdoption() {
  // Aquí iría la lógica de envío a la API
  alert(`Solicitud de adopción enviada para ${selectedAnimal.value.name}. Nos comunicaremos contigo pronto.`);
  closeModal();
}
</script>

<style scoped>
.adoptions-view {
  padding: 24px;
  background: #f5f7fb;
  min-height: 100%;
}

.adoptions-container {
  max-width: 1400px;
  margin: 0 auto;
}

/* Header */
.adoptions-header {
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 3px solid #3366CC;
}

.adoptions-header h1 {
  margin: 0 0 8px 0;
}

.adoptions-header p {
  margin: 0;
}

/* Filters */
.filters-section {
  margin-bottom: 24px;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 16px;
  align-items: end;
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.filter-group .label-govco {
  margin-bottom: 8px;
  font-size: 0.9rem;
}

.filter-actions {
  display: flex;
  align-items: flex-end;
}

/* Results */
.results-header {
  margin-bottom: 16px;
}

.animals-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

/* Animal Card */
.animal-card {
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.animal-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.animal-image {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.animal-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.animal-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

.badge-dog {
  background: #3366CC;
}

.badge-cat {
  background: #9C27B0;
}

.animal-info {
  padding: 16px;
  flex: 1;
}

.animal-name {
  margin: 0 0 8px 0;
  color: #004884;
}

.animal-details {
  display: flex;
  gap: 16px;
  margin-bottom: 12px;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.85rem;
  color: #666;
}

.animal-description {
  margin: 0 0 12px 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.animal-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.tag {
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
}

.tag-success {
  background: #E8F5E9;
  color: #069169;
}

.tag-info {
  background: #E8F0FE;
  color: #3366CC;
}

.animal-actions {
  padding: 16px;
  border-top: 1px solid #e0e0e0;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.empty-state svg {
  margin-bottom: 16px;
  opacity: 0.5;
}

.empty-state h3 {
  margin: 0 0 8px 0;
}

.empty-state p {
  margin: 0 0 16px 0;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 2px solid #3366CC;
  background: #E8F0FE;
}

.modal-header h3 {
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
  padding: 0;
  width: 32px;
  height: 32px;
}

.modal-body {
  padding: 24px;
}

.adoption-summary {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
  padding: 16px;
  background: #f5f7fb;
  border-radius: 8px;
}

.summary-image {
  width: 80px;
  height: 80px;
  border-radius: 8px;
  object-fit: cover;
}

.summary-info h4 {
  margin: 0 0 4px 0;
  color: #004884;
}

.summary-info p {
  margin: 0;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-check {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  margin-bottom: 24px;
}

.form-check input {
  margin-top: 3px;
}

.form-check label {
  font-size: 0.9rem;
  color: #4B4B4B;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

/* Responsive */
@media (max-width: 1199.98px) {
  .animals-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 991.98px) {
  .filters-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 767.98px) {
  .filters-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .animals-grid {
    grid-template-columns: 1fr;
  }

  .form-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 575.98px) {
  .adoptions-view {
    padding: 16px;
  }

  .filters-grid {
    grid-template-columns: 1fr;
  }
}
</style>
