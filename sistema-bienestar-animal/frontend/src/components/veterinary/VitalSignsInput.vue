<template>
  <div class="vital-signs-grid">
    <!-- Temperatura -->
    <div class="entradas-de-texto-govco">
      <label for="temperature">
        Temperatura (°C)<span aria-required="true">*</span>
      </label>
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

    <!-- Frecuencia cardíaca -->
    <div class="entradas-de-texto-govco">
      <label for="heartRate">
        Frecuencia cardíaca (lpm)<span aria-required="true">*</span>
      </label>
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

    <!-- Frecuencia respiratoria -->
    <div class="entradas-de-texto-govco">
      <label for="respiratoryRate">
        Frecuencia respiratoria (rpm)<span aria-required="true">*</span>
      </label>
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
      <label for="weight">
        Peso (kg)<span aria-required="true">*</span>
      </label>
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
    <div class="input-like-govco">
      <label for="bodyCondition" class="label-desplegable-govco">
        Condición corporal<span aria-required="true">*</span>
      </label>
      <div class="desplegable-govco" data-type="basic">
        <select 
          id="bodyCondition" 
          v-model="localSigns.bodyCondition"
          @change="emitUpdate"
        >
          <option disabled value="">Evaluar</option>
          <option value="emaciado">1 - Emaciado</option>
          <option value="delgado">2 - Delgado</option>
          <option value="ideal">3 - Ideal</option>
          <option value="sobrepeso">4 - Sobrepeso</option>
          <option value="obeso">5 - Obeso</option>
        </select>
      </div>
      <span v-if="errors?.bodyCondition" class="alert-desplegable-govco">{{ errors.bodyCondition }}</span>
    </div>

    <!-- Mucosas -->
    <div class="input-like-govco">
      <label for="mucosa" class="label-desplegable-govco">
        Mucosas
      </label>
      <div class="desplegable-govco" data-type="basic">
        <select 
          id="mucosa" 
          v-model="localSigns.mucosa"
          @change="emitUpdate"
        >
          <option value="">No evaluado</option>
          <option value="rosadas">Rosadas (normal)</option>
          <option value="palidas">Pálidas</option>
          <option value="cianoticas">Cianóticas</option>
          <option value="ictericas">Ictéricas</option>
        </select>
      </div>
    </div>

    <!-- Hidratación -->
    <div class="input-like-govco">
      <label for="hydration" class="label-desplegable-govco">
        Hidratación
      </label>
      <div class="desplegable-govco" data-type="basic">
        <select 
          id="hydration" 
          v-model="localSigns.hydration"
          @change="emitUpdate"
        >
          <option value="">No evaluado</option>
          <option value="normal">Normal (< 2 seg)</option>
          <option value="leve">Deshidratación leve (5%)</option>
          <option value="moderada">Deshidratación moderada (7-8%)</option>
          <option value="severa">Deshidratación severa (> 10%)</option>
        </select>
      </div>
    </div>

    <!-- TLLC -->
    <div class="entradas-de-texto-govco">
      <label for="tllc">
        TLLC (segundos)
      </label>
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
import { reactive, watch, onMounted } from 'vue';

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

watch(() => props.modelValue, (newVal) => {
  Object.assign(localSigns, newVal);
}, { deep: true });

function emitUpdate() {
  emit('update:modelValue', { ...localSigns });
}

onMounted(() => {
  // Inicializar dropdowns de GOV.CO
  if (window.GOVCo?.init) {
    const dropdowns = document.querySelectorAll('.desplegable-govco');
    dropdowns.forEach(dropdown => {
      window.GOVCo.init(dropdown.parentElement);
    });
  }
});
</script>

<style scoped>
.vital-signs-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.entradas-de-texto-govco input,
.desplegable-govco select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
}

.info-entradas-de-texto-govco {
  display: block;
  color: #666;
  font-size: 0.85rem;
  margin-top: 0.25rem;
}

.error-text, .alert-desplegable-govco {
  display: block;
  color: #b00020;
  font-size: 0.85rem;
  margin-top: 0.5rem;
}

.input-like-govco {
  display: flex;
  flex-direction: column;
  width: 100%;
}

@media (max-width: 768px) {
  .vital-signs-grid {
    grid-template-columns: 1fr;
  }
}
</style>