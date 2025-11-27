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
        :key="`med-${index}-${med.id || Date.now()}`"
        class="medication-item"
      >
        <div class="medication-grid">
          <!-- Medicamento -->
          <div class="input-like-govco">
            <DesplegableGovco
              :ref="el => setMedicationRef(el, index)"
              :id="`medication-${index}`"
              label="Medicamento"
              :options="medicationOptions"
              v-model="med.medicationId"
              placeholder="Seleccionar"
              :required="true"
              :alert-text="med.error"
              :error="!!med.error"
              width="100%"
              height="44px"
              @change="(value) => onMedicationChange(index, value)"
            />
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
            <DesplegableGovco
              :ref="el => setRouteRef(el, index)"
              :id="`route-${index}`"
              label="Vía"
              :options="routeOptions"
              v-model="med.route"
              placeholder="Seleccionar"
              :required="true"
              width="100%"
              height="44px"
              @change="(value) => onRouteChange(index, value)"
            />
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
import { reactive, computed, watch, onMounted, ref } from 'vue';
import DesplegableGovco from '../common/DesplegableGovco.vue';

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

// Referencias para los dropdowns dinámicos
const medicationRefs = ref([]);
const routeRefs = ref([]);

// Opciones para los dropdowns
const medicationOptions = computed(() => 
  props.inventory.map(item => ({
    value: item.id,
    text: `${item.name} (Stock: ${item.stock} ${item.unit})`,
    disabled: item.stock <= 0
  }))
);

const routeOptions = [
  { value: 'oral', text: 'Oral' },
  { value: 'subcutanea', text: 'Subcutánea' },
  { value: 'intramuscular', text: 'Intramuscular' },
  { value: 'intravenosa', text: 'Intravenosa' },
  { value: 'topica', text: 'Tópica' }
];

watch(() => props.modelValue, (newVal) => {
  localMedications.splice(0, localMedications.length, ...newVal);
}, { deep: true });

// Funciones para manejar las referencias dinámicas
function setMedicationRef(el, index) {
  if (el) {
    medicationRefs.value[index] = el;
  }
}

function setRouteRef(el, index) {
  if (el) {
    routeRefs.value[index] = el;
  }
}

function addMedication() {
  localMedications.push({
    id: Date.now(), // Agregar ID único para el key
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
}

function removeMedication(index) {
  localMedications.splice(index, 1);
  
  // Limpiar referencias
  medicationRefs.value.splice(index, 1);
  routeRefs.value.splice(index, 1);
  
  emitUpdate();
}

function onMedicationChange(index, value) {
  console.log(`Medication ${index} changed to:`, value);
  
  const med = localMedications[index];
  med.medicationId = value;
  
  const selected = props.inventory.find(item => item.id === value);
  
  if (selected && selected.stock <= 0) {
    med.error = 'Medicamento sin stock disponible';
  } else {
    med.error = '';
  }
  
  emitUpdate();
}

function onRouteChange(index, value) {
  console.log(`Route ${index} changed to:`, value);
  
  localMedications[index].route = value;
  emitUpdate();
}

function getUnit(medicationId) {
  const medication = props.inventory.find(m => m.id === medicationId);
  return medication ? medication.unit : '';
}

function emitUpdate() {
  emit('update:modelValue', localMedications.map(med => ({ ...med })));
}

// Función para sincronizar todos los valores desde los componentes
function syncAllMedicationValues() {
  console.log('=== Sincronizando valores de medicamentos ===');
  
  localMedications.forEach((med, index) => {
    // Sincronizar medicamento
    const medicationRef = medicationRefs.value[index];
    if (medicationRef?.getValue) {
      const medicationValue = medicationRef.getValue();
      if (medicationValue) {
        med.medicationId = medicationValue;
        console.log(`Medication ${index} synced:`, medicationValue);
      }
    }
    
    // Sincronizar ruta
    const routeRef = routeRefs.value[index];
    if (routeRef?.getValue) {
      const routeValue = routeRef.getValue();
      if (routeValue) {
        med.route = routeValue;
        console.log(`Route ${index} synced:`, routeValue);
      }
    }
  });
  
  console.log('Medicamentos sincronizados:', localMedications);
}

// Exponer función para uso externo
defineExpose({
  syncAllMedicationValues
});

onMounted(() => {
  console.log('MedicationPrescription mounted');
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
  transition: all 0.3s;
}

.govco-btn-small:hover {
  transform: translateY(-1px);
  opacity: 0.9;
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
  transition: all 0.3s;
}

.btn-remove:hover {
  background: #c82333;
  transform: translateY(-1px);
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

.entradas-de-texto-govco label span[aria-required="true"] {
  color: #d32f2f;
  margin-left: 0.25rem;
}

.entradas-de-texto-govco input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  box-sizing: border-box;
  height: 44px;
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