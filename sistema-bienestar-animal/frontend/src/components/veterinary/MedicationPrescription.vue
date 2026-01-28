<template>
  <div class="medication-prescription">
    <div class="section-header">
      <h4>Prescripción de medicamentos</h4>
      <button type="button" @click="addMedication" class="btn-add">
        ➕ Agregar medicamento
      </button>
    </div>

    <div v-if="medications.length === 0" class="empty-state">
      <p>No hay medicamentos agregados</p>
    </div>

    <div v-else class="medications-list">
      <div 
        v-for="(med, index) in medications" 
        :key="index" 
        class="medication-item"
        :class="{ 'stock-warning': isStockLow(med) }"
      >
        <div class="medication-header">
          <span class="medication-number">#{{ index + 1 }}</span>
          <button type="button" @click="removeMedication(index)" class="btn-remove">
            🗑️
          </button>
        </div>

        <div class="medication-fields">
          <!-- Selector de medicamento -->
          <div class="field">
            <label>Medicamento<span class="required">*</span></label>
            <select 
              v-model="med.medicationId" 
              @change="onMedicationChange(index)"
              :class="{ 'error': !med.medicationId }"
            >
              <option value="">Seleccionar medicamento</option>
              <option 
                v-for="item in inventory" 
                :key="item.id" 
                :value="item.id"
                :disabled="item.stock <= 0"
              >
                {{ item.name }} - Stock: {{ item.stock }} {{ item.unit }}
                {{ item.stock <= 0 ? '(Sin stock)' : '' }}
              </option>
            </select>
            
            <!-- Alerta de stock -->
            <div v-if="med.medicationId" class="stock-info">
              <span v-if="getStockInfo(med.medicationId).stock > 0" class="stock-available">
                ✅ Stock disponible: {{ getStockInfo(med.medicationId).stock }} {{ getStockInfo(med.medicationId).unit }}
              </span>
              <span v-else class="stock-unavailable">
                ⚠️ Sin stock disponible
              </span>
            </div>
          </div>

          <!-- Dosis -->
          <div class="field">
            <label>Dosis<span class="required">*</span></label>
            <input 
              v-model="med.dose" 
              type="text" 
              placeholder="Ej: 5mg, 1 tableta"
              :class="{ 'error': !med.dose }"
            />
          </div>

          <!-- Frecuencia -->
          <div class="field">
            <label>Frecuencia<span class="required">*</span></label>
            <select 
              v-model="med.frequency"
              :class="{ 'error': !med.frequency }"
            >
              <option value="">Seleccionar frecuencia</option>
              <option value="cada 6 horas">Cada 6 horas</option>
              <option value="cada 8 horas">Cada 8 horas</option>
              <option value="cada 12 horas">Cada 12 horas</option>
              <option value="cada 24 horas">Cada 24 horas (1 vez al día)</option>
              <option value="2 veces al día">2 veces al día</option>
              <option value="3 veces al día">3 veces al día</option>
              <option value="según necesidad">Según necesidad</option>
            </select>
          </div>

          <!-- Duración -->
          <div class="field">
            <label>Duración (días)<span class="required">*</span></label>
            <input 
              v-model.number="med.duration" 
              type="number" 
              min="1"
              placeholder="Ej: 7"
              @input="calculateTotalQuantity(index)"
              :class="{ 'error': !med.duration }"
            />
          </div>

          <!-- Cantidad total calculada -->
          <div class="field">
            <label>Cantidad total a usar</label>
            <input 
              v-model.number="med.totalQuantity" 
              type="number" 
              min="0.01"
              step="0.01"
              placeholder="Cantidad total"
              @input="checkStock(index)"
              :class="{ 'error': isQuantityExceedsStock(med) }"
            />
            <small v-if="isQuantityExceedsStock(med)" class="error-text">
              ⚠️ La cantidad excede el stock disponible
            </small>
          </div>

          <!-- Instrucciones -->
          <div class="field full-width">
            <label>Instrucciones adicionales</label>
            <textarea 
              v-model="med.instructions" 
              rows="2"
              placeholder="Instrucciones especiales para el tratamiento..."
            ></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

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

const medications = ref(props.modelValue || []);

// Sincronizar con v-model
watch(() => props.modelValue, (newVal) => {
  if (JSON.stringify(newVal) !== JSON.stringify(medications.value)) {
    medications.value = newVal || [];
  }
}, { deep: true });

watch(medications, (newVal) => {
  emit('update:modelValue', newVal);
}, { deep: true });

function addMedication() {
  medications.value.push({
    medicationId: '',
    dose: '',
    frequency: '',
    duration: null,
    totalQuantity: null,
    instructions: ''
  });
}

function removeMedication(index) {
  medications.value.splice(index, 1);
}

function onMedicationChange(index) {
  const med = medications.value[index];
  // Resetear cantidad cuando cambia el medicamento
  med.totalQuantity = null;
  calculateTotalQuantity(index);
}

function calculateTotalQuantity(index) {
  const med = medications.value[index];
  
  if (!med.medicationId || !med.dose || !med.frequency || !med.duration) {
    return;
  }

  // Extraer número de la dosis
  const doseMatch = med.dose.match(/(\d+\.?\d*)/);
  const doseNumber = doseMatch ? parseFloat(doseMatch[1]) : 1;

  // Calcular veces por día desde la frecuencia
  let timesPerDay = 1;
  if (med.frequency.includes('cada 6 horas')) {
    timesPerDay = 4;
  } else if (med.frequency.includes('cada 8 horas')) {
    timesPerDay = 3;
  } else if (med.frequency.includes('cada 12 horas')) {
    timesPerDay = 2;
  } else if (med.frequency.includes('cada 24 horas') || med.frequency.includes('1 vez')) {
    timesPerDay = 1;
  } else if (med.frequency.includes('2 veces')) {
    timesPerDay = 2;
  } else if (med.frequency.includes('3 veces')) {
    timesPerDay = 3;
  }

  // Calcular cantidad total
  const total = doseNumber * timesPerDay * med.duration;
  med.totalQuantity = Math.ceil(total); // Redondear hacia arriba

  checkStock(index);
}

function checkStock(index) {
  const med = medications.value[index];
  
  if (!med.medicationId || !med.totalQuantity) {
    return;
  }

  const stockInfo = getStockInfo(med.medicationId);
  
  if (med.totalQuantity > stockInfo.stock) {
    console.warn(`⚠️ Stock insuficiente: ${stockInfo.name}`);
  }
}

function getStockInfo(medicationId) {
  const item = props.inventory.find(i => i.id === medicationId);
  return item || { id: '', name: 'N/A', stock: 0, unit: 'unidades' };
}

function isStockLow(med) {
  if (!med.medicationId || !med.totalQuantity) {
    return false;
  }
  const stockInfo = getStockInfo(med.medicationId);
  return med.totalQuantity > stockInfo.stock;
}

function isQuantityExceedsStock(med) {
  if (!med.medicationId || !med.totalQuantity) {
    return false;
  }
  const stockInfo = getStockInfo(med.medicationId);
  return med.totalQuantity > stockInfo.stock;
}
</script>

<style scoped>
.medication-prescription {
  background: #f9f9f9;
  border-radius: 8px;
  padding: 1.5rem;
  border: 1px solid #e0e0e0;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-header h4 {
  margin: 0;
  color: #333;
  font-size: 1.1rem;
}

.btn-add {
  background: #069169;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.2s;
}

.btn-add:hover {
  background: #047857;
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: #999;
  font-style: italic;
}

.medications-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.medication-item {
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  padding: 1rem;
  transition: border-color 0.2s;
}

.medication-item.stock-warning {
  border-color: #ff9800;
  background: #fff8e1;
}

.medication-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #e0e0e0;
}

.medication-number {
  font-weight: 600;
  color: #3366cc;
  font-size: 1.1rem;
}

.btn-remove {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  transition: background 0.2s;
}

.btn-remove:hover {
  background: #ffebee;
}

.medication-fields {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.field {
  display: flex;
  flex-direction: column;
}

.field.full-width {
  grid-column: 1 / -1;
}

.field label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #333;
  font-size: 0.9rem;
}

.required {
  color: #d32f2f;
  margin-left: 0.25rem;
}

.field input,
.field select,
.field textarea {
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.9rem;
  font-family: inherit;
  transition: border-color 0.2s;
}

.field input:focus,
.field select:focus,
.field textarea:focus {
  outline: none;
  border-color: #3366cc;
  box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.1);
}

.field input.error,
.field select.error {
  border-color: #d32f2f;
}

.field select:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

.stock-info {
  margin-top: 0.5rem;
  font-size: 0.85rem;
}

.stock-available {
  color: #2e7d32;
  font-weight: 600;
}

.stock-unavailable {
  color: #d32f2f;
  font-weight: 600;
}

.error-text {
  color: #d32f2f;
  font-size: 0.85rem;
  margin-top: 0.25rem;
}

@media (max-width: 768px) {
  .medication-fields {
    grid-template-columns: 1fr;
  }
  
  .section-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .btn-add {
    width: 100%;
  }
}
</style>