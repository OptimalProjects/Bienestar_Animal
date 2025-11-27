<template>
  <div class="vital-signs-grid">

    <!-- Temperatura -->
    <div class="entradas-de-texto-govco">
      <label for="temperature">Temperatura (°C)<span aria-required="true">*</span></label>
      <input
        type="number"
        id="temperature"
        v-model.number="localSigns.temperature"
        step="0.1"
        min="35"
        max="42"
        placeholder="38.5"
        @input="emitUpdate"
      />
      <span class="info-entradas-de-texto-govco">Normal: 38-39°C</span>
      <span v-if="errors?.temperature" class="error-text">{{ errors.temperature }}</span>
    </div>

    <!-- FC -->
    <div class="entradas-de-texto-govco">
      <label for="heartRate">Frecuencia cardíaca (lpm)<span aria-required="true">*</span></label>
      <input
        type="number"
        id="heartRate"
        v-model.number="localSigns.heartRate"
        min="40"
        max="200"
        placeholder="80"
        @input="emitUpdate"
      />
      <span class="info-entradas-de-texto-govco">Perro: 60-140 / Gato: 140-220</span>
      <span v-if="errors?.heartRate" class="error-text">{{ errors.heartRate }}</span>
    </div>

    <!-- FR -->
    <div class="entradas-de-texto-govco">
      <label for="respiratoryRate">Frecuencia respiratoria (rpm)<span aria-required="true">*</span></label>
      <input
        type="number"
        id="respiratoryRate"
        v-model.number="localSigns.respiratoryRate"
        min="10"
        max="60"
        placeholder="20"
        @input="emitUpdate"
      />
      <span class="info-entradas-de-texto-govco">Normal: 10-30 rpm</span>
      <span v-if="errors?.respiratoryRate" class="error-text">{{ errors.respiratoryRate }}</span>
    </div>

    <!-- Peso -->
    <div class="entradas-de-texto-govco">
      <label for="weight">Peso (kg)<span aria-required="true">*</span></label>
      <input
        type="number"
        id="weight"
        v-model.number="localSigns.weight"
        step="0.1"
        min="0.5"
        max="100"
        placeholder="15.5"
        @input="emitUpdate"
      />
      <span v-if="errors?.weight" class="error-text">{{ errors.weight }}</span>
    </div>

    <!-- Condición corporal -->
    <DesplegableGovco
      id="bodyCondition"
      label="Condición corporal"
      :options="bodyConditionOptions"
      v-model="localSigns.bodyCondition"
      placeholder="Evaluar"
      :required="true"
      :alert-text="errors?.bodyCondition"
      :error="!!errors?.bodyCondition"
      width="100%"
      height="44px"
      @change="emitUpdate"
    />

    <!-- Mucosas -->
    <DesplegableGovco
      id="mucosa"
      label="Color de mucosas"
      :options="mucosaOptions"
      v-model="localSigns.mucosa"
      placeholder="No evaluado"
      width="100%"
      height="44px"
      @change="emitUpdate"
    />

    <!-- Hidratación -->
    <div class="input-like-govco">
      <DesplegableGovco
        id="hydration"
        label="Estado de hidratación"
        :options="hydrationOptions"
        v-model="localSigns.hydration"
        placeholder="No evaluado"
        width="100%"
        height="44px"
        @change="emitUpdate"
      />
      <span class="info-entradas-de-texto-govco">Evaluar mediante pliegue cutáneo</span>
    </div>

    <!-- TLLC -->
    <div class="entradas-de-texto-govco">
      <label for="tllc">TLLC - Tiempo de llenado capilar (seg)</label>
      <input
        type="number"
        id="tllc"
        v-model.number="localSigns.tllc"
        step="0.1"
        min="0"
        max="10"
        placeholder="2.0"
        @input="emitUpdate"
      />
      <span class="info-entradas-de-texto-govco">Normal: 1-2 segundos</span>
    </div>
    
  </div>
</template>

<script setup>
import { reactive, watch, onMounted, nextTick } from 'vue';
import DesplegableGovco from '../common/DesplegableGovco.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      temperature: '',
      heartRate: '',
      respiratoryRate: '',
      weight: '',
      bodyCondition: '',
      mucosa: '',
      hydration: '',
      tllc: ''
    })
  },
  errors: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update:modelValue']);

const localSigns = reactive({ ...props.modelValue });

// Opciones para los dropdowns
const bodyConditionOptions = [
  { value: 'emaciado', text: '1 - Emaciado (costillas muy visibles)' },
  { value: 'delgado', text: '2 - Delgado (se palpan costillas fácilmente)' },
  { value: 'ideal', text: '3 - Ideal (cintura visible, costillas palpables)' },
  { value: 'sobrepeso', text: '4 - Sobrepeso (difícil palpar costillas)' },
  { value: 'obeso', text: '5 - Obeso (sin cintura visible)' }
];

const mucosaOptions = [
  { value: 'rosadas', text: 'Rosadas (normal)' },
  { value: 'palidas', text: 'Pálidas (posible anemia)' },
  { value: 'cianoticas', text: 'Cianóticas (falta de oxígeno)' },
  { value: 'ictericas', text: 'Ictéricas (problemas hepáticos)' }
];

const hydrationOptions = [
  { value: 'normal', text: 'Normal (< 2 seg)' },
  { value: 'leve', text: 'Deshidratación leve (5%)' },
  { value: 'moderada', text: 'Deshidratación moderada (7-8%)' },
  { value: 'severa', text: 'Deshidratación severa (> 10%)' }
];

watch(() => props.modelValue, newVal => Object.assign(localSigns, newVal), { deep: true });

function emitUpdate() {
  emit('update:modelValue', { ...localSigns });
}

// Función para prevenir scroll automático de GOV.CO
function preventScrollOnInteractions() {
  const handleDropdownOpen = (e) => {
    const element = e.target.closest('.desplegable-govco');
    if (element) {
      const scrollPos = window.scrollY || document.documentElement.scrollTop;
      
      setTimeout(() => {
        window.scrollTo(0, scrollPos);
      }, 50);
    }
  };

  const vitalSignsGrid = document.querySelector('.vital-signs-grid');
  if (vitalSignsGrid) {
    vitalSignsGrid.removeEventListener('click', handleDropdownOpen);
    vitalSignsGrid.addEventListener('click', handleDropdownOpen);
  }
}

onMounted(() => {
  preventScrollOnInteractions();
  
  if (typeof window !== 'undefined') {
    window.addEventListener('load', () => {
      preventScrollOnInteractions();
    });
  }
});
</script>

<style scoped>
.vital-signs-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);  
  column-gap: 2rem;
  row-gap: 1.5rem;
  padding: 1.5rem;
}

@media (max-width: 768px) {
  .vital-signs-grid {
    grid-template-columns: 1fr;
  }
}

.entradas-de-texto-govco,
.input-like-govco {
  width: 100%;
}

.input-like-govco {
  display: flex;
  flex-direction: column;
  width: 100%;
}

.entradas-de-texto-govco input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  height: 44px;
  box-sizing: border-box;
}

.info-entradas-de-texto-govco {
  display: block;
  color: #666;
  font-size: 0.85rem;
  margin-top: 0.25rem;
}

.error-text {
  color: #d32f2f;
  font-size: 0.875rem;
  display: block;
  margin-top: 0.25rem;
}

:deep(.desplegable-govco .desplegable-items) { 
  z-index: 1500 !important;
}
</style>