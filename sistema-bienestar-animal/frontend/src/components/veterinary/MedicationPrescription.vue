<template>
  <div class="medication-prescription">
    <div class="prescription-header">
      <h4 class="subtitle">Prescripción de medicamentos</h4>
      <button 
        type="button" 
        class="govco-btn-small govco-bg-elf-green"
        @click="addMedication"
      >
        + Agregar medicamento
      </button>
    </div>

    <div v-if="localMedications.length === 0" class="empty-state">
      <p>No hay medicamentos prescritos</p>
    </div>

    <div v-else class="medications-list">
      <div 
        v-for="(med, index) in localMedications" 
        :key="index"
        class="medication-item"
      >
        <div class="medication-grid">
          <!-- Medicamento -->
          <div class="input-like-govco">
            <label :for="`medication-${index}`" class="label-desplegable-govco">
              Medicamento<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic" :id="`medication-dropdown-${index}`">
              <select 
                :id="`medication-${index}`"
                v-model="med.medicationId"
                @change="onMedicationChange(index)"
                aria-invalid="false"
              >
                <option disabled value="">Seleccionar</option>
                <option 
                  v-for="item in inventory" 
                  :key="item.id" 
                  :value="item.id"
                  :disabled="item.stock <= 0"
                >
                  {{ item.name }} (Stock: {{ item.stock }} {{ item.unit }})
                </option>
              </select>
            </div>
            <span v-if="med.error" class="alert-desplegable-govco">{{ med.error }}</span>
          </div>

          <!-- Dosis -->
          <div class="entradas-de-texto-govco">
            <label :for="`dose-${index}`">
              Dosis<span aria-required="true">*</span>
            </label>
            <input
              type="text"
              :id="`dose-${index}`"
              v-model="med.dose"
              placeholder="1 tableta / 0.5 ml"
              @input="emitUpdate"
            />
          </div>

          <!-- Frecuencia -->
          <div class="entradas-de-texto-govco">
            <label :for="`frequency-${index}`">
              Frecuencia<span aria-required="true">*</span>
            </label>
            <input
              type="text"
              :id="`frequency-${index}`"
              v-model="med.frequency"
              placeholder="Cada 8 horas"
              @input="emitUpdate"
            />
          </div>

          <!-- Duración -->
          <div class="entradas-de-texto-govco">
            <label :for="`duration-${index}`">
              Duración (días)<span aria-required="true">*</span>
            </label>
            <input
              type="number"
              :id="`duration-${index}`"
              v-model.number="med.duration"
              min="1"
              max="90"
              @input="emitUpdate"
            />
          </div>

          <!-- Vía de administración -->
          <div class="input-like-govco">
            <label :for="`route-${index}`" class="label-desplegable-govco">
              Vía<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic" :id="`route-dropdown-${index}`">
              <select 
                :id="`route-${index}`"
                v-model="med.route"
                @change="emitUpdate"
                aria-invalid="false"
              >
                <option disabled value="">Seleccionar</option>
                <option value="oral">Oral</option>
                <option value="subcutanea">Subcutánea</option>
                <option value="intramuscular">Intramuscular</option>
                <option value="intravenosa">Intravenosa</option>
                <option value="topica">Tópica</option>
              </select>
            </div>
          </div>

          <!-- Cantidad total -->
          <div class="entradas-de-texto-govco">
            <label :for="`total-${index}`">
              Cantidad total
            </label>
            <input
              type="number"
              :id="`total-${index}`"
              v-model.number="med.totalQuantity"
              min="1"
              @input="emitUpdate"
            />
            <span class="info-entradas-de-texto-govco">
              {{ getUnit(med.medicationId) }}
            </span>
          </div>

          <!-- Instrucciones especiales -->
          <div class="entradas-de-texto-govco full-width-grid">
            <label :for="`instructions-${index}`">
              Instrucciones especiales
            </label>
            <input
              type="text"
              :id="`instructions-${index}`"
              v-model="med.instructions"
              placeholder="Administrar con alimento"
              @input="emitUpdate"
            />
          </div>

          <!-- Botón eliminar -->
          <div class="medication-actions">
            <button 
              type="button"
              class="btn-remove"
              @click="removeMedication(index)"
              title="Eliminar medicamento"
            >
              🗑️ Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, onMounted, nextTick } from 'vue';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  inventory: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue', 'update:stock']);

const localMedications = reactive([...props.modelValue]);

watch(() => props.modelValue, (newVal) => {
  localMedications.splice(0, localMedications.length, ...newVal);
}, { deep: true });

// Función para inicializar componentes GOV.CO
function initializeGovcoComponents() {
  if (typeof window === 'undefined' || !window.GOVCo) return;
  
  nextTick(() => {
    // Inicializar todos los dropdowns
    const dropdowns = document.querySelectorAll('.medication-prescription .desplegable-govco[data-type="basic"]');
    
    dropdowns.forEach((dropdown) => {
      if (window.GOVCo?.init) {
        try {
          const parent = dropdown.parentElement;
          window.GOVCo.init(parent);
        } catch (e) {
          console.error('Error inicializando dropdown:', e);
        }
      }
    });
    
    // Sincronizar valores después de inicializar
    setTimeout(() => {
      syncDropdownValues();
      setupDropdownListeners();
    }, 250);
  });
}

// Función para sincronizar valores de dropdowns
function syncDropdownValues() {
  localMedications.forEach((med, index) => {
    const medicationSelect = document.getElementById(`medication-${index}`);
    const routeSelect = document.getElementById(`route-${index}`);
    
    if (medicationSelect && med.medicationId) {
      medicationSelect.value = med.medicationId;
    }
    
    if (routeSelect && med.route) {
      routeSelect.value = med.route;
    }
  });
}

// Función para configurar listeners en los dropdowns
function setupDropdownListeners() {
  nextTick(() => {
    localMedications.forEach((med, index) => {
      const medicationSelect = document.getElementById(`medication-${index}`);
      const routeSelect = document.getElementById(`route-${index}`);
      
      if (medicationSelect && !medicationSelect.dataset.listenerAdded) {
        medicationSelect.addEventListener('change', (e) => {
          med.medicationId = e.target.value;
          onMedicationChange(index);
        });
        medicationSelect.dataset.listenerAdded = 'true';
      }
      
      if (routeSelect && !routeSelect.dataset.listenerAdded) {
        routeSelect.addEventListener('change', (e) => {
          med.route = e.target.value;
          emitUpdate();
        });
        routeSelect.dataset.listenerAdded = 'true';
      }
    });
  });
}

function addMedication() {
  localMedications.push({
    medicationId: '',
    dose: '',
    frequency: '',
    duration: 7,
    route: '',
    totalQuantity: 1,
    instructions: '',
    error: ''
  });
  emitUpdate();
  
  // NO reinicializar GOV.CO - los dropdowns deben cargarse manualmente por el usuario
}

function removeMedication(index) {
  localMedications.splice(index, 1);
  emitUpdate();
}

function onMedicationChange(index) {
  const med = localMedications[index];
  const selected = props.inventory.find(item => item.id === med.medicationId);
  
  if (selected && selected.stock <= 0) {
    med.error = 'Medicamento sin stock disponible';
  } else {
    med.error = '';
  }
  
  emitUpdate();
}

function getUnit(medicationId) {
  const medication = props.inventory.find(m => m.id === medicationId);
  return medication ? medication.unit : '';
}

function emitUpdate() {
  emit('update:modelValue', localMedications.map(med => ({ ...med })));
}

onMounted(() => {
  initializeGovcoComponents();
  setupDropdownListeners();

  if (typeof window !== 'undefined') {
    window.addEventListener('load', () => {
      initializeGovcoComponents();
      setupDropdownListeners();
    });
  }
});
</script>

<style scoped>
.medication-prescription {
  border: 2px solid #E0E0E0;
  border-radius: 8px;
  padding: 1.5rem;
  background: #f9fafb;
}

.prescription-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.subtitle {
  margin: 0;
  color: #3366CC;
  font-weight: 600;
}

.govco-btn-small {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  color: white;
  font-size: 0.9rem;
}

.govco-bg-elf-green {
  background-color: #069169;
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: #666;
  background: white;
  border-radius: 6px;
  border: 2px dashed #D0D0D0;
}

.medications-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.medication-item {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  border: 1px solid #E0E0E0;
}

.medication-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.full-width-grid {
  grid-column: 1 / 4;
}

.medication-actions {
  grid-column: 1 / 4;
  display: flex;
  justify-content: flex-end;
  margin-top: 0.5rem;
}

.btn-remove {
  padding: 0.5rem 1rem;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-remove:hover {
  background: #c82333;
}

.entradas-de-texto-govco {
  display: flex;
  flex-direction: column;
  width: 100%;
}

.entradas-de-texto-govco label {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.entradas-de-texto-govco input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  box-sizing: border-box;
}

.info-entradas-de-texto-govco {
  display: block;
  color: #666;
  font-size: 0.85rem;
  margin-top: 0.25rem;
}

.alert-desplegable-govco {
  display: block;
  color: #b00020;
  font-size: 0.85rem;
  margin-top: 0.5rem;
}

.input-like-govco {
  display: flex;
  flex-direction: column;
  width: 100%;
  margin: 0;
}

.input-like-govco label {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.desplegable-govco {
  width: 100%;
}

.desplegable-govco select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  box-sizing: border-box;
}

/* Z-index para dropdowns */
:deep(.desplegable-govco .desplegable-items) {
  z-index: 1500 !important;
}

@media (max-width: 768px) {
  .medication-grid {
    grid-template-columns: 1fr;
  }
  
  .full-width-grid {
    grid-column: 1 / 2;
  }
  
  .medication-actions {
    grid-column: 1 / 2;
  }
}
</style>