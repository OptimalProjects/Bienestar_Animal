<template>
  <!-- Grid de 2 columnas para signos vitales -->
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
    <div class="input-like-govco">
      <label for="bodyCondition" class="label-desplegable-govco">Condición corporal<span aria-required="true">*</span></label>
      <div class="desplegable-govco" data-type="basic" id="bodyCondition-dropdown">
        <select id="bodyCondition" v-model="localSigns.bodyCondition" @change="onDropdownChange">
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
      <label for="mucosa" class="label-desplegable-govco">Mucosas</label>
      <div class="desplegable-govco" data-type="basic" id="mucosa-dropdown">
        <select id="mucosa" v-model="localSigns.mucosa" @change="onDropdownChange">
          <option disabled value="">No evaluado</option>
          <option value="rosadas">Rosadas</option>
          <option value="palidas">Pálidas</option>
          <option value="cianoticas">Cianóticas</option>
          <option value="ictericas">Ictéricas</option>
        </select>
      </div>
    </div>

    <!-- Hidratación -->
    <div class="input-like-govco">
      <label for="hydration" class="label-desplegable-govco">Hidratación</label>
      <div class="desplegable-govco" data-type="basic" id="hydration-dropdown">
        <select id="hydration" v-model="localSigns.hydration" @change="onDropdownChange">
          <option disabled value="">No evaluado</option>
          <option value="normal">Normal (&lt; 2 seg)</option>
          <option value="leve">Deshidratación leve (5%)</option>
          <option value="moderada">Moderada (7-8%)</option>
          <option value="severa">Severa (&gt; 10%)</option>
        </select>
      </div>
    </div>

    <!-- TLLC -->
    <div class="entradas-de-texto-govco">
      <label for="tllc">TLLC (segundos)</label>
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

const props = defineProps({
  modelValue: Object,
  errors: Object
});

const emit = defineEmits(['update:modelValue']);

const localSigns = reactive({ ...props.modelValue });

watch(() => props.modelValue, newVal => Object.assign(localSigns, newVal), { deep: true });

function emitUpdate() {
  emit('update:modelValue', { ...localSigns });
}

function onDropdownChange() {
  nextTick(() => {
    emitUpdate();
  });
}

function initializeGovcoComponents() {
  if (typeof window === 'undefined' || !window.GOVCo) return;
  
  nextTick(() => {
    // Inicializar dropdowns básicos
    const dropdowns = document.querySelectorAll('.desplegable-govco[data-type="basic"]');
    dropdowns.forEach(dropdown => {
      if (window.GOVCo?.init) {
        window.GOVCo.init(dropdown.parentElement);
      }
    });
  });
}

onMounted(() => {
  initializeGovcoComponents();
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
.input-like-govco,
.desplegable-govco {
  width: 100%;
}

.input-like-govco {
  display: flex;
  flex-direction: column;
  width: 100%;
}

.input-like-govco label {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.entradas-de-texto-govco input,
.desplegable-govco select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  height: 44px;
  box-sizing: border-box;
}

.desplegable-govco select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23333' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 12px 8px;
  padding-right: 2.5rem;
}

.info-entradas-de-texto-govco {
  display: block;
  color: #666;
  font-size: 0.85rem;
  margin-top: 0.25rem;
}

.error-text,
.alert-desplegable-govco {
  color: #d32f2f;
  font-size: 0.875rem;
  display: block;
  margin-top: 0.25rem;
}

:deep(.desplegable-govco .desplegable-items) { 
  z-index: 1500 !important;
}
</style>